<?php

namespace lib\core;

use lib\core;
use lib\core\Log;

/**
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 14:08:18
 */
abstract class Filter {

	/**
	 * 应用程序上下文对象
	 * @var Application
	 */
	private $applicationContext;
	
	/**
	 * 日志对象
	 * @var Log 
	 */
	protected $log = NULL;

	/**
	 * 构造方法
	 * @param Application $appContext 应用程序上下文对象
	 */
	public function __construct($appContext) {
		$this->applicationContext = $appContext;
		$this->log = $appContext->getLog();
	}
	
	/**
	 * 取得应用程序上下文
	 * @return Application
	 */
	public function getContext() {
		return $this->applicationContext;
	}

	/**
	 * 执行过滤器
	 * 
	 * @param FilterChain $filterChain 过滤器链对象
	 */
	abstract public function doFilter($filterChain);

}

/* EOF */