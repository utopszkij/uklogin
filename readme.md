# e-demokrácia web applikációkból hívható - ügyfélkapu aláíráson alapuló - login modul

## Kontribútoroknak
A fejlesztésében közreműködni kívánóknak szóló információk a [ebben a leírásban](/readmeForProgrammer.md) találhatók.

## Készültség

ß teszt

## ß teszt:

https://uklogin.tk

## Áttekintés

Ez egy web -es szolgáltatás. Az a célja, hogy e-demokrácia szoftverek az ügyfélkapus aláíráson alapuló regisztrációt és bejelentkezést használhassanak az **oAuth2** szabvány szerint. 
A rendszer biztosítja, hogy egy személy egy alkalmazásba csak egyszer regisztrálhat.
Természetesen egy ügyfélkapú loginnal több alkalmazásba is lehet regisztrálni. 

A hívó web program iframe -be hívhatja be a regisztráló képernyőt vagy a login képernyőt. Szükség esetén css fájl segítségével az iframe -ben megjelenő formok megjelenését módosíthatja.

Az applikáció adminisztrátora az erre a célra szolgáló web felületen tudja az applikációt regisztrálni a rendszerbe.

A regisztrációs folyamatban használt aláírás szolgáltató:

https://niszavdh.gov.hu 


## Programnyelvek

 PHP(7.1+), Javascript, MYSQL, JQUERY, bootstrap
 
A program az "aHang" és Szabó Simon Márk 2019 főpolgármester előválasztás 2. fordulójára készített programjában található ötletek és kód részletek felhasználásával készült.
 
Lásd: https://gitlab.com/mark.szabo-simon/elovalaszto-app?fbclid=IwAR2X4RlNDA4vHw5-4ABkDCzzuifNpE5-u9T7j1X-wuubag4ZY0fSvnifvMA

## Licensz

 GNU/GPL
 
## Programozó

Fogler Tibor (Utopszkij)

tibor.fogler@gmail.com 

https://github.com/utopszkij

## Működés

### Új applikáció regisztrálás 

Az applikációt web felületen lehet regisztrálni. A megadandó adatok:
- applikáció neve
- applikációt futtató domain
- sikeres login utáni visszahívandó url
- sikertelen user login limit
- css file url (lehet üres is)
- applikáció adminisztrátor username
- applikáció adminisztrátor jelszó (kétszer kell beirni)
- sikertelen admin login limit

A képernyőn van adatkezelés elfogadtatás és cookie engedélyeztetés is.

Annak igazolására, hogy az app. regisztrálását az adott rendszer rendszergazdája végzi, a megadott domainre fel kell tölteni egy "uklogin.html" fájlt, aminek tartalma egyetlen sor: "uklogin".

A sikeres app regisztrálás után a képernyőn megjelenik a **client_id**  és **client_secret** adat. Ezeket gondosan meg kell örizni az app adminisztrálásához és a login/regist rendszer használatához szükség van rájuk.


Ezen adatok egy része az adminisztrátor által később is  módosítható, az app admin ugyancsak kezdeményezheti az app és saját adatainak együttes törlését is.
Az app adatok módosításához, törléséhez természetesen az admin login szükséges. Ha itt a megadott limitet túllépő sikertelen login kisérlet történik akkor az app admin login blokkolásra kerül, ezt ennek az "ügyfélkapus-login" rendszernek az "főadminisztrátora" tudja feloldani.

### login folyamat a felhasználó web applikációban:
```
<iframe ..... src="<ukLoginDomain>/oath2/loginform/client_id/<client_id>" />
```
Opcionálisan  /state/xxxxx is megadható. a state tetszőleges kiegészítő infot tartalmazhat. 

Az iframe -ben egy szokásos login képernyő jelenik meg (nicknév és jelszó megadása). 
A login képernyőn a szokásos kiegészitő elemek is szerepelnek:
- elfelejtett jelszó
- még nincs fiókom, regisztrálok
- fiók törlése
- tárolt adataim lekérdezése

Miután a user megadja usernevét és jelszavát a program ellenőrzi azokat, sikeres login esetén
meghívja az app adatokban beállított callback url -t, GET paraméterként küldve: "code", "state" 

Ezután hívni kell a https://szeszt.tk/uklogin/oath2/access_token url-t, GET vayg POST paraméterként küldve a "client_id", "client_secret" és "code" adatokat. Válaszként egy json stringet kapunk:
{"access_token":"xxxxxx"} vagy {"access_token":"", "error":"hibaüzenet"}

Következő lépésként hívni kell a https://szeszt.tk/uklogin/oath2/userinfo címet, GET vagy POST paraméterként a
"access_token" értéket küldve. Válaszként a bejelentkezett user nicknevét kapjuk vagy az "error" stringet:
{"nick":"xxxx"} vagy {"error":"not found"}

