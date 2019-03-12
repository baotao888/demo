<?php
namespace app\job\controller;

use think\File;
use think\Request;
use think\Config;
use think\Log;
use ylcore\FileService;

class Picture
{
	/**
	 * 上传图片
	 */
	public function upload(){
		$flag = true;
		$result = array();
		// 获取表单上传文件
		$files = request()->file();
		if (is_array($files)){
			foreach($files as $file){
				$info = $file->move(Config::get('upload.path'));
				if($info){
					$service = new FileService();
					$result[] = $service->getUrl($info->getSaveName());
				}else{
					$flag = false;
					break;
				}
			}
		} else if ($files) {
			$info = $files->move(Config::get('upload.path'));
			if($info){
				$service = new FileService();
				$result[] = $service->getUrl($info->getSaveName());
			}else{
				$flag = false;
			}
		}
		if ($flag){
			return $result;
		} else {
			// 上传失败获取错误信息
			return $file->getError();
		}
	}
}