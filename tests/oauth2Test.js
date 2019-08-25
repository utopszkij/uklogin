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
