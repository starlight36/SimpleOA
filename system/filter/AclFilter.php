<?php
namespace filter;

use lib\core\Filter;
use lib\util\Url;

/**
 * 访问控制过滤器类
 *
 * @author starlight36
 */
class AclFilter extends Filter {
	
	/**
	 * 执行过滤器
	 * @param \lib\core\FilterChain $filterChain 
	 */
	public function doFilter($filterChain) {
		$session = $this->getContext()->getSession();
		if($this->checkManage($session->get('userInfo')->id)) {
			$filterChain->invoke();
		}else{
			$view = $this->getContext()->getView();
			$view->assign('message_type', 'error');
			$view->assign('message', '您没有执行此操作的权限.');
			$view->render('phtml', 'common/message.phtml');
		}
	}
	
	/**
	 * 检查管理权限 
	 * @return bool
	 */
	private function checkManage($id) {
		return TRUE;
	}
}

/* EOF */