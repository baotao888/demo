<?php
namespace app\customer\model;

use think\Model;

class CustomerWorkHistory extends Model
{
	public function job(){
		return $this->hasOne('app\job\model\Job','id','job_id')->field('job_name');
	}
}