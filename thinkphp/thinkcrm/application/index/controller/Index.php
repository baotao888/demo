<?php
namespace app\index\controller;

use think\Controller;
use think\Cache;
use think\Config;
use think\Log;

use app\user\business\CallinFactory;
use ylcore\FileService;
use ylcore\Format;

class Index extends Controller
{
	/**
	 * 公共信息
	 */
    public function index()
    {
    	/*1,获取当前登录用户信息*/
    	$profile = [
    		'real_name'=>'', 
    		'avatar'=>lang('male_avatar_url'), 
    		'nickname'=>'', 
    		'is_manager'=>0, 
    		'employee_id'=>0, 
    		'gender'=>1,
    		'pos_name'=>lang('no_position'),
    		'pos_level'=>''
    	];
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	if ($admin_business->isAdministratorToken($admin_information)) {
    		$profile['real_name'] = lang('admin');
    		$profile['nickname'] = lang('admin');
    		$profile['is_manager'] = 1;
    		$profile['pos_name'] = lang('admin');
    	} else {
    		if ($admin_information['employee']) {
    			$employee_biz = controller('admin/EmployeeBiz', 'business');
    			$profile['real_name'] = $admin_information['employee']['real_name'];
    			$profile['avatar'] = $admin_information['employee']['avatar']?$admin_information['employee']['avatar']:$employee_biz->getDefaultAvatar($admin_information['employee']['gender']);
    			$profile['nickname'] = $admin_information['employee']['nickname'];
    			$profile['employee_id'] = $admin_information['employee']['id'];
    			$profile['gender'] = $admin_information['employee']['gender'];
    			if ($admin_information['position']) {
    				$profile['is_manager'] = $admin_information['position']['is_manager'];
    				$profile['pos_name'] = $admin_information['position']['pos_name'];
    				$profile['pos_level'] = $admin_information['position']['level'];
    			} else {
    				$profile['is_manager'] = 0;
    				$profile['pos_name'] = lang('no_position');
    				$profile['pos_level'] = '';
    			}
    		} else {
    			$profile['real_name'] = $admin_information['admin']['admin_name'];
    		}
    	}
    	/*2, 获取用户的菜单权限*/
    	$menu = ['web'=>false, 'control'=>false, 'team'=>false];
    	if ($admin_business->isAdministratorToken($admin_information)) {
    		$menu['web'] = true;
    		$menu['control'] = true;
    		$menu['team'] = true;
    		$menu['recruit'] = true;
    	} else {
    		if ($admin_information['setting']) {
    			if ($admin_information['setting']['system']){
    				$menu['web'] = $admin_information['setting']['system']['menu']['web'];
    				$menu['control'] = $admin_information['setting']['system']['menu']['control'];
    				$menu['team'] = isset($admin_information['setting']['system']['menu']['team'])?$admin_information['setting']['system']['menu']['team']:false;
    				$menu['recruit'] = isset($admin_information['setting']['system']['menu']['recruit'])?$admin_information['setting']['system']['menu']['recruit']:false;
    			}
    		}
    	}
    	/*3,获取用户的收藏菜单*/
    	$favorite = [];
    	if (! $admin_business->isAdministratorToken($admin_information)) {
    		if (isset($admin_information['favorite'])) {
    			$favorite = $admin_information['favorite'];
    		}
    	}
    	return ['profile' => $profile, 'menu'=>$menu, 'favorite'=>$favorite];
    }
	
