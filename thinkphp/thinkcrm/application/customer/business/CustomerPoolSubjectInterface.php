<?php
/** 
 * 客户池广播主题接口 
 * 
 * 负责客户池相关的广播 
 * @author      hans<xiujixin@163.com> 
 * @version     1.0 
 * @since       1.0 
 */

namespace app\customer\business;

interface CustomerPoolSubjectInterface
{
	/**
	 * 增加一个新的观察者对象
	 * @param Observer $observer
	 */
	public function attach(CustomerPoolObserverInterface $observer);
	
	/**
	 * 删除一个已注册过的观察者对象
	 * @param Observer $observer
	 */
	public function detach(CustomerPoolObserverInterface $observer);
	
	/**
	 * 客户录入广播
	 * @param integer $operator 操作人（员工编号）
	 * @param string $mobile 客户手机号码
	 */
	public function emitRecord($operator, $phone);

}