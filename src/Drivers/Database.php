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
        
        $data['exegutor'] = implode('<br>', $data['exegutor']);
        $data['lines'] = implode('<br>', $data['lines']);
        $data['storage'] = print_r($data['storage'], true);

        $model = $this->config['model'];
        $model::create($data);
    } // end send
    
}
