<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;

class Organization
{
	public function tree()
	{
		$business = controller('admin/OrganizationBiz','business');
		$res = $business->tree();
		
		return $res;
	}
}