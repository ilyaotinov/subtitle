<?php

namespace app\models\http\form\user;

use app\models\User;
use yii\base\Exception;
use yii\base\Model;
use yii\web\BadRequestHttpException;

class RegisterForm extends Model
{
    public string $login = '';

    public string $password = '';

    public string $email = '';

    public function rules(): array
    {
        return [
            [['login', 'password', 'email'], 'required'],
            [['login'], 'string', 'max' => 255],
            [['password'], 'string', 'min' => 8],
            [['email'], 'email'],
        ];
    }

}
