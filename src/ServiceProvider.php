<?php 

namespace Yaro\LogEnvelope;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    protected $defer = false;

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/log-envelope.php' => config_path('yaro.log-envelope.php'),
        ]);
        
        $this->app['view']->addNamespace('log-envelope', __DIR__ . '/../resources/views');
        
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('LogEnvelope', 'Yaro\LogEnvelope\Facade');
    } // end boot
    
    public function register()
    {
        config([
            'config/yaro.log-envelope.php',
        ]);
        
        $this->app['yaro.log-envelope'] = $this->app->share(function($app) {
            return new LogEnvelope();
        });
    } // end register
    
}
