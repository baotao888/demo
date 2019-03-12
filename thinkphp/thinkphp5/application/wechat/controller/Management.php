<?php
//[微信控制面板]
namespace app\wechat\controller;

use tcent\Wechat;
use think\Config;
use think\Log;

class Management
{
	/**
	 * 创建自定义菜单
	 */
	public function menu(){
		$this->service = controller('WechatService', 'service');
		//设置菜单
		$newmenu =  array(
			"button"=>
				array(
					array('type' => 'click', 'name' => lang('wechat_menu_about_us'), 'key' => Config::get('wechat_key_about_us')),
					//array('type' => 'view', 'name' => '今日招聘', 'url' => $this->url_index()),
					array('type' => 'click', 'name' => lang('wechat_menu_today_job'), 'key' => Config::get('wechat_key_index')),
					//array('type' => 'view', 'name' => '免费注册', 'url' => $this->url_bind())
					array('type' => 'click', 'name' => lang('wechat_menu_register'), 'key' => Config::get('wechat_key_bind'))
				)
			);
		//Log::record(json_encode($newmenu));
		$result = $this->service->createMenu($newmenu);
		return $result?'success':$this->service->errMsg;
	}
	
	/**
	 * 微信首页
	 * @return string
	 */
	private function url_index(){
		return  Config::get('site_domain') . '/wechat/web';
	}
	
	/**
	 * 微信绑定
	 */
	private function url_bind(){
		return Config::get('site_domain') . '/wechat/user/bind';
	}
}