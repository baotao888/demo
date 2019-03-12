<?php
namespace app\customer\business;

use think\Log;
use think\Cache;
use think\Config;

class CandidateBiz
{
	const NO_INTENTION_CODE = 0;//无意向客户
	const INTENTION_CODE = 1;//有意向客户
	const SIGNUP_CODE = 2;//报名客户
	const MEET_CODE = 3;//接站客户
	const ON_DUTY_CODE = 4;//在职客户
	const OUT_DUTY_CODE = 5;//离职客户	
	const SEARCH_CUSTOMER = 1;//按照客户搜索
	const SEARCH_TAG = 2;//按照标签搜索
	const SEARCH_EMPLOYEE = 3;//按照顾问搜索
	const SEARCH_ORG = 4;//按照组织部门搜索
	const SEARCH_DETAIL = 99;//详细搜索
	
	private $searchType;//搜索类型
	public $isSearchOrg = false;//是否搜索我的部门
	
	/**
	 * 获取候选人列表
	 * @param number $page
	 * @param number $pagesize
	 * @param mixed $search 搜索关键字，搜索客户手机和名称
	 * @param string $key
	 * @param string $order
	 * @param string $by
	 * @return multitype:array
	 */
	public  function getAll($page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'create_time', $by = 'desc', $condition='', $fields=[]){
		$return = array();
		$model = model('candidate')->alias('candidate');
		if ($condition == '') $condition = $this->allCondition();
		else  $condition .= ' AND ' . $this->allCondition();
		if ($search) {
			switch ($this->searchType){
				//按照人选搜索
				case self::SEARCH_CUSTOMER :
					$model = $model->join('CustomerPool', "CustomerPool.id = cp_id")->field('candidate.*');
					$condition .= $this->customerSearchCondition($search);
					$order = 'CustomerPool.id';
					break;
				//按照标签搜索	
				case self::SEARCH_TAG :
					$condition .= ' AND ' . $this->tagCondition($search);	
					break;
				//按照顾问姓名搜索	
				case self::SEARCH_EMPLOYEE :
					$model = $model->join('employee', "employee.id = owner_id AND (`real_name`='$search' OR `nickname`='$search')")->field('candidate.*');
					break;
				//按照组织部门名称搜索	
				case self::SEARCH_ORG : 
					$model = $model->join('employee', "employee.id = owner_id")
								   ->join('organization', "organization.id = employee.org_id AND (`org_name`='$search' OR organization.nickname='$search')")->field('candidate.*');
					break;
				//详细搜索	
				case self::SEARCH_DETAIL :
					if (isset($search['text']) && $search['text']) {
						$model = $model->join('CustomerPool', "CustomerPool.id = cp_id")->field('candidate.*');
					}
					/*部门*/
					if (isset($search['org']) && $search['org']!=='') {
						$org_id = intval($search['org']);
						$model = $model->join('employee', "employee.id = owner_id")
								 	   ->join('organization', "organization.id = employee.org_id AND `org_id`=$org_id")->field('candidate.*');
					}
					$condition .= $this->advanceSearchCondition($search);
					break;
				//按照人选搜索		   
				default :
					$model = $model->join('CustomerPool', "CustomerPool.id = cp_id")->field('candidate.*');
					$condition .= $this->customerSearchCondition($search);
					$order = 'CustomerPool.id';
			}
		}	
		if (! $order) $order = 'create_time';
		$list = $model->where($condition)->page($page, $pagesize)->order("is_top desc,$order $by")->select();
		if ($list){
			$biz = controller('admin/EmployeeBiz', 'business');
			foreach ($list as $item){		
				if ($fields) {
					foreach($fields as $field){
						if ($field=='real_name'){
							if ($item->customer) $return[$item[$key]][$field] = $item->customer->real_name;
							else $return[$item[$key]][$field] = '';
						} elseif ($field=='phone'){
							if ($item->customer) $return[$item[$key]][$field] = $item->customer->phone;
							else $return[$item[$key]][$field] = '';
						} elseif ($field=='idcard'){
							if ($item->customer) $return[$item[$key]][$field] = $item->customer->idcard;
							else $return[$item[$key]][$field] = '';
						} elseif ($field=='job_name'){
							if ($item['job_id']) {
								$enterprise_model = model('job/Enterprise');
								$enterprise_name = $enterprise_model->alias('enter')->join('CrmJob job', 'job.enterprise_id=enter.id')
									->where('job.id', $item['job_id'])->value('enterprise_name');
								$return[$item[$key]][$field] = $enterprise_name;
							} else {
								$return[$item[$key]][$field] = '';
							}
							/*if ($item->job) $return[$item[$key]][$field] = $item->job->job_name . $item['job_id'];
							else $return[$item[$key]][$field] = '';*/
						} elseif ($field=='employee_name'){
							$employee = $biz->getOrganization($item['owner_id']);
							$return[$item[$key]][$field] = $employee['employee']['real_name'] . '(' . $employee['org']['org_name'] . ')';
						} elseif ($field=='gender'){
							if ($item->customer) $return[$item[$key]][$field] = $item->customer->gender;
							else $return[$item[$key]][$field] = '';
						} elseif ($field=='create_time'){
							$return[$item[$key]]['show_time'] = $item[$field];
						} elseif ($field=='dutydays'){
							$customer_on_duty_day = $item['on_duty_time']?(floor((time()-$item['on_duty_time'])/60.0/60/24)):0;
							$return[$item[$key]]['show_time'] = $customer_on_duty_day . '|'. intval($item['dutydays']);
						} elseif ($field=='signup_time'){
							$return[$item[$key]]['show_time'] = $item[$field]?date('Y-m-d H:i:s', $item[$field]):'';
						} elseif ($field=='out_duty_time'){
							$return[$item[$key]]['show_time'] = $item[$field]?date('Y-m-d H:i:s', $item[$field]):'';
						} elseif ($field=='award'){
							$return[$item[$key]]['award'] = $item[$field]>0?true:false;
						} else {
							$return[$item[$key]][$field] = $item[$field];
						}
					}
				} else {
					if ($item->customer) {
						$item['real_name'] = $item->customer->real_name;
						$item['phone'] = $item->customer->phone;
						$item['gender'] = $item->customer->gender;
					} else {
						$item['real_name'] = '';
						$item['phone'] = '';
						$item['gender'] = '';
					}
					if ($item->owner){
						$employee = $biz->getOrganization($item['owner_id']);
						$item['employee_name'] = $employee['employee']['real_name'] . '(' . $employee['org']['org_name'] . ')';
					}
					else $item['employee_name'] = lang('unsigned');
					$return[$item[$key]] = array(
							'id' => $item['id'],
							'cp_id' => $item['cp_id'],
							'real_name' => $item['real_name'],
							'phone' => $item['phone'],
							'employee_name' => $item['employee_name'],
							'create_time' => $item['create_time'],
							'gender' => $item['gender'],
							'status' => $item['status'],
							'show_time' => $item['create_time'],
							'latest_contact_time' => $item['latest_contact_time']?date('Y-m-d H:i', $item['latest_contact_time']):'',
							'is_remain'=>$item['is_remain'],
							'latest_contact_content'=>str_cut($item['latest_contact_content'], 100),
							'is_top'=>$item['is_top']
					);
				}
			}
		}
		return $return;
	}
	
	/**
	 * 获取记录总数
	 * @param string $search
	 */
	public function getCount($search, $condition = ''){
		$model = model('candidate')->alias('candidate');
		if ($condition == '') $condition = $this->allCondition();
		else $condition .= ' AND ' . $this->allCondition();
		if ($search) {
			switch ($this->searchType){
				//按照人选搜索
				case self::SEARCH_CUSTOMER :
					$model = $model->join('CustomerPool', "CustomerPool.id = cp_id")->field('candidate.*');
					$condition .= $this->customerSearchCondition($search);
					$order = 'CustomerPool.id';
					break;
				case self::SEARCH_TAG :
					$condition .= ' AND ' . $this->tagCondition($search);	
					break;
				case self::SEARCH_EMPLOYEE :
					$model = $model->join('employee', "employee.id = owner_id AND (`real_name`='$search' OR `nickname`='$search')");
					break;
				case self::SEARCH_ORG : 
					$model = $model->join('employee', "employee.id = owner_id")
								   ->join('organization', "organization.id = employee.org_id AND (`org_name`='$search' OR organization.nickname='$search')");
					break;
				//详细搜索
				case self::SEARCH_DETAIL :
					if (isset($search['text']) && $search['text']) {
						$model = $model->join('CustomerPool', "CustomerPool.id = cp_id")->field('candidate.*');
					}
					/*部门*/
					if (isset($search['org']) && $search['org']!=='') {
						$org_id = intval($search['org']);
						$model = $model->join('employee', "employee.id = owner_id")
						->join('organization', "organization.id = employee.org_id AND `org_id`=$org_id")->field('candidate.*');
					}
					$condition .= $this->advanceSearchCondition($search);
					break;
				default :
					$model = $model->join('CustomerPool', "CustomerPool.id = cp_id AND (`real_name` LIKE '$search%' OR `phone` LIKE '$search%')");
			}
		}
		return $model->where($condition)->count();
	}
	
