<?php
namespace app\admin\business;

use think\Cache;
use think\Config;
use think\Log;

use ylcore\Biz;
use app\admin\model\Employee;

class EmployeeBiz extends Biz
{
	const SIGNED_USER = 2;//分配人选
	const SURE_USER = 1;//确认人选
    
    /**
     * 添加员工
     */
    public function save($data){
        $model = model('Employee');
        $model->data([
                'real_name'  =>  $data['real_name'],
                'join_at' =>  $data['join_at'],
                'phone' =>  $data['phone'],
                'gender' =>  $data['gender'],
                'nickname' =>  $data['nickname'],
        		'number' =>  $data['number']
        ]);
        $model->save();//保存
        if ($model->id > 0){
        	$this->initEmployee($model->id);//初始换管理员
        }
        return $model->id;
    }
    
    /**
     * 获取所有员工信息
     */
    public function getAll()
    {	
        $return = array();
        $model = model('admin/Employee');
        $list = $model->select();
        if ($list){
            foreach ($list as $item){
                $return[$item['id']] = $item;
            }
        }
        return $return;
    }
    
    
    public function get($id){
        $model = model('Employee');
        $obj = $model->get($id);
        /*后台用户名*/
        if ($obj->admin) $obj->admin_name = $obj->admin->admin_name;
        else $obj->admin_name = "";
        /*组织机构名*/
        if ($obj->organization) $obj->org_name = $obj->organization->org_name;
        else $obj->org_name = "";
        /*职位名称*/
        if ($obj->position) $obj->pos_name = $obj->position->pos_name;
        else $obj->pos_name = "";
        return $obj;
    }
    
    /**
     * 更新员工信息
     */
    public function update($id, $data){
        $model = model('admin/Employee');
        $update = [];
        if (isset($data['real_name']) && $data['real_name']){
            $update['real_name'] = $data['real_name'];
        }
        if (isset($data['phone'])){
            $update['phone'] = $data['phone'];
        }
        if (isset($data['gender'])){
            $update['gender'] = $data['gender'];
        }
        if (isset($data['join_at'])){
            $update['join_at'] = $data['join_at'];
        }
        if (isset($data['nickname'])){
            $update['nickname'] = $data['nickname'];
        }
        if (isset($data['org_id'])){
            $update['org_id'] = $data['org_id'];
        }
        if (isset($data['pos_id'])){
            $update['pos_id'] = $data['pos_id'];
        }
        if (isset($data['avatar'])){
        	$update['avatar'] = $data['avatar'];
        }
        if (isset($data['number'])){
        	$update['number'] = $data['number'];
        }
        if (isset($data['status'])){
        	$update['status'] = $data['status'];
        	if ($update['status']==0){
        		//员工离职
        		$this->outduty($id);
        	}
        }
        if (isset($data['admin_id'])){
        	$model = model('Employee');
        	$obj = $model->where('admin_id',$data['admin_id'])->find();
        	if (!empty($obj)){
        		return 0;
        	}else{
            	$update['admin_id'] = $data['admin_id'];
            }	
        }
        $model->save($update, ['id' => $id]);//更新
        return $id;
    }
	
    /**
     * 获取默认头像
     * @param number $gender
     */
    public function getDefaultAvatar($gender = 0){
    	return $gender==0?lang('female_avatar_url'):lang('male_avatar_url'); 
    }
    
    public function getCache(){
    	$cache_biz = controller('admin/CacheBiz', 'business');
    	$cache_key = $cache_biz->getEmployeeKey();
    	$list = Cache::get($cache_key);
    	if (empty($list)) {
    		$list = $this->getAll();//获取所有组织架构
    		Cache::set($cache_key, $list, Config::get('token_expire'));//设置缓存
    	}
    	return $list;
    }
    
    /**
     * 员工分组
     */
    public function groupEmployeeJoinAt(){
    	$list = $this->getCache();
    	$group = ['new'=>[], 'old'=>[]];
    	foreach($list as $item){
    		if ($this->isNewEmployee($item)) $group['new'][] = $item['id'];
    		else $group['old'][] = $item['id'];
    	}
    	return $group;
    }
    
