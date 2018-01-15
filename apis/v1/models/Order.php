<?php
namespace api\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Order model
 */
class Order extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_SUBMITTED = 1;
    const STATUS_PAID = 2;
    const STATUS_CANCELED = 3;
    const STATUS_SHIPPED = 4;
    
    const PAYMENT_TRANSFER = 1;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [TimestampBehavior::className()];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'coupon_id'], 'integer'],
            ['ip_address', 'string', 'max' => 15],
            ['agent', 'string'],
            ['payment_proof', 'string', 'max' => 255],
            [['payment_id'], 'default', 'value' => 0],
            [['status_id'], 'default', 'value' => self::STATUS_DRAFT],
        ];
    }
    
    /**
     * Overrides parent implementaion of parent::beforeSave() to performs operation(s) before saving the record.
     * @return boolean
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        
        if ($this->status_id == self::STATUS_SUBMITTED) {
            $this->ordered_at = time();
        }
        
        return true;
    }
    
    /**
     * Overrides parent implementaion of parent::afterSave() to performs operation(s) after saving the record.
     * @return boolean
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        if (!$insert && $this->status_id == self::STATUS_SUBMITTED) {
            foreach ($this->lines as $line) {
                // Decreases product quantity after order submitted
                $line->product->updateCounters(['quantity' => -1]);
            }
        }
    }
    
    // list every field available to end point
    public function fields()
    {
        return [
            // field name is the same as the attribute name
            'id',
            // field name is "ordered_at", the returned value is its formatted version as defined in [[orderedAt]]
            'ordered_at' => function ($model) {
                return $model->orderedAt;
            },
            // field name is "customer", the returned value is its modified version as defined in [[customerText]]
            'customer' => function ($model) {
                return $model->customer;
            },
            // field name is "coupon", the returned value is its modified version as defined in [[couponText]]
            'coupon' => function ($model) {
                return $model->couponText;
            },
            // field name is "payment", the returned value is defined in [[paymentText]]
            'payment' => function ($model) {
                return $model->paymentText;
            },
            'payment_proof',
            'discount_percentage',
            'discount_amount',
            // field name is "sub_total", the returned value is defined in [[subTotal]]
            'sub_total' => function ($model) {
                return $model->subTotal;
            },
            // field name is "grand_total", the returned value is defined in [[grandTotal]]
            'grand_total' => function ($model) {
                return $model->grandTotal;
            },
            // field name is "status", the returned value is defined in [[statusText]]
            'status' => function ($model) {
                return $model->statusText;
            },
            // list of order lines
            'lines' => function ($model) {
                return $model->lines;
            },
            // list of order shipments
            'shipments' => function ($model) {
                return $model->shipments;
            },
        ];
    }
    
    /*************
     * Relations *
     *************/
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLines()
    {
        return $this->hasMany(OrderLine::className(), ['order_id' => 'id'])->with('product')->inverseOf('order');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShipments()
    {
        return $this->hasMany(OrderShipment::className(), ['order_id' => 'id'])->inverseOf('order');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoupon()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }
    
    /*********
     * Misc. *
     *********/
    
    /**
     * Formats [[ordered_at]] as php date Y-m-d H:i:s.
     * @return string The formatted version of [[ordered_at]]
     */
    public function getOrderedAt()
    {
        return $this->ordered_at ? Yii::$app->formatter->asDate($this->ordered_at, 'php:Y-m-d H:i:s') : '';
    }
    
    /**
     * @return string
     */
    public function getCustomerText()
    {
        return $this->customer ? $this->customer->name : '';
    }
    
    /**
     * @return string
     */
    public function getCouponText()
    {
        return $this->coupon ? $this->coupon->code : '';
    }
    
    /**
     * @return string
     */
    public function getStatusText()
    {
        switch ($this->status_id) {
            case self::STATUS_DRAFT:
                return 'In Cart';
            case self::STATUS_SUBMITTED:
                return 'Submitted';
            case self::STATUS_PAID:
                return 'Paid';
            case self::STATUS_CANCELED:
                return 'Canceled';
            case self::STATUS_SHIPPED:
                return 'Shipped';
            default:
                return '';
        }
    }
    
    /**
     * @return array
     */
    public static function getPaymentList()
    {
        return [
            self::PAYMENT_TRANSFER => 'Bank Transfer'
        ];
    }
    
    /**
     * @return string
     */
    public function getPaymentText()
    {
        $paymentList = self::getPaymentList();
        
        return array_key_exists($this->payment_id, $paymentList) ? $paymentList[$this->payment_id] : '';
    }
    
    /**
     * @return string
     */
    public function getSubTotal()
    {
        return $this->getLines()->sum('price * quantity');
    }
    
    /**
     * @return string
     */
    public function getGrandTotal()
    {
        $total = $this->subTotal;
        
        if ($this->coupon) {
            $amount = $this->subTotal - $this->discount_amount;
            $percentage = $this->subTotal - ($this->subTotal * $this->discount_percentage / 100);
            // total minus whichever the least
            $total -= $amount <= $percentage ? $amount  : $percentage;
        }
        
        return $total;
    }
}
