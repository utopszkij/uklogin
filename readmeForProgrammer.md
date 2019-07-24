# PHP - JS - JQUERY - bootstrap keretrendszer
Ez a keretrendszer kliens-szerver projektek MVC ajánlás szerinti fejlesztésére szolgál. Tartalmaz unittest eszközöket, és dokumentáló eszközöket is.
A teszteléshez nodejs rendszer és jónéhány nodejs modul is szükséges (ezeket is tartalmazza ez a repo).

## Általános müködési elv

- A web böngészők az app.php -t vagy az index.php -t hívják (az index.php is az app.php -t aktivizálja),
- a web böngészőben futó js kód szükség esetén AJAX kommunikációt folytathat a szerverrel, ilyenkor az app.php kerül meghívásra, speciális POST adatokkal.
- a böngészőből történő hívás "option" és "task" paraméterben közli melyik program opciót és annak melyik taskját kell végrehajtani. Az app.php betölti a "controllers" könyvtárból az "option" paraméterben megadott nevű php fájlt. Ezután a "task" nevű publikus method kerül végrehajtásra.
- az applikáció beállításait a .config.php tartalmazza,
- a controllerben lévő task kodok szükség esetén be inkludolnak fájlokat a "js", "moduls" és "views" könyvtárakból, valamint a többnyelvüséget biztositandó a "langs" könyvtárból.
- a megjelenitést a style.css szabályozza.

## Könyvtár szerkezet

- **javascriptResources** unittesthez szükséges dolgok, nem kell hozzá nyulni
- **controllers** MVC controllerek (a hívás option paramétereinek megfelelő nevü php fájlok, OptionnameController nevü class -t tartalmaznak)
- **core** a kretrendszert tartalmazó fájlok,  nem kell hozzá nyulni.
- **doc** php dokumentátorral generált dokumentáció
- **images** a programban használt képfájlok
- **js** javascript fájlok (JQuery)
- **langs** nyelvi konstansok
- **log** teszteléshez napló fájlok
- **models** MVC adat modell php fájlok  ('modelname'.php fájlok ModelnameMode nevű class -t tartalmaznak,
- **node_moduls** unittesthez szükséges nodejs modulok
- **sonar** sonarcloud.io kód minöség ellenörző rendszer kliens, a sonarcloud.io accontunk alapján modosítani kell.
- **tests** unittestek
- **tools** tesztelő, minöség ellenörző, dokumentáló batch scriptek
- **vendor** teszteléshez, dokumentáláshoz szükséged dolgok
- **views** MVC viewerek (Viewname.php fájlok, ViewNameView class-t tartalmaznak, ezek gondoskodnak többek között a html header kiirásáról jquery, bootstrap, és saját js kodok, style.css betöltéséről is. 
 
## A fő könyvtár fájljai
- **app.php** fő program
- **composer.json** unittest használja
- **.config.php** applikáció beállítások (a repoban lévő config.txt átnevezésével hozható létre)
- **.htaccess** appache beállítások (a repoban lévő htaccess.txt átnevezésével hozható létre). Biztositja, hogy amennyiben az URL nemlétező fájlra, könyvtárra hivatkozik akkor az app.php aktivizálódik.
- **package.json** unittest használja
- **readme.md** applikáció leíráss 
- **readmeForProgrammer.md** ez a leírás
- **style.css** applikáció megejelenése
- **.travis.yl** travis kapcsolat vezérlő
- **.gitignore** github által figyelmen kivül hagyandó dolgok
- **index.php** a repoban lévő example-index.php átnevezésével hozható létre. Feladata a SEO barát URL kezelés és az app.php aktivizálása.

## Standart GET vagy POST paraméterek
- **option** controller php fájl neve, ha nincs megdava "default" -ot használ
- **task** controller végrahajtandó methodusa, ha nincs megadva "default" -ot használ
- **lng** nyelv kód, ha nincs megadva akkor a config.php -ból veszi
- **sid** session ID, ha nincs megadva akkor azadott kliens korábbi sessionját használja, illetve új session-t hoz létre.

## SEO barát URL rendszer
```
http[s]://domain[/path/opt/optionName/taskName/paramName1/paramValue1/paramName2/paramValue2....
```
lásd az example-index.php -t is!

## Standart eljárás új funkció megvalósítására
- készül egy controllers/ujfun.php fájl, ebben UjfunController class, ebbe kerülnek majd a megvalósítandó taskok public methodusok formájában,
- készül egy models/ujfun.php fájl, ebben UjfunModel class, ebbe kerülnek majd a megvalósítandó adatmodell funkciók,
- készül egy views/ujfun.php fájl, ebben UjfunView class, ebbe kerülnek majd a megvalósítandó viewer funkciók,
- készül egy js/ujfun.js fájl, ebbe kerülnek majd a szükséges angularhjs funkciók, változók,
- készül egy tests/ujfunTest.php ez a php kodok phpunit rendszer szerinti tesztje
- készül egy tests/ujfunTest.js ez a js kodok mocha rendszer szerinti tesztje
- javasolt a TDD elvek szerint elöször teszteket irni, majd az ezeket megvalósitó kódokat.
- a fájlok elkészitéséhez kinndulásként használhatóak az example.... fájlok.
 
## tools scriptrek
A scriptek LINUX terminálban futtathatóak:
- **cd documentroot**
- **./tools/test-php.sh** php unittestek futtatása
- **./tools/test-js.sh**  javascript unittestek futtatása
- **./tools/test.sh** php és javascript unittestek futtatása
- **./tools/sonar.sh** sonarCloud kód ellenörzés futtatása
- **./tools/documentor.sh** php dokumentáció kreálás a doc könyvtárba.
 
## További infók a "doc/index.html" -ben.
 
