<?php
namespace app\admin\business;

use ylcore\Biz;
use think\Config;
use think\Log;

class AnnouncementBiz extends Biz
{	
	/**
	 * 获取所有公告信息
	 */
	public function getAll($pagesize = 5){
		$model = model('admin/Announcement');
		return $model->page(1, $pagesize)->order('create_time desc')->select();
	}
	
	/**
	 * 发布公告
	 */
	public function send($avatar, $nickname, $content) {
		$announcement_model = model('admin/Announcement');
		$announcement_model->create([
			'avatar' 		=> $avatar,
			'sender' 		=> $nickname,
			'content' 		=> $content,
			'create_time' 	=> time()
		]);
	}
}