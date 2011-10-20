	$(document).ready(function(){
		var clearLabel;
		var previousResult;
		var displayResult;
		var justPrEq;
		var oldResult;
		var activeButton;
		var newResult;
		var blockCalc;
		var nextOp;
		var memValue;

		$("#debugInfo").hide();
		if($('#showDebug').is(':checked')){
			$("#debugInfo").show();
		}
		
		$(".calc").click(function(){
			var startTime = new Date();
			var key = this.name.substr(this.name.indexOf('[') + 1, (this.name.indexOf(']') - 1 - this.name.indexOf('[')));
			var value = this.name.substr(this.name.lastIndexOf('[') + 1, (this.name.lastIndexOf(']') - 1 - this.name.lastIndexOf('['))); 
			var result = Number($('#results').val());

			if(!clearLabel){clearLabel = 'AC';}
			var buttonClass = {mem:'', divide:'', multiply:'', minus:'', plus:''};
			
			switch(key) {
				case 'num':
					if(justPrEq){
						previousResult = value;
						displayResult = value;
						oldResult = value;
					} else if(!previousResult && !nextOp){
						displayResult = value;
						previousResult = displayResult;
					} else if(previousResult && !nextOp){
						if(previousResult == 0){ displayResult = value;} else {displayResult = previousResult + value;}
						previousResult = displayResult;
					} else if(oldResult && activeButton){
						displayResult = value;
						$('#' + activeButton).removeClass('active');
						activeButton = null;
						previousResult = displayResult;
						newResult = displayResult;
					} else if(oldResult && !activeButton) {
						if(previousResult){
							displayResult = previousResult + value;
						} else {
							if(previousResult==='0.'){
								displayResult = previousResult + value;
							} else {
								displayResult = value;
							}
						}
						newResult = displayResult;
						previousResult = displayResult;
					}
					if(blockCalc){delete blockCalc;}
					clearLabel = 'C';
				break;
				case 'dot':
					if(previousResult){
						pos = previousResult.indexOf('.');
						if(pos == -1){
							displayResult = previousResult + '.';
						} else {
							displayResult = previousResult;
						}
					} else {
						displayResult = '0.';
					}
					previousResult = displayResult;
					if(activeButton){
						$('#' + activeButton).removeClass('active');
						activeButton = null;
					}
					clearLabel = 'C';
				break;
				case 'clr':
					if(clearLabel=='C'){
						displayResult = '0';
						previousResult = '0';
						if(newResult){newResult = 0;}
						clearLabel = 'AC';
					} else if(value=='AC'){
						unsetAll();
					}
				break;
				case 'neg':
					displayResult = -1 * result;
					previousResult = displayResult;
					if(oldResult && !newResult){
						oldResult = -1 * oldResult;
					} else if(oldResult && newResult && !blockCalc){
						newResult = -1 * newResult;
					} else if(oldResult && newResult && blockCalc){
						oldResult = -1 * oldResult;
					}
				break;
				case 'act':
					if(!activeButton && !nextOp){
						oldResult = result;
						displayResult = result;
					} else if(activeButton){
						displayResult = result;
					} else if(oldResult && newResult && nextOp && !blockCalc){
						displayResult = calculateNow();
						oldResult = displayResult;
						newResult = displayResult;
					} else if(oldResult && newResult && nextOp && blockCalc){
						displayResult = result;
						newResult = result;
						oldResult = result;
					}
					if(activeButton){$('#' + activeButton).removeClass('active');}
					activeButton = value;
					nextOp = value;
					$('#' + value).addClass('active');
					previousResult = null;
				break;
				case 'eql':
					if(activeButton && oldResult && !newResult){
						newResult = result;
						displayResult = calculateNow();
						oldResult = displayResult;
						previousResult = displayResult;
						blockCalc = 'set';
					} else if(oldResult && newResult && nextOp){
						displayResult = calculateNow();
						oldResult = displayResult;
						previousResult = displayResult;
						blockCalc = 'set';
					} else if(!nextOp){
						displayResult = result;
					}
					if(activeButton){
						$('#' + activeButton).removeClass('active');
						activeButton = null;
					}
					justPrEq = 'set';
				break;
				case 'cal':
					result = Number(result);
					pi = 3.14159265358979323846;
					if(value=='inv'){displayResult = 1 / result;}
					if(value=='sqr'){displayResult = result * result;}
					if(value=='tri'){displayResult = result * result * result;}
					if(value=='sqrt'){displayResult = Math.sqrt(result);}
					if(value=='sin'){displayResult = Math.sin(result *  pi / 180);}
					if(value=='cos'){displayResult = Math.cos(result *  pi / 180);}
					if(value=='tan'){displayResult = Math.tan(result *  pi / 180);}
					if(value=='ln'){displayResult = Math.log(result);}
					if(value=='sinh'){displayResult = (Math.exp(result) - Math.exp(-result)) / 2;}
					if(value=='cosh'){displayResult = (Math.exp(result) + Math.exp(-result)) / 2;}
					if(value=='tanh'){displayResult = (Math.exp(result) - Math.exp(-result)) / (Math.exp(result) + Math.exp(-result));}
					if(value=='ex'){displayResult = Math.exp(result);}
					
					if(oldResult && !newResult && nextOp){
						newResult = displayResult;
					} else if(oldResult && newResult){
						newResult = displayResult;
					} else if(previousResult){
						previousResult = displayResult;
					}
					justPrEq = 'set';
				break;
				case 'mem':
					if(value=='mplus'){
						memValue = result;
						displayResult = result;
						justPrEq = 'set';
					}
					if(value=='mminus'){
						memValue = -1 * Number(result);
						displayResult = result;
						justPrEq = 'set';
					}
					if(value=='mc'){
						displayResult = result;
						if(memValue){
							memValue = null;
							$('#mr').removeClass('active');
						}
					}
					if(value=='mr'){
						if(memValue){
							displayResult = memValue;
							justPrEq = 'set';
							if(newResult){
								newResult = displayResult;
							} else if(oldResult && !newResult && activeButton){
								newResult = displayResult;
							} else if(oldResult && !newResult && !activeButton){
								oldResult = displayResult;
							} else if(previousResult){
								previousResult = displayResult;
							}
						}
					}
				break;
			}

			if(key !== 'eql' && key !== 'cal' && key !== 'mem'){if(justPrEq){justPrEq = null;}}

			if(displayResult){
				if(Math.abs(displayResult) > 10000000000000 || (Math.abs(displayResult) < 0.0000000000001)){
					$('#results').addClass('small-font').removeClass('big-font');
					} else {$('#results').removeClass('small-font').addClass('big-font');}
				if(displayResult == 0){$('#results').removeClass('small-font').addClass('big-font');}
				if(previousResult){previousResult = displayResult;}
			}
			
			if(memValue){$('#mr').addClass('active');}
			if(activeButton){$('#' + activeButton).addClass('active');}
			$('#results').val(displayResult);
			$('#ac').val(clearLabel);
			
			$('#previousResult').val(previousResult);
			$('#justPrEq').val(justPrEq);
			$('#oldResult').val(oldResult);
			$('#activeButton').val(activeButton);
			$('#newResult').val(newResult);
			$('#blockCalc').val(blockCalc);
			$('#nextOp').val(nextOp);
			$('#clearLabel').val(clearLabel);
			$('#memValue').val(memValue);
			endTime = new Date();
			$('#calcTime').val((endTime.getTime() - startTime.getTime())/1000);
		});

		function calculateNow(){
			if(nextOp =='divide'){
				if(newResult==0){
					result = 'Error';
					unsetAll();
				} else {
					result = Number(oldResult) / Number(newResult);
				}
			}
			if(nextOp =='multiply'){result = Number(oldResult) * Number(newResult) ;}
			if(nextOp =='plus'){result = Number(oldResult) + Number(newResult) ;}
			if(nextOp =='minus'){result = Number(oldResult) - Number(newResult) ;}
			return result;
		};

		function unsetAll(){
			$('.calc').removeClass('active');
			displayResult = '0';
			clearLabel = 'AC';
			previousResult = null;
			justPrEq = null;
			oldResult = null;
			activeButton = null;
			newResult = null;
			blockCalc = null;
			nextOp = null;
		};

		$("#showDebug").click(function(){
			$("#debugInfo").toggle();
		});
	});