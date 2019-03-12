<?php
// [ 应用业务层核心类 ]
namespace ylcore;

use think\Collection;

class Biz
{
    protected $domainObjectFields = [];
    protected $orders = [];
	
    /**
     * 数据转换成传输对象
     */
    public function dto($data) {
    	$obj = [];
    	foreach ($data as $field=>$value) {
    	    if (in_array($field, $this->domainObjectFields)) {
    	        $obj[$field] = $value;
    	    }
    	}
    	return $obj;
    }
    
    /**
     * 数据传输
     */
    public function transfer($list) {
    	$return = [];
    	if ($list) {
    		foreach ($list as $item) {
    			$item = $this->formatViewField($item);
    			$return[] = $this->dto($item);
    		}
    	}
    	return $return;
    }
    
    /**
     * 对象转换成数组
     */
    public function o2a($objects) {
    	$format = new collection($objects);
    	$return = $format->toArray();
    	return $return;
    }
    
    /**
     * 格式化视图字段
     */
    public function formatViewField($item) {
    	return $item;
    }
    
    /**
     * 排序字段
     * @param string $order 排序
     * @param string $deforder 默认排序
     * @param string $by
     * @return string
     */
    public function selectOrderBy($order, $default, $by) {
    	if (! $order || ! in_array($order, $this->orders)) $order = $default;
    	if (! $by || ! in_array($by, ['desc', 'asc'])) $by = 'desc';
    	return "$order $by";
    }
}