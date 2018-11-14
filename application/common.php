<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
error_reporting(E_ALL);

return[
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    //定义根目录
    define('ROUTE_DIR','menghu-admin'),

    //定义类型常量

    //session(以tripadmin开头)
    define ('SESSION_ROOT', 'steam'),
    define ('SESSION_PASSWD', SESSION_ROOT.'_password'),
    define ('SESSION_USERID', SESSION_ROOT.'_userid'),
    define ('SESSION_FLAG', SESSION_ROOT.'_flag'),
    define ('SESSION_ACCESSTOKEN', SESSION_ROOT.'_access_token'),     // 用于访问接口时使用的临时令牌(每次登录有效)
    define ('SESSION_REFRESHTOKEN', SESSION_ROOT.'_refresh_token'),   // 用于刷新token状态(数据库中存储的长期令牌)

    define ('CLIENT_ACCESS_TOKEN', 'access_token'),
    define ('CLIENT_SESSION_ID', 'session_id'),

    // 服务器Set-Cookie的参数字段名
    define ('COOKIE_PARAM', 'PHPSESSID='),

    // 自动备份文件大小限制
    define ('AUTOBACKUP_LIMITBYTE', 50000000),

    //定义文件类型
    define ('PICTURE', 'picture'),
    define ('VIDEO', 'video'),
    define ('ICON', 'icon'),
    define('TEXT','text'),
    define ('UNKNOWN', 'unknown'),
    define ('FILE_TYPE', json_encode(
            array(
                ICON   =>  ['size'=>3000000,'ext'=>'jpg,png,gif'],
                PICTURE   =>  ['size'=>3000000,'ext'=>'jpg,png,gif,jpeg'],
                VIDEO     =>  ['size'=>50000000,'ext'=>'mp4'],
                TEXT      =>['size'=>100000,'ext'=>'txt,doc,excel,docx'],
                UNKNOWN   =>  ['size'=>0,'ext'=>'png']
            )
        )
    ),

    //定义分隔符
    define ("SPLIT_SIGN", '#'),

    // 性别定义
    define('S_BOY', 1),     // 男孩
    define('S_GIRL', 0),    // 女孩

    //定义token
    define('SQL_KISS_TOKEN','kiss_token'),

    // +----------------------------------------------------------------------
    // | 缓存常量表
    // +----------------------------------------------------------------------
    define ('REDIS_APP', 'demo'),
    define ('REDIS_EXPIRE', '60'),
    define ('LOG_EXPIRE','3600'),
    define ('REDIS_TYPE', json_encode(
        array (
            // 应用名
            'global'       =>  [
            ],

            // 登录模块
            'login'     =>  [
                "a_u" => "auth"
            ],

            // 活动模块
            'cache'  =>  [
                'activity' => 'activity:list',
            ],

            //日志模块
            'log' => [
              'auth' => 'auth:list',
            ],

            'valid' => [    
                'a_u' => 'access_token:updated'         // access_token是否需要刷新(有效则不需要刷新)
            ],

            //注册验证码
            'register' => [
                'code' => 'verifycode',
            ],

            // 支付订单缓存
            'pay' => [
                'o_r' => 'order',            // 支付订单
            ],

            "api" => [
                'a_p' => 'cache',            // 应用接口缓存
            ]
        )
    )),
];