<?php
/**
 * Created by PhpStorm.
 * User: Imxz
 * Date: 2017/6/1
 * Time: 17:53
 */


require "./utils/HttpUtils.php";
$room = isset($_GET["room"]) ? $_GET["room"] : die("未提交房间号!");
$http = HttpUtils::getInstance();
$res = $http->post("http://220.178.150.5:8888/admin/sys!chaxun.action", NULL, array("fjmc" => $room));
$str = $res->getBody();
$regx = "/总余额：([\S\s]*?)([0-9.]*?)元/i";
$matchs = array();
preg_match($regx, $str, $matchs);
echo isset($matchs[0]) ? $matchs[0] : "房间号异常";