	private function allCondition(){
		$condition = '`is_deleted` = 0';
		return $condition;
	}
	
	/**
	 * 验证候选人顾问是否完全匹配
	 * 默认匹配，只要有一个不匹配，则所有的都不匹配
	 * @param: integer $employee 顾问编号
	 * @param: array $ids 候选人编号
	 * @param: integer $org_id 组织编号
	 */
	public function validateEmployee($employee, $ids, $org_id = false){
		$flag = true;
		$model = model('candidate');
		$rows = $model->where("`id` IN (" . implode(',', $ids) . ")")->column('owner_id');
		if ($rows){
			$biz = controller('admin/OrganizationBiz', 'business');
			foreach($rows as $owner_id){
				if ($owner_id == $employee) {
				} else if ($org_id && $biz->isParentOrganization($owner_id, $org_id)){
				} else {
					$flag = false;
					break;					
				} 
			}
		} else {
			$flag = false;
		}
		return $flag;
	}
	
	/**
	 * 更新客户的状态为有意向
	 * 只能更新无意向的客户状态为有意向
	 * @param array $id
	 */
	public function intention($ids, $admin_id){
		$employee = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		foreach ($ids as $i){
			//更新顾问无意向的人选为有意向
			$arr_update = ['status'=>self::INTENTION_CODE];
			$flag = $model->save($arr_update, "`status`=" . self::NO_INTENTION_CODE . " AND `id`=$i");
			$model->setOrigin([]);//重置原始数据，否则无法更新
			if ($flag){
				/*记录人选操作日志*/
				$this->createLog($i, $admin_id, $arr_update, self::INTENTION_CODE);
			}
		}
	}
	
	/**
	 * 更新客户的状态为报名
	 * 只能更新意向的客户状态为报名	  
	 * @param array $id
	 * @param integer $job_id 职位编号
	 * @param boolean $is_old 是否为重新报名
	 */
	public function signup($ids, $admin_id, $job_id, $is_update = false, $date = ''){
		$employee = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		foreach ($ids as $i){
			if ($date == '') $date = date('Y-m-d');
			if ($is_update) {
				/*重新报名*/
				//更新人选的职位
				$arr_update = ['signup_time'=>strtotime($date), 'job_id'=>$job_id];
				$flag = $model->save($arr_update, "`id`=$i");
			} else {
				/*首次报名*/
				//更新意向人选为已报名，并且清空保留状态
				$arr_update = ['status'=>self::SIGNUP_CODE, 'signup_time'=>strtotime($date), 'job_id'=>$job_id, 'is_remain'=>0];
				$flag = $model->save($arr_update, "`status`=" . self::INTENTION_CODE . " AND `id`=$i");
			}
			$model->setOrigin([]);//重置原始数据，否则无法更新
			if ($flag){
				$customer = $model->get($i);//查询人选详情
				/*生成一条联系记录*/
				/*$contact_model = model('ContactLog');
				$contact_model->create([
					'employee_id' => $customer->owner_id,
					'cp_id' => $customer->cp_id,
					'contact_time' => time(),
					'result' => self::SIGNUP_CODE				
				]);*/
				/*生成一条公告*/
				/*$job_model = model('job/Job');
				$job = $job_model->get($job_id);
				$announcement_biz = controller('admin/AnnouncementBiz', 'business');
				$announcement_biz->send(
					$customer->owner->avatar?$customer->owner->avatar:$employee->getDefaultAvatar($customer->owner->gender), 
					$customer->owner->nickname, 
					lang('announcement_signup', [$customer->customer->real_name, $job->job_name])
				);*/
				/*为客户生成一条工作记录*/
				$this->addCustomerWork($customer->cp_id, $job_id);
				/*记录人选操作日志*/
				$this->createLog($i, $admin_id, $arr_update, self::SIGNUP_CODE);
			}
		}
	}
	
	/**
	 * 更新客户的状态为接站成功
	 * 只能更新已报名的客户状态为接站
	 * @param array $id
	 * @param string $id_card 身份证号码
	 * @param string $gender 性别
	 * @param string $birth 年龄
	 */
	public function meet($ids, $admin_id, $id_card = '', $gender = '', $birth = ''){
		$employee = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		foreach ($ids as $i){
			// 更新报名的客户为已接站
			$arr_update = ['status'=>self::MEET_CODE];
			$flag = $model->save($arr_update, "`status`=" . self::SIGNUP_CODE . " AND `id`=$i");
			$model->setOrigin([]);//重置原始数据，否则无法更新
			if ($flag){
				$customer = $model->get($i);//查询人选详情
				/*更新客户的身份证号码*/
				$customerpool_model = model('CustomerPool');
				$update_customer = ['idcard'=>$id_card];
				$gender && $update_customer['gender'] = $gender;
				$birth && $update_customer['birthday'] = $birth;
				$customerpool_model->update($update_customer, 'id=' . $customer->cp_id);				
				/*生成一条联系记录*/				
				/*$contact_model = model('ContactLog');
				$contact_model->create([
					'employee_id' 	=> $customer->owner_id,
					'cp_id' 		=> $customer->cp_id,
					'contact_time' 	=> time(),
					'result' 		=> self::MEET_CODE
				]);*/
				/*生成一条公告*/
				/*$announcement_biz = controller('admin/AnnouncementBiz', 'business');
				$announcement_biz->send(
					$customer->owner->avatar?$customer->owner->avatar:$employee->getDefaultAvatar($customer->owner->gender),
					$customer->owner->nickname,
					lang('announcement_meet', [$customer->customer->real_name])
				);*/
				/*记录人选操作日志*/
				$arr_update['idcard'] = $id_card;
				$this->createLog($i, $admin_id, $arr_update, self::MEET_CODE);
			}
		}
	}
	
	/**
	 * 更新客户的状态为已入职
	 * 只能更新接站成功的客户状态为入职
	 * @param array $id
	 * @param float $award 企业返费
	 * @param float $subsidy 人选补贴
	 * @param float $inviter 推荐人
	 * @param float $invite_amount 推荐费
	 * @param float $invite_phone 推荐人电话
	 * @param boolean $is_customer_invite 是否为会员推荐
	 */
	public function onduty($ids, $admin_id, $award, $subsidy, $inviter, $inviter_phone, $invite_amount, $dutydays, $is_customer_invite = 0){
		$employee = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		foreach ($ids as $i){
			// 查询人选详情
			$customer = $model->get($i);
			// 更新接站的客户为已入职
			$arr_update = [
				'status'		=> self::ON_DUTY_CODE, 
				'on_duty_time'	=> $customer->signup_time, 
				'award'			=> $award,
				'subsidy'		=> $subsidy,
				'inviter'		=> $inviter,
				'inviter_phone'	=> $inviter_phone,
				'invite_amount'	=> $invite_amount,
				'dutydays' 		=> $dutydays
			];
			
			//如果客户为老客户，更新标志位
			if ($this->isOldCandidate($customer->cp_id)) $arr_update['is_new'] = 0;
			$flag = $model->save($arr_update, "`status`=" . self::MEET_CODE . " AND `id`=$i");
			$model->setOrigin([]);//重置原始数据，否则无法更新
			if ($flag){
				/*生成一条联系记录*/
				/*$contact_model = model('ContactLog');
				$contact_model->create([
					'employee_id' 	=> $customer->owner_id,
					'cp_id' 		=> $customer->cp_id,
					'contact_time' 	=> time(),
					'result' 		=> self::ON_DUTY_CODE
				]);*/
				/*生成一条公告*/
				/*$announcement_biz = controller('admin/AnnouncementBiz', 'business');
				$announcement_biz->send(
					$customer->owner->avatar?$customer->owner->avatar:$employee->getDefaultAvatar($customer->owner->gender),
					$customer->owner->nickname,
					lang('announcement_onduty', [$customer->customer->real_name])
				);*/
				/*更新客户的工作记录入职时间*/
				$work_history_model = model('CustomerWorkHistory');
				$work_history_model->save(
					['create_time' => $customer->signup_time],
					'`job_id`=' . $customer->job_id . ' AND `cp_id`=' . $customer->cp_id . ' AND `create_time` IS NULL'
				);
				/*记录人选操作日志*/
				$this->createLog($i, $admin_id, $arr_update, self::ON_DUTY_CODE);
				/*生成销售订单*/
				$salesorder_biz = controller('salesorder/SalesorderBiz', 'business');
				$salesorder_biz->create($customer->owner_id, $customer->cp_id, ['inviter' => $inviter, 'inviter_phone' => $inviter_phone, 'invite_amount' => $invite_amount, 'is_customer' => $is_customer_invite]);
			}
		}
	}
	
