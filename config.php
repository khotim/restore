<?php

use yii\web\Response;

$db = require(__DIR__ . '/db.php');

return [
    'id' => 'restore',
    // the basePath of the application will be the app directory
    'basePath' => __DIR__,
    // this is where the application will find all controllers
    'controllerNamespace' => 'restore\controllers',
    // local time zone setting
    'timeZone' => 'Asia/Jakarta',
    // set an alias to enable autoloading of classes from the 'restore' namespace
    'aliases' => [
        '@restore' => __DIR__,
        '@api' => __DIR__ .'/apis'
    ],
    'modules' => [
        'v1' => ['class' => 'api\v1\Module']
    ],
    'components' => [
        'db' => $db,
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
                if ($response->data !== null) {
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
                '/v1/accesstoken' => 'v1/site/accesstoken',
                '/v1/authorize' => 'v1/site/authorize',
                '/v1/logout' => 'v1/site/logout',
                '/v1/profile' => 'v1/site/profile',
                '/v1/register' => 'v1/site/register',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'v1/admin/coupon',
                        'v1/admin/logistic',
                        'v1/admin/product'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/catalogue',
                    'patterns' => [
                        'GET' => 'index',
                        'PUT {id}' => 'create',
                        'GET {id}' => 'view',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/checkout'],
                    'patterns' => [
                        'GET' => 'index',
                        'POST' => 'create',
                        'GET payments' => 'payment',
                        'POST payments/{id}' => 'payment-create',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/admin/order',
                    'patterns' => [
                        'GET' => 'index',
                        'GET {id}' => 'view',
                        'PUT cancel/{id}' => 'cancel',
                        'PUT close/{id}' => 'close',
                        'POST shipment' => 'shipment'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/customer',
                    'patterns' => [
                        'GET' => 'index',
                        'POST authorize' => 'authorize',
                        'POST accesstoken' => 'accesstoken',
                        'GET orders' => 'order',
                        'GET orders/{id}' => 'order-detail'
                    ]
                ],
            ]
        ]
    ],
    'params' => ['uploadDir' => __DIR__ . '/web/uploads']
];
