<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/8
 * Time: 22:05
 * Useage:常用工具
 */
namespace app\common\model;
use think\Log;
use think\Model;
use think\Db;
use app\common\model\Authority;
use think\Request;

class Access extends Model
{
    /**
     * 防SQL注入，对关键字进行过滤
    **/
    public static function SQLInjectDetect ($content) {
        // $content = addslashes ($content);
        if (isset($content)) {
            if (!get_magic_quotes_gpc()) { // 判断magic_quotes_gpc是否为打开
                $content = addslashes ($content);
                $content = str_replace("_", "\_", $content); // 把 '_'过滤掉
                $content = str_replace("%", "\%", $content); // 把' % '过滤掉
                // $content = nl2br($content); // 回车转换
                $content= htmlspecialchars($content); // html标记转换
            }
            if ($content === "") {
                // $content = "0";
                return ;      // 返回一个未定义的值
            }
        }
        return $content;
    }

    /**
     * 防止SQL注入,对数组中的某个元素进行检测
     */
    public static function SQLInjectDetectWithIssetCheck ($lists, $key) {
        if (isset ($lists[$key])) {
            return Access::SQLInjectDetect($lists[$key]);
        } else {
            return ;
        }
    }

    /**
     * 防止SQL注入,对数组进行检测
     */
    public static function SQLInjectDetectOfList ($lists) {
        // SQL防注入处理
        $ret = array();
        foreach ($lists as $key=>$value){
            $tmp = Access::SQLInjectDetect ($lists[$key]);
            if (isset($tmp)) {
                $ret[$key] = $tmp;
            }
        }
        return $ret;
    }

    /**
     * 响应客户端
     */
    public static function Respond ($code, $data, $msg) {
        $mes = array();
        $mes['data']=$data;
        $mes['code']=$code;
        $mes['success']=$code;
        $mes['url']='';
        $mes['msg']=$msg;
        header('Content-Type: application/json');
        echo json_encode($mes, 256);
        // 监控
        // Statistics::getInstance ()->report ($Model, $func, $code, $code, $msg);
        exit();
    }

    /**
     * 响应客户端，显示数量
    */
    public static function RespondWithCount ($code, $data, $msg, $count) {
        $mes = array();
        $mes['data']=$data;
        $mes['code']=$code;
        $mes['url']='';
        $mes['msg']=$msg;
        $mes['totalCount']=$count;
        header('Content-Type: application/json');
        echo json_encode($mes,256);
        exit();
    }

    /**
     * 必须参数的获取
     */
    public static function MustParamDetect ($key) {
        if (!input('?'.$key)) {
            Access::Respond (0, "", "缺少参数".$key);
        } else {
            /** 有传递值 */
            $value = "";
            if(isset($_POST[$key])) {
                $value = $_POST[$key];
            }
            else{
                $value=$_GET[$key];
            }
            // 防止SQL注入
            $value = Access::SQLInjectDetect ($value);

            if (!isset($value) || $value == "") {
                Access::Respond (0, "", "参数".$key."不能为空");
            }
            return $value;
        }
    }

    /**
    /**
     * 对数组必须参数的获取
     */
    public static function MustParamDetectOfList ($listKeys) {
        // 必须参数
        foreach ($listKeys as $key=>$value){
            $listKeys[$key]=Access::MustParamDetect ($key);
        }
        return $listKeys;
    }

    /**
     * 可选参数的获取
     */
    public static function OptionalParam ($key) {
        if (!input('?'.$key)) {
            return ;
        } else {
            /** 有传递值 */
            $value = "";
            if(isset($_POST[$key])) {
                $value = $_POST[$key];
            }
            else{
                $value=$_GET[$key];
            }

            // 防止SQL注入
            $value = Access::SQLInjectDetect ($value);
            return $value;
        }
    }

    /**
     * 数组可选参数的获取
     */
    public static function OptionalParamOfList ($listKeys) {
        foreach ($listKeys as $key=>$value){
            $listKeys[$key]=Access::OptionalParam ($key);
        }
        return $listKeys;
    }

    /**
     * 获取对应的拓展名规则
     */
    public static function getFileExtendConf ($type = "icon") {
        $obj = json_decode(FILE_TYPE, true);

        if (isset ($obj[$type])) {
            // 判断是否包括
            $tmp_arr = $obj[$type];
            if ($tmp_arr != null) {
                return $tmp_arr;
            }
            return ['size'=>0,'ext'=>'jpg'];
        } else {
            return ['size'=>0,'ext'=>'jpg'];
        }
    }

