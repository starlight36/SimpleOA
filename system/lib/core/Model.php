<?php

namespace lib\core;

use lib\core;
use lib\db\DBFactory;

/**
 * 模型类基类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:24
 */
class Model {

	/**
	 * 日志类对象
	 * @return Log
	 */
	private $log = NULL;

	/**
	 * 构造方法 
	 */
	public function __construct() {
		$appContext = Application::getInstance();
		$this->log = $appContext->getLog();
		$this->init();
	}
	
	/**
	 * 用于初始化的方法 
	 */
	public function init() {
		
	}

	/**
	 * 取得日志对象
	 * @return Log
	 */
	protected function getLog() {
		return $this->log;
	}

}

/* EOF */