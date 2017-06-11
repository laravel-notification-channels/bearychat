<?php

namespace NotificationChannels\BearyChat;

use Illuminate\Support\ServiceProvider;

class BearyChatServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->register(\ElfSundae\BearyChat\Laravel\ServiceProvider::class);
    }
}
