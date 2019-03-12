<?php
namespace app\wechat\service;

use tcent\Wechat;
use think\Cache;
use think\Log;
use think\Config;

class WechatService extends Wechat
{
	public function __construct($options=array())
	{
		if (! $options){
			$options = array(
				'appid' => Config::get('wechat_app_id'), //填写高级调用功能的app id
				'appsecret' => Config::get('wechat_app_secret'), //填写高级调用功能的密钥
				'token' => Config::get('wechat_app_token'), //填写高级调用功能的密钥
				'encodingaeskey' => Config::get('encodingaeskey'), //填写高级调用功能的密钥
			);
		}
		parent::__construct($options);
	}
	
	public function setOptions($options){
		$this->token = isset($options['token'])?$options['token']:'';
		$this->encodingAesKey = isset($options['encodingaeskey'])?$options['encodingaeskey']:'';
		$this->appid = isset($options['appid'])?$options['appid']:'';
		$this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
		$this->debug = isset($options['debug'])?$options['debug']:false;
		$this->logcallback = isset($options['logcallback'])?$options['logcallback']:false;
	}
	
	/**
	 * log overwrite
	 * @see Wechat::log()
	 */
	protected function log($log){
		if ($this->debug) {
			Log::record('wechat：'.$log, Log::DEBUG);
			return true;
		}
		return false;
	}
	
	/**
	 * 重载设置缓存
	 * @param string $cachename
	 * @param mixed $value
	 * @param int $expired
	 * @return boolean
	 */
	protected function setCache($cachename,$value,$expired=3600){
		return Cache::set($cachename, $value, $expired);
	}
	
	/**
	 * 重载获取缓存
	 * @param string $cachename
	 * @return mixed
	 */
	protected function getCache($cachename){
		return Cache::get($cachename);
	}
	
	/**
	 * 重载清除缓存
	 * @param string $cachename
	 * @return boolean
	 */
	protected function removeCache($cachename){
		return Cache::rm($cachename);
	}
}