<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15
 * Time: 17:08
 */
namespace app\user\controller;

use think\Request;

use app\token\controller\AuthController;
use app\user\business\MyJobDo;

class MyJob extends AuthController
{
    /**
     * 实现抽象方法
     * 全部接口需权限验证
     */
    public function isValidate() {
        return true;
    }

    /**
     * 所有职位
     */
    public function index() {
        $request =  Request::instance();
        $param = $request->param();
        $page = isset($param['page']) ? $param['page'] : 1;                              // 分页
        $pagesize = isset($param['pagesize']) ? $param['pagesize'] : 10;                 // 每页记录数
        $signup_start = isset($param['signup_start']) ? $param['signup_start'] : false;  // 报名时间。查询开始时间
        $signup_end = isset($param['signup_end']) ? $param['signup_end' ] :false;        // 报名时间。查询截止时间

        /* 数据处理 */
        $myJobDo = new MyJobDo();
        $page = $myJobDo->pageSet($page, $pagesize);
        $jobIdStr = $myJobDo->getJobId($this->uid);
        $return = $myJobDo->getJobByJobId($this->uid, $jobIdStr, $page, $pagesize);
        return $return;
    }

    /**
     * 职位报名
     */
    public function save(Request $request) {
        $param = $request->param();
        $job_id = isset($param["job_id"]) ? $param["job_id"] : "";
        $myJobDo = new MyJobDo();
        $return = $myJobDo->signup($this->uid, $job_id);
        return $return;
    }

    /**
     * 删除报名
     */
    public function delete($id) {
        $myJobDo = new MyJobDo();
        $return = $myJobDo->delSignup($this->uid, $id);
        return $return;
    }
}