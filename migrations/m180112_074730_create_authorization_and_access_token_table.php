<?php

use yii\db\Migration;

/**
 * Handles the creation of table `authorization_code` and `access_token`.
 */
class m180112_074730_create_authorization_and_access_token_table extends Migration
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
        
        $this->createTable('{{%access_token}}', [
            'id' => $this->primaryKey(),
            'token' => $this->string(300)->notNull(),
            'expired_at' => $this->integer()->notNull(),
            'auth_code' => $this->string(200)->notNull(),
            'user_id' => $this->integer()->notNull(),
            'app_id' => $this->string(200),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ], $tableOptions);
        
        $this->createTable('{{%authorization_code}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(150)->notNull(),
            'expired_at' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'app_id' => $this->string(200),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%authorization_code}}');
        $this->dropTable('{{%access_token}}');
    }
}
