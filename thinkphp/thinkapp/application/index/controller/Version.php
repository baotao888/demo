<?php
namespace app\index\controller;

use think\Config;

class Version
{
	/**
	 * 获取最新版本
	 * @return multitype:string
	 */
	public function index()
	{
		return Config::get('app_version');
	}
}