<?php
namespace restore\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "access_token".
 */
class AccessToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%access_token}}';
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
            [['token', 'expired_at', 'auth_code', 'user_id', 'created_at', 'updated_at'], 'required'],
            [['expired_at', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['token'], 'string', 'max' => 300],
            [['auth_code', 'app_id'], 'string', 'max' => 200],
        ];
    }
    
    /*********
     * Misc. *
     *********/
    
    /**
     * @return string
     */
    public function getExpiredAt()
    {
        return $this->expired_at ? Yii::$app->formatter->asDate($this->expired_at, 'php:Y-m-d H:i:s') : '';
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
        return $this->updated_at ? Yii::$app->formatter->asDate($this->updated_at, 'php:Y-m-d H:i:s') : '-';
    }
}
