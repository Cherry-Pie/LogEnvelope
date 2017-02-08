<?php namespace Yaro\LogEnvelope;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Yaro\LogEnvelope\Models\ExceptionModel;

class LogEnvelope
{
    private $config = [];

    /**
     * LogEnvelope constructor.
     */
    public function __construct()
    {
        $this->config['except'] = config('yaro.log-envelope.except', []);
        $this->config['email_to'] = config('yaro.log-envelope.email_to');
        $this->config['email_from'] = config('yaro.log-envelope.email_from');
        $this->config['email_from_name'] = config('yaro.log-envelope.email_from_name', 'Log Envelope');
        $this->config['count'] = config('yaro.log-envelope.lines_count', 12);
        $this->config['should_queue'] = config('yaro.log-envelope.should_queue', true);
        $this->config['log_to'] = config('yaro.log-envelope.log_to', 'mail');

        if (!$this->config['email_from']) {
            $this->config['email_from'] = 'log-envelop@' . Request::server('SERVER_NAME');
        }
    }

    public function send($exception)
    {
        try {
            $data = $this->getExceptionData($exception);

            if ($this->isSkipException($data['class'])) {
                return;
            }

            /*
             * Send to both drivers
             */
            if($this->config['log_to'] == 'both'){
                $this->sendToDatabase($data);
                $this->sendMail($data);
                return;
            }

            /*
             * Send to database driver
             */
            if ($this->config['log_to'] == 'database') {
                $this->sendToDatabase($data);
                return;
            }

            /*
             * Send to mail driver
             */
            if ($this->config['log_to'] == 'mail') {
                $this->sendMail($data);

                return;
            }

        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * @param $exceptionClass
     * @return mixed
     */
    public function isSkipException($exceptionClass)
    {
        return in_array($exceptionClass, $this->config['except']);
    }

    /**
     * @param $exception
     * @return array
     */
    private function getExceptionData($exception)
    {
        $data = [];

        $data['host'] = Request::server('SERVER_NAME');
        $data['method'] = Request::method();
        $data['fullUrl'] = Request::fullUrl();
        $data['exception'] = $exception->getMessage();
        $data['error'] = $exception->getTraceAsString();
        $data['line'] = $exception->getLine();
        $data['file'] = $exception->getFile();
        $data['class'] = get_class($exception);
        $data['storage'] = array(
            'SERVER' => Request::server(),
            'GET' => Request::query(),
            'POST' => $_POST,
            'FILE' => Request::file(),
            'OLD' => Request::hasSession() ? Request::old() : [],
            'COOKIE' => Request::cookie(),
            'SESSION' => Request::hasSession() ? Session::all() : [],
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

        // to make symfony exception more readable
        if ($data['class'] == 'Symfony\Component\Debug\Exception\FatalErrorException') {
            preg_match("~^(.+)' in ~", $data['exception'], $matches);
            if (isset($matches[1])) {
                $data['exception'] = $matches[1];
            }
        }

        return $data;
    }

    /**
     * Gets information from the line
     *
     * @param $lines
     * @param $line
     * @param $i
     * @return array|void
     */
    private function getLineInfo($lines, $line, $i)
    {
        $currentLine = $line + $i;
        // cuz array starts with 0, when file lines start count from 1
        $index = $currentLine - 1;

        if (!array_key_exists($index, $lines)) {
            return;
        }
        return [
            'line' => '<span style="color:#aaaaaa;">' . $currentLine . '.</span> ' . SyntaxHighlight::process($lines[$index]),
            'wrap_left' => $i ? '' : '<span style="color: #F5F5F5; background-color: #5A3E3E; width: 100%; display: block;">',
            'wrap_right' => $i ? '' : '</span>',
        ];
    }

    /**
     * Sends error to database model
     *
     * @param $data
     * @return bool
     */
    private function sendToDatabase($data)
    {
        unset($data['exegutor']);
        unset($data['storage']);

        $exception = ExceptionModel::create($data);

        if($exception) return true;

        return false;
    }

    /**
     * Sends error to mail
     *
     * @param $data
     */
    private function sendMail($data)
    {
        $config = $this->config;

        if (!$config['email_to']) {
            return;
        }

        if ($this->config['should_queue']) {
            Mail::queue('log-envelope::main', $data, function ($message) use ($data, $config) {
                $subject = '[' . $data['class'] . '] @ ' . $data['host'] . ': ' . $data['exception'];

                // to protect from gmail's anchors automatic generating
                $message->setBody(
                    preg_replace(
                        ['~\.~', '~http~'],
                        ['<span>.</span>', '<span>http</span>'],
                        $message->getBody()
                    )
                );

                $message->to($config['email_to'])
                    ->from($config['email_from'], $config['email_from_name'])
                    ->subject($subject);
            });
        } else {
            Mail::send('log-envelope::main', $data, function ($message) use ($data, $config) {
                $subject = '[' . $data['class'] . '] @ ' . $data['host'] . ': ' . $data['exception'];

                // to protect from gmail's anchors automatic generating
                $message->setBody(
                    preg_replace(
                        ['~\.~', '~http~'],
                        ['<span>.</span>', '<span>http</span>'],
                        $message->getBody()
                    )
                );

                $message->to($config['email_to'])
                    ->from($config['email_from'], $config['email_from_name'])
                    ->subject($subject);
            });
        }
    }
}
