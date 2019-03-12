<?php
namespace app\poster\controller;

use think\File;
use think\Request;
use think\Config;
use think\Log;
use ylcore\FileService;

class Index
{
	/**
	 * 广告列表
	 */
	public function Index(){
		$param = request()->param();
		$space = isset($param['space'])?$param['space']:1;
		$business = controller('PosterBiz', 'business');
        $list = $business->getAll($space);

        return $list;
	}
	
	/**
	 * 新增广告
	 */
	public function save(){
		/*参数验证*/
		$request = Request::instance();
		$data['title'] = $request->param('title');
		$data['listorder'] = $request->param('listorder');
		$content = $request->param('content');
		if (is_url($content)){
			$data['content'] = $content;
		}else{
			$service = new FileService();
			$data['content'] = $service->base64_upload($content);//保存base64图片
		}
		$data['space_id'] = $request->param('space_id');
		$business = controller('PosterBiz', 'business');
		$p_id = $business->save($data);
		
		return ['id' => $p_id];
	}
	
	/**
	 * 更新广告
	 */
	public function update(Request $request){
		//参数验证
        $param = $request->param();
		if (! isset($param['id']) || ! $param['id']) {
    		abort(400, '400 Invalid id supplied');
    	}
    	$id = $param['id'];
        if (isset($param['content'])) {
        	$content = $param['content'];
        	if (is_url($content)){
        		$data['content'] = $content;
        	}else{
        		$service = new FileService();
        		$data['content'] = $service->base64_upload($content);//保存base64图片
        	}
        }
        if (isset($param['title'])) {
            $data['title'] = $param['title'];
        }
        if (isset($param['listorder'])) {
            $data['listorder'] = $param['listorder'];
        }
        if (isset($param['disabled'])) {
        	$data['disabled'] = $param['disabled'];
        }
        $business = controller('PosterBiz', 'business');
        $list = $business->update($id,$data);

        return ['id' => $id];
	}
	
	/**
	 * 显示广告详情
	 *
	 * @param  int  $id
	 * @return \think\Response
	 */
	public function detail($id)
	{
		$model = controller('PosterBiz', 'business');
		$poster = $model->get($id);
		return $poster;
	}
	
	/**
	 * 文件上传
	 */
	public function upload(){
		$flag = true;
		// 获取表单上传文件
		$files = request()->file();
		if (is_array($files)){
			foreach($files as $file){
				$info = $file->move(Config::get('upload.path'));
				if($info){
					// 成功上传后 获取上传信息
					// 输出 jpg
					//echo $info->getExtension();
					// 输出 42a79759f284b767dfcb2a0197904287.jpg
					//echo $info->getFilename();
					//添加附件上传记录[test]
					//do ...
				}else{
					$flag = false;
					break;
				}
			}	
		} else if ($files) {
			$info = $files->move(Config::get('upload.path'));
			if($info){
				// 成功上传后 获取上传信息
				// 输出 jpg
				//echo $info->getExtension();
				// 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
				//echo $info->getSaveName();
				// 输出 42a79759f284b767dfcb2a0197904287.jpg
				//echo $info->getFilename();
				//添加附件上传记录[test]
				//do ...
			}else{
				$flag = false;
			}
		}
		if ($flag){
			return array( 'answer' => 'File transfer completed' );
		} else {
			// 上传失败获取错误信息
			return $file->getError();			
		}
	}
	
	/**
	 * 广告位
	 */
	public function space() {
		Config::load(APP_PATH.'poster/config_space.php');
		return Config::get('poster_space');
	}
}