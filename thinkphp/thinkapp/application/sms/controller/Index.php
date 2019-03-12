<?php

namespace app\sms\controller;

use think\Controller;
use think\Request;
use think\Log;

use app\sms\service\ShortMessageService;

class Index extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
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
     * 发送短信
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        /*获取参数*/
    	$param = $request->param();
    	$mobile = isset($param['mobile']) && is_mobile($param['mobile'])?$param['mobile']:false;
    	if (! $mobile) abort(400, '400 Invalid mobile supplied');
    	$type = isset($param['type']) && in_array($param['type'], ['login', 'register', 'change_mobile'])?$param['type']:false;
    	if (! $type) abort(400, '400 Invalid type supplied');
    	$return = ['status'=>0, 'message'=>''];
    	$service = new ShortMessageService;
    	if ($type == 'login') {
    		$json_return = $service->sendLoginCode($mobile);
    		$return = json_decode($json_return);
    	} elseif ($type == 'register') {
    		$json_return = $service->sendRegisterCode($mobile);
    		$return = json_decode($json_return);
    	} elseif ($type == 'change_mobile') {
            $json_return = $service->sendChangeMobileCode($mobile);
            $return = json_decode($json_return);
        }
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
