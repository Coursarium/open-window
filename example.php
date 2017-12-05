<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'Auth.php';
use \Coursarium\OpenWindow\Auth as OpenWindowAuth;
$ow = new OpenWindowAuth("your_username", "your_password");
var_export($ow->getUserInfo());