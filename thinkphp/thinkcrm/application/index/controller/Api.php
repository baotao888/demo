<?php
namespace app\index\controller;

use think\Cache;
use think\Config;
use think\Controller;
use think\Log;

use app\user\business\CallinFactory;
use app\customer\business\Market;

class Api extends Controller
{
	const SUCCESS = 'ok';
	const FAILURE = 'error';
	
	/**
     * 注册通知
     */
    public function notifyRegister() {
    	/*参数验证*/
    	$param = request()->param();
    	if (! isset($param['mobile']) || ! isset($param['real_name'])) {
        	abort(400, '400 Invalid mobile/real_name supplied');
        }
    	$customer_business = controller('customer/CandidateBiz', 'business');//业务对象
    	$receiver = $customer_business->getEmployeeIdByCustomerMobile($param['mobile']);//查询顾问
    	/*市场推广*/
    	$from = 'web';
    	if (isset($param['market']) && $param['market']) {
    		$arr = $this->recognizeMarket($param, $receiver, 'register');
    		$receiver = $arr['receiver'];
    		$from = $arr['from'];
    	}
    	/*插入callin客户池*/
    	$callin_biz = CallinFactory::instance('register', $from);
    	$callin_biz->save($receiver, $param['mobile'], $param['real_name']);
    	if ($receiver){
    		/*发送消息*/
    		$biz = controller('admin/MessageBiz', 'business');
    		if ($from == '') {
    			$msg = lang('notify_register_tip', [$param['mobile'], $param['real_name']]); 
    		} else {
    			$msg = lang('notify_register_' . $from, [$param['mobile'], $param['real_name']]);
    		}
    		$biz->sendSystemMessage($receiver, $msg);//发送消息
    		$msg = self::SUCCESS;
    	} else {
    		$msg = self::FAILURE;
    	}
    	return ['msg' => $msg];
    }
    
    /**
     * 报名通知
     * @return multitype:string
     */
    public function notifySignup() {
    	/*参数验证*/
    	$param = request()->param();
    	if (! isset($param['mobile']) || ! isset($param['job_name'])) {
    		abort(400, '400 Invalid mobile/job_name supplied');
    	}
    	$customer_business = controller('customer/CandidateBiz', 'business');//业务对象
    	$receiver = $customer_business->getEmployeeIdByCustomerMobile($param['mobile']);//查询顾问
    	/*市场推广*/
    	$from = 'web';
    	if (isset($param['market']) && $param['market']) {
    		$arr = $this->recognizeMarket($param, $receiver, 'signup');
    		$receiver = $arr['receiver'];
    		$from = $arr['from'];
    	}
    	/*插入呼入客户报名申请*/
    	$callin_biz = CallinFactory::instance('signup', $from);
    	$callin_biz->save($receiver, $param['mobile']);
    	if ($receiver){
    		$biz = controller('admin/MessageBiz', 'business');
    		if ($from == '') {
    			$msg = lang('notify_signup_tip',[$param['mobile'], $param['job_name']]);
    		} else {
    			$msg = lang('notify_signup_' . $from, [$param['mobile'], $param['job_name']]);
    		}
    		$biz->sendSystemMessage($receiver, $msg);//发送消息
    		$msg = self::SUCCESS;
    	} else {
    		$msg = self::FAILURE;
    	}
    	return ['msg' => $msg];
    }
    
    /**
     * 识别推广途径
     * @param sting $market 市场推广码
     * @param int $receiver 客户负责顾问
     */
    private function recognizeMarket($param, $receiver, $type) {
    	$market_biz = new Market;
    	$market_biz->decodeCode($param['market']);
    	$from = $market_biz->getType();
    	/*顾问推广*/
    	if ($market_biz->isAdviser()) {
    		if ($receiver && ! $market_biz->isAdviserId($receiver)) {
    			/*不是顾问本人的推广，则客户有冲突，发送系统消息*/
    			$employee_biz = controller('admin/EmployeeBiz', 'business');
    			$adviser = $employee_biz->getOrganization($receiver);//获取顾问信息
    			$msg_biz = controller('admin/MessageBiz', 'business');
    			$msg_biz->sendSystemMessage(
    				$market_biz->getId(),
    				lang(
    					'notify_' . $type . '_conflict_' . $market_biz->getType(),
    					[$param['mobile'], $type=='register'?$param['real_name']:$param['job_name'], $adviser['org']['org_name'], $adviser['employee']['real_name']]
    				)
    			);//发送消息
    		} elseif (! $receiver) {
    			/*此客户没有负责的顾问*/
    			$receiver = $market_biz->getId();
    			$from = $market_biz->getType();
    		}
    	}
    	return ['from' => $from, 'receiver' => $receiver];
    }
}