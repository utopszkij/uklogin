<?php

class Oauth2Model {
    function __construct() {
        $db = new DB();
        $db->exec('
        CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `client_id` varchar(256),
            `nick` varchar(100),
            `pswhash` varchar(256),
            `signhash` varchar(256),
            `enabled` int(1),
            `errorcount` int(11),
            `code` varchar(256),
            `access_token` varchar(256),
            `codetime` varchar(32),
            `blocktime` varchar(32),
            PRIMARY KEY (`id`),
            KEY `users_nick` (`client_id`, `nick`),
            KEY `users_sign` (`client_id` ,`signhash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci
            ');
        
        // clear old code and access_token
        $w = date('Y-m-d H:i:s', (time() - CODE_EXPIRE));
        $db->exec('UPDATE users
        SET code="", access_token="", codetime=""
        WHERE codetime < "'.$w.'"');
        
        // több mint 10 órája blokkolt userek feloldása
        $w = date('Y-m-d H:i:s', (time() - 36000));
        $db->exec('UPDATE users
        SET enabled=1, blocktime""
        WHERE blocktime < "'.$w.'"');
        
    }
    
    /**
     * covert str to hex format
     * @param string $string
     * @return string
     */
    protected function strToHex(string $string): string {
        $hex = '';
        for ($i=0; $i<strlen($string); $i++){
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0'.$hexCode, -2);
        }
        return strToUpper($hex);
    }
    /**
     * get user data by client_id and nick
     * @param string $client_id
     * @return object|false
     */
    public function getUserByNick(string $client_id, $nick) {
        $db = new DB();
        $table = $db->table('users');
        $table->where(['client_id','=',$client_id]);
        $table->where(['nick','=',$nick]);
        $res = $table->first();
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    /**
     * get user data by client_id and signhash
     * @param string $client_id
     * @return object|false
     */
    public function getUserBySignHash(string $client_id, $signHash) {
        $db = new DB();
        $table = $db->table('users');
        $table->where(['client_id','=',$client_id]);
        $table->where(['signhash','=',$signHash]);
        $res = $table->first();
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }
    
    /**
     * get user data by code
     * @param string $client_id
     * @return object|false
     */
    public function getUserByCode(string $code) {
        $db = new DB();
        $table = $db->table('users');
        $table->where(['code','=',$code]);
        $rec = $table->first();
        if ($rec) {
            $rec->code = '';
            $table->update($rec);
            return $rec;
        } else {
            return false;
        }
    }
    
    /**
     * get user data by access_tokern
     * @param string $access_token
     * @return object|false
     */
    public function getUserByAccess_token(string $access_token) {
        $db = new DB();
        $table = $db->table('users');
        $table->where(['access_token','=',$access_token]);
        $rec = $table->first();
        if ($rec) {
            $rec->code = '';
            $rec->access_token = '';
            $rec->codetime = '';
            $table->update($rec);
            return $rec;
        } else {
            return false;
        }
    }
    
    public function check($rec): array {
        $result = [];
        if ($rec->nick == '') {
            $result[] = 'ERROR_NICK_EMPTY';
        }
        if ($rec->psw1 == '') {
            $result[] = 'ERROR_PSW_EMPTY';
        } else {
            if (strlen($rec->psw1) < 6) {
                $result[] = 'ERROR_PSW_INVALID';
            }
            if ($rec->psw1 != $rec->psw2) {
                $result[] = 'ERROR_PSW_NOTEQUAL';
            }
        }
        $rec2 = $this->getUserByNick($rec->client_id, $rec->nick);
        if ($rec2) {
            $result[] = 'ERROR_NICK_EXISTS';
        }
        $rec2 = $this->getUserBySignHash($rec->client_id, $rec->signHash);
        if ($rec2) {
            $result[] = 'ERROR_SIGN_EXISTS';
        }
        return $result;
    }
    
    public function addUser($rec): array {
        $rec->blocktime = '';
        $rec->codetime = '';
        $table = new Table('users');
        $table->insert($rec);
        $errorMsg = $table->getErrorMsg(); 
        if ($errorMsg == '') {
            $result = [];
        } else {
            $result = [];
            $result[] = $errorMsg;
        }
        return $result;
    }
    
    public function updateUser($rec): array {
        $table = new Table('users');
        $table->where(['id','=',$rec->id]);
        $table->update($rec);
        $msg = $table->getErrorMsg();
        $msgs = [];
        if ($msg != '') {
            $msgs[] = $msg;
        }
        return $msgs;
    }
    
    public function deleteUser($rec): array {
        return ['nincs kész'];
    }
    
   
} // class
?>