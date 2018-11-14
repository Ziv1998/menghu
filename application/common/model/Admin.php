<?php

/**
 * 1、身份确认(是否超管，是否学管，是否班教)
 * 2、条件SQL(指定学校,指定家长,指定学生,指定班级)
 * 3、其他暂时无法分类的
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/5/15
 * Time: 11:15
 */
namespace app\common\model;
use think\Model;

class Admin extends Model
{
    /**
     * 获取图片
     */
    public static function sqlWithLinkId ($partSql, $key="headpic") {
        $sql = "(select 
                case when qiniu_key is null or qiniu_http is null then absolutePath else concat(qiniu_http, qiniu_key) end 
                %s) as ".$key;
        $sql = sprintf ($sql, $partSql);
        return $sql;
    }

    /**
     * 同个班级的消息
     */
    public static function sqlFilterOfClass ($arr, $table="tb_user") {
        $userid = Access::IfKeyExists ($arr, "userid");

        $r_sql = sprintf ("
            (
                (
                    select TRA.roomid 
                    from tb_roomapply as TRA 
                    where TRA.userid=%s and TRA.status=%s 
                ) union (
                    select TR.id as roomid 
                    from tb_room as TR
                    where TR.userid=%s
                )
            ) as TA 
        ", $userid, ROOM_APPLY_S_PASS, $userid);

        $t_sql = sprintf ("
            and %s.userid in (
                select R.userid 
                from tb_room as R 
                inner join %s 
                where R.id=TA.roomid
            union 
                select userid 
                from tb_roomapply as RA 
                inner join %s 
                where RA.roomid=TA.roomid and RA.status=%s
            )
        ", $table, $r_sql, $r_sql,ROOM_APPLY_S_PASS);
        return $t_sql;
    }
}