    /**
     * 输入格式校验
     */
    public static function CheckParamFormat($content,$format='phone'){
        switch($format){
            case 'phone':{
                /**判断手机格式*/
                if(!is_numeric($content)){
                    return false;
                }
                return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $content) ? true : false;
            }break;
            case 'date':{
                /**判断日期*/
                if(date('Y-m-d',strtotime($content))==$content){
                    return true;
                }else{
                    Access::Respond(0,array(),'时间格式错误');
                }
            }break;
            case 'time':{
                /**判断日期*/
                if(date('Y-m-d H:i:s',strtotime($content))==$content){
                    return true;
                }else{
                    Access::Respond(0,array(),'时间格式错误');
                }
            }break;
            case 'account':{
                /**判断登录帐号*/
                if(!is_numeric($content)){
                    return false;
                }
                return preg_match('/[\d]{10}/',$content)?true:false;
            }break;
            case 'isnumber': {
                if (!is_numeric($content)) {
                    return false;
                }
                return true;
            } break;
            case 'password':{
                /**判断密码*/
                $content=trim($content);
                $len=strlen($content);
                if($len>=6 && $len<=15){
                    return true;
                }else{
                    return false;
                }
            }break;
            case 'flagid': {
                if ($content == F_TEACHER || $content == F_PARENT || $content == F_MANAGER) {
                    return true;
                }
                return false;
            } break;
            case 'msgtype': {
                if ($content == M_VIDEO || $content == M_NOTICE || $content == M_PICTURE) {
                    return true;
                }
                return false;
            } break;
            case 'point':{
                if($content == FROM || $content == DESTINATION){
                    return true;
                }else{
                    return false;
                }
            }break;
            case 'sex':{
                if($content == 1 || $content == 0){
                    return true;
                }else{
                    return false;
                }
            }break;
            case 'passStatus':{
                if($content == FAIL || $content == SUCCESS){
                    return true;
                }else{
                    return false;
                }
            }break;
            case 'status':{
                if($content == CANCEL || $content == ACTIVE){
                    return true;
                }else{
                    return false;
                }
            }break;
            default:{
                /**判断非法字符*/
                return (Access::SQLInjectDetect($content)===$content)?true:false;
            }break;
        }
        return false;
    }

    /**
     * 判断可选参数的格式是否符合
    */
    public static function CheckOptionFormat($content,$format){
        if(isset($content)){
            $result=self::CheckParamFormat($content,$format);
            return $result;
        }
        return true;
    }

    /**
     * 解析通过Curl传过的值
     */
    public static function Curldecode($result)
    {
        //如果通过json传递
        return json_decode($result, true);
    }

    /**
     * 生成一位随机数
     */
    public static function getRand(){
        $randArr = array();
        $randArr[0] = rand(0, 9);
        $randArr[1] = chr(rand(0, 25) + 97);
        shuffle($randArr);
        return $randArr[0];
    }

    /**
     * 判断是否存在数组索引
     */
    public static function IfKeyExists ($arr, $key) {
        if(array_key_exists ($key, $arr)){
            if(is_string($arr[$key])){
                if(mb_strlen($arr[$key],"utf-8")>255){
                    self::Respond(0,[],"文本过长，所输入文本最大字数为255");
                }
            }
            return $arr[$key];
        }else{
            return null;
        }
        //return array_key_exists ($key, $arr)?$arr[$key]:null;
    }

    /**
     * 登录成功返回的数据
     */
    public static function loginDataOnSuccess ($arr) {
        $userid = Access::IfKeyExists ($arr, 'userid');
        $flag = Access::IfKeyExists ($arr, 'flag');
        $password = Access::IfKeyExists ($arr, 'password');
        $username = Access::IfKeyExists ($arr, 'username');
        $limit_flagid = Access::IfKeyExists ($arr, 'limit_flagid');
        $schoolname = null;
        $headpic = null;

        $sqlUserid = $userid;
        if ($flag == F_MANAGER) {
            // 管理员做一次用户转换
            // $sqlUserid = Authority::getInstance ()->getSwitchUser ($userid);
        } else {
            // 非管理员则直接使用
            // 根据userid获取第三方账户和学校
            $sql = sprintf ("
                select 
                    tb_user.username, tb_user.userid,  
                    tb_user.headpic, tb_user.flagid, 
                    tb_flag.flagname    
                from tb_user
                left join tb_imuserid
                on tb_user.userid=tb_imuserid.userid 
                left join tb_flag
                on tb_flag.flagid=tb_user.flagid
                where 
                tb_user.userid=%s and tb_user.flagid = %s
                limit 1
            ", $sqlUserid,$limit_flagid);
            $ret = Db::query ($sql);
            if (count($ret) > 0) {
                $headpic = $ret[0]['headpic'];
                $username = $ret[0]['username'];
                $flag = $ret[0]['flagid'];
                $flagname = $ret[0]['flagname'];                

                if (isset ($limit_flagid) && $limit_flagid != $flag) {
                    // 请使用其他端登录
                    Access::Respond(0,array(),sprintf ('请使用%s端登录', $flagname));
                }
            } else {
            }
        }

        // 如果用户密码为空则默认为Default
        if ($password == null) {
            $password = "";
        }
        // 如果用户名为空则显示Default
        if ($username == null) {
            $username = "无名";
        }
        if ($schoolname == null) {
            $schoolname = "无";
        }
        //记录登录状态
        Authority::SetSession(SESSION_USERID, $userid);
        Authority::SetSession(SESSION_FLAG, $flag);
        Authority::SetSession(SESSION_PASSWD, md5($password));
        $random=Access::getRand().date('Y-m-d H:i:s').$userid;
        $access_token=md5($random);
        $refresh_token=$access_token;
        Authority::SetSession(SESSION_ACCESSTOKEN, $access_token);       // 访问接口需要的token
        Authority::SetSession(SESSION_REFRESHTOKEN, $refresh_token);     // 访问接口需要的token

        // 获取SessionID并返回sessionID
        $sessionId = Authority::getInstance ()->getSessionID ();

        //返回标志和username给前端
        $data['username']=$username;
        $data['flag']=$flag;
        $data['schoolname']=$schoolname;
        $data['headpic']=$headpic;
        $data['userid']=$userid;
        
        $data['access_token'] = $access_token;      // 重要接口临时令牌
        $data['refresh_token'] = $refresh_token;    // 刷新token令牌
        $data['session_id'] = COOKIE_PARAM.$sessionId;      // 成功登录则返回sessionID里的内容

        // 是否有关联班级
        $data['relate'] = self::relateClass (array (
            "userid" => $userid,
            "flag" => $flag
        ));
        if(count($data['relate'])){
            $data['package'] = self::relatePackage (array (
                "schoolid" => $data['relate'][0]['schoolid'],
                "tagid" => $data['relate'][0]['tagid']
            ));
        }
        

        return $data;
    }

    /**
     * 获取关联班级 (不包括创建的班级)
     */
    public static function relateClass ($arr) {
        $userid = Access::IfKeyExists ($arr, "userid");
        $flag = Access::IfKeyExists ($arr, "flag");
        if($flag==F_TEACHER){
            $sql = sprintf ("
                select TR.title as roomname, TR.id as roomid, G.title as schoolname, G.id as schoolid, TRT.name as tagname, TRT.id as tagid
                from tb_room as TR
                left join tb_group as G 
                on G.id=TR.groupid
                left join tb_room_tag as TRT
                on TRT.id=TR.tagid  
                where TR.userid=%s
            ", $userid);
        }else{
            $sql = sprintf ("
                select RA.roomid, RA.userid, RA.status, R.title as roomname, G.title as schoolname, G.id as schoolid, TRT.name as tagname, TRT.id as tagid
                from tb_roomapply as RA
                left join tb_room as R
                on RA.roomid=R.id 
                left join tb_group as G 
                on G.id=R.groupid 
                left join tb_room_tag as TRT
                on TRT.id=R.tagid
                where RA.userid=%s and RA.status=%s
            ", $userid, ROOM_APPLY_S_PASS);
        }
        
        $ret = Db::query ($sql);
        return $ret;
    }

    /**
     * 获取关联的课程库
     */
    public static function relatePackage ($arr) {
        $schoolid = Access::IfKeyExists ($arr, "schoolid");
        $tagid = Access::IfKeyExists ($arr, "tagid");
        
        $sql = sprintf("
            select IP.*
            from tb_img_package_school as IPS
            left join tb_img_package as IP
            on IPS.packid=IP.id
            where IPS.groupid=%s and IPS.tagid=%s
        ", $schoolid, $tagid);
        
        $ret = Db::query ($sql);
        return $ret;
    }

    /**
     * 加载访客信息
     */
    public static function LoadVisitorInfo () {
        
        $visitList = array();
        // 需要做一些防止注入处理
        $ip = Access::IfKeyExists ($_SERVER, "HTTP_X_REAL_IP");
        if (!isset ($ip)) {
            $ip = "127.0.0.1";
        }
        $visitList['http_x_real_ip'] = Access::SQLInjectDetect($ip);
        $visitList['php_self'] = Access::SQLInjectDetect($_SERVER['PHP_SELF']);

        $request = Request::instance();
        $visitList['module'] = Access::SQLInjectDetect($request->module ());
        $visitList['model'] = Access::SQLInjectDetect($request->controller ());
        $visitList['func'] = Access::SQLInjectDetect($request->action ());

        $visitList['DOCUMENT_ROOT'] = Access::SQLInjectDetect($_SERVER['DOCUMENT_ROOT']);

        // 找不到原因, 去掉@则ip后半部分会丢失
        $visitList['http_x_real_ip'] = sprintf ("%s", $visitList['http_x_real_ip']);
        return $visitList;
    }

    /**
     * 打日志
     */
    public static function Log ($tag, $content, $ifexit=false) {
        $text = sprintf ("%s:%s:%s\r\n", $tag, date('y-m-d h:i:s',time()), $content);
        file_put_contents('notify.txt', $text, FILE_APPEND);
        if ($ifexit) { exit(); }
    }

    /**
     * 将数组转换成json格式
    */
    public static function json_arr($arr){
        header('Content-Type: application/json');
        return json_encode($arr, 256);
    }

    /**
     * 将json格式转换为数组格式
    */
    public static function deljson_arr($json){
        header('Content-Type: application/json');
        return json_decode($json, 256);
    }

}