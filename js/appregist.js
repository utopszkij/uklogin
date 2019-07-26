  
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
			$('input#css').removeClass('error');
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
			if ($('input#css').val() != '') {
				if (!$scope.validURL($('input#css').val())) {
					msg += $scope.txt('ERROR_CSS_INVALID')+'<br />';
					$('input#css').addClass('error');
				}
				if ($('input#css').val().indexOf($('input#domain').val()) < 0) {
					msg += $scope.txt('ERROR_CSS_INVALID')+'<br />';
					$('input#css').addClass('error');
				}
			}
			if ($('input#admin').val() == '') {
				msg += $scope.txt('ERROR_ADMIN_EMPTY')+'<br />';
				$('input#admin').addClass('error');
			}
			if ($('input#client_id').val() == '') {
				if ($('input#psw1').val() == '') {
					msg += $scope.txt('ERROR_PSW_EMPTY')+'<br />';
					$('input#psw1').addClass('error');
				}
			}
			if (($('input#psw1').val() != '') && ($('input#psw1').val().length < 6)) {
				msg += $scope.txt('ERROR_PSW_INVALID')+'<br />';
				$('input#psw1').addClass('error');
			}
			if ($('input#psw1').val() != $('input#psw2').val()) {
				msg += $scope.txt('ERROR_PSW_NOTEQUAL')+'<br />';
				$('input#psw1').addClass('error');
				$('input#psw2').addClass('error');
			}
			if ($('input#dataProcessAccept').is(':checked') == false) {
				msg += $scope.txt('ERROR_DATA_ACCEP_REQUEST')+'<br />';
				$('input#dataProcessAccept').addClass('error');
			}
			if ($('input#cookieProcessAccept').is(':checked') == false) {
				msg += $scope.txt('ERROR_COOKIE_ACCEP_REQUEST')+'<br />';
				$('input#cokkieProcessAccept').addClass('error');
			}
			
			if (msg == '') {
				global.submit('formApp');
			} else {
				global.alert(msg);
			}
	  });
	  $('#userActOk').click(function() {
		  global.alert('Nincs kész');
	  });
	  $('#formAppCancel').click(function() {
		  window.location = "./index.php";
	  });
	  $('#formAdminLoginOk').click(function() {
		  global.submit('formAdminLogin');
	  });
	  $('#formAdminLoginCancel').click(function() {
		  window.location = "./index.php";
	  });
	  $('#logout').click(function() {
		    $('#task').val('logout');
			global.submit('formApp');
	  });
	  $('#formAppRemove').click(function() {
		  global.confirm($scope.txt('SUREDELAPP'),
		  function() {
			    $('#task').val('appremove');
				global.submit('formApp');
		  });
	  });
	  
	  $('#scope').show();
  }	
  
  // jquery pageOnLoad 
  $(function() {
	  $("input#name").focus();
  });

  pageOnLoad();