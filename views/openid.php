<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

include_once './views/common.php';

/**
 * OpenidView class
 * @author utopszkij
 */
class OpenidView  extends CommonView  {

    /**
     * hibaüzenet megjelenítése
     * @param Params $p
     */
    protected function echoMsgs(Params $p) {
        if (count($p->msgs) > 0) {
		    	echo '<p class="alert alert-danger">';
		    	foreach($p->msgs as $msg) {
		    	    echo $msg.'<br />';
		    	}
		    	echo '<p>';
		 }
    }

    /**
     * scope string elemzése nyelvi forditása
     * @param string $scope
     * @return string
     */
    protected function scopeTxt(string $scope): string {
        $w = explode(' ',
            str_replace('openid',
            'sub nickname address email email_verified name '.
            'picture birth_date phone_number phone_number_verified updated_at',
            $scope));
        $w2 = [];
        foreach ($w as $item) {
            $w2[] = txt($item);
        }
        return implode(', ',$w2).'<p class="alert-warning">'.txt('SCOPE_HELP').'</p>';
    }

    /**
     * login form kirajzolása
     * @param Params $p - msgs, nickname, cientTitle, scope, policy_uri
     */
    public function loginForm($p) {
        $this->echoHtmlHead($p);
        if (!isset($p->formTitle)) {
            $p->formTitle = txt('LOGIN');
        }

        ?>
        <body>
	    <div id="loginForm" style="display:none;" class="loginForm">
	      <div class="page" style="max-width:600px">
	    	<?php $this->echoMsgs($p); ?>
	    	<img class="formLogo" src="<?php echo config('MYDOMAIN'); ?>/templates/default/logo.png" alt="logo" />
	    	<h2><em class="fa fa-sign-in formIcon"></em>&nbsp;uklogin</h2>
	    	<h3><?php echo $p->formTitle; ?></h3>
	    	<div id="alterButtons" style="display:block">
	    		<?php if (config('GOOGLE_CLIENT_ID') != '') : ?>
	    		<button type="text" class="btn btn-outline-secondary alterButton" id="googleButton">
	    			<img src="<?php echo config('MYDOMAIN'); ?>/images/google.png" style="height:90%" alt="google" title="google" />
	    		</button>&nbsp;
	    		<?php endif; ?>
	    		<?php if (config('FB_CLIENT_ID') != '') : ?>
	    		<button type="text" class="btn btn-outline-secondary alterButton"
                    id="fbButton">
	    			<img src="<?php echo config('MYDOMAIN'); ?>/images/facebook.jpg" style="height:90%" alt="facebook" title="facebook" />
	    		</button>
	    		<?php endif; ?>
	    	</div>
            <div>&nbsp;</div>
		    <form class="form" method="post" id="frmLoginForm"
		    	action="<?php echo config('MYDOMAIN'); ?>/openid/dologin" target="_self">
				<input type="hidden" name="<?php echo $p->csrToken; ?>" value="1" />
				<div class="form-group">
					<label><?php echo txt('nickname'); ?></label>
					<input type="text" name="nickname" id="nickname" class="form-control"
						value="<?php echo $p->nickname; ?>" size="32" style="width:350px" />
				</div>
				<div class="form-group">
					<label><?php echo txt('PASSWORD'); ?></label>
					<input type="password" name="psw" id="psw" class="form-control"
						value="" size="32" style="width:350px" />
				</div>
				<?php if (($p->scope != '') & ($p->clientTitle != 'self')) : ?>
    		    <div class="scope">
    		    	<p><label>
                        <strong><?php echo $p->clientTitle; ?></strong>&nbsp;
                        <?php echo txt('SCOPE_TO_CLIENT'); ?>
                       </label>
                    </p>
    		    	<p><var><?php echo $this->scopeTxt($p->scope); ?></var></p>
    		    </div>
    		    <?php endif; ?>
    		    <div class="formLinks">
    		    	<p> <em class="fa fa-hand-o-right"></em>
    		    		<a href="<?php echo config('MYDOMAIN'); ?>/opt/adatkezeles/show" target="_new">
    		    			<?php echo txt('UKLOGIN_POLICY'); ?></a>
    		    	</p>
    		    	<?php if (($p->policy_uri != '') & ($p->clientTitle != 'self')) : ?>
    		    	<p> <em class="fa fa-hand-o-right"></em>
    		    		<a href="<?php echo $p->policy_uri; ?>" target="_new">
    		    			<?php echo $p->clientTitle; ?>&nbsp;<?php echo txt('CLIENT_POLICY'); ?></a>
    		    	</p>
    		    	<?php endif; ?>
    		    </div>
				<div class="form-group">
					<input type="checkbox" name="dataprocessaccept"
						 id = "dataprocessaccept" value="1" />
					<strong><?php echo txt('ACCEPT_POLICY'); ?></strong>
                    &nbsp;
					<button type="button" id="btnOk" class="btn btn-primary">
						<em class="fa fa-check"></em><?php echo txt('LOGIN'); ?>
					</button>
				</div>
    		    <div class="formLinks">
    		    	<p><em class="fa fa-hand-o-right"></em>
    		    		<a href="" id="forgetpswlink" target="_self">
    		    			<?php echo txt('FORGET_MY_PASSWORD'); ?>
    		    	   	</a>
    		    	</p>
    		    	<p><em class="fa fa-hand-o-right"></em>
    		    		<a href="index.php/openid/registform" target="_self">
    		    			<?php echo txt('NO_ACCOUNT_REGIST'); ?>
    		    	   	</a>
    		    	</p>
				</div>
		   	</form>
		  </div>
        </div>
        <?php $this->echoHtmlPopup(); ?>
        <?php $this->loadJavaScript('openid',$p); ?>
        <?php $this->echoJsLngDefs([
            'DATAPROCESS_ACCEPT_REQUIRED',
            'PSW_REQUIRED',
            'NICK_REQUIRED',
            'MYDOMAIN'
        ]); ?>
		</body>
        </html>
        <?php
    }


