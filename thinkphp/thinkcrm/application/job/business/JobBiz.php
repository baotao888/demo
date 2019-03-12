<?php
namespace app\job\business;

use ylcore\Biz;
use app\job\model\Job;
use app\job\model\JobRecommendedData;
use app\message\business\MessageFactory;

class JobBiz extends Biz
{
	/**
	 * @var \think\Model 模型类实例
	 */
	public $jobModel;
	
	/**
	 * 新增职位
	 */
	public function add($data) {
		$job = model('Job');
		$job->data([
			'cash_back' => isset($data['cash_back'])?$data['cash_back']:'',
			'cat_id' => isset($data['cat_id'])?$data['cat_id']:'',
			'condition_short' => isset($data['condition_short'])?$data['condition_short']:'',
			'cover' => isset($data['cover'])?$data['cover']:'',
			'enterprise_id'  =>  isset($data['enterprise_id'])?$data['enterprise_id']:0,
			'is_vip' => isset($data['is_vip'])?$data['is_vip']:0,
			'job_name' =>  isset($data['job_name'])?$data['job_name']:'',
			'job_tag' => isset($data['job_tag'])?$data['job_tag']:'',
			'list_order' => isset($data['list_order'])?$data['list_order']:0,
			'publish_time' => time(),
			'recommend_tag' => isset($data['recommend_tag'])?$data['recommend_tag']:'',
			'region_txt' => isset($data['region_name'])?$data['region_name']:'',
			'region_id' => isset($data['region_id'])?$data['region_id']:0,
			'salary_floor' => isset($data['salary_floor'])?$data['salary_floor']:0,
			'salary_ceil' => isset($data['salary_ceil'])?$data['salary_ceil']:0,
			'status' => isset($data['status'])?$data['status']:1,
			'type' => isset($data['type'])?$data['type']:'',
			'welfare' => isset($data['welfare'])?$data['welfare']:'',
			'welfare_tag' => isset($data['welfare_tag'])?$data['welfare_tag']:'',										
		]);
		$job->save();//保存主表
		$this->initJob($job->id);
		if (! $job->enterprise_id && isset($data['enterprise'])){
			$enterprise_id = $this->initEnterprise($data['enterprise']);
			//职位关联企业
			$job->save(['enterprise_id' => $enterprise_id], ['id' => $job->id]);
		}
		return $job->id;
	}
	
	/**
	 * 获取职位详情
	 * @param integer $id
	 */
	public function get($id) {
		$job = model('Job');
		$obj = $job->get($id);
		if (strpos($obj->detail->address_mark, ',')) $obj->detail->address_mark = explode(',', $obj->detail->address_mark);
		else $obj->detail->address_mark = [120.96914, 31.361753];
		if ($obj->detail->pictures) $obj->detail->pictures = unserialize($obj->detail->pictures);
		else $obj->detail->pictures = [];
		if ($obj->enterprise_id>0) $obj->enterprise;
		if ($obj->job_tag) $obj->job_tag = explode(',', $obj->job_tag); 
		if ($obj->recommend_tag) $obj->recommend_tag = explode(',', $obj->recommend_tag);
		$obj->status_txt = $this->mappingStatus($obj->status);
		if ($obj->welfare_tag) $obj->welfare_tag = explode(',', $obj->welfare_tag);
		return $obj;
	}
	
