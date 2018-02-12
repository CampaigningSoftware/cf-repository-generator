<?php

namespace CampaigningBureau\CfRepositoryGenerator\Commands;

use Illuminate\Console\Command;

class MakeCfRepositoryCommand extends BaseCfRepositoryCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:cf-repository {model} {--apiModelName= : defines the content model name in the cf api. if this is not specified, the lowercased model is used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create contract, repositories, factory and model';
    /**
     * paths to the stubs
     *
     * @var array
     */
    protected $stubs = [
        'contract'           => __DIR__ . '/../stubs/contract.stub',
        'repository'         => __DIR__ . '/../stubs/repository.stub',
        'factory'            => __DIR__ . '/../stubs/factory.stub',
        'model'              => __DIR__ . '/../stubs/model.stub',
        'caching-repository' => __DIR__ . '/../stubs/caching-repository.stub',
    ];
    /**
     * @var string
     */
    private $modelName;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $this->modelName = $this->argument('model');

        $this->createContract();

        $this->createContentfulRepository();

        $this->createFactory();

        $this->createModel();

        $this->createCachingRepository();

        $this->info('+++');
        $this->info('Creation completed. To use the repository, add the following lines to the register() method of your app service provider:');
        $this->info('+++');
        $this->line('$this->app->singleton(' . $this->modelName . 'Repository::class, function ()
{
    return new Caching' . $this->modelName . 'Repository(new Contentful' . $this->modelName . 'Repository(), $this->app[\'cache.store\']);
});');
        $this->info('+++');
        $this->info('Now the repository is ready to be injected in your constructors.');
    }

    /**
     * Create a new contract
     */
    private function createContract()
    {
        // load the stub file
        $content = $this->fileManager->get($this->stubs['contract']);

        $replacements = [
            '%namespaces.contracts%' => $this->calculateNamespaceFromPath($this->config('paths.contracts')),
            '%modelName%'            => $this->modelName,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName = $this->modelName . 'Repository';
        $fileDirectory = app()->basePath() . '/app/' . $this->config('paths.contracts');
        $filePath = $fileDirectory . $fileName . '.php';

        $this->putFileToHdd($fileDirectory, $filePath, $fileName, $content, 'contract');
    }

    /**
     * save the given content to the hdd.
     * if the file does already exist, print a confirmation message to overwrite it.
     *
     * @param string $fileDirectory
     * @param string $filePath
     * @param string $fileName
     * @param string $content
     * @param string $type
     */
    protected function putFileToHdd($fileDirectory, $filePath, $fileName, $content, $type)
    {
        // Check if the directory exists, if not create...
        if (!$this->fileManager->exists($fileDirectory)) {
            $this->fileManager->makeDirectory($fileDirectory, 0755, true);
        }

        if ($this->fileManager->exists($filePath)) {
            if (!$this->confirm("The $type [{$fileName}] already exists. Do you want to overwrite it?")) {
                $this->line("The $type [{$fileName}] will not be overwritten.");

                return;
            }
        }

        $this->line("The $type [{$fileName}] has been created.");

        $this->fileManager->put($filePath, $content);
    }

    /**
     * Create a new repository
     */
    protected function createContentfulRepository()
    {
        $content = $this->fileManager->get($this->stubs['repository']);

        $replacements = [
            '%modelName%'               => $this->modelName,
            '%namespaces.repositories%' => $this->calculateNamespaceFromPath($this->config('paths.repositories')),
            '%namespaces.factories%'    => $this->calculateNamespaceFromPath($this->config('paths.factories')),
            '%namespaces.models%'       => $this->calculateNamespaceFromPath($this->config('paths.models')),
            '%namespaces.contracts%'    => $this->calculateNamespaceFromPath($this->config('paths.contracts')),
            '%apiModelName%'            => $this->option('apiModelName') ? $this->option('apiModelName') : snake_case($this->modelName),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName = 'Contentful' . $this->modelName . 'Repository';
        $fileDirectory = app()->basePath() . '/app/' . $this->config('paths.repositories');
        $filePath = $fileDirectory . $fileName . '.php';

        $this->putFileToHdd($fileDirectory, $filePath, $fileName, $content, 'repository');
    }

    private function createFactory()
    {
        $content = $this->fileManager->get($this->stubs['factory']);

        $replacements = [
            '%modelName%'            => $this->modelName,
            '%namespaces.factories%' => $this->calculateNamespaceFromPath($this->config('paths.factories')),
            '%namespaces.models%'    => $this->calculateNamespaceFromPath($this->config('paths.models')),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName = $this->modelName . 'Factory';
        $fileDirectory = app()->basePath() . '/app/' . $this->config('paths.factories');
        $filePath = $fileDirectory . $fileName . '.php';

        $this->putFileToHdd($fileDirectory, $filePath, $fileName, $content, 'factory');
    }

    private function createModel()
    {
        $content = $this->fileManager->get($this->stubs['model']);

        $replacements = [
            '%modelName%'         => $this->modelName,
            '%namespaces.models%' => $this->calculateNamespaceFromPath($this->config('paths.models')),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName = $this->modelName;
        $fileDirectory = app()->basePath() . '/app/' . $this->config('paths.models');
        $filePath = $fileDirectory . $fileName . '.php';

        $this->putFileToHdd($fileDirectory, $filePath, $fileName, $content, 'model');
    }

    private function createCachingRepository()
    {
        $content = $this->fileManager->get($this->stubs['caching-repository']);

        $replacements = [
            '%modelName%'                       => $this->modelName,
            '%namespaces.repositories%'         => $this->calculateNamespaceFromPath($this->config('paths.repositories')),
            '%namespaces.factories%'            => $this->calculateNamespaceFromPath($this->config('paths.factories')),
            '%namespaces.models%'               => $this->calculateNamespaceFromPath($this->config('paths.models')),
            '%namespaces.contracts%'            => $this->calculateNamespaceFromPath($this->config('paths.contracts')),
            '%namespaces.caching-repositories%' => $this->calculateNamespaceFromPath($this->config('paths.caching-repositories')),
            '%cacheKey%'                        => snake_case(str_plural($this->modelName)),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName = 'Caching' . $this->modelName . 'Repository';
        $fileDirectory = app()->basePath() . '/app/' . $this->config('paths.caching-repositories');
        $filePath = $fileDirectory . $fileName . '.php';

        $this->putFileToHdd($fileDirectory, $filePath, $fileName, $content, 'caching repository');
    }
}