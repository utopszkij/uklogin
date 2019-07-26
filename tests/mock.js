
exports.init = function(window) {
	window.setTimeout = function(s) {};
	window.scrollTo = function(x,y) {};
	document = {};
	document.documentElement = {};
	document.documentElement.scrollTop = 0;
	global.postResult = {};
	global.alertTxt = ''; 
	global.submitFormId = '';
	global.alert = function(str) { global.alertTxt = str};
	global.confirm = function(str,yesfun, nofun) { yesfun(); };
	global.post = function(url, postData, successFun, failFun) {
		if (global.postResult.length > 0) {
			successFun(global.postResult[0]);
			global.postResult.splice(0,1);
		} else {
			successFun({});
		}	
	}	
	global.submit = function(formId) { global.submitFormId = formId; };
	global.working = function(show) {};
};

