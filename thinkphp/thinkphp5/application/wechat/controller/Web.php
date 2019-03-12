<?php
// [ 微信应用入口 ]
namespace app\wechat\controller;

use think\Cookie;
use think\Request;

use ylcore\WebView;

class Web extends Client
{
	public function index()
	{
		$img = model('poster/Poster','business');
		$imgurl = $img->getEnabledPoster();
		$job = controller('job/Job','business');
        $signCount = $job->getTodaySignCount();
		$com = $job->getInfo();
		$view = new WebView();
		$view->setSkin('wechat');
		$view->assignBaseTpl();
		$view->assign('com',$com);
        $view->assign('signcount',$signCount);
        $view->assignTpl('list_item', 'job/list-item');
		$view->assign('imgurl',$imgurl);
		$view->assign('is_weixin', is_weixin());
		if (is_weixin()) {
			$service = controller('WechatService', 'service');
			$view->assign('wechat_sign', $service->getJsSign(cur_page_url()));
		}
		$this->auth();//保存openid参数到session中
		$this->saveMarketCode();//市场推广入口保存市场推广码
		return $view->fetch('index');
	}
	
	/**
	 * 微信权限验证
	 */
	public function auth()
	{
		$auth = $this->getAuth();
		if (! $auth){
			/*每次请求权限无效重新获取权限*/
			$user = '';
			$this->getOpenId();//获取并保存参数
			$open_id = $this->decodeOpenId();//参数解密
			$business = controller('Subscriber', 'business');//业务对象
			if ($open_id && $business->isBindUser($open_id)) {
				//已绑定用户
				/*绑定用户信息*/
				$business = controller('subscriber', 'business');
				$user = $business->getBindUser($open_id);
			}
			$this->setAuth($user);
			$auth = $user;
		}
		return $auth;
	}
	
	/**
	 * 获取市场推广码
	 */
	public function market() {
		return $this->saveMarketCode();
	}
}