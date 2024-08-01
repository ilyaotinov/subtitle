<?php

namespace app\services\jwt;

use app\models\User;
use app\models\UserRefreshToken;
use DateTimeImmutable;
use kaabar\jwt\Jwt;
use Yii;
use yii\web\ServerErrorHttpException;

/**
 * @author Otinov Ilya
 */
class JWTTokenService implements JWTTokenManager
{
    public function generateJwt(User $user): string
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
    public function generateRefreshToken(
        User $user,
    ): UserRefreshToken {
        $userRefreshToken = UserRefreshToken::find()
            ->where(['user_id' => $user->id])
            // TODO: query to http request context in service not acceptable.
            ->andWhere(['agent' => Yii::$app->request->userAgent])
            // TODO: query to http request context in service not acceptable.
            ->andWhere(['ip' => Yii::$app->request->userIP])
            ->one();
        if ($userRefreshToken === null) {
            $refreshToken = Yii::$app->security->generateRandomString(200);
            $userRefreshToken = new UserRefreshToken([
                'user_id' => $user->id,
                'token' => $refreshToken,
                // TODO: query to http request context in service not acceptable.
                'ip' => Yii::$app->request->userIP,
                // TODO: query to http request context in service not acceptable.
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
        }

        return $userRefreshToken;
    }
}
