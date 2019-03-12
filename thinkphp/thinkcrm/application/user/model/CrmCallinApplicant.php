<?php
// [呼入用户]
namespace app\user\model;

use think\Model;

class CrmCallinApplicant extends Model
{
	public function setOrigin($origin = []){
		$this->origin = $origin;
	}
}