<?php
namespace app\location\business;

use ylcore\Biz;
use app\user\model\UserLocation;
use app\user\business\UserBiz;

class LocationBiz extends Biz
{
	protected $domainObjectFields = [
		'uid',
		'age',
		'avatar',
		'nickname',
		'gender',
		'distance'
	];
	
	/**
	 * 搜索附近的人
	 * @param float $longitude 经度坐标
	 * @param float $latitude 维度坐标
	 * @param integer $radius 搜索半径
	 * @param integer $page
	 * @param integer $pagesize
	 */
	public function search($longitude, $latitude, $radius, $page, $pagesize) {	
		$marker_scope = $this->getAround($latitude, $longitude, $radius);
		/*获取范围内的所有人选*/
		$model = new UserLocation();
		$users = $model->alias('location')
			->join('UserData data', 'data.uid=location.user_id')
			->where('longitude', '>', $marker_scope['minLng'])
			->where('longitude', '<', $marker_scope['maxLng'])
			->where('latitude', '>', $marker_scope['minLat'])
			->where('latitude', '<', $marker_scope['maxLat'])
			->field('uid, nickname, gender, birth, longitude, latitude, real_name')
			->select();
		/*计算距离，按照距离排序分页*/
		$return = [];
		if ($users) {
			$user_list = [];
			$arr_order = [];//距离排序数组
			$user_biz = new UserBiz();
			foreach ($users as $user) {
				$distance = $this->getDistance($latitude, $longitude, $user['latitude'], $user['longitude']);
				$user_list[] = array_merge(['distance'=>ceil($distance)], $user_biz->formatViewField($user->toArray()));
				$arr_order[] = $distance;
			}
			array_multisort($arr_order, $user_list);
			$return = array_slice($user_list, ($page - 1) * $pagesize, $pagesize);
		}
		return $return;
	}
	
	/**
	 * 计算经纬度范围
	 * @param float $lat 纬度 
	 * @param float $lon 经度
	 * @param integer $raidus 单位米
	 * @return minLat,minLng,maxLat,maxLng
	 */	
	public function getAround($lat, $lon, $raidus) {
		$PI = 3.14159265;
	
		$latitude = $lat;
		$longitude = $lon;
	
		$degree = (24901*1609)/360.0;
		$raidusMile = $raidus;
	
		$dpmLat = 1/$degree;
		$radiusLat = $dpmLat * $raidusMile;
		$minLat = $latitude - $radiusLat;
		$maxLat = $latitude + $radiusLat;
	
		$mpdLng = $degree*cos($latitude * ($PI/180));
		$dpmLng = 1 / $mpdLng;
		$radiusLng = $dpmLng * $raidusMile;
		$minLng = $longitude - $radiusLng;
		$maxLng = $longitude + $radiusLng;

		return ['minLat'=> $minLat, 'maxLat'=>$maxLat, 'minLng'=>$minLng, 'maxLng'=>$maxLng];
	}
	
	/**
	 * 计算两点之间的距离 单位米
	 * @param float $lat1
	 * @param float $lng1
	 * @param float $lat2
	 * @param float $lng2
	 * @return int
	 */
	public function getDistance($lat1, $lng1, $lat2, $lng2){
		/*将角度转为弧度*/
		$radLat1 = deg2rad($lat1);//deg2rad()函数将角度转换为弧度
		$radLat2 = deg2rad($lat2);
		$radLng1 = deg2rad($lng1);
		$radLng2 = deg2rad($lng2);
	
		$a = $radLat1 - $radLat2;
		$b = $radLng1 - $radLng2;
		$s = 2 * asin(sqrt(pow(sin($a/2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2), 2))) * 6378.137 * 1000;
	
		return $s;
	}
	
	/**
	 * 设置我的位置
	 * @param float $longitude
	 * @param float $latitude
	 * @param int $uid
	 * @return void
	 */
	public function setting($longitude, $latitude, $uid) {
		$model = new UserLocation();
		$model->insert(['user_id'=>$uid, 'base_time'=>time(), 'longitude'=>$longitude, 'latitude'=>$latitude], true);//replace into
	}
	
	/**
	 * 获取用户位置
	 * @param integer $uid
	 */
	public function myLocation($uid) {
		$model = new UserLocation();
		$return = $model->get($uid);
		return $return;
	}
}