Sikertelen login esetén, az iframe-ben hibaüzenet jelenik meg és újra a login képernyő. az app -nál megadott számú sikertelen kisérlet után a fiók blokkolásra kerül, ez a user ebbe az applikációba a továbbiakban nem tud belépni. A blokkolt fiókokat az applikáció adminisztrátor tudja újra aktivizálni.

### Regisztráció hívása a felhasználó web applikációban
```
<iframe ..... src="<ukLoginDomain>/oauth2/registform/client_id/<client_id>" />
```
Sikeres regisztrálás után az iframe-ben a login képernyő jelenik meg. Sikertelen esetén hibaüzenet és újból a regisztrálás kezdő képernyője.


### Regisztráció folyamata

1. A megjelenő első képernyőről a felhasználónak le kell töltenie egy pdf fájlt (ez csak azt tartalmazza melyik app -be regisztrál).
2. A user a letöltött pdf -et az ügyfélkapus ingyenes aláírás rendszerrel aláírja, és az aláírt pdf -et is letölti saját gépére.
3. az aláírt pdf -et feltölti ebbe az applikációba, az ezután megjelenő képernyőn usernevet és jelszót választ magának.
Mindezt részletes help segíti.

A rendszer ellenőrzi:
- a feltöltött pdf -ben a megfelelő client_id szerepel?
- a feltöltött pdf alá van írva és sértetlen?
- az aláíró email hash szerepel már a regisztrált felhasználók között? (ha már szerepel akkor kiírja milyen nick nevet adott korábban meg)
- a választott nicknév egyedi az adott applikációban?

### Elfelejtett jelszó kezelés folyamata

A teljes regisztrációt kell a usernek megismételnie, azzal az egy különbséggel, hogy most nicknevet nem választhat, hanem a korábban használt marad meg. A rendszer ellenőrzi, hogy ugyanaz az ügyfélkapus aláírás szerepel-e a pdf -en mint ami korábban volt.


### GDPR megfelelés

#### Az app adminisztrátorokkal kapcsolatban a rendszer a következő adatokat tárolja:
- nicknév
- jelszó hash
- kezelt app adatai

Itt személyes adat nincs kezelve, tehát ez nem tartozik a GDPR hatálya alá,erről tájékoztatást írunk ki.

#### a "normál" felhasználókkal kapcsolatban tárolt adatok ("users" tábla):
- nick név
- jelszó hash
- melyik applikációba regisztrált
- ügyfélkapunál megadott email hash

Itt személyes adat nincs kezelve, tehát ez nem tartozik a GDPR hatálya alá,erről tájékoztatást írunk ki.

Megjegyzés: A feldolgozás során - technikai okokból - néhány másodpercig a rendszer tárolja az aláírt pdf fájlt és  az abban lévő csatolmányokat. Ezek tartalmazzák az aláíró személy nevét és az ügyfélkapuban használt email címét valamint az aláírás időpontját. Ezen adatok közül a rendszer kizárólag az ügyfélkapus email cím sha256 algoritmussal képzett hash kódját használja és tárolja adatbázisában. Az aláírt pdf fájlt és csatolmányait az ügyfélkapus email cím kinyerése és hashalése után azonnal törli, magát az email címet nem tárolja.

#### cookie kezelés
A működéshez egy darab un. "munkamenet cookie" használata szükséges, erről tájékoztatás jelenik meg és a felhasználónak ezt el kell fogadnia.

## Brute force támadás elleni védekezés

### user login brute force támadás
Az applikáció adatoknál beállított limitet elérő hibás kisérlet után a user fiók blokkolása, amit az applikáció adminisztrátor tud feloldani.

### Tesztelés
```
cd repoRoot
./tools/test.sh
```

## Dokumentálás
```
cd repoRoot
./tools/documentor.sh
```
A dokumentáció a "doc" könyvtárba kerül

## kód minőség ellenörzés
```
cd repoRoot
./tools/sonar.sh
```
Utolsó teszt eredménye:

https://sonarcloud.io/dashboard?id=utopszkij-uklogin


## Telepítés web szerverre

### Rendszer igény:

- PHP 7.1+  shell_exec funkciónak engedélyezve kell lennie
- MYSQL 5.7+
- web server (.htaccess értelmezéssel)
- https tanusitvány
- php shell_exec -al hívhatóan  pdfsig, pdfdetach parancsok
- Létrehozandő MYSQL adatbázis: **uklogin** (utf8, magyar rendezéssel)


Telepítendő  könyvtárak:
- controllers
- core
- images
- js
- langs
- log (legyen irható a web szerver számára!)
- models
- templates
- vendor
- views
- work (legyen irható a web szerver számára!)

Telepítendő fájlok
- index.php
- .config.php  (config.txt átnevezve és értelemszerüen javítva)
- .htaccess (a htaccess.txt átnevezve)

