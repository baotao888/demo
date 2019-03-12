<?php
namespace app\customer\business;

use think\Cache;
use think\Config;
use think\Log;

use ylcore\Biz;
use app\user\business\CallinFactory;
use app\customer\model\CustomerPool;
use ylcore\Format;

class CustomerpoolBiz extends Biz implements CustomerPoolSubjectInterface
{
	const FROM_ADD = 'backend';
	const FROM_IMPORT = 'import';
	const FROM_WEB = 'web';
	const FROM_INVITE = 'invite';
	const FROM_SIGNUP = 'signup';
	const DEPOSE_SYSTEM = 'system';
	const DEPOSE_PERSONAL = 'personal';
	const LOG_TYPE_C = 'C';//创建操作
	const LOG_TYPE_U = 'U';//更新操作
	const LOG_TYPE_R = 'R';//读取操作
	const LOG_TYPE_D = 'D';//删除操作
	const LOG_TYPE_S = 'S';//分配操作
	const SIGNED_USER = 2;//分配人选
	const SURE_USER = 1;//确认人选
	
	private $import_fields;//导入字段顺序
	
	private $_observers;//观察者对象
	
	function __construct() {
		$this->_observers = array();
		$this->attach(CallinFactory::instance('register'));//添加呼入注册用户
		$this->attach(CallinFactory::instance('signup'));//添加呼入报名用户
	}
	
	/**
	 * @implement
	 */
	public function attach(CustomerPoolObserverInterface $observer) {
		return array_push($this->_observers, $observer);
	}
	
	/**
	 * @implement
	 */
	public function detach(CustomerPoolObserverInterface $observer) {
		$index = array_search($observer, $this->_observers);
		if ($index === false || ! array_key_exists($index, $this->_observers)) {
			return false;
		}
	
		unset($this->_observers[$index]);
		return true;
	}
	
    /**
     * 添加客户
     */
    public function save($data, $is_import = false){
        $model = model('customer/CustomerPool');
        $model->data([
                'real_name'  =>  trim($data['real_name']),
                'gender' =>  isset($data['gender'])?$data['gender']:0,
                'phone' =>  trim($data['phone']),
                'from'  =>  (isset($data['from'])&&$data['from'])?$data['from']:self::FROM_ADD,
                'birthday'  =>  $data['birthday'],
                'hometown'  =>  isset($data['hometown'])?$data['hometown']:'',
                'mobile_1'  =>  isset($data['mobile_1'])?trim($data['mobile_1']):'',
                'career'  =>  isset($data['career'])?$data['career']:'',
        		'idcard'  =>  isset($data['idcard'])?$data['idcard']:'',
        		'description'  =>  isset($data['description'])?$data['description']:''
        ]);
        $model->isUpdate(false)->save();//保存
        $data['id'] = $model->id;
        $modelData = model('customer/CustomerPoolData');
        $modelData->data([
                'id'  =>  $data['id'],
                'wechat' =>  isset($data['wechat'])?$data['wechat']:'',
                'qq'  =>  isset($data['qq'])?$data['qq']:'',
                'address'  =>  isset($data['address'])?$data['address']:'',
                'email' =>  isset($data['email'])?$data['email']:''
        ]);
        $modelData->isUpdate(false)->save();//保存
        
        /*非导入的客户，新增之后立即分配*/
        $is_assign = true;
        if ($is_import == false){
        	$arr[] = $model->id;
        	$status = [];
        	if (isset($data['intetion'])) $status[] = $data['intetion']?1:0;
        	$is_assign = $this->distribute($arr, $data['employee_id'], $data['employee_id'], $status);
        	if (isset($data['description']) && $data['description']!=''){
        		/*创建联系记录*/
        		$business = controller('ContactLogBiz','business');
        		$res = $business->save(['cp_id'=>$model->id, 'content'=>$data['description'], 'result'=>isset($data['intetion'])?$data['intetion']:0], $data['employee_id']);
        	}
        } else {
        	$is_assign = false;//导入的 不分配
        }

        $modelstatus = model('customer/CustomerPoolStatus');
        $modelstatus->data([
             'id'  =>  $model->id,
        	 'is_open' => 	0,
        	 'is_assign' => $is_assign?1:0
        ]);
        $modelstatus->isUpdate(false)->save();//初始化客户状态表

        $this->createLog($data['id'], $data['admin_id'], $data, self::LOG_TYPE_C);

        if ($is_assign) {
        	$this->emitRecord($data['employee_id'], $model->phone);//客户池人选录入并且认领广播
        }

        return $model->id;
    }
    
    public function emitRecord($operator, $phone) {
    	if (! is_array($this->_observers)) {
    		return false;
    	}
    	
    	foreach ($this->_observers as $observer) {
    		$observer->match($operator, $phone);//发射客户录入
    	}
    	
    	return true;
    } 
    
