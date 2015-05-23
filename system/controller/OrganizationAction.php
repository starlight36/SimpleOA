<?php
namespace controller;

use lib\core\Action;
use model\OrganizationModel;
use model\RoleModel;
use model\UserModel;

/**
 * 组织结构管理控制器类
 * @author starlight36
 * @version 1.0
 * @created 06-四月-2012 10:53:59
 */
class OrganizationAction extends Action {

	/**
	 * 默认执行方法
	 */
	public function execute() {
		$this->render('phtml', 'organization/main.phtml');
	}
	
	/**
	 * 取得组织结构树 
	 */
	public function treeviewExecute() {
		$queryArray = explode('-', $this->getForm('id', 'org-0'));
		$type = $queryArray[0] ? $queryArray[0] : 'org';
		$parentId = $queryArray[1] ? $queryArray[1] : 0;
		$tree = array();
		$orgModel = new OrganizationModel();
		$nodeList = $orgModel->getList($type, $parentId);
		foreach($nodeList as $node) {
			$treenode = array(
				'id' => $node->type.'-'.$node->id,
				'name' => $node->name,
				'description' => $node->description,
				'attributes' => array(
					'type' => $node->type,
					'id' => $node->id
				)
			);
			if($node->type == 'org') {
				$treenode['iconCls'] = 'icon-dir';
				$treenode['state'] = 'closed';
			} elseif($node->type == 'role') {
				$treenode['iconCls'] = 'icon-role';
				$treenode['state'] = 'closed';
			} else {
				$treenode['iconCls'] = 'icon-user';
				$treenode['state'] = 'open';
			}
			$tree[] = $treenode;
		}
		
		$this->render('json', $tree);
	}
	
	/**
	 * 为选择树提供数据源 
	 */
	public function treeSelectorExecute() {
		$parentId = intval($this->getForm('id', 0));
		$tree = array();
		$orgModel = new OrganizationModel();
		$nodeList = $orgModel->getList('org', $parentId, 'org');
		foreach($nodeList as $node) {
			$treenode = array(
				'id' => $node->id,
				'text' => $node->name,
				'description' => $node->description,
				'attributes' => array(
					'type' => $node->type,
					'id' => $node->id
				)
			);
			if($node->type == 'org') {
				$treenode['iconCls'] = 'icon-dir';
				$treenode['state'] = 'closed';
			} else {
				$treenode['iconCls'] = 'icon-role';
				$treenode['state'] = 'open';
			}
			$tree[] = $treenode;
		}
		$this->render('json', $tree);
	}
	
	/**
	 * 新建/编辑组织结构的表单
	 */
	public function editOrgExecute() {
		$id = intval($this->getQuery('id', 0));
		if($id) {
			$orgModel = new OrganizationModel();
			$orgInfo = $orgModel->get($id);
			if(intval($orgInfo->parent_id) > 0) {
				$orgInfo->parent_name = $orgModel->get(intval($orgInfo->parent_id))->name;
			}
			$this->assign('orgInfo', $orgInfo);
		}
		$this->render('phtml', 'organization/org_form.phtml');
	}
	
	/**
	 * 保存组织编辑 
	 */
	public function saveOrgExecute() {
		$orgModel = new OrganizationModel();
		$id = intval($this->getForm('id'));
		$parent_id = $this->getForm('parent_id', NULL); 
		$orgInfo = array(
			'name' => $this->getForm('name'),
			'description' => $this->getForm('description'),
			'status' => 0
		);
		if(empty($parent_id)) {
			$orgInfo['parent_id'] = 0;
		} elseif(preg_match('/^\d+$/', $parent_id)) {
			$orgInfo['parent_id'] = intval($parent_id);
		}
		if($id == 0) {
			$orgModel->add($orgInfo);
		} else {
			$orgModel->update($id, $orgInfo);
		}
		$this->assign('code', true);
		$this->render('json');
	}
	
	/**
	 *  新建/编辑角色的表单
	 */
	public function editRoleExecute() {
		$id = intval($this->getQuery('id', 0));
		if($id) {
			$orgModel = new OrganizationModel();
			$roleModel = new RoleModel();
			$roleInfo = $roleModel->get($id);
			if(intval($roleInfo->org_id) > 0) {
				$roleInfo->org_name = $orgModel->get(intval($roleInfo->org_id))->name;
			}
			$this->assign('roleInfo', $roleInfo);
		}
		$this->render('phtml', 'organization/role_form.phtml');
	}
	
	/**
	 * 保存角色编辑 
	 */
	public function saveRoleExecute() {
		$roleModel = new RoleModel();
		$id = intval($this->getForm('id'));
		$org_id = $this->getForm('org_id', NULL);
		$role_info = array(
			'name' => $this->getForm('name'),
			'description' => $this->getForm('description'),
			'status' => 0
		);
		if(intval($org_id) > 0) {
			$role_info['org_id'] = $org_id;
		}
		if($id == 0) {
			$roleModel->add($role_info);
		} else {
			$roleModel->update($id, $role_info);
		}
		$this->assign('code', true);
		$this->render('json');
	}
	
	/**
	 * 编辑用户页面角色选择下拉列表的数据源 
	 */
	public function rolecomboExecute() {
		$roleModel = new RoleModel();
		$roles = $roleModel->getList();
		$this->render('json', $roles);
	}
	
	/**
	 *  新建/编辑用户的表单
	 */
	public function editUserExecute() {
		$id = intval($this->getQuery('id', 0));
		if($id) {
			$userModel = new UserModel();
			$userInfo = $userModel->get($id);
			$userCurrentRoles = $userModel->getUserRoles((int)$userInfo->id);
			$this->assign('userInfo', $userInfo);
			$this->assign('userCurrentRoles', $userCurrentRoles);
		}
		$this->render('phtml', 'organization/user_form.phtml');
	}
	
	/**
	 * 保存用户信息编辑 
	 */
	public function saveUserExecute() {
		$userInfo = array(
			'number' => $this->getForm('number'),
			'name' => $this->getForm('name'),
			'email' => $this->getForm('email'),
			'gender' => $this->getForm('gender'),
			'birthday' => $this->getForm('birthday'),
			'status' => $this->getForm('status', 0)
		);
		$userModel = new UserModel();
		if($this->getForm('password')) {
			$userInfo['password'] = strtolower(md5(strtolower(md5($this->getForm('password')))));
		}
		$id = intval($this->getForm('id', 0));
		if($id) {
			$userModel->update($id, $userInfo);
		} else {
			if(!$userModel->isAvailable($userInfo['number'])) {
				$this->assign('code', FALSE);
				$this->assign('msg', '账号已被使用.');
				$this->render('json');
				return;
			}
			$userInfo['create_time'] = time();
			$id = intval($userModel->add($userInfo));
		}
		$userModel->setRole($id, $this->getForm('related-role', array()));
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 删除节点 
	 */
	public function deleteExecute() {
		$id = intval($this->getForm('id', 0));
		$type = $this->getForm('type');
		if($type == 'org') {
			$orgModel = new OrganizationModel();
			$orgModel->delete($id);
		} elseif($type == 'role') {
			$roleModel = new RoleModel();
			$roleModel->delete($id);
		} else {
			$userModel = new UserModel();
			$userModel->delete($id);
		}
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
}

/* EOF */