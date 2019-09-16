<?php
error_reporting(E_ALL);
date_default_timezone_set("GMT");

//https://www.name.com/account/settings/api
define("username", "");
define("token", "");


########## 配合 cerbot 运行 

echo $argv[1] . "-" . $argv[2] . "-" . $argv[3];

$obj = new NamecomDns(username, token, $argv[1]);
$data = $obj->ListRecords();
$data = $data["records"];
if (is_array($data)) {
      foreach ($data as $v) {
           if ($v["host"] == $argv[2]) {
               $obj->DeleteRecord($v["id"]);
           }
      }
} 
print("result:");
print_r($obj->CreateRecord("TXT", $argv[2],$argv[3]));

############ Class 定义

class NamecomDns {
    private $username = null;
    private $token = null;
    private $DomainName = null;


    public function __construct($username, $token, $domain) {
        $this->username = $username;
        $this->token = $token;
        $this->DomainName = $domain;
    }

    public function ListRecords() {
        $val = $this->send(null, array(), "GET");
        return $this->out($val);
    }


    public function UpdateRecord($id, $type, $host, $answer){
        $requestParams = array(
            "host" => $host,
            "type" => $type,
            "answer" => $answer,
        );
        $val = $this->send($id, $requestParams, "PUT");
        return $this->out($val);
    }

    public function DeleteRecord($id) {
        $val = $this->send($id, array(), "DELETE");
        return $this->out($val);
    }

    public function CreateRecord($type, $host, $answer) {
        $requestParams = array(
            "host" => $host,
            "type" => $type,
            "answer" => $answer,
        );
        $val = $this->send(null, $requestParams, "POST");
        return $this->out($val);
    }

    private function send($uri, $requestParams, $method) {
        $publicParams = array();
        $params = array_merge($publicParams, $requestParams);
        $url = "https://api.name.com/v4/domains/".$this->DomainName."/records";
        if(!is_null($uri)){
            $url = $url."/".$uri;
        }
        if($method ==="GET" && !empty($params)){
            $uri = http_build_query($params);
            $url = $url."?".$uri;
        }
        print_r($url);
        return $this->curl($url, $method, $params);
    }

    private function percentEncode($value = null){
        $en = urlencode($value);
        $en = str_replace("+", "%20", $en);
        $en = str_replace("*", "%2A", $en);
        $en = str_replace("%7E", "~", $en);
        return $en;
    }

    private function curl($url, $method, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        // curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, 1); // 发送一个常规的Post请求
        // curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($params)); // Post提交的数据包
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_USERPWD, $this->username.":".$this->token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if($method==="POST" || $method === "PUT"){
	    print("params:");	
	    print_r($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        $result = curl_exec ($ch);
        curl_close($ch);
        return $result;
    }

    private function out($msg) {
        return json_decode($msg, true);
    }
}
?>
