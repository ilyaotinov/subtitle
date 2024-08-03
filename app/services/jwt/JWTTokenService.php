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
    public function generateRefreshToken(): string
    {
        return Yii::$app->security->generateRandomString(200);
    }
}
