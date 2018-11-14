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
use think\Exception;
use think\Model;

class Oauth extends Model
{
    /** 
     * 保存kiss基本信息
     */
    public static function saveUserInfo ($list) {
        // 防注入
        $imuserid=Access::SQLInjectDetect($list['imuserid']);
        $headpic=Access::SQLInjectDetect($list['headpic']);
        $schoolid=Access::SQLInjectDetect($list['schoolid']);
        $schoolname=Access::SQLInjectDetect($list['schoolname']);
        $flagid=Access::SQLInjectDetect($list['flagid']);
        $flagname=Access::SQLInjectDetect($list['flagname']);
        $username=Access::SQLInjectDetect($list['username']);
        $access_token=Access::SQLInjectDetect($list['access_token']);
        $expires_in=Access::SQLInjectDetect($list['expires_in']);
        $refresh_token=Access::SQLInjectDetect($list['refresh_token']);

        // expires_in转成过期日期
        $deadline=date('Y-m-d H:i:s',time()+$expires_in);
        $userid = null;

        /** 获取userid */
        $result=Db::table('tb_imuserid')->where('imuserid',$imuserid)->find();
        if(count($result) > 0){
            // 如果已经存在, 则查看userid
            $userid = $result['userid'];
        }else{
            // 不存在userid记录, 需要新建userid记录
            $sql="
                INSERT INTO 
                    `tb_user` (`sex`, `username`, `phone`,`logintime`, `password`) 
                VALUES (NULL, NULL, NULL, NULL, NULL)";
            Db::execute($sql);
            
            //获取新插入的userid
            $userid=Db::table('tb_userid')->getLastInsID();
        }

        /** 如果存在则插入, 否则则更新tb_imuserid */
        $ret = Db::table('tb_school')->where('schoolid',$schoolid)->find();
        if(count($ret) <= 0){
            $sql = "insert into tb_school (schoolid,schoolname) values($schoolid,'$schoolname') ";
            Db::execute($sql);
        }
        $ret = Db::table('tb_flag')->where('flagid', $flagid)->find();
        if(count($ret) <= 0){
            $sql = "insert into tb_flag (flagid,flagname) values($flagid,'$flagname') ";
            Db::execute($sql);
        }

        $sql = "
            insert into 
                tb_imuserid 
            set imuserid=%s, kiss_access_token='%s', kiss_token_deadline='%s', kiss_refresh_token='%s',
                schoolid=%s, username='%s', flagid=%s, headpic='%s', userid=%s
            on DUPLICATE KEY UPDATE imuserid=%s, kiss_access_token='%s', kiss_token_deadline='%s', kiss_refresh_token='%s', schoolid=%s, username='%s', flagid=%s, headpic='%s'";
        $sql = sprintf ($sql, $imuserid, $access_token, $deadline, $refresh_token, $schoolid, 
                    $username, $flagid, $headpic, $userid, $imuserid, $access_token, $deadline, 
                    $refresh_token, $schoolid, $username, $flagid, $headpic);
        $ret = Db::execute ($sql);
        return $userid;
    }

    /**
     * 将基本信息存放进数据库
    */
    public static function inBasicInfo($userid, $list){
        //防止注入
        $userid = Access::SQLInjectDetect($userid);
        $headpic = Access::SQLInjectDetect($list['headpic']);
        $schoolname = Access::SQLInjectDetect($list['schoolname']);
        $schoolid = Access::SQLInjectDetect($list['schoolid']);
        $username = Access::SQLInjectDetect($list['username']);
        $flag = Access::SQLInjectDetect($list['flag']);
        $flagname = Access::SQLInjectDetect($list['flagname']);

        //判断schoolid是否存在tb_school里面，如果未存在，则插入
        $ret = Db::table('tb_school')->where('schoolid',$schoolid)->find();
        if(!count($ret)){
            $sql = "insert into tb_school (schoolid,schoolname) values($schoolid,'$schoolname') ";
            Db::execute($sql);
        }

        $ret = Db::table('tb_flag')->where('flagid', $flag)->find();
        if(!count($ret)){
            $sql = "insert into tb_flag (flagid,flagname) values($flag,'$flagname') ";
            Db::execute($sql);
        }

        $result = Db::table('tb_imuserid')->where('userid',$userid)->update(array(
            'headpic'=>$headpic,
            'schoolid'=>$schoolid,
            'username'=>$username, 
            'flagid'=>$flag
        ));
        return true;
    }

    /**
     * 存储刷新后的第三方登录关键参数
    */
    public static function saveRefreshPara($access_token,$expires_in,$refresh_token,$userid){
        // expires_in转成过期日期
        $deadline=date('Y-m-d H:i:s',time()+$expires_in);
        $sql = "
            update tb_imuserid 
            set kiss_access_token='%s', kiss_refresh_token='%s', kiss_token_deadline='%s'
            where userid=%s";
        $sql = sprintf($sql, $access_token, $refresh_token, $deadline, $userid);
        return Db::execute ($sql);
    }

    /**
     * 更新基本信息
     */
    public static function updateBasicInfo($userid,$headpic,$schoolname,$schoolid,$username)
    {
        //防止注入
        $userid = Access::SQLInjectDetect($userid);
        $headpic = Access::SQLInjectDetect($headpic);
        $schoolname = Access::SQLInjectDetect($schoolname);
        $schoolid = Access::SQLInjectDetect($schoolid);
        $username = Access::SQLInjectDetect($username);

        //先判断学校信息是否需要更新
        $school_result = Db::table('tb_school')->where(['schoolid'=>$schoolid,'schoolname'=>$schoolname])->find();
        if(!count($school_result)){
            //更新学校名称
            Db::table('tb_school')->where('schoolid',$schoolid)->update(array(
                'schoolname' => $schoolname
            ));
        }
        $result = Db::table('tb_imuserid')->where('userid', $userid)->update(array(
            'userid' => $userid, 
            'headpic' => $headpic, 
            'schoolid' => $schoolid, 
            'username' => $username
        ));
        return $result;
    }
}