    /**
     * 按照分配状态查询客户池
     * @param $sign 0,未分配;1,已分配
     */
    public function getAll(  $real_name , $phone , $from , $page = 1 , $pagesize = 20 , $sign = '')
    {	
        $return = array();
        $model = model('CustomerPool')->alias('customerpool');
        $where = 't.is_open = 0';
        if ( $sign !== ''){
            $where .= " and t.is_assign=$sign";
        }
        if ( $real_name != ''){
            $where .= ' and customerpool.real_name like"'.$real_name.'%"';
        }
        if ( $phone != ''){
        	if (is_mobile($phone)){
        		$where .= ' and (customerpool.phone="'.$phone.'" OR customerpool.mobile_1="'.$phone.'")';
        	} elseif (is_numeric($phone)) {
        		$where .= ' and (customerpool.phone like"'.$phone.'%" OR customerpool.mobile_1 like"'.$phone.'%")';
        	} else {
        		$where .= " and customerpool.phone is null";
        	}
        }
        if ( $from != ''){
            $where .= ' and customerpool.from="'.$from.'"';
        }

        $list = $model->page($page, $pagesize)->join('CustomerPoolStatus t','t.id = customerpool.id','inner')->where($where)->field('customerpool.*')->order('t.id desc')->select();
        $return['list'] = $list;
        $return['count'] = $model->alias('customerpool')->join('CustomerPoolStatus t','t.id = customerpool.id','inner')->where($where)->count();

        return $return;
    }
    
    /**
     * 获取顾问可认领客户
     * @param int $employee 顾问编号
     * @param array $search 搜索条件
     * @param int $page 分页
     */
    public function getRecognize($employee, $search, $page = 1 ,$pagesize = 20)
    {
    	$return = array();
    	$model = model('CustomerPool');
    	$where = $this->openCondition();
    	if ($search){
    		if (isset($search['real_name']) && $search['real_name'] != ''){
    			$where .= ' and customer_pool.real_name like"'.$search['real_name'].'%"';
    		}
    		if (isset($search['phone']) && $search['phone'] != ''){
    			$phone = $search['phone'];
    			if (is_mobile($phone)){
    				$where .= ' and (customer_pool.phone="'.$phone.'" OR customer_pool.mobile_1="'.$phone.'")';
    			} elseif (is_numeric($phone)) {
    				$where .= ' and (customer_pool.phone like"'.$phone.'%" OR customer_pool.mobile_1 like"'.$phone.'%")';
    			} else {
    				$where .= " and customer_pool.phone is null";
    			}
    		}
    		if (isset($search['from']) && $search['from'] != ''){
    			$from = $search['from'];
    			$where .= ' and customer_pool.from ="'.$from.'"';
    		}
    		if (isset($search['open_start']) && $search['open_start'] != ''){
    			$open_start = $search['open_start'];
    			$where .= ' and open_time >= ' . strtotime($open_start);
    		}
    		if (isset($search['open_end']) && $search['open_end'] != ''){
    			$open_end = $search['open_end'];
    			$where .= ' and open_time <= ' . strtotime(day_end_time($open_end));
    		}	
    	}
    	/*客户认领条件*/
    	$employee_biz = controller('admin/EmployeeBiz', 'business');
    	$where .= " AND (";
    	/*自己的*/
    	$employee_id = $employee['id'];
    	//1,个人丢弃
    	$person_depose_time = $employee_biz->getRecognizeSelfPersonalTime($employee['recognize_prior_time']);
    	$where .= "(t.deposer = $employee_id AND t.depose_type = '".self::DEPOSE_PERSONAL."' AND open_time<='$person_depose_time')";
    	//2,系统丢弃
    	$sys_depose_time = $employee_biz->getRecognizeSelfSystemTime($employee['recognize_prior_time']);
    	$where .= " OR (t.deposer = $employee_id AND t.depose_type = '".self::DEPOSE_SYSTEM."' AND open_time<='$sys_depose_time')";
    	/*同组的*/
    	$business = controller('admin/OrganizationBiz', 'business');
    	$employees = $business->getEmployees($employee['org_id']);
    	$arr_employee_id = [];
    	foreach($employees as $item){
    		if ($item['id'] != $employee_id) $arr_employee_id[] = $item['id'];
    	}
    	if ($arr_employee_id){
    		//1,同组丢弃
    		$group_person_depose_time = $employee_biz->getRecognizeGroupPersonalTime($employee['recognize_prior_time']);
    		$where .= " OR (t.deposer IN (".implode(',', $arr_employee_id).") AND t.depose_type = '".self::DEPOSE_PERSONAL."' AND open_time<='$group_person_depose_time')";
    		//2,系统丢弃
    		$group_sys_depose_time = $employee_biz->getRecognizeGroupSystemTime($employee['recognize_prior_time']);
    		$where .= " OR (t.deposer IN (".implode(',', $arr_employee_id).") AND t.depose_type = '".self::DEPOSE_SYSTEM."' AND open_time<='$group_sys_depose_time')";
    	}
    	/*其他顾问丢弃的*/
    	array_push($arr_employee_id, $employee_id);
    	$depose_time = $employee_biz->getRecognizeTime($employee['recognize_prior_time']);
    	$where .= " OR (t.deposer NOT IN (".implode(',', $arr_employee_id).") AND open_time<='$depose_time')";
    	/*其他不是丢弃的*/
    	$where .= " OR t.deposer IS NULL";
    	$where .= ")";
    	$list = $model->alias('customer_pool')->page($page, $pagesize)->join('CustomerPoolStatus t','t.id = customer_pool.id','inner')->where($where)->order('open_time desc,t.id desc')->select();
    	$return['list'] = $list;
    	$return['count'] = $model->alias('customer_pool')->join('CustomerPoolStatus t','t.id = customer_pool.id','inner')->where($where)->count();
    
    	return $return;
    }

