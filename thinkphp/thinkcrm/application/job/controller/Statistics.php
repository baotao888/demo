<?php
namespace app\job\controller;

class Statistics
{
	/**
	 * 更新职位报名数据
	 */
	public function updateSignup() {		
		$controller = controller('JobBiz','business');
		$controller->updateSignup();
		return 'success';
	}
}