<?php
### API文档https://cloud.tencent.com/document/api/302/4031
### example: php qcloud.php  "test.cn" "subdomain" "value"

error_reporting(E_ALL);
date_default_timezone_set("GMT");

define("secretId", "");
define("secretKey", "");

### 配合 cerbot 运行 

echo $argv[1] . "-" . $argv[2] . "-" . $argv[3]."\n";
$domain = $argv[1];
$name = $argv[2];
$value = $argv[3];

$obj = new QcloudDns(secretId, secretKey);
$data = $obj->ListRecords($domain);
$data = $data["data"]["records"];
if (is_array($data)) {
      foreach ($data as $v) {
           if ($v["name"] == $name) {
               $obj->DeleteRecord($domain, $v["id"]);
           }
      }
} 
print("result:");
print_r($obj->CreateRecord($domain, $name, "TXT", $recordLine = '默认', $value, $ttl = 600, $mx = 0));

############ Class 定义

class QcloudDns {
    private $secretId = null;
    private $secretKey = null;

    public function __construct($secretId, $secretKey) {
        $this->secretId = $secretId;
        $this->secretKey = $secretKey;
    }

    public function ListRecords($domain) {
        $requestParams = array(
            "Action" => "RecordList",
            "domain" => $domain
        );
        $val = $this->send(null, $requestParams, "GET");
        return $this->out($val);
    }


    public function UpdateRecord($domain, $recordId, $subDomain, $recordType, $recordLine = '默认', $value, $ttl = 600, $mx = 0){
        $requestParams = array(
            "Action" => "RecordModify",
            "domain" => $domain,
            "recordId" => $recordId,
            "subDomain" => $subDomain,
            "recordType" => $recordType,
            "recordLine" => $recordLine,
            "value" => $value,
            "ttl" => $ttl,
            "mx" => $mx
        );
        $val = $this->send(null, $requestParams, "GET");
        return $this->out($val);
    }

    public function DeleteRecord($domain, $recordId) {
        $requestParams = array(
            "Action" => "RecordDelete",
            "domain" => $domain,
            "recordId" => $recordId
        );
        $val = $this->send(null, $requestParams, "GET");
        return $this->out($val);
    }

    public function CreateRecord($domain, $subDomain, $recordType, $recordLine = '默认', $value, $ttl = 600) {
        $requestParams = array(
            "Action" => "RecordCreate",
            "domain" => $domain,
            "subDomain" => $subDomain,
            "recordType" => $recordType,
            "recordLine" => $recordLine,
            "value" => $value,
            "ttl" => $ttl
        );
        $val = $this->send(null, $requestParams, "GET");
        return $this->out($val);
    }

    private function send($uri, $requestParams, $method) {
        $url = "cns.api.qcloud.com/v2/index.php";
        if(!is_null($uri)){
            $url = $url."/".$uri;
        }

        $publicParams = array(
            "Timestamp" => time(),
            "Nonce" => rand(),
            "SecretId" => $this->secretId,
            "SignatureMethod" => "HmacSHA256"
        );
        $params = array_merge($publicParams, $requestParams);
        ksort($params);
        
        if($method ==="GET" && !empty($params)){
            $uri = http_build_query($params);
            $uri = urldecode($uri);
            $url = $url."?".$uri;
        }

        $srcStr = 'GET'.$url;
        $signStr = base64_encode(hash_hmac('sha256', $srcStr, $this->secretKey, true));
        $url = 'https://'.$url.'&Signature='.$this->percentEncode($signStr);
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
        // curl_setopt($ch, CURLOPT_USERPWD, $this->username.":".$this->token);
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
