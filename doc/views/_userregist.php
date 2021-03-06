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
		    <h2><?php echo $data->appName; ?></h2>
		    <h3><?php echo txt($data->title); ?></h3>
		    
	        <p>1. A lentebb megadott linkre kattintva (új böngésző fülön nyílik meg), az ügyfélkapus
	         belépésedet használva; le kell töltened a személyi
	         adataidat tartalmazó pdf fájlt a saját gépedre. Miután az ügyfélkapus belépéseddel azonosítottad magad, 
	         görgesd le a megjelenő oldalt az aljára, a jobb alsó sarokban van a "LETÖLTÉS" gomb.
	         A letöltés után térjél vissza erre a böngésző fülre!</p> 
	        <p style="padding-left:30px;"><a href="https://www.nyilvantarto.hu/ugyseged/NyilvantartottSzemelyesAdatokLekerdezeseMegjelenitoPage.xhtml"
	        	 target="_new">személyi adatokat tartalmazó pdf letöltése<br />
	        	 https://www.nyilvantarto.hu/ugyseged/NyilvantartottSzemelyesAdatokLekerdezeseMegjelenitoPage.xhtml
	           </a>
	        </p> 
	        <p>2. Ezután a pdf fájl elektronikusan  alá kell írnod. Ennek érdekében kattints
	         a lentebb megadott linkre (új böngésző fülön nyílik meg), 
	         válaszd ki az elöző lépésben letöltött pdf fájlt, válaszd a "hitelsített pdf" opciót,
	         fogadd el a felhasználási feltételeket, ha a program kéri akkor azonosítsd
	         magad az ügyfélkapus belépéseddel, kattints a "Documentum elküldése" ikonra!
	         Ezután a megjelenő új képernyöröl töltsd le az aláirt pdf -t a saját gépedre.
	         Az aláírt fájl letöltése után térjél vissza erre a böngésző fülre!</p> 
	        <p style="padding-left:30px;"><a href="https://szuf.magyarorszag.hu/szuf_avdh_feltoltes" 
	        	target="_new">
	        	pdf aláírása<br />
	        	https://szuf.magyarorszag.hu/szuf_avdh_feltoltes
	           </a>
	        </p> 
	        <p>3. Töltsd fel a fentiek szerint letöltött és aláírt pdf fájlt! (válaszd ki, majd kattints a
	         kék szinű gombra!)</p>

			<form name="formRegist1" id="formRegist1" 
				action="<?php echo MYDOMAIN; ?>/index.php" method="post" 
				target="_self" enctype="multipart/form-data">
				<input type="hidden" name="option" value="userregist" />
				<input type="hidden" name="task" value="registform2" />
				<input type="hidden" name="nick" value="<?php echo $data->nick; ?>" />
				<input type="hidden" name="<?php echo $data->csrToken?>" value="1" />
				<p style="background-color:#ade9ed; padding:4px;">
					<lable><?php echo txt('LBL_SIGNEDPDF'); ?></lable>
					<input type="file" name="signed_pdf" />
				</p>
		     <p><a href="<?php echo MYDOMAIN; ?>/opt/adatkezeles/show" target="_new">Adatkezési leírás</a>
				<p>
					<button type="submit" class="btn btn-primary">Az adatkezelést elfogadom, az aláírt PDF fájlt feltöltöm.</button>
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
				<input type="hidden" name="option" value="userregist" />
				<input type="hidden" name="task" value="doregist" />
				<input type="hidden" name="<?php echo $data->csrToken?>" value="1" />
				<p>
					<label><?php echo txt('USER'); ?></label>
					<?php if (!isset($data->nick) || ($data->nick == '')) : ?>
					<input type="text" name="nick" id="nick" value="" />
					<?php else : ?>
					<input type="hidden" name="nick" value="<?php echo $data->nick; ?>"  />
					<var><?php echo $data->nick;  ?></var>
					<?php endif; ?>   
				</p>
				<p>
					<?php if (!isset($data->nick) || ($data->nick == '')) : ?>
					<label><?php echo txt('LBL_PSW3'); ?></label>
					<?php else : ?>
					<label><?php echo txt('LBL_NEW_PSW'); ?></label>
					<?php endif; ?>
					<input type="password" name="psw1" id="psw1" value="<?php echo $data->psw1; ?>" />
				</p>
				<p>
					<?php if (!isset($data->nick) || ($data->nick == '')) : ?>
					<label><?php echo txt('LBL_PSW4'); ?></label>
					<?php else : ?>
					<label><?php echo txt('LBL_NEW_PSW2'); ?></label>
					<?php endif; ?>
					<input type="password" name="psw2" id="psw2" value="<?php echo $data->psw2; ?>" />
				</p>
    			<p>
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
				
				<p>
					<button type="button" id="formRegist2Ok" class="btn btn-primary"><?php echo txt('OK'); ?></button>
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