<?php
// [ 用户业务类 ]

namespace app\user\business;

use ylcore\Biz;
use think\Log;

class User extends Biz
{
	const SIGNED_USER = 2;//分配人选
	const SURE_USER = 1;//确认人选
	
	/**
	 * 获取所有用户信息
	 */
	public function getAll($page = 1, $pagesize = 50, $search = false, $key = false, $order = 'uid', $by = 'desc', $not_vip = false, $condition = '')
	{
		$return = array();
		$model = model('user/user');
		if ($condition=='') $condition = '`uid`>0';
		if ($search){
			if (is_mobile($search)) $condition .= " AND `mobile` = '$search'";
			elseif (is_numeric($search)) $condition .= " AND `mobile` LIKE '$search%'";
			else $condition .= " AND `mobile` IS NULL"; 
		}
		if ($not_vip)  $condition .= " AND (`is_vip` = 0 OR `is_vip` IS NULL)";
		$model->where($condition)->page($page, $pagesize)->order("$order $by");
		$list = $model->select();
		if ($list){
			foreach ($list as $item){
				if ($item->profile) $item['real_name'] = $item->profile->real_name;
				if ($item->profile) $item['reg_time'] = date('Y-m-d H:i:s', $item->profile->reg_time);
				unset($item->profile);
				$return[$item['uid']] = $item;
			}
		}
		return $return;
	}
	
	/**
	 * 获取记录总数
	 * @param string $search
	 */
	public function getCount($search, $not_vip = false, $condition = ''){
		$model = model('user/user');
		if ($condition=='') $condition = '`uid`>0';
		if ($search) $condition .= " AND `mobile` LIKE '$search%'";
		if ($not_vip)  $condition .= " AND (`is_vip` = 0 OR `is_vip` IS NULL)";
		return $model->where($condition)->count();
	}
	
	/**
	 * 获取所有用户报名
	 */
	public function getJobProcess($page = 1, $pagesize = 50, $search = false, $key = false, $order = 'creat_time', $by = 'desc', $condition = '')
	{
		$return = array();
		$model = model('user/UserJobProcess');
		if ($condition=='') $condition = $this->jobProcessCondition();
		if ($search){
			if (is_numeric($search)) $condition .= " AND `user_id` = '$search'";
			else $condition .= " AND `real_name` LIKE '$search%'";
		} 
		$model->alias('signup')->join("job","job.id=signup.job_id")->join("UserData ud","ud.uid=signup.user_id")->field("user_id, real_name, job_name, creat_time, signup.adviser_id")->where($condition)->page($page, $pagesize)->order("$order $by");
		$list = $model->select();
		if ($list){
			$biz = controller('admin/EmployeeBiz', 'business');
			foreach ($list as $item){
				$signup = [];
				$signup['user_id'] = $item->user_id;
				/*if ($item->user) {
					$signup['real_name'] = $item->user->real_name;
				}
				if ($item->job) $signup['job_name'] = $item->job->job_name;*/
				$signup[lang('real_name')] = $item['real_name'];
				if ($item->userbase) {
					$signup[lang('mobile')] = $item->userbase->mobile;
				}
				$signup[lang('job_name')] = $item['job_name'];
				$signup[lang('creat_time')] = date('Y-m-d H:i:s', $item->creat_time);
				if ($item->adviser_id){
					$employee = $biz->getOrganization($item->adviser_id);
					$value = $employee['employee']['real_name'] . '(' . $employee['org']['org_name'] . ')';
				} else {
					$value = lang('unsignned');
				}
				$signup[lang('adviser_id')] = $value;
				$return[] = $signup;
			}
		}
		return $return;
	}

	/**
	 * 获取记录总数
	 * @param string $search
	 */
	public function getJobProcessCount($search, $condition = ''){
		$model = model('user/UserJobProcess');
		if ($condition=='') $condition = $this->jobProcessCondition();
		if ($search) $condition .= " AND `user_id` = '$search'";
		return $model->where($condition)->count();
	}
	
