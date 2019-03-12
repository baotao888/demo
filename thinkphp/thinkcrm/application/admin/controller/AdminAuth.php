<?php
namespace app\admin\controller;

use think\Cache;

class AdminAuth
{
	protected $admin_information;//当前后台登录用户的信息
	
	function __construct() 
	{
		$this->AdminAuth();
	}
	/**
	 * 获取后台登录用户信息
	 * @return array
	 */
	protected function AdminAuth(){
		$admin_business = controller('admin/AdminBiz', 'business');//业务对象
		$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));	
		$this->admin_information = Cache::get($token_key);		
	}
	
	/**
	 * 判断当前登录用户是否为超级管理员
	 */
	protected function isAdministrator(){
		$admin_business = controller('admin/AdminBiz', 'business');//业务对象
		return $admin_business->isAdministratorToken($this->admin_information);
	}
	
	/**
	 * 获取当前登录员工编号
	 */
	protected function getEmployeeId(){
		return isset($this->admin_information['employee'])?$this->admin_information['employee']['id']:0;
	}
	
	/**
	 * 判断当前登录用户的职位是否为管理职位
	 */
	protected function isManager(){
		return $this->admin_information['position']['is_manager'];
	}
	
	/**
	 * 判断当前登录用户的下级组织机构
	 */
	protected function getOrganizationId(){
		return isset($this->admin_information['organization'])?$this->admin_information['organization']['id']:0;
	}
	
	/**
	 * 获取入职时间
	 */
	protected function getJoinAt(){
		return $this->admin_information['employee']['join_at'];
	}
	
	/**
	 * 获取组织机构昵称
	 */
	protected function getOrganizationName(){
		return $this->admin_information['organization']['nickname'];
	}
	
	/**
	 * 获取当前登录用户编号
	 */
	protected function getAdminId(){
		return isset($this->admin_information['admin'])?$this->admin_information['admin']['id']:0;
	}
	
	protected function getRecognizePriorTime(){
		return isset($this->admin_information['statistics']['recognize_prior_time'])?$this->admin_information['statistics']['recognize_prior_time']:0;
	}
	
	/**
	 * 判断当前登录用户是否为管理员
	 */
	protected function isAdmin(){
		return isset($this->admin_information['admin']['is_admin'])?$this->admin_information['admin']['is_admin']:0;
	}
}