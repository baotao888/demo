<?php
namespace app\wechat\model;

use think\Model;

class Subscriber extends Model
{
	protected $table = 'yl_user_bind_wechat_subscribe';
	protected $pk = 'open_id';
	
	public function user()
	{
		return $this->hasOne('app\user\model\User', 'uid', 'user_id')->field('uid,uname,email,mobile');
	}
}