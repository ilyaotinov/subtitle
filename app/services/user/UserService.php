<?php

namespace app\services\user;

use app\models\http\form\user\RegisterForm;
use app\models\User;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

class UserService
{

    /**
     * @param RegisterForm $form
     *
     * @return User
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function register(RegisterForm $form): User
    {
        $user = new User();
        $user->login = $form->login;
        $user->setPassword($form->password);
        $user->email = $form->email;
        if (! $user->save()) {
            throw new BadRequestHttpException('failed register user' . json_encode($user->errors));
        }

        return $user;
    }
}