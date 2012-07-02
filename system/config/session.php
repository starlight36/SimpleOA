<?php
/**
 * Session配置文件
 */
return array(
	// 当前使用的Session处理器
	'handler' => 'default',
	'save_path' => SYS_PATH.'temp/session/',
	
	// 基于文件的Session处理器
	'file_handler' => array(
		'class' => '\\lib\\core\\session\\FileSessionHandler',
		'config' => array(
			'save_path' => SYS_PATH.'temp/session/',
		)
	),
);
/* EOF */