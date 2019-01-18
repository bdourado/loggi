# Loggi
Loggi services API package

### Available services:

* Estimate order
* Cities available

  
### Installation

<code>composer require bdourado/loggi</code>

### How to use


```php
//your Loggi's account email
$email = 'youremail@yourdomain.com';

//your Loggi's account password
$password = 'yourPassword';

//your environment 'dev' or 'prod'
$env = 'dev';

//full address to withdraw
$fullAddressWithDraw = array(
    'street_number' => '1757',
    'route' => 'Avenida Angélica',
    'neighborhood' => 'Higienopolis',
    'city' => 'São Paulo',
    'state' => 'São Paulo',
    'state_code' => 'SP',
    'country' => 'Brasil',
    'country_code' => 'BR',
    'postal_code' => '01227-200'
);

//full address to delivery
$fullAddressDelivery = array(
    'street_number' => '260',
    'route' => 'Rua Avanhandava',
    'neighborhood' => 'Bela Vista',
    'city' => 'São Paulo',
    'state' => 'São Paulo',
    'state_code' => 'SP',
    'country' => 'Brasil',
    'country_code' => 'BR',
    'postal_code' => '01306-000'
);

$loggi = new Loggi($env,$email,$password);

//get estimated order
$estimatedOrder = $loggi->getEstimatedOrder($fullAddressWithDraw['postal_code'],$fullAddressDelivery['postal_code']);

//create a order
$createOrder = $loggi->createOrder($fullAddressWithDraw,$fullAddressDelivery);

//confirm a created order
$confirmOrder = $loggi->confirmOrder($createOrder);

```
