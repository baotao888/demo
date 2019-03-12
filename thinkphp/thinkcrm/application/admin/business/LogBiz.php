<?php
//[后台日志业务类]
namespace app\admin\business;

use ylcore\Biz;
use app\admin\model\Admin;
use think\Config;
use think\Log;

class LogBiz extends Biz
{
	/**
	 * 写入日志
	 */
	public function write($admin_id, $data = []){
		$model = model('admin/AdminLog');
		$model->data([
				'admin_id'  =>  $admin_id,
				'module' =>  $data['module'],
				'controller' =>  $data['controller'],
				'action' =>  $data['action'],
				'log_time' =>  time(),
				'record_id' => isset($data['record_id'])?$data['record_id']:''
		]);
		$model->save();//保存
		
		return $model->id;
	}
	
	/**
	 * 清理一周前的日志
	 */
	public function clear() {
		$two_days_age = time() - 3600 * 24 * 2;
		$model = model('admin/AdminLog');
		return $model->where('log_time<=' . $two_days_age)->delete();
	}
}