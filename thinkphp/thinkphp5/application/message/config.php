<?php
return [
	'aliyun' => [
		'access_key_id' => 'LTAI4SE6EBs5H02a',
		'access_key_secret' => 'kwvbA39lUOKtSooljnlVz5fGtqvjOS'
	],
	'aliyun_sms' => [
		'signature' => '永乐打工网',
		'login_tpl_id' => 'SMS_85705008',
		'register_tpl_id' => 'SMS_85695012',
		'change_mobile_tpl_id' => 'SMS_84735323',
	],
	'sms_condition' => [
		'min_seconds' => 30, // 最小时间间隔
		'max_day_count' => 10 // 每天最大发送数
	],
	'enable_sms_debug' => false // 是否开启sms调试模式
		
];