<?php
namespace filter;

use lib\core\Filter;
use lib\util\Url;

/**
 * 登录验证过滤器类
 *
 * @author starlight36
 */
class LoginFilter extends Filter {
	
	/**
	 * 执行过滤器
	 * @param \lib\core\FilterChain $filterChain 
	 */
	public function doFilter($filterChain) {
		$session = $this->getContext()->getSession();
		if (!$session->get('userInfo') || !is_object($session->get('userInfo'))) {
			$this->getContext()->getResponse()->redirect(Url::get('Home', 'login'));
		} else {
			$filterChain->invoke();
		}
	}
}

/* EOF */