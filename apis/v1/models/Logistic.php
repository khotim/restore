<?php
namespace api\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\Link; // represents a link object as defined in JSON Hypermedia API Language.
use yii\web\Linkable;
use yii\helpers\Url;

/**
 * Logistic model
 */
class Logistic extends ActiveRecord implements Linkable
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%logistic}}';
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
            [['name', 'phone', 'address'], 'required'],
            ['name', 'string', 'max' => 255],
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
            'address',
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
            Link::REL_SELF => Url::to(['admin/logistic/view', 'id' => $this->id], true),
            'edit' => Url::to(['admin/logistic/view', 'id' => $this->id], true),
            'delete' => Url::to(['admin/logistic/view', 'id' => $this->id], true),
            'index' => Url::to('admin/logistics', true),
        ];
    }
    
    /*********
     * Misc. *
     *********/
    
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
