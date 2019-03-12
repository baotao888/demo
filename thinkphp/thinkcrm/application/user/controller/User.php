<?php

namespace app\user\controller;

use think\Controller;
use think\Request;
use ylcore\Format;
use think\Config;
use think\Log;

class User extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = [];
    	if (isset($param['keyword'])) $search['keyword'] = $param['keyword'];
    	if (isset($param['adviser_id'])) $search['adviser_id'] = $param['adviser_id'];
    	if (isset($param['is_vip'])) $search['is_vip'] = $param['is_vip'];
    	if (isset($param['reg_time_start'])) $search['reg_time_start'] = $param['reg_time_start'];
    	if (isset($param['reg_time_end'])) $search['reg_time_end'] = $param['reg_time_end'];
    	$business = controller('user', 'business');
        $list = $business->getCustomer($search, $page, $pagesize);
        $count = $business->getCustomerCount($search);
        return ['list' => $list, 'count'=>$count];
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