    /**
     * 获取所有客户池公开客户
     */
    public function getOpen($real_name, $phone, $from, $page = 1, $pagesize = 20, $open_start = '', $open_end = '')
    {   
        $return = array();
        $model = model('CustomerPool');
        $where = $this->openCondition();
        if ($real_name != ''){
            $where .= ' and customer_pool.real_name like"'.$real_name.'%"';
        }
        if ($phone != ''){
            if (is_mobile($phone)){
            	$where .= ' and (customer_pool.phone="'.$phone.'" OR customer_pool.mobile_1="'.$phone.'")';
            } elseif (is_numeric($phone)) {
            	$where .= ' and (customer_pool.phone like"'.$phone.'%" OR customer_pool.mobile_1 like"'.$phone.'%")';
            } else {
            	$where .= " and customer_pool.phone is null";
            }
        }
        if ($from != ''){
            $where .= ' and customer_pool.from ="'.$from.'"';
        }
        if ($open_start != ''){
        	$where .= ' and open_time >= ' . strtotime($open_start);
        }
        if ($open_end != ''){
        	$where .= ' and open_time <= ' . strtotime(day_end_time($open_end));
        }

        $list = $model->alias('customer_pool')->page( $page , $pagesize )->join('CustomerPoolStatus t','t.id = customer_pool.id','inner')->where($where)->order('t.open_time desc')->select();
        $return['list'] = $list;
        $return['count'] = $model->alias('customer_pool')->join('CustomerPoolStatus t','t.id = customer_pool.id','inner')->where($where)->count();

        return $return;
    }
    
    private function openCondition(){
    	return 't.is_open = 1';
    }
    
    /**
     * 获取所有角色信息
     */
    public function get($id){
        $model = model('CustomerPool');
        $obj = $model->get($id);
        $obj->detail;
        $obj->poolStatus;
        if ($obj['birthday']) {
        	$birth = substr(trim(($obj['birthday'])),0,4);
        	$now = date('Y',time());
        	$age = $now - intval($birth);
        	$obj['age'] = ($age > 15 && $age < 60)?$age:'';
        } else {
        	$obj['age'] = '';
        }
        $obj['from_value'] = $obj->getData('from');
        return $obj;
    }

    /**
     * 分配网站注册用户
     * @param array $arr 端口用户编号
     * @param integer $adviser 顾问编号
     * @param integer $employee_id 分配人编号
     * @param integer $admin_id 分配用户编号
     */
    public function distributeWeb($arr, $adviser, $employee_id, $admin_id) {
        $return = [];
        $modeluser = model('User');
        foreach ($arr as $k=>$v){
        	//通过uid查询手机号码
            $userdata = $modeluser->join( 'yl_user_data d' , ' d.uid = yl_user.uid' , 'inner')->where('d.uid',$v)->find();
        	//通过手机号码验证是否在客户池有记录
            $model = model('customer/CustomerPool');
            $where = "`phone`=".$userdata['mobile']." OR `mobile_1`=".$userdata['mobile'];
            $res = $model->where($where)->find();
            if ($res){
                if ($this->isAssign($res['id'])==0) {
                	//客户未分配，分配给该顾问
                	$return[] = $res['id'];
                }
            }else{
                // 没有记录就插入新纪录
                $data['real_name'] = $userdata['real_name'];
                $data['phone'] = $userdata['mobile'];
                $data['gender'] = $userdata['gender'];
                $data['career'] = $userdata['degree'];
                $data['birthday'] = $userdata['birth'];
                $data['from'] = self::FROM_WEB;
                $data['employee_id'] = $employee_id;
                $data['admin_id'] = $admin_id;
                $return[] = $this->save($data);
            }
        }
        /*更新分配后状态*/
        if ($return && $this->distribute($return, $adviser, $employee_id)) {
        	foreach ($arr as $k=>$v){
        		$modeluser->where('uid',$v)->update(['is_vip'=>2, 'adviser_id'=>$adviser]);
        	}
        	$flag = true;
        } else {
        	$flag = false;
        }
        return $flag;
    }

