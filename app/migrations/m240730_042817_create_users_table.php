<?php

use yii\db\Migration;

/**
 * Handles the creation of table `users`.
 */
class m240730_042817_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->bigPrimaryKey()->notNull()->unsigned(),
            'status' => $this->string(255)->defaultValue('active')->notNull(),
            'password' => $this->text()->notNull(),
            'login' => $this->string(255)->notNull(),
            'email' => $this->string(255)->unique()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('users');
    }
}
