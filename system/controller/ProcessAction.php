<?php

namespace controller;

use lib\core\Action;
use model\ProcessModel;

/**
 * 主页面控制器类
 * @author starlight36
 * @version 1.0
 * @created 06-四月-2012 10:53:59
 */
class ProcessAction extends Action {
	
	/**
	 * 流程模型对象
	 * @var ProcessModel 
	 */
	private $processModel = NULL;
	
	/**
	 * 构造方法 
	 */
	public function __construct() {
		parent::__construct();
		$this->processModel = new ProcessModel();
	}
	
	/**
	 * 默认执行方法
	 */
	public function execute() {
		$this->render('phtml', 'process/main.phtml');
	}
	
	/**
	 * 取得流程树列表 
	 */
	public function treeviewExecute() {
		$processTree = array();
		$id = $this->getForm('id');
		if(empty($id)) {
			$processCategory = $this->processModel->getCategoryList();
			foreach($processCategory as $category) {
				$processTree[] = array(
					'id' => 'category-'.$category->id,
					'iconCls' => 'icon-dir',
					'state' => 'closed',
					'name' => $category->name,
					'description' => $category->description,
					'attributes' => array(
						'type' => 'category',
						'id' => $category->id
					)
				);
			}
 		} else {
			$queryArray = explode('-', $id);
			$categoryId = $queryArray[1];
			$processList = $this->processModel->getList(array('category' => $categoryId));
			foreach($processList as $process) {
				$processTreeItem = array(
					'id' => 'process-'.$process->id,
					'iconCls' => 'icon-process',
					'state' => 'open',
					'name' => $process->name,
					'description' => $process->description,
					'attributes' => array(
						'type' => 'process',
						'id' => $process->id
					)
				);
				if($process->status == 2) {
					$processTreeItem['name'] = "<span class=\"deprecated-process\">{$processTreeItem['name']}</span>";
				}
				$processTree[] = $processTreeItem;
			}
		}
		$this->render('json', $processTree);
	}
	
	/**
	 * 编辑分类信息 
	 */
	public function editCategoryExecute() {
		$id = intval($this->getQuery('id', 0));
		if($id) {
			$categoryInfo = $this->processModel->getCategory($id);
			$this->assign('category', $categoryInfo);
		}
		$this->render('phtml', 'process/category_form.phtml');
	}
	
