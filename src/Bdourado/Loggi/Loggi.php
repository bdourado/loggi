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
    private $zipWithDraw;
    private $zipDelivery;

    //env = dev or prod
    public function __construct($env, $email, $password, $zipWithDraw, $zipDelivery)
    {
        if ( $env == 'prod' ) {
            $this->url = self::PROD_URL;
        } else {
            $this->url = self::DEV_URL;
        }

        $this->email = $email;
        $this->password = $password;
        $this->zipWithDraw = $zipWithDraw;
        $this->zipDelivery = $zipDelivery;

        $apiKey = $this->getApiKey();

        if ( isset($apiKey->data->login->user->apiKey) ) {
            $this->apiKey = $apiKey->data->login->user->apiKey;
        }
    }

    private function getApiKey()
    {
        $query = 'mutation{login(input:{email: "'.$this->email.'",password: "'.$this->password.'"}){user {apiKey}}}';
        $response = $this->curl($query);
        return $response;
    }

    public function getAllCities()
    {
        $query = 'query { allCities { edges { node { pk name slug } } } }';
        $response = $this->curl($query);
        return $response;
    }

    public function getEstimatedOrder(){

        $latLonWithDraw = $this->getLatLon($this->zipWithDraw);
        $latLonDelivery = $this->getLatLon($this->zipDelivery);

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

    private function getLatLon($zip)
    {
        $opts = [
            'http' => [
                'method'=>"GET",
                'header'=>"User-Agent: 	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0  \r\n"
            ]
        ];
        $context = stream_context_create($opts);

        $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&q=brasil+".$zip;

        $response = file_get_contents($url, false, $context);
        $responseJson = json_decode($response);

        $latLon = array(
            'lat' => $responseJson[0]->lat,
            'lon' => $responseJson[0]->lon
        );

        return $latLon;

    }

    private function curl($query)
    {
        $queryBuild = http_build_query(['query' => $query]);
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
