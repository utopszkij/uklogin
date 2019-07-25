<?php
class BrowserController {
    /**
     * example browser task
     * @param Request $request
     *  - string param1 
     */
    public function browser($request) {
	    // get params from $request
        $param1 = $request->input('param1','param1');
	    
        // get Model, Viewer, 
	    $model = getModel('browser');
	    $view = getView('browser');
	    
	    // task process 
	    $data = $model->getData($param1);
	    
	    // if this task is AJAX backend then send json header
	    if (!headers_sent()) {
	        header('Content-Type: json');
	    }
	    
	    // exho result
	    $data->option = $request->input('option','default');
	    $view->browser($data);
	}
}
?>