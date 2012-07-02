<?php

namespace lib\core;

/**
 * 日志类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:24
 */
class Log {

	/**
	 * 应用程序上下文对象
	 * @var Application
	 */
	private $applicationContext = NULL;
	
	/**
	 * 日志记录级别
	 * @var int 
	 */
	private $logLevel = 0;
	
	/**
	 * 日志路径
	 * @var string 
	 */
	private $logPath = NULL;
	
	/**
	 * 日志格式
	 * @var string 
	 */
	private $logFormat = NULL;
	
	/*
	 * 日志级别 - 调试
	 */
	const DEBUG = 0;
	
	/**
	 * 日志级别 - 信息 
	 */
	const INFO = 1;
	
	/**
	 * 日志级别 - 警告 
	 */
	const WARN = 2;
	
	/**
	 * 日志级别 - 错误 
	 */
	const ERROR = 3;
	
	/**
	 * 日志级别名称
	 * @var array 
	 */
	private $levelTags = array('DEBUG', 'INFO', 'WARN', 'ERROR');

	/**
	 * 构造方法 
	 */
	public function __construct($appContext) {
		$this->applicationContext = $appContext;
		$config = $appContext->getConfig();
		$this->logLevel = $config->get('log.log_level');
		$this->logPath = $config->get('log.log_path');
		$this->logFormat = $config->get('log.log_format');
		
		// 托管PHP错误消息到本类处理
		set_error_handler(function($errno, $errstr, $errfile, $errline){
			$log_msg = "$errno $errstr in file $errfile, line $errline";
			Application::getInstance()->getLog()->error($log_msg);
			header('HTTP/1.1 500 Internal Server Error');
			if(defined('DEBUG') && DEBUG) {
				include 'lib/misc/debug.phtml';
			}
			die();
		}, E_ALL ^ E_NOTICE);
	}

	/**
	 * 记录日志的方法
	 * 
	 * @param level    日志级别
	 * @param string $output 输出内容
	 */
	private function record($level, $output) {
		if($level < $this->logLevel) {
			return;
		}
		$log_msg = str_replace('%level%', $this->levelTags[$level], $this->logFormat);
		$log_msg = str_replace('%time%', date('Y-m-d H:i:s'), $log_msg);
		$log_msg = str_replace('%output%', $output, $log_msg);
		error_log($log_msg."\n", 3, $this->logPath);
	}

	/**
	 * 记录调试级别日志
	 * 
	 * @param string $output 输出内容
	 */
	public function debug($output) {
		$this->record(self::DEBUG, $output);
	}

	/**
	 * 记录信息级别日志
	 * 
	 * @param string $output 输出内容
	 */
	public function info($output) {
		$this->record(self::INFO, $output);
	}

	/**
	 * 记录警告级别日志
	 * 
	 * @param string $output 输出内容
	 */
	public function warn($output) {
		$this->record(self::WARN, $output);
	}

	/**
	 * 记录错误级别日志
	 * @param string $output 输出内容
	 */
	public function error($output) {
		$this->record(self::ERROR, $output);
	}

}

/* EOF */