<?php
namespace app\message\business;

use ylcore\Biz;
use think\Config;
use app\message\service\SmsService;
use think\Log;

class Sms extends Biz
{
	const SUCCESS = 'OK';
	const LOGIN = 'login';
	const REGISTER = 'register';
	const CHANGEMOBILE = 'change_mobile';

	/**
	 * 发送注册短信验证码
	 */
	public function sendRegisterCode($mobile){
		$return = $this->verify($mobile);//验证短信发送次数
		if ( $return > 0 ) {
			$code = $this->randomCode();
			$service = new SmsService(Config::get('aliyun.access_key_id'), Config::get('aliyun.access_key_secret'));
			$response = $service->sendSms(
					Config::get('aliyun_sms.signature'), // 短信签名
					Config::get('aliyun_sms.register_tpl_id'), // 短信模板编号
					$mobile, // 短信接收者
					["code"=>$code]
			);
			if ($response->Code == self::SUCCESS){
				$this->saveCode($mobile, self::REGISTER, ['code'=>$code]);
				$return = 1;
			} else {
				Log::record($response);
				$return = 0;
			}
		}
		return $return;
	}
	
	/**
	 * 发送登录短信验证码
	 */
	public function sendLoginCode($mobile){
		$return = $this->verify($mobile);//验证短信发送次数
		if ( $return > 0 ) {
			$code = $this->randomCode();
			$service = new SmsService(Config::get('aliyun.access_key_id'), Config::get('aliyun.access_key_secret'));
			$response = $service->sendSms(
					Config::get('aliyun_sms.signature'), // 短信签名
					Config::get('aliyun_sms.login_tpl_id'), // 短信模板编号
					$mobile, // 短信接收者
					["code"=>$code]
			);
			if ($response->Code == self::SUCCESS){
				$this->saveCode($mobile, self::LOGIN, ['code'=>$code]);
				$return = 1;
			} else {
				$return = 0;
			}	
		}
		return $return;
	}

    /**
     * 发送更改手机号验证码
     */
    public function sendChangeMobileCode($mobile){
        $return = $this->verify($mobile);//验证短信发送次数
        if ( $return > 0 ) {
            $code = $this->randomCode();
            $service = new SmsService(Config::get('aliyun.access_key_id'), Config::get('aliyun.access_key_secret'));
            $response = $service->sendSms(
                Config::get('aliyun_sms.signature'), // 短信签名
                Config::get('aliyun_sms.change_mobile_tpl_id'), // 短信模板编号
                $mobile, // 短信接收者
                ["code"=>$code]
            );
            if ($response->Code == self::SUCCESS){
                $this->saveCode($mobile, self::CHANGEMOBILE, ['code'=>$code]);
                $return = 1;
            } else {
                $return = 0;
            }
        }
        return $return;
    }

	/**
	 * 随机生成验证码
	 * @param int $length
	 */
	private function randomCode($length = 6){
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= mt_rand(0, 9);
		}
		return $str;
	}
	
	private function saveCode($mobile, $tpl, $data = []){
		$model = controller('sms', 'model');
		$model->data([
			'mobile' => $mobile,
			'dateline' => time(),
			'message' => $this->getMessage($tpl, $data),
			'code' => $data['code'],
			'data' => $data?serialize($data):'',
			'type' => $tpl	
		]);
		$model->save();
	}
	
	/**
	 * 获取短息内容
	 * @param string $type
	 */
	private function getMessage($type, $data = []){
		$return = '';
		switch($type){
			case self::LOGIN : 
				$return = lang('sms_login_template', [$data['code']]);
				break;
			case self::REGISTER :
				$return = lang('sms_register_template', [$data['code']]);
				break;	
			default : ;	
		}
		return $return;
	}
	
	/**
	 * 短信发送验证
	 * @return int -1,最小时间受限;-2;最大数量受限
	 */
	private function verify($mobile){
		$return = 1;
		$model = controller('sms', 'model');
		/*最小发送时间间隔验证*/
		$time_floor = time() - Config::get('sms_condition.min_seconds');
		$time_floor_count = $model->where('mobile', '=', $mobile)->where('dateline', '>', $time_floor)->count();
		if ($time_floor_count > 0){
			$return = -1;
		} else {
			$day_count = Config::get('sms_condition.max_day_count');
			$day_floor = time() - 24 * 60 * 60;
			$day_count_result = $model->where('mobile', '=', $mobile)->where('dateline', '>', $day_floor)->count();
			if ($day_count_result >= $day_count){
				$return = -2;
			}
		}
		return $return;
	}
	
	/**
	 * 验证注册验证码是否正确
	 * @param string $mobile
	 * @param string $code
	 */
	public function checkRegisterCode($mobile, $code){
		$flag = false;
		if (strlen($code) >= 6) {
			$model = controller('message/sms', 'model');
			$result = $model->where('mobile', '=', $mobile)->where('type', '=', self::REGISTER)->where('code', '=', $code)->order('id desc')->value('id');
			if ($result != null) $flag = true;
		}
		return $flag;
	}
	
	/**
	 * 注册验证码验证完成
	 * @param string $mobile
	 * @param string $code
	 */
	public function finishRegisterCode($mobile, $code){
		$model = controller('message/sms', 'model');
		$result = $model->where('mobile', '=', $mobile)->where('type', '=', self::REGISTER)->where('code', '=', $code)->update(['code'=>'']);
	}
	
	/**
	 * 验证登录验证码是否正确
	 * @param string $mobile
	 * @param string $code
	 */
	public function checkLoginCode($mobile, $code){
		$flag = false;
		if (strlen($code) >= 6) {
			$model = controller('message/sms', 'model');
			$result = $model->where('mobile', '=', $mobile)->where('type', '=', self::LOGIN)->where('code', '=', $code)->order('id desc')->value('id');
			if ($result != null) $flag = true;
		}
		return $flag;
	}
	
	/**
	 * 登录验证码验证完成
	 * @param string $mobile
	 * @param string $code
	 */
	public function finishLoginCode($mobile, $code){
		$model = controller('message/sms', 'model');
		$result = $model->where('mobile', '=', $mobile)->where('type', '=', self::LOGIN)->where('code', '=', $code)->update(['code'=>'']);
	}
}