	/**
	 * 获取所有用户报名
	 */
	public function getInviteList($page = 1, $pagesize = 50, $search = false, $key = false, $order = 'create_time', $by = 'desc')
	{
		$return = array();
		$model = model('Invite');
		$condition = '`user_name`!=""';
		if ($search) $condition .= " AND (`user_name` LIKE '$search%' OR `referral` LIKE '$search%')";
		$model->where($condition)->page($page, $pagesize)->order("$order $by");
		$list = $model->select();
		if ($list){
			foreach ($list as $item){
				$invite = [];
				$invite['user_id'] = $item->user_id;
				$invite[lang('invite_name')] = $item->user_name;
				$invite[lang('referral')] = $item->referral;
				$invite[lang('mobile')] = $item->mobile;
				$invite[lang('invite_amount')] = $item->amount;
				$invite[lang('invite_time')] = $item->create_time;
				$invite[lang('owner_id')] = $item->adviser_id;
				$return[] = $invite;
			}
		}
		return $return;
	}
	
	/**
	 * 获取记录总数
	 * @param string $search
	 */
	public function getInviteCount($search){
		$model = model('Invite');
		$condition = '`user_name`!=""';
		if ($search) $condition .= " AND (`user_name` LIKE '$search%' OR `referral` LIKE '$search%')";
		return $model->where($condition)->count();
	}
	
	/**
	 * 认领注册人选
	 * @param int $employee_id
	 * @param array $user_ids
	 */
	public function distribute($employee_id, $user_ids){
		$model = model('user');
		$employee_id && $model->save(['is_vip'=>1, 'adviser_id'=>$employee_id], 'uid IN ('.implode(',', $user_ids).') AND (`is_vip`=0 OR `is_vip` IS NULL)');
	}
	
	private function jobProcessCondition(){
		return '`user_id`>0';
	}
	
	/**
	 * 认领报名人选
	 * @param int $employee_id
	 * @param array $user_ids
	 */
	public function recognize($employee_id, $user_ids){
		$model = model('UserJobProcess');
		$employee_id && $model->save(['adviser_id'=>$employee_id, 'is_assign'=>self::SURE_USER], 'user_id IN ('.implode(',', $user_ids).') AND `is_assign` = 0');
	}
	
	public function getUnassignJobProcess($page = 1, $pagesize = 50, $search = false, $key = false, $order = 'creat_time', $by = 'desc')
	{
		$condition = "adviser_id IS NULL";
		return $this->getJobProcess($page, $pagesize, $search, $key, $order, $by, $condition);
	}
	
	public function getUnassignJobProcessCount($search){
		$condition = "adviser_id IS NULL";
		return $this->getJobProcessCount($search, $condition);
	}
	
	/**
	 * 获取最新注册总数
	 */
	public function latestCount(){
		$time = time() - 3600 * 24;//24小时只能的文章
		$model = model('user/UserData');
		return $model->where('reg_time > ' . $time)->count();
	}
	
	/**
	 * 获取最新报名总数
	 */
	public function latestSignupCount(){
		$time = time() - 3600 * 24;//24小时只能的文章
		$model = model('user/UserJobProcess');
		return $model->where('creat_time > ' . $time)->count();
	}
	
	/**
	 * 获取推荐总数
	 * @param string $search
	 */
	public function latestInviteCount(){
		$time = time() - 3600 * 24;//24小时只能的文章
		$model = model('user/Invite');
		return $model->where('create_time > ' . $time)->count();
	}
	
