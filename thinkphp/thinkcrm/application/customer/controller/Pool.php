<?php
namespace app\customer\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;
use think\Log;
use app\admin\controller\AdminAuth;
use ylcore\Format;

class Pool extends AdminAuth
{
	
	/**
     * 显示客户池列表
     * 公海客户池
     */
    public function index()
    {
        $business = controller('customer/CustomerPoolBiz','business');
        $request = Request::instance();
        $real_name = $request->param('real_name');
        $phone = $request->param('phone');
        $from = $request->param('from');
        $page = $request->param('page');
        $pagesize = $request->param('pagesize');
        $param = $request->param();
        $open_start = isset($param['distribute_time_s'])?$param['distribute_time_s']:'';
        $open_end = isset($param['distribute_time_e'])?$param['distribute_time_e']:'';
        $page = max(1, $page);
        $pagesize = max(20, $pagesize);
        /*获取公开客户*/
        $result = $business->getOpen($real_name, $phone, $from, $page, $pagesize, $open_start, $open_end);
        /*客户详情字段控制*/
        if (! $this->isAdministrator() && ! $this->isAdmin()){
        	if ($result['count']>0) {
        		foreach($result['list'] as $key=>$item){
        			if ($item['phone']) $result['list'][$key]['phone'] = Format::mobileSecret($item['phone']);
        		}
        	}
        }
        return ['list' => $result['list'] , 'count' => $result['count']];
    }
    
    /**
     * 新增客户池资料
     */
    public function save(Request $request)
    {
         /*参数验证*/
        $request = Request::instance();
        $data['real_name'] = $request->param('real_name');
        $data['phone'] = $request->param('phone');
        $data['gender'] = $request->param('gender');
        $data['from'] = $request->param('from');
        $data['birthday'] = $request->param('birthday');
        $data['hometown'] = $request->param('hometown');
        $data['wechat'] = $request->param('wechat');
        $data['career'] = $request->param('career');
        $data['qq'] = $request->param('qq');
        $data['address'] = $request->param('address');
        $data['email'] = $request->param('email');
        $data['mobile_1'] = $request->param('mobile');
        $data['employee_id'] = $this->getEmployeeId();//当前登录员工
        $data['admin_id'] = $this->getAdminId();//当前登录用户
        $data['description'] = $request->param('contact');//初始联系内容
        $data['intetion'] = $request->param('intetion');//是否为意向客户

        $business = controller('CustomerPoolBiz','business');
        $res = $business->save($data);
        return ['id' => $res];
    }
    
    /**
     * 显示客户池指定的客户
     */
    public function detail($id)
    {
    	$id = intval($id);
    	/*验证用户数据权限*/
    	$this->checkDataAuth($id);
    	/*获取客户详情*/
        $business = controller('CustomerPoolBiz','business');
        $result = $business->get($id);
        /*客户详情字段控制*/
        if (! $this->isAdministrator() && ! $this->isAdmin()){
        	$result = $business->secretField($result);
        }

        return $result;
    }
    
    /**
     * 显示编辑资源表单页.
     */
    public function edit($id)
    {
        //
    }

    /**
     * 分配客户池客户给求职顾问.
     */
    public function distribute(Request $request, $adviser)
    {
        $business = controller('CustomerPoolBiz','business');
        $param = $request->param();
        $data = $param['customerpools/a'];
        $employee_id = $this->getEmployeeId();//当前登录用户
        
        if(is_array($data)){
        	$result = $business->distribute($data, $adviser, $employee_id);
    	}else{
        	$arr = explode(',',$data);
        	$result = $business->distribute($arr, $adviser, $employee_id);
    	}

        return $result;
    }

    /**
     * 认领客户
     */
    public function recognize(Request $request)
    {
        $business = controller('CustomerPoolBiz','business');
        $param = $request->param();
        $data = $param['customerpools/a'];
        $employee_id = $this->getEmployeeId();//当前登录用户
        $employee = ['recognize_prior_time'=>$this->getRecognizePriorTime(), 'org_id'=>$this->getOrganizationId()];
        if(is_array($data)){
            $result = $business->recognize($data, $employee_id, $employee);
        }else{
            $arr = explode(',',$data);
            $result = $business->recognize($arr, $employee_id, $employee);
        }

        return $result;
    }

