<?php

/**
 * Created by PhpStorm.
 * User: Imxz
 * Date: 2017/5/11
 * Time: 20:49
 */
class HttpUtils
{
    private static $http;
    private $goTo302 = false;
    private $openProxy = false;
    private $proxyHost;
    private $proxyPort;

    private function __clone()
    {
    }

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!(self::$http instanceof self)) {
            self::$http = new self;
        }
        return self::$http;
    }


    /**
     * @param $sets
     * @return Response
     *
     *
     *
     */
    public function get($url, $cookie = NULL, $userAgent = NULL, $header = NULL)
    {
        return $this->post($url, $cookie, NULL, $userAgent, $header);
    }


    /**
     * @param $postDataArr
     * @return string
     *
     * 将键值对数组型 postData 转换成 String
     *
     */
    private function getPostDataByArray($postDataArr)
    {
        //var_dump($postDataArr);
        $result = '';
        foreach ($postDataArr as $key => $content) {
            $result .= $key . '=' . $content . '&';
        }
        return substr($result, 0, strlen($result) - 1);
    }


    public function post($url, $cookie = NULL, $postDate = NULL, $userAgent = NULL, $header = NULL)
    {
        $curl = curl_init();

        $sets = array(
            'url' => $url,
            'cookie' => $cookie,
            'postData' => $postDate,
            'useragent' => $userAgent,
            'header' => $header,
        );
        $this->setCurl($curl, $sets); //设置请求信息
        $content = curl_exec($curl); //执行
        $info = curl_getinfo($curl);//获取请求头信息
        curl_close($curl); //结束请求
        $header = substr($content, 0, $info['header_size']);
        $body = substr($content, $info['header_size']);
        return new Response($header, $body, $info['http_code'], $info['content_type']);
    }


    private function setProxy($crul, $host, $port)
    {
        curl_setopt($crul, CURLOPT_PROXY, $host);
        curl_setopt($crul, CURLOPT_PROXYPORT, $port);
    }


    private function setCurl($curl, $sets)
    {


        curl_setopt($curl, CURLOPT_URL, $sets['url']); //设置 URL
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->goTo302); //跟随重定向
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 设置有返回流
        curl_setopt($curl, CURLOPT_HEADER, true); //返回请求头


        $sets['postData'] != NULL ? curl_setopt($curl, CURLOPT_POST, true) : ''; //有Post数据就开启 Post
        $sets['postData'] != NULL ? curl_setopt($curl, CURLOPT_POSTFIELDS, $this->getPostDataByArray($sets['postData'])) : ''; //设置请求数据
        $this->openProxy ? $this->setProxy($curl, $this->proxyHost, $this->proxyPort) : ''; // 设置代理
        $sets['cookie'] != NULL ? curl_setopt($curl, CURLOPT_COOKIE, $sets['cookie']) : ''; //设置cookie
        $sets['useragent'] != NULL ? curl_setopt($curl, CURLOPT_USERAGENT, $sets['useragent']) : ''; //设置useragent
        $sets['header'] != NULL ? curl_setopt($curl, CURLOPT_HTTPHEADER, $sets['header']) : ''; //设置header


    }

    public function openProxy($host, $port)
    {
        $this->openProxy = true;
        $this->proxyHost = $host;
        $this->proxyPort = $port;

    }

    public function closeProxy()
    {
        $this->openProxy = false;
    }

    public function setGoto302($YesOrNo)
    {
        $this->goTo302 = $YesOrNo;
    }

    public function gbk_to_utf8($str)
    {
        return mb_convert_encoding($str, 'utf-8', 'gbk');
    }

    public function utf8_to_gbk($str)
    {
        return mb_convert_encoding($str, 'gbk', 'utf-8');
    }

    public function decodeUnicode($str) //处理json_encode处理中文问题
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            create_function(
                '$matches',
                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
            ),
            $str);
    }

}


class Response
{
    private $header;
    private $body;
    private $http_code;
    private $content_type;
    private $cookie;

    public function __construct($header, $body, $http_code, $content_type)
    {
        $this->header = $header;
        $this->body = $body;
        $this->http_code = $http_code;
        $this->content_type = $content_type;
        preg_match_all('/Set-Cookie:(.*)[;\r\n]?/ix', $this->header, $cookie);
        $this->cookie = $cookie[1];
    }


    /*    public static function cookieMesh($newCookie, $oldCookie)
        {
            $list = array();
            foreach ($oldCookie as $item) {
                $item = preg_replace("/\s/", "", $item);
                $arr = str_split($item, ';');
                foreach ($arr as $item) {
                    $arr = str_split($item, '=');
                    if (count($arr) && isset($list[$item])){

                    }
                }

            }

        }*/


    public function getCookie()
    {
        return preg_replace("/\s/", "", implode(';', $this->cookie));
    }

    public function getCookieArr()
    {
        return $this->cookie;
    }


    public function getBody()
    {
        return $this->body;
    }

    public function getResponseCode()
    {
        return $this->http_code;
    }

    public function getContentType()
    {
        return $this->content_type;
    }


    public function getHeader()
    {
        return $this->header;
    }


    public function getHttpCode()
    {
        return $this->http_code;
    }


}
