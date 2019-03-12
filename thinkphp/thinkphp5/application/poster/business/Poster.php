<?php
namespace app\poster\business;

use ylcore\Biz;
use think\Collection;

class Poster extends Biz{
	/**
	 * 获取所有广告
	 * @return array
	 */
	public function getUrl(){
		$url = model('poster/poster');
		$list = $url->select();
		$nlist = new collection($list);
		$newurl = $nlist->toArray();
		return $newurl;
	}
	
	/**
	 * 获取已启用的广告
	 * @return array
	 */
	public function getEnabledPoster($space_id = 1){
		$url = model('poster/poster');
		$list = $url->where('space_id', $space_id)->where('disabled', 0)->order('listorder')->select();
		$nlist = new collection($list);
		$newurl = $nlist->toArray();
		return $newurl;
	}
}