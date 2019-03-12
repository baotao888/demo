<?php
/** 
 * 客户池观察者接口 
 * 
 * 观察客户池
 * @author      hans<xiujixin@163.com> 
 * @version     1.0 
 * @since       1.0 
 */

namespace app\customer\business;

interface CustomerPoolObserverInterface
{
	/**
	 * 匹配客户池客户
	 * @param integer $adviser_id 顾问（员工编号）
	 * @param string $mobile 客户电话
	 */
	public function match($adviser_id, $mobile);

}