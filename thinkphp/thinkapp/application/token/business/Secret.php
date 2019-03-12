<?php
//[身份权限验证业务类]
namespace app\token\business;

use think\Config;

class Secret
{
	public function validate($id, $key) {
		return $id == Config::get('secret_id') && $key == Config::get('secret_key');
	}
}