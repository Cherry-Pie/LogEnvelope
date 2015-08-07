<?php

namespace Yaro\LogEnvelope;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;


class LogEnvelope
{
    
    private $config = [];
    
    public function __construct()
    {
        $this->config['except'] = config('yaro.log-envelope.except', []);
        $this->config['email_to'] = config('yaro.log-envelope.email_to');
        $this->config['email_from'] = config('yaro.log-envelope.email_from');
        $this->config['count'] = config('yaro.log-envelope.lines_count', 12);
        
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
        $data['method']  = Request::method();
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
        
        $count = $this->config['count'];
        $lines = file($data['file']);
        $data['exegutor'] = [];
        
        for ($i = -1 * abs($count); $i <= abs($count); $i++) {
            $data['exegutor'][] = $this->getLineInfo($lines, $data['line'], $i);
        }
        $data['exegutor'] = array_filter($data['exegutor']);
        
        return $data;
    } // end getEmailData
    
    private function getLineInfo($lines, $line, $i)
    {
        $currentLine = $line + $i;
        // cuz array starts with 0, when file lines start count from 1
        $index = $currentLine - 1;
        
        if (!array_key_exists($index, $lines)) {
            return;
        }
        return [
            'line' => '<span style="color:#aaaaaa;">'. $currentLine .'.</span> '. SyntaxHighlight::process($lines[$index]),
            'wrap_left' => $i ? '' : '<span style="color: #F5F5F5; background-color: #5A3E3E; width: 100%; display: block;">',
            'wrap_right' => $i ? '' : '</span>',
        ];
    }

}

