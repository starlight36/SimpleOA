<?php
/**
 * 系统启动器 
 */

/*
 * 设置默认包含文件夹为系统目录
 */
ini_set('include_path', SYS_PATH.';'.ini_get('include_path'));

/**
 * 加载系统定义文件 
 */
require 'config/define.php';

/**
 * 设置错误报告级别 
 */
if(defined('DEBUG') && DEBUG) {
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(E_ERROR);
}

/**
 * 注册自动类加载器
 */
spl_autoload_register(function($class) {
	$class_path = SYS_PATH.str_replace('\\', '/', $class).'.php';
	if(is_file($class_path)) {
		require $class_path;
		return;
	}
});

/**
 * 启动应用程序运行 
 */
lib\core\Application::getInstance()->run();

/* EOF */