<?php

namespace CampaigningSoftware\CfRepositoryGenerator;

use CampaigningSoftware\CfRepositoryGenerator\Commands\MakeCfRepositoryCommand;
use Contentful\Laravel\ContentfulServiceProvider;
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

        $this->app->register(ContentfulServiceProvider::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCfRepositoryCommand::class,
            ]);
        }

    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/cf-repository-generator.php', 'cf-repository-generator');
    }
}