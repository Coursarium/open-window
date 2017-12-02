<?php
namespace Coursarium\OpenWindow;
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Symfony\Component\Yaml\Yaml;

/**
 * Интеграция с "открытым окном".
 * 
 * Пока что реализует лишь пункт "1.1. Взаимодействие с Подсистемой аутентификации Портала".
 */
class Auth {
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
     * @return Auth Объект, позволяющий в дальнейшем взаимодействовать с "открытым окном".
     */
    public function __construct(
        $username,
        $password
    ){
        $config = Yaml::parse(file_get_contents('config.yml'));
        $configAuth = $config['auth'];
        $this->baseURL = $configAuth['base_url'];
        $this->realm = $configAuth['realm'];

        $guzzle = new \GuzzleHttp\Client(['base_uri' => $this->baseURL]);
        $this->endpoints = json_decode($guzzle->get(sprintf("/realms/%s/.well-known/openid-configuration", $this->realm))->getBody());
        $loginData = json_decode($guzzle->post($this->endpoints->token_endpoint, [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $configAuth['client_id'],
                'client_secret' => $configAuth['client_secret'],
                'username' => $username,
                'password' => $password
            ]
        ])->getBody());
        $this->setTokens($loginData);
    }
    /**
     * Сохраняет информацию о токенах в соответствующих полях класса.
     * @param object $loginData Данные о токене доступа, полученные из OAuth.
     * @return void
     */
    protected function setTokens($loginData){
        $t = microtime(true);
        $this->token = $loginData->access_token;
        $this->refreshToken = $loginData->refresh_token;
        $this->tokenExpiry = $t+$loginData->expires_in*1000;
        $this->refreshTokenExpiry = $t+$loginData->refresh_expires_in*1000;
    }
    /**
     * Выполняет проверку истечения токена и его освежение.
     * @return void
     */
    public function doTokenRefresh(){
        $ct = microtime(true);
        if($ct > $this->tokenExpiry){
            if($ct < $this->refreshTokenExpiry){
                $guzzle = new \GuzzleHttp\Client(['base_uri' => $this->baseURL]);
                $configAuth = (Yaml::parse(file_get_contents('config.yml')))['auth'];
                $loginData = json_decode($guzzle->post($this->endpoints->token_endpoint, [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'client_id' => $configAuth['client_id'],
                        'client_secret' => $configAuth['client_secret'],
                        'refresh_token' => $this->refreshToken
                    ]
                ])->getBody());
                $this->setTokens($loginData);
            } else {
                throw new AuthRefreshTokenExpiredException("Refresh token expired.");
            }
        }
    }
}

/**
 * Исключение, говорящее нам, что refresh-токен истек, т.е. активности пользователя не было на протяжении долгого времени.
 * Должно быть поймано с перенаправлением на страницу входа,
 * или вход с тем же именем/паролем, что и раньше, должен быть осуществлен иным образом.
 */
class AuthRefreshTokenExpiredException extends \Exception {

}