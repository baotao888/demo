<?php
// [ 呼入注册用户业务父类 ]
namespace app\user\business;

use think\Log;

use ylcore\Biz;
use app\user\model\CrmCallinApplicant;
use app\user\business\CallinInterface;//呼入用户接口
use app\user\model\CrmCallinUser;
use app\customer\business\CustomerPoolObserverInterface;//客户池观察者接口
use app\admin\business\EmployeeBiz;
use app\user\business\PublicCustomerPool;
use app\user\model\User;

class CallinRegister extends Biz implements CallinInterface, CustomerPoolObserverInterface
{
	protected $type = 'register';
	protected $from;
	
	private $model;
	
	private $applicantModel;
	private $customerMediator;
	private $employeeBiz;
	private $userModel;
	
	function __construct() {
		$this->model = new CrmCallinUser;
	}
	
	/**
	 * @override
	 */
	protected function dependencyInjection() {
		if (! $this->applicantModel) $this->applicantModel = new CrmCallinApplicant;
		if (! $this->customerMediator) $this->customerMediator = new PublicCustomerPool;
		if (! $this->employeeBiz) $this->employeeBiz = new EmployeeBiz;
		if (! $this->userModel) $this->userModel = new User;
	}
	
	/**
	 * @implement
	 * 显示顾问呼入用户列表
	 */
	public function myList($adviser, $sure, $search, $page, $pagesize) {
		$filter = ['keyword' => $search, 'sure' => $sure];
		$where = 'adviser=' . $adviser . $this->listCondition($filter);
		$count = $this->model->where($where)->count();
		$list = $this->model->where($where)->page($page, $pagesize)->order("callin_time desc")->select();
		$return = [];
		if ($list) {
			foreach ($list as $item) {
				$customer = [];
				$customer['user_id'] = $item['uid'];
				$customer[lang('real_name')] = $item['real_name'];
				$customer[lang('mobile')] = $item['mobile'];
				$customer[lang('reg_time')] = date('Y-m-d H:i:s', $item['callin_time']);
				$customer[lang('is_vip')] = $item['is_sure'] ? lang('is_sure') : lang('unsure');
				$return[] = $customer;
			}
		}
		return ['list' => $return, 'count' => $count];
	}
	
	/**
	 * 检索条件
	 * @param array $filter
	 * @return string
	 */
	private function listCondition(array $filter) {
		$where = '';
		/*关键字*/
		if (isset($filter['keyword'])){
			$search = $filter['keyword'];
			if (is_mobile($search)) $where .= " AND `mobile` = '$search'";
			elseif (is_numeric($search)) $where .= " AND `mobile` LIKE '$search%'";
			elseif ($search) $where .= " AND `real_name` LIKE '$search%'";
		}
		/*开始时间*/
		if (isset($filter['time_start'])) {
			$start = strtotime($filter['time_start']);
			if ($start) $where .= " AND `callin_time`>=$start";
		}
		/*结束时间*/
		if (isset($filter['time_end'])) {
			$end = strtotime(day_end_time($filter['time_end']));
			if ($end) $where .= " AND `callin_time`<=$end";
		}
		/*是否已确认*/
		if (isset($filter['sure'])) {
			$is_sure = $filter['sure'];
			if ($is_sure === true) {
				$where .= ' AND `is_sure`=1';
			} elseif ($is_sure === false) {
				$where .= ' AND `is_sure`=0';
			}
		}
		if ($this->from) $where .= ' AND `from`="' . $this->from . '"';
		return $where;
	}
	
	/**
	 * @implement
	 * 顾问确认用户
	 */
	public function sure($adviser, $ids) {
		/*标记用户为已确认和确认时间*/
		$this->model->save(['is_sure'=>1, 'operate_time'=>time()], '`uid` IN ('.implode(',', $ids).') AND `is_sure`=0 AND `adviser`='.$adviser);
		/*同时标记顾问名下和未分配的此用户的其他相关未确认呼入为已确认*/
		$this->dependencyInjection();
		/*1,用户职位报名申请*/
		$this->applicantModel->save(
			['is_sure'=>1, 'operate_time'=>time()], 
			'`user_id` IN ('.implode(',', $ids).') AND (`adviser` IS NULL OR `adviser`='.$adviser.') AND `is_sure`=0'
		);
	}
	
	/**
	 * 新增呼入用户
	 * @param integer $mobile
	 * @param integer $real_name
	 * @param integer $adviser_id
	 */
	public function save($adviser_id, $mobile, $real_name) {
		$this->dependencyInjection();
		$user = $this->userModel->where('mobile', $mobile)->field('uid')->find();//获取端口用户编号
		/*新增呼入用户*/
		$this->model->uid = $user['uid'];
		$this->model->mobile = $mobile;
		$this->model->real_name = $real_name;
		$this->model->callin_time = time();
		$this->model->adviser = $adviser_id;
		$this->model->from = $this->from;
		$this->model->save();
	}
	
	/**
	 * @implement
	 * 匹配已有呼入用户
	 */
	public function match($adviser_id, $mobile) {	
		/*匹配呼入注册用户*/
		$this->model->save(['is_sure' => 1, 'adviser'=> $adviser_id, 'operate_time' => time()], "`mobile`=$mobile AND `adviser` IS NULL");
	}
	
