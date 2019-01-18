<?php

namespace Bdourado\Loggi;

class Loggi
{

    const DEV_URL   = "https://staging.loggi.com/public-graphql?";
    const PROD_URL  = "https://www.loggi.com/public-graphql";

    private $url;
    private $email;
    private $password;
    private $apiKey;

    //env = dev or prod
    public function __construct($env, $email, $password)
    {
        if ( $env == 'prod' ) {
            $this->url = self::PROD_URL;
        } else {
            $this->url = self::DEV_URL;
        }

        $this->email = $email;
        $this->password = $password;

        $apiKey = $this->getApiKey();

        if ( isset($apiKey->data->login->user->apiKey) ) {
            $this->apiKey = $apiKey->data->login->user->apiKey;
        }
    }

    /**
     * @return mixed
     * get the api key
     */
    private function getApiKey()
    {
        $query = 'mutation{login(input:{email: "'.$this->email.'",password: "'.$this->password.'"}){user {apiKey}}}';
        $response = $this->curl($query);

        return $response;
    }

    /**
     * @return mixed
     * Get all cities that Loggi works on
     */
    public function getAllCities()
    {
        $query = 'query { allCities { edges { node { pk name slug } } } }';
        $response = $this->curl($query);
        return $response;
    }

    /**
     * @param $zipWithDraw
     * @param $zipDelivery
     * @return mixed
     * Calculate the price for an order of a specific city, transport type and points.
     */
    public function getEstimatedOrder($zipWithDraw, $zipDelivery){

        $latLonWithDraw = $this->getLatLon($zipWithDraw);
        $latLonDelivery = $this->getLatLon($zipDelivery);

        if (! isset($latLonWithDraw) || ! isset($latLonDelivery) ) {
            return FALSE;
        }

        $query = '
        query{
            estimateOrder(
                city: 1
                transportType: moto
                points: [
                    { lat: '.$latLonWithDraw["lat"].', lng:'.$latLonWithDraw["lon"].' }
                    { lat: '.$latLonDelivery["lat"].', lng:'.$latLonDelivery["lon"].', hasService: false }
                ]
            ) 
            {
                routeOptimized
                prices {
                    label
                    description
                    slo
                    sloDisplay
                    estimatedCost
                    distance
                    originalEta
                }
                waypoints {
                    index
                    indexDisplay
                    originalIndex
                    originalIndexDisplay
                    outOfCityCover
                    error
                }
            }
        }';
        $response = $this->curl($query);
        return $response;
    }

