<?php
namespace app\admin\behavior;

use think\Request;
use think\Cache;
use think\Log;

class WriteLog
{
    public function run(&$params)
    {
    	$flag = true;//默认需要写入日志
    	/*操作过滤*/
    	$request = Request::instance();
    	if ($request->module() == 'admin' && $request->controller() == 'Token'){
    		$flag = false;
    	} else if ($request->module() == 'poster' && $request->controller() == 'Ueditor'){
    		$flag = false;
    	} else if ($request->module() == 'index' && $request->controller() == 'Index'){
    		$flag = false;
    	} else if ($request->module() == 'index' && $request->controller() == 'Home'){
    		$flag = false;
    	} else if ($request->module() == 'index' && $request->controller() == 'Api'){
    		$flag = false;
    	}
        if ($flag) {
        	/*获取用户信息*/
        	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
        	$token_key = $admin_business->getTokenKey($request->header('yl-crm-token'));
        	$admin_information = Cache::get($token_key);
        	/*写入日志*/
        	//超级管理员操作不写入日志
        	if ($admin_business->isAdministratorToken($admin_information) == false) {
        		$business = controller('admin/LogBiz', 'business');
        		$data = ['module'=>$request->module(), 'controller'=>$request->controller(), 'action'=>$request->action()];
        		if ($request->param('id')) $data['record_id'] = $request->param('id');
        		$business->write($admin_information['admin']['id'], $data);
        	}
        }
    }
}