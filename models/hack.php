<?php

class HackModel {
    function __construct() {
        $db = new DB();
        $db->exec('
        CREATE TABLE IF NOT EXISTS `hacks` (
            `ip` varchar(100) COLLATE utf8_hungarian_ci NOT NULL,
            `errorcount` int(4) NOT NULL,
            KEY `app_idx_ip` (`ip`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci
            ');
    }
    
    public function checkEnabled(string $ip): bool {
        $table = new Table('hacks');
        $table->where(['ip','=',$ip]);
        $rec = $table->first();
        $result = true;
        if ($rec) {
            if ($rec->errorcount > 10) {
                $result = false;
            }
        }
        return $result;
    }
    
    public function ipAddError(string $ip) {
        $table = new Table('hacks');
        $table->where(['ip','=',$ip]);
        $rec = $table->first();
        if ($rec) {
            $rec->errorcount++;
            $table->update($rec);
        } else {
            $rec = new stdClass();
            $rec->ip = $ip;
            $rec->errorcount = 1;
            $table->insert($rec);
        }
    }
    
    public function ipEnable(string $ip) {
        $table = new Table('hacks');
        $table->where(['ip','=',$ip]);
        $table->delete();
    }
    
} // class
?>