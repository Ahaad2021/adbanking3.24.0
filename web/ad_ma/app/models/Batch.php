<?php

/**
 * Description de la classe Batch
 *
 * @author danilo
 */
class Batch {

    /** Properties */
    private $_db_conn;
    private $_id_batch;
    private $_date_crea;
    private $_date_maj;
    private $_id_ag;
    private $_nom_login;
    private $_db_backup_path;
    private $_batch_rapport_pdf_path;
    //private $_batch_rapport_pdf_binary;
    private $_error_message;
    private $_sql_log;
    private $_success_flag;

    public function __construct() {
        
    }

    public function __destruct() {
        
    }

    /**
     * Renvoie le prochain ID batch libre dans la base
     * 
     */
    public function getNewBatchID() {
        global $db;
        global $dbHandler;

        $db = $dbHandler->openConnection();

        $sql = "SELECT nextval('adsys_batch_multi_agence_id_batch_seq');";

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(true);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $id_batch = $result->fetchrow();

        $dbHandler->closeConnection(true);

        return $id_batch[0];
    }

    /**
     * Sauvegarder le batch en cours
     * 
     */
    public function insertBatchData($nom_login, $id_ag) {

        global $dbHandler;

        $db = $dbHandler->openConnection();

        // Set properties
        $this->setIdBatch(self::getNewBatchID());
        $this->setDateCrea(date("r"));
        $this->setNomLogin($nom_login);
        $this->setIdAgence($id_ag);
        $this->setSuccessFlag('f');

        $batch_data = array();

        $batch_data['id_batch'] = $this->getIdBatch();
        $batch_data['date_crea'] = $this->getDateCrea();
        $batch_data['nom_login'] = $this->getNomLogin();
        $batch_data['id_ag'] = $this->getIdAgence();
        $batch_data['success_flag'] = $this->getSuccessFlag();

        $sql = buildInsertQuery("adsys_batch_multi_agence", $batch_data);

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__, $sql);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR, $this->getIdBatch());
    }

    /**
     * Sauvegarder le chemin du backup de la BDD
     * 
     */
    public function updateDbBackupPath($db_backup_path) {

        // Set properties
        $this->setDbBackupPath($db_backup_path);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'db_backup_path' => $this->getDbBackupPath(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateBatchData($insertData);
    }

    /**
     * Sauvegarder le chemin du pdf rapport batch
     * 
     */
    public function updateBatchRapportPdfPath($batch_rapport_pdf_path) {

        // Set properties
        $this->setBatchRapportPdfPath($batch_rapport_pdf_path);
        //$this->setBatchRapportPdfBinary($batch_rapport_pdf_path);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'batch_rapport_pdf_path' => $this->getBatchRapportPdfPath(),
            //'batch_rapport_pdf_binary' => $this->getBatchRapportPdfBinary(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateBatchData($insertData);
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

        return $this->updateBatchData($insertData);
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

        return $this->updateBatchData($insertData);
    }

    /**
     * Valider le batch en cours
     * 
     */
    public function updateBatchFlag($success_flag) {

        // Set properties
        $this->setSuccessFlag($success_flag);
        $this->setDateMaj(date("r"));

        $insertData = array(
            'success_flag' => $this->getSuccessFlag(),
            'date_maj' => $this->getDateMaj()
        );

        return $this->updateBatchData($insertData);
    }

    /**
     * Mettre à jour le batch en cours
     * 
     */
    private function updateBatchData($insertData) {
        global $dbHandler;

        $db = $dbHandler->openConnection();

        $whereData = array(
            'id_batch' => $this->getIdBatch(),
            'id_ag' => $this->getIdAgence()
        );

        $sql = buildUpdateQuery("adsys_batch_multi_agence", $insertData, $whereData);

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__, $sql);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Renvoie une liste de traitement de nuit pour un intervalle de dates données.
     * La recherche se fait sur base de la date création.
     *
     * @param date $date_debut Date de début de recherche
     * @param date $date_fin Date de fin de recherche
     * @return array Liste des archives batch recherchées
     */
    public static function getListBatchArchive($date_debut, $date_fin) {

        $result = executeDirectQuery("SELECT abma.*, ama.app_db_description FROM adsys_batch_multi_agence abma, adsys_multi_agence ama WHERE (abma.id_ag=ama.id_agc) AND date(abma.date_crea) BETWEEN '$date_debut' AND '$date_fin' ORDER BY abma.date_crea DESC");

        if ($result->errCode == NO_ERR) {
            return($result->param);
        } else {
            return(NULL);
        }
    }

    /**
     * Transfert le fichier crée en local sur le serveur distant
     *
     * @param string $local_host_ip
     * @param string $remote_host_ip
     * @param string $local_path
     * @param string $remote_path
     * @param string $local_ssh_login
     * @param string $local_ssh_password
     * @param string $remote_ssh_login
     * 
     * @return boolean true|false
     */
    public static function transferBatchFile($local_host_ip, $remote_host_ip, $local_path, $remote_path, $local_ssh_login, $local_ssh_password, $remote_ssh_login = 'batchma') {

        // Include SSH library
        require_once('ad_ma/batch/phpseclib0.3.5/Net/SSH2.php');

        // Connect to remote server via SSH
        $SSH = new Net_SSH2($local_host_ip);
        if ($SSH->login($local_ssh_login, $local_ssh_password)) {

            // Suppress stderr from output
            //$SSH->enableQuietMode();
            
            // Secure copy db backup file to remote server
            echo $SSH->exec('scp ' . $local_path . ' ' . $remote_ssh_login . '@' . $remote_host_ip . ':' . $remote_path);

            // Exit user 
            echo $SSH->exec('exit');
            
            if(count($SSH->getErrors()) == 0) {
                return TRUE;
            } else {
                affiche(_("Transfer error scp : ").$SSH->getLastError());
                
                //echo '<br />local_host_ip='.$local_host_ip.'<br />remote_host_ip='.$remote_host_ip.'<br />local_path='.$local_path.'<br />remote_path='.$remote_path.'<br />local_ssh_login='.$local_ssh_login.'<br />local_ssh_password='.$local_ssh_password.'<br />remote_ssh_login='.$remote_ssh_login.'<br />';

                return FALSE;
            }
        } else {
            affiche(_("Transfer error login : ").$SSH->getLastError());
            
            //echo '<br />local_host_ip='.$local_host_ip.'<br />remote_host_ip='.$remote_host_ip.'<br />local_path='.$local_path.'<br />remote_path='.$remote_path.'<br />local_ssh_login='.$local_ssh_login.'<br />local_ssh_password='.$local_ssh_password.'<br />remote_ssh_login='.$remote_ssh_login.'<br />';
            
            return FALSE;
        }
    }

    /** Getters & Setters */
    public function getDbConn() {
        return $this->_db_conn;
    }

    public function setDbConn(&$value) {
        $this->_db_conn = $value;
    }

    public function getIdBatch() {
        return $this->_id_batch;
    }

    public function setIdBatch($value) {
        $this->_id_batch = (int) $value;
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

    public function getIdAgence() {
        return $this->_id_ag;
    }

    public function setIdAgence($value) {
        $this->_id_ag = (int) $value;
    }

    public function getNomLogin() {
        return $this->_nom_login;
    }

    public function setNomLogin($value) {
        $this->_nom_login = $value;
    }

    public function getDbBackupPath() {
        return $this->_db_backup_path;
    }

    public function setDbBackupPath($value) {
        $this->_db_backup_path = (string) $value;
    }

    public function getBatchRapportPdfPath() {
        return $this->_batch_rapport_pdf_path;
    }

    public function setBatchRapportPdfPath($value) {
        $this->_batch_rapport_pdf_path = (string) $value;
    }

    /*
      public function getBatchRapportPdfBinary() {
      return $this->_batch_rapport_pdf_binary;
      }

      public function setBatchRapportPdfBinary($value) {

      // Read in a binary file
      $pdf_data = file_get_contents($value);

      $this->_batch_rapport_pdf_binary = pg_escape_bytea(($pdf_data));
      }
     */

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

}

?>
