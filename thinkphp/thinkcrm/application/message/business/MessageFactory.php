<?php
// [消息业务工厂]
namespace app\message\business;

class MessageFactory
{
	static public function instance($type = '', $options = []) {
		$type = $type != '' ? $type : 'Crm';
        $class = false !== strpos($type, '\\') ? $type : '\\app\\message\\business\\Message' . ucwords($type);
        return new $class($options);
	}
}