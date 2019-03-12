<?php
namespace app\customer\business;

use ylcore\Biz;
use think\Config;
use think\Log;
use dictionary\Segment;

class TagBiz extends Biz
{
	/**
	 * 处理关键字
	 */
	function resultKeyword($keyword){
		$key = $keyword;
		//特殊字符替换成分隔符
		$tmp = punctuation_replace($keyword,',');
		//关键字分隔成数组
		$keyword = array_unique(array_filter(explode(',',$tmp)));
		if( $keyword ){
			$arr_tag = array();//搜索标签
			$arr_all_segment = array();//分词数组
			foreach($keyword as $q){
				//英文或者数字
				if( preg_match('/^[\d\w]+$/',$q) ){
					$arr_tag[] = array('k'=>$q,'p'=>'');
				}else{
					$arr_q = $this->do_segment($q);//分词
					if($arr_q){
						foreach($arr_q as $sq){
							if( in_array($sq, $arr_all_segment) ) continue;
							$p = $this->to_pinyin($sq);
							$arr_tag[] = array('k'=>$sq,'p'=>$p);
						}
						$arr_all_segment = array_unique(array_merge($arr_all_segment,$arr_q));
					}else{
						$arr_tag[] = array('k'=>$q,'p'=>'');
					}
				}
			}
			//重置keyword
			if( $arr_tag ){
				$keyword = array();
				foreach($arr_tag as $tag){
					$keyword[] = $tag['k'];
				}
			}
			sort($keyword);
		}
		if( current($keyword)=='' ){
			$keyword = array();
			$keyword[] = substr($key,0,6);
		}
	
		return $keyword;
	}
	
	/**
	 * 分词
	 */
	function do_segment($q){
		$segment = new segment();
		$segment_q = $segment->get_keyword($segment->split_result($q));
		return array_unique(explode(' ', $segment_q));
	}
	
	/**
	 * 转拼音
	 * @param $txt
	 */
	function to_pinyin($txt) {
		$pinyin = gbk_to_pinyin($txt);
		if(is_array($pinyin)) {
			$pinyin = implode('', $pinyin);
		}
		return $pinyin;
	}

}