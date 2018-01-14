<?php
namespace api\v1\controllers\admin;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;

class LogisticController extends ActiveController
{
    public $modelClass = 'api\v1\models\Logistic';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // use query parameter or OAuth2 HTTP Bearer Tokens for authentication
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                ['class' => QueryParamAuth::className(), 'tokenParam' => 'access_token']
            ]
        ];
        
        return $behaviors;
    }
}
