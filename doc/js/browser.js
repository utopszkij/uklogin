  
  // params for controller	  
  // param1  requed	
  // sid string requed
  
  // jquery page onload --- must this function !
  // here $scope is not valid. 
  function pageOnLoad() {
  }	

  function browserFun() {
	  $scope.title = 'browser';
	  $scope.items = [];
  	  var itemTitle = '';	
	  for (var i = 0; i< 145; i++) {
	  		itemTitle = 'title'+i;
		   $scope.items[i] = {"id": i, "title": itemTitle};
	  }
	  $scope.total = 145;
	  $scope.paginatorPageOffsets = [0,0,20,40,60,80,100,120,140];
	  $scope.paginatorCurrentPage = 4;
	  $scope.paginatorLastPage = $scope.paginatorPageOffsets.length - 1;
	  
	  
	  $scope.workingTogle = function() {
			 global.working();
	  }
	  return 'browser';
  }
  
  // jquery pageOnLoad
  $(function() {
  	pageOnLoad();
  });	

  // angular pageOnLoad
  browserFun();
  
