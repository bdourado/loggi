<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \Bdourado\Loggi\Loggi;

//your Loggi's account email
$email = 'youremail@domain.com';

//your Loggi's account password
$password = 'yourpassword';

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

$estimatedOrder = $loggi->getEstimatedOrder($fullAddressWithDraw['postal_code'],$fullAddressDelivery['postal_code']);

echo '<pre>';
print_r($estimatedOrder);
echo '</pre>';

$createOrder = $loggi->createOrder($fullAddressWithDraw,$fullAddressDelivery);

echo '<pre>';
print_r($createOrder);
echo '</pre>';

$confirmOrder = $loggi->confirmOrder($createOrder);

echo '<pre>';
print_r($confirmOrder);
echo '</pre>';