<?php
/** 
 * CRM系统通信类 
 * 
 * 实现用户通知监听者接口，负责监听用户消息，然后通知CRM系统 
 * @author      hans<xiujixin@163.com> 
 * @version     1.0 
 * @since       1.0 
 */

namespace app\user\service;

use think\Config;
use think\Log;

use app\user\business\UserNotifyObserverInterface;

class Ylcrm implements UserNotifyObserverInterface
{
	private $config = [];//配置文件
	const NOTIFY_REGISTER_URL = '/index/api/notifyRegister';
	const NOTIFY_SIGNUP_URL = '/index/api/notifySignup';
	
	function __construct() {
		Config::load(APP_PATH.'user/config_crm.php');
		$this->config['api'] = Config::get('crm_api');
		$this->config['key'] = Config::get('crm_key');
	}
	
	/**
	 * @implement
	 * 接收用户注册通知
	 */
	public function updateUserRegister($mobile, $realname, $market) {
		return $this->sendRegisterMessage($mobile, $realname, $market);//发送用户注册通知
	}
	
	/**
	 * @implement
	 * 接收用户报名通知
	 */
	public function updateUserSignup($mobile, $jobname, $market) {
		return $this->sendSignupMessage($mobile, $jobname, $market);
	}
	
	/**
	 * 新用户注册消息
	 * @param string $mobile 手机号
	 * @param string $realname 姓名
	 */
	private function sendRegisterMessage($mobile, $realname, $market) {
		$data = [
			'mobile'=>$mobile, 
			'real_name'=>$realname, 
			'token'=>$this->config['key'], 
			'market'=>$market
		];
		$result = http_post($this->config['api'].self::NOTIFY_REGISTER_URL, $data);
		return $result;
	}
	
	/**
	 * 新用户报名通知
	 * @param string $mobile
	 * @param string $jobname
	 */
	private function sendSignupMessage($mobile, $jobname, $market) {
		$data = [
			'mobile'=>$mobile, 
			'job_name'=>$jobname, 
			'token'=>$this->config['key'], 
			'market'=>$market
		];
		$result = http_post($this->config['api'].self::NOTIFY_SIGNUP_URL, $data);
		return $result;
	}
}