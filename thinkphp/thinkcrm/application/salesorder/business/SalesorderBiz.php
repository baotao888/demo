<?php
namespace app\salesorder\business;

use think\Log;

use ylcore\Biz;

use app\customer\model\CustomerPool;
use app\job\model\Enterprise;
use app\recruit\model\CrmLabourService;
use app\recruit\business\LabourService;
use app\salesorder\model\FmSalesorder;
use app\salesorder\model\FmSalesorderItems;
use app\salesorder\model\FmSalesorderStatus;
use app\salesorder\model\FmSalesorderLog;

class SalesorderBiz extends Biz
{
	private $model;
	
	function __construct() {
		$this->model = new FmSalesorderItems;
	}
	
	/**
	 * 订单检索
	 * @param array $filter 检索条件
	 * @param integer $page
	 * @param integer $pagesize
	 */
	public function search($where, $page, $pagesize, $type) {
		/*订单明细总数*/
		$count = $this->model->alias('soi')
			->join('FmSalesorder salesorder', 'soi.salesorder=salesorder.id', 'left')
			->join('CustomerPool cp', 'salesorder.cp_id=cp.id', 'left')
			->join('FmSalesorderStatus sos', 'sos.salesorder=soi.salesorder', 'left')
			->where($where)
			->count();
		/*推荐费总金额*/
		$invite_amount = $this->model->alias('soi')
			->join('FmSalesorder salesorder', 'soi.salesorder=salesorder.id', 'left')
			->join('CustomerPool cp', 'salesorder.cp_id=cp.id', 'left')
			->join('FmSalesorderStatus sos', 'sos.salesorder=soi.salesorder', 'left')
			->where($where)
			->where('is_invalid', 0)
			->where('adviser_sure', 1) // 顾问已确认
			->sum('salesorder.invite_amount');
		/*企业返费总金额*/
		if ($type == 2) {
			/*小时工*/
			$amount = $this->model->alias('soi')
				->join('FmSalesorder salesorder', 'soi.salesorder=salesorder.id', 'left')
				->join('CustomerPool cp', 'salesorder.cp_id=cp.id', 'left')
				->join('FmSalesorderStatus sos', 'sos.salesorder=soi.salesorder', 'left')
				->where($where)
				->where('is_invalid', 0)
				->where('adviser_sure', 1) // 顾问已确认
				->sum('soi.ent_wage * soi.worked_time');
		} else {
			/*正式工*/
			$amount = $this->model->alias('soi')
				->join('FmSalesorder salesorder', 'soi.salesorder=salesorder.id', 'left')
				->join('CustomerPool cp', 'salesorder.cp_id=cp.id', 'left')
				->join('FmSalesorderStatus sos', 'sos.salesorder=soi.salesorder', 'left')
				->where($where)
				->where('is_invalid', 0)
				->where('adviser_sure', 1) // 顾问已确认
				->sum('soi.amount');
		}
		/*人选补贴总金额*/
		if ($type == 2) {
			/*小时工*/
			$allowance = $this->model->alias('soi')
				->join('FmSalesorder salesorder', 'soi.salesorder=salesorder.id', 'left')
				->join('CustomerPool cp', 'salesorder.cp_id=cp.id', 'left')
				->join('FmSalesorderStatus sos', 'sos.salesorder=soi.salesorder', 'left')
				->where($where)
				->where('is_invalid', 0)
				->where('adviser_sure', 1) // 顾问已确认
				->sum('soi.cp_wage * soi.worked_time');
		} else {
			/*正式工*/
			$allowance = $this->model->alias('soi')
				->join('FmSalesorder salesorder', 'soi.salesorder=salesorder.id', 'left')
				->join('CustomerPool cp', 'salesorder.cp_id=cp.id', 'left')
				->join('FmSalesorderStatus sos', 'sos.salesorder=soi.salesorder', 'left')
				->where($where)
				->where('is_invalid', 0)
				->where('adviser_sure', 1) // 顾问已确认
				->sum('soi.allowance');
		}
		/*订单明细列表*/
		$list = $this->model->alias('soi')
			->join('FmSalesorder salesorder', 'soi.salesorder=salesorder.id', 'left')
			->join('CustomerPool cp', 'salesorder.cp_id=cp.id', 'left')
			->join('FmSalesorderStatus sos', 'sos.salesorder=soi.salesorder', 'left')
			->join('Enterprise ent', 'salesorder.ent_id=ent.id', 'left')
			->field('soi.id, soi.amount, real_name, is_invalid, is_adjusted, adjusted_price, enterprise_name, go_to_time, receive_day, ls_id, onduty_day, allowance, is_outduty, worked_time, ent_wage, cp_wage, adviser_sure, onduty_type, paid_allowance_way, paid_invite_way, inviter')
			->where($where)
			->page($page, $pagesize)
			->order("go_to_time desc")
			->select();
		$return = [];
		if ($list) {
			$ls_biz = new LabourService;
			$labour_service_list = $ls_biz->cache();
			foreach ($list as $item) {
				$data = [];
				$data['id'] = $item['id']; // 订单明细编号
				$data['amount'] = $item['amount']; // 企业返费
				$data['real_name'] = $item['real_name']; // 客户姓名
				$data['go_to_time'] = date('Y-m-d', $item['go_to_time']); // 接站时间
				$data['enterprise'] = $item['enterprise_name']; // 企业名称
				$data['is_invalid'] = $item['is_invalid']; // 状态，是否删除
				$data['receive_date'] = date('Y-m-d', $this->getReceivableTime($item['go_to_time'], $item['receive_day'])); // 返费到期日期
				$data['onduty_day'] = $item['onduty_day']; // 人选在职天数
				$data['labour_service'] = $item['ls_id'] ? $labour_service_list[$item['ls_id']]['name'] : 0; // 劳务公司
				$data['allowance'] = $item['allowance']; // 补贴总金额
				$data['is_outduty'] = $item['is_outduty']; // 是否离职
				$data['worked_time'] = $item['worked_time']; // 工时
				$data['ent_wage'] = $item['ent_wage']; // 企业单价
				$data['cp_wage'] = $item['cp_wage']; // 人选单价
				$data['adviser_sure'] = $item['adviser_sure']; //是否已入账
				$data['onduty_type'] = $item['onduty_type']; // 在职类型
				$data['is_adjusted'] = $item['is_adjusted']; // 是否调整单价
				$data['adjusted_price'] = $item['adjusted_price'];
				$data['is_paid_allowance'] = $item['paid_allowance_way'] ? 1 : 0; // 是否领取补贴
				$data['is_paid_invite'] = $item['paid_invite_way'] ? 1 : 0; // 是否领取推荐费
				$data['is_inviter'] = $item['inviter'] ? 1 : 0; // 是否有推荐人
				$return[] = $data;
			}
		}
		return ['list' => $return, 'count' => $count, 'invite_amount' => floatval($invite_amount), 'amount' => floatval($amount), 'allowance' => floatval($allowance)];
	}
	
