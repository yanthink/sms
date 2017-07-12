<?php

namespace Yanthink\Sms;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{

    protected $defer = true;

    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/sms.php' => config_path('sms.php')], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/sms.php', 'sms');
    }

    public function register()
    {
        $this->app->singleton('sms', function ($app) {
            return new SmsManager($app);
        });

        $this->app->singleton('sms.driver', function ($app) {
            return $app['sms']->driver();
        });

        $this->app->alias('sms.driver', Contracts\Sms::class);
    }

    public function provides()
    {
        return ['sms', 'sms.driver', Contracts\Sms::class];
    }

}
