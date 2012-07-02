<?php

namespace lib\core;

/**
 * 请求封装类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 17:42:07
 */
class Request {
	
	/**
	 * 应用程序上下文对象
	 */
	private $applicationContext = NULL;
	
	/**
	 * 配置对象
	 * @var Config 
	 */
	private $config = NULL;
	
	/**
	 * 请求头参数
	 * @var array 
	 */
	private $requestHeader = NULL;

	/**
	 * GET方法查询参数
	 */
	private $requestQuery = NULL;

	/**
	 * POST方法请求参数
	 */
	private $requestForm = NULL;

	/**
	 * Cookie头参数
	 */
	private $requestCookie = NULL;

	/**
	 * 请求中提交上传的文件
	 */
	private $requestFile = NULL;

	/**
	 * 请求URI, 包括路径, 查询参数
	 */
	private $requestURI = NULL;

	/**
	 * 请求Action的全限定名
	 */
	private $requestAction = NULL;

	/**
	 * 请求方法名称
	 */
	private $requestMethod = NULL;
	
	/**
	 * 请求的路径, 由Action和Method生成
	 * @var string 
	 */
	private $requestPath = NULL;
	
	/**
	 * 部署上下文路径
	 * @var string 
	 */
	private $contextPath = NULL;

	/**
	 * 构造方法 
	 * @param Application $appContext 应用程序对象
	 */
	public function __construct($appContext) {
		$this->applicationContext = $appContext;
		$this->config = $appContext->getConfig();
		$this->initParam();
		$this->initContextPath();
		$this->initHeader();
		$this->initPostFile();
		$this->initAction();
	}
	
	/**
	 * 初始化参数 
	 */
	private function initParam() {
		$this->requestQuery = $_GET;
		$this->requestForm = $_POST;
		$this->requestCookie = $_COOKIE;
		$this->requestURI = $_SERVER['REQUEST_URI'];	
	}
	
	private function initContextPath() {
		//取得部署上下文路径
		$document_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
		$index_root = rtrim(SYS_INDEX_PATH, '/');
		if($document_root == $index_root) {
			$this->contextPath = '/';
		} else {
			$document_root_len = strlen($document_root);
			if(substr($index_root, 0, $document_root_len) == $document_root) {
				$this->contextPath = substr($index_root, $document_root_len - 1).'/';
			}
		}
	}
	
	/**
	 * 初始化请求头 
	 */
	private function initHeader() {
		$this->requestHeader = array();
		foreach ($_SERVER as $key => $value) {
			if ('HTTP_' == substr($key, 0, 5)) {
				$this->requestHeader[str_replace('_', '-', substr($key, 5))] = $value;
			}
		}
		if (isset($_SERVER['PHP_AUTH_DIGEST'])) {  
			$this->requestHeader['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST'];
		} elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$this->requestHeader['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
		}
		if (isset($_SERVER['CONTENT_LENGTH'])) {
			$this->requestHeader['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH'];
		}
		if (isset($_SERVER['CONTENT_TYPE'])) {
			$this->requestHeader['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE'];
		}
	}
	
	/**
	 * 初始化上传文件信息 
	 */
	private function initPostFile() {
		if(empty($_FILES)) return;
		foreach($_FILES as $fieldName => $info) {
			if(is_array($info['name'])) {
				foreach ($info['name'] as $i => $one_name) {
					$this->requestFile[$fieldName][] = array(
						'name' => $one_name,
						'type' => $_FILES[$fieldName]['type'][$i],
						'size' => $_FILES[$fieldName]['szie'][$i],
						'tmp_name' => $_FILES[$fieldName]['tmp_name'][$i],
						'error' => $_FILES[$fieldName]['error'][$i]
					);
				}
			} else {
				$this->requestFile[$fieldName] = array(
					'name' => $info['name'],
					'type' => $_FILES[$fieldName]['type'],
					'size' => $_FILES[$fieldName]['szie'],
					'tmp_name' => $_FILES[$fieldName]['tmp_name'],
					'error' => $_FILES[$fieldName]['error']
				);
			}
		}
	}
	
	/**
	 * 初始化Action信息 
	 */
	private function initAction() {
		$route_config = $this->config->get('route.mode');
		
		// 取得PATH参数
		$path_string = '';
		if($route_config == 'query') {
			$path_string = $this->requestQuery['p'];
		} else if($route_config == 'phpinfo') {
			$path_string = $_SERVER['PATH_INFO'];
		} else {
			$path_string = $_SERVER['REQUEST_URI'];
			$query_pos = strpos($path_string, '?');
			if(FALSE !== $query_pos) {
				$path_string = substr($path_string, 0, $query_pos + 1);
			}
		}
		
		// 修剪左右两端的 '/', 为空则设置默认的Action
		$path_string = trim($path_string, '/');
		if(empty($path_string)) {
			$path_string = $this->config->get('route.default_action');
		}
		$this->requestPath = $path_string;
		
		// 查找对应的Action
		$method_start_offset = 0;
		$action_path = 'controller';
		$path_statement = explode('/', $path_string);
		$path_statement_count = count($path_statement);
		foreach ($path_statement as $key => $one) {
			if($one == '..' || $one == '.') continue;
			if (is_dir(SYS_PATH.$action_path.'/'.$one)) {
				$action_path .= '/'.$one;
			} else if(is_file(SYS_PATH.$action_path.'/'.$one.'Action.php')) {
				$action_path .= '/'.$one.'Action';
				$method_start_offset = $key;
				break;
			}
		}
		$this->requestAction = str_replace('/', '\\', $action_path);
		if($method_start_offset == $path_statement_count - 1) {
			$this->requestMethod = 'execute';
		} else {
			$this->requestMethod = $path_statement[$method_start_offset + 1].'Execute';
		}
		
	}
	
