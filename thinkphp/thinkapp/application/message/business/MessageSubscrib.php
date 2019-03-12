<?php
// [订阅消息业务类]
namespace app\message\business;

use app\message\model\AppMessage;
use app\message\business\MessageBiz;

class MessageSubscrib extends MessageBiz
{
	protected $type = 'subscrib';
	
	/**
	 * @override
	 * 获取用户消息
	 */
	public function getMy($uid, $condition, $page = 1, $pagesize = 10, $order = 'id', $by = 'desc') {
		$order_by = $this->selectOrderBy($order, 'id', $by);
		$model = new AppMessage();
		$list = $model->alias('msg')
			->join('Job job', 'msg.relation=job.id', 'left')
			->where($this->myCondition($uid, $condition))
			->order($order_by)
			->page($page, $pagesize)
			->field('msg.id, msg.sender, msg.s_name, msg.relation, msg.model, msg.base_time, msg.is_read, msg.format, msg.content, job.cover AS thumb')
			->select();
		$return = $this->o2a($list);
		return $return;
	}
	
	/**
	 * 搜索条件
	 */
	protected function myCondition($uid, $condition) {
		$where = '`receiver` = ' . $uid;
		$where .= ' AND `msg`.`type`="' . $this->type . '"';
		if ($condition) {
			//是否已读
			if (isset($condition['is_read']) && $condition['is_read'] !== false) {
				$where .= ' AND `is_read`=' . $condition['is_read'];
			}
			//消息模型
			if (isset($condition['model']) && $condition['model'] != '') {
				$where .= ' AND `model`="' . $condition['model'] . '"';
			}
			//发送人
			if (isset($condition['sender']) && $condition['sender'] !== false) {
				$where .= ' AND `sender`=' . $condition['sender'];
			}
			//回复
			if (isset($condition['reply_id']) && $condition['reply_id'] !== false) {
				$where .= ' AND `reply_id`=' . $condition['reply_id'];
			}
		}
		return $where;
	}
}