    /**
     * scopeForm kirajzolása
     * @param Params $p - msgs, nickname, cientTitle, scope, policy_uri
     */
    public function scopeForm($p) {
        $this->echoHtmlHead($p);
        ?>
        <body>
	    <div id="scopeForm" style="display:none" class="scopeForm">
	      <div class="page">
	    	<?php $this->echoMsgs($p); ?>
	    	<em class="fa fa-sign-in formIcon"></em>
	    	<img class="formLogo" src="<?php echo config('MYDOMAIN'); ?>/templates/default/logo.png" alt="logo" />
		    <h2><?php echo $p->clientTitle; ?></h2>
		    <h3><?php echo txt('SCOPE_ACCEPT_FORM'); ?></h3>
		    <form class="form" method="post" id="frmScopeForm"
		    	action="<?php echo config('MYDOMAIN'); ?>/openid/doscopeform" target="_self">
				<input type="hidden" name="<?php echo $p->csrToken; ?>" value="1" />
				<div class="form-group">
					<label><?php echo txt('nickname'); ?>:</label>
					<var><strong><?php echo $p->nickname; ?></strong></var>
				</div>
				<?php if ($p->scope != '') : ?>
    		    <div class="scope">
    		    	<p><label><?php echo txt('SCOPE_TO_CLIENT'); ?></label></p>
    		    	<p><var><?php echo $this->scopeTxt($p->scope); ?></var></p>
    		    </div>
    		    <?php endif; ?>
    		    <div class="formLinks">
    		    	<p> <em class="fa fa-hand-o-right"></em>
    		    		<a href="<?php echo config('MYDOMAIN'); ?>/opt/adatkezeles/show" target="_new">
    		    			<?php echo txt('UKLOGIN_POLICY'); ?></a>
    		    	</p>
    		    	<?php if ($p->policy_uri != '') : ?>
    		    	<p> <em class="fa fa-hand-o-right"></em>
    		    		<a href="<?php echo $p->policy_uri; ?>" target="_new">
    		    			<?php echo txt('CLIENT_POLICY'); ?></a>
    		    	</p>
    		    	<?php endif; ?>
    		    </div>
				<div class="form-group">
					<input type="checkbox" name="dataprocessaccept"
						id="dataprocessaccept" value="1" />
					<strong><?php echo txt('ACCEPT_POLICY'); ?></strong>
				</div>
				<div class="form-control-buttons">
					<button type="button" id="btnOk" class="btn btn-primary">
						<em class="fa fa-check"></em><?php echo txt('OK'); ?>
					</button>
                    &nbsp;
					<a type="button" id="btnOk" class="btn btn-secondary"
                        href="<?php echo config('MYDOMAIN'); ?>">
						<em class="fa fa-ban"></em><?php echo txt('CANCEL'); ?>
					</a>
				</div>
				<p>&nbsp;</p>
		   	</form>
		  </div>
        </div>
        <?php $this->echoHtmlPopup(); ?>
        <?php $this->loadJavaScript('openid',$p); ?>
        <?php $this->echoJsLngDefs([
		  'DATAPROCESS_ACCEPT_REQUIRED']);
        ?>
		</body>
        </html>
        <?php
    }