    /**
     * @param $fullAddressWithDraw
     * @param $fullAddressDelivery
     * @return bool|mixed
     * Create an inquiry for a specific city, type of delivery, points and type of service (slo).
     */
    public function createOrder($fullAddressWithDraw,$fullAddressDelivery, $returnAddress = FALSE)
    {

        if ( ! isset($fullAddressWithDraw) || ! isset($fullAddressDelivery) ) {
            return FALSE;
        }

        $latLonWithDraw = $this->getLatLon($fullAddressWithDraw['postal_code']);
        $latLonDelivery = $this->getLatLon($fullAddressDelivery['postal_code']);

        if ( is_array($returnAddress) ) {
            $latLonReturn = $this->getLatLon($returnAddress['postal_code']);

            if ( ! isset($latLonReturn) ) {
                return FALSE;
            }
        }

        if (! isset($latLonWithDraw) || ! isset($latLonDelivery) ) {
            return FALSE;
        }



        $query = '
           mutation {
              createOrderInquiry(input: {
                city: 1
                transportType: moto
                packageType: "document"
                slo: 1
                clientMutationId: "test_inquiry"
                waypoints: [
                  {
                    addressComplement: "Complemento retirada"
                    instructions: "Retirada de documento"
                    tag: , retirar_documento
                    addressData: {addressComponents: [
                        {longName: "'.$fullAddressWithDraw["street_number"].'", shortName: "'.$fullAddressWithDraw["street_number"].'", types: ["street_number"]}, 
                        {longName: "'.$fullAddressWithDraw["route"].'", shortName: "'.$fullAddressWithDraw["route"].'", types: ["route"]}, 
                        {longName: "'.$fullAddressWithDraw["neighborhood"].'", shortName: "'.$fullAddressWithDraw["neighborhood"].'", types: ["neighborhood", "political"]}, 
                        {longName: "'.$fullAddressWithDraw["city"].'", shortName: "'.$fullAddressWithDraw["city"].'", types: ["locality", "political"]}, 
                        {longName: "'.$fullAddressWithDraw["country"].'", shortName: "'.$fullAddressWithDraw["country_code"].'", types: ["country", "political"]}, 
                        {longName: "'.$fullAddressWithDraw["postal_code"].'", shortName: "'.$fullAddressWithDraw["postal_code"].'", types: ["postal_code"]}], 
                        formattedAddress: "'.$fullAddressWithDraw["route"].', '.$fullAddressWithDraw["street_number"].' - '.$fullAddressWithDraw["neighborhood"].', '.$fullAddressWithDraw["city"].' - '.$fullAddressWithDraw["state_code"].', '.$fullAddressWithDraw["postal_code"].', '.$fullAddressWithDraw["country"].'", geometry: 
                        {location: {lat: '.$latLonWithDraw["lat"].', lng: '.$latLonWithDraw["lon"].'}}, 
                        types: ["street_address"]}
                  }, {
                    addressComplement: "Complemento da entrega"
                    instructions: "Entregar documento"
                    tag: entregar
                    addressData: {addressComponents: [
                        {longName: "'.$fullAddressDelivery["street_number"].'", shortName: "'.$fullAddressDelivery["street_number"].'", types: ["street_number"]}, 
                        {longName: "'.$fullAddressDelivery["route"].'", shortName: "'.$fullAddressDelivery["route"].'", types: ["route"]}, 
                        {longName: "'.$fullAddressDelivery["neighborhood"].'", shortName: "'.$fullAddressDelivery["neighborhood"].'", types: ["neighborhood", "political"]}, 
                        {longName: "'.$fullAddressDelivery["city"].'", shortName: "'.$fullAddressDelivery["city"].'", types: ["locality", "political"]}, 
                        {longName: "'.$fullAddressDelivery["country"].'", shortName: "'.$fullAddressDelivery["country_code"].'", types: ["country", "political"]}, 
                        {longName: "'.$fullAddressDelivery["postal_code"].'", shortName: "'.$fullAddressDelivery["postal_code"].'", types: ["postal_code"]}], 
                        formattedAddress: "'.$fullAddressDelivery["route"].', '.$fullAddressDelivery["street_number"].' - '.$fullAddressDelivery["neighborhood"].', '.$fullAddressDelivery["city"].' - '.$fullAddressDelivery["state_code"].', '.$fullAddressDelivery["postal_code"].', '.$fullAddressDelivery["country"].'", geometry: 
                        {location: {lat: '.$latLonDelivery["lat"].', lng: '.$latLonDelivery["lon"].'}}, 
                        types: ["street_address"]}
                    
                  }';

        if ( is_array($returnAddress) ) {
            $query .= ', {
                addressComplement: "Complemento retorno"
                    instructions: "Retorno de documento"
                    tag: outros
                    isReturn: true
                    addressData: {addressComponents: [
                        {longName: "'.$returnAddress["street_number"].'", shortName: "'.$returnAddress["street_number"].'", types: ["street_number"]}, 
                        {longName: "'.$returnAddress["route"].'", shortName: "'.$returnAddress["route"].'", types: ["route"]}, 
                        {longName: "'.$returnAddress["neighborhood"].'", shortName: "'.$returnAddress["neighborhood"].'", types: ["neighborhood", "political"]}, 
                        {longName: "'.$returnAddress["city"].'", shortName: "'.$returnAddress["city"].'", types: ["locality", "political"]}, 
                        {longName: "'.$returnAddress["country"].'", shortName: "'.$returnAddress["country_code"].'", types: ["country", "political"]}, 
                        {longName: "'.$returnAddress["postal_code"].'", shortName: "'.$returnAddress["postal_code"].'", types: ["postal_code"]}], 
                        formattedAddress: "'.$returnAddress["route"].', '.$returnAddress["street_number"].' - '.$returnAddress["neighborhood"].', '.$returnAddress["city"].' - '.$returnAddress["state_code"].', '.$returnAddress["postal_code"].', '.$returnAddress["country"].'", geometry: 
                        {location: {lat: '.$latLonReturn["lat"].', lng: '.$latLonReturn["lon"].'}}, 
                        types: ["street_address"]}
                  }';
        }

        $query .= ']
              }) {
                success
                inquiry {
                  pk
                  pricing {
                    totalCmGross
                    bonuses
                    totalCm
                    appliedBonuses {
                      discount
                      key
                      usercode
                    }
                  }
                  numWaypoints
                  productDescription
                  paymentMethod {
                    name
                  }
                }
                errors {
                  field
                  message
                }
              }
           }
        ';

        $response = $this->curl($query);
        return $response;
    }


    /**
     * @param $createdOrder
     * An order is created when an inquiry is confirmed.
     */
    public function confirmOrder($createdOrder)
    {
        if (
            ! isset($createdOrder->data->createOrderInquiry->inquiry->pk)
            ||
            ! isset($createdOrder->data->createOrderInquiry->inquiry->paymentMethod->name)
        )
        {
            return FALSE;
        }

        $pk = $createdOrder->data->createOrderInquiry->inquiry->pk;
        $paymentMethodName = $createdOrder->data->createOrderInquiry->inquiry->paymentMethod->name;

        $query = '
            mutation {
                confirmOrder(input: {
                    inquiry: "'.$pk.'"
                    paymentMethod: "'.$paymentMethodName.'"
                    clientMutationId: "test_inquiry"
            }) {
                success
                    order {
                        pk
                        status
                    }
                    errors {
                        field
                        message
                    }
                }
            }
        ';

        $response = $this->curl($query);
        return $response;
    }


    /**
     * @param $zip
     * @return array
     * get lat and long by zip code
     */
    private function getLatLon($zip)
    {
        $result = FALSE;

        //get full address by zipcode
        $zipCode = str_replace("-", "", $zip);
        $address = file_get_contents('https://viacep.com.br/ws/'. $zipCode . '/json/');
        $address = json_decode($address);

        foreach ($address as $key => $value) {
            $address->$key = str_replace(' ','+',$value);
        }

        $q = $address->logradouro.'+'.$address->cep.'&city='.$address->localidade;
        $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&q=".$q;

        //hack to get results from openstreet maps
        $opts = array(
            'http' => array(
                'method'=>"GET",
                'header'=>"User-Agent: 	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0  \r\n"
            )
        );

        $context = stream_context_create($opts);

        $response = file_get_contents($url, false, $context);
        $responseJson = json_decode($response);

        if ( $responseJson ) {
            $result = array(
                'lat' => $responseJson[0]->lat,
                'lon' => $responseJson[0]->lon
            );
        }

        return $result;
    }

    private function curl($query)
    {
        $queryBuild = http_build_query(array('query' => $query));
        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";

        if ( isset($this->apiKey) ) {
            $headers[] = "Authorization: ApiKey $this->email:$this->apiKey";
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryBuild);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $response = 'Error:' . curl_error($ch);
        }
        curl_close ($ch);

        return json_decode($response);
    }

    
}
