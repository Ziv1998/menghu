<?php
/**
 * 订单缓存
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/10/16
 * Time: 9:13
 */
namespace app\pay\cache;
use think\Model;
use app\common\model\RedisCache;
use app\common\model\Access;

class Wxapp extends Model
{
    private static $CODE_EXPIRE = 600;

    /**
     * 保存微信支付订单信息
     */
    public static function SetOrder ($ordercode, $arr) {
        $userid=Access::IfKeyExists($arr, 'userid');
        $goodid=Access::IfKeyExists($arr, 'goodid');
        $flag=Access::IfKeyExists($arr, 'flag');
        $imuserid=Access::IfKeyExists($arr, 'imuserid');

        $out_trade_no=Access::IfKeyExists($arr, 'out_trade_no');
        $total_fee=Access::IfKeyExists($arr, 'total_fee');
        $body=Access::IfKeyExists($arr, 'body');
        $notify_url=Access::IfKeyExists($arr, 'notify_url');
        $ip=Access::IfKeyExists($arr, 'ip');
        $key=Access::IfKeyExists($arr, 'key');
        $goodname=Access::IfKeyExists($arr, 'goodname');
        $payuserid=Access::IfKeyExists($arr, 'payuserid');
        $nonce_str=Access::IfKeyExists($arr, 'nonce_str');
        $sign=Access::IfKeyExists($arr, 'sign');
        $param=Access::IfKeyExists($arr, 'param');

        if (isset ($param) && is_array($param)) {
            $json_param = json_encode($param);
        } else {
            $json_param = $param;
        }
        $namespace = RedisCache::GetRedisKeys("pay", "o_r");
        $codeArea = sprintf ("%s:%s", $namespace, $ordercode);
        RedisCache::getInstance ()->hSet ($codeArea, 'imuserid', $imuserid);
        RedisCache::getInstance ()->hSet ($codeArea, 'userid', $userid);
        RedisCache::getInstance ()->hSet ($codeArea, 'goodid', $goodid);
        RedisCache::getInstance ()->hSet ($codeArea, 'flag', $flag);
        RedisCache::getInstance ()->hSet ($codeArea, 'out_trade_no', $out_trade_no);
        RedisCache::getInstance ()->hSet ($codeArea, 'total_fee', $total_fee);
        RedisCache::getInstance ()->hSet ($codeArea, 'body', $body);
        RedisCache::getInstance ()->hSet ($codeArea, 'notify_url', $notify_url);
        RedisCache::getInstance ()->hSet ($codeArea, 'ip', $ip);
        RedisCache::getInstance ()->hSet ($codeArea, 'key', $key);
        RedisCache::getInstance ()->hSet ($codeArea, 'goodname', $goodname);
        RedisCache::getInstance ()->hSet ($codeArea, 'payuserid', $payuserid);
        RedisCache::getInstance ()->hSet ($codeArea, 'sign', $sign);
        RedisCache::getInstance ()->hSet ($codeArea, 'param', $json_param);
        RedisCache::getInstance ()->expire ($codeArea, self::$CODE_EXPIRE);
        return ;
    }

    /**
     * 获取微信支付订单信息
     */
    public static function GetOrder ($ordercode) {
        $namespace = RedisCache::GetRedisKeys("pay", "o_r");
        $codeArea = sprintf ("%s:%s", $namespace, $ordercode);

        $arr = array();
        $arr['imuserid'] = RedisCache::getInstance ()->hGet ($codeArea, 'imuserid');
        $arr['userid'] = RedisCache::getInstance ()->hGet ($codeArea, 'userid');
        $arr['goodid'] = RedisCache::getInstance ()->hGet ($codeArea, 'goodid');
        $arr['flag'] = RedisCache::getInstance ()->hGet ($codeArea, 'flag');
        $arr['out_trade_no'] = RedisCache::getInstance ()->hGet ($codeArea, 'out_trade_no');
        $arr['total_fee'] = RedisCache::getInstance ()->hGet ($codeArea, 'total_fee');
        $arr['body'] = RedisCache::getInstance ()->hGet ($codeArea, 'body');
        $arr['notify_url'] = RedisCache::getInstance ()->hGet ($codeArea, 'notify_url');
        $arr['ip'] = RedisCache::getInstance ()->hGet ($codeArea, 'ip');
        $arr['key'] = RedisCache::getInstance ()->hGet ($codeArea, 'key');
        $arr['goodname'] = RedisCache::getInstance ()->hGet ($codeArea, 'goodname');
        $arr['payuserid'] = RedisCache::getInstance ()->hGet ($codeArea, 'payuserid');
        $arr['sign'] = RedisCache::getInstance ()->hGet ($codeArea, 'sign');
        $param = RedisCache::getInstance ()->hGet ($codeArea, 'param');

        // 将param转成对象
        $arr['param'] = json_decode($param);

        if (!$arr['out_trade_no']) {
            // 无法成功获取订单号, 则提示错误
            Access::Log ("log", sprintf ("订单号:%s, 获取订单缓存失败", $ordercode));
        } else if ($arr['out_trade_no'] != $ordercode){
            // 微信返回的订单号与本地缓存不一致
            Access::Log ("log", sprintf ("订单号:%s, 订单号不一致", $ordercode));
        }
        return $arr;
    }

}