<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \Bdourado\Loggi\Loggi;

$zipWithDraw = '01306-000';
$zipDelivery = '01227-000';
$email = 'youremail@yourdomain.com';
$password = 'yourPassword';
$env = 'dev'; // 'dev' or 'prod'

$loggi = new Loggi($env,$email,$password,$zipWithDraw,$zipDelivery);

$res = $loggi->getEstimatedOrder();

echo '<pre>';
print_r($res);
echo '</pre>';