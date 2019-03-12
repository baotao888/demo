<?php

namespace app\article\controller;

use think\Controller;
use think\Request;
use think\Log;
use ylcore\FileService;
use think\Config;

class Article extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $business = controller('ArticleBiz', 'business');
        $list = $business->getAll();
        return $list;
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //参数验证
        $param = $request->param();
        $data = array();
    	if (! isset($param['title']) || strlen($param['title']) < 2) {
    		abort(400, '400 Invalid title supplied');
    	} elseif (! isset($param['content']) || strlen($param['content']) < 10){
    		abort(400, '400 Invalid content supplied');
    	} else {
    		$data['title'] = $param['title'];
    		$data['content'] = $param['content'];
    	}
    	//保存数据
    	$business = controller('ArticleBiz', 'business');
    	$article_id = $business->add($data);
    	return ['id' => $article_id];
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //获取职位详情
    	$business = controller('ArticleBiz', 'business');
    	$article = $business->get($id);
    	return $article;
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
    	
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $param = $request->param();
    	$data = [];
    	if (isset($param['title'])) $data['title'] = $param['title'];
    	if (isset($param['content'])) $data['content'] = $param['content'];
    	if (isset($param['cover'])){
    		if (is_url($param['cover'])){
    			$cover = $param['cover'];
    		}else{
    			$service = new FileService();
    			$cover = $service->base64_upload($param['cover']);//保存base64图片
    		}
    		if ($cover) $data['thumb'] = $cover;
    	}
    	if (empty($data)){
    		abort(400, '400 Invalid param supplied');
    	}
    	//保存数据
    	$business = controller('ArticleBiz', 'business');
    	$business->update($id, $data);
    	return ['id' => $id];
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
