<?php
// [ 用户业务类 ]

namespace app\user\business;

use think\Collection;
use think\Log;

use ylcore\Biz;

class User extends Biz
{
	protected $notifyMediator;//通知中介
	
	function __construct(){
		$this->notifyMediator = controller('user/Notify', 'business');
	}
	
	/**
	 * 用户名注册用户
	 * @param string $user_name
	 * @param string $password
	 * @param array $data
	 * @return int uid
	 */
	public function register($user_name, $password, $data = [])
	{
		$service = controller('user/Uccenter', 'service');
		$mobile = isset($data['mobile'])?$data['mobile']:'';
		$email = isset($data['email'])?$data['email']:'';
		if ($password=='') $password = rand_string(8);
		$uid = $service->register($user_name, $password, $mobile, $email);//ucenter接口注册
		if ($uid > 0) {
			/*用户主表*/
			$model = model('user/user');
			$model->uid = $uid;
			$model->uname = $user_name;
			$model->password = $this->encrpyPassword($password);
			if (isset($data['mobile'])) $model->mobile = $data['mobile'];
			if (isset($data['email'])) $model->email = $data['email'];
			if (isset($data['from'])) $model->from = $data['from'];
			$model->save();
			/*用户附表*/
			$data_model = model('user/UserData');
			$data_model->uid = $uid;
			$data_model->reg_time = time();
			if (isset($data['realname'])) $data_model->real_name = $data['realname'];
			$data_model->save();
			/*发布注册通知*/
			$this->notifyMediator->emitRegister($uid, $data);
		} else {
			Log::record($service->message);
		}
		return $uid;
	}
	
	/**
	 * 手机注册用户
	 * @param string $mobile
	 * @param string $password
	 * @param array $data
	 * @return int uid
	 */
	public function mobileRegister ($mobile, $password, $data = [])
	{
		$i = 0;
		do{
			$user_name = $this->mobile2username($mobile, $i);
			$i ++;
		} while ($this->isExistsUserName($user_name));
		
		$data['mobile'] = $mobile;
		$uid = $this->register($user_name, $password, $data);
		return ['uid' => $uid, 'uname' => $user_name];
	}
	
	/**
	 * 手机号转换成用户名
	 * @param string $mobile
	 * @param integer $i
	 */
	private function mobile2username($mobile, $i = 0){
		$username = '';
		$array = ['o', 'y', 'n', 'l', 'e', 'd', 'a', 'w', 'g', 'b'];
		for ($m = 0; $m < strlen($mobile); $m++){
			$username .= $array[substr($mobile, $m, 1)];
		}
		if ($i) $username .= $i;
		return $username;
	}
	
	/**
	 * 用户名是否已注册
	 * @param string $user_name
	 */
	public function isExistsUserName($user_name){
		$service = controller('user/Uccenter', 'service');
		return $service->isExists($user_name);
	}
	
	/**
	 * 密码加密
	 */
	public function encrpyPassword($password){
		$salt = substr(md5($password),-(strlen($password)%6+6),6);
		$password = md5(md5($password).$salt);
		return $password;
	}
	
	/**
	 * 用户信息
	 */
	public function getInfo($uid){
		$model = model('user/user');
		return $model->get($uid);
	}
	
	/**
	 * 验证手机是否存在
	 */
	public function isExistsMobile($mobile){}
	
	/**
	 * 密码是否错误
	 */
	public function isBadPassword($password){
		return strlen($password)<8;
	}
	
	/**
	 * 登录
	 * 默认为用户名登录
	 */
	public function login($user_name, $password){
		$service = controller('user/Uccenter', 'service');
		$user = $service->login($user_name, $password);//ucenter接口登录
		if ($user['uid']>0){
			//获取用户真实姓名
			$info = $this->getInfo($user['uid']);
			$user['real_name'] = $info->profile->real_name;
		}
		return $user;
	}
	
	/**
	 * 手机登录
	 * @param string $mobile
	 * @param string $password
	 * @return array uid,uname
	 */
	public function mobileLogin($mobile, $password){
		$user_name = $this->getUsernameByMobile($mobile);
		if ($user_name==false) return ['uid'=>0];
		$user = $this->login($user_name, $password);
		return $user;
	}
	
