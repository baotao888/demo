<?php
namespace app\job\model;

use think\Model;

class Job extends Model
{
	public function detail()
	{
		return $this->hasOne('app\job\model\JobData', 'job_id', 'id')->field('job_id, pictures, content, salary_detail, view_time, address_short, address_mark');
	}
	
	public function enterprise()
	{
		return $this->hasOne('app\job\model\Enterprise', 'id', 'enterprise_id')->field('id, enterprise_name');
	}
	
	/**
	 * 更新字段过滤
	 */
	public function updateFilter($data, $id)
	{
		$return = [];
		$fields = $this->get($id);
		foreach ($data as $field=>$value) {
			if ($value != $fields[$field]) $return[$field] = $value;
		}
		return $return;
	}
}