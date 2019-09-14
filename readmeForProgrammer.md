# PHP - JS - JQUERY - bootstrap keretrendszer
Ez a keretrendszer kliens-szerver projektek MVC ajánlás szerinti fejlesztésére szolgál. Tartalmaz unittest eszközöket, és dokumentáló eszközöket is.
A teszteléshez nodejs rendszer és jónéhány nodejs modul is szükséges (ezeket is tartalmazza ez a repo).

## Telepítés lokális fejlesztéshez kontribútoroknak

### Telepítés és futtatás
Készíts egy *fork*-ot a github-os projektről a saját github fiókodra, és onnan *clone*-ozd a kódot.
```
git clone https://github.com/<yourguthubuser>/uklogin.git
```
Szerkeszd a [config.txt](/config.txt) fájt, add meg a gépeden futó mysql szerver elérését és mentsd el `.config.php` néven.

Futtasd a főkönyvtár index.php fájlját, mondjuk így:
```
php -S localhost:8000
```
Ezután a böngészőben navigálj a [http://localhost:8000](http://localhost:8000) címre.

### Kontribúció

Ha fejlesztesz valamit, nyiss egy új branch-et, arra commit-olj, majd push-old a saját github repo-dba. Innen tudsz merge request-et nyitni az eredeti repo master branch-ére.



## Általános müködési elv

- A web böngészők az index.php -t hívják
- a web böngészőben futó js kód szükség esetén AJAX kommunikációt folytathat a szerverrel, ilyenkor is az index.php kerül meghívásra, speciális POST adatokkal.
- a böngészőből történő hívás "option" és "task" paraméterben közli melyik program opciót és annak melyik taskját kell végrehajtani. Optcionálisan lng=kod paraméter is megadható. Az index.php betölti a "controllers" könyvtárból az "option" paraméterben megadott nevű php fájlt, a "lng".php és a option_"lang".php nyelvi fájlokat. Ezután a "task" nevű publikus method kerül végrehajtásra.
- az applikáció beállításait a .config.php tartalmazza,
- a controllerben lévő task kodok szükség esetén be inkludolnak fájlokat a "js", "moduls" és "views" könyvtárakból, valamint a többnyelvüséget biztositandó a "langs" könyvtárból.
- a megjelenitést a style.css szabályozza.

## Könyvtár szerkezet

- **controllers** MVC controllerek (a hívás option paramétereinek megfelelő nevü php fájlok, OptionnameController nevü class -t tartalmaznak)
- **core** a kretrendszert tartalmazó fájlok,  nem kell hozzá nyulni.
- **doc** program dokumentáció
- **images** a programban használt képfájlok
- **js** javascript fájlok (JQuery)
- **langs** nyelvi konstansok ("lng".php, option_"lng".php, ...)
- **log** teszteléshez napló fájlok
- **models** MVC adat modell php fájlok  ('modelname'.php fájlok ModelnameMode nevű class -t tartalmaznak,
- **node_moduls** unittesthez szükséges nodejs modulok
- **sonar** sonarcloud.io kód minöség ellenörző rendszer kliens, a sonarcloud.io accontunk alapján modosítani kell.
- **tests** unittestek
- **templates** dizájn (css és images fájlok)
- **tools** tesztelő, minöség ellenörző, dokumentáló batch scriptek
- **vendor** teszteléshez, dokumentáláshoz szükséged dolgok
- **views** MVC viewerek (Viewname.php vagy ViewName_lng.php fájlok,
ViewNameView class-t tartalmaznak, ezek gondoskodnak többek között a html header kiirásáról jquery, bootstrap, és saját js kodok, style.css betöltéséről is. 
 
## A fő könyvtár fájljai
- **composer.json** unittest használja
- **.config.php** applikáció beállítások (a repoban lévő config.txt átnevezésével hozható létre)
- **.htaccess** appache beállítások (a repoban lévő htaccess.txt átnevezésével hozható létre). Biztositja, hogy amennyiben az URL nemlétező fájlra, könyvtárra hivatkozik akkor az index.php aktivizálódik.
- **package.json** unittest használja
- **readme.md** applikáció leíráss 
- **readmeForProgrammer.md** ez a leírás
- **.travis.yl** travis kapcsolat vezérlő
- **.gitignore** github által figyelmen kivül hagyandó dolgok
- **index.php** fő program

## Standart GET vagy POST paraméterek
- **option** controller php fájl neve, ha nincs megdava "default" -ot használ
- **task** controller végrahajtandó methodusa, ha nincs megadva "default" -ot használ
- **lng** nyelv kód, ha nincs megadva akkor a config.php -ból veszi
- **sid** session ID, ha nincs megadva akkor azadott kliens korábbi sessionját használja, illetve új session-t hoz létre.

## SEO barát URL rendszer
```
http[s]://domain[/path]/opt/optionName/taskName/paramName1/paramValue1/paramName2/paramValue2....

illetve:

http[s]://domain[/path]/oauth2/taskName/pName/pvalue....

```

## Standart eljárás új funkció megvalósítására
- készül egy controllers/"ujfun".php fájl, ebben "Ujfun"Controller class, ebbe kerülnek majd a megvalósítandó taskok public methodusok formájában,
- készül egy models/"ujfun".php fájl, ebben "UjfunModel" class, ebbe kerülnek majd a megvalósítandó adatmodell funkciók,
- készül egy views/"ujfun".php fájl,ebben "UjfunView" class, ebbe kerülnek majd a megvalósítandó viewer funkciók,
- opcionálisan készülhet langs/"ujfun"_"lng".php file is ebbe kerülhetnek nyelvi definiciók,
- opcionálisan készülhet langs/"képernyőNaév"_"lng".html  nyelv függő html kód részlet is
- opcionálisan készülhet egy js/"ujfun".js fájl, ebbe kerülhetnek majd a szükséges angularJs funkciók, változók,
- készül egy tests/"ujfunTest".php ez a controller php kodok phpunit rendszer szerinti tesztje
- opcionálisan készülhet egy tests/"ujfunTest".js ez a js kodok mocha rendszer szerinti tesztje
- javasolt a TDD elvek szerint elöször teszteket irni, majd az ezeket megvalósitó kódokat.
 
### Egy tipukus model (models/ujfun.php)
```
class UjfunModel extends Model {
	public function getData(string $id): Record1 {
		$db = new DB();
		$table = DB::Table('tableName');
		...... $res kialakítása az adatbázisból ....
		return $res;
	}
	....
}
```

### Egy tipikus controller (controllers/ujfun.php):
```
class UjfunController extends Controller {
	public function taskName(Request $request) {
	    $this->checkCsrToken($request);
		$model = $this->getModel('ujfun');
		$view = $this->getView('ujfun');
		.....
		$data = new stdClass();
		$this->createCsrToken($request, $data)
		$data->par1 = $request->input('param1');
		$data->par2 = $model->getData($request->input('param1'));
		.....
		$view->show($data);	
	}
	....
}	
```

### Célszerü egy CommonViewer osztályt definiálni (views/common.php) 
```
class CommonView extends View {
	public function echoNavbar(object $data) {
		...... AngularJS elemeket is tartalmazó html kód ....
	}
	public function echoFooter(object $data) {
		...... AngularJS elemeket is tartalmazó html kód ....
	}
}
```

### Egy tipikus viewer  
```
include_once 'views/common.php';
class UjfunView extends CommonView {
	public function show(object $data) {
		
		// Ha a html kód nyelv független:
		
		$this->echoHtmlHead($data);
		?>
        <body ng-app="app">
			<?php $this->echoNavbar($data); ?>
				...... 
				AngularJS elemeket is tartalmazó html kód 
				....
			<?php $this->echoPopup(); ?> 
			<?php $this->loadJavaScriptAngular('ujfun', $data); ?>
			<?php $this->echoFooter($data); ?>
		</body>
		</html>
		<?php
		
		// Ha a html kód nyelv függő (ilyenkor kell langs/"formName"_"lng".html file):	
		
		?>
        <body ng-app="app">
	    	<div ng-controller="ctrl" id="scope" style="display:block; padding:10px;">
				<?php $this->echoNavbar($data); ?>
				<?php $this->echoLngHtml('show',$data); ?>
				<?php $this->echoPopup(); ?> 
				<?php $this->loadJavaScriptAngular('ujfun', $data); ?>
				<?php $this->echoFooter($data); ?>
			</div>
		</body>
		</html>
		<?php
		
	}
	...
}	
A változó adatok {{name}} vagy <?php echo $data->name"; ?> formában irhatóak.
Mindkét esetben használható a txt(value) formájú nyelvi forditó rutin is.
Ha az adat html tag -eket is tartalmazhat akkor csak a második forma használható.
```


## tools scriptrek
A scriptek LINUX terminálban futtathatóak:
- **cd documentroot**
- **./tools/test-php.sh** php unittestek futtatása
- **./tools/test-js.sh**  javascript unittestek futtatása
- **./tools/test.sh** php és javascript unittestek futtatása
- **./tools/sonar.sh** sonarCloud kód ellenörzés futtatása
- **./tools/documentor.sh** php dokumentáció kreálás a doc könyvtárba.
 
## További infók a "doc/index.html" -ben.
 
