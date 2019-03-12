<?php

namespace app\salesorder\controller;

use think\Cache;
use think\Request;

use app\admin\controller\AdminAuth;

class Index extends AdminAuth
{
    /**
     * 业绩入账
     *
     * @return \think\Response
     */
    public function sure()
    {
        /*参数验证*/
    	$param = request()->param();
    	$id = $param['id'];
    	$work_time = $param['work_time'];
    	if (! $id || ! $work_time) abort(400, '400 Invalid id/work_time supplied');
    	$arr_id = [$id];
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('SalesorderBiz', 'business');
    	$business->sure($arr_id, $this->getAdminId(), $work_time);
    	return true;
    }

    /**
     * 删除业绩
     *
     * @return \think\Response
     */
    public function delete()
    {
        /*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	if (! is_array($arr_id)) $arr_id = explode(',', $arr_id);
    	$note = isset($param['note']) ? $param['note'] : '';
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('SalesorderBiz', 'business');
    	$flag = $business->delete($arr_id, $this->getAdminId(), $note);
    	return true;
    }

    /**
     * 回复业绩
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function recover(Request $request)
    {
        /*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	if (! is_array($arr_id)) $arr_id = explode(',', $arr_id);
    	$note = isset($param['note']) ? $param['note'] : '';
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('SalesorderBiz', 'business');
    	$flag = $business->recover($arr_id, $this->getAdminId(), $note);
    	return true;
    }

    /**
     * 领取补贴
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function receiveallowance(Request $request)
    {
        /*参数验证*/
    	$param = $request->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	if (! is_array($arr_id)) $arr_id = explode(',', $arr_id);
    	$note = isset($param['note']) ? $param['note'] : '';
    	$pay_way = isset($param['pay_way']) ? $param['pay_way'] : false;
    	if (! $pay_way) abort(400, '400 Invalid pay_way supplied');
    	$is_borrow = isset($param['is_borrow']) ? $param['is_borrow'] : false;
    	$is_borrow = $is_borrow == 'true' ? 1 : 0;
    	
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('SalesorderBiz', 'business');
    	$flag = $business->receiveAllowance($arr_id, $this->getAdminId(), $pay_way, $is_borrow, $note);
    	return true;
    }

