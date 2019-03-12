<?php
namespace app\user\business;

use ylcore\Biz;

use app\user\model\AppSetting;

class AppSet extends Biz
{
    /**
     * 获取用户设置
     * @param int $uid
     */
    public function getData($uid)
    {
        $model = new AppSetting();
        $data = $model->where('uid',$uid)->find();
        unset($data['id']);
        $return = unserialize($data['data']);
        return $return;
    }

    /**
     *更新用户设置
     * @param int $uid
     * @param array $data
     */
    public function update($uid, $data)
    {
        $app_subscribe = $this->getData($uid);
        $model = new AppSetting();
        if ($app_subscribe == '') {
            $data['uid'] = $uid;
            return $return = $this->save($uid, $data);
        } else {
            $return = false;
            if ($data) $return = $model->save($data, ['uid' => $uid]) ? true : false;
            return $return;
        }
    }

    /**
     *保存用户设置
     * @param int $uid
     * @param array $data
     */
    public function save($uid, $data)
    {
        $model = new AppSetting();
        $return = false;
        if ($data) $return = $model->insert($data, ['uid' => $uid]) ? true : false;
        return $return;
    }

    /**
     *获取初始化值
     *@return  array $data
     */
    public function defaultValue($id) {
        $array = $this->getData($id);
        if ($array != '') {
            $data = [
                "openlocation" => $array['openlocation'],
                "openattention" => $array['openattention'],
                "openmessage" => $array['openmessage'],
                "closedisturb" => $array['closedisturb']
            ];
        } else {
            $data = [
                "openlocation" => true,
                "openattention" => true,
                "openmessage" => true,
                "closedisturb" => true
            ];
        }

        return $data;
    }
}