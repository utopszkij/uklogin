<?php
/**
 * uklogin adatbázis közös adatmodel, verzió kezelés
 * @package uklogin
 * @author Fogler Tibor
 */


/** UkloginModel osztály */
class UkloginModel {
    
    /** adatbázis verzió kontrol, szükség esetéb adatbázis update
     * $REQUEST -be is beteszi
     * @param string $version
     */
    public function init(string $version) {
        global $REQUEST;
        $db = New DB();
        $db->createTable('version', 
            [['ver','varchar',32,false]],
            ['ver']);
        $table = new Table('version');
        $verRec = $table->first();
        if (!$verRec) {
            $verRec = JSON_decode('{"ver": "v1.0"}');
            $table->insert($verRec);
        }
        if (($version == 'v1.1') & ($version > $verRec->ver)) {
            $db->alterTable('apps', 'pupkey', 'text',0);
            $db->alterTable('apps', 'policy', 'varchar',80);
            $db->alterTable('apps', 'scope', 'varchar',128);
            $db->alterTable('apps', 'jwe', 'int',1);
        }
        if ($version > $verRec->ver) {
            $verRec->ver = $version;
            $table->update($verRec);
        }
        $REQUEST->set('version',$version);
    }
} // class

?>
