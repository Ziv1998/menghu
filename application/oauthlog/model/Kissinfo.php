<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/10/17
 * Time: 14:49
 */
namespace app\oauthlog\model;

use app\common\model\Access;
use think\Db;
use think\Model;

class Kissinfo extends Model
{
    /**
     * 更新学校
     */
    public static function updateSchool ($arr)
    {
        $value_sql = "";
        for ($i=0;$i<count($arr);$i++) {
            $obj = $arr[$i];
            $schoolid = $obj['schoolid'];
            $schoolname = $obj['schoolname'];

            if ($i >= (count($arr)-1)) {
                $value_sql.=sprintf("(%s, '%s')", $schoolid, $schoolname);
            } else {
                $value_sql.=sprintf("(%s, '%s'),", $schoolid, $schoolname);
            }
        }

        $sql = sprintf ("
            insert into tb_school(schoolid, schoolname)
            values %s
            on DUPLICATE KEY UPDATE schoolname=values(schoolname)
        ", $value_sql);
        return Db::execute ($sql);
    }
}