<?php
namespace app\customer\controller;

use think\Config;
use ylcore\Format;
use think\Request;
use think\Log;
use think\Cache;
use app\admin\controller\AdminAuth;

class Candidate extends AdminAuth
{
	/**
	 * 候选人列表
	 */
	public function index()
    {
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = $this->detailSearchCondition();
    	$id = isset($param['id'])?$param['id']:false;
    	/*用户权限验证*/
    	$business = controller('CandidateBiz', 'business');
    	if (isset($param['stype']) && $param['stype']) $business->setSearchType($param['stype']);
    	if (isset($param['onlyMy']) && $param['onlyMy'] == 'true') $business->isSearchOrg = true;//按照部门搜索
    	$order = isset($param['order'])?$param['order']:'create_time';
    	if ($this->isAdministrator()){
    		//管理员查看所有的信息
    		$list = $business->getAll($page, $pagesize, $search, 'id', $order);
    		$count = $business->getCount($search);
    	} elseif ($this->isManager() && $business->isSearchOrg) {
    		/*管理岗位可以查看本组织及其下级组织的所有信息*/
    		$org_id = $this->getOrganizationId();
    		$list = $business->getOrg($org_id, $page, $pagesize, $search, 'id', $order);
    		$count = $business->getOrgCount($org_id, $search);
    	} else {
    		/*非管理岗位只能查看自己的信息*/
    		$employee_id = $this->getEmployeeId();
    		$list = $business->getMy($employee_id, $page, $pagesize, $search, 'id', $order);
    		$count = $business->getMyCount($employee_id, $search);
    	}
    	return ['list' => Format::object2array($list), 'count'=>$count];
    }
    
