<?php
namespace app\index\controller;

use think\Cache;
use think\Config;

use app\salesorder\model\FmSalesorder;
use app\salesorder\model\FmSalesorderStatus;

class Patch
{
	/**
	 * 更新端口用户的求职顾问
	 */
	public function updateUserAdviser(){
		$return = '';
		/*网站用户*/
		$model = model('user/user');
		$condition = "`is_vip` = 1 AND `mobile` IS NOT NULL AND adviser_id IS NULL";//获取网站所有已分配用户
		$list = $model->where($condition)->limit(5000)->select();
		if ($list){
			$count = 0;
			$customer_model = model('customer/CustomerPool');
			foreach($list as $user){
				if (! $user['mobile']) continue;
				$adviser_id = $customer_model->alias('cp')->join("Candidate cd","cp.id=cd.cp_id")->where("phone",$user['mobile'])->order("cd.id desc")->value('owner_id');
				$flag = $model->save(['adviser_id'=>$adviser_id], 'mobile="'.$user['mobile'].'"');
				if ($flag){
					$count ++;
				}
			}
			$return = '更新完毕，总共更新了' . $count . '个用户';
		} else {
			$return = '所有已分配用户已经更新完毕';
		}
		return $return;
	}
	
	/**
	 * 删除重复的人选
	 * 按照认领时间，只保留先认领的人选
	 */
	public function deletedRepeatCandidate() {
		/*获取所有重复客户*/
		$model = model('customer/candidate');
		$cp_id_list = $model->where('is_deleted', 0)->field('count(*) as total, cp_id')->group('cp_id')->having('total>1')->select();
		if ($cp_id_list) {
			$arr_owner = [];//消息提醒客户
			$arr_id = [];//删除的人选
			foreach ($cp_id_list as $item) {
				/*获取人选详情*/
				$candidate_list = $model
					->where('cp_id', $item['cp_id'])
					->where('is_deleted', 0)
					->field('id, owner_id, status, create_time, latest_contact_time, is_remain')
					->order('latest_contact_time desc, create_time')//按照最后联系时间降序
					->select();
				$is_deleted = false;//不删除
				foreach ($candidate_list as $key=>$candidate) {
					if ($candidate['status'] != lang('no_attention')) {
						$is_deleted = true;
						continue;//排除非无意向的人选
					} elseif ($candidate['latest_contact_time'] != null) {
						$is_deleted = true;
						continue;//排除已联系的人选
					} elseif ($candidate['is_remain'] == 1) {
						$is_deleted = true;
						continue;//排除已保留的人选
					} elseif ($key==0) {
						$is_deleted = true;
						continue;//排除第一个联系的人选
					}
					/*剩余可删除*/
					if ($is_deleted) {
						$arr_id[] = $candidate['id'];
						if (isset($arr_owner[$candidate['owner_id']])) {
							$arr_owner[$candidate['owner_id']]++;
						} else {
							$arr_owner[$candidate['owner_id']] = 1;
						}
					}
				}
			}
			/*删除人选*/
			$flag = false;
			if ($arr_id) {
				$flag = $model->save(['is_deleted'=>1, 'depose_time'=>time()], "`id` IN (" .implode(',', $arr_id). ")");
			}
			/*发送短消息*/
			if ($flag && $arr_owner) {
				$biz = controller('admin/MessageBiz', 'business');
				foreach ($arr_owner as $owner=>$count) {
					$biz->sendSystemMessage($owner, lang('delete_candidate_tip', [$count]));
				}
			}
		}
		$return = '清理完毕，总共删除了' . count($arr_id) . '个重复人选';
		return $return;
	}
	/**
	 * 去除用户手机号码前面的空格
	 */
	public function trimCustomerPhone() {
		$model = model('customer/CustomerPool');
		$list = $model->where("phone LIKE ' %'")->field('id, phone')->select();
		$count = 0;
		if ($list) {
			foreach($list as $item) {
				if ($model->where('phone', trim($item['phone']))->where("id!=".$item['id'])->value('id')) {
					continue;
				} else {
					$model->update(['phone'=>trim($item['phone'])], ['id'=>$item['id']]);
					$count ++;
				}
			}
		}
		return '总共清理了' . $count . '个客户';
	}
	
	/**
	 * 导入端口用户到CRM系统呼入用户
	 */
	public function importUserToCallin(){
		$return = '';
		/*网站用户*/
		$model = model('user/user');
		$condition = "`mobile` IS NOT NULL AND `real_name` IS NOT NULL";//获取网站所有已分配用户
		$list = $model->alias('um')->join('UserData ud', 'um.uid=ud.uid')->where($condition)->field('um.*, ud.real_name, ud.reg_time')->select();
		$count = 0;
		$cl_user_model = model('user/CrmCallinUser');
		$cl_user_list = [];
		foreach($list as $user){
			$item = [];
			$item['uid'] = $user['uid'];
			$item['mobile'] = $user['mobile'];
			$item['real_name'] = $user['real_name'];
			$item['callin_time'] = $user['reg_time'];
			if ($user['adviser_id']) {
				/*顾问已确认*/
				$item['is_sure'] = 1;
				$item['operate_time'] = $user['reg_time'];
				$item['adviser'] = $user['adviser_id'];
			} else {
				/*去客户池取未确认用户的顾问*/
				$customer_model = model('customer/CustomerPool');
				$adviser_id = $customer_model->alias('cp')->join("Candidate cd", "cp.id=cd.cp_id")->where("phone", $user['mobile'])->order("cd.id desc")->value('owner_id');
				if ($adviser_id) {
					$item['adviser'] = $adviser_id;
				}
			}
			/*标记分配人选*/
			if ($user['is_vip'] == 2) {
				$item['is_assign'] = 1;
				$item['assigned_time'] = $user['reg_time'];
			}
			$count ++;
			$cl_user_list[] = $item;
		}
		$cl_user_model->saveAll($cl_user_list, false);
		$return = '导入完毕，总共导入了' . $count . '个用户';
		return $return;
	}
	
