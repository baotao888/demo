<?php
namespace app\article\controller;

use ylcore\WebView;

class Index
{
	/**
	 * 文章列表
	 */
	public function Index(){
		$art = Controller('article/article','business');
		$res = $art->Articles();
		$page = $res->render();
		$view = new WebView();
		$view->setSkin('default1');
		$view->assignBaseTpl();
		$view->assign('current_nav', 'article');
		$view->assign('res', $res);
		$view->assign('page', $page);
		
		return $view->fetch('article/list');
	}

	/**
	 * 文章详情
	 */
	public function read($id){
		$art = Controller('article/article','business');
		$res = $art->ArticleDetail($id);
		$view = new WebView();
		$view->setSkin('default1');
		$view->assignBaseTpl();
		$view->assign('detail',$res);
		$view->assign('current_nav', 'article');
		return $view->fetch('article/detail');
	}

}