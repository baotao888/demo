<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;

class Organization extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $business = controller('OrganizationBiz','business');
        $res = $business->getAll();

        return $res;
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
        $data['parent_id'] = $request->param('parent_id');
        $data['org_name'] = $request->param('org_name');
        $data['description'] = $request->param('description');
        $data['nickname'] = $request->param('nickname');
        $data['is_adviser'] = $request->param('is_adviser');
    	$business = controller('OrganizationBiz','business');
        $res = $business->save($data);
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
        /*参数验证*/
        $param = $request->param();
        if (isset($param['parent_id'])) {
            $data['parent_id'] = $request->param('parent_id');
        }
        if (isset($param['org_name'])) {
            $data['org_name'] = $request->param('org_name');
        }
        if (isset($param['description'])) {
            $data['description'] = $request->param('description');
        }
        if (isset($param['nickname'])) {
            $data['nickname'] = $request->param('nickname');
        }
        if (isset($param['is_adviser'])) {
        	$data['is_adviser'] = $request->param('is_adviser');
        }
        if (isset($param['listorder'])) {
        	$data['listorder'] = $request->param('listorder');
        }
        $business = controller('OrganizationBiz','business');
        $res = $business->update($id,$data);

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