    /**
     * 是否为新人
     */
    public function isNewEmployee($employee){
    	$flag = false;
    	/*入职时间在两个月之内属于新人*/
    	if ($employee['join_at'] >= date('Y-m-d', time() - 60*60*24*62)){
    		$flag = true;
    	}
    	return $flag;
    }
    
    /**
     * 是否在职
     * @param array $employee
     */
    public function isOnDuty($employee){
    	return $employee['status']>0;	
    }
    
    /**
     * 获取新的报名用户
     * @param int $page
     * @param int $pagesize
     * @param string $search
     * @param int $employee_id
     */
    public function getNewJobProcess($page, $pagesize, $search, $employee_id=0){
    	$return = [];
    	$condition = $this->newJobProcessCountCondition($employee_id);
    	if ($condition!=''){
    		$business = controller('user/user', 'business');
    		$list = $business->getJobProcess($page, $pagesize, $search, true, 'creat_time', 'desc', $condition);
    		if ($list){
    			foreach ($list as $item){
    				$signup = [];
    				$signup['user_id'] = $item['user_id'];
    				$signup[lang('real_name')] = $item['real_name'];
    				$signup[lang('mobile')] = $item['mobile'];
    				$signup[lang('job_name')] = $item['job_name'];
    				$signup[lang('creat_time')] = $item['creat_time'];
    				$return[] = $signup;
    			}
    		}
    	}
    	return $return;
    }
    
    /**
     * 获取新的报名用户总数
     */
    public function getNewJobProcessCount($search, $employee_id){
    	$count = 0;
    	$condition = $this->newJobProcessCountCondition($employee_id);
    	if ($condition){
    		$business = controller('user/user', 'business');
    		$count = $business->getJobProcessCount($search, $condition);
    	}
    	return $count;
    }
    
    private function newRegisterUsers($employee_id){
    	$arr_uid = [];
    	$biz = controller('customer/CandidateBiz', 'business');
    	/*获取所有候选人的手机号码*/
    	$phones = $biz->employeeAllPhone($employee_id);
    	if ($phones){
    		$condition_tmp = "is_vip IS NULL";
    		/*搜索未分配注册用户*/
    		$model = model('user/user');
    		$uids = $model->where($condition_tmp)->field('uid, mobile')->select();
    		if ($uids){
    			foreach($uids as $item){
    				if (in_array($item['mobile'], $phones)) $arr_uid[] = $item['uid'];
    			}
    		}
    	}
    	return $arr_uid;
    }
    
    private function newJobProcessCountCondition($employee_id){
    	$condition = "";
    	$arr_uid = $this->newRegisterUsers($employee_id);
    	if ($arr_uid) $condition = "`user_id` IN (" . implode(',', $arr_uid) . ") AND adviser_id IS NULL";
    	return $condition;
    }
    
    /**
     * 获取新的免费注册用户
     */
    public function getNewRegister($page, $pagesize, $search, $employee_id=0){
    	$return = [];
    	$condition = $this->newUserCountCondition($employee_id);
    	if ($condition){
    		$business = controller('user/user', 'business');
    		$list = $business->getAll($page, $pagesize, $search, false, 'uid', 'desc', true, $condition);
    		if ($list){
    			foreach ($list as $item){
    				$signup = [];
    				$signup['user_id'] = $item['uid'];
    				$signup[lang('real_name')] = $item['real_name'];
    				$signup[lang('mobile')] = $item['mobile'];
    				$signup[lang('from')] = $item['from'];
    				$signup[lang('reg_time')] = $item['reg_time'];
    				$return[] = $signup;
    			}
    		}
    	}
    	return $return;
    }
    
    private function newUserCountCondition($employee_id){
    	$condition = "";
    	$arr_uid = $this->newRegisterUsers($employee_id);
    	if ($arr_uid) $condition = "`uid` IN (" . implode(',', $arr_uid) . ") AND is_vip IS NULL";
    	return $condition;
    }
    
