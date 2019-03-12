<?php

namespace app\admin\business;

use ylcore\Biz;
use app\admin\model\AdminRole;
use think\Config;
use think\Log;

class RoleBiz extends Biz
{
	
	/**
	 * 添加角色
	 */
	public function save($data){
		$model = model('AdminRole');
		$model->data([
				'role_name'  =>  $data['role_name'],
				'description' =>  $data['description']
		]);
		$model->save();//保存

		return $model->id;
	}
	
	/**
	 * 获取所有角色信息
	 */
	public function getAll()
	{	
		$return = array();
		$model = model('AdminRole');
		$list = $model->select();
		if ($list){
			foreach ($list as $item){
				$return[$item['id']] = $item;
			}
		}

		return $return;
	}
	
	
	public function get($id){
		$model = model('AdminRole');
		$obj = $model->get($id);
		if ($obj->privileges!='') $obj->privileges = unserialize($obj->privileges);
		return $obj;
	}
	
	/**
	 * 更新指定的角色信息
	 */
	public function update($id, $data){
		$model = model('AdminRole');
		$update = [];
		if (isset($data['role_name']) && $data['role_name']){
			$update['role_name'] = $data['role_name'];
		}
		if (isset($data['description'])){
			$update['description'] = $data['description'];
		}
		if (isset($data['privileges'])){
			$update['privileges'] = serialize($data['privileges']);
		}
		$model->save($update, ['id' => $id]);//更新
	}
}