    /**
     * 客户有意向
     */
    public function intention()
    {		
    	/*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	$business = controller('CandidateBiz', 'business');
    	$flag = $business->intention($arr_id, $this->getAdminId());
    	return true;
    }
    
    /**
     * 我的意向客户
     */
    public function myIntention(){
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = $this->detailSearchCondition();
    	$id = isset($param['id'])?$param['id']:false;
    	/*用户权限验证*/
    	$business = controller('CandidateBiz', 'business');
    	if (isset($param['stype']) && $param['stype']) $business->setSearchType($param['stype']);
    	if (isset($param['onlyMy']) && $param['onlyMy'] == 'true') $business->isSearchOrg = true;//按照部门搜索
    	if ($this->isAdministrator()){
    		//管理员查看所有的信息
    		$list = $business->getAllIntention($page, $pagesize, $search);
    		$count = $business->getIntentionCount($search);
    	} elseif ($this->isManager() && $business->isSearchOrg) {
    		/*管理岗位可以查看本组织及其下级组织的所有信息*/
    		$org_id = $this->getOrganizationId();
    		$list = $business->getOrgIntention($org_id, $page, $pagesize, $search);
    		$count = $business->getOrgIntentionCount($org_id, $search);    			
    	} else {
    		/*非管理岗位只能查看自己的信息*/
    		$employee_id = $this->getEmployeeId();
    		$list = $business->getMyIntention($employee_id, $page, $pagesize, $search);
    		$count = $business->getMyIntentionCount($employee_id, $search);
    	}
    	return ['list' => Format::object2array($list), 'count'=>$count];
    }
    
    /**
     * 我的报名客户
     */
    public function mySignup(){
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = $this->detailSearchCondition();
    	$id = isset($param['id'])?$param['id']:false;
    	/*用户权限验证*/
    	$business = controller('CandidateBiz', 'business');
    	if (isset($param['stype']) && $param['stype']) $business->setSearchType($param['stype']);
    	if (isset($param['onlyMy']) && $param['onlyMy'] == 'true') $business->isSearchOrg = true;//按照部门搜索
    	if ($this->isAdministrator()){
    		//管理员查看所有的信息
    		$list = $business->getAllSignup($page, $pagesize, $search);
    		$count = $business->getSignupCount($search);
    	} elseif ($this->isManager() && $business->isSearchOrg) {
    		/*管理岗位可以查看本组织及其下级组织的所有信息*/
    		$org_id = $this->getOrganizationId();
    		$list = $business->getOrgSignup($org_id, $page, $pagesize, $search);
    		$count = $business->getOrgSignupCount($org_id, $search);
    	} else {
    		/*非管理岗位只能查看自己的信息*/
    		$employee_id = $this->getEmployeeId();
    		$list = $business->getMySignup($employee_id, $page, $pagesize, $search);
    		$count = $business->getMySignupCount($employee_id, $search);
    	}
    	return ['list' => Format::object2array($list), 'count'=>$count];
    }
    
    /**
     * 我的在职客户
     */
    public function myOnduty(){
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = $this->detailSearchCondition();
    	$id = isset($param['id'])?$param['id']:false;
    	/*用户权限验证*/
    	$business = controller('CandidateBiz', 'business');
    	if (isset($param['stype']) && $param['stype']) $business->setSearchType($param['stype']);
    	if (isset($param['onlyMy']) && $param['onlyMy'] == 'true') $business->isSearchOrg = true;//按照部门搜索
    	if ($this->isAdministrator()){
    		//管理员查看所有的信息
    		$list = $business->getAllOnduty($page, $pagesize, $search);
    		$count = $business->getOndutyCount($search);
    	} elseif ($this->isManager() && $business->isSearchOrg) {
    		/*管理岗位可以查看本组织及其下级组织的所有信息*/
    		$org_id = $this->getOrganizationId();
    		$list = $business->getOrgOnduty($org_id, $page, $pagesize, $search);
    		$count = $business->getOrgOndutyCount($org_id, $search);
    	} else {
    		/*非管理岗位只能查看自己的信息*/
    		$employee_id = $this->getEmployeeId();
    		$list = $business->getMyOnduty($employee_id, $page, $pagesize, $search);
    		$count = $business->getMyOndutyCount($employee_id, $search);
    	}
    	return ['list' => Format::object2array($list), 'count'=>$count];
    }
    
    /**
     * 我的离职客户
     */
    public function myOutduty(){
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = $this->detailSearchCondition();
    	$id = isset($param['id'])?$param['id']:false;
    	/*用户权限验证*/
    	$business = controller('CandidateBiz', 'business');
    	if (isset($param['stype']) && $param['stype']) $business->setSearchType($param['stype']);
    	if (isset($param['onlyMy']) && $param['onlyMy'] == 'true') $business->isSearchOrg = true;//按照部门搜索
    	if ($this->isAdministrator()){
    		//管理员查看所有的信息
    		$list = $business->getAllOutduty($page, $pagesize, $search);
    		$count = $business->getOutdutyCount($search);
    	} elseif ($this->isManager() && $business->isSearchOrg) {
    		/*管理岗位可以查看本组织及其下级组织的所有信息*/
    		$org_id = $this->getOrganizationId();
    		$list = $business->getOrgOutduty($org_id, $page, $pagesize, $search);
    		$count = $business->getOrgOutdutyCount($org_id, $search);
    	} else {
    		/*非管理岗位只能查看自己的信息*/
    		$employee_id = $this->getEmployeeId();
    		$list = $business->getMyOutduty($employee_id, $page, $pagesize, $search);
    		$count = $business->getMyOutdutyCount($employee_id, $search);
    	}
    	return ['list' => Format::object2array($list), 'count'=>$count];
    }
    
    /**
     * 我的接站客户
     */
    public function myMeet(){
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = $this->detailSearchCondition();
    	$id = isset($param['id'])?$param['id']:false;
    	/*用户权限验证*/
    	$business = controller('CandidateBiz', 'business');
    	if (isset($param['stype']) && $param['stype']) $business->setSearchType($param['stype']);
    	if (isset($param['onlyMy']) && $param['onlyMy'] == 'true') $business->isSearchOrg = true;//按照部门搜索
    	if ($this->isAdministrator()){
    		//管理员查看所有的信息
    		$list = $business->getAllMeet($page, $pagesize, $search);
    		$count = $business->getMeetCount($search);
    	} elseif ($this->isManager() && $business->isSearchOrg) {
    		/*管理岗位可以查看本组织及其下级组织的所有信息*/
    		$org_id = $this->getOrganizationId();
    		$list = $business->getOrgMeet($org_id, $page, $pagesize, $search);
    		$count = $business->getOrgMeetCount($org_id, $search);
    	} else {
    		/*非管理岗位只能查看自己的信息*/
    		$employee_id = $this->getEmployeeId();
    		$list = $business->getMyMeet($employee_id, $page, $pagesize, $search);
    		$count = $business->getMyMeetCount($employee_id, $search);
    	}
    	return ['list' => Format::object2array($list), 'count'=>$count];
    }
    
    /**
     * 我的其他客户
     */
    public function myOther(){
    	/*获取参数*/
    	$param = request()->param();
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
    	$search = $this->detailSearchCondition();
    	$id = isset($param['id'])?$param['id']:false;
    	/*用户权限验证*/
    	$business = controller('CandidateBiz', 'business');
    	if (isset($param['stype']) && $param['stype']) $business->setSearchType($param['stype']);
    	if (isset($param['onlyMy']) && $param['onlyMy'] == 'true') $business->isSearchOrg = true;//按照部门搜索
    	if ($this->isAdministrator()){
    		//管理员查看所有的信息
    		$list = $business->getAllDefault($page, $pagesize, $search);
    		$count = $business->getDefaultCount($search);
    	} elseif ($this->isManager() && $business->isSearchOrg) {
    		/*管理岗位可以查看本组织及其下级组织的所有信息*/
    		$org_id = $this->getOrganizationId();
    		$list = $business->getOrgDefault($org_id, $page, $pagesize, $search);
    		$count = $business->getOrgDefaultCount($org_id, $search);
    	} else {
    		/*非管理岗位只能查看自己的信息*/
    		$employee_id = $this->getEmployeeId();
    		$list = $business->getMyDefault($employee_id, $page, $pagesize, $search);
    		$count = $business->getMyDefaultCount($employee_id, $search);
    	}
    	return ['list' => Format::object2array($list), 'count'=>$count];
    }
    
    /**
     * 候选人报名
     */
    public function signup(){
    	/*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['customers/a'];
    	$job_id = isset($param['job']) ? $param['job'] : false;//职位编号
    	$date = isset($param['date']) ? $param['date'] : date('Y-m-d');//报名时间
    	if (! $arr_id || ! $job_id) abort(400, '400 Invalid id/job_id supplied');
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('CandidateBiz', 'business');
    	$business->signup($arr_id, $this->getAdminId(), $job_id, false, $date);
    	return true;
    }
    
    /**
     * 候选人入职
     */
    public function onduty(){
    	/*参数验证*/
    	$param = request()->param();
    	$id = $param['customer'];//客户编号
    	if (! $id) abort(400, '400 Invalid id supplied');
    	$arr_id = [$id];
    	$award = isset($param['award'])?$param['award']:0;
    	$subsidy = isset($param['subsidy'])?$param['subsidy']:0;
    	$dutydays = isset($param['dutydays'])?$param['dutydays']:0;
    	$inviter = $param['inviter'];
    	$inviter_phone = $param['inviter_phone'];
    	$invite_amount = $param['invite_amount'];
    	$is_customer_invite = isset($param['invite_is_member'])?$param['invite_is_member']:'';
    	$is_customer_invite = $is_customer_invite=='true' ? 1 : 0;
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('CandidateBiz', 'business');    	 
    	$business->onduty($arr_id, $this->getAdminId(), $award, $subsidy, $inviter, $inviter_phone, $invite_amount, $dutydays, $is_customer_invite);
    	return true;
    }
    
    /**
     * 候选人离职
     */
    public function outduty(){
    	/*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('CandidateBiz', 'business');
    	$business->outduty($arr_id, $this->getAdminId());
    	return true;
    }
    
    /**
     * 丢弃候选人
     */
    public function depose(){
    	/*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('CandidateBiz', 'business');
    	$flag = $business->depose($arr_id);
    	return $flag;
    }
    
    /**
     * 候选人接站
     */
    public function meet(){
    	/*参数验证*/
    	$param = request()->param();
    	$id = $param['customer'];
    	$id_card = $param['idcard'];
    	if (! $id) abort(400, '400 Invalid id supplied');
    	$gender = isset($param['gender']) ? $param['gender'] : false;
    	$birth = isset($param['birth']) ? $param['birth'] : false;
    	$arr_id = [$id];
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('CandidateBiz', 'business');
    	$business->meet($arr_id, $this->getAdminId(), $id_card, $gender, $birth);
    	return true;
    }
    
    /**
     * 候选人详情
     */
    public function detail(){
    	/*获取参数*/
    	$param = request()->param();
    	$id = $param['id'];
    	if (! $id) abort(400, '400 Invalid id supplied');
    	/*客户权限验证*/
    	//do nothing
    	$business = controller('CandidateBiz', 'business');
    	return $business->get($id);
    }
    
    /**
     * 更新返费
     */
    public function updateAward(){
    	/*获取参数*/
    	$param = request()->param();
    	$id = $param['id'];
    	$award = $param['award'];
    	if (! $id) abort(400, '400 Invalid id/award supplied');
    	/*客户权限验证*/
    	//do nothing
    	$business = controller('CandidateBiz', 'business');
    	return $business->updateAward($id, $award);
    }
    
    /**
     * 保留候选人
     */
    public function remain(){
    	/*参数验证*/
    	$param = request()->param();
    	$id = $param['id'];
    	if (! $id) abort(400, '400 Invalid id supplied');
    	$arr_id[] = $id;
    	/*顾问权限验证*/
    	$this->checkDataAuth($arr_id);

    	$business = controller('CandidateBiz', 'business');
    	$flag = $business->remain($id, $this->getEmployeeId());
    	
    	return $flag;
    }
    
    /**
     * 取消保留候选人
     */
    public function cancelRemain(){
    	/*参数验证*/
    	$param = request()->param();
    	$id = $param['id'];
    	if (! $id) abort(400, '400 Invalid id supplied');
    	$arr_id[] = $id;
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('CandidateBiz', 'business');
    	$business->cancelRemain($id);
    	return true;
    }
    
    /**
     * 获取候选人标签
     */
    public function tag(){
    	/*参数验证*/
    	$param = request()->param();
    	$id = isset($param['id'])?$param['id']:false;
    	$cpid = isset($param['cpid'])?$param['cpid']:false;
    	if (! $id && ! $cpid) abort(400, '400 Invalid id supplied');
    	$business = controller('CandidateBiz', 'business');
    	if ($cpid){
    		$tags = $business->getTagCustomer($cpid, $this->getEmployeeId());
    	}else{
    		$tags = $business->getTag($id);
    	}
    	return $tags;
    }
    
    /**
     * 添加标签
     */
    public function addTag(){
    	/*参数验证*/
    	$param = request()->param();
    	$id = isset($param['id'])?$param['id']:false;
    	$cpid = isset($param['cpid'])?$param['cpid']:false;
    	if (! $id && ! $cpid) abort(400, '400 Invalid id supplied');
    	$tag = $param['tag'];
    	$business = controller('CandidateBiz', 'business');
    	if ( $id ){
    		$business->addTag($id, $tag);
    	} elseif ($cpid) {
    		$business->addTagCustomer($cpid, $this->getEmployeeId(), $tag);
    	} else {
    		return false;
    	}
    	return true;
    }
    
    /**
     * 删除标签
     */
    public function deleteTag(){
    	/*参数验证*/
    	$param = request()->param();
    	$id = isset($param['id'])?$param['id']:false;
    	$cpid = isset($param['cpid'])?$param['cpid']:false;
    	if (! $id && ! $cpid) abort(400, '400 Invalid id supplied');
    	$tag = $param['tag'];
    	$business = controller('CandidateBiz', 'business');
    	if ( $id ){
    		$business->deleteTag($id, $tag);
    	} elseif ($cpid) {
    		$business->deleteTagCustomer($cpid, $this->getEmployeeId(), $tag);
    	} else {
    		return false;
    	}
    	return true;
    }
    
    /**
     * 验证数据权限
     * @param: $arr_id array 人选编号
     */
    private function checkDataAuth($arr_id){
    	$flag = false;
    	//超级管理员不验证
    	if ($this->isAdministrator()) {
    		$flag = true;
    	} else {
    		/*验证客户是否对顾问可见*/
    		$business = controller('CandidateBiz', 'business');
    		$employee_id = $this->getEmployeeId();
    		$org_id = $this->getOrganizationId();
    		if ($business->validateEmployee($employee_id, $arr_id, $org_id)) {
    			$flag = true;
    		}
    	}
    	 
    	$flag == false && abort(403, '403 Forbidden');
    }
    
    /**
     * 客户状态回退
     */
    public function back(){
    	/*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['customers/a'];
    	if (! $arr_id) abort(400, '400 Invalid customers supplied');
    	$status = $param['status'];
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	$business = controller('CandidateBiz', 'business');
    	$business->backStatus($arr_id, $this->getAdminId(), $status);
    	return true;
    }
    
    /**
     * 客户划转
     */
    public function move(){
    	/*参数验证*/
    	$param = request()->param();
    	$adviser = $param['adviser'];
    	$arr_id = $param['candidates/a'];
    	if (! $arr_id || ! $adviser) abort(400, '400 Invalid adviser/candidates supplied');

    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	$business = controller('CandidateBiz', 'business');
    	$flag = $business->move($arr_id, $this->getAdminId(), $adviser, $this->getEmployeeId());
    	return $flag;
    }
    
    /**
     * 标签管理
     */
    public function tags(){
    	/*参数验证*/
    	$param = request()->param();
    	$tag = isset($param['tag'])?$param['tag']:'';
    	$business = controller('CandidateBiz', 'business');
    	if ($this->isAdministrator() || $this->isAdmin()) {
    		$tags = $business->getTags($tag);    		
    	}else{
    		$tags = $business->getMyTags($tag, $this->getEmployeeId());
    	}
    	return $tags;
    }
    
    /**
     * 即将要释放的危险客户
     */
    public function danger(){
    	/*获取参数*/
    	$param = request()->param();
    	$search = isset($param['search'])?$param['search']:false;
    	$page = isset($param['page'])?$param['page']:1;
    	$pagesize = isset($param['pagesize'])?$param['pagesize']:50;
    	$condition['is_remain'] = isset($param['is_remain'])?($param['is_remain']=='true'?1:0):false;
    	$condition['is_intention'] = isset($param['is_intention'])?($param['is_intention']=='true'?1:0):false;
    	$condition['distribute_time_s'] = isset($param['distribute_time_s'])?$param['distribute_time_s']:false;
    	$condition['distribute_time_e'] = isset($param['distribute_time_e'])?$param['distribute_time_e']:false;
    	/*获取所有过期的人选*/
    	$business = controller('customer/CandidateBiz', 'business');
    	if ($this->isAdministrator() || $this->isAdmin()){
    		$list = $business->willReleaseCustomer(Config::get('danger_candidate_expire'), $search, '', $condition);
    	}else{
    		$list = $business->willRelease($this->getEmployeeId(), Config::get('danger_candidate_expire'), $search, $condition);
    	}
    	/*获取分页*/
    	$return = [];
    	if ($list){
    		foreach($list as $key=>$item){
    			if ($key>=($page-1)*$pagesize && $key<$page*$pagesize){
    				$return[] = $item;
    			}
    		}
    	}
    	
    	return ['list'=>$return, 'count'=>count($list)];
    }
    
    /**
     * 详细搜索参数
     */
    private function detailSearchCondition(){
    	$return = '';
    	/*获取参数*/
    	$param = request()->param();
    	$search = isset($param['search'])?$param['search']:false;//默认搜索
    	/*高级搜索*/
    	if (isset($param['stype']) && $param['stype']==99) {
    		$return = [];
    		$return['text'] = $search;
    		//分配时间
    		if (isset($param['distribute_time_s']) && $param['distribute_time_s']) $return['distribute_time_s'] = $param['distribute_time_s'];
    		if (isset($param['distribute_time_e']) && $param['distribute_time_e']) $return['distribute_time_e'] = $param['distribute_time_e'];
    		//报名时间
    		if (isset($param['signup_time_s']) && $param['signup_time_s']) $return['signup_time_s'] = $param['signup_time_s'];
    		if (isset($param['signup_time_e']) && $param['signup_time_e']) $return['signup_time_e'] = $param['signup_time_e'];
    		//入职时间
    		if (isset($param['onduty_time_s']) && $param['onduty_time_s']) $return['onduty_time_s'] = $param['onduty_time_s'];
    		if (isset($param['onduty_time_e']) && $param['onduty_time_e']) $return['onduty_time_e'] = $param['onduty_time_e'];
    		//最后联系时间
    		if (isset($param['contact_time_s']) && $param['contact_time_s']) $return['latest_contact_time_s'] = $param['contact_time_s'];
    		if (isset($param['contact_time_e']) && $param['contact_time_e']) $return['latest_contact_time_e'] = $param['contact_time_e'];
    		//是否保留
    		if (isset($param['is_remain']) && $param['is_remain']) $return['is_remain'] = $param['is_remain'];
    		//入职企业
    		if (isset($param['job']) && $param['job']) $return['job'] = $param['job'];
    		//是否为新人
    		if (isset($param['is_new']) && $param['is_new']) $return['is_new'] = $param['is_new'];
    		//员工
    		if (isset($param['employee']) && $param['employee']) $return['employee'] = $param['employee'];
    		//组织机构
    		if (isset($param['org']) && $param['org']) $return['org'] = $param['org'];
    	} else {
    		$return = $search;
    	}
    	return $return;
    }
    
    /**
     * 一键保留即将释放的人选
     */
    public function remainAll(){
    	$business = controller('CandidateBiz', 'business');
    	$flag = $business->remainReleaseNow($this->getEmployeeId());
    	return $flag;
    }
    
    /**
     * 离职之后再次报名
     */
    public function resignup() {
    	/*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['customers/a'];
    	$job_id = $param['job'];//职位编号
    	$date = isset($param['date']) ? $param['date'] : date('Y-m-d');//报名时间
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	 
    	$business = controller('CandidateBiz', 'business');
    	$business->resignup($arr_id, $this->getAdminId(), $job_id, $date);
    	return true;
    }
    
    /**
     * 置顶候选人
     */
    public function top(){
    	/*参数验证*/
    	$param = request()->param();
    	$id = $param['id'];
    	if (! $id) abort(400, '400 Invalid id supplied');
    	$arr_id[] = $id;
    	/*顾问权限验证*/
    	$this->checkDataAuth($arr_id);
    
    	$business = controller('CandidateBiz', 'business');
    	$flag = $business->top($id);
    	 
    	return $flag;
    }
    
    /**
     * 取消置顶候选人
     */
    public function cancelTop(){
    	/*参数验证*/
    	$param = request()->param();
    	$id = $param['id'];
    	if (! $id) abort(400, '400 Invalid id supplied');
    	$arr_id[] = $id;
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	 
    	$business = controller('CandidateBiz', 'business');
    	$business->cancelTop($id);
    	return true;
    }
    
    /**
     * 更新报名信息
     */
    public function updateSignup() {
    	/*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['customers/a'];
    	$job_id = $param['job'];//职位编号
    	$date = isset($param['date']) ? $param['date'] : date('Y-m-d');//报名时间
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    
    	$business = controller('CandidateBiz', 'business');
    	$business->updateSignup($arr_id, $this->getAdminId(), $job_id, $date);
    	return true;
    }
}