    /**
     * 分配已报名客户的逻辑
     */
    public function distributePro($arr, $adviser, $employee_id, $admin_id){
    	$modeluser = model('User');    	
        foreach ($arr as $k=>$v){
        	//通过uid查询手机号码
            $userdata = $modeluser->join('yl_user_data d', 'd.uid = yl_user.uid', 'inner')->where('d.uid',$v)->find();
        	//通过手机号码验证是否在客户池有记录
            $model = model('CustomerPool');
            $res = $model->where('phone',$userdata['mobile'])->find();
            if ($res){
            if ($this->isAssign($res['id'])==0) {
                	//客户未分配，分配给该客户
                	$return[] = $res['id'];
                }
            }else{
                // 没有记录就插入新纪录
                $data['real_name'] = $userdata['real_name'];
                $data['phone'] = $userdata['mobile'];
                $data['gender'] = $userdata['gender'];
                $data['career'] = $userdata['degree'];
                $data['birthday'] = $userdata['birth'];
                $data['employee_id'] = $employee_id;
                $data['admin_id'] = $admin_id;
                $data['from'] = self::FROM_SIGNUP;
                $return[] = $this->save($data);
            }
        }
        /*更新分配后状态*/
        if (isset($return) && $this->distribute($return, $adviser)) {
        	foreach ($arr as $k=>$v){
        		$modelpro = model('UserJobProcess');
        		$modelpro->where('user_id',$v)->update(['adviser_id'=>$adviser, 'is_assign'=>self::SIGNED_USER]);
        	}
        	$flag = true;	
        } else {
        	$flag = false;
        }
        return $flag;
    }
    
    /**
     * 是否可以优先认领
     */
    private function canPriorRecognize($employee_id){
    	$flag = true;
    	/*判断当前时间是否为优先认领时间，优先时间段内判断优先名额*/
    	$current = date('H:i', time());
    	if ($current >= Config::get('recognize_prior_floor') && $current < Config::get('recognize_open_hour')) {
    		$biz = controller('admin/CacheBiz', 'business');
    		$statistics_key = $biz->getEmployeeStatisticsKey();//员工数据缓存
    		$arr_statistics = Cache::get($statistics_key);
    		$ceil = $arr_statistics[$employee_id]['recognize_priors'];//优先认领名额
    		if ($ceil<=0){ 
    			$flag = false;//没有优先认领名额
    		} else {
    			$cache_key = $biz->getEmployeeStatisticsTmpKey($employee_id);//员工临时数据缓存
    			if (Cache::get($cache_key) == ''){
    				Cache::set($cache_key, 1, 3600);//初始化缓存
    			} else if (Cache::get($cache_key) < $ceil){
    				$count = Cache::get($cache_key) + 1;
    				Cache::set($cache_key, $count, 3600);//更新缓存
    			} else {
    				$flag = false;//优先认领名额已满
    			}	
    		}
    	}
    	return $flag;
    }
    
    /**
     * 是否为优先认领的客户
     * @param array $customer
     */
    private function isPriorCustomer($customer, $employee=[]){
    	$flag = false;
    	$current = date('H:i');
    	/*优先认领时间*/
    	$floor_time = (Config::get('recognize_open_hour') - 1) . ':' . (60-$employee['recognize_prior_time']);
    	if ($employee['recognize_prior_time'] > 0 && $current >= $floor_time && $current < Config::get('recognize_open_hour')){
    		$depose_day = date('Y-m-d', $customer['open_time']);//丢弃时间
    		/*自己的*/
    		$employee_id = $employee['id'];
    		//1,个人丢弃
    		$employee_biz = controller('admin/EmployeeBiz', 'business');
    		$person_depose_time = $employee_biz->getRecognizeSelfPersonalTime($employee['recognize_prior_time']);//优先时间
    		//2,系统丢弃
    		$sys_depose_time = $employee_biz->getRecognizeSelfSystemTime($employee['recognize_prior_time']);
    		/*同组的*/
    		$business = controller('admin/OrganizationBiz', 'business');
    		$employees = $business->getEmployees($employee['org_id']);
    		$arr_employee_id = [];
    		foreach($employees as $item){
    			if ($item['id'] != $employee_id) $arr_employee_id[] = $item['id'];
    		}
    		//1,同组丢弃
    		$group_person_depose_time = $employee_biz->getRecognizeGroupPersonalTime($employee['recognize_prior_time']);
    		//2,系统丢弃
    		$group_sys_depose_time = $employee_biz->getRecognizeGroupSystemTime($employee['recognize_prior_time']);
    		/*其他顾问丢弃的*/
    		$arr_group = $arr_employee_id;
    		array_push($arr_group, $employee_id);
    		$depose_time = $employee_biz->getRecognizeTime($employee['recognize_prior_time']);

    		/*其他组顾问丢弃的*/
    		if (! in_array($customer['deposer'], $arr_group)
    				&& $depose_day==date('Y-m-d', $depose_time-3600*60)){
    			$flag = true;
    		}
    		/*系统丢弃同组的*/
    		elseif (in_array($customer['deposer'], $arr_employee_id)
    					&& $customer['depose_type']==self::DEPOSE_SYSTEM
    					&& $depose_day==date('Y-m-d', $group_sys_depose_time-3600*24)){
    				$flag = true;
    		}
    		/*同组丢弃的*/
    		else if (in_array($customer['deposer'], $arr_employee_id)
    				&& $customer['depose_type']==self::DEPOSE_PERSONAL
    				&& $depose_day==date('Y-m-d', $group_person_depose_time-3600*24)){
    			$flag = true;
    		}
    		/*系统丢弃自己的*/
    		elseif ($customer['deposer']==$employee_id
    				&& $customer['depose_type']==self::DEPOSE_SYSTEM
    				&& $depose_day==date('Y-m-d', $sys_depose_time-3600*24)){
    			$flag = true;
    		}
    		/*自己丢弃的*/
    		elseif ($customer['deposer']==$employee_id
    			&& $customer['depose_type']==self::DEPOSE_PERSONAL
    			&& $depose_day==date('Y-m-d', $person_depose_time-3600*24)){
    			$flag = true;
    		}
    	}
    	return $flag;
    }

