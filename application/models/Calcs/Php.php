<?php 

class Application_Model_Calcs_Php extends Application_Model_Calcs {
	
	/**
	 * Function to carry out the calculations and return the result
	 *
	 * @param string	$result		The value in the screen
	 * @param array 	$data		The button key plus value
	 * @return
	 */
	public function runCalc($result, $data){
		$startTime = microtime(true);
		$ns = new Zend_Session_Namespace('phpcalc');	// activate the session namespace so we can store states and values
		if(!$data){
			$displayResult = 0;
			$clearLabel = 'AC';
			if(isset($ns->d)){unset($ns->d);}
			if(isset($ns->m)){unset($ns->m);}
		} else {
				// capture and filter the submitted values
			$keys = array_keys($data);
			$key = $keys[0];						// this is the type of button that was pressed
			$values = array_keys($data[$key]);
			$value = $values[0];					// the is the value of the button
			$result = strip_tags($result);			// this is the value in the screen
			
			if(!isset($ns->d['clearLabel'])){$clearLabel = 'AC';} else {$clearLabel = $ns->d['clearLabel'];}	// AC = All Clear vs C being just Clear
			$buttonClass = array('mem' => '', 'divide' => '', 'multiply' => '', 'minus' => '', 'plus' => '');	// Used to show buttons in an active state
			switch ($key) {
				case 'num':
					if(isset($ns->d['justPrEq'])){
							// Rigter after eql or cal button. Test "1 + 3 = 8". Result display:8, oldResult:8, nextOp:plus, previousResult:8, newResult:3 
						$displayResult = $value;
						$ns->d['oldResult'] = $value;
					}elseif(!isset($ns->d['previousResult']) && !$ns->d['nextOp']){
							// A new number. Test "1" Result display:1, previousResult: 1
						$displayResult = $value;
						$ns->d['previousResult'] = $displayResult;
					} elseif(isset($ns->d['previousResult']) && !$ns->d['nextOp']){
							// Additional numbers. Test "1 2 3" Result: display 123, prevsiousResult 123
						$displayResult = $ns->d['previousResult'].$value; 	// just concatenate numbers
						$ns->d['previousResult'] = $displayResult;
					} elseif(isset($ns->d['oldResult']) && $ns->d['activeButton']){	
							// New number after an action button was pressed. Test "1 + 3" Result: display:3, oldResult:1, nextOp: plus, previousResult:3, newResult:3
						$displayResult = $value;	// active button, store newvalue
						unset($ns->d['activeButton']);
						$ns->d['previousResult'] = $displayResult;
						$ns->d['newResult'] = $displayResult;
					} elseif(isset($ns->d['oldResult']) && !$ns->d['activeButton']) {
							// Additional numbers after action button. Test: "1 + 34" Result: display:34, oldResult:1, nextOp:plus, previousResult:34, newResult:34 
						if($ns->d['previousResult']){
							$displayResult = $ns->d['previousResult'].$value; // just concatenate number
						} else {
							if($ns->d['previousResult']==='0.'){
								$displayResult = $ns->d['previousResult'].$value; // just concatenate number
							} else {
								$displayResult = $value; // in case previous is 0
							}
						}
						$ns->d['newResult'] = $displayResult;
						$ns->d['previousResult'] = $displayResult;
					}
					if(isset($ns->d['blockCalc'])){unset($ns->d['blockCalc']);}
					$clearLabel = 'C';
				break;
				case 'dot':
					if($ns->d['previousResult']){
						$pos = strpos($ns->d['previousResult'], '.');
						if($pos===false){
								// If number has no decimal yet. Test "3 4 ." Result: previousResult:34. (including dot)
							$displayResult = $ns->d['previousResult'].'.';
						} else {
								// If number has already a decimal. Test "3 4 . 1 ." Result: previousResult:34.1
							$displayResult = $ns->d['previousResult'];	// two dots has no effect
						}
					} else {
							// If no number was present yet. Test "0 ." Result: previousResult:0.1
						$displayResult = '0.';
					}
					$ns->d['previousResult'] = $displayResult;
						// Right after an action button was pressed. Test: "2 + ." Result: oldResult:2, nextOp:plus, previousResult:"0."
					if(isset($ns->d['activeButton'])){unset($ns->d['activeButton']);}
					$clearLabel = 'C';
				break;
				case 'clr':
					if($value=='C'){
							// After any button other than clr itself. Test: "1 C". Result: clearLabel:C, previousResult:0
						$displayResult = '0';
						$ns->d['previousResult'] = 0;
						if(isset($ns->d['newResult'])){$ns->d['newResult'] = 0;}
						$clearLabel = 'AC';
					} else if($value=='AC'){
							// After pageload or after pressing C. Test: "1 C AC". Result: clearLabel:AC
						$displayResult = '0';
						$clearLabel = 'AC';
						unset($ns->d);	// make sure everything is removed from the namespace
					}
				break;
				case 'neg':
					$displayResult = -1 * $result;
					$ns->d['previousResult'] = $displayResult;
					if(isset($ns->d['oldResult']) && !isset($ns->d['newResult'])){
							// Before any act/eql button has been pressed. Test: "2 5 +/-". Result: display:-25, previousResult: -25
						$ns->d['oldResult'] = -1 * $ns->d['oldResult'];
					} elseif(isset($ns->d['oldResult']) && isset($ns->d['newResult']) && !$ns->d['blockCalc']){
							// After an act button has been pressed. Test: "2 * 3 +/-". 
							// Result: display:-3, previousResult:-3, oldResult:2, newResult:-3, nextOp:multiply
						$ns->d['newResult'] = -1 * $ns->d['newResult'];
					} elseif(isset($ns->d['oldResult']) && isset($ns->d['newResult']) && $ns->d['blockCalc']){
							// After the eql button has been pressed. Test: "2 * 3 = +/-". 
							// Result: display:-6, previousResult:-6, oldResult:-6, newResult:3, blockCalc:set, nextOp:multiply					
						$ns->d['oldResult'] = -1 * $ns->d['oldResult'];
					}
				break;
				case 'act':	// Action buttons: +, -, : and x
					if(!$ns->d['activeButton'] && !$ns->d['nextOp']){
							// Initial entry before any calcs. Test: "1 +" Result: display:1, activeButton: plus, oldResult: 1, nextOp: plus
						$ns->d['oldResult'] = $result;
						$displayResult = $result;
					} elseif($ns->d['activeButton']){
							// When another act button is active. Test: "1 + *" Result: display:1, oldResult:1, activeButton:multiply, nextOp:multiply
						$displayResult = $result;
					} elseif(isset($ns->d['oldResult']) && isset($ns->d['newResult']) && $ns->d['nextOp'] && !$ns->d['blockCalc']){
							// Normal calculation on act. Test: "1 + 4 +". Result: display:5, oldResult:5, nextOp:plus, newResult:5, activeButton:plus 
						$displayResult = $this->calculateNow();
						$ns->d['oldResult'] = $displayResult;
						$ns->d['newResult'] = $displayResult;
					} elseif(isset($ns->d['oldResult']) && isset($ns->d['newResult']) && $ns->d['nextOp'] && $ns->d['blockCalc']){
							// The eql was just pressed. Test: "1 + 4 = +". 
							// Result: display:5, oldResult:5, activeButton:5, newResult:5, blockCalc:set, nextOp:plus, activeButton:plus
						$displayResult = $result;
						$ns->d['newResult'] = $result;
						$ns->d['oldResult'] = $result;
					}
					$ns->d['activeButton'] = $value;
					$ns->d['nextOp'] = $value;	
					$buttonClass[$value] = ' active';
					unset($ns->d['previousResult']);
				break;
				case 'eql':
							// Simply one number and act. Test: "2 + =". 
							// Result: display:4, previousResult:4, justPrEq:set, oldResult:4, newResult:2, blockCalc:set, nextOp:plus
					if(isset($ns->d['activeButton']) && isset($ns->d['oldResult']) && !isset($ns->d['newResult'])){
						$ns->d['newResult'] = $result;
						$displayResult = $this->calculateNow();
						$ns->d['oldResult'] = $displayResult;
						$ns->d['previousResult'] = $displayResult;
						$ns->d['blockCalc'] = 'set';
						if(isset($ns->d['activeButton'])){unset($ns->d['activeButton']);}
					} elseif(isset($ns->d['oldResult']) && isset($ns->d['newResult']) && $ns->d['nextOp']){
							// Standard calculation, old + new + act. Test: "2 + 3 =" 
							// Result: display:5, oldResult:5, nextOp:plus, previousResult:5, newResult:3, blockCalc:set, justPrEq:set 
						$displayResult = $this->calculateNow();
						$ns->d['oldResult'] = $displayResult;
						$ns->d['previousResult'] = $displayResult;
						$ns->d['blockCalc'] = 'set';
						if(isset($ns->d['activeButton'])){unset($ns->d['activeButton']);}
					} elseif(!$ns->d['nextOp']){
							// Pressing eql without a calc action. Just return the value. Test: "1 =". Result: display:1, previousResult:1, justPrEq:set
						$displayResult = $result;
					}
					$ns->d['justPrEq'] = 'set';
				break;
				case 'cal':	// simple immediate transformations of the shown value
					$pi = '3.14159265358979323846';
					$e = '2.71828182845904523536';
					if($value=='inv'){$displayResult = 1 / $result;}
					if($value=='sqr'){$displayResult = $result * $result;}
					if($value=='tri'){$displayResult = $result * $result * $result;}
					if($value=='sqrt'){$displayResult = sqrt($result);}
					if($value=='sin'){$displayResult = sin($result * $pi / 180);}
					if($value=='cos'){$displayResult = cos($result * $pi / 180);}
					if($value=='tan'){$displayResult = tan($result * $pi / 180);}
					if($value=='ln'){$displayResult = log($result * $pi / 180);}
					if($value=='sinh'){$displayResult = sinh($result);}
					if($value=='cosh'){$displayResult = cosh($result);}
					if($value=='tanh'){$displayResult = tanh($result);}
					if($value=='ex'){$displayResult = pow($e, $result);}
					
					if(isset($ns->d['oldResult']) && !isset($ns->d['newResult']) && isset($ns->d['nextOp'])){
							// Before a new value was entered after an act/eql button. Test: "5 + x^2". 
							// Result: display:25, oldResult:5, newResult:25, activeButton:plus, nextOp:plus, justPrEq:set
						$ns->d['newResult'] = $displayResult;
					} elseif(isset($ns->d['oldResult']) && isset($ns->d['newResult'])){
							// After an act button was pressed. Test: "5 + 7 ^2".
							// Result: display: 49, oldResult: 5, newResult: 49, nextOp:plus: justPrEq: set, previousResult:49
						$ns->d['newResult'] = $displayResult;
					} elseif(isset($ns->d['previousResult'])){
							// Only a number and a cal were pressed. Test: "5 ^2". Result: previousResult:25, justPrEq='set'
						$ns->d['previousResult'] = $displayResult;
					}
					$ns->d['justPrEq'] = 'set';
				break;
				case 'mem':
					if($value=='mplus'){
							// Add current value to memory
						$ns->m['memValue'] = $result;
						$displayResult = $result;
						$ns->d['justPrEq'] = 'set';
					}
					if($value=='mminus'){
							// Add negative of current value to memory
						$ns->m['memValue'] = -1 * $result;
						$displayResult = $result;
						$ns->d['justPrEq'] = 'set';
					}
					if($value=='mc'){
							// Clear the memory
						$displayResult = $result;
						if(isset($ns->m['memValue'])){
							unset($ns->m['memValue']);
						}
					}
					if($value=='mr'){if(isset($ns->m['memValue'])){
							// Display the value in memory
						$displayResult = $ns->m['memValue'];
						$ns->d['justPrEq'] = 'set';
						if(isset($ns->d['newResult'])){
							$ns->d['newResult'] = $displayResult;
						} elseif(isset($ns->d['oldResult']) && !isset($ns->d['newResult']) && isset($ns->d['activeButton'])){
							$ns->d['newResult'] = $displayResult;
						} elseif(isset($ns->d['oldResult']) && !isset($ns->d['newResult']) && !isset($ns->d['activeButton'])){
							$ns->d['oldResult'] = $displayResult;
						} elseif(isset($ns->d['previousResult'])){
							$ns->d['previousResult'] = $displayResult;
						}
					}}
				break;
			}
			if(!in_array($key, array('eql', 'cal', 'mem'))){if(isset($ns->d['justPrEq'])){unset($ns->d['justPrEq']);}}	// for any other key than equal or cal, drop this state
			
			$ns->d['clearLabel'] = $clearLabel;
				// make sure that integers are recognized as such
			if($key<>'dot'){
				$val = (string)number_format($displayResult, 14);
				if(strpos($val, '.000000000000')!==false && abs($displayResult) > 0.1){
					$displayResult = (float)number_format($displayResult,0, '', '');
					if(isset($ns->d['previousResult'])){$ns->d['previousResult'] = $displayResult;}
				}
			}
			if(!$displayResult){$displayResult = 0;}
			
				// return the outcome to the screen
			if(isset($ns->m['memValue'])){$buttonClass['mem'] = ' active';}
			if(isset($ns->d['activeButton'])){$buttonClass[$ns->d['activeButton']] = ' active';}
			$this->view->displayResult = $displayResult;
			$this->view->clearLabel = $clearLabel;
			$this->view->buttonClass = $buttonClass;
			
				// show everything in the session
			if(isset($data['showInfo'])){
				if(isset($ns->d['previousResult'])){ $this->view->previousResult = $ns->d['previousResult']; }
				if(isset($ns->d['justPrEq'])){ $this->view->justPrEq = $ns->d['justPrEq']; }
				if(isset($ns->d['oldResult'])){ $this->view->oldResult = $ns->d['oldResult']; }
				if(isset($ns->d['activeButton'])){ $this->view->activeButton = $ns->d['activeButton']; }
				if(isset($ns->d['newResult'])){ $this->view->newResult = $ns->d['newResult']; }
				if(isset($ns->d['blockCalc'])){ $this->view->blockCalc = $ns->d['blockCalc']; }
				if(isset($ns->d['nextOp'])){ $this->view->nextOp = $ns->d['nextOp']; }
				if(isset($ns->d['clearLabel'])){ $this->view->clearLabel = $ns->d['clearLabel']; }
				if(isset($ns->m['memValue'])){ $this->view->memValue = $ns->m['memValue']; }
				$this->view->calcTime = microtime(true) - $startTime;
				$this->view->showInfo = true;
			}
		}
		$this->view->displayResult = $displayResult;
		$this->view->clearLabel = $clearLabel;
		$this->view->isForm = true;
	}
	
	/**
	 * Function to calculate the current results 
	 *
	 * @return	The calculated result
	 */
	protected function calculateNow(){
		$ns = new Zend_Session_Namespace('phpcalc');
		if($ns->d['nextOp']=='divide'){
			if($ns->d['newResult']==0){
				$result = 'Error';
				unset($ns->d);
			} else {$result = $ns->d['oldResult'] / $ns->d['newResult'];}}
		if($ns->d['nextOp']=='multiply'){$result = $ns->d['oldResult'] * $ns->d['newResult'];}
		if($ns->d['nextOp']=='plus'){$result = $ns->d['oldResult'] + $ns->d['newResult'];}
		if($ns->d['nextOp']=='minus'){$result = $ns->d['oldResult'] - $ns->d['newResult'];}
		return $result;
	}

}
