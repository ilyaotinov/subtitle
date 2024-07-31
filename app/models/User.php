<?php

namespace app\models;

use kaabar\jwt\Jwt;
use Lcobucci\JWT\Token;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $login
 * @property string $password
 * @property string $email
 * @property string $username
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'users';
    }

    public static function findIdentity($id): ?static
    {
        /** @var static|null */
        return static::find()
            ->where('id=:id', [':id' => $id])
            ->one();
    }

    /**
     * @param $token
     * @param $type
     *
     * @return static|null
     */
    public static function findIdentityByAccessToken($token, $type = null): static|null
    {
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        /** @var Token\Plain|null $token */
        $token = $jwt->loadToken($token);
        /** @var static|null */
        return static::find()
            ->where(['id' => $token->claims()->get('uid')])
            ->andWhere('status!=:status', [':status' => 'inactive'])
            ->one();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername(string $username): ?static
    {
        return static::findOne(['login' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): ?string
    {
        return '';
    }

    public function validateAuthKey(
        $authKey,
    ): bool {
        return true;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(
        string $password,
    ): bool {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * @param $insert
     * @param $changedAttributes
     *
     * @return void
     */
    public function afterSave($insert, $changedAttributes): void
    {
        if (array_key_exists('password', $changedAttributes)) {
            UserRefreshToken::deleteAll(['user_id' => $this->id]);
        }

        parent::afterSave($insert, $changedAttributes);
    }
}
