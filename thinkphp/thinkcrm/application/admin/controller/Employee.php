<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;
use ylcore\FileService;

class Employee extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $business = controller('EmployeeBiz','business');
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
        $data['real_name'] = $request->param('real_name');
        $data['phone'] = $request->param('phone');
        $data['gender'] = $request->param('gender');
        $data['nickname'] = $request->param('nickname');
        $data['join_at'] = $request->param('join_at');
        $data['number'] = $request->param('number');
        $business = controller('EmployeeBiz','business');
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
        $business = controller('EmployeeBiz','business');
        $result = $business->get($id);
        return $result;
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
        if (isset($param['real_name'])) {
            $data['real_name'] = $request->param('real_name');
        }
        if (isset($param['phone'])) {
            $data['phone'] = $request->param('phone');
        }
        if (isset($param['gender'])) {
            $data['gender'] = $request->param('gender');
        }
        if (isset($param['nickname'])) {
            $data['nickname'] = $request->param('nickname');
        }
        if (isset($param['join_at'])) {
            $data['join_at'] = $request->param('join_at');
        }
        if (isset($param['admin_id'])) {
            $data['admin_id'] = $request->param('admin_id');
        }
        if (isset($param['org_id'])) {
            $data['org_id'] = $request->param('org_id');
        }
        if (isset($param['pos_id'])) {
            $data['pos_id'] = $request->param('pos_id');
        }
        if (isset($param['number'])) {
        	$data['number'] = $request->param('number');
        }
        if (isset($param['status'])) {
        	$data['status'] = $request->param('status');
        }
        if (isset($param['avatar'])){
        	if (is_url($param['avatar'])){
        		$avatar = $param['avatar'];
        	}else{
        		$service = new FileService();
        		$avatar = $service->base64_upload($param['avatar']);//保存base64图片
        	}
        	if ($avatar) $data['avatar'] = $avatar;
        }
        $business = controller('EmployeeBiz','business');
        if ($data) $id = $business->update($id, $data);
        
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