<?php

return [
    'local' => [
        'type' => 'Local',
        'root' => storage_path('app'),
    ],
    's3' => [
        'type' => 'AwsS3',
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'root'   => '',
        // S3-compatible services (DigitalOcean Spaces, MinIO, ...):
        // 'endpoint' => env('AWS_ENDPOINT'),
        // 'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],
    'gcs' => [
        'type' => 'Gcs',
        'project' => env('GOOGLE_CLOUD_PROJECT_ID', 'your-project-id'),
        'keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE', null),
        'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'your-bucket'),
        'root' => '',
        'prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', null),
    ],
    'rackspace' => [
        'type' => 'Rackspace',
        'username' => '',
        'key' => '',
        'container' => '',
        'zone' => '',
        'endpoint' => 'https://identity.api.rackspacecloud.com/v2.0/',
        'root' => '',
    ],
    'dropbox' => [
        'type' => 'Dropbox',
        'token' => '',
        'root' => '',
    ],
    'ftp' => [
        'type' => 'Ftp',
        'host' => '',
        'username' => '',
        'password' => '',
        'port' => 21,
        'passive' => true,
        'ssl' => true,
        'timeout' => 30,
        'root' => '',
    ],
    'sftp' => [
        'type' => 'Sftp',
        'host' => '',
        'username' => '',
        'password' => '',
        'port' => 22,
        'timeout' => 10,
        'privateKey' => '',
        'root' => '',
    ],

    // Add additional options to dump-command (like '--max-allowed-packet')
    'command-extra-params' => env('BACKUP_MANAGER_EXTRA_PARAMS'),
];