    /**
     * 分配已报名客户给顾问
     */
    public function distributePro(Request $request, $adviser)
    {
        $business = controller('CustomerPoolBiz','business');
        $request = Request::instance();
        $param = $request->param();
        $data = $param['users/a'];
        $employee_id = $this->getEmployeeId();//当前登录用户
        $admin_id = $this->getAdminId();
        if(is_array($data)){
            $result = $business->distributePro($data,$adviser,$employee_id,$admin_id);
        }else{
            $arr = explode(',',$data);
            $result = $business->distributePro($arr,$adviser,$employee_id,$admin_id);
        }

        return $result;
    }

    /**
     * 查询该客户的被操作历史记录
     */
    public function history()
    {
    	$request = Request::instance();
    	$id = $request->param('id');
    	
    	/*验证用户数据权限*/
    	$this->checkDataAuth($id);
    	
        $business = controller('CustomerPoolBiz','business');
        $result = $business->history($id);

        return $result;
    }

    /**
     * 查询客户池的已分配客户
     */
    public function signned()
    {   
        $sign = 1;
        $business = controller('CustomerPoolBiz','business');
        $request = Request::instance();
        $real_name = $request->param('real_name');
        $phone = $request->param('phone');
        $from = $request->param('from');
        $page = $request->param('page');
        $pagesize = $request->param('pagesize');
        $result = $business->getAll( $real_name , $phone , $from , $page , $pagesize , $sign);

        return ['list' => $result['list'] , 'count' => $result['count']];
    }

    /**
     * 查询客户池的未分配客户
     */
    public function unsignned()
    {
        $sign = 0;
        $business = controller('CustomerPoolBiz','business');
        $request = Request::instance();
        $real_name = $request->param('real_name');
        $phone = $request->param('phone');
        $from = $request->param('from');
        $page = $request->param('page');
        $pagesize = $request->param('pagesize');
        $result = $business->getAll( $real_name , $phone , $from, $page , $pagesize , $sign);

        return ['list' => $result['list'] , 'count' => $result['count']];
    }

    /**
     * 分配网站客户给顾问
     */
    public function distributeWebUser(Request $request, $adviser)
    {
        $business = controller('CustomerPoolBiz','business');
        $request = Request::instance();
        $param = $request->param();
        $data = $param['users/a'];
        $employee_id = $this->getEmployeeId();//当前登录用户
        $admin_id = $this->getAdminId();
        if(is_array($data)){
            $result = $business->distributeWeb($data,$adviser,$employee_id, $admin_id);
        }else{
            $arr = explode(',',$data);
            $result = $business->distributeWeb($arr,$adviser,$employee_id, $admin_id);
        }

        return $result;
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
    	/*验证用户数据权限*/
    	$this->checkDataAuth($id);
    	
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
        if (isset($param['from'])) {
            $data['from'] = $request->param('from');
        }
        if (isset($param['birthday'])) {
            $data['birthday'] = $request->param('birthday');
        }
        if (isset($param['hometown'])) {
            $data['hometown'] = $request->param('hometown');
        }
        if (isset($param['wechat'])) {
            $data['wechat'] = $request->param('wechat');
        }
        if (isset($param['career'])) {
            $data['career'] = $request->param('career');
        }
        if (isset($param['qq'])) {
            $data['qq'] = $request->param('qq');
        }
        if (isset($param['address'])) {
            $data['address'] = $request->param('address');
        }
        if (isset($param['email'])) {
            $data['email'] = $request->param('email');
        }
        if (isset($param['mobile'])) {
        	$data['mobile_1'] = $request->param('mobile');
        }
        $business = controller('CustomerPoolBiz','business');
        if ($data){
        	$data['admin_id'] = $this->getAdminId();
        	$business->update($id, $data);
        }
        
        return ['id' => $id];
    }

    /**
     * 客户分配历史
     */
	public function assignHistory(){
		$request = Request::instance();
		$id = $request->param('id');
		
		/*验证用户数据权限*/
		$this->checkDataAuth($id);
		
		$business = controller('CandidateBiz','business');
		$result = $business->employeeHistory($id);
		
		return $result;
	}

    /**
     * 客户入职记录
     */
    public function workHistory(){
    	$request = Request::instance();
    	$id = $request->param('id');
    	
    	/*验证用户数据权限*/
    	$this->checkDataAuth($id);
    	
        $business = controller('CustomerPoolBiz','business');
        $result = $business->workHistory($id);
        
        return $result;
    }
	
