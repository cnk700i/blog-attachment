# 说明
letsencrypt通配符域名续期脚本：
- name.com
certbot-dns-namecom
- 腾讯云
certbot-dns-qcloud

# 使用方法
- 安装certbot工具
`git clone https://github.com/certbot/certbot`
- certbot目录下执行
`certbot-auto renew --cert-name [域名] --manual-auth-hook /[脚本目录]/au.sh`
