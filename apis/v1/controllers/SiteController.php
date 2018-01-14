<?php
namespace api\v1\controllers;

use Yii;
use yii\helpers\Url;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use api\v1\models\SignupForm;
use api\v1\models\LoginForm;
use restore\models\AccessToken;
use restore\models\AuthorizationCode;

class SiteController extends Controller
{
    /**
     * Overrides parent implementation.
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'logout' => ['GET'],
                    'authorize' => ['POST'],
                    'register' => ['POST'],
                    'accesstoken' => ['POST'],
                    'profile' => ['GET'],
                ],
            ],
        ];
    }
    
    /**
     * Registers a new user.
     */
    public function actionRegister()
    {
        $model = new SignupForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        
        if ($user = $model->signup()) {
            $data = $user->attributes;
            
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $response->getHeaders()->set('Location', Url::toRoute(['profile'], true));
            
            return $user;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        
        return $model;
    }
    
    /**
     * Shows user detail.
     */
    public function actionProfile()
    {
        return Yii::$app->user->identity;
    }
    
    /**
     * Obtain a new access token.
     */
    public function actionAccesstoken()
    {
        $authParam = Yii::$app->getRequest()->getBodyParam('auth');
        
        if (!$authParam) {
            throw new NotFoundHttpException("There's no authorization code provided.");
        }
        
        $authCode = AuthorizationCode::isValid($authParam);
        
        if (!$authCode) {
            throw new NotFoundHttpException("Invalid authorization code..");
        }
        
        $model = new AccessToken();
        $model->token = md5(uniqid());
        $model->auth_code = $authCode->code;
        $model->expired_at = time() + (60 * 60 * 24 * 60); // 60 days
        $model->user_id = $authCode->user_id;
        $model->save(false);
        
        $data = [];
        $data['access_token'] = $model->token;
        $data['expired_at'] = $model->expiredAt;
        
        return $data;
    }
    
    /**
     * Authorizes a user account using email & password.
     */
    public function actionAuthorize()
    {
        $model = new LoginForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        
        if ($model->validate() && $model->login()) {
            $model = new AuthorizationCode();
            $model->code = md5(uniqid());
            $model->expired_at = time() + (60 * 5); // 5 minutes
            $model->user_id = Yii::$app->user->id;
            
            if (isset($_SERVER['HTTP_X_APPLICATION_ID'])) {
                $app_id = $_SERVER['HTTP_X_APPLICATION_ID'];
            } else {
                $app_id = null;
            }

            $model->app_id = $app_id;
            $model->save(false);
            
            $data = [];
            $data['authorization_code'] = $model->code;
            $data['expired_at'] = $model->expiredAt;
            
            return $data;
        }
        
        return $model;
    }
    
    /**
     * Log out a user account.
     */
    public function actionLogout()
    {
        $headers = Yii::$app->getRequest()->getHeaders();
        $access_token = $headers->get('x-access-token');
        
        if(!$access_token){
            $access_token = Yii::$app->getRequest()->getQueryParam('access-token');
        }
        
        $model = AccessToken::findOne(['token' => $access_token]);
        
        if ($model->delete()) {
            return "Logged out successfully.";
        }
        
        return "Invalid request.";
    }
}
