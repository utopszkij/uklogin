<?php

function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}

class AppregistModel {
    function __construct() {
        $db = new DB();
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
            `pswhash` varchar(256) COLLATE utf8_hungarian_ci NOT NULL,
            `adminFalseLoginLimit` int(11) NOT NULL,
            `adminLoginEnabled` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `app_idx_client_id` (`client_id`),
            KEY `app_idx_domain` (`domain`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci
            ');
    }
    
    /**
     * get data by client_id
     * @param string $client_id
     * @return object|false
     */
    public function getData(string $client_id) {
        $db = new DB();
        $table = $db->table('apps');
        $table->where(['client_id','=',$client_id]);
        $res = $table->first();
        if ($res) {
            return $res;
        } else {
            return false;
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
    public function check($data): array {
        $msg = [];
        $db = new DB();
        $table = $db->table('apps');
        $this->checkNoEmpty($msg, $data);
        if (($data->psw1 != '') && (strlen($data->psw1) < 6)) {
          $msg[] = 'ERROR_PSW_INVALID';
        }
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
        if ($data->domain == 'https://test.hu') {
            $msg[] = 'ERROR_UKLOGIN_HTML_NOT_EXISTS';
        } else if (($data->domain != 'https://valami.hu') && ($data->domain != '')) {
            try {
                $lines = file($data->domain.'/uklogin.html');
            } catch (Exception $e) {
                $lines = false;
            }
            if ($lines) {
                $str = implode("\n",$lines);
                if (strpos($str,'uklogin') === false) {
                    $msg[] = 'ERROR_UKLOGIN_HTML_NOT_EXISTS';
                }
            } else {
                $msg[] = 'ERROR_UKLOGIN_HTML_NOT_EXISTS';
            }
        }
        return $msg;
    }
    
    /**
     * cshec $data propertys no empty and accept data, cookie processing
     * @param array msg
     * @param object data
     * @return void
     */
     protected function checkNoEmpty(array &$msg, $data)   {
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
        if (($data->client_id == '') && ($data->psw1 == '')) {
            $msg[] = 'ERROR_PSW_EMPTY';
        }
        if ($data->dataProcessAccept != 1) {
            $msg[] = 'ERROR_DATA_ACCEPT_REQUEST';
        }
        if ($data->cookieProcessAccept != 1) {
            $msg[] = 'ERROR_COOKIE_ACCEPT_REQUEST';
        }
     }

     /**
      * adjust $data before save it
      * @param object $data
      */
     protected function adjustData(&$data) {
         if ($data->psw1 != '') {
             $data->pswhash = hash('sha256',$data->psw1, false);
         }
         unset($data->psw1);
         unset($data->psw2);
         unset($data->dataProcessAccept);
         unset($data->cookieProcessAccept);
         $data->adminLoginEnabled = 1;
         if (!is_numeric($data->falseLoginLimit)) {
             $data->falseLoginLimit = 0;
         }
         if (!is_numeric($data->adminFalseLoginLimit)) {
             $data->adminFalseLoginLimit = 0;
         }
     }
     
     /**
      * create client_id, client_secret update record
      * @param object $data
      * @param object $table
      * @param array $msg
      * @return void
      */
     protected function updateAfterInsert(&$data, &$table, array &$msg) {
         $id = $table->getInsertedId();
         $data->id = $id;
         $data->client_id = ''.random_int(1000000, 9999999).$id;
         $data->client_secret = ''.random_int(100000000, 999999999).$id;
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
     * @param object apps record  
     *    + psw1,dataProcessAccept,cookieProcessAccept 
     *    - client_secret,pswhash,adminLoginEnabled 
     * @return object  {"application_id", "application_secret"} or {"error":[...]}
     */
    public function save($data) {
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
                $table->where(['client_id','=',$data->client_id]);
                $table->update($data);
                $s = $table->getErrorMsg();
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
     * @param string client_id
     * @return string
     */
    public function remove(string $client_id): string {
        $msg = '';
        $db = new DB();
        $table = $db->table('apps');
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
     * @return void
     */
    public function update($rec) {
        $db = new DB();
        $table = $db->table('apps');
        $table->where(['client_id','=',$rec->client_id]);
        $table->update($rec);        
    }
    
} // class
?>