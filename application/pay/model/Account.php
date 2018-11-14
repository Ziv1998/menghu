<?php
/**
 * Account 支付账户外部交易日志 (提现、充值流水)
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/5/15
 * Time: 14:01
 */

namespace app\pay\model;

use think\Db;
use think\Model;
use app\common\model\Admin;
use app\common\model\Access;
use app\pay\common\MoneyTools;
use app\pay\model\Verify as Verify_Model;
use app\pay\model\User as User_Model;
use app\pay\common\LockTools;

class Account extends Model
{
    /**
     * 随机生成外部交易凭证 (后插入的编号比先插入的编号大)
     */
    private static function _buildaccountcode ($userid, $ranNum=4) {
        // 10位时间戳
        $curtime = time();

        // 用户ID后4位, 不够4位补0
        $leaveNum = $userid%10000;
        $num = str_pad($leaveNum, 4, "0", STR_PAD_LEFT);
        $lastfournum = $num;

        // 两位随机数
        $rightNum = Access::getRand ($ranNum);

        $code = $curtime.$lastfournum.$rightNum;
        return $code;
    }

    /**
     * 随机生成内部交易凭证
     */
    private static function _buildtradecode ($userid, $ranNum=6) {
        return self::_buildaccountcode ($userid, $ranNum);
    }

    /**
     * 检查系统支付账号是否存在
     */
    private static function _checkSysPayuser () {
        $payuserid = SYS_PAY_ACCOUNT;
        $sql = sprintf ("select * from pay_company_user where payuserid='%s' limit 1", $payuserid);
        $ret = Db::query ($sql);
        if (count ($ret) > 0) {
            return true;
        }
        return false;
    }

    /**
     * 金额加密
     */
    private static function _encrypt ($data) {
        return MoneyTools::getInstance ()->encrypt ($data);
    }

    /**
     * 金额解密
     */
    private static function _decrypt ($data) {
        return MoneyTools::getInstance ()->decrypt ($data);
    }

    /**
     * 检查交易账户是否被冻结
     */
    public static function _checkIfFrozen ($payeeid) {
        // 检查账号是否处于冻结状态
        if (Verify_Model::checkIfFrozen ($payeeid)) {
            // 处于冻结状态
            Access::Respond (0, array(), sprintf ('账号%s已经被冻结, 解冻请联系管理员', $payeeid));
        }
        return true;
    }

    /**
     * 检查交易流水的连续性, 如果不连续则直接冻结账户
     */
    public static function _checkTrade ($payeeid) {
        // 检查系统账号流水是否连续
        if (!Verify_Model::checkTrade ($payeeid)) {
            // 冻结账户, 管理员默认冻结
            User_Model::frozen (SYS_ADMIN_IMUSERID, $payeeid, "流水存在异常");
            Access::Respond (0, array(), sprintf ('账号%s流水存在异常, 请联系管理员', $payeeid));
        }
        return true;
    }

    /** 
     * 不能给自己充值
     */
    private static function _checkIfSyspayId ($payeeid) {
        // 判断到payeeid是系统账号则直接报错
        if ($payeeid === SYS_PAY_ACCOUNT) {
            Access::Respond (0, array(), sprintf ('%s为系统支付号, 不支持充值、提现等操作', $payeeid));
        }
        return true;
    }

