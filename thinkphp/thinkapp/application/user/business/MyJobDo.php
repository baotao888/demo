<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/22
 * Time: 13:33
 */
namespace app\user\business;

use ylcore\Biz;

class MyJobDo extends Biz
{

    /**
     * 分页处理
     *
     * @param $page      页数
     * @param $pagesize  每页显示个数
     * @return $page
     */
    public function pageSet($page, $pagesize) {
        $page = ($page - 1) * $pagesize;
        return $page;
    }

    /**
     * 获取用户报名职位ID
     * @param $uid
     * @return array
     */
    public function getJobId($uid) {
        $model = model("user/UserJobProcess", "model");
        $jobObj = $model->where("user_id", "eq", $uid)->select();
        $jobArr = collection($jobObj)->toArray();
        $data = [];
        foreach ($jobArr as $key => $value){
            $data[] = $jobArr[$key]['job_id'];
        }
        return $data;
    }

    /**
     * 获取职位数据
     * @param $page
     * @param $pagesize
     * @param $jobIdArr
     * @return array
     */
    public function getJobByJobId($user_id, $jobIdStr, $page, $pagesize) {
        $job = model("job/Job", "model");
        $jobStatistics  = model("job/JobStatistics", "model"); // JobStatistics 表
        $data = [];
        $jobObj = $job->alias("j")
            ->join("UserJobProcess ujp", "ujp.job_id=j.id")
            ->field("id,job_name,salary_floor,salary_ceil,condition_short,cash_back,is_vip,welfare_tag,cover,creat_time")
            ->where("ujp.user_id", $user_id)
            ->where(array("j.id"=>array("in", $jobIdStr)))
            ->limit($page, $pagesize)
            ->select();
        $jobArr = collection($jobObj)->toArray();
        foreach ($jobArr as $value){
            $value["welfare_tag"] = $this->disposeStr($value["welfare_tag"]);
            $value['base_time'] = date("Y-m-d", $value['creat_time']);
            unset($value['creat_time']);
            $deliveries = $jobStatistics->where("job_id", $value['id'])->value("deliveries");
            $value["deliveries"] = $deliveries;
            $data[] = $value;
        }
        return $data;
    }

    /**
     * 字符串处理
     * 把字符串以 逗号 分割成数组
     *
     * @param $str
     * @return array
     */
    public function disposeStr( $str ) {
        $str = explode(",", $str);
        return $str;
    }

    /**
     * 职位报名
     *
     * @param $user_id
     * @param $job_id
     * @param $birth
     * @param $gender
     * @return array|bool
     */
    public function signup($user_id, $job_id) {
        $jArr = ["user_id" => $user_id, "job_id" => $job_id, "creat_time" => time()];
        $ujp = model("user/UserJobProcess", "model"); // UserJobProcess 表
        $creatTime = $ujp->where("user_id", $user_id)->order("creat_time desc")->value("creat_time");
        $return = $this->createTime($creatTime, $jArr);
        return $return;
    }

    /**
     * 判断报名时间
     *
     * @param $creatTime
     * @param $jArr
     * @return array|bool
     */
    public function createTime($creatTime, $jArr) {
        /* 报名间隔时间 默认一天 */
        $signupTime = 60*60*24;
        if ($creatTime) {
            // 用户已经报名了
            if (($creatTime + $signupTime) < time()) {
                // 一天内可以再报名一次
                return $this->signupDo($jArr);
            } else {
                return false;
            }
        } else {
            return $this->signupDo($jArr);
        }
    }

    /**
     * 报名职位入表
     *
     * @param  array $jArr    用户ID 职位ID 时间戳
     * @return array $return  职位编号
     */
    public function signupDo($jArr) {
        $ujp = model("user/UserJobProcess", "model"); // UserJobProcess 表
        $ujp->save($jArr);
        return true;
    }

    /**
     * 删除职位报名
     * @param $user_id
     * @param $job_id
     * @return bool
     */
    public function delSignup($user_id, $job_id) {
        $ujp = model("user/UserJobProcess", "model");
        $return = $ujp->where("user_id", $user_id)
            ->where("job_id", $job_id)
            ->delete();
        if ($return) {
            return true;
        } else {
            return false;
        }
    }
}