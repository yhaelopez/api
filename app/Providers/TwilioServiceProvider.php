<?php

namespace App\Providers;

use App\Twilio\PrismHttpClient;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;

class TwilioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(Client::class, function ($app) {
            $config = config('services.twilio');

            if ($config['mock']) {
                $httpClient = new PrismHttpClient($config['mock_base']);

                return new Client($config['sid'], $config['token'], null, null, $httpClient);
            }

            return new Client($config['sid'], $config['token']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