    /**
	 * echo második regist form sessionban érkezik
	 * @param object $data {msgs, cssToken, nickname, email, address, id,
	 *    clientTitle, scope, policy_uri}
	 * @return void;}
	 */
	public function registForm2($data) {
	    $this->echoHtmlHead($data);
	    ?>
        <body>
	    <div id="registForm2" style="display:block" class="registForm2">
	      <div class="page" id="page">
	    	<?php $this->echoMsgs($data); ?>
		    <em class="fa fa-key formIcon"></em>
	    	<img class="formLogo" src="<?php echo config('MYDOMAIN'); ?>/templates/default/logo.png" alt="logo" />
			<?php if ($data->clientTitle != 'self') : ?>
		    <h2><?php echo $data->clientTitle; ?></h2>
		    <?php endif; ?>
		    <h3>Regisztráció</h3>
		    <p>A most létrehozandó regisztrációval több kliens programokba is beléphet.
		    Minden esetben  külön tájékoztatást kap a kiliens által kért adatokról. Az adat átadás csak akkor történik meg ha erhhez hozzájárul.</p>
			<form name="formRegist2" id="formRegist2"  class="form"
				action="<?php echo MYDOMAIN; ?>/index.php" method="post"
				target="_self">
				<input type="hidden" name="option" value="openid" />
				<input type="hidden" name="task" value="doregist" />
				<input type="hidden" name="id" value="<?php echo $data->id; ?>" />
				<input type="hidden" name="<?php echo $data->csrToken?>" value="1" />
				<div>&nbsp;</div>
				<blockquote class="alert alert-info signInfo">
					<h4>Aláírás és pdf információk</h4>
					Született: <?php echo $data->szuletesiNev.' '.
					   str_replace('-','.',$data->szuletesiDatum); ?><br />
					Anyja neve: <?php echo $data->anyjaNeve; ?><br />
					Lakcím: <?php echo $data->address; ?><br />
				</blockquote>
				<div class="form-group">
					<label><?php echo txt('USER'); ?></label>
					<?php if (!isset($data->nick) || ($data->nick == '')) : ?>
					<input type="text" name="nick" id="nick" value=""
					  class="form-control" style="width:350px" />
					<?php else : ?>
					<input type="hidden" name="nick" value="<?php echo $data->nick; ?>" class="form-control" />
					<var><?php echo $data->nick;  ?></var>
					<?php endif; ?>
				</div>
				<div class="form-group">
					<?php if (!isset($data->nick) || ($data->nick == '')) : ?>
					<label><?php echo txt('LBL_PSW3'); ?></label>
					<?php else : ?>
					<label><?php echo txt('LBL_NEW_PSW'); ?></label>
					<?php endif; ?>
					<input type="password" name="psw1" id="psw1" class="form-control"
						value=""  style="width:350px" />
				</div>
				<div class="form-group">
					<?php if (!isset($data->nick) || ($data->nick == '')) : ?>
					<label><?php echo txt('LBL_PSW4'); ?></label>
					<?php else : ?>
					<label><?php echo txt('LBL_NEW_PSW2'); ?></label>
					<?php endif; ?>
					<input type="password" name="psw2" id="psw2" class="form-control"
						value="" style="width:350px" />
				</div>

				<?php if (config('OPENID') == 2) :?>
				<div class="form-group">
					<label>E-mail:</label>
					<input type="text" name="email" id="email" class="form-control"
						value="<?php echo $data->email; ?>" />
				</div>
				<div class="form-group">
					<label>Telefonszám:</label>
					<input type="text" name="phone_number" id="phone_number" class="form-control"
						value="<?php echo $data->phone_number; ?>" />
				</div>
				<div class="form-group">
					<label>Nem:</label>
					<select name="gender" id="gender" class="form-control" style="width:200px">
						<option value="">Válaszd ki!</option>
						<option value="man">férfi</option>
						<option value="woman">nő</option>
					</select>
				</div>

				<?php endif; ?>
    			<?php if (($data->scope != '') & ($data->clientTitle != 'self')) : ?>
    		    <div class="scope">
    		    	<p><label>A kliens progam a következő adatokat kapja meg:</label></p>
    		    	<p><var><?php echo $this->scopeTxt($data->scope); ?></var></p>
    		    </div>
    		    <?php endif; ?>
    		    <div class="formLinks">
    		    	<p> <em class="fa fa-hand-o-right"></em>
    		    		<a href="<?php echo config('MYDOMAIN'); ?>/opt/adatkezeles/show" target="_new">
    		    			uklogin openid rendszer adatkezelési leírása</a>
    		    	</p>
    		    	<?php if (($data->policy_uri != '') & ($data->clientTitle != 'self')) : ?>
    		    	<p> <em class="fa fa-hand-o-right"></em>
    		    		<a href="<?php echo $data->policy_uri; ?>" target="_new">
    		    			Kliens program adatkezelési leírása</a>
    		    	</p>
    		    	<?php endif; ?>
    		    </div>

				<div class="form--group">
					<input type="checkbox" name="dataprocessaccept" id="dataProcessAccept"
						value="1" />
					Az adatkezeléshez hozzájárulok
				</div>
				<div class="form-control-buttons">
					<button type="button" id="formRegist2Ok" class="btn btn-primary">
						<em class="fa fa-check"></em><?php echo txt('OK'); ?>
					</button>
				</div>
			</form>
		   </div>
	    </div>
        <?php $this->echoHtmlPopup(); ?>
        <?php $this->loadJavaScript('openid',$data); ?>
        <?php $this->echoJsLngDefs([
		  'PSW_REQUIRED',
		  'ERROR_PSW_INVALID',
		  'NICK_REQUIRED',
		  'PASSWORDS_NOTEQUALS',
		  'DATAPROCESS_ACCEPT_REQUIRED']);
        ?>
		</body>
        </html>
        <?php
	}

