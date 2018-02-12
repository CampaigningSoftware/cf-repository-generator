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
];
