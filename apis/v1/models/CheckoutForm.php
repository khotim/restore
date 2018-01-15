<?php
namespace api\v1\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * Checkout form
 */
class CheckoutForm extends Model
{
    public $name;
    public $phone;
    public $email;
    public $address;
    public $coupon;
    public $payment;
    public $product;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // name, phone, email, address are required field when submitting order
            [['name','phone','email', 'address'],'required'],
            [['name','email'], 'string', 'max' => 255],
            ['phone', 'string', 'max' => 15],
            ['address', 'string'],
            // coupon code should exist in table and its quantity must greater than 0
            [
                'coupon', 
                'exist',
                'targetClass' => '\api\v1\models\Coupon',
                'targetAttribute' => 'code',
                'filter' => ['>', 'quantity', 0],
                'message' => 'Coupon is not available.'
            ],
            // payment should exist within Order::getPaymentList()
            ['payment', 'in', 'range' => Order::getPaymentList(), 'message' => 'Invalid payment type.'],
            // payment is default to Order::PAYMENT_TRANSFER if no value provided
            ['payment', 'default', 'value' => Order::PAYMENT_TRANSFER],
        ];
    }
    
    /**
     * Submit the order.
     *
     * @return Order|null Order transaction data or null if saving fails
     */
    public function submit()
    {
        // Customer data cannot be validated
        if (!$this->validate()) {
            return null;
        }
        
        // Finds existing cart for current user based on IP and user agent.
        $order = Order::findOne([
            'ip_address' => Yii::$app->request->userIP,
            'agent' => Yii::$app->request->userAgent,
            'status_id' => Order::STATUS_DRAFT
        ]);
        
        // Cart is empty.
        if ($order === null) {
            throw new NotFoundHttpException('Cart is empty.');
        }
        
        //== Begin product verification ==//
        $products = [];
        
        foreach ($order->lines as $line) {
            if ($line->quantity > $line->product->quantity) {
                // take a note for out of stock product : productName(quantity)
                $products []= $line->productText."({$line->product->quantity})";
            }
        }
        
        // Some products are not available
        if ($products) {
            $message = implode(', ', $products);
            $this->addError('product', "Not enough stock for product {$message}");
            
            return null;
        }
        //== End product verification ==//
        
        // Check if it is a returning customer
        $customer = Customer::findOne(['email' => $this->email]);
        
        if ($customer === null) {
            // new customer
            $customer = new Customer([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address
            ]);
            
            $customer->save(false);
        }
        
        // Get the coupon if this transaction use one
        if ($coupon = Coupon::findOne(['code' => $this->coupon])) {
            $order->coupon_id = $coupon->id;
            $order->discount_percentage = $coupon->percentage;
            $order->discount_amount = $coupon->amount;
            // decrease coupon quantity
            $coupon->updateCounters(['quantity' => -1]);
        }
        
        $order->customer_id = $customer->id;
        $order->payment_id = $this->payment;
        $order->status_id = Order::STATUS_SUBMITTED;
        $order->save(false);
        
        return $order;
    }
}