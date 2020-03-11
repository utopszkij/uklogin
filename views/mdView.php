<?php 
include_once './views/common.php';
class MdView extends CommonView {
    
    protected function processStrong(string &$l) {
        $i = strpos($l, '**');
        if ($i !== false) {
            $l = substr($l, 0, $i).'<strong>'.substr($l,$i+2,200);
        }
        $i = strpos($l, '**');
        if ($i > 0) {
            $l = substr($l, 0, $i).'</strong>'.substr($l,$i+2,200);
        }
    }
    
    protected function processUri(string &$line) {
        $i = strpos($line,'[');
        $j = strpos($line,']');
        $k = strpos($line,'(');
        $l = strpos($line,')');
        $line = substr($line,0,$i).
        '<a href="'.substr($line,$k+1, $l-$k-1).'">'.
        substr($line,$i+1, $j-$i-1).
        '</a>'.
        substr($line,$l+1,400);
    }
    
    public function mdShow($p, string $mdName) {
        $this->echoHtmlHead();
        ?>
        <body>
       	<?php $this->echoNavbar($p); ?>
		<div style="padding:0px 5% 5% 5%; text-align:left">
			<div class="page-icon">	
				<em class="fa fa-book"></em>
			</div>
			<?php 
                $lines = file($mdName);
                $inUl = false;
                $inUl2 = false;
                $inCode = false;
                foreach ($lines as $line) {
                    $line = str_replace('ADATKEZELO_SIGN',config('ADATKEZELO_SIGN'),$line);
                    $line = str_replace('ADATFELDOLGOZO',config('ADATFELDOLGOZO'),$line);
                    $line = str_replace('ADATKEZELO',config('ADATKEZELO'),$line);
                    
                    if (strpos(' '.$line,'  - ') == 1) {
                        if (!$inUl2) {
                            $inUl2 = true;
                            $line = '<ul style="list-style:none"><li><ul><li>'.substr($line,3,800).'</li>';
                        } else {
                            $line = '<li>'.substr($line,3,800).'</li>';
                        }
                    } else {
                        if ($inUl2) {
                            echo '</ul></li></ul>';
                        }
                        $inUl2 = false;
                    }
                    
                    if (strpos(' '.$line,'- ') == 1) {
                        if (!$inUl) {
                            $inUl = true;
                            $line = '<ul><li>'.substr($line,2,800).'</li>';
                        } else {
                            $line = '<li>'.substr($line,2,800).'</li>';
                        }
                    } else {
                        if ($inUl) {
                            echo '</ul>';
                            $inUl = true;
                        }
                        $inUl = false;
                    }
                    
                    if (strpos(' '.$line,'```') == 1) {
                        $line = substr($line,3,800);
                        if (!$inCode) {
                            $inCode = true;
                            echo '<pre><code>';
                        } else {
                            echo '</code></pre>';
                            $inCode = false;
                        }
                    }
                    
                    if (strpos(' '.$line,'# ') == 1) {
                        $line = '<h1>'.substr($line,1,800).'</h1>';
                    }
                    if (strpos(' '.$line,'## ') == 1) {
                        $line = '<h2>'.substr($line,2,800).'</h2>';
                    }
                    if (strpos(' '.$line,'### ') == 1) {
                        $line = '<h3>'.substr($line,3,800).'</h3>';
                    }
                    if (strpos(' '.$line,'#### ') == 1) {
                        $line = '<h4>'.substr($line,4,800).'</h4>';
                    }
                    if (trim($line) == '') {
                        echo '<br />';
                    }
                    $this->processStrong($line);
                    $this->processStrong($line);
                    $this->processStrong($line);
                    $this->processStrong($line);
                    if (strpos($line, '[') !== false) {
                        $this->processUri($line);
                    }
                    if ($inCode) {
                      $line = str_replace('<','&lt;',$line);
                      $line = str_replace('>','&gt;',$line);
                 	  }
                    echo $line;
                }
			?>
       	</div>
       	<?php $this->echoFooter($p); ?>
       	</body>
        <?php 	
        $this->echoHtmlEnd();
    }
}
?>