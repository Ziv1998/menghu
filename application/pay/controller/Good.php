<?php
/**
 * K支付商品管理
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/5/15
 * Time: 10:18
 */
namespace app\pay\controller;
use app\common\model\Admin;
use app\common\model\Access;
use app\common\model\Authority;
use think\Controller;
use app\pay\model\Good as Good_Model;
use app\pay\common\PayAuthority;

class Good extends Controller
{
    /** 读取商品列表 */
    public function read () {
        $schoolid = Access::OptionalParam ('schoolid');
        $goodid = Access::OptionalParam ('goodid');
        $start = Access::OptionalParam ('start');
        $limit = Access::OptionalParam ('limit');

        Authority::getInstance()->permitAll (true)->check(true)->loadAccount ($flag, $userid);
        // PayAuthority::getInstance()->permit (array(PAY_F_COMPANY))->check(true)->loadAccount ($flag, $userid)->onlyAdmin ();

        // if ($flag != PAY_F_COMPANY) {
        //     // 非公司账号不能使用
        //     Access::Respond (0, array(), '非公司账号，无权限');
        // }
        $ret = Good_Model::read (array(
            "schoolid" => $schoolid,
            "goodid" => $goodid,
            "userid" => $userid,
            "start" => $start,
            "limit" => $limit,
        ));
        Access::Respond (1, $ret, '成功读取支付商品列表');
    }

    /** 添加新商品 */
    // public function add () {
    //     $name = Access::MustParamDetect ('name');               // 商品名
    //     $total_fee = Access::MustParamDetect ('total_fee');     // 金额
    //     $alias = Access::MustParamDetect ('alias');     // 商品别名

    //     // 限制为公司账号
    //     PayAuthority::getInstance()->permit (array(PAY_F_COMPANY))->check(true)->loadAccount ($flag, $userid);

    //     if ($flag != PAY_F_COMPANY) {
    //         // 非公司账号不能使用
    //         Access::Respond (0, array(), '非公司账号，无权限');
    //     }
    //     $ret = Good_Model::in (array(
    //         "name" => $name,
    //         "alias" => $alias,
    //         "total_fee" => $total_fee
    //     ));
    //     if ($ret == false) {
    //         Access::Respond (0, array(), '新增商品出现错误, 请联系客服');
    //     } else {
    //         Access::Respond (1, array(), '成功新增商品');
    //     }
    // }

    /** 更新商品 */
    // public function update () {
    //     $name = Access::OptionalParam ('name');               // 商品名
    //     $total_fee = Access::OptionalParam ('total_fee');     // 金额
    //     $alias = Access::OptionalParam ('alias');     // 商品别名

    //     // 商品id
    //     $goodid = Access::OptionalParam ('goodid');     // 商品id

    //     // 限制为公司账号
    //     PayAuthority::getInstance()->permit (array(PAY_F_COMPANY))->check(true)->loadAccount ($flag, $userid);

    //     if ($flag != PAY_F_COMPANY) {
    //         // 非公司账号不能使用
    //         Access::Respond (0, array(), '非公司账号，无权限');
    //     }
    //     $ret = Good_Model::upd ($goodid, array(
    //         "name" => $name,
    //         "alias" => $alias,
    //         "total_fee" => $total_fee
    //     ));
    //     if ($ret == false) {
    //         Access::Respond (0, array(), '更新商品出现错误, 请联系客服');
    //     } else {
    //         Access::Respond (1, array(), '成功更新商品');
    //     }
    // }

    /** 删除商品 */
    // public function delete()
    // {
    //     //用数组装载必选参数
    //     $goodid=null;
    //     $array['goodid']=null;
    //     $mes = array ();

    //     //定义全局变量
    //     $userid=null;
    //     $flag=null;
    //     $no_goodid=null;
    //     $no_num=0;  //用来统计不能删除的id号的数量

    //     // 必须参数
    //     foreach ($array as $key=>$value){
    //         $$key = Access::MustParamDetect ($key);
    //     }
            
    //     // 限制为公司账号
    //     PayAuthority::getInstance()->permit (array(PAY_F_COMPANY))->check(true)->loadAccount ($flag, $userid);

    //     if ($flag != PAY_F_COMPANY) {
    //         // 非公司账号不能使用
    //         Access::Respond (0, array(), '非公司账号，无权限');
    //     }

    //     //将字符串切割成数组
    //     $arr_goodid=explode(SPLIT_SIGN,$goodid);
    //     foreach ($arr_goodid as $k => $new_goodid) {
    //         $result = Good_Model::del($new_goodid);
    //         if (!$result) {
    //             $no_goodid[$no_num++]['id']=$new_goodid;
    //         }
    //     }
    //     if(isset($no_goodid)){
    //         foreach ($no_goodid as $key=>$value){
    //             $mes['data'][$key]['id']=$value['id'];
    //         }
    //         Access::Respond (0, $mes['data'], '部分商品无法删除');
    //     } else{
    //         $mes['data']=array();
    //         $mes['msg']='删除成功';
    //         $mes['url']='';
    //         $mes['code']=1;
    //         Access::Respond (1, array (), '成功删除');
    //     }
    // }
    /** 读取已购买的商品列表 */
    public function orderlist () {
        $start = Access::OptionalParam ('start');
        $limit = Access::OptionalParam ('limit');

        Authority::getInstance()->permitAll (true)->check(true)->loadAccount ($flag, $userid);
        // PayAuthority::getInstance()->permit (array(PAY_F_COMPANY))->check(true)->loadAccount ($flag, $userid)->onlyAdmin ();

        // if ($flag != PAY_F_COMPANY) {
        //     // 非公司账号不能使用
        //     Access::Respond (0, array(), '非公司账号，无权限');
        // }
        $ret = Good_Model::orderlist (array(
            "userid" => $userid,
            "start" => $start,
            "limit" => $limit,
        ));
        Access::Respond (1, $ret, '成功读取订单列表');
    }
    // public function test(){
    //     $money = Access::OptionalParam ('money');

    //     $money = Good_Model::_decrypt ($money);
    //     print_r($money);
    // }
}
