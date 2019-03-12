<?php
// [职位补贴工厂]
namespace app\recruit\business;

class JobAllowanceFactory
{
	static public function instance($type = '') {
		switch ($type) {
			case 1 : {
				$name = 'Formal';
				break;
			}
			case 2 : {
				$name = 'Hour';
				break;
			}
			case 3 : {
				$name = 'Other';
				break;
			}
			default : {
				$name = 'Formal';
			}
		}
        $class = '\\app\\recruit\\business\\JobAllowance' . $name;
        return new $class();
	}
}