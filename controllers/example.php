<?php
class ExampleController {
    /**
     * example task
     * @param Request $request
     *  - string param1 
     */
    public function example($request) {
	    // get params from $request
        $param1 = $request->input('param1','param1');
	    
        // get Model, Viewer, 
	    $model = getModel('example');
	    $view = getView('example');
	    
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