    /**
     * 获取新的注册用户总数
     */
    public function getNewRegisterCount($search, $employee_id){
    	$count = 0;
    	$condition = $this->newUserCountCondition($employee_id);
    	if ($condition){
    		$business = controller('user/user', 'business');
    		$count = $business->getCount($search, false, $condition);
    	}
    	return $count;
    }
    
    /**
     * 获取员工所在的组织机构
     */
    public function getOrganization($employee_id){
    	$organization = ['employee'=>['id'=>$employee_id, 'real_name'=>''], 'org'=>['org_name'=>'', 'nickname'=>'']];
    	$list = $this->getCache();
    	if (isset($list[$employee_id])) {
    		$organization['employee'] = $list[$employee_id];
    		$biz = controller('admin/OrganizationBiz', 'business');
    		$org_list = $biz->getCache();
    		foreach($org_list as $org){
    			if ($org['id']==$list[$employee_id]['org_id']) {
    				$organization['org'] = $org;
    				break;
    			}
    		}
    	}
    	return $organization;
    }
    
    /**
     * 初始化员工
     */
    private function initEmployee($id){
    	/*1,初始化员工数据*/
    	$model = model('EmployeeStatistics');
    	$model->id = $id;
    	$model->save();
    }
    
    /**
     * 获取顾问的保留名额
     */
    public function getCandidateRemainsCeil($id){
    	$model = model('admin/EmployeeStatistics');
    	$remains = $model->where('id',$id)->value('remains');
    	return $remains;
    }
    
    /**
     * 获取顾问的保留人选总数
     * @param int $id
     */
    public function getCandidateRemains($id){
    	$model = model('customer/Candidate');
    	$count = $model->where('owner_id', $id)->where('is_remain', '1')->count();
    	return $count;
    }
    
    /**
     * 验证顾问保留名额是否足够
     * @param int $id 顾问编号
     */
    public function checkCandidateRemains($id){
    	$max = $this->getCandidateRemainsCeil($id);
    	$count = $this->getCandidateRemains($id);
    	return $max > $count;
    }
    
    /**
     * 获取顾问的人选库容
     */
    public function getCandidateMax($id){
    	$model = model('admin/EmployeeStatistics');
    	$remains = $model->where('id',$id)->value('candidates');
    	return $remains;
    }
    
    /**
     * 获取顾问的人选总数
     * 入职人选不算是人选
     * @param int $id
     */
    public function getCandidatesTotal($id){
    	$model = model('customer/Candidate');
    	$count = $model->where('owner_id', $id)->where('is_deleted=0 AND status!=4')->count();
    	return $count;
    }
    
    /**
     * 验证顾问人选库容是否已满
     * @param int $id 顾问编号
     */
    public function checkCandidateMax($id){
    	$max = $this->getCandidateMax($id);
    	$count = $this->getCandidatesTotal($id);
    	return $max - $count;
    }
    
    /**
     * 获取所有的顾问
     * @param $duty boolean 是否在职
     */
    public function getAllAdvisers($duty = true){
    	$return = array();
    	$model = model('admin/Employee');
    	$list = $model->alias('employee')->join('position', 'position.id=pos_id')->where('is_adviser','=',1)->field('employee.id,nickname,real_name,status,level,org_id')->order('is_manager desc')->select();
    	if ($list){
    		foreach ($list as $item){
    			if ($duty) {
    				if (! $this->isOnDuty($item)) continue;
    			} else {
    				if ($this->isOnDuty($item)) continue;
    			}
    			$return[$item['id']] = $item;
    		}
    	}
    	return $return;
    }
    
    /**
     * 获取顾问的保留名额
     */
    public function getAllStatistics(){
    	$return = [];
    	$model = model('admin/EmployeeStatistics');
    	$list = $model->where('id > 0')->select();
    	if ($list){
    		foreach ($list as $item){
    			$return[$item['id']] = $item;
    		}
    	}
    	return $return;
    }
    
    /**
     * 员工离职
     */
    public function outduty($id){
    	$model = model('admin/Employee');
    	$model->save(['status'=>0], ['id' => $id]);//更新
    	$model = model('Employee');
    	$obj = $model->get($id);
    	if ($obj['admin_id']){
    		$admin_model = model('admin/Admin');
    		$admin_model->save(['status'=>0], ['id' => $obj['admin_id']]);
    	}
    	return $id;
    }
    
