<?php
include_once './views/common.php';
class ReadmeView  extends CommonView  {
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
         <div ng-controller="ctrl" id="scope" style="display:none; padding:20px;">
				      	
  <h1 id="e-demokracia-web-applikaciokbol-hivhato-ugyfelkapu-alairason-alapulo-login-modul">
   e-demokrácia web applikációkból hívható - ügyfélkapu aláíráson alapuló - login modul
  </h1>
  <h2 id="attekintes">
   Áttekintés
  </h2>
  <p>
   Ez egy web -es szolgáltatás. Az a célja, hogy e-demokrácia szoftverek az ügyfélkapus aláíráson alapuló regisztrációt és bejelentkezést használhassanak az
   <strong>
    oAuth2
   </strong>
   szabvány szerint.
   <br/>
   A rendszer biztosítja, hogy egy személy egy alkalmazásba csak egyszer regisztrálhat.
   (pntosabban: az ügyfélkapus fiókban megadott kapcsolattartó email egyediségét ellenörzi a program)
   <br/>
   Természetesen egy ügyfélkapu loginnal több alkalmazásba is lehet regisztrálni.
  </p>
  <p>
   A hívó web program iframe -be hívhatja be a regisztráló képernyőt vagy a login képernyőt. Szükség esetén css fájl segítségével az iframe -ben megjelenő formok megjelenését módosíthatja.
  </p>
  <p>
   Az applikáció adminisztrátora az erre a célra szolgáló web felületen tudja az applikációt regisztrálni a rendszerbe.
  </p>
  <p>
   A regisztrációs folyamatban használt aláírás szolgáltató:
  </p>
  <p>
   <a href="https://niszavdh.gov.hu">
    https://niszavdh.gov.hu
   </a>
  </p>
  <h2 id="programnyelvek">
   Programnyelvek
  </h2>
  <p>
   PHP, MYSQL, JQUERY, bootstrap
  </p>
  <h2 id="licensz">
   Licensz
  </h2>
  <p>
   GNU/GPL
  </p>
  <h2 id="programozo">
   Programozó
  </h2>
  <p>
   Fogler Tibor (Utopszkij)
  </p>
  <p>
   <a href="mailto:tibor.fogler@gmail.com">
    tibor.fogler@gmail.com
   </a>
  </p>
  <p>
   <a href="https://github.com/utopszkij">
    https://github.com/utopszkij
   </a>
  </p>
  <h2 id="mukodes">
   Működés
  </h2>
  <h3 id="az-applikacio-regisztralas">
   Új applikáció regisztrálás
  </h3>
  <p>
   Az applikációt web felületen lehet regisztrálni. A megadandó adatok:
   <br/>
   - applikáció neve
   <br/>
   - applikációt futtató domain
   <br/>
   - sikeres login utáni visszahívandó url
   <br/>
   - sikertelen user login limit
   <br/>
   - css file url (lehet üres is)
   <br/>
   - applikáció adminisztrátor username
   <br/>
   - applikáció adminisztrátor jelszó (kétszer kell beirni)
   <br/>
   - sikertelen admin login limit
  </p>
  <p>
   A képernyőn van adatkezelés elfogadtatás és cookie engedélyeztetés is.
  </p>
  <p>Annak igazolására, hogy az app. regisztrálását az adott rendszer rendszergazdája végzi, a megadott domainre fel kell tölteni egy 
  "uklogin.html" fájlt, aminek tartalma egyetlen sor: "uklogin". </p>
  <p>
   A sikeres app regisztrálás után a képernyőn megjelenik a
   <strong>
    client_id
   </strong>
   és
   <strong>
    client_secret
   </strong>
   adat. Ezeket gondosan meg kell örizni az app adminisztrálásához és a login/regist rendszer használatához szükség van rájuk.
  </p>
  <p>
   Ezen adatok egy része az adminisztrátor által később is  módosítható, az app admin ugyancsak kezdeményezheti az app és saját adatainak együttes törlését is.
   <br/>
   Az app adatok módosításához, törléséhez természetesen az admin login szükséges. Ha itt a megadott limitet túllépő sikertelen login kisérlet történik akkor az app admin login blokkolásra kerül, ezt ennek az “ügyfélkapus-login” rendszernek az “főadminisztrátora” tudja feloldani.
  </p>
  <h3 id="login-folyamat-a-felhasznalo-web-applikacioban">
   login folyamat a felhasználó web applikációban:
  </h3>
  <pre><code>&lt;iframe ..... src="<?php echo MYDOMAIN; ?>/oath2/loginform/client_id/&lt;client_id&gt;" /&gt;
</code></pre>
  <p>
   Opcionálisan 
   <url>
    /?state=xxxxx is megadható. A state tetszőleges kiegészítő infot tartalmazhat.
   </url>
  </p>
  <p>
   Az iframe -ben egy szokásos login képernyő jelenik meg (nicknév és jelszó megadása).
   <br/>
   A login képernyőn a szokásos kiegészitő elemek is szerepelnek:
   <br/>
   - elfelejtett jelszó
   <br/>
   - még nincs fiókom, regisztrálok
   <br/>
   - fiók törlése
   <br/>
   - tárolt adataim lekérdezése
  </p>
  <p>
   Miután a user megadja usernevét és jelszavát a program ellenőrzi azokat, sikeres login esetén
   <br/>
   meghívja az app adatokban beállított callback url -t, GET paraméterként küldve: “code”, “state”. 
  </p>
  <p>
   Ezután hívni kell a
   "<?php echo MYDOMAIN; ?>/oath2/access_token" url-t, GET vagy POST paraméterként küldve a “client_id”, “client_secret” és “code” adatokat. Válaszként egy json stringet kapunk:
   <br/>
   {“access_token”:”xxxxxx”} vagy {“access_token”:”“, “error”:”hibaüzenet”}
  </p>
  <p>
   Következő lépésként hívni kell a
   "<?php echo MYDOMAIN; ?>/oath2/userinfo"
   címet, GET vagy POST paraméterként a
   <br/>
   “access_token” értéket küldve. Válaszként a bejelentkezett user nicknevét kapjuk vagy az “error” stringet:
   {"nick":"xxxx"} vagy {"error":"not found"}
  </p>
  <p>
   Sikertelen login esetén, az iframe-ben hibaüzenet jelenik meg és újra a login képernyő. az app -nál megadott számú sikertelen kisérlet után a fiók blokkolásra kerül, ez a user ebbe az applikációba a továbbiakban nem tud belépni. A blokkolt fiókokat az applikáció adminisztrátor tudja újra aktivizálni.
  </p>
  <h3 id="regisztracio-hivasa-a-felhasznalo-web-applikacioban">
   Regisztráció hívása a felhasználó web applikációban
  </h3>
  <pre><code>&lt;iframe ..... src="<?php echo MYDOMAIN; ?>/opt/userregist/registform/client_id/&lt;client_id&gt;" /&gt;
</code></pre>
  <p>
   Sikeres regisztrálás után az iframe-ben a login képernyő jelenik meg. Sikertelen esetén hibaüzenet és újból a regisztrálás kezdő képernyője.
  </p>
  <h3 id="regisztracio-folyamata">
   Regisztráció folyamata
  </h3>
  <ol>
   <li>
    A megjelenő első képernyőről a felhasználónak le kell töltenie egy pdf fájlt (ez csak azt tartalmazza melyik app -be regisztrál). 
    /li>
   <li>
    A user a letöltött pdf -et az ügyfélkapus ingyenes aláírás rendszerrel aláírja, és az aláírt pdf -et is letölti saját gépére.
   </li>
   <li>
    az aláírt pdf -et feltölti ebbe az applikációba, az ezután megjelenő képernyőn usernevet és jelszót választ magának.
    <br/>
    Mindezt részletes help segíti.
   </li>
  </ol>
  <p>
   A rendszer ellenőrzi:
   <br />
   - a feltöltött pdf a megfelelő client_id -t tartalmazza?
   <br/>
   - a feltöltött pdf alá van írva és sértetlen?
   <br/>
   - az aláíró email hash szerepel már a regisztrált felhasználók között? (ha már szerepel akkor kiírja milyen nick nevet adott korábban meg)
   <br/>
   - a választott nicknév egyedi az adott applikációban?
  </p>
  <p>
   Hiba esetén hibaüzenet jelenik meg.
  </p>
  <h3 id="elfelejtett-jelszo-kezeles-folyamata">
   Elfelejtett jelszó kezelés folyamata
  </h3>
  <p>
   A teljes regisztrációt kell a usernek megismételnie, azzal az egy különbséggel, hogy most nicknevet nem választhat, hanem a korábban használt marad meg. A rendszer ellenőrzi, hogy ugyanaz az ügyfélkapus aláírás szerepel-e a pdf -en mint ami korábban volt.
  </p>
  <h3 id="gdpr-megfeleles">
   GDPR megfelelés
  </h3>
  <h4 id="az-app-adminisztratorokkal-kapcsolatban-a-rendszer-a-kovetkezo-adatokat-tarolja">
   Az app adminisztrátorokkal kapcsolatban a rendszer a következő adatokat tárolja:
  </h4>
  <ul>
   <li>
    nicknév
   </li>
   <li>
    jelszó hash
   </li>
   <li>
    kezelt app adatai
    <br/>
    Mint látható az adminisztrátor valós személyéhez köthető adat (név, lakcím, okmány azonosító) nincs tárolva. 
    Tehát ez nem tartozik a GDPR hatálya alá. Erre vonatkozó tájékoztatás jelenik meg, és az admin -nak ezt el kell fogadnia. Lehetősége van a tárolt adatait lekérdezni, és azokat törölni is - ez utóbbi egyúttal az applikáció törlését is jelenti.
   </li>
  </ul>
  <h4 id="a-normal-felhasznalokkal-kapcsolatban-tarolt-adatok-users-tabla">
   a “normál” felhasználókkal kapcsolatban tárolt adatok (“users” tábla):
  </h4>
  <ul>
   <li>
    nick név
   </li>
   <li>
    jelszó hash
   </li>
   <li>
    melyik applikációba regisztrált
   </li>
   <li>
    ügyfélkapunál megadott email hash
   </li>
  </ul>
  <p>
   Itt személyi adat nincs, tehát ez nem tartozik a GDPR hatálya alá, erről tájékoztatást írunk ki.
  </p>
  <h4 id="cookie-kezeles">
   cookie kezelés
  </h4>
  <p>
   A működéshez egy darab un. “munkamenet cookie” használata szükséges, erről tájékoztatás jelenik meg és a felhasználónak ezt el kell fogadnia.
  </p>
  
  	<h2>Brute force támadás elleni védekezés</h2>

	<p>Az applikáció adatoknál beállított limitet elérő hibás kisérlet után a user fiók blokkolása, amit az applikáció adminisztrátor tud feloldani.</p>

	     </div><!-- #scope -->
		   <?php $this->echoFooter(); ?>
         <?php $this->loadJavaScriptAngular('frontpage',$p); ?>
        </body>
        </html>
        <?php 		
	}
	
}
?>

