<?php
namespace filter;

use lib\core\Filter;

/**
 * 测试过滤器类
 *
 * @author starlight36
 */
class TestFilter extends Filter {
	
	/**
	 * 执行过滤器
	 * @param \lib\core\FilterChain $filterChain 
	 */
	public function doFilter($filterChain) {
		$filterChain->invoke();
	}
}

/* EOF */