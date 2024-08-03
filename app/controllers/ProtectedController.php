<?php

namespace app\controllers;

use kaabar\jwt\JwtHttpBearerAuth;
use yii\rest\Controller;

/**
 * @author Otinov Ilya
 */
class ProtectedController extends Controller
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'except' => [
                'login',
                'options',
                'debug',
                'index',
                'refresh-token',
                'delete-refresh-token',
            ],
        ];

        return $behaviors;
    }
}
