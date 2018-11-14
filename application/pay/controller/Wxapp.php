<?php
/**
 * 微信APP支付接口
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/5/15
 * Time: 10:18
 */
namespace app\pay\controller;
use app\common\model\Admin;
use app\common\model\Access;
use think\Controller;
use app\pay\model\wechat\App;
use app\common\model\Authority;
use app\pay\common\WxTools;
use app\pay\common\LockTools;
use app\pay\model\Wxapp as Wxapp_Model;

class Wxapp extends Controller
{
    /** 预下单 */
    public function preorder () {

        $goodid = Access::MustParamDetect ('goodid');     // 商品alias

        Authority::getInstance()->permit (array(F_MANAGER, F_PARENT))->check(true)->loadAccount ($flag, $userid);

        /**
         *  此处要加入并发处理, 指定时间内, 不能多人支付
         *  作用: 减少通知接口的并发压力
         *  提示: 用户收到系统繁忙, 请稍后支付
         *  加锁之后副作用: 并发下用户支付会报错
         */
        $delay_i = 0;     // 当前延迟重试计数
        $PERIOD = 200;     // 周期等待时间
        $LIMIT_NUM = 3;   // 延迟重试次数 (注意不要超过锁的有效期)

        // 外部传递进来的可选参数
        // $param = array(
        //     "studentid" => $studentid
        // );
        // $json_param = json_encode($param);      // 将可选参数转成json字符串格式

        $app = new App ();

        $result = $app->preOrder(array(
            "userid" => $userid,
            "flag" => $flag,
            "goodid" => $goodid,
        ));

        Access::Respond(1, $result, '成功发起新订单');
    }

    /**
     * 预下单通知回调
     */
    public function notify () {

        Access::Log ("log", "handle notify.");

        $app = new App ();

        // 获取微信支付结果通知
        $verify = $app->notify();

        Access::Log ("log", "prepare to receive notify.");

        /**
         * 此处加入防止并发处理, 在指定时间如果有多个通知, 则返回错误
         * 作用: 减少交易并发导致内部交易没完成, 外部交易已经记录的问题
         * 提示: 让微信下次继续发过来
         * 加锁之后副作用: 通知流水记录会延迟, 通知可能会漏缺，导致用户支付成功了, 但是系统没有记录
         */
        $delay_i = 0;     // 当前延迟重试计数
        $PERIOD = 200;     // 周期等待时间
        $LIMIT_NUM = 3;   // 延迟重试次数 (注意不要超过锁的有效期)

        while ($delay_i < $LIMIT_NUM) {
            if (!LockTools::L (REDIS_LOCK_PAYNOTIFY, REDIS_LOCK_EXPIRE)) {
                // 锁被占用
                $delay_i++;  // 计数加1

                // 延迟等待
                sleep ($PERIOD);        // 延迟200ms
            } else {
                Access::Log ("log", "receive notify, prepare to account.");
                // 成功获取锁则实现流水记账, 记账后直接退出循环
                $result = $app->preNotify ($verify);
                Access::Log ("log", "receive notify, finish to account.");
                break;
            }
        }

        if ($delay_i >= $LIMIT_NUM) {
            // 反馈微信FAIL, 按照下一个周期重新接收通知
            WxTools::Quit("log", "收到并发支付通知, 下一个周期继续处理", 1);
        } else {
            LockTools::UL (REDIS_LOCK_PAYNOTIFY);     // 解除通知锁

            $cn_trade_no = $result['out_trade_no'];   // 订单号
            $total_fee = $result['total_fee'];          // 金额
            $transaction_id = $result['transaction_id'];    // 微信订单号
            WxTools::Quit("log", sprintf("成功处理支付通知(out_trade_no:%s, transaction_id:%s, money:%s)",
                $cn_trade_no, $transaction_id, $total_fee));
        }
    }

    /*public function getxml () {
        $xml = Access::toXml (array(
            "return_code" => "0",
            "return_msg" => "签名失败"
        ));
        echo $xml;exit();
    }*/
}
