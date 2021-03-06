  
  // params for controller	  
  // param1  requed	
  // sid string requed
  
  // angular page onload --- must this function !
  function pageOnLoad() {
	  $scope.validURL = function(str) {
		  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
		    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
		    '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
		    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
		    '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
		    '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
		  return !!pattern.test(str);
	  };
	  $('#formAppOk').click(function() {
			var msg = '';
			$('input#name').removeClass('error');
			$('input#domain').removeClass('error');
			$('input#callback').removeClass('error');
			$('input#admin').removeClass('error');
			$('input#psw1').removeClass('error');
			$('input#psw2').removeClass('error');
			if ($('input#name').val() == '') {
				msg += $scope.txt('ERROR_NAME_EMPTY')+'<br />';
				$('input#name').addClass('error');
			}
			if ($('input#domain').val() == '') {
				msg += $scope.txt('ERROR_DOMAIN_EMPTY')+'<br />';
				$('input#domain').addClass('error');
			}
			if (!$scope.validURL($('input#domain').val())) {
				msg += $scope.txt('ERROR_DOMAIN_INVALID')+'<br />';
				$('input#domain').addClass('error');
			}
			if ($('input#callback').val() == '') {
				msg += $scope.txt('ERROR_CALLBACK_EMPTY')+'<br />';
				$('input#callback').addClass('error');
			} else {
				if (!$scope.validURL($('input#callback').val())) {
					msg += $scope.txt('ERROR_CALLBACK_INVALID')+'<br />';
					$('input#callback').addClass('error');
				}
				if ($('input#callback').val().indexOf($('input#domain').val()) < 0) {
					msg += $scope.txt('ERROR_CALLBACK_NOT_IN_DOMAIN')+'<br />';
					$('input#callback').addClass('error');
				}
			}
			if ($('input#admin').val() == '') {
				msg += $scope.txt('ERROR_ADMIN_EMPTY')+'<br />';
				$('input#admin').addClass('error');
			}
			if ($('input#dataProcessAccept').is(':checked') == false) {
				msg += $scope.txt('ERROR_DATA_ACCEP_REQUEST')+'<br />';
				$('input#dataProcessAccept').addClass('error');
			}
			if (msg == '') {
				global.submit('formApp');
			} else {
				global.alert(msg);
			}
	  });
	  $('#formAppCancel').click(function() {
		  window.location = "./index.php";
	  });
	  $('#formAppRemove').click(function() {
		  global.confirm($scope.txt('SUREDELAPP'),
		  function() {
			    $('#task').val('appremove');
				global.submit('formApp');
		  });
	  });
	  $('#appsSelect').change(function() {
		 var client_id = $('#appsSelect').val();
	  	 window.location = "./index.php?option=appregist&task=adminform&client_id="+client_id; 
	  });
	  
	  $('#scope').show();
  }	
  
  // jquery pageOnLoad 
  $(function() {
	  $("input#name").focus();
  });

  pageOnLoad();