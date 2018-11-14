<?php
/**
 * 管理kiss账号以及权限限制
 * @ret
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/5/15
 * Time: 11:15
 */
namespace app\oauthlog\common;
use think\Db;
use think\Model;
use app\common\model\Access;
use app\common\model\Authority;
use app\common\model\Curl;
use app\oauthlog\cache\Oauth as Oauth_cache;
use app\oauthlog\model\Oauth as Oauth_model;

class KissAuthority extends Model {

    private static $instance = null;
    private $kiss_flag = null;                   // kiss登陆类型
    private $kiss_imuserid = null;

    private static $config = null;                 // 参数数组
    
    private $permitallenable = false;              // 所有已登陆用户均可访问
    private $permitList = null;             // 允许的登陆组

    private $list = null;

    public function __construct () {
        $this->instance = null;
        $this->kiss_flag = null;
        $this->permitallenable = false;

        $this->_loadUserInfo ();
        $this->initVariable ($this->list);
    }

    public static function getInstance ($config=array()) {
        if (is_null(KissAuthority::$instance)) {
            // 配置
            self::$config = $config;
            self::$instance = new KissAuthority ();
            return self::$instance;
        } else {
            return self::$instance;
        }
    }

    /**
     * 用户过滤
     */
    private function _userGlobalFirewall () {
    }

    private function _flagApiFirewall () {
        $tmp_list = $this->permitList;
        if (is_array ($tmp_list)) {
            foreach ($tmp_list as $tmp_flag){
                if ($this->kiss_flag == $tmp_flag) {
                    // 登陆类型包括在里面就通过
                    return true;
                }
            }
        }
        Access::Respond (0, array(), '此账户没有访问权限');
    }

    /**
     * 根据userid加载kiss登录信息
     */
    private function _loadDbInfoWithUserid ($userid) {
        $sql = "select I.imuserid, I.kiss_access_token, I.kiss_token_deadline, I.kiss_refresh_token, 
                    I.schoolid, I.username, I.flagid, I.headpic, F.flagname, S.schoolname 
                from tb_imuserid as I, tb_school as S, tb_flag as F 
                where userid=$userid and I.schoolid=S.schoolid and F.flagid=I.flagid limit 1
        ";
        $ret = Db::query ($sql);
        if (count($ret) > 0) {
            return $ret[0];
        } else {
            // Access::Respond (0, array(), '此账户还得到kiss授权');
        }
    }

    /**
     * 根据access_token获取kiss用户信息
     */
    private function _loadUserInfo () {
        Authority::getInstance ()->loadAccount ($_flag, $_userid);

        if ($_flag == -1 || $_userid == -1) {
            return $this->list;
        }
        // 注意$_flag和_flag是不一样的, $_userid和$userid也不一样, 一个是本系统的(flag, userid), 一个是kiss系统的(flag, userid)
        $dbInfo = $this->_loadDbInfoWithUserid ($_userid);
        $this->list[FLAG] = $dbInfo['flagid'];
        $this->list[IMUSERID] = $dbInfo['imuserid'];
        $this->list[ACCESS_TOKEN] = $dbInfo['kiss_access_token'];
        $this->list[REFRESH_TOKEN] = $dbInfo['kiss_refresh_token'];
        $this->list[ACCESS_DEADLINE] = $dbInfo['kiss_token_deadline'];
        // $this->list[REFRESH_DEADLINE] = $dbInfo['kiss_token_deadline'];
        $this->list[SCHOOLID] = $dbInfo['schoolid'];
        $this->list[SCHOOLNAME] = $dbInfo['schoolname'];
        $this->list[HEADPIC] = $dbInfo['headpic'];
        $this->list[HEADPIC] = $dbInfo['headpic'];
        $this->list[FLAGNAME] = $dbInfo['flagname'];
        return $this->list;
    }

    /**
     * 初始化变量
     */
    private function initVariable ($list) {
        $this->kiss_flag = $list[FLAG];
        $this->kiss_imuserid = $list[IMUSERID];
    }

    /**
     * 重新加载kiss配置
     */
    public function reloadUserInfo () {
        $this->_loadUserInfo ();
        return self::$instance;
    }

    /**
     * 配置接口访问权限
     */ 
    public function permit ($flagArr) {
        $this->permitList = $flagArr;
        return self::$instance;
    }

    /**
     * 对所有登陆组公开
     */
    public function permitAll ($enable) {
        $this->permitallenable = $enable;
        return self::$instance;
    }

    /**
     * 自动刷新token
     */
    private function autoRefreshToken () {
        $access_token = $this->list[ACCESS_TOKEN];

        $ret = Oauth_cache::GetTokenValid ($access_token);
        if ($ret == false) {
            // 到了token刷新周期, 完成刷新
            $this->refreshToken ();

            // 重新加载
            $this->reloadUserInfo ();
        } else {
        }
    }

    /**
     * 刷新refresh_token
     */
    public function refreshToken () {

        Authority::getInstance ()->loadAccount ($flag, $userid);
        $result = $this->list;

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

            self::ResetTokenPeriod ($access_token, $expires_in);
            // Access::Respond(1,array(),'更新token成功');
        }else{
            // Access::Respond(0,$list,$list['msg']);
        }
    }

    // 清理
    public function clean () {
        $access_token = $this->list[ACCESS_TOKEN];
        Oauth_cache::DelTokenValid ($access_token);
        return self::$instance;
    }

    /**
     * 重置token刷新周期
     */
    public static function ResetTokenPeriod ($access_token, $expire_in) {
        $tmp_expire = floor(($expire_in-10)/2);
        Oauth_cache::SetTokenValid ($access_token, $tmp_expire);
    }

    /** 
     * 校验access_token的合法性
     */
    public function check () {

        // 完成token的刷新检查, 如果需要刷新token则直接刷新
        $this->autoRefreshToken ();
        
        // 验证用户全局访问权限
        $this->_userGlobalFirewall ();

        // 验证接口访问权限
        if (!$this->permitallenable) {
            $this->_flagApiFirewall ();
        }
        return self::$instance;
    }

    public function loadAccount () {

        return $this->list;
    }

    /**
     * 发送http请求到kiss端
     */
    public static function _Post ($route, array $params) {
        $result=Curl::sendpost(KISS_LOG_HTTPS.'/'.$route, $params);
        $list=Access::Curldecode($result);
        if ($list == null) {
            /*Access::Respond (0, array(
                'result' => $result
            ), "请求方出现异常");*/
            echo $result;exit();
        }
        return $list;
    }

    /**
     * redis日志
     */
    public function _LOG ($imuserid, $code, $msg) {
        // 获取请求时间，请求方ip，请求相对路径
        $redis['data']['ip'] = $_SERVER["REMOTE_ADDR"];
        $redis['data']['time'] = date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']);
        $redis['data']['uri'] = $_SERVER['REQUEST_URI'];
        $redis['data']['imuserid'] = $imuserid;
        $redis['code'] = $code;
        $redis['msg'] = $msg;
 
        Oauth_cache::SetLoginInfo(Access::json_arr($redis));
    }
}
?>
