<?php 

class Application_Model_Calcs {
	
	public function __construct(){
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$this->view = $viewRenderer->view;
	}
	
}