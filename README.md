# Contentful Repository Generator for Laravel 7

Laravel 7 Contentful Repository generator.

Generates contract, contentful repository, model, factory and caching repository with fields that are defined in the Contentful model. (https://www.contentful.com/)

If one of these files does already exist, it can be kept or overwritten.

## Usage

### Step 1: Install Through Composer

```
composer require campaigningbureau/cf-repository-generator
```

If using Laravel 5.8, use the ^4.0 Branch:
```
composer require "campaigningbureau/cf-repository-generator: ^4.0",
```


If using Laravel >= 5.6 and < 5.8, use the ^3.0 Branch:
```
composer require "campaigningbureau/cf-repository-generator: ^3.0",
```

If using Laravel < 5.6, use the ^2.0 Branch:
```
composer require "campaigningbureau/cf-repository-generator: ^2.0",
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

`php artisan make:cf-repository`

This command will load all content types that are defined in the configured contentful space and provide an easy way to generate the relevant classes. 


## Configuration

The published config file `config/cf-repository-generator.php` contains the paths for the generated files inside the `app` directory. 
To be conform to PSR-4 autoloading, the namespaces of the files are generated out of these paths.

These are the default paths, that will be used, if the config file isn't published, or if the config keys don't exist: 

```php
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

- The `contentful_delivery_space` and `contentful_delivery_token` fields need to be set with the API Key and Space ID retrieved from Contentful.  
- By default they are set with the .env variables `CONTENTFUL_DELIVERY_SPACE` and `CONTENTFUL_DELIVERY_TOKEN`.
- `CONTENTFUL_ENVIRONMENT_ID` defines the Contentful environment to use (defaults to `master`)