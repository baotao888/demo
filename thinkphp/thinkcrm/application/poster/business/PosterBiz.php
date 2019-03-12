<?php

namespace app\poster\business;

use ylcore\Biz;
use think\Config;
use think\Log;

class PosterBiz extends Biz
{
	
	/**
	 * 添加广告
	 */
	public function save($data){
		$model = model('Poster');
		$model->data([
				'title'  =>  $data['title'],
				'listorder' =>  $data['listorder'],
				'content' => $data['content'],
				'space_id' => $data['space_id']?$data['space_id']:1
		]);
		$model->save();//保存
		return $model->id;
	}
	
	/**
	 * 获取所有广告信息
	 */
	public function getAll($space = 1)
	{
		$return = array();
		$model = model('Poster');
		$list = $model->where('space_id', $space)->select();
		if ($list){
			foreach ($list as $item){
				$return[$item['id']] = $item;
			}
		}

		return $return;
	}
	
	
	public function get($id){
		$model = model('Poster');
		$obj = $model->get($id);
		return $obj;
	}
	
	
	public function update($id, $data){
		$model = model('Poster');
		$update = [];
		if (isset($data['title']) && $data['title']){
			$update['title'] = $data['title'];
		}
		if (isset($data['listorder'])){
			$update['listorder'] = $data['listorder'];
		}
		if (isset($data['disabled'])){
			$update['disabled'] = $data['disabled']?1:0;
		}
		if (isset($data['content']) && $data['content']){
			$update['content'] = $data['content'];
		}
		$model->save($update, ['id' => $id]);//更新
	}
}