<?php
/**
 * 字段类型, 映射系统内已经安装的字段类型
 */
return array(
	'text' => array(
		'name' => '单行文本域',
		'class' => '\\plugin\\field\\TextField'
	),
	'textarea' => array(
		'name' => '多行文本域',
		'class' => '\\plugin\\field\\TextAreaField'
	),
	'radio' => array(
		'name' => '单项选择域',
		'class' => '\\plugin\\field\\RadioField'
	),
	'checkbox' => array(
		'name' => '多项选择域',
		'class' => '\\plugin\\field\\CheckBoxField'
	),
	'richtext' => array(
		'name' => '富文本编辑域',
		'class' => '\\plugin\\field\\RichTextField'
	),
	'date' => array(
		'name' => '日期域',
		'class' => '\\plugin\\field\\DateField'
	),
	'datetime' => array(
		'name' => '日期时间域',
		'class' => '\\plugin\\field\\DateTimeField'
	),
	'image' => array(
		'name' => '图像域',
		'class' => '\\plugin\\field\\ImageField'
	),
	'attachment' => array(
		'name' => '附件域',
		'class' => '\\plugin\\field\\AttachmentField'
	),
);
/* EOF */