    /**
     * 个人信息
     */
    public function profile(){
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	$employee_biz = controller('admin/EmployeeBiz', 'business');
    	if( $admin_business->isAdministratorToken($admin_information) ){
    		$profile['real_name'] = lang('admin');
    		$profile['avatar'] = lang('male_avatar_url');
    		$profile['nickname'] = lang('admin');
    		$profile['is_manager'] = 1;
    		$profile['position_name'] = lang('admin');
    		$profile['join_at'] = lang('unknown');
    		$profile['phone'] = lang('unknown');
    		$profile['gender'] = 1;
    		$profile['organization'] = lang('admin');
    		$profile['employee_id'] = 0;
    		$profile['admin'] = 0;
    		$profile['admin_name'] = 'administrator';
    		$profile['pos_level'] = '';
    		$profile['number'] = 0;    		
    		$profile['sti_candidates'] = lang('no_limit');
    		$profile['sti_remains'] = lang('no_limit');
    		$profile['sti_remain_days'] = lang('no_limit');
    		$profile['recognize_prior_time'] = date('H:i', $employee_biz->getRecognizeOpenTime());
    		$profile['recognize_priors'] = 0;
    		$profile['my_qrcode'] = false;
    	} else {
    		if ($admin_information['employee']) {
    			$profile['real_name'] = $admin_information['employee']['real_name'];
    			$profile['avatar'] = $admin_information['employee']['avatar']?$admin_information['employee']['avatar']:$employee_biz->getDefaultAvatar($admin_information['employee']['gender']);
    			$profile['nickname'] = $admin_information['employee']['nickname'];
    			$profile['join_at'] = $admin_information['employee']['join_at'];
    			$profile['phone'] = $admin_information['employee']['phone'];
    			$profile['gender'] = $admin_information['employee']['gender'];
    			$profile['employee_id'] = $admin_information['employee']['id'];
    			$profile['number'] = $admin_information['employee']['number'];
    			$profile['my_qrcode'] = $employee_biz->myQrcode($admin_information['employee']['id']);
    			if ($admin_information['position']) {
    				$profile['is_manager'] = $admin_information['position']['is_manager'];
    				$profile['position_name'] = $admin_information['position']['pos_name'];
    				$profile['pos_level'] = $admin_information['position']['level'];
    			} else {
    				$profile['is_manager'] = 0;
    				$profile['position_name'] = lang('no_position');
    				$profile['pos_level'] = '';
    			}
    			if ($admin_information['organization']) {
    				$profile['organization_name'] = $admin_information['organization']['org_name'];
    			} else {
    				$profile['organization_name'] = lang('unknown');
    			}
    			if ($admin_information['statistics']) {
    				$profile['sti_candidates'] = $admin_information['statistics']['candidates'];
    				$profile['sti_remains'] = $admin_information['statistics']['remains'];
    				$profile['sti_remain_days'] = $admin_information['statistics']['remain_days'];
    				$profile['recognize_prior_time'] = $employee_biz->showRecognizeTime($admin_information['statistics']['recognize_prior_time']);
    				$profile['recognize_priors'] = $admin_information['statistics']['recognize_priors'];
    			} else {
    				$profile['sti_candidates'] = 0;
    				$profile['sti_remains'] = 0;
    				$profile['sti_remain_days'] = 0;
    				$profile['recognize_prior_time'] = $employee_biz->showRecognizeOpenTime();
    				$profile['recognize_priors'] = 0;
    			}
    		} else {
    			$profile['real_name'] = $admin_information['admin']['admin_name'];
    			$profile['avatar'] = lang('male_avatar_url');
    			$prifle['nickname'] = '';
    			$profile['is_manager'] = 0;
    			$profile['position_name'] = lang('no_position');
    			$profile['join_at'] = lang('unknown');
    			$profile['phone'] = lang('unknown');
    			$profile['gender'] = 1;
    			$profle['organization_name'] = lang('unknown');
    			$profile['employee_id'] = 0;
    			$profile['pos_level'] = '';
    			$profile['number'] = 0;
    			$profile['sti_candidates'] = 0;
    			$profile['sti_remains'] = 0;
    			$profile['sti_remain_days'] = 0;
    			$profile['recognize_prior_time'] = date('H:i', $employee_biz->getRecognizeOpenTime());
    			$profile['recognize_priors'] = 0;
    			$profile['my_qrcode'] = false;
    		}
    		if ($admin_information['admin']) {
    			$profile['admin'] = $admin_information['admin']['id'];
    			$profile['admin_name'] = $admin_information['admin']['admin_name'];
    		} else {
    			$profile['admin'] = 0;
    			$profile['admin_name'] = lang('unknown');
    		}
    	}
    	return $profile;
    }
    
