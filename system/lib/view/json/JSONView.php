<?php

namespace lib\view\json;

use lib\core\Application;
use lib\view\ViewAdapter;
use \lib\core\Response;

/**
 * JSON应答的视图引擎
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 14:08:19
 */
class JSONView {
	
	/**
	 * 应答对象
	 * @var Response 
	 */
	private $response = NULL;
	
	/**
	 * 构造方法 
	 */
	public function __construct() {
		$this->response = Application::getInstance()->getResponse();
	}

	/**
	 * 渲染一个视图
	 * @param string $tpl 
	 */
	public function render($data) {
		$this->response->header('Content-Type', 'application/json');
		echo json_encode($data);
	}
}
/* EOF */