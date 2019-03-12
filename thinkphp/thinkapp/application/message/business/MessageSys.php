<?php
// [系统消息业务类]
namespace app\message\business;

use app\message\model\AppMessage;
use app\message\business\MessageBiz;

class MessageSys extends MessageBiz
{
	protected $type = 'sys';
	
	/**
	 * @override
	 * 获取用户消息
	 */
	public function getMy($uid, $condition, $page = 1, $pagesize = 10, $order = 'id', $by = 'desc') {
		$order_by = $this->selectOrderBy($order, 'id', $by);
		$model = new AppMessage();
		$list = $model->alias('msg')
			->join('Activity act', 'msg.relation=act.id', 'left')
			->where($this->myCondition($uid, $condition))
			->order($order_by)
			->page($page, $pagesize)
			->field('msg.id, msg.sender, msg.s_name, msg.content, msg.relation, msg.model, msg.base_time, msg.is_read, msg.format, act.title, act.cover AS thumb')
			->select();
		$return = $this->o2a($list);
		return $return;
	}
}