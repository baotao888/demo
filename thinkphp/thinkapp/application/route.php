<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    'uc/index/api$' => 'uc/index/index',//ucenter通信路由
    // 定义资源路由
    '__rest__' => [
    	'activity' => 'activity/index',
    	'city' => 'job/city',	
    	'friend' => 'friend/index',
    	'friendgroup' => 'friend/group',
        'feedback' => 'index/FeedBack',
    	'job' => 'job/index',
    	'jobcategory' => 'job/category',	
    	'jobtag' => 'job/tag',	
    	'location' => 'location/index',    	
    	'message' => 'message/index',
    	'myinvite' => 'user/MyInvite',
    	'myjob' => 'user/MyJob',
    	'myquestion' => 'user/myquestion',
    	'mysubscriber' => 'user/MySubscriber',
        'myallowance' => 'user/MyAllowance',
    	'poster' => 'index/poster',
    	'recommendedjob' => 'job/recommend',
    	'setting' => 'user/setting',
    	'sms' => 'sms/index',
    	'token'	=> 'token/index',
    	'user' => 'user/index',
    	'version' => 'index/version',
    ],
];
