<?php

namespace app\token\controller;

use think\Request;
use think\Log;

use app\token\controller\AuthController;
use app\token\business\Auth;
use app\token\business\Secret;
use app\user\business\UserBiz;

class Index extends AuthController
{
	/**
	 * 实现抽象方法
	 */
	function isValidate() {
		$flag = false;//默认无需权限验证
		$request = Request::instance();
		if ($request->action() == 'delete') $flag = true;
		return $flag;
	}
	
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return [];
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
     * 生成新的token
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
    	/*获取参数*/
    	$param = $request->param();
    	$mobile = isset($param['mobile']) && is_mobile($param['mobile'])?$param['mobile']:false;
    	if (! $mobile) abort(400, '400 Invalid mobile supplied');
    	$password = isset($param['password'])?$param['password']:false;
    	$code = isset($param['code'])?$param['code']:false;
    	$return = ['uid'=>0, 'token'=>''];
    	/*登录验证*/
    	if ($password) {
    		/*手机账号/密码登录*/
    		$biz = new UserBiz();
    		$user = $biz->mobileAccountLogin($mobile, $password);
    	} else if($code) {
    		/*短信快捷登录*/
    		$biz = new UserBiz();
    		$user = $biz->mobileCodeLogin($mobile, $code);
    	} else {
    		abort(400, '400 Invalid password/code supplied');
    	}
    	$return['uid'] = $user['uid'];
    	/*生成权限*/
    	if ($user['uid'] > 0) {
    		$auth_business = new Auth();
    		$return['token'] = $auth_business->save($user['uid']);
    	}
    	return $return;
    }

    /**
     * 获取用户授权
     *
     * @param  int  $id 用户编号
     * @return \think\Response
     */
    public function read($id)
    {
    	if ($id <= 0) abort(400, '400 Invalid id supplied');
    	/*获取参数*/
    	$header = request()->header();
    	$secretid = isset($header['secretid'])?$header['secretid']:false;
    	$secretkey = isset($header['secretkey'])?$header['secretkey']:false;
    	if (! $secretid || ! $secretkey) abort(400, '400 Invalid secretid/secretkey supplied');
    	/*账号验证*/
    	$business = new Secret();
    	if (! $business->validate($secretid, $secretkey)) abort(400, '400 Invalid secretid/secretkey supplied');
    	/*生成授权*/
        $auth_business = new Auth();
    	$token = $auth_business->save($id);
    	return ['uid'=>$id, 'token'=>$token]; 
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
        if ($id != $this->uid) {
        	abort(401, '401 Unauthorized');//权限无效
        }
        /*删除授权*/
        $auth_business = new Auth();
        $auth_business->delete($id);
        return true;
    }
}
