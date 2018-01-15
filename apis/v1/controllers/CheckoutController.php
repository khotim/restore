<?php
namespace api\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use api\v1\models\Order;
use api\v1\models\OrderLine;
use api\v1\models\Product;
use api\v1\models\CheckoutForm;
use api\v1\models\PaymentForm;

class CheckoutController extends Controller
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
    
    public function behaviors()
    {
        // remove rateLimiter which requires an authenticated user to work
        $behaviors = parent::behaviors();
        
        unset($behaviors['rateLimiter']);
        
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => ['GET'],
                'create' => ['POST'],
                'payment' => ['GET'],
                'payment-create' => ['POST'],
            ]
        ];
        
        return $behaviors;
    }
    
    /**
     * Shows current cart contents.
     */
    public function actionIndex()
    {
        return Order::find()->where([
            'ip_address' => Yii::$app->request->userIp,
            'agent' => Yii::$app->request->userAgent,
            'status_id' => Order::STATUS_DRAFT
        ])->one();
    }
    
    /**
     * Fill in customer information.
     */
    public function actionCreate()
    {
        $checkout = new CheckoutForm();
        $checkout->load(Yii::$app->getRequest()->getBodyParams(), '');
        
        if ($checkout->submit()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            //~ $response->getHeaders()->set('Location', Url::toRoute(['order/view', 'id' => $checkout->id], true));
        } elseif (!$checkout->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        
        return $checkout;
    }
    
    /**
     * List all available payment methods.
     */
    public function actionPayment()
    {
        $data = [];
        
        foreach (Order::getPaymentList() as $key => $value) {
            $data []= ['id' => $key, 'name' => $value];
        }
        
        return $data;
    }
    
    /**
     * Submits payment proof.
     */
    public function actionPaymentCreate($id)
    {
        $payment = new PaymentForm();
        $payment->id = $id;
        $payment->file = UploadedFile::getInstanceByName('file');
        
        if ($payment->submit()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            //~ $response->getHeaders()->set('Location', Url::toRoute(['order/view', 'id' => $checkout->id], true));
        } elseif (!$payment->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        
        return $payment;
    }
}