	/**
	 * 从数组中按表达式解析
	 * @param array $array 数组源
	 * @param string $exp 表达式
	 * @param mixed $default 默认值
	 * @return mixed
	 */
	private function getValue($array, $exp = NULL, $default = NULL) {
		$result = NULL;
		if(NULL == $exp) {
			$result = $array;
		} else {
			$exp_statement = explode('.', $exp);
			$new_exp = array('$array');
			foreach($exp_statement as $one) {
				if(substr($one, 0, 1) == '[' && substr($one, -1, 1) == ']') {
					$new_exp[] = $one;
				}else{
					$new_exp[] = '[\''.$one.'\']';
				}
			}
			eval('$result='.implode('', $new_exp).';');
		}
		if(NULL === $result) $result = $default;
		return $result;
	}

	/**
	 * 取得GET方法传入的查询参数
	 * 
	 * @param name    查询参数名称, NULL为取得整个查询参数数组
	 * @param default    默认值, 当查询参数不存在时, 使用此值
	 * @return mixed
	 */
	public function getQuery($name = NULL, $default = NULL) {
		return $this->getValue($this->requestQuery, $name, $default);
	}

	/**
	 * 取得POST方式传递的表单参数
	 * 
	 * @param name    参数名称, 为NULL返回整个表单查询参数数组
	 * @param default    参数默认值, 如果指定的参数不存在使用此值
	 * @return mixed
	 */
	public function getForm($name = NULL, $default = NULL) {
		return $this->getValue($this->requestForm, $name, $default);
	}

	/**
	 * 取得Cookie参数
	 * 
	 * @param name    参数名称, 为NULL返回整个Cookie参数数组.
	 * @param default    参数默认值, 当指定的参数不存在时使用此值.
	 * @return mixed
	 */
	public function getCookie($name = NULL, $default = NULL) {
		return $this->getValue($this->requestCookie, $name, $default);
	}

	/**
	 * 取得请求中上传的文件
	 * 
	 * @param name    文件域名称, 为NULL取得所有上传的文件域
	 * @return array
	 */
	public function getFile($name = NULL) {
		return $this->getValue($this->requestFile, $name);
	}
	
	/**
	 * 保存一个上传的文件
	 * @param array $file 上传的文件的摘要信息数组
	 * @param string $dest 要保存的目的地
	 * @return boolean 返回是否成功
	 */
	public function saveFile($file, $dest) {
		if(!is_array($file)) return;
		if($file['error'] > 0) return;
		if(move_uploaded_file($file['tmp_name'], $dest)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * 取得当前请求的URI
	 * @return string
	 */
	public function getURI() {
		return $this->requestURI;
	}
	
	/**
	 * 取得上下文相对路径
	 * @return string 
	 */
	public function getContextPath() {
		return $this->contextPath;
	}

	/**
	 * 取得当前请求的Action类名的全限定名
	 * @return string
	 */
	public function getRequestAction() {
		return $this->requestAction;
	}

	/**
	 * 取得当前请求的Action处理方法名称
	 * @return string
	 */
	public function getRequestMethod() {
		return $this->requestMethod;
	}
	
	/**
	 * 取得请求的路径
	 * @return string 
	 */
	public function getRequestPath() {
		return $this->requestPath;
	}

	/**
	 * 取得请求头信息
	 * 
	 * @param name    要取得的头信息名称, 为NULL返回全部头.
	 * @return string
	 */
	public function getHeader($name = NULL) {
		if(NULL == $name) {
			return $this->requestHeader;
		}
		return $this->requestHeader[strtoupper($name)];
	}

	/**
	 * 取得来访者的IP地址
	 * @return string
	 */
	public function getIpAddress() {
		if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
			$t_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}elseif($_SERVER['HTTP_CLIENT_IP']){
			$t_ip = $_SERVER['HTTP_CLIENT_IP'];
		}else{
			$t_ip = $_SERVER['REMOTE_ADDR'];
		}
		return $t_ip;
	}

	/**
	 * 获取请求方法, 以大写形式
	 * @return string
	 */
	public function getMethod() {
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * 取得请求的来源页
	 * @return string
	 */
	public function getReferURL() {
		return $_SERVER['HTTP_REFERER'];
	}

	/**
	 * 取得原始的请求体
	 * @return mixed
	 */
	public function getRawData() {
		return file_get_contents("php://input");
	}

	/**
	 * 返回请求是否为POST方式提交
	 * @return boolean
	 */
	public function isPost() {
		if($this->getMethod() == 'POST') {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * 判断一个请求是否为Ajax请求
	 * @return boolean
	 */
	public function isAjax() {
		if(strtolower($this->getHeader('request_type')) == 'ajax'
			 ||	$_REQUEST['ajax'] == TRUE) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * 跨页面的一次性存取数据
	 * 
	 * @param name    存入变量名称
	 * @param value    要存入的变量
	 * @param clean    本次访问是否清空变量
	 * @return mixed
	 */
	public function flash($name, $value = NULL, $clean = true) {
		$session = $this->applicationContext->getSession();
		if(NULL === $value) {
			$result = $session->get($name);
			if($clean) $session->remove($name);
			return $result;
		} else {
			$session->put($name, $value);
		}
	}

}

/* EOF */