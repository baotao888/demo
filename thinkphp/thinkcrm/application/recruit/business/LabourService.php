<?php
namespace app\recruit\business;

use think\Cache;
use think\Config;
use think\Log;

use ylcore\Biz;
use app\recruit\model\CrmLabourService;

class LabourService extends Biz
{
	private $model;
	
	function __construct() {
		$this->model = new CrmLabourService;
	}
	
	public function search() {
		$list = $this->model->select();
		$result = [];
		foreach ($list as $item) {
			$result[$item['id']] = $item;
		}
		return $result;
	}
	
	public function add($name) {
		$labour = $this->model->create(['name' => $name]);
		$this->updateCache();
		return $labour->id;
	}
	
	public function update($id, $name) {
		$this->model->save(['name' => $name], ['id' => $id]);
		$this->updateCache();
		return $id;
	}
	
	public function detail($id) {
		return $this->model->get($id);
	}
	
	public function cache() {
		$cache_biz = controller('admin/CacheBiz', 'business');
		$cache_key = $cache_biz->getLabourServiceKey();
		$list = Cache::get($cache_key);
		if (empty($list)) {
			$list = $this->search();//获取所有劳务公司
			Cache::set($cache_key, $list, Config::get('token_expire'));//设置缓存
		}
		return $list;
	}
	
	private function updateCache() {
		$cache_biz = controller('admin/CacheBiz', 'business');
		$cache_key = $cache_biz->getLabourServiceKey();
		$list = $this->search();//获取所有劳务公司
		Cache::set($cache_key, $list, Config::get('token_expire'));//设置缓存
	}
}