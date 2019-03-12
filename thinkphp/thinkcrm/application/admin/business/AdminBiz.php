<?php

namespace app\admin\business;

use ylcore\Biz;
use app\admin\model\Admin;
use think\Config;
use think\Log;

class AdminBiz extends Biz
{
	const ADMIN_TOKEN = 'all';
	/**
	 * @var \think\Model 模型类实例
	 */
	public $adminModel;
	
	/**
	 * 后台用户是否有效
	 * @access public
     * @param string  $user_name
     * @param string  $password
     * @return boolean
	 */
	public function isValidate($user_name, $password)
	{
		$return = false;
		if ($this->isAdministrator($user_name)){
			/*管理员取配置文件*/
			if ($this->encryptPassword($password) == Config::get('administrator_pwd')){
				$return = true;
			}
		} else {
			/*非管理员取数据库*/
			$user = model('Admin');
			$real_admin = $user->field('admin_pwd, status')->where('admin_name', $user_name)->find();
			if ($real_admin['status'] && $this->encryptPassword($password) == $real_admin['admin_pwd']){
				$return = true;
			}
		}
		return $return;
	}
	
	/**
	 * 是否为系统创建者
	 * @access public
	 * @param string $user_name
	 * @return boolean
	 */
	public function isAdministrator($user_name)
	{
		$return = false;
		if (strtolower($user_name) === 'administrator'){
			$return = true;
		}
		return $return;
	}
	
	/**
	 * 密码加密
	 * @access private
	 * @param string $password
	 * @return string
	 */
	private function encryptPassword($password)
	{
		return md5($password);
	}
	
	/**
	 * 获取后台用户信息
	 */
	public function getInfoByUserName($user_name)
	{
		$user = model('Admin');
		$this->adminModel = $user->get(["admin_name" => $user_name]);
	}
	
	/**
	 * 获取后台用户token键
	 * @access public
	 * @param string $user_name
	 * @return string
	 */
	public function getTokenKey($token){
		return 'ylcrm_admin_token#' . $token;
	}
	
	/**
	 * 获取客户端权限
	 */
	public function getToken($string, $core, $key = '', $expiry = 0, $operation = 'ENCODE') {

		$ckey_length = 4;
	
		$key = md5($key ? $key : $core);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
	
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
	
		$result = '';
		$box = range(0, 255);
	
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}
	
	/**
	 * 添加管理员
	 */
	public function add($data){
		if ($this->isBadAdminName($data['admin_name'])) return 0;//用户名不合法
		$model = model('Admin');
		$model->data([
				'admin_name'  =>  $data['admin_name'],
				'admin_pwd' =>  $this->encryptPassword($data['admin_pwd']),
				'create_at' => time()
		]);
		$model->save();//保存
		if ($model->id > 0){
			$this->initAdmin($model->id);//初始换管理员
		}
		return $model->id;
	}
	
	/**
	 * 获取所有管理员信息
	 */
	public function getAll()
	{
		$return = array();
		$model = model('admin');
		$list = $model->where('id', '>', 0)->select();
		if ($list){
			foreach ($list as $item){
				$return[$item['id']] = $item;
			}
		}
		return $return;
	}
	
	/**
	 * 获取管理员详情
	 * @param integer $id
	 */
	public function get($id){
		$model = model('admin');
		$obj = $model->get($id);
		if ($obj->role) $obj->role_name = $obj->role->role_name;
		else $obj->role_name = '';
		return $obj;
	}
	
	/**
	 * 更新信息
	 */
	public function update($id, $data){
		$model = model('admin/admin');
		$update = [];
		if (isset($data['admin_name']) && ! $this->isBadAdminName($data['admin_name'])){
			$update['admin_name'] = $data['admin_name'];
		}
		if (isset($data['admin_pwd']) && $data['admin_pwd']){
			$update['admin_pwd'] = $this->encryptPassword($data['admin_pwd']);
		}
		if (isset($data['status'])){
			$update['status'] = $data['status']?1:0;
		}
		if (isset($data['role_id'])){
			$update['role_id'] = $data['role_id'];
		}
		if (isset($data['is_admin'])){
			$update['is_admin'] = $data['is_admin']?1:0;
		}
		$model->save($update, ['id' => $id]);//更新
	}
	
