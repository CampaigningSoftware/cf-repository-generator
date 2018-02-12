<?php

namespace StefanSchindler\CfRepositoryGenerator\Commands;

use Illuminate\Console\Command;

class BaseCfRepositoryCommand extends Command
{
    /**
     * File manager.
     *
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $fileManager;

    /**
     * Application namespace
     *
     * @var string
     */
    protected $appNamespace;

    protected $defaultPaths = [
        'contracts'            => 'Repositories/Contracts/',
        'repositories'         => 'Repositories/',
        'caching-repositories' => 'Repositories/Caching/',
        'models'               => 'Models/',
        'factories'            => 'Factories/',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->fileManager = app('files');
        $this->appNamespace = app()->getNamespace();
    }

    /**
     * get a configuration value from the package config file.
     * if the config value is not found, the default path is used.
     *
     * @param  string $key
     *
     * @return string
     */
    protected function config($key)
    {
        return config('cf-repository-generator.' . $key, $this->defaultPaths[substr($key, strrpos($key, '.') + 1)]);
    }


    /**
     * calculate the namespace from a given path.
     * removes trailing slashes, replaces slashes with backslashes, prepends app namespace
     *
     * @param string $path
     *
     * @return string
     */
    protected function calculateNamespaceFromPath($path): string
    {
        return $this->appNamespace . str_replace('/', '\\', rtrim($path, '/'));
    }
}