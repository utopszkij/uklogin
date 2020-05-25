<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/**
 * AppRecord 
 */
class AppRecord {
    /** egyedi kulcs */
    public $id = 0;
    /** megnevezés */
    public $name = '';
    /** client_id */
    public $client_id = '';
    /** client_secret (régi Oauth2 müködéshez kellett) */
    public $client_secret = '';
    /** client domain URL */
    public $domain = '';
    /** sikeres login után visszahivandó URL (redirect_uri) */
    public $callback = '';
    /** css file */
    public $css = '';
    /** hibás bejelentkezési limit (későbbi esetleges továbbfejlesztéshez) */
    public $falseLoginLimit = 5;
    /** cliens adminisztrátor nickname */
    public $admin = '';
    /** userinfo output format 0-json string, 1-JWE */
    public $jwe = 0;
    /** public key */
    public $pubkey = '';
    /** adatkezelési leírás URL */
    public $policy = '';
    /** default scope */
    public $scope = '';
}

/** AppRegistModel osztály */
class AppregistModel extends Model {
    
    /** konstruktor */
    function __construct() {
        $db = new DB();
        // ha még nincs tábl létrehozzuk
        $db->exec('
        CREATE TABLE IF NOT EXISTS `apps` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) COLLATE utf8_hungarian_ci NOT NULL,
            `client_id` varchar(100) COLLATE utf8_hungarian_ci NOT NULL,
            `client_secret` varchar(100) COLLATE utf8_hungarian_ci NOT NULL,
            `domain` varchar(100) COLLATE utf8_hungarian_ci NOT NULL,
            `callback` varchar(100) COLLATE utf8_hungarian_ci NOT NULL,
            `css` varchar(256) COLLATE utf8_hungarian_ci NOT NULL,
            `falseLoginLimit` int(11) NOT NULL,
            `admin` varchar(32) COLLATE utf8_hungarian_ci NOT NULL,
            `pubkey` text  COLLATE utf8_hungarian_ci NOT NULL,
            `policy` varchar(80) COLLATE utf8_hungarian_ci NOT NULL,
            `scope` varchar(128) COLLATE utf8_hungarian_ci NOT NULL,
            `jwe` int(1) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `app_idx_client_id` (`client_id`),
            KEY `app_idx_domain` (`domain`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci
            ');
        // ha még nincs teszt app rekord létrhozzuk
        $table = new table('apps');
        if ($table->count() == 0) {
            $db->exec('INSERT INTO apps VALUES (
            0,"uklogin","12","13",
            "'.MYDOMAIN.'",
            "'.MYDOMAIN.'/index.php?option=login&task=code","",5,"6766487",
            "","","",0
            )
            ');
            $db->exec('INSERT INTO apps VALUES (
            0,"teszt","0000000000","0000000000",
            "'.MYDOMAIN.'/example.php",
            "'.MYDOMAIN.'/example.php?task=code","",5,"745633"
            )
            ');
        }
    }
    
    /**
     * string koncertálása hexa stringbe
     * @param string $string
     * @return string
     */
    public function strToHex(string $string): string {
        $hex = '';
        for ($i=0; $i<strlen($string); $i++){
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0'.$hexCode, -2);
        }
        return strToUpper($hex);
    }
    
    /**
     * konvert object into Apprecord
     * @param object $obj
     * @return AppRecord
     */
    protected function convert($obj): AppRecord {
        $result = new AppRecord();
        foreach ($result as $fn => $fv) {
            if (isset($obj->$fn)) {
                $result->$fn = $obj->$fn;
            }
        }
        return $result;
    }
    
    /**
     * get data by client_id
     * @param string $client_id
     * @return AppRecord   - hiba esetén $result->error = true
     */
    public function getData(string $client_id): AppRecord {
        $db = new DB();
        $table = $db->table('apps');
        $table->where(['client_id','=',$client_id]);
        $res = $table->first();
        if ($res) {
            $result = $this->convert($res);
        } else {
            $result = new AppRecord();
            $result->error = true;
        }
        return $result;
    }
    
    /**
     * url létezik?
     * @param string $url
     * @return bool
     */
    protected function url_exists(string $url): bool {
        $file_headers = @get_headers($url);
        if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            $exists = false;
        } else {
            $exists = true;
        }
        return $exists;
    }
    
