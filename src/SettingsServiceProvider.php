<?php

namespace Pigzzz\Settings;

use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(Settings $extension)
    {
        if (! Settings::boot()) {
            return ;
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations')
            ], 'migrations');
        }

        $this->app->booted(function () {
            Settings::routes(__DIR__.'/../routes/web.php');
        });
    }
}