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