    /**
     * 更新头像
     */
    public function uploadAvatar()
    {
    	/*参数验证*/
    	$param = request()->param();
    	if (isset($param['avatar'])){
    		$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    		$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    		$admin_information = Cache::get($token_key);
    		$id = $admin_information['employee']['id'];
    		if (is_url($param['avatar'])){
    			$avatar = $param['avatar'];
    		}else{
    			$service = new FileService();
    			$avatar = $service->base64_upload($param['avatar']);//保存base64图片
    		}
    		if ($avatar) {
    			$data['avatar'] = $avatar;
    			$business = controller('admin/EmployeeBiz','business');
    			$business->update($id, $data);
    		}
    	} else {
    		$id = 0;
    	}
    	
    	return ['id' => $id];
    }
    
    /**
     * 最新消息
     */
    public function message(){
    	$param = request()->param();
    	$size = isset($param['size'])?$param['size']:5;
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	$list = [];//消息列表
    	$alert_count = 0;//人选提醒总数
    	$message_count = 0;//消息总数
    	$task_count = 0;//未完成任务总数
    	$business = controller('customer/CandidateBiz', 'business');
    	if (isset($admin_information['employee']) && $admin_information['employee']) {
    		/*获取消息提醒*/
    		$biz = controller('admin/MessageBiz', 'business');
    		$list = $biz->getUnreadMessage($admin_information['employee']['id'], $size);
    		$message_count = $biz->getUnreadMessageCount($admin_information['employee']['id']);
    		/*获取人选提醒*/
    		if ($admin_business->isAdminToken($admin_information)){
    			$alert_count = count($business->willReleaseCustomer(Config::get('danger_candidate_expire')));//所有即将释放的人员
    		} else {
    			$alert_count = count($business->willRelease($admin_information['employee']['id'], Config::get('danger_candidate_expire')));
    		}
    		/*获取今日未完成任务*/
    		if (isset($admin_information['employee'])){
    			$task_biz = controller('admin/TaskBiz', 'business');
    			$task_count = $task_biz->getTodayCount($admin_information['employee']['id']);
    		}
    	}
    	return ['count'=>$message_count, 'list'=>$list, 'alert'=>$alert_count, 'task'=>$task_count];
    }
    
    /**
     * 最新内容
     */
    public function latest(){
    	$article_biz = controller('article/ArticleBiz', 'business');
    	$article_count = $article_biz->latestCount();//最新文章数
    	$job_biz = controller('job/JobBiz', 'business');
    	$job_count = $job_biz->latestCount();//最新职位数
    	$user_biz = controller('user/User', 'business');
    	$user_count = $user_biz->latestCount();//最新注册会员
    	$signup_count = $user_biz->latestSignupCount();//最新报名会员
    	$invite_count = $user_biz->latestInviteCount();//最新推荐会员
    	return ['article'=>$article_count, 'user'=>$user_count, 'signup'=>$signup_count, 'invite'=>$invite_count, 'job'=>$job_count];
    }
    
    /**
     * 我的组织
     */
    public function myOrganization(){
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	$list = [];
    	if ($admin_business->isAdministratorToken($admin_information)) {
    		$admin_information = [];
    		$admin_information['position']['is_manager'] = true;
    		$admin_information['organization']['id'] = 1;
    	}
    	if (isset($admin_information['organization'])){
    		$business = controller('admin/OrganizationBiz', 'business');
    		if ($admin_information['position']['is_manager']){
    			/*获取下级组织*/
    			$sub_orgs = $business->subOrg($admin_information['organization']['id']);
    			array_push($sub_orgs, $admin_information['organization']['id']);
    		} else {
    			$sub_orgs = [$admin_information['organization']['id']];
    		}
    		sort($sub_orgs);
    		foreach($sub_orgs as $org_id){
    			$return = ['list'=>[], 'header'=>[], 'nickname'=>lang('unknown')];
    			$org_detail = $business->getOrgDetail($org_id);
    			$return['nickname'] = $org_detail['nickname'];
    			/*获取组织下的所有成员*/
    			$employees = $business->getEmployees($org_id);
    			if ($employees) {
    				$employee_biz = controller('admin/EmployeeBiz', 'business');
    				foreach($employees as $employee){
    					if ($employee->position->is_manager){
    						$return['header'][] = [
    								'nickname' => $employee['nickname'],
    								'avatar' => $employee['avatar']?$employee['avatar']:$employee_biz->getDefaultAvatar($employee['gender']),
    								'real_name' => $employee['real_name'],
    								'gender' => $employee['gender']
    						];
    					} else {
    						$return['list'][] = [
    								'nickname' => $employee['nickname'],
    								'avatar' => $employee['avatar']?$employee['avatar']:$employee_biz->getDefaultAvatar($employee['gender']),
    								'real_name' => $employee['real_name'],
    								'gender' => $employee['gender']
    						];
    					}
    				}
    				$list[] = $return;
    			}
    		}
    	}
    	//Log::record($list);
		return $list;
    }
    
