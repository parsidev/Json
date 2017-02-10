<?php namespace Parsidev\Json;

use Illuminate\Support\ServiceProvider;

class JsonServiceProvider extends ServiceProvider
{

    protected $defer = false;

    public function register()
    {
        $this->app->singleton(JsonWrapper::class, function ($app) {
            return $encoder = new JsonWrapper;
        });
    }

    public function provides()
    {
        return ['parsidev-json'];
    }

}
