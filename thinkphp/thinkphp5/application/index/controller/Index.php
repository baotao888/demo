<?php
namespace app\index\controller;

use think\Model;
use think\Request;
use ylcore\WebView;
use think\Config;

class Index
{
    /**
     * 首页
     */
    public function index(){
        $img = model('poster/Poster','business');
        $imgurl = $img->getEnabledPoster();
        $job = controller('job/Job','business');
        $com = $job->getInfo();
        //$jobCount = $job->getJobCount();
        $signCount = $job->getTodaySignCount();
        $view = new WebView();
        $view->setSkin('default1');
        $view->assignBaseTpl();
        //$view->assign('jobcount',$jobCount);
        $view->assign('signcount',$signCount);
        $view->assign('com',$com);
        $view->assign('imgurl',$imgurl);
        $view->assignTpl('list_item', 'job/list-item');
        $view->assign('current_nav', 'index');
        return $view->fetch('index');
    }
    
	public function search(){
		$param = request()->param();
        $User = controller('job/Job','business');
        //$jobCount = $User->getJobCount();
        $signCount = $User->getTodaySignCount();
		$key = isset($param['key'])?$param['key']:'';
		$Job = controller('job/Job','business');
		$cominfo = $Job->getsearch($key);
		$view = new WebView();
		$view->setSkin('default1');
		$view->assignBaseTpl();
		$view->assign('com',$cominfo);
        //$view->assign('jobcount',$jobCount);
        $view->assign('signcount',$signCount);
        $view->assign('keyword', $key);
		$view->assignTpl('list_item', 'job/list-item');
		$view->assign('current_nav', 'todayjob');
        if (Request::instance()->isAjax()){ Config::set('default_ajax_return', 'html'); return '';}
		// if (Request::instance()->isAjax()){ Config::set('default_ajax_return', 'html'); return $view->fetch('job/list-item');}
		else return $view->fetch('search');		
	}
	
    public function about(){
        $view = new WebView();
        $view->setSkin('default1');
        $view->assignBaseTpl();
        return $view->fetch('about');
    }
    
    public function protocol(){
    	$view = new WebView();
    	$view->setSkin('default1');
    	$view->assignBaseTpl();
    	return $view->fetch('userprotocol');
    }
    
    public function contact(){
    	$view = new WebView();
    	$view->setSkin('default1');
    	$view->assignBaseTpl();
    	return $view->fetch('contact');
    }
    
    public function client(){
    	$view = new WebView();
    	$view->assignBaseTpl();
    	$view->assign('current_nav', 'client');
    	return $view->fetch('client');
    }
    
    public function statement(){
    	$view = new WebView();
    	$view->setSkin('default1');
    	$view->assignBaseTpl();
    	return $view->fetch('statement');
    }

     public function recruit(){
        	$view = new WebView();
        	$view->setSkin('default1');
        	$view->assignBaseTpl();
        	return $view->fetch('recruit');
        }
}