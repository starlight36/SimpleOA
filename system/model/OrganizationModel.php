<?php
namespace model;

use lib\core\Model;
use lib\db\Connection;
use lib\db\DBFactory;
use lib\db\ResultSet;

/**
 * 组织模型类
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class OrganizationModel extends Model {
	
	/**
	 * 数据库链接对象
	 * @var Connection
	 */
	private $conn = NULL;
	
	/**
	 * 初始化方法 
	 */
	public function init() {
		$this->conn = DBFactory::getInstance()->getConnection();
	}

	/**
	 * 根据取得某个组织信息
	 * 
	 * @param id    组织ID
	 */
	public function get($id) {
		assert(is_int($id));
		$sql = 'SELECT * FROM oa_organization WHERE status = 0 AND id = ? LIMIT 1';
		return $this->conn->query($sql, $id)->getFirst();
	}

	/**
	 * 根据父组织信息读取一个组织结构节点
	 * @param string $type 节点类型
	 * @param type $parentId 
	 */
	public function getList($type = 'org', $parentId = 0, $filter = NULL) {
		$result = array();
		if($type == 'org') {
			$sql = 'SELECT id, `name`, description, \'org\' AS `type` FROM oa_organization WHERE status = 0 AND parent_id = ?';
			$rs = $this->conn->query($sql, $parentId);
			$result = array_merge($result, $rs->getAll());
			if($filter != 'org') {
				$sql = 'SELECT id, `name`, description, \'role\' AS `type` FROM oa_role WHERE status = 0 AND org_id = ?';
				$rs = $this->conn->query($sql, $parentId);
				$result = array_merge($result, $rs->getAll());
			}
		} elseif($type == 'role') {
			$sql = 'SELECT id, `name`, `number` AS description, \'role\' AS `user` FROM oa_user, oa_user_role_relation WHERE oa_user.id = oa_user_role_relation.user_id AND oa_user.status != 2 AND role_id = ?';
			$rs = $this->conn->query($sql, $parentId);
			$result = array_merge($result, $rs->getAll());
		} else {
			return NULL;
		}
		return $result;
	}

	/**
	 * 添加一个新的组织结构
	 * 
	 * @param info    组织信息
	 */
	public function add($info) {
		return $this->conn->insert('oa_organization', $info);
	}

	/**
	 * 更新一个组织
	 * 
	 * @param id    要更新的组织ID
	 * @param info    组织的信息
	 */
	public function update($id, $info) {
		assert(is_int($id));
		return $this->conn->update('oa_organization', $info, 'status = 0 AND id = :orgid', array(':orgid'=>$id));
	}

	/**
	 * 要删除的组织ID
	 * 
	 * @param id    组织Id
	 */
	public function delete($id) {
		assert(is_int($id));
		return $this->conn->delete('oa_organization', 'id = ?', $id);
	}

}

/* EOF */