<?php

class PdfformView extends View {
	
	public function pdfForm($p) {
		echo '
		<html>
		  <head>
		    <meta charset="utf-8">
		    <meta name="title" content="Ügyfélkapus login rendszer">
		    <meta name="description" content="web szolgáltatás e-demokrácia programok számára. Regisztráció ügyfélkapus aláírás segitségével.">
		    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		    <title>uklogin</title>
			<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		  </head>
		';
		echo '
		<body style="background-color:white; width:100%; height:90%">
        <div id="ukAudit" class="page" style="padding:20px; margin:20px; background-color:white; opacity:0.9">
        ';
		if (isset($p->msgs) && (count($p->msgs) > 0)) {
		    echo '<div class="alert alert-danger">';
    	    foreach ($p->msgs as $item) {
    	        echo txt($item).'<br />';
    	    }
    	    echo '</div>';
		}
        echo '
        <h2>'.$p->formTitle.'
				<img src="'.MYDOMAIN.'/templates/default/logo.png" style="height:150px; float:right">        
        </h2>
        <p>1. A lentebb megadott linkre kattintva (új böngésző fülön nyílik meg), az ügyfélkapus
         belépésedet használva; le kell töltened a személyi
         adataidat tartalmazó pdf fájlt a saját gépedre. Miután az ügyfélkapus belépéseddel azonosítottad magad, 
         görgesd le a megjelenő oldalt az aljára, a jobb alsó sarokban van a "LETÖLTÉS" gomb.
         A letöltés után térjél vissza erre a böngésző fülre!</p> 
        <p style="padding-left:30px;">
          <em class="fa fa-hand-o-right"></em>
          <a href="https://www.nyilvantarto.hu/ugyseged/NyilvantartottSzemelyesAdatokLekerdezeseMegjelenitoPage.xhtml"
        	 target="_new">személyi adatokat tartalmazó pdf letöltése<br />
        	 https://www.nyilvantarto.hu/ugyseged/NyilvantartottSzemelyesAdatokLekerdezeseMegjelenitoPage.xhtml
           </a>
        </p> 
        <p>2. Ezután a pdf fájl elektronikusan  alá kell írnod. Ennek érdekében kattints
         a lentebb megadott linkre (új böngésző fülön nyílik meg), 
         válaszd ki az elöző lépésben letöltött pdf fájlt, válaszd a "hitelesített pdf" opciót,
         fogadd el a felhasználási feltételeket, ha a program kéri akkor azonosítsd
         magad az ügyfélkapus belépéseddel, kattints a "Documentum elküldése" ikonra!
         Ezután a megjelenő új képernyöröl töltsd le az aláirt pdf -t a saját gépedre.
         Az aláírt fájl letöltése után térjél vissza erre a böngésző fülre!</p> 
        <p style="padding-left:30px;">
          <em class="fa fa-hand-o-right"></em>
          <a href="https://szuf.magyarorszag.hu/szuf_avdh_feltoltes" 
        	target="_new">
        	pdf aláírása<br />
        	https://szuf.magyarorszag.hu/szuf_avdh_feltoltes
           </a>
        </p> 
        <p>3. Töltsd fel a fentiek szerint letöltött és aláírt pdf fájlt! (válaszd ki, majd kattints a 
        kék szinű <em class="fa fa-upload"></em> ikonnal jelölt gombra!)</p>
        <form name="formRegist1" id="formRegist1"	
            action="'.$p->okURL.'" class="form"
            method="post"target="_self" enctype="multipart/form-data">
				<input type="hidden" name="pdffile" value="alairt_pdf" />
				<input type="hidden" name="process" value="1" />
				<input type="hidden" name="'.$p->csrToken.'" value="1" />
				<br />
				<div class="form-control-group">
				<label>aláírt pdf file:</label>
				<input type="file" class="form-control" name="alairt_pdf" />
				</div>
				<br />
				<br />
				<div style="display:none">
				</div>
                <div class="adatkezeles">
                    <em class="fa fa-hand-o-right"></em>
                    <a href="'.MYDOMAIN.'/opt/adatkezeles/show" target="_new">
                        Adatkezelési leírás
                    </a>
                    <br />
                    <br />
                </div>
				<div class="buttons">
				<button type="submit" class="btn btn-primary">
					<em class="fa fa-upload"></em>
					Az adatkezeléshez hozzájárulok,<br />az aláírt pdf -et feltöltöm
				</button>
				<br />
				<br />
				</div>
			</form>
        </div>
   	</body>
   	';
   	echo '
   	</html>
   	'; 
	} 
}

?>