    /**
     * 外部记账
     * @param $arr
     * @param string $goodalias 商品索引
     * @return bool 记账是否成功
     */
    private static function _account ($arr, $goodalias=PAY_GOODID_CHARGE) {
        $sys_pay_account = SYS_PAY_ACCOUNT;
        // 判断是否给系统账号充值、提现等
        self::_checkIfSyspayId ($arr['payeeid']);

        // 检查系统账号是否存在
        // if (!self::_checkSysPayuser ()) {
        //    // 系统账号不存在
        //    Access::Respond (0, array(), '系统支付账号不存在或者已经被冻结, 请联系管理员');
        // }
        // 检查账号是否处于冻结状态
        //self::_checkIfFrozen ($arr['payeeid']);
        // 检查账号是否处于冻结状态
        //self::_checkIfFrozen ($sys_pay_account);
        // 检查系统账号流水是否连续
        //self::_checkTrade ($sys_pay_account);
        // 检查账号流水是否连续
        //self::_checkTrade ($arr['payeeid']);
        // 付款人备注
        //$arr['payname'] = self::_getNameWithPayuserid ($arr['payeeid']);
        /** 开始事务 */
        Access::Log ("log", "1");
        Db::startTrans ();
        Access::Log ("log", "2");
        // 外部记账
        $accountcode = self::_externalAccount ($arr);
        Access::Log ("log", "3");
        if ($accountcode <= 0) {
            Db::rollback ();
            return false;
        }

        /** 判断商品类型 */
        // if (self::checkIfChargeType($goodalias)) {
        //     // 充值类型
        //     $type = PAY_TRADETYPE_CHARGE;
        // } else if (self::checkIfCashType ($goodalias)) {
        //     // 提现类型
        //     $type = PAY_TRADETYPE_CASH;
        // } else if (self::checkIfConsumeType($goodalias)) {
        //     // 消费类型 (基础会员、星级会员)
        //     $type = PAY_TRADETYPE_CONSUME;
        // } else {
        //     // 未知类型
        //     Db::rollback();
        //     return false;
        // }

        // 内部记账
        // $arr['accountcode'] = $accountcode;         // 存储accoutcode
        // $arr['trade_money'] = $arr['cn_money'];     // 存储trade_money
        // if ($type == PAY_TRADETYPE_CHARGE) {
        //     // 充值
        //     $arr['remitteeid'] = $arr['payeeid'];   // 收款人
        //     $arr['payeeid'] = SYS_PAY_ACCOUNT;      // 付款人
        //     $arr['opid'] = PAY_TRADETYPE_CHARGE;
        // } else if ($type == PAY_TRADETYPE_CASH) {
        //     // 提现
        //     $arr['remitteeid'] = SYS_PAY_ACCOUNT;   // 收款人
        //     $arr['opid'] = PAY_TRADETYPE_CASH;      
        // } else if ($type == PAY_TRADETYPE_CONSUME) {
        //     /* 购买服务, 先充值, 后消费 */
        //     // 充值
        //     $arr['remitteeid'] = $arr['payeeid'];   // 收款人
        //     $arr['payeeid'] = SYS_PAY_ACCOUNT;      // 付款人
        //     $arr['opid'] = PAY_TRADETYPE_CHARGE;    // 充值
        //     $ret = self::_internalAccount ($arr);
        //     if ($ret <= 0) {
        //         Db::rollback ();
        //         return false;
        //     }

        //     // 消费
        //     $arr['payeeid'] = $arr['remitteeid'];   // 付款人
        //     $arr['remitteeid'] = SYS_PAY_ACCOUNT;   // 收款人
        //     $arr['opid'] = PAY_TRADETYPE_CONSUME;    // 消费
        // } else {
        //     // 不支持此记账方式
        //     Db::rollback ();
        //     return false;
        // }

        // $ret = self::_internalAccount ($arr);
        // if ($ret <= 0) {
        //     Db::rollback ();
        //     return false;
        // }

        // 必要的日志保存

        /** 完成事务 */
        Db::commit ();
        return true;
    }

