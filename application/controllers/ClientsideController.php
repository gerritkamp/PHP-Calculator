<?php

class ClientsideController extends Zend_Controller_Action {

	public function init(){
		
	}
	
	public function indexAction(){
		$data = $this->_request->getPost('data');
		$result = $this->_request->getPost('result');
		$JsCalc = new Application_Model_Calcs_Js();
		$JsCalc->runCalc($result, $data);
		
		$this->includeJQuery = true;
	}


}