<?php

namespace app\admin\model;

use think\Model;

class Employee extends Model
{
	/**
	 * 后台用户
	 */
	public function admin()
	{
		return $this->hasOne('app\admin\model\Admin', 'id', 'admin_id')->field('id, admin_name, status');
	}
	
	/**
	 * 组织机构
	 */
	public function organization()
	{
		return $this->hasOne('app\admin\model\Organization', 'id', 'org_id')->field('id, org_name, description, nickname');
	}
	
	/**
	 * 职位
	 */
	public function position()
	{
		return $this->hasOne('app\admin\model\Position', 'id', 'pos_id')->field('id, pos_name, description, is_manager');
	}
}