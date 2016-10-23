<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'public_folder' => '/var/www/html/public/',

        'googleKeys' => [
            'devApiKey' => 'AIzaSyCtSe8mBjFHpgVohw_KKSgQ8EijRxNS6xA',
            'customSearchKey' => '017208545546905665118:szy-v54078u'
        ],

        'numberOfImages' => 100,
        'downloadTimeout' => 3,
        'thumbnailSize' => 300


    ],
];