	/**
	 * profil form
	 * @param Params $data - user rekord mezői
	 */
	public function profileform(Params $data) {
	    $this->echoHtmlHead($data);
	    ?>
        <body>
        <?php $this->echoNavBar($data); ?>
	    <div id="profileForm" style="display:block" class="profileForm">
	      <div class="page" id="page">
	    	<?php $this->echoMsgs($data); ?>
		    <em class="fa fa-user formIcon"></em>
	    	<img class="formLogo" src="<?php echo config('MYDOMAIN'); ?>/templates/default/logo.png" alt="logo" />
		    <h2>Felhasználói profil</h2>
		    <p>
		    <?php if ($data->picture != '') : ?>
		      <img src="<?php echo $data->picture; ?>" style="height:125px;" alt="avatar">
		    <?php else : ?>
		      <img src="images/guest.jpg" style="height:125px;" alt="avatar">
		    <?php endif;?>
		    </p>
			<form name="formProfile" id="formProfile"  class="form audited<?php echo $data->audited; ?>"
				action="<?php echo MYDOMAIN; ?>/index.php" method="post"
				target="_self">
				<input type="hidden" name="option" value="openid" />
				<input type="hidden" name="task" value="profilesave" />
				<input type="hidden" name="id" value="<?php echo $data->id; ?>" />
				<input type="hidden" name="<?php echo $data->csrToken?>" value="1" />
				<div>&nbsp;</div>

				<?php if ((config('OPENID') == 2) & ($data->audited == 1)): ?>
				<blockquote class="alert alert-info signInfo">
					Név: <?php echo $data->family_name.' '.
									$data->middle_name.' '.$data->given_name; ?>
					<br />Születési dátum:
					<?php echo str_replace('-','.',$data->birth_date); ?>
					<br />Lakcím:
					<?php echo $data->postal_code.' '.$data->locality.' '.$data->street_address; ?><br />
				</blockquote>
				<?php endif; ?>
				<?php if ((config('OPENID') == 1) & ($data->audited == 1)): ?>
				<blockquote class="alert alert-info signInfo">
					<br />Lakcím:
					<?php echo $data->postal_code.' '.$data->locality; ?><br />
				</blockquote>
				<?php endif; ?>

				<div class="form-group">
					<label><?php echo txt('USER'); ?></label>
					<strong>
					<input type="text" name="nickname" value="<?php echo $data->nickname; ?>" disabled="disabled" class="form-control" />
					</strong>
				</div>
				<div class="form-group">
					<?php if (!isset($data->nick) || ($data->nickname == '')) : ?>
					<label><?php echo txt('LBL_PSW3'); ?></label>
					<?php else : ?>
					<label><?php echo txt('LBL_NEW_PSW'); ?></label>
					<?php endif; ?>
					<input type="password" name="psw1" id="psw1" class="form-control"
						value=""  style="width:350px" />
				</div>
				<div class="form-group">
					<?php if (!isset($data->nick) || ($data->nickname == '')) : ?>
					<label><?php echo txt('LBL_PSW4'); ?></label>
					<?php else : ?>
					<label><?php echo txt('LBL_NEW_PSW2'); ?></label>
					<?php endif; ?>
					<input type="password" name="psw2" id="psw2" class="form-control"
						value="" style="width:350px" />
				</div>
				<p>Ha jelszót nem akarsz változtatni akkor a két jelszó mezőt hagyd üresen!</p>

				<?php if ((config('OPENID') == 2) & ($data->audited != 1)): ?>
					<?php
					$name = $data->family_name.' '.	$data->middle_name.' '.$data->given_name;
					$address = $data->postal_code.' '.$data->locality.' '.$data->street_address;
					?>
					<div class="form-group">
						<label>Név:</label>
						<input type="text" name="name" id="name" class="form-control"
							value="<?php echo $name; ?>" style="width:600px" />
					</div>
					<div class="form-group">
						<label>Születési dátum )éééé,hh.nn):</label>
						<input type="text" name="birth_date" id="birth_date" class="form-control"
							value="<?php echo str_replace('-','.',$data->birth_date); ?>" style="width:350px" />
					</div>
					<div class="form-group">
						<label>Lakcím (ir.szám település utca házszám...):</label>
						<input type="text" name="address" id="addresse" class="form-control"
							value="<?php echo $address; ?>" style="width:600px" />
					</div>
				<?php endif; ?>

				<?php if ((config('OPENID') == 1) & ($data->audited != 1)): ?>
					<?php
					$address = $data->postal_code.' '.$data->locality;
					?>
					<div class="form-group">
						<label>Lakcím (ir.szám település):</label>
						<input type="text" name="address" id="addresse" class="form-control"
							value="<?php echo $address; ?>" style="width:600px" />
					</div>
				<?php endif; ?>

				<?php if (config('OPENID') == 2) :?>
				<div class="form-group">
					<label>E-mail:</label>
					<input type="text" name="email" id="email" class="form-control"
						value="<?php echo $data->email; ?>" />
				</div>
				<div class="form-group">
					<label>Telefonszám:</label>
					<input type="text" name="phone_number" id="phone_number" class="form-control"
						value="<?php echo $data->phone_number; ?>" />
				</div>
				<div class="form-group">
					<label>Avatar kép url:</label>
					<input type="text" name="picture" id="picture" class="form-control"
						value="<?php echo $data->picture; ?>" />
				</div>
				<div class="form-group">
					<label>Web site url:</label>
					<input type="text" name="profile" id="profile" class="form-control"
						value="<?php echo $data->profile; ?>" />
				</div>
				<div class="form-group">
					<label>Nem:</label>
					<select name="gender" id="gender" class="form-control" style="width:200px">
						<option value="man"<?php if ($data->gender == 'man') { echo ' selected="selected"'; } ?>>férfi</option>
						<option value="woman"<?php if ($data->gender == 'woman') { echo ' selected="selected"'; } ?>>nő</option>
					</select>
				</div>

				<?php endif; ?>
				<?php if ($data->sysadmin == 1) : ?>
				<p>system adminisztrátor</p>
				<?php endif; ?>
				<?php if ($data->audited == 1) : ?>
					<p style="color:green">Hiteles&nbsp;
					   Hitelesítés időpontja:<?php echo date('Y.m.d H:i:s', $data->audit_time); ?>
					</p>
				<?php else : ?>
					<p>
						<strong style="color:red">Nem hiteles</strong>&nbsp;
						<!--  a class="btn btn-secondary"
							href="<?php echo config('MYDOMAIN'); ?>/opt/auditor/info">Hitelesítés</a -->
					</p>
				<?php endif; ?>
				<p>Utolsó módosítás:<?php echo date('Y.m.d H:i:s', $data->updated_at); ?></p>

				<div class="form-control-buttons">
					<button type="button" id="formProfileOk" class="btn btn-primary">
						<em class="fa fa-check"></em>&nbsp;<?php echo txt('OK'); ?>
					</button>
					&nbsp;
					<button type="button" id="btnMyData" class="btn btn-secondary">
						<em class="fa fa-info"></em>&nbsp;<?php echo txt('MYDATA'); ?>
					</button>
					&nbsp;
					<button type="button" id="btnDelAccount" class="btn btn-danger">
						<em class="fa fa-ban"></em>&nbsp;<?php echo txt('DEL_MY_ACCOUNT'); ?>
					</button>

				</div>
			</form>
		   </div>
	    </div>
        <?php $this->echoHtmlPopup(); ?>
        <?php $this->loadJavaScript('openid',$data); ?>
        <?php $this->echoJsLngDefs([
		  'PSW_REQUIRED',
		  'ERROR_PSW_INVALID',
		  'NICK_REQUIRED',
		  'PASSWORDS_NOTEQUALS']);
        ?>
        <?php $this->echoFooter(); ?>
		</body>
        </html>
        <?php
	}

}
?>
