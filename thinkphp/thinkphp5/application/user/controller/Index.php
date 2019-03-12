<?php
namespace app\user\controller;

use ylcore\WebView;
use think\Request;
use think\Session;
use think\Cookie;
use think\Log;
use think\Config;

class Index extends Client
{
    /**
     * 登录
     */
    public function login(){
        if ($this->checkAuth()){
            /*已登录，提示信息*/
            $view = new WebView();
            return $view->message(lang('already_login'), $this->getBackward()); 
        }
        if (Request::instance()->isAjax()){
            return $this->doLogin();
        } elseif (Request::instance()->isPost()){
            $view = new WebView();
            $message = $this->doLogin();
            return $view->message($message, $this->getBackward());
        } else {
            return $this->toLogin();
        }
    }
    
    /**
     * 登录页面
     */
    private function toLogin(){
        $view = new WebView();
        $view->assignTpl('base', 'base-dialog');
        $view->assignTpl('content', 'token');
        return $view->fetch('login');
    }
    
    /**
     * 登录操作
     */
    private function doLogin(){
        /*参数验证*/
        $mobile = request()->param('mobile');
        $password = request()->param('password');
        $business = controller('user/user', 'business');//定义业务对象
        if (is_mobile($mobile)==false){
            return lang('mobile_format_error');
        } elseif ($business->isBadPassword($password)){
            return lang('login_error');
        }
        $user = $business->mobileLogin($mobile, $password);
        if ($user['uid']<=0){
            return lang('login_error');
        } else {
            $cookie = (request()->param('auto_login')!='')?true:false;
            $this->setAuth($user, $cookie, $password);
            return lang('login_success');
        }
    }
    
    public function register(){
        if ($this->checkAuth()){
            $view = new WebView();
            $backward = $this->getBackward();
            return $view->message(lang('already_login'), strpos($backward, 'register')?'/':$backward);
        }
        if (Request::instance()->isAjax()){
            return $this->doRegister();
        } elseif (Request::instance()->isPost()){
            $view = new WebView();
            $message = $this->doRegister();
            return $view->message($message, $this->getBackward());
        } else {
            return $this->toRegister();
        }
    }
    
    /**
     * 注册操作
     * 自动注册时密码无需验证
     * @param boolean $auto 是否自动注册
     */
    private function doRegister($auto = false){
        /*参数验证*/
        $mobile = request()->param('mobile');
        $real_name = request()->param('realname');
        $password = request()->param('password');
        $business = controller('user/user', 'business');//定义业务对象
        if (is_mobile($mobile)==false){
            return lang('mobile_format_error');
        } elseif (! preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $real_name)){
            return lang('realname_format_error');
        } elseif ($auto == false && $business->isBadPassword($password)){
            return lang('password_format_error');
        } elseif (Config::get('enable_register_sms_code')){
        	$code = request()->param('sms_code');
        	/*注册验证码*/
        	$biz = controller('message/sms', 'business');
        	$status = $biz->checkRegisterCode($mobile, $code);
        	if ($status == false) return lang('sms_code_error');
        	else $biz->finishRegisterCode($mobile, $code);//完成验证
        }
        //注册用户
        $user = $business->mobileRegister($mobile, $password, array('realname' => $real_name));
        if ($user['uid']<=0) return lang('register_failure');
        $user['real_name'] = $real_name;
        //设置客户端缓存
        $this->setAuth($user);
        return lang('register_success');
    }
    
    /**
     * 注册页面
     */
    private function toRegister(){
        $view = new WebView();
        $view->setSkin('default1');
        $view->assignTpl('base', 'base-dialog');
        $view->assignTpl('content', 'token');
        $view->assign('t_type', 'register');
        return $view->fetch('register');
    }
    
    /**
     * 权限验证
     */
    public function auth(){
        $flag = '';       
        if ($this->checkAuth()){
            $flag = Session::get('user');
            //Log::record($flag);            
        }
        return $flag;
    }
    
    /**
     * 退出登录
     */
    public function logout(){
        Session::delete('user');
        Cookie::delete('yl_auth');
        $view = new WebView();
        return $view->message(lang('logout_success'), $this->getBackward());
    }
    
    /**
     * 职位报名
     */
    public function signup(){
        if (Request::instance()->isAjax()){
            return $this->doSignup();
        } elseif (Request::instance()->isPost()){
            $view = new WebView();
            $message = $this->doSignup();
            return $view->message($message, $this->getBackward());
        } else {
            return $this->toSignup();
        }
    }
    
    /**
     * 报名
     */
    private function doSignup(){
        if ($this->checkAuth()==false){
            /*未登录情况*/
            /*1,先注册*/
            $message = $this->doRegister(true);
            /*2,注册之后再次验证权限，确认注册成功*/
            if ($this->checkAuth()==false){
                return $message;
            }	
        }
        /*报名*/
        $birthday = request()->param('birthday');
        $gender = request()->param('gender');
        $job_id = request()->param('job_id');
        $business = controller('user/user', 'business');//定义业务对象
        $business->signup(Session::get('user.uid'), array('job_id'=>$job_id, 'birthday'=>$birthday, 'gender'=>$gender));
        return lang('signup_success');
    }
    
    /**
     * 显示报名
     */
    private function toSignup(){
        $view = new WebView();
        $view->assignTpl('base', 'base-dialog');
        $view->assignTpl('content', 'signup-modal');
        return $view->fetch('signup');
    }
    
    /**
     * 报名验证
     */
    public function checkSignup(){
        $job_id = request()->param('job_id');
        $business = controller('user/user', 'business');//定义业务对象
        $status = $business->checkSignup(Session::get('user.uid'), $job_id)?1:0;
        return [$status];
    }
    
    /**
     * 推荐榜列表
     */
    public function inviteList(){
        $res = controller('user/user','business');
        $invite = $res->invite(4);
        return json($invite);
    }
    /**
     * 推荐流程
     */
    public function invite(){
        $view = new WebView();
        if ($this->checkAuth()==false){
        	return $view->message(lang('invite_failure'), $this->getBackward());
        }
        $name = Request::instance()->post('realname');
        $mobile = Request::instance()->post('mobile');
        $uid = Session::get('user.uid');
        $username = Session::get('user.real_name');
        $res = controller('user/user','business');
        $invite = $res->addInvite($uid,$username,$mobile,$name);
        return $view->message(lang('invite_success'), $this->getBackward());
    }
}