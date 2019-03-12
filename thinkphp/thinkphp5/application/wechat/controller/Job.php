<?php
//[微信应用入口]
namespace app\wechat\controller;

use ylcore\WebView;
use think\Request;
use think\Cookie;

class Job extends Client
{

	// 主页
	public function index(){
		$view = new WebView();
		$view->setSkin('wechat');
		$view->assignBaseTpl();
		return $view->fetch('job/list');
	}
	
	// 详情页面
	public function detail($id){
		$User = controller('job/Job','business');
		$com = $User->getDetails($id);
		$job = current($com);
		$view = new WebView();
		$view->setSkin('wechat');
		$view->assignBaseTpl();
		$view->assign('com', $job);
		$view->assignTpl('thumbs', 'job/thumbs');//组图
		$view->assignTpl('signupitem', 'job/signupitme1');//职位报名信息
		$view->assign('is_weixin', is_weixin());
		if (is_weixin()) {
			$service = controller('WechatService', 'service');
			$view->assign('wechat_sign', $service->getJsSign(cur_page_url()));
			$view->assign('share_link', cur_page_url());
			$view->assign('share_title', $job['job_name']);
		}
		return $view->fetch('job/detail');
	}

	// 搜索页面
	public function search(){
		$param = request()->param();
        $User = controller('job/Job','business');
        //$jobCount = $User->getJobCount();
        $signCount = $User->getTodaySignCount();
		$key = isset($param['key'])?$param['key']:'';
		$Job = controller('job/Job','business');
		$cominfo = $Job->getsearch($key);
		$view = new WebView();
		$view->setSkin('wechat');
		$view->assignBaseTpl();
		$view->assign('com',$cominfo);
        //$view->assign('jobcount',$jobCount);
        $view->assign('signcount',$signCount);
		$view->assignTpl('list_item', 'job/list-item');
		$view->assign('current_nav', 'todayjob');
		$view->assign('keyword', $key);
		$view->assign('is_weixin', is_weixin());
		if (is_weixin()) {
			$service = controller('WechatService', 'service');
			$view->assign('wechat_sign', $service->getJsSign(cur_page_url()));
			$view->assign('share_link', cur_page_url());
		}
        if (Request::instance()->isAjax()){ Config::set('default_ajax_return', 'html'); return '';}
		// if (Request::instance()->isAjax()){ Config::set('default_ajax_return', 'html'); return $view->fetch('job/list-item');}
		else return $view->fetch('job/list');		
	}

}