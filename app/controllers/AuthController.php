<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\User;
use app\models\UserRefreshToken;
use app\services\auth\AuthService;
use Yii;
use yii\web\Cookie;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @author Otinov Ilya
 */
class AuthController extends ProtectedController
{
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actionLogin(AuthService $authService)
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->getBodyParams(), 'data') && $model->login()) {
            $user = Yii::$app->user->identity;

            $token = $authService->login($user);
            $refreshToken = $authService->generateRefreshToken($user);

            Yii::$app->response->cookies->add(
                new Cookie([
                    'name' => 'refresh-token',
                    'value' => $refreshToken->token,
                    'httpOnly' => true,
                    'sameSite' => 'none',
                    'secure' => false,
                    'path' => '/auth/refresh-token',
                ]),
            );

            return [
                'token' => $token,
            ];
        }

        return new UnauthorizedHttpException('given creds is invalid');
    }

    public function actionRefreshToken(AuthService $authService)
    {
        $refreshToken = Yii::$app->request->cookies->getValue('refresh-token', false);
        if (! $refreshToken) {
            return new UnauthorizedHttpException('No refresh token found.');
        }

        $userRefreshToken = UserRefreshToken::findOne(['token' => $refreshToken]);

        if (Yii::$app->request->getMethod() == 'POST') {
            if (! $userRefreshToken) {
                return new UnauthorizedHttpException('The refresh token no longer exists.');
            }

            /** @var User $user */
            $user = User::find()
                ->where(['id' => $userRefreshToken->user_id])
                ->andWhere(['not', ['status' => 'inactive']])
                ->one();
            if (! $user) {
                $userRefreshToken->delete();
                return new UnauthorizedHttpException('The user is inactive.');
            }

            $token = $authService->generateToken($user);

            return [
                'status' => 'ok',
                'token' => $token,
            ];
        } elseif (Yii::$app->request->getMethod() == 'DELETE') {
            if ($userRefreshToken && ! $userRefreshToken->delete()) {
                return new ServerErrorHttpException('Failed to delete the refresh token.');
            }

            return ['status' => 'ok'];
        } else {
            return new UnauthorizedHttpException('The user is inactive.');
        }
    }
}
