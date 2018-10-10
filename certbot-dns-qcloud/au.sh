#!/bin/bash
SHELLPATH=$(cd `dirname $0`; pwd)

#echo $SHELLPATH"/qcloud.php"

# 调用 PHP 脚本，自动设置 DNS TXT 记录。
# 第一个参数：域名
# 第二个参数：解析记录名称
# 第三个参数: letsencrypt 动态传递的 校验值 
#read -p "Press any key to continue." var
echo $CERTBOT_DOMAIN"_acme-challenge"
echo $CERTBOT_VALIDATION

sudo php  $SHELLPATH"/qcloud.php"  $CERTBOT_DOMAIN "_acme-challenge"  $CERTBOT_VALIDATION

# DNS TXT 记录刷新时间
echo "wait 300s for record refresh"
sleep 300

echo "END"
###
