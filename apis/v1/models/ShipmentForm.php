<?php
namespace api\v1\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * Shipment form
 */
class ShipmentForm extends Model
{
    public $order;
    public $logistic;
    public $shipped_at;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // order, logistic, shipped_at are required fields when shipping an order
            [['order', 'logistic', 'shipped_at'],'required'],
            // logistic partner should exist in table
            [
                'logistic', 
                'exist',
                'targetClass' => '\api\v1\models\Logistic',
                'targetAttribute' => 'id',
                'message' => 'Logistic partner is not available.'
            ],
            // order should exist in table and its status is currently paid
            [
                'order', 
                'exist',
                'targetClass' => '\api\v1\models\Order',
                'targetAttribute' => 'id',
                'filter' => ['status_id' => Order::STATUS_PAID],
                'message' => 'Order transaction ({value}) is not available.'
            ],
            // format input according to [[php:Y-m-d H:i]]
            ['shipped_at', 'date', 'format' => 'php:Y-m-d H:i', 'message' => 'Date format should look like : '.date('Y-m-d H:i')]
        ];
    }
    
    /**
     * Process order shipment.
     *
     * @return Order|null Order transaction data or null if saving fails
     */
    public function submit()
    {
        // Shipping data cannot be validated
        if (!$this->validate()) {
            return null;
        }
        
        // Finds order which has been paid
        $order = Order::findOne(['id' => $this->order, 'status_id' => Order::STATUS_PAID]);
        
        $model = new OrderShipment([
            'order_id' => $order->id,
            'logistic_id' => $this->logistic,
            'shipped_at' => $this->shipped_at
        ]);
        $model->save();
        
        $order->updateAttributes(['status_id' => Order::STATUS_SHIPPED]);
        
        return $order;
    }
}
