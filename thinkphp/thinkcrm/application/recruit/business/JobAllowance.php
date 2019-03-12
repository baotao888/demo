<?php
namespace app\recruit\business;

use think\Log;

use ylcore\Biz;
use app\recruit\model\CrmJobAllowance;

abstract class JobAllowance extends Biz
{
	private $model;
	
	protected $onduty_delay = 10;//人选在职天数比企业在职天数的延长
	
	function __construct() {
		$this->model = new CrmJobAllowance;
	}
	
	abstract public function show($allowance);
	
	public function getOndutyType($type) {
		$text = '';
		switch ($type) {
			case 0 : {
				$text = lang('onduty_type_0');
				break;
			}
			case 1 : {
				$text = lang('onduty_type_1');
				break;
			}
			default : $text = lang('onduty_type_0');
		}
		return $text;
	}
	
	public function getConditions($conditions) {
		return unserialize($conditions);
	}
	
	/**
	 * 显示返费条件
	 * @param string $conditions
	 */
	public function showCondition($conditions) {
		$return = '';
		$arr_condition = $this->getConditions($conditions);
		if ($arr_condition) {
			switch ($arr_condition['field']) {
				case 'gender' : {
					$field = lang('gender');
				}break;
				case 'age' : {
					$field = lang('age');
				}break;
				default: $field = '';
			}
			switch ($arr_condition['operator']) {
				case -1 : {
					$operator = lang('less');
				}break;
				case 0 : {
					$operator = lang('equal');
				}break;
				case 1 : {
					$operator = lang('greater');
				}break;
				default: $operator = $arr_condition['operator'];
			}
			switch ($arr_condition['value']) {
				case 0 : {
					$value = $arr_condition['field'] == 'gender' ? lang('gender_0') : $arr_condition['value'];
				}break;
				case 1 : {
					$value = $arr_condition['field'] == 'gender' ? lang('gender_1') : $arr_condition['value'];
				}break;
				default: $value = $arr_condition['value'];
			}
			if ($field != '') $return = $field . $operator . $value;
		}
		return $return;
	}
		
	public function joinCondition($data) {
		$arr = ['id'=>$data['id'], 'onduty_type'=>$data['onduty_type'], 'allowance'=>$data['allowance'], 'term'=>$data['term'], 'amount'=>$data['amount']];
		if (is_array($this->getConditions($data['conditions']))) {
			$arr = array_merge(
				$this->getConditions($data['conditions']),
				$arr 
			);
		}
		return $arr;
	}
}