<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/12
 * Time: 19:28
 */

namespace app\admin\controller;

use think\Controller;
use app\admin\model\Demo as DemoModel;
use app\common\model\Admin;
use app\common\model\Access;
use app\common\model\Authority;

class Demo extends Controller
{
    public function test(){

        $ret = DemoModel::test ();
        Access::Respond (1, $ret, "读取成功");
    }
}