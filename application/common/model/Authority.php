<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/12
 * Time: 22:35
 */

namespace app\common\model;


use think\Db;
use think\Model;
use think\Session;
use app\oauthlog\common\KissAuthority;

class Authority extends Model
{
    private static $instance=null;
    private $userid=null;
    private $flag=null;
    private $password=null;
    private $access_token = null;           // 客户接口访问token
    
    private static $config = null;                 // 参数数组
    public static $Session_enable = false;      // session是否使能

    private $permitallenable=false;             //所有已登录用户均可访问
    private $permitList=null;                   //允许的登录组
    private $isLoad = false;
    private $isLogincheck = true;       // 是否检测是否登录

    const SESSION_ID = "sessionID";
    const SESSION_ENABLE = "session_disable";

    public function __construct(){
        $this->instance=null;
        $this->userid=null;
        $this->flag=null;

        $this->access_token = null;
        $this->permitallenable = false;

        // 初始化的时候才启动session以及加载数据库常量
        $this->loadData();
        $this->startSession ();
        $this->loadInterface();      // 加载接口地址配置
    }

    public static function getInstance ($config=array(
            Authority::SESSION_ID => "", 
            Authority::SESSION_ENABLE => "yes"
        )) {
        if (is_null(Authority::$instance)) {
            // 配置
            self::$config = $config;
            self::$instance = new Authority ();
            return self::$instance;
        } else {
            return self::$instance;
        }
    }

    public function setLoginCheck ($isLogincheck) {
        $this->isLogincheck = $isLogincheck;
        return self::$instance;
    }

    /**
     * 空操作
     */
    public function e () {
    }

    /**
     * 获取session目录
     */
    public static function _SessionDir () {
        return SESSION_ROOT.':session:';
    }

    /**
     * 启动session
     */
    private function startSession () {
        if (!$this->ifSessionEnable ()) {
            return ;
        }
        if (self::$config == null)  {
        } else {
            $sessionID = Access::IfKeyExists (self::$config, Authority::SESSION_ID);
            if ($sessionID != null && $sessionID != "") {
                // 指定session_id
                session_id ($sessionID);
            }
        }
        session([
            'prefix'     => 'sess_',
            'type'       => 'redis',
            'host'       => REDIS_HOST,
            'port'       => '6379',
            'password'   => REDIS_PASS,
            'expire' => 259200,
            'auto_start' => true,
            'session_name'=> self::_SessionDir ()     // 配置redis目录
        ]);
        // Session::start ();
    }

    /**
     * 是否不使能Session
     */
    private function ifSessionEnable () {
        // 默认session_enable为false
        Authority::$Session_enable = false;

        if (self::$config == null) {
            // 不使能session
        } else {
            $session_enable = Access::IfKeyExists (self::$config, Authority::SESSION_ENABLE);

            if ($session_enable === null || $session_enable === '' || strtoupper($session_enable) === 'NO') {
            } else {
                Authority::$Session_enable = true;
            }
        }
        return Authority::$Session_enable;
    }

    /**
     * 获取sessionID
     */
    public function getSessionID () {
        if (!self::$Session_enable) {
            return "";
        }
        return session_id ();
    }

    /**
     * 删除session
     */
    private function destroySession () {
        if (!self::$Session_enable) {
            return ;
        }
        // 删除所有包括SESSION_ROOT的session值
        foreach($_SESSION as $key => $val) {
            if (strpos ($key, SESSION_ROOT) !== false) {
                $this->DelSession ($key);
            }
        }
        Session::clear ();
        
        // 讨厌的Session object destruction failed 警告
        error_reporting(E_ERROR);

        Session::destroy ();
    }

    /**
     * 获取Session
     */
    public static function GetSession ($key) {
        if (!self::$Session_enable) {
            return "";
        }
        return Session::get ($key);
    }

    public static function HasSession ($key) {
        if (!self::$Session_enable) {
            return "";
        }
        return Session::has ($key);
    }

    /**
     * 设置session
     */
    public static function SetSession ($key, $value) {
        if (!self::$Session_enable) {
            return ;
        }
        Session::set ($key, $value);
    }

    /**
     * 删除Session
     */
    public static function DelSession ($key) {
        if (!self::$Session_enable) {
            return ;
        }
        Session::delete ($key);
    }

    /**
     * 获取session
     */
    public function get_session ($key) {
        if (!self::$Session_enable) {
            return "";
        }
        return Session::get ($key);
    }

    /*
     * 检查登录状态
     */
    public function ifLogin () {
        if (Authority::HasSession (SESSION_USERID) && Authority::HasSession (SESSION_FLAG) && 
            Authority::HasSession (SESSION_ACCESSTOKEN)) {
            // 已经登录
            return true;
        } else {
            // 未登录或者登录过期
            return false;
        }
    }


    /**
     * 获取登录的session
    */
    private function _loadSession(){
        $this->userid=Session::get(SESSION_USERID);
        $this->password=Session::get(SESSION_PASSWD);
        $this->flag=Session::get(SESSION_FLAG);
        $this->access_token = Session::get (SESSION_ACCESSTOKEN);       // 加载access_token
        
//        if (isset($this->userid) && isset($this->flag) && isset($this->access_token)
        if ($this->isLogincheck) {
            if ($this->ifLogin ()) {
            } else {
                $this->clean ();
                Access::Respond (-1, array(), '请先进行登录');
            }
        }
    }
    
