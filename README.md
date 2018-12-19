# Loggi
Loggi services API package

### Available services:

* Estimated order
* Cities available

  
### Installation

<code>composer require bdourado/loggi</code>

### How to use



```php
//zip code to take the package
$zipWithDraw = '01306-000';

//zip code to deliver the package
$zipDelivery = '01227-000';

//your Loggi's account email
$email = 'youremail@yourdomain.com';

//your Loggi's account password
$password = 'yourPassword';

//your environment
$env = 'dev'; // 'dev' or 'prod'

$loggi = new Loggi($env,$email,$password,$zipWithDraw,$zipDelivery);

$res = $loggi->getEstimatedOrder();
```
