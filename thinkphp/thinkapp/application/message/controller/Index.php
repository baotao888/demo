<?php

namespace app\message\controller;

use think\Controller;
use think\Request;

use app\token\controller\AuthController;
use app\message\business\MessageFactory;

class Index extends AuthController
{
	/**
	 * 实现抽象方法
	 * 全部接口需权限验证
	 */
	function isValidate() {
		return true;
	}
	
    /**
     * 显示我的消息列表
     *
     * @return \think\Response
     */
    public function index()
    {
        /*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:8;
    	$order = isset($param['order'])?$param['order']:false;
    	$by = isset($param['by'])?$param['by']:false;
    	$type = isset($param['type'])?$param['type']:false;
    	/*获取消息*/
    	$biz = MessageFactory::instance($type);
    	$list = $biz->getMy(
    		$this->uid,
    		[],
    		$page, 
    		$pagesize, 
    		$order, 
    		$by
    	);
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
     * 发送消息
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        /*获取参数*/
    	$param = request()->param();
    	$receiver = isset($param['receiver'])?$param['receiver']:false;//接收者
    	$content = isset($param['content'])?$param['content']:false;//消息内容
    	$reply_id = isset($param['reply_id'])?$param['reply_id']:null;//回复消息编号
    	if (! $receiver || ! $content) abort(400, '400 Invalid receiver/content supplied.');//参数无效
    	/*发送消息*/
    	$biz = MessageFactory::instance();
    	$msg_id = $biz->send($this->uid, $receiver, $content, $reply_id);
    	return true;
    }

    /**
     * 显示私聊详情
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $biz = MessageFactory::instance('private');
        $list = $biz->detail($this->uid, $id);//获取私聊信息
        /*数据传输*/
        $return = $biz->transfer($list);
        return $return;
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
     * 更新消息为已读
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
    	/*消息权限认证*/
        $biz = MessageFactory::instance();
        if ($biz->isMy($id, $this->uid) == false) abort(401, '401 Unauthorized');//权限无效
        $biz->setRead($id);
        return true;
    }

    /**
     * 删除消息
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        /*消息权限认证*/
        $biz = MessageFactory::instance();
        if ($biz->isMy($id, $this->uid) == false) abort(401, '401 Unauthorized');//权限无效
        $biz->delete($id);
        return true;
    }
}
