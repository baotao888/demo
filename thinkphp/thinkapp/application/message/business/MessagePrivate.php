<?php
// [私聊消息业务类]
namespace app\message\business;

use app\message\model\AppMessage;
use app\message\business\MessageBiz;
use app\user\business\UserBiz;

class MessagePrivate extends MessageBiz
{
	protected $type = 'private';
	
	/**
	 * @override
	 * 获取用户消息
	 */
	public function getMy($uid, $condition, $page = 1, $pagesize = 10, $order = 'id', $by = 'desc') {
		$order_by = $this->selectOrderBy($order, 'id', $by);
		$model = new AppMessage();
		$list = $model->where($this->myCondition($uid, $condition))
			->order($order_by)
			->page($page, $pagesize)
			->select();
		$return = $this->o2a($list);
		if ($return) {
			$ubiz = new UserBiz;
			foreach ($return as $key=>$value) {
				$return[$key]['avatar'] = $ubiz->getAvatar($value['sender']);
			}
		}
		return $return;
	}
	
	/**
	 * @override
	 * 获取好友私聊记录
	 */
	public function detail($uid, $friend, $config = []) {
		$where = "(`sender`=$uid AND `receiver`=$friend) OR (`sender`=$friend AND `receiver`=$uid)";
		$page = isset($config['page'])?$config['page']:1;
		$pagesize = isset($config['pagesize'])?$config['pagesize']:20;
		$model = new AppMessage();
		$list = $model->where($where)
		->where('type', $this->type)
		->order("id desc")
		->page($page, $pagesize)
		->select();
		$return = $this->o2a($list);
		return $return;
	}
}