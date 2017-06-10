<?php
/**
 * Created by PhpStorm.
 * User: Imxz
 * Date: 2017/5/11
 * Time: 20:03
 */
require './utils/HttpUtils.php';
session_start();

$verifyImgUrl = "http://220.178.150.5:8082/cas/imagerandomcheckedcodeservlet";
$http = HttpUtils::getInstance();

if (isset($_SESSION['cookie'])) {
    $cookie = $_SESSION['cookie'];
    $response = $http->get($verifyImgUrl,$cookie);
} else {
    $response = $http->get($verifyImgUrl);
    $_SESSION['cookie'] = $response->getCookie();
}

header("Content-Type: {$response->getContentType()}");
echo $response->getBody();






