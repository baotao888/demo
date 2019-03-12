<?php
/**
 * [微信客户端控制器]
 * 处理客户端缓存，调度服务器端缓存，管理用户权限
 */
namespace app\wechat\controller;

use think\Cookie;
use think\Log;
use think\Request;
use think\Session;

use ylcore\WebView;

class Client
{
	protected $openId;
	
	/**
	 * 加密open_id
	 */
	protected function encodeOpenId(){
		return $this->openId?encrypt_param($this->openId):'';
	}
	
	/**
	 * 解密open_id
	 */
	protected function decodeOpenId(){
		return $this->openId?encrypt_param($this->openId, 'DECODE'):'';
	}
	
	/**
	 * 获取open_id
	 */
	protected function getOpenId(){
		$request = request();
		if ($request->param('openid') != '') {
			$this->openId = $request->param('openid');
		} elseif ($request->session('openid') != '') {
			$this->openId = $request->session('openid');
		} elseif ($request->header('openid') != '') {
			$this->openId = $request->header('openid');
		} elseif (Cookie::get('openid') != '') {
			$this->openId = $request->cookie('openid');
		}
		if ($this->openId && Session::get('openid')!=$this->openId) {
			Session::set('openid', $this->openId);
		}
	}
	
	/**
	 * 信息提示页面
	 */
	protected function message($message, $backward = ''){
		if ($backward=='') $backward = $this->getBackward();
		$view = new WebView();
		$view->setSkin('wechat');
		$view->assignBaseTpl();
		$view->assign('message', $message);
		$view->assign('backward', $backward);
		return $view->fetch('message');
	}
	
	/**
	 * 获取权限
	 * @return fixed false=>未设置权限，[]=>权限为空，[user]=>绑定用户
	 */
	protected function getAuth(){
		$auth = false;
		if (Session::has('user')){
			$auth = Session::get('user');
		}
		return $auth;
	}
	
	/**
	 * 设置权限
	 */
	protected function setAuth($auth){
		Session::set('user', $auth);
	}
	
	/**
	 * 获取返回路径
	 */
	public function getBackward(){
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
	
	private function setOpenId($openId){
		$this->openId = $this->encodeOpenId($openId);
		if ($this->openId != '' && $this->openId != Cookie::get('openid')){
			Cookie::set('openid', $this->openId);//设置客户端缓存
		}
	}
	
	/**
	 * 保存市场推广码
	 */
	protected function saveMarketCode(){
		$request = request();
		$code = '';
		if ($request->param('ylmcode') != '') {
			$code = $request->param('ylmcode');
		} elseif (Cookie::get('ylmcode') != '') {
			$code = $request->cookie('ylmcode');
		}
		if ($code != '' && Cookie::get('ylmcode') != $code) {
			Cookie::set('ylmcode', $code);
		}
		return $code;
	}
}