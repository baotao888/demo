<?php
// [ 市场推广业务类 ]

namespace app\wechat\business;

use think\Cookie;
use think\Log;

use ylcore\Biz;
use app\wechat\bean\MarketCode;

class Market extends Biz
{
	public $codeBean;//推广码对象
	
	public function __construct() {
		$this->codeBean = new MarketCode;
	}
	
	public function decodeCode($code) {
		$str_code = encrypt_param($code, 'DECODE');
		if (strpos($str_code, '-')) {
			list($type, $model, $id) = explode('-', $str_code);
			$this->codeBean->type = $type;
			$this->codeBean->model = $model;
			$this->codeBean->id = $id;
		}
	}
	
	/**
	 * 市场推广验证
	 * @param boolean $decode 是否需要解码 
	 */
	public function getCode($decode = false)
	{
		$code = Cookie::get('ylmcode');
		if ($decode && ! $code) {
			//推广码解密
			$this->decodeCode($code);
			$code = $this->codeBean;
		}
		return $code;
	}
}