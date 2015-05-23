<?php

namespace model;

use lib\core\Model;
use lib\db\Connection;
use lib\db\DBFactory;

/**
 * 用户类
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class UserModel extends Model {
	/**
	 * 登录操作结果常量, 登录成功
	 */
	const LOGIN_SUCCESS = 0;
	
	/**
	 * 登录操作结果常量, 用户名不存在
	 */
	const LOGIN_USER_NOT_EXISIT = 1;
	
	/**
	 * 登录操作结果常量,  密码不正确
	 */
	const LOGIN_PASS_NOT_CORRECT = 2;
	
	/**
	 * 登录操作结果常量, 用户已经锁定 
	 */
	const LOGIN_USER_LOCKED = 3;

	/**
	 * 数据库连接
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
	 * 执行登录操作
	 * 
	 * @param account 用户账号
	 * @param password MD5加密的密码
	 */
	public function login($account, $password) {
		if(NULL == ($userInfo = $this->get((string)$account)) || $userInfo->status == 2) {
			return self::LOGIN_USER_NOT_EXISIT;
		}
		if(strtoupper($userInfo->password) != strtoupper($password)) {
			return self::LOGIN_PASS_NOT_CORRECT;
		}
		if($userInfo->status == 1) {
			return self::LOGIN_USER_LOCKED;
		}
		$modifier = array(
			'last_login_time' => time()
		);
		$this->conn->update('oa_user', $modifier, 'id = :uid', array(':uid'=>$userInfo->id));
		return self::LOGIN_SUCCESS;
	}
	
	/**
	 * 根据用户ID取得所在组ID的数组
	 * @param int $id 
	 */
	public function getUserRoles($id) {
		assert(is_int($id));
		$sql = 'SELECT DISTINCT role_id FROM oa_user_role_relation WHERE user_id = ?';
		$rs = $this->conn->query($sql, $id);
		$result = array();
		foreach($rs as $row) {
			$result[] = $row->role_id;
		}
		return $result;
	}

	/**
	 * 取得指定用户的信息, 不存在返回NULL
	 * 
	 * @param account    查询的用户账号
	 */
	public function get($account) {
		if(is_int($account)) {
			$sql = 'SELECT * FROM oa_user WHERE id = ?';
		} else {
			$sql = 'SELECT * FROM oa_user WHERE `number` = ?';
		}
		$userInfo = $this->conn->query($sql, $account);
		if($userInfo->size()) {
			return $userInfo->getFirst();
		}
		return NULL;
	}
	
	/**
	 * 用于检查账号是否可用的方法
	 * @param string $account 查询的账号
	 */
	public function isAvailable($account) {
		if($this->get($account) === NULL) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * 创建新用户
	 * 
	 * @param info    用户信息关联数组
	 */
	public function add($info) {
		return $this->conn->insert('oa_user', $info);
	}

	/**
	 * 更新用户信息
	 * @param type $id 要更新的用户的ID
	 * @param type $info 要更新的信息数组
	 */
	public function update($id, $info) {
		return $this->conn->update('oa_user', $info, 'status = 0 AND id = :uid', array(':uid'=>$id));
	}
	
	/**
	 * 删除一个用户
	 * @param int $id
	 * @return int 
	 */
	public function delete($id) {
		return $this->conn->update('oa_user', array('status' => 2), 'id = :uid', array(':uid'=>$id));
	}

	/**
	* 设置一个用户的角色
	* @param int $userId 用户ID
	* @param array $roles 角色ID数组
	*/
	public function setRole($userId, $roles) {
		assert(is_int($userId));
		assert(is_array($roles));
		$this->conn->delete('oa_user_role_relation', 'user_id = ? ', $userId);
		foreach($roles as $role) {
			$this->conn->insert('oa_user_role_relation', array(
				'user_id' => $userId,
				'role_id' => $role,
				'create_time' => time()
			));
		}
	}

	/**
	 * 返回用户信息数组
	 * 
	 * @param roleId
	 */
	public function getList($roleId) {
		
	}

}

/* EOF */