	/**
	 * 获取端口用户信息
	 * @param array $search 搜索条件
	 * @param int $page 分页
	 * @param int $pagesize 每页个数
	 * @param string $order 排序
	 * @param string $by
	 * @return array
	 */
	public function getCustomer($search = [], $page = 1, $pagesize = 50, $order = 'reg_time desc')
	{
		$return = array();
		$model = model('user/user');
		$condition = $this->getCustomerCondition($search);
		$list = $model
			->alias('user')
			->join('UserData ud', 'ud.uid=user.uid')
			->join('CustomerPool cp', 'cp.phone=user.mobile', 'left')
			->join('Candidate cd', 'cd.cp_id=cp.id', 'left')
			->where($condition)
			->field('user.uid,mobile,ud.real_name,adviser_id,owner_id,reg_time,user.from')
			->page($page, $pagesize)
			->order($order)
			->select();
		if ($list){
			$biz = controller('admin/EmployeeBiz', 'business');
			foreach ($list as $item){
				$user = [];
				foreach($item->toArray() as $field=>$value){
					if ($field=='reg_time') $value = date('Y-m-d H:i:s', $value);
					if ($field=='owner_id'){
						$employee_id = $value?$value:$item['adviser_id'];
						if ($employee_id){
							$employee = $biz->getOrganization($employee_id);
							$value = $employee['employee']['real_name'] . '(' . $employee['org']['org_name'] . ')';
						}
					}
					if ($field=='adviser_id') {
						if (isset($search['is_vip']) && $search['is_vip']){
							$value = $value?lang('is_sure'):lang('unsure');
						} else {
							$value = $value?lang('signned'):lang('unsignned');
						}
					}
					if ($field=='uid') $user[$field] = $value;
					else  $user[lang($field)] = $value;	
				}
				$return[] = $user;
			}
		}
		return $return;
	}
	
	/**
	 * 获取端口用户总数
	 * @param array $search 搜索条件
	 */
	public function getCustomerCount($search){
		$model = model('user/user');
		$condition = $this->getCustomerCondition($search);
		return $model
			->alias('user')
			->join('UserData ud', 'ud.uid=user.uid')
			->join('CustomerPool cp', 'cp.phone=user.mobile', 'left')
			->join('Candidate cd', 'cd.cp_id=cp.id', 'left')
			->where($condition)
			->count();
	}
	
	/**
	 * 获取端口用户条件
	 */
	private function getCustomerCondition($search){
		$condition = '`ud`.`uid`>0';//所有用户
		if (is_array($search)){
			/*是否为名下人选*/
			if (isset($search['is_vip'])){
				$is_vip = $search['is_vip'];
				if ($is_vip) {
					/*名下人选*/
					$condition .= " AND `cd`.`is_deleted`=0";//未丢弃人选
					$condition .= " AND `owner_id` IS NOT NULL";//已分配人选
					$condition .= " AND (`is_vip` != " . self::SIGNED_USER . " OR `is_vip` IS NULL)";//非分配或者未分配的用户
				} else {
					/*其他人选*/
					$condition .= " AND (";
					$condition .= " (`is_vip` = " . self::SIGNED_USER . " AND `cd`.`is_deleted`=0)";//分配用户顾问未丢弃
					$condition .= " OR `owner_id` IS NULL";//未分配人选
					$condition .= " )";
				}
			}
			if (isset($search['keyword']) && $search['keyword']){
				/*按照手机号搜索*/
				$mobile = $search['keyword'];
				if (is_mobile($search['keyword'])){
					$condition .= " AND `mobile`='$mobile'";
				} elseif (is_numeric($search['keyword'])){
					$condition .= " AND `mobile` LIKE '$mobile%'";
				} elseif (preg_match("/^\w+$/", $search['keyword'])){
					/*按照用户名搜索*/
					$condition .= " AND `uname` LIKE '$mobile%'";
				} else {
					/*按照用户姓名搜索*/
					$condition .= " AND `ud`.`real_name` LIKE '$mobile%'";
				}
			}
			if (isset($search['adviser_id']) && $search['adviser_id']){
				$adviser_id = $search['adviser_id'];
				$condition .= " AND `adviser_id` = $adviser_id";
			}
			if (isset($search['reg_time_start']) && $search['reg_time_start']){
				$start_time = strtotime($search['reg_time_start']);
				$condition .= " AND `reg_time` >= $start_time";
			}
			if (isset($search['reg_time_end']) && $search['reg_time_end']){
				$end_time = strtotime(day_end_time($search['reg_time_end']));
				$condition .= " AND `reg_time` <= $end_time";
			}
		}
		return $condition;
	}
	
