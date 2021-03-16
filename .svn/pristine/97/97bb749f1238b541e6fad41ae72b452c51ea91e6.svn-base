<?php

/**
 * Description de la classe Epargne
 *
 * @author danilo
 */
require_once 'ad_ma/app/models/BaseModel.php';

class Epargne extends BaseModel {

    /** Properties */
    private $_id_produit;
    private $_info_cpte;

    public function __construct(&$dbc, $id_agence = NULL) {
        parent::__construct($dbc, $id_agence);
    }

    public function __destruct() {
        parent::__destruct();
    }


    /**
     * Récupère les opérations comptables destinés à prelever le frais forfaitaire transactionnel SMS
     *
     * @return array
     */
    public function getListeTypeOptDepPourPreleveFraisSMS(){

        $sql = "SELECT type_opt FROM adsys_param_mouvement WHERE id_ag = :id_agence AND preleve_frais = 't' AND deleted = 'f'";

        $param_arr = array(':id_agence' => $this->getIdAgence());

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $tmp_arr = array();
        foreach ($result as $type_opt) {
            foreach ($type_opt as $opt => $valeur) {
                $tmp_arr[] = $valeur;
            }
        }

        return $tmp_arr;
    }

    /**
    * Récupère les infos d'une tarification
    *
    *  tableau avec les infos sur la tarification
    */
    public function getTarificationDatas(){
        $typeFrais = "SMS_FRAIS";

        $sql = "SELECT * FROM adsys_tarification WHERE id_ag= :id_agence AND statut='t' AND type_de_frais = :type_frais AND ((date(date_debut_validite) IS NULL AND date(date_fin_validite) IS NULL) OR (date(date_debut_validite) <= date(NOW()) AND date(date_fin_validite) IS NULL) OR (date(date_debut_validite) IS NULL AND date(date_fin_validite) >= date(NOW())) OR (date(date_debut_validite) <= date(NOW()) AND date(date_fin_validite) >= date(NOW()))) ORDER BY date_creation DESC";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':type_frais' => $typeFrais);

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if($result===FALSE) {
          $this->getDbConn()->rollBack(); // Roll back
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if(!empty($result)){
            $frais = array();
          foreach($result as $key => $row){
            $frais = $row;
          }

          return $frais;
        }
        return null;
    }

