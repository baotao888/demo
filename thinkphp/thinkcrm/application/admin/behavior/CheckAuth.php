<?php
namespace app\admin\behavior;

use think\Request;
use think\Cache;
use think\Log;
use think\Config;

class CheckAuth
{
    public function run(&$params)
    {
    	/*身份验证*/
    	$flag = false;//默认需身份验证
    	$request = Request::instance();
    	if ($request->module() == 'admin' && $request->controller() == 'Token'){
    		//登录无需身份验证
    		$flag = true;
    	} elseif ($request->module() == 'poster' && $request->controller() == 'Ueditor'){
    		//ueditor无需身份验证
    		$flag = true;
    	} elseif ($request->module() == 'index' && $request->controller() == 'Api'){
    		//外部调用接口身份验证
    		$service_business = controller('index/ApiBiz', 'business');//业务对象
    		$flag = $service_business->checkToken($request->param('token'), $request->action());
    	} else {
    		//获取参数
    		$header = $request->header();
    		if (!isset($header['yl-crm-token']) || $header['yl-crm-token']==''){
    			$flag = false;
    		} else {
    			//获取缓存
    			$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    			$token_key = $admin_business->getTokenKey($header['yl-crm-token']);
    			$admin_information = Cache::get($token_key);
    			if ($admin_information){
    				$flag = true;
    			}
    		}
    	}
        if ($flag == false) abort(401, '401 Unauthorized');
        
        /*权限验证*/
        $privilege = false;//默认需权限验证        
        if ($request->module() == 'admin' && $request->controller() == 'Token'){
        	//登录无需权限验证
        	$privilege = true;
        } elseif ($request->module() == 'poster' && $request->controller() == 'Ueditor'){
        	//ueditor无需权限验证
        	$privilege = true;
        } elseif ($request->module() == 'index'
        		&& ($request->controller() == 'Index' || $request->controller() == '')){
        	//个人主页无需权限验证
        	$privilege = true;
        } elseif ($request->module() == 'index' && $request->controller() == 'Api'){
        	//外部调用接口无需权限验证
        	$privilege = true;
        } else {
        	//获取缓存
        	if (! isset($admin_information)) {
        		$admin_business = controller('admin/AdminBiz', 'business');//业务对象
        		$header = $request->header();
        		$token_key = $admin_business->getTokenKey($header['yl-crm-token']);
        		$admin_information = Cache::get($token_key);
        	}
        	if (! $admin_information){
        		//没有设置权限
        		$privilege = false;
        	} elseif ($admin_business->isAdminToken($admin_information)) {
        		//管理员
        		$privilege = true;
        	} elseif (! is_array($admin_information['privileges'])) {
        		//权限格式不对
        		$privilege = false;
        	} elseif (! in_array(['module'=>strtolower($request->module()), 'controller'=>strtolower($request->controller()), 'action'=>strtolower($request->action())], $admin_information['privileges'])){
        		//权限不存在
        		$privilege = false;
        	} else {
        		//验证通过
        		$privilege = true;
       		}
        }
        if ($privilege == false) abort(403, '403 Forbidden');
    }
}