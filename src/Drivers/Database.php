<?php 

namespace Yaro\LogEnvelope\Drivers;

use Yaro\LogEnvelope\Models\ExceptionModel;

class Database extends AbstractDriver
{
    protected function check() 
    {
        return $this->isEnabled();
    }
    
    public function send()
    {
        if (!$this->check()) {
            return;
        }
        
        $data = $this->data;
        
        $data['exegutor'] = implode('<br>', array_map($data['exegutor'], function ($item) {
            return $item['wrap_left'] . $item['line'] . $item['wrap_right']);
        });
        $data['lines'] = implode("\n", $data['lines']);
        $data['storage'] = json_encode($data['storage']);

        $model = $this->config['model'];
        $model::create($data);
    }
}
