<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_refresh_tokens}}`.
 */
class m240730_044921_create_user_refresh_tokens_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_refresh_tokens}}', [
            'id' => $this->bigPrimaryKey()->notNull()->unsigned(),
            'user_id' => $this->bigInteger()->notNull()->unsigned(),
            'token' => $this->string(1000)->notNull(),
            'ip' => $this->string(50)->notNull(),
            'agent' => $this->string(1000)->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_user_refresh_tokens_user_id',
            'user_refresh_tokens',
            'user_id',
            'users',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_refresh_tokens}}');
    }
}
