<?php
namespace app\admin\business;

use ylcore\Biz;
use think\Config;
use think\Log;

class TaskBiz extends Biz
{
	
	/**
	 * 获取未完成任务
	 */
	public function getUnfinished($employee_id, $size, $start_time=false, $end_time=false){
		$return = [];
		$model = model('admin/task');
		$where = "`owner`=$employee_id AND `is_finished`=0";
		
		if ($start_time) $where .= " AND LEFT(`start_time`, 10) >= '$start_time'";
		if ($end_time) $where .= " AND LEFT(`start_time`, 10) <= '$end_time'";
		$return = $model->where($where)
						->page(1, $size)
						->select();
		return $return;
	}
	
	/**
	 * 新增任务
	 */
	public function save($data){
		$model = model('admin/task');
		$model->data([
			'owner'  =>  $data['owner'],
			'create_time' =>  time(),
			'title' => $data['title'],
			'start_time' => $data['start_time'],
			'info' => $data['info'],
			'location' => isset($data['location'])?$data['location']:'',
			'is_finished' => 0,
			'type' => $data['type'],
			'assigner' => isset($data['assigner'])?$data['assigner']:'',
			'customer' => isset($data['customer'])?$data['customer']:''
		]);
		$model->save();//保存
		return $model->id;
	}
	
	/**
	 * 添加普通任务
	 */
	public function addCommonTask($owner, $title, $start_time, $info, $location, $end_time){
		return $this->save([
				'owner' => $owner,
				'title' => $title,
				'start_time' => $start_time,
				'info' => $info,
				'location' => $location,
				'end_time' => $end_time,
				'type' => 'note'
		]);
	}
	
	/**
	 * 更新任务
	 */
	public function updateTask($id, $title, $info, $location, $start_time, $end_time){
		$model = model('admin/task');
		$update = [];
		if ($title) $update['title'] = $title;
		if ($info) $update['info'] = $info;
		if ($location) $update['location'] = $location;
		if ($start_time) $update['start_time'] = $start_time;
		if ($end_time) $update['end_time'] = $end_time;
		if ($update) $model->save($update, ['id' => $id]);//更新
		return true;
	}
	
	/**
	 * 完成任务
	 */
	public function finishTask($id){
		$model = model('admin/task');
		$update = ['is_finished'=>1];
		$model->save($update, ['id' => $id]);//更新
		return true;
	}
	
	/**
	 * 添加拨打计划
	 */
	public function addCustomerTask($employee, $customer, $start_time, $info = ''){
		//获取客户信息
		$business = controller('customer/CustomerPoolBiz', 'business');
		$customer_info = $business->get($customer);
		$title = lang('customer_task_title', [$customer_info['real_name']]);
		$customer_info['gender_txt'] = $customer_info['gender']?lang('gender_2'):lang('gender_1');
		if ($info=='') $info = lang('customer_task_info', [$customer_info['real_name'], $customer_info['phone'], $customer_info['gender_txt']]);
		return $this->save([
				'owner' => $employee,
				'title' => $title,
				'start_time' => $start_time,
				'info' => $info,
				'type' => 'contact',
				'customer' => $customer
		]);
	}
	
	/**
	 * 获取今日未完成任务总数
	 * @param integer $employee_id
	 * @return integer
	 */
	public function getTodayCount($employee_id){
		$model = model('admin/task');
		$today = date('Y-m-d');
		$return = $model->where('owner',  '=', $employee_id)
			->where('is_finished', '=', '0')
			->where("left(`start_time`, 10)<='$today'")
			->count();
		return $return;
	}
}