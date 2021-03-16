<?php

/**
 * Description de la classe Historisation
 *
 * @author danilo
 */
class Historisation {

    /** Properties */
    private $_db_conn;
    private $_id_dcr_his;
    private $_id_doss;
    private $_mod_type;
    private $_id_ech;
    private $_ech_date;
    private $_reech_duree;
    private $_approb_date;
    private $_approb_flag;
    private $_date_crea;
    private $_date_modif;
    private $_global_nom_login;
    private $_id_ag;
    private $_id_etr_his;
    private $_mnt_cap;
    private $_mnt_int;
    private $_mnt_gar;
    private $_mnt_reech;
    private $_solde_cap;
    private $_solde_int;
    private $_solde_gar;
    private $_solde_pen;
    private $_error_message;
    private $_sql_log;
    private $_success_flag;
    private $_differe_ech;

    /**
     * @return mixed
     */
    public function getDiffereEch()
    {
        return $this->_differe_ech;
    }

    /**
     * @param mixed $differe_ech
     */
    public function setDiffereEch($differe_ech)
    {
        $this->_differe_ech = $differe_ech;
    }

    const MOD_TYPE_MODIF_DATE = 1;
    const MOD_TYPE_RACCOURCI = 2;
    const MOD_TYPE_REECH = 3;
    
    public function __construct() {
        
    }

    public function __destruct() {
        
    }

    /**
     * Renvoie le prochain ID dossier historisation libre dans la base
     * 
     */
    public function getNewDcrHisID() {
        global $db;
        global $dbHandler;

        $db = $dbHandler->openConnection();

        $sql = "SELECT nextval('ad_dcr_his_id_dcr_his_seq');";

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(true);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $id_dcr_his = $result->fetchrow();

        $dbHandler->closeConnection(true);

        return $id_dcr_his[0];
    }

    /**
     * Renvoie le prochain ID échéance historisation libre dans la base
     * 
     */
    public function getNewEtrHisID() {
        global $db;
        global $dbHandler;

        $db = $dbHandler->openConnection();

        $sql = "SELECT nextval('ad_etr_his_id_etr_his_seq');";

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(true);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $id_etr_his = $result->fetchrow();

        $dbHandler->closeConnection(true);

        return $id_etr_his[0];
    }

