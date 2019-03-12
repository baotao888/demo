<?php
//[接口权限验证业务类]
namespace app\token\business;

use think\Config;

use app\token\model\AppAuth;

class Auth
{
	static $certificate = [];
	
	/**
	 * 获取权限
	 * @param string $token
	 * @return object
	 */
	public function getCertificate($token) {
		if (isset(self::$certificate[$token])) {
			$certificate = self::$certificate[$token];
		} else {
			$model = new AppAuth();
			$certificate = $model->where('token', $token)->find();
			self::$certificate[$token] = $certificate;
		}
		return $certificate;
	}
	
	/**
	 * 验证权限
	 * @param string $token
	 * @return int 正整数=>用户编号;0=>权限过期;-1=>权限无效
	 */
	public function verify($token) {
		$flag = -1;
		$certificate = $this->getCertificate($token);
		if ($certificate) {
			Config::load(APP_PATH . 'token/config.php');
			if ($certificate['base_time'] + Config::get('token_expired') < time()) {
				$flag = 0;
			} else {
				$flag = $certificate['user_id'];//权限有效
				if (time() - $certificate['base_time'] > Config::get('token_refresh')) {
					/*刷新权限*/
					$this->refresh($certificate['user_id']);	
				}
			}
		}
		return $flag;
	}
	
	/**
	 * 保存权限
	 */
	public function save($uid) {
		$str_token = $this->generateToken();
		$model = new AppAuth();
		$model->insert(['user_id'=>$uid, 'base_time'=>time(), 'token'=>$str_token], true);//replace into
		return $str_token;
	}
	
	private function generateToken() {
		return rand_string(80, true);
	}
	
	/**
	 * 刷新权限时间
	 */
	public function refresh($uid) {
		$model = new AppAuth();
		$model->where('user_id', $uid)->update(['base_time'=>time()]);
	}
	
	/**
	 * 删除APP授权
	 * @param unknown $id
	 */
	public function delete($id) {
		$model = new AppAuth();
		$model->where('user_id', $id)->delete();
	}
}