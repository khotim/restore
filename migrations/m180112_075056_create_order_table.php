<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order`.
 */
class m180112_075056_create_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'ip_address' => $this->string(15)->notNull(),
            'agent' => $this->string()->notNull(),
            'ordered_at' => $this->integer()->notNull(),
            'payment_id' => 'tinyint not null',
            'payment_proof' => $this->string()->notNull(),
            'discount_percentage' => 'tinyint not null',
            'discount_amount' => $this->float()->notNull(),
            'coupon_id' => $this->integer()->notNull(),
            'status_id' => 'tinyint not null',
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%order}}');
    }
}
