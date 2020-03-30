<?php
class ExampleController extends Controller {
    /**
     * example task
     * @param Request $request
     *  - string param1 
     */
    public function example(RequestObject $request) {
	    // get params from $request
        $param1 = $request->input('param1','param1');
	    
        // get Model, Viewer, 
        $model = $this->getModel('example');
        $view = $this->getView('example');
	    
	    // task process 
	    $data = $model->getData($param1);
	    
	    // if this task is AJAX backend then send json header
	    if (!headers_sent()) {
	        header('Content-Type: json');
	    }
	    
	    // exho result
	    $data->option = $request->input('option','default');
	    $view->example($data);
	}
}
?>