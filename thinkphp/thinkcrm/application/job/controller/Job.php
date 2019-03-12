<?php

namespace app\job\controller;

use think\Controller;
use think\Request;
use think\Log;
use ylcore\FileService;
use think\Config;

class Job extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $business = controller('JobBiz', 'business');
        $list = $business->getAll();
        return $list;
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //参数验证
        $param = $request->param();
        $data = array();
    	if (! isset($param['job_name']) || strlen($param['job_name']) < 5) {
    		abort(400, '400 Invalid job_name supplied');
    	} elseif (! isset($param['salary_floor']) || intval($param['salary_floor']) < 0 || ! isset($param['salary_ceil']) || intval($param['salary_ceil']) < intval($param['salary_floor'])){
    		abort(400, '400 Invalid salary_floor/salaryceil supplied');
    	} else if (! isset($param['region_id']) || $param['region_id']<=0 || ! isset($param['region_name']) || $param['region_name']==''){
    		abort(400, '400 Invalid region supplied');
    	} else {
    		$data['job_name'] = $param['job_name'];
    		$data['region_id'] = intval($param['region_id']);
    		$data['region_name'] = $param['region_name'];
    		$data['salary_floor'] = intval($param['salary_floor']);
    		$data['salary_ceil'] = intval($param['salary_ceil']);
    		if (isset($param['cash_back'])) $data['cash_back'] = $param['cash_back'];
    		if (isset($param['cat_id'])) $data['cat_id'] = intval($param['cat_id']);
    		if (isset($param['condition_short'])) $data['condition_short'] = $param['condition_short'];
    		if (isset($param['enterprise_id'])) $data['enterprise_id'] = $param['enterprise_id'];
    		if (isset($param['enterprise_name'])) $data['enterprise']['enterprise_name'] = $param['enterprise_name'];
    		if (isset($param['is_vip'])) $data['is_vip'] = $param['is_vip'];
    		if (isset($param['status'])) $data['status'] = $param['status'];
    		if (isset($param['type'])) $data['type'] = intval($param['type']);
    		if (isset($param['welfare'])) $data['welfare'] = $param['welfare'];
    	}
    	//保存数据
    	$business = controller('JobBiz', 'business');
    	$job_id = $business->add($data);
    	return ['id' => $job_id];
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //获取职位详情
    	$business = controller('JobBiz', 'business');
    	$job = $business->get($id);
    	return $job;
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
    	
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id )
    {
        $param = $request->param();
    	$data = [];
    	if (isset($param['address_mark'])) {
    		$data['detail']['address_mark'] = $param['address_mark'];
    	}
    	if (isset($param['address_short'])){
    		$data['detail']['address_short'] = $param['address_short'];
    	}
    	if (isset($param['cash_back'])) $data['cash_back'] = $param['cash_back'];
    	if (isset($param['cat_id'])) $data['cat_id'] = $param['cat_id'];
    	if (isset($param['condition_short'])) $data['condition_short'] = $param['condition_short'];
    	if (isset($param['content'])) {
    		$data['detail']['content'] = html_entity_decode($param['content']);
    	}
    	if (isset($param['cover'])){
    		if (is_url($param['cover'])){
    			$cover = $param['cover'];
    		}else{
    			$service = new FileService();
    			$cover = $service->base64_upload($param['cover']);//保存base64图片
    		}
    		if ($cover) $data['cover'] = $cover;
    	}
    	if (isset($param['enterprise_id'])) $data['enterprise_id'] = $param['enterprise_id'];
    	if (isset($param['is_vip'])) $data['is_vip'] = $param['is_vip'];
    	if (isset($param['job_name']) && strlen($param['job_name']) >= 5) {
    		$data['job_name'] = $param['job_name'];
    	}
    	if (isset($param['job_tag'])) $data['job_tag'] = $param['job_tag'];
    	if (isset($param['list_order'])) $data['list_order'] = $param['list_order'];
    	if (isset($param['pictures']) && is_array($param['pictures'])){
    		$data['detail']['pictures'] = $param['pictures'];
    	}
    	if (isset($param['recommend_tag'])) $data['recommend_tag'] = $param['recommend_tag'];
    	if (isset($param['region_id']) && $param['region_id']>0 && isset($param['region_name']) && $param['region_name']!=''){
    		$data['region_id'] = $param['region_id'];
    		$data['region_name'] = $param['region_name'];
    	}
    	if (isset($param['salary_detail'])){
    		$data['detail']['salary_detail'] = html_entity_decode($param['salary_detail']);
    	}
    	if (isset($param['salary_floor']) && intval($param['salary_floor']) > 0 && isset($param['salary_ceil']) && intval($param['salary_ceil']) > intval($param['salary_floor'])){
    		$data['salary_floor'] = $param['salary_floor'];
    		$data['salary_ceil'] = $param['salary_ceil'];
    	}
    	if (isset($param['status'])) $data['status'] = $param['status'];
    	if (isset($param['type'])) $data['type'] = $param['type'];
    	if (isset($param['view_time'])){
    		$data['detail']['view_time'] = $param['view_time'];
    	}
    	if (isset($param['welfare'])) $data['welfare'] = $param['welfare'];
        if (isset($param['welfare_tag'])) $data['welfare_tag'] = $param['welfare_tag'];
    	if (empty($data)){
    		abort(400, '400 Invalid param supplied');
    	}
    	//保存数据
    	$business = controller('JobBiz', 'business');
    	$business->update($id, $data);
    	return ['id' => $id];
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
