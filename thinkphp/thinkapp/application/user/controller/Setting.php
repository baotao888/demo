<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/28 0028
 * Time: 15:39
 */
namespace app\user\controller;

use think\Request;

use app\user\business\AppSet;
use app\token\controller\AuthController;

class Setting extends AuthController
{
    public function isValidate()
    {
        return true;
    }

    /**
     * 获取用户信息
     * @param int $uid
     * */
    function read($id)
    {
        $model = new AppSet();
        $return = $model->getData($id);
        return $return;
    }

    /**
     * 更新用户信息
     * @param int $uid
     * $param int $data
     * */
    public function update(Request $request, $id)
    {
        $param = $request->param();
        $business = new AppSet();
        $array = $business->defaultValue($id);
        $openlocation = isset($param['openlocation']) && $param['openlocation'] !== '' ? $param['openlocation'] : $array['openlocation'];
        $openattention = isset($param['openattention']) && $param['openattention'] !== '' ? $param['openattention'] : $array['openattention'];
        $openmessage = isset($param['openmessage']) && $param['openmessage'] !== '' ? $param['openmessage'] : $array['openmessage'];
        $closedisturb = isset($param['closedisturb']) && $param['closedisturb'] !== '' ? $param['closedisturb'] : $array['closedisturb'];
        $setting = [
            "openlocation" => $openlocation,
            "openattention" => $openattention,
            "openmessage" => $openmessage,
            "closedisturb" => $closedisturb
        ];
        $data['data'] = serialize($setting);
        $return = $business->update($id, $data);
        return $return;
    }
}
