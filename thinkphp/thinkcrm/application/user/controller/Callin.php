<?php
namespace app\user\controller;

use think\Cache;
use think\Config;
use think\Log;
use think\Request;

use app\user\business\CallinFactory;
use app\admin\controller\AdminAuth;
use ylcore\Format;

class Callin extends AdminAuth
{
	/**
	 * 顾问确认呼入用户
	 */
	public function confirm() {
		$param = request()->param();
		if (! isset($param['users/a']) || empty($param['users/a'])) {
			abort(400, '400 Invalid users supplied');
		}
		if (! isset($param['from']) || empty($param['type'])) {
			abort(400, '400 Invalid from/type supplied');
		}
		$business = CallinFactory::instance($param['type'], $param['from']);
		$business->sure($this->getEmployeeId(), $param['users/a']);
		return true;
	}

	
	/**
	 * 注册用户
	 */
	public function users() {
		/*获取参数*/
		$param = request()->param();
		$page = isset($param['page']) ? $param['page'] : 1;
		$pagesize = isset($param['pagesize']) ? $param['pagesize'] : Config::get('user_list_pagesize_default');
		$search = isset($param['keyword']) ? $param['keyword'] : false;
		$start = isset($param['reg_time_start']) ? $param['reg_time_start'] : false;
		$end = isset($param['reg_time_end']) ? $param['reg_time_end'] : false;
		$is_assign = isset($param['is_assign']) ? $param['is_assign'] : false;
		
		$filters = ['keyword'=>$search, 'time_start'=>$start, 'time_end'=>$end];
		
		$business = CallinFactory::instance('register');
		if ($is_assign) $return = $business->otherList($filters, $page, $pagesize);
		else $return = $business->advisersList($filters, $page, $pagesize);
		return $return;
	}
	
	/**
	 * 职位申请用户
	 */
	public function applicants() {
		/*获取参数*/
		$param = request()->param();
		$page = isset($param['page']) ? $param['page'] : 1;
		$pagesize = isset($param['pagesize']) ? $param['pagesize'] : Config::get('user_list_pagesize_default');
		$search = isset($param['keyword']) ? $param['keyword'] : false;
		$start = isset($param['time_start']) ? $param['time_start'] : false;
		$end = isset($param['time_end']) ? $param['time_end'] : false;
		$is_assign = isset($param['is_assign']) ? $param['is_assign'] : false;
		
		$filters = ['keyword'=>$search, 'time_start'=>$start, 'time_end'=>$end];
		
		$business = CallinFactory::instance('signup');
		if ($is_assign) $return = $business->otherList($filters, $page, $pagesize);
		else $return = $business->advisersList($filters, $page, $pagesize);
		return $return;
	}
	
	/**
	 * 分配呼入用户给顾问
	 */
	public function assignUser(Request $request, $adviser)
	{
		$param = $request->param();
		$data = $param['users/a'];
		if (! is_array($data)) {
			$data = explode(',', $data);
		}
		$employee_id = $this->getEmployeeId();//当前登录用户
		
		$business = CallinFactory::instance('register');
		$result = $business->assign($data, $adviser, $employee_id);
		return $result;
	}
	
	/**
	 * 分配呼入职位报名用户给顾问
	 */
	public function assignApplicant(Request $request, $adviser) {
		$param = $request->param();
		$data = $param['users/a'];
		if (! is_array($data)) {
			$data = explode(',', $data);
		}
		$employee_id = $this->getEmployeeId();//当前登录用户
		$admin_id = $this->getAdminId();
		
		$business = CallinFactory::instance('signup');
		$result = $business->assign($data, $adviser, $employee_id);
		return $result;
	}
	
	public function callinUnsure(Request $request) {
		$employee_id = $this->getEmployeeId();//当前登录用户
		$business = CallinFactory::instance('register');
		$result = $business->unsureCount($employee_id);
		$count = 0;
		$user_web = 0;
		$user_qrcode = 0;
		foreach ($result as $f=>$v) {
			$count += $v;
			if ($f == 'web') $user_web = $v;
			elseif ($f == 'qrcode') $user_qrcode = $v;	
		}
		$business = CallinFactory::instance('signup');
		$result = $business->unsureCount($employee_id);
		$applicant_web = 0;
		$applicant_qrcode = 0;
		foreach ($result as $f=>$v) {
			$count += $v;
			if ($f == 'web') $applicant_web = $v;
			elseif ($f == 'qrcode') $applicant_qrcode = $v;
		}
		return [
			'count'=>$count,
			'user_web'=>$user_web, 
			'user_qrcode'=>$user_qrcode, 
			'applicant_web'=>$applicant_web, 
			'applicant_qrcode'=>$applicant_qrcode
		];
	}
}