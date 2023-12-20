<?php

use davidhirtz\yii2\config\Bootstrap;
use davidhirtz\yii2\config\modules\admin\Module;
use yii\web\Session;

return [
    'id' => 'yii2-config',
    'aliases' => [
        // This is a fix for the broken aliasing of `BaseMigrateController::getNamespacePath()`
        '@davidhirtz/yii2/config' => __DIR__ . '/../../src/',
    ],
    'bootstrap' => [
        Bootstrap::class,
    ],
    'components' => [
        'assetManager' => [
            'linkAssets' => true,
        ],
        'db' => [
            'dsn' => getenv('MYSQL_DSN') ?: 'mysql:host=127.0.0.1;dbname=yii2_config_test',
            'username' => getenv('MYSQL_USER') ?: 'root',
            'password' => getenv('MYSQL_PASSWORD') ?: '',
            'charset' => 'utf8',
            ...is_file(__DIR__ . '/db.php') ? require(__DIR__ . '/db.php') : [],
        ],
        'mailer' => [
            'useFileTransport' => true,
        ],
        'session' => [
            'class' => Session::class,
        ],
    ],
    'modules' => [
        'admin' => [
            'modules' => [
                'config' => [
                    'class' => Module::class,
                    'configFile' => '@runtime/config/test.php',
                ],
            ],
        ],
    ],
    'params' => [
        'cookieValidationKey' => 'test',
    ],
];
