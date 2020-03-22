  
  // params for controller	  
  // param1  requed	
  // sid string requed
  
  // jquery page onload --- must this function !

  // here $scope is not valid. 
  function pageOnLoad() {
  }	

  function frontpageFun() {
	  $('#scope').show();
	  if ($('.carousel').carousel) {
		  $('.carousel').carousel();
	  }	  
	  return 'frontpage';
  }
  
  // jquery pageOnLoad
  $(function() {
  	pageOnLoad();
  });	

  // angular pageOnLoad
  frontpageFun();
  
