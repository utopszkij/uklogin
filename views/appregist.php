<?php
include_once './views/common.php';
class AppregistView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function form($p) {
	    $this->echoHtmlHead();
	    ?>
        <body ng-app="app">
	    <?php $this->echoNavbar($p); ?>
        <div ng-controller="ctrl" id="scope" style="display:none" class="appRegist">
            <h2 ng-if="client_id == ''">{{txt('NEWAPP')}}</h2>
            <div ng-show="client_id != ''">
            	<p id="pAppsSelect">
            	{{txt('LBL_TITLE')}}:&nbsp;<select id="appsSelect">
            	<option ng-repeat="app in apps" value="{{app.client_id}}">{{app.name}}</option>
            	</select>
            	</p>
                <h3>client_id:&nbsp;{{client_id}}</h2>
                <h3>client_secret:&nbsp;{{client_secret}}</h2>
            </div>
            <div class="alert alert-warning" role="alert">
    			  <?php echo txt('SECRETINFO'); ?>
            </div>
            <div ng-if="msg != ''" class="alert alert-danger" role="alert">
            	<p ng-repeat="msg1 in msg">{{txt(msg1)}}</p>
            </div>
    		<form id="formApp" name="formApp" action="<?php echo txt('MYDOMAIN'); ?>/index.php" 
    			method="post" target="_self">
    			<input type="hidden" name="option" id="option" value="appregist" />
    			<input type="hidden" name="task" id="task" value="save" />
    			<input type="hidden" name="{{csrToken}}" value="1" />
    			<input type="hidden" name="client_id" id="client_id" value="{{client_id}}" />
    			<input type="hidden" name="client_secret" id="client_secret" value="{{client_secret}}" />
    			<input type="hidden" name="id" id="id" value="{{id}}" />
                <fieldset ng-show="client_id != ''" class="userActivation">
                	<legend>{{txt('USERACTIVATION')}}</legend>
                	<p>
                		<label>{{txt('USER')}}</label>
                		<input type="text" name="nick" id="nick" value="" size="32" />
                		<button type="button" id="userActOk" class="btn btn-secondary">
                			{{txt('USRACTOK')}}
                		</button>
                	</p>
                </fieldset>
    			<fieldset class="appDatas">
    				<legend>{{txt('LBL_APPDATAS')}}</legend>
    				<p>
    					<label>{{txt('LBL_TITLE')}}</label>
    					<input type="text" name="name" id="name"value="{{name}}" size="100" class="appName" />
    				</p>
    				<p>
    					<label>{{txt('LBL_DOMAIN')}}</label>
    					<input type="text" name="domain" id="domain" value="{{domain}}" size="100" 
    						placeholder="https://yourdomain.com" class="appDomain" />
    				</p>
    				<p>
    					<label>{{txt('LBL_CALLBACK')}}</label>
    					<input type="text" name="callback" id="callback" value="{{callback}}" size="100" 
    						placeholder="https://yourdomain.com/index.php?opt=login&task=logged" class="appCallback" />
    				</p>
    				<p>
    					<label>{{txt('LBL_CSS')}}</label>
    					<input type="text" name="css" id="css" size="100"  class="appCss" value="{{css}}" />
    				</p>
    				<p>
    					<label>{{txt('LBL_FALSELOGINLIMIT')}}</label>
    					<input type="text" min="1" max="10" name="falseLoginLimit" value="{{falseLoginLimit}}" size="10" 
    						class="appFalseLoginLimit" />
    				</p>
    			</fieldset>
    			<fieldset class="appAdmin">
    				<legend>{{txt('LBL_APPADMIN')}}</legend>
    				<p>
    					<label>{{txt('LBL_ADMIN')}}</label>
    					<input type="hidden" name="admin" id="admin" value="{{admin}}" size="32" 
    						class="appAdmin" />
    					<var>{{admin}}</var>	
    				</p>
    			</fieldset>
    			<p ng-if="client_id == ''">
    				<a href="<?php echo txt('MYDOMAIN'); ?>/opt/adatkezeles/show" target="_new">
    				<?php echo txt('DATAPROCESS');  ?></a>&nbsp;
    				<div style="display:inline-block; width:auto">
    					<var><input type="checkbox" name="dataProcessAccept" id="dataProcessAccept" value="1"  /></var>
    					<?php echo txt('DATAPROCESSACCEPT'); ?>&nbsp;&nbsp;
    				</div>
    				<div style="display:inline-block; width:auto">
	    				<var><input type="checkbox" name="cookieProcessAccept" id="cookieProcessAccept" value="1" /></var>
    					<?php echo txt('COOKIEPROCESSACCEPT'); ?>
    				</div>	
    			</p>
    			<p ng-id="client_id != ''" style="display:none">
    				<input type="checkbox" name="dataProcessAccept" id="dataProcessAccept" value="1" checked="checked"  /></var>
    				<input type="checkbox" name="cookieProcessAccept" id="cookieProcessAccept" value="1" checked="checked" /></var>
    			</p>
    			<p class="formButtons">
    				<button type="button" id="formAppOk" class="btn btn-primary">
    					<em class="fa fa-check-square"></em>{{txt('OK')}}
    				</button>&nbsp;
    				<button type="button" id="formAppCancel" class="btn btn-secondary" 
    					onclick="location='<?php  echo MYDOMAIN; ?>';">
    					<em class="fa fa-arrow-left"></em>{{txt('CANCEL')}}
    				</button>
    				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    				<button ng-show="client_id != ''" type="button" id="formAppRemove" class="btn btn-danger">
    					<em class="fa fa-ban"></em>{{txt('APPREMOVE')}}
    				</button>&nbsp;
    			</p>
    		</form>
        	<?php $this->echoHtmlPopup(); ?>
       	</div>
		<?php $this->echoFooter(); ?>
        <?php $this->loadJavaScriptAngular('appregist',$p); ?>
        </body>
        </html>
        <?php 		
	}
	
	/**
	 * echo succes message after add or update new app
	 * @param object $res {client_id, client_secret
	 * @return void;}
	 */
	public function AppsuccessMsg($res) {
	    $this->echoHtmlHead();
	    ?>
        <body ng-app="app">
	    <?php $this->echoNavbar($res); ?>
        <div ng-controller="ctrl" id="scope" style="display:none" class="appRegist">
    	    <div class="savedMsg" id="scope">
    	    	<h2 class="alert alert-success">{{txt('APPSAVED')}}</h2>
    	    	<p>Client_id: {{client_id}}</p>
    	    	<p>Client_secret: {{client_secret}}</p>
    	    	<p><?php echo txt('ADMININFO'); ?></p>
    	    </div>
        </div>
		<?php $this->echoFooter(); ?>
        <?php $this->loadJavaScriptAngular('appregist',$res); ?>
        </body>
        </html>
	    <?php 
	}
	
	/**
	 * echo not found error
	 * @param string $msg
	 * @return void
	 */
	public function removedMsg($rec) {
	    $this->echoHtmlHead();
	    ?>
        <body ng-app="app">
	    <?php $this->echoNavbar($rec); ?>
        <div ng-controller="ctrl" id="scope" style="display:none" class="appRegist">
    	    <div class="successMsg" id="scope">
    	    <h2 class="alert alert-success">{{txt('APPREMOVED')}}</h2>
    	    <p>{{name}}</p>
    	    </div>
        </div>
		<?php $this->echoFooter(); ?>
        <?php $this->loadJavaScriptAngular('appregist',$rec); ?>
        </body>
        </html>
        <?php 
	}
	
}
?>