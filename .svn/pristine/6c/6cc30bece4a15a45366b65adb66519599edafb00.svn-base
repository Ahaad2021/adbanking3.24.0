<?php

/**
 * Description de la classe Audit
 *
 * @author danilo
 */
class Audit {

    /** Properties */
    private $_db_conn;
    private $_id_audit;
    private $_date_crea;
    private $_date_maj;
    private $_id_ag_local;
    private $_id_ag_distant;
    private $_nom_login;
    private $_id_client_distant;
    private $_id_compte_distant;
    private $_type_transaction;
    private $_type_choix;
    private $_type_choix_libel;
    private $_montant;
    private $_code_devise_montant;
    private $_commission;
    private $_code_devise_commission;
    private $_commission_ope_deplace;
    private $_post_message;
    private $_id_his_local;
    private $_id_ecriture_local;
    private $_id_his_distant;
    private $_id_ecriture_distant;
    private $_error_message;
    private $_sql_log;
    private $_success_flag;
    private $_comm_agent;
    private $_comm_ins;

    public function __construct() {
        
    }

    public function __destruct() {
        
    }

    /**
     * Renvoie le prochain ID audit libre dans la base
     * 
     */
    public function getNewAuditID() {
        global $db;
        global $dbHandler;

        $db = $dbHandler->openConnection();

        $sql = "SELECT nextval('adsys_audit_multi_agence_id_audit_seq');";

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(true);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $id_audit = $result->fetchrow();

        $dbHandler->closeConnection(true);

        return $id_audit[0];
    }

