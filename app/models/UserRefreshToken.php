<?php

namespace app\models;

use DateTime;
use Exception;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id;
 * @property string $token;
 * @property string $ip;
 * @property string $agent;
 * @property DateTime|string $created_at;
 */
class UserRefreshToken extends ActiveRecord
{


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