    /**
     * adat ellenörzések0
     * @param object $data
     * @param array $msg
     */
    protected function domainCheck($data, array &$msg) {
        if ($data->domain == 'https://test.hu') {
            $msg[] = 'ERROR_UKLOGIN_HTML_NOT_EXISTS';
        } else if (($data->domain != 'https://valami.hu') &&
                   ($data->domain != 'http://robitc/uklogin') &&
                   ($data->domain != '')) {
                // megjegyzés: valami.hu unitteszthez kell, robitc/uklogin lokális teszthez
                if ($this->url_exists($data->domain.'/uklogin.html')) {
                    try {
                        $lines = file($data->domain.'/uklogin.html');
                        $str = implode("\n",$lines);
                        if (strpos($str,'uklogin') === false) {
                            $msg[] = 'ERROR_UKLOGIN_HTML_NOT_EXISTS';
                        }
                    } catch (Exception $e) {
                        $msgs[] = 'domain check error';
                    }
                } else {
                    $msg[] = 'ERROR_UKLOGIN_HTML_NOT_EXISTS';
                }
        }
    }
    
    /**
     * check record before save
     * az ellenörzések többsége kliens oldalon js -ben is megtörtént
     * - új felvitelnél domain még nem létezik ? (csak szerver oldali ellenörzés)
     *       (unittesthez a "https://test.hu" -ra hibát jelez)
     * - domainen létezik és megfelelő tartalmú  az "uklogin.html" ? (csak szerver oldali ellenörzés)
     *       (unittesthez a "https://valami.hu" -ra nem jelez hibát)
     * @param object $data "apps" rekord
     * @return array of errorMsgstrings or []
     */
    public function check(AppRecord $data): array {
        $msg = [];
        $db = new DB();
        $table = $db->table('apps');
        $this->checkNoEmpty($msg, $data);
        if (!filter_var($data->callback, FILTER_VALIDATE_URL)) {
            $msg[] = 'ERROR_CALLBACK_INVALID';
        }
        if (($data->css != '') && ($data->domain != '') && !(strpos($data->css, $data->domain) === 0)) {
            $msg[] = 'ERROR_CSS_INVALID';
        }
        if (($data->callback != '') && ($data->domain != '') && !(strpos($data->callback, $data->domain) === 0)) {
            $msg[] = 'ERROR_CALLBACK_NOT_IN_DOMAIN';
        }
        if ($data->client_id == '') {
            $table->where(['domain','=',$data->domain]);
            $res = $table->first();
            if (($res) || ($data->domain == 'https://test.hu')) {
                $msg[] = 'ERROR_DOMAIN_EXISTS';
            }
        }
        if (($data->client_id == '') && ($data->domain == 'https://test.hu')) {
            $msg[] = 'ERROR_DOMAIN_EXISTS';
        }
        $this->domainCheck($data, $msg);
        return $msg;
    }
    
    /**
     * check $data propertys no empty and accept data, cookie processing
     * @param array $msg
     * @param AppRecord $data
     * @return void
     */
     protected function checkNoEmpty(array &$msg, AppRecord $data)   {
        if ($data->domain == '') {
            $msg[] = 'ERROR_DOMAIN_EMPTY';
        }
        if (!filter_var($data->domain, FILTER_VALIDATE_URL)) {
            $msg[] = 'ERROR_DOMAIN_INVALID';
        }
        if ($data->name == '') {
            $msg[] = 'ERROR_NAME_EMPTY';
        }
        if ($data->callback == '') {
            $msg[] = 'ERROR_CALLBACK_EMPTY';
        }
        if ($data->admin == '') {
            $msg[] = 'ERROR_ADMIN_EMPTY';
        }
        if ($data->dataProcessAccept != 1) {
            $msg[] = 'ERROR_DATA_ACCEPT_REQUEST';
        }
     }

