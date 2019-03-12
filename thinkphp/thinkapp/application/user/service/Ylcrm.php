<?php
// [ CRM服务类 ]

namespace app\user\service;

use think\Config;
use think\Log;

class Ylcrm
{
	private $config = [];//配置文件
	const NOTIFY_REGISTER_URL = '/index/api/notifyRegister';
	const NOTIFY_SIGNUP_URL = '/index/api/notifySignup';
	
	function __construct(){
		Config::load(APP_PATH.'user/config_crm.php');
		$this->config['api'] = Config::get('crm_api');
		$this->config['key'] = Config::get('crm_key');
	}
	
	/**
	 * 新用户注册消息
	 * @param string $mobile 手机号
	 * @param string $realname 姓名
	 */
	public function sendRegisterMessage($mobile, $realname){
		$data = ['mobile'=>$mobile, 'real_name'=>$realname, 'token'=>$this->config['key']];
		$result = http_post($this->config['api'].self::NOTIFY_REGISTER_URL, $data);
		return $result;
	}
	
	/**
	 * 新用户报名通知
	 * @param string $mobile
	 * @param string $jobname
	 */
	public function sendSignupMessage($mobile, $jobname){
		$data = ['mobile'=>$mobile, 'job_name'=>$jobname, 'token'=>$this->config['key']];
		$result = http_post($this->config['api'].self::NOTIFY_SIGNUP_URL, $data);
		return $result;
	}
	
}