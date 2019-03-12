<?php

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
	/**
	 * 后台权限
	 */
	public function role()
	{
		return $this->hasOne('app\admin\model\AdminRole', 'id', 'role_id')->field('id, role_name, privileges');
	}
}