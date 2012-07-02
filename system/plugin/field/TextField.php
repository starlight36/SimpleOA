<?php
namespace plugin\field;

/**
 * 单行文本域字段
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class TextField extends Field {

	public function generate() {
		$html = '<input type="text" class="textfield long" name="'.$this->getFieldKey().'"'
			.' value = "'.$this->getDefault().'" />';
		return $html;
	}

	public function getErrorMessage() {
		return $this->getName()."需要正确填写";
	}

}
/* EOF */