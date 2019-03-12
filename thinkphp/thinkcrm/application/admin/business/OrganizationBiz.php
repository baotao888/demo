<?php

namespace app\admin\business;

use ylcore\Biz;
use app\admin\model\Organization;
use think\Config;
use think\Log;
use ylcore\Tree;
use think\Cache;

class OrganizationBiz extends Biz
{
	
	/**
	 * 添加架构
	 */
	public function save($data){
		$model = model('Organization');
		$model->data([
				'parent_id'  =>  $data['parent_id'],
				'org_name' =>  $data['org_name'],
				'description' =>  $data['description'],
				'nickname' =>  $data['nickname'],
				'is_adviser' =>  $data['is_adviser']
		]);
		$model->save();//保存

		return $model->id;
	}
	
	
	/**
	 * 获取所有架构信息
	 */
	public function getAll()
	{	
		$return = array();
		$model = model('admin/Organization');
		$list = $model->select();
		if ($list){
			foreach ($list as $item){
				$item['description'] = $item['description'];
				$return[$item['id']] = $item;
			}
		}

		return $return;
	}

	
	/**
	 * 更新指定的架构信息
	 */
	public function update($id, $data){
		$model = model('Organization');
		$update = [];
		if (isset($data['parent_id']) && $data['parent_id']){
			$update['parent_id'] = $data['parent_id'];
		}
		if (isset($data['org_name'])){
			$update['org_name'] = $data['org_name'];
		}
		if (isset($data['description'])){
			$update['description'] = $data['description'];
		}
		if (isset($data['nickname'])){
			$update['nickname'] = $data['nickname'];
		}
		if (isset($data['is_adviser'])){
			$update['is_adviser'] = $data['is_adviser'];
		}
		if (isset($data['listorder'])){
			$update['listorder'] = $data['listorder'];
		}
		$model->save($update, ['id' => $id]);//更新
	}
	
	/**
	 * 获取组织架构树
	 */
	public function tree(){
		$list = $this->getAll();
		$tree = new Tree();
		$tree->init($list);
		$arr_tree = $tree->get_tree_data(0);
		return $arr_tree;
	}
	
	/**
	 * 获取下级组织架构
	 * @param int $org_id
	 * @return array org_id list [1,2,3,4]
	 */
	public function subOrg($org_id){
		$return = [];
		$tree = new Tree();
		$tree->init($this->getCache());
		$tree->get_sub_children($org_id, $return);
		return $return;
	}
	
	public function getCache(){
		$cache_biz = controller('admin/CacheBiz', 'business');
		$cache_key = $cache_biz->getOrganizKey();
		$organization_list = Cache::get($cache_key);
		if (empty($organization_list)) {
			$organization_list = $this->getAll();//获取所有组织架构
			Cache::set($cache_key, $organization_list, Config::get('token_expire'));//设置缓存
		}
		return $organization_list;
	}
	
	/**
	 * 获取下级组织的所有员工
	 */
	public function subEmployee($org_id){
		$return = [];
		$sub_org = $this->subOrg($org_id);
		array_push($sub_org, $org_id);
		$model = model('admin/employee');
		$employees = $model->where("`org_id` IN (" . implode(',', $sub_org) . ")")->field('id')->select();
		if ($employees) {
			foreach($employees as $employee){
				$return[] = $employee['id'];
			}
		}
		return $return;
	}
	
	/**
	 * 获取所有的员工
	 * 过滤掉离职员工
	 * @param int $org_id
	 * @return array
	 */
	public function getEmployees($org_id){
		$biz = controller('admin/EmployeeBiz', 'business');
		$all_employees = $biz->getCache();
		$employees = [];
		foreach($all_employees as $employee){
			if ($employee['status']==0) continue;//过滤掉离职人员
			if ($employee['org_id']==$org_id) $employees[] = $employee;
		}
		return $employees;
	}
	
	public function employeeRelation($join_at, $gener, $me_join){
		$call = '';
		if ($me_join < $join_at){
			if ($gener==1) $call = lang('brother');//师弟
			else $call = lang('sister');//师妹
		}else{
			if ($gener==1) $call = lang('brother_1');//师弟
			else $call = lang('sister_1');//师妹
		}
		return $call;
	}
	
	/**
	 * 获取员工的所有上级组织
	 */
	public function employeeParentOrg($employee_id){
		$return = [];
		$model = model('admin/employee');
		$org_id = $model->where('id', '=', $employee_id)->value('org_id');
		$tree = new Tree();
		$tree->init($this->getCache());
		$tree->get_pos($org_id, $return);
		return $return;
	}
	
	/**
	 * 组织是否为员工的上级
	 */
	public function isParentOrganization($employee_id, $organization_id){
		$parent_org = $this->employeeParentOrg($employee_id);
		$flag = false;
		if ($parent_org){
			foreach ($parent_org as $org){
				if ($org['id'] == $organization_id){
					$flag = true;
					break;
				}
			}
		}
		return $flag;
	}
	
	/**
	 * 获取组织详情
	 * @param integer $org_id
	 */
	public function getOrgDetail($org_id){
		$return = [];
		$list = $this->getCache();
		foreach($list as $org){
			if ($org['id'] == $org_id){
				$return = $org;
				break;
			}
		}
		return $return;
	}
	
	/**
	 * 获取所有业务部门及其员工
	 * 此方法获取所有的员工数据统计，不过滤离职员工
	 */
	public function allAdviserOrgEmployees(){
		$group = [];
		$orgs = $this->getCache();
		foreach($orgs as $org){
			if ($org['is_adviser']<=0) continue;
			$items = [];
			$employees = $this->getEmployees($org['id']);
			foreach($employees as $employee){
				$items[] = $employee['id'];
			}
			$group[] = ['employees'=>$items, 'org'=>$org];
		}
		return $group;
	}
	
	/**
	 * 获取所有的业务部门
	 */
	public function getAllAdvisers(){
		$arr_org = [];
		$arr_employee = [];
		$orgs = $this->getCache();
		foreach($orgs as $org){
			if ($org['is_adviser']<=0) continue;
			$arr_org[] = ['name'=>$org['org_name'], 'id'=>$org['id'], 'order'=>$org['listorder']];
			$employees = $this->getEmployees($org['id']);
			foreach($employees as $employee){
				$item = $employee->toArray();
				$item['group'] = $org['org_name'];
				$arr_employee[] = $item;
			}
		}
		return ['orgs'=>$arr_org, 'employees'=>$arr_employee];
	}
}