<?php
namespace plugin\field;

/**
 * 多行文本域字段
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class TextAreaField extends Field {

	public function generate() {
		$html = '<textarea class="textarea long" name="'.$this->getFieldKey().'">'.$this->getDefault().'</textarea>';
		return $html;
	}

	public function getErrorMessage() {
		return $this->getName()."需要正确填写";
	}

}
/* EOF */