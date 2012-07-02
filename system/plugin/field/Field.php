<?php
namespace plugin\field;

use lib\core\Application;
use lib\core\Request;
use lib\core\Log;

/**
 * 字段抽象基类
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
abstract class Field {

	/**
	 * 应用程序对象
	 * @var \lib\core\Application 
	 */
	private $application = NULL;
	
	/**
	 * 请求对象
	 * @var \lib\core\Request 
	 */
	private $request = NULL;
	
	/**
	 * 日志对象
	 * @var Log
	 */
	private $log = NULL;
	
	/**
	 * 字段类型
	 * @var string 
	 */
	private $type = NULL;
	
	/**
	 * 字段名称
	 * @var string 
	 */
	private $name = NULL;
	
	/**
	 * 字段说明
	 * @var string 
	 */
	private $description = NULL;
	
	/**
	 * 字段配置参数
	 * @var mixed 
	 */
	private $config = NULL;
	
	/**
	 * 字段标识符
	 * @var string 
	 */
	private $fieldKey = NULL;
	
	/**
	 * 字段值
	 * @var mixed 
	 */
	private $value = NULL;
	
	/**
	 * 默认值
	 * @var mixed 
	 */
	private $default = NULL;
	
	/**
	 * 是否必须
	 * @var bool 
	 */
	private $required = FALSE;
	
	/**
	 * 验证规则正则表达式
	 * @var string 
	 */
	private $validator = NULL;

	/**
	 * 构造方法 
	 */
	public function __construct() {
		$this->application = Application::getInstance();
		$this->request = $this->application->getRequest();
		$this->log = $this->application->getLog();
		$this->type = strtolower(preg_replace('/^(\w+?)FieldType$/i', '$1', get_class($this)));
	}
	
	/**
	 * 取得应用程序上下文
	 * @return Application 
	 */
	protected function getContext() {
		return $this->application;
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
	 * 设置字段名称
	 * @param string $name 
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * 取得字段名称
	 * @return string 
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * 取得简介
	 * @return string 
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * 设置简介
	 * @param string $description 
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * 设置字段标识
	 * @param string $key 
	 */
	public function setFieldKey($key) {
		$this->fieldKey = $key;
	}
	
	/**
	 * 读取字段标识
	 * @return string 
	 */
	public function getFieldKey() {
		return $this->fieldKey;
	}
	
	/**
	 * 取得字段配置参数
	 * @return mixed
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * 设置字段配置参数
	 * @param mixed $config 
	 */
	public function setConfig($config) {
		$this->config = $config;
	}
	
	/**
	 * 设置字段值
	 * @param mixed $val 
	 */
	public function setValue($val) {
		$this->value = $val;
	}
	
	/**
	 * 读取字段值
	 * @return mixed 
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * 读取默认值
	 * @return mixed 
	 */
	public function getDefault() {
		return $this->default;
	}

	/**
	 * 设置默认值
	 * @param mixed $default 
	 */
	public function setDefault($default) {
		$this->default = $default;
	}

	/**
	 * 读取是否必须
	 * @return bool 
	 */
	public function isRequired() {
		return $this->required;
	}

	/**
	 * 设置是否必须
	 * @param bool $required 
	 */
	public function setRequired($required) {
		$this->required = $required;
	}

	/**
	 * 读取验证器
	 * @return string 
	 */
	public function getValidator() {
		return $this->validator;
	}

	/**
	 * 设置验证器
	 * @param string $validator 
	 */
	public function setValidator($validator) {
		$this->validator = $validator;
	}
	
	/**
	 * 验证字段是否合法
	 * @return mixed 
	 */
	public function validation() {
		if($this->required && $this->value === NULL) {
			return FALSE;
		}
		if(empty($this->validator)) {
			return TRUE;
		}
		if (preg_match($this->validator, $this->value)) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 取得验证失败消息 
	 */
	abstract function getErrorMessage();

	/**
	 * 生成表单字段
	 */
	abstract function generate();


}
/* EOF */