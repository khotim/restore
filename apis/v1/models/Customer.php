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
    
    // list every field available to end point
    public function fields()
    {
        return [
            // field name is the same as the attribute name
            'id',
            'name',
            'phone',
            'email',
            'address',
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
    
    /*********
     * Misc. *
     *********/
    
    /**
     * Formats [[auth_expired]] as php date Y-m-d H:i:s.
     * @return string The formatted version of [[auth_expired]]
     */
    public function getAuthExpired()
    {
        return $this->auth_expired ? Yii::$app->formatter->asDate($this->auth_expired, 'php:Y-m-d H:i:s') : '';
    }
    
    /**
     * Formats [[token_expired]] as php date Y-m-d H:i:s.
     * @return string The formatted version of [[token_expired]]
     */
    public function getTokenExpired()
    {
        return $this->token_expired ? Yii::$app->formatter->asDate($this->token_expired, 'php:Y-m-d H:i:s') : '';
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
