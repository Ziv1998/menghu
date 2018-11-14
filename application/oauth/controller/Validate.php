<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/9/8
 * Time: 17:01
 */
namespace app\oauth\controller;

use app\common\model\Access;
use think\Controller;
use app\common\model\Curl;
use think\Session;
use app\oauth\model\Validate as Validate_model;
use app\common\model\Authority;
class Validate extends Controller
{
    /**
     * 获取token
    */
    public function check(){
        //加载数据
        Authority::getInstance()->loadData();

        //必选参数
        $appid=Access::MustParamDetect('appid');
        $state=Access::MustParamDetect('state');

        // 如果此appid不为指定的appid则报错
        if ($appid != KISS_APPID) {
            Access::Respond(0, array(), '请检查待授权服务的appid的配置');
        }

        //post过去的数据
        $data=array(
            'appid' =>KISS_APPID,
            'appkey'=>md5(KISS_APPKEY),
            'state'=>$state,
        );
        $result=Curl::sendpost(KISS_HTTPS.'/'.KISS_URL_GETTOKEN,$data);

        $list=Access::Curldecode($result);
        if($list['code']==1) {
            //存储token
            $result=Validate_model::updateToken($list['data']['token']);
            if($result) {
                Access::Respond(1,$list['data']['token'], '获取token成功');
            }else{
                Access::Respond(0,array(),'获取token失败');
            }
        } else{
            Access::Respond(0,array(),$list['msg']);
        }
    }

    /**
     * 绑定userid和imuserid
    */
   /* public function bind(){
        //必选参数
        $token=Access::MustParamDetect('token');
        $imuserid=Access::MustParamDetect('imuserid');
        //校准token是否正确
        if($token==Validate_model::readToken()){
            //判断imuserid是否已经存在了
            $is=Validate_model::checkImuserid($imuserid);
            if($is){
                Access::Respond(1,array(),'绑定成功');
            }
            //将imuserid存储，并和userid绑定
            $result=Validate_model::bindUserid($imuserid);
            if($result){
                Access::Respond(1,array(),'绑定成功');
            }else{
                Access::Respond(0,array(),'绑定失败');
            }
        }else{
            Access::Respond(0,array(),'token错误，无法绑定');
        }
    }*/

    /**
     * 获取K.I.S.S系统传来的数据
    */
    public function getData(){
        //必选参数
        $imuserid=Access::MustParamDetect('imuserid');
        //判断是否已经登录
        Authority::getInstance()->loadData()->permit (array(F_MANAGER, F_USER))->check();
        //从数据库中调取token
        $token=Validate_model::readToken();
        $appid=KISS_APPID;
        $data=array(
            'imuserid'=>$imuserid,
            'token'=>$token,
            'appid'=>$appid,
        );
        $result=Curl::sendpost(KISS_HTTPS.'/'.KISS_URL_GETDATA,$data);
        $list=Access::Curldecode($result);
        if($list['code']==0){
            Access::Respond(0,array(),$list['msg']);
        }else{
            Access::Respond(1,$list['data'],'获取成功');
        }
    }
}