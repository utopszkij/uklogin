  
  // ÁTNÉZNI, NEM JÓ !
  function pageOnLoad() {
	    if ($('#loginForm')) {
	    	$('#btnOk').click(function() {
				$('input#dataprocessaccept').removeClass('is-invalid');
				$('input#psw').removeClass('is-invalid');
				$('input#nickname').removeClass('is-invalid');
	    		msg = '';
				if ($('input#dataprocessaccept').is(':checked') == false) {
					msg += global.LNG.DATAPROCESS_ACCEPT_REQUIRED+'<br />';
					$('input#dataprocessaccept').addClass('is-invalid');
				}
				if ($('input#psw').val() == '') {
					msg += global.LNG.PSW_REQUIRED+'<br />';
					$('input#psw').addClass('is-invalid');
				}
				if ($('input#nickname').val() == '') {
					msg += global.LNG.NICK_REQUIRED+'<br />';
					$('input#nickname').addClass('is-invalid');
				}
				if (msg == '') {
					global.submit('frmLoginForm');
				} else {
					global.alert(msg);
				}
	    	});
	    	$('#forgetpswlink').click(function() {
	    		document.location = 'index.php/openid/forgetpswform/nickname/'+$('#nickname').val();
	    		return false;
	    	});
	    	$('#loginForm').show();
	    	$('#nickname').focus();
	    } // loginForm
	    
	    if ($('#scopeForm')) {
	    	$('#btnOk').click(function() {
				$('input#dataprocessaccept').removeClass('is-invalid');
	    		msg = '';
				if ($('input#dataprocessaccept').is(':checked') == false) {
					msg += global.LNG.DATAPROCESS_ACCEPT_REQUIRED+'<br />';
					$('input#dataprocessaccept').addClass('is-invalid');
				}
				if (msg == '') {
					global.submit('frmScopeForm');
				} else {
					global.alert(msg);
				}
	    	});
	    	$('#scopeForm').show();
	    } // scopeForm

	    if ($('#formRegist2')) {
			$('#nick').focus();
			
			global.pswCheck = function(psw1, msg) {
				if ($('input#psw1').val() == '') {
					msg += global.LNG.PSW_REQUIRED+'<br />';
					$('input#psw1').addClass('is-invalid');
				}
				if (($('input#psw1').val() != '') && ($('input#psw1').val().length < 6)) {
					msg += global.LNG.ERROR_PSW_INVALID+'<br />';
					$('input#psw1').addClass('is-invalid');
				}
				return msg;
			};
			global.nickCheck = function(nick, msg) {
				if ($('input#nick').val() == '') {
					msg += global.LNG.NICK_REQUIRED+'<br />';
					$('input#nick').addClass('is-invalid');
				}
				return msg;
			} 
			
			$('#formRegist2Ok').click(function() {
				var msg = '';
				// nick, psw1, psw2, dataProcessAccept, cookieProcessAccept ellenörzése
				$('input#nick').removeClass('is-invalid');
				$('input#psw1').removeClass('is-invalid');
				$('input#psw2').removeClass('is-invalid');
				$('input#dataProcessAccept').removeClass('is-invalid');
				$('input#cookieProcessAccept').removeClass('is-invalid');
				msg = global.nickCheck($('input#nick').val(),msg);
				msg = global.pswCheck($('input#psw1').val(),msg);
				if ($('input#psw1').val() != $('input#psw2').val()) {
					msg += global.LNG.PASSWORDS_NOTEQUALS+'<br />';
					$('input#psw1').addClass('is-invalid');
					$('input#psw2').addClass('is-invalid');
				}
				if ($('input#dataProcessAccept').is(':checked') == false) {
					msg += global.LNG.DATAPROCESS_ACCEPT_REQUIRED+'<br />';
					$('input#dataProcessAccept').addClass('is-invalid');
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