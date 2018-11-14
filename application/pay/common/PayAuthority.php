<?php
/**
 * 支付用户管理模块
 */
namespace app\pay\common;
use think\Model;
use app\common\model\Access; 
use app\common\model\Authority;
use think\Session;

class PayAuthority extends Model {
    
    private static $config = null;                 // 参数数组

    private $userid = null;                 // 用户ID
    private $flag = null;                   // 登陆类型
    private $access_token = null;           // 客户接口访问token

    private static $instance = null;
    private $permitallenable = false;              // 所有已登陆用户均可访问
    private $permitList = null;             // 允许的登陆组

    const SESSION_ID = "sessionID";
    const SESSION_ENABLE = "session_disable";
    public static $Session_enable = false;      // session是否使能

    public function __construct() {
        $this->instance = null;
        $this->userid = null;
        $this->flag = null;
        $this->access_token = null;
        $this->permitallenable = false;

        $this->startSession ();
    }

    /**
     * 将会员商品alias解析成id
     * @param $goodsalias
     * @return bool|string
     */
    public static function TranslateAliasToId ($goodsalias) {
        if ($goodsalias == PAY_GOODID_BASICMEMBER) {
            // 基础会员
            $usertypeid = MEMBER_ID_NORMAL;
        } else if ($goodsalias == PAY_GOODID_STARMEMBER) {
            // 星级会员
            $usertypeid = MEMBER_ID_STAR;
        } else {
            return false;
        }
        return $usertypeid;
    }

    /**
     * 将会员商品id解析成alias
     * @param $usertypeid
     * @return bool|string
     */
    public static function TranslateIdToAlias ($usertypeid) {
        if ($usertypeid == MEMBER_ID_NORMAL) {
            // 基础会员
            $goodsalias = PAY_GOODID_BASICMEMBER;
        } else if ($usertypeid == MEMBER_ID_STAR) {
            // 星级会员
            $goodsalias = PAY_GOODID_STARMEMBER;
        } else {
            return false;
        }
        return $goodsalias;
    }

    /**
     * 是否不使能Session
     */
    private function ifSessionEnable () {
        // 默认session_enable为false
        self::$Session_enable = false;

        if (self::$config == null) {
            // 不使能session
        } else {
            $session_enable = Access::IfKeyExists (self::$config, self::SESSION_ENABLE);

            if ($session_enable === null || $session_enable === '' || strtoupper($session_enable) === 'NO') {
            } else {
                self::$Session_enable = true;
            }
        }
        return self::$Session_enable;
    }

    public static function getInstance ($config=array(
            PayAuthority::SESSION_ID => "", 
            PayAuthority::SESSION_ENABLE => "yes"
        )) {
        if (is_null(PayAuthority::$instance)) {
            // 配置
            self::$config = $config;
            self::$instance = new PayAuthority ();
            return self::$instance;
        } else {
            return self::$instance;
        }
    }

    public function _e () {}

    /**
     * 验证access_token是否有效
     */
    private function access_token_isvalid () {
        $access_token = Access::MustParamDetect (CLIENT_ACCESS_TOKEN);
        if ($this->access_token != $access_token) {
            // 校验是否相等
            Access::Respond (0, array(), '接口访问令牌有误');
        }
        return self::$instance;
    }

    /**
     * 获取session目录
     */
    public static function _SessionDir () {
        return SESSION_ROOT.':ksession:';
    }

    /**
     * 启动session
     */
    private function startSession () {
        if (!$this->ifSessionEnable ()) {
            return ;
        }
        $this->ifSessionStart = true;

        if (self::$config == null)  {
        } else {
            $sessionID = Access::IfKeyExists (self::$config, PayAuthority::SESSION_ID);
            if ($sessionID != null && $sessionID != "") {
                // 指定session_id
                session_id ($sessionID);
            }
        }
        // 初始化session
        Session::init([
            'prefix'     => 'kipay_',
            'type'       => 'redis',
            'host'       => REDIS_HOST,
            'port'       => '6379',
            'password'   => REDIS_PASS,
            'expire' => 259200,
            'auto_start' => true,
            'session_name'=> PayAuthority::_SessionDir ()     // 配置redis目录
        ]); 
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

    /*
     * 检查登录状态
     */
    public function ifLogin () {
        if (PayAuthority::HasSession (SESSION_PAY_USERID) && PayAuthority::HasSession (SESSION_PAY_FLAG) && 
            PayAuthority::HasSession (SESSION_PAY_ACCESSTOKEN)) {
            // 已经登录
            return true;
        } else {
            // 未登录或者登录过期
            return false;
        }
    }

    public function clean () {
        error_reporting(E_ERROR);
        Session::destroy ();
        return self::$instance;
    }

    /**
     * 获取登陆的session
     */
    private function _loadSession () {
        $this->userid = PayAuthority::GetSession (SESSION_PAY_USERID);
        $this->flag = PayAuthority::GetSession (SESSION_PAY_FLAG);
        $this->access_token = PayAuthority::GetSession (SESSION_PAY_ACCESSTOKEN);       // 加载access_token
        if ($this->ifLogin ()) {
            // 已经登录
        } else {
            // 未登录则清除session
            $this->clean ();
            Access::Respond (-1, array(), '请先进行登录');
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
                if ($this->flag == $tmp_flag) {
                    // 登陆类型包括在里面就通过
                    return true;
                }
            }
        }
        Access::Respond (0, array(), '此账户没有访问权限');
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
     * 获取sessionID
     */
    public function getSessionID () {
        return session_id ();
    }

    /**
     * 验证权限
     * @ischecktoken: true则表示access_token为必须项
     */
    public function check ($ischecktoken=true) {

        // 获取用户登陆信息
        $this->_loadSession ();

        // 验证用户全局访问权限
        $this->_userGlobalFirewall ();

        // 验证接口访问权限
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
     * 只允许管理员
     */
    public function onlyAdmin () {
        if (md5($this->userid) !== md5(PAY_SYS_COMPANYID)) {
            Access::Respond (0, array(), '无操作权限, 直接退出');
        } 
        return self::$instance;
    }

    /**
     * 获取登陆账号
     */
    public function loadAccount (&$flag, &$userid) {
        $flag = $this->flag;
        $userid = $this->userid;
        return self::$instance;
    }
}
?>