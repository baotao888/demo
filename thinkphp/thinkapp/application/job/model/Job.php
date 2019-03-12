<?php
namespace app\job\model;

use think\Model;

class Job extends Model
{
	
	/**
	 * 职位数据
	 */ 
	public function statistics()
	{
		return $this->hasOne('app\job\model\JobStatistics', 'job_id', 'id')->field('deliveries, hits');
	}
	
	/**
	 * 职位详情
	 */ 
	public function detail()
	{
		return $this->hasOne('app\job\model\JobData', 'job_id', 'id')->field('job_id, view_time, salary_detail, address_short, content, pictures');
	}
	
	/**
	 * 企业信息
	 */ 
	public function enterprise()
	{
		return $this->hasOne('app\job\model\Enterprise', 'id', 'enterprise_id')->field('description, nature, scale, industry, tag');
	}
}