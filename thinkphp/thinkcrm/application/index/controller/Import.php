<?php
namespace app\index\controller;

use think\Cache;
use app\admin\controller\AdminAuth;

class Import extends AdminAuth
{
	/**
	 * 客户池导入
	 */
	public function customer(){
		$param = request()->param();
		if (! isset($param['page']) || $param['page'] <= 1){
			$page = 1;//开始导入
		} else {
			$page = intval($param['page']);
		}
		$pagesize = 50;//每次导入记录数 [test]
		return $this->importContent('customer', $page, $pagesize);
	}
	
	/**
	 * 候选人导入
	 */
	public function candidate(){
		$param = request()->param();
		if (! isset($param['adviser']) || $param['adviser'] <= 0) {
			abort(400, '400 Invalid adviser supplied');
		}
		if (! isset($param['page']) || $param['page'] <= 1){
			$page = 1;//开始导入
		} else {
			$page = intval($param['page']);
		}
		$pagesize = 50;//每次导入记录数 [test]
		return $this->importContent('candidate', $page, $pagesize, ['adviser'=>$param['adviser']]);
	}
	
	private function importContent($type, $page = 1, $pagesize = 50, $param = []){
		/*1,获取文件缓存*/
		$admin_business = controller('admin/AdminBiz', 'business');//业务对象
		$upload_key = $admin_business->getUploadKey($this->admin_information, $type);
		$import_sheets = Cache::get($upload_key);
		if (! is_array($import_sheets) || !isset($import_sheets['import_content']) || empty($import_sheets['import_content'])){
			abort(400, '400 ' . lang('import_data_null'));//导入内容为空
		}
		$import_rows = $import_sheets['import_content'];
		
		/*导入操作*/
		$total = $import_sheets['import_total'];
		$pages = ceil($total/$pagesize);
		$offset = ($page-1) * $pagesize;
		if ($page <= $pages){
			if ($type == 'customer'){
				$biz = controller('customer/CustomerPoolBiz', 'business');
			} else if ($type == 'candidate'){
				$biz = controller('customer/CandidateBiz', 'business');
			}
			$biz->set_import_field(2);//模型可导入字段
			foreach($import_rows as $key=>$data){
				if ($key >= $offset && $key < $offset+$pagesize){
					$current_data[] = $data;
				}
			}
			$employee_id = $this->getEmployeeId();//当前登录员工
			$admin_id = $this->getAdminId();//当前登录用户
			$i_result = $biz->import($current_data, $employee_id, $admin_id, $param);//导入数据
			//$i_result = [0=>true, 1=>false, 2=>true];//[test]
		
			/*记录导入信息*/
			foreach ($i_result as $i=>$r){
				if ($r) $import_sheets['imported_success']++;
				else $import_sheets['imported_failure']++;
				$result[$offset+$i] = $r;
			}
			Cache::set($upload_key, $import_sheets, 3600);//缓存时间一小时
		
			/*总记录数，已处理的记录数，处理结果*/
			return ['total'=>$total, 'offset'=>$offset, 'result'=>$result, 'finished'=>false];
		}else{
			/*导入失败的记录数，导入成功的记录数*/
			return ['failure_total'=>$import_sheets['imported_failure'], 'success_total'=>$import_sheets['imported_success'], 'finished'=>true];
		}
	}
}