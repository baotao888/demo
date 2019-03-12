<?php
// [客户回收站]
namespace app\recycle\business;

use ylcore\Biz;
use app\recycle\model\CandidateRecycleBin;

class CandidateRecycleBinBiz extends Biz
{
	/**
	 * 获取人选
	 * @param integer $id 客户编号
	 */
	public function get($id) {
		$model = new CandidateRecycleBin();
		$condition = 'cp_id=' . $id;
		$return = $model->where($condition)->select();
		return $return;
	}
}