	/**
	 * 获取所有报名用户
	 */
	public function getSignupList($search = [], $page = 1, $pagesize = 50, $order = 'creat_time desc')
	{
		$return = array();
		$model = model('user/UserJobProcess');
		$condition = $this->getSignupCondition($search);
		$list = $model->alias('signup')
					  ->join('job', 'job.id=signup.job_id')
					  ->join('user user', 'signup.user_id=user.uid')
					  ->join('UserData ud', 'ud.uid=user.uid')
					  ->join('CustomerPool cp', 'cp.phone=user.mobile', 'left')
					  ->join('Candidate cd', 'cd.cp_id=cp.id', 'left')
					  ->where($condition)
		 			  ->field('signup.user_id,mobile,ud.real_name,job_name,signup.adviser_id,owner_id,creat_time')
					  ->page($page, $pagesize)
		   			  ->order($order)
					  ->select();
		if ($list){
			foreach ($list as $item){
				$signup = [];
				$biz = controller('admin/EmployeeBiz', 'business');
				foreach($item->toArray() as $field=>$value){
					if ($field=='creat_time') $value = date('Y-m-d H:i:s', $value);
					if ($field=='owner_id'){
						$employee_id = $value?$value:$item['adviser_id'];
						if ($employee_id){
							$employee = $biz->getOrganization($employee_id);
							$value = $employee['employee']['real_name'] . '(' . $employee['org']['org_name'] . ')';
						}
					}
					if ($field=='adviser_id') {
						if (isset($search['is_signned']) && $search['is_signned']){
							$value = $value?lang('is_sure'):lang('unsure');
						} else {
							$value = $value?lang('signned'):lang('unsignned');
						}
					}
					if ($field=='user_id') $signup[$field] = $value;
					else  $signup[lang($field)] = $value;
				}
				$return[] = $signup;
			}
		}
		return $return;
	}
	
	/**
	 * 获取端口报名用户总数
	 * @param array $search 搜索条件
	 */
	public function getSignupCount($search){
		$model = model('user/UserJobProcess');
		$condition = $this->getSignupCondition($search);
		return $list = $model->alias('signup')
					  ->join('job', 'job.id=signup.job_id')
					  ->join('user user', 'signup.user_id=user.uid')
					  ->join('UserData ud', 'ud.uid=user.uid')
					  ->join('CustomerPool cp', 'cp.phone=user.mobile', 'left')
					  ->join('Candidate cd', 'cd.cp_id=cp.id', 'left')
					  ->where($condition)
					  ->count();
	}
	
	/**
	 * 获取端口报名用户条件
	 */
	private function getSignupCondition($search){
		$condition = '`signup`.`job_id`>0 and `signup`.`user_id`>0';//所有职位
		if (is_array($search)){
			if (isset($search['job_id'])){
				$job_id = $search['job_id'];
				if ($job_id) $condition .= " AND `job_id` = $job_id";
			}
			if (isset($search['keyword'])){
				/*按照手机号搜索*/
				$mobile = $search['keyword'];
				if (is_mobile($search['keyword'])){
					$condition .= " AND `mobile`='$mobile'";
				} elseif (is_numeric($search['keyword'])){
					$condition .= " AND `mobile` LIKE '$mobile%'";
				} elseif (preg_match("/^\w+$/", $search['keyword'])){
					/*按照用户名搜索*/
					$condition .= " AND `uname` LIKE '$mobile%'";
				} else {
					/*按照用户姓名搜索*/
					$condition .= " AND `ud`.`real_name` LIKE '$mobile%'";
				}
			}
			if (isset($search['adviser_id'])){
				$adviser_id = $search['adviser_id'];
				$condition .= " AND `signup`.`adviser_id` = $adviser_id";
			}
			if (isset($search['is_signned'])){
				$is_signned = $search['is_signned'];
				if ($is_signned) {
					/*名下人选*/
					$condition .= " AND `owner_id` IS NOT NULL";//已分配人选
					$condition .= " AND `is_deleted`=0";//非丢弃人选
					$condition .= " AND `is_assign`!=" . self::SIGNED_USER;//非分配用户
				} else {
					/*其他人选*/
					$condition .= " AND (";
					$condition .= " (`is_assign` = " . self::SIGNED_USER . " AND `cd`.`is_deleted`=0)";//分配用户顾问未丢弃
					$condition .= " OR `owner_id` IS NULL";//未分配人选
					$condition .= " )";
				}
			}
			if (isset($search['creat_time_start'])){
				$start_time = strtotime($search['creat_time_start']);
				$condition .= " AND `creat_time` >= $start_time";
			}
			if (isset($search['creat_time_end'])){
				$end_time = strtotime(day_end_time($search['creat_time_end']));
				$condition .= " AND `creat_time` <= $end_time";
			}
		}
		return $condition;
	}
	
