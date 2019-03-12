<?php
// [ 微信业务类 ]

namespace app\user\business;

use ylcore\Biz;

class Wechat extends Biz
{
	/**
	 * 获取所有用户信息
	 */
	public function getAll($page = 1, $pagesize = 1000, $search = '')
	{
		$return = array();
		$model = model('subscriber');
		$list = $model->where('subscribe_time', '>', 0)->page($page, $pagesize)->order('subscribe_time desc')->select();
		if ($list){
			foreach ($list as $item){
				$item['subscribe_time'] = date('Y-m-d H:i:s', $item['subscribe_time']);
				$return[] = $item;
			}
		}
		return $return;
	}
	
	/**
	 * 获取微信用户总数
	 */
	public function getCount() 
	{
		$model = model('subscriber');
		return $model->where('subscribe_time', '>', 0)->count();
	}
	
	/**
	 * 获取所有微信日志
	 * @param number $page
	 * @param number $pagesize
	 * @return array
	 */
	public function getLogs($page = 1, $pagesize = 20) {
		$model = model('WechatSubscribeLog');
		$return = $model->where('action_time', '>', 0)->page($page, $pagesize)->order('action_time desc')->select();
		return $return;
	}
}