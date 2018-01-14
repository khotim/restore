<?php
namespace restore\controllers;

use Yii;
use yii\rest\Controller;

class SiteController extends Controller
{
    /**
     * Landing page.
     */
    public function actionIndex()
    {
        return "RESTful API service for order transaction";
    }
    
    /**
     * Using yii\web\ErrorAction to send error data.
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
}
