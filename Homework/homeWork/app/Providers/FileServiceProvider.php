<?php

namespace App\Providers;

use App\Services\FileService;
use App\Services\FileServiceDynamicDecorator;
use Illuminate\Support\ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FileService::class, function ($app) {
            $originalService = $app->make(FileService::class);
            return new FileServiceDynamicDecorator($originalService);
        });
    }
}
