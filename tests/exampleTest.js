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

//define angularjs test enviroment
var $scope = {};

//define multylanguage system test enviroment
$scope.txt = function(token) {return token};

//include js file for test (must pageOnLoad() root level function)
var fs = require('fs');
eval(fs.readFileSync('./js/example.js')+'');


//run jquery pageOnload 
if (pageOnLoad != undefined) {
	pageOnLoad();
}	

// test cases
describe('exampleTest.js', function() {
	
	it('test_exampleFun', function() {
		
		var res = exampleFun();
		assert.ok($scope.title == 'example');
	}); 
});
