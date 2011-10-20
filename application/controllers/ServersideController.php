<?php

class ServersideController extends Zend_Controller_Action {

	public function init(){
		
	}
	
	public function indexAction(){
		$data = $this->_request->getPost('data');
		$result = $this->_request->getPost('result');
		$PhpCalc = new Application_Model_Calcs_Php();
		$PhpCalc->runCalc($result, $data);
		
		$this->includeJQuery = false;
	}


}