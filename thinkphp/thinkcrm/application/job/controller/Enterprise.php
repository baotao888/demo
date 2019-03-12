<?php
namespace app\job\controller;

use think\Request;

class Enterprise
{
	/**
	 * 获取企业列表
	 * @return array
	 */
	public function Index(){
		//获取企业列表
		$business = controller('JobBiz', 'business');
		$enterprises = $business->getEnterprises();
		return $enterprises;
	}
	
	/**
	 * 获取企业详情
	 * @return array
	 */
	public function Read(){
		$param = request()->param();
		$id = isset($param['id'])?$param['id']:0;
		//获取企业列表
		$business = controller('JobBiz', 'business');
		$enterprise = $business->getEnterprise($id);
		return $enterprise;
	}
	
	/**
	 * 更新企业信息
	 */
	public function Update(){
		$param = request()->param();
		$id = isset($param['id'])?$param['id']:0;
		$data = [];
		if (isset($param['enterprise_name'])) $data['enterprise_name'] = $param['enterprise_name'];
		if (isset($param['description'])) $data['description'] = $param['description'];
		if (isset($param['industry'])) $data['industry'] = $param['industry'];
		if (isset($param['tag'])) $data['tag'] = $param['tag'];
		if (isset($param['scale'])) $data['scale'] = $param['scale'];
		if (isset($param['nature'])) $data['nature'] = $param['nature'];
		if (empty($data)){
			abort(400, '400 Invalid param supplied');
		}
		//保存数据
		$business = controller('JobBiz', 'business');
		$business->updateEnterprise($id, $data);
		return ['id' => $id];
	}
}