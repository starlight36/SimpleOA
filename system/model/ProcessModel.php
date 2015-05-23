<?php

namespace model;

use lib\core\Model;
use lib\db\Connection;
use lib\db\DBFactory;

/**
 * 流程模型类
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class ProcessModel extends Model {
	
	/**
	 * 连接对象
	 * @var Connection 
	 */
	private $conn = NULL;

	public function __construct() {
		parent::__construct();
		$this->conn = DBFactory::getInstance()->getConnection();
	}

	/**
	 * 根据流程ID取得一个流程
	 * 
	 * @param id    要取得的流程ID
	 */
	public function get($id) {
		$sql = 'SELECT * FROM oa_process WHERE id = ?';
		return $this->conn->query($sql, $id)->getFirst();
	}

	/**
	 * 根据参数以分页形式取得流程
	 * 
	 * @param query    流程查询数组
	 * @param pageSize    分页参数 - 每页条数, 0为不分页
	 * @param pageNum    分页参数 - 页码
	 * @param rowCount    分页参数 - 记录条数变量
	 * @param pageCount    分页参数 - 页面总数
	 */
	public function getList($query = NULL, $pageSize = 0, $pageNum = 1, &$rowCount = NULL, &$pageCount = NULL) {
		$sql_tpl = array();
		$sql = 'SELECT %s FROM ';
		
		$sql_tpl['oa_process'] = NULL;
		
		// 指定流程状态
		if($query && array_key_exists('process_status', $query)) {
			$sql_tpl['oa_process']['oa_process.status = :process_status'] = array(':process_status'=>$query['process_status']);
		}
		
		// 指定用户ID
		if($query && array_key_exists('user', $query)) {
			$sql_tpl['oa_user_role_relation']['oa_user_role_relation.user_id = :user'] = array(':user'=>$query['user']);
			$sql_tpl['oa_role']['oa_role.id = oa_user_role_relation.role_id'] = NULL;
			$sql_tpl['oa_role']['oa_role.status = 0'] = NULL;
			$sql_tpl['oa_process_acl']['oa_process_acl.role_id = oa_role.id'] = NULL;
			$sql_tpl['oa_process']['oa_process.id = oa_process_acl.process_id'] = NULL;
			$sql_tpl['oa_process']['oa_process.status = 1'] = NULL;
			// 用户访问权限
			if(array_key_exists('user-acl', $query)) {
				$sql_tpl['oa_process_acl']['oa_process_acl.`type` > :user_acl'] = array(':user_acl' => $user['user_acl']);
			}
		}

		// 指定角色ID
		if($query && array_key_exists('role', $query)) {
			$sql_tpl['oa_process']['oa_process.status = 1'] = NULL;
			$sql_tpl['oa_process']['oa_process.id = oa_process_acl.process_id'] = NULL;
			$sql_tpl['oa_process_acl']['oa_process_acl.role_id = :role'] = array(':role'=>$query['role']);
			// 角色访问权限
			if(array_key_exists('role-acl', $query)) {
				$sql_tpl['oa_process_acl']['oa_process_acl.`type` > :role_acl'] = array(':role_acl' => $user['role_acl']);
			}
		}
		
		// 指定分类ID
		if($query && array_key_exists('category', $query)) {
			$sql_tpl['oa_process_category']['oa_process_category.id = :category'] = array(':category' => $query['category']);
			$sql_tpl['oa_process']['oa_process.cate_id = oa_process_category.id'] = NULL;
		}
		
		// 关键字
		if($query && array_key_exists('keyword', $query)) {
			$sql_tpl['oa_process']['oa_process.description = \'%:keywords%\''] = array(':keywords' => $query['keywords']);
		}
		
		// 取得表列表
		$tables = array_keys($sql_tpl);
		// 取得条件列表和参数列表
		$wheres = $args = array();
		foreach($sql_tpl as $conditions) {
			if(empty($conditions)) continue;
			$wheres = array_merge($wheres, array_keys($conditions));
			foreach($conditions as $arg) {
				if(empty($arg) || !is_array($arg)) continue;
				$args = array_merge($args, $arg);
			}
		}
		
		// 生成SQL语句
		$sql .= implode(' , ', $tables);
		if(!empty($wheres)) {
			$sql .= ' WHERE '.implode(' AND ', $wheres);
		}
		
		// 查询并分页
		if($pageSize > 0) {
			assert(!is_null($rowCount));
			assert(!is_null($pageCount));
			$rowCount = $this->conn->query(sprintf($sql, 'COUNT(oa_process.id)'), $args)->getValue();
			$pageCount = ceil($rowCount / $pageSize);
			if($pageNum > $pageCount) $pageNum = $pageCount;
			if($pageNum < 1) $pageNum = 1;
			$offset = ($pageNum - 1) * $pageNum;
			$sql .= ' LIMIT :limit, :offset';
			$args[':limit'] = $pageSize;
			$args[':offset'] = $offset;
		}
		
		// 执行查询
		$rs = $this->conn->query(sprintf($sql, 'oa_process.*'), $args);
		return $rs->getAll();
	}

	/**
	 * 创建流程, 返回新流程的ID号
	 * 
	 * @param info    流程信息
	 */
	public function add($info) {
		return $this->conn->insert('oa_process', $info);
	}

	/**
	 * 更新一个流程的基本信息
	 * 
	 * @param id    要更新的流程ID
	 * @param info    流程基本信息数组
	 */
	public function update($id, $info) {
		return $this->conn->update('oa_process', $info, 'id = :pid', array(':pid' => $id));
	}

	/**
	 * 删除一个流程
	 * 
	 * @param id    要删除的流程Id
	 */
	public function delete($id) {
		$sql = 'SELECT COUNT(*) FROM oa_task WHERE id = ?';
		if($this->conn->query($sql, $id)->getValue() == 0) {
			$result = $this->conn->delete('oa_process', 'id = ?', $id);
		} else {
			$result = $this->conn->update('oa_process', array('status' => 2, 'cate_id' => 0), 'id = :id', array(':id' => $id));
		}
		return $result;
	}

	/**
	 * 取得访问控制列表
	 * @param int $processId 
	 */
	public function getAclList($processId) {
		assert(is_int($processId));
		$sql = 'SELECT oa_process_acl.*, oa_role.`name` AS role_name
				FROM oa_process_acl, oa_role 
				WHERE role_id = oa_role.id AND process_id = ?';
		return $this->conn->query($sql, $processId)->getAll();
	}
	
	/**
	 * 设置访问控制记录
	 * @param int $roleId 角色ID
	 * @param int $processId 流程ID
	 * @param int $permission 权限操作
	 */
	public function setAcl($roleId, $processId, $permission) {
		assert(is_int($roleId) && is_int($processId) && is_int($permission));
		$sql = 'UPDATE oa_process_acl SET `type` = `type` + ? WHERE role_id = ? AND process_id = ?';
		return $this->conn->execute($sql, $permission, $roleId, $processId);
	}
	
	/**
	 * 添加访问角色记录
	 * @param int $roleId 角色ID
	 * @param int $processId 流程ID
	 * @param int $permission 权限操作
	 */
	public function addAclRole($roleId, $processId, $permission = 0) {
		assert(is_int($roleId) && is_int($processId) && is_int($permission));
		$rowData = array(
			'role_id' => $roleId,
			'process_id' => $processId,
			'type' => $permission
		);
		$sql = 'SELECT COUNT(*) FROM oa_process_acl WHERE role_id = ? AND process_id = ?';
		if($this->conn->query($sql, $roleId, $processId)->getValue() == 0) {
			$this->conn->insert('oa_process_acl', $rowData);
		}
	}
	
	/**
	 * 删除访问角色记录
	 * @param int $roleId
	 * @param int $processId 
	 */
	public function removeAclRole($roleId, $processId) {
		assert(is_int($roleId) && is_int($processId));
		return $this->conn->delete('oa_process_acl', 'role_id = ? AND process_id = ?', (string)$roleId, $processId);
	}
	
	/**
	 * 检查某角色对某流程的访问权限
	 * @param int $roleId 角色ID
	 * @param int $processId 流程ID
	 * @param int $permission 权限操作
	 */
	public function checkProcessAccessByRole($roleId, $processId, $permission = 1) {
		assert(is_int($roleId) && is_int($processId) && is_int($permission));
		$sql = 'SELECT `type` FROM oa_process_acl WHERE role_id = ? AND process_id = ?';
		$rs = $this->conn->query($sql, $roleId, $processId);
		if($rs->size() == 1) {
			$type = $rs->getValue();
			if($permission == 0) {
				return TRUE;
			}
			if($type == $permission || $type == 3) {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * 检查某用户对某流程的访问权限
	 * @param int $userId 角色ID
	 * @param int $processId 流程ID
	 * @param int $permission 权限操作
	 */
	public function checkProcessAccessByUser($userId, $processId, $permission = 1) {
		assert(is_int($userId) && is_int($processId) && is_int($permission));
		$sql = 'SELECT `type` 
				FROM oa_process_acl, oa_user_role_relation 
				WHERE oa_user_role_relation.role_id = oa_process_acl.role_id
				AND user_id = ? AND process_id = ?';
		$rs = $this->conn->query($sql, $userId, $processId);
		if($rs->size() == 1) {
			$type = $rs->getValue();
			if($permission == 0) {
				return TRUE;
			}
			if($type == $permission || $type == 3) {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * 取得流程节点信息
	 * @param int $processId
	 * @param int $nodeKey
	 * @return mixed 
	 */
	public function getNode($processId, $nodeKey) {
		assert(is_int($processId));
		$sql = 'SELECT * FROM oa_process_node WHERE process_id = ? AND node_key = ?';
		return $this->conn->query($sql, $processId, $nodeKey)->getFirst();
	}

	/**
	 * 根据流程ID读取节点列表
	 * @param int $processId 流程ID
	 */
	public function getNodeList($processId) {
		assert(is_int($processId));
		$sql = '
			SELECT oa_process_node.*, oa_role.`name` AS actor_name 
			FROM oa_process_node, oa_role 
			WHERE actor = oa_role.id AND process_id = ?';
		return $this->conn->query($sql, $processId)->getAll();
	}
	
	/**
	 * 检查某流程节点的Key是否可用
	 * @param int $processId 流程ID
	 * @param string $key key名称
	 * @return boolean 
	 */
	public function checkNodeKeyAvailable($processId, $key) {
		assert(is_int($processId));
		$sql = 'SELECT COUNT(*) FROM oa_process_node WHERE process_id = ? AND `node_key` = ?';
		$count = $this->conn->query($sql, $processId, $key)->getValue();
		if($count > 0) {
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * 插入一个新的节点, 并返回新节点的ID信息
	 * @param array $info
	 * @return int 
	 */
	public function addNode($info) {
		return $this->conn->insert('oa_process_node', $info);
	}
	
	/**
	 * 更新一个节点信息
	 * @param int $nodeId 节点ID
	 * @param array $info 节点信息数组
	 * @return int 
	 */
	public function updateNode($nodeId, $info) {
		assert(is_int($nodeId));
		return $this->conn->update('oa_process_node', $info, 'id = :pnid', array(':pnid'=>$nodeId));
	}

	/**
	 * 删除一个流程节点
	 * @param processId    流程Id
	 * @param nodeId    删除的节点Id
	 */
	public function deleteNode($nodeId) {
		assert(is_int($nodeId));
		return $this->conn->delete('oa_process_node', 'id = :id', array(':id'=>$nodeId));
	}

	/**
	 * 取得流程分类列表
	 * @return array
	 */
	public function getCategoryList() {
		$sql = 'SELECT * FROM oa_process_category';
		return $this->conn->query($sql)->getAll();
	}

	/**
	 * 取得某Id的分类信息
	 * 
	 * @param id    要取得信息的分类Id
	 */
	public function getCategory($id) {
		assert(is_int($id));
		$sql = 'SELECT * FROM oa_process_category WHERE id = ?';
		return $this->conn->query($sql, $id)->getFirst();
	}

	/**
	 * 添加一个流程分类
	 * 
	 * @param info    流程分类信息
	 */
	public function addCategory($info) {
		return $this->conn->insert('oa_process_category', $info);
	}

	/**
	 * 更新一个流程分类
	 * 
	 * @param id    要更新的流程分类的Id
	 * @param info    流程信息数组
	 */
	public function updateCategory($id, $info) {
		return $this->conn->update('oa_process_category', $info, 'id = :pcid', array(':pcid' => $id));
	}

	/**
	 * 删除流程分类
	 * 
	 * @param id    要删除的分类的Id
	 */
	public function deleteCategory($id) {
		assert(is_int($id));
		$sql = 'SELECT COUNT(*) FROM oa_process WHERE cate_id = ? AND status < 2';
		if($this->conn->query($sql, $id)->getValue() > 0) {
			return FALSE;
		}
		return $this->conn->delete('oa_process_category', 'id = ?', $id);
	}

}

/* EOF */