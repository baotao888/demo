<?php
namespace app\recruit\model;

use think\Model;

class CrmJob extends Model
{
	/**
	 * 职位提供者
	 */
	public function provider()
	{
		return $this->hasOne('app\recruit\model\CrmJobProvider', 'job_id', 'id')->field('labour_service_id');
	}
	
	/**
	 * 职位企业
	 */
	public function enterprise()
	{
		return $this->hasOne('app\job\model\Enterprise', 'id', 'enterprise_id')->field('enterprise_name');
	}
}