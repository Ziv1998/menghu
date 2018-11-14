<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | pay模块的配置
// +----------------------------------------------------------------------

return [
    /** 常用支付账号操作定义(pay_optype定义) */
    define ("PAYOP_CREATE", 1),              // 开户
    define ("PAYOP_DESTROY", 2),             // 销户
    define ("PAYOP_FROZEN", 3),              // 冻结
    define ("PAYOP_UNFROZEN", 4),            // 解冻
    define ("PAYOP_DEPOSITABLE", 5),         // 设置为可充值
    define ("PAYOP_UNDEPOSITABLE", 6),       // 设置为不可充值
    define ("PAYOP_UNKNOWN", 1000),          // 未知类型 

    /** 支付账号session */
    define ('SESSION_PAY_USERID', 'kipay_userid'),
    define ('SESSION_PAY_FLAG', 'kipay_flag'),
    define ('SESSION_PAY_ACCESSTOKEN', 'kipay_access_token'),

    /** 支付账号登录类型 */
    define ('PAY_F_COMPANY', '1'),           // 公司账号

    /** 冻结\解冻标志 */
    define ("PAYSIGN_FROZEN", 1),            // 被冻结
    define ("PAYSIGN_UNFROZEN", 0),          // 解冻状态   

    /** 充值\提现 */
    define ("PAYSIGN_CHARGE", 1),   // 充值
    define ("PAYSIGN_CASH", 2),     // 提现

    define ("PAY_CURRENCY", "K豆"),
];
