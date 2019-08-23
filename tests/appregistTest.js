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
$('body').append('<div id="formApp"></div>'); // <form -al nem jó!!!!
$('#formApp').append('<button id="formAppOk">OK</button>');
$('#formApp').append('<input id="client_id" type="text" value="" />');
$('#formApp').append('<input id="name" type="text" value="" />');
$('#formApp').append('<input id="domain" type="text" value="" />');
$('#formApp').append('<input id="callback" type="text" value="" />');
$('#formApp').append('<input id="css" type="text" value="" />');
$('#formApp').append('<input id="admin" type="text" value="" />');
$('#formApp').append('<input id="dataProcessAccept" type="checkbox" value="1" />');
$('#formApp').append('<input id="cookieProcessAccept" type="checkbox" value="1" />');

//define angularjs test enviroment
var $scope = {};

//define multylanguage system test enviroment
$scope.txt = function(token) {return token};

//include js file for test (must pageOnLoad() root level function)
var fs = require('fs');
eval(fs.readFileSync('./js/appregist.js')+'');


//run jquery pageOnload 
if (pageOnLoad != undefined) {
	pageOnLoad();
}	

// test cases
describe('appregistTest.js', function() {
	
	it('validURLFun_OK', function() {
		var res = $scope.validURL('https://balmix.hu');
		assert.ok(res == true);
	}); 

	it('validURLFun_NOTOK', function() {
		var res = $scope.validURL('https://bal:-mix.hu');
		assert.ok(res == false);
	}); 

	it('formAppOKClick_NOTOK1', function() {
		$('#formAppOk').click();
		assert.ok(global.alertTxt != '???');
	}); 

	it('formAppOKClick_NOTOK_invalidurls', function() {
		$('#domain').val('https:*\|//balmix.hu');
		$('#callback').val('https:*\|balmix.hu/logged');
		$('#css').val('https://balmix.hu/style.css');
		$('#formAppOk').click();
		assert.ok(global.alertTxt != '???');
	}); 

	it('formAppOKClick_NOTOK_notindomain', function() {
		$('#domain').val('https://balmix.hu');
		$('#callback').val('https://mashol.hu/logged');
		$('#css').val('https://mashol.hu/style.css');
		$('#formAppOk').click();
		assert.ok(global.alertTxt != '???');
	}); 

	it('formAppOKClick_OK', function() {
		$('#client_id').val('');
		$('#name').val('test');
		$('#domain').val('https://balmix.hu');
		$('#callback').val('https://balmix.hu/logged');
		$('#css').val('https://balmix.hu/style.css');
		$('#admin').val('admin');
		$('#dataProcessAccept').attr('checked','checked');
		$('#cookieProcessAccept').attr('checked','checked');
		global.alertTxt = '';
		$('#formAppOk').click();
		assert.ok(global.alertTxt == '');
	}); 
	
	it('formAdminLoginOKClick', function() {
		global.alertTxt = '';
		$('#formAdminLoginOk').click();
		assert.ok(global.alertTxt == '');
	});

	it('formAdminLoginCancelClick', function() {
		global.alertTxt = '';
		$('#formAdminLoginCancel').click();
		assert.ok(global.alertTxt == '');
	});
	
	it('UserActOkClick', function() {
		global.alertTxt = '';
		$('#userActOk').click();
		assert.ok(global.alertTxt == '');
	});

	it('formAppCancelClick', function() {
		global.alertTxt = '';
		$('#formAppcancel').click();
		assert.ok(global.alertTxt == '');
	});
	
	it('logoutkClick', function() {
		global.alertTxt = '';
		$('#logut').click();
		assert.ok(global.alertTxt == '');
	});
	
	it('formAppremoveClick', function() {
		global.alertTxt = '';
		$('#formAppremove').click();
		assert.ok(true); // csak szintaxis ellenörzés
	});
	
});
