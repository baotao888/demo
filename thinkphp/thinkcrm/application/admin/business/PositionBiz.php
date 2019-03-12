<?php

namespace app\admin\business;

use ylcore\Biz;
use app\admin\model\Position;
use think\Config;
use think\Log;

class PositionBiz extends Biz
{
	
	/**
	 * 添加职位
	 */
	public function save($data){
		$model = model('Position');
		$model->data([
				'pos_name'  =>  $data['pos_name'],
				'description' =>  $data['description'],
				'is_manager' => $data['is_manager']?1:0,
				'is_adviser' => $data['is_manager']?1:0,
				'level' => $data['level']
		]);
		$model->save();//保存
		return $model->id;
	}
	
	/**
	 * 获取所有职位信息
	 */
	public function getAll()
	{
		$return = array();
		$model = model('Position');
		$list = $model->select();
		if ($list){
			foreach ($list as $item){
				$return[$item['id']] = $item;
			}
		}

		return $return;
	}
	
	
	public function get($id){
		$model = model('Position');
		$obj = $model->get($id);
		return $obj;
	}
	
	
	public function update($id, $data){
		$model = model('Position');
		$update = [];
		if (isset($data['pos_name']) && $data['pos_name']){
			$update['pos_name'] = $data['pos_name'];
		}
		if (isset($data['description'])){
			$update['description'] = $data['description'];
		}
		if (isset($data['is_manager'])){
			$update['is_manager'] = $data['is_manager']?1:0;
		}
		if (isset($data['is_adviser'])){
			$update['is_adviser'] = $data['is_adviser']?1:0;
		}
		if (isset($data['level'])){
			$update['level'] = $data['level'];
		}
		$model->save($update, ['id' => $id]);//更新
	}
}