	/**
	 * 检索条件
	 * @param array $filter
	 * @return string
	 */
	private function listCondition(array $filter) {
		$where = '';
		/*关键字*/
		if (isset($filter['keyword'])) {
			$search = $filter['keyword'];
			if (is_mobile($search)) $where .= " AND `mobile` = '$search'";
			elseif (is_numeric($search)) $where .= " AND `mobile` LIKE '$search%'";
			elseif ($search) $where .= " AND `real_name` LIKE '$search%'";
		}
		/*开始时间*/
		if (isset($filter['time_start']) && $filter['time_start']) {
			$start = strtotime($filter['time_start']);
			if ($start) $where .= " AND `go_to_time`>=$start";
		}
		/*结束时间*/
		if (isset($filter['time_end']) && $filter['time_end']) {
			$end = strtotime(day_end_time($filter['time_end']));
			if ($end) $where .= " AND `go_to_time`<=$end";
		}
		/*是否无效*/
		if (isset($filter['is_invalid']) && $filter['is_invalid'] !== false) {
			$where .= " AND `is_invalid`=" . $filter['is_invalid'];
		}
		/*企业编号*/
		if (isset($filter['ent_id']) && $filter['ent_id']) {
			$where .= " AND `ent_id`=" . $filter['ent_id'];
		}
		/*劳务公司*/
		if (isset($filter['ls_id']) && $filter['ls_id']) {
			$where .= " AND `ls_id`=" . $filter['ls_id'];
		}
		/*顾问是否确认*/
		if (isset($filter['is_sure']) && $filter['is_sure'] !== false) {
			$where .= " AND `adviser_sure`=" . $filter['is_sure'];
		}
		/*到期时间*/
		if (isset($filter['receive_start']) && $filter['receive_start']) {
			$start = strtotime($filter['receive_start']);
			if ($start) $where .= " AND (`go_to_time` + `receive_day` * 3600 * 24>=$start)";
		}
		if (isset($filter['receive_end']) && $filter['receive_end']) {
			$end = strtotime(day_end_time($filter['receive_end']));
			if ($end) $where .= " AND (`go_to_time` + `receive_day` * 3600 * 24<=$end)";
		}
		return $where;
	}
	
