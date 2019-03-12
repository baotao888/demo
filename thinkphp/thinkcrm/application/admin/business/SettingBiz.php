<?php
namespace app\admin\business;

use ylcore\Biz;
use think\Config;
use think\Log;
use app\admin\model\EmployeeFavorite;

class SettingBiz extends Biz
{	
	/**
	 * 获取用户的系统设置
	 * @param integer $admin_id
	 */
	public function getSetting($admin_id){
		$model = model('AdminSetting');
		$setting = $model->get($admin_id);
		if ($setting['system']) $setting['system'] = unserialize($setting['system']);
		if ($setting['personal']) $setting['personal'] = unserialize($setting['personal']);
		return $setting;
	}
	
	/**
	 * 更新用户设置
	 */
	public function update($id, $data){
		$model = model('AdminSetting');
		$update = [];
		if (isset($data['system']) && $data['system']){
			$update['system'] = $data['system'];
		}
		if (isset($data['personal'])){
			$update['personal'] = $data['personal'];
		}
		return $model->save($update, ['id' => $id]);//更新
	}
	
	/**
	 * 更新用户系统设置
	 */
	public function updateSystem($id, $setting) {
		$data = ['system' => serialize($setting)];
		return $this->update($id, $data);
	}
	
	/**
	 * 更新员工数据
	 */
	public function updateEmployeeStatstics($id, $data){
		$model = model('EmployeeStatistics');
		$update = [];
		if (isset($data['candidates'])){
			$update['candidates'] = $data['candidates'];
		}
		if (isset($data['remains'])){
			$update['remains'] = $data['remains'];
		}
		if (isset($data['remain_days'])){
			$update['remain_days'] = $data['remain_days'];
		}
		if (isset($data['recognize_prior_time'])){
			$update['recognize_prior_time'] = $data['recognize_prior_time'];
		}
		if (isset($data['recognize_priors'])){
			$update['recognize_priors'] = $data['recognize_priors'];
		}
		if ($update) $model->update($update, ['id' => $id]);//更新
		return true;
	}
	
	/**
	 * 获取员工的数据
	 * @param integer $id
	 */
	public function getEmployeeStatistics($id){
		$model = model('EmployeeStatistics');
		$statistics = $model->get($id);
		return $statistics;
	}
	
	/**
	 * 获取员工的收藏
	 * @param integer $id
	 */
	public function getEmployeeFavorite($id){
		$model = new EmployeeFavorite();
		$list = $model->where('employee_id', $id)->order('listorder')->select();
		return $list;
	}
	
	/**
	 * 添加收藏
	 * @param integer $id
	 * @param string $url
	 */
	public function addFavorite($id, $url, $title, $listorder = 0) {
		$model = new EmployeeFavorite();
		$model->data(['employee_id'=>$id, 'url'=>$url, 'listorder'=>$listorder, 'title'=>$title]);
		$model->save();
	}
	
	/**
	 * 删除收藏
	 */
	public function deleteFavorite($id, $url) {
		$model = new EmployeeFavorite();
		return $model->where('employee_id', $id)->where('url', $url)->delete();
	}
}