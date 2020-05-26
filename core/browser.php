<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */


include_once 'views/common.php';

/**
   
a browser controller feladata
- offset, limit, order, order_dir inputból vagy sessionból vagy alapértelmezett
- offset, limit, order, order_dir tárolás sessionba
- viewer hívása

*/
class BrowserView extends CommonView {
/**
      * echo paginator
      * szükséges JS: paginatorClick($offset)
      * szükséges language konstansok: TOTAL, FIRST, LAST, NEXT, PRIOR
      * @param int $total
      * @param int $offset
      * @param int $limit
      */
     protected function echoPaginator(int $total, int $offset, int $limit) {
         $offsetPrev = $offset - $limit;
         $offsetLast = 0;
         if ($offsetPrev < 0) {
             $offsetPrev = 0;
         }
         echo '<ul class="paginator">';
         echo '<li class="paginator-total"><a class="page-link disabled">'.txt('TOTAL').': '.$total.' '.txt('PAGES').':</a></li>';
         if ($offset > 0) {
             echo '<li class="paginator-item" paginatr-first><a href="#" class="page-link" onclick="paginatorClick(0)">
                <em class="fa fa-backward" title="'.txt('FIRST').'"></em>
              </a></li>';
             echo '<li class="paginator-item" paginator-prior><a href="#" class="page-link" onclick="paginatorClick('.$offsetPrev.')">
                <em class="fa fa-caret-left" title="'.txt('PRIOR').'"></em></a></li>';
         }
         $p = 1;
         $n = 1;
         if ($offset < (2*$limit)) {
				$n = 3;         
         }
         if ($offset > ($total - (2*$limit))) {
				$n = 3;         
         }
         for ($o = 0; $o < $total; $o = $o + $limit) {
         	if (($o == 0) | 
         		 (($o >= ($offset - ($n*$limit))) & ($o <= ($offset + ($n*$limit)))) |
         		 ($o >= ($total - $limit))) {
	             if ($o == $offset) {
	                 echo '<li class="paginator-item active"><a href=""  class="page-link disabled" onclick="false">'.$p.'</a></li>';
	             } else {
	                 echo '<li class="pageinator-item paginator-item-middle"><a href="#"  class="page-link" onclick="paginatorClick('.$o.')">'.$p.'</a></li>';
	             }
	             $offsetLast = $o;
	          } else {
					 if ($o == ($offsetLast + $limit)) {
						echo '<li class="paginator-item-middle">...</li>';					 
					 } 	          
	          }   
             $p = $p + 1;
         }
         $offsetNext = $offset + $limit;
         if ($offsetNext >= $offsetLast) {
             $offsetNext = $offsetLast;
         }
         if ($offset < $offsetLast) {
             echo '<li class="paginator-item paginator-next"><a href="#" class="page-link" onclick="paginatorClick('.$offsetNext.')">
                <em class="fa fa-caret-right" title="'.txt('NEXT').'"></em></a></li>';
             echo '<li class="paginator-item paginator-last"><a href="#" class="page-link" onclick="paginatorClick('.$offsetLast.')">
                <em class="fa fa-forward" title="'.txt('LAST').'"></em></a></li>';
         }
         echo '</ul>';
         echo '</div>';
     } // echoPaginator
     
     /**
     * tábla fejléc kirajzolása
     * @param array $items
     * @param string $order
     * @param $order_dir
     * @return void
     */
	  protected function echoTableHead(array $items, string $order, string $order_dir) {
	  	 if (count($items) > 0) {
	  	 	 ?>
		  	 <thead class="thead-dark">
		  	 <tr>
		  	 <?php
		  	 foreach ($items[0] as $fn => $fv) {
		  	 	if ($fn == $order) {
		  	 		$thClass = 'order';
		  	 		if (($order_dir == 'DESC') | ($order_dir == 'desc')) {
		  	 			$thIcon = '<em class="fa fa-caret-up"></em>';
		  	 		} else {
		  	 			$thIcon = '<em class="fa fa-caret-down"></em>';
		  	 		}
		  	 	} else {
		  	 		$thClass = 'unorder';
		  	 		$thIcon = '';
		  	 	}
		  	 	?>
		  	 	<th class="<?php echo 'th_'.$fn.' '.$thClass; ?>" style="cursor:pointer" 
		  	 	    onclick="titleClick('<?php echo $fn; ?>','<?php echo $order; ?>','<?php echo $order_dir; ?>')">
		  	 	    <?php echo txt($fn).'&nbsp;'.$thIcon; ?>
		  	 	</th>
		  	 	<?php
		  	 }
		  	 echo "</tr>\n</thead>\n";
	  	 }
	  }	     
     
