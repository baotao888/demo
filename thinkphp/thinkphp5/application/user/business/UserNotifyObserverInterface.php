<?php
/** 
 * 用户通知观察者接口 
 * 
 * 观察用户发出的通知
 * @author      hans<xiujixin@163.com> 
 * @version     1.0 
 * @since       1.0 
 */

namespace app\user\business;

interface UserNotifyObserverInterface
{
	/**
	 * 接收新用户注册消息
	 * @param string $mobile 手机号
	 * @param string $realname 姓名
	 * @param object $market 市场推广对象
	 */
	public function updateUserRegister($mobile, $realname, $market);
	
	/**
	 * 新用户报名通知
	 * @param string $mobile 手机号
	 * @param string $jobname 职位名称
	 * @param object $market 市场推广对象
	 */
	public function updateUserSignup($mobile, $realname, $market);
}