<?php
namespace api\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use api\v1\models\Customer;
use api\v1\models\Order;

class CustomerController extends Controller
{
    public $customer_id = 0; // customer id;
    
    /**
     * Overrides parent implementation.
     */
    public function behaviors()
    {
        
        $behaviors = parent::behaviors();
        
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'authorize' => ['POST'],
                'accesstoken' => ['POST'],
                'index' => ['GET'],
                'order' => ['GET'],
                'order-detail' => ['GET'],
            ]
        ];
        
        return $behaviors;
    }
    
    /**
     * Performs accesstoken check before executing action
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        
        if ($action->id == 'authorize' || $action->id == 'accesstoken') {
            return true;
        }
        
        $request = Yii::$app->getRequest();
        $authHeader = $request->getHeaders()->get('Authorization');
        // Find access token using HttpBearer method
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $token = $matches[1];
        }
        // Find access token from query parameter if no token from HttpBearer
        if (!isset($token)) {
            $token = $request->getQueryParam('access_token');
        }
        
        $customer = Customer::find()->where(['token_code' => $token])->andWhere(['>', 'token_expired', time()])->one();
        
        if ($customer !== null) {
            $this->customer_id = $customer->id;
            
            return true;
        }
        
        throw new UnauthorizedHttpException('Unauthorized access!');
    }
    
    /**
     * Authorizes a customer using email.
     */
    public function actionAuthorize()
    {
        $model = Customer::findOne(['email' => Yii::$app->getRequest()->getBodyParam('email')]);
        
        if ($model !== null) {
            $model->auth_code = md5(uniqid());
            $model->auth_expired = time() + (60 * 5); // 5 minutes
            $model->save(false);
            
            $data = [
                'authorization_code' => $model->auth_code,
                'expired_at' => $model->authExpired
            ];
            
            return $data;
        }
        
        throw new NotFoundHttpException('{email} : Email address is not available!');
    }
    
    /**
     * Obtain a new access token for customer.
     */
    public function actionAccesstoken()
    {
        $authParam = Yii::$app->getRequest()->getBodyParam('auth');
        
        if (!$authParam) {
            throw new NotFoundHttpException("There's no authorization code provided.");
        }
        
        $model = Customer::find()->where(['auth_code' => $authParam])->andWhere(['>', 'auth_expired', time()])->one();
        
        if ($model === null) {
            throw new NotFoundHttpException("Invalid authorization code.");
        }
        
        $model->token_code = md5(uniqid());
        $model->token_expired = time() + (60 * 60 * 24 * 60); // 60 days
        $model->save(false);
        
        $data = [
            'access_token' => $model->token_code,
            'expired_at' => $model->tokenExpired
        ];
        
        return $data;
    }
    
    /**
     * Shows customer detail.
     */
    public function actionIndex()
    {
        if ($customer = Customer::findOne($this->customer_id)) {
            return $customer;
        }
        
        throw new NotFoundHttpException('Customer is not available.');
    }
    
    /**
     * Shows list of customer orders.
     */
    public function actionOrder()
    {
        $request = Yii::$app->getRequest();
        $requestParams = $request->getBodyParams();
        
        if (empty($requestParams)) {
            $requestParams = $request->getQueryParams();
        }
        
        $query = Order::find()
            ->where(['customer_id' => $this->customer_id])
            ->andWhere(['<>', 'status_id', Order::STATUS_DRAFT])
            // filter based on date range
            ->andFilterWhere([
                'between',
                'ordered_at',
                $request->getBodyParam('order_from'),
                $request->getBodyParam('order_to')
            ]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['params' => $requestParams],
            'sort' => ['params' => $requestParams],
        ]);
        
        return $dataProvider;
    }
    
    /**
     * Shows a single order detail.
     */
    public function actionOrderDetail($id)
    {
        $order = Order::find()
            ->where(['id' => $id, 'customer_id' => $this->customer_id])
            ->andWhere(['<>', 'status_id', Order::STATUS_DRAFT])
            ->one();
        
        return $order ?: new NotFoundHttpException("Order transaction ({$id}) is not available.");
    }
}
