<?php
namespace app\index\controller;

use think\Request;
use app\token\controller\AuthController;
use app\index\business\FeedBackBiz;

class FeedBack extends AuthController
{
    public function isValidate()
    {
        // TODO: Implement isValidate() method.
    }
    public function save(Request $request)
    {
        $param = $request->param();
        $uid = $this->getUser();
        $time = time();
        $model = new FeedBackBiz();
        $data = [
            'uid' => $uid,
            'content' => $param['content'],
            'create_time' => $time
        ];
        $return = $model->save($data);
        return $return;
    }
}