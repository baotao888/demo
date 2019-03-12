<?php
//[永乐打工网视图类]

namespace ylcore;

use think\View;

class WebView extends View
{
	public $tpl_path = '../templates/';//模板路径
	public $skin = 'default';//皮肤
	public $ext = '.html';//模板后缀
	
	/**
	 * 
	 * @override
	 */
	public function fetch($template = '', $vars = [], $replace = [], $config = [], $renderContent = false)
	{
		$file = $this->tpl_path . $this->skin . '/' . $template . $this->ext;
		if(! file_exists($file)){
			$file  = $this->tpl_path . 'default/' . $template . $this->ext;
		}
		return parent::fetch($file, $vars, $replace, $config, $renderContent);
	}

	public function setSkin($skin){
		$this->skin = $skin;
	}
	
	public function setTplPath($path){
		$this->tpl_path = $path;
	}
	
	public function setExt($ext){
		$this->ext = $ext;
	}
	
	private function getTplPath($tpl){
		$path = $this->tpl_path . $this->skin . '/' . $tpl . $this->ext;
		if(! file_exists($path)){
			$path  = $this->tpl_path . 'default/' . $tpl . $this->ext;
		} else {
			//echo $path . '<br/>';//[test]
		}
		return $path;
	}
	
	public function getBaseTpl(){
		return $this->getTplPath('base');
	}
	
	public function getHeaderTpl(){
		return $this->getTplPath('header');
	}
	
	public function getFooterTpl(){
		return $this->getTplPath('footer');
	}
	
	public function getBannerTpl(){
		return $this->getTplPath('banner1');
	}
	
	public function getNavigationTpl(){
		return $this->getTplPath('navigation');
	}
	
	public function getContentTpl(){
		return $this->getTplPath('content');
	}
	
	public function getPopupTpl(){
		return $this->getTplPath('token');
	}
	
	public function getSignupModalTpl(){
		return $this->getTplPath('signup-modal');
	}
	
	public function assignTpl($var, $tpl){
		$this->assign($var, $this->getTplPath($tpl));
	}
	
	public function assignBaseTpl(){
		$this->assign('base', $this->getBaseTpl());
		$this->assign('header', $this->getHeaderTpl());
		$this->assign('footer', $this->getFooterTpl());
		$this->assign('banner', $this->getBannerTpl());
		$this->assign('navigation', $this->getNavigationTpl());
		$this->assign('content', $this->getContentTpl());
		$this->assign('popup', $this->getPopupTpl());
		$this->assign('signupmodal', $this->getSignupModalTpl());
		$this->assign('current_nav', 'index');
	}
	
	/**
	 * 信息提示
	 * @param string $msg 提示信息
	 * @param string $backward 回调地址
	 */
	public function message($msg, $backward = ''){
		$this->assignTpl('base', 'base-dialog');
		$this->assignTpl('content', 'content-dialog');
		$this->assign('msg' , $msg);
		$this->assign('backward', $backward);
		return $this->fetch('message');
	}
	

}