<?php
/** 
 * 用户通知主题接口 
 * 
 * 负责发送用户相关的通知。 
 * @author      hans<xiujixin@163.com> 
 * @version     1.0 
 * @since       1.0 
 */

namespace app\user\business;

interface NotifySubjectInterface
{
	/**
	 * 增加一个新的观察者对象
	 * @param Observer $observer
	 */
	public function attach(UserNotifyObserverInterface $observer);
	
	/**
	 * 删除一个已注册过的观察者对象
	 * @param Observer $observer
	 */
	public function detach(UserNotifyObserverInterface $observer);
	
	/**
	 * 用户注册通知
	 * @param integer $uid 用户编号
	 * @param array $data 用户注册数据
	 * @param mixed $market 市场推广对象
	 */
	public function userRegister($uid, $data, $market);
	
	/**
	 * 用户报名通知
	 * @param int $uid 用户编号
	 * @param int $job_id 职位编号
	 * @param mixed $market 市场推广对象
	 */
	public function userSignup($uid, $job_id, $market);
}