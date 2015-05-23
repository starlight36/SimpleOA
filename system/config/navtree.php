<?php
/**
 * 导航树配置文件
 */
return array(
	array(
		'id' => 'workspace',
		'text' => '工作台',
		'iconCls' => 'icon-workspace',
		'order' => 1,
		'attributes' => array(
			'url' => NULL
		),
		'children' => array(
			array(
				'id' => 'desktop',
				'text' => '我的桌面',
				'iconCls' => 'icon-desktop',
				'order' => 1,
				'attributes' => array(
					'url' => lib\util\Url::get('Home', 'desktop')
				),
			),
			
			array(
				'id' => 'task',
				'text' => '任务管理',
				'iconCls' => 'icon-task',
				'order' => 2,
				'attributes' => array(
					'url' => NULL
				),
				'children' => array(
					array(
						'id' => 'task-todo',
						'text' => '待办任务',
						'iconCls' => 'icon-task-todo',
						'order' => 1,
						'attributes' => array(
							'url' => lib\util\Url::get('Task')
						),
					),
					array(
						'id' => 'task-mine',
						'text' => '我的任务',
						'iconCls' => 'icon-task-mine',
						'order' => 2,
						'attributes' => array(
							'url' => lib\util\Url::get('Task', 'myTaskList')
						),
					),
					array(
						'id' => 'task-history',
						'text' => '历史任务',
						'iconCls' => 'icon-task-history',
						'order' => 3,
						'attributes' => array(
							'url' => lib\util\Url::get('Task', 'historyTaskList')
						),
					),
				),
			),
			
			array(
				'id' => 'process',
				'text' => '办理业务',
				'iconCls' => 'icon-process',
				'order' => 3,
				'children' => NULL,
				'attributes' => array(
					'url' => NULL
				),
			),
			
			array(
				'id' => 'manage',
				'text' => '管理中心',
				'iconCls' => 'icon-manage',
				'order' => 4,
				'attributes' => array(
					'url' => NULL
				),
				'children' => array(
					array(
						'id' => 'manage-org',
						'text' => '组织管理',
						'iconCls' => 'icon-manage-org',
						'order' => 1,
						'attributes' => array(
							'url' => lib\util\Url::get('Organization')
						),
					),
					array(
						'id' => 'manage-process',
						'text' => '流程管理',
						'iconCls' => 'icon-manage-process',
						'order' => 2,
						'attributes' => array(
							'url' => lib\util\Url::get('Process')
						),
					),
				),
			),
		),
	)
);