	/**
	 * 导入端口用户职位申请到CRM系统呼入报名用户
	 */
	public function importUserJobProcessToCallinTrace(){
		$return = '';
		/*CRM系统呼入用户*/
		$job_process_model = model('user/UserJobProcess');
		$list = $job_process_model->alias('ujp')
			->join('User user', 'user.uid=ujp.user_id')
			->join('UserData ud', 'user.uid=ud.uid')
			->where("job_id > 0")
			->field('ujp.*, user.mobile, ud.real_name')
			->select();
		$count = 0;
		$cl_applicant_model = model('user/CrmCallinApplicant');//CRM系统呼入报名用户
		$applicant_list = [];
		foreach($list as $user){
			$item = [];
			$item['user_id'] = $user['user_id'];
			$item['mobile'] = $user['mobile'];
			$item['real_name'] = $user['real_name'];
			$item['base_time'] = $user['creat_time'];
			$item['job_id'] = $user['job_id'];
			if ($user['adviser_id']) {
				/*已确认的申请*/
				$item['is_sure'] = 1;
				$item['operate_time'] = $user['creat_time'];
				$item['adviser'] = $user['adviser_id'];
			} else {
				/*去客户池取未确认用户的顾问*/
				$customer_model = model('customer/CustomerPool');
				$adviser_id = $customer_model->alias('cp')->join("Candidate cd", "cp.id=cd.cp_id")->where("phone", $user['mobile'])->order("cd.id desc")->value('owner_id');
				if ($adviser_id) {
					$item['adviser'] = $adviser_id;
				}
			}
			/*标记分配人选*/
			if ($user['is_assign'] == 2) {
				$item['is_assign'] = 1;
				$item['assigned_time'] = $user['creat_time'];
			}
			$count ++;
			$applicant_list[] = $item;
		}
		$cl_applicant_model->saveAll($applicant_list, false);
		$return = '导入完毕，总共导入了' . $count . '条报名申请';
		return $return;
	}
	
	/**
	 * 官网职位导入系统招聘职位
	 */
	public function job2recruit() {
		$model = model('job/Job');
		$list = $model->field('id,enterprise_id,region_txt,publish_time,welfare')->select();
		$count = 0;
		foreach ($list as $item) {
			$job_model = model('recruit/CrmJob');
			$job_model->create([
				'id' => $item['id'],
				'enterprise_id' => $item['enterprise_id'],
				'region' => $item['region_txt'],
				'salary_intro' => strip_tags($item['welfare']),
				'validity_period' => date('Y-m-d', $item['publish_time']),
				'type' => 1
			]);
			$count ++;
		}
		$return = '导入完毕，总共导入了' . $count . '个职位';
		return $return;
	}
	
	/**
	 * 入职人选生成销售订单
	 */
	public function candidate2salesorder() {
		$count = 0;
		$model= model('customer/Candidate');
		$start_time = mktime(0, 0, 0, 10, 1, 2017);
		$return = $model->alias('cad')
			->join('CustomerPool cp', 'cad.cp_id=cp.id')
			->join('Job job', 'job.id=cad.job_id')
			->field('cad.*, job.enterprise_id, cp.real_name, cp.phone')
			->where('`cad`.`subsidy` is not null and `on_duty_time`>' . $start_time . ' and `job_id` IS NOT NULL')
			->select();
		foreach ($return as $customer) {
			/*订单*/
			$so_item = [
				'adviser_id' => $customer['owner_id'],
				'cp_id' => $customer['cp_id'],
				'job_id' => $customer['job_id'],
				'ent_id' => $customer['enterprise_id'],
				'ls_id' => 1,	
				'go_to_time' => $customer['on_duty_time'],
				'inviter' => $customer['inviter'],
				'inviter_phone' => $customer['inviter_phone'],
				'invite_amount' => $customer['invite_amount'],
				'type' => 1,
			];
			$salesorder = FmSalesorder::create($so_item);
			/*订单明细*/
			$salesorderitem = model('salesorder/FmSalesorderItems');
			$salesorderitem->id = '';
			$si_item = [
				'salesorder' => $salesorder->id,
				'amount'	=> $customer['award'], // 企业返费
				'receive_day' => $customer['dutydays'], // 返费天数
				'allowance' => $customer['subsidy'], // 补贴金额
				'onduty_day' => $customer['dutydays'] // 在职天数
			];
			$salesorderitem->isUpdate(false)->save($si_item);
			/*订单状态*/
			if ($customer['on_duty_time'] <= 1543593600) {
				$ss_item = [
					'salesorder' => $salesorder->id,
					'adviser_sure' => 1,
					'paid_allowance_way' => 1,
					'is_borrow_allowance' => 0,
					'paid_invite_way' => $customer['inviter'] ? 1 : 0,
					'is_borrow_invite' => $customer['inviter'] ? 1 : 0,
				];
			} else {
				$ss_item = [
					'salesorder' => $salesorder->id
				];
			}
			FmSalesorderStatus::create($ss_item);
			$count ++;
		}
		$return = '导入完毕，总共导入了' . $count . '个销售订单';
		return $return;
	}
}