<?php
namespace app\recruit\business;

use think\Log;

use ylcore\Biz;
use app\recruit\model\CrmJobAllowance;

class JobAllowanceMediator extends Biz
{
	private $model;
	
	function __construct() {
		$this->model = new CrmJobAllowance;
	}
	
	public function showJobAllowance($job) {
		$text = [];
		$arr = $this->getJobAllowances($job);
		if ($arr) {
			foreach ($arr as $allowance) {
				$biz = JobAllowanceFactory::instance($allowance['type']);
				$text[] = $biz->show($allowance);
			}
		}
		return $text;
	}
	
	public function getJobAllowances($job) {
		$list = $this->model->where('job_id', $job)->select();
		return $list;
	}
	
	public function addJobAllowances($job, $data, $type, $ent_wage, $cp_wage) {
		foreach ($data as $allowance) {
			$this->model->create([
				'job_id' => $job,
				'type' => isset($allowance['type']) ? $allowance['type'] : $type,
				'amount' => $allowance['amount'],
				'term' => $allowance['term'],
				'allowance' => $allowance['allowance'],
				'conditions' => serialize($allowance['conditions']),
				'ent_wage' => isset($allowance['ent_wage']) ? $allowance['ent_wage'] : $ent_wage,
				'cp_wage' => isset($allowance['cp_wage']) ? $allowance['cp_wage'] : $cp_wage,
				'onduty_type' => $allowance['onduty_type']
			]);
		}
	}
	
	public function formatJobAllowance($job) {
		$return = [];
		$arr = $this->getJobAllowances($job);
		if ($arr) {
			foreach ($arr as $allowance) {
				$biz = JobAllowanceFactory::instance($allowance['type']);
				$return['type'] = $allowance['type'];
				$return['ent_wage'] = $allowance['ent_wage'];
				$return['cp_wage'] = $allowance['cp_wage'];
				$return['conditions'][] = $biz->joinCondition($allowance);
			}
		}
		return $return;
	}
	
	/**
	 * 更新职位返费信息
	 */
	public function updateAllowance($job, $datas, $type, $ent_wage, $cp_wage) {
		$arr_new = [];//新增条件
		$arr_delete = [];//删除条件
		foreach ($datas as $data) {
			if (! isset($data['id']) || ! $data['id']) {
				$arr_new[] = $data;
				continue;
			} else {
				$arr_delete[] = $data['id'];
			}
			$update = array();
			$update['type'] = $type;
			$update['ent_wage'] = $ent_wage;
			$update['cp_wage'] = $cp_wage;
			if (isset($data['amount'])){
				$update['amount'] = $data['amount'];
			}
			if (isset($data['term'])) {
				$update['term'] = $data['term'];
			}
			if (isset($data['conditions']) && is_array($data['conditions'])) {
				$update['conditions'] = serialize($data['conditions']);
			}
			if (isset($data['allowance'])){
				$update['allowance'] = $data['allowance'];
			}
			if (isset($data['onduty_type'])){
				$update['onduty_type'] = $data['onduty_type'];
			}
			$this->model->save($update, ['id' => $data['id']]);
		}
		if ($arr_new) {
			$this->addJobAllowances($job, $arr_new, $type, $ent_wage, $cp_wage);
		}
		if ($arr_delete) {
			$this->deleteAllowance($job, $arr_delete);
		}
	}
	
	/**
	 * 删除职位返费
	 */
	public function deleteAllowance($job, $ids, $not = true) {
		$this->model->where("`job_id`=$job AND `id` NOT IN (" . implode(',', $ids) . ")")->delete();
	}
	
	/**
	 * 计算人选补贴金额
	 */
	public function calculateAllowance($user, $allowances) {
		$return = [];
		$amount = 0; // 返费金额
		$allowance = 0; // 补贴金额
		$ent_day = 0; // 企业在职天数
		$onduty_type = 0;//在职类型
		if (! empty($allowances)) {
			if ($allowances['type'] == 1 || $allowances['type'] == 3) {
				foreach ($allowances['conditions'] as $condition) {
					if ($condition['field'] == 'gender') {
						if ($condition['value'] == $user['gender']) {
							$amount += $condition['amount'];
							$allowance += $condition['allowance'];
						}
					} else if ($condition['field'] == 'age') {
						if ($user['birthday']) {
							$birth_year = substr(trim(($user['birthday'])),0,4);
							$now_year = date('Y',time());
							$age = $now_year - intval($birth_year);
							if ($condition['operator'] == 0) {
								// 等于
								if ($age == $condition['value']) {
									$amount += $condition['amount'];
									$allowance += $condition['allowance'];
								}
							} elseif ($condition['operator'] == -1) {
								// 小于
								if ($age < $condition['value']) {
									$amount += $condition['amount'];
									$allowance += $condition['allowance'];
								}
							} elseif ($condition['operator'] == 1) {
								// 大于
								if ($age > $condition['value']) {
									$amount += $condition['amount'];
									$allowance += $condition['allowance'];
								}
							}
						}
					} else {
						$amount += $condition['amount'];
						$allowance += $condition['allowance'];
					}
					if ($condition['term'] > $ent_day) $ent_day = $condition['term'];
					$onduty_type = $condition['onduty_type'];
				}
			}
		}
		$return['type'] = isset($allowances['type']) ? $allowances['type'] : 1;
		$return['ent_wage'] = isset($allowances['ent_wage']) ? $allowances['ent_wage'] : 0;
		$return['cp_wage'] = isset($allowances['cp_wage']) ? $allowances['cp_wage'] : 0;
		$return['amount'] = $amount;
		$return['allowance'] = $allowance;
		$return['onduty_type'] = $onduty_type;
		$return['term'] = $ent_day;
		return $return;
	}
}