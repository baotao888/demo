<?php
namespace app\user\controller;

use think\Request;

class Check
{
	/**
	 * 验证用户名是否已经注册
	 * @return: -1,用户名无效；-2，用户名包含敏感词；-3，用户名已注册
	 */
	public function username()
	{
		$request = request();
		$user_name = $request->param('username');
		$service = controller('user/Uccenter', 'service');
		$status = $service->checkUserName($user_name)>0?false:true;
		return ['valid'=>$status];
	}
	
	/**
	 * 验证手机号码是否已经注册
	 */
	public function mobile()
	{
		$status = true;//默认未注册，手机号码不正确不验证
		$request = request();
		$mobile = $request->param('mobile');
		if (is_mobile($mobile)){
			$service = controller('user/Uccenter', 'service');
			$status = $service->isExists(false, false, $mobile)?false:true;
		}
		return ['valid'=>$status];
	}
	
	/**
	 * 验证手机号码是否未注册
	 */
	public function notmobile(){
		$status = true;//默认已注册，手机号不正确不验证
		$request = request();
		$mobile = $request->param('mobile');
		if (is_mobile($mobile)){
			$service = controller('user/Uccenter', 'service');
			$status = $service->isExists(false, false, $mobile)?true:false;
		}
		return ['valid'=>$status];
	}
}