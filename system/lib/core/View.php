<?php
namespace lib\core;

/**
 * 视图类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:22
 */
class View {

	/**
	 * 应用程序上下文对象
	 */
	private $applicationContext = NULL;

	/**
	 * 应答对象
	 */
	private $response = NULL;

	/**
	 * 视图值栈
	 */
	private $valueStack = array();

	/**
	 * 视图引擎对象列表
	 */
	private $engine = array();

	/**
	 * 构造方法
	 * @param Application $appContext 
	 */
	public function __construct($appContext) {
		$this->applicationContext = $appContext;
		$this->response = $appContext->getResponse();
	}
	
	/**
	 * 取得一个类型的视图引擎
	 * @param string $engineType 配置里的引擎类型
	 * @return view\ViewAdapter
	 */
	public function getEngine($engineType) {
		$engineType = strtolower($engineType);
		if(array_key_exists($engineType, $this->engine)) {
			return $this->engine[$engineType];
		}
		
		$config = $this->applicationContext->getConfig()->get('view.engine');
		if(array_key_exists($engineType, $config)) {
			$view_class = $config[$engineType]['class'];
			return $this->engine[$engineType] = new $view_class($this, $config[$engineType]['config']);
		} else {
			trigger_error('[ERROR] unknown view engine type.', E_USER_ERROR);
		}
	}
	
	/**
	 * 取得视图值栈
	 * @return array 
	 */
	public function getValueStack() {
		return $this->valueStack;
	}
	
	/**
	 * 设置页面标题 
	 * @param string $title 页面标题
	 */
	public function setPageTitle($title) {
		$this->assign(array('__page__'=>array('title'=>$title)));
	}
	
	/**
	 * 设置页面简介信息
	 * @param string $description 页面简介
	 */
	public function setPageDescription($description) {
		$this->assign(array('__page__'=>array('description'=>$description)));
	}
	
	/**
	 * 设置页面关键字
	 * @param string $keyword 关键字
	 */
	public function setPageKeyword($keyword) {
		$this->assign(array('__page__'=>array('keyword'=>$keyword)));
	}

	/**
	 * 向值栈内添加数据
	 * 
	 * @param name    要渲染的变量名称, 若为数组将把数组作为关联数组渲染.
	 * @param value    要渲染的值.
	 */
	public function assign($name, $value = NULL) {
		if(is_array($name)) {
			foreach($name as $key => $val) {
				$this->assign($key, $val);
			}
		} else {
			$this->valueStack[$name] = $value;
		}
	}

	/**
	 * 渲染一个视图
	 * @param string $type 视图类型
	 * @param string $data 视图值
	 */
	public function render($type, $data = NULL) {
		$this->getEngine($type)->render($data);
	}

}

/* EOF */