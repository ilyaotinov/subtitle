<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\User;
use app\models\UserRefreshToken;
use Yii;
use yii\web\Cookie;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class AuthController extends ProtectedController
{
    /**
     * @param User $user
     *
     * @return mixed
     */
    private function generateJwt(User $user)
    {
        $jwt = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();
        $time = time();

        $jwtParams = Yii::$app->params['jwt'];

        return $jwt->getBuilder()
            ->issuedBy($jwtParams['issuer'])
            ->permittedFor($jwtParams['audience'])
            ->identifiedBy($jwtParams['id'], true)
            ->issuedAt($time)
            ->expiresAt($time + $jwtParams['expire'])
            ->withClaim('uid', $user->id)
            ->getToken($signer, $key);
    }

    /**
     * @throws yii\base\Exception
     */
    private function generateRefreshToken(User $user): UserRefreshToken
    {
        $refreshToken = Yii::$app->security->generateRandomString(200);

        // TODO: Don't always regenerate - you could reuse existing one if user already has one with same IP and user agent
        $userRefreshToken = new UserRefreshToken([
            'user_id' => $user->id,
            'token' => $refreshToken,
            'ip' => Yii::$app->request->userIP,
            'user_agent' => Yii::$app->request->userAgent,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
        if (! $userRefreshToken->save()) {
            throw new ServerErrorHttpException(
                message: 'Failed to save the refresh token: ' . implode(
                    separator: "\n",
                    array: $userRefreshToken->getErrorSummary(true),
                ),
            );
        }

        Yii::$app->response->cookies->add(
            new Cookie([
                'name' => 'refresh-token',
                'value' => $refreshToken,
                'httpOnly' => true,
                'sameSite' => 'none',
                'secure' => true,
                'path' => '/v1/auth/refresh-token',
            ]),
        );

        return $userRefreshToken;
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->getBodyParams()) && $model->login()) {
            $user = Yii::$app->user->identity;

            $token = $this->generateJwt($user);

            $this->generateRefreshToken($user);

            return [
                'user' => $user,
                'token' => (string) $token,
            ];
        } else {
            return $model->getFirstErrors();
        }
    }

    public function actionRefreshToken()
    {
        $refreshToken = Yii::$app->request->cookies->getValue('refresh-token', false);
        if (! $refreshToken) {
            return new UnauthorizedHttpException('No refresh token found.');
        }

        $userRefreshToken = UserRefreshToken::findOne(['urf_token' => $refreshToken]);

        if (Yii::$app->request->getMethod() == 'POST') {
            if (! $userRefreshToken) {
                return new UnauthorizedHttpException('The refresh token no longer exists.');
            }

            /** @var User $user */
            $user = User::find()
                ->where(['id' => $userRefreshToken->user_id])
                ->andWhere(['not', ['status' => 'inactive']])
                ->one();
            if ($user === null) {
                $userRefreshToken->delete();
                return new UnauthorizedHttpException('The user is inactive.');
            }

            $token = $this->generateJwt($user);

            return [
                'status' => 'ok',
                'token' => (string) $token,
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