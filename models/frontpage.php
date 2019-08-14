<?php

class FrontpageModel {
   
    public function getAppCount(): int {
        $table = new table('apps');
        return $table->count();
    }
    
    public function getUserCount(): int {
        $table = new table('users');
        return $table->count();
    }
    
  
} // class
?>