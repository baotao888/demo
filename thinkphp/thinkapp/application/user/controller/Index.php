<?php

namespace app\user\controller;

use think\Controller;
use think\Request;

use app\token\business\Auth;
use app\token\controller\AuthController;
use app\user\business\UserBiz;

class Index extends AuthController
{
	/**
	 * 实现抽象方法
	 * 更新接口需权限验证
	 */
	function isValidate() {
		$flag = true;//默认无需权限验证
		$request = Request::instance();
		if ($request->action() == 'save') $flag = false;//注册无需权限验证
		return $flag;
	}
	
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
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
     * 注册新用户
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        /*获取参数*/
    	$param = $request->param();
    	$mobile = isset($param['mobile']) && is_mobile($param['mobile'])?$param['mobile']:false;
    	$password = isset($param['pwd'])?$param['pwd']:false;
    	$code = isset($param['code'])?$param['code']:false;
    	$realname = isset($param['realname'])?$param['realname']:false;
    	if (! $mobile || ! $password || ! $code || ! $realname) abort(400, '400 Invalid mobile/pwd/code/realname supplied');    	
    	$return = ['uid'=>0, 'token'=>''];
    	/*验证并注册*/
        $biz = new UserBiz();
        $user = $biz->mobileCodeRegister($mobile, $code, $password, $realname);
        $return['uid'] = $user['uid'];        
    	/*生成权限*/
    	if ($user['uid'] > 0) {
    		$auth_business = new Auth();
    		$return['token'] = $auth_business->save($user['uid']);
    	}
    	return $return;
    }

    /**
     * 获取用户信息
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
    	if ($id != $this->uid) {
    		abort(401, '401 Unauthorized');//权限无效
    	}
    	$biz = new UserBiz;
    	$return = $biz->getData($id);
    	return $return;
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
     * 更新个人资料
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
    	if ($id != $this->uid) {
    		abort(401, '401 Unauthorized');//权限无效
    	}
        /*获取参数/参数判断*/
    	$param = $request->param();
        $data = $this->updateParamDispose($param);
        /*更新个人资料*/
        $business = new UserBiz();
    	$return = $business->update($this->uid, $data);
    	return $return;
    }

    /**
     * 参数判断/处理
     *
     * @param $param
     * @return array
     */
    private function updateParamDispose($param) {
        $data = [
            'birth' => "",
            'nickname' => "",
            'gender' => "",
            'real_name' => "",
            'hometown' => ""
        ];
        $birthday = "";
        $nickname = "";
        $gender = "";
        $realname = "";
        $hometown = "";
        if (isset($param['birthday'])) {
            $birthday = $param['birthday'];
            $data['birth'] = $birthday;
            if ($birthday != "") if (! is_date($birthday)) unset($data['birth']);
        } else {
            unset($data['birth']);
        }
        if (isset($param['nickname'])) {
            $nickname = $param['nickname'];
            $data['nickname'] = $nickname;
        } else {
            unset($data['nickname']);
        }
        if (isset($param['gender']) && in_array($param['gender'], ['0', '1'], true)) {
            $gender = $param['gender'];
            $data['gender'] = $gender;
            if ($gender != "") $data['gender'] = intval($gender);
        } else {
            unset($data['gender']);
        }
        if (isset($param['realname'])) {
            $realname = $param['realname'];
            $data['real_name']  = $realname;
        } else {
            unset($data['real_name']);
        }
        if (isset($param['hometown'])) {
            $hometown = $param['hometown'];
            $data['hometown'] = $hometown;
        } else {
            unset($data['hometown']);
        }
        if ($gender == 0) $gender = true;
        if (! $birthday && ! $nickname && ! $gender && ! $realname && ! $hometown) abort(400, '400 Invalid params supplied');
        return $data;
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
