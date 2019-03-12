<?php
// [ 职位实体 ]

namespace app\recruit\bean;

class JobBean
{
	public $id;//职位编号
	public $enterprise;//企业名称
	public $region;//地区
	public $salary_intro;//工资说明
	public $validity_period;//有效期
	public $type;//职位类型
	
	public function getType($type) {
		$text = '';
		switch ($type) {
			case 1 : {
				$text = lang('job_type_1');
				break;
			}
			case 2 : {
				$text = lang('job_type_2');
				break;
			}
			case 3 : {
				$text = lang('job_type_3');
				break;
			}
			default : $text = lang('job_type_1');
		}
		return $text;
	}
}