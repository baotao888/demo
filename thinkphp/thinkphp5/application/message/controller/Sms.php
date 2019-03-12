<?php
// [短信控制器]

namespace app\message\controller;

use think\Config;
use think\Log;


class Sms
{
	/**
	 * 发送登录短信验证码
	 */
	public function sendLoginCode(){
		$param = request()->param();
		if (!isset($param['mobile']) || ! is_mobile($param['mobile'])) {
			return json(['status'=>0, 'message'=>lang('mobile_error')]);
		}
		$mobile = $param['mobile'];
		$biz = controller('sms', 'business');
		$code = $biz->sendLoginCode($mobile);
		if ($code == 1)  $message = lang('send_success');
		else if ($code == -1) $message = lang('min_time_send_error', [Config::get('sms_condition.min_seconds')]);
		else if ($code == -2) $message = lang('max_day_send_error', [Config::get('sms_condition.max_day_count')]);
		else $message = lang('send_failure');
		return json(['status'=>$code, 'message'=>$message]);
	}
	
	/**
	 * 发送注册短信验证码
	 */
	public function sendRegisterCode(){
		$param = request()->param();
		if (!isset($param['mobile']) || ! is_mobile($param['mobile'])) {
			return json(['status'=>0, 'message'=>lang('mobile_error')]);
		}
		$mobile = $param['mobile'];
		$biz = controller('sms', 'business');
		$code = $biz->sendRegisterCode($mobile);
		if ($code == 1)  $message = lang('send_success');
		else if ($code == -1) $message = lang('min_time_send_error', [Config::get('sms_condition.min_seconds')]);
		else if ($code == -2) $message = lang('max_day_send_error', [Config::get('sms_condition.max_day_count')]);
		else $message = lang('send_failure');
		return json(['status'=>$code, 'message'=>$message]);
	}
	
	/**
	 * 验证短信验证码是否正确
	 */
	public function checkRegisterCode(){
		$param = request()->param();
		$mobile = $param['mobile'];
		$code = $param['sms_code'];
		$biz = controller('sms', 'business');
		$status = $biz->checkRegisterCode($mobile, $code);
		return json(['valid'=>$status]);
	}
	
	/**
	 * 验证登录验证码是否正确
	 */
	public function checkLoginCode(){
		$param = request()->param();
		$mobile = $param['mobile'];
		$code = $param['sms_code'];
		$biz = controller('sms', 'business');
		$status = $biz->checkLoginCode($mobile, $code);
		return json(['valid'=>$status]);
	}

    /**
     * 发送更换手机号验证码
     */
    public function sendChangeMobileCode() {
        $param = request()->param();
        if (!isset($param['mobile']) || ! is_mobile($param['mobile'])) {
            return json(['status'=>0, 'message'=>lang('mobile_error')]);
        }
        $mobile = $param['mobile'];
        $biz = controller('sms', 'business');
        $code = $biz->sendChangeMobileCode($mobile);
        if ($code == 1)  $message = lang('send_success');
        else if ($code == -1) $message = lang('min_time_send_error', [Config::get('sms_condition.min_seconds')]);
        else if ($code == -2) $message = lang('max_day_send_error', [Config::get('sms_condition.max_day_count')]);
        else $message = lang('send_failure');
        return json(['status'=>$code, 'message'=>$message]);
    }
}