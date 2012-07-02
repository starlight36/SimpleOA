<?php

namespace controller;

use lib\core\Action;
use model\FormModel;
use model\TaskModel;
use plugin\field\Field;


/**
 * 表单管理控制器类
 * @author starlight36
 * @version 1.0
 * @created 06-四月-2012 10:53:59
 */
class FormAction extends Action {
	
	/**
	 * 表单模型对象
	 * @var FormModel 
	 */
	private $formModel = NULL;
	
	/**
	 * 构造函数 
	 */
	public function __construct() {
		parent::__construct();
		$this->formModel = new FormModel();
	}

	/**
	 * 默认执行方法
	 */
	public function execute() {
		
	}

	/**
	 * 生成表单列表 
	 */
	public function getFormListExecute() {
		$id = intval($this->getQuery('pid'));
		$formList = $this->formModel->getList($id);
		$this->render('json', $formList);
	}
	
	/**
	 * 生成字段列表 
	 */
	public function getFiledListExecute() {
		$id = intval($this->getQuery('id'));
		$fieldList = $this->formModel->getFieldList($id);
		$this->render('json', $fieldList);
	}
	
	/**
	 * 编辑表单 
	 */
	public function editExecute() {
		$id = intval($this->getQuery('id'));
		if($id) {
			$formInfo = $this->formModel->get($id);
		}
		$this->assign('formInfo', $formInfo);
		$this->render('phtml', 'form/main.phtml');
	}
	
	/**
	 * 保存表单信息 
	 */
	public function saveExecute() {
		$id = intval($this->getForm('id'));
		$formInfo = array(
			'name' => $this->getForm('name'),
			'form_key' => $this->getForm('form_key'),
			'description' => $this->getForm('description'),
			'process_id' => $this->getForm('process_id')
		);
		if($id) {
			$this->formModel->update($id, $formInfo);
		} else {
			if(!$this->formModel->checkFormKeyAvailable(
					intval($formInfo['process_id']), $formInfo['form_key'])) {
				$this->assign('code', FALSE);
				$this->assign('msg', '同一流程下的表单标识符不能重复.');
				$this->render('json');
				return;
			}
			$id = $this->formModel->add($formInfo);
		}
		$this->assign('code', TRUE);
		$this->assign('form_id', $id);
		$this->render('json');
	}
	
	/**
	 * 删除表单 
	 */
	public function deleteExecute() {
		$id = intval($this->getForm('id'));
		$result = $this->formModel->delete($id);
		$this->assign('code', $result);
		$this->render('json');
	}
	
	/**
	 * 保存字段 
	 */
	public function saveFieldExecute() {
		$id = intval($this->getForm('id'));
		$fieldInfo = array(
			'name' => $this->getForm('name'),
			'description' => $this->getForm('description'),
			'form_id' => $this->getForm('form_id'),
			'field_key' => $this->getForm('field_key'),
			'type' => $this->getForm('type'),
			'config' => $this->getForm('config'),
			'required' => $this->getForm('required'),
			'default_value' => $this->getForm('default_value'),
			'validator' => $this->getForm('validator')
		);
		if($id) {
			$this->formModel->updateField($id, $fieldInfo);
		} else {
			if(!$this->formModel->checkFieldKeyAvailable(
					intval($fieldInfo['form_id']), $fieldInfo['field_key'])) {
				$this->assign('code', FALSE);
				$this->assign('msg', '同一表单下的字段标识符不能重复.');
				$this->render('json');
				return;
			}
			$this->formModel->addField($fieldInfo);
		}
		$this->assign('code', TRUE);
		$this->render('json');
	}
	
	/**
	 * 删除一个字段 
	 */
	public function deleteFieldExecute() {
		$id = intval($this->getForm('id'));
		$result = $this->formModel->deleteField($id);
		$this->assign('code', $result);
		$this->render('json');
	}
	
	/**
	 * 显示用于填写表单 
	 */
	public function fillExecute() {
		$id = intval($this->getQuery('id'));
		$tid = intval($this->getQuery('tid'));
		$uid = intval($this->getSession()->get('userInfo')->id);
		$taskModel = new TaskModel();
		if($taskModel->checkDisposePermission($tid, $uid)) {
			$formInfo = $this->formModel->get($id);
			$fieldInfoList = $this->formModel->getFieldList($id);
			$fieldList = $this->buildFieldList($fieldInfoList);
			$formData = $this->formModel->getFormData($tid, $id);
			foreach($fieldList as $field) {
				$field->setDefault($formData[$field->getFieldKey()]);
			}
			$this->assign('formInfo', $formInfo);
			$this->assign('fieldList', $fieldList);
		}
		$this->render('phtml', 'form/fill.phtml');
	}
	
	/**
	 * 保存表单内容 
	 */
	public function saveFillExecute() {
		$fid = intval($this->getForm('fid'));
		$tid = intval($this->getForm('tid'));
		$uid = intval($this->getSession()->get('userInfo')->id);
		$taskModel = new TaskModel();
		if($taskModel->checkDisposePermission($tid, $uid)) {
			$fieldInfoList = $this->formModel->getFieldList($fid);
			$fieldList = $this->buildFieldList($fieldInfoList);
			$data = array();
			foreach($fieldList as $field) {
				$field->setValue($this->getForm($field->getFieldKey()));
				if(!$field->validation()) {
					$this->assign('code', FALSE);
					$this->assign('msg', $field->getErrorMessage());
					$this->render('json');
					return;
				}
				$data[$field->getFieldKey()] = $field->getValue();
			}
			if($this->formModel->setFormData($tid, $fid, $data)) {
				$this->assign('code', TRUE);
			} else {
				$this->assign('msg', '保存数据出错.');
				$this->assign('code', FALSE);
			}
		}
		$this->render('json');
	}
	
	/**
	 * 查看表单内容 
	 */
	public function viewExecute() {
		$id = intval($this->getQuery('id'));
		$tid = intval($this->getQuery('tid'));
		$uid = intval($this->getSession()->get('userInfo')->id);
		$taskModel = new TaskModel();
		if($taskModel->checkViewPermission($tid, $uid)) {
			$formInfo = $this->formModel->get($id);
			$fieldInfoList = $this->formModel->getFieldList($id);
			$formData = $this->formModel->getFormData($tid, $id);
			$this->assign('formInfo', $formInfo);
			$this->assign('fieldInfoList', $fieldInfoList);
			$this->assign('formData', $formData);
		}
		if((bool)$this->getQuery('print')) {
			$this->render('phtml', 'form/print.phtml');
		} else {
			$this->render('phtml', 'form/view.phtml');
		}
	}
	
	/**
	 * 根据表字段单信息列表构建表单字段对象数组
	 * @param mixed $fieldInfoList 
	 */
	private function buildFieldList($fieldInfoList) {
		$fieldList = array();
		$config = $this->getConfig()->get('fieldtype');
		foreach($fieldInfoList as $fieldInfo) {
			$className = $config[$fieldInfo->type]['class'];
			$field = new $className();
			$field->setName($fieldInfo->name);
			$field->setDescription($fieldInfo->description);
			$field->setFieldKey($fieldInfo->field_key);
			$field->setConfig($fieldInfo->config);
			$field->setDefault($fieldInfo->default_value);
			$field->setValidator($fieldInfo->validator);
			$field->setRequired((boolean)$fieldInfo->required);
			$fieldList[$fieldInfo->field_key] = $field;
		}
		return $fieldList;
	}
	
}

/* EOF */