<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;

class Index extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $business = controller('AdminBiz', 'business');
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
     * 登录
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        /*参数验证*/
    	$param = $request->param();
    	$business = controller('AdminBiz', 'business');//业务对象
        if (! isset($param['admin_name']) || strlen($param['admin_name']) < 4 || $business->isBadAdminName($param['admin_name'])) {
        	abort(400, '400 Invalid admin_name supplied');
        } elseif (! isset($param['admin_pwd']) || strlen($param['admin_pwd']) < 4) {
        	abort(400, '400 Invalid admin_pwd supplied');
        }

        $data = ['admin_name'=>$param['admin_name'], 'admin_pwd'=>$param['admin_pwd']];
        $admin_id = $business->add($data);
    	return ['id' => $admin_id];
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //获取管理员详情
    	$model = controller('AdminBiz', 'business');
    	$admin = $model->get($id);
    	return $admin;
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
    	$data = [];
    	if (isset($param['admin_name'])) $data['admin_name'] = $param['admin_name'];
    	if (isset($param['admin_pwd']) && $param['admin_pwd'] != '') $data['admin_pwd'] = $param['admin_pwd'];
    	if (isset($param['status'])) $data['status'] = $param['status'];
    	if (isset($param['role_id'])) $data['role_id'] = $param['role_id'];
    	if (isset($param['is_admin'])) $data['is_admin'] = $param['is_admin'];
    	if (empty($data)){
    		abort(400, '400 Invalid param supplied');
    	}
    	//保存数据
    	$business = controller('AdminBiz', 'business');
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
