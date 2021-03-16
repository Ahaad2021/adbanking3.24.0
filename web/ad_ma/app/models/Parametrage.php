<?php

/**
 * Description de la classe Parametrage
 *
 * @author danilo
 */

require_once 'ad_ma/app/models/BaseModel.php';

class Parametrage extends BaseModel {

    public function __construct(&$dbc, $id_agence=NULL) {        
        parent::__construct($dbc, $id_agence); 
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Renvoie un booléen indiquant si le profil a accès au solde des comptes clients
     * 
     * @param int $id_agence L'identifiant de l'agence distante
     * @param int $profil_id L'identifiant du profil
     * @param int $prod_epargne_id L'identifiant du produit epargne distant
     * 
     * @return bool Indiquant si le profil a accès au solde des comptes clients
     */
    public function getProfilAccesSolde($profil_id, $prod_epargne_id = NULL) {
        global $dbHandler;

        // Vérifier si l'acces au solde est interdit pour ce produit d'epargne
        $masque_solde_epargne = 'f';
        if ($prod_epargne_id != NULL) {

            $sql = "SELECT masque_solde_epargne FROM adsys_produit_epargne WHERE id = :prod_epargne_id and id_ag = :id_agence";

            $param_arr = array(':prod_epargne_id' => $prod_epargne_id, ':id_agence' => $this->getIdAgence());

            $masque_solde_epargne = $this->getDbConn()->prepareFetchColumn($sql, $param_arr);
            if (count($masque_solde_epargne) < 1) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Produit epargne '$prod_epargne_id' non-trouvé dans la base de données"
            }
        }

        $db = $dbHandler->openConnection();

        // Vérifier si l'acces au solde est interdit pour ce profil
        $sql = "SELECT access_solde FROM adsys_profils WHERE id=$profil_id";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        if ($result->numrows() != 1) {
            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Profil '$profil_id' non-trouvé dans la base de données"
        }

        $retour = $result->fetchrow();
        $profil_access = $retour[0];

        $db = $dbHandler->closeConnection(true);
        if ($prod_epargne_id != NULL) {
            return (($profil_access == 't') || ($masque_solde_epargne == 'f'));
        } else {
            return ($profil_access == 't');
        }
    }

    /**
     * Renvoie les libellés à afficher pour le choix d'une banque
     * 
     * @return un tableau contenant le libellé et la clef de la banque
     */
    public function getLibelBanque() {

        $sql = "SELECT id_banque, nom_banque ";
        $sql .= "FROM adsys_banque WHERE id_ag = :id_agence ";

        $param_arr = array(':id_agence' => $this->getIdAgence());

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $libels = array();

        foreach ($result as $row) {
            $libels[$row['id_banque']] = $row['nom_banque'];
        }

        return $libels;
    }

    /**
     * récupére les différents billets d'une devise donné
     * 
     * @param $devise la dvise
     * 
     * @return $billet l'ensemble des billet
     */
    public function recupeBillet($devise) {

        $sql = "SELECT valeur FROM adsys_types_billets WHERE id_ag=:id_agence AND devise= :devise ORDER BY valeur DESC";
        
        $param_arr = array(':id_agence' => $this->getIdAgence(), ':devise' => $devise);

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);
        
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $billet = array();
        foreach($result as $row) {
            array_push($billet, $row['valeur']);
        }
        
        return $billet;
    }

    
    /**
     * Renvoie les libellés à afficher pour le choix d'un correspondant bancaire
     * @param char(3) $devise La devise des correspondants à renvoyer
     * @return Array Tableau contenant le libellé et la clef du correspondant
     */
    public function getLibelCorrespondant($devise = NULL)
    {    
     
        $sql = "SELECT a.id, nom_banque, c.devise ";
        $sql .= "FROM adsys_correspondant a, adsys_banque b, ad_cpt_comptable c ";
        $sql .= "WHERE a.id_ag=b.id_ag AND b.id_ag=c.id_ag AND a.id_ag=:id_agence AND a.id_banque=b.id_banque
                AND a.cpte_bqe = c.num_cpte_comptable";
    
        if (isset($devise)) {
            $sql .= " AND c.devise=:devise ";
        }
    
        $param_arr = array(':id_agence' => $this->getIdAgence());
    
        if (isset($devise)) {
            $param_arr[':devise'] = $devise;
        }
    
        $dbc = $this->getDbConn();
        $result = $dbc->prepareFetchAll($sql, $param_arr);
    
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
    
        $libels = array();
    
        foreach ($result as $row) {
            //$libels[$row['id_banque']] = $row['nom_banque'];
            $libels[$row['id']] = $row['nom_banque']." - ".$row['cpte_bqe']."(".$row['devise'].")";
        }
         
        return $libels;
    }
    
    
    /**
     * Liste des types pièce comptable sur la base remote
     * @return array Liste des types pièce comptable
     */
    public function getListeTypePieceComptables() 
    {    
        global $global_langue_utilisateur;    

        $sql = "SELECT id, traduction(libel, :langue) as libel FROM adsys_type_piece_payement WHERE id_ag=:id_ag order by id; ";        
        $params = array(':id_ag' => $this->getIdAgence(), ':langue' => $global_langue_utilisateur); 
        
        $dbc = $this->getDbConn();
        $results = $dbc->prepareFetchAll($sql, $params);
        
        if($results===FALSE || count($result)<0) {           
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }      
    
        $TP = array();
        
        foreach ($results as $result) {
            $TP[$result['id']]= $result['libel'];
        }
        return $TP;
    }

    public function get_profil_acces_solde_vip($profil_id, $id_client = NULL) {
        global $dbHandler;
        // Vérifier si l'acces au solde est interdit pour ce client
        $masque_solde_client_vip = 'f';
        if($id_client != NULL) {
            $sql = "SELECT pp_is_vip FROM ad_cli WHERE id_client=:id_client and id_ag = :id_agence";
            $param = array(":id_client" => $id_client, ":id_agence" => $this->getIdAgence());

            $results = $this->getDbConn()->prepareFetchRow($sql, $param);
            if ($results===FALSE || count($results)<0) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__,__LINE__,__FUNCTION__);
            }
            if (count($results) != 1) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Client '$id_client' non-trouvé dans la base de données"
            }

            $masque_solde_client_vip = $results["pp_is_vip"];
        }

        $db = $dbHandler->openConnection();

        // Vérifier si l'acces au solde VIP est interdit pour ce profil
        $sql = "SELECT access_solde_vip FROM adsys_profils WHERE id=$profil_id";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        if ($result->numrows() != 1) {
            $dbHandler->closeConnection(false);
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Profil '$profil_id' non-trouvé dans la base de données"
        }

        $next_result = $result->fetchrow();
        $profil_access_vip = $next_result[0];

        $db = $dbHandler->closeConnection(true);

        if($id_client != NULL)
            return (($profil_access_vip == 't') || ($masque_solde_client_vip == 'f'));
        else
            return ($profil_access_vip == 't');
    }

    public function manage_display_solde_access($access_solde, $access_solde_vip) {
        $display_solde = false;

        if($access_solde){
            $display_solde = true;

            if(!$access_solde_vip) {
                $display_solde = false;
            }
        }

        return $display_solde;
    }
}