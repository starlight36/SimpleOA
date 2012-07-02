<?php
namespace lib\core;

/**
 * 系统应用类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:21
 */
class Application {

	/**
	 * 本类单例
	 * @var Application
	 */
	private static $instance = NULL;

	/**
	 * 属性变量
	 * @var array
	 */
	private $attribute = array();

	/**
	 * 配置对象
	 * @var Config
	 */
	private $config = NULL;

	/**
	 * 日志类对象
	 * @var Log
	 */
	private $log = NULL;

	/**
	 * 视图对象
	 * @var View
	 */
	private $view = NULL;

	/**
	 * 请求对象
	 * @var Request
	 */
	private $request = NULL;

	/**
	 * 应答对象
	 * @var Response
	 */
	private $response = NULL;

	/**
	 * 请求会话对象
	 * @var Session
	 */
	private $session = NULL;
	
	/**
	 * Action对象
	 * @var Action 
	 */
	private $action = NULL;
	
	/**
	 * 过滤器链对象
	 * @var FilterChain 
	 */
	private $filterChain = NULL;

	/**
	 * 私有构造方法
	 */
	private function __construct() {
		$this->config = Config::getInstance();
		$this->log = new Log($this);
		$this->request = new Request($this);
		$this->response = new Response($this);
		$this->session = new Session($this);
		$this->view = new View($this);
	}

	/**
	 * 取得应用程序单例的方法
	 * @return Application
	 */
	public static function getInstance() {
		if(self::$instance instanceof self) {
			return self::$instance;
		}
		return self::$instance = new self();
	}

	/**
	 * 设置一个属性
	 * 
	 * @param val    属性值
	 * @param key    属性键名
	 */
	public function setAttribute($val, $key) {
		$this->attribute[$val] = $key;
	}

	/**
	 * 读取一个属性
	 * 
	 * @param key    属性名
	 */
	public function getAttribute($key = NULL) {
		if($key === NULL) {
			return $this->attribute;
		}
		return $this->attribute[$key];
	}

	/**
	 * 取得配置对象
	 * @return Config
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * 取得日志对象
	 * @return Log
	 */
	public function getLog() {
		return $this->log;
	}

	/**
	 * 取得视图对象
	 * @return View
	 */
	public function getView() {
		return $this->view;
	}

	/**
	 * 取得请求对象
	 * @return Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * 取得应答对象
	 * @return Response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * 取得会话对象
	 * @return Session
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * 取得Action对象
	 * @return Action
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * 启动应用程序 
	 */
	public function run() {
		// 验证Action是否存在
		if (!class_exists($this->request->getRequestAction()) 
				|| !method_exists($this->request->getRequestAction()
						, $this->request->getRequestMethod())) {
			header('HTTP/1.1 404 Not Found');
			include 'lib/misc/404.phtml';
			die();
		}
		
		// 初始化Action对象
		$reflectionClass = new \ReflectionClass($this->request->getRequestAction());
		$this->action = $reflectionClass->newInstance();
		
		//启动过滤器责任链
		$this->filterChain = new FilterChain($this);
		$this->filterChain->invoke();
	}

}

/* EOF */