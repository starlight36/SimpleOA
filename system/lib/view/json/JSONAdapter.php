<?php

namespace lib\view\json;

use lib\core\Application;
use lib\view\ViewAdapter;

/**
 * 用于PHtml的视图适配器
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 14:08:19
 */
class JSONAdapter extends ViewAdapter {

	/**
	 * JSONView对象
	 * @var JSONView
	 */
	private $jsonView = NULL;
	
	/**
	 * 初始化方法
	 */
	public function init() {
		$this->jsonView = new JSONView();
	}
	
	/**
	 * 渲染视图
	 * @param string $data 视图名称
	 */
	public function render($data = NULL) {
		if(NULL != $data && is_array($data)) {
			$data = array_merge($this->view->getValueStack(), $data);
		} else {
			$data = $this->view->getValueStack();
		}
		$this->jsonView->render($data);
	}
}
/* EOF */