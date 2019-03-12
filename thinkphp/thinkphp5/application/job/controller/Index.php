<?php
namespace app\job\controller;

use ylcore\WebView;

class Index
{
	/**
	 * 职位列表
	 */
	public function Index(){
		$view = new WebView();
		$view->assignBaseTpl();
		$view->assign('current_nav', 'todayjob');
		return $view->fetch('job/list');
	}
	
	/**
	 * 职位详情
	 */
	public function read($id){
		$User = controller('job/Job','business');
		$com = $User->getDetails($id);
		$list = $User->getDetailsList($id);
		$map = $User->getMap($id);
		$view = new WebView();
		$view->setSkin('default1');
		$view->assignBaseTpl();
		$view->assign('com',current($com));
		$view->assign('list',$list);
		$view->assign('map',$map);
		$view->assignTpl('thumbs', 'job/thumbs');//组图
		$view->assignTpl('maps', 'job/maps');//地图
		$view->assignTpl('signupitem', 'job/signupitme1');//职位报名信息
		return $view->fetch('job/detail');
	}
	
	/**
	 * 浏览更新点击量
	 */
	public function hit(){
		//update statistics
		$id = $_GET['id'];
		$model = model('job/JobStatistics');
		$model->where('job_id', $id)->setInc('hits');
	}

}