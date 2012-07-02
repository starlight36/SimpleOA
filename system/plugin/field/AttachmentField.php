<?php
namespace plugin\field;

/**
 * 附件字段
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class AttachmentField extends Field {

	public function generate() {
		
	}

	public function getErrorMessage() {
		return $this->getName()."需要正确填写";
	}

}
/* EOF */