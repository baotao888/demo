<?php
namespace app\admin\business;

use ylcore\Biz;
use think\Config;
use think\Log;

class CacheBiz extends Biz
{
	const ORGANIZATION = 'yl-crm-organization';//组织架构缓存名称
	const EMPLOYEE = 'yl-crm-employee';//员工缓存名称
	const EMPLOYEE_STATISTICS = 'yl-crm-employee-statistics';//员工数据缓存
	const EMPLOYEE_TEMP = 'yl-crm-employee-statistics-tmp';//员工临时数据缓存
	const LABOUR_SERVICE = 'yl-crm-labour-service';//劳务公司数据缓存
	
	
	/**
	 * 获取组织架构缓存键
	 */
	public function getOrganizKey(){
		return self::ORGANIZATION;
	}
	
	public function getEmployeeKey(){
		return self::EMPLOYEE;
	}
	
	public function getEmployeeStatisticsKey(){
		return self::EMPLOYEE_STATISTICS;
	}
	
	public function getEmployeeStatisticsTmpKey($id){
		return self::EMPLOYEE_TEMP . $id;
	}
	
	public function getLabourServiceKey() {
		return self::LABOUR_SERVICE;
	}
}