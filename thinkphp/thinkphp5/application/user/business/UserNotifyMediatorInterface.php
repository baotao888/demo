<?php
/** 
 * 用户通知中介类 
 * 
 * 负责发送用户通知
 * @author      hans<xiujixin@163.com> 
 * @version     1.0 
 * @since       1.0 
 */

namespace app\user\business;

interface UserNotifyMediatorInterface
{
	/**
	 * 发送注册通知
	 * @param int $uid
	 * @param array $data
	 */
	public function emitRegister($uid, $data);
	
	/**
	 * 发送报名通知
	 * @param int $uid
	 * @param int $job_id
	 */
	public function emitSignup($uid, $job_id);
}