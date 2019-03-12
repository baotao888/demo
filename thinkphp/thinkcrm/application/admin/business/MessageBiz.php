<?php
namespace app\admin\business;

use ylcore\Biz;
use think\Config;
use think\Log;

class MessageBiz extends Biz
{
	const ALL = 'all';//全部消息
	const COMMON_TYPE = 'default';//未读消息
	const SYSTEM = 'sys';//系统消息
	const SYS_NAME = 'SYSTEM';//系统消息发送者
	
	/**
	 * 获取员工所有未读消息
	 */
	public function getUnreadMessage($employee_id, $size, $page=1, $set_read=false){
		$return = [];
		$model = model('admin/message');
		$return = $model->where('receiver',  '=', $employee_id)
						->where('is_read', '=', '0')
						->page($page, $size)
						->order('`create_time` desc')
						->select();
		/*标记消息为已读*/
		if ($set_read && $return){
			$arr_id = [];
			foreach ($return as $item){
				$arr_id[] = $item['id'];
			}
			$str_id = implode(',', $arr_id);
			$model->save(['is_read'=>1], "`id` IN ($str_id)");
		}
		return $return;
	}
	
	/**
	 * 获取员工所有未读消息总数
	 */
	public function getUnreadMessageCount($employee_id){
		$return = [];
		$model = model('admin/message');
		$return = $model->where('receiver',  '=', $employee_id)
		->where('is_read', '=', '0')
		->count();
		return $return;
	}
	
	/**
	 * 新增消息
	 */
	public function save($data){
		$model = model('admin/message');
		$model->data([
			'receiver'  =>  $data['receiver'],
			'create_time' =>  time(),
			'content' => $data['content'],
			'sender' => $data['sender'],
			'sender_name' => $data['sender_name'],
			'type' => $data['type'],
			'is_read' => 0				
		]);
		$model->isUpdate(false)->save();//保存
		return $model->id;
	}
	
	/**
	 * 发送系统消息
	 */
	public function sendSystemMessage($receiver, $content){
		return $this->save([
			'receiver' => $receiver,
			'content' => $content,
			'type' => self::SYSTEM,
			'sender' => 0,
			'sender_name' => self::SYS_NAME			
		]);
	}
	
	/**
	 * 发送普通消息
	 */
	public function sendCommonMessage($receiver, $content, $sender, $sender_name){
		return $this->save([
				'receiver' => $receiver,
				'content' => $content,
				'type' => self::COMMON_TYPE,
				'sender' => $sender,
				'sender_name' => $sender_name
		]);
	}
	
	/**
	 * 发送系统消息
	 */
	public function massMessage($content){
		$count = 0;
		$biz = controller('admin/EmployeeBiz', 'business');
		$list = $biz->getCache();
		foreach($list as $employee){
			$this->save([
					'receiver' => $employee['id'],
					'content' => $content,
					'type' => self::SYSTEM,
					'sender' => 0,
					'sender_name' => self::SYS_NAME
			]);
			$count ++;
		}
		return $count;
	}
}