    /**
     * Vérifie que le retrait est possible sur le compte
     * 
     * @param array $InfoCpte : données du compte d'épargne sélectionné
     * @param array $InfoProduit : données du produit d'épargne
     * @param float $montant : montant à débiter du compte
     * @param int $operation : (0 prend en compte les frais de retrait, 1 prend en compte les frais de transfert)
     * 
     * @return ErrorObj Objet Erreur
     */
    public function CheckRetrait($InfoCpte, $InfoProduit, $montant, $operation, $id_mandat,$commission_op = 0, $test_delai = false) {
        //vérification de l'état du compte : ouvert
        if ($InfoCpte["etat_cpte"] == 3) {
            return new ErrorObj(ERR_CPTE_BLOQUE, $InfoCpte["id_cpte"]);
        }
        if ($InfoCpte["etat_cpte"] == 4) {
            return new ErrorObj(ERR_CPTE_ATT_FERM, $InfoCpte["id_cpte"]);
        }
        if ($InfoCpte["etat_cpte"] == 7) {
            return new ErrorObj(ERR_CPTE_BLOQUE_RETRAIT, $InfoCpte["id_cpte"]);
        }

        //vérifier possibilité retrait
        if ($InfoProduit["retrait_unique"] == 't') {
            return new ErrorObj(ERR_RETRAIT_UNIQUE, $InfoCpte['id_cpte']);
        }

        // Recherche des frais à appliquer en fonction du type d'opération
        $frais = 0;
        if ($operation == 0) { // Retrait cash
            $frais = $InfoProduit['frais_retrait_cpt'];
        } else if ($operation == 1) { // Retrait par transfert
            $frais = $InfoProduit['frais_transfert'];
        }

        $solde_disponible = self::getSoldeDisponible($InfoCpte['id_cpte']);

        if (($solde_disponible - $frais) < 0) {
            return new ErrorObj(ERR_MNT_MIN_DEPASSE, $InfoCpte["id_cpte"]);
        }

        if (($solde_disponible - $commission_op) < 0){
            return new ErrorObj(ERR_MNT_MIN_DEPASSE, $InfoCpte["id_cpte"]);
        }

        if ($test_delai == false) {
            //vérifier si durée mini entre deux retraits
            if ($InfoProduit["duree_min_retrait_jour"] > 0) {
                $erreur = self::CheckDureeMinRetrait($InfoCpte["id_cpte"], $InfoProduit["duree_min_retrait_jour"]);
                if ($erreur->errCode != NO_ERR) {
                    return $erreur;
                }
            }
        }


        // Vérifications sur le mandat
        if ($id_mandat != NULL && $id_mandat != 'CONJ' && $id_mandat != 0) {
            $MANDAT = self::getInfosMandat($id_mandat);
            if (($MANDAT['limitation'] != NULL && $MANDAT['limitation'] < $montant) || $MANDAT['id_cpte'] != $InfoCpte['id_cpte']) {
                return new ErrorObj(ERR_MANDAT_INSUFFISANT, $InfoCpte['id_cpte']);
            }
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     * Vérifie si la durée minimum entre 2 retraits est respectée.
     * On cherche d'abord la dernière date de retrait pour le compte sélectionné, on additionne la durée minimum de retrait en jours et on la compare à la date d'aujourd'hui
     */
    public function CheckDureeMinRetrait($id_cpte, $duree_min_retrait) {

        // Prendre le dernier mouvement débiteur sur le compte du client pour un retrait ou un transfert
        $sql = "select a.date from ad_his a, ad_ecriture b, ad_mouvement c  where (a.id_ag=b.id_ag) and (b.id_ag=c.id_ag) and (a.id_ag = :id_agence) AND (a.type_fonction=70 OR a.type_fonction=76 or a.type_fonction=85 or a.type_fonction=92) ";
        $sql .= "and a.id_his=b.id_his and b.id_ecriture=c.id_ecriture and c.cpte_interne_cli = :id_compte and c.sens='d' ";
        $sql .= "order by a.date DESC limit 1;";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_compte' => $id_cpte);

        $result = $this->getDbConn()->prepareFetchColumn($sql, $param_arr);
        
        if (DB::isError($result)) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if ($result) {

            $date_dernier_retrait = pg2phpDatebis($result); //array sous la forme M/J/Y

            $date_prochain_retrait = mktime(0, 0, 0, $date_dernier_retrait[0], $date_dernier_retrait[1] + $duree_min_retrait, $date_dernier_retrait[2]); //quel est la date du prochain retrait ?

            $today = mktime(0, 0, 0, date("m"), date("d"), date("Y")); //on va comparer avec aujourd'hui

            $temp = mktime(0, 0, 0, $date_dernier_retrait[0], $date_dernier_retrait[1], $date_dernier_retrait[2]);
            if ($today < $date_prochain_retrait)
                return new ErrorObj(ERR_DUREE_MIN_RETRAIT, $id_cpte);
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     * Retourne les caractéristiques d'un produit d'épargne
     * 
     * @param int $id_produit
     * 
     * @return array Un tableau associatif avec les caractéristiques du produit, NULL si pas de produit trouvé.
     */
    public function getProdEpargne($id_produit) {
        $sql = "SELECT * FROM adsys_produit_epargne WHERE id_ag = :id_agence AND id = :id_produit";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_produit' => $id_produit);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

        if ($result === FALSE || count($result) == 0) {
            return new ErrorObj(ERR_DB_SQL, $result);
        }

        return $result;
    }
    
    /**
    * Renvoie l'id d'un compte en fonction de son numéro de compte complet.
    *
    * @param string $num_complet_cpte Le numéro de compte complet.
     * 
    * @return int L'identifiant du compte.
    */
    public function getIdCompte($num_complet_cpte) {
        
        $sql = "SELECT id_cpte FROM ad_cpt WHERE id_ag = ".$this->getIdAgence()." AND num_complet_cpte LIKE '".$num_complet_cpte."'";

        $result = $this->getDbConn()->prepareFetchColumn($sql);

        if ($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Renvoie le solde disponible sur un compte client en tenant compte de
     * 
     *  - Compte bloqué => solde = 0
     *  - Retrait unique => solde = 0
     *  - Montant bloqué
     *  - Montant minimum
     *  - Découvert maximum autorisé
     *  - Si solde dispo négatif alors solde disponible = 0
     * 
     * @param int $id_cpte
     * 
     * @return float Solde disponible
     */
    public function getSoldeDisponible($id_cpte) {

        // Init class
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());

        // Remplir 2 tableaux avec toutes les infos sur le compte et le produit associé       
        $InfoCpte = $CompteObj->getAccountDatas($id_cpte);       
        $InfoProduit = self::getProdEpargne($InfoCpte["id_prod"]);

        // Destroy object
        unset($CompteObj);

        if ($InfoProduit["retrait_unique"] == 't' || $InfoCpte["etat_cpte"] == 3) {
            $solde_dispo = 0;
        } else {
            $solde_dispo = $InfoCpte["solde"] - $InfoCpte["mnt_bloq"] - $InfoCpte["mnt_min_cpte"] + $InfoCpte["decouvert_max"] - $InfoCpte["mnt_bloq_cre"];
        }

        if ($solde_dispo < 0) {
            $solde_dispo = 0;
        }

        return $solde_dispo;
    }
    
    /**
     * Renvoie la liste des comptes sur lesquels le client peut faire un retrait
     * i.e. les comptes qui ne sont pas à retrait unique et qui sont ouverts
     * et dont la classe comptable est 1 (DAV)
     * 
     * @param array $ListeDeComptes
     * 
     * @return array Tableau associatif avec les comptes trouvés, indicé par les identifiants du compte.
     */
    public function getComptesRetraitPossible($ListeDeComptes) {

        foreach ($ListeDeComptes as $key => $value) {
            //FIXME : le test sur classe_comptable n'est pas bon car on doit pouvoir
            //retirer sur un compte à terme
            if (($value["retrait_unique"] == 't') || ($value["etat_cpte"] != "1" && $value["etat_cpte"] != "6") || $value["classe_comptable"] != 1) {
                unset($ListeDeComptes[$key]);
            }

            $soldeDispo = self::getSoldeDisponible($key);
            if ($soldeDispo == 0) {
                unset($ListeDeComptes[$key]);
            }
        }

        return $ListeDeComptes;
    }

    /**
     * Renvoie tous les comptes d'épargne d'un client qui sont services financiers
     * 
     * @param int $id_client L'identifiant du client
     * @param str $devise La devise dans laquelle on cherche les comptes
     * 
     * @return array Tableau associatif avec les comptes trouvés, indicé par les identifiants du compte.
     */
    public function getComptesEpargne($id_client, $devise = NULL) {

        $sql = "SELECT a.*, b.* FROM ad_cpt a,adsys_produit_epargne b WHERE a.id_ag=b.id_ag and a.id_ag = :id_agence AND a.id_prod = b.id AND ";
        $sql .= "a.id_titulaire = :id_client and b.service_financier = true";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);

        // On ne prend pas les comptes bloqués
        $sql .= " AND (a.etat_cpte <> 2)";
        if ($devise != NULL) {
            $sql .= " AND a.devise = :devise";

            $param_arr[':devise'] = $devise;
        }

        $sql .= " ORDER BY a.num_complet_cpte";

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);
        
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $tmp_arr = array();
        foreach ($result as $cpte) {
            $tmp_arr[$cpte["id_cpte"]] = $cpte;
            $tmp_arr[$cpte["id_cpte"]]["soldeDispo"] = self::getSoldeDisponible($cpte["id_cpte"]);
        }

        /*
          if (!$result) {
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
          }
         */

        return $tmp_arr;
    }

    /**
     * Renvoie toutes les informations concernant un compte d'épargne en particulier.
     *
     * @param int $id_compte L'identifiant du compte dont on veut les informations.
     * 
     * @return array Tableau contenant les informations du compte (ad_cpt).
     */
    public function getCompteEpargneInfo($id_compte) {

        $sql = "SELECT * FROM ad_cpt WHERE id_ag = :id_agence AND id_cpte = :id_compte";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_compte' => $id_compte);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

        if ($result===FALSE || count($result) == 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Aucune ou plusieurs occurences du compte !"));
        }

        return $result;
    }

    /**
     * Renvoie la liste de tous les comptes du client sur lesquels le dépôt est possible
     * 
     * @param array $ListeComptes (liste des comptes à filtrer) FIXME : Pas très propre : à changer
     * 
     * @return array
     */
    public function getComptesDepotPossible($ListeDeComptes) {

        if (is_array($ListeDeComptes)) {
            foreach ($ListeDeComptes as $key => $value) {
                if (($value["depot_unique"] == 't') || ($value["etat_cpte"] != 1 && $value["etat_cpte"] != "7")) {
                    unset($ListeDeComptes[$key]);
                }
            }
        }

        return $ListeDeComptes;
    }
    
    /**
     * Renvoie la liste de tous les comptes du client
     * 
     * @param array $ListeComptes
     * 
     * @return array
     */
    public function getComptesPossible($ListeDeComptes) {

        $ListeDeTousComptes = array();
        $ListeDeComptesRetrait = $ListeDeComptes;
        $ListeDeComptesDepot = $ListeDeComptes;
        
        // Retrait
        foreach ($ListeDeComptesRetrait as $key => $value) {
            if (($value["retrait_unique"] == 't') || ($value["etat_cpte"] != "1" && $value["etat_cpte"] != "6") || $value["classe_comptable"] != 1) {
                unset($ListeDeComptesRetrait[$key]);
            }

            $soldeDispo = self::getSoldeDisponible($key);
            if ($soldeDispo == 0) {
                unset($ListeDeComptesRetrait[$key]);
            }
        }
        
        // Dépôt
        if (is_array($ListeDeComptesDepot)) {
            foreach ($ListeDeComptesDepot as $key => $value) {
                if (($value["depot_unique"] == 't') || ($value["etat_cpte"] != 1 && $value["etat_cpte"] != "7")) {
                    unset($ListeDeComptesDepot[$key]);
                }
            }
        }

        // Merge arrays
        $ListeDeTousComptes = array_merge($ListeDeComptesRetrait, $ListeDeComptesDepot);

        // Remove duplicate array elements
        $ListeDeTousComptes = array_unique($ListeDeTousComptes, SORT_REGULAR);
        
        return $ListeDeTousComptes;
    }

    /**
     * Récupérer la liste des mandats liés à un compte
     *
     * @param integer $id_cpte identifiant du compte
     *
     * @return Array liste des mandats du compte
     */
    public function getMandats($id_cpte) {

        if ($id_cpte == NULL) {
            return NULL;
        }

        $WHERE['id_cpte'] = $id_cpte;
        $WHERE['id_ag'] = $this->getIdAgence();

        $sql = buildSelectQuery('ad_mandat', $WHERE);

        $result = $this->getDbConn()->prepareFetchAll($sql);

        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $TMPARRAY = array();
        foreach ($result as $row) {

            // Init class
            $ClientObj = new Client($this->getDbConn(), $this->getIdAgence());
            $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());

            $PERS_EXT = $ClientObj->getPersonneExt(array('id_pers_ext' => $row['id_pers_ext']));
            $ACC = $CompteObj->getAccountDatas($id_cpte);

            // Destroy object
            unset($ClientObj);
            unset($CompteObj);

            $row['denomination'] = $PERS_EXT[0]['denomination'];
            $row['devise'] = $ACC['devise'];
            $id_mandat = $row['id_mandat'];
            unset($row['id_mandat']);
            $TMPARRAY[$id_mandat] = $row;
        }

        return $TMPARRAY;
    }

    /**
     * Récupérer les infos du mandat liés à un id mandat (#pp 242 et trac t728)
     *
     * @param integer $id_mandat identifiant du mandat
     *
     * @return Array liste infos pour mandat
     */
    public function getMandatInfo($id_mandat) {

        if ($id_mandat == NULL) {
            return NULL;
        }

        $sql = "select id_pers_ext from ad_mandat where id_mandat = $id_mandat and id_ag = ".$this->getIdAgence();

        $result = $this->getDbConn()->prepareFetchAll($sql);

        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $mandatInfo = array();
        foreach ($result as $row) {

            // Init class
            $ClientObj = new Client($this->getDbConn(), $this->getIdAgence());

            $PERS_EXT = $ClientObj->getPersonneExt(array('id_pers_ext' => $row['id_pers_ext']));

            // Destroy object
            unset($ClientObj);

            $mandatInfo['denomination'] = $PERS_EXT[0]['denomination'];
            $mandatInfo['tireur'] = 'f';
            $mandatInfo['beneficiaire'] = 't';
            $mandatInfo['adresse'] = $PERS_EXT[0]['adresse'];
            $mandatInfo['code_postal'] = $PERS_EXT[0]['code_postal'];
            $mandatInfo['ville'] = $PERS_EXT[0]['ville'];
            $mandatInfo['pays'] = $PERS_EXT[0]['pays'];
            $mandatInfo['num_tel'] = $PERS_EXT[0]['num_tel'];
            $mandatInfo['type_piece'] = $PERS_EXT[0]['type_piece_id'];
            $mandatInfo['num_piece'] = $PERS_EXT[0]['num_piece_id'];
            $mandatInfo['lieu_delivrance'] = $PERS_EXT[0]['lieu_piece_id'];
        }

        return $mandatInfo;
    }

    function dateExpValide($sDateExp) {
        if($sDateExp == null){
            return true;
        }
        $dateExp = date_parse_from_format('Y-m-d H:i:s', $sDateExp . " 00:00:00");
        $now = date_parse_from_format('Y-m-d H:i:s', date("Y") . '-' . date("m") . '-' .date("d") . " 00:00:00");
        return $dateExp > $now;
    }

    /**
     * Fabriquer la liste de tous les mandataires liés à un compte avec leur dénomination et le débit maximum
     * 
     * @param integer $id_cpte identifiant du compte
     * 
     * @return Array liste des mandats du compte
     */
    public function getListeMandatairesActifs($id_cpte, $is_non_join = NULL) {

        $MANDATAIRES = self::getMandats($id_cpte);

        if ($MANDATAIRES == NULL) {
            return NULL;
        }

        foreach ($MANDATAIRES as $key => $value) {
            if ($value['valide'] == 't' && dateExpValide($value['date_exp'])) {
                if ($value['type_pouv_sign'] == 1 || $is_non_join) {
                    $TMPARRAY[$key]['libelle'] = $value['denomination'];
                    if ($value['limitation'] != NULL) {
                        $TMPARRAY[$key]['libelle'] .= " - " . Divers::afficheMontant($value['limitation']) . " " . $value['devise'];
                        $TMPARRAY[$key]['limitation'] = $value['limitation'];
                    }
                } else {
                    $TMPARRAY['CONJ']['libelle'] .= $value['denomination'] . ", ";
                }
            }
        }

        if ($TMPARRAY['CONJ'] != NULL) {
            $TMPARRAY['CONJ']['libelle'] = substr($TMPARRAY['CONJ']['libelle'], 0, $TMPARRAY['CONJ']['libelle'] - 2);
        }

        return $TMPARRAY;
    }

    /**
     * Récupérer toutes les informations d'un mandat
     * 
     * @param integer $id_mandat identifiant du mandat
     * 
     * @return Array informations sur le mandat
     */
    public function getInfosMandat($id_mandat) {

        $WHERE['id_mandat'] = $id_mandat;
        $WHERE['id_ag'] = $this->getIdAgence();
        $sql = buildSelectQuery('ad_mandat', $WHERE);

        $row = $this->getDbConn()->prepareFetchRow($sql);

        if ($row===FALSE || count($row) == 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $ClientObj = new Client($this->getDbConn(), $this->getIdAgence());

        $PERS_EXT = $ClientObj->getPersonneExt(array('id_pers_ext' => $row['id_pers_ext']));
        $row['id_pers_ext'] = $PERS_EXT[0]['id_pers_ext'];
        $row['denomination'] = $PERS_EXT[0]['denomination'];
        $row['type_piece_id'] = $PERS_EXT[0]['type_piece_id'];
        $row['num_piece_id'] = $PERS_EXT[0]['num_piece_id'];
        $row['lieu_piece_id'] = $PERS_EXT[0]['lieu_piece_id'];
        $row['date_piece_id'] = $PERS_EXT[0]['date_piece_id'];
        $row['date_exp_piece_id'] = $PERS_EXT[0]['date_exp_piece_id'];
        $row['id_client'] = $PERS_EXT[0]['id_client'];
        $row['photo'] = $PERS_EXT[0]['photo'];
        $row['signature'] = $PERS_EXT[0]['signature'];

        // Destroy object
        unset($ClientObj);

        if ($row['type_piece_id'] != NULL) {
            $sql = "select b.traduction from adsys_type_piece_identite a, ad_traductions b where a.id_ag = :id_agence and  a.id = :type_piece_id and a.libel = b.id_str";

            $param_arr = array(':id_agence' => $this->getIdAgence(), ':type_piece_id' => (int)trim($row['type_piece_id']));

            $tmprow = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

            if ($tmprow===FALSE) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }

            if (count($tmprow) == 1) {
                $row['libel_type_piece_id'] = $tmprow['traduction'];
            }
        }

        return $row;
    }

    /**
     * Cette fonction insère une attente dans la table Attente, après avoir vérifié que celle-ci n'avait pas déjà été encodée, 
     * en vérifiant s'il n'y a pas une attente avec les mêmes numéro, correspondant et date.
     * 
     * @param array $data : tous les champs à insérer dans la table attentes_credit
     * 
     * @return ErrorObj Les erreurs possibles et s'il n'y a pas d'erreur, renvoie en paramètre l'identifiant de l'attente insérée.
     */
    public function insertAttente($data) {

        //vérifier que le chèque n'a pas déjà été saisi
        $sql = "SELECT * FROM attentes where ";
        $sql .= " (num_piece= '" . $data["num_piece"] . "')";
        if ($data['type_piece'] == 5)//Si c'est un Travelers cheque, il n'y a pas de correspondant.
            $sql .= " AND (id_correspondant IS NULL";
        else
            $sql .= " AND (id_correspondant= " . $data["id_correspondant"];
        $sql .= ") AND (date_piece= date('" . $data["date_piece"] . "')) AND (id_ag=" . $this->getIdAgence() . ") ;";

        $result = $this->getDbConn()->prepareFetchAll($sql);      
        
        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if (count($result) != 0) {
            return new ErrorObj(ERR_DUPLICATE_CHEQUE);
        }

        //insertion dans la table des attentes
        $data['id_ag'] = $this->getIdAgence();
        $sql = buildInsertQuery("attentes", $data);

        $result = $this->getDbConn()->execute($sql);
        
        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        //on récupère le id du chèque qu'on vient d'insérer pour le mettre dans l'historique
        $sql = "select max(id) from attentes where id_ag=" . $this->getIdAgence() . ";";

        $id_cheque = $this->getDbConn()->prepareFetchColumn($sql);
        
        if ($id_cheque===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return new ErrorObj(NO_ERR, $id_cheque);
    }

    /**
     * Prélève les frais de découvert
     *
     * Cette fonction peut être appellée dans 2 contextes :
     *  * lors d'une opération lorsqu'un compte passe en négatif ou devient plus négatif (cas premier, implémenté d'abord pour la TMB,
     *    dans ce cas la fonction est normalement appellée uniquement lors d'une opération initiée par un client),
     *  * lors de l'octroi du découvert, ce sont alors les frais de dossier de découvert qui doivent être prélevés,
     *    dans ce cas, ils ne sont prélevés que si le découvert proposé au client est plus grand que le découvert actuellement en cours.
     *
     * Voir le cahier des charges pour plus d'informations {@link https://devel.adbanking.org/wiki/CdCh/EparGne/Decouverts}
     * 
     * @param int $a_id_cpte : Compte d'épargne concerné
     * @param array $comptable Liste de mouvements comptables précédemment enregistrés et qui sera finalement passée à ajout_historique.
     * @param bool $a_frais Le montant des frais demandés ou NULL si ce sont les frais lors d'une opération qui sont prélevés.
     * 
     * @return ErrorObj Objet Erreur avec les frais prélevés en paramètre
     */
    public function preleveFraisDecouvert($a_id_cpte, &$comptable, $a_frais = NULL) {
        global $global_remote_monnaie;

        // Init class
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());

        $cpte = $CompteObj->getAccountDatas($a_id_cpte);
        $frais = 0;

        // Quel type de frais doit-on prélever ?
        if ($a_frais != NULL) {
            $frais = $a_frais;
        } else {
            // On va calculer le solde actuel en parcourant l'array comptable
            $solde = $cpte["solde"];
            reset($comptable);
            foreach ($comptable as $key => $ligne) {
                if ($ligne["cpte_interne_cli"] == $a_id_cpte) {
                    if ($ligne["sens"] == 'd')
                        $solde -= $ligne['montant'];
                    else if ($ligne["sens"] == 'c')
                        $solde += $ligne['montant'];
                }
            }
            if ($solde < 0) {
                global $global_client_debiteur;
                $global_client_debiteur = true;
                $frais = $cpte["decouvert_frais"];
            }
        }

        // S'il y a des frais à prélever, préparer les écritures comptables correspondantes
        if ($frais > 0) {
            //débit du compte d'épargne par le crédit d'un compte de produit
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($a_id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $a_id_cpte;

            // Init class
            $DeviseObj = new Devise($this->getDbConn(), $this->getIdAgence());

            $err = $DeviseObj->effectueChangePrivate($cpte["devise"], $global_remote_monnaie, $frais, 470, $cptes_substitue, $comptable);

            // Destroy object
            unset($DeviseObj);

            if ($err->errCode != NO_ERR) {
                return $err;
            }
        }

        // Destroy object
        unset($CompteObj);

        return new ErrorObj(NO_ERR, $frais);
    }

    /**
     *
     * Récupérer toutes les informations d'un mandat
     * @param Text $denomination le nom de la personne extérieure
     * @return Array informations sur la personne
     */
    public function getInfosPersExt($denomination) {
        $sql = "SELECT * FROM ad_pers_ext ";
        $sql .= "WHERE denomination = :denomination AND id_ag = :id_agence";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':denomination' => $denomination);
        $result = $this->getDbConn()->prepareFetch($sql);
        return $result;
    }

    /**
     *
     * Vérifie que le dépôt est possible sur le compte.
     *
     * @param array $InfoCpte Tableau avec les infos sur le compte
     * @param int $montant Montant à déposer sur le compte.
     * @return ErrorObj
     */
    public function CheckDepot($InfoCpte, $montant) {
        $InfoProduit = $this->getProdEpargne($InfoCpte['id_prod']);

        //pour le dépôt unique, on vérifie s'il s'agit du versement initial avec solde = 0
        if (($InfoProduit["depot_unique"] == 't') && ($InfoCpte["solde"] != 0))
            return new ErrorObj(ERR_DEPOT_UNIQUE, $InfoCpte['id_cpte']);

        $id_cpte = $InfoCpte["id_cpte"];
        $id_client = $InfoCpte["id_titulaire"];

        // On ne peut pas déposer sur un compte bloqué
        if ($InfoCpte["etat_cpte"] == 3) {
            $num_complet_cpte = $InfoCpte["num_complet_cpte"];
            $id_client = $InfoCpte["id_titulaire"];
            return new ErrorObj(ERR_CPTE_BLOQUE, sprintf(_("Le Compte n° %s du client n° %s est bloqué."), $num_complet_cpte, $id_client));
        } else if ($InfoCpte["etat_cpte"] == 6) {
            $num_complet_cpte = $InfoCpte["num_complet_cpte"];
            $id_client = $InfoCpte["id_titulaire"];
            return new ErrorObj(ERR_CPTE_BLOQUE_DEPOT, sprintf(_("Le Compte n° %s du client n° %s est bloqué pour les dépôts."), $num_complet_cpte, $id_client));
        } else if ($InfoCpte["etat_cpte"] == 4) {
            $num_complet_cpte = $InfoCpte["num_complet_cpte"];
            $id_client = $InfoCpte["id_titulaire"];
            return new ErrorObj(ERR_CPTE_DORMANT, sprintf(_("Le Compte n° %s du client n° %s est dormant."), $num_complet_cpte, $id_client));
        }

        //vérifier dépassement montant maximum
        if ($InfoProduit["mnt_max"] > 0) {
            //on suppose que le montant bloqué sur le compte est intégré au solde
            if (($InfoCpte["solde"] + $montant) > $InfoProduit["mnt_max"]) {
                return new ErrorObj(ERR_MNT_MAX_DEPASSE, $InfoCpte['id_cpte']);
            }
        }

        //vérifier montant minimum non dépassé
        if ($InfoCpte["mnt_min_cpte"] > 0) {
            //il se peut qu'on fasse un premier dépôt qui soit inférieur au montant mini et dans ce cas, interdire que ce premier dépôt soit inférieur au mini autorisé pour le compte
            if (($InfoCpte["solde"] + $InfoCpte["decouvert_max"] + $montant) < $InfoCpte["mnt_min_cpte"]) {
                return new ErrorObj(ERR_MNT_MIN_DEPASSE, sprintf(_("Montant du dépôt inférieur au montant minimum sur le compte n° %s du client n° %s."), $id_cpte, $id_client));
            }
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     *
     * Permet de savoir s'il ya des frais en attente sur un compte d'un client
     * @since 3.0.4
     * @param int $id_cpte numero du compte
     * @return boolean true si le compte a des frais en attente
     */
    public function hasFraisAttenteCompte($id_cpte) {
        $result = $this->getFraisAttenteCompte($id_cpte);

        if (count($result) > 0) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @desc liste des frais en attente sur un compte
     * @since 3.0.4
     * @param int $id_cpte identifiant du compte ayant des frais en attente
     * @return array liste des faris en attente
     */
    public function getFraisAttenteCompte($id_cpte) {
        $sql = "SELECT * FROM ad_frais_attente ";
        $sql .= " WHERE id_cpte = :id_cpte  AND id_ag = :id_agence;";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_cpte' => $id_cpte);
        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);
        
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     *
     * @desc Paiement de frais en attente
     * @since 2.8
     * @param int $id_cpte identifiant du compte de prélèvement
     * @param int $type_op numéro de l'opération
     * @param real $montant_frais le montant des frais à payer
     * @return ErrorObj Objet Error avec en paramètre 0 si pas erreur sinon le code de l'erreur rencontré
     */
    public function paieFraisAttente($id_cpte, $type_op, $montant_frais, &$comptable) {
        global $global_remote_monnaie;

        // Infos sur le compte source
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());
        $InfoCpte = $CompteObj->getAccountDatas($id_cpte);
        $devise = $InfoCpte["devise"];

        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
        $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        $cptes_substitue["int"]["debit"] = $id_cpte;

        $DeviseObj = new Devise($this->getDbConn(), $this->getIdAgence());
        $erreur = $DeviseObj->effectueChangePrivate($devise, $global_remote_monnaie, $montant_frais, $type_op, $cptes_substitue, $comptable, NULL, NULL, $id_cpte);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     *
     * @desc Suppresion des frais en attente
     * @param int $id_cpte identifiant du compte de prélèvement
     * @param date $date_frais date des frais
     * @param $type_frais le type de frais en question
     * @return ErrorObj Objet Error avec en paramètre 0 si pas erreur sinon le code de l'erreur rencontré
     */
    public function supprimeFraisAttente($id_cpte, $date_frais, $type_frais) 
    {       
        $sql = " DELETE FROM ad_frais_attente WHERE id_cpte = :id_cpte
                 AND date_frais = :date_frais AND type_frais = :type_frais; ";

        $param_arr = array(':id_cpte' => $id_cpte, ':date_frais' => $date_frais, ':type_frais' => $type_frais);

        $result = $this->getDbConn()->execute($sql, $param_arr);
        
        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        return $result;
    }

    /**
     *
     * Cette fonction constate la réception d'un chèque ou d'une opération de paiement et le met éventuellement en attente.
     * @param array $data : toutes les informations utiles à la mise à jour de la table attentes_credit
     * @param array $InfoCpte : Les champs du compte client
     * @param array $InfoProduit : les champs du produit d'épargne lié au compte client
     * @param float $mntCompte : Montant qui sera déposé sur le compte (en cas de crédit direct)
     * @param boolean $credit_direct : s'il est à TRUE, il y a un crédit direct sauf bonne fin. Il ne faut pas mettre le montant en attente, mais prélever des frais de commission
     * @param array $mnt_comm : montant de la commission si crédit direct sauf bonne fin.
     * @param array $CHANGE : toutes les données concernant le change
     * @return ErrorObj Les erreurs possibles
     */
    public function receptionCheque($data, $InfoCpte, $InfoProduit, $mntCompte, $credit_direct = false, $mnt_comm = NULL, $CHANGE = NULL, &$montant_remboursement = null)
    {
        global $global_id_client, $global_nom_login, $global_monnaie, $global_id_agence, $global_remote_monnaie ;      
        
        // Le login configuré pour loggé les transactions distantes
        $login_remote = 'distant';
        
        // Init object
        $EpargneObj = new Epargne($this->getDbConn(), $this->getIdAgence());
        $HistoriqueObj = new Historique($this->getDbConn(), $this->getIdAgence());
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());
        $DeviseObj = new Devise($this->getDbConn(), $this->getIdAgence());
        $ClientObj = new Client($this->getDbConn(), $this->getIdAgence());

        if ($data != NULL)
            //$data_his_ext = creationHistoriqueExterieur($data);
            $data_his_ext =  $HistoriqueObj->creationHistoriqueExterieur($data);
        else
            $data_his_ext = NULL;

        unset($data['id_pers_ext']);

        $comptable = array();

        //Check que le dépôt est possible sur le compte
        $erreur = $EpargneObj->CheckDepot($InfoCpte, $data["montant"]);

        if ($erreur->errCode != NO_ERR) {
            //$dbHandler->closeConnection(false);
            return $erreur;
        }

        //On ajoute une attente (seulement s'il ne s'agit pas d'un crédit direct)
        if ($credit_direct) // Credit direct = passage ecritures comptable remote
        {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            //Mouvement des comptes "comptables" associés
            $operation = '503';
            $operation_comptable = 503;
            
            /*
            if (isset($CHANGE))
                $deviseCheque = $CHANGE['devise'];
            else
              $deviseCheque = $InfoCpte['devise'];
            */
            
            
            $deviseCheque = $InfoCpte['devise'];
            
            if ($data['type_piece'] == 2) { // en cas de chèque on mouvemente les comptes de compensation
                $comptesCompensation = $CompteObj->getComptesCompensation($data['id_correspondant']);              
                $cptes_substitue["cpta"]["debit"] = $comptesCompensation['compte'];
            } 

            // Credit le produit epargne
            $cptes_substitue["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($InfoCpte['id_cpte']);
            
            if ($cptes_substitue["cpta"]["credit"] == NULL) {
               //$dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                //$dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé aux Correspondants)"));
            }

            $cptes_substitue["int"]["credit"] = $InfoCpte['id_cpte'];
            $dev1 = $CHANGE['devise'];
            $dev2 = $InfoCpte['devise'];

            /*
            if (isset($CHANGE)) {
                //@todo : multidevise ?
                //$myErr = change($dev1, $dev2, $CHANGE["cv"], $mntCompte, $operation, $cptes_substitue, $comptable, $CHANGE["dest_reste"], $CHANGE["comm_nette"], $CHANGE["taux"]);
            } else {               
                $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $deviseCheque);
            }
            */

            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $deviseCheque);
                        
            if ($myErr->errCode != NO_ERR) {
                //$dbHandler->closeConnection(false);
                return $myErr;
            }
           
            //perception des frais liés au crédit direct sauf bonne fin         
            if ($mnt_comm != NULL) 
            {                
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();
                $montant_remboursement -= $mnt_comm;

                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($InfoCpte['id_cpte']);

                if ($cptes_substitue["cpta"]["debit"] == NULL) {                   
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("Compte comptable associé au produit d'épargne"));
                }
                $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];             
                $myErr = $DeviseObj->effectueChangePrivate($dev2, $global_remote_monnaie, $mnt_comm, 510, $cptes_substitue, $comptable);
                 
                if ($myErr->errCode != NO_ERR) {                   
                    return $myErr;
                }
            }
            
            
        } else { // Pas de crédit direct, on crée tout simplement une attente de crédit           
           
            $erreur = $this->insertAttente($data);         
            
            if ($erreur->errCode != NO_ERR) {
                //$dbHandler->closeConnection(false);
                return $erreur;
            } else {
                $id_cheque = $erreur->param;
            }
            
            $comptable = array();
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            
            // Mouvement des comptes "comptables" associés
            $operation = '500';
            $operation_comptable = 500;

            /*
            if (isset($CHANGE))
                $deviseCheque = $CHANGE['devise'];
            else
              $deviseCheque = $InfoCpte['devise'];     
             */
            
            $deviseCheque = $InfoCpte['devise'];
            
            $comptesCompensation = $CompteObj->getComptesCompensation($data['id_correspondant']);
            $cptes_substitue["cpta"]["credit"] = $comptesCompensation['credit'];
            $cptes_substitue["cpta"]["debit"] = $comptesCompensation['debit'];    
            
            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $deviseCheque);
                        
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }           
        }        

        // S'il y a des frais de dépot, percevoir ceux-ci :
        if ($InfoProduit["frais_depot_cpt"] > 0) 
        {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($InfoCpte['id_cpte']);
            $montant_remboursement -= $InfoProduit["frais_depot_cpt"];

            if ($cptes_substitue["cpta"]["debit"] == NULL) {                
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("Compte comptable associé au produit d'épargne"));
            }
            
            $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];           
            //$myErr = effectueChangePrivate($InfoCpte["devise"], $global_remote_monnaie, $InfoProduit["frais_depot_cpt"], 150, $cptes_substitue, $comptable);
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte["devise"], $global_remote_monnaie, $InfoProduit["frais_depot_cpt"], 150, $cptes_substitue, $comptable);
            
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }          

        //--------------Frais forfaitaire SMS--------------------------------------------
        $listeTypeOpt = $EpargneObj->getListeTypeOptDepPourPreleveFraisSMS();

        if (in_array($operation_comptable, $listeTypeOpt)) {
            $SMSTransactionnel = $EpargneObj->getTarificationDatas();
            $clientSMS = $ClientObj->checkIfClientAbonnerSMS($global_id_client);
            $operation_frais = 188;

            if (!empty($SMSTransactionnel) && $clientSMS == true) {
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();
                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($InfoCpte['id_cpte']);
                $montant_remboursement -= $SMSTransactionnel["valeur"];

                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("Compte comptable associé au produit d'épargne"));
                }

                $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];

                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte["devise"], $global_remote_monnaie, $SMSTransactionnel["valeur"], $operation_frais, $cptes_substitue, $comptable);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
        }

        //---------------Commission OD----------------------------------------------------

        if ($data['commission_op_deplace'] > 0)
        {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($InfoCpte['id_cpte']);
            $montant_remboursement -= $data['commission_op_deplace'];

            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("Compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];
            //$myErr = effectueChangePrivate($InfoCpte["devise"], $global_remote_monnaie, $InfoProduit["frais_depot_cpt"], 150, $cptes_substitue, $comptable);
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte["devise"], $global_remote_monnaie, $data['commission_op_deplace'], 156, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }
        $fonction = 93; // Dépôt en déplacé
        /*
        $login  = $global_id_agence. ' - ' . $global_nom_login;
        $infos_his = "Dépôt en déplacé";

        if (! $credit_direct) {
            $infos_his .= " id cheque attente - " .$id_cheque;
        }
        */
        
        $infos_his = 'agc='.$global_id_agence . ' - login=' . $global_nom_login;
        
        /*
        if (! $credit_direct) {
            $infos_his .= " id cheque attente - " .$id_cheque;
        }
        */
        
        $login = $login_remote;
        
        $MyError = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], $infos_his, $login, date("r"), $comptable, $data_his_ext);

        unset($EpargneObj);
        unset($HistoriqueObj);
        unset($CompteObj);
        
        if ($MyError->errCode != NO_ERR) {           
            return $MyError;
        }
        
        $id_his = $MyError->param['id_his'];
    	$id_ecriture = $MyError->param['id_ecriture'];    	
    	
    	return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));     
    }
    
    
    /**
     * Cette fonction traite la réception d'un virement
     * @param array $data : toutes les informations utiles au traitement et à l'écriture de l'historique
     * @param array $InfoCpte : Les champs du compte client
     * @param array $InfoProduit : les champs du produit d'épargne lié au compte client
     * @param array $CHANGE : les données concernant le change
     * @param array $frais_virement : eventuels frais de virement(exemple dépôt par lot pour les virements des salaires)
     * @param integer $type_fonction le numero de la fonction, par defaut 75=depot
     * @return ErrorObj Les erreurs possibles
     */
    
    public function receptionVirement($data, $InfoCpte, $InfoProduit, $CHANGE=NULL, $frais_virement=NULL,$type_fonction=75, $infos_sup, &$montant_remboursement = null)
    {
        global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_monnaie, $global_remote_monnaie;
        // Le login configuré pour loggé les transactions distantes
        $login_remote = 'distant';
                
        // Init object
        $EpargneObj = new Epargne($this->getDbConn(), $this->getIdAgence());
        $HistoriqueObj = new Historique($this->getDbConn(), $this->getIdAgence());
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());
        $DeviseObj = new Devise($this->getDbConn(), $this->getIdAgence());
        $ClientObj = new Client($this->getDbConn(), $this->getIdAgence());
        
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
                
        //Check que le dépôt est possible sur le compte
        $erreur = $EpargneObj->CheckDepot($InfoCpte, $data["montant"]);

        if ($erreur->errCode != NO_ERR) {           
            return $erreur;
        }
        
        //Création historique extérieure
        if ($data != NULL)           
            $data_his_ext =  $HistoriqueObj->creationHistoriqueExterieur($data);
        else
           $data_his_ext = NULL;
            
        //vérifier que le compte est ouvert ou bloqué
        if ($InfoCpte['etat_cpte'] == "2") {           
            return new ErrorObj(ERR_CPTE_FERME);
        } else if ($InfoCpte['etat_cpte'] == "4") {          
            return new ErrorObj(ERR_CPTE_ATT_FERM);
        }
    
        if ($InfoProduit["mnt_max"] > 0) {
            $new_solde = $InfoCpte["solde"] + $data["montant"];
            if ($new_solde  > $InfoProduit["mnt_max"])
                return  new ErrorObj(ERR_MNT_MAX_DEPASSE);//on suppose que le montant bloqué sur le compte est intégré au solde
        }        
    
        // Passage des écritures comptables
        $comptable = array();
    
        //Opération 508 : débit compte correspondant / crédit compte client
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
    
        
        $comptesCompensation = $CompteObj->getComptesCompensation($data['id_correspondant']);
        $cptes_substitue["cpta"]["debit"] = $comptesCompensation['compte'];
            
        $operation = 508;
                
        // Credit le produit epargne
        $cptes_substitue["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($InfoCpte['id_cpte']);
        
        if ($cptes_substitue["cpta"]["credit"] == NULL) {           
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
    
        $cptes_substitue["int"]["credit"] = $InfoCpte['id_cpte'];
        
        /*
        if (isset($CHANGE)) {
            $myErr = change($CHANGE['devise'],$data['devise'], $CHANGE['cv'], $data['montant'], $operation, $cptes_substitue, $comptable, $CHANGE["dest_reste"], $CHANGE["comm_nette"], $CHANGE["taux"],NULL,$infos_sup);
        } else {             
            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $data['devise'], NULL, NULL, $infos_sup);                
                  
        }
        */
        
        $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $data['devise'], NULL, NULL, $infos_sup);
               
        if ($myErr->errCode != NO_ERR) {            
            return $myErr;
        }           
       
        //Opération 150 (perception des frais de dépôt) : débit compte client / frais de dépôt
        if ($InfoProduit["frais_depot_cpt"] > 0 ) 
        {
            // Passage des écritures comptables
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $montant_remboursement -= $InfoProduit["frais_depot_cpt"];

            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($InfoCpte['id_cpte']);
            
            if ($cptes_substitue["cpta"]["debit"] == NULL) {                
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];           
            
            if ($InfoProduit['devise'] != $global_remote_monnaie) {
                //$myErr = effectueChangePrivate($InfoProduit['devise'],$global_monnaie,$InfoProduit['frais_depot_cpt'],150,$cptes_substitue,$comptable);
                $myErr = $DeviseObj->effectueChangePrivate($InfoProduit['devise'],$global_remote_monnaie,$InfoProduit['frais_depot_cpt'],150,$cptes_substitue,$comptable);
            } else {
                //$myErr = passageEcrituresComptablesAuto(150, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue);
                $myErr = $CompteObj->passageEcrituresComptablesAuto(150, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue);
            }
            if ($myErr->errCode != NO_ERR) {               
                return $myErr;
            }
        }

        //Opération 188 (frais forfaitaire transactionnel SMS) : débit compte client / frais sms
        $listeTypeOpt = $EpargneObj->getListeTypeOptDepPourPreleveFraisSMS();

        if (in_array($operation, $listeTypeOpt)) {
            $SMSTransactionnel = $EpargneObj->getTarificationDatas();
            $clientSMS = $ClientObj->checkIfClientAbonnerSMS($global_id_client);
            $operation_frais = 188;

            if (!empty($SMSTransactionnel) && $clientSMS == true) {
                // Passage des écritures comptables
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();
                $montant_remboursement -= $SMSTransactionnel["valeur"];
                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($InfoCpte['id_cpte']);

                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }

                $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];

                if ($InfoProduit['devise'] != $global_remote_monnaie) {
                    //$myErr = effectueChangePrivate($InfoProduit['devise'],$global_monnaie,$InfoProduit['frais_depot_cpt'],150,$cptes_substitue,$comptable);
                    $myErr = $DeviseObj->effectueChangePrivate($InfoProduit['devise'],$global_remote_monnaie,$SMSTransactionnel["valeur"],$operation_frais,$cptes_substitue,$comptable);
                } else {
                    //$myErr = passageEcrituresComptablesAuto(150, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue);
                    $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_frais, $SMSTransactionnel["valeur"], $comptable, $cptes_substitue);
                }
                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            }
        }

        //Opération 156 (perception des commissions en operation en deplace) : débit compte client / commission en deplace
        if ($data['commission_op_deplace'] > 0 )
        {
            // Passage des écritures comptables
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $montant_remboursement -= $data['commission_op_deplace'];
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($InfoCpte['id_cpte']);

            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];

            if ($InfoProduit['devise'] != $global_remote_monnaie) {
                //$myErr = effectueChangePrivate($InfoProduit['devise'],$global_monnaie,$InfoProduit['frais_depot_cpt'],150,$cptes_substitue,$comptable);
                $myErr = $DeviseObj->effectueChangePrivate($InfoProduit['devise'],$global_remote_monnaie,$data['commission_op_deplace'],156,$cptes_substitue,$comptable);
            } else {
                //$myErr = passageEcrituresComptablesAuto(150, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue);
                $myErr = $CompteObj->passageEcrituresComptablesAuto(156, $data['commission_op_deplace'], $comptable, $cptes_substitue);
            }
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
         
        
        /* Pas de frais de virement pour virement AU GUICHET */
        
        /* Perception d'éventuels frais de virement. Par exemple virement des salaires par dépot par lot  */       
        /*
        if ($frais_virement !=NULL ) {
            // Passage des écritures comptables
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
    
            $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($InfoCpte['id_cpte']);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }
    
            $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];
            if ($InfoProduit['devise']!=$global_monnaie)
                $myErr = effectueChangePrivate($InfoProduit['devise'],$global_monnaie,$frais_virement,151,$cptes_substitue,$comptable);
            else
                $myErr = passageEcrituresComptablesAuto(151, $frais_virement, $comptable, $cptes_substitue);
    
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
    
        }
        */
        
        $fonction = 93; // Dépôt en déplacé

        /*
        $login  = $global_id_agence. ' - ' . $global_nom_login;
        //$infos_his = "Dépôt en déplacé id cheque - " .$id_cheque;        
        $infos_his = "Dépôt en déplacé";
        */
        
        $login = $login_remote;
        $infos_his = 'agc='.$global_id_agence . ' - login=' . $global_nom_login;
        
        $MyError = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], $infos_his, $login, date("r"), $comptable, $data_his_ext, NULL, $infos_sup);

        unset($EpargneObj);
        unset($HistoriqueObj);
        unset($CompteObj);

        if ($MyError->errCode != NO_ERR) {           
            return $MyError;
        }

        $id_his = $MyError->param['id_his'];
    	$id_ecriture = $MyError->param['id_ecriture'];    	

    	return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }

    /*
     * fonction qui retourne la liste des transaction de compte avec une limite des transactions
     * @ param int $id_cpte: compte client
     * @ param int #numero: limit de transaction
     *
     * @return array $result: array des transactions*/
    public function getMvtsCpteClientParNumero($id_cpte, $numero){

        global $global_remote_id_agence;
        $sql = "SELECT 	A.id_his,A.infos,A.type_fonction,A.date,b.libel_ecriture,T .traduction AS libel_operation, b.type_operation, b.info_ecriture, C.sens, C.montant
                  FROM	ad_his A INNER JOIN ad_ecriture b USING (id_ag, id_his) INNER JOIN ad_mouvement C USING (id_ag, id_ecriture)
                  INNER JOIN ad_traductions T ON T .id_str = b.libel_ecriture
                  INNER JOIN ad_agc ag on ag.id_ag = a.id_ag and ag.langue_systeme_dft = T.langue ";
        $sql .= "WHERE  (a.id_ag = $global_remote_id_agence) AND c.cpte_interne_cli = $id_cpte ";

        $sql .= " ORDER BY b.id_ecriture DESC LIMIT $numero";

        $result = $this->getDbConn()-> prepareFetchAll ($sql);

        if ($result === FALSE || count($result) == 0) {
            return new ErrorObj(ERR_DB_SQL, $result);
        }

        return $result;
    }

    /*
 * fonction qui retourne la liste des transaction de compte durant une certaine periode
 * @ param int $id_cpte: compte client
 * @ param date $date_debut
 * @ param date $date_fin
 *
 * @return array $result: array des transactions*/
    public function getMvtsCpteClientParDates($id_cpte, $date_debut,$date_fin){

        global $global_remote_id_agence;

        $sql = "SELECT 	A.id_his,A.infos,A.type_fonction,A.date,b.libel_ecriture,T .traduction AS libel_operation, b.type_operation, b.info_ecriture, C.sens, C.montant
                  FROM	ad_his A INNER JOIN ad_ecriture b USING (id_ag, id_his) INNER JOIN ad_mouvement C USING (id_ag, id_ecriture)
                  INNER JOIN ad_traductions T ON T .id_str = b.libel_ecriture
                  INNER JOIN ad_agc ag on ag.id_ag = a.id_ag and ag.langue_systeme_dft = T.langue ";
        $sql .= "WHERE  (a.id_ag = $global_remote_id_agence) AND c.cpte_interne_cli = $id_cpte   AND (date(a.date) BETWEEN '$date_debut' AND '$date_fin' )";

        $sql .= "ORDER BY b.id_ecriture DESC ";

        $result = $this->getDbConn()-> prepareFetchAll ($sql);

        if ($result === FALSE || count($result) == 0) {
            return new ErrorObj(ERR_DB_SQL, $result);
        }

        return $result;
    }

    /*
     * fonction qui calcule le solde d'un compte d'épargne à une date
     *
     * @param int $NumCpte: numéro compte client
     * @param date $date
     * */
    public  function  calculeSoldeCpteInterness($NumCpte, $date){
        global   $global_remote_id_agence;

        $sql = " select calculesoldecpteinterne($NumCpte, date('$date'), $global_remote_id_agence)";

        $result = $this->getDbConn()-> prepareFetchAll ($sql);

        if ($result === FALSE) {
            return new ErrorObj(ERR_DB_SQL, $result);
        }

        //si le numéro de chèque n'est pas remonter

        if ($result[0]['calculesoldecpteinterne'] != null) {
            return $result[0]['calculesoldecpteinterne'];
        }
        else{
            return null;
        }
    }
    
    /** Getters & Setters */
    public function getIdProduit() {
        return $this->_id_produit;
    }

    public function setIdProduit($value) {
        $this->_id_produit = $value;
    }

    public function getInfoCpte() {
        return $this->_info_cpte;
    }

    public function setInfoCpte($value) {
        $this->_info_cpte = (array) $value;
    }

    public function getChequeno($id_his,$libel_ecriture,$info_ecriture=null){

        $sql = " SELECT ext.num_piece from
            ad_his_ext ext inner join ad_his his
            on ext.id=his.id_his_ext
            where id_his= :id_his and his.id_ag = :id_agence ";
        $param = array(":id_his" => $id_his, ":id_agence" => $this->getIdAgence());

        $result = $this->getDbConn()->prepareFetchRow($sql, $param);

        if ($result === FALSE || count($result) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if (count($result) == 0) {
            if ($info_ecriture != null && trim($info_ecriture)!="") {
                return $libel_ecriture." No: ".$info_ecriture;
            } else {
                return $libel_ecriture;
            }
        }

        $res = $libel_ecriture." No: ".$result["num_piece"];
        return $res;
    }
}
