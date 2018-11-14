<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | 远程服务器和测试服务器不一样的配置
// +----------------------------------------------------------------------

return [
    define ("SERVER_HOST", "http://119.23.61.173"),

    // kiss访问的第三方服务器地址
    define ("KISS_OPEN_ACCESSKEY", "X3Z9hZA5mNdCuopy"),
    define ('REDIS_HOST', '119.23.61.173'),
    define ('REDIS_PASS', 'RfdK9lNIJYP8yzyk4&p%$yoN1W$'),       // 设置密码

    /** 支付成功微信的回调接口 */
    //define ('PAY_WX_NOTIFY', SERVER_HOST.'/steam-admin/public/index.php/pay/Wxapp/notify'),
	
    define ('QINIU_TOKEN_EXPIRES', 1800),

    define('SMSBAO_HTTP', 'http://www.smsbao.com/'),
    define('SMSBAO_USERNAME', 'iHAL9K'),
    define('SMSBAO_PASSWD', 'Zri5dqJyRX'),
    /** 注册验证码有效期 */
    define('VERIFYCODE_EXPIRE', 120),

        /** rsa密钥\公钥对 */
    define ('RSA_PUBLIC', 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDB436hbhw5hh+dj/g1GVBQq7vN22igWymLZHuOUMe8d+G7cd8a2w0AdYNFiNhbXMAbZLeUPP4DwgrlcXVjHDBQokg+DQqF6qnZm0MnCYyr2Gy61IuPG1wH1Tjuk2STcHbTga3SS2H9OJ8UKxZToPa3O3nag4HI/5lMJpqAuZGTFx/sGAIaTz444f9SyurzJRdmSAO5un/0TezFCXWC8iAQsugq/LlboBBRPH17x/iBMUXBVpe7Nrf3yBS9DE6Pgvbp8ciRlRFqavMC4OlVnlcCs1942XIl1y+WoYgy4zMkzfEiDxSr8JPrjCnjzahsyKYYy8UrLPBlhoawQ3VYZHCR Administrator@Y04YQ0DV66CPY1X'),      // 公钥
    
    define ('RSA_PRIVATE', '-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAweN+oW4cOYYfnY/4NRlQUKu7zdtooFspi2R7jlDHvHfhu3Hf
GtsNAHWDRYjYW1zAG2S3lDz+A8IK5XF1YxwwUKJIPg0Kheqp2ZtDJwmMq9hsutSL
jxtcB9U47pNkk3B204Gt0kth/TifFCsWU6D2tzt52oOByP+ZTCaagLmRkxcf7BgC
Gk8+OOH/Usrq8yUXZkgDubp/9E3sxQl1gvIgELLoKvy5W6AQUTx9e8f4gTFFwVaX
uza398gUvQxOj4L26fHIkZURamrzAuDpVZ5XArNfeNlyJdcvlqGIMuMzJM3xIg8U
q/CT64wp482obMimGMvFKyzwZYaGsEN1WGRwkQIDAQABAoIBAHEqAZ/g75JXiR5i
iEEdrDXZdjzZgcCOVLoqBL90wI9s/RB1jv1Susz4yYyNKZJxmSKj704TJ0M0Pz3Z
seUN79kwTrAA1pKA+2+p4lDnjvZB1HxbT7VQB6/+sEL3Pi2b547dUoG6Q9AS+y4/
U8Gt4jHiAeYa+WZCYMwEgAr/xEqvoK+LahJRI05d8fb8YAZQ8BblPh39rpyBDRp4
8AdxPNpXS+C1rZ2sNbpHITZVIgAhu7PgrxQeS5lDVay+g+HMrz+56Z17JVIpFBVk
RDTeIjzuwomVfY+0JTQNk4kIflvFW/DXJ62S846MR3C9iH+uVBnU8rrdDIE2UTEX
CVwQ2VECgYEA8QMgRpZpyHoBeDBSqQpjB/Y0WTuw0hnI4YrD/Fnbg/P2IixSx7ZY
ib4L9nt0awfxbBjMX909cVmvE3q7Fnj3Ye156OrPGCiob/PXcWZ4Bx3/psgPd1et
5md/LHPYc4W/oGIVrNiLcOjhP4T0PfOHAfTq65Oyf+8BirTLKiHC1PUCgYEAzfIr
XmVL04oeCaQ5a5RiXcT/WNjpaO58cJPP1xUYe1Wk1RfkMBDDqHMnImGPteOA4+8l
Bp++PD2A2SgAI+vJ9P76FwfHKpDwDb9AccoY0at5K87s/OfPNddQnuH4fQcfIRce
T94xWSg9vs0jLIFPDf8aGglO393okyBFa4nEC60CgYEAzLnxEsT2OruYv/WcYcdW
9A5pzVrtCquofh4X+SaDV4VGkSTBdLAqaWcsVkXsUHbA1ygBg3U1mjGqjIt2nNJI
ocDOC9JZ9JWk7uRdIBEA6FrNLWlH6gJq0Asedi45sXftnVl0PAz99f18SS6xImbZ
Sgo3+8DMoevujVqiMbnX/HkCgYAYM/uwzkrJA5RgIbK11+xvhLVVpbhYop5xRI3K
gyorZHdIq9Qfafj0lQPmYHzZLXF6WCKL0r4yqi+4VaEel5lWd5jAmCNp9zqfAvj5
5nxgN7y3z0u+tSd+9lz9LyVa3DlmVCc1z7EQ/0+yWr1lf5Tg2jghEzL30EaC8+n0
p24oyQKBgDkctK0GWGstj95aJWS8Vg3gR0nHsrVXe6X6BuYMejVOgJkzUdWC5MrI
+3XNzXlQRMR4lccE1AVMG6sPWQYt9joMtLl0AXnebZrXSUlbNBYraalV7uMYpfGl
rsqX1FRR1plbewSRYvAdJzjt17vxEs+NmwzfezVk7uDyjcJKshEF
-----END RSA PRIVATE KEY-----'),     // 密钥

    /** 系统账号 (用户充值、提现等均通过此账号抵扣, 永远不要修改它, 非常重要!!!!!!) */
    define ('SYS_PAY_ACCOUNT', '1520497307000391'),     // 系统支付账号
    define ('SYS_ADMIN_IMUSERID', '27'),                  // 系统imuserid
    define ("SYS_PAY_CORRECT", '1522740010000321'),     // 修正账号

    /** 渠道ID */
    define ('PAY_CHID_WEIXIN', '1'),    // 微信
    define ('PAY_CHID_ALIPAY', '2'),    // 支付宝
    define ('PAY_CHID_HUMAN', '3'),     // 人工充值

    define ('PAY_CHNAME_WEIXIN', '微信'),    // 微信
    define ('PAY_CHNAME_ALIPAY', '支付宝'),    // 支付宝
    define ('PAY_CHNAME_HUMAN', '人工充值'),     // 人工充值

];
