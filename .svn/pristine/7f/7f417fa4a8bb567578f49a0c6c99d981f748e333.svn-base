<?php

/**
 * Description de la classe Compta
 *
 * @author danilo
 */

require_once 'ad_ma/app/models/BaseModel.php';

class Compta extends BaseModel {

    public function __construct(&$dbc, $id_agence=NULL) {        
        parent::__construct($dbc, $id_agence); 
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * @description: Calcul le niveau d'un compte
     * 
     * @param text Numéro d'un Compte comptables
     * 
     * @return int le niveau du compte comptable
     */
    public function getNiveauCompte($compte) {
        $id_agence = $this->getIdAgence();
        
        // On commence par récupérer le numéro de lot
        $sql = "SELECT getNiveau('$compte', $id_agence)";

        $result = $this->getDbConn()->prepareFetchColumn($sql);

        if ($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Fonction renvoyant les informations sur les comptes comptables définis dans le plan comptable
     * 
     * @param array $fields_values Array permettant de construire une clause WHERE pour le SELECT.
     * Si argument est NULL, on renvoie tous les comptes. L'array a la forme (fieldname=>value recherchée).
     * 
     * @return array On renvoie un tableau de la forme (numéro compte => infos compte)
     */
    public function getComptesComptables($fields_values = NULL, $niveau = NULL, $date_modif = NULL) {
        // Vérifier qu'on reçoit bien un array
        if (($fields_values != NULL) && (!is_array($fields_values))) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Mauvais argument dans l'appel de la fonction"
        }

        if ($date_modif == NULL) {
            $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag = ".$this->getIdAgence()." AND is_actif = 't' AND ";
        } else {
            $date_mod = php2pg($date_modif);
            $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag = ".$this->getIdAgence()." AND ((is_actif = 't') OR (is_actif = 'f' AND date_modif > '$date_mod')) AND ";
        }
        if (isset($fields_values)) {
            foreach ($fields_values as $key => $value) {
                if (($value == '') or ($value == NULL)) {
                    $sql .= "$key IS NULL AND ";
                } else {
                    $sql .= "$key = '$value' AND ";
                }
            }
        }
        $sql = substr($sql, 0, -4);
        $sql .= "ORDER BY id_ag, num_cpte_comptable ASC";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        
        if($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $cptes = array();
        if(is_array($result) && count($result)>0) {
            foreach ($result as $row) {
                if (self::getNiveauCompte($row["num_cpte_comptable"]) <= $niveau && $niveau != NULL) {
                    $cptes[$row["num_cpte_comptable"]] = $row;
                } elseif ($niveau == NULL) {
                    $cptes[$row["num_cpte_comptable"]] = $row;
                }
            }
        }

        return $cptes;
    }

    /**
     * Fonction renvoyant toutes les associations définies selon les opérations ou les informations concernant une opération particulière
     * IN : $id_oper = 0 ==> Renvoie toutes les opérations
     *               > 0 ==> Renvoie l'opération id_oper
     * OUT: Objet ErrorObj avec en param :
     * Si $id_oper = 0 : array($key => array("type_operation", "libel", "cptes" = array ("sens" = array("categorie, "compte")))
     *             > 0 : array("libel") = libellé de l'opération
     */
    public function getOperations($id_oper = 0) {

        $sql = "SELECT * FROM ad_cpt_ope ";
        if ($id_oper == 0)
            $sql .= "WHERE id_ag = ".$this->getIdAgence()." ORDER BY type_operation";
        else
            $sql .= "WHERE type_operation = $id_oper and id_ag = ".$this->getIdAgence();

        $result = $this->getDbConn()->prepareFetchAll($sql);
        
        if($result===FALSE) {
            // Il n'y a pas d'association pour cette opération
            return new ErrorOBj(ERR_NO_ASSOCIATION, "L'opération $id_oper n'existe pas");
        }

        if ($id_oper > 0) {
            if (count($result) == 0) {
                // Il n'y a pas d'association pour cette opération
                return new ErrorOBj(ERR_NO_ASSOCIATION, "L'opération $id_oper n'existe pas");
            } else {
                $row = $result[0];

                return new ErrorObj(NO_ERR, array("libel" => $row["libel_ope"], "type_operation" => $row["type_operation"], "categorie_ope" => $row["categorie_ope"]));
            }
        } else {
            $OP = array();
            
            foreach ($result as $rows) {
                $sql = "SELECT * FROM ad_cpt_ope_cptes WHERE id_ag = ".$this->getIdAgence()." and type_operation = " . $rows["type_operation"];

                $result2 = $this->getDbConn()->prepareFetchAll($sql);
                
                if($result2===FALSE) {
                    // Il n'y a pas d'association pour cette opération
           	 		return new ErrorOBj(ERR_NO_ASSOCIATION, "L'opération $id_oper n'existe pas");                   
                }

                foreach ($result2 as $row_cptes) {
                    $rows["cptes"][$row_cptes["sens"]] = $row_cptes;
                }

                array_push($OP, $rows);
            }

            return new ErrorObj(NO_ERR, $OP);
        }
    }

    /**
     * Fonction renvoyant les informations au débit et au crédit d'une ou des opérations ADbanking
     * 
     * @param int $type_oper le numéro de l'opération
     * 
     * @return Objet ErrorObj avec param contenant le tableau les infos au débit au crédit de l'opération ADbanking le tableau des infos est de la forme array(debit => array(compte, sens, categorie credit => array(compte, sens, categorie)
     * 
     */
    public function getDetailsOperation($type_oper) {

        // récupération du détail de l'opération
        $sql = "SELECT * FROM ad_cpt_ope_cptes WHERE id_ag=".$this->getIdAgence()." and type_operation=$type_oper ORDER BY sens DESC;";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        
        if($result===FALSE || count($result)<=0) {  // Il n'y a pas d'association pour cette opération
            return new ErrorOBj(ERR_NO_ASSOCIATION);
        }

        $OP = array();

        foreach ($result as $row) {
            if ($row["sens"] == SENS_DEBIT) // informations au débit de l'opération
                $OP["debit"] = array("compte" => $row["num_cpte"], "sens" => $row["sens"], "categorie" => $row["categorie_cpte"]);
            elseif ($row["sens"] == SENS_CREDIT)  // informations au crédit de l'opération
                $OP["credit"] = array("compte" => $row["num_cpte"], "sens" => $row["sens"], "categorie" => $row["categorie_cpte"]);
        }

        return new ErrorObj(NO_ERR, $OP);
    }

    /**
     * 
     * Verifie s'il y des ecritures en attente sur le compte
     * 
     * @param type $num_cpte
     * 
     * @return boolean
     */
    public function isEcritureAttente($num_cpte) {

        $sql = "SELECT count(compte) FROM ad_brouillard where id_ag=".$this->getIdAgence()." and compte ='$num_cpte' ";

        $result = $this->getDbConn()->prepareFetchColumn($sql);

        if ($result > 0)
            return true;
        else
            return false;
    }

    /**
     * 
     * Fonction renvoyant le nombre de sous comptes d'un compte principal définis dans le plan comptable
     * IN : numero du compte
     * OUT: nombre de sous compte
     * 
     */
    public function getNbreSousComptesComptables($num_cpte, $a_isActif = NULL) {

        $sql = "SELECT count(num_cpte_comptable) FROM ad_cpt_comptable where id_ag=".$this->getIdAgence()." and num_cpte_comptable like '$num_cpte.%' ";
        if ($a_isActif != NULL) {
            $sql .=" AND is_actif='" . $a_isActif . "' ";
        }

        $result = $this->getDbConn()->prepareFetchColumn($sql);
        
        if ($result===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }
    
    /**
     * 
     * Renvoie l'exercice comptables en cours
     * 
     * IN : <néant>
     * OUT: array ( index => array(infos exercice))
     * 
     */
    public function getCurrentExercicesComptables() {

        $sql = "SELECT id_exo_compta FROM ad_exercices_compta WHERE id_ag=".$this->getIdAgence()." AND etat_exo=1 ORDER BY id_exo_compta DESC";
        
        $id_exo_compta = $this->getDbConn()->prepareFetchColumn($sql);
        
        if ($id_exo_compta===FALSE) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $id_exo_compta;
    }

    /**
     * 
     * Fonction renvoyant l'ensemble des exercices comptables
     * IN : <néant>
     * OUT: array ( index => array(infos exercice))
     * 
     */
    public function getExercicesComptables($id_exo = NULL) {

        $sql = "SELECT * FROM ad_exercices_compta where id_ag=".$this->getIdAgence();
        if ($id_exo)
            $sql .= " AND id_exo_compta=$id_exo ";
        $sql .= "ORDER BY id_exo_compta";
        
        $result = $this->getDbConn()->prepareFetchAll($sql);
        
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $exos = array();
        foreach ($result as $row) {
            array_push($exos, $row);
        }

        return $exos;
    }

    /**
     * 
     * Fonction qui calcule pour un compte le solde des mouvements de l'exerciece en cours
     * utile pour la répartition des soldes des comptes de gestions centralisateurs lors de la création de sous-comptes
     * 
     * @param txt $compte Le numéro du compte comptable
     * 
     * @return int Le solde des mouvements du compte dans l'exercice en cours
     */
    public function calculeSoldeCpteGestion($compte) {
        $solde = 0;
        
        // Init class
        $AgenceObj = new Agence($this->getDbConn());

        /* Exercice en cours */
        $AG = $AgenceObj->getAgenceDatas($this->getIdAgence());
        $id_exo_encours = $AG["exercice"];
        
        // Destroy object
        unset($AgenceObj);

        $infos_exo_encours = self::getExercicesComptables($id_exo_encours);

        /* Mouvements au débit dans l'exercie en cours */
        $sql = "SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE b.id_ag=".$this->getIdAgence()." and a.id_ag=".$this->getIdAgence()." and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN date('" . $infos_exo_encours[0]['date_deb_exo'] . "') AND date('" . $infos_exo_encours[0]['date_fin_exo'] . "') AND sens = 'd' ";
        $total_debit = $this->getDbConn()->prepareFetchColumn($sql);
        
        if ($total_debit===FALSE || !$total_debit) {
            $total_debit = 0;
        }

        /* Mouvements au crédit dans l'exercie en cours */
        $sql = "SELECT sum(montant) FROM ad_mouvement a, ad_ecriture b WHERE a.id_ag=".$this->getIdAgence()." and b.id_ag=".$this->getIdAgence()." and a.id_ecriture = b.id_ecriture AND compte = '$compte' AND date_comptable BETWEEN date('" . $infos_exo_encours[0]['date_deb_exo'] . "') AND date('" . $infos_exo_encours[0]['date_fin_exo'] . "') AND sens = 'c'";
        $total_credit = $this->getDbConn()->prepareFetchColumn($sql);

        if ($total_credit===FALSE || !$total_credit) {
            $total_credit = 0;
        }

        $solde = $total_credit - $total_debit;

        return $solde;
    }

    /**
     * 
     * Renvoie les donnes de la table ad_journaux_cptie
     * 
     * @param type $id_jou
     * @param type $num_cpte
     * 
     * @return array
     */
    public function getInfosJournalCptie($id_jou = NULL, $num_cpte = NULL) {

        $sql = "SELECT * FROM ad_journaux_cptie where id_ag=".$this->getIdAgence();
        if ($id_jou != NULL) {
            $sql .= "AND id_jou=$id_jou";
            if ($num_cpte != NULL)
                $sql .= " and (num_cpte_comptable='$num_cpte' OR num_cpte_comptable like '$num_cpte.%')";
        } else
        if ($num_cpte != NULL)
            $sql .= "AND num_cpte_comptable='$num_cpte' OR num_cpte_comptable like '$num_cpte.%'";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $cptie = array();
        foreach ($result as $row) {
            array_push($cptie, $row);
        }

        return $cptie;
    }

    /**
     * Supprime des comptes de contrepartie d'un journal
     * 
     * @param type $id_jou
     * @param type $id_compte
     * 
     * @return boolean
     */
    public function supJournalCptie($id_jou, $id_compte) {

        // Le compte et ses sous-comptes qui sont de la contrepartie
        $cptie = self::getInfosJournalCptie($id_jou, $id_compte);
        if (isset($cptie))
            foreach ($cptie as $row) {
                $id = $row["id_jou"];
                $num = $row["num_cpte_comptable"];

                $sql = "delete from ad_journaux_cptie where id_ag=".$this->getIdAgence()." and id_jou=$id and num_cpte_comptable='$num'";
                $this->getDbConn()->execute($sql);
            }

        return true;
    }

    /**
     * Renvoie la liste des sous-comptes d'un compte comptable
     * 
     * @param text $compte Numéro du compte comptable
     * @param bool $recusrif true si on désire ontenir tous les sous comptes récursivement
     * @param text $whereSousCpte condition de selections des sous comptes
     * 
     * @return Array List edes sous comptes
     */
    public function getSousComptes($compte, $recursif = true, $whereSousCpte = "") {

        $liste_sous_comptes = array();

        $sql .= "SELECT * FROM ad_cpt_comptable WHERE cpte_centralise = '" . $compte . "' AND id_ag = " . $this->getIdAgence();
        $sql .= $whereSousCpte;

        $result = $this->getDbConn()->prepareFetchAll($sql);
        
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        foreach ($result as $row) {
            // ajoute le compte dans la liste
            $liste_sous_comptes[$row['num_cpte_comptable']] = $row;

            // ajouter les sous-comptes du sous-compte par récursivité si récursif
            if ($recursif)
                $liste_sous_comptes = array_merge($liste_sous_comptes, self::getSousComptes($row['num_cpte_comptable'], true, $whereSousCpte));
        }

        return $liste_sous_comptes;
    }

    /**
     * Ajout le compte $compte et ses sous-comptes dans la contrepartie du journal dont l'id est donné
     * 
     * @param type $id_jou
     * @param type $compte
     * 
     * @return ErrorOBj
     */
    public function ajoutJournalCptie($id_jou, $compte) {

        // si le compte ou les sous-comptes sont de la contrepartie, les supprimer d'abord
        $sup = self::supJournalCptie($id_jou, $compte);

        // Récupération de tous les comptes dérivés de ce compte
        $sous_comptes = self::getSousComptes($compte, true);

        // Ajout du compte dans la contrepartie du journal
        $sql = "INSERT INTO ad_journaux_cptie Values($id_jou,'$compte',".$this->getIdAgence().")";

        $this->getDbConn()->execute($sql);

        // Ajout des sous-comptes dans la contrepartie du journal
        if (isset($sous_comptes))
            foreach ($sous_comptes as $key => $value) {
                // Récupère informations du sous-compte
                $param["num_cpte_comptable"] = $key;
                $cpte = self::getComptesComptables($param);

                // vérifie si le sous-compte n'est pas compte principal d'un journal
                if ($cpte[$key]["cpte_princ_jou"] == 't') {
                    return new ErrorOBj(ERR_DEJA_PRINC_JOURNAL, $key);
                }

                // ajout du sous-compte dans la contrepartie
                $sql = "INSERT INTO ad_journaux_cptie Values($id_jou,'$key',".$this->getIdAgence().")";
                $this->getDbConn()->execute($sql);
            }

        return new ErrorObj(NO_ERR);
    }

    /**
     * Fonction qui ajoute des sous-comptes à un compte comptable
     * 
     * IN: - $compte_centralisateur = le numéro du compte auquel on veut ajouter des sous-comptes
     *     - $liste_sous_comptes = tableau contenant la liste des sous-comptes au format
     *       array (n° cpte => array(n° cpte, libel, solde de départ, devise))
     * 
     * OUT : Objet ErrorObj
     */
    public function ajoutSousCompteComptable($compte_centralisateur, $liste_sous_comptes, $solde_reparti = NULL) {
        global $global_nom_login;

        //Recupèration des infos du compte centralisateur
        $param["num_cpte_comptable"] = $compte_centralisateur;
        $infocptecentralise = self::getComptesComptables($param);

        // Verifier s'il n y a pas, pour le compte centralisateur, des ecritures en attente dans le brouillard
        $ecriture_attente = self::isEcritureAttente($compte_centralisateur);
        if ($ecriture_attente == true) {
            return new ErrorObj(ERR_CPT_ECRITURE_EXIST, $compte_centralisateur);
        }

        // Récupère le nombre de sous-comptes du compte centralisateur
        $nbre_souscompte = self::getNbreSousComptesComptables($compte_centralisateur);

        // Vérifie si c'est la première création de sous-comptes pour le compte centralisateur
        if ($nbre_souscompte == 0) {
            // première création, Vérifier alors que solde du compte centralisateur est complétement réparti entre les sous-comptes

            $soldesc = 0; // la somme des soldes des sous-comptes
            if (isset($liste_sous_comptes))
                foreach ($liste_sous_comptes as $key => $value)
                    $soldesc = $soldesc + abs($value["solde"]);
            if ($solde_reparti == NULL) {
                if ($infocptecentralise[$compte_centralisateur]['compart_cpte'] == 3 OR $infocptecentralise[$compte_centralisateur]['compart_cpte'] == 4) {
                    $solde_reparti = self::calculeSoldeCpteGestion($compte_centralisateur);
                } else {
                    $solde_reparti = $infocptecentralise[$compte_centralisateur]['solde'];
                }
            }

            //comparaison entre la sommme des soldes et le solde du compte centralisateur
            if (abs($solde_reparti) != $soldesc) {
                return new ErrorObj(ERR_SOLDE_MAL_REPARTI, $compte_centralisateur);
            }
        }
        // Ajout des sous comptes
        if (isset($liste_sous_comptes)) // parcours de la liste des sous-comptes
            foreach ($liste_sous_comptes as $key => $value)
                if ($key != '') {
                    // Vérifier que le sous-compte n'existe pas dans la DB
                    $sql = "SELECT * FROM ad_cpt_comptable WHERE id_ag=".$this->getIdAgence()." and num_cpte_comptable='$key';";
                    // FIXME : Utiliser getComptesComptables ?
                    $result = $this->getDbConn()->prepareFetchAll($sql);
                    
                    if($result===FALSE || count($result)<0) {
                        $this->getDbConn()->rollBack(); // Roll back
                        signalErreur(__FILE__, __LINE__, __FUNCTION__);
                    }

                    if (count($result) > 0) {
                        return new ErrorObj(ERR_CPT_EXIST, $key);
                    }

                    // Héritage automatique de la devise du compte centralisateur
                    if (!isset($value["devise"]) && isset($infocptecentralise[$compte_centralisateur]["devise"]))
                        $value["devise"] = $infocptecentralise[$compte_centralisateur]["devise"];

                    // Vérfieir si la devise du sous-compte n'est pas différente de la devise du compte centralisateur
                    if ($infocptecentralise[$compte_centralisateur]["devise"] != NULL && $infocptecentralise[$compte_centralisateur]["devise"] != $value["devise"]) {
                        return new ErrorObj(ERR_DEV_DIFF_CPT_CENTR, $value["devise"]);
                    }
                    // Construction de la requête d'insertion de sous-compte
                    $DATA = array();
                    // Vérifier si la devise du sous-compte n'est pas différente de la devise du compte de provision
                    if ($value['cpte_provision'] != "[Aucun]" && $value["cpte_provision"] != NULL) {
                        $infoscpteprov = self::getComptesComptables(array("num_cpte_comptable" => $value["cpte_provision"]));

                        if ($infoscpteprov[$value["cpte_provision"]]["devise"] != $value["devise"]) {
                            return new ErrorObj(ERR_DEV_DIFF_CPT_PROV, $value["devise"]);
                        }
                        $DATA["cpte_provision"] = $value["cpte_provision"];
                    } else {
                        $DATA["cpte_provision"] = NULL;
                    }

                    $DATA["num_cpte_comptable"] = $value["num_cpte_comptable"];
                    $DATA["libel_cpte_comptable"] = $value["libel_cpte_comptable"];
                    if ($value["compart_cpte"] != '') // si le compartiment n'edst pas renseigné alors il l'hérite du compte père
                        $DATA["compart_cpte"] = $value["compart_cpte"];
                    else
                        $DATA["compart_cpte"] = $infocptecentralise[$compte_centralisateur]["compart_cpte"];

                    if ($value["sens_cpte"] != '') // si le sens n'est pas renseigné alors il l'hérite du compte père
                        $DATA["sens_cpte"] = $value["sens_cpte"];
                    else
                        $DATA["sens_cpte"] = $infocptecentralise[$compte_centralisateur]["sens_cpte"];

                    $DATA["classe_compta"] = $infocptecentralise[$compte_centralisateur]["classe_compta"];
                    //$DATA["cpte_centralise"] = $compte_centralisateur;

                    if ($infocptecentralise[$compte_centralisateur]['cpte_princ_jou'] == 't')
                        $DATA["cpte_princ_jou"] = 't';
                    else
                        $DATA["cpte_princ_jou"] = 'f';

                    $DATA["solde"] = 0;

                    $now = date("Y-m-d");
                    $DATA["date_ouvert"] = $now;
                    $DATA["etat_cpte"] = 1;
                    $DATA["id_ag"] = $this->getIdAgence();
                    $DATA["devise"] = $value["devise"];

                    $sql = buildInsertQuery("ad_cpt_comptable", $DATA);

                    //Recherche des contrepartie pour le compte centralisateur
                    $cpt_cptie = self::getInfosJournalCptie(NULL, $compte_centralisateur);
                    if (is_array($cpt_cptie)) {
                        foreach ($cpt_cptie as $key1 => $DATA) {
                            foreach ($liste_sous_comptes as $key2 => $value2) {
                                //ajout dans le journal des contreparties
                                $myErr = self::ajoutJournalCptie($DATA["id_jou"], $value2["num_cpte_comptable"]);
                                if ($myErr->errCode != NO_ERR) {
                                    $html_err = new HTML_erreur(_("Echec création journal. "));
                                    $html_err->setMessage(_("Erreur") . " : " . $myErr->param);
                                    $html_err->addButton("BUTTON_OK", 'Jou-6');
                                    $html_err->buildHTML();
                                    echo $html_err->HTML_code;
                                }
                            }
                        }
                    }
                    // Insertion dans la DB
                    $this->getDbConn()->execute($sql);

                    if (abs($solde_reparti) != 0 && ($value['solde'] != 0)) {
                        // Passage des écritures comptables
                        $comptable = array();
                        $cptes_substitue = array();
                        $cptes_substitue["cpta"] = array();
                        if ($solde_reparti < 0) {
                            //crédit du compte centralisateur par le débit d'un sous-compte
                            $cptes_substitue["cpta"]["debit"] = $key;
                            $cptes_substitue["cpta"]["credit"] = $compte_centralisateur;
                        } else {
                            //débit d'un sous compte par le credit du compte centralisateur
                            $cptes_substitue["cpta"]["debit"] = $compte_centralisateur;
                            $cptes_substitue["cpta"]["credit"] = $key;
                        }
                        
                        // Init class
                        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());

                        $myErr = $CompteObj->passageEcrituresComptablesAuto(1003, abs($value["solde"]), $comptable, $cptes_substitue, $value["devise"]);
                        
                        // Destroy object
                        unset($CompteObj);

                        if ($myErr->errCode != NO_ERR) {
                            return $myErr;
                        }
                        
                        // Init class
                        $HistoriqueObj = new Historique($this->getDbConn(), $this->getIdAgence());

                        $erreur = $HistoriqueObj->ajoutHistorique(410, NULL, _("Virement solde compte principal"), $global_nom_login, date("r"), $comptable);
                        // Destroy object
                        unset($HistoriqueObj);

                        if ($erreur->errCode != NO_ERR) {
                            return $erreur;
                        }
                    }
                }

        // Mise à jour du champs compte centralisateur des sous-compte
        if (isset($liste_sous_comptes)) // parcours de la liste des sous-comptes
            foreach ($liste_sous_comptes as $key => $value)
                if ($key != '') {
                    $niveau = self::getNiveauCompte($compte_centralisateur) + 1;

                    $sql = "UPDATE ad_cpt_comptable set cpte_centralise='$compte_centralisateur', niveau = $niveau WHERE id_ag=".$this->getIdAgence()." AND num_cpte_comptable = '$key'";
                    $this->getDbConn()->execute($sql);
                }

        return new ErrorObj(NO_ERR);
    }

    /**
     * Verifie si le compte est centralisateur, c'est à dire s'il a des sous-comptes.
     *
     * @param string $num_cpte Le numéro du compte comptable.
     * @return boolean True si compte possède des sous comptes, False sinon.
     */
    public function isCentralisateur($num_cpte) {

        $sql = "SELECT COUNT(*) FROM ad_cpt_comptable where cpte_centralise ='$num_cpte'  ";

        $result = $this->getDbConn()->prepareFetchColumn($sql);

        if ($result > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Renvoie les infos des journaux
     * 
     */
    public function getInfosJournal($id_jou = NULL) {

        $sql = "SELECT * FROM ad_journaux where id_ag=".$this->getIdAgence();

        if ($id_jou != NULL)
            $sql .= "AND id_jou=$id_jou";

        $result = $this->getDbConn()->prepareFetchAll($sql);

        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        $jnl = array();
        foreach ($result as $row) {
            $jnl[$row["id_jou"]] = $row;
        }

        return $jnl;
    }

    /**
     * Renvoie les informations sur le Journal associé au compte comptable
     * 
     */
    public function getJournalCpte($num_cpte) {
        $infos = array();
        $non_jou = false;

        // Regarder si ce compte a un journal associé
        $sql = "SELECT * FROM ad_cpt_comptable where id_ag=".$this->getIdAgence()." and num_cpte_comptable='$num_cpte' and cpte_princ_jou = 't'";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        if (count($result) == 0) { // Si pas de journal associé. Rem :pourquoi ne pas faire appel à getComptesComptes et vérifier que c'un compte principal
            $non_jou = true;
        }

        $sql = "SELECT * FROM ad_journaux  where id_ag=".$this->getIdAgence()." and num_cpte_princ = '$num_cpte' ";

        $result = $this->getDbConn()->prepareFetchAll($sql);

        if (!$result) {
            $non_jou = true; // $non_jou nous indique que c'est le journal 1 qui sera utilisé par défaut
        }

        if ($non_jou == false) { // Sinon pas la peine, on sait déjà qu'il n'y a pas de journal associé
            // Si on a de la chance, ce compte est directement associé à un journal
            $sql = "SELECT * FROM ad_journaux  where id_ag=".$this->getIdAgence()." and num_cpte_princ = '$num_cpte' ";

            $result = $this->getDbConn()->prepareFetchAll($sql);
            
            if($result===FALSE || count($result)<0) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }

            if (count($result) == 1) {
                $infos = $result[0];
            } else {
                // On regarde si le compte centralisateur n'est pas compte principal d'un journal
                $sql = "SELECT cpte_centralise FROM ad_cpt_comptable where id_ag=".$this->getIdAgence()." and num_cpte_comptable = '$num_cpte'";

                $result = $this->getDbConn()->prepareFetchAll($sql);
                
                if($result===FALSE || count($result)<0) {
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__);
                }

                if (count($result) == 1) {
                    $row = $result[0];
                    $info_jou = self::getJournalCpte($row[0]); // Appel récursif avec le compte centralisateur

                    return $info_jou;
                } else {
                    // On est arrivés à la racine du plan comptable, il y a donc une inconsistance dans la base de données
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Inconsistance dans la DB : le compte $num_cpte est censé tre compte principal et pourant ..."
                }
            }
        }

        if ($non_jou == true) {
            $jou_princ = self::getInfosJournal(1);
            $infos = $jou_princ[1];
            return($infos);
        }
        else
            return($infos);
    }

    /**
     * Fonction renvoyant l'ensemble des comptes de liaison et leurs journaux associés
     * 
     * @param array $fields_values, on construit la clause WHERE ainsi : ... WHERE field = value ...
     * 
     * @return array ( index => infos)
     */
    public function getJournauxLiaison($fields_values = NULL) {

        //vérifier qu'on reçoit bien un array
        if (($fields_values != NULL) && (!is_array($fields_values))) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Mauvais argument dans l'appel de la fonction"
        }

        // construction de la requête
        $sql = "SELECT * FROM ad_journaux_liaison where id_ag=".$this->getIdAgence();
        if (isset($fields_values)) {
            $sql .= " AND ";
            foreach ($fields_values as $key => $value)
                if ($key == 'id_jou1' || $key == 'id_jou2')
                    $sql .= "(id_jou1=$value OR id_jou2=$value ) AND "; // Soit il est à la première position soit il est la 2ème
                else
                    $sql .= "$key = '$value' AND ";
            $sql = substr($sql, 0, -4);
        }
        $sql .= " ORDER BY id_jou1 ASC";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        
        if($result===FALSE || count($result)<0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Liste des comptes de liaison
        $info = array();
        foreach($result as $row)
            array_push($info, $row);

        return $info;
    }
	
	/**
	 * Fonction renvoyant la liste des comptes centralisateurs d'un compte comptable
	 * 
	 * Renvoie la liste des comptes dont est dérivée un un compte comptable 
	 * IN: compte comptable 
     * OUT : liste des comptes centralisateurs de ce compte
	 * 
	 * @param text $compte
	 *        	: numéro d'un compte comptable.
	 * @return array liste de comptes comptables
	 */
	public function getComptesCentralisateurs($compte) {		
		$centralisateurs = array();
				
		// récupère le numéro du compte centralisateur
		$param["num_cpte_comptable"] = $compte;		
		$cpte = $this->getComptesComptables($param);	
		
		if(!empty($cpte[$compte]["cpte_centralise"])) {
			$cpte_centralise = $cpte[$compte]["cpte_centralise"];		
			array_push($centralisateurs, $cpte_centralise);		
			$centralisateurs = array_merge($centralisateurs, $this->getComptesCentralisateurs($cpte_centralise));			
		}
		
		if(!empty($centralisateurs)) {
			sort($centralisateurs);
		}		
		
		return $centralisateurs;	
	}


    /**
     * Fonction vérifiant si un compte comptable est associé à un guichet
     * Un compte comptable est associé à un guichet :
     *    - s'il est directement liè à guichet
     *    - si au moins un de ses comptes centralisateurs est directement lié à un guichet   
     * @param text $compte : numéro d'un compte comptable.
     * @return boolean true si le compte est associé à un guichet sinon false
     */
    public function isCompteGuichet($compte) {   	
    	$id_agence = $this->getIdAgence();
    	
    	// Récupération des comptes centralisateurs du compte comptable
    	$centralisateurs = array();
    	$centralisateurs = self::getComptesCentralisateurs($compte);   	
    
    	// On fusionne le compte lui-même et la liste des comptes centralisateurs
    	$liste = array();
    	$liste = array_merge($centralisateurs,(array)$compte);   	
    
    	// Parcours de la liste des comptes
    	foreach($liste as $key=>$value) {   		
    		$sql = "SELECT * FROM ad_gui WHERE id_ag=$id_agence and cpte_cpta_gui = '$value'";    		
    		$result = $this->getDbConn()->prepareFetchAll($sql);    		
    		// ni le compte comptable lui-même ni un de ses comptes centralisateurs n'est associé à un guichet
    		if(!empty($result) && $result->numRows() >= 1) {    			 	
    			return true; 
    		}    		
    	}
    	    	
    	return false;
    }


    /**
     * Recupere le solde du compte de liaison
     * @param $id_cpte_liaison
     * @param $date_deb
     * @param $date_fin
     * @return mixed
     */
    public function getSoldeCompteLiaisonForCompensation($id_cpte_liaison, $id_agence, $date_deb, $date_fin, $code_devise)
    {
        //solde_view_compta
        $sql = "SELECT num_cpte_comptable, libel_cpte_comptable, solde
                FROM ad_cpt_comptable WHERE num_cpte_comptable
                IN (SELECT compte FROM view_compta where compte = '$id_cpte_liaison');";

        $result = $this->getDbConn()->prepareFetchAll($sql);

        if ($result === FALSE || count($result) == 0 || count($result) > 1) {
            return "0";
        }

        $solde_view_compta = $result[0]['solde'];

        //somMvtCreditApDateFin
        $sql = "SELECT sum(COALESCE(montant,0))
                FROM view_compta c
                WHERE sens = 'c'
                AND c.compte = '$id_cpte_liaison'
                and c.date_comptable > '$date_fin' and c.date_comptable <= date(now());";

        $result = $this->getDbConn()->prepareFetchAll($sql);

        $somMvtCreditApDateFin = $result[0]['sum'];
        if(empty($somMvtCreditApDateFin)) $somMvtCreditApDateFin = 0;

        //somMvtDebitApDateFin
        $sql = "SELECT sum(COALESCE(montant,0))
                FROM view_compta c
                WHERE sens = 'd'
                AND c.compte = '$id_cpte_liaison'
                and c.date_comptable > '$date_fin' and c.date_comptable <= date(now());";

        $result = $this->getDbConn()->prepareFetchAll($sql);


        $somMvtDebitApDateFin = $result[0]['sum'];
        if(empty($somMvtDebitApDateFin)) $somMvtDebitApDateFin = 0;

        $solde_fin = $solde_view_compta - $somMvtCreditApDateFin + $somMvtDebitApDateFin;

        if(empty($solde_fin)) $solde_fin = 0;

        // Solde debut :
        $sql = "SELECT (date('$date_deb') - interval '1 day') as date_deb_calc;";
        $result = $this->getDbConn()->prepareFetchAll($sql);
        $date_deb_calc = $result[0]['date_deb_calc'];

        // somMvtCreditApDateDeb
        $sql = "SELECT sum(COALESCE(montant,0))
                FROM view_compta c
                WHERE sens = 'c'
                AND c.compte = '$id_cpte_liaison'
                and c.date_comptable > '$date_deb_calc' and c.date_comptable <= date(now());";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        $somMvtCreditApDateDeb = $result[0]['sum'];

        // somMvtDebitApDateDeb
        $sql = "SELECT sum(COALESCE(montant,0))
                FROM view_compta c
                WHERE sens = 'd'
                AND c.compte = '$id_cpte_liaison'
                and c.date_comptable > '$date_deb_calc' and c.date_comptable <= date(now());";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        $somMvtDebitApDateDeb = $result[0]['sum'];

        $solde_deb = $solde_view_compta - $somMvtCreditApDateDeb + $somMvtDebitApDateDeb;

        if(empty($solde_deb)) $solde_deb = 0;

        // Mouvements au debit autre que les operations en deplacer
        $sql = "select sum(COALESCE(m.montant,0)) from ad_his h
                inner join ad_ecriture e on h.id_his = e.id_his
                inner join ad_mouvement m on e.id_ecriture = m.id_ecriture
                where h.type_fonction not in (92,93)
                and m.compte = '$id_cpte_liaison'  and m.sens = 'd' and m.devise = '$code_devise'
                and m.date_valeur between '$date_deb' and '$date_fin'
                and m.id_ag = $id_agence;
                ";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        $somMvtDebit = $result[0]['sum'];

        // Mouvements au credit autre que les operations en deplacer
        $sql = "select sum(COALESCE(m.montant,0)) from ad_his h
                inner join ad_ecriture e on h.id_his = e.id_his
                inner join ad_mouvement m on e.id_ecriture = m.id_ecriture
                where h.type_fonction not in (92,93)
                and m.compte = '$id_cpte_liaison'  and m.sens = 'c' and m.devise = '$code_devise'
                and m.date_valeur between '$date_deb' and '$date_fin'
                and m.id_ag = $id_agence;
                ";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        $somMvtCredit = $result[0]['sum'];

        // Si solde_calculer < 0 : mouvement debiteur en se basant sur Grand livre
        if($solde_deb < 0) { // Debiteur
            $solde_deb = abs($solde_deb);
        }
        else { // Crediteur
            $solde_deb = -1 * $solde_deb;
        }
        if($solde_fin < 0) { // Debiteur
            $solde_fin = abs($solde_fin);
        }
        else { // Crediteur
            $solde_fin = -1 * $solde_fin;
        }

        // Les lignes du grand livres qui correspondent au total des depots et retraits :
        // Depots:
        $sql = "SELECT sum(COALESCE(montant,0))
                from getGrandLivreView(date('$date_deb'), date('$date_fin'), $id_agence)
                where  id_ag = $id_agence
                and compte >= '$id_cpte_liaison' and compte <= '$id_cpte_liaison'
                and id_jou = '1'
                and sens = 'd';";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        $sommeDepots = $result[0]['sum'];

        //Retraits:
        $sql = "SELECT sum(COALESCE(montant,0))
                from getGrandLivreView(date('$date_deb'), date('$date_fin'), $id_agence)
                where  id_ag = $id_agence
                and compte >= '$id_cpte_liaison' and compte <= '$id_cpte_liaison'
                and id_jou = '1'
                and sens = 'c';";

        $result = $this->getDbConn()->prepareFetchAll($sql);
        $sommeRetraits = $result[0]['sum'];

        if($sommeRetraits > 0) $sommeRetraits = $sommeRetraits * -1; // Affichage au -ve
        if($somMvtCredit > 0) $somMvtCredit = $somMvtCredit * -1; // Affichage au -ve
        $sql = "SELECT sum(COALESCE(montant,0)) from ad_mouvement m
                inner join ad_ecriture e on e.id_ecriture = m.id_ecriture
                where e.type_operation in (156)
                and m.sens = 'c'
                and m.compte IN (SELECT cpte_comm_od FROM adsys_multi_agence where id_agc = $id_agence)
                and m.date_valeur between '$date_deb' and '$date_fin'";
        $result = $this->getDbConn()->prepareFetchAll($sql);
        $sommeCommOd_depot = $result[0]['sum'];

        $sql = "SELECT sum(COALESCE(montant,0)) from ad_mouvement m
                inner join ad_ecriture e on e.id_ecriture = m.id_ecriture
                where e.type_operation in (157)
                and m.sens = 'c'
                and m.compte IN (SELECT cpte_comm_od FROM adsys_multi_agence where id_agc = $id_agence)
                and m.date_valeur between '$date_deb' and '$date_fin'";
        $result = $this->getDbConn()->prepareFetchAll($sql);
        $sommeCommOd_retrait = $result[0]['sum'];

        $sql = "select code_devise_reference from ad_agc where id_ag = $id_agence";
        $result = $this->getDbConn()->prepareFetchAll($sql);
        $code_devise = $result[0]['code_devise_reference'];

        $soldes = array();
        $soldes['solde_deb'] = round($solde_deb, 8);
        $soldes['solde_fin'] = round($solde_fin, 8);
        $soldes['mvmts_deb'] = round($somMvtDebit, 8);
        $soldes['mvmts_cred'] = round($somMvtCredit, 8);
        $soldes['total_depot'] = round($sommeDepots, 8);
        $soldes['total_retrait'] = round($sommeRetraits, 8);
        $soldes['solde_comm_od_depot'] = round($sommeCommOd_depot,8);
        $soldes['solde_comm_od_retrait'] = round($sommeCommOd_retrait,8);
        $soldes['solde_comm_od'] = round($sommeCommOd_retrait,8)+ round($sommeCommOd_depot,8);
        $soldes['devise'] = $code_devise;

        return $soldes;
      }

      /**
   * Recupere le solde du compte de liaison
   * @param $id_cpte_liaison
   * @param $date_deb
   * @param $date_fin
   * @return mixed
   */
    public function getSoldeCompteLiaisonForCompensationSiege($id_cpte_liaison, $id_agence, $date_deb, $date_fin, $code_devise)
    {
      //solde_view_compta
      $sql = "SELECT num_cpte_comptable, libel_cpte_comptable, solde
              FROM ad_cpt_comptable WHERE num_cpte_comptable
              IN (SELECT compte FROM view_compta where compte = '$id_cpte_liaison');";

      $result = $this->getDbConn()->prepareFetchAll($sql);

      if ($result === FALSE || count($result) == 0 || count($result) > 1) {
        return "0";
      }

      $solde_view_compta = $result[0]['solde'];

      //somMvtCreditApDateFin
      $sql = "SELECT sum(COALESCE(montant,0))
              FROM view_compta c
              WHERE sens = 'c'
              AND c.compte = '$id_cpte_liaison'
              and c.date_comptable > '$date_fin' and c.date_comptable <= date(now());";

      $result = $this->getDbConn()->prepareFetchAll($sql);

      $somMvtCreditApDateFin = $result[0]['sum'];
      if (empty($somMvtCreditApDateFin)) $somMvtCreditApDateFin = 0;

      //somMvtDebitApDateFin
      $sql = "SELECT sum(COALESCE(montant,0))
              FROM view_compta c
              WHERE sens = 'd'
              AND c.compte = '$id_cpte_liaison'
              and c.date_comptable > '$date_fin' and c.date_comptable <= date(now());";

      $result = $this->getDbConn()->prepareFetchAll($sql);


      $somMvtDebitApDateFin = $result[0]['sum'];
      if (empty($somMvtDebitApDateFin)) $somMvtDebitApDateFin = 0;

      $solde_fin = $solde_view_compta - $somMvtCreditApDateFin + $somMvtDebitApDateFin;

      if (empty($solde_fin)) $solde_fin = 0;

      // Solde debut :
      $sql = "SELECT (date('$date_deb') - interval '1 day') as date_deb_calc;";
      $result = $this->getDbConn()->prepareFetchAll($sql);
      $date_deb_calc = $result[0]['date_deb_calc'];

      // somMvtCreditApDateDeb
      $sql = "SELECT sum(COALESCE(montant,0))
              FROM view_compta c
              WHERE sens = 'c'
              AND c.compte = '$id_cpte_liaison'
              and c.date_comptable > '$date_deb_calc' and c.date_comptable <= date(now());";

      $result = $this->getDbConn()->prepareFetchAll($sql);
      $somMvtCreditApDateDeb = $result[0]['sum'];

      // somMvtDebitApDateDeb
      $sql = "SELECT sum(COALESCE(montant,0))
              FROM view_compta c
              WHERE sens = 'd'
              AND c.compte = '$id_cpte_liaison'
              and c.date_comptable > '$date_deb_calc' and c.date_comptable <= date(now());";

      $result = $this->getDbConn()->prepareFetchAll($sql);
      $somMvtDebitApDateDeb = $result[0]['sum'];

      $solde_deb = $solde_view_compta - $somMvtCreditApDateDeb + $somMvtDebitApDateDeb;

      if (empty($solde_deb)) $solde_deb = 0;

      // Mouvements au debit autre que les operations en deplacer
      $sql = "select sum(COALESCE(m.montant,0)) from ad_his h
              inner join ad_ecriture e on h.id_his = e.id_his
              inner join ad_mouvement m on e.id_ecriture = m.id_ecriture
              where h.type_fonction not in (92,93)
              and m.compte = '$id_cpte_liaison'  and m.sens = 'd' and m.devise = '$code_devise'
              and m.date_valeur between '$date_deb' and '$date_fin'
              and m.id_ag = numagc();
              ";

      $result = $this->getDbConn()->prepareFetchAll($sql);
      $somMvtDebit = $result[0]['sum'];

      // Mouvements au credit autre que les operations en deplacer
      $sql = "select sum(COALESCE(m.montant,0)) from ad_his h
              inner join ad_ecriture e on h.id_his = e.id_his
              inner join ad_mouvement m on e.id_ecriture = m.id_ecriture
              where h.type_fonction not in (92,93)
              and m.compte = '$id_cpte_liaison'  and m.sens = 'c' and m.devise = '$code_devise'
              and m.date_valeur between '$date_deb' and '$date_fin'
              and m.id_ag = numagc();
              ";

      $result = $this->getDbConn()->prepareFetchAll($sql);
      $somMvtCredit = $result[0]['sum'];

      // Si solde_calculer < 0 : mouvement debiteur en se basant sur Grand livre
      if ($solde_deb < 0) { // Debiteur
        $solde_deb = abs($solde_deb);
      } else { // Crediteur
        $solde_deb = -1 * $solde_deb;
      }
      if ($solde_fin < 0) { // Debiteur
        $solde_fin = abs($solde_fin);
      } else { // Crediteur
        $solde_fin = -1 * $solde_fin;
      }

      // Les lignes du grand livres qui correspondent au total des depots et retraits :
      // Depots:
      $sql = "select sum(COALESCE(tot.montant,0)) from (select a.type_transaction as transaction, a.montant, m.devise from adsys_audit_multi_agence a, ad_mouvement m where a.id_ecriture_local = m.id_ecriture and date(a.date_crea) >= date('$date_deb') and date(a.date_crea) <= date('$date_fin') and a.id_ag_local = numagc() and a.id_ag_distant = $id_agence and a.type_transaction = 'depot' and a.success_flag = 't' and m.compte = '$id_cpte_liaison'
) tot group by tot.transaction, tot.devise;";


      $result = $this->getDbConn()->prepareFetchAll($sql);
      $sommeDepots = $result[0]['sum'];

      //Retraits:
      $sql = "select sum(COALESCE(tot.montant,0)) from (select a.type_transaction as transaction, a.montant, m.devise from adsys_audit_multi_agence a, ad_mouvement m where a.id_ecriture_local = m.id_ecriture and date(a.date_crea) >= date('$date_deb') and date(a.date_crea) <= date('$date_fin') and a.id_ag_local = numagc() and a.id_ag_distant = $id_agence and a.type_transaction = 'retrait' and a.success_flag = 't' and m.compte = '$id_cpte_liaison'
) tot group by tot.transaction, tot.devise;";

      $result = $this->getDbConn()->prepareFetchAll($sql);
      $sommeRetraits = $result[0]['sum'];

      /*if ($sommeRetraits > 0) $sommeRetraits = $sommeRetraits * -1; // Affichage au -ve
      if ($somMvtCredit > 0) $somMvtCredit = $somMvtCredit * -1; // Affichage au -ve*/

        //TODO: ajout des deux requetes de recuperations des montant OD sur les 2 fonctions retraits et depots
        /*$sql = "SELECT sum(COALESCE(montant,0)) from ad_mouvement m
                inner join ad_ecriture e on e.id_ecriture = m.id_ecriture
                where e.type_operation in (156)
                and m.sens = 'c'
                and m.compte IN (SELECT cpte_comm_od FROM adsys_multi_agence where id_agc = $id_agence)
                and m.date_valeur between '$date_deb' and '$date_fin'
                and m.id_ag = numagc()";print_rn($sql);*/
        $sql = "SELECT SUM(COALESCE(commission_ope_deplace,0)) from adsys_audit_multi_agence
                where success_flag ='t'
                and id_ag_distant = $id_agence
                and id_ag_local = numagc()
                and type_transaction = 'depot'";
        $result = $this->getDbConn()->prepareFetchAll($sql);
        $sommeCommOd_depot = $result[0]['sum'];


        /*$sql = "SELECT sum(COALESCE(montant,0)) from ad_mouvement m
                inner join ad_ecriture e on e.id_ecriture = m.id_ecriture
                where e.type_operation in (157)
                and m.sens = 'c'
                and m.compte IN (SELECT cpte_comm_od FROM adsys_multi_agence where id_agc = $id_agence)
                and m.date_valeur between '$date_deb' and '$date_fin'
                and m.id_ag = numagc()";print_rn($sql);*/
        $sql = "SELECT SUM(COALESCE(commission_ope_deplace,0)) from adsys_audit_multi_agence
                where success_flag ='t'
                and id_ag_distant = $id_agence
                and id_ag_local = numagc()
                and type_transaction = 'retrait'";
        $result = $this->getDbConn()->prepareFetchAll($sql);
        $sommeCommOd_retrait = $result[0]['sum'];

      $soldes = array();
      $soldes['solde_deb'] = round($solde_deb, 8);
      $soldes['solde_fin'] = round($solde_fin, 8);
      $soldes['mvmts_deb'] = round($somMvtDebit, 8);
      $soldes['mvmts_cred'] = round($somMvtCredit, 8);
      $soldes['total_depot'] = round($sommeDepots, 8);
      $soldes['total_retrait'] = round($sommeRetraits, 8);
      $soldes['solde_comm_od_depot'] = round($sommeCommOd_depot,8);
      $soldes['solde_comm_od_retrait'] = round($sommeCommOd_retrait,8);
      $soldes['devise'] = $code_devise;

      return $soldes;
    }

}

