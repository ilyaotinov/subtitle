<?php

namespace app\controllers;

use app\models\LoginForm;
use app\services\auth\AuthService;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\Cookie;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @author Otinov Ilya
 */
class AuthController extends ProtectedController
{
    public function __construct($id, $module, private readonly AuthService $authService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionLogin(): array
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->getBodyParams(), 'data') && $model->login()) {
            $user = Yii::$app->user->identity;

            $tokens = $this->authService->login($user, Yii::$app->request->userAgent, Yii::$app->request->userIP);

            Yii::$app->response->cookies->add(
                new Cookie([
                    'name' => 'refresh-token',
                    'value' => $tokens->refreshToken,
                    'httpOnly' => true,
                    'sameSite' => 'none',
                    'secure' => false,
                    'path' => '/auth/refresh-token',
                ]),
            );

            return [
                'token' => $tokens->token,
            ];
        }

        throw new UnauthorizedHttpException('given creds is invalid');
    }

    /**
     * @return array|UnauthorizedHttpException
     * @throws StaleObjectException
     * @throws Throwable
     * @throws UnauthorizedHttpException
     */
    public function actionRefreshToken()
    {
        $refreshToken = Yii::$app->request->cookies->getValue('refresh-token', false);
        if (! $refreshToken) {
            return new UnauthorizedHttpException('No refresh token found.');
        }

        $token = $this->authService->refreshToken($refreshToken);

        return [
            'status' => 'ok',
            'token' => $token,
        ];
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     * @throws ServerErrorHttpException
     */
    public function actionDeleteRefreshToken(): array
    {
        $refreshToken = Yii::$app->request->cookies->getValue('refresh-token', false);
        $this->authService->deleteRefreshToken($refreshToken);

        return ['status' => 'ok'];
    }
}
