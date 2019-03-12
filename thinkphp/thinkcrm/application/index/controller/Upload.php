<?php
namespace app\index\controller;

use think\File;
use think\Request;
use think\Config;
use think\Log;
use think\Cache;

class Upload
{
	/**
	 * 上传Excel
	 * 客户批量导入
	 * 上传文件，读取文件内容，映射字段
	 */
	public function customer(){
		return $this->cacheFileContent('customer');
	}
	/**
	 * 上传Excel
	 * 人选批量导入
	 * 上传文件，读取文件内容，映射字段
	 */
	public function candidate(){
		return $this->cacheFileContent('candidate');
	}
	
	private function cacheFileContent($type){
		/*上传附件*/
		$flag = false;
		$inputFileName = '';
		// 获取表单上传文件
		$files = request()->file();
		if (is_array($files)){
			$files = current($files);
		}
		if ($files) {
			$info = $files->move(Config::get('upload.path'));
			if ($info){
				$inputFileName = Config::get('upload.path') . DS . $info->getSaveName();
				$flag = true;
			}
		}
		if (! $flag){
			// 上传失败返回错误信息
			abort(400, '400 ' . $file->getError());
		}
		
		/***读取excel文件内容***/
		/*1, 加载phpexcel类库*/
		import('PHPExcel.PHPExcel', EXTEND_PATH);
		/*2, 读取文件内容*/
		try{
			$objReader = new \PHPExcel_Reader_Excel5();
			$objReader->setReadDataOnly(true);
			$objPHPExcel = $objReader->load($inputFileName);
			$objWorksheet = $objPHPExcel->getActiveSheet();
		}catch(Exception $e){
			abort(400, '400 ' . lang('attachment_type_error'));
		}
		$arr_cell = array();//文件内容容器
		foreach ($objWorksheet->getRowIterator() as $row) {
			$cell_row = array();
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			foreach ($cellIterator as $cell) {
				$cell_row[] = $cell->getValue();
			}
			$arr_cell[] = $cell_row;
		}
		$title_row = array_shift($arr_cell);//提取标题行
		
		/***缓存文件内容***/
		//1,设置缓存键
		//1.1,获取用户缓存
		$admin_business = controller('admin/AdminBiz', 'business');//业务对象
		$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
		$this->admin_information = Cache::get($token_key);
		//1.2,根据用户编号和文件名生成缓存键
		$upload_key = $admin_business->getUploadKey($this->admin_information, $type);
		//2,设置缓存内容
		$all_info = array(
				'import_total' => count($arr_cell),//导入总数
				'import_title' => $title_row,//导入标题
				'import_content' => $arr_cell,//导入内容
				'imported_success' => 0,//成功导入的记录数
				'imported_failure' => 0,//导入失败的记录数
		);
		//3,设置缓存
		Cache::set($upload_key, $all_info, 3600);//缓存时间一小时
		
		/***设置输出内容***/
		return $all_info;
	}
}