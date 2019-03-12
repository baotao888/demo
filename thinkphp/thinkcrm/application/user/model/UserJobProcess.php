<?php
namespace app\user\model;

use think\Model;

class UserJobProcess extends Model
{
	/*用户信息*/
	public function user()
	{
		return $this->hasOne('app\user\model\UserData', 'uid', 'user_id')->field('real_name');
	}
	
	/*职位信息*/
	public function job()
	{
		return $this->hasOne('app\job\model\Job', 'id', 'job_id')->field('job_name');
	}
	
	/*用户基本信息*/
	public function userbase()
	{
		return $this->hasOne('app\user\model\User', 'uid', 'user_id')->field('mobile');
	}
}