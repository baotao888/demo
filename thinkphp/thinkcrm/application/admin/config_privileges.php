<?php
//[角色权限配置]
return [
	/*功能权限*/	
	'role_privileges' => [
		//系统控制
		'admin' => [
			//员工
			'employee' => ['index', 'save', 'update', 'read'],
			//后台用户
			'index' => ['index', 'save', 'update', 'read'],
			//组织架构
			'organization' => ['index', 'save', 'update', 'read'],				
			//职位
			'position' => ['index', 'save', 'update', 'read'],
			//权限
			'role' => ['index', 'save', 'update', 'read'],
			//设置
			'setting' => ['privileges', 'personal', 'admin', 'updatesystem', 'updatestatistics', 'statistics'],
		],
		//文章资讯
		'article' => [
			'article' => ['index', 'read', 'update', 'save'],
		],			
		//客户
		'customer' => [
			//候选人
			'candidate' => ['index', 'intention', 'signup', 'meet', 'onduty', 'outduty' , 'depose', 'resignup', 'myintention', 'mysignup', 'mymeet', 'myonduty', 'myoutduty', 'myother', 'detail', 'tag', 'tags', 'addtag', 'deletetag', 'danger', 'back', 'top', 'canceltop', 'updatesignup', 'remain', 'cancelremain', 'remainall', 'move', 'updateaward'],
			//联系记录
			'contact' => ['index', 'save', 'today', 'addtask', 'my', 'getsetting', 'search'],
			//客户池
			'pool' => ['index', 'my', 'detail', 'history', 'signned', 'unsignned', 'assignhistory', 'workhistory', 'save', 'update', 'check', 'recognize', 'distribute', 'distributepro', 'distributewebuser', 'release', 'delete', 'deletecustomer'],
		],
		//工作面板
		'index' => [
			'home' => ['monthcandidate', 'todaycandidate', 'weekcandidate', 'quartercandidate', 'monthplotstatistics', 'organization', 'announcement'],
			'import' => ['customer', 'candidate'],				
			'organization' => ['tree'],
			'panel' => ['cache', 'deleteadminlog', 'clearquitemployeecandidate', 'deletecandidate', 'croncandidates'],
			'upload' => ['customer', 'candidate'],
			'wechat' => ['updatemenu']		
		],
		//职位
		'job' => [
			//企业
			'enterprise' => ['index', 'read', 'update'],
			//职位
			'job' => ['index', 'read', 'update', 'save'],
			//图片
			'picture' => ['upload'],
			//推荐位
			'recommend' => ['space', 'jobs', 'add', 'listorder', 'details']		
		],
		//广告
		'poster' => [
			'index' => ['index', 'space', 'detail', 'save', 'upload', 'update'],
		],
		//招聘信息
		'recruit' => [
			'index' => ['index', 'save', 'read', 'update', 'delete'],
			'labour' => ['index', 'save', 'read', 'update'],
		],
		//回收站
		'recycle' => [
			'index' => ['customerool', 'restore', 'delete'],
		],
		//订单
		'salesorder' => [
			'index' => ['sure', 'receiveallowance', 'receiverecommend', 'adjusthourprice', 'export', 'goonduty', 'outduty', 'delete', 'recover'],
			'salesorder' => ['index', 'read']
		],	
		//端口用户
		'user' => [
			//呼入用户	
			'callin' => ['confirm', 'users', 'applicants', 'callinunsure', 'assignuser', 'assignapplicant'],
			//端口用户		
			'index' => ['distribute', 'distributesignup', 'index', 'signuplist', 'jobapplies', 'invitelist', 'export', 'exportsignup', 'exportinvite'],
			//用户
			'user' => ['index'],
			//微信
			'wechat' => ['subscribers', 'logs']		
		],
	],
	/*数据权限，数据列*/	
	'role_except_fields' => [
		'user' => ['password']
	],
	'role_access_fields' => [
		
	]	
];