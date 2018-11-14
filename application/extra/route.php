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
return[
    // 定义路由地址
    define('ROUTE_LIST',json_encode(array(
        // 授权相关
        'KISS_LOG_URL_REFRESHTOKEN' => 'sws-admin/public/index.php/oauthlog/oauth/refreshToken',
        'KISS_LOG_URL_GETTOKEN'=>'sws-admin/public/index.php/oauthlog/oauth/sendToken',             // 授权获取token
        'KISS_SERVEROAUTH' => 'sws-admin/public/index.php/oauthlog/oauth/serverlogin',              // 服务器直接授权登录

        // 数据接口
        'KISS_LOG_URL_GETIMUSERID'=>'sws-admin/public/index.php/oauthlog/oauth/sendImuserid',
        'KISS_GETBASICINFO' => 'sws-admin/public/index.php/oauthlog/oauth/sendInfo',                // 获取个人信息
        'KISS_CLASSES_READ' => 'sws-admin/public/index.php/oauthlog/classes/read',                  // 读取班级列表   
        'KISS_CLASSES_READREASON' => 'sws-admin/public/index.php/oauthlog/classes/readReason',  // 读取缺勤原因
        'KISS_STUDENT_READ' => 'sws-admin/public/index.php/oauthlog/student/read',           // 读取学生列表
        'KISS_CLASSES_ABSENCE' => 'sws-admin/public/index.php/oauthlog/classes/absence',      // 读取班级考勤
        'KISS_CLASSES_ABSENCESET' => 'sws-admin/public/index.php/oauthlog/classes/absenceset',      // 请假
        'KISS_CLASSES_ABSENCESIGN' => 'sws-admin/public/index.php/oauthlog/classes/addsign',      // 补签到
        'KISS_CLASSES_READTYPE' => 'sws-admin/public/index.php/oauthlog/classes/readtype',      // 读取班级类型
        'KISS_CLASSES_READCLASSTYPE' => 'sws-admin/public/index.php/oauthlog/classes/readclasstype',// 读取指定班级的类型
        'KISS_PARENTS_ABOUTCLASS' => 'sws-admin/public/index.php/oauthlog/parents/aboutclass', // 读取指定指定班级的家长列表
        'KISS_MOMENTS_READ' => 'sws-admin/public/index.php/oauthlog/student/inclass',            // 读取孩子朋友圈
        'KISS_SCHOOL_READ' => 'sws-admin/public/index.php/open/Monitor/readSchool'      // 读取学校列表
   ))),
];