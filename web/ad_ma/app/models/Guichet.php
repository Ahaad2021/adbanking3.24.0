<?php

/**
 * Description de la classe Guichet
 *
 * @author danilo
 */

require_once 'ad_ma/app/models/BaseModel.php';

class Guichet extends BaseModel {

    public function __construct(&$dbc, $id_agence=NULL) {        
        parent::__construct($dbc, $id_agence); 
    }

    public function __destruct() {
        parent::__destruct();
    }

}
