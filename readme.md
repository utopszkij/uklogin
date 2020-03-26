# e-demokrácia web applikációkból hívható - ügyfélkapu aláíráson alapuló - login modul

## Kontribútoroknak
A fejlesztésében közreműködni kívánóknak szóló információk a [ebben a leírásban](/readmeForProgrammer.md) találhatók.

## Készültség

ß teszt

## Online ß teszt:

https://uklogin.tk

## Áttekintés

Ez egy web -es szolgáltatás. Az a célja, hogy e-demokrácia szoftverek az ügyfélkapus aláíráson alapuló regisztrációt és bejelentkezést használhassanak az **OpenId** szabvány szerint.
A rendszer biztosítja, hogy egy személy csak egyszer regisztrálhat.
(az ügyfélkapus aláírásban szereplő születési név, születési dátum és anyja neve adat egyediségét ellenörzi a program).

Természetesen egy ügyfélkapú loginnal több alkalmazásba is be lehet lépni.

A hívó web program iframe -be hívhatja be a login képernyőt. Ezen van "regisztrálok" link is azok számára akik még nem regisztráltak.

Az applikáció adminisztrátora az erre a célra szolgáló web felületen tudja az applikációt regisztrálni a rendszerbe.

A regisztrációs folyamatban használt aláírás szolgáltató:

https://magyarorszag.hu/szuf_avdh_feltoltes


```
Openid Bejelentkezés folyamata
+--------+                                   +--------+
|        |                                   |        |
|        |----(1) /authorize Request-------->|        |
|        |     respose_type=id_token token   |        |
|        |  +--------+                       |        |
|        |  |        |                       |        |
|        |  |  End-  |<--(2)---login form----|        |
|        |  |  User  |--(3)---nickname,psw-->|        |
|   web  |  |        |                       |        |
| client |  +--------+                       | openId |
|   app  |                                   | szerver|
|        |<--------(4) access_token----------|        |
|        |                                   |        |
|        |---------(5) /userinfo Request---->|        |
|        |                                   |        |
|        |<--------(6) UserInfo Response-----|        |
|        |                                   |        |
+--------+                                   +--------+
```

## magyaroszag.hu felhasználásával történő user regisztráció

A felhasználó a

