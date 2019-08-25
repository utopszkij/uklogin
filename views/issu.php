<?php
include_once './views/common.php';
class IssuView  extends CommonView  {
	
    /**
	* echo html form
	* @param object $p  {msgs, title, body, sender, email}
	* @return void
	*/
	public function form($p) {
	    $this->echoHtmlHead();
	    $p->adminNick = '';
	    $this->echoNavbar($p);
	    ?>
        <body ng-app="app">
    	    <div ng-controller="ctrl" id="scope" style="display:block; padding:10px;">
        	    <h2><?php echo txt('LBL_ISSU'); ?></h2>
        	    <?php if (count($p->msgs) > 0) : ?>
        	    <div class="alert alert-danger">
        			<?php 
        			foreach ($p->msgs as $msg) {
        			    echo txt($msg).'<br />';
        			}
        			?>
        	    </div>
        	    <?php endif; ?>
    			<form name="issuForm" id="issuForm" method="post" 
    				  action="<?php echo MYDOMAIN; ?>/opt/issu/send">
    				  <p>
    				  	<label><?php echo txt('LBL_ISSU_TITLE'); ?></label><br />
    				  	<input name="title" size="60" value="<?php echo $p->title; ?>"></input>
    				  </p>
    				  <p>
    				  	<label><?php echo txt('LBL_ISSU_BODY'); ?></label><br />
    				  	<textarea name="body" cols="60" rows="10"><?php echo $p->body; ?></textarea>
    				  </p>
    				  <p>
    				  	<label><?php echo txt('LBL_ISSU_SENDER'); ?></label><br />
    				  	<input name="sender" size="60" value="<?php echo $p->sender; ?>"></input>
    				  </p>
    				  <p>
    				  	<label><?php echo txt('LBL_ISSU_EMAIL'); ?></label><br />
    				  	<input name="email" size="60" value="<?php echo $p->email; ?>"></input>
    				  </p>
    				  <p>
    				  	<button type="submit" class="btn btn-primary">
    				  		<em class="fa fa-check"></em>
    				  		<?php echo txt('OK'); ?>
    				  	</button>
    				  	&nbsp;&nbsp;
    				  	<button type="button" class="btn btn-secondary" onclick="location=<?php echo MYDOMAIN; ?>">
    				  		<em class="fa fa-arrow-left"></em>
    				  		<?php echo txt('CANCEL'); ?>
    				  	</button>
    				  </p>
    			</form>
    	    </div>
	    	<?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 		
	}
	
	/**
	 * echo sikeressen tárolva üzenet
	 * @return void;
	 */
	public function successMsg() {
	    $this->echoHtmlHead();
	    $p = new stdClass();
	    $p->adminNick = '';
	    $this->echoNavbar($p);
	    ?>
        <body ng-app="app">
    	    <div ng-controller="ctrl" id="scope" style="display:block" class="successMsg">
        	    <h2><?php echo txt('LBL_ISSU'); ?></h2>
        	    <h2 class="alert alert-success"><?php echo txt('ISSU_SAVED'); ?></h2>
    	    </div>
	    	<?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 		
	}
}
?>