    /**
     * 内部记账
     */
    private static function _internalAccount ($arr) {
        // 必须项
        $payeeid=Access::IfKeyExists($arr, 'payeeid');  // 付款者id
        if ($payeeid == null) { return false; }

        $remitteeid=Access::IfKeyExists($arr, 'remitteeid');    // 收款者id
        if ($remitteeid == null) { return false; }

        $opid=Access::IfKeyExists($arr, 'opid');        // 交易类型id
        if ($opid == null) { return false; }

        $visitor_info = Access::LoadVisitorInfo ();
        $ip = $visitor_info['http_x_real_ip'];

        $accountcode=Access::IfKeyExists($arr, 'accountcode');  // 外部交易凭证

        $other=Access::IfKeyExists($arr, 'trade_other');  // 交易备注

        $trade_money=Access::IfKeyExists($arr, 'trade_money');  // 交易金额
        if ($trade_money == null) { return false; }

        // 校验金钱格式
        MoneyTools::check ($trade_money);

        // 检查付款人处于冻结状态
        self::_checkIfFrozen ($payeeid);

        // 检查收款人是否处于冻结状态
        self::_checkIfFrozen ($remitteeid);

        // 检查收款人流水是否连续
        self::_checkTrade ($remitteeid);

        // 检查付款人是否连续
        self::_checkTrade ($payeeid);

        // 付款人备注
        $payname = self::_getNameWithPayuserid ($payeeid);

        // 收款人备注
        $remitteename = self::_getNameWithPayuserid ($remitteeid);

        // 根据payeeid和remiteeid生成tradecode
        $tmp_id = $payeeid+$remitteeid;
        $tradecode = self::_buildtradecode ($tmp_id);

        /*
         * 此处开始做并发过滤, 多个同付款人\收款人执行进程, 则后面的直接返回false
         * 作用: 避免流水出现不连续情况
         * 提示: 返回系统繁忙, 请稍后再试
         * 加锁之后副作用: 并发下, 用户支付成功且系统有外部支付记录, 但是内部交易流水没改变即系统余额没变动
         */
        if (!LockTools::L(REDIS_LOCK_TRADE, REDIS_LOCK_EXPIRE)) {
            // 并发交易出现
            Access::Respond (0, array(), '系统繁忙,请稍后再试');
        }

        // 获取payee的最新一条余额, 减去交易金额做为新的余额
        $payee_balance = self::_getLastMoney ($payeeid);
        if (!isset ($payee_balance)) {
            return false;
        }
        $payee_balance-=$trade_money;

        // 获取remittee的最新一条余额, 加上交易金额为新的余额
        $remittee_balance = self::_getLastMoney ($remitteeid);
        if (!isset ($remittee_balance)) {
            return false;
        }
        $remittee_balance+=$trade_money;

        // 金额加密
        $en_trade_money = self::_encrypt ($trade_money);
        $en_payee_balance = self::_encrypt ($payee_balance);
        $en_remittee_balance = self::_encrypt ($remittee_balance);

        if (isset($accountcode) && $accountcode != null) {
            $sql = sprintf ("
                insert into pay_trade (tradecode, payeeid, payee_money, remitteeid, remittee_money, trade_money, accountcode, ip, opid, payname, remitteename, other)
                values ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s, '%s', '%s', '%s')
            ", $tradecode, $payeeid, $en_payee_balance, $remitteeid, $en_remittee_balance, $en_trade_money, $accountcode, $ip, $opid, $payname, $remitteename, $other);
        } else {
            $sql = sprintf ("
                insert into pay_trade (tradecode, payeeid, payee_money, remitteeid, remittee_money, trade_money, ip, opid, payname, remitteename, other)
                values ('%s', '%s', '%s', '%s', '%s', '%s', '%s', %s, '%s', '%s', '%s')
            ", $tradecode, $payeeid, $en_payee_balance, $remitteeid, $en_remittee_balance, $en_trade_money, $ip, $opid, $payname, $remitteename, $other);
        }
        $ret = Db::execute ($sql);

        LockTools::UL(REDIS_LOCK_TRADE);    // 解锁
        return $ret;
    }

    /**
     * 根据payuserid得到名字备注(用户量大可以直接设置缓存)
     */
    public static function _getNameWithPayuserid ($payuserid) {

        // 按用户量排位置, 依次为公司、家长、孩子、老师、园长
        $other = "";

        // 判断是否为公司账号
        $sql = sprintf ("
            select name as username  
            from pay_company_user 
            where payuserid=%s limit 1
        ", $payuserid);
        $ret = Db::query ($sql);
        if (count ($ret) > 0) {
            $other = sprintf ("%s(%s)", $ret[0]['username'], "商户");
            return $other;
        }

        // 判断是否为家长账号
        $sql = sprintf ("
            select concat(TP.surname,TP.username) as username 
            from tb_parents as TP, 
            (select id as imuserid from tb_imuser where payuserid=%s limit 1) as TT 
            where TT.imuserid=TP.imuserid limit 1
        ", $payuserid);
        $ret = Db::query ($sql);
        if (count ($ret) > 0) {
            $other = sprintf ("%s(%s)", $ret[0]['username'], "家长");
            return $other;
        }

        // 判断是否为孩子账号
        $sql = sprintf ("
            select concat(TS.surname,TS.username) as username 
            from tb_student as TS, 
            (select id as imuserid from tb_imuser where payuserid=%s limit 1) as TT 
            where TT.imuserid=TS.imuserid limit 1
        ", $payuserid);
        $ret = Db::query ($sql);
        if (count ($ret) > 0) {
            $other = sprintf ("%s(%s)", $ret[0]['username'], "孩子");
            return $other;
        }

        // 判断是否为老师账号
        $sql = sprintf ("
            select concat(TE.surname,TE.username) as username 
            from tb_teacher as TE, 
            (select id as imuserid from tb_imuser where payuserid=%s limit 1) as TT 
            where TT.imuserid=TE.imuserid limit 1
        ", $payuserid);
        $ret = Db::query ($sql);
        if (count ($ret) > 0) {
            $other = sprintf ("%s(%s)", $ret[0]['username'], "老师");
            return $other;
        }

        // 判断是否为园长账号
        $sql = sprintf ("
            select concat(TM.surname,TM.name) as username 
            from tb_school_master as TM, 
            (select id as imuserid from tb_imuser where payuserid=%s limit 1) as TT 
            where TT.imuserid=TM.imuserid limit 1
        ", $payuserid);
        $ret = Db::query ($sql);
        if (count ($ret) > 0) {
            $other = sprintf ("%s(%s)", $ret[0]['username'], "园长");
            return $other;
        }
    }

    /**
     * 判断用户是否存在
     */
    private static function _checkPayuserIfExists ($payuserid) {
        $payuserid=Access::SQLInjectDetect($payuserid);

        $sql = sprintf ("select * from pay_user where payuserid='%s' limit 1", $payuserid);
        $ret = Db::query ($sql);
        if (count ($ret) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 外部记账
     */
    private static function _externalAccount ($arr) {
        // 必须项
        Access::Log ("log", $arr['payeeid']);
        $payeeid=Access::IfKeyExists($arr, 'payeeid');
        if ($payeeid == null) { return false; }
        Access::Log ("log", "OK2");
        // 充值对象备注
        $payname=Access::IfKeyExists($arr, 'payname');
        if ($payname == null) { return false; }
        Access::Log ("log", "OK3");
        $cn_money=Access::IfKeyExists($arr, 'cn_money');        // 需要加密
        if ($cn_money == null) { return false; }
        Access::Log ("log", "OK4");
        // 检验钱的格式是否正确
        MoneyTools::check ($cn_money);

        $cn_charge=Access::IfKeyExists($arr, 'cn_charge', 0);      // 需要加密
        
        // 校验金钱格式
        MoneyTools::check ($cn_charge);

        // 渠道详细
        $cn_code=Access::IfKeyExists($arr, 'cn_code');              // 渠道支付凭证
        $cn_msg=Access::IfKeyExists($arr, 'cn_msg');

        // 渠道id
        $cn_id=Access::IfKeyExists($arr, 'cn_id');                 // 渠道id
        $cn_name=Access::IfKeyExists($arr, 'cn_name');              // 渠道名(微信、支付宝、人工充值)

        $other=Access::IfKeyExists($arr, 'other');
        $cn_appid=Access::IfKeyExists($arr, 'cn_appid');
        $cn_mch_id=Access::IfKeyExists($arr, 'cn_mch_id');
        $cn_openid=Access::IfKeyExists($arr, 'cn_openid');
        $cn_nonce_str=Access::IfKeyExists($arr, 'cn_nonce_str');
        $cn_sign=Access::IfKeyExists($arr, 'cn_sign');
        $cn_sign_type=Access::IfKeyExists($arr, 'cn_sign_type');
        $cn_type=Access::IfKeyExists($arr, 'cn_type');
        $cn_result_code=Access::IfKeyExists($arr, 'cn_result_code');
        $cn_err_code=Access::IfKeyExists($arr, 'cn_err_code');
        $cn_trade_no=Access::IfKeyExists($arr, 'cn_trade_no');
        $cn_time=Access::IfKeyExists($arr, 'cn_time');

        $account_type=Access::IfKeyExists($arr, 'type');        // 支付类型 (充值、提现)
        if ($account_type == null) {
            $account_type = PAYSIGN_CHARGE;
        }

        // 生成记账凭证
        $accountcode = self::_buildaccountcode ($payeeid);

        $sql = sprintf ("
            insert into pay_account (
                accountcode, payeeid, cn_code, cn_id, cn_name, cn_msg, cn_money, cn_charge, other, payname,
                cn_appid, cn_mch_id, cn_openid, cn_nonce_str, cn_sign, cn_sign_type, cn_type, cn_result_code,
                cn_err_code, cn_trade_no, cn_time, type
            ) values ('%s', '%s', '%s', %s, '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s)
        ", $accountcode, $payeeid, $cn_code, $cn_id, $cn_name, $cn_msg, $cn_money, $cn_charge, $other, $payname,
        $cn_appid, $cn_mch_id, $cn_openid, $cn_nonce_str, $cn_sign, $cn_sign_type, $cn_type, $cn_result_code, $cn_err_code,
        $cn_trade_no, $cn_time, $account_type);
        Access::Log ("log", $sql);
        $ret = Db::execute ($sql);
        if ($ret > 0) {
            return $accountcode;
        }
        return false;
    }

    /**
     * 转账
     */
    public static function transfer ($arr) {
        // 必须项
        $payeeid=Access::IfKeyExists($arr, 'payeeid');  // 付款者id
        if ($payeeid == null) { return false; }

        $remitteeid=Access::IfKeyExists($arr, 'remitteeid');    // 收款者id
        if ($remitteeid == null) { return false; }

        $trade_money=Access::IfKeyExists($arr, 'trade_money');  // 交易金额
        if ($trade_money == null) { return false; }

        $opid=Access::IfKeyExists($arr, 'opid');            // 转账类型

        $trade_other = Access::IfKeyExists($arr, 'trade_other');       // 转账备注

        // 校验金钱格式
        MoneyTools::check ($trade_money);

        // 检查付款人处于冻结状态
        self::_checkIfFrozen ($payeeid);

        // 检查收款人是否处于冻结状态
        self::_checkIfFrozen ($remitteeid);

        // 检查收款人流水是否连续
        self::_checkTrade ($remitteeid);

        // 检查付款人是否连续
        self::_checkTrade ($payeeid);

        if (!isset ($opid)) {
            $opid = PAY_TRADETYPE_TRANSFER;
        }

        // 判断opid是否存在 TODO
        $arr['opid'] = $opid;
        $arr['accountcode'] = null;

        // 转账需要确认转账后金额不小于0
        $balance = self::_getLastMoney ($payeeid);
        if ($balance < $trade_money) {
            // 余额小于转账额则报错
            Access::Respond (0, array(), '余额不足, 请先充值');
        }
        return self::_internalAccount ($arr);
    }

    /**
     * 获取余额 (要求查找流水最新一条, 可以保证余额实时)
     */
    public static function _getLastMoney ($payuserid) {
        return Verify_Model::_getLastMoney($payuserid);
    }

    /** 
     * 充值商品
     */
    public static function recharge ($arr, $cn_id) {
        $text = "充值";
        $arr['cn_id'] = $cn_id;
        $arr['type'] = PAYSIGN_CHARGE;
        if (PAY_CHID_WEIXIN == $cn_id) {
            // 微信充值方式
            $arr['cn_name'] = PAY_CHNAME_WEIXIN;
            $arr['trade_other'] = PAY_CHNAME_WEIXIN.$text;
        } else if (PAY_CHID_HUMAN == $cn_id) {
            // 人工充值方式
            $arr['cn_name'] = PAY_CHNAME_HUMAN;
            $arr['trade_other'] = PAY_CHNAME_HUMAN.$text;
        } else if (PAY_CHID_ALIPAY == $cn_id) {
            // 支付宝充值方式
            $arr['cn_name'] = PAY_CHNAME_ALIPAY;
            $arr['trade_other'] = PAY_CHNAME_ALIPAY.$text;
        } else {
            // 其他方式, 目前未支持
            Access::Respond (0, array(), '目前尚未支持此类充值方式');
        }
        return self::_account ($arr, PAY_GOODID_CHARGE);
    }

    /**
     * 提现
     */
    public static function recash ($arr, $cn_id) {
        $text = "提现";
        $arr['cn_id'] = $cn_id;
        $arr['type'] = PAYSIGN_CASH;
        if (PAY_CHID_WEIXIN == $cn_id) {
            // 微信提现方式
            $arr['cn_name'] = PAY_CHNAME_WEIXIN;
            $arr['trade_other'] = PAY_CHNAME_WEIXIN.$text;
        } else if (PAY_CHID_HUMAN == $cn_id) {
            // 人工提现方式
            $arr['cn_name'] = PAY_CHNAME_HUMAN;
            $arr['trade_other'] = PAY_CHNAME_HUMAN.$text;
        } else if (PAY_CHID_ALIPAY == $cn_id) {
            // 支付宝提现方式
            $arr['cn_name'] = PAY_CHNAME_ALIPAY;
            $arr['trade_other'] = PAY_CHNAME_ALIPAY.$text;
        } else {
            // 其他方式, 目前未支持
            Access::Respond (0, array(), '目前尚未支持此类提现方式');
        }
        return self::_account ($arr, PAY_GOODID_CASH);
    }

    /**
     * 消费商品
     * @param $arr
     * @param $cn_id 渠道ID
     * @param $goodalias 商品索引
     * @param $trade_other 消费备注
     * @return bool
     */
    public static function buy ($arr, $cn_id, $goodalias, $trade_other) {
        $arr['cn_id'] = $cn_id;
        if (PAY_CHID_WEIXIN == $cn_id) {
            // 微信充值方式
            $arr['cn_name'] = PAY_CHNAME_WEIXIN;
            $arr['trade_other'] = $trade_other;
        } else if (PAY_CHID_HUMAN == $cn_id) {
            // 人工充值方式
            $arr['cn_name'] = PAY_CHNAME_HUMAN;
            $arr['trade_other'] = $trade_other;
        } else if (PAY_CHID_ALIPAY == $cn_id) {
            // 支付宝充值方式
            $arr['cn_name'] = PAY_CHNAME_ALIPAY;
            $arr['trade_other'] = $trade_other;
        } else {
            // 其他方式, 目前未支持
            Access::Respond (0, array(), '目前尚未支持此类充值方式');
        }
        return self::_account ($arr, $goodalias);
    }

    /**
     * 查询余额 (不一定实时, 有可能存在缓存, 根据需求可以变动)
     */
    public static function balance ($arr) {
        $payeeid=Access::IfKeyExists($arr, 'payeeid');
        if ($payeeid == null) { return false; }

        // 先判断是否存在此账户
        if (!self::_checkPayuserIfExists ($payeeid)) {
            // 不存在此账户
            Access::Respond (0, array(), '不存在此账户');
        }

        $balance = self::_getLastMoney ($payeeid);
        if (!isset ($balance)) {
            return false;
        }
        return $balance;
    }

    /**
     * 读取账户流水
     */
    public static function read ($arr) {
        $payeeid=Access::IfKeyExists($arr, 'payeeid');
        $accountcode=Access::IfKeyExists($arr, 'accountcode');
        $searchkey=Access::IfKeyExists($arr, 'searchkey');
        $cn_id=Access::IfKeyExists($arr, 'cn_id');
        $start=Access::IfKeyExists($arr, 'start');
        $limit=Access::IfKeyExists($arr, 'limit');

        if (isset ($accountcode)) {
            $tmp_sql = sprintf (" and PA.accountcode='%s' ", $accountcode);
        } else {
            $tmp_sql = "";
        }

        if (isset ($cn_id)) {
            $tmp_sql=sprintf (" %s and PA.cn_id='%s'", $tmp_sql, $cn_id);
        } else {
        }
        
        if (isset ($payeeid)) {
            $tmp_sql=sprintf (" %s and PA.payeeid='%s'", $tmp_sql, $payeeid);
        } else {
        }

        if (isset ($searchkey)) {
            // 关键词搜索（支付账号、支付凭证、渠道凭证、渠道付款人、渠道收款人）
            $part_sql=" and (
                PA.payeeid like '%$searchkey%' or
                PA.cn_code like '%$searchkey%' or
                PA.accountcode like '%$searchkey%'
            )";
            $tmp_sql=sprintf (" %s %s ", $tmp_sql, $part_sql);
        }

        if (!isset ($start) || !isset ($limit)) {
            $start = 0;
            $limit = 20;
        }

        $sql = sprintf ("
            select PA.accountcode, PA.payeeid, PA.cn_code, PA.cn_id, PA.cn_name, PA.cn_msg,
              PA.cn_appid, PA.cn_mch_id, PA.cn_result_code, PA.cn_err_code, PA.cn_err_code_des,
              PA.cn_trade_no, PA.cn_time, PA.type as type,
              PA.cn_money, PA.cn_charge, PA.other, PA.time, PA.payname, PA.cn_openid, (
                case when exists (
                  select 1 from pay_trade where accountcode=PA.accountcode limit 1
                ) then 1 else 0 end
              ) as iftrade
            from pay_account as PA
            where 1 %s order by time desc limit %s, %s
        ", $tmp_sql, $start, $limit);
        $ret = Db::query ($sql);

        // 获取总条数
        $sql = sprintf ("
            select count(1) as i 
            from pay_account as PA
            where 1 %s order by time desc
        ", $tmp_sql);
        $count_ret = Db::query ($sql);

        $result['data'] = $ret;
        $result['count'] = $count_ret[0]['i'];
        return $result;
    }

    /**
     * 判断内部支付订单号是否已存在支付成功的记录
     * @param $trade_no
     */
    public static function checkTradeNoOfSuccess ($trade_no) {
        $sql = sprintf ("select 1 from pay_account where cn_trade_no='%s' and cn_result_code='%s' and cn_id=%s limit 1",
            $trade_no, "SUCCESS", PAY_CHID_WEIXIN);
        $ret = Db::query ($sql);
        return count ($ret);
    }

    /**
     * 判断是否为消费类
     */
    public static function checkIfConsumeType ($goodalias) {
        if ($goodalias == PAY_GOODID_BASICMEMBER || $goodalias == PAY_GOODID_STARMEMBER) {
            // 基础会员\星级会员
            return true;
        }
        return false;
    }

    /**
     * 判断是否为充值类
     */
    public static function checkIfChargeType ($goodalias) {
        if ($goodalias == PAY_GOODID_CHARGE) {
            // 充值
            return true;
        }
        return false;
    }

    /**
     * 判断是否为提现类
     * @param $goodalias
     */
    public static function checkIfCashType ($goodalias) {
        if ($goodalias == PAY_GOODID_CASH) {
            // 提现
            return true;
        }
        return false;
    }
}