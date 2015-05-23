<?php

namespace controller;

use lib\core\Action;
use model\TaskModel;
use model\FormModel;
use model\ProcessModel;

/**
 * 任务控制器
 * @author starlight36
 * @version 1.0
 * @created 06-四月-2012 10:53:59
 */
class TaskAction extends Action {
	
	/**
	 * 任务模型对象
	 * @var TaskModel
	 */
	private $taskModel = NULL;
	
	/**
	 * 构造方法 
	 */
	public function __construct() {
		parent::__construct();
		$this->taskModel = new TaskModel();
	}

	/**
	 * 待办任务 
	 */
	public function execute() {
		$this->assign('listType', 'todo');
		$this->render('phtml', 'task/list.phtml');
	}
	
	/**
	 * 我的任务列表 
	 */
	public function myTaskListExecute() {
		$this->assign('listType', 'my');
		$this->render('phtml', 'task/list.phtml');
	}
	
	/**
	 * 历史任务列表 
	 */
	public function historyTaskListExecute() {
		$this->assign('listType', 'history');
		$this->render('phtml', 'task/list.phtml');
	}
	
	/**
	 * 取得任务列表 
	 */
	public function getListExecute() {
		$pageNum = intval($this->getForm('page', 1));
		$pageSize = intval($this->getForm('rows', 10));
		$search = array(
			'task_id' => $this->getForm('task_id'),
			'creator_name' => $this->getForm('creator_name'),
			'keywords' => $this->getForm('keywords'),
			'current_status' => intval($this->getForm('current_status', -1))
		);
		$uid = intval($this->getSession()->get('userInfo')->id);
		$pageCount = $rowCount = 0;
		if($this->getQuery('type') == 'todo') {
			$taskList = $this->taskModel->getTodoList($uid, $search, $pageSize, $pageNum, $rowCount, $pageCount);
		} elseif($this->getQuery('type') == 'my') {
			$taskList = $this->taskModel->getMyList($uid, $search, $pageSize, $pageNum, $rowCount, $pageCount);
		} else {
			$taskList = $this->taskModel->getHistoryList($uid, $search, $pageSize, $pageNum, $rowCount, $pageCount);
		}
			
		$this->assign('total', $rowCount);
		$this->assign('rows', $taskList);
		$this->render('json');
	}

	/**
	 * 创建任务 
	 */
	public function createExecute() {
		$pid = intval($this->getForm('id'));
		$uid = intval($this->getSession()->get('userInfo')->id);
		if($this->taskModel->checkTaskCreatable($uid, $pid)) {
			$taskId = $this->taskModel->create($uid, $pid, $this->getForm('title'));
			$this->assign('code', TRUE);
			$this->assign('task_id', $taskId);
		} else {
			$this->assign('code', FALSE);
		}
		$this->render('json');
	}
	
	/**
	 * 查看任务 
	 */
	public function viewExecute() {
		$id = intval($this->getQuery('id'));
		$uid = intval($this->getSession()->get('userInfo')->id);
		if($this->taskModel->checkViewPermission($id, $uid)) {
			if($this->taskModel->checkDisposePermission($id, $uid)) {
				$this->assign('disposePermission', TRUE);
			}
			$formModel = new FormModel();
			$processModel = new ProcessModel();
			$taskInfo = $this->taskModel->get($id);
			$processInfo = $processModel->get(intval($taskInfo->process_id));
			$processNodeInfo = $processModel->getNode(intval($taskInfo->process_id), $taskInfo->current_node);
			$processNodeList = $processModel->getNodeList(intval($taskInfo->process_id));
			$logList = $this->taskModel->getTaskLogList($id);
			$formList = $formModel->getList(intval($taskInfo->process_id));
			$this->assign('taskInfo', $taskInfo);
			$this->assign('processInfo', $processInfo);
			$this->assign('processNodeInfo', $processNodeInfo);
			$this->assign('logList', $logList);
			$this->assign('formList', $formList);
			$this->assign('processNodeList', $processNodeList);
		}
		$this->render('phtml', 'task/view.phtml');
	}
	
	/**
	 *  办理任务页面
	 */
	public function disposeExecute() {
		$id = intval($this->getForm('id'));
		$uid = intval($this->getSession()->get('userInfo')->id);
		if($this->taskModel->checkDisposePermission($id, $uid)) {
			$result = $this->getForm('result');
			$message = $this->getForm('message');
			$jumpto = intval($this->getForm('jumpto'));
			if($this->taskModel->dispose($id, $uid, $result, $jumpto, $message)) {
				$this->assign('code', TRUE);
			} else {
				$this->assign('code', FALSE);
			}
		} else {
			$this->assign('code', FALSE);
		}
		$this->render('json');
	}

}

/* EOF */