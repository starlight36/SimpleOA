<?php
/**
 * 过滤器配置文件 
 */
return array(
	'#^((?!/home/login|/home/doLogin).)*$#i' => array(
		'filter\\LoginFilter',
		'filter\\AclFilter'
	),
);