<?php
/**
 * 微信支付工具类
 */
namespace app\pay\common;
use think\Model;
use app\common\model\Access;
use app\common\model\Authority;
use app\pay\common\LockTools;

class WxTools extends Model {

    private static $instance = null;

    public function __construct() {
    }

    public static function getInstance () {
        if (is_null(WxTools::$instance)) {
            // 配置
            self::$instance = new WxTools ();
            return self::$instance;
        } else {
            return self::$instance;
        }
    }

    /**
     * @param $tag
     * @param $msg
     * @param $ifrepeat 1表示微信会继续发送通知, 0表示微信不会继续发通知
     */
    public static function Quit ($tag, $msg, $ifrepeat=0) {
        Access::Log($tag, $msg);
        if (!$ifrepeat) {
            $return_code = "SUCCESS";
        } else {
            $return_code = "FAIL";
        }
        $xml = Access::toXml (array(
            "return_code" => $return_code,
            "return_msg" => $msg
        ));
        echo $xml;exit();
    }
}
?>