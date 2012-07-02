<?php
/**
 * 视图配置文件 
 */
return array(
	'engine' => array(
		'phtml' => array(
			'class' => '\\lib\\view\\phtml\\PHtmlAdapter',
			'config' => array(
				'tpl_path' => SYS_PATH.'view/',
			)
		),
		'json' => array(
			'class' => '\\lib\\view\\json\\JSONAdapter'
		),
	),
	'config' => array(),
);