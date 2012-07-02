<?php

namespace lib\core\session;

use lib\core\Session;

/**
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 14:08:19
 */
abstract class SessionHandler {

	/**
	 * session对象
	 * @var Session
	 */
	protected $session = NULL;
	
	/**
	 * Session配置数组
	 * @var array 
	 */
	protected $config = NULL;

	public function __construct($session, $config) {
		$this->session = $session;
		$this->config = $config;
	}

	/**
	 * Session打开方法
	 * 
	 * @param save_path    Session保存位置
	 * @param session_name    Session名称
	 */
	abstract public function open($save_path, $session_name);

	/**
	 * Session关闭方法 
	 */
	abstract public function close();

	/**
	 * 读取Session内容
	 * 
	 * @param sess_id    Session ID
	 */
	abstract public function read($sess_id);

	/**
	 * 写入Session方法
	 * 
	 * @param sess_id    Session ID
	 * @param sess_data    Session数据
	 */
	abstract public function write($sess_id, $sess_data);

	/**
	 * 销毁Session方法
	 * 
	 * @param sess_id    Session ID
	 */
	abstract public function destroy($sess_id);

	/**
	 * Session垃圾回收方法
	 * 
	 * @param maxlifetime    生命周期
	 */
	abstract public function gc($maxlifetime);

}

/* EOF */