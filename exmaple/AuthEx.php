<?php
require __DIR__ . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Auth.php';
use \Coursarium\OpenWindow\Auth as OpenWindowAuth;
$ow = new OpenWindowAuth("username", "password");
var_export($ow->getUserInfo());