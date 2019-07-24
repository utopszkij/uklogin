<?php

class ExampleModel {
    /**
     * example function
     * @param string $param1
     * @return object
     */
    public function getData(string $param1) {
        $db = new DB();
        $table = $db->table('users');
        $res = $table->first();
        if ($res) {
            return JSON_decode('{ "param1":"'.$param1.'", "avatar":"'.$res->avatar.'", "errorMsg":""}');
        } else {
            return JSON_decode('{ "param1":"'.$param1.'", "errorMsg":"'.$table->getErrorMsg().'"}');
        }
    }
} // class
?>