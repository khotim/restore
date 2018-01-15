<?php

$db = require(__DIR__ . '/db.php');

return [
    'id' => 'restore-console',
    'basePath' => __DIR__,
    'controllerNamespace' => 'restore\controllers',
    'aliases' => [
        '@restore' => __DIR__,
        '@api' => __DIR__ .'/apis'
    ],
    'components' => [
        'db' => $db
    ],
];