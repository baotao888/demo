<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;

class Role extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $business = controller('RoleBiz','business');
        $result = $business->getAll();

        return $result;
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
    	$request = Request::instance();
        $data['role_name'] = $request->param('role_name');
        $data['description'] = $request->param('description');
        $result = controller('RoleBiz','business');
        $res = $result->save($data);

        return ['id' => $res];
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $result = controller('RoleBiz','business');
        $res = $result->get($id);
        
        return $res;
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
        //参数验证
        $param = $request->param();
        if (isset($param['role_name'])) {
            $data['role_name'] = $param['role_name'];
        }
        if (isset($param['description'])) {
            $data['description'] = $param['description'];
        }
        if (isset($param['privileges'])) {
            $data['privileges'] = $param['privileges'];
        }
        
        $result = controller('RoleBiz','business');
        $list = $result->update($id,$data);

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
