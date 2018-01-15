<?php
namespace api\v1\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $name;
    public $email;
    public $password;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'password', 'email'],'required'],
            ['email', 'unique', 'targetClass' => '\api\v1\models\User', 'message' => 'This email has already been registered.'],
            ['email', 'string', 'max' => 255],
            ['email', 'trim'],
            ['email', 'email'],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }
    
    /**
     * Registers a user.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $user = new User();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        
        return $user->save() ? $user : null;
    }
}
