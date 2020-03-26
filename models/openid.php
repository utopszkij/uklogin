<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * openid adatmodel oi_users táblát használja
 * két config opció van GDPR=true vagy false
 *a "false" esetben csak a csilaggal jelzett mezőket használjuk (a többi üres)
 * @package uklogin
 * @author Fogler Tibor
 */

/**
 * User rekord
 * @author utopszkij
 */
class UserRecord {
    /** azonosító szám**/
    public $id = 0; // *
    /** azonosító kód **/
    public $sub = '';  // * hash(id)
    /** bejelentkezési név **/
    public $nickname = 'GUEST'; // * bejelentkezési név
    /** jelszó hash **/
    public $pswhash = ''; // * jelszó sha256 hash
    /** keresztnév1 **/
    public $given_name = ''; // első keresztnév
    /** keresztnév 2**/
    public $middle_name = ''; // második keresztnév
    /** családnév **/
    public $family_name = ''; // családnév
    /** anyja neve **/
    public $mothersname = ''; // anyja neve
    /** email **/
    public $email = ''; // email
    /** email ellenörzött **/
    public $email_verified = 0; // email ellnörzött?
    /** telefonszám **/
    public $phone_number = ''; // telefonszám
    /** telefonszám ellenörzött **/
    public $phone_number_verified = 0; // telefonszám ellenörzött?
    /** lakcím utca hézszám stb **/
    public $street_address = ""; // * utca, házszám, emelet
    /** település **/
    public $locality = ''; // * település
    /** irsz **/
    public $postal_code = ''; // * irányító szám
    /** születési dátum **/
    public $birth_date = ''; // születési dátum timestram
    /** nem **/
    public $gender = 'man'; // 'man' vagy 'woman'
    /** kép url **/
    public $picture = ''; // avatar kép uri
    /** profil web oldal url **/
    public $profile = ''; // profil web uri
    /** modositva **/
    public $updated_at = 0; // utolsó modosítás timestamp
    /** létrehozva **/
    public $created_at = 0;
    /** ellenörzött **/
    public $audited = 0; // * hitelesített vagy nem?
    /** ellenör **/
    public $auditor = ''; // * auditor nick name vagy "ugyfélkapu"
    /** ellenörzés időpontja **/
    public $audit_time = 0; // * auditálás időpontja
    /** rendszer admin **/
    public $sysadmin = 0;
    /** kód **/
    public $code = ''; // * hash(origname.mothersname,birth_date)
    /** születési név **/
    public $origname = '';
    /** egyedi kód **/
    public $signdate = ''; // *
}

/**
 * OpenIdModel
 * @author utopszkij
 */
class OpenidModel {
    
    /**
     * Tábla név
     * @var string
     */
    protected $tableName = 'oi_users';
    
    /**
     * konstruktor
     */
    function __construct() {
        $varchar = 'varchar';
        $int = 'int';
        $this->db = new DB();
        $this->db->createTable('oi_users',[
                ['id',$int,11,true],
                ['sub',$varchar,384,false], // hexa azonosító  coded(name+mothersname+birth_date)
                ['nickname',$varchar,32,false], // bejelentkezési név
                ['pswhash',$varchar,384,false],
                ['given_name',$varchar,64,false],
                ['middle_name',$varchar,64,false],
                ['family_name',$varchar,64,false],
                ['mothersname',$varchar,64,false],
                ['email',$varchar,80,false],
                ['email_verified',$int,1,false],
                ['phone_number',$varchar,32,false],
                ['phone_number_verified',$int,1,false],
                ['street_address',$varchar,64, false],
                ['locality',$varchar,64, false],
                ['postal_code',$varchar,16, false],
                ['birth_date',$varchar,10, false],
                ['gender',$varchar,8, false],
                ['picture',$varchar,255, false],
                ['profile',$varchar,255, false],
                ['updated_at',$int,11,false],
                ['created_at',$int,11,false],
                ['audited',$int,1,false],
                ['auditor',$varchar,128,false],
                ['audit_time',$int,11,false],
                ['sysadmin',$int,1,false],
                ['code',$varchar,512,false],    	            
            
                ['origname',$varchar,128,false],
                ['signdate',$varchar,10,false]
        ],
        ['id','sub','code']);
    }
    
    /**
     * esetleges későbbi fejlesztéshez
     * az id és a code adatot nem kodolja
     * ha lesz kodolás akkor táblában a mező mérteket növelni kell
     * @param UserRecord $user
     * @param string $privKey
     * @return UserRecord
     */
    protected function encrypt($user, string $privKey = 'default'): UserRecord {
        $result = new UserRecord();
        if (!isset($user->pswhash)) {
            unset($result->pswhash);
        }
        foreach ($user as $fn => $fv) {
            $result->$fn = $fv;
        }
        return $result;
    }
    
    /**
     * esetleges későbbi fejlesztéshez
     * az id és a code nincs kodolva
     * @param UserRecord $user
     * @param string $pubKey
     * @return UserRecord
     */
    protected function decrypt($user, string $pubKey = 'default'): UserRecord {
        $result = new UserRecord();
        foreach ($user as $fn => $fv) {
            $result->$fn = $fv;
        }
        return $result;
    }
    
    
    /**
     * új user rekord felvitele
     * @param UserRecord $userRec
     * @return string
     * @return '' vagy hibaüzenet
     */
    public function saveUser(UserRecord &$userRec): string {
        $encryptedUser = $this->encrypt($userRec);
        $table = new Table($this->tableName);
        $userRec->updated_at = time();
        if ($userRec->id == 0) {
            $table->insert($encryptedUser);
            $userRec->id = $table->getInsertedId();
        } else {
            $table->where(['id','=',$userRec->id]);
            $table->update($encryptedUser);
        }
        return $table->getErrorMsg();
    }
    
    /**
     * user rekord olvasása nickname alapján
     * @param string $nick
     * @return UserRecord 
     */
    public function getUserByNick(string $nick): UserRecord {
        $result = new UserRecord();
        $table = new Table($this->tableName);
        $table->where(['nickname','=',$nick]);
        $res = $table->first();
        if ($res) {
            $result = $this->decrypt($res);
        }
        return $result;
    }
    
    /**
     * client beolvasása client_id alapján, ha nincs akkor domain kiemelése
     * a name propertibe
     * @param string $client_id
     * @return object {"name":"...", "domain":"...", "callback":"..."}
     */
    public function getApp(string $client_id): AppRecord {
        $result = new AppRecord();
        $table = new Table('apps');
        $table->where(['client_id','=',$client_id]);
        $res = $table->first();
        if ($res) {
            foreach ($res as $fn => $fv) {
                $result->$fn = $fv;
            }
        } else {
            $result->name = ' '.parse_url($client_id, PHP_URL_HOST);
            $result->callback = '';
            $result->domain = '';
        }
        return $result;
    }
    
    /**
     * user rekord olvasása code alapján
     * @param string $code
     * @return UserRecord
     */
    public function getUserByCode(string $code): UserRecord {
        $result = new UserRecord();
        $table =  new Table($this->tableName);
        $table->where(['code','=',$code]);
        $res = $table->first();
        if ($res) {
            $result = $this->decrypt($res);
        }
        return $result;
    }
    
    /**
     * User rekord törlése
     * @param int $id
     * @return string
     */
    public function delUser(int $id): string {
        $table =  new Table($this->tableName);
        $table->where(['id','=',$id]);
        $table->delete();
        return $table->getErrorMsg();
    }
                
} // class
?>
