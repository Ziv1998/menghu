<?php
/**
 * 微信支付基本类
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/5/15
 * Time: 14:01
 */
namespace app\pay\model\wechat;

use think\Model;
use app\common\model\Admin;
use app\common\model\Access;

require_once dirname(dirname(dirname(dirname(__FILE__)))).('/common/pay-php-sdk/init.php');
use Pay\Pay;

class Base extends Model {

    protected $config = null;
    protected $options = null;
    protected $pay = null;

    public function __construct () {
        // 配置微信信息
        $this->config = [
            // 微信支付参数
            'wechat' => [
                // 沙箱模式
                'debug'      => false,
                // 应用ID
                'app_id'     => 'wx2a7bb5037d11d9dd',
                // 微信支付商户号
                'mch_id'     => '1513397431',
                /*
                 // 子商户公众账号ID
                 'sub_appid'  => '子商户公众账号ID，需要的时候填写',
                 // 子商户号
                 'sub_mch_id' => '子商户号，需要的时候填写',
                */
                // 微信支付密钥
                'mch_key'    => 'xow3mcUDmd8cm2sjxosn7cm3spb63Lj4',
                // 微信证书 cert 文件
                /*'ssl_cer'    => __DIR__.'/cert/1300513101_cert.pem',
                // 微信证书 key 文件
                'ssl_key'    => __DIR__.'/cert/1300513101_key.pem',*/
                // 缓存目录配置
                'cache_path' => '',
                // 支付成功通知地址
                'notify_url' => '',
                // 网页支付回跳地址
                'return_url' => '',
            ]
        ];
        $this->pay = new Pay ($this->config);
    }

    /**
     * 预付款
     */
    protected function preOrder ($arr) {

        $out_trade_no=Access::IfKeyExists($arr, 'out_trade_no');
        $total_fee=Access::IfKeyExists($arr, 'total_fee');
        $body=Access::IfKeyExists($arr, 'body');
        $notify_url=Access::IfKeyExists($arr, 'notify_url');
        $ip=Access::IfKeyExists($arr, 'ip');

        // 支付参数
        $this->options = [
            'out_trade_no'     => $out_trade_no, // 订单号
            'total_fee'        => $total_fee, // 订单金额，**单位：分**
            'body'             => $body, // 订单描述
            'spbill_create_ip' => $ip, // 支付人的 IP
            'notify_url'       => $notify_url, // 定义通知URL
        ];
        try {
            $result = $this->pay->driver('wechat')->gateway('app')->apply($this->options);
            return $result;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 收到预下单通知
     */
    protected function notify () {
        $data = file_get_contents('php://input');

        // 这里可以加个判断, 保证传进来的参数没有异常
        // TODO ...
        $verify = $this->pay->driver('wechat')->gateway('mp')->verify($data);
        return $verify;
    }

    /**
     * 微信打日志
     */
    public function log ($content) {
        $text = sprintf ("%s:%s:%s\r\n", "weixin", time(), $content);
        file_put_contents('notify.txt', $text, FILE_APPEND);
    }
}