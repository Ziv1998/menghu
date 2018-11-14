<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/12/11
 * Time: 15:46
 */

/**
 * 读取redis第三方登录信息到数据库，并将相应的日志删除
*/
//配置文件
$config = [
    'redis_host'=>'127.0.0.1',
    'redis_port' => '6379',
    'redis_app' => 'trip',
    'redis_area' => 'shenzhen',
    'redis_module' => 'log',
    'redis_index' => 'auth:list',

    'mysql_host'=>'127.0.0.1',
    'mysql_user'=>'root',
    'mysql_passwd'=>'123',
    'mysql_database' => 'statistics',
];
//json 转换为数组
function json_arr($value){
    header('Content-Type: application/json');
    return json_decode($value, 256);
}
// 数组拼凑数据库语言
function excute_sql($result){
    $valueStr = null;
    //连接成功，进行批量插入
    foreach ($result as $v){
        if($v['imuserid'] == null){
            $valueStr .= "(".$v['code'].",'".$v['msg']."','".$v['ip']."','".$v['time']."','".$v['uri']."',NULL),";
        }else{
            $valueStr .= "(".$v['code'].",'".$v['msg']."','".$v['ip']."','".$v['time']."','".$v['uri']."',".$v['imuserid']."),";
        }
    }
    $valueStr = rtrim($valueStr,',');
    return $valueStr;
}

$redis = new Redis;
/**
 * 连接redis
*/
$redis->connect($config['redis_host'], $config['redis_port']);
//检测是否连接成功
if($redis->ping() == "+PONG"){
    //生成键名
    $redis_key = sprintf ("%s:%s:%s:%s", $config['redis_app'], $config['redis_area'], $config['redis_module'], $config['redis_index']);
    //读取信息
    $result = $redis->lrange($redis_key,0,-1);
    //将json转换为数组
    foreach ($result as $key=>$value){
        $result[$key] = json_arr($value);
    }
    // 将数组格式转变为数据库格式
    foreach ($result as $key=>$value){
        foreach ($value['data'] as $k=>$v){
            $result[$key][$k] = $v;
        }
        unset($result[$key]['data']);
    }

    /**
     * 连接数据库
    */
    $db = new mysqli($config['mysql_host'],$config['mysql_user'],$config['mysql_passwd'],$config['mysql_database']);
    $db->set_charset('utf8');
    if(!mysqli_connect_error()){
        $valueStr = excute_sql($result);
        $sql = "insert ignore into tb_log_oauth(code,msg,ip,time,uri,imuserid) VALUES $valueStr";
        $flag = $db->query($sql);
        if($flag){
            //如果插入成功,则将队列的数据删除
            $redis->del($redis_key);
            echo '同步到数据库成功';
        }else{
            echo '同步到数据库失败,可能缓存中没有数据';
        }
    } else{
        echo '连接数据库失败';
        exit();
    }
}else{
    echo '连接Redis服务器失败';
    exit();
}
