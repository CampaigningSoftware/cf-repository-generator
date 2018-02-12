<?php
/**
 * Created by PhpStorm.
 * User: stefanschindler
 * Date: 06.02.18
 * Time: 16:50
 */

namespace StefanSchindler\CfRepositoryGenerator;

use MScharl\LaravelStaticImageCache\Provider\LaravelStaticImageCacheProvider;
use StefanSchindler\CfRepositoryGenerator\Commands\MakeCfRepositoryCommand;
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