	/**
	 * 获取登录用户的所有信息
	 * @param string $id
	 */
	public function getInformation($username){
		$return = ['admin'=>'', 'privileges'=>'', 'employee'=>'', 'organization'=>'', 'position'=>''];
		//1,后台用户基本信息
		$model = model('admin');
		$admin = $model->where('admin_name', '=', $username)->field('id, admin_name, status, role_id, is_admin')->find();
		$return['admin'] = $admin;
		//2,用户权限信息
		if ($admin != null) {
			$role_model = model('AdminRole');
			$privileges = $role_model->where('id', '=', $admin['role_id'])->value('privileges');
			$return['privileges'] = $privileges?unserialize($privileges):'';
		}
		//3,员工信息
		if ($admin != null) {
			$employee_model = model('Employee');
			$employee = $employee_model->where('admin_id', '=', $admin['id'])->find();
			$return['employee'] = $employee;
		}
		//4,组织机构信息
		if (isset($employee) && $employee) {
			$org_model = model('organization');
			$organization = $org_model->get($employee['org_id']);
			$return['organization'] = $organization;
		}
		//5,职位信息
		if (isset($employee) && $employee) {
			$pos_model = model('position');
			$position = $pos_model->get($employee['pos_id']);
			$return['position'] = $position;
		}
		//6,个人设置
		if ($admin != null) {
			$biz = controller('SettingBiz', 'business');
			$setting = $biz->getSetting($admin['id']);
			$return['setting'] = $setting;
		}
		//7,个人数据
		if (isset($employee) && $employee) {
			$biz = controller('SettingBiz', 'business');
			$statistics = $biz->getEmployeeStatistics($employee['id']);
			$return['statistics'] = $statistics;
		}
		//8,个人收藏
		if (isset($employee) && $employee) {
			$biz = controller('SettingBiz', 'business');
			$favorite = $biz->getEmployeeFavorite($employee['id']);
			$return['favorite'] = $favorite;
		}
		return $return;
	}
	
	/**
	 * 注册后台用户名是否合法
	 */
	public function isBadAdminName($name){
		$flag = false;//默认合法
		if ($this->isAdministrator($name)) {
			$flag = true;//超级管理员不能创建
		} else if (strpos($name, 'root') !== false) {
			$flag = true;
		} else if (strpos($name, 'admin') !== false) {
			$flag = true;
		}
		return $flag;
	}
	
	/**
	 * 设置管理员缓存
	 */
	public function setAdminToken(){
		return self::ADMIN_TOKEN;
	}
	
	/**
	 * 判断是否为管理员缓存
	 */
	public function isAdminToken($token){
		$flag = false;
		if ($token == self::ADMIN_TOKEN) $flag = true;
		else if(isset($token['admin']['is_admin']) && $token['admin']['is_admin']>0){
			$flag = true;
		}
		return $flag;
	}
	
	/**
	 * 获取管理员信息
	 */
	public function getAdminInfomation(){
		
	}
	
	/**
	 * 初始换管理员信息
	 * @param integer $id
	 */
	public function initAdmin($id){
		/*1,初始化用户设置*/
		$model = model('AdminSetting');
		$model->id = $id;
		$model->save();
	}
	
	/**
	 * 获取后台用户上传文件缓存键
	 * @access public
	 * @param mixed $id 用户
	 * @param string $file_name 上传文件名
	 * @return string
	 */
	public function getUploadKey($user, $operate){
		if (is_array($user) && isset($user['admin']['id'])) $key = $user['admin']['id'];
		else $key = 0;
		return 'ylcrm_admin_upload' . $key . '#' . $operate;
	}
	
	/**
	 * 判断是否为系统创始人
	 */
	public function isAdministratorToken($token){
		$flag = false;
		if ($token == self::ADMIN_TOKEN) $flag = true;
		return $flag;
	}
	
	public function getOrganizationId($token){
		return isset($token['employee']['org_id'])?$token['employee']['org_id']:0;
	}
}