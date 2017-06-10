<?php
/**
 * Created by PhpStorm.
 * User: Imxz
 * Date: 2017/5/11
 * Time: 22:49
 */

session_start();

require './utils/HttpUtils.php';

header('Content-Type: application/json; charset=UTF-8');

$stuId = isset($_POST['stuId']) ? $_POST['stuId'] : die('学号参数信息不正确!');
$idCard = isset($_POST['idCard']) ? $_POST['idCard'] : die('身份证参数信息不正确!');
$verifyCode = isset($_POST['verifyCode']) ? $_POST['verifyCode'] : die('验证码参数信息不正确!');


$http = HttpUtils::getInstance();

if (isset($_SESSION['cookie'])) {
    $cookie = $_SESSION['cookie'];
} else {
    echo '{"status":-1}';//没有Cookie
    die();
}

$url = "http://220.178.150.5:8082/cas/resetpasswordservletldap";

$postData = array(
    'uid' => $stuId,
    'idcard' => $idCard,
    'randnumber' => $verifyCode
);

$response = $http->post($url, $cookie, $postData);

if ($response->getHttpCode() == 200) {
    preg_match("(\\d{6})", $response->getBody(), $str);
    if (count($str) == 1) {
        echo '{"status":1,"result":"' . $str[0] . '"}';
    } else {
        echo '{"status":0}';
    }
} else {
    echo '{"status":0}';
}



