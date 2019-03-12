<?php
namespace app\article\business;

use ylcore\Biz;
use think\Collection;

class Article 
{
	/*
	*文章列表
	*/
	public function Articles(){
		$page = 10;
		$model = model('article/Article');
		$all = $model->Order('id','desc')->paginate($page);

		return $all;
	}


	/*
	*文章列表->详情页
	*/
	public function ArticleDetail($id){
		$model = model('article/Article');
		$all = $model->get($id);
		
		return $all;
	}
}