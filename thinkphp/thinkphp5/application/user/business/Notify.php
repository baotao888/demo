<?php
/** 
 * 用户通知中介业务类 
 * 
 * 1，实现用户通知中介，负责各种用户的通知广播，用户类不负责广播，所有广播交给广播中介来做
 * 2，实现用户通知接口，负责广播用户消息 
 * @author      hans<xiujixin@163.com> 
 * @version     1.0 
 * @since       1.0 
 */

namespace app\user\business;

use think\Log;

use ylcore\Biz;
use app\wechat\business\Market;
use app\user\service\Ylcrm;

class Notify extends Biz implements NotifySubjectInterface, UserNotifyMediatorInterface
{
	private $_observers;//观察者对象
	private $_market;//推广市场
	
	public function __construct() {
		$this->_observers = array();
		$this->attach(new Ylcrm());//添加crm系统订阅
		$this->_market = new Market;
	}
	
	/**
	 * @implement
	 */
	public function attach(UserNotifyObserverInterface $observer) {
		return array_push($this->_observers, $observer);
	}
	
	/**
	 * @implement
	 */
	public function detach(UserNotifyObserverInterface $observer) {
		$index = array_search($observer, $this->_observers);
		if ($index === false || ! array_key_exists($index, $this->_observers)) {
			return false;
		}
	
		unset($this->_observers[$index]);
		return true;
	}
	
	/**
	 * @implement
	 * 广播用户注册通知
	 */
	public function userRegister($uid, $data, $market) {
		if (! is_array($this->_observers)) {
			return false;
		}
		
		foreach ($this->_observers as $observer) {
			$observer->updateUserRegister($data['mobile'], $data['realname'], $market);//发送新用户注册消息
		}
		
		return true;
	}
	
	/**
	 * @implement
	 * 广播用户报名通知
	 */
	public function userSignup($uid, $job_id, $market) {
		if (! is_array($this->_observers)) {
			return false;
		}
		
		$user_model = model('user/User');
		$user = $user_model->get($uid);//获取用户手机号码
		$job_model = model('job/Job');
		$job = $job_model->get($job_id);//获取职位名称
		
		foreach ($this->_observers as $observer) {
			$observer->updateUserSignup($user['mobile'], $job['job_name'], $market);//发送新用户报名消息
		}
		
		return true;
	}
	
	/**
	 * @implement
	 * 发送注册通知
	 */
	public function emitRegister($uid, $data) {
		$market = $this->getMarket();
		return $this->userRegister($uid, $data, $market);
	}
	
	/**
	 * @implement
	 * 发送报名通知
	 */
	public function emitSignup($uid, $job_id) {
		$market = $this->getMarket();
		return $this->userSignup($uid, $job_id, $market);
	}
	
	private function getMarket() {
		$code = $this->_market->getCode();
		return $code;
	}
}