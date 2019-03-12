<?php

namespace app\job\controller;

use think\Controller;
use think\Request;

use app\token\controller\AuthController;
use app\job\business\JobBiz;
use ylcore\Agreement;

class Index extends AuthController
{
	/**
	 * 实现抽象方法
	 * 全部接口无需权限验证
	 */
	function isValidate() {
		return false;
	}
	
    /**
     * 显示职位列表
     *
     * @return \think\Response
     */
    public function index()
    {
        /*获取参数*/
    	$param = request()->param();
    	$by = isset($param['by'])?$param['by']:false;
    	$is_subsidy = isset($param['is_subsidy'])?$param['is_subsidy']:false;
    	$is_vip = isset($param['is_vip'])?$param['is_vip']:false;    	 	
    	$keyword = isset($param['keyword'])?$param['keyword']:false;
    	$order = isset($param['order'])?$param['order']:false;
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:8;
    	$region_id = isset($param['region_id'])?$param['region_id']:false;
    	$salary_floor = isset($param['salary_floor'])?$param['salary_floor']:false;
    	$salary_ceil = isset($param['salary_ceil'])?$param['salary_ceil']:false;
    	$tag = isset($param['tag'])?$param['tag']:false;
    	$type = isset($param['type'])?$param['type']:false;
    	/*检索职位*/
    	$biz = new JobBiz();
    	$condition = [
    		'is_subsidy' => $is_subsidy,
    		'is_vip' => $is_vip,	
    		'keyword' => $keyword,
    		'region_id' => $region_id,
    		'salary_ceil' => $salary_ceil, 
    		'salary_floor' => $salary_floor, 
    		'tag' => $tag,
    		'type' => $type,	
    		'user' => $this->getUser()
    	];
    	$list = $biz->search(
    		$condition,
    		$page, 
    		$pagesize, 
    		$order, 
    		$by
    	);
        $model = new Agreement();
        foreach ($list as $key => $value) {
            $list[$key]['cover'] = $model->httpAgreement($value['cover']);
        }
    	/*数据传输*/
    	$return = $biz->transfer($list);
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
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $biz = new JobBiz();
        $job = $biz->get($id);
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
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
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
