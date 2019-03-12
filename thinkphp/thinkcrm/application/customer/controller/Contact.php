<?php
namespace app\customer\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;
use app\admin\controller\AdminAuth;
use ylcore\Format;

class Contact extends AdminAuth
{
	/**
     * 显示客户的联系记录列表
     */
    public function index()
    {   
        $business = controller('customer/ContactLogBiz','business');
        $request = Request::instance();
        $customer = $request->param('customer');
        $result = $business->getList($customer);

        return $result;
    }
    
    /**
     * 新增联系记录
     */
    public function save(Request $request)
    {
         /*参数验证*/
        $request = Request::instance();
        $data['cp_id'] = $request->param('cp_id');
        $data['content'] = $request->param('content');
        $data['result'] = $request->param('result');
        
        $business = controller('ContactLogBiz','business');
        $res = $business->save($data, $this->getEmployeeId());
        return ['id' => $res];
    }

    /**
     * 显示联系记录
     */
    public function detail($id)
    {
        //
    }

    /**
     * 显示今日联系记录
     */
    public function today()
    {
        $employee_id = $this->getEmployeeId();//当前登录用户
        $business = controller('ContactLogBiz','business');
        $res = $business->today($employee_id );

        return $res;
    }

    /**
     * 创建拨打计划
     */
    public function addTask(){
    	/*参数验证*/
    	$param = request()->param();
    	if (! isset($param['customer']) || ! isset($param['start_time'])) {
    		abort(400, '400 Invalid customer/start_time supplied');
    	}
    	
    	$employee_id = $this->getEmployeeId();//当前登录用户
   		$biz = controller('admin/TaskBiz', 'business');
   		$info = isset($param['content'])?$param['content']:'';
   		$return = $biz->addCustomerTask($employee_id, $param['customer'], $param['start_time'], $info);

    	return ['id' => $return];
    }
    
    /**
     * 联络日志统计
     */
    public function search(){
    	$business = controller('customer/ContactLogBiz','business');
    	$request = Request::instance();
    	$employee_id = $request->param('employee_id');
    	$org_id = $request->param('org_id');
    	$contact_start = $request->param('contact_start');
    	$contact_end = $request->param('contact_end');
		if ($org_id==''){
			/*管理员查看所有部门*/
			if ($this->isAdministrator() || $this->isAdmin()){
				//do nothing
			}
			/*经理查看下级部门*/
			else if ($this->isManager()){
				$org_id = $this->getOrganizationId();
			}
		}	
    	$result = $business->getAll($employee_id, $org_id, $contact_start, $contact_end);
    	
    	return Format::object2array($result);
    }
    
    /**
     * 显示我的联系记录
     */
    public function my()
    {
    	$request = Request::instance();
    	$contact_start = $request->param('contact_start');
    	$contact_end = $request->param('contact_end');
    	$page = $request->param('page');
    	$pagesize = $request->param('pagesize');
    	if ($page == ''){
    		$page = 1;
    	}
    	if ($pagesize == ''){
    		$pagesize = 20;
    	}
    	$employee_id = $this->getEmployeeId();//当前登录用户
    	
    	$business = controller('customer/ContactLogBiz','business');
    	$result = $business->getMyContact($employee_id, $page, $pagesize, $contact_start, $contact_end);
    	$total = $business->getMyContactTotal($employee_id, $contact_start, $contact_end);
    	return ['list'=>$result, 'total'=>$total];
    }
    
    /**
     * 获取联络记录配置
     */
    public function getSetting()
    {
    	$business = controller('customer/ContactLogBiz','business');
    	$content = $business->getContentSetting();
    	$result = $business->getResultSetting();
    	return ['content'=>$content, 'result'=>$result];
    }
}