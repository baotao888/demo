<?php
//[微信消息入口]
namespace app\wechat\controller;

use tcent\Wechat;
use think\Config;
use think\Log;
use ylcore\WechatXml;
use think\Response;

class Index
{
	const MESSAGE_SUCCESS = 'success';
	private $service;
	
	public function __construct()
	{
	}
	
	/**
	 * 微信入口
	 * @return string
	 */
	public function index()
	{
		$message = self::MESSAGE_SUCCESS;
		try{
			$this->service = controller('WechatService', 'service');
			$this->service->debug = true;
			$type = $this->service->getRev()->getRevType();
			switch($type) {
				case Wechat::MSGTYPE_TEXT:
					$this->text($this->service->getRevContent());
					break;
				case Wechat::MSGTYPE_EVENT:
					$arr_event = $this->service->getRevEvent();
					if (isset($arr_event['event'])) $this->event($arr_event['event']);
					if (isset($arr_event['key'])) $this->eventKey($arr_event['key']);
					break;
				case Wechat::MSGTYPE_IMAGE:
					break;
				default:
					$this->service->text($this->defaultHelp());
			}
			if (is_array($this->service->Message('success'))){
				return Response::create($this->service->reply('', false), '', 200);
			}
		}catch(\Exception $e){
			//halt($e);//[debug]
		    return self::MESSAGE_SUCCESS;
		}
		return $message;
	}
	
	/**
	 * 处理文本消息
	 * @access private
     * @param string  $message 文本内容
     * @return void
	 */
	private function text($message)
	{
		$matches = [];//匹配消息
		$message = trim($message);//消息格式化
		/*注册绑定：姓名+手机号码*/
		if (is_mobile(substr($message, -11))) {
			if (preg_match("/^([\x{4e00}-\x{9fa5}]+)\s?\+?\s?(1[3|5|8][0-9]{9})$/u", $message, $matches)) {	
				$real_name = $matches[1];
				$mobile = $matches[2];
				$business = controller('Subscriber', 'business');//业务对象
				$open_id = $this->service->getRevFrom();//消息发送者
				if ($business->isSubscriber($open_id)){
					/*$user = $business->doMobileBind($open_id, $mobile, $real_name);
					if ( $user['uid'] > 0 ){
						$text = lang('register_success', [$user['uname']]);
					}else{
						$text = lang('register_failure');//[debug]
					}*/	
					$text = lang('register_award');
					$this->service->text($text);
				} else {
					$this->service->text($this->replyAttention());//未关注
				}
			}
		}
	}
	
	/**
	 * 处理事件
	 * @access private
	 * @param string  $event 事件类型
	 * @return void
	 */
	private function event($event)
	{
		$return = '';
		switch($event){
			//订阅
			case Wechat::EVENT_SUBSCRIBE:
				$open_id = $this->service->getRevFrom();//消息发送者
				$info = $this->service->getUserInfo($open_id);//订阅者信息
				//$info = array('openid'=>$open_id, 'nickname'=>'张三丰');//[test]
				$business = controller('Subscriber', 'business');
				$business->doSubscribe($info);//订阅
				$return = $this->service->text($this->subscribeInfo());
				break;
			//取消订阅
			case Wechat::EVENT_UNSUBSCRIBE:
				$open_id = $this->service->getRevFrom();//消息发送者
				$business = controller('Subscriber', 'business');
				$business->cancelSubscribe($open_id);//取消订阅
				break;	
		}
		return $return;
	}
	
	/**
	 * 处理菜单事件
	 */
	private function eventKey($key)
	{
		switch($key){
			//关于我们
			case Config::get('wechat_key_about_us') :
				$article = array(
					array(
						'Title'=>lang('wechat_menu_about_us'),
						'Description'=>lang('about_us_desc'),
						'PicUrl'=> 'http:' . Config('cdn_domain') . '/wechat/about_us.jpg',
						'Url'=>'https://mp.weixin.qq.com/s/PA5hVYuK6JrZ1BR2gzef_w'
					)
				);
				$this->service->news($article); 
				break;
			//首页	
			case Config::get('wechat_key_index') :
				$openid = $this->service->getRevFrom();//消息发送者
				$openid = encrypt_param($openid);//加密
				$article = array(
					array(
						'Title'=>lang('wechat_menu_today_job'),
						'Description'=>lang('index_desc'),
						'PicUrl'=> 'http:' . Config('cdn_domain') . '/wechat/index.jpg',
						'Url'=>Config::get('site_domain') . '/wechat/web?openid='. $openid
					)
				);
				$this->service->news($article);
				break;
			//免费注册
			case Config::get('wechat_key_bind') :
				$openid = $this->service->getRevFrom();//消息发送者
				$openid = encrypt_param($openid);//加密
				$article = array(
					array(
						'Title'=>lang('wechat_menu_register'),
						'Description'=>lang('register_award'),
						'PicUrl'=> 'http:' . Config('cdn_domain') . '/wechat/bind.jpg',
						'Url'=>Config::get('site_domain') . '/wechat/user/bind?openid=' . $openid
					)
				);
				$this->service->news($article);
				break;
			default : ;	
		}
	}
	
	/**
	 * 回复关注图片消息
	 */
	private function replyAttention(){
		return lang('not_attention');
	}
	
	/**
	 * 帮助信息
	 */
	private function defaultHelp(){
		return lang('help_info');
	}
	
	/**
	 * 关注信息
	 */
	private function subscribeInfo(){
		return lang('subscribe_info');
	}
}