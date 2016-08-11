<?php 

namespace Yaro\LogEnvelope\Drivers;

use Yaro\LogEnvelope\Drivers\Mail;
use Yaro\LogEnvelope\Drivers\Database;
use Yaro\LogEnvelope\Drivers\Telegram;
use Yaro\LogEnvelope\Drivers\Slack;
use Yaro\LogEnvelope\Drivers\Dummy;

class DriverFactory 
{
    
    public static function create($driver, $data)
    {
        switch ($driver) {
            case 'mail':
                return new Mail($data);
            
            case 'database':
                return new Database($data);
                
            case 'telegram':
                return new Telegram($data);
                
            case 'slack':
                return new Slack($data);
            
            default:
                return new Dummy($data);
        }
    } // end create
    
}
