<?php
namespace app\user\business;

use app\user\model\CrmCallinUser;
use app\user\business\Callin;
use app\user\business\PublicCustomerPool;

class CallinRegisterQrcode extends CallinRegister
{
	protected $from = 'qrcode';
	
	/**
	 * @override
	 * 顾问确认用户
	 */
	public function sure($adviser, $ids) {
		/* 更新分配后状态 */
		if ($this->enterCustomerPool($ids, $adviser, $adviser)) {
			parent::sure($adviser, $ids);
			$flag = true;
		} else {
			$flag = false;
		}
		return $flag;
	}
}