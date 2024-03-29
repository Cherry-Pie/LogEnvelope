<?php 

namespace Yaro\LogEnvelope;

use Exception;
use SplFileObject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Yaro\LogEnvelope\Drivers\DriverFactory;

class LogEnvelope
{
    private $config = [];
    private $cachedConfig = [];

    public function __construct()
    {
        $this->config['censored_fields'] = config('yaro.log-envelope.censored_fields', ['password']);
        $this->config['except'] = config('yaro.log-envelope.except', []);
        $this->config['count'] = config('yaro.log-envelope.lines_count', 6);
        $this->config['drivers'] = config('yaro.log-envelope.drivers', []);
    } // end __construct

    public function send($exception)
    {
        $this->onBefore();

        try {
            $data = $this->getExceptionData($exception);

            if ($this->isSkipException($data['class'])) {
                return;
            }

            foreach ($this->config['drivers'] as $driver => $driverConfig) {
                DriverFactory::create($driver, $data)->setConfig($driverConfig)->send();
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        
        $this->onAfter();
    } // end send
    
    private function onBefore()
    {
        $this->cachedConfig = [];
        $forcedConfig = config('yaro.log-envelope.force_config', []);
        foreach ($forcedConfig as $configKey => $configValue) {
            $this->cachedConfig[$configKey] = config($configKey);
        }
        if ($forcedConfig) {
            config($forcedConfig);
        }
    } // end onBefore
    
    private function onAfter()
    {
        if ($this->cachedConfig) {
            config($this->cachedConfig);
        }
    } // end onAfter
    
    public function isSkipException($exceptionClass)
    {
        return in_array($exceptionClass, $this->config['except']);
    } // end isSkipException

    private function getExceptionData($exception)
    {
        $data = [];

        $data['host'] = Request::server('HTTP_HOST');
        $data['method'] = Request::method();
        $data['fullUrl'] = Request::fullUrl();
        if (php_sapi_name() === 'cli') {
            $data['host'] = parse_url(config('app.url'), PHP_URL_HOST);
            $data['method'] = 'CLI';
        }
        $data['exception'] = $exception->getMessage();
        $data['error'] = $exception->getTraceAsString();
        $data['line'] = $exception->getLine();
        $data['file'] = $exception->getFile();
        $data['class'] = get_class($exception);
        $data['storage'] = array(
            'SERVER'  => Request::server(),
            'GET'     => Request::query(),
            'POST'    => $_POST,
            'FILE'    => Request::file(),
            'OLD'     => Request::hasSession() ? Request::old() : [],
            'COOKIE'  => Request::cookie(),
            'SESSION' => Request::hasSession() ? Session::all() : [],
            'HEADERS' => Request::header(),
        );

        // Remove empty, false and null values
        $data['storage'] = array_filter($data['storage']);

        // Censor sensitive field values
        array_walk_recursive($data['storage'], self::censorSensitiveFields(...));

        $count = $this->config['count'];
        
        $data['exegutor']   = [];
        $data['file_lines'] = [];
        
        $file = new SplFileObject($data['file']);
        for ($i = -1 * abs($count); $i <= abs($count); $i++) {
            list($line, $exegutorLine) = $this->getLineInfo($file, $data['line'], $i);
            if (!$line && !$exegutorLine) {
                continue;
            }
            $data['exegutor'][] = $exegutorLine;
            $data['file_lines'][$data['line'] + $i] = $line;
        }

        // to make Symfony exception more readable
        if ($data['class'] == 'Symfony\Component\Debug\Exception\FatalErrorException') {
            preg_match("~^(.+)' in ~", $data['exception'], $matches);
            if (isset($matches[1])) {
                $data['exception'] = $matches[1];
            }
        }

        return $data;
    } // end getExceptionData

    /**
     * Set the value of specified fields to *****
     *
     * @param string $value
     * @param string $key
     * @return void
     */
    public function censorSensitiveFields(&$value, $key)
    {
        if (in_array($key, $this->config['censored_fields'], true)) {
            $value = '*****';
        }
    }

    /**
     * @param SplFileObject $file
     */
    private function getLineInfo($file, $line, $i)
    {
        $currentLine = $line + $i;
        // cuz array starts with 0, when file lines start count from 1
        $index = $currentLine - 1;
        if ($index < 0) {
            return [false, false];
        }
        $file->seek($index);
        
        if ($file->eof()) {
            return [false, false];
        }

        return [
            $file->current(),
            [
                'line' => '<span style="color:#aaaaaa;">' . $currentLine . '.</span> ' . SyntaxHighlight::process($file->current()),
                'wrap_left' => $i ? '' : '<span style="color: #F5F5F5; background-color: #5A3E3E; width: 100%; display: block;">',
                'wrap_right' => $i ? '' : '</span>',
            ]
        ];
    } // end getLineInfo
}
