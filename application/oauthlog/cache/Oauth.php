<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/12/11
 * Time: 8:38
 */

namespace app\oauthlog\cache;
use app\common\model\Access;
use think\Model;
use app\common\model\RedisCache;

class Oauth extends Model
{
    /**
     * 登录日志记录
    */

    private static $LOG_EXPIRE = LOG_EXPIRE;

    // 将登录信息以json的形式记录到队列中
    public static function SetLoginInfo($info){
        $namespace = RedisCache::GetRedisKeys("log", "auth");
        RedisCache::getInstance()->lPush($namespace,$info);
        //设置过期时间
        RedisCache::getInstance()->expire($namespace,self::$LOG_EXPIRE);
    }
    
    /**
     * 获取token刷新周期
     */
    public static function GetTokenValid ($access_token) {
        $namespace = RedisCache::GetRedisKeys("valid", "a_u");
        $codeArea = sprintf ("%s:%s", $namespace, $access_token);
        $json = RedisCache::getInstance ()->get ($codeArea);
        return $json;
    }

    /**
     * 设置token刷新周期
     */
    public static function SetTokenValid ($access_token, $expire_in) {
        $namespace = RedisCache::GetRedisKeys("valid", "a_u");
        $codeArea = sprintf ("%s:%s", $namespace, $access_token);
        RedisCache::getInstance()->set($codeArea, '1', $expire_in);
    }

    /**
     * 删除token刷新缓存
     */
    public static function DelTokenValid ($access_token) {
        $namespace = RedisCache::GetRedisKeys("valid", "a_u");
        $codeArea = sprintf ("%s:%s", $namespace, $access_token);
        RedisCache::getInstance ()->del ($codeArea);
    }
}