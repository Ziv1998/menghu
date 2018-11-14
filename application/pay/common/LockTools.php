<?php
/**
 * redis并发锁定义
 */
namespace app\pay\common;
use app\common\model\RedisCache;
use think\Model;
use app\common\model\Access;
use app\common\model\Authority;

class LockTools extends Model {

    private static $instance = null;

    public function __construct() {
    }

    public static function getInstance () {
        if (is_null(MoneyTools::$instance)) {
            // 配置
            self::$instance = new LockTools ();
            return self::$instance;
        } else {
            return self::$instance;
        }
    }

    /**
     * 加锁
     * @param bool $key
     */
    public static function L ($key, $expire) {
        // 如果$key不存在则返回true, 否则返回false, 有效期为$expire
        return RedisCache::getInstance ()->setExtend ($key, 1, array('nx', 'ex'=>$expire));
    }

    /**
     * 去锁
     * @param $key
     */
    public static function UL ($key) {
        RedisCache::getInstance ()->del ($key);
    }
}
?>