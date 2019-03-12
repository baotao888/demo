<?php
function is_url($s)  {
	return preg_match('/^(http[s]?:)?\/\/'.
			'(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184
			'|'. // 允许IP和DOMAIN（域名）
			'([0-9a-z_!~*\'()-]+\.)*'. // 域名- www.
			'([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域名
			'[a-z]{2,10})'.  // first level domain- .com or .museum
			'(:[0-9]{1,4})?'.  // 端口- :80
			'((\/\?)|'.  // a slash isn't required if there is no file name
			'(\/[0-9a-zA-Z_!~\'\(\)\[\]\.;\?:@&=\+\$,%#-\/^\*\|]*)?)$/',
			$s) == 1;
}
/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $dot
 */
function str_cut($string, $length, $dot = '...', $charset = 'utf-8') {
	$strlen = strlen($string);
	if($strlen <= $length) return $string;
	$string = str_replace(array(' ','&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵',' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
	$strcut = '';
	if($charset == 'utf-8') {
		$length = intval($length-strlen($dot)-$length/3);
		$n = $tn = $noc = 0;
		while($n < strlen($string)) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}
			if($noc >= $length) {
				break;
			}
		}
		if($noc > $length) {
			$n -= $tn;
		}
		$strcut = substr($string, 0, $n);
		$strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
	} else {
		$dotlen = strlen($dot);
		$maxi = $length - $dotlen - 1;
		$current_str = '';
		$search_arr = array('&',' ', '"', "'", '“', '”', '—', '<', '>', '·', '…','∵');
		$replace_arr = array('&amp;','&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;',' ');
		$search_flip = array_flip($search_arr);
		for ($i = 0; $i < $maxi; $i++) {
			$current_str = ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
			if (in_array($current_str, $search_arr)) {
				$key = $search_flip[$current_str];
				$current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
			}
			$strcut .= $current_str;
		}
	}
	return $strcut.$dot;
}
/**
 * 安全过滤函数
 *
 * @param $string
 * @return string
 */
function punctuation_replace($string, $replace=' ') {
	$string = str_replace('%20',$replace,$string);
	$string = str_replace('%27',$replace,$string);
	$string = str_replace('%2527',$replace,$string);
	$string = str_replace('*',$replace,$string);
	$string = str_replace('"',$replace,$string);
	$string = str_replace("'",$replace,$string);
	$string = str_replace(',',$replace,$string);
	$string = str_replace(';',$replace,$string);
	$string = str_replace('<',$replace,$string);
	$string = str_replace('>',$replace,$string);
	$string = str_replace("{",$replace,$string);
	$string = str_replace('}',$replace,$string);
	$string = str_replace('\\',$replace,$string);
	$string = str_replace('.',$replace,$string);
	$string = str_replace(' ',$replace,$string);
	$string = str_replace('(',$replace,$string);
	$string = str_replace(')',$replace,$string);
	$string = str_replace('-',$replace,$string);
	$string = str_replace('$',$replace,$string);
	$string = str_replace('#',$replace,$string);
	$string = str_replace('!',$replace,$string);
	$string = str_replace('?',$replace,$string);
	$string = str_replace('@',$replace,$string);
	$string = str_replace('&',$replace,$string);
	$string = str_replace('%',$replace,$string);
	$string = str_replace('|',$replace,$string);
	return $string;
}

/**
 * Ascii转拼音
 * @param $asc
 * @param $pyarr
 */
function asc_to_pinyin($asc,&$pyarr) {
	if($asc < 128)return chr($asc);
	elseif(isset($pyarr[$asc]))return $pyarr[$asc];
	else {
		foreach($pyarr as $id => $p) {
			if($id >= $asc)return $p;
		}
	}
}

/**
 * gbk转拼音
 * @param $txt
 */
function gbk_to_pinyin($txt, $charset = 'utf-8') {
	if($charset != 'gbk') {
		$txt = iconv($charset,'GBK',$txt);
	}
	$l = strlen($txt);
	$i = 0;
	$pyarr = array();
	$py = array();
	$filename = ROOT_PATH . '/extend/dictionary/gb-pinyin.table';
	$fp = fopen($filename,'r');
	while(!feof($fp)) {
		$p = explode("-",fgets($fp,32));
		$pyarr[intval($p[1])] = trim($p[0]);
	}
	fclose($fp);
	ksort($pyarr);
	while($i<$l) {
		$tmp = ord($txt[$i]);
		if($tmp>=128) {
			$asc = abs($tmp*256+ord($txt[$i+1])-65536);
			$i = $i+1;
		} else $asc = $tmp;
		$py[] = asc_to_pinyin($asc,$pyarr);
		$i++;
	}
	return $py;
}

/**
 * 是否为手机
 * @param string $mobile
 * @return boolean
 */
function is_mobile($mobile){
	return strlen($mobile) == 11 && preg_match("/^1[3|4|5|7|8][0-9]{9}$/", $mobile);
}

function day_end_time($day){
	return $day . ' 23:59:59';
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
