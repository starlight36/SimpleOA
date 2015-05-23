<?php
namespace model;

use lib\core\Model;
use lib\db\DBFactory;

/**
 * 任务模型类
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class TaskModel extends Model {
	
	/**
	 * 数据库连接对象
	 * @var \lib\db\Connection
	 */
	private $conn = NULL;

	/**
	 * 初始化方法 
	 */
	public function init() {
		$this->conn = DBFactory::getInstance()->getConnection();
	}

	/**
	 * 根据任务ID取得任务基本信息
	 * 
	 * @param id    任务Id
	 */
	public function get($id) {
		assert(is_int($id));
		$sql = 'SELECT oa_task.*, oa_user.`name` AS creator_name, oa_process_node.`name` AS node_name
				FROM oa_task
				JOIN oa_user ON oa_task.creator = oa_user.id 
				LEFT JOIN oa_process_node 
					ON oa_task.process_id = oa_process_node.process_id
						AND oa_task.current_node = oa_process_node.node_key
				WHERE oa_task.id = ?';
		return $this->conn->query($sql, $id)->getFirst();
	}
	
	/**
	 * 检查任务是否可被创建
	 * @param int $userId 执行操作的用户ID
	 * @param int $processId 创建任务依据的流程
	 */
	public function checkTaskCreatable($userId, $processId) {
		assert(is_int($userId) && is_int($processId));
		$processModel = new ProcessModel();
		if($processModel->checkProcessAccessByUser($userId, $processId, 2)) {
			if($processModel->get($processId)->status == 1) {
				return TRUE;
			}
		}
		return FALSE;
	}
 
	/**
	 * 根据查询参数读取一个任务列表
	 * @param string $query 查询语句
	 * @param array $args 参数列表
	 * @param array $search 查询参数
	 * @param int $pageSize 分页参数 - 每页显示的记录数, 0为不分页
	 * @param int $pageNum 分页参数 -  要显示的页码
	 * @param int $rowCount 分页参数 - 记录总数
	 * @param int $pageCount 分页参数 - 总页数
	 */
	public function getList($query, $args = NULL, $search = NULL
					,  $pageSize = 0, $pageNum = 1, &$rowCount = NULL, &$pageCount = NULL) {
		if($search) {
			if(preg_match('/^\d+$/', $search['task_id'])) {
				$query .= ' AND oa_task.id = :taskid';
				$args[':taskid'] =  intval($search['task_id']);
			}
			if(!empty($search['creator_name'])) {
				$query .= ' AND oa_user.`name` = :creator_name';
				$args[':creator_name'] =  $search['creator_name'];
			}
			if(!empty($search['keyword'])) {
				$query .= ' AND oa_task.title LIKE :keywords';
				$args[':keywords'] =  '%'.$search['keywords'].'%';
			}
			if($search['current_status'] > -1) {
				$query .= ' AND oa_task.current_status = :current_status';
				$args[':current_status'] = $search['current_status'];
			}
		}
		$query .= ' ORDER BY oa_task.time DESC';
		if($pageSize > 0) {
			$count_query = preg_replace('/^SELECT (.+?) FROM/i', 'SELECT COUNT(*) FROM', $query, 1);
			$rowCount = $this->conn->query($count_query, $args)->getValue();
			$pageCount = ceil($rowCount / $pageSize);
			if($pageNum > $pageCount) $pageNum = $pageCount;
			if($pageNum < 1) $pageNum = 1;
			$query .= ' LIMIT :offset, :limit';
			$args[':offset'] = ($pageNum - 1) * $pageSize;
			$args[':limit'] = $pageSize;
		}
		return $this->conn->query($query, $args)->getAll();
	}
	
	/**
	 * 根据用户ID取得待办任务
	 * @param int $uid 
	 */
	public function getTodoList($uid, $search = NULL, $pageSize = 0, $pageNum = 1, &$rowCount = NULL, &$pageCount = NULL) {
		assert(is_int($uid));
		$sql = 'SELECT DISTINCT oa_task.*, oa_process.`name` AS process_name, oa_user.`name` AS creator_name,
					oa_process_node.`name` AS current_node_name
				FROM oa_task
				LEFT JOIN oa_process_node
					ON oa_task.process_id = oa_process_node.process_id
						AND oa_task.current_node = oa_process_node.node_key
				JOIN oa_user_role_relation 
					ON oa_user_role_relation.role_id = oa_process_node.actor
				LEFT JOIN oa_user
					ON oa_task.creator = oa_user.id
				JOIN oa_process
					ON oa_task.process_id = oa_process.id
				WHERE 
					oa_task.current_node != \'start\'
					AND oa_user_role_relation.user_id =  :uid';
		$args = array(':uid' => $uid);
		return $this->getList($sql, $args, $search, $pageSize, $pageNum, $rowCount, $pageCount);
	}
	
	/**
	 * 取得我创建的任务列表
	 * @param type $uid
	 * @param type $search
	 */
	public function getMyList($uid, $search = NULL, $pageSize = 0, $pageNum = 1, &$rowCount = NULL, &$pageCount = NULL) {
		assert(is_int($uid));
		$sql = 'SELECT DISTINCT oa_task.*, oa_process.`name` AS process_name, oa_user.`name` AS creator_name,
					oa_process_node.`name` AS current_node_name
				FROM oa_task
				LEFT JOIN oa_process_node
					ON oa_task.process_id = oa_process_node.process_id
						AND oa_task.current_node = oa_process_node.node_key
				LEFT JOIN oa_user
					ON oa_task.creator = oa_user.id
				JOIN oa_process
					ON oa_task.process_id = oa_process.id
				WHERE 
					oa_task.creator = :uid';
		$args = array(':uid' => $uid);
		return $this->getList($sql, $args, $search, $pageSize, $pageNum, $rowCount, $pageCount);
	}
	
	/**
	 * 取得历史任务
	 * @param type $uid
	 * @param type $search
	 */
	public function getHistoryList($uid, $search = NULL, $pageSize = 0, $pageNum = 1, &$rowCount = NULL, &$pageCount = NULL) {
		assert(is_int($uid));
		$sql = 'SELECT DISTINCT oa_task.*, oa_process.`name` AS process_name, oa_user.`name` AS creator_name,
					oa_process_node.`name` AS current_node_name
				FROM oa_task
				LEFT JOIN oa_process_node
					ON oa_task.process_id = oa_process_node.process_id
						AND oa_task.current_node = oa_process_node.node_key
				LEFT JOIN oa_user
					ON oa_task.creator = oa_user.id
				JOIN oa_process
					ON oa_task.process_id = oa_process.id
				WHERE 
					oa_task.id IN (SELECT task_id FROM oa_task_procedure WHERE actor = :uid)';
		$args = array(':uid' => $uid);
		return $this->getList($sql, $args, $search, $pageSize, $pageNum, $rowCount, $pageCount);
	}
	
	

	/**
	 * 创建一个任务
	 * @param int $creator 创建者ID
	 * @param int $processId 流程ID
	 * @param int $title 任务标题
	 * @return int 新建任务的ID
	 */
	public function create($creator, $processId, $title = NULL) {
		assert(is_int($creator) && is_int($processId));
		if(empty($title)) {
			$title = '新建任务'.date('YmdHis').  rand(100, 999);
		}
		$taskInfo = array(
			'process_id' => $processId,
			'creator' => $creator,
			'time' => time(),
			'title' => $title,
			'variable' => serialize(array()),
			'current_status' => 0,
			'current_node' => 'start'
		);
		return $this->conn->insert('oa_task', $taskInfo);
	}
	
	/**
	 * 检查特定用户对特定任务是否有查看权限
	 * @param int $taskId
	 * @param int $uid 
	 */
	public function checkViewPermission($taskId, $uid) {
		assert(is_int($taskId) && is_int($uid));
		$taskInfo = $this->get($taskId);
		if(empty($taskInfo) || empty($uid)) {
			return FALSE;
		}
		if($taskInfo->creator == $uid) {
			return TRUE;
		}
		$sql = 'SELECT COUNT(*)
				FROM oa_user_role_relation, oa_process_node
				WHERE oa_user_role_relation.role_id = oa_process_node.actor
					AND oa_user_role_relation.user_id = ?
					AND oa_process_node.process_id = ?';
		$taskProcessId = intval($taskInfo->process_id);
		$result = $this->conn->query($sql, $uid, $taskProcessId)->getValue();
		if($result > 0) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 检查某一用户是否有处理当前任务的权限
	 * @param int $taskId 要处理的流程
	 * @param int $uid 处理用户的ID
	 * @return boolean 
	 */
	public function checkDisposePermission($taskId, $uid) {
		assert(is_int($taskId) && is_int($uid));
		$taskInfo = $this->get($taskId);
		if(empty($taskInfo) || empty($uid)) {
			return FALSE;
		}
		if($taskInfo->current_node == 'end') {
			return FALSE;
		} elseif ($taskInfo->current_node == 'start') {
			if($taskInfo->creator == $uid) {
				return TRUE;
			}
			return FALSE;
		} else {
			$sql = 'SELECT COUNT(*)
					FROM oa_user_role_relation, oa_process_node
					WHERE oa_user_role_relation.role_id = oa_process_node.actor
						AND oa_user_role_relation.user_id = ?
						AND oa_process_node.process_id = ?
						AND oa_process_node.node_key = ?';
			$taskProcessId = intval($taskInfo->process_id);
			$taskNodeKey = $taskInfo->current_node;
			$result = $this->conn->query($sql, $uid, $taskProcessId, $taskNodeKey)->getValue();
			if($result > 0) {
				return TRUE;
			}
			return FALSE;
		}
	}
	
	/**
	 * 取得任务处理日志
	 * @param int $taskId 
	 * @return mixed
	 */
	public function getTaskLogList($taskId) {
		assert(is_int($taskId));
		$sql = 'SELECT oa_task_procedure.*, oa_process_node.`name` AS node_name,  oa_user.`name` AS actor_name
				FROM oa_task_procedure, oa_process_node, oa_user 
				WHERE oa_task_procedure.process_node_id = oa_process_node.id
					AND oa_task_procedure.actor = oa_user.id
					AND task_id = ?';
		return $this->conn->query($sql, $taskId)->getAll();
	}

	/**
	 * 执行任务处理
	 * 
	 * @param taskId    要处理的任务Id
	 * @param actorId    处理者Id
	 * @param result    处理结果
	 * @param message    处理意见
	 */
	public function dispose($taskId, $actorId, $result, $jumpto = NULL, $message = NULL) {
		assert(is_int($taskId) && is_int($actorId));
		if($jumpto) assert (is_int ($jumpto));

		$this->conn->beginTransaction();
		$processModel = new ProcessModel();
		$taskInfo = $this->get($taskId);
		$taskVariable = unserialize($taskInfo->variable);	// 任务变量
		$processNodeInfo = $processModel->getNode(intval($taskInfo->process_id), $taskInfo->current_node);
		if($result == 'complete') {			// 处理 完成处理 将会解析后继表达式选择下一节点
			$nextNodeKey = $this->expressionParser($processNodeInfo->goto_exp, $taskId, $taskVariable);
		} elseif($result == 'return') {		// 处理 返回 将会取得流程上一步的节点作为下一节点
			$sql = 'SELECT oa_process_node.node_key 
					FROM oa_task_procedure, oa_process_node 
					WHERE oa_task_procedure.process_node_id = oa_process_node.id
					ORDER BY oa_task_procedure.`time` DESC
					LIMIT 1';
			$nextNodeKey = $this->conn->query($sql)->getValue();
		} elseif($result == 'jump') {		// 处理 跳转 根据提供的转跳ID取得下一节点
			$sql = 'SELECT node_key FROM oa_task, oa_process_node
					WHERE oa_process_node.process_id = oa_task.process_id
						AND oa_task.id = ? AND oa_process_node.id = ?';
			$nextNodeKey = $this->conn->query($sql, $taskId, $jumpto)->getValue();
		} else {							// 处理 关闭 下一节点为end
			$nextNodeKey = 'end';
		}
		
		// 如果解析出现错误, 或者解析出来的节点不存在, 则回滚事务, 返回FALSE
		if(empty($nextNodeKey)) {
			$sql = 'SELECT COUNT(*) FROM oa_process_node, oa_task 
					WHERE oa_process_node.process_id = oa_task.process_id
						AND oa_task.id = ? AND oa_process_node.node_key = ?';
			if($this->conn->query($sql, $taskId, $nextNodeKey)->getValue() == 0) { 
				$this->conn->rollBack();
				return FALSE;
			}
		}
		
		// 如果下一节点为end, 需要特殊处理任务状态
		$currentStatus = 1;
		if($nextNodeKey == 'end') {
			$currentStatus = 2;
		}
		
		// 记录处理过程
		$taskProcedure = array(
			'task_id' => $taskId,
			'process_node_id' => intval($processNodeInfo->id),
			'actor' => $actorId,
			'time' => time(),
			'result' => $result,
			'message' => $message
		);
		$this->conn->insert('oa_task_procedure', $taskProcedure);
		
		// 完成后设置新的当前节点
		$this->conn->update('oa_task', array('current_node' => $nextNodeKey
							, 'current_status'=> $currentStatus
							, 'variable'=> serialize($taskVariable))
							, 'id = :task_id', array(':task_id' => $taskId));
		
		// 提交事务
		if($this->conn->commit()) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 后继节点表达式解析器
	 * @param string $exp 
	 * @param int $taskId 
	 */
	private function expressionParser($exp, $taskId, &$taskVariable) {
		assert(is_int($taskId));
		$exp = preg_replace_callback('/(IF|ELSEIF)\s+?(.+)\s+?THEN/is', function($match){
			$result = (strtoupper($match[1]) == 'IF') ? 'if' : 'elseif';
			$val_exp = preg_replace_callback('/([#%])([a-zA-Z][\w.]+)/i', function($match){
				$vals = explode('.', $match[2]);
				array_walk($vals, 'trim');
				$val_exp = '[\''.implode("']['", $vals)."']";
				if($match[1] == '#') {
					return '$formData'.$val_exp;
				} else {
					return '$variable'.$val_exp;
				}
			}, $match[2]);
			return $result.'('.$val_exp.'): ';
		}, $exp);
		$exp = preg_replace('/ELSE/is', 'else:', $exp);
		$exp = preg_replace('/END\s+IF/is', 'endif;', $exp);
		$exp = preg_replace('/GO (\w+)/is', 'return "$1";', $exp);
		
		$result = NULL;
		$formModel = new FormModel();
		$formData = $formModel->getTaskFormData($taskId);
		eval('$result = call_user_func(function($formData, &$taskVariable){ '
				.$exp.' }, $formData, array(&$taskVariable));');
		return $result;
	}

}
/* EOF */