    /**
     * 所有顾问
     */
    public function allEmployee(){
    	$business = controller('admin/EmployeeBiz','business');
    	$result = $business->getAllAdvisers();
    	return $result;
    }
    
    /**
     * 发送消息
     */
    public function sendMessage(){
    	/*参数验证*/
    	$param = request()->param();
    	if (! isset($param['content']) || strlen($param['content']) < 1 || ! isset($param['receiver'])) {
        	abort(400, '400 Invalid content/receiver supplied');
        }
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	if( $admin_business->isAdministratorToken($admin_information) ){
    		$biz = controller('admin/MessageBiz', 'business');
    		$return = $biz->sendSystemMessage($param['receiver'], $param['content']);
    	} elseif (isset($admin_information['employee']) && $admin_information['employee']) {
    		$biz = controller('admin/MessageBiz', 'business');
    		$return = $biz->sendCommonMessage($param['receiver'], $param['content'], $admin_information['employee']['id'], $admin_information['employee']['nickname']);
    	} else {
    		$return = 0;
    	}
    	return ['id' => $return];
    }
    
    /**
     * 更新密码
     */
    public function updatePassword()
    {
    	$param = request()->param();
    	$data = [];
    	if (! isset($param['admin_pwd']) || $param['admin_pwd']=='') abort(400, '400 Invalid param supplied');
    	$data['admin_pwd'] = $param['admin_pwd'];
    	/*获取用户数据*/
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	$return = false;
    	if (isset($admin_information['admin']) && $admin_information['admin']) {
    		$business = controller('admin/AdminBiz', 'business');
    		$business->update($admin_information['admin']['id'], $data);
    		$return = true;
    	}
    	return $return;
    }
    
    public function allJob(){
    	$business = controller('job/JobBiz', 'business');
    	$list = $business->getAll();
    	return $list;
    }
    
    /**
     * 我的任务
     */
    public function task(){
    	$param = request()->param();
    	$size = max($param['size'], 1000);
    	$start_time = isset($param['start_date'])?$param['start_date']:false;
    	$end_time = isset($param['end_date'])?$param['end_date']:false;
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	$list = [];
    	if (isset($admin_information['employee']) && $admin_information['employee']) {
    		$biz = controller('admin/TaskBiz', 'business');
    		$list = $biz->getUnfinished($admin_information['employee']['id'], $size, $start_time, $end_time);
    	}
    	return $list;
    }
    
    /**
     * 添加任务
     */
    public function addTask(){
    	/*参数验证*/
    	$param = request()->param();
    	if (! isset($param['title']) || ! isset($param['start_time'])) {
    		abort(400, '400 Invalid title/start_time supplied');
    	}
    	$info = isset($param['info'])?$param['info']:'';
    	$location = isset($param['location'])?$param['location']:'';
    	$end_time = isset($param['end_time'])?$param['end_time']:'';
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	if (isset($admin_information['employee']) && $admin_information['employee']) {
    		$biz = controller('admin/TaskBiz', 'business');
    		$return = $biz->addCommonTask($admin_information['employee']['id'], $param['title'], $param['start_time'], $info, $location, $end_time);
    	} else {
    		$return = 0;
    	}
    	return ['id' => $return];
    }
    
