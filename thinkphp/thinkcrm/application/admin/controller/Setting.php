<?php
namespace app\admin\controller;

use think\Config;

class Setting
{
	/**
	 * 权限
	 */
	public function privileges(){
		Config::load(APP_PATH.'admin/config_privileges.php');
		return Config::get('role_privileges');
	}
	
	/**
	 * 个性化
	 */
	public function personal(){
		Config::load(APP_PATH.'admin/config_personal.php');	
		return Config::get('user_setting');
	}
	
	/**
	 * 获取用户设置信息
	 */
	public function admin(){
		$admin_id = request()->param('id');
		$biz = controller('SettingBiz', 'business');
		$setting = $biz->getSetting($admin_id);
		return $setting;
	}
	
	/**
	 * 更新用户系统设置
	 */
	public function updateSystem(){
		//参数验证
		$param = request()->param();
		if (! isset($param['id']) || ! isset($param['system'])) {
			abort(400, '400 Invalid id/system supplied');
		}
		$id = $param['id'];
		$biz = controller('SettingBiz', 'business');
		$flag = $biz->updateSystem($id, $param['system']);
		if (! $flag) {
			$id = 0;
		}
		return ['id' => $id];
	}
	
	public function updatestatistics(){
		//参数验证
		$param = request()->param();
		if (! isset($param['employee']) || $param['employee']<=0) {
			abort(400, '400 Invalid id supplied');
		}
		$id = $param['employee'];
		$data = [];
		if(isset($param['candidates'])) $data['candidates'] = $param['candidates'];
		if(isset($param['remains'])) $data['remains'] = $param['remains'];
		if(isset($param['remain_days'])) $data['remain_days'] = $param['remain_days'];
		if(isset($param['recognize_prior_time'])) $data['recognize_prior_time'] = $param['recognize_prior_time'];
		if(isset($param['recognize_priors'])) $data['recognize_priors'] = $param['recognize_priors'];
		$biz = controller('SettingBiz', 'business');
		$flag = $biz->updateEmployeeStatstics($id, $data);
		return $flag;
	}
	
	public function statistics(){
		//参数验证
		$param = request()->param();
		if (! isset($param['id']) || $param['id']<=0) {
			abort(400, '400 Invalid id supplied');
		}
		$id = $param['id'];
		$biz = controller('SettingBiz', 'business');
		$statistics = $biz->getEmployeeStatistics($id);
		return $statistics;
	}
}