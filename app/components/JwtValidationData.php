<?php

namespace app\components;

use Yii;
use sizeg\jwt\JwtValidationData as BaseJwtValidationData;

class JwtValidationData extends BaseJwtValidationData
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $jwtParams = Yii::$app->params['jwt'];
        $this->validationData->setIssuer($jwtParams['issuer']);
        $this->validationData->setAudience($jwtParams['audience']);
        $this->validationData->setId($jwtParams['id']);

        parent::init();
    }
}