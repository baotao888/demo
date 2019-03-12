<?php

namespace app\customer\model;

use think\Model;

class CustomerPool extends Model
{
	/*客户副表信息*/
	public function detail()
	{
		return $this->hasOne('app\customer\model\CustomerPoolData', 'id', 'id')->field('wechat, qq, address, email');
	}
	
	/**
	 * 状态获取器
	 * @param int $value
	 * @return Ambigous <string>
	 */
	public function getFromAttr($value)
	{
		$status = ['web'=>'网站','wechat'=>'微信','backend'=>'录入','import'=>'导入','invite'=>'推荐','signup'=>'报名'];
		return isset($status[$value])?$status[$value]:$value;
	}
	
	/**
	 * 客户池状态信息
	 */
	public function poolStatus(){
		return $this->hasOne('app\customer\model\CustomerPoolStatus', 'id', 'id')->field('is_open,is_assign');
	}
}