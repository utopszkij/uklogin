<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/** AuditorController  sysadmin funkciók */
class AuditorController extends Controller {
    
    /**
     * információs képernyő a hitelesítésről (többek között hitelesítő lista)
     * valami a loggedUser "code" adatát is kiirja 
     * @param Request $request
     */
    public function info(Request $request) {
        echo 'auditor controller info task';
    }
    
    /**
     * sysadmin képernyő, csak sysadmin használhatja
     * lehetőségek:
     * - új sysadmin felvétele   nickname --> addsysadmin
     * - sysadmin jog megvonása  nickname --> delsysadmin
     * - user hitelesités inditása (code --> userauditform)
     * - saját publikus adataim megadása (txt -->saveauditorino)
     * 
     * @param Request $request - sysssionban loggedUser
     */
    public function form(Request $request) {
        echo 'auditor controller form task';
        
    }
    
    /**
     * sysadmin jog hozzáadása a paraméterben érkező 'nickname' usernek
     * csak sysadmin használhatja, csrToken ellenörzés
     * sikeres vagy hibás végrehajtás után  msgs --> form,
     * @param Request $request - nickname, csrToken, lsessionban loggedUser
     */
    public function addsysadmin(Request $request) {
        echo 'auditor controller addsysadmin task';
    }
    
    /**
     * sysadmin jog megvonásaa a paraméterben érkező 'nickname' usernek
     * csak sysadmin használhatja, csrToken ellenörzés
     * FIGYELEM ha csak egy sysadmin van akkor nem végrehajható!
     * sikeres vagy hibás végrehajtás után msgs --> form,
     * @param Request $request - nickname, csrToken, lsessionban loggedUser
     */
    public function delsysadmin(Request $request) {
        echo 'auditor controller delsysadmin task';
    }
    
    /**
     * user auditálási form. Csak sysadmin használhatja
     * kiirja a user tárolt adatait, 
     * origname, mothersName, code, birth_date --> douseraudit
     * @param Request $request - code, csrToken, sessionban loggedUser
     */
    public function userauditForm(Request $request) {
        echo 'auditor controller userauditform task';
    }
    
    /**
     * user hitelesitése code alapján, csak sysadmin használhatja
     * msgs --> form
     * @param Request $request - csrToken, code, origname, mothersName, birth_date
     */
    public function douseraudit(Request $request) {
        echo 'auditor controller douseraudit task';
        
    }
    
    /**
     * auditor publikos infok tárolása, csak sysadmin használhatja
     * msgs --> form
     * @param Request $request - csrTokem, txt, sessionban loggedUser
     */
    public function saveaudiorinfo(Request $request) {
        echo 'auditor controller saveauditorinfo task';
    }
}
?>