    /**
     * Sauvegarder la transaction en cours
     * 
     */
    public function insertTransacData($nom_login, $id_ag_local, $id_ag_distant, $id_client_distant, $id_compte_distant, $type_transaction, $type_choix, $type_choix_libel, $montant, $post_message='', $code_devise_montant='', $commission=0, $code_devise_commission='',$commission_ope_deplace=0, $comm_agent = 0, $comm_ins = 0) {

        global $dbHandler;

        $db = $dbHandler->openConnection();
        // Set properties
        $this->setIdAudit(self::getNewAuditID());
        $this->setDateCrea(date("r"));
        $this->setNomLogin($nom_login);
        $this->setIdAgenceLocal($id_ag_local);
        $this->setIdAgenceDistant($id_ag_distant);
        $this->setIdClientDistant($id_client_distant);
        $this->setIdCompteDistant($id_compte_distant);
        $this->setTypeTransaction($type_transaction);
        $this->setTypeChoix($type_choix);
        $this->setTypeChoixLibel($type_choix_libel);
        $this->setMontant($montant);
        $this->setCodeDeviseMontant($code_devise_montant);
        $this->setCommission($commission);
        $this->setCodeDeviseCommission($code_devise_commission);
        $this->setCommissionOpeDep($commission_ope_deplace);
        $this->setPostMessage($post_message);
        $this->setSuccessFlag('f');
        $this->setCommAgent($comm_agent);
        $this->setCommIns($comm_ins);

        $agence_data = array();

        $agence_data['id_audit'] = $this->getIdAudit();
        $agence_data['date_crea'] = $this->getDateCrea();
        $agence_data['nom_login'] = $this->getNomLogin();
        $agence_data['id_ag_local'] = $this->getIdAgenceLocal();
        $agence_data['id_ag_distant'] = $this->getIdAgenceDistant();
        $agence_data['id_client_distant'] = $this->getIdClientDistant();
        $agence_data['id_compte_distant'] = $this->getIdCompteDistant();
        $agence_data['type_transaction'] = $this->getTypeTransaction();
        $agence_data['type_choix'] = $this->getTypeChoix();
        $agence_data['type_choix_libel'] = $this->getTypeChoixLibel();
        $agence_data['montant'] = $this->getMontant();
        $agence_data['code_devise_montant'] = $this->getCodeDeviseMontant();
        $agence_data['commission'] = $this->getCommission();
        $agence_data['code_devise_commission'] = $this->getCodeDeviseCommission();
        $agence_data['commission_ope_deplace'] = $this->getCommissionOpeDep();
        $agence_data['commission_agent'] = $this->getCommAgent();
        $agence_data['commission_inst'] = $this->getCommIns();
        $agence_data['post_message'] = $this->getPostMessage();
        $agence_data['success_flag'] = $this->getSuccessFlag();

        $sql = buildInsertQuery("adsys_audit_multi_agence", $agence_data);
        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__, $sql);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR, $this->getIdAudit());
    }

    /**
     * Sauvegarder l'ID historique en déplacé
     * 
     */
    public function updateRemoteHisId($id_his_distant) {

        // Set properties
        $this->setIdHisDistant($id_his_distant);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'id_his_distant' => $this->getIdHisDistant(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateTransacData($insertData);
    }

    /**
     * Sauvegarder l'ID ecriture en déplacé
     * 
     */
    public function updateRemoteEcritureId($id_ecriture_distant) {

        // Set properties
        $this->setIdEcritureDistant($id_ecriture_distant);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'id_ecriture_distant' => $this->getIdEcritureDistant(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateTransacData($insertData);
    }

    /**
     * Sauvegarder l'ID historique en local
     * 
     */
    public function updateLocalHisId($id_his_local) {

        // Set properties
        $this->setIdHisLocal($id_his_local);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'id_his_local' => $this->getIdHisLocal(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateTransacData($insertData);
    }

    /**
     * Sauvegarder l'ID ecriture en local
     * 
     */
    public function updateLocalEcritureId($id_ecriture_local) {

        // Set properties
        $this->setIdEcritureLocal($id_ecriture_local);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'id_ecriture_local' => $this->getIdEcritureLocal(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateTransacData($insertData);
    }

    /**
     * Sauvegarder le message d'erreur
     * 
     */
    public function saveErrorMessage($error_message) {

        // Set properties
        $this->setErrorMessage($error_message);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'error_message' => $this->getErrorMessage(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateTransacData($insertData);
    }

    /**
     * Sauvegarder le log SQL
     * 
     */
    public function saveSQLLog($sql_log) {

        // Set properties
        $this->setSQLLog($sql_log);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'sql_log' => $this->getSQLLog(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateTransacData($insertData);
    }

    /**
     * Valider la transaction en cours
     * 
     */
    public function updateTransacFlag($success_flag) {

        // Set properties
        $this->setSuccessFlag($success_flag);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'success_flag' => $this->getSuccessFlag(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateTransacData($insertData);
    }

    /**
     * Mattre à jour la transaction en cours
     * 
     */
    private function updateTransacData($insertData) {
        global $dbHandler;

        $db = $dbHandler->openConnection();

        $whereData = array(
            'id_audit' => $this->getIdAudit(),
            'id_ag_distant' => $this->getIdAgenceDistant(),
            'id_client_distant' => $this->getIdClientDistant()
        );

        $sql = buildUpdateQuery("adsys_audit_multi_agence", $insertData, $whereData);

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }
    
 
    /** Getters & Setters */
    public function getDbConn() {
        return $this->_db_conn;
    }

    public function setDbConn(&$value) {
        $this->_db_conn = $value;
    }

    public function getIdAudit() {
        return $this->_id_audit;
    }

    public function setIdAudit($value) {
        $this->_id_audit = (int) $value;
    }

    public function getDateCrea() {
        return $this->_date_crea;
    }

    public function setDateCrea($value) {
        $this->_date_crea = $value;
    }

    public function getDateMaj() {
        return $this->_date_maj;
    }

    public function setDateMaj($value) {
        $this->_date_maj = $value;
    }

    public function getIdAgenceLocal() {
        return $this->_id_ag_local;
    }

    public function setIdAgenceLocal($value) {
        $this->_id_ag_local = (int) $value;
    }

    public function getIdAgenceDistant() {
        return $this->_id_ag_distant;
    }

    public function setIdAgenceDistant($value) {
        $this->_id_ag_distant = (int) $value;
    }

    public function getNomLogin() {
        return $this->_nom_login;
    }

    public function setNomLogin($value) {
        $this->_nom_login = $value;
    }

    public function getIdClientDistant() {
        return $this->_id_client_distant;
    }

    public function setIdClientDistant($value) {
        $this->_id_client_distant = (int) $value;
    }

    public function getIdCompteDistant() {
        return $this->_id_compte_distant;
    }

    public function setIdCompteDistant($value) {
        $this->_id_compte_distant = (int) $value;
    }

    public function getTypeTransaction() {
        return trim($this->_type_transaction);
    }

    public function setTypeTransaction($value) {
        $this->_type_transaction = trim($value);
    }

    public function getTypeChoix() {
        return $this->_type_choix;
    }

    public function setTypeChoix($value) {
        $this->_type_choix = (int) $value;
    }

    public function getTypeChoixLibel() {
        return trim($this->_type_choix_libel);
    }

    public function setTypeChoixLibel($value) {
        $this->_type_choix_libel = trim($value);
    }

    public function getMontant() {
        return $this->_montant;
    }

    public function setMontant($value) {
        $this->_montant = (float) $value;
    }

    public function getCodeDeviseMontant() {
        return $this->_code_devise_montant;
    }

    public function setCodeDeviseMontant($value) {
        $this->_code_devise_montant = $value;
    }

    public function getCommission() {
        return $this->_commission;
    }

    public function setCommission($value) {
        $this->_commission = (float) $value;
    }

    public function getCodeDeviseCommission() {
        return $this->_code_devise_commission;
    }

    public function setCodeDeviseCommission($value) {
        $this->_code_devise_commission = $value;
    }
    // Rajouter  pour comm deplace
    public function setCommissionOpeDep($value) {
        $this->_commission_ope_deplace = (float) $value;
    }

    public function getCommissionOpeDep() {
        return $this->_commission_ope_deplace;
    }

    public function getPostMessage() {
        return $this->_post_message;
    }

    public function setPostMessage($value) {
        $this->_post_message = (string) $value;
    }

    public function getIdHisLocal() {
        return $this->_id_his_local;
    }

    public function setIdHisLocal($value) {
        $this->_id_his_local = (int) $value;
    }

    public function getIdEcritureLocal() {
        return $this->_id_ecriture_local;
    }

    public function setIdEcritureLocal($value) {
        $this->_id_ecriture_local = (int) $value;
    }

    public function getIdHisDistant() {
        return $this->_id_his_distant;
    }

    public function setIdHisDistant($value) {
        $this->_id_his_distant = (int) $value;
    }

    public function getIdEcritureDistant() {
        return $this->_id_ecriture_distant;
    }

    public function setIdEcritureDistant($value) {
        $this->_id_ecriture_distant = (int) $value;
    }

    public function getErrorMessage() {
        return $this->_error_message;
    }

    public function setErrorMessage($value) {
        $this->_error_message = $value;
    }

    public function getSQLLog() {
        return $this->_sql_log;
    }

    public function setSQLLog($value) {
        $this->_sql_log = $value;
    }

    public function getSuccessFlag() {
        return $this->_success_flag;
    }

    public function setSuccessFlag($value) {
        $this->_success_flag = $value;
    }
    /**
     * @return mixed
     */
    public function getCommAgent()
    {
        return $this->_comm_agent;
    }

    /**
     * @param mixed $comm_agent
     */
    public function setCommAgent($comm_agent)
    {
        $this->_comm_agent = $comm_agent;
    }

    /**
     * @return mixed
     */
    public function getCommIns()
    {
        return $this->_comm_ins;
    }

    /**
     * @param mixed $comm_ins
     */
    public function setCommIns($comm_ins)
    {
        $this->_comm_ins = $comm_ins;
    }


}