	/**
	 * 更新职位信息
	 */
	public function update($id, $data) {
		$job = model('Job');
		$update = [];
		if (isset($data['cash_back'])){
			$update['cash_back'] = $data['cash_back'];
		}
		if (isset($data['cat_id']) && $data['cat_id']){
			$update['cat_id'] = $data['cat_id'];
		}
		if (isset($data['condition_short'])){
			$update['condition_short'] = $data['condition_short'];
		}
		if (isset($data['cover']) && is_url($data['cover'])) {
			$update['cover'] = $data['cover'];
		}
		if (isset($data['enterprise_id']) && intval($data['enterprise_id']) > 0){
			$update['enterprise_id'] = $data['enterprise_id'];
		}
		if (isset($data['is_vip'])){
			$update['is_vip'] = $data['is_vip']?1:0;
		}
		if (isset($data['job_name']) && $data['job_name']!=''){
			$update['job_name'] = $data['job_name'];
		}
		if (isset($data['job_tag']) && $data['job_tag'] && is_array($data['job_tag'])){
			$update['job_tag'] = implode(',', $data['job_tag']);
		}
		if (isset($data['list_order']) && intval($data['list_order']) > 0){
			$update['list_order'] = $data['list_order'];
		}
		if (isset($data['recommend_tag']) && $data['recommend_tag'] && is_array($data['recommend_tag'])) {
			$update['recommend_tag'] = implode(',', $data['recommend_tag']);
		}
		if (isset($data['region_id']) && $data['region_id']!=''){
			$update['region_id'] = $data['region_id'];
		}
		if (isset($data['region_name']) && $data['region_name']!=''){
			$update['region_txt'] = $data['region_name'];
		}
		if (isset($data['salary_ceil']) && $data['salary_ceil']!=''){
			$update['salary_ceil'] = $data['salary_ceil'];
		}
		if (isset($data['salary_floor']) && $data['salary_floor']!=''){
			$update['salary_floor'] = $data['salary_floor'];
		}
		if (isset($data['status'])){
			$update['status'] = $data['status'];
		}
		if (isset($data['type']) && $data['type']){
			$update['type'] = $data['type'];
		}
		if (isset($data['welfare']) && $data['welfare']!=''){
			$update['welfare'] = $data['welfare'];
		}
		if (isset($data['welfare_tag']) && $data['welfare_tag'] && is_array($data['welfare_tag'])){
			$update['welfare_tag'] = implode(',', $data['welfare_tag']);
		}
		$update = $job->updateFilter($update, $id);
		$this->SendMessage($update, $id);
		$job->save($update, ['id' => $id]);//更新主表
		//更新附表
		if (isset($data['detail'])){
			$this->updateDetail($id, $data['detail']);
		}
	}
	
	/**
	 * 更新职位详细信息
	 */
	public function updateDetail($id, $data) {
		$update = array();
		if (isset($data['address_mark'])){
			$update['address_mark'] = $data['address_mark'];
		}
		if (isset($data['address_short'])){
			$update['address_short'] = $data['address_short'];
		}
		if (isset($data['content'])) {
			$update['content'] = $data['content'];
		}
		if (isset($data['pictures']) && is_array($data['pictures'])) {
			$update['pictures'] = serialize($data['pictures']);
		}
		if (isset($data['salary_detail'])){
			$update['salary_detail'] = $data['salary_detail'];
		}
		if (isset($data['view_time'])){
			$update['view_time'] = $data['view_time'];
		}
		$model = model('JobData');
		$model->save($update, ['job_id' => $id]);
	}
	
	/**
	 * 获取所有职位信息
	 */
	public function getAll()
	{
		$return = array();
		$model = model('job/Job');
		$list = $model->where('id', '>', 0)->select();
		if ($list){
			foreach ($list as $item){
				$return[$item['id']] = $item;
			}
		}
		return $return;
	}
	
	/**
	 * 初始化职位信息
	 */
	public function initJob($id, $job = []) {
		/*初始化详情*/
		$detail = model('JobData');
		$data = ['job_id'=>$id];
		$detail->data($data);
		$detail->save();
		/*初始化数据*/
		$statistics = model('JobStatistics');
		$s_data = ['job_id'=>$id];
		$statistics->data($s_data);
		$statistics->save();
	}
	
	/**
	 * 获取所有企业信息
	 */
	public function getEnterprises()
	{
		$return = array();
		$model = model('Enterprise');
		$list = $model->where('id', '>', 0)->select();
		if ($list){
			foreach ($list as $item){
				$return[$item['id']] = $item;
			}
		}
		return $return;
	}
	
