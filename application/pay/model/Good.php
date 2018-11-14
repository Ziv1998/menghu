<?php
/**
 * Good 商品管理
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/5/15
 * Time: 14:01
 */
namespace app\pay\model;

use think\Db;
use think\Model;
use app\common\model\Admin;
use app\common\model\Access;
use app\pay\common\MoneyTools;

class Good extends Model {

    /**
     * 金额加密
     */
    private static function _encrypt ($data) {
        return MoneyTools::getInstance ()->encrypt ($data);
    }

    /**
     * 金额解密
     */
    private static function _decrypt ($data) {
        return MoneyTools::getInstance ()->decrypt ($data);
    }

    /**
     * 读取商品
     */
    public static function read ($arr) {
        $start=Access::IfKeyExists($arr, 'start');
        $limit=Access::IfKeyExists($arr, 'limit');
        //$searchkey=Access::IfKeyExists($arr, 'searchkey');
        $schoolid=Access::IfKeyExists($arr, 'schoolid');
        $goodid=Access::IfKeyExists($arr, 'goodid');
        $userid=Access::IfKeyExists($arr, 'userid');

        $sql = sprintf ("
            select PG.goodsid as goodid, PG.price as money, PGT.name as goodname, PGT.content, PGT.url, PG.time, PS.name as semestar
            from pay_goods as PG 
            left join pay_good_type as PGT
            on PG.typeid=PGT.id 
            left join pay_semestar as PS
            on PG.semestarid=PS.id
            where 1 
        ");

        if (!isset ($limit) || !isset ($start)) {
            $limit = 20;
            $start = 0;
        }
        if (isset ($schoolid)) {
            $sql = sprintf ("%s and PG.groupid=%s", $sql, $schoolid);
        }
        if (isset ($goodid)) {
            $sql = sprintf ("%s and PG.goodsid=%s", $sql, $goodid);
        }
        // if (isset ($searchkey)) {
        //     // 搜索关键词
        //     $sql = sprintf ("%s and (alias='%s' or id='%s')", $sql, $searchkey, $searchkey);
        // }

        $sql = sprintf ("%s limit %s, %s", $sql, $start, $limit);
        $ret = Db::query ($sql);
        for ($i=0;$i<count ($ret);$i++) {
            //$ret[$i]['money'] = self::_decrypt ($ret[$i]['money']);
            // $sql_service = sprintf("select PSS.name as apiclassname, PSS.headpic, PSS.id as apiclassid
            //     from pay_good_service as PGS
            //     left join pay_service as PSS
            //     on PGS.serviceid=PSS.id
            //     where PGS.goodid = %s",$ret[$i]['goodid']);
            // $ret_service = Db::query($sql_service);
            $ret[$i]['buystatus'] = self::getbuystatus($userid,$ret[$i]['goodid']);
            //$ret[$i]['apiclass'] = $ret_service;
        }
        return $ret;
    }

    public static function getbuystatus ($userid,$goodid){
        $curDate = date ("Y-m-d", time ());
        $sql = sprintf("
            select PS.e_date, PS.s_date 
            from pay_good_user as PGU
            left join pay_goods as PG
            on PGU.goodid=PG.goodsid
            left join pay_semestar as PS
            on PG.semestarid=PS.id
            where PGU.userid=%s and PGU.goodid=%s and PS.s_date<='%s' and PS.e_date>='%s'
        ",$userid,$goodid,$curDate,$curDate);
        $ret = Db::query ($sql);
        if (count ($ret) > 0) {
            return 0;
        }else{
            return 1;
        }
    }
    /**
     * 新增商品
     */
    public static function in ($arr) {
        $name=Access::IfKeyExists($arr, 'name');
        $alias=Access::IfKeyExists($arr, 'alias');
        $total_fee=Access::IfKeyExists($arr, 'total_fee');

        // 校验金钱格式
        MoneyTools::check ($total_fee);

        // 加密
        $total_fee = self::_encrypt ($total_fee);
        $sql = sprintf ("insert into pay_good(name, alias, total_fee) values ('%s', '%s', '%s')", $name, $alias, $total_fee);

        try {
            return Db::execute ($sql);
        } catch (Exception $e) {
            return -1;
        }
    }

    /**
     * 更新商品
     */
    public static function upd($goodid, $arr){

        $goodid=Access::SQLInjectDetect($goodid);
        $arr = Access::SQLInjectDetectOfList ($arr);
        $total_fee=Access::IfKeyExists($arr, 'total_fee');

        // 加密
        $arr['total_fee'] = self::_encrypt ($total_fee);

        $result=Db::table('pay_good')->where('id',$goodid)->update($arr);
        if($result){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 删除商品
     */
    public static function del ($goodid) {
        
        $goodid=Access::SQLInjectDetect($goodid);

        $where['id']=$goodid;
        try{
            Db::table('pay_good')->where($where)->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public static function orderlist ($arr){
        $start=Access::IfKeyExists($arr, 'start');
        $limit=Access::IfKeyExists($arr, 'limit');
        $userid=Access::IfKeyExists($arr, 'userid');

        $sql = sprintf ("
            select PA.id as orderid, PA.cn_trade_no as ordernumber, PA.time, PA.cn_money as money, PS.name as semestar, PGT.name as goodname, PGT.url, PGT.content as goodcontent, TG.title as schoolname
            from pay_account as PA 
            left join pay_goods as PG
            on PA.goodid=PG.goodsid 
            left join pay_good_type as PGT
            on PG.typeid=PGT.id
            left join pay_semestar as PS
            on PG.semestarid=PS.id
            left join tb_group as TG
            on PG.groupid=TG.id
            where PA.userid=%s
            order by PA.time desc
        ",$userid);

        if (!isset ($limit) || !isset ($start)) {
            $limit = 20;
            $start = 0;
        }
        // if (isset ($searchkey)) {
        //     // 搜索关键词
        //     $sql = sprintf ("%s and (alias='%s' or id='%s')", $sql, $searchkey, $searchkey);
        // }

        $sql = sprintf ("%s limit %s, %s", $sql, $start, $limit);
        $ret = Db::query ($sql);
        return $ret;
    }
}