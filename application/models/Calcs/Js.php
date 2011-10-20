<?php 

class Application_Model_Calcs_Js extends Application_Model_Calcs {
	
	/**
	 * Function to carry out the calculations and return the result.
	 * For documentation and test cases of this functionality, check the PHP version in th Application_Model_Calcs_Php class
	 *
	 * @param string	$result		The value in the screen
	 * @param array 	$data		The button key plus value
	 * @return
	 */
	public function runCalc($result, $data){
		$this->view->clearLabel = 'AC';
		$this->view->includeJQuery = true;
		$this->view->isForm = false;
	}

}
