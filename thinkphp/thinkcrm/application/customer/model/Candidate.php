<?php

namespace app\customer\model;

use think\Model;

class Candidate extends Model
{
	public function setOrigin($origin){
		$this->origin = $origin;
	}
	
	/**
	 * 客户信息
	 */
	public function customer()
	{
		return $this->hasOne('app\customer\model\CustomerPool', 'id', 'cp_id')->field('real_name, phone, gender, idcard, birthday');
	}
	
	/**
	 * 顾问信息
	 */
	public function owner()
	{
		return $this->hasOne('app\admin\model\Employee', 'id', 'owner_id')->field('real_name, nickname, avatar, gender');
	}
	
	/**
	 * 状态获取器
	 * @param int $value
	 * @return Ambigous <string>
	 */
	public function getStatusAttr($value)
	{
		$status = [0=>'无意向', 1=>'意向', 2=>'报名', 3=>'接站', 4=>'入职', 5=>'离职'];
		return isset($status[$value])?$status[$value]:$value;
	}
	
	/**
	 * 职位信息
	 */
	public function job()
	{
		return $this->hasOne('app\job\model\Job', 'id', 'job_id')->field('job_name');
	}
}