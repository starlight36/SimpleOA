<?php
namespace lib\core;

/**
 * 配置文件类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:22
 */
class Config {

	/**
	 * 本类对象
	 * @var Config
	 */
	private static $instance = NULL;

	/**
	 * 配置信息数组
	 */
	private $config = array();

	/**
	 * 私有构造方法 
	 */
	private function __construct() {}

	/**
	 * 取得本类实例
	 * @var Config
	 */
	public static function getInstance() {
		if(self::$instance instanceof self) {
			return self::$instance;
		}
		return self::$instance = new self();
	}

	/**
	 * 读取一个配置项
	 * 
	 * @param key    配置键名
	 */
	public function get($key = NULL) {
		if($key === NULL) {
			return $this->config;
		}
		$key_statement = explode('.', $key);
		$perfix = $key_statement[0];
		if(!array_key_exists($perfix, $this->config)) {
			$this->config[$perfix] = $this->loadConfigFile($perfix);
		}
		$config_exp = '$this->config';
		foreach ($key_statement as $one) {
			$config_exp .= "['{$one}']";
		}
		eval('$config = '.$config_exp.';');
		return $config;
	}

	/**
	 * 从文件加载一个配置
	 * @param perfix    配置前缀
	 * @return array
	 */
	private function loadConfigFile($perfix) {
		if(!is_file(SYS_PATH.'config/'.$perfix.'.php')) {
			return NULL;
		}
		return include 'config/'.$perfix.'.php';
	}

}

/* EOF */