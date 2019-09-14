<?php
include_once './views/common.php';
class UserregistView  extends CommonView  {
	
	/**
	 * echo első regist form
	 * @param object $data {cssToken}
	 * @return void;}
	 */
	public function registForm1($data) {
	    $this->echoHtmlHead($data);
	    ?>
        <body ng-app="app">
	    <div ng-controller="ctrl" id="scope" style="display:none;" class="registForm1">
		    <h2>{{appName}}</h2>
		    <h3>{{txt(title)}}</h3>
		    <p><?php echo txt('LBL_REGISTFORM1_HELP1'); ?></p>
		    <p>{{txt('LBL_PDF')}}
		    	<a href="<?php echo MYDOMAIN; ?>/index.php?option=userregist&task=pdf&client_id=<?php echo $data->client_id; ?>">
		    		{{txt('LBL_DOWNLOAD')}}
		    	</a>
		    </p>
		    <p><?php echo txt('LBL_REGISTFORM1_HELP2'); ?></p>
		    <p><?php echo txt('LBL_REGISTFORM1_HELP3'); ?></p>
		    <p><?php echo txt('LBL_REGISTFORM1_HELP4'); ?></p>
			<form name="formRegist1" id="formRegist1" 
				action="<?php echo MYDOMAIN; ?>/index.php" method="post" 
				target="_self" enctype="multipart/form-data">
				<input type="hidden" name="option" value="userregist" />
				<input type="hidden" name="task" value="registform2" />
				<input type="hidden" name="nick" value="{{nick}}" />
				<input type="hidden" name="{{csrToken}}" value="1" />
				<p style="background-color:#ade9ed; padding:4px;">
					<lable>{{txt('LBL_SIGNEDPDF')}}</lable>
					<input type="file" name="signed_pdf" />
				</p>
				<p>
					<button type="submit" class="btn btn-primary">
						{{txt('NEXTSTEP')}}
					</button>
				</p>
			</form>
	    </div>
        <?php $this->echoHtmlPopup(); ?>
        <?php $this->loadJavaScriptAngular('userregist',$data); ?>
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
	    $this->echoHtmlHead($data);
	    ?>
        <body ng-app="app">
	    <div ng-controller="ctrl" id="scope" style="display:none" class="registForm2">
	    
		    <h2>{{appName}}</h2>
		    <h3>{{txt(title)}}</h3>
		    <div ng-if="msgs.length() > 0">
		    	<p class="alert alert-danger">
		    	<p ng-repeat="msg of msgs">{{msg}}</p> 
		    </div>
			<form name="formRegist2" id="formRegist2" 
				action="<?php echo MYDOMAIN; ?>/index.php" method="post" 
				target="_self">
				<input type="hidden" name="option" value="userregist" />
				<input type="hidden" name="task" value="doregist" />
				<input type="hidden" name="{{csrToken}}" value="1" />
				<p>
					<label>{{txt('USER')}}</label>
					<?php if (!isset($data->nick) || ($data->nick == '')) : ?>
					<input type="text" name="nick" id="nick" value="" />
					<?php else : ?>
					<input type="hidden" name="nick" value="<?php echo $data->nick; ?>"  />
					<var><?php echo $data->nick;  ?></var>
					<?php endif; ?>   
				</p>
				<p>
					<label ng-if="((nick == undefined) | (nick == ''))">{{txt('LBL_PSW3')}}</label>
					<label ng-if="nick > ' '">{{txt('LBL_NEW_PSW')}}</label>
					<input type="password" name="psw1" id="psw1" value="{{psw1}}" />
				</p>
				<p>
					<label ng-if="((nick == undefined) | (nick == ''))">{{txt('LBL_PSW4')}}</label>
					<label ng-id="nick > ''">{{txt('LBL_NEW_PSW2')}}</label>
					<input type="password" name="psw2" id="psw2" value="{{psw2}}" />
				</p>
    			<p>
    				<a href="<?php echo txt('MYDOMAIN'); ?>/opt/adatkezeles/show" target="_new">
    				{{txt('DATAPROCESS')}}</a>&nbsp;
    				<div style="display:inline-block; width:auto">
    					<var><input type="checkbox" name="dataProcessAccept" id="dataProcessAccept" value="1"  /></var>
    					{{txt('DATAPROCESSACCEPT')}}&nbsp;&nbsp;
    				</div>
    				<div style="display:inline-block; width:auto">
	    				<var><input type="checkbox" name="cookieProcessAccept" id="cookieProcessAccept" value="1" /></var>
    					{{txt('COOKIEPROCESSACCEPT')}}
    				</div>	
    			</p>
				
				<p>
					<button type="button" id="formRegist2Ok" class="btn btn-primary">
						{{txt('OK')}}
					</button>
				</p>
			</form>		    
	    </div>
        <?php $this->echoHtmlPopup(); ?>
        <?php $this->loadJavaScriptAngular('userregist',$data); ?>
		</body>
        </html>
        <?php 
	}
	
}
?>