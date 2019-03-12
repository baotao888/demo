<?php
namespace app\customer\business;

use think\Config;
use think\Log;
use think\Lang;
use ylcore\Biz;
use app\customer\model\ContactLog;

class ContactLogBiz extends Biz
{
    /**
     * 添加联系日志
     */
    public function save($data, $employee_id){
        $model = model('ContactLog');
        $model->data([
                'cp_id'  =>  $data['cp_id'],
                'content' =>  $data['content'],
                'contact_time' =>  time(),
                'result' =>  $data['result'],
        		'employee_id' => $employee_id
        ]);
        $model->save();//保存
		/*更新人选的最后联系时间*/
		$candidate = model('Candidate');
		$flag = $candidate->save(
			['latest_contact_time'=>time(), 'latest_contact_content'=>$data['content']], 
			['cp_id'=>$data['cp_id'], 'owner_id'=>$employee_id, 'is_deleted'=>0]
		);
		/*更新客户的最后联系内容*/
		if ($flag) {
			$candidate = model('CustomerPool');
			$candidate->update(['latest_contact_content'=>$data['content']], ['id'=>$data['cp_id']]);
		}
        return $model->id;
    }
    
    /**
     * 统计所有联系日志
     * @param int $employee 员工编号
     * @param int $org 部门编号
     * @param string $contact_start 开始时间
     * @param string $contact_end 结束时间
     * @param int $customer 客户编号
     */
    public function getAll($employee_id = '', $org = '', $contact_start = '', $contact_end = '', $customer = false)
    {	
        $arr_employee = array();
        $model = model('ContactLog');
        if ($employee_id) $where = '`employee_id`=$employee_id';
        else $where = '`employee_id`>0';
        if($org != ''){
            $where .= ' and ' . $this->orgCondition($org);
        }
        if($customer){
        	$where .= " and cp_id=$customer";
        }
        /*默认搜索时间范围为今日*/
        if( $contact_start == ''){
        	$contact_start = date('Y-m-d 00:00:00');
        }
        $contact_start = intval(strtotime($contact_start));
        $where .= ' and contact_time >='.$contact_start;
        if( $contact_end == ''){
        	$contact_end = date('Y-m-d 23:59:59');
        } else {
        	$contact_end = day_end_time($contact_end);
        }
        $contact_end= intval(strtotime($contact_end));
        $where .= ' and contact_time <='.$contact_end;

        $list = $model->field('cp_id, employee_id, content, result')->where($where)->select();
        if ($list){
        	$biz = controller('admin/EmployeeBiz', 'business');
        	$arr_employee_customer = [];
            foreach ($list as $item){
            	$key = $item['employee_id'];//按照员工统计
            	$status = $item->getData('result');
            	if (isset($arr_employee[$key])){
            		$arr_employee[$key]['total'] ++;//总数加一
            		if (! $this->isEffective($item)) continue;
            		if (! in_array($item['cp_id'], $arr_employee_customer[$key])){
            			$arr_employee[$key]['effective'] ++;//有效客户数加一
            			if ($status==1){
            				$arr_employee[$key]['intention'] ++;//意向客户数加一
            			}
            			$arr_employee_customer[$key][] = $item['cp_id'];
            		}
            	}else{
            		$employee = $biz->getOrganization($item['employee_id']);
            		$employee_info = $employee['employee']['real_name'] . '(' . $employee['org']['org_name'] . ')';
            		$arr_employee[$key] = ['employee'=>$employee_info, 'effective'=>$this->isEffective($item)?1:0, 'total'=>1, 'intention'=>$status==1?1:0];
            		$arr_employee_customer[$key][] = $item['cp_id'];
            	}
            }
        }
        
        return $arr_employee;
    }
    
    /**
     * 获取所有角色信息
     */
    public function get($id){
        $model = model('ContactLog');
        $obj = $model->get($id);
        
        return $obj;
    }

    /**
     * 获取所有角色信息
     */
    public function distribute($arr , $adviser){
        foreach ($arr as $k=>$v){
            $model = model('Candidate');
            $res = $model->create([
                'cp_id'  =>  $v,
                'owner_id' =>  $adviser,
                'status' =>  0,
                'create_time'  =>  time()
            ]);
            $modelstatus = model('ContactLog');
            $modelstatus->where('id',$v)->update(['is_assign'=>1]);
        }

        return $res;
    }
    