    /**
     * 获取客户池公开认领客户的时间
     */
    public function getRecognizeOpenTime(){
    	Config::load(APP_PATH.'customer/config.php');
    	$arr_date = getdate();
    	$time = mktime(0, 0, 0, $arr_date['mon'], $arr_date['mday'], $arr_date['year']);
    	return $time - Config::get('recognize_open_day') * 3600 * 24;
    }
    
    /**
     * 获取顾问客户池认领客户的时间
     * @param int $prior_time
     */
    public function getRecognizeTime($prior_time=0){
    	$open_time = $this->getRecognizeOpenTime();
    	/*认领时间开始之后可以看到前一天的数据*/
    	if ($this->isRecognizeStart($prior_time)) $open_time = $open_time + 24 * 3600;
    	return $open_time;
    }
    
    /**
     * 认领是否已开始
     * @param number $prior_time 开始优先时间(分)
     */
    public function isRecognizeStart($prior_time=0){
    	$flag = false;//默认没有开始
    	$current = date('H:i', time());
    	$floor = Config::get('recognize_open_hour');
    	if ($prior_time>0){
    		$floor = (Config::get('recognize_open_hour') - 1) . ':' . (60-$prior_time);
    	}    	
    	if ($current>=$floor){
    		$flag = true;//已开始认领
    	}
    	return $flag;
    }
    
    /**
     * 认领自己丢弃客户的时间
     */
    public function getRecognizeSelfPersonalTime($prior_time=0){
    	Config::load(APP_PATH.'customer/config.php');
    	$arr_date = getdate();
    	$time = mktime(0, 0, 0, $arr_date['mon'], $arr_date['mday'], $arr_date['year']);
    	$time = $time - Config::get('personal_depose_self_recognize') * 3600 * 24;
    	/*认领时间开始之后可以看到前一天的数据*/
    	if ($this->isRecognizeStart($prior_time)) $time = $time + 24 * 3600;
    	return $time;
    }
    
    /**
     * 认领系统丢弃的自己客户的时间
     */
    public function getRecognizeSelfSystemTime($prior_time=0){
    	Config::load(APP_PATH.'customer/config.php');
    	$arr_date = getdate();
    	$time = mktime(0, 0, 0, $arr_date['mon'], $arr_date['mday'], $arr_date['year']);
    	$time = $time - Config::get('sys_depose_self_recognize') * 3600 * 24;
    	/*认领时间开始之后可以看到前一天的数据*/
    	if ($this->isRecognizeStart($prior_time)) $time = $time + 24 * 3600;
    	return $time;
    }
    
    /**
     * 认领同组顾问丢弃客户的时间
     */
    public function getRecognizeGroupPersonalTime($prior_time=0){
    	Config::load(APP_PATH.'customer/config.php');
    	$arr_date = getdate();
    	$time = mktime(0, 0, 0, $arr_date['mon'], $arr_date['mday'], $arr_date['year']);
    	$time = $time - Config::get('personal_depose_org_recognize') * 3600 * 24;
    	/*认领时间开始之后可以看到前一天的数据*/
    	if ($this->isRecognizeStart($prior_time)) $time = $time + 24 * 3600;
    	return $time;
    }
    
    /**
     * 认领系统丢弃的同组顾问客户的时间
     */
    public function getRecognizeGroupSystemTime($prior_time=0){
    	Config::load(APP_PATH.'customer/config.php');
    	$arr_date = getdate();
    	$time = mktime(0, 0, 0, $arr_date['mon'], $arr_date['mday'], $arr_date['year']);
    	$time = $time - Config::get('sys_depose_org_recognize') * 3600 * 24;
    	/*认领时间开始之后可以看到前一天的数据*/
    	if ($this->isRecognizeStart($prior_time)) $time = $time + 24 * 3600;
    	return $time;
    }
    
    /**
     * 根据员工编号获取相应的用户编号
     */
    public function employeeMappingAdmin($employee_id){
    	$admin_id = 0;
    	$list = $this->getCache();
    	if (isset($list[$employee_id])) $admin_id = $list[$employee_id]['admin_id'];
    	return $admin_id;
    }
    
