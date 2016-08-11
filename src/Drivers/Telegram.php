<?php 

namespace Yaro\LogEnvelope\Drivers;

class Telegram extends AbstractDriver
{
        
    protected function check() 
    {
        return $this->isEnabled() && (isset($this->config['chats']) && $this->config['chats'] && is_array($this->config['chats']) && isset($this->config['token']) && $this->config['token']);
    } // end check
    
    public function send()
    {
        if (!$this->check()) {
            return;
        }
        
        //https://api.telegram.org/{$this->config['token']}/getUpdates
        $data = $this->data;
        
        $text = sprintf(
            '[%s] <b>%s</b>%s[%s] <b>%s</b>%sin <code>%s</code> line %s%s', 
            urlencode($data['method']), 
            urlencode($data['fullUrl']), 
            urlencode("\n \n"),
            urlencode($data['class']),
            urlencode($data['exception']),
            urlencode("\n"),
            urlencode($data['file']),
            $data['line'],
            urlencode("\n \n")
        );

        $biggestNumberLength = strlen(max(array_keys($data['file_lines'])));
        foreach ($data['file_lines'] as $num => $line) {
            $num = str_pad($num, $biggestNumberLength, ' ', STR_PAD_LEFT);
            $num = '<code>'. $num .'</code>';
            $text .= urlencode($num .'|<code>'. htmlentities($line) .'</code>');
        }

        foreach ($this->config['chats'] as $idUser) {
            $url = 'https://api.telegram.org/'. $this->config['token'] .'/sendMessage?disable_web_page_preview=true&chat_id='. $idUser
                 . '&parse_mode=HTML&text=';
                 
            @file_get_contents($url . $text);
        }
    } // end send
    
}
