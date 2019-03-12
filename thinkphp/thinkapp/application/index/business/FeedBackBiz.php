<?php
namespace app\index\business;

use ylcore\Biz;
use app\index\model\FeedBack;

class FeedBackBiz extends Biz
{
    /**
     * @param array $data
     * @return true
     */
    public function save($data) {
        $model = new FeedBack();
        $return = false;
        if ($data) $return = $model->insert($data) ? true : false;
        return $return;
    }
}