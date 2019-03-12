<?php
namespace app\uc\controller;

use app\uc\service\Note;

import('uc_client.lib.xml', EXTEND_PATH, '.class.php');//center类库
import('uc.config_uc', APP_PATH);//ucenter配置文件
import('user.config_uc', APP_PATH);//ucenter配置文件

class Index
{
	public function index()
	{
		//note 普通的 http 通知方式
		if(!defined('IN_UC')) {		
			$_DCACHE = $get = $post = array();
			$code = @$_GET['code'];
			parse_str($this->authCode($code, 'DECODE', UC_KEY), $get);
		
			$timestamp = time();
			if($timestamp - $get['time'] > 3600) {
				exit('Authracation has expiried');
			}
			if(empty($get)) {
				exit('Invalid Request');
			}
			$action = $get['action'];
			
			$input = file_get_contents('php://input');
			$post = $this->xml_unserialize($input);
		
			if(in_array($action, array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
				$uc_note = new Note();
				exit($uc_note->$action($get, $post));
			} else {
				exit(API_RETURN_FAILED);
			}
		
			//note include 通知方式
		}
	}
	
	public function api() {
		$this->index();
	}
	
	private function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;
	
		$key = md5($key ? $key : UC_KEY);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
	
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
	
		$result = '';
		$box = range(0, 255);
	
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
					return '';
				}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	
	}
	
	private function xml_unserialize(&$xml, $isnormal = FALSE) {
		$xml_parser = new \XML($isnormal);
		$data = $xml_parser->parse($xml);
		$xml_parser->destruct();
		return $data;
	}
	
	private function xml_serialize($arr, $htmlon = FALSE, $isnormal = FALSE, $level = 1) {
		$s = $level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : '';
		$space = str_repeat("\t", $level);
		foreach($arr as $k => $v) {
			if(!is_array($v)) {
				$s .= $space."<item id=\"$k\">".($htmlon ? '<![CDATA[' : '').$v.($htmlon ? ']]>' : '')."</item>\r\n";
			} else {
				$s .= $space."<item id=\"$k\">\r\n".xml_serialize($v, $htmlon, $isnormal, $level + 1).$space."</item>\r\n";
			}
		}
		$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
		return $level == 1 ? $s."</root>" : $s;
	}
}