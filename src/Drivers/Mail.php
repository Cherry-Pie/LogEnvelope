<?php 

namespace Yaro\LogEnvelope\Drivers;

use Illuminate\Support\Facades\Mail as MailFacade;
use Yaro\LogEnvelope\Mail\LogEmail;

class Mail extends AbstractDriver
{
    
    protected function prepare() 
    {
        $this->config['from_name']  = $this->config['from_name'] ?: 'Log Envelope';
        $this->config['from_email'] = $this->config['from_email'] ?: 'logenvelope@'. $this->data['host'];
    } // end prepare
    
    protected function check() 
    {
        return $this->isEnabled() && (isset($this->config['to']) && $this->config['to']);
    } // end check
    
    public function send()
    {
        if (!$this->check()) {
            return;
        }
        
        $data = $this->data;
        $config = $this->config;
        
        MailFacade::queue(new LogEmail($data, $config));
    } // end send
    
}
