<?php

namespace controller;

use lib\core\Action;
use model\UserModel;
use widget\NavtreeWidget;

/**
 * 主页面控制器类
 * @author starlight36
 * @version 1.0
 * @created 06-四月-2012 10:53:59
 */
class HomeAction extends Action {

	/**
	 * 默认执行方法
	 */
	public function execute() {
		$this->getView()->setPageTitle('工作台');
		$this->render('phtml', 'home/main.phtml');
	}

	/**
	 * 登录页表单
	 */
	public function loginExecute() {
		$this->getView()->setPageTitle('登录系统');
		$this->render('phtml', 'home/login.phtml');
	}

	/**
	 * 登录页提交处理
	 */
	public function doLoginExecute() {
		if(!$this->getRequest()->isPost()){
			$this->getResponse()->stop();
		}
		$account = $this->getForm('account');
		$password = $this->getForm('password');
		$userModel = new UserModel();
		$loginResult = $userModel->login($account, $password);
		if(UserModel::LOGIN_SUCCESS == $loginResult) {
			$userInfo = $userModel->get($account);
			$this->getSession()->put('userInfo', $userInfo);
			$this->assign('redirect', \lib\util\Url::get('Home'));
		}
		$this->assign('code', $loginResult);
		$this->render('json');
	}
	
	/**
	 * 退出登录 
	 */
	public function logoutExecute() {
		$this->getSession()->clear();
		$this->getResponse()->redirect(\lib\util\Url::get('Home', 'login'));
	}
	
	/**
	 * 我的桌面
	 */
	public function desktopExecute() {
		$this->getView()->setPageTitle('我的桌面');
		$this->render('phtml', 'home/desktop.phtml');
	}
	
	/**
	 * 左侧导航树 
	 */
	public function navtreeExecute() {
		if(!$this->getRequest()->isPost()){
			$this->getResponse()->stop();
		}
		$navtreeWidget = new NavtreeWidget();
		$this->render('json', $navtreeWidget->getNavtree());
	}
	
	/**
	 * 测试用的 
	 */
	public function testExecute() {
		$processModel = new \model\ProcessModel();
		var_dump($processModel->getCategoryList());
	}
	
}

/* EOF */