# Contentful Repository Generator for Laravel 11

Laravel 11 Contentful Repository generator.

Generates contract, contentful repository, model, factory and caching repository with fields that are defined in the Contentful model. (https://www.contentful.com/)

If one of these files does already exist, it can be kept or overwritten.

## Usage

### Step 1: Install Through Composer

```
composer require campaigningsoftware/cf-repository-generator
```

#### Supported versions

| Package version | Required Laravel version | Minimum PHP version |
|-----------------|--------------------------|---------------------|
| ^11.0           | 11.x                     | ^8.1                |

### Step 2: Publish and edit the config file

```bash
$ php artisan vendor:publish --provider="CampaigningSoftware\CfRepositoryGenerator\CfRepositoryGeneratorServiceProvider"
```

### Step 3: Create repositories

`php artisan make:cf-repository`

This command will load all content types that are defined in the configured contentful space and provide an easy way to generate the relevant classes. 

The generated files also contain fake repositories, that can be used instead of the actual contentful data (for instance, if no data is available during development). 
The whole directory (`FakeData` by default) can be removed, if it isn't used.


## Configuration

The published config file `config/cf-repository-generator.php` contains the paths for the generated files inside the `app` directory. 
To be conform to PSR-4 autoloading, the namespaces of the files are generated out of these paths.

These are the default paths, that will be used, if the config file isn't published, or if the config keys don't exist: 

```php
return [
    'paths' => [
        'contracts'            => 'Repositories/Contracts/',
        'repositories'         => 'Repositories/',
        'caching-repositories' => 'Repositories/Caching/',
        'models'               => 'Models/',
        'factories'            => 'Factories/',
        'fake-data'            => 'FakeData/',
    ],
];
```

- The `contentful_delivery_space` and `contentful_delivery_token` fields need to be set with the API Key and Space ID retrieved from Contentful.  
- By default they are set with the .env variables `CONTENTFUL_DELIVERY_SPACE` and `CONTENTFUL_DELIVERY_TOKEN`.
- `CONTENTFUL_ENVIRONMENT_ID` defines the Contentful environment to use (defaults to `master`)
