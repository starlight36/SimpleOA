<?php
namespace widget;

use lib\core\Widget;
use lib\core\Config;
use model\UserModel;
use model\ProcessModel;

/**
 * 后台界面导航树的部件
 *
 * @author starlight36
 */
class NavtreeWidget extends Widget {
	
	/**
	 * 用户模块
	 * @var UserModel 
	 */
	private $userModel = NULL;
	
	/**
	 * 流程模块
	 * @var ProcessModel 
	 */
	private $processModel = NULL;


	/**
	 * 初始化部件 
	 */
	public function init() {
		$this->userModel = new UserModel();
		$this->processModel = new ProcessModel();
	}
	
	/**
	 * 修改导航树 使用回调函数对树上节点进行调整
	 * @param array $navtree 
	 * @param callback $callback
	 * @param mixed $userdata
	 */
	private function modifyNavtree(&$navtree, $callback, $userdata = NULL) {
		if(NULL == $navtree || !is_array($navtree)) {
			return;
		}
		foreach($navtree as $key => &$val) {
			if(empty($val) || !is_array($val)) {
				return;
			}
			if(array_key_exists('id', $val)) {
				if(!$callback($val, $userdata)) {
					unset($navtree[$key]);
				}else{
					$this->modifyNavtree($val['children'], $callback, $userdata);
				}
			}
		}
		usort($navtree , function($a, $b){
			if(!is_array($a) || !array_key_exists('order', $a) 
					|| !is_array($b) || !array_key_exists('order', $b)) {
				return 0;
			}
			if($a['order'] == $b['order']) {
				return 0;
			} elseif($a['order'] > $b['order']) {
				return 1;
			} else {
				return -1;
			}
		});
	}
	
	/**
	 * 取得可创建流程的树 
	 * @return array
	 */
	private function getCreatableProcessTree() {
		$userId = intval($this->session->get('userInfo')->id);
		$processTree = array();
		$processCategory = $this->processModel->getCategoryList();
		foreach($processCategory as $category) {
			$categoryTree = array(
				'id' => 'process-cate-'.$category->id,
				'text' => $category->name,
				'iconCls' => 'icon-dir',
				'order' => $category->id,
				'state' => 'closed',
				'attributes' => array('url'=>NULL)
			);
			// 该分类下所有的流程
			$processArray = $this->processModel->getList(array('user'=>$userId, 'category'=>$category->id));
			if(!empty($processArray)) {
				foreach($processArray as $process) {
					$categoryTree['children'][] = array(
						'id' => 'process-'.$process->id,
						'text' => $process->name,
						'iconCls' => 'icon-process',
						'order' => $process->id,
						'attributes' => array(
							'url' => \lib\util\Url::get('Process', 'view', array('id'=>$process->id))
						),
					);
				}
				$processTree[] = $categoryTree;
			}
		}
		
		return $processTree;
	}
	
	/**
	 * 取得当前用户的导航树
	 * @return array 
	 */
	public function getNavtree() {
		$navtree = $this->config->get('navtree');
		
		$data = array(
			'manage' => false,
			'process' => NULL
		);
		
		// 是否为管理员
		$userId = intval($this->session->get('userInfo')->id);
		if(in_array(0, $this->userModel->getUserRoles($userId))) {
			$data['manage'] = true;
		}
		
		// 加载可用的流程列表
		$data['process'] = $this->getCreatableProcessTree();
		
		// 修改导航树
		$this->modifyNavtree($navtree, function(&$tree, $data) {
			switch($tree['id']) {
				case 'process':
					if($data['process']) {
						$tree['children'] = $data['process'];
						return true;
					}else{
						return false;
					}
				case 'manage':
					if($data['manage']) {
						return true;
					}else{
						return false;
					}
				default:
					return true;
			}
		}, $data);
		return $navtree;
	}
	
	
	
}

/* EOF */