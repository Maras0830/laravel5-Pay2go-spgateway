<?php
namespace Maras0830\Pay2Go\Providers;
use Illuminate\Support\ServiceProvider;
use Maras0830\Pay2Go\Pay2Go;

class Pay2GoServiceProvider extends ServiceProvider  {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Pay2Go::class, function($app) {
            $config = $app['config']['pay2go'];

            return new Pay2Go($config['MerchantID'], $config['HashKey'], $config['HashIV']);
        });

        $this->app->alias(Pay2Go::class, 'pay2go');
    }

    /**
     *  Boot
     */
    public function boot()
    {
        $this->addConfig();
    }

    /**
     *  Config publishing
     */
    private function addConfig()
    {
        $this->publishes([
            __DIR__.'/../../config/pay2go.php' => config_path('pay2go.php')
        ]);
    }
}
