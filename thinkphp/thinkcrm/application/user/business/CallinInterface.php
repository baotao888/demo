<?php
// [ 呼入用户接口 ]

namespace app\user\business;

interface CallinInterface
{
	/**
	 * 显示顾问呼入用户列表
	 * @param integer $adviser 顾问（员工编号）
	 * @param boolean $sure 是否已确认
	 * @param string $search 搜索关键字
	 * @param number $page 分页
	 * @param number $pagesize 每页 
	 * @return array [count, list]
	 */
	function myList($adviser, $sure, $search, $page, $pagesize);
	
	/**
	 * 顾问确认用户
	 * @param integer $adviser 顾问（员工编号）
	 * @param array $ids 用户编号
	 * @return boolean
	 */
	function sure($adviser, $ids);
	
	/**
	 * 经理分配用户
	 * @param array $ids 用户编号
	 * @param integer $adviser 顾问（员工编号）
	 * @param integer $manager 经理（员工编号）
	 * @return boolean
	 */
	function assign($ids, $adviser, $manager);
	
	/**
	 * 显示所有顾问名下用户列表
	 * @param array $search 检索条件
	 * @param number $page 分页
	 * @param number $pagesize 每页
	 * @return array [count, list]
	 */
	function advisersList($search, $page, $pagesize);
	
	/**
	 * 显示所有其他用户列表
	 * 包括经理分配的用户和未分配的用户
	 * @param array $search 检索条件
	 * @param number $page 分页
	 * @param number $pagesize 每页
	 * @return array [count, list]
	 */
	function otherList($search, $page, $pagesize);

}