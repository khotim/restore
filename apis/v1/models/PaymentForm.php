<?php
namespace api\v1\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * Payment form
 */
class PaymentForm extends Model
{
    public $id;
    public $file;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['file', 'required'],
            [
                'file', // $this->file
                'image', // type of file
                'extensions' => 'png, jpg, jpeg, bmp, gif',
                'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/bmp']
            ],
            ['id', 'integer'],
        ];
    }
    
    /**
     * Submit payment proof.
     *
     * @return Order|null Order transaction data or null if saving fails
     */
    public function submit()
    {
        // Validates file type
        if (!$this->validate()) {
            return false;
        }
        
        // Finds order transaction which has been submitted
        $order = Order::findOne(['id' => $this->id, 'status_id' => Order::STATUS_SUBMITTED]);
        
        if ($order === null) {
            throw new NotFoundHttpException("Order transaction ID#{$this->id} is not available.");
        }
        
        // Checks if file can be uploaded
        if (!$this->file) {
            throw new NotFoundHttpException("File cannot be uploaded.");
        }
        
        $order->status_id = Order::STATUS_PAID;
        // Gets full path for file upload
        $order->payment_proof = Yii::$app->params['uploadDir'].'/'.$this->file->baseName.'.'.$this->file->extension;
        // Uploads the file
        $this->file->saveAs($order->payment_proof);
        
        return $order->save() ? $order : null;
    }
}