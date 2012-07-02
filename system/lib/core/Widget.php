<?php
namespace lib\core;

/**
 * 部件基类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:22
 */
abstract class Widget {

	/**
	 * 应用程序上下文对象
	 * @var Application
	 */
	protected $applicationContext = NULL;
	
	/**
	 * 配置对象
	 * @var Config 
	 */
	protected $config = NULL;

	/**
	 * 日志对象
	 * @var Log 
	 */
	protected $log = NULL;
	
	/**
	 * 请求对象
	 * @var Request 
	 */
	protected $request = NULL;
	
	/**
	 * 应答对象
	 * @var Response 
	 */
	protected $response = NULL;
	
	/**
	 * 视图对象
	 * @var View 
	 */
	protected $view = NULL;
	
	/**
	 * 会话对象
	 * @var Session 
	 */
	protected $session = NULL;

	/**
	 * 构造方法
	 */
	public function __construct() {
		$this->applicationContext = Application::getInstance();
		$this->config = $this->applicationContext->getConfig();
		$this->log = $this->applicationContext->getLog();
		$this->request = $this->applicationContext->getRequest();
		$this->response = $this->applicationContext->getResponse();
		$this->view = $this->applicationContext->getView();
		$this->session = $this->applicationContext->getSession();
		$this->init();
	}
	
	/**
	 * 部件初始化方法 
	 */
	public function init() {
		
	}
	
}

/* EOF */