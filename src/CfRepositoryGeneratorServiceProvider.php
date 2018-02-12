<?php

namespace CampaigningBureau\CfRepositoryGenerator;

use MScharl\LaravelStaticImageCache\Provider\LaravelStaticImageCacheProvider;
use CampaigningBureau\CfRepositoryGenerator\Commands\MakeCfRepositoryCommand;
use Illuminate\Support\ServiceProvider;

class CfRepositoryGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // publish config
        $this->publishes([
            __DIR__ . '/config/cf-repository-generator.php' => config_path('cf-repository-generator.php'),
        ], 'config');

        $this->app->register(LaravelStaticImageCacheProvider::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCfRepositoryCommand::class
            ]);
        }

    }
}