<?php
// [短信服务类]
namespace app\sms\service;

use think\Config;

class ShortMessageService
{
	private $config = [];//配置文件
	const SMS_REGISTER_URL = '/message/sms/sendRegisterCode';
	const SMS_LOGIN_URL = '/message/sms/sendLoginCode';
	const SMS_CHANGEMOBILE_URL = '/message/sms/sendChangeMobileCode';

	function __construct(){
		Config::load(APP_PATH.'user/config_crm.php');
		$this->config['api'] = Config::get('yldagong_api');
	}
	
	/**
	 * 发送登录短信验证码
	 * @param string $mobile 手机号
	 */
	public function sendLoginCode($mobile){
		$data = ['mobile'=>$mobile];
		$result = http_post($this->config['api'].self::SMS_LOGIN_URL, $data);
		return $result;
	}
	
	/**
	 * 发送注册短信验证码
	 * @param string $mobile 手机号
	 */
	public function sendRegisterCode($mobile){
		$data = ['mobile'=>$mobile];
		$result = http_post($this->config['api'].self::SMS_REGISTER_URL, $data);
		return $result;
	}

    /**
     * 发送更新手机号短信验证码
     * @param string $mobile 手机号
     */
    public function sendChangeMobileCode($mobile){
        $data = ['mobile'=>$mobile];
        $result = http_post($this->config['api'].self::SMS_CHANGEMOBILE_URL, $data);
        return $result;
    }
}