<?php 

namespace Yaro\LogEnvelope\Drivers;

abstract class AbstractDriver
{
    
    protected $config = [];
    protected $data   = [];
    
    public function __construct($data)
    {
        $this->data = $data;
    } // end __construct
    
    public function setConfig($config)
    {
        $this->config = $config;
        $this->prepare();
        
        return $this;
    } // end setConfig
    
    protected function prepare() {} // end prepare
    
    protected function isEnabled()
    {
        return isset($this->config['enabled']) && $this->config['enabled'];
    } // end isEnabled
    
    abstract public function send();
    
    abstract protected function check();
    
}
