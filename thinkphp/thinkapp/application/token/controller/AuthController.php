<?php
//[权限控制器父类]
namespace app\token\controller;

use think\Controller;
use think\Request;

use app\token\business\Auth;

abstract class AuthController extends Controller
{
	protected $uid;
	
	function __construct() {
		$this->validateAccess();//权限验证
	}
	
	/**
	 * 是否需要验证
	 */
	abstract function isValidate();
	
	/**
	 * 权限验证
	 * @return boolean
	 */
	protected function validateAccess() {
		if ($this->isValidate()) {
			/*获取参数*/
			$request = Request::instance();
			$header = $request->header();
			if (isset($header['yl-app-token']) && $header['yl-app-token'] != '' ){
				$business = new Auth();//业务对象
				$user_flag = $business->verify($header['yl-app-token']);//验证权限
				if ($user_flag == -1) abort(401, '401 Unauthorized');//权限无效
				elseif ($user_flag == 0) abort(411, '411 Authorization expired.');//权限过期
				else $this->uid = $user_flag;//验证通过，获取用户编号
			} else {
				abort(401, '401 Unauthorized');//权限无效
			}			
		}
	}
	
	protected function getUser() {
		$user_id = 0;
		/*获取参数*/
		$request = Request::instance();
		$header = $request->header();
		if (isset($header['yl-app-token']) && $header['yl-app-token'] != '' ){
			$business = new Auth();//业务对象
			$user_flag = $business->verify($header['yl-app-token']);//验证权限
			if ($user_flag > 0) $user_id = $this->uid = $user_flag;
		}
		return $user_id;
	}
}