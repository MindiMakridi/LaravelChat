<?php

namespace Frameworkteam\Chat;

use Illuminate\Support\ServiceProvider;
use Frameworkteam\Chat\Models\Chat;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Views' => base_path('resources/views/frameworkteam/chat'),
            __DIR__ . '/Controller' => base_path('app/Http/Controllers'),
            __DIR__ . '/js'         => base_path('public/js'),
            __DIR__ . '/config'     => base_path('config'),
            ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__ . '/routes.php';
        $this->app->make('Frameworkteam\Chat\MessageController');
    }
}
