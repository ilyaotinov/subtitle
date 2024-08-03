<?php

namespace app\services\auth;

use app\dto\auth\LoginTokensDTO;
use app\models\User;
use app\models\UserRefreshToken;
use app\services\jwt\JWTTokenManager;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class AuthService
{
    public function __construct(
        public JWTTokenManager $tokenManager,
    ) {}

    /**
     * @param User $user
     * @param string $userAgent
     * @param string $userIp
     *
     * @return LoginTokensDTO
     * @throws Exception
     * @throws ServerErrorHttpException
     */
    public function login(User $user, string $userAgent, string $userIp): LoginTokensDTO
    {
        $token = $this->tokenManager->generateJwt($user);
        $userRefreshToken = $this->generateRefreshToken($user, $userAgent, $userIp);
        return new LoginTokensDTO($token, $userRefreshToken->token);
    }

    /**
     * @param User $user
     * @param string $userAgent
     * @param string $userIp
     *
     * @return UserRefreshToken
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    private function generateRefreshToken(User $user, string $userAgent, string $userIp): UserRefreshToken
    {
        $userRefreshToken = UserRefreshToken::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['agent' => $userAgent])
            ->andWhere(['ip' => $userIp])
            ->one();
        if ($userRefreshToken === null) {
            $refreshToken = $this->tokenManager->generateRefreshToken();
            $userRefreshToken = new UserRefreshToken([
                'user_id' => $user->id,
                'token' => $refreshToken,
                'ip' => $userIp,
                'agent' => $userAgent,
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
        }

        return $userRefreshToken;
    }

    /**
     * @param string $refreshToken
     *
     * @return string
     * @throws StaleObjectException
     * @throws Throwable
     * @throws UnauthorizedHttpException
     */
    public function refreshToken(string $refreshToken): string
    {
        $userRefreshToken = UserRefreshToken::findOne(['token' => $refreshToken]);

        if (! $userRefreshToken) {
            throw new UnauthorizedHttpException('The refresh token no longer exists.');
        }

        /** @var User $user */
        $user = User::find()
            ->where(['id' => $userRefreshToken->user_id])
            ->andWhere(['not', ['status' => 'inactive']])
            ->one();
        if (! $user) {
            $userRefreshToken->delete();
            throw new UnauthorizedHttpException('The user is inactive.');
        }

        return $this->tokenManager->generateJwt($user);
    }

    /**
     * @param bool|string $refreshToken
     *
     * @return void
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteRefreshToken(bool|string $refreshToken): void
    {
        $userRefreshToken = UserRefreshToken::findOne(['token' => $refreshToken]);
        if ($userRefreshToken && ! $userRefreshToken->delete()) {
            throw new BadRequestHttpException('Failed to delete the refresh token.');
        }
    }
}