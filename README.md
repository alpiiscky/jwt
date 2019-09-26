Jwt-компонент v1 
======================
Авторизация при помощи jwt-токена

Установка
------------

```
composer require --prefer-dist alpiiscky/yii2-jwt-component "*"
```

или добавьте

```
"alpiiscky/yii2-jwt-component": "*"
```

в раздел `require` вашего `composer.json` файла.


Настройка
-----

```
'jwt' => [
    'class' => 'alpiiscky\jwt\JwtComponent',
    'accessSecret' => '<рандом строка>',
    'iss' => 'my_company',
    'userClass' => 'app\\models\\User'
],
```

Также необязательные параметры:
```lifetime``` - время жизни токена (по умолчанию - 4 часа)



Использование
-----

В вашем модуле, для аутентификации используйте

```
public function init()
{
    parent::init();

    if (!Yii::$app->jwt->autoAuthorization()) {
        throw new UnauthorizedHttpException('Срок действия токенов истек. Пожалуйста, повторите авторизацию');
    }
}
```

Для авторизации и получения токена Access-Token:

```
$accessToken = Yii::$app->jwt->generateTokensForAuth();
```

После успешной авторизации все запросы должны проходить с установленным header-ом ```Access-Token```.