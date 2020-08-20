<?php

namespace CampaigningBureau\CfRepositoryGenerator\Commands;

use CampaigningBureau\CfRepositoryGenerator\Services\ContentfulService;
use Contentful\Delivery\Resource\ContentType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class MakeCfRepositoryCommand
 * @package CampaigningBureau\CfRepositoryGenerator\Commands
 */
class MakeCfRepositoryCommand extends BaseCfRepositoryCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:cf-repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create contract, repositories, factory and model for a contentful content type';
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
        'fake-data'          => __DIR__ . '/../stubs/fake-data',
    ];
    /**
     * @var string
     */
    private $modelName;
    /**
     * @var string
     */
    private $contentTypeName;
    /**
     * @var string
     */
    private $contentTypeId;

    /**
     * @var Collection
     */
    private $contentfulFields;

    /**
     * Execute the console command.
     *
     * @param ContentfulService $contentful
     */
    public function handle(ContentfulService $contentful)
    {
        parent::handle($contentful);

        // select content type
        $this->selectContentType();

        // let the user choose a model name
        $this->selectModelName();

        $this->contentfulFields = $this->contentful->getFieldsForId($this->contentTypeId);

        $this->createContract();

        $this->createContentfulRepository();

        $this->createFactory();

        $this->createModel();

        $this->createCachingRepository();

        $this->createFakeData();

        $this->info('+++');
        $this->info('Creation completed. To use the repository, add the following lines to the register() method of your app service provider:');
        $this->info('+++');
        $this->line('$this->app->singleton(' . $this->modelName . 'Repository::class, function ()
{
    return new Caching' . $this->modelName . 'Repository(new Contentful' . $this->modelName . 'Repository(), $this->app[\'cache.store\']);
});');
        $this->info('+++');
        $this->info('If you prefer using fake data for development use the following lines instead:');
        $this->info('+++');
        $this->line('$this->app->singleton(' . $this->modelName . 'Repository::class, function ()
{
    return new Caching' . $this->modelName . 'Repository(new Fake' . $this->modelName . 'Repository(), $this->app[\'cache.store\']);
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
            '%namespaces.models%'    => $this->calculateNamespaceFromPath($this->config('paths.models')),
            '%modelName%'            => $this->modelName,
        ];

        $this->replacePlaceholdersAndPersistFile($replacements, $content, $this->config('paths.contracts'),
            $this->modelName . 'Repository', 'contract');
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
            '%apiModelName%'            => $this->contentTypeId,
        ];

        $this->replacePlaceholdersAndPersistFile($replacements, $content, $this->config('paths.repositories'),
            'Contentful' . $this->modelName . 'Repository', 'repository');
    }

    private function createFactory()
    {
        $content = $this->fileManager->get($this->stubs['factory']);

        $replacements = [
            '%modelName%'            => $this->modelName,
            '%namespaces.factories%' => $this->calculateNamespaceFromPath($this->config('paths.factories')),
            '%namespaces.models%'    => $this->calculateNamespaceFromPath($this->config('paths.models')),
            '%modelGetterList%'      => $this->contentful->getModelGetterList($this->contentfulFields),
        ];

        $this->replacePlaceholdersAndPersistFile($replacements, $content, $this->config('paths.factories'),
            $this->modelName . 'Factory', 'factory');
    }

    private function createModel()
    {
        $content = $this->fileManager->get($this->stubs['model']);

        $replacements = [
            '%modelName%'                 => $this->modelName,
            '%namespaces.models%'         => $this->calculateNamespaceFromPath($this->config('paths.models')),
            '%instanceVariables%'         => $this->contentful->getInstanceVariables($this->contentfulFields),
            '%constructorArgumentList%'   => $this->contentful->getConstructorArgumentList($this->contentfulFields),
            '%constructorArgumentDoc%'    => $this->contentful->getConstructorArgumentDoc($this->contentfulFields),
            '%constructorInitialization%' => $this->contentful->getConstructorInitialization($this->contentfulFields),
            '%methodList%'                => $this->contentful->getMethodList($this->contentfulFields),
        ];

        $this->replacePlaceholdersAndPersistFile($replacements, $content, $this->config('paths.models'),
            $this->modelName, 'model');
    }

    /**
     * create the caching repository
     */
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
            '%cacheKey%'                        => Str::snake(Str::plural($this->modelName)),
        ];

        $this->replacePlaceholdersAndPersistFile($replacements, $content, $this->config('paths.caching-repositories'),
            'Caching' . $this->modelName . 'Repository', 'caching repository');
    }

    /**
     * let the user select the content type from contentful
     */
    private function selectContentType()
    {
        $contentTypes = $this->contentful->getAvailableContentTypes();

        // if no content types are found, a dummy content type may be created
        if ($contentTypes->count() === 0) {
            $this->manuallyDefineContentType();

            return;
        }

        $this->contentTypeName = $this->choice('The following content types were found in the configured contentful space. Please select a content type for the generation: ',
            $contentTypes->map(function (ContentType $contentType)
            {
                return $contentType->getName();
            })
                         ->toArray());

        // after selection, get the id for this content type name
        $this->contentTypeId = $this->contentful->getIdByName($this->contentTypeName);
    }

    /**
     * let the user specify a model name
     */
    private function selectModelName()
    {
        $this->modelName = $this->ask('Please specify a model name. This value will be used for naming the created classes.',
            Str::studly($this->contentTypeName));

        // convert the model name to studly case. removes blanks and ensures the first letter is uppercased
        $this->modelName = Str::studly($this->modelName);
    }

    /**
     * if no content types were loaded from contentful, the user can manually define a content type name.
     * the content type id is generated of the selected name.
     */
    private function manuallyDefineContentType()
    {
        $this->contentTypeName = $this->ask('No content types could be loaded from the configured contentful space. A stubbed version will be created. Please enter a name');

        $this->contentTypeId = Str::camel($this->contentTypeName);
    }

    /**
     * perform replacement or all relevant placeholders and save the generated file to the hdd
     *
     * @param array  $replacements
     * @param string $content
     * @param string $relativePath
     * @param string $fileName
     * @param string $type
     * @param string $extension
     */
    private function replacePlaceholdersAndPersistFile(
        $replacements,
        $content,
        $relativePath,
        $fileName,
        $type,
        $extension = 'php'
    ) {
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileDirectory = app()->basePath() . '/app/' . $relativePath;
        $filePath = $fileDirectory . $fileName . '.' . $extension;

        $this->putFileToHdd($fileDirectory, $filePath, $fileName, $content, $type);
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
     * create fake data
     */
    private function createFakeData()
    {
        $replacements = [
            '%modelName%'               => $this->modelName,
            '%namespaces.repositories%' => $this->calculateNamespaceFromPath($this->config('paths.repositories')),
            '%namespaces.models%'       => $this->calculateNamespaceFromPath($this->config('paths.models')),
            '%namespaces.fake-data%'    => $this->calculateNamespaceFromPath($this->config('paths.fake-data')),
            '%fakerArgumentList%'       => $this->contentful->getFakerArgumentList($this->contentfulFields),
        ];

        $this->replacePlaceholdersAndPersistFile($replacements,
            $this->fileManager->get($this->stubs['fake-data'] . '/fake-asset.stub'), $this->config('paths.fake-data'),
            'FakeAsset', 'Fake File');
        $this->replacePlaceholdersAndPersistFile($replacements,
            $this->fileManager->get($this->stubs['fake-data'] . '/fake-image-file.stub'),
            $this->config('paths.fake-data'), 'FakeImageFile', 'Fake File');
        $this->replacePlaceholdersAndPersistFile($replacements,
            $this->fileManager->get($this->stubs['fake-data'] . '/fake-repository.stub'),
            $this->config('paths.fake-data'), 'Fake' . $this->modelName . 'Repository', 'Fake File');

        $this->replacePlaceholdersAndPersistFile($replacements,
            $this->fileManager->get($this->stubs['fake-data'] . '/info.md'),
            $this->config('paths.fake-data'), 'info', 'Info File', 'md');
    }
}