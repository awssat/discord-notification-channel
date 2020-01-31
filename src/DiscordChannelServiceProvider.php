<?php

namespace Awssat\Notifications;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class DiscordChannelServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('discord', function ($app) {
                return new Channels\DiscordWebhookChannel($app->make(HttpClient::class));
            });
        });
    }
}