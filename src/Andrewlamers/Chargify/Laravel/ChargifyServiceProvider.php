<?php
namespace Andrewlamers\Chargify\Laravel;
use Illuminate\Support\ServiceProvider;
use Config;
use Andrewlamers\Chargify\Chargify;

class ChargifyServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(array(
            __DIR__.'/../config/chargify.php' => config_path('chargify.php')
        ), 'config');
        $this->mergeConfigFrom(__DIR__.'/../config/chargify.php', 'chargify');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind('chargify', function () {

            $chargify = new Chargify(config('chargify'));

            return $chargify;

        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('chargify');
    }

}