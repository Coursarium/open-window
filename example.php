<?php
require __DIR__ . DIRECTORY_SEPARATOR;
use \Coursarium\OpenWindow\Auth as OpenWindowAuth;
$ow = new OpenWindowAuth("universarium ", "3kplctd2");
var_export($ow->getUserInfo());