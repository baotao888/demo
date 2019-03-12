<?php
namespace app\job\controller;

use app\job\business\JobBiz;

class Info
{
	/**
	 * 职位已报名的人
	 */
	public function process() {
		$param = request()->param();
		$job_id = isset($param['id'])?$param['id']:false;
		if (! $job_id) abort(400, '400 Invalid id supplied');
		$page = isset($param['page'])?$param['page']:1;
		$pagesize = isset($param['pagesize'])?$param['pagesize']:8;
		/*获取职位报名进程*/
		$biz = new JobBiz();
		$users = $biz->getApplicants($job_id, $page, $pagesize);
		return $users;
	}
	
	/**
	 * 您可能感兴趣的其他职位
	 */
	public function same() {
		$param = request()->param();
		$job_id = isset($param['id'])?$param['id']:false;
		if (! $job_id) abort(400, '400 Invalid id supplied');
		$page = isset($param['page'])?$param['page']:1;
		$pagesize = isset($param['pagesize'])?$param['pagesize']:8;
		/*获取相似的职位*/
		$biz = new JobBiz();
    	$list = $biz->getSimilar($job_id, $page, $pagesize);
    	/*数据传输*/
    	$return = $biz->transfer($list);
		return $return;
	}
}