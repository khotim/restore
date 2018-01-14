<?php
namespace api\v1\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\Link; // represents a link object as defined in JSON Hypermedia API Language.
use yii\web\Linkable;
use yii\helpers\Url;
use yii\web\IdentityInterface;

/**
 * User model
 */
class User extends \restore\models\User implements Linkable, IdentityInterface
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            [['name', 'email'], 'string', 'max' => 255],
        ];
    }
    
    // list every field available to end point
    public function fields()
    {
        return [
            // field name is the same as the attribute name
            'id',
            'name',
            'email',
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
            Link::REL_SELF => Url::to(['user/view', 'id' => $this->id], true),
            'edit' => Url::to(['user/view', 'id' => $this->id], true),
            'index' => Url::to('users', true),
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
