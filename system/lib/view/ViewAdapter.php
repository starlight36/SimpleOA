<?php

namespace lib\view;

/**
 * 视图引擎的抽象类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:24
 */
abstract class ViewAdapter {

	/**
	 * 视图对象
	 * @var \lib\core\View
	 */
	protected $view = NULL;
	
	/**
	 * 视图配置数组
	 * @var array
	 */
	protected $config = NULL;

	/**
	 * 构造方法
	 * @param \lib\core\View $view 视图对象
	 * @param array $config  视图配置数组
	 */
	public function __construct($view, $config) {
		$this->view = $view;
		$this->config = $config;
		$this->init();
	}

	/**
	 * 初始化方法
	 */
	public function init() { }

	/**
	 * 渲染视图
	 * 
	 * @param name    视图名称
	 */
	abstract public function render($data = NULL);

}

/* EOF */