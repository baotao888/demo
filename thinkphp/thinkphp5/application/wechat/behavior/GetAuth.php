<?php
namespace app\wechat\behavior;

use think\Request;
use think\Config;
use think\Session;
use think\Cookie;
use think\Log;

class GetAuth
{
	private $wechat_state = 'yldagong';
	
    public function run(&$params)
    {
    	$flag = false;//默认无需权限验证
    	/*权限验证*/
    	$request = Request::instance();
    	$callback = Config::get('site_domain');
    	if ($request->module() == 'wechat'){
    		if ($request->controller() == 'Web'){
    			if ($request->action() == 'index') {
    				$flag = true;
    				$callback = $callback . '/wechat/web';
    			}
    		} elseif ($request->controller() == 'User') {
    			if ($request->action() == 'bind') {
    				$flag = true;
    				$callback = $callback . '/wechat/user/bind';
    			}
    			if ($request->action() == 'signup') {
    				$flag = true;
    				$callback = $callback . '/wechat/signup/1';
    			}
    		}
    	}
        if($flag){
        	if (Session::get('openid') != ''){
        		//do nothing
        	} elseif (Cookie::get('openid') != ''){
        		//do nothing
        	} elseif ($request->header('openid') != ''){
        		//do nothing
        	} elseif ($request->param('openid') != ''){
        		Session::set('openid', $request->param('openid'));//加密
        	} 
        	/*订阅号无法授权*/
        	if (false && is_weixin()) {
	        	$service = controller('WechatService', 'service');//微信服务对象
	        	$result = $service->getOauthAccessToken();//授权验证
	        	if ($result!=false){
	        		if ($result->openid) {
	        			Session::set('openid', encrypt_param($result->openid));//加密
	        		}
	        	} else {
	        		if ($request->param('state') == $this->wechat_state){
	        			Log::record($service->errMsg);//获取授权失败
	        			abort(401, '401 Unauthorized');
	        		}
	        	}
	        	if (Session::get('openid') != ''){
	        		//do nothing
	        	} elseif (Cookie::get('openid') != ''){
	        		//do nothing
	        	} elseif ($request->param('openid') != ''){
	        		//do nothing
	        	} elseif ($request->header('openid') != ''){
	        		//do nothing
	        	} else {
	        		/*用户身份为空，抓取微信端获取授权*/
        			//跳转获取授权，首次授权为空
        			$url = $service->getOauthRedirect($callback, $this->wechat_state, 'snsapi_base');
        			//Log::record($url);//[test]        			
        			header("Location: $url");
        		}
        	}
        }
    }
}