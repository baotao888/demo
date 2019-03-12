<?php

namespace app\recruit\controller;

use think\Controller;
use think\Request;

use ylcore\Format;

use app\recruit\business\LabourService;

class Labour extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $business = new LabourService;
        $return = $business->cache();
        return Format::object2array($return);
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
        $param = $request->param();
        if (! isset($param['name']) || ! $param['name']) abort(400, '400 Invalid name supplied');
        //保存数据
        $business = new LabourService;
        $id = $business->add($param['name']);
        return ['id' => $id];
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $business = new LabourService;
        return $business->detail($id);
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
    	$param = $request->param();
    	if (! isset($param['name']) || ! $param['name']) abort(400, '400 Invalid name supplied');
    	//保存数据
    	$business = new LabourService;
    	$business->update($id, $param['name']);
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
