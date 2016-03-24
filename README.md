# Log Envelope

Laravel 5 package for logging errors to your e-mail(s), database or both!


## Installation 

You can install the package through Composer.
```bash
composer require yaro/log-envelope
```
You must install this service provider. Make this the very first provider in list.
```php
// config/app.php
'providers' => [
    // make this very first provider
    // so fatal exceptions can be catchable by envelope
    Yaro\LogEnvelope\ServiceProvider::class,
    //...
    //...
];
```

Then publish the config and migration file of the package using artisan.
```bash
php artisan vendor:publish --provider="Yaro\LogEnvelope\ServiceProvider"
```

And put receiver email into the configuration, this can also be an array to send to more users:

```
'email_to' => '',

--OR--

'email_to' => [
    'example@example.com',
    'example2@example2.com'
],
```

You can also choose to log your errors to your database, or log to both ways, to do this change the config option to your likings:
```
/*
 * Decide where to log to
 *
 * Options: mail, database or both
 */
'log_to' => 'database',
````

If you don't want the messages (e-mails) to be send immediately, change this to true so they get queued:
```
/*
 * Decide wether it should queue
 *
 */
'should_queue' => false,
```

Add to your Exception Handler's (```/app/Exceptions/Handler.php``` by default) ```report``` method these line:
```php
//...
public function report(Exception $e)
{
    $res = parent::report($e);
    
    \LogEnvelope::send($e);
    //...
    
    return $res; 
}
//...
```

## TODO
- Add slack support

## Results
Something like this with other info for debugging.
![results](https://raw.githubusercontent.com/Cherry-Pie/LogEnvelope/master/envelope.png)


## License
The MIT License (MIT). Please see [LICENSE](https://github.com/Cherry-Pie/LogEnvelope/blob/master/LICENSE) for more information.
