<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_shipment`.
 */
class m180112_075134_create_order_shipment_table extends Migration
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
        
        $this->createTable('{{%order_shipment}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull(),
            'order_id' => $this->integer()->notNull(),
            'logistic_id' => $this->integer()->notNull(),
            'shipped_at' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%order_shipment}}');
    }
}
