<?php

namespace lib\view\phtml;

use lib\core\Application;
use lib\view\ViewAdapter;

/**
 * 用于PHtml的视图适配器
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 14:08:19
 */
class PHtmlAdapter extends ViewAdapter {

	/**
	 * PHtml对象
	 * @var PHtml
	 */
	private $phtml;
	
	/**
	 * Application对象
	 * @var Application
	 */
	private $applicationContext = NULL;
	
	/**
	 * 初始化方法
	 */
	public function init() {
		if($this->phtml instanceof PHtml) {
			return;
		}
		
		// 初始化视图类
		$this->phtml = new PHtml();
		$this->phtml->setTemplatePath($this->config['tpl_path']);
		
		// 系统内置对象
		$this->applicationContext = Application::getInstance();
		$this->phtml->bind('APP', $this->applicationContext);
		$this->phtml->bind('REQUEST', $this->applicationContext->getRequest());
		$this->phtml->bind('RESPONSE', $this->applicationContext->getResponse());
		$this->phtml->bind('SESSION', $this->applicationContext->getSession());
		$this->phtml->bind('CONFIG', $this->applicationContext->getConfig());
		$this->phtml->bind('LOG', $this->applicationContext->getLog());
		
		// 系统内置变量
		$this->phtml->assign('appConfig'
				, $this->applicationContext->getConfig()->get('appConfig'));
		$this->phtml->assign('contextPath'
				, $this->applicationContext->getRequest()->getContextPath());
	}
	
	/**
	 * 渲染视图
	 * @param string $data 视图名称
	 */
	public function render($data = NULL) {
		$this->phtml->assign($this->view->getValueStack());
		$this->phtml->render($data);
	}
}
/* EOF */