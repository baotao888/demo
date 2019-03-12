<?php
// [呼入用户业务工厂]
namespace app\user\business;

class CallinFactory
{
	static public function instance($type = '', $from = '') {
		$type = $type != '' ? $type : 'Register';//默认注册用户
		$name = $from != '' ? ucwords($type) . ucwords($from) : ucwords($type);//类名
        $class = false !== strpos($type, '\\') ? $type : '\\app\\user\\business\\Callin' . $name;
        return new $class();
	}
}