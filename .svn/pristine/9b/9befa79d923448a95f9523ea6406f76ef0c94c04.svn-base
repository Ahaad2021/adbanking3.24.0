<?php

/**
 * Description de la classe TireurBenef
 *
 * @author danilo
 */
require_once 'ad_ma/app/models/BaseModel.php';

class TireurBenef extends BaseModel {

    public function __construct(&$dbc, $id_agence = NULL) {
        parent::__construct($dbc, $id_agence);
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Renvoie le nombre de tireur / beneficiaire
     * 
     * @param string $Where
     * @param string $type
     * 
     * @return int
     */
    public function countTireurBenef($Where, $type) {

        $WhereClause = "";

        foreach ($Where as $key => $value) {
            if ($value != "") {
                $WhereClause .= " upper($key) like '%' || upper('$value') || '%' AND";
            }
        }

        switch ($type) {
            case 'b':
                $WhereClause .= " beneficiaire = 't' AND ";
                break;
            case 't':
                $WhereClause .= " tireur = 't' AND ";
                break;
        }

        if ($WhereClause == "")
            $sql = "SELECT count(*) FROM tireur_benef a, adsys_pays b WHERE a.id_ag = " . $this->getIdAgence() . "AND a.id_ag = b.id_ag AND a.pays = b.id_pays;";
        else {
            $sql = "SELECT count(*) FROM tireur_benef a, adsys_pays b WHERE" . $WhereClause . " a.pays=b.id_pays AND a.id_ag = " . $this->getIdAgence() . " AND a.id_ag = b.id_ag;";
        }

        $result = $this->getDbConn()->prepareFetchColumn($sql);

        if (!$result) {
            return NULL;
        }

        return $result;
    }

    /**
     * Fonction qui recherche dans la tables tireurs_beneficiaires selon certains critères
     * 
     * @param Array $Where Un tableau associatif avec les critères de recherche
     * @param char $type 'b' si recherche des bénéficaires, 't' sie recherche des tireurs, recherche générale si autre
     */
    public function getMatchedTireurBenef($Where, $type) {
        $WhereClause = "";

        foreach ($Where as $key => $value) {
            if ($value != "") {
                $WhereClause .= " upper($key) like '%' || upper('$value') || '%' AND";
            }
        }

        switch ($type) {
            case 'b':
                $WhereClause .= " beneficiaire = 't' AND ";
                break;
            case 't':
                $WhereClause .= " tireur = 't' AND ";
                break;
        }

        if ($WhereClause == "")
            $sql = "SELECT a.*, b.libel_pays FROM tireur_benef a, adsys_pays b WHERE a.id_ag = " . $this->getIdAgence() . " AND a.id_ag = b.id_ag AND a.pays = b.id_pays;";
        else {
            $sql = "SELECT a.*, b.libel_pays FROM tireur_benef a, adsys_pays b WHERE" . $WhereClause . " a.pays=b.id_pays AND a.id_ag = " . $this->getIdAgence() . " AND a.id_ag = b.id_ag;";
        }

        $DATAS = $this->getDbConn()->prepareFetchAll($sql);

        if ($DATAS === FALSE || count($DATAS) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $DATAS;
    }

    /**
     * Cette fonction renvoie toutes les informations contenues dans la table tireur_benef pour un identifiant donné.
     * @param int $id : identifiant du tireur/bénéficiaire
     * 
     * @return $DATAS : tableau contenant tous les champs du tireur/bénéficiaire choisi.
     */
    public function getTireurBenefDatas($id) {

        $sql = "SELECT * FROM tireur_benef WHERE id_ag = :id_agence AND id = :id;";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id' => $id);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

        if ($result === FALSE || count($result) == 0) {
            return NULL;
        }

        return $result;
    }

    /**
     * Renvoie le prochain ID de client libre dans la base
     * 
     */
    public function getNewTireurBenefID() {

        $sql = "SELECT nextval('tireur_benef_seq');";

        $id_pers_ext = $this->getDbConn()->prepareFetchColumn($sql);

        if (!isset($id_pers_ext)) {
            return NULL;
        }

        return $id_pers_ext;
    }

    /**
     * Cette fonction ajoute un nouveau tireur/bénéficiaire dans la table tireur_benef.
     * 
     * @param array $data : toutes les champs de la table.
     * 
     * @return int $DATA['id']: Si l'insertion s'est bien passée, renvoie le numéro d'identifiant du tireur/bénéficiaire inséré.
     */
    public function insereTireurBenef($DATA) {

        $DATA['id'] = self::getNewTireurBenefID();
        $DATA['id_ag'] = $this->getIdAgence();

        $sql = buildInsertQuery("tireur_benef", $DATA);

        $result = $this->getDbConn()->execute($sql);
        
        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $DATA['id'];
    }

    /**
     * Cette fonction met à jour un tireur/bénéficiaire dans la table tireur_benef
     * 
     * @param int $id : identifiant du tireur/bénéficiaire
     * @param array $Fields : les champs contenant les modifications.
     * 
     * @return l'objet erreur
     */
    public function updateTireurBenef($id, $Fields) {

        $Where["id"] = $id;
        $Where["id_ag"] = $this->getIdAgence();
        $sql = buildUpdateQuery("tireur_benef", $Fields, $Where);

        $result = $this->getDbConn()->execute($sql);
        
        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     * Cette fonction met à true le champ "Bénéficiaire" d'un tireur/bénéficiaire dans la table tireur_beneficiaire
     * 
     * @param int $id : identifiant du tireur/bénéficiaire
     * 
     * @return l'objet erreur
     */
    public function setBeneficiaire($id) {

        $Where["id"] = $id;
        $Where["id_ag"] = $this->getIdAgence();
        $Fields['beneficiaire'] = 't';

        $sql = buildUpdateQuery("tireur_benef", $Fields, $Where);

        $result = $this->getDbConn()->execute($sql);
        
        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     * Cette fonction met à true le champ "Tireur" d'un tireur/bénéficiaire dans la table tireur_beneficiaire
     * @param int $id : identifiant du tireur/bénéficiaire
     * @return l'objet erreur
     */
    public function setTireur($id) {
        $Where["id"] = $id;
        $Where["id_ag"] = $this->getIdAgence();
        $Fields['tireur'] = 't';
        $sql = buildUpdateQuery("tireur_benef", $Fields, $Where);
        $result = $this->getDbConn()->execute($sql);
        
        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return new ErrorObj(NO_ERR);
    }

}
