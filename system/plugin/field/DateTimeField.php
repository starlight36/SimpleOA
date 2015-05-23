<?php
namespace plugin\field;

/**
 * 日期时间字段
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class DateTimeField extends Field {

	public function generate() {
		$html = '<input class="easyui-datetimebox" style="width:302px;" type="text" name="'
				.$this->getFieldKey().'" value="'.$this->getDefault().'" />';
		return $html;
	}

	public function getErrorMessage() {
		return $this->getName()."需要正确填写";
	}

}
/* EOF */