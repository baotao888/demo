<?php
/** 
 * 公海客户池业务类 
 * 
 * 负责呼入用户进入公海客户池
 * @author      hans<xiujixin@163.com> 
 * @version     1.0 
 * @since       1.0 
 */

namespace app\user\business;

use think\Log;

use ylcore\Biz;
use app\customer\business\CustomerPoolBiz;
use app\admin\business\EmployeeBiz;

class PublicCustomerPool extends Biz implements CustomerMediatorInterface
{
	const FROM_WEB = 'web';
	private $customerPoolBiz;
	private $employeeBiz;
	
	function __construct() {
		$this->employeeBiz = new EmployeeBiz;
		$this->customerPoolBiz = new CustomerPoolBiz;
	}
	
	/**
	 * @implement
	 * 用户进入公海客户池
	 */
	public function transferCustomer($users, $adviser, $operator) {
		$return = [];
		$assigner = $this->employeeBiz->employeeMappingAdmin($operator);//获取系统操作用户
		foreach ($users as $user_data) {
			//通过手机号码验证是否在客户池有记录
			$customer_id = $this->customerPoolBiz->mobileCanAssign($user_data['mobile']);
			if ($customer_id){
				if ($customer_id === true) {
					//插入新客户并分配
					$data['real_name'] = $user_data['real_name'];
					$data['phone'] = $user_data['mobile'];
					$data['gender'] = $user_data['gender'];
					$data['career'] = $user_data['degree'];
					$data['birthday'] = $user_data['birth'];
					$data['from'] = self::FROM_WEB;
					$data['employee_id'] = $operator;
					$data['admin_id'] = $assigner;
					$return[] = $this->customerPoolBiz->save($data);
				} else {
					//客户未分配，分配给该顾问
					$return[] = $customer_id;
				}
			}
		}
		$flag = true;//默认分配成功
		if ($return) {
			$flag = $this->customerPoolBiz->distribute($return, $adviser, $operator);//分配
		}
		return $flag;
	}
}