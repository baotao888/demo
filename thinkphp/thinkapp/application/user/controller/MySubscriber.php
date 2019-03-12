<?php

namespace app\user\controller;

use app\user\model\AppSubscribeJob;
use think\Controller;
use think\Request;

use app\token\controller\AuthController;
use app\job\business\JobBiz;
use app\user\business\UserBiz;

class MySubscriber extends AuthController
{
	/**
	 * 实现抽象方法
	 * 全部接口需权限验证
	 */
	function isValidate() {
		return true;
	}
	
    /**
     * 显示我的订阅列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $param = request()->param();
        $page = isset($param['page'])?$param['page']:1;
        $pagesize = isset($param['pagesize'])?$param['pagesize']:8;
        $biz = new UserBiz();
        $list = $biz->mySubscribeJob($this->uid, $page, $pagesize);
        $job_biz = new JobBiz();
        $return = $job_biz->transfer($list);
        return $return;
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 订阅职位
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $param = $request->param();
        $job_id = isset($param['job_id'])?$param['job_id']:false;
        if ($job_id <= 0) abort(400, '400 Invalid job_id supplied');
        $biz = new UserBiz();
        $biz->subscribeJob($job_id, $this->uid);
        return true;
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $uid = $this->uid;
        $Biz = new UserBiz();
        $Biz->delete($uid, $id);
        return true;
    }
}
