<?php
namespace app\user\controller;

use think\Config;
use think\Session;
use think\Request;
use think\Cookie;

class Client
{
	protected $auth;
	
	/**
	 * 权限加密
	 */
	protected function encodeAuth($userid, $password){
		return encrypt_string($userid."\t".$password, Config::get('auth_token'), 'ENCODE', $this->getAuthKey('login'));
	}
	
	/**
	 * 权限解密
	 */
	protected function decodeAuth($auth){
		$auth_key = $this->getAuthKey('login');
		list($userid, $password) = explode("\t", encrypt_string($auth, Config::get('auth_token'), 'DECODE', $auth_key));
		return ['userid' => $userid, 'password'=>$password];
	}
	
	/**
	 * 生成验证key
	 * @param $prefix   参数
	 * @param $suffix   参数
	 */
	private function getAuthKey($prefix, $suffix="") {
		$pc_auth_key = md5('login' . Config::get('auth_key'));
		$authkey = md5($prefix.$pc_auth_key);
		return $authkey;
	}
	
	/**
	 * 验证权限是否正确
	 */
	protected function checkAuth(){
		$flag = false;
		if (Session::has('user') && Session::get('user')){
			$flag = true; 
		} elseif (Cookie::get('yl_auth')!=''){
			$auth = $this->decodeAuth(Cookie::get('yl_auth'));
			$biz = controller('user/user', 'business');
			$user = $biz->quickLogin($auth['userid'], $auth['password']);//ucenter接口登录
			if ($user['uid'] > 0){
				$this->setAuth($user);
				$flag = true;
			}
		}
		return $flag;
	}
	
	/**
	 * 获取返回路径
	 */
	protected function getBackward(){
		$backward = '';
		$request = request();
		if ($request->param('backward') != ''){
			$backward = $request->param('backward');
		} elseif ($request->session('backward') != ''){
			$backward = $request->session('backward');
		} elseif ($request->header('backward') != ''){
			$backward = $request->header('backward');
		} elseif (Cookie::get('backward') != ''){
			$backward = $request->cookie('backward');
		} elseif (isset($_SERVER["HTTP_REFERER"])) {
			$backward = $_SERVER["HTTP_REFERER"];
		}
		return $backward;
	}
	
	/**
	 * 设置客户端缓存
	 * @param array $user
	 * @param boolean $cookie 是否设置客户端缓存
	 * @param string $password 用户密码
	 */
	protected function setAuth($user, $cookie = false, $password = ''){
		Session::set('user', $user);
		if ($cookie){
			Cookie::set('yl_auth', $this->encodeAuth($user['uid'], $password), 3600 * 24 * 30);
		}
	}
}