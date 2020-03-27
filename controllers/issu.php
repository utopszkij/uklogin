<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/** IssuController osztály   Hibajelzés beküldés */
class IssuController extends Controller {
	
    /**
     * issu beköldő form kirajzolása
     * @param object $request - title, body, sender, email
     * @return void
     */
    public function form(Request $request) {
        $view = $this->getView('issu');
        $data = new Params();
        $data->msgs = [];
        $data->title = $request->input('title');
        $data->body = $request->input('body');
        $data->sender = $request->input('sender');
        $data->email = $request->input('email');
        $data->adminNick = $request->sessionGet('adminNick','');
        $data->access_token = $request->sessionGet('access_token','');
        $view->form($data);
    }
    
    /**
     * issu beküldése
     * @param object $request - title, body, sender, email
     * @retiurn void
     */
    public function send(Request $request) {
        $model = $this->getModel('issu');
        $view = $this->getView('issu');
        $data = new Params();
        $data->msgs = [];
        $data->title = $request->input('title');
        $data->body = $request->input('body');
        $data->sender = $request->input('sender');
        $data->email = $request->input('email');
        $data->adminNick = $request->sessionGet('adminNick','');
        $data->access_token = $request->sessionGet('access_token','');
        $rec = new IssuRecord();
        foreach ($rec as $fn => $fv) {
            if (isset($data->$fn)) {
                $rec->$fn = $data->$fn;
            } else {
                $rec->$fn = $fv;
            }
        }
        $data->msgs = $model->check($rec);
        if (count($data->msgs) == 0) {
            $data->msgs = $model->send($rec);
            $view->successMsg([txt('ISSU_SAVED')],'','',true);
        } else {
            $view->form($data);
        }
    }
    
}
?>