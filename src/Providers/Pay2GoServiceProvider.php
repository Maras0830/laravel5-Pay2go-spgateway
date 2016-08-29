<?php
namespace Maras0830\Pay2Go\Providers;
use Illuminate\Support\ServiceProvider;

class Pay2GoServiceProvider extends ServiceProvider  {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
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