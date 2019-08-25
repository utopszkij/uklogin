  
  // angular page onload --- must this function !
  function pageOnLoad() {
		if ($('#formLogin')) {
			$('#nick').focus();
			$('#formLoginOk').click(function() {
				var msg = '';
				// nick, psw1, psw2, dataProcessAccept, cookieProcessAccept ellenörzése
				$('input#nick').removeClass('error');
				$('input#psw1').removeClass('error');
				if ($('#nick').val() == '') {
					msg += $scope.txt('ERROR_NICK_EMPTY')+'<br>';
				}
				if ($('#psw1').val() == '') {
					msg += $scope.txt('ERROR_PSW_EMPTY')+'<br>';
				}
				if (msg == '') {
					global.submit('formLogin');
				} else {
					global.alert(msg);
				}
			});
		} // formLogin	
  }	
  
  // jquery pageOnLoad 
  $(function() {
  }); // Jquery pageOnload

  pageOnLoad();