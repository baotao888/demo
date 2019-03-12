<?php
namespace app\poster\controller;

use think\Config;
use think\Request;
use ylcore\Uploader;
use think\Log;

class Ueditor
{
	public function Index(){
		$request = request()->param();
		$action = isset($request['action'])?$request['action']:'';
		
		
		switch ($action) {
			case 'config':
				$result =  $this->getConfig();
				break;
		
				/* 上传图片 */
			case 'uploadimage':
				/* 上传涂鸦 */
			case 'uploadscrawl':
				/* 上传视频 */
			case 'uploadvideo':
				/* 上传文件 */
			case 'uploadfile':
				$result = $this->upload($action);
				break;
		
				/* 列出图片 */
			case 'listimage':
				$allowFiles = $CONFIG['imageManagerAllowFiles'];
				$listSize = $CONFIG['imageManagerListSize'];
				$path = $CONFIG['imageManagerListPath'];
				$result = $this->listImage($allowFiles, $listSize, $path);
				break;
				/* 列出文件 */
			case 'listfile':
				$allowFiles = $CONFIG['fileManagerAllowFiles'];
				$listSize = $CONFIG['fileManagerListSize'];
				$path = $CONFIG['fileManagerListPath'];
				$result = $this->listImage($allowFiles, $listSize, $path);
				break;
		
				/* 抓取远程文件 */
			case 'catchimage':
				$result = $this->crawler();
				break;
		
			default:
				$result = array(
					'state'=> '请求地址出错'
				);
				break;
		}

		/* 输出结果 */
		if (isset($_GET["callback"])) {
			if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
				return htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
			} else {
				return array(
						'state'=> 'callback参数不合法'
				);
			}
		} else {
			return $result;
		}
	}
	
	private function getConfig(){
		$CONFIG = Config::get('ueditor');
		$CONFIG['imagePathFormat'] = Config::get('upload.path') . $CONFIG['imagePathFormat'];
		$CONFIG['rootPath'] = Config::get('upload.path');
		$CONFIG['imageUrlPrefix'] = $CONFIG['imageUrlPrefix'] . Config::get('upload.domain');
		return $CONFIG;
	}
	
	/**
	 * 上传附件
	 */
	private function upload($action){
		/* 上传配置 */
		$base64 = "upload";
		$CONFIG = Config::get('ueditor');
		switch ($action) {
			case 'uploadimage':
				$config = array(
				"pathFormat" => $CONFIG['imagePathFormat'],
				"maxSize" => $CONFIG['imageMaxSize'],
				"allowFiles" => $CONFIG['imageAllowFiles']
				);
				$fieldName = $CONFIG['imageFieldName'];
				break;
			case 'uploadscrawl':
				$config = array(
				"pathFormat" => $CONFIG['scrawlPathFormat'],
				"maxSize" => $CONFIG['scrawlMaxSize'],
				"allowFiles" => $CONFIG['scrawlAllowFiles'],
				"oriName" => "scrawl.png"
						);
						$fieldName = $CONFIG['scrawlFieldName'];
						$base64 = "base64";
						break;
			case 'uploadvideo':
				$config = array(
				"pathFormat" => $CONFIG['videoPathFormat'],
				"maxSize" => $CONFIG['videoMaxSize'],
				"allowFiles" => $CONFIG['videoAllowFiles']
				);
				$fieldName = $CONFIG['videoFieldName'];
				break;
			case 'uploadfile':
			default:
				$config = array(
				"pathFormat" => $CONFIG['filePathFormat'],
				"maxSize" => $CONFIG['fileMaxSize'],
				"allowFiles" => $CONFIG['fileAllowFiles']
				);
				$fieldName = $CONFIG['fileFieldName'];
				break;
		}
		
		/* 生成上传实例对象并完成上传 */
		$config['imagePathFormat'] = Config::get('upload.path') . $CONFIG['imagePathFormat'];
		$config['rootPath'] = Config::get('upload.path');
		$config['imageUrlPrefix'] = $CONFIG['imageUrlPrefix'] . Config::get('upload.domain');
		$up = new Uploader($fieldName, $config, $base64);
		/**
		 * 得到上传文件所对应的各个参数,数组结构
		 * array(
		 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
		 *     "url" => "",            //返回的地址
		 *     "title" => "",          //新文件名
		 *     "original" => "",       //原始文件名
		 *     "type" => ""            //文件类型
		 *     "size" => "",           //文件大小
		 * )
		*/
		
		/* 返回数据 */
		$file_info = $up->getFileInfo();
		//$file_info['url'] = 'http://' . Config::get('upload.domain') . $file_info['url'];
		return $file_info;
	}
	
	/**
	 * 抓取远程图片
	 */
	public function crawler(){
		$CONFIG = $this->getConfig();
		/* 上传配置 */
		$config = array(
				"pathFormat" => $CONFIG['catcherPathFormat'],
				"maxSize" => $CONFIG['catcherMaxSize'],
				"allowFiles" => $CONFIG['catcherAllowFiles'],
				"oriName" => "remote.png"
		);
		$fieldName = $CONFIG['catcherFieldName'];
		
		/* 抓取远程图片 */
		$list = array();
		if (isset($_POST[$fieldName])) {
			$source = $_POST[$fieldName];
		} else {
			$source = $_GET[$fieldName];
		}
		foreach ($source as $imgUrl) {
			$item = new Uploader($imgUrl, $config, "remote");
			$info = $item->getFileInfo();
			array_push($list, array(
			"state" => $info["state"],
			"url" => $info["url"],
			"size" => $info["size"],
			"title" => htmlspecialchars($info["title"]),
			"original" => htmlspecialchars($info["original"]),
			"source" => htmlspecialchars($imgUrl)
			));
		}
		
		/* 返回抓取数据 */
		return array(
				'state'=> count($list) ? 'SUCCESS':'ERROR',
				'list'=> $list
		);
	}
	
	private function listImage($allowFiles, $listSize, $path){
		$allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
		
		/* 获取参数 */
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;
		
		/* 获取文件列表 */
		$path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
		$files = $this->getFiles($path, $allowFiles);
		if (!count($files)) {
			return json_encode(array(
					"state" => "no match file",
					"list" => array(),
					"start" => $start,
					"total" => count($files)
			));
		}
		
		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
			$list[] = $files[$i];
		}
		//倒序
		//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
		//    $list[] = $files[$i];
		//}
		
		/* 返回数据 */
		$result = array(
				"state" => "SUCCESS",
				"list" => $list,
				"start" => $start,
				"total" => count($files)
		);
		
		return $result;
	}
	
	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	private function getFiles($path, $allowFiles, &$files = array())
	{
		if (!is_dir($path)) return null;
		if(substr($path, strlen($path) - 1) != '/') $path .= '/';
		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$path2 = $path . $file;
				if (is_dir($path2)) {
					getfiles($path2, $allowFiles, $files);
				} else {
					if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
						$files[] = array(
								'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
								'mtime'=> filemtime($path2)
						);
					}
				}
			}
		}
		return $files;
	}
}