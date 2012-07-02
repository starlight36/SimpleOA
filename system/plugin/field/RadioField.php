<?php
namespace plugin\field;

/**
 * 单项选择字段
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class RadioField extends Field {

	public function generate() {
		$html = '<select class="easyui-combobox" style="width: 302px;" editable="false" name="'
				.$this->getFieldKey().'">';
		if($this->getConfig()) {
			foreach(explode(',', $this->getConfig()) as $val) {
				if(trim($val) == $this->getDefault()) {
					$html .= '<option selected="selected">';
				}else{
					$html .= '<option>';
				}
				$html .= htmlspecialchars(trim($val)).'</option>';
			}
		}
		return $html;
	}

	public function getErrorMessage() {
		return $this->getName()."需要正确填写";
	}

}
/* EOF */