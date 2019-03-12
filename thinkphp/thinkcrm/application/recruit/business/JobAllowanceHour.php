<?php
// [小时型职位返费]
namespace app\recruit\business;

use ylcore\Biz;

class JobAllowanceHour extends JobAllowance
{
	public function show($allowance) {
		$ent_wage = $allowance['ent_wage'];
		$cp_wage = $allowance['cp_wage'];
		$text = lang('allowance_hour_text', [$ent_wage, $cp_wage]);
		return $text;
	}
}