<?php
/**
 * Created by PhpStorm.
 * User: Imxz
 * Date: 2017/5/23
 * Time: 9:56
 */


require 'utils/HttpUtils.php';
require 'utils/simple_html_dom.php';
require 'class/Score.class.php';
header('Content-Type: application/json; charset=UTF-8'); // 编码

$userName = isset($_POST['user']) ? $_POST['user'] : die('帐号参数信息不正确!');
$passWord = isset($_POST['pwd']) ? $_POST['pwd'] : die('密码参数信息不正确!');
$nowYear = date('Y') - 1;


if (date('m') >= 6 && date('m') <= 7) {
    $nowXQ = 1;
} else if (date('m') >= 1 && date('m') <= 2) {
    $nowXQ = 0;
} else {
    die('{"status":-1}'); //不在查分时间范围
}

$loginUrl = "http://220.178.150.5:8082/cas/login?service=http%3A%2F%2F220.178.150.5%3A8082%2Fc%2Fportal%2Flogin";

$http = HttpUtils::getInstance();

//$http->openProxy("127.0.0.1", '8888');
$http->setGoto302(true);

$res = $http->get($loginUrl);

$document = new simple_html_dom();
$document->load($http->gbk_to_utf8($res->getBody()));
$lt = $document->find('input', 4)->value; //获取登录随机的lt
$document->clear(); //清理内存

$post = array(
    'username' => $userName,
    'password' => $passWord,
    'logintype' => 'cas',
    '_eventId' => 'submit',
    'lt' => $lt
); //构造post数据



$res = $http->post($loginUrl, $res->getCookie(), $post); // 登录完成 获取返回结果

$retBack = $res->getBody();

if (strpos($retBack, "我同意") == true) {
    $http->get('http://220.178.150.5:8082/c/portal/update_terms_of_use?doAsUserId=&referer=%2Fc%2Fportal%2Flayout%3FdoAsUserId%3D',$res->getCookie());

    $loginUrl = "http://220.178.150.5:8082/cas/login?service=http%3A%2F%2F220.178.150.5%3A8082%2Fc%2Fportal%2Flogin";
    $res = $http->get($loginUrl);
    $document = new simple_html_dom();
    $document->load($http->gbk_to_utf8($res->getBody()));
    $lt = $document->find('input', 4)->value; //获取登录随机的lt
    $document->clear(); //清理内存
    $post = array(
        'username' => $userName,
        'password' => $passWord,
        'logintype' => 'cas',
        '_eventId' => 'submit',
        'lt' => $lt
    ); //构造post数据

    $res = $http->post($loginUrl, $res->getCookie(), $post); // 登录完成 获取返回结果
    $retBack = $res->getBody();
};

if (strpos($retBack, "欢迎您") == false) {
    die('{"status":0}'); //登录失败!
} else {
    preg_match('/欢迎您：(\S{2,4})<br>\s{0,}帐号：(\d{8,10})/iu', $res->getBody(), $info);
    if (count($info) == 3) {
        $stuName = $info[1];
        $stuId = $info[2];
    } else {
        $stuName = "未知";
        $stuId = "未知";
    }
};

$res = $http->get('http://220.178.150.5:8083/jwweb/sys/pageXfer.aspx?url=../xscj/Stu_cjfb.aspx&usertype=STU000', $res->getCookie()); //使用返回的Cookie载入教务系统

$tempArr = $res->getCookieArr();

$cookie = trim($tempArr[1]);

$fbUrl = "http://220.178.150.5:8083/jwweb/xscj/Stu_cjfb_rpt.aspx";
$post = array(
    'sel_xq' => $nowXQ,
    'SelXNXQ' => '2',
    'sel_xn' => $nowYear
);
$back = $http->post($fbUrl, $cookie, $post); // 获取分数结果

$back = $http->gbk_to_utf8($back->getBody()); // GBK转UTF-8 返回主体


$document = new simple_html_dom();
$document->load($back);
$all = $document->find('#ID_Table tbody tr');

$jsonArr = array();

for ($i = 0; $i < count($all) - 2; $i++) {
    $score = new Score();
    $jsonArr[] = $score;
    $score->subject = preg_replace('/\[\d{1,}\]/i', "", trim($all[$i]->children(1)->plaintext));
    if ($all[$i]->children(6)->plaintext == '√') {
        $score->score = "优秀 [100,90]分";
        continue;
    };

    if ($all[$i]->children(7)->plaintext == '√') {
        $score->score = "良好 (90,80]分";
        continue;
    };

    if ($all[$i]->children(8)->plaintext == '√') {
        $score->score = "中等 (80,70]分";
        continue;
    };

    if ($all[$i]->children(9)->plaintext == '√') {
        $score->score = "及格 (70,60]分";
        continue;
    };

    if ($all[$i]->children(10)->plaintext == '√') {
        $score->score = "不及格 (60,0]分";
        continue;
    };
}
$document->clear();
echo '{"status":1,"stuName":"' . $stuName . '","stuId":"' . $stuId . '","result":' . $http->decodeUnicode(json_encode($jsonArr)) . '}';