    /**
     * 顾问按照组织分组
     */
    public function groupAdviserOrg(){
    	$list = $return = [];
    	$result = $this->getAllAdvisers();
    	$org_biz = controller('admin/OrganizationBiz', 'business');
    	
    	foreach($result as $employee){
    		$key = $employee['org_id'];
    		if (! isset($return[$key])){
    			$org = $org_biz->getOrgDetail($key);
    			$return[$key] = ['org_name'=>$org['org_name'], 'list'=>[], 'order'=>$org['listorder']];
    		}
    		$return[$key]['list'][] = $employee;
    	}
    	foreach($return as $group){
    		$list[$group['order']] = $group;
    	}
    	return $list;
    }
    
    /**
     * 显示客户池公开认领客户的时间
     */
    public function showRecognizeOpenTime(){
    	Config::load(APP_PATH.'customer/config.php');
    	return Config::get('recognize_open_hour') . ':' . '00';
    }
    
    /**
     * 显示顾问客户池认领客户的时间
     * @param int $prior_time
     */
    public function showRecognizeTime($prior_time=0){
    	Config::load(APP_PATH.'customer/config.php');
    	$str_time = $this->showRecognizeOpenTime();
    	if ($prior_time) $str_time = (Config::get('recognize_open_hour')-1) . ':' . (60 - $prior_time);
    	return $str_time;
    }
    
    /**
     * 获取顾问剩余保留名额
     * @param int $id 顾问编号
     */
    public function getCandidateRemainsLeft($id){
    	$max = $this->getCandidateRemainsCeil($id);
    	$count = $this->getCandidateRemains($id);
    	return $max - $count;
    }

    private function getRegisterCondition($employee_id, $scope, $search) {
    	$condition = "`owner_id`=$employee_id AND `is_deleted`=0";
    	switch ($scope) {
    		/*全部*/
    		case 'all': {
    			break;
    		}
    		/*未确认*/
    		case 'unsure': {
    			$condition .= " AND (`is_vip`=0 OR `is_vip` IS NULL)";
    			break;
    		}    		
    		/*已确认*/
    		case 'is_sure': {
    			$condition .= " AND `is_vip`=" . self::SURE_USER;
    			break;
    		}
    		/*已分配*/
    		case 'signned': {
    			$condition .= " AND `is_vip`=" . self::SIGNED_USER;
    			break;
    		}
    		default: ;
    	}
    	if ($search){
    		if (is_mobile($search)) $condition .= " AND `mobile` = '$search'";
    		elseif (is_numeric($search)) $condition .= " AND `mobile` LIKE '$search%'";
    		elseif ($search != '') $condition .= " AND `cp`.`real_name` LIKE '$search%'";
    	}
    	
    	return $condition;
    }
    
    /**
     * 获取所有注册用户
     * @param int $employee_id 顾问编号
     * @param string $scope 范围
     * @param array $search 搜索条件
     * @param int $page 分页
     * @param int $pagesize 每页记录数
     */
    public function getRegisterUser($employee_id, $scope, $search, $page = 1, $pagesize = 50, $order = 'reg_time desc'){
    	$return = [];
    	$condition = $this->getRegisterCondition($employee_id, $scope, $search);
    	$model = model('customer/candidate');
    	$list = $model
    		->alias('cd')
    		->join('CustomerPool cp', 'cd.cp_id=cp.id')
    		->join('User user', 'cp.phone=user.mobile')
    		->join('UserData ud', 'ud.uid=user.uid')
    		->where($condition)
    		->field('user.uid,mobile,cp.real_name,is_vip,reg_time,user.from')
    		->page($page, $pagesize)
    		->order($order)
    		->select();
    	if ($list){
    		foreach ($list as $item){
    			$signup = [];
    			$signup['user_id'] = $item['uid'];
    			$signup[lang('real_name')] = $item['real_name'];
    			$signup[lang('mobile')] = $item['mobile'];
    			$signup[lang('from')] = $item['from'];
    			$signup[lang('reg_time')] = date('Y-m-d H:i:s', $item['reg_time']);
    			$status = lang('unsure');
    			if ($item['is_vip'] == self::SURE_USER) $status = lang('is_sure');
    			else if ($item['is_vip'] == self::SIGNED_USER) $status = lang('signned');
    			$signup[lang('is_vip')] = $status;
    			$return[] = $signup;
    		}
    	}
    	return $return;
    }
    