     /**
      * adjust $data before save it
      * @param object $data
      */
     protected function adjustData(AppRecord &$data) {
         unset($data->dataProcessAccept);
         unset($data->cookieProcessAccept);
         if (!is_numeric($data->falseLoginLimit)) {
             $data->falseLoginLimit = 0;
         }
     }
     
     /**
      * create client_id, client_secret update record
      * @param object $data
      * @param object $table
      * @param array $msg
      * @return void
      */
     protected function updateAfterInsert(AppRecord &$data, &$table, array &$msg) {
         $id = $table->getInsertedId();
         $data->id = $id;
         $data->client_id = $this->strToHex(random_int(100000, 999999).$id);
         $data->client_secret = ''.random_int(100000000, 999999999).$id;
         $table = new Table('apps');
         $table->where(['id','=',$id]);
         $table->update($data);
         $s = $table->getErrorMsg();
         if ($s != '') {
             $msg[] = $s;
         }
     }
     
     /**
     * save record
     * insert -nél client_id és client_secret generálás és tárolás
     * update -nél client_id -t, client_secret -et soha nem modosítja
     * a hibás login limit mezőknél ha üres vagy nem szám akkor 0 -át tárol 
     * @param AppRecord apps record  
     *    + psw1,dataProcessAccept,cookieProcessAccept 
     *    - client_secret,pswhash,adminLoginEnabled 
     * @return object  {"client_id", "application_secret"} or {"error":[...]}
     */
    public function save(AppRecord $data) {
        $db = new DB();
        $table = $db->table('apps');
        $msg = $this->check($data);
        if (count($msg) == 0) {
            $this->adjustData($data);
            if ($data->client_id == '') {
                $data->id = 0;
                $data->client_secret = '';
                // rekord tárolás, új id elérése
                $table->insert($data);
                $s = $table->getErrorMsg();
                if ($s == '') {
                    $this->updateAfterInsert($data, $table, $msg);
                    // client_id, client_secret generálása, tárolása
                } else {
                    $msg[] = $s;
                }
            } else {
                // rekord modosítása
                $s = $this->update($data);
                if ($s != '') {
                    $msg[] = $s;
                }
            }
        }
        $result = new stdClass();
        if (count($msg) == 0) {
            $result->client_id = $data->client_id;
            if (isset($data->client_secret)) {
                $result->client_secret = $data->client_secret;
            }
        } else {
            $result->error = $msg;            
        }
        return $result;
    }
    
    /**
     * remove record from database
     * @param string $client_id
     * @return string
     */
    public function remove(string $client_id): string {
        $msg = '';
        $table = new Table('apps');
        $table->where(['client_id','=',$client_id]);
        $res = $table->first();
        if ($res) {
            $table->delete();
            $msg = $table->getErrorMsg();
        } else {
            $msg = 'ERROR_NOT_FOUND';
        }
        return $msg;
    }
    
    /**
     * update apps record
     * @param object $rec
     * @return string
     */
    public function update(AppRecord $rec): string {
        $db = new DB();
        $table = $db->table('apps');
        $table->where(['client_id','=',$rec->client_id]);
        $old = $table->first();
        foreach ($old as $fn => $fv) {
            if (isset($rec->$fn)) {
                $old->$fn = $rec->$fn;
            } else {
                $old->$fn = $fv;
            }
        }
        $table->update($old);
        return $table->getErrorMsg();
    }
    
    /**
     * get app recordset admin nick alapján
     * @param string $admin
     * @return array
     */
    public function getAppsByAdmin(string $admin): array {
        $db = new DB();
        $table = $db->table('apps');
        $table->where(['admin','=',$admin]);
        return $table->get();
    }
    
} // class
?>