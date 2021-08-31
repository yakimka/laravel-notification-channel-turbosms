<?php

namespace NotificationChannels\TurboSms;

use Illuminate\Support\ServiceProvider;
use SoapClient;

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
            
            $client = new SoapClient($config['url']);
            return new TurboSmsApi($config['login'], $config['secret'],
              $config['sender'], $client);
        });
    }
}
