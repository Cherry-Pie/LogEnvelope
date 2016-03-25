<?php namespace Yaro\LogEnvelope;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        /*
         * Publish configuration file
         */
        $this->publishes([
            __DIR__ . '/../config/log-envelope.php' => config_path('yaro.log-envelope.php'),
        ]);

        /*
         * Publish migration if not published yet
         */
        if (!$this->migrationHasAlreadyBeenPublished()) {
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__ . '/../resources/migrations/create_exceptions_table.php.stub' => database_path('migrations/' . $timestamp . '_create_exceptions_table.php'),
            ], 'migrations');
        }

        $this->app['view']->addNamespace('log-envelope', __DIR__ . '/../resources/views');

        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('LogEnvelope', 'Yaro\LogEnvelope\Facade');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        config([
            'config/yaro.log-envelope.php',
        ]);

        $this->app['yaro.log-envelope'] = $this->app->share(function ($app) {
            return new LogEnvelope();
        });
    }

    /**
     * @return bool
     */
    protected function migrationHasAlreadyBeenPublished()
    {
        $files = glob(database_path('/migrations/*_create_exceptions_table.php'));
        return count($files) > 0;
    }

}
