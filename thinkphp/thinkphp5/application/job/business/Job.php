<?php
// [ 用户业务类 ]

namespace app\job\business;

use ylcore\Biz;
use think\Collection;
use think\Config;
use think\Cache;
use think\Log;

class Job extends Biz
{
	/**
	 * 获取首页职位列表信息
	 */
	public function getInfo(){
		$model = model('job/Job');
		$list = $model->where('status', '>', 0)->order('list_order desc')->select();
		$nlist = new Collection($list);
		$newarr = $nlist->toArray();
		for ( $i = 0; $i < count($list); $i++ ){
		 	$newarr[$i]['welfare_tag']= explode(',',$newarr[$i]['welfare_tag']);
			// 联查面试时间
		 	$newarr[$i]['subdata'] = $list[$i]->subData;
			// 联查投递人数
		 	$newarr[$i]['renshu'] = $list[$i]->getDeliveries;
		}

		return $newarr;
	}

	/**
	 * 首页职位和已报名数量变身数组
	 */
	public function changeArray($key){
		$zhong = str_split($key);
		$count = count($zhong);
		if( $count == 1){
			$result = array(0,0,0,0,$key);
		}elseif($count == 2){
			$result = array(0,0,0,$zhong[0],$zhong[1]);
		}elseif($count == 3){
			$result = array(0,0,$zhong[0],$zhong[1],$zhong[2]);
		}elseif($count == 4){
			$result = array(0,$zhong[0],$zhong[1],$zhong[2],$zhong[3]);
		}elseif($count == 5){
			$result = $zhong;
		}

		return $result;
	}

	/**
	 * 获取首页职位数量
	 */
	public function getJobCount(){
		$model = model('job/Job');
		$list = $model->where('status', '>', 0)->select();
		$nlist = new Collection($list);
		$newarr = $nlist->toArray();
		$jobcount = $this->changeArray(count($newarr));

		return $jobcount;
	}

	/**
	 * 获取首页报名人数量
	 */
	public function getSignCount(){
		$model = model('job/JobStatistics');
		$list = $model->select();
		$nlist = new Collection($list);
		$newarr = $nlist->toArray();
		$signcount = 0;
		for ( $i = 0; $i < count($list); $i++ ){
		 	$signcount	+= intval($newarr[$i]['deliveries']);
		}
		$result = $this->changeArray($signcount);

		return $result;
	}

	/**
	 * 模拟今日报名人数量
	 */
	public function getTodaySignCount(){
		$cache_key = 'yl-web-job-signup';
		/*加载模拟数据*/
		Config::load(APP_PATH.'job/config_data.php');
		$data = Config::get('signup_demo_data');
		$today = getdate();//日期
		$total = $data[$today['wday']][$today['hours']];//基数按照星期分组，按照小时升序
		$cache_data = Cache::get($cache_key);
		if (empty($cache_data)) {
			Cache::set($cache_key, $total);//初始缓存
			$return = $total;
		} else {
			if ($total == 0 || $total == 10) {
				Cache::set($cache_key, $total);//重设缓存
				$return = $total;
			} else {
				$return = Cache::get($cache_key);//读取缓存
			}
		}
		/*每十分钟随机添加数字*/
		$sub_total = pow(10, floor(log10($total/10)));//随机基数
		$minute_group = $today['minutes']/10;//随机比例
		$total += rand($minute_group*$sub_total, ($minute_group+1)*$sub_total);

		if ($total > Cache::get($cache_key)) {
			Cache::set($cache_key, $total);//更新缓存
			$return = $total;
		}
		$return = $this->changeArray($return);
		return $return;
	}

	/**
	 * 获取职位详情信息
	 */
	public function getDetails($id){
		$model = model('job/Job');
		$list = $model->where('id',$id)->select();
		$nlist = new Collection($list);
		$newarr = $nlist->toArray();
		for ( $i = 0; $i < count($list); $i++ ){
		 	$newarr[$i]['welfare_tag']= explode(',',$newarr[$i]['welfare_tag']);
			// 联查面试时间,薪资详情,职位详情,公司简介,组图
		 	$newarr[$i]['jobdata'] = $list[$i]->detail;
		 	$newarr[$i]['company'] = $list[$i]->company;
		 	$newarr[$i]['jobdata']['pictures'] = unserialize($newarr[$i]['jobdata']['pictures']);
			// 联查投递人数
		 	$newarr[$i]['renshu'] = $list[$i]->getDeliveries;
		}

		return $newarr;
	}

	/**
	 * 获取职位地图信息
	 */
	public function getMap($id){
		$model = model('job/Jobdata');
		$list = $model->where('job_id',$id)->select();
		$nlist = new Collection($list);
		$newarr = $nlist->toArray();
		$newarr[0]['address_mark'] == '' ? $newarr[0]['address_mark'] = '120.971105,31.36439' : $newarr[0]['address_mark'];

		return $newarr;
	}

	/**
	 * 获取搜索的职位信息
	 */
	public function getsearch($key){
		$model = model('job/Job');
		$where = " job_name like'%".$key."%' AND `status` > 0";
		$list = $model->where($where)->select();
		$nlist = new Collection($list);
		$newarr = $nlist->toArray();
		for ( $i = 0; $i < count($list); $i++ ){
		 	$newarr[$i]['welfare_tag']= explode(',',$newarr[$i]['welfare_tag']);
			// 联查面试时间
		 	$newarr[$i]['subdata'] = $list[$i]->subData;
			// 联查投递人数
		 	$newarr[$i]['renshu'] = $list[$i]->getDeliveries;
		}

		return $newarr;
	}

	/**
	 * 更新职位投递数
	 */
	public function updateDeliveries($id, $statistics, $op = '+'){
		$model = model('job/JobStatistics');
		$model->where('job_id', '=', $id)->setInc('deliveries', $statistics);
	}

	/**
	 * 获取详情页职位列表信息
	 */
	public function getDetailsList($id){
		$model = model('job/Job');
		$list = $model->where('id','<>',$id)->select();
		$nlist = new Collection($list);
		$newarr = $nlist->toArray();
		for ( $i = 0; $i < count($list); $i++ ){
		 	$newarr[$i]['welfare_tag']= explode(',',$newarr[$i]['welfare_tag']);
		}
		$return = array_rand($newarr,10);
		foreach($return as $v){
			$res[] = $newarr[$v];
		}

		return $res;
	}

}