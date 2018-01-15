<?php
namespace api\v1\controllers\admin;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\NotFoundHttpException;
use yii\rest\Controller;
use api\v1\models\Order;
use api\v1\models\ShipmentForm;

class OrderController extends Controller
{
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
    
    /**
     * Finds the Order model.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Order::find()
            ->where(['id' => $id])
            ->andWhere(['not in', 'status_id', [Order::STATUS_DRAFT, Order::STATUS_SUBMITTED]])
            ->one();
        if ($model !== null) {
            return $model;
        }
        
        throw new NotFoundHttpException("Order transaction ({$id}) is not available");
    }
    
    /**
     * Shows all order transactions for administration purpose.
     * @return $dataProvider \yii\data\ActiveDataProvider
     */
    public function actionIndex()
    {
        $request = Yii::$app->getRequest();
        $requestParams = $request->getBodyParams();
        
        if (empty($requestParams)) {
            $requestParams = $request->getQueryParams();
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find()
                // only find order transaction which payment proof has been uploaded
                ->where(['not in', 'status_id', [Order::STATUS_DRAFT, Order::STATUS_SUBMITTED]])
                // filter based on date range
                ->andFilterWhere([
                    'between',
                    'ordered_at',
                    $request->getBodyParam('order_from'),
                    $request->getBodyParam('order_to')
                ]),
            'pagination' => ['params' => $requestParams],
            'sort' => ['params' => $requestParams],
        ]);
        
        return $dataProvider;
    }
    
    /**
     * Shows a single order transaction
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }
    
    /*
     * Cancels an order based on given key.
     * @return mixed
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->updateAttributes(['status_id' => Order::STATUS_CANCELED]);
        
        return $model;
    }
    
    /*
     * Close an order based on given key.
     * @return mixed
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        $model->updateAttributes(['status_id' => Order::STATUS_CLOSED]);
        
        return $model;
    }
    
    /**
     * Process shipment for order
     */
    public function actionShipment()
    {
        $shipment = new ShipmentForm();
        $shipment->load(Yii::$app->getRequest()->getBodyParams(), '');
        
        if ($order = $shipment->submit()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            
            return $order;
        } elseif (!$shipment->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        
        return $shipment;
    }
}
