<?php

namespace model;

use lib\core\Model;
use lib\db\DBFactory;

/**
 * 表单类
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class FormModel extends Model {

	/**
	 * 数据库连接对象
	 * @var \lib\db\Connection
	 */
	private $conn = NULL;

	public function __construct() {
		parent::__construct();
		$this->conn = DBFactory::getInstance()->getConnection();
	}

	/**
	 * 取得表单信息
	 * 
	 * @param id    要取得信息的表单Id
	 */
	public function get($id) {
		assert(is_int($id));
		$sql = 'SELECT * FROM oa_form WHERE id = ?';
		return $this->conn->query($sql, $id)->getFirst();
	}

	/**
	 * 根据流程Id取得表单信息列表
	 * 
	 * @param processId    流程ID
	 */
	public function getList($processId) {
		assert(is_int($processId));
		$sql = 'SELECT * FROM oa_form WHERE process_id = ?';
		return $this->conn->query($sql, $processId)->getAll();
	}

	/**
	 * 新建表单并返回新表单的Id
	 * 
	 * @param info    表单信息数组
	 */
	public function add($info) {
		assert(is_array($info));
		return $this->conn->insert('oa_form', $info);
	}
	
	/**
	 * 检查同一流程下的表单标识符是否可用
	 * @param type $processId 流程ID
	 * @param type $formKey 表单标识符
	 * @return boolean 
	 */
	public function checkFormKeyAvailable($processId, $formKey) {
		assert(is_int($processId) && is_string($formKey));
		$sql = 'SELECT COUNT(*) FROM oa_form WHERE process_id = ? AND form_key = ?';
		$count = $this->conn->query($sql, $processId, $formKey)->getValue();
		if($count > 0) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * 更新一个表单信息
	 * 
	 * @param id    表单Id
	 * @param info    表单信息
	 */
	public function update($id, $info) {
		assert(is_int($id) && is_array($info));
		return $this->conn->update('oa_form', $info, 'id = :formid', array(':formid' => $id));
	}

	/**
	 * 删除一个表单
	 * 
	 * @param id    要删除的ID
	 */
	public function delete($id) {
		assert(is_int($id));
		return (bool)$this->conn->delete('oa_form', 'id = ?', $id);
	}

	/**
	 * 读取字段列表
	 * @param int $fid 表单ID
	 * @return array
	 */
	public function getFieldList($fid) {
		assert(is_int($fid));
		$sql = 'SELECT * FROM oa_form_field WHERE form_id = ?';
		return $this->conn->query($sql, $fid)->getAll();
	}

	/**
	 * 添加一个表单字段
	 * @param info 字段信息
	 */
	public function addField($info) {
		assert(is_array($info));
		return $this->conn->insert('oa_form_field', $info);
	}
	
	/**
	 * 检查同表单中指定的字段标识符是否可用
	 * @param int $formId 表单ID
	 * @param string $fieldKey 字段标识符
	 * @return bool
	 */
	public function checkFieldKeyAvailable($formId, $fieldKey) {
		assert(is_int($formId) && is_string($fieldKey));
		$sql = 'SELECT COUNT(*) FROM oa_form_field WHERE form_id = ? AND field_key = ?';
		$count = $this->conn->query($sql, $formId, $fieldKey)->getValue();
		if($count > 0) {
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * 更新字段信息
	 * @param int $id
	 * @param array $info
	 * @return bool 
	 */
	public function updateField($id, $info) {
		assert(is_int($id) && is_array($info));
		return (bool)$this->conn->update('oa_form_field', $info, 'id = :fieldid', array(':fieldid'=>$id));
	}

	/**
	 * 删除表单字段
	 * 
	 * @param fieldId 字段ID
	 * @return bool
	 */
	public function deleteField($fieldId) {
		return (bool) $this->conn->delete('oa_form_field', 'id = ?', $fieldId);
	}
	
	/**
	 * 取得一个表单的值列表
	 * @param int $taskId
	 * @param int $formId 
	 */
	public function getFormData($taskId, $formId) {
		assert(is_int($taskId) && is_int($formId));
		$sql = 'SELECT * FROM oa_task_data WHERE task_id = ? AND form_id = ?';
		$rs = $this->conn->query($sql, $taskId, $formId);
		$result = array();
		foreach($rs as $row) {
			$result[$row->field_key] = $row->field_value;
		}
		return $result;
	}
	
	/**
	 * 取得任务相关联的所有表单的数据, 以二维数组形式返回
	 * @param int $taskId
	 * @return array 
	 */
	public function getTaskFormData($taskId) {
		assert(is_int($taskId));
		$sql = 'SELECT process_id FROM oa_task WHERE id = ?';
		$processId = intval($this->conn->query($sql, $taskId)->getValue());
		$formList = $this->getList($processId);
		$data = array();
		foreach($formList as $form) {
			$data[$form->form_key] = $this->getFormData($taskId, intval($form->id));
		}
		return $data;
	}
	
	/**
	 * 将一个表单提交保存
	 * @param int $taskId
	 * @param int $formId
	 * @param array $data
	 * @return boolean 
	 */
	public function setFormData($taskId, $formId, $data) {
		assert(is_int($taskId) && is_int($formId));
		$this->conn->beginTransaction();
		foreach ($data as $key => $value) {
			$this->setFieldData($taskId, $formId, $key, $value);
		}
		$this->conn->commit();
		return TRUE;
	}
	
	/**
	 * 设置表单字段数据
	 * @param int $taskId 任务ID
	 * @param int $formId 表单ID
	 * @param string $key 字段标识
	 * @param string $value 字段值
	 */
	public function setFieldData($taskId, $formId, $key, $value) {
		assert(is_int($taskId) && is_int($formId));
		$this->getLog()->debug($taskId);
		$this->getLog()->debug($formId);
		$sql = 'SELECT COUNT(*) FROM oa_task_data WHERE task_id = ? AND form_id = ? AND field_key = ?';
		$count = $this->conn->query($sql, $taskId, $formId, $key)->getValue();
		if($count) {
			$this->conn->update('oa_task_data', array('field_value' => $value)
					, 'task_id = :tid AND form_id = :fid AND field_key = :fk'
					,  array(':tid' => $taskId, ':fid' => $formId, ':fk' => $key));
		} else {
			$this->conn->insert('oa_task_data', array(
				'task_id' => $taskId,
				'form_id' => $formId,
				'field_key' => $key,
				'field_value' => $value
			));
		}
	}

}

/* EOF */