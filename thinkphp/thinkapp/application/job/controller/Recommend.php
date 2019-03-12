<?php
namespace app\job\controller;

use think\Controller;
use think\Request;

use ylcore\Agreement;
use app\job\business\JobBiz;

class Recommend extends Controller
{
    /**
     * 显示推荐职位列表
     *
     * @return \think\Response
     */
    public function index()
    {
    	/*获取参数*/
    	$param = request()->param();
    	$size = isset($param['size'])?$param['size']:8;//职位个数
    	$type = isset($param['type'])?$param['type']:2;//推荐位类型。默认2(APP职位主页)
    	/*获取推荐职位*/
    	$biz = new JobBiz();
    	$list = $biz->recommend($type, $size);
        foreach ($list as $key => $value) {
            $list[$key]['cover'] = $biz->cover($value['cover']);
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
