<?php
include_once './views/common.php';
class Oauth2View  extends CommonView  {
	
	public function loginform($data) {
	    $this->echoHtmlHead($data);
	    ?>
        <body ng-app="app">
	    <div ng-controller="ctrl" id="scope" style="display:none" class="loginForm">
		    <h2><?php echo $data->appName; ?></h2>
		    <?php if (count($data->msgs) > 0) : ?>
		    	<p class="alert alert-danger">
		    	<?php
		    	foreach($data->msgs as $msg) {
		    	    echo txt($msg).'<br />';
		    	}
		    	?>
		    	</p>
		    <?php endif; ?>
			<form name="formLogin" id="formLogin" 
				action="<?php echo MYDOMAIN; ?>/index.php" method="post" 
				target="_self">
				<input type="hidden" name="option" id="option" value="oauth2" />
				<input type="hidden" name="task" id="task" value="dologin" />
				<input type="hidden" name="<?php echo $data->csrToken?>" value="1" />
				<p>
					<label><?php echo txt('USER'); ?></label>
					<input type="text" name="nick" id="nick" value="<?php echo $data->nick; ?>"
					   value="<?php $data->nick ?>?>" />
				</p>
				<p>
					<label><?php echo txt('LBL_PSW3'); ?></label>
					<input type="password" name="psw1" id="psw1" value="" />
				</p>
				<p>
					<button type="button" id="formLoginOk" class="btn btn-primary"><?php echo txt('LOGIN'); ?></button>
				</p>
				<p>
				<a href="<?php echo MYDOMAIN; ?>/opt/userregist/registform/client_id/<?php echo $data->client_id; ?>"
					target="_self">
					<?php echo txt('NOTMYACCOUNT');  ?>
				</a><br />
				<a style="cursor:pointer" onclick="forgetpswClick()">
					<?php echo txt('FORGET_PSW');  ?>
				</a><br />
				<a style="cursor:pointer" onclick="changepswClick()">
					<?php echo txt('CHANGE_PSW');  ?>
				</a><br />
				<a style="cursor:pointer" onclick="mydataClick()">
					<?php echo txt('MY_DATA');  ?>
				</a><br />
				<a style="cursor:pointer" onclick="delAccountClick()">
					<?php echo txt('DELETE_ACCOUNT');  ?>
				</a><br />
				</p>
			</form>		    
	    </div>
        <?php $this->echoHtmlPopup(); ?>
        <?php $this->loadJavaScriptAngular('oauth2',$data); ?>
	    <script type="text/javascript">
	    
        function delAccountClick() {
            if (confirm('<?php echo txt('SURE_DELETE_ACCOUNT'); ?>')) {
                $('#option').val('userregist')
                $('#task').val('deleteaccount'); 
                $('#formLogin').submit();
            }
        }
        function mydataClick() {
            $('#option').val('userregist')
            $('#task').val('mydata'); 
            $('#formLogin').submit();
        }
        function changepswClick() {
            $('#option').val('userregist')
            $('#task').val('changepsw'); 
            $('#formLogin').submit();
        }
        function forgetpswClick() {
            $('#option').val('userregist')
            $('#task').val('forgetpsw'); 
            $('#formLogin').submit();
        }
        </script>
		</body>
        </html>
        <?php 
	}
}
?>