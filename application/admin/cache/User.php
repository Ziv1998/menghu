<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/10/16
 * Time: 9:13
 */
namespace app\admin\cache;
use think\Model;
use app\common\model\RedisCache;
use app\common\model\Access;

class User extends Model
{
    private static $VERIFYCODE_EXPIRE = VERIFYCODE_EXPIRE;

    /**
     * 保存verifycode
     */
    public static function SetVerifycode ($phone, $verifycode) {
        $namespace = RedisCache::GetRedisKeys("register", "code");
        $codeArea = sprintf ("%s:%s", $namespace, $phone);
        RedisCache::getInstance ()->set ($codeArea, $verifycode, self::$VERIFYCODE_EXPIRE);
    }

    /**
     * 获取verifycode
     */
    public static function GetVerifycode ($phone) {
        $namespace = RedisCache::GetRedisKeys("register", "code");
        $codeArea = sprintf ("%s:%s", $namespace, $phone);
        return RedisCache::getInstance ()->get ($codeArea);
    }
}