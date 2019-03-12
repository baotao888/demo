<?php
namespace app\job\controller;

use think\Config;

use app\job\business\JobBiz;

class Recommend
{
	/**
	 * 推荐位
	 */
	public function space() {
		Config::load(APP_PATH.'job/config_recommend.php');
		return Config::get('job_recommend_space');
	}
	
	/**
	 * 已推荐的职位
	 */
	public function jobs() {
		$param = request()->param();
		$recommend_id = isset($param['id'])?$param['id']:false;
		if (! $recommend_id) abort(400, '400 Invalid id supplied');
		$biz = new JobBiz();
		$return = $biz->recommended($recommend_id);
		return $return;
	}
	
	/**
	 * 推荐职位
	 */
	public function add() {
		/*参数验证*/
		$param = request()->param();
		$arr_id = $param['jobs/a'];
		$id = $param['id'];//推荐位编号
		if (! $arr_id || !$id) abort(400, '400 Invalid id/jobs supplied');
		 
		$business = new JobBiz();
		$business->addRecommend($arr_id, $id);
		return true;
	}
	
	/**
	 * 推荐位排序
	 */
	public function listorder() {
		/*参数验证*/
		$param = request()->param();
		$job_id = $param['job'];
		$order = $param['order'];
		$id = $param['id'];//推荐位编号
		if (! $job_id || !$id || !$order) abort(400, '400 Invalid id/job/order supplied');
			
		$business = new JobBiz();
		$business->orderRecommend($job_id, $id, $order);
		return true;
	}
	
	/**
	 * 推荐职位列表
	 */
	public function details() {
		$param = request()->param();
		$recommend_id = isset($param['id'])?$param['id']:false;
		if (! $recommend_id) abort(400, '400 Invalid id supplied');
		$biz = new JobBiz();
		$return = $biz->recommendedList($recommend_id);
		return $return;
	}
}