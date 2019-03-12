<?php
// [正式型职位返费]
namespace app\recruit\business;

use think\Log;

use ylcore\Biz;

class JobAllowanceFormal extends JobAllowance
{	
	public function show($allowance) {
		$onduty_type = $this->getOndutyType($allowance['onduty_type']);//在职类型
		$term = $allowance['term'] + $this->onduty_delay;//在职天数
		$amount = $allowance['amount'];
		$subsidy = $allowance['allowance'];
		$text = $this->showCondition($allowance['conditions']);
		$text .= lang('allowance_text', [$onduty_type, $term, $amount, $subsidy]);
		return $text;
	}
	
	
}