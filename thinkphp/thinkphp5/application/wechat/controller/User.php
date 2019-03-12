<?php
namespace app\wechat\controller;

use ylcore\WebView;
use think\Request;
use think\Cookie;
use think\Session;
use think\Log;
use think\Config;

class User extends Client
{
	/**
	 * 注册绑定
	 */
	public function bind(){
		$this->getOpenId();//获取参数
		$open_id = $this->decodeOpenId();//参数解密
		$business = controller('Subscriber', 'business');//业务对象
		$this->auth();
		if ($open_id && $business->isBindUser($open_id)) {
			//已绑定用户
			/*绑定用户信息*/
			$user = $business->getBindUser($open_id);
			return $this->toBind($user['uid']);
		} elseif ($auth = $this->getAuth()){
			//已注册用户
			return $this->toBind($auth['uid']);
		} else {
			//未绑定
			if (Request::instance()->isPost()){
				$message = $this->doRegister($open_id);
				return $this->message($message, '/wechat/user/bind');
			} else {
				return $this->toRegister();
			}	
		}
	}
	
	/**
	 * 注册操作
	 */
	private function doRegister($open_id){
		/*参数验证*/
		$mobile = request()->param('mobile');
		$real_name = request()->param('realname');
		if (is_mobile($mobile)==false){
			return lang('mobile_format_error');
		} elseif (! preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $real_name)){
			return lang('realname_format_error');
		} elseif (Config::get('enable_register_sms_code')){
        	$code = request()->param('sms_code');
        	/*注册验证码*/
        	$biz = controller('message/sms', 'business');
        	$status = $biz->checkRegisterCode($mobile, $code);
        	if ($status == false) return lang('sms_code_error');
        	else $biz->finishRegisterCode($mobile, $code);//完成验证
        }
		//注册用户
		$business = controller('user/user', 'business');
		$user = $business->mobileRegister ($mobile, '', array('realname' => $real_name, 'from'=>'wechat'));
		if ($user['uid']<=0) return lang('register_failure');
		$user['real_name'] = $real_name;
		$this->setAuth($user);//设置客户端缓存
		//绑定用户
		$subscriber_business = controller('subscriber', 'business');
		if( $open_id && $subscriber_business->isSubscriber($open_id) ){
			$subscriber_business->bindUser($open_id, $user['uid']);
		}
		return lang('register_success', [$user['uname']]);
	}
	
	/**
	 * 注册页面
	 */
	private function toRegister(){
		$view = new WebView();
		$view->setSkin('wechat');
		$view->assignBaseTpl();
		$view->assignTpl('weixin_header', 'header-weixin');
		$view->assign('title', lang('register'));
		$view->assign('is_weixin', is_weixin());
		if (is_weixin()) {
			$service = controller('WechatService', 'service');
			$view->assign('wechat_sign', $service->getJsSign(cur_page_url()));
		}
		return $view->fetch('register');
	}
	
	/**
	 * 绑定页面
	 */
	private function toBind($uid){
		$business = controller('user/user', 'business');
		$user=$business->getInfo($uid);
		/*视图*/	
		$view = new WebView();
		$view->setSkin('wechat');
		$view->assignBaseTpl();
		$view->assign('user', $user);
		$view->assignTpl('weixin_header', 'header-weixin');
		$view->assign('title', lang('my_account'));
		$view->assign('is_weixin', is_weixin());
		if (is_weixin()) {
			$service = controller('WechatService', 'service');
			$view->assign('wechat_sign', $service->getJsSign(cur_page_url()));
		}
		return $view->fetch('bind');
	}
	
	/**
	 * 微信权限验证
	 */
	public function auth(){
		$auth = $this->getAuth();
		if (! $auth){
			/*首次获取权限*/
			$user = '';
			$this->getOpenId();//获取参数
			$open_id = $this->decodeOpenId();//参数解密
			$business = controller('Subscriber', 'business');//业务对象
			if ($open_id && $business->isBindUser($open_id)) {
				//已绑定用户
				/*绑定用户信息*/
				$business = controller('subscriber', 'business');
				$user = $business->getBindUser($open_id);
			}
			$this->setAuth($user);
			$auth = $user;
		}
		return $auth;
	}
	
	/**
	 * 报名
	 */
	public function signup($id) {
		if (Request::instance()->isPost()) {
			$this->getOpenId();//获取参数
			$open_id = $this->decodeOpenId();//参数解密
			$message = $this->doSignup($id, $open_id);
			return $this->message($message, '/wechat/job/detail?id=' . $id);
		} else {
			return $this->toSignup($id);
		}
	}
	
	private function toSignup($job_id){
		$auth = $this->getAuth();//获取权限
		if ($auth){
			$business = controller('user/user', 'business');//定义业务对象
		
			if ($business->checkSignup($auth['uid'], $job_id)){
				return $this->message(lang('signup_success'), '/wechat/job/detail?id=' . $job_id);
			}
			//报名
			$view = new WebView();
			$view->setSkin('wechat');
			$view->assignBaseTpl();
			$view->assignTpl('weixin_header', 'header-weixin');
			$view->assign('title', lang('free_signup'));
			return $view->fetch('signup');
		}else{
			//绑定+报名
			$view = new WebView();
			$view->setSkin('wechat');
			$view->assignBaseTpl();
			$view->assignTpl('weixin_header', 'header-weixin');
			$view->assign('title', lang('free_signup'));
			return $view->fetch('bind-signup');
		}
	}
	
	private function doSignup($job_id, $open_id) {
		$auth = $this->getAuth();//获取权限
		$business = controller('user/user', 'business');//定义业务对象
		if ($auth) {
			if ($business->checkSignup($auth['uid'], $job_id)) return lang('signup_success');
		} else {
			/*1,先注册*/
			$message = $this->doRegister($open_id);
			/*2,注册之后再次验证权限，确认注册成功*/
			if ($this->getAuth()==false){
				return $message;
			} else {
				$auth = $this->getAuth();
			}
		}
		/*报名*/
		$birthday = request()->param('birthday');
		$gender = request()->param('gender');
		$business->signup($auth['uid'], array('job_id'=>$job_id, 'birthday'=>$birthday, 'gender'=>$gender));
		return lang('signup_success');
	}
	
	/**
	 * 邀请
	 */
	public function invite(){
		if (Request::instance()->isPost()){
			$auth = $this->getAuth();
			if (! $auth) {
				return $this->message(lang('invite_refuse'), '/wechat/user/bind');
			}
			$name = Request::instance()->post('realname');
			$mobile = Request::instance()->post('mobile');
			$uid = Session::get('user.uid');
			$username = Session::get('user.real_name');
			$res = controller('user/user','business');
			$result = $res->addInvite($uid,$username,$mobile,$name);

			return $this->message(lang('invite_success'), '/wechat/web');

		} else {
			return $this->toInvite();
		}
	}
	
	/**
	 * 邀请
	 */
	private function toInvite(){
		$res = controller('user/user','business');
		$result = $res->invite();
		//绑定+报名
		$view = new WebView();
		$view->setSkin('wechat');
		$view->assignBaseTpl();
		$view->assign('res',$result);
		return $view->fetch('invite');
	}
	
	/**
	 * 登录
	 */
	public function login(){
		$this->getOpenId();//获取参数
		$open_id = $this->decodeOpenId();//参数解密
		$business = controller('Subscriber', 'business');//业务对象
		$this->auth();
		if ($auth = $this->getAuth()){
			//已登录
			return $this->toBind($auth['uid']);
		} else {
			//未登录
			if (Request::instance()->isPost()){
				$message = $this->doLogin($open_id);
				return $this->message($message, '/wechat/user/bind');
			} else {
				//登录
				return $this->toLogin();
			}
		}
	}
	
	/**
	 * 登录界面
	 */
	private function toLogin(){
		$view = new WebView();
		$view->setSkin('wechat');
		$view->assignBaseTpl();
		$view->assignTpl('weixin_header', 'header-weixin');
		$view->assign('title', lang('login'));
		$view->assign('is_weixin', is_weixin());
		if (is_weixin()) {
			$service = controller('WechatService', 'service');
			$view->assign('wechat_sign', $service->getJsSign(cur_page_url()));
		}
		return $view->fetch('login');
	}
	
	/**
	 * 登录操作
	 */
	private function doLogin($open_id){
		/*参数验证*/
		$mobile = request()->param('mobile');
		//$password = request()->param('password');
		$business = controller('user/user', 'business');//定义业务对象
		if (is_mobile($mobile)==false){
			return lang('mobile_format_error');
		} elseif (Config::get('enable_login_sms_code')){
        	$code = request()->param('sms_code');
        	/*注册验证码*/
        	$biz = controller('message/sms', 'business');
        	$status = $biz->checkLoginCode($mobile, $code);
        	if ($status == false) return lang('sms_code_error');
        	else $biz->finishLoginCode($mobile, $code);//完成验证
        }
		$user = $business->getUserByMobile($mobile);
		if (!$user){
			return lang('login_error');
		} else {
			$this->setAuth($user);//设置客户端缓存
			//绑定用户
			$subscriber_business = controller('subscriber', 'business');
			if( $open_id && $subscriber_business->isSubscriber($open_id) ){
				$subscriber_business->bindUser($open_id, $user['uid']);
			}
			return lang('login_success');
		}
	}
}