    /**
     * 获取注册用户总数
     * @param int $employee_id 顾问编号
     * @param string $scope 范围
     * @param array $search 搜索条件
     */
    public function getRegisterUserCount($employee_id, $scope, $search){
    	$condition = $this->getRegisterCondition($employee_id, $scope, $search);
    	$model = model('customer/candidate');
    	$count = $model
	    	->alias('cd')
	    	->join('CustomerPool cp', 'cd.cp_id=cp.id')
	    	->join('User user', 'cp.phone=user.mobile')
	    	->where($condition)
	    	->count();
    	return $count;
    }
    
    private function getSignupCondition($employee_id, $scope, $search) {
    	$condition = "`owner_id`=$employee_id AND `is_deleted`=0";
    	switch ($scope) {
    		/*全部*/
    		case 'all': {
    			break;
    		}
    		/*未确认*/
    		case 'unsure': {
    			$condition .= " AND `is_assign` = 0";
    			break;
    		}
    		/*已确认*/
    		case 'is_sure': {
    			$condition .= " AND `is_assign`=" . self::SURE_USER;
    			break;
    		}
    		/*已分配*/
    		case 'signned': {
    			$condition .= " AND `is_assign`=" . self::SIGNED_USER;
    			break;
    		}
    		default: ;
    	}
    	if ($search){
    		if (is_mobile($search)) $condition .= " AND `mobile` = '$search'";
    		elseif (is_numeric($search)) $condition .= " AND `mobile` LIKE '$search%'";
    		elseif ($search != '') $condition .= " AND `cp`.`real_name` LIKE '$search%'";
    	}
    	 
    	return $condition;
    }
    
    /**
     * 获取所有报名用户
     * @param int $employee_id 顾问编号
     * @param string $scope 范围
     * @param array $search 搜索条件
     * @param int $page 分页
     * @param int $pagesize 每页记录数
     */
    public function getSignupUser($employee_id, $scope, $search, $page = 1, $pagesize = 50, $order = 'creat_time desc'){
    	$return = [];
    	$condition = $this->getSignupCondition($employee_id, $scope, $search);
    	$model = model('customer/candidate');
    	$list = $model
	    	->alias('cd')
	    	->join('CustomerPool cp', 'cd.cp_id=cp.id')
	    	->join('User user', 'cp.phone=user.mobile')
	    	->join('UserJobProcess signup', 'signup.user_id=user.uid')
	    	->join('Job job', 'signup.job_id=job.id')
	    	->where($condition)
	    	->field('user.uid,mobile,cp.real_name,is_assign,creat_time,job.job_name')
	    	->page($page, $pagesize)
	    	->order($order)
	    	->select();
    	if ($list){
    		foreach ($list as $item){
    			$signup = [];
    			$signup['user_id'] = $item['uid'];
    			$signup[lang('real_name')] = $item['real_name'];
    			$signup[lang('mobile')] = $item['mobile'];
    			$signup[lang('job_name')] = $item['job_name'];
    			$signup[lang('creat_time')] = date('Y-m-d H:i:s', $item['creat_time']);
    			$status = lang('unsure');
    			if ($item['is_assign'] == self::SURE_USER) $status = lang('is_sure');
    			else if ($item['is_assign'] == self::SIGNED_USER) $status = lang('signned');
    			$signup[lang('is_vip')] = $status;
    			$return[] = $signup;
    		}
    	}
    	return $return;
    }
    
