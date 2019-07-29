<?php
include_once './views/common.php';
class Oauth2View  extends CommonView  {
	
    /**
     * echo succes message after add new app
     * @param object $res {client_id, client_secret
     * @return void;}
     */
    public function successMsg($msgs) {
        echo htmlHead();
        $p = new stdClass();
        ?>
        <body ng-app="app">
	    <div class="successMsg">
	    <h2 class="alert alert-success">
			<?php 
			foreach ($msgs as $msg) {
			    echo txt($msg).'<br />';
			}
			?>
	    </h2>
	    </div>
        </body>
        </html>
        <?php 
	}
    
	/**
	 * echo fatal error in app save
	 * @param array of string
	 * @return void
	 */
	public function errorMsg(array $msgs, string $backLink='', string $backStr='') {
	    echo htmlHead();
	    $p = new stdClass();
	    ?>
        <body ng-app="app">
	    <div class="errorMsg">
	    <h2 class="alert alert-danger">
			<?php 
			foreach ($msgs as $msg) {
			    echo txt($msg).'<br />';
			}
			?>
	    </h2>
	    </div>
	    <?php if ($backLink != '') : ?>
	    <p><a href="<?php echo $backLink; ?>" target="_self"><?php echo txt($backStr); ?></a>
	    <?php endif; ?>
        </body>
        </html>
        <?php 
	}
	
	/**
	 * echo első regist form
	 * @param object $data {cssToken}
	 * @return void;}
	 */
	public function registForm1($data) {
	    echo htmlHead($data);
	    ?>
        <body ng-app="app">
	    <div id="registForm1">
		    <h2><?php echo $data->appName; ?></h2>
		    <h3><?php echo txt($data->title); ?></h3>
		    <p><?php echo txt('LBL_REGISTFORM1_HELP1'); ?></p>
		    <p><?php echo txt('LBL_PDF'); ?>
		    	<a href="<?php echo MYDOMAIN; ?>/index.php?option=oauth2&task=pdf&client_id=<?php echo $data->client_id; ?>">
		    		<?php echo txt('LBL_DOWNLOAD'); ?>
		    	</a>
		    </p>
		    <p><?php echo txt('LBL_REGISTFORM1_HELP2'); ?></p>
		    <p><?php echo txt('LBL_REGISTFORM1_HELP3'); ?></p>
		    <p><?php echo txt('LBL_REGISTFORM1_HELP4'); ?></p>
			<form name="formRegist1" id="formRegist1" 
				action="<?php echo MYDOMAIN; ?>/index.php" method="post" 
				target="_self" enctype="multipart/form-data">
				<input type="hidden" name="option" value="oauth2" />
				<input type="hidden" name="task" value="registform2" />
				<input type="hidden" name="nick" value="<?php echo $data->nick; ?>" />
				<input type="hidden" name="<?php echo $data->csrToken?>" value="1" />
				<p style="background-color:#ade9ed; padding:4px;">
					<lable><?php echo txt('LBL_SIGNEDPDF'); ?></lable>
					<input type="file" name="signed_pdf" />
				</p>
				<p>
					<button type="submit" class="btn btn-primary"><?php echo txt('NEXTSTEP'); ?></button>
					&nbsp;
					<button type="button" class="btn btn-secondary" id="btnCancel"><?php echo txt('CANCEL'); ?></button>
				</p>
			</form>		    
	    </div>
		</body>
        </html>
        <?php 
	}
	
	/**
	 * echo második regist form
	 * @param object $data {cssToken}
	 * @return void;}
	 */
	public function registForm2($data) {
	    echo htmlHead($data);
	    ?>
        <body ng-app="app">
	    <div id="registForm2">
		    <h2><?php echo $data->appName; ?></h2>
		    <h3><?php echo txt($data->title); ?></h3>
		    <?php if (count($data->msgs) > 0) : ?>
		    	<p class="alert alert-danger">
		    	<?php
		    	foreach($data->msgs as $msg) {
		    	    echo $msg.'<br />';
		    	}
		    	?>
		    	</p>
		    <?php endif; ?>
			<form name="formRegist2" id="formRegist2" 
				action="<?php echo MYDOMAIN; ?>/index.php" method="post" 
				target="_self">
				<input type="hidden" name="option" value="oauth2" />
				<input type="hidden" name="task" value="doregist" />
				<input type="hidden" name="<?php echo $data->csrToken?>" value="1" />
				<p>
					<label><?php echo txt('USER'); ?></label>
					<?php if ($data->nick == '') : ?>
					<input type="text" name="nick" value="<?php echo $data->nick; ?>"
					   value="<?php $data->nick ?>?>" />
					<?php else : ?>
					<input type="hidden" name="nick" value="<?php echo $data->nick; ?>"
					   value="<?php $data->nick ?>?>" />
					<var><?php echo $data->nick;  ?></var>
					<?php endif; ?>   
				</p>
				<p>
					<label><?php echo txt('LBL_PSW3'); ?></label>
					<input type="password" name="psw1" value="<?php echo $data->psw1; ?>" />
				</p>
				<p>
					<label><?php echo txt('LBL_PSW4'); ?></label>
					<input type="password" name="psw2" value="<?php echo $data->psw2; ?>" />
				</p>
				<p>
					<button type="submit" class="btn btn-primary"><?php echo txt('OK'); ?></button>
					&nbsp;
					<button type="button" class="btn btn-secondary" id="btnCancel"><?php echo txt('CANCEL'); ?></button>
				</p>
			</form>		    
	    </div>
		</body>
        </html>
        <?php 
	}
}
?>