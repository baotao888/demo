<?php
namespace app\user\business;

use app\user\model\FmSalesorderItems;
use app\user\model\CustomerPool;
use app\user\model\Enterprise;
use app\user\model\FmSalesorder;
use app\user\model\User;
use ylcore\Biz;

class AllowanceBiz extends Biz
{
    /**
     *我的补贴
     *@param int $uid
     */
    public function myAllowance($uid, $page=1, $pagesize=10) {
        /*获取手机号*/
        $user =new User();
        $id = $user->alias('user')->where('uid', $uid)
                   ->join('CustomerPool customer', 'user.mobile = customer.phone')
                   ->field('id')->find();
        $cp_id = $id['id'];
        $data[0] = [
            'job_name' => '',
            'amount' => '',
            'onduty_time' => '',
            'days' => '',
            'deadline' => ''
        ];
        /*获取客户补贴信息*/
        $enterprise = new Enterprise();
        $array = $enterprise->alias('ent')
                ->join('FmSalesorder salesorder', 'ent.id = salesorder.ent_id')
                ->join('FmSalesorderItems items', 'salesorder.id = items.salesorder')
                ->where('salesorder.cp_id', $cp_id)
                ->field('enterprise_name, type, go_to_time, allowance, receive_day')
                ->page($page, $pagesize)
                ->select();
        foreach ($array as $key => $value) {
            $jobType = $this->jobType($value['type']);
            $ondutyTime = $this->ondutyTime($value['go_to_time']);
            $receiveday = $this->receiveDay($value['receive_day'], $value['go_to_time']);
            $job_name = $value['enterprise_name'].$jobType;
            $name= [
                'job_name' => $job_name,
                'amount' => $value['allowance'],
                'onduty_time' => $ondutyTime,
                'days' => $receiveday['days'],
                'deadline' => $receiveday['deadline']
            ];
            $data[$key] = $name;
        }
        return $data;
    }

    /**
     *职业类型
     *@param int $type
     *@return int jobtype
     */
    public function jobType($type) {
        if ($type === 1) {
            $jobtype = lang('formal');
        } else if($type === 2) {
            $jobtype = lang('hourly');
        } else {
            $jobtype = lang('temporary');
        }
        return $jobtype;
    }

    /**
     *获取入职时间
     * @param  int $type 职业类型
     * @param  int $go_to_time 入职时间
     * @return array $time
     */
    public function ondutyTime($go_to_time) {
        $time = date('Y-m-d', $go_to_time);
        return $time;
    }

    /**
     * 补贴时间
     * @param int $receive 领取天数
     * @param  int $go_to_time 入职时间
     * @return array $data
     */
    public function receiveDay($receive, $go_to_time) {
        $deadline_time = $go_to_time + $receive*24*3600;
        $deadline = date('Y-m-d', $deadline_time);
        $days_time = floor((($deadline_time - time())/24)/3600);
        if ($days_time <= 0) {
            $days_time = 0;
        }
        $data = [
            'days' => $days_time,
            'deadline' => $deadline
        ];
        return $data;
    }
}