	/**
	 * 初始化企业
	 * @param array $data
	 */
	public function initEnterprise($data) {
		if (! isset($data['enterprise_name']) || $data['enterprise_name'] == '') return 0;
		/*初始化详情*/
		$detail = model('Enterprise');
		$data = ['enterprise_name'=>$data['enterprise_name']];
		$detail->data($data);
		$detail->save();//保存主表
		return $detail->id;
	}
	
	/**
	 * 获取所有企业信息
	 */
	public function getEnterprise($id)
	{
		$return = array();
		$model = model('Enterprise');
		$return = $model->get($id);
		if ($return && $return->tag) $return->tag = explode(',', $return->tag);
		return $return;
	}
	
	/**
	 * 更新企业信息
	 */
	public function updateEnterprise($id, $data) {
		$model = model('Enterprise');
		$update = [];
		if (isset($data['description'])){
			$update['description'] = $data['description'];
		}
		if (isset($data['enterprise_name']) && $data['enterprise_name']){
			$update['enterprise_name'] = $data['enterprise_name'];
		}
		if (isset($data['industry'])){
			$update['industry'] = $data['industry'];
		}
		if (isset($data['nature'])){
			$update['nature'] = $data['nature'];
		}
		if (isset($data['scale'])){
			$update['scale'] = $data['scale'];
		}
		if (isset($data['tag']) && is_array($data['tag'])){
			$update['tag'] = implode(',', $data['tag']);
		}
		$model->save($update, ['id' => $id]);//更新主表
	}
	
	/**
	 * 获取最新职位总数
	 */
	public function latestCount() {
		$time = time() - 3600 * 24;//24小时只能的文章
		$model = model('job/Job');
		return $model->where('publish_time > ' . $time)->count();
	}

	/**
	 * 更新职位报名数据
	 */
	public function updateSignup() {
		$model = model('JobStatistics');
		$result = $model->select();
		foreach ($result as $key => $value) {
			$rand = rand(10,89);
			$deliveries = $value['deliveries'] + $rand;
			$model->where('job_id',$value['job_id'])->update(['deliveries'=>$deliveries]);
		}
	}
	
	/**
	 * 职位状态映射
	 */
	public function mappingStatus($status) {
		return lang('job_status_' . $status);
	}
	
	/**
	 * 获取已推荐的职位
	 * @param integer $id 推荐位编号
	 */
	public function recommended($id) {
		$model = new JobRecommendedData();
		$list = $model->where('re_id', $id)->column('job_id');
		return $list;
	}
	
	/**
	 * 新增推荐
	 * @param array $arr_id 职位编号数组
	 * @param integer $id 推荐位编号
	 */
	public function addRecommend($arr_id, $id) {
		$model = new JobRecommendedData();
		$model->where('re_id', $id)->delete();//删除之前推荐位
		/*添加新的推荐位*/
		$arr_data = [];
		foreach ($arr_id as $job_id) {
			$arr_data[] = ['re_id'=>$id, 'job_id'=>$job_id];
		}
		return $model->insertAll($arr_data);
	}
	
	/**
	 * 职位排序
	 * @param integer $job_id
	 * @param integer $id 推荐位
	 * @param integer $order
	 * @return void
	 */
	public function orderRecommend($job_id, $id, $order) {
		$model = new JobRecommendedData();
		$model->update(['list_order'=>$order], ['re_id'=>$id, 'job_id'=>$job_id]);
	}
	
	/**
	 * 获取已推荐的职位列表
	 * @param integer $id 推荐位编号
	 */
	public function recommendedList($id) {
		$model = new JobRecommendedData();
		$list = $model->alias('jrd')
			->join('Job job', 'jrd.job_id=job.id')
			->where('re_id', $id)
			->field('id, jrd.list_order, job_name')
			->select();
		return $list;
	}
	
	/**
	 * 发送订阅消息
	 * @param array $data
	 * @param integer $id
	 */
	public function SendMessage($data, $id) {
		if (isset($data['cash_back'])) {
			$model = new Job;
			$job_name = $model->where('id', $id)->value('job_name');
			$message_biz = MessageFactory::instance('app');
			$message_biz->sendJobSubscribMessage($id, lang('job_subscrib_message', [$job_name, $data['cash_back']]));
		}
	}
}