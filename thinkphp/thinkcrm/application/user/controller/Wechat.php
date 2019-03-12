<?php
namespace app\user\controller;

use ylcore\Format;

class Wechat
{
	/**
	 * 微信用户列表
	 */
	public function subscribers(){
		$param = request()->param();
		$page = isset($param['page'])?$param['page']:1;
		$pagesize = isset($param['pagesize'])?$param['pagesize']:100;
		$search = isset($param['search'])?$param['search']:'';
		$business = controller('wechat', 'business');
		$list = $business->getAll($page, $pagesize, $search);
		$count = $business->getCount($search);
		return ['list' => $list, 'count'=>$count];
	}
	
	/**
	 * 微信日志
	 */
	public function logs(){
		$param = request()->param();
		$page = isset($param['page'])?$param['page']:1;
		$pagesize = isset($param['pagesize'])?$param['pagesize']:20;
		$business = controller('wechat', 'business');
		$list = $business->getLogs($page, $pagesize);
		return Format::object2array($list);
	}
}