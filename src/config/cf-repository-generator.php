<?php

return [
    /**
     * This config defines the locations for the generated files inside the app directory.
     * Please append trailing slashes to the paths.
     * The namespaces of the created files will be set according to these paths (PSR-4).
     */
    'paths' => [
        'contracts'            => 'Repositories/Contracts/',
        'repositories'         => 'Repositories/',
        'caching-repositories' => 'Repositories/Caching/',
        'models'               => 'Models/',
        'factories'            => 'Factories/',
    ],

    /**
     * the id for the contentful space that needs to be accessed
     */
    'contentful_delivery_space' => env('CONTENTFUL_DELIVERY_SPACE', ''),
    /**
     * an api key for accessing the delivery space
     */
    'contentful_delivery_token' => env('CONTENTFUL_DELIVERY_TOKEN', ''),

    /*
     * The ID of the environment you want to access.
     */
    'contentful_environment'    => env('CONTENTFUL_ENVIRONMENT_ID', 'master'),
];
