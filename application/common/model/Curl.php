<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/15
 * Time: 14:30
 */

namespace app\common\model;


use think\Model;

class Curl extends Model
{
    public static function sendpost($url,$postData){

        $ch=curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        //post的变量
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);

        $output=curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}