	/**
	 * 顾问认领客户
	 * @param array $data 认领人选
	 * @param int $employee_id 顾问编号
	 * @param array  $employee 顾问信息
	 * @return int 1,认领成功;0,库容已满;-1,优先认领已达上限;-2,此时间段内不能认领该客户
	 */
	public function recognize($data, $employee_id, $employee = []) {
		$return = 1;
		/*是否可认领判断*/
		foreach ($data as $v) {
			$modelstatus = model('customer/CustomerPoolStatus');
			$customer_status = $modelstatus->get($v);
			$employee['id'] = $employee_id;//顾问信息
			/*不可认领判断*/
			if (! $this->canRecognize($customer_status, $employee)) {
				$return = -2;//不可认领
				break;
			}
		}
		if ($return > 0) {
			$flag = $this->distribute($data, $employee_id, $employee_id, [], [], $employee);//分配客户
			if ($flag == false){
				$flag = $this->canPriorRecognize($employee_id);//判断是否为优先认领出错
				if ($flag==false) $return = -1;
				else $return = 0;
			}	
		}
		return $return;
	}

    /**
     * 客户池分配
     * @param array $arr 客户编号
     * @param integer $adviser 顾问编号
     * @param integer $assigner 分配人(员工编号)
     * @param array $status 分配状态
     * @param array $tag 分配标签
     * @param mixed $is_recognize 是否为顾问认领
     */
    public function distribute($arr, $adviser, $assigner = 0, $status = [], $tag = [], $is_recognize = []){
    	$flag = true;
    	/*验证顾问的人选库容是否已满*/
    	$biz = controller('admin/EmployeeBiz', 'business');
    	$left_quantity = $biz->checkCandidateMax($adviser);//剩余库容数量
    	if ($left_quantity > 0) {
    		foreach ($arr as $k=>$v){
    			if ($k >= $left_quantity) break;//库容超标
    			/*用户状态*/
    			$modelstatus = model('customer/CustomerPoolStatus');
    			$customer_status = $modelstatus->get($v);
    			if ($customer_status['is_assign']) continue;//防止并发时多人同时认领
    			/*优先认领名额判断*/
    			if ($is_recognize){
    				$is_recognize['id'] = $adviser;//顾问信息
    				/*优先认领判断*/
    				if ($this->isPriorCustomer($customer_status, $is_recognize)){
    					if ($this->canPriorRecognize($adviser)==false){
    						$flag = false;//优先认领名额已满
    						/*消息提醒*/
    						$message_biz = controller('admin/MessageBiz', 'business');
    						$message_biz->sendSystemMessage($adviser, lang('recognize_error'));
    						break;
    					}
    				}
    			}
    			/*客户信息*/
    			$modelcustomer = model('customer/CustomerPool');
    			if (! isset($tag[$k])) {
    				$description = $modelcustomer->where('id', $v)->value('description');
    			}		
    			/*新增人选*/
    			$candidate_biz = controller('customer/CandidateBiz', 'business');
    			$candidate_biz->newCandidate(
    				$v, 
    				$adviser, 
    				isset($status[$k])?$status[$k]:0, 
    				$assigner, 
    				isset($tag[$k])?$tag[$k]:$description
    			);
    			/*更新客户状态*/
    			$modelstatus->where('id', $v)->update(['is_assign'=>1, 'is_open'=>0]);//已分配, 不公开
    			$admin_id = $biz->employeeMappingAdmin($assigner);
    			$this->createLog($v, $admin_id, [], $is_recognize?self::LOG_TYPE_R:self::LOG_TYPE_S);//操作日志
    		}
    		if (! $is_recognize) {
    			/*消息提醒*/
    			$message_biz = controller('admin/MessageBiz', 'business');
    			$message_biz->sendSystemMessage($adviser, lang('new_candidate_tip'));
    		}
    	} else {
    		/*消息提醒*/
    		$message_biz = controller('admin/MessageBiz', 'business');
    		$message_biz->sendSystemMessage($adviser, lang('candidate_max'));
    		$flag = false;//库容已满
    	}
        return $flag;
    }

    
    /**
     * 更新指定的角色信息
     */
    public function update($id, $data){

        $model = model('CustomerPool');
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
        if (isset($data['from'])){
            $update['from'] = $data['from'];
        }
        if (isset($data['birthday'])){
            $update['birthday'] = $data['birthday'];
        }
        if (isset($data['hometown'])){
            $update['hometown'] = $data['hometown'];
        }
        if (isset($data['career'])){
            $update['career'] = $data['career'];
        }
        if (isset($data['mobile_1'])){
            $update['mobile_1'] = $data['mobile_1'];
        }
        $model->save($update, ['id' => $id]);//更新主表数据

        $modelData = model('CustomerPoolData');
        $update1 = '';
        if (isset($data['wechat'])){
            $update1['wechat'] = $data['wechat'];
        }
        if (isset($data['qq'])){
            $update1['qq'] = $data['qq'];
        }
        if (isset($data['address'])){
            $update1['address'] = $data['address'];
        }
        if (isset($data['email'])){
            $update1['email'] = $data['email'];
        }
        $modelData->save($update1, ['id' => $id]);//更新副表数据

        $this->createLog($id, $data['admin_id'], $data, self::LOG_TYPE_U);
    }

