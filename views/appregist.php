<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

include_once './views/common.php';

/**
 * AppregistView osztály
 * @author utopszkij
 */
class AppregistView  extends CommonView  {
	/**
	* echo html page
	* @param Params $p
	* @return void
	*/
	public function form(Params $p) {
	    $this->echoHtmlHead();
	    ?>
        <body ng-app="app">
	    <?php $this->echoNavbar($p); ?>
        <div ng-controller="ctrl" id="scope" style="display:none" class="appRegist">
            <?php if ($p->client_id == '') : ?>
                <h2><?php echo txt('NEWAPP'); ?></h2>
            <?php else : ?>
            	<p id="pAppsSelect">
            	<?php echo txt('LBL_TITLE')?>:&nbsp;<select id="appsSelect">
            	<?php 
            	foreach ($p->apps as $app) {
            	    if ($app->client_id == $p->client_id) {
            	        echo '<option selected="selected" value="'.$app->client_id.'">'.$app->name.'</option>';
            	    } else {
            	        echo '<option value="'.$app->client_id.'">'.$app->name.'</option>';
            	    }
            	}
            	?>
            	</select>
            	</p>
                <h3>client_id:&nbsp;<?php echo $p->client_id; ?></h2>
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
    		<form id="formApp" name="formApp" action="<?php echo txt('MYDOMAIN'); ?>/index.php" 
    			method="post" target="_self">
    			<input type="hidden" name="option" id="option" value="appregist" />
    			<input type="hidden" name="task" id="task" value="save" />
    			<input type="hidden" name="{{csrToken}}" value="1" />
    			<input type="hidden" name="client_id" id="client_id" value="{{client_id}}" />
    			<input type="hidden" name="client_secret" id="client_secret" value="{{client_secret}}" />
    			<input type="hidden" name="id" id="id" value="{{id}}" />
    			<?php if ($p->client_id != '') : ?>
                <fieldset class="userActivation" style="display:none">
                	<legend><?php echo txt('USERACTIVATION'); ?></legend>
                	<p>
                		<label><?php echo txt('USER'); ?></label>
                		<input type="text" name="nick" id="nick" value="" size="32" />
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
    				<p style="display:none">
    					<label><?php echo txt('LBL_CSS'); ?></label>
    					<input type="text" name="css" id="css" size="100"  class="appCss" value="{{css}}" />
    				</p>
    				<p style="display:none">
    					<label><?php echo txt('LBL_FALSELOGINLIMIT'); ?></label>
    					<input type="number" min="1" max="10" name="falseLoginLimit" value="{{falseLoginLimit}}" size="10" 
    						class="appFalseLoginLimit" />
    				</p>
    			</fieldset>
    			<fieldset class="appAdmin">
    				<legend><?php echo txt('LBL_APPADMIN'); ?></legend>
    				<p>
    					<label><?php echo txt('LBL_ADMIN'); ?></label>
    					<input type="hidden" name="admin" id="admin" value="{{admin}}" size="32" 
    						class="appAdmin" />
    					<var>{{admin}}</var>	
    				</p>
    			</fieldset>
   				<?php if ($p->client_id == '') : ?>
    			<p>
    				<a href="<?php echo txt('MYDOMAIN'); ?>/opt/adatkezeles/show" target="_new">
    				<?php echo txt('DATAPROCESS');  ?></a>&nbsp;
    				<div style="display:inline-block; width:auto">
    					<var><input type="checkbox" name="dataProcessAccept" id="dataProcessAccept" value="1"  /></var>
    					<?php echo txt('DATAPROCESSACCEPT'); ?>&nbsp;&nbsp;
    				</div>
    				<div style="display:none">
	    				<var><input type="checkbox" name="cookieProcessAccept" id="cookieProcessAccept" value="1" /></var>
    					<?php echo txt('COOKIEPROCESSACCEPT'); ?>
    				</div>
    			</p>
    			<?php else :?>
    			<p style="display:block">
    				<input type="checkbox" name="dataProcessAccept" id="dataProcessAccept" value="1" checked="checked"  /></var>
    				Adatkezeléshez hosszájárulok
    				<input type="hidden" name="cookieProcessAccept" id="cookieProcessAccept" value="1" checked="checked"/></var>
    			</p>
    			<?php endif; ?>
    			<p class="formButtons">
    				<button type="button" id="formAppOk" class="btn btn-primary">
    					<em class="fa fa-check-square"></em>
    					<?php echo txt('OK'); ?></button>&nbsp;
    				<button type="button" id="formAppCancel" class="btn btn-secondary" 
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
        	<?php $this->echoHtmlPopup(); ?>
       	</div>
  	  
        <?php $this->loadJavaScriptAngular('appregist',$p); ?>
		<?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 		
	}
	
}
?>