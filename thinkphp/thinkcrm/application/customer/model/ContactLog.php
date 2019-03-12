<?php

namespace app\customer\model;

use think\Model;

class ContactLog extends Model
{
	public function poolname()
	{
		return $this->hasOne('app\customer\model\CustomerPool', 'id', 'cp_id')->field('real_name');
	}
	
	public function adviser()
	{
		return $this->hasOne('app\customer\model\Employee', 'id', 'employee_id')->field('real_name, nickname');
	}
	
	/**
	 * 状态获取器
	 * @param int $value
	 * @return Ambigous <string>
	 */
	public function getResultAttr($value)
	{
		$status = [0=>'无意向',1=>'意向',2=>'报名',3=>'接站',4=>'入职',5=>'离职'];
		return isset($status[$value])?$status[$value]:$value;
	}
}