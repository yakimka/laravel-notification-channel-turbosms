<?php

namespace NotificationChannels\TurboSms;

use Illuminate\Support\ServiceProvider;

class TurboSmsServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(TurboSmsApi::class, function () {
            $config = config('services.turbosms');

            return new TurboSmsApi($config['login'], $config['secret'],
              $config['sender'], $config['url']);
        });
    }
}