    /**
     * 用户是否唯一
     */
	public function isUnique($item, $update = false){
		$model = model('customer/CustomerPool');
		$where = "(phone='".$item['phone']."' OR mobile_1='".$item['phone']."')";
		if ($update) $where .= " AND id!='$update'";
		$rs = $model->where($where)->field('id, real_name')->find();//客户详情
		//所属顾问
		return $rs;
	}

    /**
     * 客户被操作历史记录查询
     */
    public function history($id){
		$model = model('CustomerPoolLog');
		$res = $model->where('id',$id)->select();
		foreach ($res as $k => $v){
			$modelpool = model('admin/Admin');
			$name = $modelpool->where('id', $v['admin_id'])->find();
			$v['admin_id'] =$name['admin_name'];
			$v['type'] = lang('log_type_' . strtolower($v['type']));
		}
        return $res;
    }
    
    /**
     * 进入公海客户池
     * @param mixed $id
     * @param boolean $auto 是否为系统自动清理 default false
     */
    public function inPublicPool($id, $employee_id = 0, $auto = false){
    	$modelstatus = model('customer/CustomerPoolStatus');
    	if (is_array($id)){
    		$where = "`id` IN (" . implode(',', $id) . ")";
    	}else{
    		$where = "`id`=" . intval($id);
    	}
    	$update = ['is_assign'=>0, 'is_open'=>1, 'deposer'=>$employee_id, 'open_time'=>time()];
    	if ($auto) $update['depose_type'] = self::DEPOSE_SYSTEM;
    	else $update['depose_type'] = self::DEPOSE_PERSONAL;
    	$flag = $modelstatus->where($where)->update($update);
    	/*操作日志*/
    	$biz = controller('admin/EmployeeBiz', 'business');
    	$admin_id = $biz->employeeMappingAdmin($employee_id);
    	if (is_array($id)){
    		foreach($id as $customer_id){
    			$this->createLog($customer_id, $admin_id, $update, self::LOG_TYPE_D);//操作日志
    		}
    	}else{
    		$this->createLog($id, $admin_id, $update, self::LOG_TYPE_D);//操作日志
    	}
    	return $flag;
    }

    /**
     * 是否为公开客户
     */
    public function isPublic($id){
    	$modelstatus = model('CustomerPoolStatus');
    	$status = $modelstatus->get($id);
    	return $status['is_open'];
    }
    
    /**
     * 是否为分配客户
     */
    public function isAssign($id){
    	$modelstatus = model('customer/CustomerPoolStatus');
    	$status = $modelstatus->get($id);
    	return $status['is_assign'];
    }
    
    /**
     * 客户入职记录
     */
    public function workHistory($id){
        $model = model('CustomerWorkHistory');
        $result = $model->alias('history')->join('CrmJob job', 'job.id=history.job_id')
        	->join('Enterprise ent', 'job.enterprise_id = ent.id')->field('history.*, ent.enterprise_name')->where('cp_id',$id)->select();
        $res = [];
        foreach ($result as $k => $v)
        {   
            //$obj = $v->job;
            $res[$k]['job_name'] = $v['enterprise_name'];
            $onduty_time = $v->getData('create_time');
            $res[$k]['create_time'] = $onduty_time?substr($v['create_time'], 0, 10):'';
            if ($v['end_time']){
                $res[$k]['end_time'] = date('Y-m-d',$v['end_time']);
            }else{
                $res[$k]['end_time'] = '';
            }
        }

        return $res;
    }
    
