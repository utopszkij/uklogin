<?php
class IssuController extends Controller {
	
    /**
     * issu beköldő form kirajzolása
     * @param object $request - title, body, sender, email
     * @return void
     */
    public function form(RequestObject $request) {
        $view = $this->getView('issu');
        $data = new stdClass();
        $data->msgs = [];
        $data->title = $request->input('title');
        $data->body = $request->input('body');
        $data->sender = $request->input('sender');
        $data->email = $request->input('email');
        $view->form($data);
    }
    
    /**
     * issu beküldése
     * @param object $request - title, body, sender, email
     * @retiurn void
     */
    public function send(RequestObject $request) {
        $model = $this->getModel('issu');
        $view = $this->getView('issu');
        $data = new IssuRecord();
        $data->msgs = [];
        $data->title = $request->input('title');
        $data->body = $request->input('body');
        $data->sender = $request->input('sender');
        $data->email = $request->input('email');
        $data->msgs = $model->check($data);
        if (count($data->msgs) == 0) {
            $data->msgs = $model->send($data);
            $view->successMsg();
        } else {
            $view->form($data);
        }
    }
    
}
?>