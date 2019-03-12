<?php

namespace app\recruit\controller;

use think\Controller;
use think\Request;

use app\admin\controller\AdminAuth;
use app\recruit\business\Job;

class Index extends AdminAuth
{
    /**
     * 显示职位列表
     *
     * @return \think\Response
     */
    public function index()
    {
    	$param = request()->param();
    	$date = isset($param['date']) ? $param['date'] : false;

    	$business = new Job;
    	$return = $business->search($date);
        return $return;
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
     * 保存新建的职位
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //参数验证
        $param = $request->param();
        $data = array();
    	if (! isset($param['validity_period']) || strlen($param['validity_period']) != 10) {
    		abort(400, '400 Invalid validity_period supplied');
    	} elseif (! isset($param['type']) || $param['type'] <= 0 ){
    		abort(400, '400 Invalid type supplied');
    	} else if ((! isset($param['enterprise_id']) || $param['enterprise_id']<=0) && (! isset($param['enterprise_name']) || $param['enterprise_name']=='')){
    		abort(400, '400 Invalid enterprise supplied');
    	} else {
    		$data['validity_period'] = $param['validity_period'];
    		$data['type'] = intval($param['type']);
    		$data['enterprise_id'] = isset($param['enterprise_id']) ? $param['enterprise_id'] : 0;
    		$data['enterprise_name'] = isset($param['enterprise_name']) ? $param['enterprise_name'] : '';
    		$data['allowance_type'] = isset($param['allowance_type']) ? $param['allowance_type'] : '';
    		$data['ent_wage'] = isset($param['ent_wage']) ? $param['ent_wage'] : '';
    		$data['cp_wage'] = isset($param['cp_wage']) ? $param['cp_wage'] : '';
    		$data['labour'] = isset($param['labour']) ? $param['labour'] : '';
    		if (isset($param['region'])) $data['region'] = $param['region'];
    		if (isset($param['salary_intro'])) $data['salary_intro'] = $param['salary_intro'];    		
    		if (isset($param['list_order'])) $data['list_order'] = intval($param['list_order']);
    		if (isset($param['allowance'])) $data['allowance'] = $param['allowance'];//array
    	}
    	//保存数据
    	$business = new Job;
    	$job_id = $business->add($data, $this->getAdminId());
    	return ['id' => $job_id];
    }

    /**
     * 显示职位详情
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //保存数据
    	$business = new Job;
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
        //
    }

    /**
     * 保存更新的职位
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //参数验证
        $param = $request->param();
        $data = array();
    	$data['validity_period'] = $param['validity_period'];
    	$data['type'] = isset($param['type']) ? intval($param['type']) : 2;
    	$data['enterprise_id'] = isset($param['enterprise_id']) ? $param['enterprise_id'] : 0;
    	$data['enterprise_name'] = isset($param['enterprise_name']) ? $param['enterprise_name'] : '';
    	if (isset($param['region'])) $data['region'] = $param['region'];
    	if (isset($param['salary_intro'])) $data['salary_intro'] = $param['salary_intro'];    		
    	if (isset($param['list_order'])) $data['list_order'] = intval($param['list_order']);
    	if (isset($param['allowance'])) $data['allowance'] = $param['allowance'];//array
    	if (isset($param['allowance_type'])) $data['allowance_type'] = $param['allowance_type'];//array
    	$data['ent_wage'] = isset($param['ent_wage']) ? $param['ent_wage'] : '';
    	$data['cp_wage'] = isset($param['cp_wage']) ? $param['cp_wage'] : '';
    	$data['labour'] = isset($param['labour']) ? $param['labour'] : '';
    	//保存数据
    	$business = new Job;
    	$business->update($id, $data, $this->getAdminId());
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
        if (! $id) return false;
        $business = new Job;
        $return = $business->delJob($id);
        return $return;
    }
}
