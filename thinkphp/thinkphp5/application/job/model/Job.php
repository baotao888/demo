<?php
namespace app\job\model;

use think\Model;

class Job extends Model
{
	protected $pk = 'id';

	// 联查面试时间
	public function subData()
	{
		return $this->hasOne('app\job\model\JobData', 'job_id', 'id')->field('job_id, view_time');
	}

	// 联查投递人数
	public function getDeliveries()
	{
		return $this->hasOne('app\job\model\JobStatistics', 'job_id', 'id')->field('deliveries, hits');
	}

	// 联查详情数据
	public function detail()
	{
		return $this->hasOne('app\job\model\JobData', 'job_id', 'id')->field('job_id, view_time, salary_detail, address_short, content, pictures');
	}

	// 联查公司介绍
	public function company()
	{
		return $this->hasOne('app\job\model\Enterprise', 'id', 'enterprise_id')->field('description, nature, scale, industry');
	}
}