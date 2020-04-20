# Log Envelope

Laravel 5|6|7 package for logging errors to your e-mail(s), telegram, slack and database!

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Cherry-Pie/LogEnvelope/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Cherry-Pie/LogEnvelope/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/Cherry-Pie/LogEnvelope/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Cherry-Pie/LogEnvelope/build-status/master)
[![Total Downloads](https://poser.pugx.org/yaro/log-envelope/downloads)](https://packagist.org/packages/yaro/log-envelope)

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
];
```

Then publish the config and migration file of the package using artisan.
```bash
php artisan vendor:publish --provider="Yaro\LogEnvelope\ServiceProvider"
```

Change your Exception Handler's (```/app/Exceptions/Handler.php``` by default) ```report``` method like this:
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

Change config ```yaro.log-envelope.php``` for your needs. You can choose to log your errors to your database or send them to your email/telegram/slack. Emails are preferable, cuz they contains more debug information, such as traceback.

There is a ```censored_fields``` option which will change any fields value to `*****` if it is named in this array. For example by default it will change values for fields called `password` to `*****`.

Also there is ```force_config``` option, where you can define which configs to override for LogEnvelope execution. E.g., if you using some smtp for mail sending and queue it, you can change configs to send LogEnvelope emails immediately and not via smtp:
```
'force_config' => [
    'mail.driver' => 'sendmail',
    'queue.default' => 'sync',
],
```


## TODO
- highlight traceback in emails
- page with logs from database

## Results
Something like this with other info for debugging.
- - -
Email:

![results](https://raw.githubusercontent.com/Cherry-Pie/LogEnvelope/master/envelope-email.png)
- - -
Slack:

![results](https://raw.githubusercontent.com/Cherry-Pie/LogEnvelope/master/envelope-slack.jpg)
- - -
Telegram:

![results](https://raw.githubusercontent.com/Cherry-Pie/LogEnvelope/master/envelope-telegram.jpg)


## License
The MIT License (MIT). Please see [LICENSE](https://github.com/Cherry-Pie/LogEnvelope/blob/master/LICENSE) for more information.
