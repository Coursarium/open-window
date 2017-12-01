<?php
namespace Universarium\OpenWindow;
require __DIR__ . '/vendor/autoload.php';
/**
 * Интеграция с "открытым окном".
 * 
 * Пока что реализует лишь пункт "1.1. Взаимодействие с Подсистемой аутентификации Портала".
 */
class OpenWindow {
    /**
     * @var string Хранит URL портала (на случай если он изменится)
     */
    protected $baseURL;
    /**
     * @var string Хранит то, что в документации называют "имя домена".
     */
    protected $realm;
    /**
     * @var string Хранит текущий токен.
     */
    protected $token;
    /**
     * @var string Хранит текущий токен для освежения.
     */
    protected $refreshToken;
    /**
     * @var float Хранит время, когда токен истекает.
     */
    protected $tokenExpiry;
    /**
     * @var float Хранит время, когда истекает refresh-токен.
     */
    protected $refreshTokenExpiry;
    /**
     * @var array Хранит endpoint-ы API, используемые для взаимодействия с "окном".
     */
    protected $endpoints;
    /**
     * @param string $username Имя пользователя, используемое для входа.
     * @param string $password Пароль.
     * @param array $config Конфигурация, в частности, URL-ы используемые классом.
     * @return OpenWindow Объект, позволяющий в дальнейшем взаимодействовать с "открытым окном".
     */
    function __construct(
        $username,
        $password,
        $config = [
            'baseURL' => 'https://sso.online.edu.ru/',
            'realm' => 'portfolio'
        ]
    ){
        $this->baseURL = $config['baseURL'];
        $this->realm = $config['realm'];
        $guzzle = new \GuzzleHttp\Client(['base_uri' => $this->baseURL]);
        $this->endpoints = json_decode($guzzle->get(sprintf("/realms/%s/.well-known/openid-configuration", $this->realm))->getBody());
        $t = microtime(true);
        $loginData = json_decode($guzzle->post($this->endpoints->token_endpoint, [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => 'universarium',
                'client_secret' => '8ec3a771-fec3-44f9-b54b-300bf7b0db58',
                'username' => $username,
                'password' => $password
            ]
        ])->getBody());
        $this->token = $loginData->access_token;
        $this->refreshToken = $loginData->refresh_token;
        $this->tokenExpiry = $t+$loginData->expires_in*1000;
        $this->refreshTokenExpiry = $t+$loginData->refresh_expires_in*1000;
    }
}