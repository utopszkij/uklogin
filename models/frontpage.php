<?php

class FrontpageModel {
   
    /**
     * applikációk számának lekérése
     * @return int
     */
    public function getAppCount(): int {
        $table = new table('apps');
        return $table->count();
    }
    
    /**
     * regisztrált user fiokok számának lekérése
     * @return int
     */
    public function getUserCount(): int {
        $table = new table('users');
        return $table->count();
    }
    
  
} // class
?>