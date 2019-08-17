var assert = require('assert');
const { JSDOM } = require('jsdom');
const jsdom = new JSDOM('<!doctype html><html><body></body></html>');
const { window } = jsdom;
const $ = global.jQuery = require('jquery')(window);

// mock document object and some browser function
var mock = require('./mock.js');
mock.init(window);

// params for controller	  
var param1  = 'TestParam';	

// create test html 
$('body').append('<h1>Example</h1>');
$('body').append('<div id="formRegist2"></div>'); // <form -al nem jó!!!!
$('#formRegist2').append('<input id="nick" type="text" value="" />');
$('#formRegist2').append('<input id="psw1" type="text" value="" />');
$('#formRegist2').append('<input id="psw2" type="text" value="" />');
$('#formRegist2').append('<input id="dataProcessAccept" type="checkbox" value="0" />');
$('#formRegist2').append('<input id="cookieProcessAccept" type="checkbox" value="0" />');
$('#formRegist2').append('<button id="formRegist2Ok">OK</button>');
$('body').append('<div id="formLogin"></div>'); // <form -al nem jó!!!!
$('#formLogin').append('<input id="nick" type="text" value="" />');
$('#formLogin').append('<input id="psw1" type="text" value="" />');
$('#formLogin').append('<button id="formLoginOk">OK</button>');


//define angularjs test enviroment
var $scope = {};

//define multylanguage system test enviroment
$scope.txt = function(token) {return token};

//include js file for test (must pageOnLoad() root level function)
var fs = require('fs');
eval(fs.readFileSync('./js/oauth2.js')+'');


//run jquery pageOnload 
if (pageOnLoad != undefined) {
	pageOnLoad();
}	

// test cases
describe('oauth2Test.js', function() {
	
	it('formRegist2Click_NOTOK_1', function() {
		global.alertTxt = '???';
		$('#nick').val('');
		$('#psw1').val('');
		$('#psw2').val('');
		$('#dataProcessAccept').attr('checked',false);
		$('#cookieProcessAccept').attr('checked',false);
		$('#formRegist2Ok').click();
		assert.ok(global.alertTxt != '???');
	}); 

	it('formRegist2Click_NOTOK_2', function() {
			global.alertTxt = '???';
			$('#nick').val('');
			$('#psw1').val('1234567');
			$('#psw2').val('12345678');
			$('#dataProcessAccept').attr('checked',false);
			$('#cookieProcessAccept').attr('checked',false);
			$('#formRegist2Ok').click();
			assert.ok(global.alertTxt != '???');
	}); 
	
	it('formRegist2Click_OK', function() {
				global.alertTxt = 'ok';
				$('#nick').val('user1');
				$('#psw1').val('123456');
				$('#psw2').val('123456');
				$('#dataProcessAccept').attr('checked',true);
				$('#cookieProcessAccept').attr('checked',true);
				$('#formRegist2Ok').click();
				assert.ok(global.alertTxt == 'ok');
	}); 

	it('formLoginClick_NOTOK', function() {
				global.alertTxt = '???';
				$('#nick').val('');
				$('#psw1').val('');
				$('#formLoginOk').click();
				assert.ok(global.alertTxt != '???');
	}); 
	

	it('formLoginClick_OK', function() {
				global.alertTxt = 'ok';
				$('#nick').val('user1');
				$('#psw1').val('123456');
				$('#formLoginOk').click();
				assert.ok(global.alertTxt == 'ok');
	}); 
	
});
