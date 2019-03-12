<?php
namespace app\user\service;

use think\Config;
use think\Image;
use think\Log;

import('user.config_uc', APP_PATH);//ucenter配置文件
import('uc_client.client', EXTEND_PATH);//ucenter接口

class Uccenter
{
	public $message;
	
	/**
	 * Ucenter登录验证
	 */
	public function login($username, $password, $isuid = 0) {
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
	public function register($username, $password, $mobile = '', $email = '') {
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
	public function isExists($username, $uid = false, $mobile = false) {
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
	public function checkUserName($username) {
		return uc_user_checkname($username);
	}
	
	/**
	 * 验证用户头像是否存在
	 */
	public function checkAvatar($uid) {
		return uc_check_avatar($uid);
	}
	
	/**
	 * 获取用户头像
	 */
	public function getAvatar($uid) {
		return UC_AVATAR_PATH . '/' . uc_get_avatar($uid);
	}
	
	/**
	 * 保存头像
	 */
	public function saveAvatar($uid, $avatar) {
		/*保存到当前项目*/
		/*此处文件路径有耦合，不要分离文件，以免对应不到*/
		$avatar_path = Config::get('upload.path') . DS . 'avatar' . DS;
		$image = Image::open($avatar);
		$this->setAvatarHome($uid, $avatar_path);
		$big_image = $avatar_path . uc_get_avatar($uid, 'big');
		$image->thumb(400, 400)->save($big_image);
		$middle_image = $avatar_path . uc_get_avatar($uid, 'middle');
		$image->thumb(180, 180)->save($middle_image);
		$small_image = $avatar_path . uc_get_avatar($uid, 'small');
		$image->thumb(90, 90)->save($small_image);
		/*同步到ucenter*/
		$input = uc_api_input("uid=$uid");
		$formvars["avatar1"] = $this->imgTo16Code($big_image);
		$formvars["avatar2"] = $this->imgTo16Code($middle_image);
		$formvars["avatar3"] = $this->imgTo16Code($small_image);
		$action = UC_API . '/index.php?m=user&inajax=1&a=rectavatar&appid='.UC_APPID.'&input='.$input.'&avatartype=virtual&agent='.md5($_SERVER['HTTP_USER_AGENT']);
		$return = http_post($action, $formvars, false, md5($_SERVER['HTTP_USER_AGENT']));
		//Log::record($return);
		return $return;
	}
	
	/**
	 * 把图片转换成16进制
	 * @file : 文件路径
	 */
	private function imgTo16Code($filename){
		$file = file_get_contents($filename);
		$code = strtoupper(bin2hex($file));
		return $code;
	}
	
	private function setAvatarHome($uid, $dir = '.') {
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		!is_dir($dir.'/'.$dir1) && mkdir($dir.'/'.$dir1, 0777);
		!is_dir($dir.'/'.$dir1.'/'.$dir2) && mkdir($dir.'/'.$dir1.'/'.$dir2, 0777);
		!is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && mkdir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3, 0777);
	}

	public function updUcenterPwd($username, $newpw){
		return uc_user_edit($username, '', $newpw, '', 1);
	}
}