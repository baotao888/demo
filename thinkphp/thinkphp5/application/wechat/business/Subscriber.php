<?php
// [ 订阅者业务类 ]

namespace app\wechat\business;

use ylcore\Biz;
use think\Log;

class Subscriber extends Biz
{
	// 数据模型
	private $model = null;
	
	const WECHAT_CLIENT = 'Wechat';
	
	public function __construct(){
		$this->model = model('subscriber');
	}
	
	/**
	 * 微信订阅
	 * @access public
	 * @param array{subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]} $user 用户信息
	 * @return void
	 */
	public function doSubscribe($user){
		if ($this->isSubscriber($user['openid'])){
			$this->reSubscribe($user['openid'], $user['nickname']);
		} else {
			$this->addSubscribe($user);
		}
		$this->createLog($user['openid'], 'subscribe');
	}
	
	/**
	 * 是否已经订阅
	 */
	public function isSubscriber($open_id){
		$flag = false;//默认未订阅
		$subscriber = $this->model->get($open_id);
		if ($subscriber) $flag = true;
		return $flag;
	}
	
	/**
	 * 记录日志
	 * @access public
	 * @param $open_id string
	 * @param $action string [message=>发送消息, subscribe=>关注, unsubscribe=>取消关注]
	 */
	public function createLog($open_id, $action, $content=''){
		$log_model = model('log');
		$log_model->open_id = $open_id;
		$log_model->action_type = $action;
		$log_model->action_time = time();
		$log_model->content = $content;
		$log_model->save();
	}
	
	/**
	 * 首次订阅
	 */
	public function addSubscribe($user){
		$this->model->open_id = $user['openid'];
		if(isset($user['unionid'])) $this->model->union_id = $user['unionid'];
		$this->model->is_subscribe = 1;
		$this->model->subscribe_time = time();
		$this->model->nick_name = $user['nickname'];
		$this->model->save();
		$this->initStatistics($user['openid']);
	}
	
	/**
	 * 重新订阅
	 */
	public function reSubscribe($open_id, $nickname=''){
		$this->model->save([
		    'is_subscribe'  => 1,
			'nick_name' => $nickname	
		],['open_id' => $open_id]);
	}
	
	/**
	 * 初始化统计信息
	 * @param string $open_id
	 * @return void
	 */
	public function initStatistics($open_id){
		$statistics_model = model('statistics');
		$statistics_model->open_id = $open_id;
		$statistics_model->activity = 1;
		$statistics_model->save();
	}
	
	/**
	 * 微信手机绑定
	 * @access public
	 * @param string $open_id
	 * @param string $mobile
	 * @param string $realname
	 */
	public function doMobileBind($open_id, $mobile, $realname){
		if ($this->isBindUser($open_id)==false) {
			/*未绑定*/
			$user_business = controller('user/User', 'business');//用户业务对象
			$password = $this->generatePassword();
			$user = $user_business->mobileRegister($mobile, $password, array('realname' => $realname, 'from' => self::WECHAT_CLIENT));//注册用户
			if ( $user['uid'] > 0 ){
				$this->bindUser($open_id, $user['uid']);
			}
		} else {
			/*已绑定*/
			$user = $this->getBindUser($open_id);
			$user['uid'] = $user->uid;
			$user['uname'] = $user->uname;
		}
		return $user;
	}
	
	/**
	 * 绑定用户
	 * @access public
	 * @param string $open_id 微信用户
	 * @param integer $uid 永乐打工网用户
	 * @return boolean
	 */
	public function bindUser($open_id, $uid){
		$this->model->save([
			'user_id'  => $uid
		],['open_id' => $open_id]);
	}
	
	/**
	 * 是否已绑定用户
	 * @access public
	 * @param string $open_id 微信用户
	 * @return boolean
	 */
	public function isBindUser($open_id){
		$flag = false;//默认未订阅
		$subscriber = $this->model->get($open_id);
		if ($subscriber && $subscriber->user_id) $flag = true;
		return $flag;
	}
	
	/**
	 * 生成随机密码
	 * @access private
	 * @param integer $length 密码长度
	 * @return string
	 */
	private function generatePassword($length = 8){
		return rand_string($length);
	}
	
	/**
	 * 获取绑定用户的信息
	 */
	public function getBindUser($open_id){
		$subscriber = $this->model->get($open_id);
		$user = $subscriber->user;
		if ($user['uid'] > 0) {
		  //获取用户真实姓名
		  $biz = controller('user/user', 'business');
		  $info = $biz->getInfo($user['uid']);
		  $user['real_name'] = $info->profile->real_name;
		}
		return $user;
	}
	
	/**
	 * 取消订阅
	 * @param string $open_id
	 */
	public function cancelSubscribe($open_id){
		$this->model->save([
			'user_id'  => ''
		],['open_id' => $open_id]);
	}
}