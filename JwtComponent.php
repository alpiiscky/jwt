<?php

namespace alpiiscky\jwt;

use Firebase\JWT\JWT;
use yii\base\Component;
use Yii;

class JwtComponent extends Component
{
    /**
     * secret for access token
     *
     * @var string
     */
    public $accessSecret = '';

    /**
     * @var array
     */
    public $algorithm = ['HS256'];

    /**
     * Время жизни токена
     * @var int
     */
    public $lifetime = 14400;

    /**
     * @var string
     */
    public $userClass = 'app\\models\\User';

    /**
     * @var string
     */
    public $iss = 'my_key';

    /**
     * @var int
     */
    protected $expires;


    public function __construct($config = [])
    {
        $this->expires = time() + $this->lifetime;

        parent::__construct($config);
    }

    /**
     * Обновление токена
     * @return array
     */
    public function generateToken()
    {
        $accessToken = $this->encodeAccessToken();
        $this->setHeaderToken($accessToken);

        return [
            'accessToken' => $accessToken,
            'expires' => $this->expires
        ];
    }

    /**
     * Раскодирование JWT токена
     * @param $jwt
     * @param $secret
     * @return object | bool
     */
    public function decodeToken($jwt, $secret)
    {
        try {
            return JWT::decode($jwt, $secret, $this->algorithm);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Восстановление авторизации через JWT токены
     * @return bool
     */
    public function autoAuthorization()
    {
        $accessToken = Yii::$app->request->headers->get('Access-Token');

        if ($accessToken && $this->authWithAccessToken($accessToken)) {
            $this->setHeaderToken($accessToken);
            return true;
        }

        return false;
    }

    /**
     * Попытка восстановить сессию из Access-Token
     *
     * @param $accessToken
     * @return bool
     */
    public function authWithAccessToken($accessToken)
    {
        $accessJwt = Yii::$app->jwt->decodeToken(
            $accessToken,
            $this->accessSecret
        );

        if ($accessJwt) {

            $model = $this->userClass;

            $identity = $model::find()
                ->where('id = :user_id', [':user_id'=>$accessJwt->id])
                ->one();

            if (!$identity) {
                return false;
            }

            if (Yii::$app->user->login($identity)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Генерация JWT access токена
     *
     * @return string
     */
    protected function encodeAccessToken()
    {
        $payload = [
            'iss' => $this->iss,
            'iat' => time(),
            'exp' => $this->expires,
            'id' => \Yii::$app->user->id
        ];

        return JWT::encode($payload, $this->accessSecret);
    }

    /**
     * Установка токена в header для response
     *
     * @param $accessToken
     */
    protected function setHeaderToken($accessToken = null)
    {
        if ($accessToken) {
            Yii::$app->response->headers->set('Access-Token', $accessToken);
        }
    }
}