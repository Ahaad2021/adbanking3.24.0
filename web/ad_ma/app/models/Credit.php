<?php

/**
 * Description de la classe Credit
 *
 * @author danilo
 */

require_once 'ad_ma/app/models/BaseModel.php';

class Credit extends BaseModel {

    /** Properties */
    private $_id_dossier;

    public function __construct(&$dbc, $id_agence=NULL) {        
        parent::__construct($dbc, $id_agence); 
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Renvoie le num de produit référençant les comptes de crédit
     * 
     * @return integer $id_prod
     */
    public function getCreditProductID() {

        // Récupération du n° de produit d'épargne utilisé par l'agence pour les comptes de crédit
        $sql = "SELECT id_prod_cpte_credit FROM ad_agc WHERE id_ag = ".$this->getIdAgence().";"; // Recherche l'état du client

        $id_prod = $this->getDbConn()->prepareFetchColumn($sql);
        
        if ($id_prod===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $sql);
        }

        return $id_prod;
    }

    /**
     * Renvoie, pour un client,les infos des dossiers de crédit dont les états correspondent aux états spécifiés dans la condition whereCl
     * 
     * @param int $id_client l'identifiant du client titulaire des dossiers de crédits
     * @param text $whereCl la conditions spécifiant les états des dossiers à chercher
     * 
     * @return array tableau de la forme (index => infos compte) : les index sont les identifiants des dossiers
     */
    public function getIdDossier($id_client, $whereCl) {

        $sql = "SELECT id_doss, id_client, id_prod, date_dem, mnt_dem, obj_dem, detail_obj_dem, etat, date_etat, motif, id_agent_gest, delai_grac, differe_jours, prelev_auto, duree_mois, nouv_duree_mois, terme, gar_num, gar_tot, gar_mat, gar_num_encours, cpt_gar_encours, num_cre, assurances_cre, cpt_liaison, cre_id_cpte, cre_etat, cre_date_etat, cre_date_approb, cre_date_debloc, cre_nbre_reech, cre_mnt_octr, details_motif, suspension_pen, perte_capital, cre_retard_etat_max, cre_retard_etat_max_jour, differe_ech, id_dcr_grp_sol, gs_cat, prelev_commission, cpt_prelev_frais, id_ag, cre_prelev_frais_doss, prov_mnt, prov_date, prov_is_calcul, cre_mnt_deb, doss_repris, cre_cpt_att_deb, date_creation, date_modif, is_ligne_credit, deboursement_autorisee_lcr, motif_changement_authorisation_lcr, date_changement_authorisation_lcr, duree_nettoyage_lcr, remb_auto_lcr, tx_interet_lcr, taux_frais_lcr, taux_min_frais_lcr, taux_max_frais_lcr, ordre_remb_lcr, mnt_assurance, mnt_commission, mnt_frais_doss, detail_obj_dem_bis, id_bailleur, libel as libelle, periodicite FROM get_ad_dcr_ext_credit(null, :id_client, null, null, :id_agence) WHERE id_client = :id_client $whereCl ORDER BY id_doss;";
        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);
        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $retour = array();
        foreach ($results as $key => $row) {
            $retour[$row["id_doss"]] = $row;
        }

