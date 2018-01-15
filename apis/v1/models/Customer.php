<?php
namespace api\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\Link; // represents a link object as defined in JSON Hypermedia API Language.
use yii\web\Linkable;
use yii\helpers\Url;

/**
 * Customer model
 */
class Customer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
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
            [['name', 'phone', 'email', 'address'], 'required'],
            [['name', 'email'], 'string', 'max' => 255],
            ['phone', 'string', 'max' => 15],
            ['address', 'string'],
        ];
    }
    
    /*************
     * Relations *
     *************/
    
    /**
     * Returns product name.
     * @return string
     */
    public function getProductText()
    {
        return $this->product ? $this->product->name : '';
    }
}
