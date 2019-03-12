<?php
namespace app\recruit\business;

use think\Log;

use ylcore\Biz;
use app\recruit\model\CrmJob;
use app\recruit\model\CrmJobAllowance;
use app\job\model\Enterprise;
use app\recruit\model\CrmJobLog;
use app\recruit\bean\JobBean;
use app\recruit\model\CrmJobProvider;

class Job extends Biz
{
	private $model;
	private $enterpriseModel;
	private $logModel;
	private $bean;
	private $jobAllowance;
	private $labourModel;
	
	function __construct() {
		$this->model = new CrmJob;
	}
	
	/**
	 * 依赖注入
	 */
	protected function dependencyInjection() {
		if (empty($this->enterpriseModel)) $this->enterpriseModel = new Enterprise;
		if (empty($this->logModel)) $this->logModel = new CrmJobLog;
		if (empty($this->bean)) $this->bean = new JobBean;
		if (empty($this->jobAllowanceMediator)) $this->jobAllowanceMediator = new JobAllowanceMediator;
		if (empty($this->labourModel)) $this->labourModel = new CrmJobProvider;
	}
	
	/**
	 * 职位检索
	 * @param array $filter 检索条件
	 * @param integer $page
	 * @param integer $pagesize
	 */
	public function search($date) {
		if (! $date) $date = date('Y-m-d', time());//默认当天
		$where = "`validity_period`='$date'";
		//提成列表
		$list = $this->model->alias('job')
			->join('Enterprise enterprise', 'enterprise.id=job.enterprise_id')
			->field('job.*, enterprise.enterprise_name')
			->where($where)
			->select();
		$return = [];
		if ($list) {
			$this->dependencyInjection();
			foreach ($list as $item) {
				$data = [
					'id' => $item['id'],
					'enterprise_id' => $item['enterprise_id'],
					'enterprise_name' => $item['enterprise_name'],
					'region' => $item['region'],
					'salary_intro' => $item['salary_intro'],
					'validity_period' => $item['validity_period'],
					'type' => $this->bean->getType($item['type']),
					'allowance' => $this->jobAllowanceMediator->showJobAllowance($item['id'])		
				];
				$return[] = $data;
			}
		}
		return $return;
	}
	
	/**
	 * 添加招聘职位
	 * @param array $data 数据
	 * @param integer $operator 操作人
	 */
	public function add($data, $operator) {
		$this->model->data([
			'enterprise_id'  =>  $data['enterprise_id'],
			'region' => isset($data['region']) ? $data['region'] : '',
			'salary_intro' => isset($data['salary_intro']) ? $data['salary_intro'] : '',
			'validity_period' => $data['validity_period'],
			'list_order' => isset($data['list_order']) ? $data['list_order'] : 0,
			'type' => isset($data['type']) ? $data['type'] : ''									
		]);
		$this->model->save();//保存主表
		$this->dependencyInjection();
		$this->initJob($this->model->id, $operator, $data);
		if ($this->model->enterprise_id == 0 && isset($data['enterprise_name']) && $data['enterprise_name'] != ''){
			$enterprise_id = $this->initEnterprise($data['enterprise_name']);
			//职位关联企业
			$this->model->save(['enterprise_id' => $enterprise_id], ['id' => $this->model->id]);
		}
		return $this->model->id;
	}
	
	/**
	 * 初始化职位信息
	 */
	public function initJob($id, $operator, $data) {
		/*添加返费*/
		if (isset($data['allowance']) && $data['allowance'] && is_array($data['allowance'])) {
			$this->jobAllowanceMediator->addJobAllowances($id, $data['allowance'], $data['allowance_type'], $data['ent_wage'], $data['cp_wage']);
		}
		/*添加劳务公司*/
		if (isset($data['labour']) && $data['labour']) {
			$this->labourModel->create(['job_id'=>$id, 'labour_service_id'=>$data['labour']]);
		}
		/*添加日志*/
		$this->logModel->save([
			'job_id' => $id,
			'admin_id' => $operator,
			'create_time' => time(),
			'type' => 'create',
			'content' => serialize($data)	
		]);
	}
	
	/**
	 * 初始化企业
	 * @param string $enterprise_name
	 */
	public function initEnterprise($enterprise_name) {
		$data = ['enterprise_name' => $enterprise_name];
		$this->enterpriseModel->data($data);
		$this->enterpriseModel->save();
		return $this->enterpriseModel->id;
	}

	/**
	 * 职位详情
	 * @param integer $id
	 */
	public function get($id) {
		$recruit = $this->model->get($id);
		$this->dependencyInjection();
		if ($recruit) $recruit['allowance'] = $this->jobAllowanceMediator->formatJobAllowance($id);
		$recruit['labour'] = $recruit->provider ? $recruit->provider->labour_service_id : 0;
		$recruit->enterprise;
		return $recruit;
	}
	
	/**
	 * 更新招聘职位
	 */
	public function update($id, $data, $operator) {
		$this->dependencyInjection();
		$job = $this->model->get($id);
		$update = [];
		if (isset($data['enterprise_id']) && intval($data['enterprise_id']) > 0 && $data['enterprise_id'] != $job->enterprise_id){
			$update['enterprise_id'] = $data['enterprise_id'];
		}
		if (isset($data['region']) && $data['region'] != $job->region){
			$update['region'] = $data['region'];
		}
		if (isset($data['salary_intro']) && $data['salary_intro'] != $job->salary_intro){
			$update['salary_intro'] = $data['salary_intro'];
		}
		if (isset($data['validity_period']) && $data['validity_period'] != $job->validity_period){
			$update['validity_period'] = $data['validity_period'];
		}
		if (isset($data['type']) && $data['type'] && $data['type'] != $job->type){
			$update['type'] = $data['type'];
		}
		if (isset($data['list_order']) && $data['list_order'] != $job->list_order){
			$update['list_order'] = $data['list_order'];
		}
		$this->model->save($update, ['id' => $id]);
		//更新职位返费
		if (isset($data['allowance'])) {
			$this->jobAllowanceMediator->updateAllowance($id, $data['allowance'], $data['allowance_type'], $data['ent_wage'], $data['cp_wage']);
			$update['allowance'] = $data['allowance'];
		}
		//更新劳务公司
		if (isset($data['labour']) && $data['labour']) {
			$this->labourModel->update(['labour_service_id'=>$data['labour']], ['job_id'=>$id]);
		}
		/*添加日志*/
		$this->logModel->save([
			'job_id' => $id,
			'admin_id' => $operator,
			'create_time' => time(),
			'type' => 'update',
			'content' => serialize($update)
		]);
	}

    /**
     * 根据id 删除指定资源
     * @param $id
     * @return bool
     */
    public function delJob($id) {
        $crmJob = $this->model->where("id", "EQ", "{$id}")->delete();
        if ($crmJob) {
            $this->jobAllowance = new CrmJobAllowance;
            $return = $this->jobAllowance->where("job_id", "EQ", "{$id}")->delete();
            if ($return) return true;
            //crm_job_allowance 表中没有 job_id = $id 的数据
            //
        } else {
            return false;
        }
    }
}