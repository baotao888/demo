<?php

namespace app\salesorder\controller;

use think\Controller;
use think\Request;
use app\admin\controller\AdminAuth;

class Salesorder extends AdminAuth
{
    /**
     * 显示订单列表
     *
     * @return \think\Response
     */
    public function index()
    {
    	/*获取参数*/
        $param = request()->param();
        if (! isset($param['type']))  abort(400, '400 Invalid type supplied');
        $type = $param['type'] ? $param['type'] : 1;
        $page = isset($param['page']) ? max(1, $param['page']) : 1;
        $pagesize = isset($param['pagesize']) ? $param['pagesize'] : 20;
        $time_start = isset($param['time_start']) ? $param['time_start'] : false;
        $time_end = isset($param['time_end']) ? $param['time_end'] : false;
        $keyword = isset($param['keyword']) ? $param['keyword'] : false;
        $adviser = isset($param['adviser']) ? $param['adviser'] : false;
        $org = isset($param['org']) ? $param['org'] : false;
        $is_invalid = isset($param['is_invalid']) ? $param['is_invalid'] : false;
        $ent_id = isset($param['ent_id']) ? $param['ent_id'] : false;
        $ls_id = isset($param['ls_id']) ? $param['ls_id'] : false;
        $is_sure =  isset($param['is_sure']) ? $param['is_sure'] : false;
        $receive_start = isset($param['receive_start']) ? $param['receive_start'] : false;
        $receive_end = isset($param['receive_end']) ? $param['receive_end'] : false;
        /*获取数据*/
        $biz = controller('salesorder/SalesorderBiz', 'business');
        $filter = [
        	'keyword' => $keyword,
        	'time_start' => $time_start, 
        	'time_end' => $time_end, 
        	'org' => $org, 
        	'is_invalid' => $is_invalid == 'true' ? 1 : 0,
        	'is_sure' => $is_sure !== false ? ($is_sure == 'true' ? 1 : 0) : false,
        	'ent_id' => $ent_id,
        	'ls_id' => $ls_id,
        	'receive_start' => $receive_start,
        	'receive_end' => $receive_end,
        ];
        if ( $adviser ) {
        	$list = $biz->searchMy($type, $adviser, $page, $pagesize, $filter);
        } elseif ($org) {
        	$list = $biz->searchOrg($type, $org, $page, $pagesize, $filter);
        } elseif ($this->isAdministrator()){
        	// 管理员查看所有的信息
        	$list = $biz->searchAll($type, $page, $pagesize, $filter);
        } elseif ($this->isManager()) {
        	/*管理岗位可以查看本组织及其下级组织的所有信息*/
        	$org_id = $this->getOrganizationId();
        	$list = $biz->searchOrg($type, $org_id, $page, $pagesize, $filter);
        } else {
        	/*非管理岗位只能查看自己的信息*/
        	$employee_id = $this->getEmployeeId();
        	$list = $biz->searchMy($type, $employee_id, $page, $pagesize, $filter);
        }
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
        //
    }

    /**
     * 显示订单资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $this->checkDataAuth([$id]);
        
        $business = controller('SalesorderBiz', 'business');
        $detail = $business->detail($id);//订单明细详情
        return $detail;
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
    
    /**
     * 验证数据权限
     * @param: $arr_id array 业绩编号
     */
    private function checkDataAuth($arr_id){
    	$flag = false;
    	//超级管理员不验证
    	if ($this->isAdministrator()) {
    		$flag = true;
    	} else {
    		/*验证业绩是否对顾问可见*/
    		$business = controller('SalesorderBiz', 'business');
    		$employee_id = $this->getEmployeeId();
    		$org_id = $this->getOrganizationId();
    		if ($business->validateEmployee($employee_id, $arr_id, $org_id)) {
    			$flag = true;
    		}
    	}
    
    	$flag == false && abort(403, '403 Forbidden');
    }
}
