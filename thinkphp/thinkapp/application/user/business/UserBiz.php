<?php
namespace app\user\business;

use think\Config;
use think\Log;

use ylcore\Biz;
use ylcore\FileService;

use app\user\model\AppSubscribeJob;
use app\job\business\JobBiz;
use app\user\business\Notify;
use app\sms\business\ShortMessageBiz;
use app\user\model\User;
use app\user\model\UserData;
use app\user\service\Uccenter;
use app\user\model\Invite;

class UserBiz extends Biz
{
	const USER_CLIENT = 'app';
	protected $domainObjectFields = [
		'uid',
		'age',
		'gender',
		'nickname',
		'avatar',
		'distance'	
	];
	
	/**
	 * 出生日期转换成年龄
	 */
	public function birth2age($birth) {
		$age = 0;
		if ($birth) {
			$birth = substr(trim(($birth)), 0, 4);
			$now = date('Y', time());
			$age = $now - intval($birth);
		}
		return $age;
	}
	
	/**
	 * 获取用户头像
	 */
	public function getAvatar($uid, $gender = 0) {
		$avatar = '';
		$service = new Uccenter();
		if ($service->checkAvatar($uid)) {
			$avatar = $service->getAvatar($uid);
		} else {
			$avatar = Config::get('site_domain') . '/static/images/avatar.jpg';
		}
		return $avatar;
	}
	
	/**
	 * 手机密码登录
	 * @param string $mobile
	 * @param string $password
	 * @return array uid
	 */
	public function mobileAccountLogin($mobile, $password){
		$user_name = $this->getUsernameByMobile($mobile);
		if (! $user_name) return ['uid'=>-1];//用户不存在
		$user = $this->login($user_name, $password);
		return $user;
	}
	
	/**
	 * 登录
	 * 默认为用户名登录
	 * @return array uid
	 */
	public function login($user_name, $password){
		$service = new Uccenter();
		$user = $service->login($user_name, $password);//ucenter接口登录
		return $user;
	}
	
	/**
	 * 手机号查找用户名
	 * @return string
	 */
	public function getUsernameByMobile($mobile){
		$model = new User();
		$username = $model->where('mobile', $mobile)->value('uname');
		return $username;
	}
	
	/**
	 * 短信验证码登录
	 */
	public function mobileCodeLogin($mobile, $code) {
		$uid = 0;
		$biz = new ShortMessageBiz();
		$status = $biz->checkLoginCode($mobile, $code);//验证码验证
		if ($status) {
			$biz->finishLoginCode($mobile, $code);//完成验证
			$uid = $this->getUserIdByMobile($mobile);//获取用户编号
			if (! $uid) {
				/*注册一个新的用户*/
				$user = $this->mobileRegister($mobile, '');
				$uid = $user['uid'];
			}
		} else {
			$uid = -11;
		}
		return ['uid'=>$uid];
	}
	
