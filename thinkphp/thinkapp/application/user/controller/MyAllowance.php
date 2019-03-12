<?php
namespace app\user\controller;

use think\Controller;
use think\Request;

use app\token\controller\AuthController;
use app\user\business\AllowanceBiz;

class MyAllowance extends AuthController
{
	/**
	 * 实现抽象方法
	 * 全部接口需权限验证
	 */
	function isValidate() {
		return true;
	}
    /**
     *获取我的补贴
     */
	public function index() {
	    $param = request()->param();
	    $page = isset($param['page']) ? $param['page'] : 1;
	    $pagesize = isset($param['pagesize']) ? $param['pagesize'] : 10;
	    $model = new AllowanceBiz();
	    $return = $model->myAllowance($this->uid, $page, $pagesize);
	    return $return;
    }
}