	/**
	 * 获取所有用户报名信息
	 */
	public function getAllUserJobProcess($limit = 50, $order = 'creat_time', $by = 'desc', $condition = '')
	{
		$model = model('user/UserJobProcess');
		if ($condition=='') $condition = $this->jobProcessCondition();
		$list = $model
			->alias('signup')
			->join("job","job.id=signup.job_id")
			->join("UserData ud","ud.uid=signup.user_id")
			->join("User user","ud.uid=user.uid")
			->field("user_id, real_name, job_name, creat_time, mobile")
			->where($condition)
			->limit($limit)
			->order("$order $by")
			->select();
		if ($list){
			foreach ($list as $key=>$item){
				$list[$key]['creat_time'] = date('Y-m-d H:i:s', $item->creat_time);
			}
		}
		return $list;
	}
	
	/**
	 * 获取端口非呼入客户信息
	 * @param array $search 搜索条件
	 * @param int $page 分页
	 * @param int $pagesize 每页个数
	 * @param string $order 排序
	 * @param string $by
	 * @return array
	 */
	public function notCallinUser($search = [], $page = 1, $pagesize = 50, $order = 'reg_time desc')
	{
		$return = array();
		$model = model('user/user');
		$condition = $this->notCallinUserCondition($search);
		$list = $model->alias('user')
			->join('UserData ud', 'ud.uid=user.uid')
			->join('CrmCallinUser ccu', 'ccu.uid=user.uid', 'left')
			->where($condition)
			->field('user.uid,user.mobile,ud.real_name,ccu.adviser,reg_time,user.from,ccu.callin_time')
			->page($page, $pagesize)
			->order($order)
			->select();
		$count = $model->alias('user')
			->join('UserData ud', 'ud.uid=user.uid')
			->join('CrmCallinUser ccu', 'ccu.uid=user.uid', 'left')
			->where($condition)
			->count();
		if ($list){
			$biz = controller('admin/EmployeeBiz', 'business');
			foreach ($list as $item){
				$user = [];
				foreach($item->toArray() as $field=>$value){
					if ($field=='reg_time') $value = date('Y-m-d H:i:s', $value);
					if ($field=='adviser'){
						if ($value){
							$employee = $biz->getOrganization($value);
							$value = $employee['employee']['real_name'] . '(' . $employee['org']['org_name'] . ')';
						}
					}
					if ($field=='callin_time') {
						$value = $value?lang('signned'):lang('unsignned');
					}
					if ($field=='uid') $user[$field] = $value;
					else  $user[lang($field)] = $value;
				}
				$return[] = $user;
			}
		}
		return ['list' => $return, 'count'=>$count];
	}
	
	private function notCallinUserCondition($search) {
		$condition = '(ccu.`from`="assigned" OR ccu.`from` IS NULL)';
		if (isset($search['keyword']) && $search['keyword']){
			/*按照手机号搜索*/
			$mobile = $search['keyword'];
			if (is_mobile($search['keyword'])){
				$condition .= " AND user.`mobile`='$mobile'";
			} elseif (is_numeric($search['keyword'])){
				$condition .= " AND user.`mobile` LIKE '$mobile%'";
			} elseif (preg_match("/^\w+$/", $search['keyword'])){
				/*按照用户名搜索*/
				$condition .= " AND `uname` LIKE '$mobile%'";
			} else {
				/*按照用户姓名搜索*/
				$condition .= " AND `ud`.`real_name` LIKE '$mobile%'";
			}
		}
		if (isset($search['reg_time_start']) && $search['reg_time_start']){
			$start_time = strtotime($search['reg_time_start']);
			$condition .= " AND `reg_time` >= $start_time";
		}
		if (isset($search['reg_time_end']) && $search['reg_time_end']){
			$end_time = strtotime(day_end_time($search['reg_time_end']));
			$condition .= " AND `reg_time` <= $end_time";
		}
		return $condition;
	}
	
