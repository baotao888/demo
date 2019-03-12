<?php
/** 
 * 客户池中介类 
 * 
 * 负责呼入用户映射客户池
 * @author      hans<xiujixin@163.com> 
 * @version     1.0 
 * @since       1.0 
 */

namespace app\user\business;

interface CustomerMediatorInterface
{
	/**
	 * 用户进入客户池
	 * @param array $users 用户
	 * @param int $adviser 顾问编号
	 * @param int $operator 操作人
	 */
	function transferCustomer($users, $adviser, $operator);
}