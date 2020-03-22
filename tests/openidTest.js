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
$('body').append('<div id="scope"></div>');
$('#scope').append('<div id="loginForm"></div>');
$('#loginForm').append('<formt id="frmLoginForm">');
$('#loginForm').append('<input type="chckbox" id="dataprocessaccept" value="1" />');
$('#loginForm').append('<input type="password" id="psw" value="" />');
$('#loginForm').append('<input type="text" id="nickname" value="" />');
$('#loginForm').append('<button type="text" id="btnOk"></button>');
$('#loginForm').append('<var id="forgetpswlink"></var>');

//define angularjs test enviroment
global.LNG = {};
global.LNG.DATAPROCESS_ACCEPT_REQUIRED = '';
global.LNG.PSW_REQUIRED = '';
global.LNG.NICK_REQUIRED = '';
global.LNG.DATAPROCESS_ACCEPT_REQUIRED = '';
global.LNG.ERROR_PSW_INVALID = '';
global.LNG.PASSWORDS_NOTEQUALS = '';

var $scope = {};

//define multylanguage system test enviroment
$scope.txt = function(token) {return token};

//include js file for test (must pageOnLoad() root level function)
var fs = require('fs');
eval(fs.readFileSync('./js/openid.js')+'');

if (pageOnLoad != undefined) {
	pageOnLoad();
}	


// test cases
describe('openidTest.js', function() {
	
	
	// loginForm
	// =========
	
	it('loginForm_btnOk_click_notok', function() {
		global.alertTxt = '';
		$('#dataprocessaccept').attr('checked',false);
		$('#nickname').val('');
		$('#psw').val('');
		$('#btnOk').click();
		assert.notEqual(global.alertTxt,'');
	}); 

	it('loginForm_btnOk_click_ok', function() {
		global.submitFormId = '';
		$('#loginForm #dataprocessaccept').attr('checked','checked');
		$('#nickname').val('user1');
		$('#psw').val('123456');
		$('#loginForm #btnOk').click();
		assert.notEqual(global.submitFormId,'');
	}); 
	
	it('loginForm_forgetpswlink', function() {
		global.alertTxt = '';
		global.submitFormId = '';
		document.location = '';
		$('#loginForm #forgetpswlink').click();
		assert.notEqual(document.location,'');
	});
	
	// scopeForm
	// =========
	
	it('scopeForm_btnOk_click_notok', function() {

		// remove loginForm	
		$('#scope').html('');
		
		// define scopeForm
		$('#scope').append('<div id="scopeForm"></div>');
		$('#scopeForm').append('<div id="frmScopeForm"></div>');
		$('#scopeForm').append('<input type="chckbox" id="dataprocessaccept" value="1" chacked="checked" />');
		$('#scopeForm').append('<button id="btnOk"></button>');
		if (pageOnLoad != undefined) {
			pageOnLoad();
		}	
		
		global.alertTxt = '';
		$('#scopeForm #btnOk').click();
		assert.notEqual(global.alertTxt,'');
	}); 

	it('scopeForm_btnOk_click_ok', function() {
		global.submitFormId = '';
		$('#scopeForm #dataprocessaccept').attr('checked','checked');
		$('#scopeForm #btnOk').click();
		assert.notEqual(global.submitFormId,'');
	}); 
	
	// formProfile
	// ===========
	
	it('formProfile_btnOK_notok1', function() {

		// remove loginForm	
		$('#scope').html('');
		
		// define scopeForm
		$('#scope').append('<div id="profileForm"></div>');
		$('#profileForm').append('<div id="formProfile"></div>');
		$('#formProfile').append('<input type="text" id="nickname" />');
		$('#formProfile').append('<input type="text" id="psw1" />');
		$('#formProfile').append('<input type="text" id="psw2" />');
		$('#formProfile').append('<input type="text" id="email" />');
		$('#formProfile').append('<button id="formProfileOk"></button>');
		$('#formProfile').append('<button type="button" id="btnMyData"></button>');
		$('#formProfile').append('<button type="button" id="btnDelAccount"></button>');

		if (pageOnLoad != undefined) {
			pageOnLoad();
		}	
		
		global.alertTxt = '';
		$('#psw1').val('12345678');
		$('#psw2').val('ssssssss');
		$('#formProfileOk').click();
		assert.notEqual(global.alertTxt,'');
	}); 
	
	it('formProfile_btnOK_notok1', function() {
		global.alertTxt = '';
		$('#psw1').val('123');
		$('#psw2').val('123');
		$('#formProfileOk').click();
		assert.notEqual(global.alertTxt,'');
	});	
	
	it('formProfile_btnOK_ok', function() {
		global.submitFormId = '';
		global.alertTxt = '';
		$('#psw1').val('');
		$('#psw2').val('');
		$('#formProfileOk').click();
		assert.notEqual(global.submitFormId,'');
	});	

	it('formProfile_btnMyData', function() {
		csrToken = '122';
		document.location = '';
		$('#btnMyData').click();
		assert.notEqual(document.location,'');
	});	

	it('formProfile_btnDelAccount', function() {
		document.location = '';
		$('#btnDelAccount').click();
	});	

	// formRegist2
	// ===========
	
	it('formRegist2_btnOK_notok1', function() {

		// remove loginForm	
		$('#scope').html('');
		
		// define scopeForm
		$('#scope').append('<div id="formRegist2"></div>');
		$('#formRegist2').append('<input type="text" id="nick" />');
		$('#formRegist2').append('<input type="text" id="psw1" />');
		$('#formRegist2').append('<input type="text" id="psw2" />');
		$('#formRegist2').append('<input type="text" id="email" />');
		$('#formRegist2').append('<input type="checkbox" id="dataProcessAccept" />'); 
		$('#formRegist2').append('<button id="formRegist2Ok"></button>');
		if (pageOnLoad != undefined) {
			pageOnLoad();
		}	
		
		global.alertTxt = '';
		$('#psw1').val('12345678');
		$('#psw2').val('ssssssss');
		$('#formRegist2Ok').click();
		assert.notEqual(global.alertTxt,'');
	}); 
	
	it('formRegist2_btnOK_notok2', function() {
		global.alertTxt = '';
		$('#psw1').val('123');
		$('#psw2').val('123');
		$('#formRegist2Ok').click();
		assert.notEqual(global.alertTxt,'');
	});	

	it('formRegist2_btnOK_notok3', function() {
		global.alertTxt = '';
		$('#psw1').val('');
		$('#psw2').val('');
		$('#formRegist2Ok').click();
		assert.notEqual(global.alertTxt,'');
	});	
	
	it('formRegist2_btnOK_ok', function() {
		global.submitFormId = '';
		global.alertTxt = '';
		$('#psw1').val('123456');
		$('#psw2').val('123456');
		$('#nick').val('user1');
		$('#dataProcessAccept').attr('checked','checked');
		$('#formRegist2Ok').click();
		assert.notEqual(global.submitFormId,'');
	});	
	
	
});
