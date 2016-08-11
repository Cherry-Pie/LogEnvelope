<?php 

namespace Yaro\LogEnvelope\Drivers;

class Slack extends AbstractDriver
{
        
    protected function check() 
    {
        return $this->isEnabled() && (isset($this->config['channel']) && $this->config['channel'] && isset($this->config['token']) && $this->config['token']);
    } // end check
    
    public function send()
    {
        if (!$this->check()) {
            return;
        }
        
        $data = $this->data;
        
        $text = sprintf(
            '[%s] %s%s[%s] *%s*%sin %s line %s%s', 
            $data['method'], 
            $data['fullUrl'], 
            "\n",
            $data['class'],
            $data['exception'],
            "\n",
            $data['file'],
            $data['line'],
            "\n \n"
        );

        $attachments = [
            'fallback' => $text,
            'color'    => '#55688a',
            'text'     => '',
        ];
        $biggestNumberLength = strlen(max(array_keys($data['file_lines'])));
        foreach ($data['file_lines'] as $num => $line) {
            $num = str_pad($num, $biggestNumberLength, ' ', STR_PAD_LEFT);
            // to be sure that we'll have no extra endings
            $line = preg_replace('~[\r\n]~', '', $line);
            // double spaces, so in slack it'll be more readable
            $line = preg_replace('~(\s+)~', "$1$1", $line);
            $attachments['text'] .= $num .'| '. $line ."\n";
        }


        $url = 'https://slack.com/api/chat.postMessage?'
             . 'token='. $this->config['token']
             . '&channel='. urlencode($this->config['channel'])
             . '&text='. urlencode($text)
             . '&username='. urlencode($this->config['username']) 
             . '&as_user=false&icon_url=http%3A%2F%2Fcherry-pie.co%2Fimg%2Flog-envelope.png&mrkdwn=1&pretty=1&attachments='. urlencode(json_encode([$attachments]));
        
        @file_get_contents($url);
    } // end send
    
}
