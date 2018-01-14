<?php

use yii\web\Response;

return [
    'id' => 'restore',
    // the basePath of the application will be the app directory
    'basePath' => __DIR__,
    // this is where the application will find all controllers
    'controllerNamespace' => 'restore\controllers',
    // set an alias to enable autoloading of classes from the 'restore' namespace
    'aliases' => [
        '@restore' => __DIR__,
        '@api' => __DIR__ .'/apis'
    ],
    'modules' => [
        'v1' => ['class' => 'api\v1\Module']
    ],
    'components' => [
        
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=restore',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser'
            ]
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'format' => Response::FORMAT_JSONP,
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && Yii::$app->request->get('suppress_response_code')) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
            'formatters' => [
                Response::FORMAT_JSONP => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ]
            ]
        ],
        'user' => [
            'identityClass' => 'api\v1\models\User',
            'enableSession' => false,
            'loginUrl' => null
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                '/' => 'site/index',
            ]
        ]
    ]
];