    /**
     * 清除缓存
     */
    private function cleanCache () {
        KissAuthority::getInstance ()->clean ();
    }
    
    /**
     * 清除Session
    */
    public function clean(){
        $this->cleanCache ();
        $this->destroySession ();
        return self::$instance;
    }

    /**
     * 配置接口访问权限
    */
    public function permit($flagArr){
        $this->permitList=$flagArr;
        return self::$instance;
    }

    /**
     * 对所有登录组公开
    */
    public function permitAll($enable){
        $this->permitallenable=$enable;
        return self::$instance;
    }

    /**
     * 用户过滤
    */
    private function _flagApiFirewall () {
        $tmp_list = $this->permitList;
        if (is_array ($tmp_list)) {
            foreach ($tmp_list as $tmp_flag){
                if ($this->flag == $tmp_flag) {
                    // 登陆类型包括在里面就通过
                    return true;
                }
            }
        }
        Access::Respond (0, array(), '此账户没有访问权限');
    }

    /**
     * 验证access_token是否有效
     */
    private function access_token_isvalid () {
        $access_token = Access::MustParamDetect (CLIENT_ACCESS_TOKEN);
        if ($this->access_token != $access_token) {
            // 校验是否相等
            Access::Respond (0, array(), '接口访问令牌有误,'.$this->access_token.','.$access_token);
        }
        return self::$instance;
    }

    /**
     * 验证权限
     * @ischecktoken: true则表示access_token为必须项
    */
    public function check($ischecktoken=true){
        //获取用户登录信息
        /*$this->_loadSession();

        //验证接口访问权限
        if (!$this->permitallenable) {
            $this->_flagApiFirewall ();
        }

        // 校验token
        if ($ischecktoken==true) {
            $this->access_token_isvalid ();
        }*/
        
        $this->switchUser ($ischecktoken);
        return self::$instance;
    }

    /**
     * 获取登录账号
    */
    public function loadAccount (&$flag, &$userid) {
        $flag = $this->flag;
        $userid = $this->userid;
        if ($flag == false && $userid == false) {
            $flag = -1;
            $userid = -1;
        }
        return self::$instance;
    }

    /**
     * 验证并切换管理员权限
     * @ischecktoken: true则表示access_token为必须项
    */
    public function switchUser($ischecktoken=true){
        //获取用户登录信息
        $this->_loadSession();
        if($this->flag==F_MANAGER){
            // 这里还需要判断一下系统账号是否存在, 或者根据管理员账号切换到不同的用户账号
            // 如果有需要可以获取后将此userid保存到session则下次可直接从session（redis）中获取
            /*$this->flag=F_USER;
            $this->userid=MANAGER_USERID;
            $ret = Db::query ("select count(*) as i from tb_user where userid=".$this->userid);
            if ($ret[0]['i'] <= 0) { 
                // 不存在此用户账号
                Access::Respond(0, array(), "此管理员无系统用户权限");
            }*/
        }
        if (!$this->permitallenable) {
            $this->_flagApiFirewall ();
        }

        // 校验token
        if ($ischecktoken==true) {
            $this->access_token_isvalid ();
        }
        return self::$instance;
    }

    /**
     * 获取管理员转换ID
     */
    public function getSwitchUser ($userid=null) {
        return MANAGER_USERID;
    }

    /**
     * 判断是否为管理员
     */
    public function checkIfManagerUser ($userid) {
        Access::Respond(0, array(), "userid:".$userid);
        return ($userid==MANAGER_USERID)?true:false;
    }

    /**
     * 初始化加载数据
    */
    public function loadData(){
        //拆解数组常量
        return self::$instance;
        /*if ($this->isLoad) {
            return self::$instance;
        }
        $SQL_VLIST=json_decode(SQL_VLIST,true);
        foreach ($SQL_VLIST as $key=>$value){
            $this->loadKeyData($key,$SQL_VLIST[$key]);
        }
        $this->isLoad = true;
        return self::$instance;*/
    }

    /**
     * 加载接口地址配置
     */
    public function loadInterface () {
        $ROUTE_LIST=json_decode(ROUTE_LIST,true);
        foreach ($ROUTE_LIST as $key=>$value){
            define($key, $value);
        }
    }
    
    /**
     * 从数据库加载数据并判断数据是否存在
     */
    private function loadKeyData($key,$value){
        //构造全局变量，截取键名的后两段
        $arr=explode('_',$key,2);
        $ifHasSession = Authority::HasSession (SESSION_ROOT."_".$arr[1]);
        if(!$ifHasSession) {
            $sql = "SELECT value FROM tb_vlist WHERE keyword='$value'";
            $result = Db::query($sql);
            if (count($result) <= 0) {
                Access::Respond(0, array(), $value . '数据加载失败');
            } else {
                define($arr[1], $result[0]['value']);
                Authority::SetSession(SESSION_ROOT."_".$arr[1], $result[0]['value']);
            }
        } else {
            define($arr[1],Authority::GetSession(SESSION_ROOT."_".$arr[1]));
        }
    }

    /**
     * 判断是否为系统用户
    */
    public function isSystemUser(){
        if($this->userid == MANAGER_USERID && $this->flag == F_USER){
        }else{
            Access::Respond(0,array(),'没有操作权限');
        }
        return self::$instance;
    }
}
