<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;

class Position extends Controller
{
    /**
     * 显示职位资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $business = controller('PositionBiz', 'business');
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
    	$request = Request::instance();
        $data['pos_name'] = $request->param('pos_name');
        $data['description'] = $request->param('description');
        $data['is_manager'] = $request->param('is_manager');
        $data['is_adviser'] = $request->param('is_adviser');
        $data['level'] = $request->param('level');
        $business = controller('PositionBiz', 'business');
        $p_id = $business->save($data);

        return ['id' => $p_id];
    }


    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $business = controller('PositionBiz', 'business');
        $list = $business->get($id);

        return $list;
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
        if (isset($param['pos_name'])) {
            $data['pos_name'] = $param['pos_name'];
        }
        if (isset($param['description'])) {
            $data['description'] = $param['description'];
        }
        if (isset($param['is_manager'])) {
            $data['is_manager'] = $param['is_manager'];
        }
        if (isset($param['is_adviser'])) {
        	$data['is_adviser'] = $param['is_adviser'];
        }
        if (isset($param['level'])) {
        	$data['level'] = $param['level'];
        }
        $business = controller('PositionBiz', 'business');
        $list = $business->update($id,$data);

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
