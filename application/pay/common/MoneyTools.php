<?php
/**
 * 金钱管理模块 (涉及金钱的加密, 解密, 数字大小校验等)
 */
namespace app\pay\common;
use think\Model;
use app\common\model\Access; 
use app\common\model\Authority;

class MoneyTools extends Model {
    
    private static $instance = null;

    public function __construct() {
    }

    public static function getInstance () {
        if (is_null(MoneyTools::$instance)) {
            // 配置
            self::$instance = new MoneyTools ();
            return self::$instance;
        } else {
            return self::$instance;
        }
    }

    /**
     * 保证符合金钱格式(非0正整数, 单位为分)
     */
    public static function check ($money, $type='money') {
        if (!is_numeric($money)||strpos($money,".")!==false) {
            Access::Respond (0, array(), '金钱必须为正整数');
        }
        return true;
    }

    /**
     * 加密
     */
    public function encrypt ($data, $key=RSA_PRIVATE) {
        $data = $data."";           // 数字不能直接加密, 所以要先转换成字符串
        $char = "";
        $str = "";
        $key = md5($key);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        for ($i=0;$i<$len;$i++) {
            if ($x == $l)
            {
                $x = 0;
            }
            $char.=$key{$x};
            $x++;
        }
        for ($i=0;$i<$len;$i++) {
            $str.=chr(ord($data{$i})+(ord($char{$i}))%256);
        }
        return base64_encode($str);
    }

    /**
     * 解密
     */
    public function decrypt ($data, $key=RSA_PRIVATE) {
        $char = "";
        $str = "";
        $key = md5($key);
        $x = 0;
        $data = base64_decode($data);
        $len = strlen($data);
        $l = strlen($key);
        for ($i=0;$i<$len;$i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char.=substr($key, $x, 1);
            $x++;
        }
        for ($i=0;$i<$len;$i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
            {
                $str.=chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            }
            else
            {
                $str.=chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }
}
?>