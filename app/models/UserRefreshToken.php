<?php

namespace app\models;

use DateTime;
use Exception;
use yii\db\ActiveRecord;

/**
 * @author Otinov Ilya
 */
class UserRefreshToken extends ActiveRecord
{
    /** @var int Идентификатор */
    public int $id;

    /** @var int Идентификатор связанного пользователя */
    public int $user_id;

    /** @var string Токен */
    public string $token;

    /** @var string IP пользователя с которого был получен токен */
    public string $ip;

    /** @var string устройство пользователя, с которого был получен токен */
    public string $agent;

    /** @var DateTime|string Дата создания */
    public DateTime|string $created_at;

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'user_refresh_tokens';
    }

    /**
     * @throws Exception
     */
    public function afterFind(): void
    {
        parent::afterFind();
        $this->created_at = new DateTime($this->created_at);
    }

    /**
     * @param $insert
     *
     * @return bool
     */
    public function beforeSave($insert): bool
    {
        if (! parent::beforeSave($insert)) {
            return false;
        }
        if ($this->created_at instanceof DateTime) {
            $this->created_at = $this->created_at->format('Y-m-d H:i:s');
        }

        return true;
    }
}
