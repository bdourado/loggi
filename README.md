# loggi
Loggi services API package

### Available services:
<li>Estimated order</li>
<li>Cities available</li>

### Installation

<code>composer require bdourado/loggi</code>

### How to use


```
$zipWithDraw = '01306-000';
$zipDelivery = '01227-000';
$email = 'youremail@yourdomain.com';
$password = 'yourPassword';
$env = 'dev'; // 'dev' or 'prod'

$loggi = new Loggi($env,$email,$password,$zipWithDraw,$zipDelivery);

$res = $loggi->getEstimatedOrder();
```