	/**
	 * 更新客户的状态为离职
	 * 只能更新已入职的客户状态为离职
	 * @param array $id
	 */
	public function outduty($ids, $admin_id){
		$employee = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		foreach ($ids as $i){
			//更新入职的客户为已离职
			$arr_update = ['status'=>self::OUT_DUTY_CODE, 'out_duty_time'=>time()];
			$flag = $model->save($arr_update, "`status`=" . self::ON_DUTY_CODE . " AND `id`=$i");
			$model->setOrigin([]);//重置原始数据，否则无法更新
			if ($flag){
				$customer = $model->get($i);//查询人选详情
				/*生成一条联系记录*/
				/*$contact_model = model('ContactLog');
				$contact_model->create([
					'employee_id' 	=> $customer->owner_id,
					'cp_id' 		=> $customer->cp_id,
					'contact_time' 	=> time(),
					'result' 		=> self::OUT_DUTY_CODE
				]);*/
				/*更新客户工作经历的离职时间*/
				$work_history_model = model('CustomerWorkHistory');
				$work_history_model->save(
					['end_time' => time()],
					'`job_id`=' . $customer->job_id . ' AND `cp_id`=' . $customer->cp_id . ' AND `end_time` IS NULL'
				);
				/*记录人选操作日志*/
				$this->createLog($i, $admin_id, $arr_update, self::OUT_DUTY_CODE);
			}
		}
	}
	
