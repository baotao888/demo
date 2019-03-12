<?php
/**
 * 生成随机字符串
 * @param number $length
 * @return string
 */
function rand_string($length = 8, $type = false){
	// 密码字符集，可任意添加你需要的字符
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
	$chars_1 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	if ($type) $chars = $chars_1;
	$password = '';
	for ( $i = 0; $i < $length; $i++ ) {
		// 这里提供两种字符获取方式
		// 第一种是使用 substr 截取$chars中的任意一位字符；
		// 第二种是取字符数组 $chars 的任意元素
		// $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}

	return $password;
}

/**
 * 加密解密字符串
 * @param string $string
 * @param string $core
 * @param string $key
 * @param number $expiry
 * @param string $operation
 * @return string
 */
function encrypt_string($string, $core, $operation = 'ENCODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : $core);
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

/**
 * 是否为微信浏览器
 * @return boolean
 */
function is_weixin(){
	$flag = false;
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
		$flag = true;
	}
	return $flag;
}

function is_mobile($mobile){
	return strlen($mobile) == 11 && preg_match("/^1[3-9][0-9]{9}$/", $mobile);
}

function is_email($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

/**
 * 加密解密url参数
 * @param string $param
 * @param string $type(ENCODE|DECODE)
 * @return string
 */
function encrypt_param($param, $type = 'ENCODE'){
	$str = '';
	for ( $i = 0; $i < strlen($param); $i++ ) {
		$word = $param[$i];
		if ($type == 'DECODE') {
			$word = chr(ord($word)+1);
		}else{
			$word = chr(ord($word)-1);
		}
		$str .= $word;
	}
	if ($type == 'DECODE') $str = substr($str, 3, 2) . substr($str, 8, 2) . substr($str, 13);
	else $str = rand_string(3, 6) . substr($str, 0, 2) . rand_string(3, 9) . substr($str, 2, 2) . rand_string(3, 12) . substr($str, 4);
	return $str;
}

/**
 * 获取当前页面的url
 * @return string
 */
function cur_page_url()
{
	$pageURL = 'http';

	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
	{
		$pageURL .= "s";
	}
	$pageURL .= "://";

	$this_page = $_SERVER["REQUEST_URI"];
	 
	// 只取 ? 前面的内容
	if (strpos($this_page, "?") !== false)
	{
		$this_pages = explode("?", $this_page);
		$this_page = reset($this_pages);
	}

	if ($_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $this_page;
	} else {
		$pageURL .= $_SERVER["HTTP_HOST"] . $this_page;
	}
	$str_query = '';
	if (isset($_SERVER['argv']))
	{
		$str_query = $_SERVER['argv'][0];
	} elseif ($_SERVER['QUERY_STRING']) {
		$str_query = $_SERVER['QUERY_STRING'];
	}
	/*去除nginx rewrite规则参数*/
	if ($str_query) {
		$str_query = preg_replace("/s=([^\&]*)/", '', $str_query);
		if ($str_query!='') $pageURL .= '?' . (substr($str_query,0,1)=='&'?substr($str_query,1):$str_query);
	}
	return $pageURL;
}

/**
 * GET 请求
 * @param string $url
 */
function http_get($url){
	$oCurl = curl_init();
	if(stripos($url,"https://")!==FALSE){
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
	}
	curl_setopt($oCurl, CURLOPT_URL, $url);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
	$sContent = curl_exec($oCurl);
	$aStatus = curl_getinfo($oCurl);
	curl_close($oCurl);
	if(intval($aStatus["http_code"])==200){
		return $sContent;
	}else{
		return false;
	}
}

/**
 * POST 请求
 * @param string $url
 * @param array $param
 * @param boolean $post_file 是否文件上传
 * @return string content
 */
function http_post($url, $param, $post_file=false) {
	$oCurl = curl_init();
	if(stripos($url,"https://")!==FALSE){
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
	}
	if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
		$is_curlFile = true;
	} else {
		$is_curlFile = false;
		if (defined('CURLOPT_SAFE_UPLOAD')) {
			curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
		}
	}
	if (is_string($param)) {
		$strPOST = $param;
	} elseif ($post_file) {
		if ($is_curlFile) {
			foreach ($param as $key => $val) {
				if (substr($val, 0, 1) == '@') {
					$param[$key] = new \CURLFile(realpath(substr($val,1)));
				}
			}
		}
		$strPOST = $param;
	} else {
		$aPOST = array();
		foreach ($param as $key=>$val) {
			$aPOST[] = $key . "=" . urlencode($val);
		}
		$strPOST =  join("&", $aPOST);
	}
	curl_setopt($oCurl, CURLOPT_URL, $url);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($oCurl, CURLOPT_POST,true);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
	$sContent = curl_exec($oCurl);
	$aStatus = curl_getinfo($oCurl);
	curl_close($oCurl);
	if (intval($aStatus["http_code"]) == 200) {
		return $sContent;
	} else {
		return false;
	}
}