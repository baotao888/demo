<?php
namespace app\user\model;

use think\Model;
use think\Config;
use think\Log;

class User extends Model
{
	protected $pk = 'uid';
	// 设置返回数据集的对象名
	protected $resultSetType = '';
	
	private $role_fields_key = 'user';//角色数据访问列权限键
	
	/*用户信息*/
	public function profile()
	{
		return $this->hasOne('app\user\model\UserData', 'uid', 'uid')->field('real_name, reg_time');
	}
	
	/**
	 * 重写父类方法
	 * 设置数据访问权限
	 */
	public function select()
	{
		/*添加列访问权限*/
		Config::load(APP_PATH.'admin/config_privileges.php');
		$except_fields = Config::get('role_except_fields');//不允许访问的列
		if (isset($except_fields[$this->role_fields_key])){
			$except = true;
			if (empty($this->getOptions('field'))) {
				$access_fields = Config::get('role_access_fields');
				if (isset($access_fields[$this->role_fields_key])) {
					$field = $access_fields[$this->role_fields_key];//可访问的列
					$except = false;
				} else {
					$field = $except_fields[$this->role_fields_key];//不可访问的列
				}
			} else {
				$field = $except_fields[$this->role_fields_key];//不可访问的列
			}
			$this->field($field, $except);
		}
		return parent::select();
	}
	
	/**
	 * 状态获取器
	 * @param int $value
	 * @return Ambigous <string>
	 */
	public function getFromAttr($value)
	{
		$status = ['Web'=>'PC端', 'wechat'=>'微信', 'Wechat'=>'微信'];
		return isset($status[$value])?$status[$value]:$value;
	}
}