<?php
namespace api\v1\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use api\v1\models\Order;
use api\v1\models\OrderLine;
use api\v1\models\Product;

class CatalogueController extends Controller
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // remove rateLimiter which requires an authenticated user to work
        unset($behaviors['rateLimiter']);
        
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => ['GET'],
                'create' => ['PUT'],
                'view' => ['GET']
            ]
        ];
        
        return $behaviors;
    }
    
    /**
     * Finds a single product.
     */
    protected function findProduct($id)
    {
        if (($model = Product::findOne($id)) !== null && $model->quantity > 0) {
            return $model;
        }
        
        throw new NotFoundHttpException('Product is not available.');
    }
    
    /**
     * List all products with quantity > 0
     */
    public function actionIndex()
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        
        $query = Product::find()->where(['<>', 'quantity', 0]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);
        
        return $dataProvider;
    }
    
    /**
     * Adds product to cart
     * @params integer $id the id of product being added to cart.
     */
    public function actionCreate($id)
    {
        // Finds existing cart for current user based on IP and user agent.
        $model = Order::findOne([
            'ip_address' => Yii::$app->request->userIP,
            'agent' => Yii::$app->request->userAgent,
            'status_id' => Order::STATUS_DRAFT
        ]);
        
        // Cart is empty, prepare a new one.
        if ($model === null) {
            $model = new Order([
                'ip_address' => Yii::$app->request->userIP,
                'agent' => Yii::$app->request->userAgent
            ]);
        }
        
        // Finds product with quantity > 0
        $product = $this->findProduct($id);
        
        // Finds product in order lines. If it's there, increare the quantity by 1
        if ($line = $model->getLines()->where(['product_id' => $id])->one()) {
            $line->updateCounters(['quantity' => 1]);
            
            return $model;
        }
        
        // Product is available and is not in order lines. Saves new order as cart.
        $model->save();
        //Saves new order line for new product.
        $line = new OrderLine(['product_id' => $product->id, 'quantity' => 1, 'order_id' => $model->id]);
        $line->save();
        
        return $model;
    }
    
    /**
     * Shows a single product detail.
     */
    public function actionView($id)
    {
        return $this->findProduct($id);
    }
}
