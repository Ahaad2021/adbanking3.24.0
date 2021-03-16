<?php

/**
 * Description de la classe Agence
 *
 * @author danilo
 */

require_once 'ad_ma/app/models/BaseModel.php';

class Agence extends BaseModel {

    public function __construct(&$dbc, $id_agence=NULL) {        
        parent::__construct($dbc, $id_agence); 
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Renvoie le numéro de l'agence
     * 
     * @return int Tableau associatif avec les comptes trouvés, indicé par les identifiants du compte.
     */
    public function getNumAgc() {
        $sql = "SELECT MIN(id_ag) AS id_ag FROM ad_agc;";

        $result = $this->getDbConn()->prepareFetchColumn($sql);

        if ($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Cette fonction renvoie toutes les informations relatives à l'agence
     * 
     * @param null|integer $id_agence
     * 
     * @return array Tableau associatif avec les comptes trouvés, indicé par les identifiants du compte.
     */
    public function getAgenceDatas($id_agence = NULL) {

        $sql = "SELECT * FROM ad_agc";
        if ($id_agence != NULL) {
            $sql .= " WHERE id_ag = $id_agence";
        }

        $result = $this->getDbConn()->prepareFetchRow($sql);

        if ($result===FALSE || count($result) == 0) {
            return NULL;
        }

        return $result;
    }

    /**
     * Renvoie le libellé de l'agence
     * @param $id_agence
     * @return string
     */
    public function getAgenceName($id_agence) {
        $name = '';
        $agenceData = $this->getAgenceDatas($id_agence);

        if (is_array($agenceData)) {
            $name = $agenceData['libel_ag'];
        }
        return trim($name);
    }

    /**
     * Fabrique un numéro de compteclient à partir du id client et éventullement
     *
     * @param integer $id_client ID du client titulaire
     * @param integer $id_agence
     * 
     * @return string Numéro de compte-client
     */
    public function makeNumClient($id_client, $id_agence) {

        $agenceData = self::getAgenceDatas($id_agence);

        $NumCompletClient = $id_client;
        if (is_array($agenceData) && $agenceData["type_numerotation_compte"] == 4) {
            // Crée un numéro de compte au format AAB-CCCCCC à partir de l'id agence(AA), du numéro de bureau(B) et de l'ID client (C)
            $numAntenne = $agenceData['code_antenne'];
            if ($numAntenne != '0' && $numAntenne != NULL) {
                $NumCompletClient = $numAntenne . $id_agence;
            } else {
                $NumCompletClient = $id_agence;
            }
            $NumCompletClient .= sprintf("-%06d", $id_client);
        } else {
            $NumCompletClient = sprintf("%06d", $id_client);
        }

        return $NumCompletClient;
    }

    /**
     * Renvoie la date de la dernière exécution du batch
     * 
     * @param int $id_agence
     * 
     * @return date
     */
    public function getLastBatch($id_agence) {

        $sql = "SELECT last_batch FROM ad_agc WHERE (id_ag=$id_agence)";
        
        $last_batch = $this->getDbConn()->prepareFetchColumn($sql);

        if ($last_batch===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $last_batch;
    }

    /**
     * Recupere le parametre 'pct_comm_change_local' qui indique ou devrait etre percues les commissions pour les transactions en multidevises, multiagences.
     *
     * @see agence.php -> getWherePerceptionCommissionsMultiAgence()
     * @return boolean
     */
    public function getWherePerceptionCommissionsMultiAgence()
    {
        global $error;        
        
        try {
            $agenceDatas = $this->getAgenceDatas($this->getIdAgence());          
            
            if (array_key_exists('pct_comm_change_local', $agenceDatas)) {
                $wherePerceptionCommission = $agenceDatas['pct_comm_change_local'];
                
                if ($wherePerceptionCommission == 't') {
                    $wherePerceptionCommission = true;
                } else {
                    $wherePerceptionCommission = false;
                }
                return $wherePerceptionCommission;
            } else {
                throw new Exception("Le champ pct_comm_change_local n'existe pas.");
            }
        } catch (Exception $e) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, $e->getMessage());
        }
    }

}
