<?php

namespace app\location\controller;

use think\Controller;
use think\Request;

use app\location\business\LocationBiz;
use app\token\controller\AuthController;

class Index extends AuthController
{
	/**
	 * 实现抽象方法
	 */
	function isValidate() {
		$flag = false;//默认无需权限验证
		$request = Request::instance();
		if ($request->action() == 'save') $flag = true;
		return $flag;
	}
	
    /**
     * 显示附近的人
     *
     * @return \think\Response
     */
    public function index()
    {
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:8;
    	$longitude = isset($param['longitude'])?$param['longitude']:false;//坐标经度
    	$latitude = isset($param['latitude'])?$param['latitude']:false;//坐标维度
    	$radius = isset($param['radius'])?$param['radius']:5000;//搜索半径。默认5千米
    	if (! $longitude || !$latitude) abort(400, '400 Invalid location marker supplied');
    	/*搜索附近的人*/
    	$biz = new LocationBiz();
    	$list = $biz->search($longitude, $latitude, $radius, $page, $pagesize);
    	/*数据传输*/
    	$return = $biz->transfer($list);
    	return $return;
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
     * 保存位置
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
    	/*获取参数*/
    	$param = request()->param();
    	$longitude = isset($param['longitude'])?$param['longitude']:false;//坐标经度
    	$latitude = isset($param['latitude'])?$param['latitude']:false;//坐标维度
    	if (! $longitude || !$latitude) abort(400, '400 Invalid location marker supplied');
    	/*保存位置*/
    	$biz = new LocationBiz();
    	$biz->setting($longitude, $latitude, $this->uid);
    	return true;
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
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
        //
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
