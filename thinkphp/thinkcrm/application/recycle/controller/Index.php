<?php
namespace app\recycle\controller;

use think\Cache;
use think\Request;
use app\recycle\business\CustomerPoolRecycleBinBiz;

class Index
{
	/**
	 * 客户回收站
	 */
	public function customerPool(Request $request) {
		$param = $request->param();
		$search = isset($param['search'])?$param['search']:'';
		$page = isset($param['page'])?$param['page']:'';
		$pagesize = isset($param['pagesize'])?$param['pagesize']:'';
		$business = new CustomerPoolRecycleBinBiz();
		$list = $business->getAll($page, $pagesize, $search);
		$count = $business->getCount($search);
		return ['list' => $list, 'count'=>$count];
	}
	
	/**
	 * 还原客户
	 * @param Request $request
	 */
	public function restore(Request $request) {
		/*参数验证*/
		$param = $request->param();
		$arr_id = $param['customers/a'];
		if (! $arr_id) abort(400, '400 Invalid ids supplied');
		$business = new CustomerPoolRecycleBinBiz();
		$flag = $business->restore($arr_id);
		return ['success'=>$flag, 'error'=>count($arr_id)-$flag];
	}
	
	/**
	 * 删除客户
	 * @param Request $request
	 */
	public function delete(Request $request) {
		/*参数验证*/
		$param = $request->param();
		$arr_id = $param['customers/a'];
		if (! $arr_id) abort(400, '400 Invalid ids supplied');
		$business = new CustomerPoolRecycleBinBiz();
		$flag = $business->delete($arr_id);
		return ['success'=>$flag, 'error'=>count($arr_id)-$flag];
	}
}