	/**
	 * 保存分类信息 
	 */
	public function saveCategoryExecute() {
		$id = intval($this->getForm('id', 0));
		$categoryInfo = array(
			'name' => $this->getForm('name'),
			'description' => $this->getForm('description')
		);
		if($id) {
			$this->processModel->updateCategory($id, $categoryInfo);
		} else {
			$this->processModel->addCategory($categoryInfo);
		}
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 编辑流程基本信息 
	 */
	public function editProcessInfoExecute() {
		$id = intval($this->getQuery('id', 0));
		$processInfo = array();
		if($id == 0) {
			$processInfo = array(
				'name' => '新建流程'.date('YmdHis'),
				'description' => '',
				'cate_id' => 1,
				'create_time' => time(),
				'status' => 0
			);
			$id = $processInfo['id'] = $this->processModel->add($processInfo);
		}
		$processInfo = $this->processModel->get($id);
		$this->assign('processInfo', $processInfo);
		$this->render('phtml', 'process/process_form.phtml');
	}
	
	/**
	 * 取得流程分类列表 
	 */
	public function getProcessCategoryListExecute() {
		$processCategoryList = $this->processModel->getCategoryList();
		$selectList = array();
		foreach($processCategoryList as $processCategory) {
			$selectList[] = array(
				'id' => $processCategory->id,
				'text' => $processCategory->name
			);
		}
		$this->render('json', $selectList);
	}
	
	/**
	 * 保存流程基本信息 
	 */
	public function saveProcessInfoExecute() {
		$id = intval($this->getForm('id'));
		$processInfo = array(
			'name' => $this->getForm('name'),
			'description' => $this->getForm('description'),
			'cate_id' => $this->getForm('cate_id')
		);
		$this->processModel->update($id, $processInfo);
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 发布流程 
	 */
	public function pubProcessExecute() {
		$id = intval($this->getForm('id'));
		$processInfo = array(
			'status' => 1
		);
		$this->processModel->update($id, $processInfo);
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 取得流程节点列表
	 */
	public function getProcessNodeListExecute() {
		$id = intval($this->getQuery('id'));
		$processNodeList = $this->processModel->getNodeList($id);
		$this->render('json', $processNodeList);
	}
	
	/**
	 * 保存流程节点信息
	 */
	public function saveProcessNodeExecute() {
		$id = intval($this->getForm('id'));
		$nodeInfo = array(
			'name' => $this->getForm('name'),
			'description' => $this->getForm('description'),
			'node_key' => $this->getForm('node_key'),
			'process_id' => intval($this->getForm('process_id')),
			'actor' => intval($this->getForm('actor')),
			'goto_exp' => $this->getForm('goto_exp'),
			'action_exp' => implode(',', $this->getForm('action_exp', array()))
		);
		if($id){
			$this->processModel->updateNode($id, $nodeInfo);
		} else {
			if(!$this->processModel->checkNodeKeyAvailable($nodeInfo['process_id'], $nodeInfo['node_key'])) {
				$this->assign('code', FALSE);
				$this->assign('msg', sprintf('流程标识符%s已被使用.', $nodeInfo['key']));
				$this->render('json');
				return;
			}
			$this->processModel->addNode($nodeInfo);
		}
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 移除某流程节点 
	 */
	public function removeProcessNodeExecute() {
		$id = intval($this->getForm('id'));
		if($this->processModel->deleteNode($id)) {
			$this->assign('code', TRUE);
		}  else {
			$this->assign('code', FALSE);
		}
		$this->render('json');
	}
	
	/**
	 * 取得访问控制表 
	 */
	public function getAclListExecute() {
		$id = intval($this->getQuery('id'));
		$aclList = $this->processModel->getAclList($id);
		foreach($aclList as &$acl) {
			$acl->supervise = FALSE;
			$acl->create = FALSE;
			if($acl->type == 1 || $acl->type == 3) {
				$acl->supervise = TRUE;
			}
			if($acl->type == 2 || $acl->type == 3) {
				$acl->create = TRUE;
			}
		}
		$this->render('json', $aclList);
	}
	
	/**
	 * 设置访问权限 
	 */
	public function setAclExecute() {
		$rid = intval($this->getForm('rid'));
		$pid = intval($this->getForm('pid'));
		$permission = intval($this->getForm('permission'));
		$this->processModel->setAcl($rid, $pid, $permission);
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 添加某角色的访问权限 
	 */
	public function addAclRoleExecute() {
		$rid = intval($this->getForm('rid'));
		$pid = intval($this->getForm('pid'));
		$this->processModel->addAclRole($rid, $pid);
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 删除某角色的访问权限记录 
	 */
	public function removeAclRoleExecute() {
		$rid = intval($this->getForm('rid'));
		$pid = intval($this->getForm('pid'));
		$this->processModel->removeAclRole($rid, $pid);
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 删除流程或者流程分类 
	 */
	public function deleteExecute() {
		$id = intval($this->getForm('id'));
		$type = $this->getForm('type');
		if($type == 'category') {
			if(FALSE === $this->processModel->deleteCategory($id)) {
				$this->assign('code', FALSE);
				$this->assign('msg', '您不能删除一个不为空的流程分类.');
				$this->render('json');
				return;
			}
		} else {
			$this->processModel->delete($id);
		}
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 查看流程 
	 */
	public function viewExecute() {
		$id = intval($this->getQuery('id'));
		$uid = intval($this->getSession()->get('userInfo')->id);
		if($this->processModel->checkProcessAccessByUser($uid, $id, 0)) {
			$processInfo = $this->processModel->get($id);
			if($processInfo->status == 1) {
				$this->assign('processInfo', $processInfo);
			}
		}
		$this->render('phtml', 'process/view.phtml');
	}

}

/* EOF */