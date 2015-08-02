<?php

namespace Yaro\LogEnvelope;

use Mail;
use Request;
use Session;


class LogEnvelope
{
    
    private $config = [];
    
    public function __construct()
    {
        $this->config['except'] = config('yaro.log-envelope.except', []);
        $this->config['email_to'] = config('yaro.log-envelope.email_to');
        $this->config['email_from'] = config('yaro.log-envelope.email_from');
        
        if (!$this->config['email_from']) {
            $this->config['email_from'] = 'log-envelop@'. Request::server('SERVER_NAME');
        }
    } // end __construct
    
    public function send($exception)
    {
        $config = $this->config;
        if (!$config['email_to']) {
            return;
        }
        
        $data = $this->getEmailData($exception);
        
        if ($this->isSkipException($data['class'])) {
            return;
        }
        
        Mail::send('log-envelope::main', $data, function($message) use($data, $config) {
            $subject = '[' . $data['class'] .'] @ '. $data['host'] .': ' . $data['exception'];
            
            // to protect from gmail's anchors automatic generating
            $message->setBody(
                preg_replace(
                    ['~\.~', '~http~'], 
                    ['<span>.</span>', '<span>http</span>'], 
                    $message->getBody()
                )
            );
            
            $message->to($config['email_to'])
                    ->from($config['email_from'], 'Log Envelope')
                    ->subject($subject);
        });
    } // end send
    
    public function isSkipException($exceptionClass)
    {
        return in_array($exceptionClass, $this->config['except']);
    } // end isSkipException
    
    private function getEmailData($exception)
    {
        $data = [];
        
        $data['host']    = Request::server('SERVER_NAME');
        $data['fullUrl'] = Request::fullUrl();
        $data['exception'] = $exception->getMessage();
        $data['error'] = $exception->getTraceAsString();
        $data['line']  = $exception->getLine();
        $data['file']  = $exception->getFile();
        $data['class'] = get_class($exception);
        $data['storage'] = array(
            'SERVER'  => Request::server(),
            'GET'     => Request::query(),
            'POST'    => $_POST,
            'FILE'    => Request::file(),
            'OLD'     => Request::old(),
            'COOKIE'  => Request::cookie(),
            'SESSION' => Session::all(),
            'HEADERS' => Request::header(),
        );
        
        $data['storage'] = array_filter($data['storage']);
        
        return $data;
    } // end getEmailData

}