	/**
	 * 手机号查找用户名
	 */
	public function getUsernameByMobile($mobile){
		$username = false;
		$model = model('user/user');
		$user = $model->get(['mobile' => $mobile]);
		if ($user){
			$username = $user->uname;
		}
		return $username;
	}
	
	/**
	 * 报名
	 * @param integer $uid 用户编号
	 * @param array $data 报名数据
	 */
	public function signup($uid, $info){
		/*更新用户信息*/
		$data_model = model('user/UserData');
		$data = [];
		if (isset($info['birthday'])) $data['birth'] = $info['birthday'];
		if (isset($info['gender'])) $data['gender'] = $info['gender'];
		$data_model->save($data,['uid' => $uid]);
		/*增加职位进程*/
		if ($this->isSignup($uid) == false) $this->addJobProcess($uid, $info['job_id']);
		/*更新职位投递人数*/
		$business = controller('job/job', 'business');//定义业务对象
		$business->updateDeliveries($info['job_id'], 1);
		/*发布报名通知*/
		$this->notifyMediator->emitSignup($uid, $info['job_id']);
	}
	
	public function addJobProcess($uid, $job_id){
		$process_model = model('user/UserJobProcess');
		$process_model->user_id = $uid;
		$process_model->job_id = $job_id;
		$process_model->creat_time = time();
		$process_model->save();
	}
	
	/**
	 * 验证用户是否已经报名
	 * @param int $uid
	 * @param int $job_id
	 */
	public function checkSignup($uid, $job_id){
		$flag = false;//默认没有报名
		$process_model = model('user/UserJobProcess');
		$today = getdate();
		$condition = '`user_id`='.$uid.' AND `creat_time` > ' . mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
		//$rs = $process_model->where('user_id', '=', $uid)->where('job_id', '=', $job_id)->find();
		$rs = $process_model->where($condition)->find();
		if ($rs!=null) $flag = true;
		return $flag;
	}
	
	/**
	 * 快速登录
	 * 用户ID快速登录
	 */
	public function quickLogin($uid, $password){
		$service = controller('user/Uccenter', 'service');
		$user = $service->login($uid, $password, true);//ucenter接口登录
		if ($user['uid']>0){
			//获取用户真实姓名
			$info = $this->getInfo($user['uid']);
			$user['real_name'] = $info->profile->real_name;
		}
		return $user;
	}

	/**
	 * 推荐排行榜
	 */
	public function invite($num = 5){
		$res = model('user/Invite');
		$result = $res->limit($num)->order('amount','desc')->select();
		$newarr = new Collection($result);
		$result = $newarr->toArray();
		for( $i = 0; $i<count($result); $i++){
			$result[$i]['pictures'] = ' ' ? \think\Config::get('img_domain').'/logo.png' : $result[$i]['pictures'];
		}
		
		return $result;
	}


	/**
	 * 推荐新增
	 */
	public function addInvite($uid,$username,$mobile,$referral){
		$res = model('user/Invite');
		$data['user_id'] = $uid;
		$data['user_name'] = $username;
		$data['mobile'] = $mobile;
		$data['referral'] = $referral;
		$data['amount'] = 0;
		$data['create_time'] = intval(time());
		$result = $res->insert($data);
		
		return $result;
	}
	
	/**
	 * 手机号查找用户
	 */
	public function getUserByMobile($mobile){
		$return = [];
		$model = model('user/user');
		$user = $model->get(['mobile' => $mobile]);
		if ($user){
			$return['uid'] = $user->uid;
			//获取用户真实姓名
			$info = $this->getInfo($user->uid);
			$return['real_name'] = $info->profile->real_name;
		}
		return $return;
	}
	
	/**
	 * 验证用户今日是否已报名
	 * @param int $uid
	 */
	public function isSignup($uid){
		$flag = false;//默认没有报名
		$process_model = model('user/UserJobProcess');
		$condition = "`user_id`=$uid";
		$today = getdate();
		$condition .= ' AND `creat_time` > ' . mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
		$rs = $process_model->where($condition)->find();
		if ($rs!=null) $flag = true;
		return $flag;
	}

}