<?php
return [
	'danger_candidate_expire' => 3600 * 24 * 20,//释放提醒天数[20天]
	'cron_candidate_expire' => 3600 * 24 * 30,//客户人选保留期限[一个月]
	'danger_candidate_pagesize' => 5000,//释放提醒显示总数
	'recognize_open_hour' => 18,//客户池公开认领时间[6点钟]
	'recognize_prior_floor' => '17:30',//客户池优先认领开始时间
	'recognize_open_day' => 1,//客户池公开认领天[次日]
	'personal_depose_self_recognize' => 15,//自己丢弃客户自己认领条件限制[15天]
	'personal_depose_org_recognize' => 7,//自己丢弃客户同组认领条件限制[7天]
	'sys_depose_self_recognize' => 7,//系统丢弃客户自己认领条件限制[7天]
	'sys_depose_org_recognize' => 3,//系统丢弃客户同组认领条件限制[3天]
];