    /**
     * 更新任务
     */
    public function updateTask(){
    	/*参数验证*/
    	$param = request()->param();
    	if (! isset($param['id']) || $param['id'] <= 0) {
    		abort(400, '400 Invalid id supplied');
    	}
    	$title = isset($param['title'])?$param['title']:'';
    	$info = isset($param['info'])?$param['info']:'';
    	$location = isset($param['location'])?$param['location']:'';
    	$start_time = isset($param['start_time'])?$param['start_time']:'';
    	$end_time = isset($param['end_time'])?$param['end_time']:'';

    	$biz = controller('admin/TaskBiz', 'business');
    	$return = $biz->updateTask($param['id'], $title, $info, $location, $start_time, $end_time);
    	return ['id' => $return];
    }
    
    /**
     * 我的呼入报名用户
     */
    public function mySignup(){
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = isset($param['search'])?$param['search']:false;
    	$scope = isset($param['scope'])?$param['scope']:'all';
    	$from = isset($param['from'])?$param['from']:'';
    	$sure = isset($param['sure'])?$param['sure']:'';
    	 
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	if (isset($admin_information['employee']) && $admin_information['employee']) {
    		$business = CallinFactory::instance('signup', $from);
    		if ($sure == 1) $sure = true;
    		elseif ($sure == 0) $sure = false;
    		$result = $business->myList($admin_information['employee']['id'], $sure, $search, $page, $pagesize);
    		$list = $result['list'];
    		$count = $result['count'];
    	} elseif ($admin_business->isAdministratorToken($admin_information)){
    		$business = CallinFactory::instance('signup', $from);
    		if ($sure == 1) $sure = true;
    		elseif ($sure == 0) $sure = false;
    		$result = $business->getAll($sure, $search, $page, $pagesize);
    		$list = $result['list'];
    		$count = $result['count'];
    	} else {
    		$list = [];
    		$count = 0;
    	}
    	 
    	return ['list' => Format::object2array($list), 'count'=>$count];
    }
    
    /**
     * 我的呼入注册用户
     */
    public function myRegister(){
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = isset($param['search'])?$param['search']:false;
    	$scope = isset($param['scope'])?$param['scope']:'all';
    	$from = isset($param['from'])?$param['from']:'';
    	$sure = isset($param['sure'])?$param['sure']:'';
    	
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	if (isset($admin_information['employee']) && $admin_information['employee']) {
    		$business = CallinFactory::instance('register', $from);
    		if ($sure == 1) $sure = true;
    		elseif ($sure == 0) $sure = false;
    		$result = $business->myList($admin_information['employee']['id'], $sure, $search, $page, $pagesize);
    		$list = $result['list'];
    		$count = $result['count'];
    	} elseif ($admin_business->isAdministratorToken($admin_information)){
    		$business = CallinFactory::instance('register', $from);
    		if ($sure == 1) $sure = true;
    		elseif ($sure == 0) $sure = false;
    		$result = $business->getAll($sure, $search, $page, $pagesize);
    		$list = $result['list'];
    		$count = $result['count'];
    	} else {
    		$list = [];
    		$count = 0;
    	}
    	
    	return ['list' => Format::object2array($list), 'count'=>$count];
    }
    
    /**
     * 完成任务
     */
    public function finishTask(){
    	/*参数验证*/
    	$param = request()->param();
    	if (! isset($param['id']) || $param['id'] <= 0) {
    		abort(400, '400 Invalid id supplied');
    	}
    	
    	$biz = controller('admin/TaskBiz', 'business');
    	$return = $biz->finishTask($param['id']);
    	return ['id' => $return];
    }
    
    /**
     * 获取用户操作按钮权限
     */
    public function operateBtn(){
    	/*获取用户个性化设置*/
    	Config::load(APP_PATH.'admin/config_personal.php');
    	$user_setting = Config::get('user_setting');
    	/*获取登录用户信息*/
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	/*初始化按钮权限*/
    	$btn = [];
    	foreach($user_setting as $btn_type=>$types){
    		$btn[$btn_type] = [];
    		foreach($types as $titem){
    			$btn[$btn_type][$titem] = $admin_business->isAdministratorToken($admin_information)?true:false;
    		}
    	}
    	/*设置每个人的按钮权限*/
    	if (is_array($admin_information)){
    		if (isset($admin_information['setting'])) {
    			if (isset($admin_information['setting']['system'])){
    				foreach($user_setting as $btn_type=>$types){
    					if (isset($admin_information['setting']['system'][$btn_type])){
    						$btn[$btn_type] = $admin_information['setting']['system'][$btn_type];
    					}
    				}
    			}
    		}
    	}
    	return $btn;
    }
    
