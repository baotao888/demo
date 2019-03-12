<?php
namespace app\user\controller;

use think\Request;
use ylcore\Format;
use think\Log;
use think\Config;
use think\Cache;
use app\admin\controller\AdminAuth;

class Index extends AdminAuth
{
	/**
	 * 显示网站注册其他人选列表
	 *
	 * @return \think\Response
	 */
	public function index()
	{
		/*获取参数*/
		$param = request()->param();
		$page = isset($param['page'])?$param['page']:1;
		$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
		$search = [];
		if (isset($param['keyword'])) $search['keyword'] = $param['keyword'];
		if (isset($param['adviser_id'])) $search['adviser_id'] = $param['adviser_id'];
		if (isset($param['is_vip'])) $search['is_vip'] = $param['is_vip'];
		if (isset($param['reg_time_start'])) $search['reg_time_start'] = $param['reg_time_start'];
		if (isset($param['reg_time_end'])) $search['reg_time_end'] = $param['reg_time_end'];
		$business = controller('user', 'business');
		$return = $business->notCallinUser($search, $page, $pagesize);
		return $return;
	}
	
	/**
	 * 认领
	 * 顾问认领自己的注册客户
	 */
	public function distribute(){
		$param = request()->param();
		if (! isset($param['users/a']) || empty($param['users/a'])) {
			abort(400, '400 Invalid employee/users supplied');
		}
		$business = controller('user', 'business');
		$business->distribute($this->getEmployeeId(), $param['users/a']);
		return true;
	}
	
	/**
	 * 导出
	 */
	public function export(){
		/*获取参数*/
		$param = request()->param();
		if (! isset($param['users/a']) || empty($param['users/a'])) abort(400, '400 Invalid id supplied');
		$arr_id = $param['users/a'];//客户编号
		/*获取操作用户*/
		$user_name = $this->fetchExportUser();
		/*表格头部信息*/
		$export_title =  [lang('header_id'), lang('header_realname'), lang('header_mobile'), lang('header_time')];
		/*获取内容*/
		$business = controller('user', 'business');
		$condition = 'uid IN (' . implode(',', $arr_id) . ')';
		$rows = $business->getAll(1, 2000, false, false, 'uid', 'desc', false, $condition);
		$fields = ['uid', 'real_name', 'mobile', 'reg_time'];//可导出字段
		$this->outputExcel($user_name, lang('export_user_title'), lang('export_user_description'), $export_title, $rows, $fields);
	}
	
	/**
	 * 报名信息列表
	 */
	public function signupList(){
		/*获取参数*/
		$param = request()->param();
		$page = isset($param['page'])?$param['page']:1;
		$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
		$search = [];
		if (isset($param['is_signned'])) $search['is_signned'] = $param['is_signned'];
		if (isset($param['job_id'])) $search['job_id'] = $param['job_id'];
		if (isset($param['keyword'])) $search['keyword'] = $param['keyword'];
		if (isset($param['adviser_id'])) $search['adviser_id'] = $param['adviser_id'];
		if (isset($param['creat_time_start'])) $search['creat_time_start'] = $param['creat_time_start'];
		if (isset($param['creat_time_end'])) $search['creat_time_end'] = $param['creat_time_end'];
		$business = controller('user', 'business');
		$order = (isset($param['is_signned']) && $param['is_signned'])?'owner_id desc':'creat_time desc';
		$list = $business->getSignupList($search, $page, $pagesize, $order);
		$count = $business->getSignupCount($search);
		return ['list' => $list, 'count'=>$count];
	}
	
	/**
	 * 认领
	 * 顾问认领自己的报名客户
	 */
	public function distributeSignup(){
		$param = request()->param();
		if (! isset($param['users/a']) || empty($param['users/a'])) {
			abort(400, '400 Invalid employee/users supplied');
		}
		$business = controller('user', 'business');
		$business->recognize($this->getEmployeeId(), $param['users/a']);
		return true;
	}
	
	/**
	 * 导出
	 * 导出网站报名信息
	 */
	public function exportSignup() {
		/*获取参数*/
		$param = request()->param();
		if (! isset($param['users/a']) || empty($param['users/a'])) abort(400, '400 Invalid id supplied');
		$arr_id = $param['users/a'];//客户编号
		/*获取操作用户*/
		$user_name = $this->fetchExportUser();
		/*表格头部信息*/
		$export_title =  [lang('header_id'), lang('header_realname'), lang('header_mobile'), lang('job_name'), lang('header_time')];
		/*获取内容*/
		$business = controller('user', 'business');
		$condition = 'user_id IN (' . implode(',', $arr_id) . ')';
		$rows = $business->getAllUserJobProcess(2000, 'job_id', 'asc', $condition);
		$fields = ['user_id', 'real_name', 'mobile', 'job_name', 'creat_time'];//可导出字段
		$this->outputExcel($user_name, lang('export_signup_title'), lang('export_signup_description'), $export_title, $rows, $fields);
	}
	
	/**
	 * 推荐信息列表
	 */
	public function inviteList(){
		/*获取参数*/
		$param = request()->param();
		$page = isset($param['page'])?$param['page']:1;
		$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
		$search = isset($param['search'])?$param['search']:false;
		$business = controller('user', 'business');
		$list = $business->getInviteList($page, $pagesize, $search, true);
		$count = $business->getInviteCount($search);
		return ['list' => Format::object2array($list), 'count'=>$count];
	}
	
	/**
	 * 导出
	 * 导出网站推荐信息
	 */
	public function exportInvite(){
		return true;
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
		$arr_column_index = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N');
		foreach($export_title as $tk=>$str_title){
			$objPHPExcel->getActiveSheet()->setCellValue($arr_column_index[$tk].$num, $str_title);
		}
		$num++;
			
		/*添加内容*/
		foreach ($rows as $rk=>$row) {
			foreach ($fields as $cloumn_key=>$value_key) {
				$objPHPExcel->getActiveSheet()->setCellValue($arr_column_index[$cloumn_key].$num, $row[$value_key]);
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
	 * 显示网站职位申请人选列表
	 *
	 * @return \think\Response
	 */
	public function jobapplies()
	{
		/*获取参数*/
		$param = request()->param();
		$page = isset($param['page'])?$param['page']:1;
		$pagesize = isset($param['pagesize'])?$param['pagesize']:Config::get('user_list_pagesize_default');
		$search = [];
		if (isset($param['keyword'])) $search['keyword'] = $param['keyword'];
		if (isset($param['creat_time_start'])) $search['time_start'] = $param['creat_time_start'];
		if (isset($param['creat_time_end'])) $search['time_end'] = $param['creat_time_end'];
		$business = controller('user', 'business');
		$return = $business->notCallinJobApply($search, $page, $pagesize);
		return $return;
	}
}