	/**
	 * 手机号查找用户编号
	 */
	public function getUserIdByMobile($mobile){
		$model = new User();
		$user_id = $model->where('mobile', $mobile)->value('uid');
		return $user_id;
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
		$array = ['o', 'y', 'n', 'l', 'e', 'd', 'a', 'w', 'g', 'p'];
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
		$service = new Uccenter();
		return $service->isExists($user_name);
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
			$model = new User();
			$model->uid = $uid;
			$model->uname = $user_name;
			$model->password = $this->encrpyPassword($password);
			$model->from = isset($data['from'])?$data['from']:self::USER_CLIENT;
			if (isset($data['mobile'])) $model->mobile = $data['mobile'];
			if (isset($data['email'])) $model->email = $data['email'];
			$model->save();
			/*用户附表*/
			$data_model = new UserData();
			$data_model->uid = $uid;
			$data_model->reg_time = time();
			if (isset($data['realname'])) $data_model->real_name = $data['realname'];
			$data_model->save();
			/*系统通知*/
			$notify = new Notify();
			$notify->userRegister($uid, $data);
		} else {
			Log::record($service->message);
		}
		return $uid;
	}
	
	/**
	 * 密码加密
	 * @todo 用户加密方式需要统一
	 */
	public function encrpyPassword($password){
		$salt = substr(md5($password), -(strlen($password)%6+6), 6);
		$password = md5(md5($password) . $salt);
		return $password;
	}
	
	/**
	 * 短信验证码注册
	 */
	public function mobileCodeRegister($mobile, $code, $password, $realname) {
		$uid = 0;
		$biz = new ShortMessageBiz();
		$status = $biz->checkRegisterCode($mobile, $code);//验证码验证
		if ($status) {
			$biz->finishRegisterCode($mobile, $code);//完成验证
			/*注册一个新的用户*/
			$user = $this->mobileRegister($mobile, $password, ['realname'=>$realname]);
			$uid = $user['uid'];
		} else {
			$uid = -11;//验证码错误
		}
		return ['uid'=>$uid];
	}
	
	/**
	 * @override
	 * 格式化视图字段
	 * @param array $user
	 */
	public function formatViewField($user) {
        $item['real_name'] = $user['real_name'];
        $model = new User();
        $mobile = $model->where($user['uid'])->field('mobile')->find();
        $item['mobile'] = $mobile['mobile'];
        $item['avatar'] = $this->getAvatar($user['uid'], $user['gender']);
    	if ($user['nickname'] == null) $item['nickname'] = lang('anonymous');
    	else $item['nickname'] = $user['nickname'];
    	if ($user['gender'] == 1) $item['gender'] = lang('gender_1');
    	else if ($user['gender'] === '0') $item['gender'] = lang('gender_2');
    	else $item['gender'] = lang('secret');
        $item['hometown'] = $user['hometown'];
		return $item;
	}
	
	/**
	 * 获取用户的昵称
	 * @param int $id
	 */
	public function getNickname($id) {
		$user = new UserData();
		$sender_name = $user->where('uid', $id)->value('nickname');
		if ($sender_name == null) $sender_name = lang('anonymous');
		return $sender_name;
	}
	
	/**
	 * 订阅职位
	 * @param integer $id
	 */
	public function subscribeJob($job_id, $user_id) {
		$model = new AppSubscribeJob();
		$model->insert(['uid'=>$user_id, 'job_id'=>$job_id, 'base_time'=>time()], true);//replace into
	}
	
	/**
	 * 更新用户信息
	 * @param int $user_id
	 * @param array $data
	 */
	public function update($user_id, $data) {
		$fields = ['real_name', 'degree', 'gender', 'career', 'birth', 'nickname', 'hometown'];//可更新字段
		$update = [];
		$model = new UserData();
		foreach ($data as $field=>$value) {
			if (in_array($field, $fields)) $update[$field] = $value;
		}
		$return = false;
		if ($update) $return = $model->save($update, ['uid' => $user_id]) ? true : false;
		return $return;
	}
	
	/**
	 * 上传头像
	 * @param $uid integer
	 * @param $avatar string 图片base64编码
	 */
	public function uploadAvatar($uid, $avatar) {
		$return = false;
		$service = new FileService();
		$avatar = $service->base64Upload($avatar);//保存base64图片
		if ($avatar) {
			//保存为ucenter头像
			$uc_service = new Uccenter();
			$uc_service->saveAvatar($uid, $avatar['path']);
			$return = true;
		}
		return $return;
	}
	
	/**
	 * 获取用户资料
	 * @param int $uid
	 */
	public function getData($uid) {
		$model = new UserData();
		$user = $model->get($uid);
		return $this->formatViewField($user);
	}
	
	/**
	 * 我的订阅职位
	 * @param integer $id
	 */
	public function mySubscribeJob($user_id, $page = 1, $pagesize = 8) {
		$job_biz = new JobBiz;
		$where = $job_biz->defaultCondition();
		$model = new AppSubscribeJob();
		$list = $model->alias('subscribe')
			->join('Job job', 'job.id=subscribe.job_id')
			->where($where)
			->where('uid', $user_id)
			->page($page, $pagesize)
			->select();
		$return = $job_biz->o2a($list);
		return $return;
	}

	/**
	 * 获取用户详细
	 * @param integer $id
	 */
	public function get($id) {
		$model = new User();
		$model->get($id);
	}

    /**
     * 删除用户订阅信息
     * @param int $id
     * $param int $job_id
     */
    public function delete($id, $job_id)
    {
        $model = new AppSubscribeJob();
        $model->where(['uid' => $id, 'job_id' => $job_id])->delete();
    }

    /**
     * 更新密码
     * @param $uid
     * @param $pwd
     * @return mixed
     */
    public function updatePwd($uid, $pwd) {
        $service = new Uccenter();
        $username = $this->getUnameByUid($uid);
        $return = $service->updUcenterPwd($username, $pwd);
        if ($return) {
            /*密码加密*/
            $pwd = $this->encrpyPassword($pwd);
            $data['password'] = $pwd;
            $model = new User();
            $upd = $model->where("uid", $uid)->update($data);
            if ($upd) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * 获取用户名 uname
     * @param $uid
     * @return mixed
     */
    public function getUnameByUid($uid) {
        $model = new User();
        $username = $model->where("uid", $uid)->value("uname");
        return $username;
    }

    /**
     * 获取用户名 real_name
     * @param $uid
     * @return mixed
     */
    public function getRealNameByUid($uid) {
        $model = new UserData();
        $username = $model->where("uid", $uid)->value("real_name");
        return $username;
    }

    /**
     * 推荐职位入表
     * @param $data
     * @return bool
     */
    public function recommend($data) {
        $model = new Invite();
        $model->save($data);
        return true;
    }

    /**
     * 推荐职位查询
     */
    public function recommendSel($uid, $page, $pagesize, $keyword) {
        $where = [
            "user_id" => $uid
        ];
        if ($keyword) is_mobile($keyword) ? $where["mobile"] = $keyword : $where["referral"] = $keyword;
        $model = new Invite();
        $invObj = $model->where($where)
            ->page($page, $pagesize)
            ->field("referral,mobile,amount")
            ->select();
        $invArr = collection($invObj)->toArray();
        return $invArr;
    }

    /**
     * 更新用户手机号
     */
    public function changeMobileDo($uid, $mobile) {
        $model = new User();
        $oldMobile = $model->where("uid", $uid)->field('mobile')->find();
        if ($oldMobile['mobile'] == $mobile) return ['uid' => -1, 'mobile' => $mobile];//手机号码重复
        $model->where("uid", $uid)->update(['mobile' => $mobile]);
        return ['uid' => $uid, 'mobile' => $mobile];
    }
}