    /**
     * 所有顾问组织及其员工
     */
    public function adviserOrganization(){
    	$business = controller('admin/OrganizationBiz','business');
    	$result = $business->getAllAdvisers();
    	/*组织权限过滤*/
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	if(! $admin_business->isAdminToken($admin_information) ){
    		$org_id = $admin_business->getOrganizationId($admin_information);
    		$org_business = controller('admin/OrganizationBiz', 'business');//组织业务对象
    		/*组织过滤*/
    		$sub_org = $org_business->subOrg($org_id);
    		array_push($sub_org, $org_id);
    		foreach($result['orgs'] as $key => $org){
    			if (! in_array($org['id'], $sub_org)) unset($result['orgs'][$key]);
    		}
    		/*员工过滤*/
    		foreach($result['employees'] as $key => $employee){
    			if (! in_array($employee['org_id'], $sub_org)) unset($result['employees'][$key]);
    		}
    	}
    	return $result;
    }
    /**
     * 搜索
     */
    public function searchTag(){
    	/*参数验证*/
    	$param = request()->param();
    	$tag = isset($param['tag'])?$param['tag']:'';
    	$business = controller('customer/TagBiz', 'business');
    	$tags = $business->resultKeyword($tag);
    	return $tags;
    }
    
    /**
     * 群发消息
     */
    public function mass(){
    	/*参数验证*/
    	$param = request()->param();
    	if (! isset($param['content']) || strlen($param['content']) < 1) {
    		abort(400, '400 Invalid content supplied');
    	}
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	if( $admin_business->isAdministratorToken($admin_information) ){
    		$biz = controller('admin/MessageBiz', 'business');
    		$return = $biz->massMessage($param['content']);
    	} else {
    		$return = 0;
    	}
    	return ['id' => $return];
    }
    
    /**
     * 未读消息
     */
    public function messages(){
    	$param = request()->param();
    	$page = $param['page'];
    	$pagesize = $param['pagesize'];
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	$list = [];
		if (isset($admin_information['employee']) && $admin_information['employee']) {
    		/*获取未读消息提醒*/
    		$biz = controller('admin/MessageBiz', 'business');
    		$list = $biz->getUnreadMessage($admin_information['employee']['id'], $pagesize, $page, true);
    	}
    	return $list;
    }
    
    /**
     * 所有顾问
     */
    public function groupEmployee(){
    	$business = controller('admin/EmployeeBiz','business');
    	$result = $business->groupAdviserOrg();
    	return $result;
    }
    
    /**
     * 收藏快捷菜单
     */
    public function addFavorite(){
    	$param = request()->param();
    	if (! isset($param['url']) || $param['url'] == '') {
    		abort(400, '400 Invalid url supplied');
    	}
    	$url = $param['url'];
    	$title = $param['title'];
    	$list = [];
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	if (isset($admin_information['employee']) && $admin_information['employee']) {
    		$biz = controller('admin/SettingBiz', 'business');
    		$list = $biz->addFavorite($admin_information['employee']['id'], $url, $title);
    	}
    	return $list;
    }
    
    /**
     * 删除快捷菜单
     */
    public function deleteFavorite(){
    	$param = request()->param();
    	if (! isset($param['url']) || $param['url'] == '') {
    		abort(400, '400 Invalid url supplied');
    	}
    	$url = $param['url'];
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$admin_information = Cache::get($token_key);
    	if (isset($admin_information['employee']) && $admin_information['employee']) {
    		$biz = controller('admin/SettingBiz', 'business');
    		$biz->deleteFavorite($admin_information['employee']['id'], $url);
    	}
    	return true;
    }
    
}
