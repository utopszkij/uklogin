<?php
// formApp
DEFINE('LBL_APPDATAS','Applikáció adatai');
DEFINE('LBL_APPADMIN','Applikáció menedzser');
DEFINE('LBL_TITLE','Applikáció megnevezése');
DEFINE('LBL_CSS','CSS URL (lehet üres is)');
DEFINE('LBL_DOMAIN','Applikáció domain');
DEFINE('LBL_CALLBACK','Sikeres login után hívandó URL');
DEFINE('LBL_FALSELOGINLIMIT','Hibás bejelentkezési limit');
DEFINE('LBL_ADMIN','Adminisztrátor login név');
DEFINE('LBL_PSW1','Adminisztrátor jelszó<br />(min. 6 karakter)');
DEFINE('LBL_PSW2','Adminisztrátor jelszó ismét');
DEFINE('LBL_PSW4','Jelszó ismét');
DEFINE('LBL_NEW_PSW','Új jelszó<br />(min. 6 karakter)');
DEFINE('LBL_NEW_PSW2','Új jelszó ismét');
DEFINE('LBL_FALSEADMINLOGINLIMIT','Hibás admin bejelentkezési limit');
DEFINE('LBL_REGISTFORM1','Regisztráció 1. képernyő');
DEFINE('LBL_REGISTFORM2','Regisztráció 2. képernyő');
DEFINE('LBL_REGISTFORM1_HELP1','1. Töltse le az alábbi "Letöltés" linkről a saját gépére a regisztrációhoz szükséges,
 aláírandó pdf fájlt!');
DEFINE('LBL_REGISTFORM1_HELP2','2. A letöltött fájlt írja alá az ügyfélkapu segítségével,
 a <a href="https://niszavdh.gov.hu" target="_new">niszavdh.gov.hu</a>
 hitelesítő szolgáltást használva (Új "fülön" nyilik meg). Az aláírt pdf -et mentse le saját gépére, ');
DEFINE('LBL_REGISTFORM1_HELP3','Az "új fülön":<ul>
<li>aláírandó pdf feltöltése a saját gépéről a hitelesítő szerverre (Fájl kiválasztása),</li>
<li>"Hiteles PDF" bejelölése,</li>
<li>ÁSZF elfogadása,</li>
<li>"Dokumentum elküldése" ikonra kattintás,</li>
<li>Ügyfélkapu azonosítási módozat kiválasztása,</li>
<li>Bejelentkezés az ügyfélkapuba (a folyamat egy kis időt vehet igénybe)</li>
<li>Aláírt (hitlesített) dokumentum letöltése a saját gépére</li>
</ul>');
DEFINE('LBL_REGISTFORM1_HELP4','3. Léjen vissza erre a "fülre"<br />4. Az aláírt pdf fájlt saját gépéről töltse fel ebbe a rendszerbe; a képernyő alábbi (világoskék) részét használva,
  majd kattintson a "Tovább" gombra!');
DEFINE('LBL_SIGNEDPDF','Aláírt pdf feltöltése:');
DEFINE('LBL_PDF','Letöltendő pdf:');
DEFINE('LBL_DOWNLOAD','Letöltés (jobb egérgombbal kattinsts!)');
DEFINE('APPREMOVE','Applikáció törlése');
DEFINE('DATAPROCESSACCEPT','Adat kezelés elfogadása');
DEFINE('COOKIEPROCESSACCEPT','Munkamenet cookie engedélyezése');
DEFINE('SECRETINFO','Annak igazolására, hogy az app. regisztrálását az adott rendszer rendszergazdája végzi, a megadott domainre, a fő könyvtárba fel kellett töltenie egy <strong>"uklogin.html</strong> fájlt, aminek tartalma egyetlen sor: <strong>uklogin</strong>.');
DEFINE('USERACTIVATION','Letiltott user fiók aktiválása');
DEFINE('USRACTOK','Aktivál');
DEFINE('PSWCHGINFO','A két jelszó mezőt csak akkor töltse ki, ha változtatni akarja!');
DEFINE('APPSAVED','Applikáció adatai tárolva');
DEFINE('APPREMOVED','Applikáció és admin adatok törölve');
DEFINE('ADMIN_NICK','Admin név');
DEFINE('ADMININFO','A <strong>client_id</strong> és <strong>client_secret</secret>
 adatokat jegyezd fel és gondossan őrizd meg! A szolgáltatás használatához 
 és adminisztrálásához szükség lesz rájuk.');
DEFINE('USER_SAVED','Felhasználói fiók adati tárolva');
DEFINE('USER_DELETED','Felhasználói fiók törölve');
DEFINE('CLIENT_ID','Client_id');
DEFINE('SUREDELAPP','Bitos törölni akarja ezt az applikációt?<br />Törlés után sem az admin bejelentkezés, sem a szolgáltatás nem lesz használható.<br />A törlés nem visszavonható.');
DEFINE('SURE_DELETE_ACCOUNT','Bitos törölni akarja ezt a fiokot? (Törlés után minden ezzel a fiokkal kapcsolatos adatot törlünk a rendszerből. Nem fog tudni bejelentkezni, de szükség esetén újra regisztrálhatja magát.)');
DEFINE('ERROR_DOMAIN_EMPTY','Domain nevet meg kell adni');
DEFINE('ERROR_DOMAIN_INVALID','Domain név nem megfelelő');
DEFINE('ERROR_DOMAIN_EXISTS','Ez a domain már regisztrálva van');
DEFINE('ERROR_NAME_EMPTY','Megnevezést meg kell adni');
DEFINE('ERROR_CALLBACK_EMPTY','Visszahívási URL -t meg kell adni');
DEFINE('ERROR_CALLBACK_INVALID','Visszahívási URL nem megfelelő');
DEFINE('ERROR_CALLBACK_NOT_IN_DOMAIN','Visszahívási URL nem a megadott domainben van');
DEFINE('ERROR_CSS_INVALID','CSS URL nem megfelelő');
DEFINE('ERROR_ADMIN_EMPTY','Adminisztrátor belépési nevet meg kell adni');
DEFINE('ERROR_NICK_EXISTS','Ezzel a bejelentkezési névvel már regisztráltak!');
DEFINE('ERROR_PSW_NOTEQUAL','A két jelszó nem azonos');
DEFINE('ERROR_UKLOGIN_HTML_NOT_EXISTS','uklogin.html nem található a megadott domainen');
DEFINE('ERROR_DATA_ACCEP_REQUEST','Adat kezelés elfogadása szükséges');
DEFINE('ERROR_COOKIE_ACCEP_REQUEST','Cookie kezelés elfogadása szükséges');
DEFINE('ERROR_NOTFOUND','Nincs ilyen');
DEFINE('ERROR_APP_NOTFOUND','Nincs applikáció ehhez a bejelentkezéshez');
DEFINE('ERROR_PDF_NOT_UPLOADED','Nincs aláírt fájl feltöltve');
DEFINE('ERROR_PDF_SIGN_ERROR','A pdf nincs aláírva, vagy az aláírás nem  megfelelő');
DEFINE('ERROR_PDF_SIGN_EXISTS','Ezzel az ügyfélkapu aláírással már regisztráltak!');
?>