<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;

class Token extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        return ['access'=>'token'];
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
    	$param = $request->param();
        if (! isset($param['username']) || strlen($param['username']) < 4 || ! isset($param['password']) || strlen($param['password']) < 6) {
        	abort(400, '400 Invalid username/password supplied');
        }
        
        /*登录验证*/
        $business = controller('AdminBiz', 'business');//业务对象
        if ($business->isValidate($param['username'], trim($param['password'])) == false){
        	abort(400, '400 Invalid username/password supplied');
        }
        
        /*登录成功*/
        $token = $business->getToken($param['username'], Config::get('token_key'), rand(), Config::get('token_expire'));//用户token
        $token_key = $business->getTokenKey($token);
        if ($business->isAdministrator($param['username'])) $all_info = $business->setAdminToken();//超级管理员
        else $all_info = $business->getInformation($param['username']);
        Cache::set($token_key, $all_info, Config::get('token_expire'));//设置缓存
        
        return ["token" => $token];
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
