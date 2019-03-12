<?php
// [ 通知业务类 ]

namespace app\user\business;

use think\Log;

use ylcore\Biz;
use app\user\service\Ylcrm;

class Notify extends Biz
{
	/**
	 * 用户注册通知
	 * @param integer $uid 用户编号
	 * @param array $data 用户注册数据
	 */
	public function userRegister($uid, $data){
		$service = new Ylcrm();//crm系统
		$service->sendRegisterMessage($data['mobile'], $data['realname']);//发送新用户注册消息
	}
	
	/**
	 * 用户报名通知
	 * @param integer $uid
	 * @param integer $job_id
	 */
	public function userSignup($uid, $job_id){
		$user_model = model('user/User');
		$user = $user_model->get($uid);//获取用户手机号码
		$job_model = model('job/Job');
		$job = $job_model->get($job_id);//获取职位名称
		$service = new Ylcrm();//crm系统
		$service->sendSignupMessage($user['mobile'], $job['job_name']);//发送新用户报名消息
	}
}