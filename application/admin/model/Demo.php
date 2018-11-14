<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/12
 * Time: 19:28
 */

namespace app\admin\model;

use think\Db;
use think\Model;
use app\common\model\Admin;
use app\common\model\Access;

class Demo extends Model
{
    public static function test () {
        $sql = "
            select * 
            from tb_user 
        ";
        $ret = Db::query ($sql);
        return $ret;
    }
}