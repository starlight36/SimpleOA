<?php
namespace lib\core;

/**
 * Action基类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:23
 */
abstract class Action {

	/**
	 * 应用程序上下文对象
	 * @var Application
	 */
	private $applicationContext = NULL;
	
	/**
	 * 日志对象
	 * @var Log 
	 */
	private $log = NULL;

	/**
	 * 配置对象
	 * @var Config
	 */
	private $config = NULL;

	/**
	 * 请求对象
	 * @var Request
	 */
	private $request = NULL;

	/**
	 * 请求对象
	 * @var Response
	 */
	private $response = NULL;

	/**
	 * 请求会话对象
	 * @var Session
	 */
	private $session = NULL;

	/**
	 * 视图对象
	 * @var View
	 */
	private $view = NULL;

	/**
	 * 构造方法 
	 */
	public function __construct() {
		$this->applicationContext = Application::getInstance();
		$this->log = $this->applicationContext->getLog();
		$this->config = $this->applicationContext->getConfig();
		$this->request = $this->applicationContext->getRequest();
		$this->response = $this->applicationContext->getResponse();
		$this->session = $this->applicationContext->getSession();
		$this->view = $this->applicationContext->getView();
	}

	/**
	 * 取得应用程序上下文
	 * @return Application
	 */
	protected function getContext() {
		return $this->applicationContext;
	}

	/**
	 * 取得配置对象
	 * @return Config 
	 */
	protected function getConfig() {
		return $this->config;
	}
	
	/**
	 * 取得日志对象
	 * @return Log 
	 */
	protected function getLog() {
		return $this->log;
	}

	/**
	 * 取得请求对象
	 * @return Request
	 */
	protected function getRequest() {
		return $this->request;
	}

	/**
	 * 取得应答对象
	 * @return Response
	 */
	protected function getResponse() {
		return $this->response;
	}

	/**
	 * 取得Session对象
	 * @return Session
	 */
	protected function getSession() {
		return $this->session;
	}

	/**
	 * 取得视图对象
	 * @return View
	 */
	protected function getView() {
		return $this->view;
	}

	/**
	 * 取得表单变量, 对Request::getForm的快捷封装.
	 * 
	 * @param name    表单变量名称
	 * @param default    默认值
	 */
	public function getForm($name = NULL, $default = NULL) {
		return $this->getRequest()->getForm($name, $default);
	}

	/**
	 * 取得查询变量值, 对Request::getQuery的快捷封装
	 * 
	 * @param string    变量名称
	 * @param default    默认值
	 */
	protected function getQuery($name = NULL, $default = NULL) {
		return $this->getRequest()->getQuery($name, $default);
	}

	/**
	 * 渲染一个视图, 对View::render方法的快捷封装
	 * 
	 * @param name    视图名称
	 */
	protected function render($name, $data = NULL) {
		return $this->getView()->render($name, $data);
	}

	/**
	 * 分配视图变量, 对View::assign方法的快捷封装
	 * 
	 * @param name    视图变量名称
	 * @param value    视图变量值
	 */
	protected function assign($name, $value = NULL) {
		return $this->getView()->assign($name, $value);
	}

}

/* EOF */