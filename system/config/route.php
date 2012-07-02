<?php
/**
 * 路由配置文件 
 */
return array(
	// 取得访问路径的方式
	// query - 使用查询字符串参数p指定
	// pathinfo - 使用pathinfo的内容
	// rewrite - 使用URL重写方式
	'mode' => 'query',
	'default_action' => 'Home',
);