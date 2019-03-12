<?php
namespace app\message\business;

use think\Config;
use think\Log;

use ylcore\Biz;
use app\message\model\AppMessage;
use app\user\business\UserBiz;

class MessageBiz extends Biz
{
	protected $domainObjectFields = [
		'avatar',
		'base_time',
		'content',
		'id',
		'model',
		'relation',
		'sender',
		's_name',
		'thumb',
		'title',
		'format',
		'is_read'	
	];
	protected $orders = ['id', 'base_time', 'model', 'format', 'is_read'];
	protected $type;
	const FORMAT_TEXT = 0;//文本
	const FORMAT_PIC = 1;//图片

	/**
	 * 获取用户消息
	 * @param int $uid
	 * @param array $condition
	 */
	public function getMy($uid, $condition, $page = 1, $pagesize = 10, $order = 'id', $by = 'desc') {
		$order_by = $this->selectOrderBy($order, 'id', $by);
		$model = new AppMessage();
		$list = $model->where($this->myCondition($uid, $condition))
			->order($order_by)
			->page($page, $pagesize)
			->select();
		$return = $this->o2a($list);
		return $return;
	}
	
	/**
	 * 搜索条件
	 */
	protected function myCondition($uid, $condition) {
		$where = '`receiver` = ' . $uid;
		$where .= ' AND `type`="' . $this->type . '"';
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
	
	/**
	 * 是否为我的消息
	 */
	public function isMy($msg_id, $user_id) {
		$model = new AppMessage();
		$receiver = $model->where('id', $msg_id)->value('receiver');
		return $user_id == $receiver;
	}
	
	/**
	 * 标记为已读
	 */
	public function setRead($id) {
		$model = new AppMessage();
		return $model->where('id', $id)->update(['is_read'=>1]);
	}
	
	/**
	 * 删除消息
	 */
	public function delete($id) {
		$model = new AppMessage();
		return $model->where('id', $id)->delete();
	}
	
	/**
	 * 发送消息
	 * @param int $sender
	 * @param int $receiver
	 * @param string $content
	 * @param int $reply_id
	 */
	public function send($sender, $receiver, $content, $reply_id = null, $format = '') {
		/*获取发送人昵称*/
		$biz = new UserBiz();
		$sender_name = $biz->getNickname($sender);
		if (! $format) $format = self::FORMAT_TEXT;//文本
		$model = new AppMessage();
		$msg_id = $model->insert([
			'receiver' => $receiver,
			'content' => $content,
			'sender' => $sender,
			's_name' => $sender_name,
			'base_time' => time(),
			'type' => $this->type,
			'format' => $format,
			'reply_id' => $reply_id	
		]);
		return $msg_id;
	}
	
	/**
	 * @override
	 * 格式化视图字段
	 * @param array $item
	 */
	public function formatViewField($item) {
		if ($item['base_time'] != '') $item['base_time'] = date('Y-m-d H:i:s', $item['base_time']);
		return $item;
	}
	
	/**
	 * 获取消息详情
	 * @param int $uid 用户编号
	 * @param int $id 消息编号
	 * @param array $config
	 */
	public function detail($uid, $id, $config = []) {
		$where = "`sender`=$uid AND `id`=$id";
		$model = new AppMessage();
		$object = $model->where($where)->find();
		$return = $object->toArray($object);
		return $return;
	}
}