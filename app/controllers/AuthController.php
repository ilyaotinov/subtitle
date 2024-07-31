<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\User;
use app\models\UserRefreshToken;
use DateTimeImmutable;
use kaabar\jwt\Jwt;
use Yii;
use yii\web\Cookie;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @author Otinov Ilya
 */
class AuthController extends ProtectedController
{
    private function generateJwt(User $user): string
    {
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();

        $now = new DateTimeImmutable();

        $jwtParams = Yii::$app->params['jwt'];

        $token = $jwt->getBuilder()
            ->issuedBy($jwtParams['issuer'])
            ->permittedFor($jwtParams['audience'])
            ->identifiedBy($jwtParams['id'])
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify($jwtParams['request_time']))
            ->expiresAt($now->modify($jwtParams['expire']))
            ->withClaim('uid', $user->id)
            ->getToken($signer, $key);

        return $token->toString();
    }

    /**
     * @throws yii\base\Exception
     */
    private function generateRefreshToken(
        User $user,
    ): UserRefreshToken {
        $refreshToken = Yii::$app->security->generateRandomString(200);

        // TODO: Don't always regenerate - you could reuse existing one if user already has one with same IP and user agent
        $userRefreshToken = new UserRefreshToken([
            'user_id' => $user->id,
            'token' => $refreshToken,
            'ip' => Yii::$app->request->userIP,
            'agent' => Yii::$app->request->userAgent,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
        if (! $userRefreshToken->save()) {
            throw new ServerErrorHttpException(
                'Failed to save the refresh token: ' . implode(
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
        if ($model->load(Yii::$app->request->getBodyParams(), 'data') && $model->login()) {
            $user = Yii::$app->user->identity;

            $token = $this->generateJwt($user);

            return [
                'user' => $user,
                'token' => $token,
            ];
        } else {
            $model->validate();
            return $model;
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
                ->where(['userID' => $userRefreshToken->user_id])
                ->andWhere(['not', ['usr_status' => 'inactive']])
                ->one();
            if (! $user) {
                $userRefreshToken->delete();
                return new UnauthorizedHttpException('The user is inactive.');
            }

            $token = $this->generateJwt($user);

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