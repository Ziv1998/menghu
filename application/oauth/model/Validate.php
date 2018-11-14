<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/9/23
 * Time: 9:39
 */
namespace app\oauth\model;

use app\common\model\Access;
use think\Db;
use think\Exception;
use think\Model;

class Validate extends Model
{
    /**
     * 绑定userid
    */
    public static function bindUserid($imuserid){
        //放注入
        $imuserid=Access::SQLInjectDetect($imuserid);
        $sql="INSERT INTO `tb_user` (`userid`, `sex`, `username`, `phone`, `updatetime`,
 `logintime`, `password`) VALUES (NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, NULL, NULL)";
        Db::execute($sql);
        //获取新插入的userid
        $userid=Db::table('tb_userid')->getLastInsID();
        //将数据插入到tb_imuserid_userid
        try{
            $list['userid']=$userid;
            $list['imuserid']=$imuserid;
            $result=Db::table('tb_imuserid')->insert($list);
            return $result;
        }catch (Exception $e){
            Access::Respond(0,array(),'绑定失败');
        }
    }

    /**
     * 判断imuserid是否已经存在
    */
    public static function checkImuserid($imuserid){
        //防注入
        $imuserid=Access::SQLInjectDetect($imuserid);
        $result=Db::table('tb_imuserid')->where('imuserid',$imuserid)->find();
        if(count($result)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 存储kiss中的token
    */
    public static function updateToken($token){
        //防注入
        $list['value']=Access::SQLInjectDetect($token);
        try{
            $result=Db::table('tb_vlist')->where('keyword',SQL_KISS_TOKEN)->update($list);
            return $result;
        }catch (Exception $e){
            Access::Respond(0,array(),'token存储失败');
        }
    }

    /**
     * 读取kiss中的token
    */
    public static function readToken(){
        $result=Db::table('tb_vlist')->where('keyword',SQL_KISS_TOKEN)->field('value')->find();
        if(count($result)>0){
            return $result['value'];
        }else{
            Access::Respond(0,array(),'读取token失败');
        }
    }
}