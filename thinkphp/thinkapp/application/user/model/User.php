<?php
namespace app\user\model;

use think\Model;

class User extends Model
{
	protected $pk = 'uid';
	
	public function profile()
	{
		return $this->hasOne('app\user\model\UserData', 'uid', 'uid')->field('real_name');
	}
}