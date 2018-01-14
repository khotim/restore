<?php
namespace api\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\Link; // represents a link object as defined in JSON Hypermedia API Language.
use yii\web\Linkable;
use yii\helpers\Url;

/**
 * Coupon model
 */
class Coupon extends ActiveRecord implements Linkable
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%coupon}}';
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
            [['code', 'valid_from', 'valid_to'], 'required'],
            ['code', 'filter', 'filter' => function ($value) {
                // removes all whitespaces
                return preg_replace('/\s+/', '', $value);
            }],
            ['code', 'string', 'max' => 9],
            ['code', 'unique'],
            ['valid_to', 'date', 'timestampAttribute' => 'valid_to', 'format' => 'php:Y-m-d H:i:s', 'message' => 'The date format should look like : '.date('Y-m-d')],
            ['valid_from', 'date', 'timestampAttribute' => 'valid_from', 'format' => 'php:Y-m-d H:i:s', 'message' => 'The date format should look like : '.date('Y-m-d')],
            ['description', 'string'],
            ['amount', 'number'],
            ['percentage', 'number', 'min' => 0, 'max' => 100],
            ['quantity', 'integer'],
        ];
    }
    
    /**
     * Overrides parent implementaion of parent::beforeValidate() to performs operation(s) before validating the attributes.
     * @return boolean
     */
    public function beforeValidate()
    {
        $this->valid_from = $this->valid_from.' 00:00:00';
        $this->valid_to = $this->valid_to.' 23:59:59';
        
        return parent::beforeValidate();
    }
    
    // list every field available to end point
    public function fields()
    {
        return [
            // field name is the same as the attribute name
            'id',
            'code',
            'description',
            'quantity',
            'amount',
            'percentage',
            // field name is "created", the returned value is its formatted version as defined in [[validFrom]]
            'valid_from' => function ($model) {
                return $model->validFrom;
            },
            // field name is "created", the returned value is its formatted version as defined in [[validTo]]
            'valid_to' => function ($model) {
                return $model->validTo;
            },
            // field name is "created", the returned value is its formatted version as defined in [[createdAt]]
            'created' => function ($model) {
                return $model->createdAt;
            },
            // field name is "updated", the returned value is its formatted version as defined in [[updatedAt]]
            'updated' => function ($model) {
                return $model->updatedAt;
            },
        ];
    }
    
    /*
     * Returns information that allows clients to discover actions supported for this resources.
     * @return array
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['coupon/view', 'id' => $this->id], true),
            'edit' => Url::to(['coupon/view', 'id' => $this->id], true),
            'index' => Url::to('coupons', true),
        ];
    }
    
    /**
     * Formats [[valid_from]] as php date Y-m-d H:i:s.
     * @return string The formatted version of [[valid_from]]
     */
    public function getValidFrom()
    {
        return $this->valid_from ? Yii::$app->formatter->asDate($this->valid_from, 'php:Y-m-d H:i:s') : '-';
    }
    
    /**
     * Formats [[valid_to]] as php date Y-m-d H:i:s.
     * @return string The formatted version of [[valid_to]]
     */
    public function getValidTo()
    {
        return $this->valid_to ? Yii::$app->formatter->asDate($this->valid_to, 'php:Y-m-d H:i:s') : '-';
    }
    
    /**
     * Formats [[created_at]] as php date Y-m-d H:i:s.
     * @return string The formatted version of [[created_at]]
     */
    public function getCreatedAt()
    {
        return $this->created_at ? Yii::$app->formatter->asDate($this->created_at, 'php:Y-m-d H:i:s') : '-';
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
