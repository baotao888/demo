<?php
// [ 市场推广业务类 ]

namespace app\customer\business;

use think\Cookie;
use think\Log;

use ylcore\Biz;
use app\customer\bean\MarketBean;

class Market extends Biz
{
	public $codeBean;//推广码对象
	
	public function __construct() {
		$this->codeBean = new MarketBean;
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
	 * 顾问二维码推广 
	 */
	public function adviserQrcode($adviser)
	{
		return encrypt_param('qrcode-adviser-' . $adviser);
	}
	
	/**
	 * 是否为员工推广
	 */
	public function isAdviser() {
		return $this->codeBean->model == 'adviser';
	}
	
	/**
	 * 是否为员工本人的推广
	 * @param int $id 员工编号	
	 */
	public function isAdviserId($id) {
		return $this->codeBean->id == $id;
	}
	
	public function getId() {
		return $this->codeBean->id;
	}
	
	public function getType() {
		return $this->codeBean->type;
	}
}