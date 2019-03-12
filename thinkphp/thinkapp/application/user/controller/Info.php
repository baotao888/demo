<?php
// [用户信息]
namespace app\user\controller;

use app\token\controller\AuthController;
use app\user\business\UserBiz;
use app\sms\business\ShortMessageBiz;

class Info extends AuthController
{
	/**
	 * 实现抽象方法
	 * 所有接口需权限验证
	 */
	function isValidate() {
		$flag = true;//所有接口需要验证
		return $flag;
	}
	
    /**
     * 更新头像
     *
     * @return \think\Response
     */
    public function avatar()
    {
    	$param = request()->param();
    	$avatar = isset($param['avatar']) && $param['avatar'] ? $param['avatar'] : false;
    	if (! $avatar) abort(400, '400 Invalid params avatar supplied');

    	$business = new UserBiz();
    	$return = $business->uploadAvatar($this->uid, $avatar);
    	
    	return $return;
    }

    /**
     * 更改密码
     */
    public function pwd() {
        $param = request()->param();
        $pwd = isset($param['pwd']) && strlen($param['pwd']) >= 6 ? $param['pwd'] : false;
        if (! $pwd) abort(400, '400 Invalid params avatar supplied');

        $business = new UserBiz();
        $return = $business->updatePwd($this->uid, $pwd);

        return $return;
    }

    /**
     * 更改手机号
     */
    public function mobile() {
        $param = request()->param();
        $mobile = isset($param['mobile']) && is_mobile($param['mobile']) ? $param['mobile'] : false;
        $code = isset($param['code']) ? $param['code'] : false;
        $biz = new ShortMessageBiz();
        $status = $biz->checkChangeMobileCode($mobile, $code);//验证码验证
        if($status) {
        	$biz->finishChangeMobileCode($mobile, $code);//完成验证
        	//更新手机号
        	$Ubiz = new UserBiz();
        	$return = $Ubiz->changeMobileDo($this->uid, $mobile);
        	return $return;
        }
        return ['uid' => 0, 'mobile' => $mobile];//验证码验证失败
    }
}