    /**
     * 更新指定的角色信息
     */
    public function update($id, $data){

        $model = model('ContactLog');
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
        $model->save($update, ['id' => $id]);//更新主表数据

        $modelData = model('ContactLog');
        $update1['wechat'] = $data['wechat'];
        $update1['qq'] = $data['qq'];
        $update1['address'] = $data['address'];
        $update1['email'] = $data['email'];
        $modelData->save($update1, ['id' => $id]);//更新副表数据

        $modelLog = model('ContactLog');
        $modelLog->create([
            'id'=> $id,
            'admin_id'=> 2,
            'create_time'=> time(),
            'content'=> serialize($data),
            'type'=> 'U'
            ]);

    }

    /**
     * 获取当前求职顾问当天的联系总数，意向客户总数,有效客户数
     */
    public function today($employee_id){
        $modelData = model('ContactLog');
        $start = strtotime(date('Y-m-d 00:00:00'));
        $end = strtotime(date('Y-m-d H:i:s'));
        $where = 'contact_time >=  '.$start.' AND contact_time <=  '.$end;
        if ($employee_id !== '') {
            $where .= ' and employee_id='.$employee_id;            
        }

        $obj = $modelData->where($where)->field('contact_time,content,result,cp_id')->select();
        
        $return['total'] = count($obj);// 当天联系日志总数
        $return['success'] = 0;// 当天接通电话总数
        $count = [];//当天所有有效电话的客户
        for ($i=0; $i < count($obj); $i++) { 
        	if ($this->isEffective($obj[$i])) {
        		$count[] = $obj[$i]['cp_id'];
        		$return['success']++;
        	}
        }
        $return['effective'] = count(array_unique($count));//当天有效客户数
        // 当天意向客户数
        $where1 = $where.' and result = 1';
        $return['intention'] = $modelData->where($where1)->count();
        
        $res = [];
        foreach($obj as $k=>$v)
        {   
            $res[$k]['contact_time'] = date('H:i:s',$v['contact_time']);
            $res[$k]['content'] = $v['content'];
            $res[$k]['result'] = $v['result'];
            $obj= $v->poolname;
            $res[$k]['customer'] = $obj['real_name'];
            $res[$k]['cp_id'] = $v['cp_id'];
        }
        $return['list'] = $res;//当天联系日志

        return $return;
    }

    /**
     * 获取所有联系日志
     */
    public function getContacts($where = '', $page = 1 , $pagesize = 20)
    {
    	$return = array();
    	$model = model('ContactLog');
    	if ($where == '') $where = '`employee_id`>0';
    	$list = $model->alias('contact')->join('CustomerPool cp', "contact.cp_id = cp.id")->page($page, $pagesize)->field("contact.*, cp.real_name as customer")->where($where)->order('contact.id desc')->select();
    	if ($list){
    		foreach ($list as $item){
    			$item['contact_time'] = date('Y-m-d H:i:s', $item['contact_time']);
    			$return[] = $item;
    		}
    	}
    
    	return $return;
    }
    
    /**
     * 获取我的联系日志
     */
    public function getMyContact($employee_id, $page=1, $pagesize=20, $contact_start='', $contact_end='')
    {
    	$where = "`employee_id`=$employee_id";
    	if( $contact_start != ''){
    		$contact_start= intval(strtotime($contact_start));
    		$where .= ' and contact_time >='.$contact_start;
    	}
    	if( $contact_end != ''){
    		$contact_end= intval(strtotime(day_end_time($contact_end)));
    		$where .= ' and contact_time <='.$contact_end;
    	}
    	return $this->getContacts($where, $page, $pagesize);
    }
    
    private function orgCondition($org_id){
    	$biz = controller('admin/OrganizationBiz', 'business');
    	$employee_list = $biz->subEmployee($org_id);
    	$condition = "`employee_id` IN (" . implode(',', $employee_list) . ")";
    	return $condition;
    }
    
