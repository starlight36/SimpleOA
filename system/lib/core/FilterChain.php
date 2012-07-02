<?php
namespace lib\core;

use lib\core\exception\StopActionException;

/**
 * 过滤器责任链类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:23
 */
class FilterChain {
	
	/**
	 * 应用程序上下文对象
	 * @var Application
	 */
	private $applicationContext = NULL;
	
	/**
	 * Action对象
	 * @var Action 
	 */
	private $action = NULL;
	
	/**
	 * Log对象
	 * @var Log 
	 */
	private $log = NULL;
	
	/**
	 * Action类全限定名
	 * @var string 
	 */
	private $actionName = NULL;
	
	/**
	 * Method名称
	 * @var string 
	 */
	private $methodName = NULL;
	
	/**
	 * 当前过滤器链执行位置
	 * @var int 
	 */
	private $pos = 0;

	/**
	 * 过滤器对象数组
	 * @var array
	 */
	private $filters = array();
	
	/**
	 * 过滤器数量
	 * @var int 
	 */
	private $filterCount = 0;

	/**
	 * 构造方法
	 * @param Application $appContext 配置对象
	 * @param string $action Action路径
	 * @param string $method Method名称
	 */
	public function __construct($appContext) {
		$this->applicationContext = $appContext;
		$this->action = $appContext->getAction();
		$this->log = $appContext->getLog();
		$this->actionName = $appContext->getRequest()->getRequestAction();
		$this->methodName = $appContext->getRequest()->getRequestMethod();
		$this->initFilterChain();
	}
	
	/**
	 * 初始化过滤器链
	 */
	private function initFilterChain() {
		$filterConfig = $this->applicationContext->getConfig()->get('filter');
		$requestPath = '/'.trim($this->applicationContext->getRequest()->getRequestPath(), '/');
		foreach ($filterConfig as $filterPattern => $filterClassList) {
			if(preg_match($filterPattern, $requestPath)) {
				foreach ($filterClassList as $filterClassName) {
					if(class_exists($filterClassName)) {
						$this->filters[] = new $filterClassName($this->applicationContext);
					}
				}
			}
		}
		$this->filterCount = count($this->filters);
	}
	
	/**
	 * 执行Action 
	 */
	private function invokeAction() {
		try {
			// 处理验证器
			$methodValidator = preg_replace('/(\w*)Execute$/i', '$1Validator', $this->methodName);
			if (method_exists($this->action, $methodValidator)) {
				$this->log->info('Process validator: '.$methodValidator);
				call_user_func(array($this->action, $methodValidator));
			}
			//调用方法
			call_user_func(array($this->action, $this->methodName));
		} catch (StopActionException $e) {
			$this->log->info('Action throw StopActionException, ignored.');
		}
	}

	/**
	 * 执行责任链节点
	 */
	public function invoke() {
		if($this->pos == $this->filterCount) {
			// 执行Action
			$this->invokeAction();
		} else {
			try {
				$this->filters[$this->pos++]->doFilter($this);
			} catch (StopActionException $e) {
				$this->log->info('Catch a StopActionException exception from filter, ignored.');
			}
		}
	}

}

/* EOF */