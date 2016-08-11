<?php 

namespace Yaro\LogEnvelope\Drivers;

use Yaro\LogEnvelope\Models\ExceptionModel;

class Database extends AbstractDriver
{
            
    protected function check() 
    {
        return $this->isEnabled();
    } // end check
    
    public function send()
    {
        if (!$this->check()) {
            return;
        }
        
        $data = $this->data;
        
        unset($data['exegutor']);
        unset($data['storage']);

        $model = $this->config['model'];
        $model::create($data);
    } // end send
    
}
