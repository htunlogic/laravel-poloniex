### Installation

Require this package in your project with `composer require htunlogic/laravel-poloniex`.

Add the service provider to your `config/app.php`:
 
 ``` 
 'providers' => [
 
     Htunlogic\Poloniex\PoloniexServiceProvider::class,
     
 ],
 ```
 
...run `php artisan vendor:publish` to copy the config file.

Edit the `config/poloniex.php` or add Poloniex api and secret in your `.env` file

```
POLONIEX_KEY={YOUR_API_KEY}
POLONIEX_SECRET={YOUR_API_SECRET}

```

Optionally you can add alias to your `config/app.php`:

```    
'aliases' => [
           
    'Poloniex' => Htunlogic\Poloniex\Poloniex::class,
           
],
```

Usage examples: 
``` 
use Htunlogic\Poloniex\Poloniex;
```
``` 
Poloniex::getBalanceFor('BTC');
Poloniex::getOpenOrders('BTC_XMR');
Poloniex::getMyTradeHistory('BTC_XMR');
Poloniex::buy('BTC_XMR', 0.013, 1, 'postOnly');
Poloniex::sell('BTC_XMR', 0.013, 1, 'postOnly');
Poloniex::cancelOrder('BTC_XMR', 123);
Poloniex::withdraw('BTC', 1, '14PJdqimDkCqWCH1oXy4sVV6nwweqXYDjt');
Poloniex::getTradeHistory('BTC_XMR');
Poloniex::getOrderBook('BTC_XMR');
Poloniex::getVolumeFor('BTC_XMR');
Poloniex::getTradingPairs();
Poloniex::getTicker("BTC_XMR");
Poloniex::getBalances();
Poloniex::getVolume();
Poloniex::getTickers();
```
