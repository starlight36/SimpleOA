<?php

namespace lib\core;

/**
 * 应答包装类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:22
 */
class Response {
	
	/**
	 * 应用程序上下文对象
	 * @var Application
	 */
	private $applicationContext = NULL;

	/**
	 * 输出缓冲区状态.
	 */
	private $obStatus = FALSE;

	/**
	 * 构造方法 
	 * @param Application $appContext 应用程序对象
	 */
	public function __construct($appContext) {
		$this->applicationContext = $appContext;
		$this->obStatus = ob_start();
	}

	/**
	 * 抛出StopActionException终止当前Action的执行.
	 */
	public function stop() {
		throw new exception\StopActionException();
	}

	/**
	 * 显示404错误, 并终止当前Action的运行.
	 * 
	 * @param msg    404错误消息.
	 */
	public function show404($msg = 'Not Found') {
		header('HTTP/1.1 404 '.$msg);
		$this->stop();
	}

	/**
	 * 捕获输出. 执行该方法将会先判断输出缓冲区是否以已经打开, 然后清空缓冲区.
	 */
	public function capture() {
		if(!$this->obStatus) ob_start();
		ob_clean();
	}

	/**
	 * 取得已经捕获的缓冲区内容, 并清空缓冲区.
	 */
	public function getCaptured() {
		$ob = ob_get_contents();
		ob_clean();
		return $ob;
	}

	/**
	 * 清空输出缓冲区
	 */
	public function clear() {
		ob_clean();
	}

	/**
	 * 发送一条HTTP应答头信息
	 * 
	 * @param name    HTTP头名称, 或完整的一行HTTP头
	 * @param value    HTTP头值, 为NULL则把name参数看作一条完整的HTTP头
	 */
	public function header($name, $value = NULL) {
		if($value == NULL) {
			header($name);
		} else {
			header($name . ': ' . $value);
		}
	}

	/**
	 * 向客户端发送Cookie
	 * 
	 * @param name    Cookie名称
	 * @param value    Cookie内容
	 * @param expire    有效期, 默认为0, 即会话有效
	 * @param path    有效路径, 默认为'/', 即相对站点根有效
	 * @param domain    有效域, 默认为NULL, 即当前域
	 */
	public function cookie($name, $value, $expire = 0, $path = '/', $domain = NULL) {
		setcookie($name, $value, $expire, $path, $domain);
	}

	/**
	 * 重定向到一个新的URL, 可以是内部地址, 也可以是外部地址
	 * 
	 * @param target    要重定向到的地址
	 */
	public function redirect($target) {
		if(strtolower(substr($target, 0, 7)) != 'http://' && strtolower(substr($target, 0, 1)) != '/') {
			$contextPath = $this->applicationContext->getRequest()->getContextPath();
			$target = rtrim($contextPath, '/').'/'.$target;
		}
		header('Location: '.$target);
		$this->stop();
	}

}

/* EOF */