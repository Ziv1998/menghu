<?php
/**
 * APP端支付后台接口 (包括后台数据存储)
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/5/15
 * Time: 14:01
 */
namespace app\pay\model\wechat;

use think\Db;
use app\common\model\Admin;
use app\common\model\Access;
use app\pay\model\wechat\Base;
use app\pay\model\Good as Good_Model;
use app\pay\cache\Wxapp as Wxapp_Cache;
use app\pay\common\WxTools;

class App extends Base
{
    /** 生成唯一订单号 */
    private static function _buildordercode ($userid, $ranNum=2) {
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

    /** 预下单 */
    public function preOrder ($arr) {

        $goodid=Access::IfKeyExists($arr, 'goodid');      
        $userid=Access::IfKeyExists($arr, 'userid');
        $flag=Access::IfKeyExists($arr, 'flag');

        Access::Log ("log", "enter preOrder function.");

        if (!isset ($userid) || !isset ($flag)) {
            // userid不能为空
            Access::Log ("error", "id no permit null.");
            Access::Respond (0, array(), '支付停止, 需要先登录');
        }

        // 获取ip
        $info = Access::LoadVisitorInfo ();
        $ip=$info['http_x_real_ip'];

        // if (!isset ($key)) {
        //     // 默认为购买基础会员
        //     $key = PAY_GOODID_CHARGE;
        // }

        Access::Log ("log", "get good's money and info.");

        // 构造价格等商品信息
        // if ($key != PAY_GOODID_CHARGE) {
        //     // 实际商品, 在数据库中查找
        //     $good_info = Good_Model::read(array(
        //         "searchkey" => $key,
        //         "start" => 0,
        //         "limit" => 1
        //     ));

        //     // 指定账号有内部价格
        //     $price = Consume_Model::inside ($arr, $good_info);
        //     if (null != $price) {
        //         $good_info[0]['total_fee'] = $price;
        //     }
        // } else {
        //     // 充值操作
        //     $good_info[0] = array(
        //         "total_fee" => $money,
        //         "alias" => PAY_GOODID_CHARGE,
        //         "goodname" => "K豆充值"
        //     );
        // }
        // if (count ($good_info) <= 0) {
        //     Access::Log ("error", "goodalias no found.");
        //     Access::Respond (0, array(), '没有找到这个商品, 支付停止');
        // }

        $good_info = Good_Model::read(array(
            "goodid" => $goodid,
            "userid" => $userid
        ));
        if(count($good_info)<=0){
            Access::Respond (0, array(), '未找到商品');
        }
        // 生成唯一订单
        $ordercode = self::_buildordercode ($userid);

        $out_trade_no=$ordercode;
        $total_fee=$good_info[0]['money'];          // 价格
        $body=$good_info[0]['goodname'];                // 商品名
        $notify_url=PAY_WX_NOTIFY;                      // 微信支付成功回调

        Access::Log ("log", "prepare to send to weixin.");

        // 向微信服务器发起预下单请求
        $wx_result =  parent::preOrder (array(
            "out_trade_no" => $out_trade_no,
            "total_fee" => $total_fee,
            "body" => $body,
            "notify_url" => $notify_url,
            "ip" => $ip
        ));
        // 签名
        $sign = $wx_result['sign'];                 // 签名

        Access::Log ("log", "prepare to setOrder cache.");

        // 预下单缓存, 保存(订单号,用户imuserid,用户userid,用户flag)
        Wxapp_Cache::SetOrder ($ordercode, array(
            "userid" => $userid,
            "goodid" => $goodid,
            "flag" => $flag,
            "out_trade_no" => $out_trade_no,
            "total_fee" => $total_fee,
            "body" => $body,
            "notify_url" => $notify_url,
            "ip" => $ip,
            "goodname" => $body,        // 商品名
        ));
        return $wx_result;
    }

    /**
     *  收到预下单通知
     */
    public function notify () {
        return parent::notify();
    }

    /**
     * 收到预下单通知
     */
    public function preNotify ($verify)
    {
        if ($verify) {
            // 获取订单缓存
            $out_trade_no = $verify['out_trade_no'];
            $transaction_id = $verify['transaction_id'];
            $total_fee = $verify['total_fee'];
            $sign = $verify['sign'];

            Access::Log ("log", sprintf ("接收到新订单, 订单号:%s, 微信订单号:%s, 金额:%s, 签名:%s",
                $out_trade_no,$transaction_id,$total_fee, $sign));
            $order_cache = Wxapp_Cache::GetOrder($out_trade_no);

            // 保存支付结果
            $this->saveAccount($verify, $order_cache);
            // $xml = Access::toXml($verify);
            // parent::log ($xml);
            return $verify;
        } else {
            WxTools::Quit("log", "找不到此接口");
        }
    }

    /**
     * 检查订单是否已经存在记录中, 且业务结果为SUCCESS
     * @param $accountcode
     */
    private function _checkTradeNo ($cn_trade_no) {
        return self::checkTradeNoOfSuccess ($cn_trade_no);
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
     * 记录支付流水
     */
    private function saveAccount ($verify, $cache) {
        $verify_money = Access::IfKeyExists($verify, 'total_fee');          // 微信支付成功返回的金额
        $payuserid = Access::IfKeyExists($cache,'payuserid');               // KISS充值者支付账号
        $appid = Access::IfKeyExists($verify,'appid');                      // 公众账号ID
        $cn_code = Access::IfKeyExists($verify,'transaction_id');           // 微信支付订单号
        $cn_mch_id = Access::IfKeyExists($verify,'mch_id');                 // 商户号
        $cn_openid = Access::IfKeyExists($verify,'openid');                 // 用户标识
        $cn_nonce_str = Access::IfKeyExists($verify,'nonce_str');           // 随机字符串
        $cn_sign = Access::IfKeyExists($verify,'sign');                     // 签名
        $cn_sign_type = Access::IfKeyExists($verify,'sign_type');           // 签名类型
        $cn_type = Access::IfKeyExists($verify,'trade_type');               // 交易类型
        $cn_result_code = Access::IfKeyExists($verify,'result_code');       // 业务结果
        $cn_err_code = Access::IfKeyExists($verify, 'err_code');             // 错误代码
        $cn_err_code_des = Access::IfKeyExists($verify, 'err_code_des');     // 错误代码描述
        $cn_time = Access::IfKeyExists($verify, 'time_end');                 // 支付完成时间
        $cn_trade_no = Access::IfKeyExists($verify, 'out_trade_no');         // 商户订单号
        $cn_money = Access::IfKeyExists($cache, 'total_fee');                // 缓存的金额
        $cache_sign = Access::IfKeyExists($cache, 'sign');          // 缓存中签名
        $userid = Access::IfKeyExists($cache, 'userid');          // 用户userid
        $goodid = Access::IfKeyExists($cache, 'goodid');          // 用户goodid
        //$cache_key= Access::IfKeyExists($cache, 'key');          // 缓存中购买类型
        $goodname = Access::IfKeyExists($cache, 'goodname');     // 缓存中商品名
        $param = $cache['param'];     // 购买可选参数
        $cn_id = PAY_CHID_WEIXIN;
        $cn_name = PAY_CHNAME_WEIXIN;
        $cn_msg="";

        Access::Log ("log", sprintf ('订单号:%s,记录支付流水前的校验', $cn_trade_no));
        
        /** 判断支付结果反馈是否为SUCCESS，否则不处理 */
        if ($cn_result_code != "SUCCESS") {
            WxTools::Quit("log", sprintf ("订单号:%s, 支付结果反馈不为SUCCESS, 支付不成功", $cn_trade_no), 1);
        }
        /** 判断此订单号是否已经处理过了 */
        if (self::_checkTradeNo($cn_trade_no)) {
            // 如果已经处理过则忽略
            WxTools::Quit("log", sprintf ("订单号:%s, 此订单号已经处理过了", $cn_trade_no));
        }
        if ($cn_money != $verify_money) {
            // 如果两个金额不一致则报错 (重要)
            WxTools::Quit("log",sprintf ("订单号:%s,微信支付返回的金额%s与订单金额%s不一致", $cn_trade_no, $verify_money, $cn_money));
        }
        if ($cache_sign != $cn_sign) {
            // 如果两个签名不一致 (实际发现每次都是不一样的, 应该不是直接做对比, 目前先做订单号校验, 也可以满足一定需求了)
            // WxTools::Quit("log",sprintf ("订单号:%s, 微信支付返回的签名%s与缓存中签名%s不一致", $cn_trade_no, $cn_sign, $cache_sign));
        }

        Access::Log ("log", sprintf ("订单号:%s,开始记录支付流水", $cn_trade_no));

        Access::Log ("log", sprintf ("订单号:%s,消费商品:%s", $cn_trade_no, $goodname));

        // Access::Log("log",sprintf("微信支付成功返回的金额:%s",$verify_money));
        // Access::Log("log",sprintf("KISS充值者支付账号:%s",$payuserid));
        // Access::Log("log",sprintf("公众账号ID:%s",$appid));
        // Access::Log("log",sprintf("微信支付订单号:%s",$cn_code));
        // Access::Log("log",sprintf("商户号:%s",$cn_mch_id));
        // Access::Log("log",sprintf("用户标识:%s",$cn_openid));
        // Access::Log("log",sprintf("随机字符串:%s",$cn_nonce_str));
        // Access::Log("log",sprintf("签名:%s",$cn_sign));
        // Access::Log("log",sprintf("签名类型:%s",$cn_sign_type));
        // Access::Log("log",sprintf("交易类型:%s",$cn_type));
        // Access::Log("log",sprintf("业务结果:%s",$cn_result_code));
        // Access::Log("log",sprintf("错误代码:%s",$cn_err_code));
        // Access::Log("log",sprintf("错误代码描述:%s",$cn_err_code_des));
        // Access::Log("log",sprintf("支付完成时间:%s",$cn_time));
        // Access::Log("log",sprintf("商户订单号:%s",$cn_trade_no));
        // Access::Log("log",sprintf("缓存的金额:%s",$cn_money));
        // Access::Log("log",sprintf("缓存中签名:%s",$cache_sign));
        // Access::Log("log",sprintf("userid:%s",$userid));
        // Access::Log("log",sprintf("缓存中商品名:%s",$goodname));
        // 基础会员和星级会员
        $sql = sprintf ("
            insert into pay_account (
                userid, goodid, cn_code, cn_id, cn_name, cn_msg, cn_money, cn_appid, cn_mch_id, cn_openid, cn_nonce_str, cn_sign, cn_sign_type, cn_type, cn_result_code, cn_err_code, cn_trade_no, cn_time
            ) values (%s, %s, '%s', %s, '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        ", $userid, $goodid, $cn_code, $cn_id, $cn_name, $cn_msg, $cn_money, $appid, $cn_mch_id, $cn_openid, $cn_nonce_str, $cn_sign, $cn_sign_type, $cn_type, $cn_result_code, $cn_err_code, $cn_trade_no, $cn_time);
        $ret = Db::execute ($sql);
        if($ret>0){
            Access::Log ("log", sprintf ("订单号:%s,成功记录支付流水", $cn_trade_no));
            $sql_user = sprintf("insert into pay_good_user(userid, goodid) values (%s, %s)",$userid, $goodid);
            $ret_user = Db::execute ($sql_user);
            if($ret_user>0){
                Access::Log ("log", sprintf ("订单号:%s,成功记录用户购买记录", $cn_trade_no));
            }else{
                Access::Log ("log", sprintf ("订单号:%s,记录用户购买记录失败", $cn_trade_no));
            }
        }else{
            Access::Log ("log", sprintf ("订单号:%s,记录支付流水失败", $cn_trade_no));
        }
        
    }
}
