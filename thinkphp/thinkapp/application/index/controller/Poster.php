<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

use app\index\business\PosterBiz;

class Poster extends Controller
{
    /**
     * 显示广告列表
     *
     * @return \think\Response
     */
    public function index()
    {
    	/*获取参数*/
        $param = request()->param();
        $size = isset($param['size'])?$param['size']:4;//广告个数
        $type = isset($param['type'])?$param['type']:2;//广告类型。默认2(APP职位tab主页banner)
        /*获取广告*/
        $biz = new PosterBiz();
        $list = $biz->getAll($type, $size);
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
    	$param = request()->param();
        $data = request()->file('image');
        $avatar = isset($param['avatar']) ? $param['avatar'] : false;
    	if (! $data) abort(400, '400 Invalid params data supplied');
    
    	$business = new PosterBiz();
    	$return = $business->upload($data, $avatar);
    	 
    	return $return;
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
