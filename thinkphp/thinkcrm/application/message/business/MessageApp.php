<?php
// [app客户端消息业务类]
namespace app\message\business;

use ylcore\Biz;

use app\message\model\AppMessage;
use app\message\model\AppSubscribeJob;

class MessageApp extends Biz
{
	const TYPE = 'subscrib';
	const MODEL = 'job';
	const SYSTEM = 'sys';
	
	private $model;
	
	function __construct() {
		$this->model = new AppMessage;
	}
	
	/**
	 * 新增消息
	 */
	public function save($data){
		$this->model->data([
				'base_time' =>  time(),
				'content' => $data['content'],
				'format' => $data['format'],
				'model' => $data['model'],
				'receiver'  =>  $data['receiver'],
				'relation' => $data['relation'],
				'sender' => 0,//系统发送
				's_name' => self::SYSTEM,
				'type' => $data['type']
		]);
		$this->model->isUpdate(false)->save();//保存
		return $this->model->id;
	}
	
	/**
	 * 发送职位订阅消息
	 */
	public function sendJobSubscribMessage($job_id, $content){
		$subscribe_model = new AppSubscribeJob();
		$receivers = $subscribe_model->where('job_id', $job_id)->column('uid');
		if ($receivers) {
			foreach ($receivers as $uid) {
				$this->save([
					'content' => $content,
					'format' => 1,
					'model' => self::MODEL,
					'receiver' => $uid,
					'relation' => $job_id,
					'type' => self::TYPE
				]);
			}
		}
	}
}