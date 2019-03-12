<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5
 * Time: 14:23
 */
namespace app\user\controller;

use app\user\business\UserBiz;
use think\Request;

use app\token\controller\AuthController;

class MyInvite extends AuthController
{
    /**
     * 实现抽象方法
     * 全部接口需权限验证
     */
    public function isValidate() {
        return true;
    }

    /**
     * 我的推荐
     */
    public function index() {
        $request =  Request::instance();
        $param = $request->param();
        $page = isset($param['page']) ? $param['page'] : 1;                              // 分页
        $pagesize = isset($param['pagesize']) ? $param['pagesize'] : 10;                 // 每页记录数
        $keyword = isset($param['keyword']) ? $param['keyword'] : false;                 // 好有名或手机号
        $buesiness = new UserBiz();
        $return = $buesiness->recommendSel($this->uid, $page, $pagesize, $keyword);
        return $return;
    }

    /**
     * 推荐好友
     */
    public function save(Request $request) {
        $param = $request->param();
        $referral = isset($param['referral']) && $param['referral'] != '' ? $param['referral'] : false;
        $mobile = isset($param['mobile']) && is_mobile($param['mobile']) ? $param['mobile'] : false;
        if (! $referral && ! $mobile) abort(400, '400 Invalid params supplied');
        $buesiness = new UserBiz();
        $username = $buesiness->getRealNameByUid($this->uid);
        $data = [
            "user_id"     => $this->uid,
            "referral"    => $referral,
            "mobile"      => $mobile,
            "user_name"   => $username,
            "create_time" => time()
        ];
        $return = $buesiness->recommend($data);
        return $return;
    }
}