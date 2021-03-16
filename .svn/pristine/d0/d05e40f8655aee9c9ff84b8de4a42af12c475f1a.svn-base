<?php

/**
 * Description de la classe BaseModel
 *
 * @author danilo
 */
class BaseModel {

    /** Properties */
    protected $_db_conn;
    protected $_id_agence;

    public function __construct(&$dbc, $id_agence=NULL) {
        $this->setDbConn($dbc);
        $this->setIdAgence($id_agence);
    }

    public function __destruct() {
        
    }

    /** Getters & Setters */
    public function getDbConn() {
        return $this->_db_conn;
    }

    public function setDbConn(&$value) {
        $this->_db_conn = $value;
    }

    public function getIdAgence() {
        return $this->_id_agence;
    }

    public function setIdAgence($value) {
        $this->_id_agence = $value;
    }

}
