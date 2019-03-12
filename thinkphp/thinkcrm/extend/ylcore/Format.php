<?php
// [数据格式化类]
namespace ylcore;

class Format
{
	/**
	 * 对象列表转换成数组列表
	 */
	static function object2array($list)
	{
		$array = array();
		foreach ($list as $item){
			$array[] = $item;
		}
		return $array;
	}
	
	/**
	 * 转换成jquery datatable格式
	 */
	static function object2datatable($list){
		return ['aaData' => self::object2array($list)];
	}
	
	/**
	 * 手机号码加密
	 */
	static function mobileSecret($mobile) {
		return substr($mobile, 0, 3) . '****' . substr($mobile, 7);
	}
	
	/**
	 * 身份证号码加密
	 */
	static function idcardSecret($idcard) {
		return substr($idcard, 0, -8) . '********';
	}
}