    /**
     * Sauvegarder les détails d'un dossier de crédit à modifier/rééchelonner
     * 
     */
    public function insertDossierHis($id_doss, $mod_type, $id_ech=0, $ech_date_dem=null, $reech_duree=0, $differe_ech=0) {

        global $dbHandler, $global_id_agence, $global_nom_login;

        $db = $dbHandler->openConnection();

        // Set properties
        $this->setIdDcrHis(self::getNewDcrHisID());
        $this->setIdDoss($id_doss);
        $this->setModType($mod_type);
        if($id_ech > 0) {
          $this->setIdEch($id_ech);
        }
        if($ech_date_dem != null) {
          $this->setEchDate($ech_date_dem);
        }
        if($reech_duree > 0) {
          $this->setReechDuree($reech_duree);
        }
        if($differe_ech > 0) {
            $this->setDiffereEch($differe_ech);
        }
        $this->setDateCrea(date("r"));
        $this->setApprobFlag('f');
        $this->setNomLogin($global_nom_login);
        $this->setIdAgence($global_id_agence);

        $dcr_his_data = array();

        $dcr_his_data['id_dcr_his'] = $this->getIdDcrHis();
        $dcr_his_data['id_doss'] = $this->getIdDoss();
        $dcr_his_data['mod_type'] = $this->getModType();
        if($id_ech > 0) {
          $dcr_his_data['id_ech'] = $this->getIdEch();
        }
        if($ech_date_dem != null) {
          $dcr_his_data['ech_date_dem'] = $this->getEchDate();
        }
        if($reech_duree > 0) {
          $dcr_his_data['reech_duree'] = $this->getReechDuree();
        }
        if($differe_ech > 0) {
            $dcr_his_data['diff_ech'] = $this->getDiffereEch();
        }
        $dcr_his_data['approb_flag'] = $this->getApprobFlag();
        $dcr_his_data['date_crea'] = $this->getDateCrea();
        $dcr_his_data['nom_login'] = $this->getNomLogin();
        $dcr_his_data['id_ag'] = $this->getIdAgence();

        $sql = buildInsertQuery("ad_dcr_his", $dcr_his_data);

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__, $sql);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR, $this->getIdDcrHis());
    }
    
    /**
     * Supprimer un dossier de crédit
     * 
     */
    public function deleteDossierHis($id_doss, $mod_type, $approb_flag) {
        
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();
        
        $dcr_his_data = array();

        $dcr_his_data['id_doss'] = $id_doss;
        $dcr_his_data['mod_type'] = $mod_type;
        $dcr_his_data['approb_flag'] = $approb_flag;
        $dcr_his_data['id_ag'] = $global_id_agence;
        
        $sql = buildDeleteQuery("ad_dcr_his", $dcr_his_data);
        
        $result = $db->query($sql);
        if (DB :: isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR);
    }

    /**
     * Sauvegarder les détails des échéanciers théoriques du rééchelonnement d'un dossier de crédit
     * 
     */
    public function insertEtrHis($id_doss, $id_ech, $ech_date, $mnt_cap, $mnt_int, $mnt_gar, $mnt_reech, $solde_cap, $solde_int, $solde_gar, $solde_pen, $mod_type = 2) {

        global $dbHandler, $global_id_agence, $global_nom_login;

        $db = $dbHandler->openConnection();

        // Set properties
        $this->setIdEtrHis(self::getNewEtrHisID());
        $this->setIdDcrHis(self::getIdDcrHisByParam($id_doss, $mod_type, 'f'));
        $this->setIdDoss($id_doss);
        $this->setIdEch($id_ech);
        $this->setEchDate($ech_date);
        $this->setMntCap($mnt_cap);
        $this->setMntInt($mnt_int);
        $this->setMntGar($mnt_gar);
        $this->setMntReech($mnt_reech);
        $this->setSoldeCap($solde_cap);
        $this->setSoldeInt($solde_int);
        $this->setSoldeGar($solde_gar);
        $this->setSoldePen($solde_pen);
        $this->setNomLogin($global_nom_login);
        $this->setIdAgence($global_id_agence);

        $etr_his_data = array();

        $etr_his_data['id_etr_his'] = $this->getIdEtrHis();
        $etr_his_data['id_dcr_his'] = $this->getIdDcrHis();
        $etr_his_data['id_doss'] = $this->getIdDoss();
        $etr_his_data['id_ech'] = $this->getIdEch();
        $etr_his_data['ech_date'] = $this->getEchDate();
        $etr_his_data['mnt_cap'] = $this->getMntCap();
        $etr_his_data['mnt_int'] = $this->getMntInt();
        $etr_his_data['mnt_gar'] = $this->getMntGar();
        $etr_his_data['mnt_reech'] = $this->getMntReech();
        $etr_his_data['solde_cap'] = $this->getSoldeCap();
        $etr_his_data['solde_int'] = $this->getSoldeInt();
        $etr_his_data['solde_gar'] = $this->getSoldeGar();
        $etr_his_data['solde_pen'] = $this->getSoldePen();
        $etr_his_data['nom_login'] = $this->getNomLogin();
        $etr_his_data['id_ag'] = $this->getIdAgence();

        $sql = buildInsertQuery("ad_etr_his", $etr_his_data);

        $result = $db->query($sql);
        if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__, $sql);
        }
        $dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR, $this->getIdEtrHis());
    }

    /**
     * Renvoie le dernier id_dcr_his
     * 
     * @return int id_dcr_his
     */
    public static function getIdDcrHisByParam($id_doss, $mod_type = 1, $approb_flag = 't') {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        $sql = "SELECT id_dcr_his FROM ad_dcr_his WHERE id_doss=$id_doss AND id_ag=$global_id_agence AND mod_type='$mod_type' AND approb_flag='$approb_flag' ORDER BY id_dcr_his DESC LIMIT 1";

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
    
        $dbHandler->closeConnection(true);
    
        if ($result->numRows() == 0) {
            return NULL;
        }    
        
        $ad_dcr_his = $result->fetchrow();       
        return $ad_dcr_his[0];
    }
    
    /**
     * Renvoie un ou une liste de dossier de crédit déjà modifié / rééchelonné
     * 
     * @return array Tableau associatif avec les dossiers trouvés.
     */
    public static function getListDossierHis($id_doss = null, $mod_type = 1, $approb_flag = 't') {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();
        $sql = "SELECT * FROM ad_dcr_his WHERE id_ag=$global_id_agence AND mod_type='$mod_type' AND approb_flag='$approb_flag' ";

        if ($id_doss != null) {
            $sql .= " AND id_doss=$id_doss ";
        }

        $sql .= " ORDER BY id_doss ASC";

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $dbHandler->closeConnection(true);

        if ($result->numRows() == 0) {
            return NULL;
        }

        $tmp_arr = array();
        while ($doss = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
            $tmp_arr[$doss["id_doss"]] = $doss;
        }

        return $tmp_arr;
    }

    /**
     * Renvoie un ou une liste d'échéances théoriques pour un dossier de crédit
     * 
     * @return array Tableau associatif avec les échéances trouvés.
     */
    public static function getListEtrHis($id_doss, $id_dcr_his = null, $id_ech = null) {
        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();
        $sql = "SELECT * FROM ad_etr_his WHERE id_ag=$global_id_agence AND id_doss=$id_doss ";

        if ($id_dcr_his != null) {
            $sql .= " AND id_dcr_his=$id_dcr_his ";
        }

        if ($id_ech != null) {
            $sql .= " AND id_ech=$id_ech ";
        }

        $sql .= " ORDER BY id_doss ASC, id_ech ASC";

        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $dbHandler->closeConnection(true);

        if ($result->numRows() == 0) {
            return NULL;
        }

        $tmp_arr = array();
        while ($etr = $result->fetchrow(DB_FETCHMODE_ASSOC)) {

            $tmp_arr[$etr["id_doss"]] = $etr;
        }

        return $tmp_arr;
    }

    /**
     * Mettre à jour une demande de modification/rééchelonnement d'un dossier de crédit
     * 
     */
    public function updateDossierHis($id_doss, $mod_type, $approb_flag='t', $echeance_index=NULL) {

        global $dbHandler, $global_id_agence;

        $db = $dbHandler->openConnection();

        // Set properties
        $this->setApprobDate(date('r'));
        $this->setApprobFlag($approb_flag);

        $updateData = array(
            'approb_date' => $this->getApprobDate(),
            'approb_flag' => $this->getApprobFlag(),
            'date_modif' => $this->getApprobDate()
        );

        $whereData = array(
            'id_doss' => $id_doss,
            'mod_type' => $mod_type,
            'approb_flag' => 'f',
            'id_ag' => $global_id_agence
        );
        
        if($echeance_index != NULL) {
            $whereData['id_ech'] = $echeance_index;
        }

        $sql = buildUpdateQuery("ad_dcr_his", $updateData, $whereData);

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

    public function getIdDcrHis() {
        return $this->_id_dcr_his;
    }

    public function setIdDcrHis($value) {
        $this->_id_dcr_his = $value;
    }

    public function getIdDoss() {
        return $this->_id_doss;
    }

    public function setIdDoss($value) {
        $this->_id_doss = $value;
    }

    public function getModType() {
        return $this->_mod_type;
    }

    public function setModType($value) {
        $this->_mod_type = $value;
    }

    public function getIdEch() {
        return $this->_id_ech;
    }

    public function setIdEch($value) {
        $this->_id_ech = $value;
    }

    public function getEchDate() {
        return $this->_ech_date;
    }

    public function setEchDate($value) {
        $this->_ech_date = $value;
    }

    public function getReechDuree() {
        return $this->_reech_duree;
    }

    public function setReechDuree($value) {
        $this->_reech_duree = $value;
    }

    public function getApprobDate() {
        return $this->_approb_date;
    }

    public function setApprobDate($value) {
        $this->_approb_date = $value;
    }

    public function getApprobFlag() {
        return $this->_approb_flag;
    }

    public function setApprobFlag($value) {
        $this->_approb_flag = $value;
    }

    public function getDateCrea() {
        return $this->_date_crea;
    }

    public function setDateCrea($value) {
        $this->_date_crea = $value;
    }

    public function getDateModif() {
        return $this->_date_modif;
    }

    public function setDateModif($value) {
        $this->_date_modif = $value;
    }

    public function getNomLogin() {
        return $this->_global_nom_login;
    }

    public function setNomLogin($value) {
        $this->_global_nom_login = $value;
    }

    public function getIdAgence() {
        return $this->_id_ag;
    }

    public function setIdAgence($value) {
        $this->_id_ag = (int) $value;
    }

    public function getIdEtrHis() {
        return $this->_id_etr_his;
    }

    public function setIdEtrHis($value) {
        $this->_id_etr_his = $value;
    }

    public function getMntCap() {
        return $this->_mnt_cap;
    }

    public function setMntCap($value) {
        $this->_mnt_cap = $value;
    }

    public function getMntInt() {
        return $this->_mnt_int;
    }

    public function setMntInt($value) {
        $this->_mnt_int = $value;
    }

    public function getMntGar() {
        return $this->_mnt_gar;
    }

    public function setMntGar($value) {
        $this->_mnt_gar = $value;
    }

    public function getMntReech() {
        return $this->_mnt_reech;
    }

    public function setMntReech($value) {
        $this->_mnt_reech = $value;
    }

    public function getSoldeCap() {
        return $this->_solde_cap;
    }

    public function setSoldeCap($value) {
        $this->_solde_cap = $value;
    }

    public function getSoldeInt() {
        return $this->_solde_int;
    }

    public function setSoldeInt($value) {
        $this->_solde_int = $value;
    }

    public function getSoldeGar() {
        return $this->_solde_gar;
    }

    public function setSoldeGar($value) {
        $this->_solde_gar = $value;
    }

    public function getSoldePen() {
        return $this->_solde_pen;
    }

    public function setSoldePen($value) {
        $this->_solde_pen = $value;
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

}
