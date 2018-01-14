<?php
namespace api\v1\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\Link; // represents a link object as defined in JSON Hypermedia API Language.
use yii\web\Linkable;
use yii\helpers\Url;

/**
 * OrderLine model
 */
class OrderLine extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_line}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['product_id', 'required'],
            ['quantity', 'integer'],
            ['quantity', 'default', 'value' => 1],
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
        
        if ($insert) {
            $this->price = $this->product ? $this->product->price : 0;
        }
        
        return true;
    }
    
    // list every field available to end point
    public function fields()
    {
        return [
            // field name is the same as the attribute name
            'id',
            // field name is "product", the returned value is its modified version as defined in [[productText]]
            'product' => function ($model) {
                return $model->productText;
            },
            // field name is the same as the attribute name
            'quantity',
            // field name is the same as the attribute name
            'price',
            // Field name 'amount', the returned value is defined in 'amount'
            'amount' => function ($model) {
                return $this->amount;
            },
        ];
    }
    
    /*************
     * Relations *
     *************/
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
    
    /*********
     * Misc. *
     *********/
    
    /**
     * Returns product name.
     * @return string
     */
    public function getProductText()
    {
        return $this->product ? $this->product->name : '';
    }
    
    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->quantity * $this->price;
    }
}