    /****************************导入接口************************************/
    /**
     * 客户池导入功能
     */
    public function import($rows, $employee_id, $admin_id, $param = []){
    	$result = [];
    	foreach($rows as $key=>$row){
    		$data = array();
    		$flag = true;    			
    		foreach($row as $index=>$value){
    			if ($index >= count($this->import_fields)) break;
    			$field = $this->import_fields[$index];
    			if ($field=='phone'){
    				/*用户联系电话不能为空，并且不能重复*/
    				if ($value=='' || $this->isUnique(['phone' => $value])){
    					$flag = false;
    					break;
    				}
    				$data[$field] = $value;
    			} elseif ($field=='real_name'){
    				/*用户真实姓名不能为空*/
    				if ($value==''){
    					$flag = false;
    					break;
    				}
    				$data[$field] = $value;
    			} elseif ($field=='birthday'){
    				$data[$field] = $this->formatImportBrithday($value);
    			} else if ($field=='gender'){
    				$data[$field] = $this->formatImportGender($value);
    			} else if ($field!='') {
    				$data[$field] = $value;
    			}
    		}
    		if ($flag){
    			$data['from'] = self::FROM_IMPORT;
    			$data['employee_id'] = $employee_id;//当前登录员工
    			$data['admin_id'] = $admin_id;//当前登录用户
    			$cp_id = $this->save($data, true);
    			$flag = $cp_id;
    		}
    		$result[$key] = $flag;
    	}
    	return $result;
    }
    
    /**
     * 设置可导入字段
     */
    public function set_import_field($type=1){
    	if ($type==2) {
    		$this->import_fields = [
    				'real_name',
    				'phone',
    				'idcard',
    				'birthday',
    				'hometown',
    				'description'
    		];
    	}else{
    		$this->import_fields = [
    				'real_name',
    				'gender',
    				'birthday',
    				'career',
    				'phone',
    				'hometown'
    		];
    	}
    }
    
    /**
     * 格式化导入的年龄
     */
    public function formatImportBrithday($age){
    	$today = getdate();
    	$year = $today['year'] - intval($age);
    	$month = $today['mon']<10?'0' . $today['mon']:$today['mon'];
    	$day = $today['mday']<10?'0' . $today['mday']:$today['mday'];
    	return $year . '-' . $month . '-' . $day;
    }
    
    /**
     * 格式化导入的性别
     */
    public function formatImportGender($gender){
    	if ($gender == lang('gender_1')) {
    		$gender = 1;
    	} else if ($gender == lang('gender_0')) {
    		$gender = 0;
    	} else {
    		$gender == intval($gender);
    	}
    	return $gender;
    }
    /****************************导入接口************************************/
    
    /**
     * 添加客户池操作日志
     */
    private function createLog($id, $operator, $data, $type){
    	$type_list = array('C', 'U', 'R', 'D', 'S');
    	if (in_array($type, $type_list)) {
    		$modelLog = model('customer/CustomerPoolLog');
    		$modelLog->create([
    				'id'=> $id,
    				'admin_id'=> $operator,
    				'create_time'=> time(),
    				'content'=> serialize($data),
    				'type'=> $type
    		]);
    	}
    }
    
    /**
     * 客户可认领
     * 根据丢弃时间和丢弃人判断用户的入库时间是否在可认领的日期之内
     * @param array $customer_status 客户信息
     * @param array $employee 顾问信息
     * @return boolean
     */
    public function canRecognize($customer, $employee) {
    	$flag = true;
    	$depose_day = $customer['open_time'];//丢弃时间
    	/*自己的*/
    	$employee_id = $employee['id'];
    	//1,个人丢弃
    	$employee_biz = controller('admin/EmployeeBiz', 'business');
    	$person_depose_time = $employee_biz->getRecognizeSelfPersonalTime($employee['recognize_prior_time']);//优先时间
    	//2,系统丢弃
    	$sys_depose_time = $employee_biz->getRecognizeSelfSystemTime($employee['recognize_prior_time']);
    	/*同组的*/
    	$business = controller('admin/OrganizationBiz', 'business');
    	$employees = $business->getEmployees($employee['org_id']);
    	$arr_employee_id = [];
    	foreach($employees as $item){
    		if ($item['id'] != $employee_id) $arr_employee_id[] = $item['id'];
    	}
    	//1,同组丢弃
    	$group_person_depose_time = $employee_biz->getRecognizeGroupPersonalTime($employee['recognize_prior_time']);
    	//2,系统丢弃
    	$group_sys_depose_time = $employee_biz->getRecognizeGroupSystemTime($employee['recognize_prior_time']);
    	/*其他顾问丢弃的*/
    	$arr_group = $arr_employee_id;
    	array_push($arr_group, $employee_id);
    	$depose_time = $employee_biz->getRecognizeTime($employee['recognize_prior_time']);
    	
    	/*其他组顾问丢弃的*/
    	if (
    		! in_array($customer['deposer'], $arr_group)
    		&& $depose_day >= $depose_time
    	){
    		$flag = false;
    	}
    	/*系统丢弃同组的*/
    	elseif (
    		in_array($customer['deposer'], $arr_employee_id)
    		&& $customer['depose_type'] == self::DEPOSE_SYSTEM
    		&& $depose_day >= $group_sys_depose_time
    	){
    		$flag = false;
    	}
    	/*同组丢弃的*/
    	else if (
    		in_array($customer['deposer'], $arr_employee_id)
    		&& $customer['depose_type'] == self::DEPOSE_PERSONAL
    		&& $depose_day >= $group_person_depose_time
    	){
    		$flag = false;
    	}
    	/*系统丢弃自己的*/
    	elseif (
    		$customer['deposer'] == $employee_id
    		&& $customer['depose_type'] == self::DEPOSE_SYSTEM
    		&& $depose_day >= $sys_depose_time
    	){
    		$flag = false;
    	}
    	/*自己丢弃的*/
    	elseif (
    		$customer['deposer'] == $employee_id
    		&& $customer['depose_type'] == self::DEPOSE_PERSONAL
    		&& $depose_day >= $person_depose_time
    	){
    		$flag = false;
    	}
    	
    	return $flag;
    }
    