    /**
     * 获取报名用户总数
     * @param int $employee_id 顾问编号
     * @param string $scope 范围
     * @param array $search 搜索条件
     */
    public function getSignupUserCount($employee_id, $scope, $search){
    	$condition = $this->getSignupCondition($employee_id, $scope, $search);
    	$model = model('customer/candidate');
    	$count = $model
    	->alias('cd')
    	->join('CustomerPool cp', 'cd.cp_id=cp.id')
    	->join('User user', 'cp.phone=user.mobile')
    	->join('UserJobProcess signup', 'signup.user_id=user.uid')
    	->where($condition)
    	->count();
    	return $count;
    }
    
    /**
     * 我的二维码
     */
    public function myQrcode($uid) {
    	$image = 'static/images/qrcode/' . $uid . '.png';
    	if (file_exists($image) == false || true) {
    		$biz = controller('customer/Market', 'business');
    		$qrcode = $biz->adviserQrcode($uid);
    		$url = Config::get('market_domain') . '/wechat/web?ylmcode=' . $qrcode;
    		$this->qrcode($url, $image, 6);
    	}
    	return Config::get('site_domain') . DS . $image;
    }
    
    /**
     * 生成二维码
     * @param int $size 图片大小
     * @param string $errorCorrectionLevel 容错级别：L、M、Q、H
     * @param int $matrixPointSize 边距：1到10
     * @return void
     */
    private function qrcode($url, $file = false, $size = 4, $errorCorrectionLevel = "Q", $matrixPointSize = 2) {
    	//加载第三方类库
    	vendor('phpqrcode.phpqrcode');
    	//实例化
    	$qr = new \QRcode();
    	//会清除缓冲区的内容，并将缓冲区关闭，但不会输出内容。
    	ob_end_clean();
    	//输出二维码
    	$qr::png($url, $file, $errorCorrectionLevel, $size, $matrixPointSize);
    	//添加logo
    	if ($file !== false) {
    		//logo图片
    		$logo = 'static/images/logo.png';    		
    		$QR = imagecreatefromstring(file_get_contents($file));
    		$logo = imagecreatefromstring(file_get_contents($logo));
    		$QR_width = imagesx($QR);//二维码图片宽度
    		$QR_height = imagesy($QR);//二维码图片高度
    		$logo_width = imagesx($logo);//logo图片宽度
    		$logo_height = imagesy($logo);//logo图片高度
    		$logo_qr_width = $QR_width / 5;
    		$scale = $logo_width/$logo_qr_width;
    		$logo_qr_height = $logo_height/$scale;
    		$from_width = ($QR_width - $logo_qr_width) / 2;
    		//重新组合图片并调整大小
    		imagecopyresampled(
    			$QR, 
    			$logo, 
    			$from_width, 
    			$from_width, 
    			0, 
    			0, 
    			$logo_qr_width,
    			$logo_qr_height, 
    			$logo_width, 
    			$logo_height
    		);
    	}
    	//输出图片
    	imagepng($QR, $file);
    }
    
    /**
     * @implement
     * 网站呼入人选接口
     */
    public function getCallinCustomers($employee_id, $from, $sure, $search, $order, $page = 1, $pagesize = 50) {
    	if ($from == 'assigned') {
    		$scope = 'signned';
    	} elseif ($sure === true) {
    		$scope = 'is_sure';
    	} elseif ($sure === false) {
    		$scope = 'unsure';
    	} else {
    		$scope = 'all';
    	}
    	$list = $this->getRegisterUser($employee_id, $scope, $search, $page = 1, $pagesize = 50);
    	$count = $this->getRegisterUserCount($employee_id, $scope, $search);
    	return ['list' => $list, 'count' => $count];
    }
    
    /**
     * @implement
     * 网站呼入人选动态接口
     */
    public function trackCallinCustomers($employee_id, $from, $sure, $search, $order, $page = 1, $pagesize = 50) {
    	if ($from == 'assigned') {
    		$scope = 'signned';
    	} elseif ($sure === true) {
    		$scope = 'is_sure';
    	} elseif ($sure === false) {
    		$scope = 'unsure';
    	} else {
    		$scope = 'all';
    	}
    	$list = $this->getSignupUser($employee_id, $scope, $search, $page, $pagesize);
    	$count = $this->getSignupUserCount($employee_id, $scope, $search);
    	return ['list' => $list, 'count' => $count];
    }
}