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
use app\oauthlog\common\KissAuthority;
use app\oauthlog\model\Kissinfo as Kissinfo_Model;

class Kissinfo extends Controller
{       
    /**
     * 读取学校
     */
    public function readschool () {
        
        $start = Access::OptionalParam('start');
        $limit = Access::OptionalParam('limit');

        if (!isset ($start) || !isset ($limit)) {
            $start = 0;
            $limit = 3;
        }
        $param = sprintf ("{\"start\": \"%s\", \"limit\": \"%s\"}", $start, $limit);
        $list = KissAuthority::getInstance()->_Post (KISS_SCHOOL_READ, array(
            'appkey' => KISS_OPEN_ACCESSKEY,
            'param' => $param
        ));

        // 将学校同步到本地
        Kissinfo_Model::updateSchool ($list['data']);

        Access::RespondWithCount (1, $list['data'], "成功读取学校列表", $list['totalCount']);
    }
}
