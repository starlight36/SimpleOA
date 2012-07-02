<?php
namespace lib\db;

use lib\core\Application;

/**
 * 数据连接工厂
 * @author starlight36
 */
class DBFactory {
	
	/**
	 * 本类实例
	 * @var DBFactory 
	 */
	private static $instance = NULL;
	
	/**
	 * 配置数组
	 * @var array 
	 */
	private $config = NULL;
	
	/**
	 * 连接池
	 * @var array 
	 */
	private $pool = array();
	
	/**
	 * 构造方法 
	 */
	private function __construct() {
		$this->config = Application::getInstance()->getConfig()->get('db');
	}
	
	/**
	 * 取得本类单例对象
	 * @return DBFactory
	 */
	public static function getInstance() {
		if(self::$instance instanceof self) {
			return self::$instance;
		}
		return self::$instance = new self();
	}
	
	/**
	 * 取得一个数据库连接对象
	 * @param int $id  数据库标识
	 * @return Connection
	 */
	public function getConnection($id = NULL) {
		if($this->pool[$id] instanceof Connection) {
			return $this->pool[$id];
		}
		$config = $this->config['pool'][($id === NULL) ? $this->config['default'] : $id];
		if(empty($config)) {
			trigger_error('Selected DB connection does not exist.', E_USER_ERROR);
		}
		if(array_key_exists('uri', $config) && !empty($config['uri'])) {
			$config = $config['uri'];
		}
		return $this->pool[$id] = new Connection($config);
	}
	
}

/* EOF */