        return $retour;
    }
    
    /**
     * Construit un tableau associatif avec toutes les données des produits recherchés
     *
     * @param mixed $whereCond Clause SQL choisissant les produits
     * 
     * @return void tableau de tableaux associatifs des champs de la table adsys_produit ou NULL si aucun dossier de crédit correspondant
     */
    public function getProdInfo($whereCond, $id_doss = NULL) {

        global $global_id_agence;

        if ($id_doss != NULL && $id_doss > 0) {
            $sql = "SELECT * FROM get_ad_dcr_ext_credit(:id_dossier, null, null, null, :id_agence)";
            $param_arr = array(':id_dossier' => $id_doss, ':id_agence' => $this->getIdAgence());
        } else {
            $sql = "SELECT * FROM adsys_produit_credit";
        }

        if ($whereCond == null || $whereCond == "") {
            $sql .= " WHERE ";
        } else {
            $sql .= " $whereCond AND ";
        }

        $sql .= " id_ag=:id_agence_ ORDER BY id ASC";

        $param_arr[":id_agence_"] = $this->getIdAgence();
        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        $Produit = array();
        foreach ($results as $key => $row) {
            array_push($Produit, $row);
        }

        return $Produit;
    }
    
    /**
    * Renvoi les informations sur le dossier de crédit
    * 
    * @param int $id_dossier L'identifiant du dossier de crédit
    * 
    * @return tableau associatif si OK, die si erreur dans la BD
    */
    public function getDossierCrdtInfo($id_dossier) {
        
        $sql = "SELECT ad_dcr.* ,adsys_produit_credit.devise, adsys_produit_credit.max_jours_compt_penalite";
        $sql .= " FROM ad_dcr, adsys_produit_credit WHERE id_doss = :id_dossier AND id_prod = id ";
        $sql .= " AND ad_dcr.id_ag = adsys_produit_credit.id_ag and ad_dcr.id_ag = :id_agence ";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_dossier' => $id_dossier);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

        if ($result === FALSE || count($result) == 0) {
            return NULL;
        }

        return $result;
    }
    
    /**
     * Construit un tableau de tableaux associatifs à partir de la table <b>ad_etr</b>
     *                 avec les éléments sélectionés de l'échéancier.  Chaque tableau associatif a:<ul>
     *                 <li>nom d'un élément = nom de champ dans la table,
     *                 <li>valeur de cet élément = valeur actuelle du champ.</ul>
     *
     * @param str $whereCond : clause SQL de sélection des entrées de l'échéancier
     * 
     * @return void : le tableau de tableaux si OK, NULL si aucun élément
     */
    public function getEcheancier($whereCond) {

        $Echeancier = array();

        $sql = "SELECT * FROM ad_etr $whereCond AND id_ag = :id_agence ORDER BY id_ech";

        $param_arr = array(':id_agence' => $this->getIdAgence());

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        foreach ($results as $key => $row) {
            array_push($Echeancier, $row);
        }

        return $Echeancier;
    }
    
    /**
     * Construit un tableau associatif avec tout l'échéancier de remboursement (table ad_sre)
     *
     * @param mixed $whereCond : une clause SQL à associer à la requête sur ad_sre
     * 
     * @return void tableau de tableaux associatifs des champs de la table ad_sre ou NULL si aucun dossier de crédit correspondant
     */
    public function getRemboursement($whereCond) {

        $Remb = array();

        $sql = "SELECT * FROM ad_sre $whereCond and id_ag = :id_agence ORDER BY id_ech";

        $param_arr = array(':id_agence' => $this->getIdAgence());

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        foreach ($results as $key => $row) {
            array_push($Remb, $row);
        }

        return $Remb;
    }
    
    public function getRechMorHistorique($oper, $client, $date_dem) {
        // Renvoie tous les  historiques d'une opération donnée dépuis la mise en place du dossier de crédit
        // IN : $oper (type opération) $client (id client)
        // OUT: Tableau associatif ou NULL

        $historiq = array();

        $sql = "SELECT * FROM ad_his WHERE (id_ag = :id_agence) AND (type_fonction = :oper) AND (id_client = :id_client) AND (date(date) > date(:date_dem)) ORDER BY date";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':oper' => $oper, ':id_client' => $client, ':date_dem' => $date_dem);

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        foreach ($results as $key => $row) {
            array_push($historiq, $row);
        }

        return $historiq;
    }

    /**
     * Renvoie tous les états de crédit possibles classés par ID
     */
    public function getTousEtatCredit($en_retard = false) {

        $retour = array();

        $sql = "SELECT * FROM adsys_etat_credits WHERE id_ag = :id_agence";

        if($en_retard){
              $sql .= " AND nbre_jours != 1 AND nbre_jours != -1 ";
        }

        $sql .= " ORDER BY id ";

        $param_arr = array(':id_agence' => $this->getIdAgence());

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        foreach ($results as $key => $row) {
            $retour[$row["id"]] = $row;
        }

        return $retour;
    }


    public function getDateLastRemb($id_doss) {

        $sql = "SELECT date_remb from ad_sre where id_doss = :id_dossier and id_ag = :id_agence ";
        $sql .= "AND date_remb = (select max(date_remb) from ad_sre where id_doss = :id_dossier and id_ag = :id_agence) ";
        $sql .= "AND id_ech = (select max(id_ech) from ad_sre where id_doss = :id_dossier and id_ag = :id_agence)";
        
        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_dossier' => $id_doss);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

        if ($result === FALSE || count($result) == 0) {
            return NULL;
        }

        return $result;
    }
}