	/**
	 * 获取我的候选人列表
	 * @param number $page
	 * @param number $pagesize
	 * @param string $search 搜索关键字，搜索客户手机和名称
	 * @param string $key
	 * @param string $order
	 * @param string $by
	 * @return multitype:array
	 */
	public function getMy($employee_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'create_time', $by = 'desc'){
		$condition = $this->myCondition($employee_id);
		if (! in_array($order, ['create_time', 'latest_contact_time', 'id'])) $order = '';
		$return = $this->getAll($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取我的候选人总数
	 */
	public function getMyCount($employee_id, $search){
		$condition = $this->myCondition($employee_id);
		$return = $this->getCount($search, $condition);
		return $return;
	}
	
	private function myCondition($employee_id){
		$condition = '`owner_id` = ' . $employee_id;
		return $condition;
	}
	
	/**
	 * 获取组织候选人
	 */
	public function getOrg($org_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->orgCondition($org_id);
		if (! in_array($order, ['create_time', 'latest_contact_time', 'id'])) $order = '';
		$return = $this->getAll($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取组织候选人总数
	 */
	public function getOrgCount($org_id, $search){
		$condition = $this->orgCondition($org_id);
		$return = $this->getCount($search, $condition);
		return $return;
	}
	
	private function orgCondition($org_id){
		$biz = controller('admin/OrganizationBiz', 'business');
		$employee_list = $biz->subEmployee($org_id);
		$condition = "`owner_id` IN (" . implode(',', $employee_list) . ")";
		return $condition;
	}
	
	/**
	 * 统计候选人
	 * @param boolean $plot 是否统计
	 */
	public  function allStatistics($condition = ''){
		$return = array('onduty'=>0, 'offduty'=>0, 'new'=>0, 'old'=>0, 'award'=>0, 'subsidy'=>0, 'signup'=>0, 'meet'=>0, 'day'=>[]);
		$model = model('customer/candidate');
		if ($condition == '') $condition = $this->allCondition();
		else $condition .= ' AND ' . $this->allCondition();
		$list = $model->where($condition)->select();
		/*每天统计*/
		$day = [];
		for($d = 1; $d <= 31; $d++){
			$day[$d] = 0;
		}
		if ($list){
			$arr = [];//状态统计
			$award = 0;
			$subsidy = 0;
			$new = $old = 0;
			foreach ($list as $item){
				$status = $item->getData('status');
				if (isset($arr[$status])) $arr[$status]++;
				else $arr[$status] = 1;
				if ($this->isOnDuty($item)){
					if ($item['is_new']) $new++;
					else $old++;
					$award += $item['award'];
					$subsidy += $item['subsidy'];
				}
				/*本月入职人数统计*/
				if ($status==self::OUT_DUTY_CODE || $this->isOnDuty($item)) {
					$day_key = intval(date('d', $item['on_duty_time']));
					$day[$day_key]++;
				}
			}
			$return['offduty'] = isset($arr[self::OUT_DUTY_CODE])?$arr[self::OUT_DUTY_CODE]:0;//离职人数
			$return['onduty'] = (isset($arr[self::ON_DUTY_CODE])?$arr[self::ON_DUTY_CODE]:0) + $return['offduty'];//在职人数
			$return['meet'] = (isset($arr[self::MEET_CODE])?$arr[self::MEET_CODE]:0) + $return['onduty'];//接站人数
			$return['signup'] = (isset($arr[self::SIGNUP_CODE])?$arr[self::SIGNUP_CODE]:0) + $return['meet'];//报名人数
			
			$return['new'] = $new;//新人次
			$return['old'] = $old;//老人次
			$return['award'] = $award;//企业返费
			$return['subsidy'] = $subsidy;//人员补贴
		}
		$return['day'] = $day;//本月每天入职人数
		return $return;
	}
	
	/**
	 * 统计本月候选人
	 */
	public function monthStatistics($condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->monthCondition();
		} else {
			$condition = $this->monthCondition();
		}
		
		$return = $this->allStatistics($condition);
		return $return;
	}
	
	private function monthCondition(){
		$today = getdate();
		$month_start = mktime(0, 0, 0, $today['mon'], 1, $today['year']);
		$condition = '(`signup_time` > ' . $month_start . ' OR `on_duty_time` > ' . $month_start . ')';
		return $condition;
	}
	
	public function orgMonthStatistics($org_id){
		$condition = $this->orgCondition($org_id);
		$return = $this->monthStatistics($condition);
		return $return;
	}
	
	public function myMonthStatistics($employee_id){
		$condition = $this->myCondition($employee_id);
		$return = $this->monthStatistics($condition);
		return $return;
	}
	
	public function isOnDuty($bean){
		return $bean->getData('status') == self::ON_DUTY_CODE;
	}
	
	/**
	 * 意向客户条件
	 */
	private function intentionCondition(){
		$condition = '`candidate`.`status` = ' . self::INTENTION_CODE;
		return $condition;
	}
	
	/**
	 * 获取所有意向客户
	 */
	public function getAllIntention($page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'latest_contact_time', $by = 'desc', $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->intentionCondition();
		} else {
			$condition = $this->intentionCondition();
		}
		
		$return = $this->getAll($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取所有意向客户总数
	 */
	public function getIntentionCount($search, $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->intentionCondition();
		} else {
			$condition = $this->intentionCondition();
		}
		$return = $this->getCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取组织意向客户
	 */
	public function getOrgIntention($org_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->orgCondition($org_id);
		$return = $this->getAllIntention($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取组织意向客户总数
	 */
	public function getOrgIntentionCount($org_id, $search){
		$condition = $this->orgCondition($org_id);
		$return = $this->getIntentionCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取我的意向客户列表
	 * @param number $page
	 * @param number $pagesize
	 * @param string $search 搜索关键字，搜索客户手机和名称
	 * @param string $key
	 * @param string $order
	 * @param string $by
	 * @return multitype:array
	 */
	public function getMyIntention($employee_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->myCondition($employee_id);
		$return = $this->getAllIntention($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取我的意向客户总数
	 */
	public function getMyIntentionCount($employee_id, $search){
		$condition = $this->myCondition($employee_id);
		$return = $this->getIntentionCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取所有报名客户
	 */
	public function getAllSignup($page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'signup_time', $by = 'desc', $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->signupCondition();
		} else {
			$condition = $this->signupCondition();
		}
	
		$fields = ['id', 'real_name', 'phone', 'employee_name', 'signup_time', 'gender', 'status', 'cp_id', 'job_name'];
		$return = $this->getAll($page, $pagesize, $search, $key, $order, $by, $condition, $fields);
		return $return;
	}
	
	/**
	 * 报名客户条件
	 */
	private function signupCondition(){
		$condition = '`candidate`.`status` = ' . self::SIGNUP_CODE;
		return $condition;
	}
	
	/**
	 * 获取所有报名客户总数
	 */
	public function getSignupCount($search, $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->signupCondition();
		} else {
			$condition = $this->signupCondition();
		}
		$return = $this->getCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取组织报名客户
	 */
	public function getOrgSignup($org_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->orgCondition($org_id);
		$return = $this->getAllSignup($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取组织报名客户总数
	 */
	public function getOrgSignupCount($org_id, $search){
		$condition = $this->orgCondition($org_id);
		$return = $this->getSignupCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取我的报名客户列表
	 */
	public function getMySignup($employee_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->myCondition($employee_id);
		$return = $this->getAllSignup($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取我的报名客户总数
	 */
	public function getMySignupCount($employee_id, $search){
		$condition = $this->myCondition($employee_id);
		$return = $this->getSignupCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取所有在职客户
	 */
	public function getAllOnduty($page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'on_duty_time', $by = 'desc', $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->ondutyCondition();
		} else {
			$condition = $this->ondutyCondition();
		}
		$fields = ['id', 'real_name', 'phone', 'employee_name', 'on_duty_time', 'gender', 'status', 'award', 'cp_id', 'idcard', 'job_name', 'dutydays'];
		$return = $this->getAll($page, $pagesize, $search, $key, $order, $by, $condition, $fields);
		return $return;
	}
	
	/**
	 * 在职客户条件
	 */
	private function ondutyCondition(){
		$condition = '`candidate`.`status` = ' . self::ON_DUTY_CODE;
		return $condition;
	}
	
	/**
	 * 获取所有在职客户总数
	 */
	public function getOndutyCount($search, $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->ondutyCondition();
		} else {
			$condition = $this->ondutyCondition();
		}
		$return = $this->getCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取组织在职客户
	 */
	public function getOrgOnduty($org_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->orgCondition($org_id);
		$return = $this->getAllOnduty($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取组织在职客户总数
	 */
	public function getOrgOndutyCount($org_id, $search){
		$condition = $this->orgCondition($org_id);
		$return = $this->getOndutyCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取我的在职客户列表
	 */
	public function getMyOnduty($employee_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->myCondition($employee_id);
		$return = $this->getAllOnduty($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取我的入职客户总数
	 */
	public function getMyOndutyCount($employee_id, $search){
		$condition = $this->myCondition($employee_id);
		$return = $this->getOndutyCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取所有离职客户
	 */
	public function getAllOutduty($page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'out_duty_time', $by = 'desc', $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->outdutyCondition();
		} else {
			$condition = $this->outdutyCondition();
		}
		$fields = ['id', 'real_name', 'phone', 'employee_name', 'out_duty_time', 'gender', 'status', 'cp_id', 'idcard', 'job_name'];
		$return = $this->getAll($page, $pagesize, $search, $key, $order, $by, $condition, $fields);
		return $return;
	}
	
	/**
	 * 离职客户条件
	 */
	private function outdutyCondition(){
		$condition = '`candidate`.`status` = ' . self::OUT_DUTY_CODE;
		return $condition;
	}
	
	/**
	 * 获取所有离职客户总数
	 */
	public function getOutdutyCount($search, $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->outdutyCondition();
		} else {
			$condition = $this->outdutyCondition();
		}
		$return = $this->getCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取组织离职客户
	 */
	public function getOrgOutduty($org_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->orgCondition($org_id);
		$return = $this->getAllOutduty($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取组织离职客户总数
	 */
	public function getOrgOutdutyCount($org_id, $search){
		$condition = $this->orgCondition($org_id);
		$return = $this->getOutdutyCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取我的离职客户列表
	 */
	public function getMyOutduty($employee_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->myCondition($employee_id);
		$return = $this->getAllOutduty($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取我的离职客户总数
	 */
	public function getMyOutdutyCount($employee_id, $search){
		$condition = $this->myCondition($employee_id);
		$return = $this->getOutdutyCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取所有接站客户
	 */
	public function getAllMeet($page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'signup_time', $by = 'desc', $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->meetCondition();
		} else {
			$condition = $this->meetCondition();
		}
		$fields = ['id', 'real_name', 'phone', 'employee_name', 'signup_time', 'gender', 'status', 'cp_id', 'idcard', 'job_name'];
		$return = $this->getAll($page, $pagesize, $search, $key, $order, $by, $condition, $fields);
		return $return;
	}
	
	/**
	 * 接站客户条件
	 */
	private function meetCondition(){
		$condition = '`candidate`.`status` = ' . self::MEET_CODE;
		return $condition;
	}
	
	/**
	 * 获取所有接站客户总数
	 */
	public function getMeetCount($search, $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->meetCondition();
		} else {
			$condition = $this->meetCondition();
		}
		$return = $this->getCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取组织接站客户
	 */
	public function getOrgMeet($org_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->orgCondition($org_id);
		$return = $this->getAllMeet($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取组织接站客户总数
	 */
	public function getOrgMeetCount($org_id, $search){
		$condition = $this->orgCondition($org_id);
		$return = $this->getMeetCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取我的接站客户列表
	 */
	public function getMyMeet($employee_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->myCondition($employee_id);
		$return = $this->getAllMeet($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取我的接站客户总数
	 */
	public function getMyMeetCount($employee_id, $search){
		$condition = $this->myCondition($employee_id);
		$return = $this->getMeetCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取所有默认客户
	 */
	public function getAllDefault($page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc', $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->defaultCondition();
		} else {
			$condition = $this->defaultCondition();
		}
	
		$return = $this->getAll($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 默认客户条件
	 */
	private function defaultCondition(){
		$condition = '`candidate`.`status` = ' . self::NO_INTENTION_CODE;
		return $condition;
	}
	
	/**
	 * 获取所有默认客户总数
	 */
	public function getDefaultCount($search, $condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->defaultCondition();
		} else {
			$condition = $this->defaultCondition();
		}
		$return = $this->getCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取组织默认客户
	 */
	public function getOrgDefault($org_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->orgCondition($org_id);
		$return = $this->getAllDefault($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取组织默认客户总数
	 */
	public function getOrgDefaultCount($org_id, $search){
		$condition = $this->orgCondition($org_id);
		$return = $this->getDefaultCount($search, $condition);
		return $return;
	}
	
	/**
	 * 获取我的默认客户列表
	 */
	public function getMyDefault($employee_id, $page = 1, $pagesize = 50, $search = false, $key = 'id', $order = 'id', $by = 'desc'){
		$condition = $this->myCondition($employee_id);
		$return = $this->getAllDefault($page, $pagesize, $search, $key, $order, $by, $condition);
		return $return;
	}
	
	/**
	 * 获取我的默认客户总数
	 */
	public function getMyDefaultCount($employee_id, $search){
		$condition = $this->myCondition($employee_id);
		$return = $this->getDefaultCount($search, $condition);
		return $return;
	}
	
	/**
	 * 丢弃客户
	 * @param array $ids
     * @param boolean $auto 是否为系统自动清理 default false
	 */
	public function depose($ids, $auto = false){
		$flag = true;
		$employee = controller('admin/EmployeeBiz', 'business');
		$customer_biz = controller('customer/CustomerPoolBiz', 'business');
		$model = model('customer/candidate');
		foreach ($ids as $i){
			$flag = $this->deleteCandidate($i);//删除人选
			if ($flag){
				$customer = $model->get($i);
				/*系统之前存在bug，多个人可同时认领一个客户，所以为了修复此问题，在丢弃人选的时候，判断此人选是否还在其他顾问名下*/
				if ($this->canInPublic($customer['cp_id'])) {
					/*调用客户池业务类，操作客户；不能直接调用模型类，否则引起数据不一致*/
					$customer_biz->inPublicPool($customer['cp_id'], $customer['owner_id'], $auto);
				}
			}
		}
		return $flag;
	}
	
	/**
	 * 获选人基本信息
	 * @param integer $id
	 * @return array
	 */
	public function get($id){
		$model = model('Candidate');
		$candidate = $model->get($id);
		if ($candidate->customer) {
			$candidate['real_name'] = $candidate->customer->real_name;
			$birth_year = substr(trim(($candidate->customer->birthday)),0,4);
			$now_year = date('Y',time());
			$age = $now_year - intval($birth_year);
			$candidate['customer']['age'] = $age;
		}
		$candidate['signup_time'] = $candidate['signup_time']?date('Y-m-d', $candidate['signup_time']):'';
		/*获取职位企业*/
		if ($candidate['job_id']) {
			$recruit = controller('recruit/Job', 'business');
			$candidate['recruit'] = $recruit->get($candidate['job_id']);
			/*计算返费金额*/
			$mediator = controller('recruit/JobAllowanceMediator', 'business');
			$candidate['recruit']['allowance'] = $mediator->calculateAllowance($candidate['customer'], $candidate['recruit']['allowance']);
		}
		return $candidate;
	}
	
	/**
	 * 更新候选人返费
	 */
	public function updateAward($id, $award){
		$model = model('Candidate');
		return $model->save(['award'=>$award], ['id' => $id, 'status' => self::ON_DUTY_CODE]);//更新
	}
	
	/**
	 * 统计今日候选人
	 */
	public function todayStatistics($condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->todayCondition();
		} else {
			$condition = $this->todayCondition();
		}
	
		$return = $this->allStatistics($condition);
		return $return;
	}
	
	private function todayCondition(){
		$today = getdate();
		$today_start = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
		$condition = '(`signup_time` > ' . $today_start . ' OR `on_duty_time` > ' . $today_start . ')';
		return $condition;
	}
	
	/**
	 * 统计组织内每日数据
	 * @param int $org_id
	 * @return array
	 */
	public function orgTodayStatistics($org_id){
		$condition = $this->orgCondition($org_id);
		$return = $this->todayStatistics($condition);
		return $return;
	}
	
	/**
	 * 统计员工自己每日数据
	 * @param int $employee_id
	 * @return array
	 */
	public function myTodayStatistics($employee_id){
		$condition = $this->myCondition($employee_id);
		$return = $this->todayStatistics($condition);
		return $return;
	}
	
	/**
	 * 统计本周候选人
	 */
	public function weekStatistics($condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->weekCondition();
		} else {
			$condition = $this->weekCondition();
		}
	
		$return = $this->allStatistics($condition);
		return $return;
	}
	
	private function weekCondition(){
		//当前日期
		$sdefaultDate = date("Y-m-d");
		//$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
		$first=1;
		//获取当前周的第几天 周日是 0 周一到周六是 1 - 6
		$w = date('w', strtotime($sdefaultDate));
		//获取本周开始日期，如果$w是0，则表示周日，减去 6 天
		$week_start=date('Y-m-d', strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
		//本周结束日期
		$week_end = date('Y-m-d',strtotime("$week_start +6 days"));
		$condition = '(`signup_time` > ' . strtotime($week_start) . ' OR `on_duty_time` > ' . strtotime($week_start) . ')';
		return $condition;
	}
	
	/**
	 * 统计组织内本周数据
	 * @param int $org_id
	 * @return array
	 */
	public function orgWeekStatistics($org_id){
		$condition = $this->orgCondition($org_id);
		$return = $this->weekStatistics($condition);
		return $return;
	}
	
	/**
	 * 统计员工自己本周数据
	 * @param int $employee_id
	 * @return array
	 */
	public function myWeekStatistics($employee_id){
		$condition = $this->myCondition($employee_id);
		$return = $this->weekStatistics($condition);
		return $return;
	}
	
	/**
	 * 统计本季度候选人
	 */
	public function quarterStatistics($condition = ''){
		if ( $condition ) {
			$condition .= ' AND ' . $this->quarterCondition();
		} else {
			$condition = $this->quarterCondition();
		}
	
		$return = $this->allStatistics($condition);
		return $return;
	}
	
	private function quarterCondition(){
		$season = ceil((date('n'))/3);//当月是第几季度
    	$quarter_start = date('Y-m-d', mktime(0, 0, 0, $season*3-3+1, 1, date('Y')));
    	$quarter_end = date('Y-m-d', mktime(23, 59, 59, $season*3, date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y')));
		$condition = '`signup_time` > ' . strtotime($quarter_start);
		return $condition;
	}
	
	/**
	 * 统计组织内本季度数据
	 * @param int $org_id
	 * @return array
	 */
	public function orgQuarterStatistics($org_id){
		$condition = $this->orgCondition($org_id);
		$return = $this->quarterStatistics($condition);
		return $return;
	}
	
	/**
	 * 统计员工自己本季度数据
	 * @param int $employee_id
	 * @return array
	 */
	public function myQuarterStatistics($employee_id){
		$condition = $this->myCondition($employee_id);
		$return = $this->quarterStatistics($condition);
		return $return;
	}
	
	/**
	 * 员工的所有候选人手机
	 */
	public function employeeAllPhone($employee_id = 0){
		$model = model('customer/candidate');
		$list = $model->join('CustomerPool', "CustomerPool.id = cp_id AND owner_id = $employee_id")->column('phone');
		return $list;
	}
	
	/**
	 * 保留人选
	 * @param int $id
	 */
	public function remain($id, $employee_id){
		/*验证顾问的保留名额是否已满*/
		$biz = controller('admin/EmployeeBiz', 'business');
		if ($biz->checkCandidateRemains($employee_id)){
			$model = model('candidate');
			$model->save(['is_remain'=>1], "`id`=" . $id);
			$flag = true;
		}else{
			$flag = false;
		}
		return $flag;
	}
	
	/**
	 * 取消保留客户
	 * @param int $id
	 */
	public function cancelRemain($id){
		$flag = true;
		$model = model('candidate');
		/*取消保留*/
		$model->save(['is_remain'=>0], "`id`=" . $id);
		/*如果用户已经超期，释放到公海客户池*/
		$bean = $model->get($id);
		if ($this->isExpired($bean)){
			$this->depose([$bean['id']]);//丢弃
			/*消息提醒*/
			$biz = controller('admin/MessageBiz', 'business');
			$biz->sendSystemMessage($bean['owner_id'], lang('cancel_tip',[$bean->customer->real_name]));
		}
		return $flag;
	}
	
	public function employeeHistory($id){
		$return = array();
		$business = controller('recycle/CandidateRecycleBinBiz', 'business');
		$list = $business->get($id);
		$list = array_merge($list, $this->history($id));
		if ($list){
			foreach ($list as $item){
				if ($item->owner) $item['employee_name'] = $item->owner->real_name;
				else $item['employee_name'] = lang('unsigned');
				if ($item->job) $item['job_name'] = $item->job->job_name;
				else $item['job_name'] = '';
				$return[] = array(
					'invite_amount' => $item['invite_amount'],
					'inviter_phone' => $item['inviter_phone'],
					'inviter' => $item['inviter'],
					'subsidy' => $item['subsidy'],
					'employee' => $item['employee_name'],
					'create_time' => $item['create_time'],
					'dutydays' => $item['dutydays'],
					'status' => $item['status'],
					'out_duty_time' => $item['out_duty_time']?date('Y-m-d', $item['out_duty_time']):'',
					'on_duty_time' => $item['on_duty_time']?date('Y-m-d', $item['on_duty_time']):'',
					'award'=>$item['award'],
					'job'=>$item['job_name']	
				);
			}
		}
		return $return;
	}
	
	public function getTag($id){
		$model = model('candidate');
		$str_tag = $model->where('id', $id)->value('tag');
		return $str_tag?explode(' ', $str_tag):[];
	}
	
	public function addTag($id, $tag){
		$model = model('candidate');
		$str_tag = $model->where('id', $id)->value('tag');
		$arr_tag = explode(' ', $str_tag);
		array_push($arr_tag, $tag);
		$tag = implode(' ', array_filter(array_unique($arr_tag)));
		$model->update(['tag'=>$tag], ['id'=>$id]);
		return true;
	}
	
	public function deleteTag($id, $tag){
		$model = model('candidate');
		$str_tag = $model->where('id', $id)->value('tag');
		$arr_tag = explode(' ', $str_tag);
		$arr_tmp = [];
		foreach($arr_tag as $item){
			if ($item!=$tag) $arr_tmp[] = $item;
		}
		$tag = implode(' ', $arr_tmp);
		$model->update(['tag'=>$tag], ['id'=>$id]);
		return true;
	}
	
	/**
	 * 验证客户顾问是否完全匹配
	 * 默认不匹配，只要有一个匹配，则所有的都匹配
	 * @param: integer $employee 顾问编号
	 * @param: array $ids 客户编号
	 * @param: integer $org_id 组织编号
	 */
	public function validateCandidate($employee, $ids, $org_id = false){
		$flag = false;
		$model = model('candidate');
		$rows = $model->where("`cp_id` IN (" . implode(',', $ids) . ") AND is_deleted=0")->column('owner_id');
		if ($rows){
			$biz = controller('admin/OrganizationBiz', 'business');
			foreach($rows as $owner_id){
				if ($owner_id == $employee) {
					$flag = true;
					break;
				} else if ($org_id && $biz->isParentOrganization($owner_id, $org_id)){
					$flag = true;
					break;
				}
			}
		}
		return $flag;
	}
	
	/**
	 * 统计人选
	 * @param string $condition 人选条件
	 */
	public  function plotStatistics($condition = ''){
		$return = [];
		$model = model('customer/candidate');
		if ($condition == '') $condition = $this->allCondition();
		else $condition .= ' AND ' . $this->allCondition();
		$list = $model->where($condition)->select();
		/*每天统计*/
		$day = [];
		for($d = 1; $d <= 31; $d++){
			$day[$d] = 0;//横轴为天数，纵轴为数量
		}
		/*每个业务部门统计*/
		$ticks = [];//横轴坐标
		$data = [];//纵轴数据
		$organization_biz = controller('admin/OrganizationBiz', 'business');
		$adviser_org = $organization_biz->allAdviserOrgEmployees();
		$arr_employee = [];//每个顾问和组织做映射
		foreach($adviser_org as $item){
			$org = $item['org'];
			$plot_key = $org['listorder']*10;
			$ticks[] = [$plot_key, $org['org_name']];
			$data['signup'][$plot_key] = 0;
			$data['meet'][$plot_key] = 0;
			$data['onduty'][$plot_key] = 0;
			$data['outduty'][$plot_key] = 0;
			foreach($item['employees'] as $employee_id){
				$arr_employee[$employee_id] = $plot_key;
			}
		}

		if ($list){
			$arr = [];//状态统计
			$award = 0;
			$subsidy = 0;
			//获取员工信息
			$employee_biz = controller('admin/EmployeeBiz', 'business');
			$employee_group = $employee_biz->groupEmployeeJoinAt();
			$new = $old = 0;
			foreach ($list as $item){
				$status = $item->getData('status');
				if (isset($arr[$status])) $arr[$status]++;
				else $arr[$status] = 1;
				/*本月入职人数统计*/
				if ($this->isOnDuty($item) || $status == self::OUT_DUTY_CODE) {
					$day_key = intval(date('d', $item['on_duty_time']));
					$day[$day_key]++;
				}
				/*本月业务人选统计*/
				if (isset($arr_employee[$item['owner_id']])){
					$plot_key = $arr_employee[$item['owner_id']];
					if ($status==self::SIGNUP_CODE) {
						$data['signup'][$plot_key]++;
					} else if ($status==self::MEET_CODE) {
						$data['meet'][$plot_key]++;
						$data['signup'][$plot_key]++;
					} else if ($status==self::ON_DUTY_CODE) {
						$data['onduty'][$plot_key]++;
						$data['meet'][$plot_key]++;
						$data['signup'][$plot_key]++;
					} else if ($status==self::OUT_DUTY_CODE) {
						$data['outduty'][$plot_key]++;
						$data['onduty'][$plot_key]++;
						$data['meet'][$plot_key]++;
						$data['signup'][$plot_key]++;
					}
				}
			}
		}
		
		$return['day'] = $day;
		$return['group'] = ['ticks'=> $ticks, 'data'=>$data];
		
		return $return;
	}
	
	public function monthPlotStatistics(){
		$condition = $this->monthCondition();
		$return = $this->plotStatistics($condition);
		return $return;
	}
	
	/**
	 * 候选人状态回退
	 * @param array $arr_id
	 * @param integer $admin_id
	 * @param integer $status
	 */
	public function backStatus($arr_id, $admin_id, $status){
		$employee = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		foreach ($arr_id as $i){
			$customer = $model->get($i);
			//更新顾问无意向的客户为有意向
			$arr_update = ['status'=>$status, 'signup_time'=>'', 'job_id'=>''];
			$flag = $model->save($arr_update, "`id`=$i");
			$model->setOrigin([]);//重置原始数据，否则无法更新
			if ($flag){
				if ($customer->job_id) {
					/*删除入职时间*/
					$work_history_model = model('CustomerWorkHistory');
					$job_id = $customer->job_id;
					$cp_id = $customer->cp_id;
					$work_history_model->where("`job_id`=$job_id AND `cp_id`=$cp_id AND create_time IS NULL")->delete();
				}
				/*记录人选操作日志*/
				$log_model = model('CandidateLog');
				$log_model->create([
						'id'=> $i,
						'admin_id'=> $admin_id,
						'create_time'=> time(),
						'content'=> serialize($arr_update),
						'type'=> $status
				]);
			}
		}
	}
	
	/**
	 * 划转候选人
	 * @param array $arr_id 人选
	 * @param integer $admin_id 划转用户
	 * @param integer $adviser 人选负责人
	 * @param integer $employee_id 划转人
	 */
	public function move($arr_id, $admin_id, $adviser, $employee_id){
		$flag = false;
		$employee = controller('admin/EmployeeBiz', 'business');
		$left_quantity = $employee->checkCandidateMax($adviser);
		if ($left_quantity > 0){
			$model = model('candidate');
			$new_data = [];
			foreach ($arr_id as $key=>$i){
				if ($key >= $left_quantity) break;//库容已满
				/*生成一条分配历史*/
				$customer = $model->get($i);
				$new_item = [];
				foreach($customer->toArray() as $field=>$value){
					if ($field == 'is_deleted') $new_item[$field] = 1;
					else if ($field == 'status') $new_item[$field] = $customer->getData('status');
					else if ($field == 'create_time') $new_item[$field] = strtotime($value);
					else if ($field != 'id') $new_item[$field] = $value;
					
				}
				$new_data[] = $new_item;
				/*更新人选的负责人*/
				$arr_update = ['owner_id'=>$adviser, 'create_time'=>time(), 'assigner'=>$employee_id];
				$flag = $model->save($arr_update, "`id`=$i");
				$model->setOrigin([]);//重置原始数据，否则无法更新
				if ($flag){
					/*记录人选操作日志*/
					$log_model = model('CandidateLog');
					$log_model->create([
							'id'=> $i,
							'admin_id'=> $admin_id,
							'create_time'=> time(),
							'content'=> serialize($arr_update),
							'type'=> 0
					]);
				}
			}
			$model->isUpdate(false)->saveAll($new_data);
			/*消息提醒*/
			$biz = controller('admin/MessageBiz', 'business');
			$biz->sendSystemMessage($adviser, lang('move_tip'));
			$flag = true;
		}
		return $flag;
	}
	/****************************导入接口************************************/
	/**
	 * 候选人导入功能
	 * 此处为初始化导入，暂时不验证顾问库容
	 */
	public function import($rows, $employee_id, $admin_id, $param = []){
		$result = [];
		/*首先导入客户池*/
		$customer_pool_biz = controller('customer/CustomerPoolBiz', 'business');
		$customer_pool_biz->set_import_field();
		$cp_result = $customer_pool_biz->import($rows, $employee_id, $admin_id, $param);
		$arr_customer = [];
		$arr_status = [];
		$arr_tag = [];
		foreach($rows as $key=>$row){
			if ($cp_result[$key]){
				$arr_customer[$key] = $cp_result[$key];//客户池编号
				foreach($row as $index=>$value){
					$field = $this->import_fields[$index];
					if ($field=='status'){
						$arr_status[$key] = $this->formatImportStatus($value);//分配状态
					} else if ($field=='tag'){
						$arr_tag[$key] = $this->formatImportTag($value);//分配标签
					}
				}
			}
		}
		$arr_customer && $customer_pool_biz->distribute($arr_customer , $param['adviser'], $employee_id, $arr_status, $arr_tag);//分配
		return $cp_result;
	}
	
	/**
	 * 设置可导入字段
	 */
	public function set_import_field(){
		$this->import_fields = [
				'real_name',
				'gender',
				'birthday',
				'career',
				'phone',
    			'hometown',
				'status',
				'tag'
		];
	}
	
	/**
	 * 格式化导入的标签
	 */
	public function formatImportTag($tag){
		return $tag;
	}
	
	/**
	 * 格式化导入的状态
	 */
	public function formatImportStatus($status){
		if ($status == lang('no_attention')) {
			$status = self::NO_INTENTION_CODE;
		} else if ($status == lang('no')) {
			$status = self::NO_INTENTION_CODE;
		} else if ($status == lang('is_attention')) {
			$status = self::INTENTION_CODE;
		} else if ($status == lang('yes')) {
			$status = self::INTENTION_CODE;
		} else if ($status == lang('status_meet')) {
			$status = self::MEET_CODE;
			//$status = self::INTENTION_CODE;
		} else if ($status == lang('status_signup')) {
			$status = self::SIGNUP_CODE;
			//$status = self::INTENTION_CODE;
		} else if ($status == lang('status_onduty')) {
			$status = self::ON_DUTY_CODE;
			//$status = self::INTENTION_CODE;
		} else if ($status == lang('status_outduty')) {
			$status = self::OUT_DUTY_CODE;
			//$status = self::INTENTION_CODE;
		}  else {
			$status = self::NO_INTENTION_CODE;
		}
		return $status;
	}
	/****************************导入接口************************************/
	
	public function setSearchType($type = 1){
		$this->searchType = $type;
	}
	
	private function tagCondition($tag){
		//$condition = "`tag` = '$tag'";
		$condition = "MATCH (`tag`) AGAINST ('$tag' IN BOOLEAN MODE)";
		return $condition;
	}
	
	/**
	 * 获取所有过期候选人
	 * @param int $expired
	 */
	public function getAllExpired($expired, $condition = ''){
		$limit_time = time() - $expired;
		$where = $this->expiredCondition($limit_time);//过期条件
		if ($condition) $where .= ' AND ' . $condition;
		$model = model('customer/Candidate');
		$list = $model->where($where)->field('id, is_remain, create_time, owner_id')->select();
		return $list;
	}
	
	/**
	 * 更新所有过期候选人
	 * @param int $expired
	 */
	public function updateAllExpired($expired){
		$business = controller('admin/CacheBiz', 'business');//定义缓存业务对象
		$statistics_key = $business->getEmployeeStatisticsKey();
		$arr_statistics = Cache::get($statistics_key);
		$employee_key = $business->getEmployeeKey();
		if ($expired == null) $expired = 3600 * 24 * 30;//默认为一个月
		$list = $this->getAllExpired($expired);
		$arr_release = [];//可释放的候选人
		if ($list){
			$arr_owners = [];//候选人负责人
			foreach($list as $candidate){
				if ($candidate['is_remain']){
					$limit_time = time() - $expired - $arr_statistics[$candidate['owner_id']]['remain_days'] * 24 * 3600;//保留期限
					if (strtotime($candidate['create_time']) < $limit_time){
						$arr_release[] = $candidate['id'];
						if (isset($arr_owners[$candidate['owner_id']])) $arr_owners[$candidate['owner_id']]++;
						else $arr_owners[$candidate['owner_id']] = 1;
					}
				}else{
					$arr_release[] = $candidate['id'];
					if (isset($arr_owners[$candidate['owner_id']])) $arr_owners[$candidate['owner_id']]++;
					else $arr_owners[$candidate['owner_id']] = 1;
				}
			}
			if ($arr_release){
				$this->depose($arr_release, true);//释放客户
				/*消息提醒*/
				foreach($arr_owners as $owner=>$count){
					$biz = controller('admin/MessageBiz', 'business');
					$biz->sendSystemMessage($owner, lang('depose_tip', [$count]));
				}
			}
		}
		return count($arr_release);
	}
	
	public function getMyTags($search, $employee_id){
		$condition = $this->myCondition($employee_id);
		return $this->getTags($search, $condition);
	}
	
	public function getTags($search, $condition=''){
		if ($condition=='') $condition = $this->allCondition();
		else $condition .= ' AND ' . $this->allCondition();
		if ($search) $condition .= ' AND ' . $this->tagCondition($search);
		else  $condition .= ' AND `tag` IS NOT null AND `tag`!=""';
		$model = model('candidate');
		$list = $model->alias('candidate')->join('CustomerPool cp', "cp.id = candidate.cp_id")->field('candidate.tag, cp.*')->where($condition)->order("create_time desc")->limit(100)->select();
		$return = [];
		if ($list){
			foreach($list as $key=>$item){
				$list[$key] = ['cp_id'=>$item['id'], 'gender'=>$item['gender'], 'real_name'=>$item['real_name'], 'phone'=>$item['phone'], 'tags'=>$item['tag']?explode(' ', $item['tag']):[]];
			}
		}
		return $list;
	}
	
	public function addTagCustomer($customer, $employee, $tag){
		$model = model('candidate');
		$str_tag = $model->where('cp_id', '=', $customer)->where('owner_id', '=', $employee)->value('tag');
		$arr_tag = explode(' ', $str_tag);
		array_push($arr_tag, $tag);
		$tag = implode(' ', array_filter(array_unique($arr_tag)));
		$model->update(['tag'=>$tag], ['cp_id'=>$customer, 'owner_id'=>$employee]);
		return true;
	}
	
	/**
	 * 候选人是否为老客户
	 * @param integer $cp_id
	 */
	public function isOldCandidate($cp_id){
		$work_history_model = model('CustomerWorkHistory');
		$int_id = $work_history_model->where("`cp_id`=$cp_id AND create_time IS NOT NULL")->value('id');
		return $int_id;
	}
	
	/**
	 * 获取即将被释放的客户
	 * @param int $day 即将释放的天数
	 */
	public function willRelease($employee, $day, $search='', $where = []){
		$condition = "`owner_id`=$employee";//员工自己的顾问
		return $this->willReleaseCustomer($day, $search, $condition, $where);
	}
	
	private function releaseSearchCondition($condition=[]){
		$where = '';
		if (isset($condition['search'])){
			$search = $condition['search'];
			//按照人选搜索
			if (is_numeric($search)) $where .= " AND (`phone` LIKE '$search%' OR `mobile_1` LIKE '$search%')";
			else $where .= " AND (`real_name` LIKE '$search%' OR " . $this->tagCondition($search) . ")";
		}
		if (isset($condition['is_remain']) && $condition['is_remain']){
			$where .= " AND `is_remain`=" . $condition['is_remain'];
		}
		if (isset($condition['is_intention']) && $condition['is_intention']){
			$where .= " AND `status`=" . self::INTENTION_CODE;
		}
		if(isset($condition['distribute_time_s']) && $condition['distribute_time_s']!==false){
			$create_time = strtotime($condition['distribute_time_s']);
			$where .= ' AND create_time >='.$create_time;
		}
		if(isset($condition['distribute_time_e']) && $condition['distribute_time_e']!==false){
			$create_time = strtotime(day_end_time($condition['distribute_time_e']));
			$where .= ' AND create_time <='.$create_time;
		}
		return $where;
	}
	
	/**
	 * 获取即将被释放的客户
	 * @param int $day 即将释放的天数
	 * @param string $search 搜索文本
	 * @param string $condition 其他限制sql
	 * @param array $where 其他搜索条件
	 * @param array $page 分页设置
	 */
	public function willReleaseCustomer($day, $search='', $condition='', $param=[]){
		$business = controller('admin/CacheBiz', 'business');//定义缓存业务对象
		$statistics_key = $business->getEmployeeStatisticsKey();
		$arr_statistics = Cache::get($statistics_key);//员工数据缓存
		$limit_time = time() + $day - Config::get('cron_candidate_expire');//预警截止日 = 当前时间+预警天数-保留天数
		if ($condition) $where = $condition . ' AND ' . $this->expiredCondition($limit_time);
		else $where = $this->expiredCondition($limit_time);//即将释放的条件
		$fields = 'candidate.id, cp_id, real_name, phone, owner_id, create_time, status, is_remain, candidate.latest_contact_time, candidate.latest_contact_content';
		$model = model('customer/Candidate')->alias('candidate')->join('CustomerPool', "CustomerPool.id = cp_id")->field($fields);
		if ($search || $param){
			$param['search'] = $search;
			$where .= $this->releaseSearchCondition($param);
		}
		$list = $model->where($where)->order('create_time')->limit(Config::get('danger_candidate_pagesize'))->select();//不分页，最多显示2000条记录
		$arr_release = [];//即将释放的候选人
		
		if ($list){
			foreach($list as $candidate){
				$candidate['remain_days'] = floor((time() - strtotime($candidate['create_time']))/3600/24);
				if ($candidate['is_remain']){
					/*保留人选*/
					$limit_time = $limit_time - $arr_statistics[$candidate['owner_id']]['remain_days'] * 24 * 3600;//保留期限
					if (strtotime($candidate['create_time']) < $limit_time){
						//距离释放的天数 = 分配日+保留天数+释放天数-当天日期
						$release_day = floor((strtotime($candidate['create_time']) + $arr_statistics[$candidate['owner_id']]['remain_days'] * 24 * 3600 + Config::get('cron_candidate_expire') - time())/(24*3600));
						$candidate['release_time'] = $this->releaseTip($release_day);
						$candidate['latest_contact_time'] = $candidate['latest_contact_time']?date('Y-m-d H:i:s', $candidate['latest_contact_time']):'';
						$arr_release[] = $candidate;
					}
				}else{
					//距离释放的天数 = 分配日+释放天数-当天日期
					$release_day = floor((strtotime($candidate['create_time']) + Config::get('cron_candidate_expire') - time())/(24*3600));
					$candidate['release_time'] = $this->releaseTip($release_day);
					$candidate['latest_contact_time'] = $candidate['latest_contact_time']?date('Y-m-d H:i:s', $candidate['latest_contact_time']):'';
					$arr_release[] = $candidate;
				}
			}
		}
		return $arr_release;
	}
	
	private function releaseTip($release_day){
		if ($release_day<=0) $return = lang('release_now');
		else $return = lang('release_tip',[$release_day]);
		return $return;
	}
	
	public function getTagCustomer($cpid, $employee){
		$model = model('candidate');
		$str_tag = $model->where('cp_id', '=', $cpid)->where('owner_id', '=', $employee)->value('tag');
		return $str_tag?explode(' ', $str_tag):[];
	}
	
	public function expiredCondition($limit_time){
		$where = '`is_deleted`=0';//未删除
		$where .= ' AND (`status`=' . self::NO_INTENTION_CODE . ' OR `status`=' . self::INTENTION_CODE . ')';//意向或无意向客户
		$where .= ' AND `create_time`<' . $limit_time;//分配时间超过了保留期限
		return $where;
	}
	
	public function advanceSearchCondition($search){
		$condition = '';
		/*人选名称或者手机号*/
		if (isset($search['text']) && $search['text']) {
			$name = $search['text'];
			if (is_numeric($name)) {
				$condition .= " AND (`phone` LIKE '$name%' OR `mobile_1` LIKE '$name%')";
			} else {
				$condition .= " AND `real_name` LIKE '$name%'";
			}
		}
		/*分配时间*/
		if (isset($search['distribute_time_s']) && $search['distribute_time_s']) {
			$create_time = strtotime($search['distribute_time_s']);
			$condition .= " AND `create_time`>='$create_time'";
		}
		if (isset($search['distribute_time_e']) && $search['distribute_time_e']) {
			$create_time = strtotime(day_end_time($search['distribute_time_e']));
			$condition .= " AND `create_time`<='$create_time'";
		}
		/*报名时间*/
		if (isset($search['signup_time_s']) && $search['signup_time_s']) {
			$signup_time = strtotime($search['signup_time_s']);
			$condition .= " AND `signup_time`>='$signup_time'";
		}
		if (isset($search['signup_time_e']) && $search['signup_time_e']) {
			$signup_time = strtotime(day_end_time($search['signup_time_e']));
			$condition .= " AND `signup_time`<='$signup_time'";
		}
		/*入职时间*/
		if (isset($search['onduty_time_s']) && $search['onduty_time_s']) {
			$onduty_time = strtotime($search['onduty_time_s']);
			$condition .= " AND `on_duty_time`>='$onduty_time'";
		}
		if (isset($search['onduty_time_e']) && $search['onduty_time_e']) {
			$onduty_time = strtotime(day_end_time($search['onduty_time_e']));
			$condition .= " AND `on_duty_time`<='$onduty_time'";
		}
		/*最后联系时间*/
		if (isset($search['latest_contact_time_s']) && $search['latest_contact_time_s']) {
			$contact_time = strtotime($search['latest_contact_time_s']);
			$condition .= " AND `latest_contact_time`>='$contact_time'";
		}
		if (isset($search['latest_contact_time_e']) && $search['latest_contact_time_e']) {
			$contact_time = strtotime(day_end_time($search['latest_contact_time_e']));
			$condition .= " AND `latest_contact_time`<='$contact_time'";
		}
		/*是否为保留客户*/
		if (isset($search['is_remain']) && $search['is_remain']!=='') {
			$is_remain = intval($search['is_remain']);
			$condition .= " AND `is_remain`=$is_remain";
		}
		/*入职企业*/
		if (isset($search['job']) && $search['job']!=='') {
			$job_id = intval($search['job']);
			$condition .= " AND `job_id`=$job_id";
		}
		/*是否为新客户*/
		if (isset($search['is_new']) && $search['is_new']!=='') {
			$is_new = intval($search['is_new']);
			$condition .= " AND `is_new`=$is_new";
		}
		/*员工*/
		if (isset($search['employee']) && $search['employee']!=='') {
			$owner_id = intval($search['employee']);
			$condition .= " AND `owner_id`=$owner_id";
		}
		return $condition;
	}
	
	/**
	 * 人选是否已超期
	 * @param array $bean 人选
	 * @return boolean
	 */
	public function isExpired($bean){
		return strtotime($bean['create_time']) < time() - Config::get('cron_candidate_expire');
	}

	/**
	 * 关键词搜索条件
	 * @param string $keyword
	 */
	private function customerSearchCondition($keyword){
		$condition = '';
		if (is_mobile($keyword)) $condition .= " AND (`phone`='$keyword' OR `mobile_1`='$keyword')";
		elseif (is_numeric($keyword)) $condition .= " AND (`phone` LIKE '$keyword%' OR `mobile_1` LIKE '$keyword%')";
		else $condition .= " AND `real_name` LIKE '$keyword%'";
		return $condition;
	}
	
	/**
	 * 根据人选手机号码获取对应的顾问编号
	 */
	public function getEmployeeIdByCustomerMobile($mobile){
		$model = model('customer/Candidate');
		$where = "`is_deleted`=0 AND (`phone`='$mobile' OR `mobile_1`='$mobile')";
		$employee_id = $model->alias('cd')->join('CustomerPool cp', 'cd.cp_id=cp.id')->where($where)->value('owner_id');
		return $employee_id;
	}
	
	/**
	 * 保留今日将要释放的人员
	 * @param integer $employee
	 * @return boolean 
	 */
	public function remainReleaseNow($employee) {
		$flag = false;
		$employee_biz = controller('admin/EmployeeBiz', 'business');
		$left = $employee_biz->getCandidateRemainsLeft($employee);
		if ($left > 0){
			$day = 24 * 3600;//一天之内释放
			$limit_time = time() + $day - Config::get('cron_candidate_expire');//预警截止日 = 当前时间+预警天数-保留天数
			$where = "`owner_id`=$employee and `is_remain`=0";//顾问未保存的人选
			$where .= ' AND ' . $this->expiredCondition($limit_time);
			model('customer/Candidate')->limit($left)->where($where)->update(['is_remain'=>1]);
			$flag = true;
		}
		return $flag;
	}
	
	/**
	 * 是否可以进入公海客户池
	 * 人选还在其他顾问名下的客户不能进入公海客户池
	 * 系统之前存在bug，多个人可同时认领一个客户
	 */
	public function canInPublic($customer_id){
		$model = model('customer/candidate');
		$candidate = $model->where('is_deleted', 0)->where('cp_id', $customer_id)->value('id');
		return $candidate?false:true;
	}
	
	/**
	 * 新增人选
	 * @param int $customer_id
	 * @param int $adviser_id
	 */
	public function newCandidate($customer_id, $adviser_id, $status, $assigner, $tag = '', $others = []) {
		$model = model('customer/Candidate');
		$data = [
			'cp_id'  		=> $customer_id,
			'owner_id' 		=> $adviser_id,
			'status' 		=> $status,
			'create_time'  	=> time(),
			'assigner'		=> $assigner,
			'tag' 			=> $tag
		];
		if ($others) {
			foreach ($others as $field=>$value) {
				if (in_array($field, ['job_id', 'signup_time', 'is_new'])) {
					$data[$field] = $value;
				}
			}
		}
		$id = $model->create($data);
		return $id;
	}
	
	/**
	 * 离职人选再次报名
	 * 删除原人选，新增报名人选
	 * @param array $id
	 * @param integer $job_id 职位编号
	 * @param string $date 报名时间
	 */
	public function resignup($ids, $admin_id, $job_id, $date = ''){
		$employee = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		foreach ($ids as $i){
			$flag = $this->deleteCandidate($i);//删除原人选
			$model->setOrigin([]);//重置原始数据，否则无法更新
			if ($date == '') $date = date('Y-m-d');
			if ($flag){
				$customer = $model->get($i);//查询人选详情
				/*生成新人选*/
				$new_id = $this->newCandidate(
					$customer->cp_id, 
					$customer->owner_id, 
					self::SIGNUP_CODE, 
					$customer->owner_id, 
					$customer->tag,
					['job_id'=>$job_id, 'signup_time'=>strtotime($date), 'is_new'=>0]
				);
				/*生成一条工作记录*/
				$this->addCustomerWork($customer->cp_id, $job_id);
			}
		}
	}
	
	/**
	 * 删除人选
	 * 防止误删在职人选
	 */
	private function deleteCandidate($id) {
		$model = model('customer/candidate');
		$flag = $model->save(['is_deleted'=>1, 'depose_time'=>time(), 'is_remain'=>0], "`id`=$id");
		$model->setOrigin([]);//重置原始数据，否则无法更新
		return $flag;
	}
	
	/**
	 * 添加人选操作日志
	 */
	private function createLog($id, $operator, $data, $type){
		$log_model = model('CandidateLog');
		$log_model->create([
			'id'			=> $id,
			'admin_id'		=> $operator,
			'create_time'	=> time(),
			'content'		=> serialize($data),
			'type'			=> $type
		]);
	}
	
	/**
	 * 添加客户工作经历
	 */
	private function addCustomerWork($customer_id, $job_id) {
		$work_history_model = model('CustomerWorkHistory');
		$work_history_model->create([
			'job_id'	=> $job_id,
			'cp_id'		=> $customer_id
		]);
	}
	
	/**
	 * 根据客户删除标签
	 * @param int $id
	 * @param string $tag
	 * @return boolean
	 */
	public function deleteTagCustomer($customer, $employee, $tag){
		$model = model('candidate');
		$str_tag = $model->where('cp_id', '=', $customer)->where('owner_id', '=', $employee)->value('tag');
		$arr_tag = explode(' ', $str_tag);
		$arr_tmp = [];
		foreach($arr_tag as $item){
			if ($item!=$tag) $arr_tmp[] = $item;
		}
		$tag = implode(' ', $arr_tmp);
		$model->update(['tag'=>$tag], ['cp_id'=>$customer, 'owner_id'=>$employee]);
		return true;
	}
	
	/**
	 * 置顶人选
	 * @param int $id
	 */
	public function top($id){
		$biz = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		$flag = $model->save(['is_top'=>1], "`id`=" . $id);
		return $flag;
	}
	
	/**
	 * 取消置顶人选
	 * @param int $id
	 */
	public function cancelTop($id){
		$biz = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		$flag = $model->save(['is_top'=>0], "`id`=" . $id);
		return $flag;
	}
	
	/**
	 * 清理所有离职顾问
	 */
	public function updateQuitEmployee() {
		$business = controller('admin/EmployeeBiz', 'business');//定义员工业务对象
		$quit_employees = $business->getAllAdvisers(false);
		$arr_release = [];//可释放的候选人
		if ($quit_employees){
			$str_id = '';
			foreach ($quit_employees as $employee) {
				if ($str_id != '') $str_id .= ',';
				$str_id .= $employee['id'];
			}
			//获取已离职顾问的非在职人选
			$arr_release = model('customer/candidate')
				->where('owner_id IN (' . $str_id . ') AND `is_deleted`=0 AND `status`!=' . self::ON_DUTY_CODE)
				->column('id');
			if ($arr_release){
				$this->depose($arr_release, true);//释放客户
			}
		}
		return count($arr_release);
	}
	
	/**
	 * 删除丢弃的人选
	 */
	public function deleteDepose() {
		$model = model('customer/candidate');
		$flag = $model->destroy(['is_deleted'=>1]);
		return $flag;
	}
	
	/**
	 * 分配历史
	 */
	public function history($id) {
		$model = model('customer/candidate');
		return $model->where('cp_id', $id)->select();
	}
	
	/**
	 * 更新报名信息
	 * @param array $id
	 * @param integer $job_id 职位编号
	 * @param string $date 报名时间
	 */
	public function updateSignup($ids, $admin_id, $job_id, $date){
		$employee = controller('admin/EmployeeBiz', 'business');
		$model = model('candidate');
		foreach ($ids as $i){
			$arr_update = ['signup_time'=>strtotime($date), 'job_id'=>$job_id];
			$flag = $model->save($arr_update, "`id`=$i");
			$model->setOrigin([]);//重置原始数据，否则无法更新
			if ($flag) {
				$customer = $model->get($i);//查询人选详情
				/*更新客户的工作记录*/
				$work_history_model = model('CustomerWorkHistory');
				$work_history_model->save(
					['job_id' => $job_id],
					'`cp_id`=' . $customer->cp_id . ' AND `create_time` IS NULL'
				);
				/*记录人选操作日志*/
				$this->createLog($i, $admin_id, $arr_update, self::SIGNUP_CODE);
			}
		}
	}
}