	/**
	 * 获取企业返费时间
	 * @param 入职时间 $onduty_time
	 * @param 返费天数 $day
	 */
	private function getReceivableTime($onduty_time, $day) {
		return $onduty_time + $day * 3600 * 24;
	}
	
	/**
	 * 创建订单
	 * @param $adviser_id int 顾问编号
	 * @param $customer_id int 客户编号
	 * @param $inviter array 推荐人信息
	 */
	public function create($adviser_id, $customer_id, $inviter) {
		/*1.获取候选人详情*/
		$candidate_model = model('customer/Candidate');
		$customer_info = $candidate_model->alias('can')->join('CrmJob cj', 'cj.id=can.job_id')
			->join('CustomerPool cp', 'cp.id=can.cp_id')
			->join('CrmJobProvider cls', 'cls.job_id=cj.id', 'left')			
			->where('owner_id', $adviser_id)->where('cp_id', $customer_id)->where('is_deleted', 0)
			->field('can.*, cj.enterprise_id, cls.labour_service_id, cj.validity_period, cp.gender, cp.birthday')->find();
		/*2.获取职位补贴详情*/
		$allowance_model = model('recruit/CrmJobAllowance');
		$allowances = $allowance_model->where('job_id', $customer_info['job_id'])->select();
		/*3.生成订单*/
		$salesorder_model = model('salesorder/FmSalesorder');
		$sales_data = [
			'adviser_id' => $adviser_id,
			'cp_id' => $customer_id,
			'job_id' => $customer_info['job_id'],
			'ent_id' => $customer_info['enterprise_id'],
			'ls_id' => $customer_info['labour_service_id'],
			'go_to_time' => strtotime($customer_info['validity_period']),
			'inviter' => $inviter['inviter'],
			'inviter_phone' => $inviter['inviter_phone'],
			'invite_amount' => $inviter['invite_amount'],
			'is_customer_invite' => $inviter['is_customer']	
		];
		$salesorder_model = $salesorder_model->create($sales_data);
		/*4.生成订单明细*/
		if ($allowances) {
			$salesorder_type = 1;
			foreach ($allowances as $item) {
				$model = model('salesorder/FmSalesorderItems');
				$detail = [
					'salesorder' => $salesorder_model->id,
					'amount' => $item['amount'],
					'receive_day' => $item['term'] + 10,
					'allowance' => $item['allowance'],
					'onduty_day' => $item['term'],
					'onduty_type' => $item['onduty_type'],
					'ent_wage' => $item['ent_wage'],
					'cp_wage' => $item['cp_wage']				
				];
				$salesorder_type = $item['type'];
				$condition = unserialize($item['conditions']);
				$flag = false;
				if ($condition['field'] == 'gender') {
					if ($condition['value'] == $customer_info['gender']) {
						$flag = true;
					}
				} else if ($condition['field'] == 'age') {
					if ($user['birthday']) {
						$birth_year = substr(trim(($user['birthday'])),0,4);
						$now_year = date('Y',time());
						$age = $now_year - intval($birth_year);
						if ($condition['operator'] == 0) {
							// 等于
							if ($age == $condition['value']) {
								$flag = true;
							}
						} elseif ($condition['operator'] == -1) {
							// 小于
							if ($age < $condition['value']) {
								$flag = true;
							}
						} elseif ($condition['operator'] == 1) {
							// 大于
							if ($age > $condition['value']) {
								$flag = true;
							}
						}
					}
				} else {
					$flag = true;
				}
				$flag && $model->create($detail);
			}
			//更新合同类型
			$salesorder_model->save(['type'=>$salesorder_type], ['id'=>$salesorder_model->id]);
		}
		/*5,生成订单状态*/
		$status_model = model('salesorder/FmSalesorderStatus');
		$status_model->create(['salesorder' => $salesorder_model->id]);
	}
	
