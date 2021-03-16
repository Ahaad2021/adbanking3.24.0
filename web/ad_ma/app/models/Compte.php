<?php

/**
 * Description de la classe Compte
 *
 * @author danilo
 */
require_once 'ad_ma/app/models/BaseModel.php';

class Compte extends BaseModel {

    /** Properties */
    private $_id_client;
    private $_id_compte;

    public function __construct(&$dbc, $id_agence=NULL) {        
        parent::__construct($dbc, $id_agence); 
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Renvoie le solde du compte client
     *
     * @param $id_cpte
     * @return mixed
     */
    public function getSoldeCpteCli($id_cpte){
        $sql = "SELECT solde FROM ad_cpt WHERE id_ag = :id_agence AND id_cpte = :id_cpte";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_cpte' => $id_cpte);

        $result = $this->getDbConn()->prepareFetchColumn($sql, $param_arr);

        if ($result===FALSE || count($result) == 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Renvoie l'ID du compte de base d'un client donné.
     *
     * @param int $id_client
     * 
     * @return int L'identifiant du compte de base du client ou NULL si le client n'existe pas ou ne possède pas de comtpe de base.
     */
    public function getBaseAccountID($id_client) {

        $sql = "SELECT id_cpte_base FROM ad_cli WHERE id_ag = :id_agence AND id_client = :id_client";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);

        $result = $this->getDbConn()->prepareFetchColumn($sql, $param_arr);

        if ($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Renvoie un tableau associatif avec toutes les données du compte
     *
     * Les données retournées sont une synthèse cumulative de celles du produit et celles du compte lui-même,
     * en donnant la priorité aux données venant du produit.
     * 
     * @param int $id_compte
     *
     * @return array NULL si le compte n'existe pas, le tableau des données sinon.
     */
    public function getAccountDatas($id_compte) {

        if (($id_compte == NULL) || ($id_compte == '')) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Le numéro du compte n'est pas renseigné")));
        } else {
            // Attention ! Laisser les tables dans cet ordre car devise apparait 2 fois et c'est celui de ad_cpt qui a précédence
            $sql = "SELECT * FROM adsys_produit_epargne p, ad_cpt c WHERE c.id_ag = :id_agence AND c.id_ag = p.id_ag AND c.id_prod = p.id AND c.id_cpte = :id_cpte";
            $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_cpte' => $id_compte);
        }

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

        if ($result===FALSE || count($result) == 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    
    /**
     * Renvoie le numero de compte complet du client
     *
     * @param int $id_compte
     */
    public function getClientAccount($id_compte) {
    
        if (($id_compte == NULL) || ($id_compte == '')) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Le numéro du compte n'est pas renseigné")));
        } else {
            // Attention ! Laisser les tables dans cet ordre car devise apparait 2 fois et c'est celui de ad_cpt qui a précédence
            $sql = "SELECT c.num_complet_cpte FROM adsys_produit_epargne p, ad_cpt c 
                    WHERE c.id_ag = :id_agence AND c.id_ag = p.id_ag 
                    AND c.id_prod = p.id AND c.id_cpte = :id_cpte";
            
            $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_cpte' => $id_compte);
        }
    
        $result = $this->getDbConn()->prepareFetchColumn($sql, $param_arr);
    
        if ($result===FALSE || count($result) == 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        return $result;
    }
    
    
    /**
     * Renvoie le compte comptable associé à un guichet
     * 
     * @param int $id_gui
     *
     * @return string
     */
    public function getCompteCptaGui($id_gui) {

        if (($id_gui == null) or ($id_gui == '')) {
            erreur("getCompteCptaGui", sprintf(_("Le numéro du guichet n'est pas renseigné.")));
        } else {
            $sql = "SELECT cpte_cpta_gui ";
            $sql .= "FROM ad_gui  ";
            $sql .= "WHERE id_ag = :id_agence AND id_gui = :id_gui";
        }

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_gui' => $id_gui);

        $result = $this->getDbConn()->prepareFetchColumn($sql, $param_arr);

        if ($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Renvoie le compte comptable associé à un compte client donné.
     *
     * Dans le cas d'un compte d'épargne nantie, on remonte jusqu'au produit de crédit.
     * @param int $id_cpte_cli Id du compte client associé
     * 
     * @return text Numéro du compte comptable associé
     */
    public function getCompteCptaProdEp($id_cpte_cli) {

        if (($id_cpte_cli == null) or ($id_cpte_cli == '')) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Le compte interne du client n'est pas renseigné.")));
        } else {
            $sql = "SELECT b.id, b.cpte_cpta_prod_ep ";
            $sql .= "FROM ad_cpt a, adsys_produit_epargne b  ";
            $sql .= "WHERE b.id_ag = :id_agence AND b.id_ag = a.id_ag AND a.id_prod = b.id AND a.id_cpte = :id_cpte";
        }

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_cpte' => $id_cpte_cli);

        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if($result===FALSE || count($result)<=0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Aucun compte associé. Veuillez revoir le paramétrage"));
        }

        $row = $result[0];

        if ($row['id'] == 4) { // Cas particulier du compte d'épargne nantie
            $sql = "SELECT cpte_cpta_prod_cr_gar from adsys_produit_credit a, ad_dcr b where b.id_ag = " . $this->getIdAgence() . " AND b.id_ag = a.id_ag AND a.id = b.id_prod AND ";
            $sql .= "b.id_doss = (SELECT distinct(id_doss) FROM ad_gar WHERE id_ag = " . $this->getIdAgence() . " AND gar_num_id_cpte_nantie = $id_cpte_cli)";

            $cpte_cpta_prod_cr_gar = $this->getDbConn()->prepareFetchColumn($sql);

            if ($cpte_cpta_prod_cr_gar===FALSE) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }

            return $cpte_cpta_prod_cr_gar;
        } else {
            return $row["cpte_cpta_prod_ep"];
        }
    }

    /**
     * Verifie si le cheque n'est pas mis en opposition ou déjà encaissé.
     *
     * @param int $a_num_cheque L'identifiant du cheque
     * @param int $a_num_cpte L'identifiant du compte
     * 
     * @return ErrorObj Un ErrorObj avec comme paramètre true si tout c'est bien passé sinon  un array contenant la raison .
     */
    public function valideCheque($a_num_cheque, $a_num_cpte = NULL) {
        global $adsys;
        $result = self::getChequierByNumCheque($a_num_cheque, $a_num_cpte);
        $msg_numCheque = _(" numéro chèque ");
        $msg_etatCheque = _(" Etat du chèque ");

        if ($result->errCode != NO_ERR) {
            return $result;
        } elseif (count($result->param) == 1) {
            $chequier = $result->param[0];
            
            //verifier que le chéquier est actif (remis au client)
            if ($chequier['statut'] != 1) {
                $param = array();
                $param[$msg_etatCheque] = $adsys['adsys_etat_chequier'][$chequier['etat_chequier']];
                return new ErrorObj(ERR_CHEQUIER_INACTIF, $param);
            }
            //verifier que le cheque n'est pas utilisé
            $result = self::getCheque($a_num_cheque);
            if ($result->errCode != NO_ERR) {
                return $result;
            } elseif (count($result->param) > 0) {
                $param = array();
                $param[$msg_etatCheque] = $adsys["adsys_etat_cheque"][$result->param[0]['etat_cheque']];
                $param[$msg_numCheque] = $result->param[0]['id_cheque'];
                if ($result->param[0]['is_opposition'] == 't') {
                    return new ErrorObj(ERR_CHEQUE_OPPOSITION, $param);
                } else {
                    return new ErrorObj(ERR_CHEQUE_USE, $param);
                }
            }
            //Fin verifier que le cheque n'est pas utilisé
        } else {
            $result->param[$msg_numCheque] = $a_num_cheque;
            return new ErrorObj(ERR_NO_CHEQUE, $result->param);
        }
        return new ErrorObj(NO_ERR, $chequier['id_chequier']);
    }

    /**
     * Renvoie les informations d'un chequier en donnant le numero du chèque .
     *
     * @param int $a_num_cheque L'identifiant du cheque
     * @param int $a_num_cpte L'identifiant du compte
     * 
     * @return un array contenant la liste des chequiers .
     */
    public function getChequierByNumCheque($a_num_cheque, $a_num_cpte = NULL) {

        $sql = "SELECT * from ad_chequier WHERE id_ag = " . $this->getIdAgence() . " AND (num_first_cheque <= '".(int)$a_num_cheque."' AND num_last_cheque>='".(int)$a_num_cheque."') ";
        if (!is_null($a_num_cpte)) {
            $sql.=" AND id_cpte =$a_num_cpte ";
        }

        $result = $this->getDbConn()->prepareFetchAll($sql);

        /*
        if($result===FALSE || count($result)<0) {
            return new ErrorObj(ERR_DB_SQL, (string) $this->getDbConn()->getError());
        }
        */

        return new ErrorObj(NO_ERR, $result);
    }

    /**
     * Renvoie les informations d'un chèque dont l'idenfiant est  passé en paramètre.
     *
     * @param int $a_num_cheque L'identifiant du cheque
     * 
     * @return un array contenant les informations du chéque .
     */
    public function getCheque($a_num_cheque) {

        $sql = "SELECT * from ad_cheque WHERE id_ag = " . $this->getIdAgence() . " AND id_cheque = ".$a_num_cheque;

        $result = $this->getDbConn()->prepareFetchAll($sql);

        if($result===FALSE || count($result)<0) {
            return new ErrorObj(ERR_DB_SQL, (string) $this->getDbConn()->getError());
        }

        return new ErrorObj(NO_ERR, $result);
    }

    /**
     * Renvoie les comptes comptables associés à un correspondant bancaire.
     * 
     * @param int $idCorrespondant : ID du correspondant
     * 
     * @return array Renvoie un tableau de la forme ("compte" => Compte du correspondant, "debit" => Compte d'ordre débiteur, "credit" => Compte d'ordre créditeur)
     */
    public function getComptesCompensation($idCorrespondant) {

        $sql = "SELECT cpte_bqe, cpte_ordre_deb, cpte_ordre_cred FROM adsys_correspondant WHERE id_ag = :id_agence AND id = :id_correspondant;";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_correspondant' => $idCorrespondant);        
        $result = $this->getDbConn()->prepareFetchAll($sql, $param_arr);
        
        if($result===FALSE || count($result)<=0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $row = $result[0];
        $cptes = array();
        $cptes['compte'] = $row['cpte_bqe'];
        $cptes['debit'] = $row['cpte_ordre_deb'];
        $cptes['credit'] = $row['cpte_ordre_cred'];

        return $cptes;
    }

    /**
     * Retourne la date passée en paramètre augmentée ou diminuée d'un certain nombre de jours ouvrables,
     * en fonction des paramètres du produit épargne associé au compte.
     * 
     * @param int $compte Identifiant du compte épargne
     * @param string $sens Sens de l'opération 'd' pour débit, 'c' pour crédit, il déterminera si on retranche ou si l'on rajoute des jours.
     * @param string $date_compta La date de comptabilisation de l'opération, au format jj/mm/aaaa
     * 
     * @return string $date_valeur La date valeur calculée, au format jj/mm/aaaa
     */
    public function getDateValeur($a_compte, $a_sens, $a_date_compta) {

        if (!isset($a_compte))
            return $a_date_compta;
        $info_compte = self::getAccountDatas($a_compte);

        // Init class
        $EpargneObj = new Epargne($this->getDbConn(), $this->getIdAgence());

        $info_produit = $EpargneObj->getProdEpargne($info_compte["id_prod"]);

        // Destroy object
        unset($EpargneObj);

        $decalage_debit = $info_produit["nbre_jours_report_debit"];
        $decalage_credit = $info_produit["nbre_jours_report_credit"];

        $nombre_jours = 0;
        if ($a_sens == 'c')
            $nombre_jours = $decalage_credit;
        if ($a_sens == 'd')
            $nombre_jours = $decalage_debit * (-1);

        $annee = substr($a_date_compta, 6, 4);
        $mois = substr($a_date_compta, 3, 2);
        $jour = substr($a_date_compta, 0, 2);

        $date_valeur = jour_ouvrable($jour, $mois, $annee, $nombre_jours);
        return $date_valeur;
    }

    /**
     * Ajout d'un cheque encaissé, volé ou perdu.
     *
     * @param array a_data tableau contenant les informations du cheque à ajouter
     * 
     * @return ErrorObj Un ErrorObj.
     */
    public function insertCheque($a_data, $id_cpte = NULL) {

        $a_data["id_ag"] = $this->getIdAgence();
        $result = self::valideCheque($a_data['id_cheque'], $id_cpte);
        if ($result->errCode != NO_ERR) {
            return $result;
        }
        $a_data['id_chequier'] = $result->param;

        $sql = buildInsertQuery("ad_cheque", $a_data);

        $result = $this->getDbConn()->execute($sql);

        if ($result===FALSE) {
            return new ErrorObj(ERR_DB_SQL, (string) $this->getDbConn()->getError());
        }

        return new ErrorObj(NO_ERR, array("id_chequier" => $a_data['id_chequier']));
    }
    
    /**
     * Supprimer un cheque encaissé, volé ou perdu.
     *
     * @param int $id_cheque
     * 
     * @return ErrorObj Un ErrorObj.
     */
    public function deleteCheque($id_cheque) {

        $sql = "DELETE FROM ad_cheque WHERE id_ag=:id_agence AND id_cheque=:id_cheque";
        
        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_cheque' => $id_cheque);

        $result = $this->getDbConn()->execute($sql, $param_arr);

        if ($result===FALSE) {
            return new ErrorObj(ERR_DB_SQL, (string) $this->getDbConn()->getError());
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     * 
     * Fonction qui met à jour le solde d'un compte de comptabilité.
     * On devrait décider qu'on ne peut pas mouvementer un compte collectif mais plutôt un sous-ompte. Mais, pour le moment, on suppose que chaque compte a un solde indépendant et pour obtenir le solde d'un compte collectif, il faudra faire la somme  des sous-comptes.
     * Au niveau de la DB, on devrait faire des CHECK CONSTRAINTS pour s'assurer en fonction du sens du compte que :
     * - Un compte créditeur ne peut devenir négatif
     * - Un compte débiteur ne peut devenir positif
     * 
     * Il faudrait trouver un moyen de récupérer l'erreur interne générée par un trigger
     * 
     * Pour l'instant, on passe par PHP pour implémenter les contraintes d'intégrité
     * cpte est le n° compte comptable
     * sens est SENS_DEBIT (signe -) ou SENS_CREDIT (signe +)
     * montant à mouvementer sur le compte c'est en valeur absolue
     * devise = Devise du mouvement
     * 
     * FIXME : tester si le compte qu'on veut mettre à jour n'est pas fermé, bloqué, etc
     * 
     * echo "On met à jour le compte comptable $cpte";
     * 
     * @param type $cpte
     * @param type $sens
     * @param type $montant
     * @param type $devise
     * 
     * @return ErrorObj
     */
    public function setSoldeComptable($cpte, $sens, $montant, $devise) {

        global $error;

        //Quel est le solde du compte
        $sql = "SELECT solde, sens_cpte, cpte_centralise, devise ";
        $sql .= "FROM ad_cpt_comptable ";
        $sql .= "WHERE id_ag = " . $this->getIdAgence() . " AND num_cpte_comptable = '$cpte' ";
        $sql .= "FOR UPDATE OF ad_cpt_comptable;";

        $row = $this->getDbConn()->prepareFetchRow($sql);

        if ($row===FALSE || count($row) == 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Le compte comptable lié n existe pas")); // "Compte inconnu"
        }

        $solde = $row["solde"];
        $sens_cpte = $row["sens_cpte"];
        $cpte_centralise = $row["cpte_centralise"];
        $devise_cpte = $row["devise"];

        // #514 : Arrondir le montant a passer :
        $montant = arrondiMonnaiePrecision($montant, $devise);
        
        //vérifier si le nouveau solde est conforme au sens du compte

        if ($sens == SENS_DEBIT) {
            $solde = $solde - $montant;
        } else if ($sens == SENS_CREDIT) {
            $solde = $solde + $montant;
        }
        $solde = round($solde, EPSILON_PRECISION);
        

        if ($sens_cpte == 1) {
            //cas des comptes naturellement débiteurs : le solde ne peut pas devenir positif
            if ($solde > 0) {
                return new ErrorObj(ERR_CPTE_DEB_POS, $cpte); // "Compte $cpte debiteur va devenir positif !"
            }
        } else if ($sens_cpte == 2) {
            //cas des comptes naturellement créditeurs : le solde ne peut pas devenir négatif
            if ($solde < 0) {
                return new ErrorObj(ERR_CPTE_CRED_NEG, $cpte); // "Le compte $cpte crediteur va devenir negatif !"
            }
        }

        /* On ne mouvemente pas un compte centralisateur
          if (isCentralisateur($cpte))
          {
          $dbHandler->closeConnection(false);
          return new  ErrorObj(ERR_CPT_CENTRALISE, "Compte $cpte"); // "Tentative de mouvementer le compte centralisateur $cpte"
          }
         */

        // Vérifie que le mouvement a bien lieu dans la meme devise
        if ($devise_cpte != $devise) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Tentative de mouvementer le compte dans une autre devise " . $devise_cpte . " != " . $devise));
        }

        //mettre a jour solde courant et solde centralise
        $sql = "UPDATE ad_cpt_comptable ";
        $sql .= "SET solde = $solde ";
        $sql .= "WHERE id_ag=" . $this->getIdAgence() . " AND num_cpte_comptable = '$cpte';";

        $result = $this->getDbConn()->execute($sql);

        if ($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     * 
     * Fonction qui met à jour le solde d'un compte client dans ad_cpt suite à une opération financière.
     * Il faut vérifier que le solde ne peut pas être négatif sauf pour un compte dont le id produit est celui du type de compte de crédit.
     * 
     * Important : on ne vérifie pes les soldes mini, c'est à l'appelant de le faire
     * 
     * IN : $id_cpte = identifiant dans ad_cpt
     * $sens = SENS_DEBIT => le compte interne est débité (signe de l'opération est -)
     * SENS_CREDIT => le compte interne est crédité (signe de l'opération est +)
     * $montant = Montant du transfert sur le compte interne
     * 
     * OUT : Objet Erreur
     * 
     * @param type $id_cpte
     * @param type $sens
     * @param type $montant
     * @param type $devise
     * 
     * @return \ErrorObj
     */
    public function setSoldeCpteCli($id_cpte, $sens, $montant, $devise) {

        // Init class
        $CreditObj = new Credit($this->getDbConn(), $this->getIdAgence());

        $id_prod_credit = $CreditObj->getCreditProductID();

        // Destroy object
        unset($CreditObj);

        $sql = "SELECT solde, id_prod, devise ,mnt_bloq,mnt_min_cpte,decouvert_max ";
        $sql .= "FROM ad_cpt ";
        $sql .= "WHERE id_ag = " . $this->getIdAgence() . " AND id_cpte = $id_cpte ";
        $sql .= "FOR UPDATE OF ad_cpt;";

        $result = $this->getDbConn()->prepareFetchAll($sql);

        if($result===FALSE || count($result)<=0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        //FIXME : vérifier si on a trouvé quelque chose

        $row = $result[0];

        $solde = $row["solde"];

        $ProdCpte = $row["id_prod"];
        $devise_cpte = $row["devise"];

        if ($sens == SENS_DEBIT)
            $solde = $solde - $montant;
        elseif ($sens == SENS_CREDIT)
            $solde = $solde + $montant;

        $solde = round($solde, EPSILON_PRECISION);

        //verifier de quel type de compte client il s'agit : compte d'epargne ou de credit
        if ($ProdCpte == $id_prod_credit) {
            //Si compte de crédit, le solde doit être débiteur et ne peut devenir positif
            if ($solde > 0) {
                return new ErrorObj(ERR_CPTE_DEB_POS, _("compte client") . " $id_cpte");
            }
        } else {
            $mnt_bloq = $row["mnt_bloq"];
            $mnt_min_cpte = $row["mnt_min_cpte"];
            $decouvert_max = $row["decouvert_max"];
            $solde1 = $solde + $decouvert_max;
            if ($solde1 < 0) {
                return new ErrorObj(ERR_CPTE_CRED_NEG, _("compte client") . " $id_cpte");
            }
        }

        // Vérification sur la devise
        if ($devise_cpte != $devise) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Tentative de mouvementer le compte client $id_cpte dans la devise $devise"
        }

        //mettre à  jour le solde
        $sql = "UPDATE ad_cpt SET solde = $solde WHERE id_ag=" . $this->getIdAgence() . " AND id_cpte=$id_cpte;";

        $result = $this->getDbConn()->execute($sql);

        if ($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return new ErrorObj(NO_ERR);
    }

    /**
     * Fonction qui permet de faire la comptabilisation des écritures de ADbanking, elle construit un tableau qu'on passera à ajout_historique
     * 
     * @param int $type_oper Numéro de l'opération, elle peut donnée directement ou déduite dans le schemas comptable
     * @param int $montant Montant de la transaction :Comme on a un seul débit et un seul crédit, le montant est le même des 2 côtés du mouvement
     * @param array $comptable_his tableau passé par référence, il va contenir l'historique des comptes à debiter et à crediter dans le cadre de l'appel
     * @param array $array_cptes tableau utilisé si on doit faire une substitution : (
     * - "cpta" => array("debit" => compte comptable à débiter,"credit" => compte comptable à  créditer)
     * - "int"  => array("debit" => compte interne à  débiter,"credit" => compte interne à  créditer) )
     * L'array "cpta" permet de passer les comptes comptables à subsituer au débit ou au crédit.
     * L'array "int" permet de passer le compte interne (ad_cpt) si la transaction implique un compte client.
     * Les 2 arrays sont indépendants
     * 
     * @return ErrorObj Objet erreur
     */
    public function passageEcrituresComptablesAuto($type_oper, $montant, &$comptable_his, $array_cptes = NULL, $devise = NULL, $date_compta = NULL, $info_ecriture = NULL, $infos_sup = NULL) {

        global $global_remote_id_exo;
        global $global_multidevise;

        $mouvements = array();
        //verifier s'il faut substituer des comptes
        if (isset($array_cptes)) {
            //FIXME : verifier que le vecteur a au plus 2 lignes (1 debit et 1 credit)
            //lire chaque element du vecteur
            foreach ($array_cptes as $key => $value) {
                //prendre les comptes a substituer
                if ($key == "cpta") { //il existe des comptes comptables a substituer
                    foreach ($value as $key2 => $value2)
                        if ($key2 == "debit")
                            $cpte_debit_sub = $value2;
                        elseif ($key2 == "credit")
                            $cpte_credit_sub = $value2;
                }

                if ($key == "int") { //il existe des comptes internes a renseigner
                    foreach ($value as $key2 => $value2)
                        if ($key2 == "debit")
                            $cpte_int_debit = $value2;
                        elseif ($key2 == "credit")
                            $cpte_int_credit = $value2;
                }
            }
        }

        //FIXME : gérer les frais en attente
        //Recuperer les infos sur l'operation
        // Init class
        $ComptaObj = new Compta($this->getDbConn(), $this->getIdAgence());

        $InfosOperation = array();
        $MyError = $ComptaObj->getOperations($type_oper);

        if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
            return $MyError;
        } else {
            $InfosOperation = $MyError->param;
        }

        // comptes au débit et crédit
        $DetailsOperation = array();

        $MyError = $ComptaObj->getDetailsOperation($type_oper);

        if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
            return $MyError;
        } else {
            $DetailsOperation = $MyError->param;
        }

        // Recherche du dernier élément du tableau
        end($comptable_his);
        $tmparr = current($comptable_his);
        $last_libel_oper = $tmparr["libel"];

        // Init class
        $HistoriqueObj = new Historique($this->getDbConn(), $this->getIdAgence());

        if ($last_libel_oper == $type_oper)
            $newID = $HistoriqueObj->getLastIdOperation($comptable_his);
        else
            $newID = $HistoriqueObj->getLastIdOperation($comptable_his) + 1;

        // Destroy object
        unset($HistoriqueObj);

        //Changer le libellé de l'opération, si autre libellé
        if ($infos_sup["autre_libel_ope"] != NULL)
            $InfosOperation["libel"] = $infos_sup["autre_libel_ope"];

        //FIXME : ici ça marche parce qu'on a 1 débit et 1 crédit
        $comptable = array();

        // Choix du journal ,cela va dependre des comptes au débit et au crédit
        //Compte comptable à debiter

        if (isset($cpte_debit_sub))
            $cpte_debit = $cpte_debit_sub;
        else
            $cpte_debit = $DetailsOperation["debit"]["compte"];

        // Si on a pas de compte comptable, il y a eu un problème dans le paramétrage des opérations :
        if (!isset($cpte_debit)) {
            return new ErrorObj(ERR_NO_ASSOCIATION, sprintf(_("Compte au débit de l'opération %s"), $type_oper));
        }

        //Compte comptable à crediter
        if (isset($cpte_credit_sub))
            $cpte_credit = $cpte_credit_sub;
        else
            $cpte_credit = $DetailsOperation["credit"]["compte"];

        // Si on a pas de compte comptable, il y a eu un problème dans le paramétrage des opérations :
        if (!isset($cpte_credit)) {
            return new ErrorObj(ERR_NO_ASSOCIATION, sprintf(_("Compte au crédit de l'opération %s"), $type_oper));
        }

        // Si multidevise, vérifie que l'écriture peut avoir lieu        
        if ($global_multidevise) {
            if ($devise == NULL) { // Par defaut la devise de reference est utilisee
                global $global_remote_monnaie;
                $devise = $global_remote_monnaie;
            }

            // Init class
            $DeviseObj = new Devise($this->getDbConn(), $this->getIdAgence());

            $cpte_debit_dev = $DeviseObj->checkCptDeviseOK($cpte_debit, $devise);
            if ($cpte_debit_dev == NULL) {
                return new ErrorObj(ERR_DEVISE_CPT, _("Devise") . " : $devise, " . _("compte debit") . " : $cpte_debit");
            }
            $cpte_credit_dev = $DeviseObj->checkCptDeviseOK($cpte_credit, $devise);
            if ($cpte_credit_dev == NULL) {
                return new ErrorObj(ERR_DEVISE_CPT, _("Devise") . " : $devise, " . _("compte") . " : $cpte_credit");
            }

            // Destroy object
            unset($DeviseObj);

            $cpte_debit = $cpte_debit_dev;
            $cpte_credit = $cpte_credit_dev;

            // Vérifie également que les comptes internes associés s'ils existent sont dans la bonne devise
            if (isset($cpte_int_debit)) {
                $ACC = self::getAccountDatas($cpte_int_debit);
                if ($ACC["devise"] != $devise) {
                    return new ErrorObj(ERR_DEVISE_CPT_INT, _("Devise") . " : $devise, " . _("opération") . " : $type_oper");
                }
            }
            if (isset($cpte_int_credit)) {
                $ACC = self::getAccountDatas($cpte_int_credit);
                if ($ACC["devise"] != $devise) {
                    return new ErrorObj(ERR_DEVISE_CPT_INT, _("Devise") . " : $devise, " . _("opération") . " : $type_oper");
                }
            }
        } else { // En mode unidevise, la devise est toujours la devise de référence
            global $global_remote_monnaie;
            $devise = $global_remote_monnaie;
        }

        // On ne mouvemente pas un compte centralisateur
        if ($ComptaObj->isCentralisateur($cpte_debit)) {
            return new ErrorObj(ERR_CPT_CENTRALISE, _("compte") . " : $cpte_debit");
        }

        if ($ComptaObj->isCentralisateur($cpte_credit)) {
            return new ErrorObj(ERR_CPT_CENTRALISE, _("compte") . " : $cpte_credit");
        }

        $jou_cpte_debit = $ComptaObj->getJournalCpte($cpte_debit);
        $jou_cpte_credit = $ComptaObj->getJournalCpte($cpte_credit);

        // la date comptable doit être dans la période de l'exercice en cours à cours
        $exo_encours = $ComptaObj->getExercicesComptables($global_remote_id_exo);
        $date_debut = pg2phpDate($exo_encours[0]["date_deb_exo"]);
        $date_fin = pg2phpDate($exo_encours[0]["date_fin_exo"]);

        // date comptable
        if ($date_compta == NULL) {
            $date_comptable = date("d/m/Y"); // date du jour
            if (isAfter($date_comptable, $date_fin)) {
                // Init class
                $AgenceObj = new Agence($this->getDbConn());

                $date_comptable = pg2phpDate($AgenceObj->getLastBatch($this->getIdAgence()));

                // Destroy object
                unset($AgenceObj);
            }
        }
        else
            $date_comptable = $date_compta;

        if ((isAfter($date_debut, $date_comptable)) or (isAfter($date_comptable, $date_fin))) {
            return new ErrorObj(ERR_DATE_NON_VALIDE, ". La date n'est pas dans la période de l'exercice. date_debut=" . $date_debut . " - date_fin=" . $date_fin . " - date_comptable=" . $date_comptable);
        }

        //echo "Journal au débit : $jou_cpte_debit et journal au crédit : $jou_cpte_credit<BR>";
        if (($jou_cpte_debit["id_jou"] != $jou_cpte_credit["id_jou"]) && ($jou_cpte_debit["id_jou"] > 1) && ($jou_cpte_credit["id_jou"] > 1)) {
            //Utilisation d'un compte de liaison
            $InfosOperation["jou_debit"] = $jou_cpte_debit ["id_jou"];
            $InfosOperation["jou_credit"] = $jou_cpte_credit ["id_jou"];
            $temp1 = $jou_cpte_debit["id_jou"];
            $temp2 = $jou_cpte_credit["id_jou"];

            //Recuperation du compte de liaison

            $temp["id_jou1"] = $temp1;
            $temp["id_jou2"] = $temp2;

            $temp_liaison = $ComptaObj->getJournauxLiaison($temp);

            if (count($temp_liaison) != 1) {
                return new ErrorObj(ERR_PAS_CPTE_LIAISON);
            }
            $cpte_liaison = $temp_liaison[0]["num_cpte_comptable"];

            // Passages écritures du compte debit au compte de liaison
            $comptable[0]["id"] = $newID;
            $comptable[0]["compte"] = $cpte_debit;
            if (isset($cpte_int_debit))
                $comptable[0]["cpte_interne_cli"] = $cpte_int_debit;
            else
                $comptable[0]["cpte_interne_cli"] = NULL;

            $comptable[0]["date_valeur"] = self::getDateValeur($cpte_int_debit, 'd', $date_comptable);
            $comptable[0]["sens"] = SENS_DEBIT;
            $comptable[0]["montant"] = $montant;
            $comptable[0]["date_comptable"] = $date_comptable;
            $comptable[0]["libel"] = $InfosOperation["libel"];
            $comptable[0]["type_operation"] = $InfosOperation["type_operation"];
            $comptable[0]["jou"] = $InfosOperation["jou_debit"];
            $comptable[0]["exo"] = $global_remote_id_exo;
            $comptable[0]["validation"] = 't';
            $comptable[0]["devise"] = $devise;
            $comptable[0]["info_ecriture"] = $info_ecriture;

            $comptable[1]["id"] = $newID;
            $comptable[1]["compte"] = $cpte_liaison;
            $comptable[1]["cpte_interne_cli"] = NULL;
            $comptable[1]["date_valeur"] = self::getDateValeur($cpte_int_credit, 'c', $date_comptable);
            $comptable[1]["sens"] = SENS_CREDIT;
            $comptable[1]["montant"] = $montant;
            $comptable[1]["date_comptable"] = $date_comptable;
            $comptable[1]["libel"] = $InfosOperation["libel"];
            $comptable[1]["type_operation"] = $InfosOperation["type_operation"];
            $comptable[1]["jou"] = $InfosOperation["jou_debit"];
            $comptable[1]["exo"] = $global_remote_id_exo;
            $comptable[1]["validation"] = 't';
            $comptable[1]["devise"] = $devise;
            $comptable[1]["info_ecriture"] = $info_ecriture;

            // Passages ecritures du compte credit au compte de liaison

            $newID++;
            $comptable[2]["id"] = $newID;
            $comptable[2]["compte"] = $cpte_liaison;
            $comptable[2]["cpte_interne_cli"] = NULL;
            $comptable[2]["date_valeur"] = self::getDateValeur($cpte_int_debit, 'd', $date_comptable);
            $comptable[2]["sens"] = SENS_DEBIT;
            $comptable[2]["montant"] = $montant;
            $comptable[2]["date_comptable"] = $date_comptable;
            $comptable[2]["libel"] = $InfosOperation["libel"];
            $comptable[2]["type_operation"] = $InfosOperation["type_operation"];
            $comptable[2]["jou"] = $InfosOperation["jou_credit"];
            $comptable[2]["exo"] = $global_remote_id_exo;
            $comptable[2]["validation"] = 't';
            $comptable[2]["devise"] = $devise;
            $comptable[2]["info_ecriture"] = $info_ecriture;

            $comptable[3]["id"] = $newID;
            $comptable[3]["compte"] = $cpte_credit;
            if (isset($cpte_int_credit))
                $comptable[3]["cpte_interne_cli"] = $cpte_int_credit;
            else
                $comptable[3]["cpte_interne_cli"] = NULL;
            $comptable[3]["date_valeur"] = self::getDateValeur($cpte_int_credit, 'c', $date_comptable);
            $comptable[3]["sens"] = SENS_CREDIT;
            $comptable[3]["montant"] = $montant;
            $comptable[3]["date_comptable"] = $date_comptable;
            $comptable[3]["libel"] = $InfosOperation["libel"];
            $comptable[3]["type_operation"] = $InfosOperation["type_operation"];
            $comptable[3]["jou"] = $InfosOperation["jou_credit"];
            $comptable[3]["exo"] = $global_remote_id_exo;
            $comptable[3]["validation"] = 't';
            $comptable[3]["devise"] = $devise;
            $comptable[3]["info_ecriture"] = $info_ecriture;
        }

        else {//Ici, on choisit le journal dont l'id > journal principal si un des comptes est associé à ce journal
            $InfosOperation["jou"] = max($jou_cpte_debit ["id_jou"], $jou_cpte_credit ["id_jou"]);

            $comptable[0]["id"] = $newID;
            $comptable[0]["compte"] = $cpte_debit;
            if (isset($cpte_int_debit))
                $comptable[0]["cpte_interne_cli"] = $cpte_int_debit;
            else
                $comptable[0]["cpte_interne_cli"] = NULL;
            $comptable[0]["date_valeur"] = self::getDateValeur($cpte_int_debit, 'd', $date_comptable);
            $comptable[0]["sens"] = SENS_DEBIT;
            $comptable[0]["montant"] = $montant;
            $comptable[0]["date_comptable"] = $date_comptable;
            $comptable[0]["libel"] = $InfosOperation["libel"];
            $comptable[0]["type_operation"] = $InfosOperation["type_operation"];
            $comptable[0]["jou"] = $InfosOperation["jou"];
            $comptable[0]["exo"] = $global_remote_id_exo;
            $comptable[0]["validation"] = 't';
            $comptable[0]["devise"] = $devise;
            $comptable[0]["info_ecriture"] = $info_ecriture;

            $comptable[1]["id"] = $newID;
            $comptable[1]["compte"] = $cpte_credit;
            if (isset($cpte_int_credit))
                $comptable[1]["cpte_interne_cli"] = $cpte_int_credit;
            else
                $comptable[1]["cpte_interne_cli"] = NULL;
            $comptable[1]["date_valeur"] = self::getDateValeur($cpte_int_credit, 'c', $date_comptable);
            $comptable[1]["sens"] = SENS_CREDIT;
            $comptable[1]["montant"] = $montant;
            $comptable[1]["date_comptable"] = $date_comptable;
            $comptable[1]["libel"] = $InfosOperation["libel"];
            $comptable[1]["type_operation"] = $InfosOperation["type_operation"];
            $comptable[1]["jou"] = $InfosOperation["jou"];
            $comptable[1]["exo"] = $global_remote_id_exo;
            $comptable[1]["validation"] = 't';
            $comptable[1]["devise"] = $devise;
            $comptable[1]["info_ecriture"] = $info_ecriture;
        }

        // Destroy object
        unset($ComptaObj);

        $comptable_his = array_merge($comptable_his, $comptable);

        return new ErrorObj(NO_ERR);
    }
    
    
    /**
     * Renvoie les informations concernant un correspondant déterminé : les champs de la table + la devise des comptes (qui doit être la même pour les trois comptes) + le nom de la banque associée.
     * @param int $id_cor Identifiant du correspondant (clé primaire de la table adsys_correspondant)
     * @return array Renvoie un tableau contant les champs de la table adsys_correspondant un champs "nom_banque" et un champs "devise".  Si les devises des trois comptes sont différentes, le champs "devise" retourné est NULL.
     */
    public function getInfosCorrespondant($id_cor)
    {
        $sql = "SELECT a.*, b.nom_banque
                FROM adsys_correspondant a, adsys_banque b
                WHERE a.id_ag = :id_agence AND a.id_ag = b.id_ag AND a.id=:id_cor AND a.id_banque=b.id_banque; ";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_cor' => $id_cor);   
        
        $row = $this->getDbConn()->prepareFetchRow($sql, $param_arr);      
        
        if ($row===FALSE) {
            return NULL;
        }
         
        //recherche de la devise des comptes du correspondant.
        $param = array();
        $param['num_cpte_comptable'] = $row['cpte_bqe'];        
        
        $ComptaObj = new Compta($this->getDbConn(), $this->getIdAgence());
        $cpte_bqe = $ComptaObj->getComptesComptables($param);
        $cpte_bqe = $cpte_bqe[$row['cpte_bqe']];
                
        $param = array();
        $param['num_cpte_comptable'] = $row['cpte_ordre_deb'];
        $cpte_ordre_deb = $ComptaObj->getComptesComptables($param);
        $cpte_ordre_deb = $cpte_ordre_deb[$row['cpte_ordre_deb']];       
        
        $param = array();
        $param['num_cpte_comptable'] = $row['cpte_ordre_cred'];
        $cpte_ordre_cred = $ComptaObj->getComptesComptables($param);
        $cpte_ordre_cred = $cpte_ordre_cred[$row['cpte_ordre_cred']];

        if ($cpte_bqe['devise']==$cpte_ordre_deb['devise'] && $cpte_bqe['devise']==$cpte_ordre_cred['devise']) {
            $row['devise']=$cpte_bqe['devise'];
        } else {
            $row['devise']=NULL;
        }

        return $row;
    }

    /**
     * Renvoie les infos sur une banque. S'il n'y a rien on renvoie NULL
     * @param int $id_bqe
     * @param int $id_ag
     * @return array Renvoie un tableau contant les champs de la banque
     */
    public function getInfosBanque($id_bqe = NULL, $id_ag = NULL)
    {
        $sql = "SELECT * FROM adsys_banque";

        if ($id_bqe != NULL) {
            if ($id_ag == NULL)
                $sql .="  WHERE id_banque = :id_bqe";
            else
                $sql .="  WHERE id_banque = :id_bqe and id_ag = :id_ag ";
        }
        elseif ($id_ag != NULL) $sql .="  WHERE id_ag = :id_ag";

        $sql.= ";";  
        
        $param_arr = array(); 
        
        if($id_bqe != NULL) {
            $param_arr = array(':id_bqe' => $id_bqe);   
        }      
        
        if($id_ag != NULL) {
            $param_arr[':id_ag'] = $this->getIdAgence();
        }
        
        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);
        
        if($results===FALSE || count($results)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
         
        $i = 0;

        foreach ($results as $result) {
            foreach ($result as $key => $value) {
                $DATAS[$i][$key] = $value;
            }
            $i++;
        }
        return $DATAS;
    }

    /**
     * Renvoie les infos sur tous les comptes client <B>ouverts</B> pour un client donné
     * 
     * @param $id_client int Numéro du client
     * 
     * @return Array Un Array indicé par le numéro du compte avec pour chaque compte una rray associatif avec toutes les infos
     */
    public function getAccounts ($id_client) {
        
        $sql = "SELECT b.*, a.* FROM adsys_produit_epargne b, ad_cpt a WHERE a.id_ag = :id_agence AND a.id_ag = b.id_ag AND a.id_prod = b.id AND a.id_titulaire = :id_client AND NOT (a.etat_cpte = 2) ORDER BY a.num_complet_cpte";  //il se peut qu'on veuille avoir les comptes bloqués

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_client' => $id_client);

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $TMPARRAY = array();
        foreach ($results as $key => $cpt) {
            //foreach ($result as $key => $cpt) {
                $TMPARRAY[$cpt["id_cpte"]] = $cpt;
            //}
        }
        return $TMPARRAY;
    }

    public function getCpteComptaInstRemote() {
        $sql = "SELECT cpte_compta_comm_retrait,cpte_compta_comm_depot from ag_param_commission_institution WHERE id_ag = :id_agence;";
        $param_arr = array(':id_agence' => $this->getIdAgence());

        $cpte = $this->getDbConn()->prepareFetchAll($sql,$param_arr);

        if ($cpte === FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $cpte;
    }

    /** Getters & Setters */
    public function getIdClient() {
        return $this->_id_client;
    }

    public function setIdClient($value) {
        $this->_id_client = $value;
    }

    public function getIdCompte() {
        return $this->_id_compte;
    }

    public function setIdCompte($value) {
        $this->_id_compte = $value;
    }

    public function getMvtsCpteClientParNumero($id_client, $id_cpte, $numero) {
        $sql = "SELECT * FROM ad_his a, ad_ecriture b, ad_mouvement c ";
        $sql .= "WHERE (a.id_ag = b.id_ag) and (b.id_ag = c.id_ag) and (a.id_ag = :id_agence) AND c.cpte_interne_cli = :id_cpte AND a.id_his = b.id_his AND c.id_ecriture = b.id_ecriture ORDER BY b.id_ecriture DESC LIMIT :numero";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':id_cpte' => $id_cpte, ":numero" => $numero);

        $results = $this->getDbConn()->prepareFetchAll($sql, $param_arr);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $results;
    }

    /**
     * Renvoie les derniers mouvements de l'historique du client pour un intervalle de dates données.
     * La recherche se fait sur base de la date de l'entrée dans l'historique.
     *
     * @param int $id_client Identifiant client
     * @param int $id_cpte Identifiant compte
     * @param date $date_debut Date de début de recherche
     * @param date $date_fin Date de fin de recherche
     * @return array Liste des mouvements recherchés
     */
    public function getMvtsCpteClientParDates($id_cpte, $date_debut, $date_fin) {
        $sql ="SELECT a.id_his,a.infos,type_fonction,date,libel_ecriture,montant,sens,type_operation,info_ecriture FROM ad_his a, ad_ecriture b, ad_mouvement c WHERE (a.id_ag=b.id_ag) and (b.id_ag=c.id_ag) and (a.id_ag= :id_agence) AND date(a.date) BETWEEN :date_debut AND :date_fin AND c.cpte_interne_cli = :id_cpte AND a.id_his = b.id_his AND c.id_ecriture = b.id_ecriture ORDER BY b.id_ecriture DESC";
        $param = array(":id_agence" => $this->getIdAgence(), ":date_debut" =>$date_debut, ":date_fin" =>$date_fin, ":id_cpte" => $id_cpte);

        $results = $this->getDbConn()->prepareFetchAll($sql, $param);

        if ($results === FALSE || count($results) < 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $results;
    }

    /**
     * Retourne le solde d'un compte interne à la date demandée.
     *
     * @param int $id_cpte L'identifiant du compte interne
     * @param date $date La date à laquelle il faut calculer le solde
     * @return float Le solde du compte interne
     */
    public  function calculeSoldeCpteInterne($id_cpte, $date) {
        // On a comme référence le solde à la date d'aujourd'hui
        $infos_cpte = $this->getAccountDatas($id_cpte);
        $solde = $infos_cpte["solde"];

        // Il faut ensuite remonter jusqu'à la date demandée en annulant les opérations
        // D'abord ajouter le total des opérations au débit
        $sql = "SELECT sum(m.montant) FROM ad_ecriture e, ad_mouvement m, ad_his h
         WHERE e.id_ag = :id_agence AND e.id_ag = m.id_ag AND e.id_ag = h.id_ag
         AND e.id_ecriture = m.id_ecriture AND e.id_his = h.id_his
         AND date(h.date) BETWEEN date(:date) AND date(now())
         AND sens = 'd' AND cpte_interne_cli = :id_cpte";

        $param = array(":id_agence" =>$this->getIdAgence(), ":date" => $date, ":id_cpte" => $id_cpte);

        $results = $this->getDbConn()->prepareFetchRow($sql, $param);
        if ($results !== FALSE || count($results) > 0) {
            $solde += $results["sum"];
        }

        // Ensuite soustraire le total des opérations au crédit
        $sql1 = "SELECT sum(m.montant) FROM ad_ecriture e, ad_mouvement m, ad_his h
         WHERE e.id_ag = :id_agence AND e.id_ag = m.id_ag AND e.id_ag = h.id_ag
         AND e.id_ecriture = m.id_ecriture AND e.id_his = h.id_his
         AND date(h.date) BETWEEN date(:date) AND date(now())
         AND sens = 'c' AND cpte_interne_cli = :id_cpte";

        $param = array(":id_agence" =>$this->getIdAgence(), ":date" => $date, ":id_cpte" => $id_cpte);
        $results = $this->getDbConn()->prepareFetchRow($sql1, $param);

        if ($results !== FALSE || count($results) > 0) {
            $solde -= $results["sum"];
        }

        return $solde;
    }
}
