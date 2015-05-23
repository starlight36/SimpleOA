<?php
namespace model;

use lib\core\Model;
use lib\db\Connection;
use lib\db\DBFactory;
use lib\db\ResultSet;

/**
 * 角色模型类
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class RoleModel extends Model {
	
	/**
	 * 数据库连接对象
	 * @var Connection 
	 */
	private $conn = NULL;

	public function init() {
		$this->conn = DBFactory::getInstance()->getConnection();
	}

	/**
	 * 取得某一ID角色的信息
	 * 
	 * @param id    角色ID
	 */
	public function get($id) {
		assert(is_int($id));
		$sql = 'SELECT * FROM oa_role WHERE status = 0 AND id = ?';
		return $this->conn->query($sql, $id)->getFirst();
	}

	/**
	 * 根据组织ID查找角色
	 * 
	 * @param orgId    组织ID
	 */
	public function getList($orgId = NULL) {
		$sql = 'SELECT id, `name`, description FROM oa_role WHERE status = 0';
		if($orgId) {
			$sql .= ' AND org_id = ?';
		}
		return $this->conn->query($sql, $orgId)->getAll();
	}

	/**
	 * 新建角色
	 * 
	 * @param info    角色信息
	 */
	public function add($info) {
		return $this->conn->insert('oa_role', $info);
	}

	/**
	 * 更新角色
	 * 
	 * @param id    要更新的ID
	 * @param info    角色信息
	 */
	public function update($id, $info) {
		assert(is_int($id));
		return $this->conn->update('oa_role', $info, 'status = 0 AND id = :rid', array(':rid'=>$id));
	}

	/**
	 * 删除角色
	 * 
	 * @param id    要删除的角色ID
	 */
	public function delete($id) {
		assert(is_int($id));
		return $this->conn->delete('oa_role', 'id = ?', $id);
	}

}

/* EOF */