    /**
     * 获取联系记录列表
     */
    public function getList($customer){
    	$return = [];
    	$modelData = model('ContactLog');
        $where = "cp_id='$customer'";
    	$obj = $modelData->where($where)->field('contact_time,content,result,cp_id,employee_id')->select();
    	if ($obj){
    		$biz = controller('admin/EmployeeBiz', 'business');
    		foreach($obj as $k=>$v){
    			$return[$k]['contact_time'] = date('Y-m-d H:i:s',$v['contact_time']);
    			$return[$k]['content'] = $v['content'];
    			$return[$k]['result'] = $v['result'];
    			$employee = $biz->getOrganization($v['employee_id']);
    			$employee_info = $employee['employee']['real_name'] . '(' . $employee['org']['org_name'] . ')';
    			$return[$k]['employee'] = $employee_info;
    			$return[$k]['cp_id'] = $v['cp_id'];
    		}
    	}
    	return $return;
    }
    
    /**
     * 获取顾问联系记录总数
     */
    public function getMyContactTotal($employee_id, $contact_start='', $contact_end=''){
    	$where = "`employee_id`=$employee_id";
    	if( $contact_start != ''){
    		$contact_start= intval(strtotime($contact_start));
    		$where .= ' and contact_time >='.$contact_start;
    	}
    	if( $contact_end != ''){
    		$contact_end= intval(strtotime(day_end_time($contact_end)));
    		$where .= ' and contact_time <='.$contact_end;
    	}
    	$model = model('ContactLog');
    	$total = $model->where($where)->count();
    	return $total;
    }
    
    /**
     * 获取内容配置
     */
    public function getContentSetting() {
    	$return = [
    		['value'=>1, 'html'=>lang('contact_fail_type_1'), 'popover'=>[
    			[lang('contact_fail_1_zh'), lang('contact_fail_1_en')]	
    		]],
    		['value'=>2, 'html'=>lang('contact_fail_type_2'), 'popover'=>[
    			[lang('contact_fail_2_zh'), lang('contact_fail_2_en')]	
    		]],
    		['value'=>3, 'html'=>lang('contact_fail_type_3'), 'popover'=>[
    			[lang('contact_fail_3_zh'), lang('contact_fail_3_en')]	
    		]],
    		['value'=>4, 'html'=>lang('contact_fail_type_4'), 'popover'=>[
    			[lang('contact_fail_4_zh'), lang('contact_fail_4_en')]	
    		]],
    		['value'=>5, 'html'=>lang('contact_fail_type_5'), 'popover'=>[
    			[lang('contact_fail_5_zh'), lang('contact_fail_5_en')],
    			[lang('contact_fail_6_zh'), lang('contact_fail_6_en')]
    		]],
    		['value'=>6, 'html'=>lang('contact_fail_type_6'), 'popover'=>[
    			[lang('contact_fail_7_zh'), lang('contact_fail_7_en')],
    			[lang('contact_fail_9_zh'), lang('contact_fail_9_en')]
    		]],
    		['value'=>7, 'html'=>lang('contact_fail_type_7'), 'popover'=>[
    			[lang('contact_fail_8_zh'), lang('contact_fail_8_en')]	
    		]],
    		['value'=>8, 'html'=>lang('contact_fail_type_8'), 'popover'=>[
    			[lang('contact_fail_10_zh'), lang('contact_fail_10_en')],
    		]]							
    	];
    	return $return;
    }
    
    /**
     * 获取结果设置
     */
    public function getResultSetting() {
    	$return = [
    		lang('contact_result_0'),
    		lang('contact_result_1'), 
    		lang('contact_result_2'), 
    		lang('contact_result_3'),
    		lang('contact_result_4'),
    		lang('contact_result_5')    			
    	];
    	return $return;
    }
    
    /**
     * 电话是否已接通
     */
    private function isEffective($log) {
    	$flag = true;//默认已接通
    	if ($log['content'] == lang('contact_fail_type_1')) {
    		$flag = false;
    	} else if ($log['content'] == lang('contact_fail_type_2')) {
    		$flag = false;
    	} else if ($log['content'] == lang('contact_fail_type_3')) {
    		$flag = false;
    	} else if ($log['content'] == lang('contact_fail_type_4')) {
    		$flag = false;
    	} else if ($log['content'] == lang('contact_fail_type_5')) {
    		$flag = false;
    	} else if ($log['content'] == lang('contact_fail_type_6')) {
    		$flag = false;
    	} else if ($log['content'] == lang('contact_fail_type_7')) {
    		$flag = false;
    	} else if ($log['content'] == lang('contact_fail_type_8')) {
    		$flag = false;
    	}
    	return $flag;
    }
}