	/**
	 * 获取端口非呼入用户职位报名申请
	 */
	public function notCallinJobApply($search = [], $page = 1, $pagesize = 50, $order = 'creat_time desc')
	{
		$return = array();
		$model = model('user/UserJobProcess');
		$condition = $this->getSignupCondition($search);
		$list = $model->alias('signup')
			->join('job', 'job.id=signup.job_id')
			->join('user user', 'signup.user_id=user.uid')
			->join('UserData ud', 'ud.uid=user.uid')
			->join('CrmCallinUserTrace trace', 'signup.user_id=trace.user_id and signup.job_id=trace.cms_id', 'left')
			->where($condition)
			->field('signup.user_id,mobile,ud.real_name,job_name,trace.adviser,is_assign,creat_time')
			->page($page, $pagesize)
			->order($order)
			->select();
		$count = $model->alias('signup')
			->join('job', 'job.id=signup.job_id')
			->join('user user', 'signup.user_id=user.uid')
			->join('UserData ud', 'ud.uid=user.uid')
			->join('CrmCallinUserTrace trace', 'signup.user_id=trace.user_id and signup.job_id=trace.cms_id', 'left')
			->where($condition)
			->count();
		if ($list){
			foreach ($list as $item){
				$signup = [];
				$biz = controller('admin/EmployeeBiz', 'business');
				foreach($item->toArray() as $field=>$value){
					if ($field=='creat_time') $value = date('Y-m-d H:i:s', $value);
					if ($field=='adviser'){
						if ($value){
							$employee = $biz->getOrganization($value);
							$value = $employee['employee']['real_name'] . '(' . $employee['org']['org_name'] . ')';
						}
					}
					if ($field=='is_assign') {
						$value = $value?lang('signned'):lang('unsignned');
					}
					if ($field=='user_id') $signup[$field] = $value;
					else  $signup[lang($field)] = $value;
				}
				$return[] = $signup;
			}
		}
		return ['list' => $return, 'count' => $count];
	}
	
	/**
	 * 获取端口报名用户条件
	 */
	private function notCallinJobApplyCondition($search){
		$condition = '`signup`.`job_id`>0 and `signup`.`user_id`>0';//所有职位
		$condition .= ' AND (`is_assign`=1 OR `is_assign` IS NULL)';//分配和待分配人选
		if (is_array($search)){
			if (isset($search['job_id'])){
				$job_id = $search['job_id'];
				if ($job_id) $condition .= " AND `job_id` = $job_id";
			}
			if (isset($search['keyword'])){
				/*按照手机号搜索*/
				$mobile = $search['keyword'];
				if (is_mobile($search['keyword'])){
					$condition .= " AND `mobile`='$mobile'";
				} elseif (is_numeric($search['keyword'])){
					$condition .= " AND `mobile` LIKE '$mobile%'";
				} elseif (preg_match("/^\w+$/", $search['keyword'])){
					/*按照用户名搜索*/
					$condition .= " AND `uname` LIKE '$mobile%'";
				} else {
					/*按照用户姓名搜索*/
					$condition .= " AND `ud`.`real_name` LIKE '$mobile%'";
				}
			}
			if (isset($search['time_start'])){
				$start_time = strtotime($search['time_start']);
				$condition .= " AND `creat_time` >= $start_time";
			}
			if (isset($search['time_end'])){
				$end_time = strtotime(day_end_time($search['time_end']));
				$condition .= " AND `creat_time` <= $end_time";
			}
		}
		return $condition;
	}
}