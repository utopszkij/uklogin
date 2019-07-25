<?php
include_once './views/common.php';
class AppregistView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function form($p) {
	    echo htmlHead();
	    
	    ?>
        <body ng-app="app">
	    <?php $this->echoNavbar($p); ?>
        <div ng-controller="ctrl" id="scope" style="display:none" class="appRegist">
            <?php if ($p->client_id == '') : ?>
                <h2><?php echo txt('NEWAPP'); ?></h2>
            <?php else : ?>
            	<p style="text-align:right">
            		<button type="button" id="logout">
            			<em class="fa fa-sign-out"></em><?php echo txt('LOGOUT') ?>
            		</button>
            	</p> 
            	<h2>client_id:&nbsp;<?php echo $p->client_id; ?></h2>
                <h3>client_secret:&nbsp;<?php echo $p->client_secret; ?></h2>
            <?php endif; ?>
            <div class="alert alert-warning" role="alert">
    			  <?php echo txt('SECRETINFO'); ?>
            </div>
            <?php if ($p->msg != '') : ?>
            	<div class="alert alert-danger" role="alert">
            	<?php
            	if (is_array($p->msg)) {
            	    foreach ($p->msg as $msg1) {
            	        echo txt($msg1).'<br />';
            	    }
            	} else {
            	   echo txt($p->msg);
            	}
            	?>
            	</div>
            <?php endif; ?>
    		<form id="formApp" name="formApp" action="<?php echo txt('MYDOMAIN'); ?>/index.php" method="post">
    			<input type="hidden" name="option" value="appregist" />
    			<input type="hidden" name="task" value="save" />
    			<input type="hidden" name="{{csrtoken}}" value="1" />
    			<input type="hidden" name="client_id" id="client_id" value="{{client_id}}" />
    			<?php if ($p->client_id != '') : ?>
                <fieldset class="userActivation">
                	<legend><?php echo txt('USERACTIVATION'); ?></legend>
                	<p>
                		<label><?php echo txt('USER'); ?></label>
                		<input type="text" name="user" id="user" value="" size="32" />
                		<button type="button" id="userActOk" class="btn btn-secondary"><?php echo txt('USRACTOK'); ?></button>
                	</p>
                </fieldset>
    			<?php endif; ?>
    			<fieldset class="appDatas">
    				<legend><?php echo txt('LBL_APPDATAS'); ?></legend>
    				<p>
    					<label><?php echo txt('LBL_TITLE'); ?></label>
    					<input type="text" name="name" id="name"value="{{name}}" size="100" class="appName" />
    				</p>
    				<p>
    					<label><?php echo txt('LBL_DOMAIN'); ?></label>
    					<input type="text" name="domain" id="domain" value="{{domain}}" size="100" 
    						placeholder="https://yourdomain.com" class="appDomain" />
    				</p>
    				<p>
    					<label><?php echo txt('LBL_CALLBACK'); ?></label>
    					<input type="text" name="callback" id="callback" value="{{callback}}" size="100" 
    						placeholder="https://yourdomain.com/index.php?opt=login&task=logged" class="appCallback" />
    				</p>
    				<p>
    					<label><?php echo txt('LBL_CSS'); ?></label>
    					<input type="text" name="css" id="css" size="100"  class="appCss" value="{{css}}" />
    				</p>
    				<p>
    					<label><?php echo txt('LBL_FALSELOGINLIMIT'); ?></label>
    					<input type="number" min="1" max="10" name="falseLoginLimit" value="{{falseLoginLimit}}" size="10" 
    						class="appFalseLoginLimit" />
    				</p>
    			</fieldset>
    			<fieldset class="appAdmin">
    				<legend><?php echo txt('LBL_APPADMIN'); ?></legend>
    				<p>
    					<label><?php echo txt('LBL_ADMIN'); ?></label>
    					<input type="text" name="admin" id="admin" value="{{admin}}" size="32" 
    						class="appAdmin" />
    				</p>
    				<p>
    					<label><?php echo txt('LBL_PSW1'); ?></label>
    					<input type="password" name="psw1" id="psw1" value="{{psw1}}" size="32" 
    						class="appPsw" />
    				</p>
    				<p>
    					<label><?php echo txt('LBL_PSW2'); ?></label>
    					<input type="password" name="psw2" id="psw2" value="{{psw2}}" size="32" 
    						class="appPsw" />
    				</p>
    				<?php if ($p->client_id != '') : ?>
    				<p><?php echo txt('PSWCHGINFO'); ?></p>
    				<?php endif; ?>
    				<p>
    					<label><?php echo txt('LBL_FALSEADMINLOGINLIMIT'); ?></label>
    					<input type="number" min="1" max="10" name="falseAdminLoginLimit" value="{{falseAdminLoginLimit}}" size="10" 
    						class="appFalseAdminLoginLimit" />
    				</p>
    			</fieldset>
    			<p>
    				<a href="<?php echo txt('MYDOMAIN'); ?>/opt/adatkezeles/show" target="_new">
    					<?php echo txt('DATAPROCESS');  ?></a>&nbsp;
    				<var><input type="checkbox" name="dataProcessAccept" id="dataProcessAccept" value="1" /></var>
    				<?php echo txt('DATAPROCESSACCEPT'); ?>&nbsp;&nbsp;
    				<var><input type="checkbox" name="cookieProcessAccept" id="cookieProcessAccept" value="1" /></var>
    				<?php echo txt('COOKIEPROCESSACCEPT'); ?>
    				
    			</p>
    			<p class="formButtons">
    				<button type="button" id="formAppOk" class="btn btn-primary">
    					<em class="fa fa-check-square"></em>
    					<?php echo txt('OK'); ?></button>&nbsp;
    				<button type="button" id="formAppOk" class="btn btn-secondary" 
    					onclick="location='<?php  echo MYDOMAIN; ?>';">
    					<em class="fa fa-arrow-left"></em>
    					<?php echo txt('CANCEL'); ?></button>&nbsp;
    					
    				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    				<?php if ($p->client_id != '') : ?>
    				<button type="button" id="formAppRemove" class="btn btn-danger">
    					<em class="fa fa-ban"></em>
    					<?php echo txt('APPREMOVE'); ?></button>&nbsp;
    				<?php endif; ?>	
    			</p>
    		</form>
        	<?php echo htmlPopup(); ?>
       	</div>
  	  
        <?php loadJavaScriptAngular('appregist',$p); ?>
		<?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 		
	}
	
	/**
	 * echo succes message after add new app
	 * @param object $res {client_id, client_secret
	 * @return void;}
	 */
	public function successMsg($res) {
	    echo htmlHead();
	    ?>
        <body ng-app="app">
	    <?php $this->echoNavbar($res); ?>
	    <div class="savedMsg">
	    	<h2 class="alert alert-success"><?php echo txt('APPSAVED'); ?></h2>
	    	<p>Client_id: <?php echo $res->client_id; ?></p>
	    	<p>Client_secret: <?php echo $res->client_secret; ?></p>
	    </div>
		<?php $this->echoFooter(); ?>
        </body>
        </html>
	    <?php 
	}
	
	/**
	 * echo fatal error in app save
	 * @return void
	 */
	public function errorMsg() {
	    echo htmlHead();
	    $p = new stdClass();
	    ?>
        <body ng-app="app">
	    <?php $this->echoNavbar($p); ?>
	    <div class="savedMsg">
	    <h2 class="alert alert-danger">appregist Fatal error in save</h2>
	    </div>
		<?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 
	}
}
?>