<?php
namespace app\article\business;

use ylcore\Biz;
use app\article\model\Article;

class ArticleBiz extends Biz
{
	/**
	 * 获取所有文章信息
	 */
	public function getAll()
	{
		$return = array();
		$model = model('Article');
		$list = $model->where('id', '>', 0)->field('id, title, cat_id, thumb, create_at, desc')->order('create_at desc')->select();
		if ($list){
			foreach ($list as $item){
				$return[$item['id']] = $item;
			}
		}
		return $return;
	}
	
	/**
	 * 新增文章
	 */
	public function add($data){
		$article = model('Article');
		$article->data([
				'title'  =>  $data['title'],
				'content' =>  $data['content'],
				'desc' => $this->cutDesc($data['content']),
				'create_at' => time()
		]);
		$article->save();//保存
		return $article->id;
	}
	
	/**
	 * 获取文章详情
	 * @param integer $id
	 */
	public function get($id){
		$article = model('Article');
		$obj = $article->get($id);
		//if ($obj->content) $obj->content = html_entity_decode($obj->content);
		return $obj;
	}
	
	/**
	 * 更新文章信息
	 */
	public function update($id, $data){
		$article = model('Article');
		$update = [];
		if (isset($data['content'])){
			$update['content'] = $data['content'];
			$update['desc'] = $this->cutDesc($update['content']);
		}
		if (isset($data['title']) && $data['title']){
			$update['title'] = $data['title'];
		}
		if (isset($data['thumb']) && $data['thumb']){
			$update['thumb'] = $data['thumb'];
		}
		$article->save($update, ['id' => $id]);//更新
	}
	
	/**
	 * 截取简介
	 * @param string $content
	 */
	private function cutDesc($content){
		return str_cut(strip_tags($content), 200);
	}
	
	/**
	 * 获取最新文章总数
	 */
	public function latestCount(){
		$time = time() - 3600 * 24;//24小时只能的文章
		$model = model('article/Article');
		return $model->where('create_at > ' . $time)->count();
	}
}