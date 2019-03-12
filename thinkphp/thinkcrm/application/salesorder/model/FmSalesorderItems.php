<?php

namespace app\salesorder\model;

use think\Model;

class FmSalesorderItems extends Model
{
	/**
	 * 订单信息
	 */
	public function sales()
	{
		return $this->hasOne('app\salesorder\model\FmSalesorder', 'id', 'salesorder')->field('*');
	}
	
	/**
	 * 订单状态
	 */
	public function status()
	{
		return $this->hasOne('app\salesorder\model\FmSalesorderStatus', 'salesorder', 'salesorder')->field('*');
	}
}