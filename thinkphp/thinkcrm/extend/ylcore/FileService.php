<?php
// [文件服务]

namespace ylcore;

use think\Config;

class FileService
{
	/**
	 * base64位图片上传
	 */
	public function base64_upload($base64, $is_post = false) {
		//post的数据里面，加号会被替换为空格，需要重新替换回来
		$base64_image = $is_post?str_replace(' ', '+', $base64):$base64;
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
			//匹配成功
			if($result[2] == 'jpeg'){
				$image_name = uniqid().'.jpg';
				//纯粹是看jpeg不爽才替换的
			}else{
				$image_name = uniqid().'.'.$result[2];
			}
			$url_path = date('Ymd');//附件地址路径
			$upload_path = Config::get('upload.path') . DS . $url_path . DS;//文件路径
			if ($this->checkPath($upload_path) == false) return false;//创建目录失败
			$image_file =  $upload_path . $image_name;
			//服务器文件存储路径
			if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))){
				return $this->getUrl("{$url_path}/{$image_name}");
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	/**
	 * 检查路径
	 * @param string $path
	 * @return boolean
	 */
	public function checkPath($path)
	{
		if (is_dir($path)) {
			return true;
		}
	
		if (mkdir($path, 0755, true)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 返回图片url
	 */
	public function getUrl($file){
		return '//' . Config::get('upload.domain') . "/" . str_replace('\\', '/', $file);
	}
}