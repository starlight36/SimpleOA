<?php
/**
 * 系统目录
 * @var string 
 */
define('SYS_INDEX_PATH', str_replace('\\', '/', dirname(__FILE__).'/'));
define('SYS_PATH', SYS_INDEX_PATH.'system/');

require SYS_PATH.'bootstrap.php';

/* EOF */