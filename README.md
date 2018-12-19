# Loggi
Loggi services API package

### Available services:

* Estimated order
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

//zip code to take the package
$zipWithDraw = '01306-000';

//zip code to deliver the package
$zipDelivery = '01227-000';

$loggi = new Loggi($env,$email,$password);

$res = $loggi->getEstimatedOrder($zipWithDraw,$zipDelivery);
```
