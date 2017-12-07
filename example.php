<?php
require __DIR__ . DIRECTORY_SEPARATOR;
use \Coursarium\OpenWindow\Auth as OpenWindowAuth;
$ow = new OpenWindowAuth("username", "password");
var_export($ow->getUserInfo());