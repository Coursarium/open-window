<?php
/**
 * Created by PhpStorm.
 * User: shapkin
 * Date: 07/12/2017
 * Time: 09:27
 */

namespace Coursarium\OpenWindow;
require implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);
require implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'src', 'Auth.php']);
use Symfony\Component\Yaml\Yaml;

class AuthTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigFileExists(){
        $this->assertFileExists(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'config.yml']));
    }
    public function testCanCreateFromValidUsernameAndPassword(){
        $conf = Yaml::parse(file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'config.yml'])));
        $this->assertInstanceOf(Auth::class, new Auth($conf['auth']['phpunit_username'], $conf['auth']['phpunit_password']));
    }
    public function testCanNotCreateFromInvalidUsernameOrPassword(){
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        new Auth("baduser", "badpass");
    }
    public function testAuthHasAllExpectedUserInfoAvailable(){
        $conf = Yaml::parse(file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'config.yml'])));
        $auth = new Auth($conf['auth']['phpunit_username'], $conf['auth']['phpunit_password']);
        $userinfo = $auth->getUserInfo();
        foreach(['sub', 'name', 'preferred_username', 'middle_name', 'given_name', 'family_name', 'email'] as $attr){
            $this->assertObjectHasAttribute($attr, $userinfo);
        }
    }
}
