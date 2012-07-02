<?php
namespace plugin\field;

/**
 * 富文本字段
 * @author starlight36
 * @version 1.0
 * @created 05-四月-2012 15:00:36
 */
class RichTextField extends Field {

	public function generate() {
		$html = '<textarea id="richtextField_'.$this->getFieldKey().'" class="textarea long" name="'
				.$this->getFieldKey().'" style="width:550px;height:220px;">'.$this->getDefault().'</textarea>'
				.'<script type="text/javascript">$(function($){$("#richtextField_'.$this->getFieldKey()
				.'").xheditor({tools:"simple"});});</script>';
		return $html;
	}

	public function getErrorMessage() {
		return $this->getName()."需要正确填写";
	}

}
/* EOF */