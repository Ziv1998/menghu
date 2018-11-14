<?php
/**
 * 第三方云存储(七牛)
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/08/24
 * Time: 11:15
 */
namespace app\common\model;
use think\Db;
use think\Model;
require_once dirname(dirname(__FILE__)).('/php-sdk-7.2.0/autoload.php');

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class ThirtStore extends Model {

    private static $instance = null;

    public static $Addr = QINIU_HTTP;
    public static $AccessKey = QINIU_ACCESSKEY;
    public static $SecretKey = QINIU_SECRETKEY;
    public static $expires = QINIU_TOKEN_EXPIRES;                 // 0表示永久, 先写成15h

    public static $bucket = QINIU_BUCKET;     // 空间
    public $upToken = null;            // 上传凭证
    private $auth = null;               // 鉴权
    private $uploadMgr = null;          // 上传句柄

    public function __construct () {
        // 上传工具
        $this->uploadMgr = new UploadManager ();

        // 设置密钥公钥
        $this->setKeys (self::$AccessKey, self::$SecretKey);

        // 设置空间
        $this->setBucket (self::$bucket);
    }

    public static function getInstance () {
        if (is_null(ThirtStore::$instance)) {
            self::$instance = new ThirtStore ();
            return self::$instance;
        } else {
            return self::$instance;
        }
    }

    /** 
     * 设置AccessKey和SecretKey
     */
    private function setKeys ($accesskey, $secretkey) {
        $this->auth = new Auth ($accesskey, $secretkey);
    }

    /**
     * 生成上传凭证
     */
    private function generateToken ($bucket, $key, $expires) {
        $upToken = $this->auth->uploadToken ($bucket, $key, $expires);
        return $upToken;
    }
    
    /**
     * 设置空间
     */
    public function setBucket ($bucket) {
        $this->bucket = $bucket;
        $this->upToken = $this->generateToken ($this->bucket, null, self::$expires);        // 这里有效期不能不写, 否则机器传不了文件
        return self::$instance;
    }

    /**
     * 上传文件
     */
    public function upload ($filename) {
        //将字符串切割成数组
        $fileArrs = explode(DS, $filename);
        $remotefilename = "";
        if (count ($fileArrs) > 1) {
            $remotefilename = $fileArrs[count($fileArrs)-1];
        } else {
            $remotefilename = $filename;
        }

        list($ret, $err) =  $this->uploadMgr->putFile ($this->upToken, $remotefilename, $filename);
        if ($err !== null || $ret == null) {
            $data_ret['ifsuccess'] = false;
            $data_ret['data'] = $err;

            $tmp['err'] = $err;
            $tmp['ret'] = $ret;
            $tmp['filename'] = $filename;
            Access::Respond (0, $tmp, "文件远程同步失败, 请联系管理员");
        } else {
            $data_ret['ifsuccess'] = true;
            $data_ret['data'] = $ret;
        }
        return $data_ret;
    }
}
?>
