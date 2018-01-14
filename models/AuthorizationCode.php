<?php
namespace restore\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "authorization_code".
 */
class AuthorizationCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%authorization_code}}';
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
            [['code', 'expired_at', 'user_id', 'created_at', 'updated_at'], 'required'],
            [['expired_at', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 150],
            [['app_id'], 'string', 'max' => 200],
        ];
    }
    
    public static function isValid($code)
    {
        $model = static::findOne(['code' => $code]);

        if (!$model || $model->expired_at < time()) {
            throw new ServerErrorHttpException("Authorization code has expired.");
        }
        
        return $model;
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
