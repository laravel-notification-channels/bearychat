<?php

namespace NotificationChannels\BearyChat;

use Illuminate\Support\ServiceProvider;
use ElfSundae\BearyChat\Laravel\ClientManager;

class BearyChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(BearyChatChannel::class)
            ->needs(ClientManager::class)
            ->give('bearychat');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\ElfSundae\BearyChat\Laravel\ServiceProvider::class);
    }
}
