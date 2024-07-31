<?php

namespace app\models;

use Lcobucci\JWT\Token;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @author Otinov Ilya
 */
class User extends ActiveRecord implements IdentityInterface
{
    /** @var int Идентификатор пользователя */
    public int $id;

    /** @var string $login Логин пользователя */
    public string $login;

    /** @var string Хэш пароля */
    public string $password;

    /** @var string E-mail пользователя */
    public string $email;

    private static $users = [
        '100' => [
            'id' => '100',
            'username' => 'admin',
            'password' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ],
        '101' => [
            'id' => '101',
            'username' => 'demo',
            'password' => 'demo',
            'authKey' => 'test101key',
            'accessToken' => '101-token',
        ],
    ];

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): ?static
    {
        return isset(self::$users[$id]) ? new static (self::$users[$id]) : null;
    }

    /**
     * @param Token $token
     */
    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        foreach (self::$users as $user) {
            if ($user['id'] === $token->getClaim('uid')) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username): ?static
    {
        foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;
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
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey(
        $authKey,
    ) {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(
        $password,
    ) {
        return $this->password === $password;
    }
}