    public function secretField($result) {
    	if ($result->poolStatus && $result->poolStatus->is_assign!=1) {
    		$result['phone'] = Format::mobileSecret($result['phone']);
    		if ($result['mobile_1']) $result['mobile_1'] = Format::mobileSecret($result['mobile_1']);
    		if ($result['idcard']) $result['idcard'] = Format::idcardSecret($result['idcard']);
    	}
    	return $result;
    }
    
    /**
     * 删除公海客户
     * 每次限定删除1000人
     * @param int $type [1=>空号,2=>用户关机,3=>不在服务区,4=>停机,5=>被叫忙,6=>网络忙,7=>对方设置了呼入限制,8=>久叫不应)
     * @return boolean
     */
    public function deleteOpenCustomer($type) {
    	$flag = false;
    	if ($type >= 1 && $type <= 8) {
    		$latest_contact_content = lang('contact_fail_type_' . $type);
    		$model = new CustomerPool();
    		$where = $this->openCondition();
    		$where .= " AND `latest_contact_content`='$latest_contact_content'";
    		$arr_id = $model->alias('customerpool')
    			->join('CustomerPoolStatus t','t.id = customerpool.id','inner')
    			->where($where)
    			->limit(1000)
    			->column('customerpool.id');
    		$flag = count($arr_id);
    		$this->delete($arr_id);
    	}
    	return $flag;
    }
    
    /**
     * 删除客户
     */
    public function delete($arr_id) {
    	$flag = false;
    	if ($arr_id) {
    		/*1,删除客户池*/
    		$model = new CustomerPool();
    		$model->destroy($arr_id);
    		/*2,删除客户信息*/
    		$data_model = model('CustomerPoolData');
    		$data_model->where('id IN (' . implode(',', $arr_id) . ')')->delete();
    		/*3,删除客户状态*/
    		$status_model = model('CustomerPoolStatus');
    		$status_model->where('id IN (' . implode(',', $arr_id) . ')')->delete();
    		/*4,删除人选*/
    		$candidate_model = model('Candidate');
    		$candidate_model->where('cp_id IN (' . implode(',', $arr_id) . ')')->delete();
    		/*5,删除联系记录*/
    		$contact_model = model('ContactLog');
    		$contact_model->where('cp_id IN (' . implode(',', $arr_id) . ')')->delete();
    		$flag = true;
    	}
    	return $flag;
    }
    
    /**
     * 获取客户所属顾问
     */
    public function myCandidate($id) {
    	$model = model('candidate');
    	$owner_id = $model->where("`cp_id`=$id AND is_deleted=0")->value('owner_id');
    	if ($owner_id){
    		$biz = controller('admin/EmployeeBiz', 'business');
    		$employee = $biz->getOrganization($owner_id);
			$owner_id = $employee['employee']['nickname'] . '(' . $employee['org']['nickname'] . ')';
    	}
    	return $owner_id;
    }
    
    /**
     * 手机号码是否可分配
     * @return mixed 不可分配返回false，可以分配返回客户编号，此用户不存在返回true
     */
    public function mobileCanAssign($mobile) {
    	$flag = true;
    	//通过手机号码验证是否在客户池有记录
    	$model = model('customer/CustomerPool');
    	$where = "`phone`=" . $mobile . " OR `mobile_1`=" . $mobile;
    	$customer_id = $model->where($where)->value('id');
    	if ($customer_id){
    		$flag = $this->isAssign($customer_id) ? false : $customer_id;
    	}
    	return $flag;
    }
}