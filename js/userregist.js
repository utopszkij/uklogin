  
  // angular page onload --- must this function !
  function pageOnLoad() {
		if ($('#formRegist2')) {
			$('#nick').focus();
			
			global.pswCheck = function(psw1, msg) {
				if ($('input#psw1').val() == '') {
					msg += $scope.txt('ERROR_PSW_EMPTY')+'<br />';
					$('input#psw1').addClass('error');
				}
				if (($('input#psw1').val() != '') && ($('input#psw1').val().length < 6)) {
					msg += $scope.txt('ERROR_PSW_INVALID')+'<br />';
					$('input#psw1').addClass('error');
				}
				return msg;
			};
			global.nickCheck = function(nick, msg) {
				if ($('input#nick').val() == '') {
					msg += $scope.txt('ERROR_NAME_EMPTY')+'<br />';
					$('input#nick').addClass('error');
				}
				return msg;
			} 
			
			$('#formRegist2Ok').click(function() {
				var msg = '';
				// nick, psw1, psw2, dataProcessAccept, cookieProcessAccept ellenörzése
				$('input#nick').removeClass('error');
				$('input#psw1').removeClass('error');
				$('input#psw2').removeClass('error');
				$('input#dataProcessAccept').removeClass('error');
				$('input#cookieProcessAccept').removeClass('error');
				msg = global.nickCheck($('input#nick').val(),msg);
				msg = global.pswCheck($('input#psw1').val(),msg);
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
					global.submit('formRegist2');
				} else {
					global.alert(msg);
				}
			});
		} // formRegist2
  }	
  
  // jquery pageOnLoad 
  $(function() {
  }); // Jquery pageOnload

  pageOnLoad();