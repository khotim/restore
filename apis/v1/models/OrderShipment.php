<?php
namespace api\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * OrderShipment model
 */
class OrderShipment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_shipment}}';
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
            [['shipped_at', 'logistic_id'], 'required'],
            ['logistic_id', 'exist', 'targetClass' => '\api\v1\models\Logistic', 'targetAttribute' => 'id'],
            ['shipped_at', 'date', 'timestampAttribute' => 'shipped_at', 'format' => 'php:Y-m-d H:i', 'skipOnEmpty' => false],
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
            // generates shipment code using "cyclic redundancy checksum polynomial" of 32-bit lengths of the str
            $this->code = sprintf('%x', crc32($this->shipped_at . $this->logistic_id . time()));
        }
        
        return true;
    }
    
    // list every field available to end point
    public function fields()
    {
        return [
            // field name is the same as the attribute name
            'id',
            'code',
            // field name is "product", the returned value is defined in self::getLogistic()
            'logistic' => function ($model) {
                return $model->logistic;
            },
            // Field name 'shipped_at', the returned value is defined in '[[shippedAt]]'
            'shipped_at' => function ($model) {
                return $this->shippedAt;
            },
            // Field name 'created_at', the returned value is defined in '[[createdAt]]'
            'created_at' => function ($model) {
                return $this->createdAt;
            },
            // Field name 'updated_at', the returned value is defined in '[[updatedAt]]'
            'updated_at' => function ($model) {
                return $this->updatedAt;
            }
        ];
    }
    
    /*************
     * Relations *
     *************/
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogistic()
    {
        return $this->hasOne(Logistic::className(), ['id' => 'logistic_id']);
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
    public function getLogisticText()
    {
        return $this->logistic ? $this->logistic->name : '';
    }
    
    /**
     * Formats [[shipped_at]] as php date Y-m-d H:i.
     * @return string The formatted version of [[shipped_at]]
     */
    public function getShippedAt()
    {
        return $this->shipped_at ? Yii::$app->formatter->asDate($this->shipped_at, 'php:Y-m-d H:i') : '';
    }
    
    /**
     * Formats [[created_at]] as php date Y-m-d H:i:s.
     * @return string The formatted version of [[created_at]]
     */
    public function getCreatedAt()
    {
        return $this->created_at ? Yii::$app->formatter->asDate($this->created_at, 'php:Y-m-d H:i:s') : '';
    }
    
    /**
     * Formats [[updated_at]] as php date Y-m-d H:i:s.
     * @return string The formatted version of [[updated_at]]
     */
    public function getUpdatedAt()
    {
        return $this->updated_at ? Yii::$app->formatter->asDate($this->updated_at, 'php:Y-m-d H:i:s') : '';
    }
}