	/**
	 * 检索顾问自己的业绩
	 */
	public function searchMy($type, $adviser, $page, $pagesize, $filter) {
		$where = '`salesorder`.`type` = ' . $type . ' AND `salesorder`.`adviser_id`=' . $adviser . $this->listCondition($filter);
		return $this->search($where, $page, $pagesize, $type);
	}
	
	/**
	 * 检索全部业绩
	 */
	public function searchAll($type, $page, $pagesize, $filter) {
		$where = "`salesorder`.`type` = $type" . $this->listCondition($filter);
		return $this->search($where, $page, $pagesize, $type);
	}
	
	/**
	 * 检索部门业绩
	 */
	public function searchOrg($type, $org_id, $page, $pagesize, $filter) {
		$biz = controller('admin/OrganizationBiz', 'business');
		$employee_list = $biz->subEmployee($org_id);
		$where = "`salesorder`.`type` = $type AND `salesorder`.`adviser_id` IN (" . implode(',', $employee_list) . ")" . $this->listCondition($filter);
		return $this->search($where, $page, $pagesize, $type);
	}
	
	/**
	 * 验证业绩和顾问是否完全匹配
	 * 默认匹配，只要有一个不匹配，则所有的都不匹配
	 * @param: integer $employee 顾问编号
	 * @param: array $ids 业绩编号
	 * @param: integer $org_id 组织编号
	 */
	public function validateEmployee($employee, $ids, $org_id = false){
		$flag = true;
		$model = model('FmSalesorderItems');
		$rows = $model->alias('items')->join('FmSalesorder sales', 'items.salesorder=sales.id')
			->where("items.`id` IN (" . implode(',', $ids) . ")")->column('adviser_id');
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
	 * 更新业绩的状态为顾问确认
	 * 同时更新订单明细的人选工作时间
	 * @param array $id
	 * @param integer $work_time 工作时间
	 */
	public function sure($ids, $admin_id, $work_time){
		foreach ($ids as $i){
			/*更新业绩工作时间*/
			$item = $this->model->where('id', $i)->find();
			if (! $item->sales) continue; // 没有订单不能操作
			if ($item->status->is_invalid == 1) continue; //失效的订单不能操作
			if ($item->sales->type == 2) {
				//小时型
				$item->worked_time = $work_time;
				$profit = ($item->ent_wage - $item->cp_wage) * $work_time;
			} elseif ($item->sales->type == 1) {
				//正式型
				$item->onduty_day = $work_time;
				$profit = ($work_time > $item->onduty_day) ? ($item->amount - $item->allowance) : 0;
				
			} else {
				//混合型
				$item->onduty_day = $work_time;
				$profit = $item->amount;
			}
			$item->profit = $profit; // 利润
			$item->save();
			/*标记顾问已确认*/
			$arr_update = ['adviser_sure' => 1];
			FmSalesorderStatus::where('salesorder', $item->salesorder)->update($arr_update);
			/*记录人选操作日志*/
			$arr_update['worked_time'] = $work_time;
			$this->createLog($item->salesorder, $admin_id, $arr_update, 'update');
		}
	}
	
	/**
	 * 添加订单操作日志
	 */
	private function createLog($id, $operator, $data, $type, $note = ''){
		$log_model = new FmSalesorderLog;
		$log_model->create([
				'order_id'			=> $id,
				'admin_id'		=> $operator,
				'create_time'	=> time(),
				'content'		=> serialize($data),
				'type'			=> $type,
				'note'			=> $note
		]);
	}
	
	/**
	 * 更新业绩的状态为已失效
	 * 同时更新订单明细的人选已离职
	 * @param array $id
	 * @param string $note 备注
	 */
	public function delete($ids, $admin_id, $note){
		foreach ($ids as $i){
			/*更新明细的状态*/
			$item = $this->model->where('id', $i)->find();
			if (! $item->sales) continue;
			$item->is_outduty = 1;
			$item->save();
			/*标记订单失效*/
			$arr_update = ['is_invalid' => 1];
			FmSalesorderStatus::where('salesorder', $item->salesorder)->update($arr_update);
			/*记录人选操作日志*/
			$this->createLog($item->salesorder, $admin_id, $arr_update, 'update', $note);
		}
	}
	
	/**
	 * 更新业绩的状态为未失效
	 * 同时更新订单明细的人选为在职
	 * @param array $id
	 * @param string $note 备注
	 */
	public function recover($ids, $admin_id, $note){
		foreach ($ids as $i){
			/*更新明细的状态*/
			$item = $this->model->where('id', $i)->find();
			if (! $item->sales) continue;
			$item->is_outduty = 0;
			$item->save();
			/*标记订单失效*/
			$arr_update = ['is_invalid' => 0];
			FmSalesorderStatus::where('salesorder', $item->salesorder)->update($arr_update);
			/*记录人选操作日志*/
			$this->createLog($item->salesorder, $admin_id, $arr_update, 'update', $note);
		}
	}
	
	/**
	 * 领取补贴
	 * @param array $ids
	 * @param int $admin_id
	 * @param int $pay_way
	 * @param boolean $is_borrow
	 * @param string $note
	 */
	public function receiveAllowance($ids, $admin_id, $pay_way, $is_borrow, $note) {
		foreach ($ids as $i){
			/*更新明细的状态*/
			$item = $this->model->where('id', $i)->find();
			if (! $item->sales) continue;
			if ($item->status->adviser_sure != 1) continue; // 顾问未确认的订单不能操作
			/*记录领取方式*/
			$arr_update = ['paid_allowance_way' => $pay_way, 'is_borrow_allowance' => $is_borrow];
			FmSalesorderStatus::where('salesorder', $item->salesorder)->update($arr_update);
			/*记录人选操作日志*/
			$this->createLog($item->salesorder, $admin_id, $arr_update, 'update', $note);
		}
	}
	
	/**
	 * 领取推荐费
	 * @param array $ids
	 * @param int $admin_id
	 * @param int $pay_way
	 * @param boolean $is_borrow
	 * @param string $note
	 */
	public function receiveRecommend($ids, $admin_id, $pay_way, $is_borrow, $note) {
		foreach ($ids as $i){
			/*更新明细的状态*/
			$item = $this->model->where('id', $i)->find();
			if (! $item->sales) continue;
			if ($item->status->adviser_sure != 1) continue; // 顾问未确认的订单不能操作
			/*记录领取方式*/
			$arr_update = ['paid_invite_way' => $pay_way, 'is_borrow_invite' => $is_borrow];
			FmSalesorderStatus::where('salesorder', $item->salesorder)->update($arr_update);
			/*记录人选操作日志*/
			$this->createLog($item->salesorder, $admin_id, $arr_update, 'update', $note);
		}
	}
	
	/**
	 * 订单详情
	 * @param integer $id
	 */
	public function detail($id) {
		$item = $this->model->where('id', $id)->find(); // 订单明细
		$item->status; // 订单状态
		$item->sales; // 订单详情
		$item['customer'] = CustomerPool::get($item->sales->cp_id); // 人选信息
		$item['enterprise'] = Enterprise::get($item->sales->ent_id)->enterprise_name; // 企业
		$item['labour'] = CrmLabourService::get($item->sales->ls_id)->name; // 劳务公司
		$biz = controller('admin/EmployeeBiz', 'business');
		$employee = $biz->getOrganization($item->sales->adviser_id);
		$item['adviser_name'] = $employee['employee']['real_name'];
		$item['org_name'] = $employee['org']['org_name'];
		$item['deadline'] = date('Y-m-d', $item['receive_day'] * 24 * 3600 + $item->sales->go_to_time); // 企业到期时间
		$item['receivetime'] = date('Y-m-d', ($item['receive_day']+10) * 24 * 3600 + $item->sales->go_to_time); // 人选到期时间
		if ($item->sales->type == 2) {
			$item['amount'] = $item['worked_time'] * $item['ent_wage'];
			$item['allowance'] = $item['worked_time'] * $item['cp_wage'];
		}
		return $item;
	}
	
	/**
	 * 调整小时工单价
	 * @param float $price 价格
	 * @param string $note 备注
	 */
	public function adjustHourPrice($arr_id, $admin_id, $price, $note) {
		foreach ($arr_id as $i){
			/*更新明细的状态*/
			$item = $this->model->where('id', $i)->find();
			if (! $item->sales) continue;
			if ($item->sales->type == 1) continue; //正式工不能调整单价
			if ($item->status->adviser_sure != 1) continue; // 顾问未确认的订单不能操作
			$item->is_adjusted = 1; // 调整单价
			$item->adjusted_price = $price; // 调整金额
			$item->save();
			/*记录人选操作日志*/
			$arr_update = ['is_adjusted' => 1, 'adjusted_price' => $price];			
			$this->createLog($item->salesorder, $admin_id, $arr_update, 'update', $note);
		}
	}
	
	public function exportAll($type, $ids) {
		$where = "`soi`.`id` IN (" . implode(',', $ids) . ")";
		/*订单明细列表*/
		$list = $this->model->alias('soi')
			->join('FmSalesorder salesorder', 'soi.salesorder=salesorder.id', 'left')
			->join('CustomerPool cp', 'salesorder.cp_id=cp.id', 'left')
			->join('FmSalesorderStatus sos', 'sos.salesorder=soi.salesorder', 'left')
			->join('Enterprise ent', 'salesorder.ent_id=ent.id', 'left')
			->field('soi.id, real_name, phone, idcard, gender, soi.amount, go_to_time, enterprise_name, receive_day, onduty_day, ls_id, allowance, is_outduty, worked_time, ent_wage, cp_wage, adviser_sure, adjusted_price, paid_allowance_way, paid_invite_way, is_borrow_invite, is_borrow_allowance, inviter, inviter_phone, invite_amount, is_customer_invite, is_borrow_invite')
			->where($where)
			->select();
		$return = [];
		if ($list) {
			$ls_biz = new LabourService;
			$labour_service_list = $ls_biz->cache();
			foreach ($list as $item) {
				$data = [];
				$data['id'] = $item['id']; // 订单明细编号
				$data['real_name'] = $item['real_name']; // 客户姓名
				$data['phone'] = $item['phone']; // 联系电话
				$data['idcard'] = $item['idcard']; // 身份证号
				$data['gender'] = $item['gender'] ? lang('male') : lang('female'); // 性别
				$data['amount'] = $type==2 ? $item['ent_wage'] * $item['worked_time'] : $item['amount']; // 企业返费
				$data['go_to_time'] = date('Y-m-d', $item['go_to_time']); // 接站时间
				$data['enterprise'] = $item['enterprise_name']; // 企业名称
				$data['deadline'] = date('Y-m-d', $this->getReceivableTime($item['go_to_time'], $item['receive_day'])); // 返费到期日期
				$data['receive_date'] = date('Y-m-d', $this->getReceivableTime($item['go_to_time'], $item['receive_day'] + 10)); // 补贴到期日期
				$data['receive_day'] = $item['receive_day']; // 企业在职天数
				$data['onduty_day'] = $item['onduty_day']; // 人选在职天数
				$data['labour_service'] = $item['ls_id'] ? $labour_service_list[$item['ls_id']]['name'] : ''; // 劳务公司
				$data['allowance'] = $type==2 ? $item['cp_wage'] * $item['worked_time'] : $item['allowance']; // 补贴总金额
				$data['is_outduty'] = $item['is_outduty'] ? lang('yes') : lang('no'); // 是否离职
				$data['worked_time'] = $item['worked_time']; // 工时
				$data['ent_wage'] = $item['ent_wage']; // 企业单价
				$data['cp_wage'] = $item['cp_wage']; // 人选单价
				$data['adviser_sure'] = $item['adviser_sure'] ? lang('yes') : lang('no'); //是否已入账
				$data['adjusted_price'] = $item['adjusted_price'];
				$data['paid_allowance_way'] = $this->paidWay($item['paid_allowance_way']); // 领取补贴方式
				$data['paid_invite_way'] = $this->paidWay($item['paid_invite_way']); // 领取推荐费方式
				$data['is_borrow_allowance'] = $item['is_borrow_allowance'] ? lang('yes') : lang('no'); // 补贴是否为垫付
				$data['inviter'] = $item['inviter']; // 推荐人
				$data['inviter_phone'] = $item['inviter_phone']; // 推荐人电话
				$data['invite_amount'] = $item['invite_amount']; // 推荐费
				$data['is_customer_invite'] = $item['is_customer_invite'] ? lang('yes') : lang('no'); // 是否为会员推荐
				$data['is_borrow_invite'] = $item['is_borrow_invite'] ? lang('yes') : lang('no'); // 推荐费是否为垫付
				$return[] = $data;
			}
		}
		return $return;
	}
	
	private function paidWay($type) {
		$return = '';
		if ($type == 1) $return = lang('cash');
		elseif ($type == 2) $return = lang('transfer');
	}
	
	/**
	 * 离职
	 * 更新订单明细的人选已离职
	 * @param array $id
	 * @param string $note 备注
	 */
	public function outduty($ids, $admin_id, $note){
		foreach ($ids as $i){
			/*更新明细的状态*/
			$item = $this->model->where('id', $i)->find();
			if (! $item->sales) continue;
			$item->is_outduty = 1;
			$item->save();
			/*记录人选操作日志*/
			$this->createLog($item->salesorder, $admin_id, ['is_outduty' => 1], 'update', $note);
		}
	}
	
	/**
	 * 继续在职
	 * 生成新的业绩
	 * @param array $id
	 * @param string $note 备注
	 */
	public function goonduty($ids, $admin_id, $note){
		foreach ($ids as $i){
			/*1.查询订单明细*/
			$item = $this->model->where('id', $i)->find();
			if (! $item->sales) continue;
			/*2.生成订单*/
			$salesorder_model = FmSalesorder::get($item->salesorder);
			$salesorder_model->id = '';
			$salesorder_model->go_to_time = time(); // 入职时间
			$salesorder_model->inviter = '';
			$salesorder_model->inviter_phone = '';
			$salesorder_model->invite_amount = 0;
			$salesorder_model->is_customer_invite = 0;
			$salesorder_model->isUpdate(false)->save();
			/*3.生成订单明细*/
			$item->id = '';
			$item->salesorder = $salesorder_model->id;
			$item->worked_time = 0;
			$item->isUpdate(false)->save();
			/*4,生成订单状态*/
			$status_model = model('salesorder/FmSalesorderStatus');
			$status_model->create(['salesorder' => $salesorder_model->id]);
			
			/*5.记录人选操作日志*/
			$this->createLog($item->salesorder, $admin_id, ['id' => $item->id], 'update', $note);
		}
	}
}
