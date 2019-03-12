<?php
namespace app\sms\business;

use think\Config;
use think\Log;

use ylcore\Biz;
use app\sms\model\Sms;

class ShortMessageBiz extends Biz
{
	const SUCCESS = 'OK';
	const LOGIN = 'login';
	const REGISTER = 'register';
	const CHANGEMOBILE = 'change_mobile';
	/**
	 * 验证登录验证码是否正确
	 * @param string $mobile
	 * @param string $code
	 */
	public function checkLoginCode($mobile, $code) {
		$flag = $this->checkCode($mobile, $code, self::LOGIN);
		return $flag;
	}
	
	/**
	 * 登录验证码验证完成
	 * @param string $mobile
	 * @param string $code
	 */
	public function finishLoginCode($mobile, $code) {
		$model = new Sms();
		$model->where('mobile', '=', $mobile)
			->where('type', '=', self::LOGIN)
			->where('code', '=', $code)
			->update(['code'=>'']);
	}
	
	/**
	 * 验证注册验证码是否正确
	 * @param string $mobile
	 * @param string $code
	 */
	public function checkRegisterCode($mobile, $code) {
		$flag = $this->checkCode($mobile, $code, self::REGISTER);
		return $flag;
	}
	
	/**
	 * 注册验证码验证完成
	 * @param string $mobile
	 * @param string $code
	 */
	public function finishRegisterCode($mobile, $code) {
		$model = new Sms();
		$result = $model->where('mobile', '=', $mobile)
			->where('type', '=', self::REGISTER)
			->where('code', '=', $code)
			->update(['code'=>'']);
	}

    /**
     * 验证更改手机号验证码是否正确
     * @param $mobile
     * @param $code
     * @return bool
     */
    public function checkChangeMobileCode($mobile, $code) {
        $flag = $this->checkCode($mobile, $code, self::CHANGEMOBILE);
        return $flag;
    }

    /**
     * 验证更改手机号验证完成
     * @param $mobile
     * @param $code
     */
    public function finishChangeMobileCode($mobile, $code) {
        $model = new Sms();
        $result = $model->where('mobile', '=', $mobile)
            ->where('type', '=', self::CHANGEMOBILE)
            ->where('code', '=', $code)
            ->update(['code'=>'']);
    }

	private function checkCode($mobile, $code, $type) {
		$flag = false;
		if (strlen($code) >= 6) {
			$model = new Sms();
			$result = $model->where('mobile', '=', $mobile)
			->where('type', '=', $type)
			->order('id desc')
			->limit(1)
			->value('code');
			if ($result == $code) $flag = true;
		}
		return $flag;
	}
}