     /**
     * egy tábla sor kirajzolása
     * @param object $item - legyen benne id
     * @param string $trClass css class name a "tr" elemhez
     * @return void
     */
     protected function echoTableRow($item, $trClass) {
     		echo '<tr onclick="itemClick('.$item->id.')" class="'.$trClass.'" style="cursor:pointer">'."\n";
     		foreach ($item as $fn => $fv) {
				echo '<td class="td_'.$fn.'">'.$fv.'</td>'."\n";     		
     		}
     		echo "</tr>\n";
     }
     
     /**
     * browser táblázat megjelenítése
     * szükséges JS: titleClick(colName, order, order_dir), itemClick(id)
     * @param array $items  rekordokat tartalmazó tömb
     * @param int $offset
     * @param int $limit
     * @param string $order
     * @param string $order_dir ASC vagy DESC 
     */
     protected function echoBrowserTable(array $items, int $offset, int $limit, 
        string $order, string $order_dir) {
        ?>
        <table class="table table-bordered">
				<?php $this->echoTableHead($items, $order, $order_dir); ?>
				<tbody>
				<?php 
				$trClass = 'tr0';
				foreach ($items as $item) {
					$this->echoTableRow($item,$trClass);
					if ($trClass == 'tr0') {
						$trClass = 'tr1';					
					} else {
						$trClass = 'tr0';					
					}
				}
				?>
				</tbody>        
        </table>
        <?php	
     }
     
	  /**
	  * browser form
	  * @param Params $p - formTitle, formHelp, formIcon option, itemTask,
	  *                    items, total, offset, order, order_dir, searchstr, csrToken
	  */
     public function browserForm(Params $p) {
			$this->echoHtmlHeader($p);
			echo "<body>\n";
			$this->echoNavBar($p);
			$this->echoHtmlPopup($p);
			?>
			<script type="text/javascript" src="<?php echo config('MYDOMAIN'); ?>/core/browser.js"></script>
			<?php if(file_exists(config('MYPATH').'/views/'.$p->option.'.js')) : ?>
			<script type="text/javascript" src="<?php echo config('MYDOMAIN'); ?>/views/<?php echo $p->option; ?>.js"></script>
			<?php endif; ?>
			<div class="page" id="page">
				<div class="pageBody <?php echo $p->option; ?>" id="pageBody">
					<p><em class="fa <?php echo $p->formIcon; ?>"></em></p>
					<h3><?php echo $p->formTitle; ?></h3>				
					<?php if (isset($p->formHelp)) : ?>
					<p><?php echo $p->formHelp; ?></p>
					<?php endif; ?>
					<?php if (isset($p->formSubTitle)) : ?>
					<p><?php echo $p->formSubTitle; ?></p>
					<?php endif; ?>
					<form id="browserForm" method="get" action="<?php echo config('MYDOMAIN'); ?>" target="_self">
						<input type="hidden" id="option" name="option" value="<?php echo $p->option; ?>" /> 
						<input type="hidden" id="task" name="task" value="list" /> 
						<input type="hidden" id="itemTask" name="itemTask" value="<?php echo $p->itemTask; ?>" /> 
						<input type="hidden" name="<?php echo $p->csrToken; ?>" value="1" /> 
						<input type="hidden" id="offset" name="offset" value="<?php echo $p->offset; ?>" /> 
						<input type="hidden" id="limit" name="limit" value="<?php echo $p->limit; ?>" /> 
						<input type="hidden" id="order" name="order" value="<?php echo $p->order; ?>" /> 
						<input type="hidden" id="order_dir" name="order_dir" value="<?php echo $p->order_dir; ?>" />
						<input type="hidden" id="id" name="id" value="" />
						<div class="search">
							<input type="text" name="searchstr" id="searchstr" value="<?php echo $p->searchstr; ?>" />
							<div style="display:inline-block; width:auto">
								<button type="button" id="searchBtn" onclick="searchClick()" class="btn btn-primary">
									<em class="fa fa-search"></em>
								</button>	
								<button type="button" id="delSearchBtn" onclick="delSearchClick()" class="btn btn-danger">
									<em class="fa fa-remove"></em>
								</button>	
							</div>					
						</div> 
						<?php $this->echoBrowsertable($p->items, $p->offset, $p->limit, $p->order, $p->order_dir); ?>
						<?php if (isset($p->addUrl)) : ?>
						<div class="browserButtons">
						<button type="button" id="addBtn" class="btn btn-primary">
							<em class="fa fa-plus-circle"></em>&nbsp;<?php echo txt('ADD'); ?>
						</button> 	
						</div>
						<?php endif; ?>
						<?php $this->echoPaginator($p->total, $p->offset, $p->limit); ?>
					</form>	
					<script type="text/javascript">
					global = {};
					global.LNG = {}; 
					global.LNG.MYDOMAIN = "<?php echo config('MYDOMAIN'); ?>";					
					global.LNG.csrToken = "<?php echo $p->csrToken; ?>";					
					</script>			
				</div>			
			</div>
			<?php
			$this->echoFooter($p);
			echo "</body>
			</html>\n"; 
						
     }
     
}
?>