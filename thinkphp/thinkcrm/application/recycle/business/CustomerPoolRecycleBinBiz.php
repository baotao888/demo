<?php
// [客户回收站]
namespace app\recycle\business;

use think\Db;
use think\Config;
use think\Log;
use ylcore\Biz;
use app\recycle\model\CustomerPoolRecycleBin;

class CustomerPoolRecycleBinBiz extends Biz
{
	/**
	 * 获取所有回收的客户
	 * @param int $page
	 * @param int $pagesize
	 * @param string $search
	 */
	public function getAll($page=1, $pagesize=50, $search='') {
		if ($search != '') {
			if (is_mobile($search)) {
				$condition = "`phone` = '$search' OR `mobile_1` = '$search'";
			} elseif (is_numeric($search)) {
				$condition = "`phone` LIKE '$search%' OR `mobile_1` LIKE '$search%'";
			} else {
				$condition = "`real_name` LIKE '$search%'";
			}	
		} else {
			$condition = '`id`>0';			
		}
		$model = new CustomerPoolRecycleBin();
		return $model->page($page, $pagesize)->where($condition)->select();
	}
	
	public function getCount($search) {
		$model = new CustomerPoolRecycleBin();
		$condition = '`id`>0';
		return $model->where($condition)->count();
	}
	
	/**
	 * 客户还原
	 */
	public function restore($customers) {
		$where = "`id` IN (" . implode(',', $customers) . ")";
		/*1,还原客户池*/
		$sql1 = "INSERT INTO yl_customer_pool SELECT * FROM yl_customer_pool_recycle_bin WHERE $where";
		$flag1 = Db::execute($sql1);
		if ($flag1 == count($customers)) {
			Db::execute("DELETE FROM yl_customer_pool_recycle_bin WHERE $where");
		}
		/*2,还原客户信息*/
		$sql2 = "INSERT INTO yl_customer_pool_data SELECT * FROM yl_customer_pool_data_recycle_bin WHERE $where";
		$flag2 = Db::execute($sql2);
		if ($flag2 == count($customers)) {
			Db::execute("DELETE FROM yl_customer_pool_data_recycle_bin WHERE $where");
		}
		/*3,还原客户状态*/
		$sql3 = "INSERT INTO yl_customer_pool_status SELECT * FROM yl_customer_pool_status_recycle_bin WHERE $where";
		$flag3 = Db::execute($sql3);
		if ($flag3 == count($customers)) {
			Db::execute("DELETE FROM yl_customer_pool_status_recycle_bin WHERE $where");
		}
		/*4,还原联系记录*/
		/*$where = "`cp_id` IN (" . implode(',', $customers) . ")";
		$sql4 = "INSERT INTO yl_contact_log SELECT * FROM yl_contact_log_recycle_bin WHERE $where";
		Db::execute($sql4);
		if ($sql4) {
			Db::execute("DELETE FROM yl_contact_log WHERE $where");
		}*/
		return min($flag1, $flag2, $flag3);
	}
	
	/**
	 * 客户删除
	 */
	public function delete($customers) {
		$where = "`id` IN (" . implode(',', $customers) . ")";
		/*1,删除客户池*/
		$flag1 = Db::execute("DELETE FROM yl_customer_pool_recycle_bin WHERE $where");
		/*2,删除客户信息*/
		$flag2 = Db::execute("DELETE FROM yl_customer_pool_data_recycle_bin WHERE $where");
		/*3,删除客户状态*/
		$flag3 = Db::execute("DELETE FROM yl_customer_pool_status_recycle_bin WHERE $where");
		/*4,删除联系记录*/
		$where = "`cp_id` IN (" . implode(',', $customers) . ")";
		Db::execute("DELETE FROM yl_contact_log WHERE $where");
		return min($flag1, $flag2, $flag3);
	}
}