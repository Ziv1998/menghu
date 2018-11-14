<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/10/17
 * Time: 14:49
 */

namespace app\oauthlog\controller;
use app\common\model\Access;
use app\common\model\Authority;
use think\Controller;
use app\oauthlog\model\Oauth as Oauth_model;
use app\oauthlog\cache\Oauth as Oauth_cache;
use app\oauthlog\common\KissAuthority;
use app\common\model\Curl;

class Oauth extends Controller
{   
    /**
     * 加载kiss用户个人信息
     */ 
    private function _loadUserInfo ($list) {
        $access_token = $list['data']['access_token'];
        $expires_in = $list['data']['expires_in'];
        $refresh_token = $list['data']['refresh_token'];

        // 重置token刷新周期
        KissAuthority::ResetTokenPeriod ($access_token, $expires_in);

        // kiss 请求获取kiss个人信息
        $result=KissAuthority::_Post (KISS_GETBASICINFO, array(
            'access_token' => $access_token
        ));
        if($result['code'] == 1){
            $data_arr = $result['data'];

            // 保存用户kiss信息到本地
            $userid = Oauth_model::saveUserInfo (array(
                'imuserid' => $data_arr['imuserid'],
                'headpic'  => $data_arr['headpic'],
                'schoolid' => $data_arr['schoolid'],
                'schoolname' => $data_arr['schoolname'],
                'flagid'   => $data_arr['flag'],
                'flagname' => $data_arr['flagname'],
                'username' => $data_arr['username'],
                'access_token' => $access_token,
                'expires_in' => $expires_in,
                'refresh_token' => $refresh_token
            ));

            if ($userid === false) {
                // 保存用户信息失败
                Access::Respond(0,array(),'保存用户信息失败');
            }

            // 默认登录
            $data = Access::loginDataOnSuccess (array(
                'username' => $data_arr['schoolname'],
                'userid'   => $userid,
                'flag'     => F_USER
            ));

            Access::Respond(1,$data,'登录成功');
        }else{
            Access::Respond(0, $result, $result['msg']);
        }
    }

    /**
     * 从前端获取code和state，实现第三方登录
     */
    public function oauth(){
        //必选参数
        $code=Access::MustParamDetect('code');
        $state=Access::MustParamDetect('state');

        //加载数据
        Authority::getInstance()->loadData();

        //post过去的数据
        $list=KissAuthority::_Post (KISS_LOG_URL_GETTOKEN, array(
            'code' => $code,
            'state' => $state,
            'secret' => KISS_LOG_APPKEY,
            'appid' => KISS_LOG_APPID,
            'grant_type' => GRANT_TYPE_VALUE,
        ));
        if($list['code'] == 1){
            // 服务器授权成功
            $this->_loadUserInfo ($list);
        }else{
            Access::Respond(0,$list,$list['msg']);
        }
    }

    /**
     * 登录kiss
     */
    public function loginkiss () {
        $list['access_token'] = null;
        //查看必选参数是否存在
        foreach ($list as $key => $value) {
            $list[$key] = Access::MustParamDetect($key);
        }
        Authority::getInstance()->e();

        $list = KissAuthority::_Post (KISS_SERVEROAUTH, array(
            'access_token' => $list['access_token'],
            'appid' => KISS_LOG_APPID,
            'appkey' => KISS_LOG_APPKEY
        ));
        if ($list['code'] == 1) {
            // 服务器授权成功
            $this->_loadUserInfo ($list);
        } else {
            // 服务器授权失败
            Access::Respond(0,$list,$list['msg']);
        }
    }

    /**
     * 更新基本信息
    */
    public function updateBasicInfo(){
        //判断登录状态
        Authority::getInstance()->permit(array(F_USER))->check(false)->loadAccount ($flag, $userid);

        // 获取kiss授权信息
        $result = KissAuthority::getInstance()->permitAll (true)->check()->loadAccount();

        // 发起http请求
        $list = KissAuthority::_Post (KISS_GETBASICINFO, array(
            'access_token' => $result[ACCESS_TOKEN]
        ));

        if($list['code'] == 1){
            //获取到数据，将数据更新
            $result = Oauth_model::updateBasicInfo($userid, $list['data']['headpic'], $list['data']['schoolname'], $list['data']['schoolid'], $list['data']['username']);
            if($result){
                Access::Respond(1,array(),'更新kiss基本信息成功');
            }else{
                Access::Respond(0,array(),'无需更新kiss基本信息');
            }
        }else{
            Access::Respond(0,array(),$list['msg']);
        }
    }

    /**
     *刷新token
    */
    public function refreshToken(){
        //判断登录状态
        Authority::getInstance()->permit(array(F_USER,F_MANAGER))->check(false)->loadAccount ($flag, $userid);

        // 从数据库获取appid和refresh_token
        $result = KissAuthority::getInstance()->permitAll (true)->check()->loadAccount();

        // 发起http请求
        $list = KissAuthority::getInstance()->_Post (KISS_LOG_URL_REFRESHTOKEN, array(
            'grant_type' => 'refreshToken',
            'refresh_token' => $result[REFRESH_TOKEN]
        ));
        if($list['code'] == 1){
            $access_token = $list['data']['access_token'];
            $expires_in = $list['data']['expires_in'];
            // $scope = $list['data']['scope'];
            $refresh_token = $list['data']['refresh_token'];
            //更新数据库
            Oauth_model::saveRefreshPara ($access_token, $expires_in, $refresh_token, $userid);
            Access::Respond(1,array(),'更新token成功');
        }else{
            Access::Respond(0,$list,$list['msg']);
        }
    }

    /**
     * 刷新用户个人信息
     */
    public function refreshUserInfo () {
        //判断登录状态
        Authority::getInstance()->permit(array(F_USER,F_MANAGER))->check(false)->loadAccount ($flag, $userid);

        // 刷新用户个人信息
        $result = KissAuthority::getInstance()->permitAll (true)->check()->loadAccount();

        // 发起http请求
        $list = KissAuthority::getInstance()->post (KISS_GETBASICINFO, array(
            'access_token' => $result[ACCESS_TOKEN]
        ));
        if($list['code'] == 1){
            //获取到数据，将数据存入数据库
            $tmpResult = Oauth_model::inBasicInfo($userid, $list['data']);
            if(!$tmpResult){
                Access::Respond(0,array(),"获取kiss基本信息失败");
            }
            Access::Respond(0, array(), "成功刷新用户个人信息");
        }else{
            Access::Respond(0,array(),$list['msg']);
        }
    }
}