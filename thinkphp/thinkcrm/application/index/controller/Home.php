<?php
namespace app\index\controller;

use think\Config;
use ylcore\Format;
use think\Log;
use think\Cache;
use app\admin\controller\AdminAuth;

class Home extends AdminAuth
{
	/**
	 * 本月候选人统计
	 */
	public function monthCandidate(){
		/*用户权限验证*/
		$business = controller('customer/CandidateBiz', 'business');
		if ($this->isAdministrator()){
			//管理员统计所有的候选人
			$list = $business->monthStatistics();
		} elseif ($this->isManager()) {
			/*管理岗位统计本组织及其下级组织的候选人*/
			$org_id = $this->getOrganizationId();
			$list = $business->orgMonthStatistics($org_id);
		} else {
			/*非管理岗位统计自己的候选人*/
			$employee_id = $this->getEmployeeId();
			$list = $business->myMonthStatistics($employee_id);
		}
		return array('list' => $list);
	}
	
	/**
	 * 组织结构信息统计
	 */
	public function organization(){
		if ($this->isAdministrator()) return false;
		$return = ['total'=>0, 'male'=>0, 'female'=>0, 'teacher'=>0, 'list'=>[], 'header'=>[], 'nickname'=>$this->getOrganizationName()];		
		/*获取组织下的所有成员*/
		$business = controller('admin/OrganizationBiz', 'business');
		$org_id = $this->getOrganizationId();
		$employees = $business->getEmployees($org_id);
		$employee_biz = controller('admin/EmployeeBiz', 'business');
		foreach($employees as $employee){
			$return['total']++;//总人数加一
			/*统计师兄弟*/
			if ($employee->position->is_manager){
				$return['teacher']++;
			}else{
				if ($employee['gender']) $return['male']++;
				else $return['female']++;
			}
			/*统计关系*/
			if ($employee['id'] != $this->getEmployeeId() || $this->isManager()){
				if ($this->isManager()){
					if ($employee->position->is_manager){
						$return['header'][] = [
								'nickname' => $employee['nickname'],
								'avatar' => $employee['avatar']?$employee['avatar']:$employee_biz->getDefaultAvatar($employee['gender']),
								'call' => lang('teacher'),
								'gender' => $employee['gender']
						];
					} else {
						$return['list'][] = [
								'nickname' => $employee['nickname'],
								'avatar' => $employee['avatar']?$employee['avatar']:$employee_biz->getDefaultAvatar($employee['gender']),
								'call' => lang('student')
						];
					}
				} else {
					if ($employee->position->is_manager){
						$return['header'][] = [
							'nickname' => $employee['nickname'],
							'avatar' => $employee['avatar']?$employee['avatar']:$employee_biz->getDefaultAvatar($employee['gender']),
							'call' => lang('teacher'),
							'gender' => $employee['gender']	
						];
					} else {
						$return['list'][] = [
							'nickname' => $employee['nickname'],
							'avatar' => $employee['avatar']?$employee['avatar']:$employee_biz->getDefaultAvatar($employee['gender']),
							'call' => $business->employeeRelation($employee['join_at'], $employee['gender'], $this->getJoinAt()),
							'gender' => $employee['gender']	
						];
					}	
				}
			}
		}
		$return['list'] = array_slice($return['list'], 0, 4);//主页只取前4条记录
		return $return;
	}
	
	/**
	 * 公告信息统计
	 */
	public function announcement(){
		$param = request()->param();
		$pagesize = isset($param['pagesize'])?$param['pagesize']:5;
		$business = controller('admin/AnnouncementBiz', 'business');
		$list = $business->getAll($pagesize);
		return $list;
	}
	
	/**
	 * 今日候选人统计
	 */
	public function todayCandidate(){
		/*用户权限验证*/
		$business = controller('customer/CandidateBiz', 'business');
		if ($this->isAdministrator()){
			//管理员统计所有的候选人
			$list = $business->todayStatistics();
		} elseif ($this->isManager()) {
			/*管理岗位统计本组织及其下级组织的候选人*/
			$org_id = $this->getOrganizationId();
			$list = $business->orgTodayStatistics($org_id);
		} else {
			/*非管理岗位统计自己的候选人*/
			$employee_id = $this->getEmployeeId();
			$list = $business->myTodayStatistics($employee_id);
		}
		return $list;
	}
	
	/**
	 * 本周候选人统计
	 */
	public function weekCandidate(){
		/*用户权限验证*/
		$business = controller('customer/CandidateBiz', 'business');
		if ($this->isAdministrator()){
			//管理员统计所有的候选人
			$list = $business->weekStatistics();
		} elseif ($this->isManager()) {
			/*管理岗位统计本组织及其下级组织的候选人*/
			$org_id = $this->getOrganizationId();
			$list = $business->orgWeekStatistics($org_id);
		} else {
			/*非管理岗位统计自己的候选人*/
			$employee_id = $this->getEmployeeId();
			$list = $business->myWeekStatistics($employee_id);
		}
		return $list;
	}
	
	/**
	 * 季度候选人统计
	 */
	public function quarterCandidate(){
		/*用户权限验证*/
		$business = controller('customer/CandidateBiz', 'business');
		if ($this->isAdministrator()){
			//管理员统计所有的候选人
			$list = $business->quarterStatistics();
		} elseif ($this->isManager()) {
			/*管理岗位统计本组织及其下级组织的候选人*/
			$org_id = $this->getOrganizationId();
			$list = $business->orgQuarterStatistics($org_id);
		} else {
			/*非管理岗位统计自己的候选人*/
			$employee_id = $this->getEmployeeId();
			$list = $business->myQuarterStatistics($employee_id);
		}
		return $list;
	}
	
	/**
	 * 月度图表统计
	 */
	public function monthPlotStatistics(){
		$business = controller('customer/CandidateBiz', 'business');
		$plot = $business->monthPlotStatistics();
		return $plot;
	}
}