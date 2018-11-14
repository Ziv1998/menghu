<?php
/**
 * 登录特殊需求
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/7/4
 * Time: 10:01
 */
namespace app\admin\special;

use think\Db;
use think\Model;
use app\common\model\Admin;
use app\common\model\Access;
use app\common\model\Authority;
use app\common\model\ThirtStore;

/**
 * 登录Session
 */
class Login extends Model
{   
    /**
     * 定制变量
     */
    public static function Variable ($arr) {
        $userid=Access::IfKeyExists($arr, 'userid');
        $flag=Access::IfKeyExists($arr, 'flag');

        if ($flag == F_TEACHER || $flag == F_PARENT) {
            // 教师端
            $Bucket = QINIU_BUCKET;                // 机器端上传的视频
            $expires = QINIU_TOKEN_EXPIRES;   // 30天有效期
            $http = QINIU_HTTP;

            ThirtStore::$bucket = $Bucket;
            ThirtStore::$expires = $expires;
            $upToken = ThirtStore::getInstance ()->upToken;        // 获取上传凭证
            // 机器端
            return array (
                'upToken' => $upToken,
                'bucket'    => $Bucket,
                'expires'   => $expires,
                'http'    => $http
            );
        } else {
            return array ();
        }
    }
}
