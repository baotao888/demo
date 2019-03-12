<?php

namespace app\job\controller;

use think\Controller;
use think\Request;

use app\job\business\JobBiz;

class Tag extends Controller
{
    /**
     * 显示职位标签列表
     *
     * @return \think\Response
     */
    public function index()
    {
        /*获取参数*/
    	$param = request()->param();
    	$size = isset($param['size'])?$param['size']:6;//标签个数
    	$type = isset($param['type'])?$param['type']:1;//推荐位类型。默认1(热门标签)
    	/*获取职位标签*/
    	$biz = new JobBiz();
    	$list = $biz->tags($type, $size);
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
        //
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