	/**
	 * @implement
	 * 显示所有顾问名下用户列表
	 */
	public function advisersList($search, $page, $pagesize) {
		$where = '`is_assign`=0 AND `adviser` IS NOT NULL' . $this->listCondition($search);
		$count = $this->model->where($where)->count();
		$list = $this->model->where($where)->page($page, $pagesize)->order('`adviser` asc, `callin_time` desc')->select();
		$return = [];
		if ($list){
			$this->dependencyInjection();
			foreach ($list as $item){
				$customer = [];
				$customer['uid'] = $item['uid'];
				$customer[lang('mobile')] = $item['mobile'];
				$customer[lang('real_name')] = $item['real_name'];
				$adviser = $this->employeeBiz->getOrganization($item['adviser']);//获取顾问信息
				$customer[lang('owner_id')] = $adviser['employee']['real_name'] . '(' . $adviser['org']['org_name'] . ')';
				$customer[lang('adviser_id')] = $item['is_sure'] ? lang('is_sure') : lang('unsure');
				$customer[lang('reg_time')] = date('Y-m-d H:i:s', $item['callin_time']);
				$customer[lang('from')] = lang($item['from']);
				$return[] = $customer;
			}
		}
		return ['list' => $return, 'count' => $count];
	}
	
	/**
	 * @implement
	 * 显示所有其他用户列表
	 */
	public function otherList($search, $page, $pagesize) {
		$where = '(`is_assign`=1 OR `adviser` IS NULL)' . $this->listCondition($search);
		$count = $this->model->where($where)->count();
		$list = $this->model->where($where)->page($page, $pagesize)->order('`callin_time` desc')->select();
		$return = [];
		if ($list){
			$this->dependencyInjection();
			foreach ($list as $item){
				$customer = [];
				$customer['uid'] = $item['uid'];
				$customer[lang('mobile')] = $item['mobile'];
				$customer[lang('real_name')] = $item['real_name'];
				if ($item['adviser']) {
					$adviser = $this->employeeBiz->getOrganization($item['adviser']);//获取顾问信息
					$customer[lang('owner_id')] = $adviser['employee']['real_name'] . '(' . $adviser['org']['org_name'] . ')';
				} else {
					$customer[lang('owner_id')] = '';
				}
				$customer[lang('adviser_id')] = $item['adviser'] ? lang('signned') : lang('unsignned');
				$customer[lang('reg_time')] = date('Y-m-d H:i:s', $item['callin_time']);
				$customer[lang('from')] = lang($item['from']);
				$return[] = $customer;
			}
		}
		return ['list' => $return, 'count' => $count];
	}
	
	/**
	 * @implement
	 * 经理分配用户
	 */
	public function assign($ids, $adviser, $manager) {
		/*更新分配后状态*/
		if ($this->enterCustomerPool($ids, $adviser, $manager)) {
			/*标记用户为已分配和分配时间*/
			$this->model->save(['is_assign'=>1, 'assigned_time'=>time(), 'adviser'=>$adviser], '`uid` IN ('.implode(',', $ids).') AND `adviser` IS NULL');
			/*同时标记未分配的此用户的其他相关未确认呼入为已分配*/
			/*1,用户职位报名申请*/
			$this->applicantModel->save(
				['is_assign'=>1, 'assigned_time'=>time(), 'adviser'=>$adviser], 
				'`user_id` IN ('.implode(',', $ids).') AND `adviser` IS NULL'
			);
			$flag = true;
		} else {
			$flag = false;
		}
		return $flag;
	}
	
	/**
	 * 用户进入客户池
	 */
	protected function enterCustomerPool($uids, $adviser, $employee_id) {
		$return = [];
		$this->dependencyInjection();
		//通过uid查询用户信息
		foreach ($uids as $k=>$v) {
			$return[] = $this->model->alias('callin')
				->join('UserData d', 'd.uid=callin.uid')
				->where('d.uid', $v)
				->field('d.gender, d.degree, d.birth, callin.mobile, callin.real_name')
				->find();
		}
		$flag = $this->customerMediator->transferCustomer($return, $adviser, $employee_id);//默认分配成功
		return $flag;
	}
	
	/**
	 * 获取顾问未确认用户数目
	 * @param int $adviser
	 */
	public function unsureCount($adviser) {
		$user_group = $this->model->where("`adviser`=$adviser AND `is_sure`=0")->field('COUNT(*) AS total,`from`')->group('`from`')->select();
		$return = [];
		if ($user_group) {
			foreach ($user_group as $item) {
				$return[$item['from']] = $item['total'];
			}
		}
		return $return;
	}
	
	/**
	 * 获取所有用户
	 */
	public function getAll($sure, $search, $page, $pagesize) {
		$filter = ['keyword' => $search];
		if ($sure !== false) {
			$filter['sure'] = $sure;
		}
		/*默认显示一天之内的用户*/
		$where = 'callin_time>' . (time() - 3600 * 24) . $this->listCondition($filter);
		$count = $this->model->where($where)->count();
		$list = $this->model->where($where)->page($page, $pagesize)->order("callin_time desc")->select();
		return ['list' => $list, 'count' => $count];
	}
}