<?php
/**
 * PHTML增强函数库 
 */
use lib\util\Url;
/**
 * 返回指定的请求的URL形式
 * @param string $action 要请求的Action, 不含Action后缀的Action名称
 * @param string $method 要请求的方法, 不含Execute后缀的方法名称
 * @param mixed $query 查询字符串, 可以为数组或者字符串
 * @return string 
 */
function get_url($action, $method = NULL, $query = NULL) {
	return Url::get($action, $method, $query);
}

/**
 * 返回友好的时间格式, 多长时间之前
 * @param mixed $date 时间戳或者合法的时间格式
 * @return string 
 */
function relatively_date($date) {
	if (!preg_match('/^\d+$/', $date)) $date = strtotime(trim($date));
	$sec = time() - $date;
	switch(true){
		case $sec < 3600:
			return round($sec/60). '分钟前';
		case $sec < 86400:
			return round($sec/3600). '小时前';
		case $sec < (86400 * 7):
			return round($sec/86400). '天前';//days ago
		case $sec < (86400 * 7 * 4):
			return round($sec/(86400*7)). '周前'; //weeks ago
		default:
			return longDate($date);
	}
}
/* EOF */