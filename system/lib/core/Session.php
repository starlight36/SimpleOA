<?php

namespace lib\core;

use lib\core;
use lib\core\session\SessionHandler;

/**
 * 请求会话封装类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:23
 */
class Session {

	/**
	 * Session配置数组
	 * @var array
	 */
	private $config = NULL;

	/**
	 * session处理器对象
	 * @var SessionHandler
	 */
	private $sessionHandler;

	/**
	 * 构造函数
	 * @param Application $appContext 
	 */
	public function __construct($appContext) {
		$this->config = $appContext->getConfig()->get('session');
		if(!empty($this->config['save_path'])) {
			session_save_path($this->config['save_path']);
		}
		if(!empty($this->config['handler']) && $this->config['handler'] != 'default') {
			$handler_class = $this->config[$this->config['handler']]['class'];
			$this->sessionHandler = new $handler_class($this, $this->config);
			session_set_save_handler(
				array($this->sessionHandler, 'open') , array($this->sessionHandler, 'close')
				, array($this->sessionHandler, 'read'), array($this->sessionHandler, 'write')
				, array($this->sessionHandler, 'destory'), array($this->sessionHandler, 'gc')
			);
		}
		session_start();
	}

	/**
	 * 取得一个会话值
	 * 
	 * @param name    会话名称
	 * @param default    默认值
	 */
	public function get($name, $default = NULL) {
		if($_SESSION[APP_ID] && array_key_exists($name, $_SESSION[APP_ID])) {
			if($_SESSION[APP_ID][$name]['t'] > 0 
					&& $_SESSION[APP_ID][$name]['t'] < time()) {
				$this->remove($name);
				return $default;
			} else {
				return $_SESSION[APP_ID][$name]['v'];
			}
		} else {
			return $default;
		}
	}

	/**
	 * 设置一个会话值
	 * @param name    会话值名称
	 * @param value    会话值
	 * @param expire    有效期
	 */
	public function put($name, $value, $expire = 0) {
		$_SESSION[APP_ID][$name] = array(
			't' => ($expire > 0) ? time() + $expire : 0,
			'v' => $value
		);
	}

	/**
	 * 删除一个会话值
	 * 
	 * @param name    会话名称
	 */
	public function remove($name) {
		unset($_SESSION[APP_ID][$name]);
	}

	/**
	 * 清空所有会话值
	 */
	public function clear() {
		$_SESSION[APP_ID] = NULL;
	}

}

/* EOF */