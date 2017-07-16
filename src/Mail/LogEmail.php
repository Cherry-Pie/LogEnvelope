<?php
namespace Yaro\LogEnvelope\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    public $data;
    public $config;
    
    public function __construct($data, $config)
    {
        $this->data = $data;
        $this->config = $config;
    }
    
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = sprintf('[%s] @ %s: %s', $this->data['class'], $this->data['host'], $this->data['exception']);
    
        // to protect from gmail's anchors automatic generating
        $this->withSwiftMessage(function ($message) {
            $message->setBody(
                preg_replace(
                    ['~\.~', '~http~'],
                    ['<span>.</span>', '<span>http</span>'],
                    $message->getBody()
                )
            );
        });
        
        return $this->view('log-envelope::main')
            ->with($this->data)
            ->to($this->config['to'])
            ->from($this->config['from_email'], $this->config['from_name'])
            ->subject($subject);
    }
}