    /**
     * 验证数据权限
     * @param: $id integer 客户编号
     */
    private function checkDataAuth($id){
    	if ($id) {
    		$flag = false;
    		/*验证客户是否公开*/
    		$biz = controller('CustomerPoolBiz', 'business');
    		if ($biz->isPublic($id)) $flag = true;
    		else {
    			//超级管理员不验证
    			if ($this->isAdministrator()) {
    				$flag = true;
    			} else if ($this->isAdmin()) {
    				//管理员不验证
    				$flag = true;
    			} else {
    				/*验证客户是否对顾问可见*/
    				$business = controller('CandidateBiz', 'business');
    				$employee_id = $this->getEmployeeId();
    				$org_id = $this->getOrganizationId();
    				$arr_id = [$id];
    				if ($business->validateCandidate($employee_id, $arr_id, $org_id)) {
    					$flag = true;
    				}
    			}
    		}
    		$flag == false && abort(403, '403 Forbidden');
    	} else {
    		abort(400, '400 Invalid param id.');
    	}
    }
    
    /**
     * 验证客户是否已经存在
     */
    public function check(){
    	$request = Request::instance();
    	$param = $request->param();
    	$phone = $param['phone'];
    	if (! $phone) abort(400, '400 Invalid phone supplied');
    	
    	$id = isset($param['cpid'])?$param['cpid']:false;
    	$business = controller('CustomerPoolBiz','business');
    	$result = $business->isUnique(['phone'=>$phone], $id);
    	if ($result) $result['owner'] = $business->myCandidate($result['id']);
    	return $result;
    }
    
    /**
     * 释放客户
     */
    public function release(Request $request)
    {
    	$business = controller('CustomerPoolBiz','business');
    	$param = $request->param();
    	$data = $param['customerpools/a'];
    	$employee_id = $this->getEmployeeId();//当前登录用户
    	if(is_array($data)){
    		$result = $business->inPublicPool($data, $employee_id, true);
    	}else{
    		$arr = explode(',',$data);
    		$result = $business->inPublicPool($arr, $employee_id, true);
    	}
    
    	return true;
    }
    
    /**
     * 我的可认领客户
     */
    public function my(){
    	$business = controller('customer/CustomerPoolBiz','business');
    	$request = Request::instance();
    	$param = $request->param();
    	$search = [];
    	if (isset($param['real_name'])) $search['real_name'] = $param['real_name'];
    	if (isset($param['phone'])) $search['phone'] = $param['phone'];
    	if (isset($param['from'])) $search['from'] = $param['from'];
    	if (isset($param['distribute_time_s'])) $search['open_start'] = $param['distribute_time_s'];
    	if (isset($param['distribute_time_e'])) $search['open_end'] = $param['distribute_time_e'];
    	$page = max(1, $param['page']);
    	$pagesize = max(20, $param['pagesize']);
    	$employee = ['id'=>$this->getEmployeeId(), 'recognize_prior_time'=>$this->getRecognizePriorTime(), 'org_id'=>$this->getOrganizationId()];
    	if ($this->isAdministrator() || $this->isAdmin()) {
    		$real_name = isset($search['real_name'])?$search['real_name']:'';
    		$phone = isset($search['phone'])?$search['phone']:'';
    		$from = isset($search['from'])?$search['from']:'';
    		$open_start = isset($search['open_start'])?$search['open_start']:'';
    		$open_end = isset($search['open_end'])?$search['open_end']:'';
    		$result = $business->getOpen($real_name, $phone, $from, $page, $pagesize, $open_start, $open_end);//管理员可见所有客户
    	}else{
    		$result = $business->getRecognize($employee, $search, $page, $pagesize);
    		if ($result['count'] > 0) {
    			foreach($result['list'] as $key=>$item){
    				if ($item['phone']) $result['list'][$key]['phone'] = Format::mobileSecret($item['phone']);
    			}
    		}
    	}
    	return ['list' => $result['list'] , 'count' => $result['count']];
    }
    
    /**
     * 批量删除客户池公海客户
     */
    public function deleteCustomer() {
    	$param = request()->param();
    	$type = isset($param['type'])?$param['type']:0;
    	$business = controller('customer/CustomerPoolBiz', 'business');
    	$flag = $business->deleteOpenCustomer($type);//删除公海客户
    	return $flag?lang('delete_customer_tip', [$flag]):'failure';
    }
    
    /**
     * 删除客户
     */
    public function delete() {
    	$param = request()->param();
    	$id = isset($param['id'])?$param['id']:false;
    	if (! $id) abort(400, '400 Invalid id supplied');
    	$business = controller('customer/CustomerPoolBiz', 'business');
    	$flag = $business->delete([$id]);//删除客户
    	return $flag?lang('delete_customer_tip', [$flag]):'failure';
    }
}