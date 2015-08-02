# Log Envelope

Laravel 5 package for mailing errors.


## Installation 

You can install the package through Composer.
```bash
composer require yaro/log-envelope
```
You must install this service provider.
```php
// config/app.php
'providers' => [
    //...
    Yaro\LogEnvelope\ServiceProvider::class,
    //...
];
```

Then publish the config file of the package using artisan.
```bash
php artisan vendor:publish --provider="Yaro\LogEnvelope\ServiceProvider"
```

And put receiver name to it.


Add to your Exception Handler's (```/app/Exceptions/Handler.php``` by default) ```report``` method these lines:
```php
//...
public function report(Exception $e)
{
    \LogEnvelope::send($e); // <- yeah, that line
    
    //...
    
    return parent::report($e); 
}
//...
```


## License
The MIT License (MIT). Please see [LICENSE](https://github.com/Cherry-Pie/LogEnvelope/blob/master/LICENSE) for more information.
