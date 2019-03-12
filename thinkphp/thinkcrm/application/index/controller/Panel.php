<?php
namespace app\index\controller;

use think\Cache;
use think\Config;

use app\recruit\business\LabourService;

class Panel
{
	/**
	 * 更新缓存
	 */
	public function cache(){
		$business = controller('admin/CacheBiz', 'business');//定义缓存业务对象
		/*1,更新组织架构缓存*/
		$cache_key = $business->getOrganizKey();//获取组织机构缓存键
		$org_biz = controller('admin/OrganizationBiz', 'business');//定义组织机构业务对象
		$org_data = $org_biz->getAll();//获取所有组织架构
		Cache::set($cache_key, $org_data, Config::get('org_token_expire'));//设置缓存
		/*2,更新员工缓存*/
		$cache_key = $business->getEmployeeKey();//获取员工缓存键
		$employee_biz = controller('admin/EmployeeBiz', 'business');//定义员工业务对象
		$employee_data = $employee_biz->getAll();//获取所有员工
		Cache::set($cache_key, $employee_data, Config::get('org_token_expire'));//设置缓存
		/*3,更新员工数据缓存*/
		$cache_key = $business->getEmployeeStatisticsKey();
		$employee_biz = controller('admin/EmployeeBiz', 'business');
		$statistics = $employee_biz->getAllStatistics();
		Cache::set($cache_key, $statistics, Config::get('org_token_expire'));//设置缓存
		/*4,更新劳务公司缓存*/
		$cache_key = $business->getLabourServiceKey();
		$labour_biz = new LabourService;
		$labour_service_list = $labour_biz->cache();
		Cache::set($cache_key, $labour_service_list, Config::get('org_token_expire'));//设置缓存
		return 'success';
	}
	
	/**
	 * 更新顾问客户库容和保留人选
	 */
	public function cronCandidates(){
		/*更新所有过期的人选*/
		$business = controller('customer/CandidateBiz', 'business');
		$count = $business->updateAllExpired(Config::get('cron_candidate_expire'));
		return lang('clear_candidate_tip', [$count]);
	}
	
	/**
	 * 删除后台操作日志
	 */
	public function deleteAdminLog() {
		/*清理一周前的日志*/
		$business = controller('admin/LogBiz', 'business');
		$count = $business->clear();
		return lang('delete_adminlog_tip', [$count]);
	}
	
	/**
	 * 清理离职顾问的人选
	 */
	public function clearQuitEmployeeCandidate() {
		/*获取所有离职顾问的人选*/
		$business = controller('customer/CandidateBiz', 'business');
		$count = $business->updateQuitEmployee();
		return lang('clear_candidate_tip', [$count]);
	}
	
	/**
	 * 删除所有丢弃的人选
	 */
	public function deleteCandidate() {
		$business = controller('customer/CandidateBiz', 'business');
		$count = $business->deleteDepose();
		return lang('delete_candidate_tip_1', [$count]);
	}
}