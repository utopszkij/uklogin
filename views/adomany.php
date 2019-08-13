<?php
include_once './views/common.php';
class AdomanyView  extends CommonView  {
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
	    echo htmlHead();
        ?>	
        <body ng-app="app">
          <?php $this->echoNavbar($p); ?>
          <div ng-controller="ctrl" id="scope" style="display:block; padding:20px;">
            <h2>Adományozás</h2>
          	<h3>Tájékoztatás</h3>
          	<p>A szoftver teljes egészében önkéntes munkával, grátisz lett kifejlesztve.
          	A rendszer üzemeltetése is ilyen formában van megoldva. Viszont a müködéshez szükséges 
          	VPS szerver pénzbe kerül. Jelenleg az a Forpsi-cloud small szervert,ingyenes domaint 
          	és inygenes https tanusitványt használunk.
          	Az adományokat kizárólag a szerver bérlés finanszirozására használjuk fel.
          	Ha komolyabb érdeklődés lesz a rendszer használatára akkor domain név és minősített https
          	tanusitvány beszerzése és fentartása is szükséges lehet.</p>
          	<p>Minden adományt - akár 1000 Ft -ot is - hálássan köszönünk. Az adományok beérkezéséről és
          	felhasználásáról lentebb részletes elszámolást teszünk közz.</p>
          	<p>Ha azt kivánja, hogy neve vagy szervezete szerepeljen az elszámolásban akkor azt az utalás
          	közleményében jelezze! Ha a közleményben erről nem rendelkezik az adományt névtelenül
          	szerepeltetjük az elszámolásban.</p>
          	
          	<p>Bankszámla: 11600006-00000000-23190212</p>
          	<p>IBAN: HU75 1160 0006 0000 0000 2319 0212</p>
          	
          	<p>Ethereum tárca: 0xb7233a1474eb3f0359b01A83e57C636DE78C09Da</p>
          	
          	<h3>Elszámolás</h3>
          	<table class="table">
          		<thead class="thead-dark">
          			<tr><th>Dátum</th>
          			    <th>Összeg (HUF)</th>
          			    <th>Leírás</th>
          			</tr>
          		</thead>
          		<tboy>
          			<tr>
          				<td>2019.08.02</td>
          				<td alight="right">+1500</td>
          				<td>Adomány  Fogler Tibor</td>
          			</tr>
          			<tr>
          				<td>2019.08.02</td>
          				<td alight="right">-1500</td>
          				<td>Forpsi VPS egyenleg feltöltés</td>
          			</tr>
          		</tboy>
          	</table>
	      </div><!-- #scope -->
		  <?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 		
	}
}
?>

