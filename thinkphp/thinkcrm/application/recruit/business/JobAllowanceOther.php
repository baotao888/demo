<?php
// [正式型职位返费]
namespace app\recruit\business;

use ylcore\Biz;

class JobAllowanceOther extends JobAllowance
{
	public function show($allowance) {
		$onduty_type = $this->getOndutyType($allowance['onduty_type']);//在职类型
		$term = $allowance['term'] > 0 ? ($allowance['term'] + $this->onduty_delay) : 0;//在职天数
		$amount = $allowance['amount'];
		$subsidy = $allowance['allowance'];
		$text = $this->showCondition($allowance['conditions']);
		$text .= lang('allowance_text', [$onduty_type, $term, $amount, $subsidy]);
		$ent_wage = $allowance['ent_wage'];
		$cp_wage = $allowance['cp_wage'];
		$text .= lang('allowance_hour_text', [$ent_wage, $cp_wage]);
		return $text;
	}
}