[magyarorszag.hu](http://magyarorszag.hu)

oldalról lettölti az ott tárolt személyi adatait tartalmazó pdf fájlt. Ezután ezt a fájlt a belügyminisztérium által üzemeltetett ingyenes aláírás szolgáltatás

[szuf.magyarorszag.hu](https://szuf.magyarorszag.hu)

segítségével aláírja. Majd az aláírt pdf fájlt feltölti ebbe a programba.

A program a következő ellenőrzéseket végzi el:
- a pdf alá van írva, és a belügyminisztérium nyilvános aláírás szolgáltatója írta alá?
- a PDF információkban a megfelelő Creator, Producer, PDF version adat szerepel?
- a PDF -ben lévő név, anyja neve, lakcím, születési dátum azonos az aláírásban megadottal?
- az aláírás kezdeményező ügyfélkapuban megadott születési névvel, anyja nevével és születési dátummal még nincs másik fiók hitelesítve.


### Mennyire biztonságos az ügyfélkapus hitelesítés?

1. A kormány által biztosított aláírás szolgáltatás a  magyar törvények közhiteles aláírásnak ismerik el. Mivel a progrm azt, hogy egy usernek csak egy hitelesített bejelentkezése lehet ennek az aláírásnak az ellenörzésével biztosítja - ezt nagy biztonságunak fogadhatjuk el.

2. A személyi adatokat a program a magyarorszag.hu -ról letöltött pdf fájl tartalmából veszi ki. Megfelelő informatikai eszközökkel a pdf fájlok modosíthatóak. A pdf fájlokban van információ a pdf -et előállító szoftverről és a pdf -et utoljára modosító szoftveről. A program a pdf-ben lévő információk ellenörzésével igyekszik észlelni az ilyen manipulációkat. A legtöbb könnyen hozzáférhető pdf editorral modosított pdf fájlt nem fogadja el. Sajnos azonban speciális hacker eszközökkel (elég nagy idő és erőforrás ráfordítással) a pdf bizonyára "nyom nélkül" is modosítható. A program összeveti az aláírásban szereplő adatokat a pdf ben szereplőkkel. Gyakorlatilag ezen a módon is csak a lakcím hamisítása lehetséges. Viszont még ilyen módon is csak egyetlen egy ügyfélkapus aláírással hitelesített hamis fiokot tud egy felhasználó létrehozni.

Összefoglalva: Átlagos informatikai tudással rendelkező emberek, ha túl sok időt nem akarnak a csalásra forditani elég nagy biztonsággal csak egy ügyfélkapuval hitelesített, valós adatokat tartalmazó fiokot tudnak létrehozni.

## FIGYELEM FONTOS!

A magyaroszag.hu segítségével történő hitelesítési rendszer több olyan elemet tartalmaz ami esetenként változtatandó lehet amennyiben a magyarorszag.hu rendszerben vagy a használt aláírás szolgáltató rendszerében változás történik (lásd az elöző pontot és a **controllers/pdfparser.php** fájlt). Ezért az éles üzemeltetés folyamán az üzemeltetőnek folyamatosan figyelnie kell, hogy az érintett állami rendszerekben nem történik-e változás!



## Programnyelvek

 PHP(7.1+), Javascript, MYSQL, JQUERY, bootstrap

A program az "aHang" és Szabó Simon Márk 2019 főpolgármester előválasztás 2. fordulójára készített programjában található ötletek és kód részletek felhasználásával készült.

Lásd: https://gitlab.com/mark.szabo-simon/elovalaszto-app?fbclid=IwAR2X4RlNDA4vHw5-4ABkDCzzuifNpE5-u9T7j1X-wuubag4ZY0fSvnifvMA

A program MVC ajánlás szerint struktúrált.

## Licensz

 GNU/GPL

## Programozó

Fogler Tibor (Utopszkij)

tibor.fogler@gmail.com

https://github.com/utopszkij

## Új applikáció regisztrálás

Az applikációt web felületen lehet regisztrálni. A megadandó adatok:
- applikáció neve
- applikációt futtató domain
- sikeres login utáni visszahívandó url
- applikáció adminisztrátor username

A képernyőn van adatkezelés elfogadtatás is.

Annak igazolására, hogy az app. regisztrálását az adott rendszer rendszergazdája végzi, a megadott domainre fel kell tölteni egy "uklogin.html" fájlt, aminek tartalma egyetlen sor: "uklogin".

A sikeres app regisztrálás után a képernyőn megjelenik a **client_id**  adat. Ezeket gondosan meg kell örizni az app adminisztrálásához és a login/regist rendszer használatához szükség van rájuk.


Ezen adatok egy része az adminisztrátor által később is  módosítható, az app admin ugyancsak kezdeményezheti az app és saját adatainak együttes törlését is.
Az app adatok módosításához, törléséhez természetesen az admin login szükséges. Ha itt a megadott limitet túllépő sikertelen login kisérlet történik akkor az app admin login blokkolásra kerül, ezt ennek az "ügyfélkapus-login" rendszernek az "főadminisztrátora" tudja feloldani.

## OpenID müködés

Az openid szolgáltatás konfigurációjának lekérése:

```
<ukloginDomain>/openid
```

A szerver két adatkezeleési módban konfigurálható. A két mód a kezelt user adatokban tér el egymástól (lásd lentebb). A fent megedott végpontról lekérhető json formátumú információ tájékoztat arról, hogy az adott szerver milyen user adatokat tud szolgáltatni.

### OpenId login

végpont:

```
<ukloginDomain>/openid/authorize
```

POST vagy GET pareméterek (url encoded formában):

**client_id** alkalmazás azonosító, vagy a redirect_uri val megegyező string **kötelező**

**redirect_uri** sikeres login után visszahivandó **opcionális**

**policy_uri**  alkalmazás adatkezelési leírása **opcionális de erősen ajánlott**

**scope** alkalmazás által kért user adatok (lásd a **/openid** hívással lekérhető json -ban) **kötelező**

**state** tetszőleges string, ezt is megkapja a redirect_uri **opcionális**

**nonce** tetszőleges string, ezt is megkapja a redirect_uri **opcionális**

**response_type** ha szerepel akkor kötelezően: "token id_token" **opcionális**


**Regisztrált kliens app** esetén a **redirect_uri** elhagyható, ez esetben a klien regisztrációnál megadottat használjuk.
Ha viszont megadunk **redirect_uri** -t annak a kliens regisztrációnál megadott domainben kell lennie.


**Nem regisztrált kliensnél** a **redirect_uri** kötelező, **client_id** -ben és a **redirect_uri** -ban egyaránt a visszahívandó URL-t kell szerepeltetni.


A login képernyőn szerepel **"még nincs fiokom, regisztrálok"** link, valamint **"elfelejtettem a jelszavam"** link is. A szerver az ezekre történő kattintást is kezeli.
A login képernyőn szerepel az alkalmazás által kért user adatok felsorolása is,
és az alkalmazás adatkezelési leírására mutató link is (ha megadtunk policy_uri -t).
A felhasználónak az adat kezelést el kell fogadnia.

Amennyiben a hívás pillanatában a user már be van jelentkezve az uklogin/openid szolgáltatásba akkor csak az alkalmazás által kért user adatokok átadásához való hozzájárulást kérő képernyő jelenik meg. Ezen is szerepel az alkalmazás adatkezelési leírására mutató link.

Sikeres login, illetve az adatkezeléshez történő hozzájárulás után a **redirect_uri** -ra, ennek hiányában a kliens regisztrációban beállított visszahívási címre kerül a vezérlés, négy paramétert átadva:
- **id_token**,
- **token**,
- **state** ,
- **nonce**.

A **token** adatot használva a **userinfo** végpontról lekérhetőek a json formátumú  user információk (token = access_token). A **state** és **nonce** adatot a kliens tetszőleges célra használhatja. Gyakran a **state** adatot egy biztonságot növelő egyedi token céljára használják, a **nonce** -ben bpedig a sikeres login után aktiválandó applikáció funciót indító URL szerepel.

### OpenId logout

végpont:

```
<ukloginDomain>/openid/logout
```

POST vagy GET pareméterek:

**token_type_hint**  kötelezően "access_token"

**token**

**redirect_uri**


### OpenId refresh

végpont:

```
<ukloginDomain>/openid/refresh
```

POST vagy GET pareméterek:

**token_type_hint**  kötelezően "access_token"

**token**

**redirect_uri**


### Openid userinfo

végpont:

```
<ukloginDomain>/openid/userinfo
```

POST vagy GET pareméterek:

**access_token**

result a korábbi "authorize" hívásban megadott "scope" paraméterben kért user információk json string formájában.


### GDPR megfelelés

#### Az app adminisztrátorokkal kapcsolatban a rendszer a következő adatokat tárolja:
- nicknév
- jelszó hash
- kezelt app adatai

Itt személyes adat nincs kezelve, tehát ez nem tartozik a GDPR hatálya alá,erről tájékoztatást írunk ki.

#### a "normál" felhasználókkal kapcsolatban tárolt adatok :

A szerver két adatkezelési beállítással üzemeltethető

**Csökkentet openid adatkezelési beállításnál**
- azonsoító kód
- bejelentkezési név
- állandó lakcímből az irányító szám és település név
- jelszó hash
- email
- melyik applikációba regisztrált (csak Oauth2 esetében)
- ügyfélkapunál megadott személyes adataiból képzett (reális idő alatt nem visszafejthető) kód

** Teljes Openid adatkezelési beállításnál**
- Azonoító kód
- Bejelentkezési név
- Jelszó hash
- Postai irányító szám
- Település
- utca és házszám
- E-mail
- E-mail ellenörzött (Igen vagy Nem)
- Második kersztnév
- Első keresztnév
- Vezeték név
- Avatar kép URI
- Születési dátum
- Telefon szám
- Telefon szám ellenörzött  (Igen vagy Nem)
- Utolsó módosítás időpontja
- Rendszer adminisztrátor  (Igen vagy Nem)
- ügyfélkapunál megadott személyes adataiból képzett (reális idő alatt nem visszafejthető) kód


**Ezek személyes adatok, kezelésüknél a GDPR ide vonatkozó előírásait kell érvényesíteni.**


Megjegyzés: A feldolgozás során - technikai okokból - néhány másodpercig a rendszer tárolja az aláírt pdf fájlt és  az abban lévő csatolmányokat. Ezek tartalmazzák az aláíró személyi adatait (név, lakcím, születési dátum, anyja neve, születési név, okmány azonosítók, személyi szám).
 Ezen adatok közül a rendszer kizárólag a születési névből, születési dátumból és anyja nevéből SHA256 hash algoritmussal
 képzett hash kódját, valamint a fentebb leirt user adatokat használja és tárolja adatbázisában. A többi személyes adatot nem tárolja. (a hash kódból reális idő alatt nem fejthetőek vissz az adatok)
Az aláírt pdf fájlt és csatolmányait a a kód előállítása, és a kezelt user adatok tárolása  után azonnal törli,


#### cookie kezelés
A működéshez egy darab un. "munkamenet cookie" használata szükséges, erről tájékoztatás jelenik meg és a felhasználónak ezt el kell fogadnia.

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

[documentation](https://uklogin.tk/doc/)

## kód minőség ellenörzés
```
cd repoRoot
./tools/sonar.sh
```
Utolsó teszt eredménye:

[sonarcloud.io](https://sonarcloud.io/dashboard?id=utopszkij-uklogin)


## Telepítés web szerverre

### Rendszer igény:

- PHP 7.1+  shell_exec funkciónak engedélyezve kell lennie
- MYSQL 5.7+
- web server (.htaccess értelmezéssel)
- https tanusitvány
- php shell_exec -al hívhatóan  pdfsig, pdfdetach, pdftotext parancsok (lásd: poppler-utils)
- Létrehozandó egy MYSQL adatbázis: **uklogin** (utf8, magyar rendezéssel)


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
- example.php
- readme.md

Ahol ezt  külön nem jelöltük ott a fájlok, könyvtárak csak olvashatóak legyenek a web szerver számára.(oktális 640 jogosultság)
