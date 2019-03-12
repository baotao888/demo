<?php
// [接口业务类]

namespace app\index\business;

use think\Log;
use think\Config;

class ApiBiz
{
	/**
	 * 接口权限验证
	 * @param string $action 接口
	 * @param string $access 权限
	 */
	public function checkToken($access, $action){
		$flag = false;
		Config::load(APP_PATH.'index/config.php');
		if (Config::get('crm_key') == $access){
			$flag = true;
		}
		return $flag;
	}
}