<?php

use yii\db\Migration;

/**
 * Handles the creation of table `coupon`.
 */
class m180112_075031_create_coupon_table extends Migration
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
        
        $this->createTable('{{%coupon}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(9)->notNull(),
            'description' => $this->text()->notNull(),
            'valid_from' => $this->integer()->notNull(),
            'valid_to' => $this->integer()->notNull(),
            'amount' => $this->float()->notNull(),
            'percentage' => 'tinyint not null',
            'quantity' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%coupon}}');
    }
}
