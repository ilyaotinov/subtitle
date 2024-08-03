<?php

namespace app\controllers;

use app\models\http\form\user\RegisterForm;
use app\services\user\UserService;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\web\BadRequestHttpException;

class UserController extends ProtectedController
{
    public function __construct($id, $module, private readonly UserService $userService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws InvalidConfigException
     */
    public function actionRegister(): array
    {
        $model = new RegisterForm();
        if ($model->load(Yii::$app->request->getBodyParams(), 'data') && $model->validate()) {
            $user = $this->userService->register($model);
            return $user->toArray(['id', 'login']);
        }
        throw new BadRequestHttpException(
            'Invalid data for register:' . json_encode($model->errors),
        );
    }
}