<?php
namespace app\user\service;

import('user.config_uc', APP_PATH);//ucenter配置文件

import('uc_client.client', EXTEND_PATH);//ucenter接口

class Uccenter
{
	public $message;
	
	/**
	 * Ucenter登录验证
	 */
	public function login($username, $password, $isuid = 0){
		$uc = [];
		list($uid, $uc['uname'], $uc['password'], $uc['email'], $merge) = uc_user_login($username, $password, $isuid);
		if($uid <= 0) {
			/*统一提示信息，防止泄露*/
			if($uid == -1) {
				//账号不存在
				$this->message = lang('login_error');
			} elseif($uid == -2) {
				//密码错误
				$this->message = lang('login_error');
			} elseif($uid == -3) {
				//密保问题错误
				$this->message = lang('login_error');
			} else {
				$this->message = lang('unknow_error');
			}
		}
		$uc['uid'] = $uid;
		return $uc;
	}
	
	/**
	 * 在UCenter注册用户信息
	 */
	public function register($username, $password, $mobile = '', $email = ''){
		$uid = uc_user_register($username, $password, $email, '', '', '', $mobile);
		if($uid <= 0) {
			if($uid == -1) {
				$this->message = lang('register_error_1');
			} elseif($uid == -2) {
				$this->message = lang('register_error_2');
			} elseif($uid == -3) {
				$this->message = lang('register_error_3');
			} elseif($uid == -4) {
				$this->message = lang('register_error_4');
			} elseif($uid == -5) {
				$this->message = lang('register_error_5');
			} elseif($uid == -6) {
				$this->message = lang('register_error_6');
			} elseif($uid == -7) {
				$this->message = lang('register_error_7');
			} elseif($uid == -8) {
				$this->message = lang('register_error_8');
			} else {
				$this->message = lang('unknow_error');
			}
		}
		return $uid;
	}
	
	/**
	 * 用户是否已经注册
	 */
	public function isExists($username, $uid = false, $mobile = false){
		$flag = false;//默认没有注册
		if ($uid){
			if (uc_get_user($uid, true)) $flag = true;
		} elseif ($mobile){
			if (uc_get_user($mobile, false, true)) $flag = true;	
		} else {
			if (uc_get_user($username)) $flag = true;
		}
		return $flag;
	}
	
	/**
	 * 验证用户名是否正确
	 */
	public function checkUserName($username){
		return uc_user_checkname($username);
	}
	
}