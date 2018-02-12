# Contentful Repository Generator for Laravel 5.5

Laravel 5.5 Contentful Repository generator.

Generates contract, contentful repository, model, factory and caching repository.

If one of these files does already exist, it can be kept or overwritten.

## Usage

### Step 1: Install Through Composer

```
composer require campaigningbureau/cf-repository-generator
```

### Step 2: Register the Service Provider

Add the service provider to `config/app.php`.

```php
	/*
	 * Package Service Providers...
	 */
	CampaigningBureau\CfRepositoryGenerator\CfRepositoryGeneratorServiceProvider::class,
```

### Step 3: Publish and edit the config file

```bash
$ php artisan vendor:publish --provider="CampaigningBureau\CfRepositoryGenerator\CfRepositoryGeneratorServiceProvider"
```

### Step 4: Create repositories

`php artisan make:cf-repository {model} {--apiModelName=}`

The `apiModelname` specifies the name of the content type in contentful. If this name is not set, the lowercased model name is used.


## Configuration

The published config file `config/cf-repository-generator.php` contains the paths for the generated files inside the `app` directory. 
To be conform to PSR-4 autoloading, the namespaces of the files are generated out of these paths.

These are the default paths, that will be used, if the config file isn't published, or if the config keys don't exist: 

```php
<?php

return [
    'paths' => [
        'contracts'    => 'Repositories/Contracts/',
        'repositories' => 'Repositories/',
        'caching-repositories' => 'Repositories/Caching/',
        'models' => 'Models/',
        'factories' => 'Factories/',
    ],
];
```