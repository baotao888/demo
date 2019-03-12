<?php
// [用户个性化设置]
return [
	'user_setting' => [
		//菜单	
		'menu' => ['team', 'control', 'web', 'recruit'],
		//客户池：分配，导入	
		'customer_btn' => ['distribute', 'import', 'delete'],
		//候选人：返费，保留，划转，导入
		'candidate_btn' => ['remain', 'move', 'import', 'award'],
		//消息：群发
		'message_btn' => ['mass'],
		//搜索：顾问，组织
		'search_btn' => ['adviser', 'organization'],
		//端口用户：导出
		'user_btn' => ['export'],
		//业绩管理
		'performance_btn' => ['delete', 'restore'],
	]	
];