    /**
     * 领取推荐费
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function receiverecommend(Request $request)
    {
        /*参数验证*/
    	$param = $request->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	if (! is_array($arr_id)) $arr_id = explode(',', $arr_id);
    	$note = isset($param['note']) ? $param['note'] : '';
    	$pay_way = isset($param['pay_way']) ? $param['pay_way'] : false;
    	if (! $pay_way) abort(400, '400 Invalid pay_way supplied');
    	$is_borrow = isset($param['is_borrow']) ? $param['is_borrow'] : false;
    	$is_borrow = $is_borrow == 'true' ? 1 : 0;
    	
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('SalesorderBiz', 'business');
    	$flag = $business->receiveRecommend($arr_id, $this->getAdminId(), $pay_way, $is_borrow, $note);
    	return true;
    }

    /**
     * 调整小时工差价
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function adjusthourprice(Request $request)
    {
        /*参数验证*/
    	$param = $request->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	if (! is_array($arr_id)) $arr_id = explode(',', $arr_id);
    	$note = isset($param['note']) ? $param['note'] : '';
    	$price = isset($param['price']) ? $param['price'] : false;
    	if ($price <= 0) abort(400, '400 Invalid price supplied');
    	
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('SalesorderBiz', 'business');
    	$flag = $business->adjustHourPrice($arr_id, $this->getAdminId(), $price, $note);
    	return true;
    }

    /**
     * 验证数据权限
     * @param: $arr_id array 业绩编号
     */
    private function checkDataAuth($arr_id){
    	$flag = false;
    	//超级管理员不验证
    	if ($this->isAdministrator()) {
    		$flag = true;
    	} else {
    		/*验证业绩是否对顾问可见*/
    		$business = controller('SalesorderBiz', 'business');
    		$employee_id = $this->getEmployeeId();
    		$org_id = $this->getOrganizationId();
    		if ($business->validateEmployee($employee_id, $arr_id, $org_id)) {
    			$flag = true;
    		}
    	}
    	 
    	$flag == false && abort(403, '403 Forbidden');
    }
    
    /**
     * 业绩导出
     */
    public function export() {
    	/*获取参数*/
    	$param = request()->param();
    	if (! isset($param['ids/a']) || empty($param['ids/a'])) abort(400, '400 Invalid id supplied');
    	$arr_id = $param['ids/a'];//业绩编号
    	$type = isset($param['type']) ? $param['type'] : 1;
    	/*获取操作用户*/
    	$user_name = $this->fetchExportUser();
    	/*表格头部信息*/
    	$export_title = $this->fetchExportHead($type);
    	/*获取内容*/
    	$business = controller('SalesorderBiz', 'business');
    	$rows = $business->exportAll($type, $arr_id);
    	
    	$fields = $this->fetchExportField($type); // 可导出字段
    	
    	$this->outputExcel($user_name, lang('export_user_title'), lang('export_user_description'), $export_title, $rows, $fields);
    }
    
    private function fetchExportField($type) {
    	$customer_fields = ['enterprise', 'labour_service', 'real_name', 'gender', 'phone', 'idcard', 'go_to_time']; // 客户字段
    	$invite_fields = ['inviter', 'inviter_phone', 'invite_amount', 'is_customer_invite', 'paid_invite_way', 'is_borrow_invite']; // 推荐字段
    	if ($type == 2) {
    		$profit_fields = ['worked_time', 'is_outduty', 'ent_wage', 'amount', 'adviser_sure', 'cp_wage', 'allowance', 'adjusted_price'];
    	} else if ($type == 3) {
    		$profit_fields = ['receive_day', 'onduty_day', 'deadline', 'receive_date', 'amount', 'adviser_sure'];
    	} else {
    		$profit_fields = ['receive_day', 'onduty_day', 'deadline', 'receive_date', 'amount', 'adviser_sure', 'allowance', 'paid_allowance_way', 'is_borrow_allowance'];
    	}
    	return array_merge($customer_fields, $profit_fields, $invite_fields);
    }
    
    /**
     * 获取表格头部
     */
    private function fetchExportHead($type) {
    	$arr_customer_pool = [
    		lang('header_enterprise'), 
    		lang('header_labour'), 
    		lang('header_realname'), 
    		lang('header_gender'),
    		lang('header_mobile'),
    		lang('header_idcard'),
    		lang('header_gototime'),	  			
    	];
    	$arr_invite = [
    		lang('header_inviter'), 
    		lang('header_inviter_phone'), 
    		lang('header_invite_amount'), 
    		lang('header_is_member_invite'),
    		lang('header_paid_invite_way'),
    		lang('header_is_borrow_invite'),    			
    	];
    	if ($type == 2) {
    		$arr_profit =  [
    			lang('header_worktime'), 
    			lang('header_is_outduty'), 
    			lang('header_enterprise_price'), 
    			lang('header_amount'),
    			lang('header_adviser_sure'),    				
    			lang('header_candidate_price'),
    			lang('header_ambulance'),
    			lang('header_adjusted_price'),
    		];
    	} else if ($type == 3) {
    		$arr_profit =  [
    			lang('header_receive_day'), 
    			lang('header_onduty_day'), 
    			lang('header_deadline'), 
    			lang('header_receivetime'),
    			lang('header_amount'),
    			lang('header_adviser_sure'),   				    				
    		];
    	} else {
    		$arr_profit =  [
    			lang('header_receive_day'), 
    			lang('header_onduty_day'), 
    			lang('header_deadline'), 
    			lang('header_receivetime'),
    			lang('header_amount'),
    			lang('header_adviser_sure'),
    			lang('header_ambulance'),
    			lang('header_paid_allowance_way'),
    			lang('header_is_borrow_allowance'),    				    				
    		];
    	}
    	return array_merge($arr_customer_pool, $arr_profit, $arr_invite);
    }
    
    /**
     * 获取操作用户
     */
    private function fetchExportUser() {
    	$admin_business = controller('admin/AdminBiz', 'business');//业务对象
    	$token_key = $admin_business->getTokenKey(request()->header('yl-crm-token'));
    	$user_information = Cache::get($token_key);
    	if ($admin_business->isAdminToken($user_information)) {
    		$user_name = lang('export_default_user');
    	} else {
    		$user_name = $user_information['employee']['nickname'];
    	}
    	return $user_name;
    }
    
    /**
     * 输出excel文件
     */
    private function outputExcel($author, $title, $description, $export_title, $rows, $fields) {
    	/*1, 加载phpexcel类库*/
    	import('PHPExcel.PHPExcel', EXTEND_PATH);
    	/*定义phpexcel对象*/
    	$objPHPExcel = new \PHPExcel();
    	/*设置文件属性*/
    	$objPHPExcel
    	->getProperties()
    	->setCreator($author)
    	->setLastModifiedBy($author)
    	->setTitle($title)
    	->setSubject($title)
    	->setDescription($description);
    	$sheet_index = 0;//表索引
    	$num = 1;//行索引
    	/*添加头部*/
    	$objPHPExcel->setActiveSheetIndex($sheet_index);
    	$arr_column_index = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    	foreach($export_title as $tk=>$str_title){
    		$objPHPExcel->getActiveSheet()->setCellValue($arr_column_index[$tk].$num, $str_title);
    	}
    	$num++;
    		
    	/*添加内容*/
    	foreach ($rows as $rk=>$row) {
    		foreach ($fields as $cloumn_key=>$value_key) {
    			if (in_array($value_key, ['idcard', 'phone', 'inviter_phone'])) {
    				$objPHPExcel->getActiveSheet()->setCellValueExplicit($arr_column_index[$cloumn_key].$num, $row[$value_key], \PHPExcel_Cell_DataType::TYPE_STRING);
    			} else {
    				$objPHPExcel->getActiveSheet()->setCellValue($arr_column_index[$cloumn_key].$num, $row[$value_key]);
    			}
    		}
    		$num++;
    	}
    	//设置sheet名称
    	$objPHPExcel->getActiveSheet()->setTitle($title);
    
    	/*客户端下载*/
    	$file_name = date("YmdHis");
    	$file_type = '.xls';
    	ob_end_clean();
    	header('Content-Type: application/vnd.ms-excel');
    	header('Content-Disposition: attachment;filename="'.$file_name.$file_type.'"');
    	// If you're serving to IE over SSL, then the following may be needed
    	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    	header ('Pragma: public'); // HTTP/1.0
    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    	$objWriter->save('php://output');
    	exit();
    }
    
	/**
     * 离职
     *
     * @return \think\Response
     */
    public function outduty()
    {
        /*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	if (! is_array($arr_id)) $arr_id = explode(',', $arr_id);
    	$note = isset($param['note']) ? $param['note'] : '';
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('SalesorderBiz', 'business');
    	$flag = $business->outduty($arr_id, $this->getAdminId(), $note);
    	return true;
    }
    
	/**
     * 继续在职
     *
     * @return \think\Response
     */
    public function goonduty()
    {
        /*参数验证*/
    	$param = request()->param();
    	$arr_id = $param['id/a'];
    	if (! $arr_id) abort(400, '400 Invalid id supplied');
    	if (! is_array($arr_id)) $arr_id = explode(',', $arr_id);
    	$note = isset($param['note']) ? $param['note'] : '';
    	/*客户权限验证*/
    	$this->checkDataAuth($arr_id);
    	
    	$business = controller('SalesorderBiz', 'business');
    	$flag = $business->goonduty($arr_id, $this->getAdminId(), $note);
    	return true;
    }
}
