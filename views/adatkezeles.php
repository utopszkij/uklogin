<?php
include_once './views/common.php';
class AdatkezelesView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function display($p) {
	    if (!isset($p->user)) {
	        $p->user = new stdClass();
	        $p->user->id = 0;
	        $p->user->nick = 'guest';
	        $p->user->avatar = 'https://www.gravatar.com/avatar';
	    }
	    $this->echoHtmlHead();
        ?>	
        <body ng-app="app">
          <?php $this->echoNavbar($p); ?>
          <div ng-controller="ctrl" id="scope" style="display:block; padding:20px;">
            <h2>Adatkezelési leírás</h2>
            <p>Jelen leírás az ügyfélkapu segítségével történő felhasználó regisztrációt és
            bejelentkezést kezelő web szolgáltatás adatkezeléséről szól. Az ezt használó 
            egyes applikációk adatkezelési leírását az adott applikációban találhatja meg!</p>
            <p><strong>Ez a szoftver személyhez kapcsolt személyes adatokat tartósan nem kezel és tárol.
            Az állandólakcíből az iráníító számot és a település nevet anonim módon, (személy azonosításra alkalmas adathoz nem kapcsolva) tárolja.
            Ezért a GDPR erre a szotverre nem vonatkozik.</strong></p>

            <h3>Cookie használat</h3>
            <p>A szoftver működésének testreszabásához, az egyes működési munkamenetek 
            szervezéséhez egy daram un "munkamenet cookie"-t használ amit a felhasználó
            gépén tárol. Ez a cookie személyes adatokat nem tartalmaz, tartalmából 
            mindössze annyit lehet megállapítani, hogy a felhasználó használja/használta
            ezt a rendszert.</p>

            <h3>Kezelt adatok az applikációk felhasználóival kapcsolatban</h3>
            <ul>
            <li>Választott bejelentkezési név (nick név)</li>
            <li>Jelszó sha256 hash kódja</li>
            <li>Éllandó Lakcím (irányító szám, település</li>
            <li>Használt applikáció azonosítója</li>
            <li>Az ügyfélkapus aláírásból kinyert születési névből, születési dátumból, anyja neve adatból 
             sha256 hash algoritmussal képzett kód</li>
            <li>Bejelentkezés engedélyezve/letiltva</li>
            <li>Hibás bejelentkezési kisérlet számláló</li>
            <li>code - az oAuth2 eljárás során használt ideiglenes, egyszer használható technikai azonosító</li>
            <li>access_token - az oAuth2 eljárás során használt ideiglenes, egyszer használható technikai azonosító</li>
            <li>Fiók blokkolás időpontja</li>
            </ul>
            <p>Megjegyzések: Egy felhasználónak - amennyiben több applikációt is használ - több ilyen adat készlete is lehet.
            Az "sha256 hash" egy algoritmikusan képzett karakter sorozat. Ebből nem állapítható meg az eredeti jelszó illetve 
            személyes adatok, de alkalmas a bejelentkezésnél a jelszó ellenörzésre, illetve annak
            megakadályozására, hogy egy ember többször regisztráljon ugyanabba az applikációba.
            </p>
            <p>A regisztrációs folyamat során fel kell tölteni egy az ügyfélkapu ingyenes aláírás szolgáltatása
             segítségével aláírt hitelesített pdf dokumentumot. Ez a dokumentum tartalmazza a felhasználó teljes nevét 
             és az ügyfélkapuhoz megadott  e-mail címét és több más személyes adatát. Ezeket az adatokat 
             (technikai okokból) a program csak néhány másodpercig tárolja és kezeli ezután
            törli őket. Tartósan csak a fentebb felsorolt adatokat tárolja</p>

            <h3>Kezelt / tárolt adatok az applikációkkal és az applikáció adminisztrátorokkal kapcsolatban</h3>
            <ul>
            <li>Applikáció neve</li>
            <li>Applikáció domain</li>
            <li>Sikeres login esetén visszahívandó URL</li>
            <li>client_id</li>
            <li>client_secret</li>
            <li>login és regist képernyő testreszabásához css file URL</li>
            <li>Hibás user bejelentkezési kisérlet limit</li>
            <li>Admin bejelentkezési név (nick név)</li>
            <li>Lakcím (irányító szám, település</li>
            <li>Jelszó sha256 hash kódja</li>
            <li>Hibás admin bejelentkezési kisérlet limit</li>
            </ul>
            <p>Megjegyzések: Egy embernek - amennyiben több applikációt is menedzsel - több ilyen adat készlete is lehet.
            Az "sha256 hash" egy algoritmikusan képzett karakter sorozat. Ebből nem állapítható meg az eredeti jelszó  
            ,de alkalmas a bejelentkezésnél a jelszó ellenörzésre.
            </p>
            <p>Az adminisztrátor sikeres bejelentkezése után a megjelenő képernyőn láthatja
            ezeket a tárolt adatokat, lehetőségük van az adatok törlésére is (ezzel az adott
            applikáció nem tudja a továbbiakban használni ezt a rendszert)</p>
            
            <h3>Adatkezelési nyilatkozat</h3>
            <p>A rendszer üzemeltetői minden tőlük telhetőt megtesznek a tárolt adatok biztonságos, védett tárolása
            és kezelése érdekében. Mindent megtesznek annak érdekében, hogy az adatok illetéktelen kezekbe
            ne kerülhessenek.</p>
            <p>A tárolt adatokat harmadik félnek csak abban az esetben adja át, ha erre őket törvény kötelezi.</p>
            <p>Adatkezelő: Fogler Tibor Adatok technikai tárolása, szerver üzemeltető: Forpsi.hu</p>
            <p>&nbsp;</p>
            <p><strong>Ez a leírás kizárólag az uklogin regisztrációs és bejelentkező modulra vonatkozik. 
             Az a web program ami ezt a szolgáltatást használja további adatokat is kezelhet, 
             erről az adott rendszerben találhat információkat.</strong></p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
	      </div><!-- #scope -->
		  <?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 		
	}
}
?>

