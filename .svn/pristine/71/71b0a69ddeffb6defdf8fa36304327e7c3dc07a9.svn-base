<?php

/**
 * Description de la classe Devise
 *
 * @author danilo
 */
require_once 'ad_ma/app/models/BaseModel.php';
                
class Devise extends BaseModel {

    public function __construct(&$dbc, $id_agence = NULL) {
        parent::__construct($dbc, $id_agence);
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Fonction de création du document XML pour le reçu de change
     * 
     * @param int $id_his Numéro de transaction
     * @param  array $DATA
     * 
     * @return code xml
     * */
    public function xmlChangeTaux($id_his, $DATA) {
        global $global_remote_monnaie;

        $document = create_xml_doc("changetaux", "change_taux.dtd");
        //Element root
        $root = $document->root();

        $set = 0;

        if ($DATA["dest_reste"] == "VIDE")
            $set = 0;
        else
            $set = $DATA["affiche_reste"];
        //En-tête généraliste

        $ref = gen_header($root, 'REC-CHG');
        $body = $root->new_child("body", "");
        $body->set_attribute("affiche_reste", $set);

        $body->new_child("id_his", sprintf("%08d", $id_his));

        Divers::setMonnaieCourante($this->getDbConn(), $this->getIdAgence(), $DATA["achat"]["devise"]);
        $body->new_child("mnt_achat", Divers::afficheMontant($DATA["achat"]["mnt"]));
        $body->new_child("devise_achat", $DATA["achat"]["devise"]);
        $body->new_child("source_achat", $DATA["achat"]["source_dest"]);

        Divers::setMonnaieCourante($this->getDbConn(), $this->getIdAgence(), $DATA["vente"]["devise"]);
        setMonnaieCourante($DATA["vente"]["devise"]);
        $body->new_child("mnt_vente", Divers::afficheMontant($DATA["vente"]["mnt"]));
        $body->new_child("devise_vente", $DATA["vente"]["devise"]);
        $body->new_child("dest_vente", $DATA["vente"]["source_dest"]);
        $body->new_child("dest_reste", $DATA["dest_reste"]);

        Divers::setMonnaieCourante($this->getDbConn(), $this->getIdAgence(), $global_remote_monnaie);
        setMonnaieCourante($global_remote_monnaie);
        $body->new_child("devise_ref", $global_remote_monnaie);
        $body->new_child("reste", $DATA["reste"]);
        $body->new_child("dest_reste", $DATA["dest_reste"]);
        $body->new_child("taux", $DATA["taux"]);

        Divers::setMonnaieCourante($this->getDbConn(), $this->getIdAgence(), $DATA["achat"]["devise"]);
        setMonnaieCourante($DATA["achat"]["devise"]);
        $body->new_child("commission", Divers::afficheMontant($DATA["commission"]));
        
        $hasBilletageChange = $DATA["hasBilletageChange"];
        
        // Billetage
        if($hasBilletageChange) {
            
            $body->new_child("hasBilletage", true);
            
            $listTypesBilletArr = $DATA["listTypesBilletArr"];
            $valeurBilletArr = $DATA["valeurBilletArr"];
            $total_billetArr = $DATA["total_billetArr"];
            $global_langue_rapport = $DATA["global_langue_rapport"];
        
            for ($x = 0; $x < count($valeurBilletArr); $x ++) {
                if ($valeurBilletArr[$x] != 'XXXX') {
                    $body->new_child("libel_billet_" . $x, afficheMontant($listTypesBilletArr[$x]['libel']));
                    $body->new_child("valeur_billet_" . $x, $valeurBilletArr[$x]);
                    $body->new_child("total_billet_" . $x, afficheMontant($total_billetArr[$x]));
                }
            }
        }
        
        $xml =  $document->dump_mem(true);        
        $RET = array("xml" => $xml, "ref" => $ref);      
        return $RET;
    }

    /**
     * Retourne tous les champs de la table devise pour une devise donnée ($dev)
     * 
     * @param char(3) code de la devise
     * 
     * @return array
     */
    public function getInfoDevise($code_devise) {

        $sql = "SELECT * FROM devise WHERE code_devise = :code_devise AND id_ag = :id_agence";

        $param_arr = array(':id_agence' => $this->getIdAgence(), ':code_devise' => $code_devise);

        $result = $this->getDbConn()->prepareFetchRow($sql, $param_arr);

        if ($result === FALSE || count($result) == 0) {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        return $result;
    }

    /**
     * Renvoie la valeur du taux dez change devise1 => devise2 en fonction du contexte
     * 
     * @param char(3) $devise1 Code ISO de la devise 1
     * @param char(3) $devise2 Code ISO de la devise 2
     * @param bool $commercial Indique si les taux commerciaux doivent tre utilisés
     * @param defined(1,2)  $type Le type est 1 pour un change cash, 2 pour un change par transfert (utile uniquement si $commercial = true)
     * 
     * @return double Taux de change ou NULL si une des devises n'existe pas
     */
    public function getTauxChange($devise1, $devise2, $commercial, $type = NULL) {
        // Recherche infos devise 1
        $DEV1 = self::getInfoDevise($devise1);

        if (!is_array($DEV1)) { // La devise 1 n'existe pas
            return NULL;
        }

        // Recherche infos devise 2
        $DEV2 = self::getInfoDevise($devise2);

        if (!is_array($DEV2)) { // La devise 2 n'existe pas
            return NULL;
        }

        if (!$commercial) { // C'est le taux indicatif qui dpoit etre utilisé
            $field_taux1 = "taux_indicatif";
            $field_taux2 = "taux_indicatif";
        } else { // On prend le taux achat de $devise1 et le taux vente de $devise2 pour maximiser le bénéfice
            if ($type == 1) { // CASH
                $field_taux1 = "taux_achat_cash";
                $field_taux2 = "taux_vente_cash";
            } else if ($type == 2) { // TRANSFERT
                $field_taux1 = "taux_achat_trf";
                $field_taux2 = "taux_vente_trf";
            }
        }

        // Calcul du taux réel
        $taux_change = round($DEV2[$field_taux2] / $DEV1[$field_taux1], 12);
        return $taux_change;
    }

    /**
     * Calcule le C/V en $devise2 de $motnant exprimé dans $devise1
     * 
     * @param double $montant Montant à changer
     * @param char(3) $devise1 Code ISO de la devise de départ
     * @param char(3) $devise2 Code ISO de la devise de destination
     * 
     * @return double Montant de la C/V
     */
    public function calculeCV($devise1, $devise2, $montant) {

        if ($devise1 == $devise2)
            return $montant;

        $taux = self::getTauxChange($devise1, $devise2, false);

        $cv_montant = $montant * $taux;
        $DEV = self::getInfoDevise($devise2);
        $cv_montant = round($cv_montant, $DEV["precision"]);
        return $cv_montant;
    }

    /**
     * Renvoie le montant qui sera prélevé au titre de commission lorsqu'une opérationd e change a lieu
     * 
     * @param double $montant Montant à changer
     * @param char(3) $devise1 Code ISO de la devise de départ
     * @param char(3) $devise2 Code ISO de la devise de destination
     * 
     * @return double Montant de la commission
     */
    public function calculeCommissionChange($montant, $devise1, $devise2) {

        // Init class
        $AgenceObj = new Agence($this->getDbConn());

        $AG = $AgenceObj->getAgenceDatas($this->getIdAgence());
        $dev_ref = $AG["code_devise_reference"];

        // Destroy object
        unset($AgenceObj);

        // On vérifie d'abord si une commission doit bien etre prélevée
        if ($AG["comm_dev_ref"] == 'f') { // Si la commission ne doit pas etre prélevée sur le change de la devise de référence
            if (($devise1 == $dev_ref) || ($devise2 == $dev_ref))
                return 0;
        }

        // Recherhe de la C/V du minimum à payer dans la devise de la commission
        $contreval_comm_min = self::calculeCV($dev_ref, $devise1, $AG["mnt_min_comm_change"]);

        $prc_com = $AG['prc_comm_change'];
        //Constante de la commission de change
        if ($AG["constante_comm_change"] == "" || $AG["constante_comm_change"] == NULL)
            $contante_commission = 0;
        else
            $contante_commission = $AG["constante_comm_change"];
        //Valeur maximale en tenant compte de la constante de la commission de change
        $mnt_commission = max($contreval_comm_min, $montant * $prc_com + $contante_commission);
        // Arrondi (supérieur) à la plus petite unité monétaire
        $DEV = self::getInfoDevise($devise1);

        $mnt_commission = round($mnt_commission, $DEV["precision"]);

        return $mnt_commission;
    }

    /**
     * Renvoie le montant qui sera prélevé au titre de taxe lors d'une opération de change.<BR>Ce montant est proportionnel à la commission
     * 
     * @param double $montant Commission prélevée
     * @param char(3) $devise1 Code ISO de la devise de départ
     * @param char(3) $devise2 Code ISO de la devise de destination
     * 
     * @return double Montant de la taxe
     */
    public function calculeTaxeChange($montant, $devise1, $devise2) {

        // Init class
        $AgenceObj = new Agence($this->getDbConn());

        $AG = $AgenceObj->getAgenceDatas($this->getIdAgence());
        $dev_ref = $AG["code_devise_reference"];

        // Destroy object
        unset($AgenceObj);

        // On vérifie d'abord si une taxe doit bien etre prélevée
        if ($AG["tax_dev_ref"] == 'f') {
            // Si la taxe ne doit pas etre prélevée sur le change de la devise de référence
            if (($devise1 == $dev_ref) || ($devise2 == $dev_ref))
                return 0;
        }

        $prc_tax = $AG['prc_tax_change'];

        // Recherhe de la C/V du minimum à payer dans la devise de la taxe
        // $taux_dev_ref = getTauxChange($dev_ref, $devise1, false);
        // $contreval_tax_min = $AG["mnt_min_tax_change"] * $taux_dev_ref;
        // $mnt_tax = max($contreval_tax_min, $montant * $prc_tax);
        $mnt_tax = $montant * $prc_tax;

        // Arrondi (supérieur) à la plus petite unité monétaire
        $DEV = self::getInfoDevise($devise1);
        $mnt_tax = round($mnt_tax, $DEV["precision"]);

        return $mnt_tax;
    }

    /**
     * Sépare la taxe et la commission à partir de la commission nette
     * 
     * @param float $comm_nette Montant de la commission nette
     * @param char(3) $devise1 Code ISO de la devise de départ
     * @param char(3) $devise2 Code ISO de la devise de destination
     * 
     * @return Array Tableau ("commission" => Commission, "Taxe" => Taxe)
     */
    public function splitCommissionNette($comm_nette, $devise1, $devise2) {

        // Init class
        $AgenceObj = new Agence($this->getDbConn());

        $AG = $AgenceObj->getAgenceDatas($this->getIdAgence());
        $dev_ref = $AG["code_devise_reference"];

        // Destroy object
        unset($AgenceObj);

        $DEV1 = self::getInfoDevise($devise1);

        // On vérifie d'abord si une taxe doit bien etre prélevée
        if ($AG["tax_dev_ref"] == 'f') {
            // Si la taxe ne doit pas etre prélevée sur le change de la devise de référence
            if (($devise1 == $dev_ref) || ($devise2 == $dev_ref))
                return array("commission" => $comm_nette, "taxe" => 0);
        }

        $prc_tax = $AG['prc_tax_change'];

        $commission = round($comm_nette / (1 + $prc_tax), $DEV1["precision"]);
        $taxe = round($prc_tax * $commission, $DEV1["precision"]);

        // Au cas où le montant ne tomberait pas tout à fait juste suite aux arrondis, ajouter ce qu'il faut du coté de la commission
        $diff = $comm_nette - ($commission + $taxe);
        if ($diff != 0)
            $commission += $diff;

        return array("commission" => $commission, "taxe" => $taxe);
    }

    /**
     * Renvoie le montant réalisé comme bénéfice (ou comme perte) en jouant sur le taux lors d'une opération de change
     * 
     * @param double $montant Montant à changer
     * @param char(3) $devise1 Code ISO de la devise de départ
     * @param char(3) $devise2 Code ISO de la devise de destination
     * @param double $taux Taux de change utilisé pour l'opération
     * @param defined(1,2) $renvoi_devise 1 si résultat en devise1 et 2 si résultat en devise2
     * 
     * @return double Montant du bénéfice sur taux
     */
    public function calculeBeneficeTaux($montant, $devise1, $devise2, $taux, $renvoi_devise = 1) {
        // Recherche du taux indicatif de conversion
        $taux_ind = self::getTauxChange($devise1, $devise2, false);

        if ($renvoi_devise == 1) {
            // Calcul du bénéfice
            $benef_taux = $montant * (($taux_ind - $taux) / $taux_ind);

            // Arrondi à la plus petite unité monétaire
            $DEV = self::getInfoDevise($devise1);
            debug("BNEF TAUX AVANT $benef_taux");
            $benef_taux = round($benef_taux, $DEV["precision"]);
        } else if ($renvoi_devise == 2) {
            // Calcul du bénéfice
            $benef_taux = $montant * ($taux_ind - $taux);

            // Arrondi à la plus petite unité monétaire
            $DEV = self::getInfoDevise($devise2);
            $benef_taux = round($benef_taux, $DEV["precision"]);
        }
        return $benef_taux;
    }

    /**
     * Vérifie si le compte $num_cpte peut être mouvementé dans la devise $devise
     * Impossible si le compte possède déjà une devise différente de la devise $devise
     * Si le compte n'a pas de devise assignée,
     * création d'un sous-compte dans la devise désirée si ce dernier est inexistant
     *
     * @param text $num_cpte
     *        	Numéro du compte
     * @param char(3) $devise
     *        	Code ISO de la devise du mouvement
     *        	
     * @return text Numéro du compte à mouvementer ou NULL si mouvement impossible
     */
    public function checkCptDeviseOK($num_cpte, $devise) {
        global $global_multidevise, $error;

        if ($global_multidevise) {

            // Init class
            $ComptaObj = new Compta($this->getDbConn(), $this->getIdAgence());

            // Recherche des infos sur le compte
            $ACC = $ComptaObj->getComptesComptables(array("num_cpte_comptable" => $num_cpte));

            //debug($ACC,"acc");

            if (sizeof($ACC) != 1) {
                $this->getDbConn()->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }

            $ACC = $ACC[$num_cpte];

            // Si le compte a une devise associée, alors vérifier que c'est la même que celle de l'opération
            if (isset($ACC["devise"])) {
                if ($ACC["devise"] == $devise)
                    return $num_cpte;
                else {
                    return NULL;
                }
            } else {
                // Chercher si le compte possède un sous-compte dans la devise renseignée
                $ACC2 = $ComptaObj->getComptesComptables(array("cpte_centralise" => $num_cpte, "devise" => $devise));
                if (count($ACC2) == 1) {
                    $ACC = array_pop($ACC2);
                    return $ACC["num_cpte_comptable"];
                } else if (count($ACC2) == 0) {
                    // Création du sous-compte dans la devise de l'écriture
                    $sscomptes = array();
                    $sscompte = array();
                    $sscompte["num_cpte_comptable"] = $num_cpte . ".$devise";
                    $sscompte["libel_cpte_comptable"] = $ACC["libel_cpte_comptable"] . "-$devise";
                    $sscompte["solde"] = 0;
                    $sscompte["devise"] = $devise;
                    $sscomptes[$num_cpte . ".$devise"] = $sscompte;

                    $myErr = $ComptaObj->ajoutSousCompteComptable($num_cpte, $sscomptes);
                    if ($myErr->errCode != NO_ERR) {
                        $this->getDbConn()->rollBack(); // Roll back
                        debug(sprintf(_("Problème lors de la création du sous-compte %s"), $num_cpte . $devise) . " : " . $error[$myErr->errCode]);
                        signalErreur(__FILE__, __LINE__, __FUNCTION__);
                    } else
                        return $num_cpte . "." . $devise;
                }
                else {
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("Au moins deux sous-comptes du compte %s existent dans la devise %s"), $num_cpte, $devise));
                }
                return $num_cpte;
            }

            // Destroy object
            unset($ComptaObj);
        } else
            return $num_cpte;
    }

    /**
     * Fonction effectuant une opération de change sans la perception de commissions.
     *
     * Elle n'est normalement jamais appelée directement.
     * @author Thomas FASTENAKEL
     * @param char(3) $devise_achat Code ISO de la devise achetée
     * @param char(3) $devise_vente Code ISO de la devise de la devise vendue
     * @param double $montant Montant à changer exprimé dans la devise d'achat
     * @param int $type_oper Le type d'opération ayant entrainé le change
     * @param array $subst Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
     * @param &array $comptable Liste de mouvements comptables précédemment enregistrés
     * @param bool $mnt_debit Flag : true si $montant exprime le montant à débiter, false si $montant exprime le montant à créditer
     * @param float $cv Contre valeur (= Le montant au crédit), si elle n'est pas donnée on fait un appel à calculeCV {@see calculeCV}.
     * 
     * @return ErrorObj Avec en paramètre un array des montants au débit et au crédit si pas d'erreur, sinon le code de l'erreur.
     */
    public function effectueChangePrivate($devise_achat, $devise_vente, $montant, $type_oper, $subst, &$comptable, $mnt_debit = true, $cv = NULL, $info_ecriture = NULL, $infos_sup = NULL) {
        // Vérifie que les devises sont renseignées
        if ($devise_achat == '' || $devise_vente == '') {
            $this->getDbConn()->rollBack(); // Roll back
            signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Devises non renseignées"));
        }

        // Init class
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());

        if ($devise_achat == $devise_vente) {
            // Pas d'opération de change à réaliser
            $result = $CompteObj->passageEcrituresComptablesAuto($type_oper, $montant, $comptable, $subst, $devise_achat, NULL, $info_ecriture, $infos_sup);
            $montant_debit = $montant;
            $montant_credit = $montant;
        } else {
            if ($mnt_debit == true) {
                // $montant représente un montant à débiter en $devise_achat
                $montant_debit = $montant;
                if ($cv == NULL)
                    $montant_credit = self::calculeCV($devise_achat, $devise_vente, $montant);
                else
                    $montant_credit = $cv;
            } else {
                // $montant représente un montant à créditer en $devise_vente
                $montant_credit = $montant;
                if ($cv == NULL)
                    $montant_debit = self::calculeCV($devise_vente, $devise_achat, $montant);
                else
                    $montant_debit = $cv;
            }

            // On récupère la devise de référence
            global $global_remote_monnaie;
            $dev_ref = $global_remote_monnaie;

            // Passage des écritures relatives à la devise d'achat
            $cptes = $subst;
            if ($devise_achat != $dev_ref) {
                $cpt_devise = self::getCptesLies($devise_achat);
                $cptes["cpta"]["credit"] = $cpt_devise['position'];
            } else {
                $cpt_devise = self::getCptesLies($devise_vente);
                $cptes["cpta"]["credit"] = $cpt_devise['cvPosition'];
            }

            $cptes["int"]["credit"] = NULL;
            $result = $CompteObj->passageEcrituresComptablesAuto($type_oper, $montant_debit, $comptable, $cptes, $devise_achat, NULL, $info_ecriture, $infos_sup);

            if ($result->errCode != NO_ERR) {
                return $result;
            }
            // Passage des écritures relatives à la devise de vente
            $cptes = $subst;
            if ($devise_vente != $dev_ref) {
                $cpt_devise = self::getCptesLies($devise_vente);
                $cptes["cpta"]["debit"] = $cpt_devise['position'];
            } else {
                $cpt_devise = self::getCptesLies($devise_achat);
                $cptes["cpta"]["debit"] = $cpt_devise['cvPosition'];
            }

            $cptes["int"]["debit"] = NULL;
            $result = $CompteObj->passageEcrituresComptablesAuto($type_oper, $montant_credit, $comptable, $cptes, $devise_vente, NULL, $info_ecriture, $infos_sup);

            if ($result->errCode != NO_ERR) {
                return $result;
            }

            // Passage des écritures relatives à la devise de référence (intermédiaire)
            if (($devise_achat != $dev_ref) && ($devise_vente != $dev_ref)) {
                // Recherche de la CV en devise de référence
                $cv_montant_dev_ref = self::calculeCV($devise_achat, $dev_ref, $montant_debit);
                $cptes = $subst;
                $cpt_devise = self::getCptesLies($devise_achat);
                $cptes["cpta"]["debit"] = $cpt_devise['cvPosition'];
                $cptes["int"]["debit"] = NULL;
                $cpt_devise = self::getCptesLies($devise_vente);
                $cptes["cpta"]["credit"] = $cpt_devise['cvPosition'];
                $cptes["int"]["credit"] = NULL;
                $result = $CompteObj->passageEcrituresComptablesAuto($type_oper, $cv_montant_dev_ref, $comptable, $cptes, $dev_ref, NULL, $info_ecriture, $infos_sup);
            }
        }

        // Destroy object
        unset($CompteObj);

        if ($result->errCode != NO_ERR) {
            return $result;
        }

        // Préparation des valeurs de retour
        $param_result = array("montant_debit" => $montant_debit, "montant_credit" => $montant_credit);
        $result = new ErrorObj(NO_ERR, $param_result);

        return $result;
    }

    /**
     * Renvoie tous les numéros de comptes liés à une devise, tels que paramétrés dans ad_agc
     * <ul>
     *  <li>compte de position de change</li>
     *  <li>compte de la contrevaleur de la position de change</li>
     *  <li>compte d'attente de variation de taux au crédit</li>
     *  <li>compte d'attente de variation de taux au débit</li>
     * </ul>
     * 
     * @param char(3) $devise Code ISO de la devise
     * 
     * @return array les quatres numéros de compte
     */
    public function getCptesLies($devise) {

        $comptes = array();

        // Init class
        $AgenceObj = new Agence($this->getDbConn());

        $AG = $AgenceObj->getAgenceDatas($this->getIdAgence());

        // Destroy object
        unset($AgenceObj);

        $cpt_pos_ch = $AG["cpte_position_change"];
        $cpt_cv_pos_ch = $AG["cpte_contreval_position_change"];
        $cpt_credit = $AG["cpte_variation_taux_cred"];
        $cpt_debit = $AG["cpte_variation_taux_deb"];
        $comptes['position'] = $cpt_pos_ch . "." . $devise;
        $comptes['cvPosition'] = $cpt_cv_pos_ch . "." . $devise;
        $comptes['debit'] = $cpt_debit . "." . $devise;
        $comptes['credit'] = $cpt_credit . "." . $devise;

        return $comptes;
    }

    /**
     * Effectue une opération de change de haut niveau
     * Càd qu'elle perçoit d'abord les divers frais et commissions avant d'effectuer le change lui-même
     * 
     * @param char(3) $devise_achat Code ISO de la devise achetée
     * @param char(3) $devise2 Code ISO de la devise de la devise vendue
     * @param double $montant Montant à changer exprimé dans la devise d'achat
     * @param double $cv_montant Montant que l'on est censé obtenir après l'opération de change. Ce paramètre est nécessaire pour vérifier que l'utilisateur a utilisé correctement le logiciel.
     * @param int $type_oper Le type d'opération ayant entrainé le change
     * @param array $subst Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
     * @param &array $comptable Liste de mouvements comptables précédemment enregistrés
     * @param defined(1,2,3) $destination_reste Indique que faire du reste de l'opération de change (1 = versement au guichet, 2 = versement sur compte de base, 3 = intégration dans produits)
     * @param double $commission Montant prélevé au titre de commission (optionnel)
     * @param double $taxe Montant prélevé au titre de taxe (optionnel)
     * @param float $taux : Taux appliqué afind e réaliser un bénéfice supplémentaire sur le taux
     * @param boolean $is_guichet : true si le change se fait au guichet
     * @param int info_ecriture : information de l'ecriture comtable à passer : (dans le cas de l'epargne le id_cpte du compte source)
     * 
     * @return array Array comptable à passer à ajout_historique
     */
    public function change($devise_achat, $devise_vente, $montant, $cv_montant, $type_oper, $subst, &$comptable, $destination_reste, $commissionnette = NULL, $taux = NULL, $is_guichet = NULL, $infos_sup = NULL)
    {
        global $global_remote_monnaie, $global_id_agence;
        
        $dev_ref = $global_remote_monnaie;
        
        $DEV_A = self::getInfoDevise($devise_achat);
        $DEV_V = self::getInfoDevise($devise_vente);
        
        // Init :
        $commission = 0;
        $taxe = 0;
        $benef_taux = 0;
        
        // Récupère la commission de change si non précisé
        if (! isset($commissionnette)) {
            $commission = self::calculeCommissionChange($montant, $devise_achat, $devise_vente);
            $taxe = self::calculeTaxeChange($commission, $devise_achat, $devise_vente);
        } else {
            $SPLIT = self::splitCommissionNette($commissionnette, $devise_achat, $devise_vente);
            $commission = $SPLIT["commission"];
            $taxe = $SPLIT["taxe"];
        }
        
        // Récupère le bénéfice sur le taux si le taux n'est pas précisé
        if (! isset($taux)) {
            $taux = self::getTauxChange($devise_achat, $devise_vente, true, 1);
        }
        $benef_taux = self::calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux);
        
        // Init class
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());
        
        // Prélèvement de la commission
        /*
         * if ($commission > 0) { // Construction de l'array de substitution : // Compte au D = compte source du change $array_cptes = array(); $array_cptes["int"]["debit"] = $subst["int"]["debit"]; $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"]; if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change // Compte au C = compte de produit de commission de la devise de vente $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"]; $myErr = $CompteObj->passageEcrituresComptablesAuto(450, $commission, $comptable, $array_cptes, $dev_ref, NULL, $info_ecriture); if ($myErr->errCode != NO_ERR) { return $myErr; } } else { // Mouvement de la position de change // Compte au C = compte de produit de commission de la devise d'achat $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"]; $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $commission, 450, $array_cptes, $comptable, NULL, NULL, $info_ecriture); if ($myErr->errCode != NO_ERR) { return $myErr; } } }
         */
        
        // Prélèvement de la taxe
        /*
         * if ($taxe > 0) { // Construction de l'array de substitution $array_cptes = array(); $array_cptes["int"]["debit"] = $subst["int"]["debit"]; $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"]; if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change $myErr = $CompteObj->passageEcrituresComptablesAuto(451, $taxe, $comptable, $array_cptes, $dev_ref); if ($myErr->errCode != NO_ERR) { return $myErr; } } else { // Mouvement de la position de change $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $taxe, 451, $array_cptes, $comptable); if ($myErr->errCode != NO_ERR) { return $myErr; } } }
         */
        
        // Prélèvement du bénéfice par jeu sur le taux
        /*
         * if ($benef_taux > 0) { // Construction de l'array de substitution $array_cptes = array(); $array_cptes["int"]["debit"] = $subst["int"]["debit"]; $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"]; if ($devise_achat == $dev_ref) { // Pas de mouvement de la position de change // Compte au C = compte de produit de commission de la devise de vente $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_taux"]; $myErr = $CompteObj->passageEcrituresComptablesAuto(452, $benef_taux, $comptable, $array_cptes, $dev_ref); if ($myErr->errCode != NO_ERR) { return $myErr; } } else { // Mouvement de la position de change // Compte au C = compte de produit de commission de la devise d'achat $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_taux"]; $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $benef_taux, 452, $array_cptes, $comptable); if ($myErr->errCode != NO_ERR) { return $myErr; } } } else if ($benef_taux < 0) { // Cas d'une vente de devise à perte // On va plutot exprimer $benef_taux dans la devise vendue //$benef_taux_dev $benef_taux_dev_vente = self::calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux, 2); // Sera utilisé ultérieurement $perte_change = abs($benef_taux_dev_vente); // Construction de l'array de substitution $array_cptes = array(); $array_cptes["int"]["credit"] = $subst["int"]["credit"]; $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"]; if ($devise_vente == $dev_ref) { // Pas de mouvement de la position de change // Compte au D = compte de perte de change de la devise $array_cptes["cpta"]["debit"] = $DEV_V["cpte_perte_taux"]; $myErr = $CompteObj->passageEcrituresComptablesAuto(458, abs($benef_taux_dev_vente), $comptable, $array_cptes, $dev_ref); if ($myErr->errCode != NO_ERR) { return $myErr; } } else { // Mouvement de la position de change // Compte au D = compte de perte de change $array_cptes["cpta"]["debit"] = $DEV_V["cpte_perte_taux"]; $myErr = self::effectueChangePrivate($dev_ref, $devise_vente, abs($benef_taux_dev_vente), 458, $array_cptes, $comptable, false); if ($myErr->errCode != NO_ERR) { return $myErr; } } // La perte ayant déjà été comptabilisée, la suite se déroule comme si on changeait au taux indicatif $benef_taux = 0; $taux = self::getTauxChange($devise_achat, $devise_vente, false); $cv_montant = self::calculeCV($devise_achat, $devise_vente, $montant - $commission - $taxe); }
         */
        // Opération de change proprement dite
        // Le montant brut = le montant à changer uen fois les commissions et taxes retirées
        $mnt_brut = $montant - $commission - $taxe;
        // Le montant réel à changer = le montant réel qui est changé (par rapport au taux indicatif)
        $mnt_change = $mnt_brut - $benef_taux;
        // La C/V calculée = la C/V du montant réellement changée
        $cv_calculee = self::calculeCV($devise_achat, $devise_vente, $mnt_change);
        // On peut aussi calculer la C/V en diminuant le risque d'erreur sur base du montant brut
        $cv_calculee_rapport_mnt_brut = round(($mnt_brut * $taux), $DEV_V["precision"]);
        
        // Vérifie que ce montant est bien conforme aux attentes
        // Si il s'agit d'une opération au guichet, la C/V passée est une C/V arrondie inférieure au plus petit billet. Il faut donc y ajouter le reste en devise vente avant de faire le test d'équivalence
        $cpt_credit = $subst["cpta"]["credit"];
        
        if (isCompteGuichet($cpt_credit) || $is_guichet) {
            $reste_dev_vente = ($cv_calculee_rapport_mnt_brut + $perte_change) - (Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $cv_calculee_rapport_mnt_brut + $perte_change, - 1, $devise_vente));
            
            if (round($reste_dev_vente, $DEV_V["precision"] > 0)) { // Il y a un reliquiat à traiter
                                                                    // On cherche la C/V en devise de référence de ce reste
                $reste_dev_ref = self::calculeCV($devise_vente, $dev_ref, $reste_dev_vente);
                // On ne fait le change que si le montant peut tre remis au client
                $reste_dev_ref_arrondi_billet = Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $reste_dev_ref, - 1, $dev_ref);
            } else {
                $reste_dev_ref_arrondi_billet = 0;
            }
        } else {
            $reste_dev_ref_arrondi_billet = 0;
        }
        
        if (! estEquivalent($mnt_change, $devise_achat, $cv_montant + $reste_dev_vente, $devise_vente)) {
            // Quelque chose n'est pas clair ...
            return new ErrorObj(ERR_MNT_NON_EQUIV, _("pas d'équivalence'"));
        } else { // A partir de maintenant c'est $cv_montant qui fera foi
            $cv_mnt_change = $cv_montant;
        }
        
        debug($reste_dev_ref_arrondi_billet, "reste_dev_ref_arrondi_billet");
        
        // *********** Gestion des arrondis *********
        
        if ($reste_dev_ref_arrondi_billet > 0) {
            $array_cptes = array();
            $array_cptes["int"]["debit"] = $subst["int"]["debit"];
            $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
            
            // Recherche du montant à changer dans la devise de départ
            $cv_reste_dev_achat = self::calculeCV($devise_vente, $devise_achat, $reste_dev_vente);
            switch ($destination_reste) {
                case 1: // Versement au guichet
                    global $global_id_guichet;
                    $id_gui = $global_id_guichet; // FIXME Je sais que je ne devrais pas ...
                                                  // $array_cptes["cpta"]["credit"] = $CompteObj->getCompteCptaGui($id_gui);
                    $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"]; // AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                    $type_oper_rest = 455;
                    break;
                case 2: // Versement sur compte de base
                    global $global_remote_id_client; // FIXME Je sais que je ne devrais pas ...
                    $id_cpt_base = $CompteObj->getBaseAccountID($global_remote_id_client);
                    $array_cptes["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($id_cpt_base);
                    if ($array_cptes["cpta"]["credit"] == NULL) {
                        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                    }
                    $array_cptes["int"]["credit"] = $id_cpt_base;
                    $type_oper_rest = 456;
                    break;
                case 3:
                    $type_oper_rest = 457;
                    break;
                default:
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "le paramètre 'destination_change' est incorrect : $destination_change"
            }
            
            // Destroy object
            unset($CompteObj);
            
            $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $cv_reste_dev_achat, $type_oper_rest, $array_cptes, $comptable);
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
            $mnt_change -= $cv_reste_dev_achat;
        }
        
        $myErr = self::effectueChangePrivate($devise_achat, $devise_vente, $mnt_change, $type_oper, $subst, $comptable, true, $cv_montant, NULL, $infos_sup);
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        return new ErrorObj(NO_ERR);
    }
    


    /**
     * Effectue une opération de change de haut niveau sur l'agence distante lors d'un depot en deplacé
     * Càd qu'elle perçoit d'abord les divers frais et commissions avant d'effectuer le change lui-mme
     *
     * @author BD
     * @param char(3) $devise_achat Code ISO de la devise achetée
     * @param char(3) $devise2 Code ISO de la devise de la devise vendue
     * @param double $montant Montant à changer exprimé dans la devise d'achat
     * @param double $cv_montant Montant que l'on est censé obtenir après l'opération de change. Ce paramètre est nécessaire pour vérifier que l'utilisateur a utilisé correctement le logiciel.
     * @param int $type_oper Le type d'opération ayant entrainé le change
     * @param array $subst Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
     * @param &array $comptable Liste de mouvements comptables précédemment enregistrés
     * @param defined(1,2,3) $destination_reste Indique que faire du reste de l'opération de change (1 = versement au guichet, 2 = versement sur compte de base, 3 = intégration dans produits)
     * @param double $commission Montant prélevé au titre de commission (optionnel)
     * @param double $taxe Montant prélevé au titre de taxe (optionnel)
     * @param float $taux : Taux appliqué afind e réaliser un bénéfice supplémentaire sur le taux
     * @param boolean $is_guichet : true si le change se fait au guichet
     * @param int info_ecriture : information de l'ecriture comtable à passer : (dans le cas de l'epargne le id_cpte du compte source)
     *
     * @return array Array comptable à passer à ajout_historique
     */
    public function changeDepotRemote($devise_achat, $devise_vente, $montant, $cv_montant, $type_oper, $subst, &$comptable, $destination_reste, $commissionnette = NULL, $taux = NULL, $is_guichet = NULL, $infos_sup = NULL)
    {
        require_once 'lib/dbProcedures/agence.php';
        
        global $global_remote_monnaie, $global_id_agence;
        
        $dev_ref = $global_remote_monnaie;
        
        $DEV_A = self::getInfoDevise($devise_achat);
        $DEV_V = self::getInfoDevise($devise_vente);
        
        // Init class
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());
        $ComptaObj = new Compta($this->getDbConn(), $this->getIdAgence());
        
        // Init :
        $commission = 0;
        $taxe = 0;
        $benef_taux = 0;
        
        $toPreleveCommissionsDansAgenceRemote = false;
        
        // verification si les commissions doivent etre prelevés sur l'agence distante
        $toPreleveCommissionsDansAgenceLocal = getWherePerceptionCommissionsMultiAgence();
        
        if (! $toPreleveCommissionsDansAgenceLocal) {
            $toPreleveCommissionsDansAgenceRemote = true;
        }
        
        // Récupère la commission de change si non précisé
        if (! isset($commissionnette)) {
            $commission = $this->calculeCommissionChange($montant, $devise_achat, $devise_vente);
            $taxe = $this->calculeTaxeChange($commission, $devise_achat, $devise_vente);
        } else {
            $SPLIT = $this->splitCommissionNette($commissionnette, $devise_achat, $devise_vente);
            $commission = $SPLIT["commission"];
            $taxe = $SPLIT["taxe"];
        }
        
        // Récupère le bénéfice sur le taux si le taux n'est pas précisé
        if (! isset($taux)) {
            $taux = $this->getTauxChange($devise_achat, $devise_vente, true, 1);
        }
        $benef_taux = $this->calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux);
        
        // Traitement si les commissions doivent etre prelevés
        if ($toPreleveCommissionsDansAgenceRemote) {
            // Prélèvement de la commission
            if ($commission > 0) {
                // Construction de l'array de substitution :
                
                // Compte au D = compte source du change
                $array_cptes = array();
                $array_cptes["int"]["debit"] = $subst["int"]["debit"];
                $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
                
                // Compte au C = compte de produit de commission de la devise d'achat
                $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];
                
                $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $commission, 450, $array_cptes, $comptable, TRUE, NULL, $info_ecriture);
                
                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            } // end :prelevement commissions           
        }         // End : traitement prelevements commissions + taxes
        
        // We need to amend the effective amount to be changed by subtracting the commissions      
        
        // Opération de change proprement dite
        // Le montant brut = le montant à changer uen fois les commissions et taxes retirées
        $mnt_brut = $montant - $commission - $taxe;
        // Le montant réel à changer = le montant réel qui est changé (par rapport au taux indicatif)
        $mnt_change = $mnt_brut - $benef_taux;
        // La C/V calculée = la C/V du montant réellement changée
        $cv_calculee = self::calculeCV($devise_achat, $devise_vente, $mnt_change);
        // On peut aussi calculer la C/V en diminuant le risque d'erreur sur base du montant brut
        $cv_calculee_rapport_mnt_brut = round(($mnt_brut * $taux), $DEV_V["precision"]);
        
        // Vérifie que ce montant est bien conforme aux attentes
        // Si il s'agit d'une opération au guichet, la C/V passée est une C/V arrondie inférieure au plus petit billet. Il faut donc y ajouter le reste en devise vente avant de faire le test d'équivalence
        $cpt_credit = $subst["cpta"]["credit"];
        $isCompteGuichet = $ComptaObj->isCompteGuichet($cpt_credit);
        
        if ($isCompteGuichet || $is_guichet) {
            $reste_dev_vente = ($cv_calculee_rapport_mnt_brut + $perte_change) - (Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $cv_calculee_rapport_mnt_brut + $perte_change, - 1, $devise_vente));
            
            if (round($reste_dev_vente, $DEV_V["precision"] > 0)) { // Il y a un reliquiat à traiter
                                                                    // On cherche la C/V en devise de référence de ce reste
                $reste_dev_ref = self::calculeCV($devise_vente, $dev_ref, $reste_dev_vente);
                // On ne fait le change que si le montant peut tre remis au client
                $reste_dev_ref_arrondi_billet = Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $reste_dev_ref, - 1, $dev_ref);
            } else {
                $reste_dev_ref_arrondi_billet = 0;
            }
        } else {
            $reste_dev_ref_arrondi_billet = 0;
        }
        $isEquivalent = self::estEquivalent($mnt_change, $devise_achat, $cv_montant + $reste_dev_vente, $devise_vente);

        
        if (! $isEquivalent) {
            // Quelque chose n'est pas clair ...
            return new ErrorObj(ERR_MNT_NON_EQUIV, _("pas d'équivalence'"));
        } else { // A partir de maintenant c'est $cv_montant qui fera foi
            $cv_mnt_change = $cv_montant;
        }
        
        // *********** Gestion des arrondis *********
        
        if ($reste_dev_ref_arrondi_billet > 0) {
            $array_cptes = array();
            $array_cptes["int"]["debit"] = $subst["int"]["debit"];
            $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
            
            // Recherche du montant à changer dans la devise de départ
            $cv_reste_dev_achat = self::calculeCV($devise_vente, $devise_achat, $reste_dev_vente);
            
            switch ($destination_reste) {
                case 1: // Versement au guichet
                    global $global_id_guichet;
                    $id_gui = $global_id_guichet; // FIXME Je sais que je ne devrais pas ...                                                  
                    $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"]; // AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                    $type_oper_rest = 455;
                    break;
                
                case 2: // Versement sur compte de base
                    global $global_remote_id_client; // FIXME Je sais que je ne devrais pas ...
                    $id_cpt_base = $CompteObj->getBaseAccountID($global_remote_id_client);
                    $array_cptes["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($id_cpt_base);
                    
                    if ($array_cptes["cpta"]["credit"] == NULL) {
                        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                    }
                    
                    $array_cptes["int"]["credit"] = $id_cpt_base;
                    $type_oper_rest = 456;
                    break;
                
                case 3:
                    $type_oper_rest = 457;
                    break;
                
                default:
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "le paramètre 'destination_change' est incorrect : $destination_change"
            }
            
            $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $cv_reste_dev_achat, $type_oper_rest, $array_cptes, $comptable);
            
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
            $mnt_change -= $cv_reste_dev_achat;
        }
        // Fin : Gestion des arrondies
        
        // Change :        
        $myErr = self::effectueChangePrivate($devise_achat, $devise_vente, $mnt_change, $type_oper, $subst, $comptable, true, $cv_montant, NULL, $infos_sup);
        
        unset($ComptaObj);
        unset($CompteObj);
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        return new ErrorObj(NO_ERR);
    }

    /**
     * Effectue l'inverse d'un opération de change de haut niveau sur l'agence distante lors d'un depot en deplacé
     * Càd qu'elle perçoit d'abord les divers frais et commissions avant d'effectuer le change lui-mme
     *
     * @author BD
     * @param char(3) $devise_achat
     *            Code ISO de la devise achetée
     * @param char(3) $devise2
     *            Code ISO de la devise de la devise vendue
     * @param double $montant
     *            Montant à changer exprimé dans la devise d'achat
     * @param double $cv_montant
     *            Montant que l'on est censé obtenir après l'opération de change. Ce paramètre est nécessaire pour vérifier que l'utilisateur a utilisé correctement le logiciel.
     * @param int $type_oper
     *            Le type d'opération ayant entrainé le change
     * @param array $subst
     *            Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
     * @param &array $comptable
     *            Liste de mouvements comptables précédemment enregistrés
     * @param defined(1,2,3) $destination_reste
     *            Indique que faire du reste de l'opération de change (1 = versement au guichet, 2 = versement sur compte de base, 3 = intégration dans produits)
     * @param double $commission
     *            Montant prélevé au titre de commission (optionnel)
     * @param double $taxe
     *            Montant prélevé au titre de taxe (optionnel)
     * @param float $taux
     *            : Taux appliqué afind e réaliser un bénéfice supplémentaire sur le taux
     * @param boolean $is_guichet
     *            : true si le change se fait au guichet
     * @param
     *            int info_ecriture : information de l'ecriture comtable à passer : (dans le cas de l'epargne le id_cpte du compte source)
     *            
     * @return array Array comptable à passer à ajout_historique
     */
    public function changeDepotRemoteRevert($devise_achat, $devise_vente, $montant, $cv_montant, $type_oper, $subst, &$comptable, $destination_reste, $commissionnette = NULL, $taux = NULL, $is_guichet = NULL, $infos_sup = NULL)
    {
        require_once 'lib/dbProcedures/agence.php';
        
        global $global_remote_monnaie, $global_id_agence;
        
        $dev_ref = $global_remote_monnaie;
        
        $DEV_A = self::getInfoDevise($devise_achat);
        $DEV_V = self::getInfoDevise($devise_vente);
        
        // Init class
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());
        $ComptaObj = new Compta($this->getDbConn(), $this->getIdAgence());
        
        // Inverser les montants
        if($montant > 0) {
            $montant = 0 - $montant;
        }
        
        if($cv_montant > 0) {
            $cv_montant = 0 - $cv_montant;
        }
        
        // Init :
        $commission = 0;
        $taxe = 0;
        $benef_taux = 0;      
        
        $toPreleveCommissionsDansAgenceRemote = false;
        
        // verification si les commissions doivent etre prelevés sur l'agence distante
        $toPreleveCommissionsDansAgenceLocal = getWherePerceptionCommissionsMultiAgence();
        
        if (! $toPreleveCommissionsDansAgenceLocal) {
            $toPreleveCommissionsDansAgenceRemote = true;
        }
        
        // Récupère la commission de change si non précisé
        if (! isset($commissionnette)) {
            $commission = $this->calculeCommissionChange($montant, $devise_achat, $devise_vente);
            $taxe = $this->calculeTaxeChange($commission, $devise_achat, $devise_vente);
        } else {
            $SPLIT = $this->splitCommissionNette($commissionnette, $devise_achat, $devise_vente);
            $commission = $SPLIT["commission"];
            $taxe = $SPLIT["taxe"];
        }
        
        // Récupère le bénéfice sur le taux si le taux n'est pas précisé
        if (! isset($taux)) {
            $taux = $this->getTauxChange($devise_achat, $devise_vente, true, 1);
        }
        $benef_taux = $this->calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux);
        
        // Inverser les montants pour l'operation de renversement du retrait
        if($montant < 0) {
            $commission = 0 - $commission;
            $taxe = 0 - $taxe;
            $benef_taux = 0 - $benef_taux;
        }
        
        
        // Traitement si les commissions doivent etre prelevés
        if ($toPreleveCommissionsDansAgenceRemote) {
            // Prélèvement de la commission
            if ($commission != 0) {
                // Construction de l'array de substitution :
                
                // Compte au D = compte source du change
                $array_cptes = array();
                $array_cptes["int"]["debit"] = $subst["int"]["debit"];
                $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
                
                // Compte au C = compte de produit de commission de la devise d'achat
                $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];
                
                $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $commission, 450, $array_cptes, $comptable, TRUE, NULL, $info_ecriture);
                
                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            } // end :prelevement commissions
                  
            // @todo : taxes?
        } // End : traitement prelevements commissions + taxes
          
        // We need to amend the effective amount to be changed by subtracting the commissions
          
        // Opération de change proprement dite
          // Le montant brut = le montant à changer uen fois les commissions et taxes retirées
        $mnt_brut = $montant - $commission - $taxe;
        // Le montant réel à changer = le montant réel qui est changé (par rapport au taux indicatif)
        $mnt_change = $mnt_brut - $benef_taux;
        // La C/V calculée = la C/V du montant réellement changée
        $cv_calculee = self::calculeCV($devise_achat, $devise_vente, $mnt_change);
        // On peut aussi calculer la C/V en diminuant le risque d'erreur sur base du montant brut
        $cv_calculee_rapport_mnt_brut = round(($mnt_brut * $taux), $DEV_V["precision"]);
        
        // Vérifie que ce montant est bien conforme aux attentes
        // Si il s'agit d'une opération au guichet, la C/V passée est une C/V arrondie inférieure au plus petit billet. Il faut donc y ajouter le reste en devise vente avant de faire le test d'équivalence
        $cpt_credit = $subst["cpta"]["credit"];
        $isCompteGuichet = $ComptaObj->isCompteGuichet($cpt_credit);
        
        if ($isCompteGuichet || $is_guichet) {
            $reste_dev_vente = ($cv_calculee_rapport_mnt_brut + $perte_change) - (Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $cv_calculee_rapport_mnt_brut + $perte_change, - 1, $devise_vente));
            
            if (round($reste_dev_vente, $DEV_V["precision"] > 0)) { // Il y a un reliquiat à traiter
                                                                    // On cherche la C/V en devise de référence de ce reste
                $reste_dev_ref = self::calculeCV($devise_vente, $dev_ref, $reste_dev_vente);
                // On ne fait le change que si le montant peut tre remis au client
                $reste_dev_ref_arrondi_billet = Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $reste_dev_ref, - 1, $dev_ref);
            } else {
                $reste_dev_ref_arrondi_billet = 0;
            }
        } else {
            $reste_dev_ref_arrondi_billet = 0;
        }
        
        $isEquivalent = self::estEquivalent($mnt_change, $devise_achat, $cv_montant + $reste_dev_vente, $devise_vente);
        
        if (! $isEquivalent) {
            // Quelque chose n'est pas clair ...
            return new ErrorObj(ERR_MNT_NON_EQUIV, _("pas d'équivalence'"));
        } else { // A partir de maintenant c'est $cv_montant qui fera foi
            $cv_mnt_change = $cv_montant;
        }
        
        // *********** Gestion des arrondis *********
        
        if ($reste_dev_ref_arrondi_billet > 0) {
            $array_cptes = array();
            $array_cptes["int"]["debit"] = $subst["int"]["debit"];
            $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
            
            // Recherche du montant à changer dans la devise de départ
            $cv_reste_dev_achat = self::calculeCV($devise_vente, $devise_achat, $reste_dev_vente);
            
            switch ($destination_reste) {
                case 1: // Versement au guichet
                    global $global_id_guichet;
                    $id_gui = $global_id_guichet; // FIXME Je sais que je ne devrais pas ...
                    $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"]; // AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                    $type_oper_rest = 455;
                    break;
                
                case 2: // Versement sur compte de base
                    global $global_remote_id_client; // FIXME Je sais que je ne devrais pas ...
                    $id_cpt_base = $CompteObj->getBaseAccountID($global_remote_id_client);
                    $array_cptes["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($id_cpt_base);
                    
                    if ($array_cptes["cpta"]["credit"] == NULL) {
                        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                    }
                    
                    $array_cptes["int"]["credit"] = $id_cpt_base;
                    $type_oper_rest = 456;
                    break;
                
                case 3:
                    $type_oper_rest = 457;
                    break;
                
                default:
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "le paramètre 'destination_change' est incorrect : $destination_change"
            }
            
            $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $cv_reste_dev_achat, $type_oper_rest, $array_cptes, $comptable);
            
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
            $mnt_change -= $cv_reste_dev_achat;
        }
        // Fin : Gestion des arrondies
        
        // Change :
        $myErr = self::effectueChangePrivate($devise_achat, $devise_vente, $mnt_change, $type_oper, $subst, $comptable, true, $cv_montant, NULL, $infos_sup);
        
        unset($ComptaObj);
        unset($CompteObj);
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        return new ErrorObj(NO_ERR);
    }
    
    /**
     * Effectue une opération de change de haut niveau sur l'agence distante lors d'un retrait en deplacé
     * Càd qu'elle perçoit d'abord les divers frais et commissions avant d'effectuer le change lui-mme
     *
     * @author BD
     * @param char(3) $devise_achat
     *            Code ISO de la devise achetée
     * @param char(3) $devise2
     *            Code ISO de la devise de la devise vendue
     * @param double $montant
     *            Montant à changer exprimé dans la devise d'achat
     * @param double $cv_montant
     *            Montant que l'on est censé obtenir après l'opération de change. Ce paramètre est nécessaire pour vérifier que l'utilisateur a utilisé correctement le logiciel.
     * @param int $type_oper
     *            Le type d'opération ayant entrainé le change
     * @param array $subst
     *            Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
     * @param &array $comptable
     *            Liste de mouvements comptables précédemment enregistrés
     * @param defined(1,2,3) $destination_reste
     *            Indique que faire du reste de l'opération de change (1 = versement au guichet, 2 = versement sur compte de base, 3 = intégration dans produits)
     * @param double $commission
     *            Montant prélevé au titre de commission (optionnel)
     * @param double $taxe
     *            Montant prélevé au titre de taxe (optionnel)
     * @param float $taux
     *            : Taux appliqué afind e réaliser un bénéfice supplémentaire sur le taux
     * @param boolean $is_guichet
     *            : true si le change se fait au guichet
     * @param
     *            int info_ecriture : information de l'ecriture comtable à passer : (dans le cas de l'epargne le id_cpte du compte source)
     *            
     * @return array Array comptable à passer à ajout_historique
     */
    public function changeRetraitRemote($devise_achat, $devise_vente, $montant, $cv_montant, $type_oper, $subst, &$comptable, $destination_reste, $commissionnette = NULL, $taux = NULL, $is_guichet = NULL, $infos_sup = NULL)
    {
        require_once 'lib/dbProcedures/agence.php';
        
        global $global_remote_monnaie, $global_id_agence;
                
        $dev_ref = $global_remote_monnaie;
        
        $DEV_A = self::getInfoDevise($devise_achat);
        $DEV_V = self::getInfoDevise($devise_vente);
        
        // Init class
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());
        $ComptaObj = new Compta($this->getDbConn(), $this->getIdAgence());
        
        // Init :
        $commission = 0;
        $taxe = 0;
        $benef_taux = 0;
        
        $toPreleveCommissionsDansAgenceRemote = false;
        
        // verification si les commissions doivent etre prelevés sur l'agence distante
        $toPreleveCommissionsDansAgenceLocal = getWherePerceptionCommissionsMultiAgence();
        
        if (! $toPreleveCommissionsDansAgenceLocal) {
            $toPreleveCommissionsDansAgenceRemote = true;
        }
        
        // Récupère la commission de change si non précisé
        if (! isset($commissionnette)) {
            $commission = $this->calculeCommissionChange($montant, $devise_achat, $devise_vente);
            $taxe = $this->calculeTaxeChange($commission, $devise_achat, $devise_vente);
        } else {
            $SPLIT = $this->splitCommissionNette($commissionnette, $devise_achat, $devise_vente);
            $commission = $SPLIT["commission"];
            $taxe = $SPLIT["taxe"];
        }
        
        // Récupère le bénéfice sur le taux si le taux n'est pas précisé
        if (! isset($taux)) {
            $taux = $this->getTauxChange($devise_achat, $devise_vente, true, 1);
        }
        $benef_taux = $this->calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux);
        
        // Traitement si les commissions doivent etre prelevés dans l'agence distante
        if ($toPreleveCommissionsDansAgenceRemote) {
            // Prélèvement de la commission
            if ($commission > 0) {
                // Construction de l'array de substitution :
                
                // Compte au D = compte source du change
                $array_cptes = array();
                $array_cptes["int"]["debit"] = $subst["int"]["debit"];
                $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
                
                // Compte au C = compte de produit de commission de la devise d'achat
                $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];
                
                $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $commission, 450, $array_cptes, $comptable, TRUE, NULL, $info_ecriture);
                                
                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            } // end :prelevement commissions
                  
            // @todo : taxes?
        }         // End : traitement prelevements commissions + taxes
        else {
            /*
             * Si les commissions ne sont pas prelevés dans l'agence distante, il faut prelever le montant de la 
             * commission depuis le compte du client pour passer au compte de liaison de l'agence locale dans la devise
             * d'achat (KMF).
             */
            
            if($commission > 0) {
                $array_cptes = array();
                $array_cptes["int"]["debit"] = $subst["int"]["debit"];
                $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
                $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"]; // AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                
                $result = $CompteObj->passageEcrituresComptablesAuto(450, $commission, $comptable, $array_cptes, $devise_achat, NULL, $info_ecriture, NULL);
                                
                if ($result->errCode != NO_ERR) {
                    return $result;
                }    
            }
            // @todo : taxes etc. ?
           
        }
        
        // We need to amend the effective amount to be changed by subtracting the commissions
        
        // Opération de change proprement dite
        // Le montant brut = le montant à changer uen fois les commissions et taxes retirées
        $mnt_brut = $montant - $commission - $taxe;
        // Le montant réel à changer = le montant réel qui est changé (par rapport au taux indicatif)
        $mnt_change = $mnt_brut - $benef_taux;
        // La C/V calculée = la C/V du montant réellement changée
        $cv_calculee = self::calculeCV($devise_achat, $devise_vente, $mnt_change);
        // On peut aussi calculer la C/V en diminuant le risque d'erreur sur base du montant brut
        $cv_calculee_rapport_mnt_brut = round(($mnt_brut * $taux), $DEV_V["precision"]);
        
        // Vérifie que ce montant est bien conforme aux attentes
        // Si il s'agit d'une opération au guichet, la C/V passée est une C/V arrondie inférieure au plus petit billet. Il faut donc y ajouter le reste en devise vente avant de faire le test d'équivalence
        $cpt_credit = $subst["cpta"]["credit"];
        $isCompteGuichet = $ComptaObj->isCompteGuichet($cpt_credit);
        
        if ($isCompteGuichet || $is_guichet) {
            $reste_dev_vente = ($cv_calculee_rapport_mnt_brut + $perte_change) - (Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $cv_calculee_rapport_mnt_brut + $perte_change, - 1, $devise_vente));
            
            if (round($reste_dev_vente, $DEV_V["precision"] > 0)) { // Il y a un reliquiat à traiter
                                                                    // On cherche la C/V en devise de référence de ce reste
                $reste_dev_ref = self::calculeCV($devise_vente, $dev_ref, $reste_dev_vente);
                // On ne fait le change que si le montant peut tre remis au client
                $reste_dev_ref_arrondi_billet = Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $reste_dev_ref, - 1, $dev_ref);
            } else {
                $reste_dev_ref_arrondi_billet = 0;
            }
        } else {
            $reste_dev_ref_arrondi_billet = 0;
        }
        
        $isEquivalent = self::estEquivalent($mnt_change, $devise_achat, $cv_montant + $reste_dev_vente, $devise_vente);
        
        if (! $isEquivalent) {
            // Quelque chose n'est pas clair ...
            return new ErrorObj(ERR_MNT_NON_EQUIV, _("pas d'équivalence'"));
        } else { // A partir de maintenant c'est $cv_montant qui fera foi
            $cv_mnt_change = $cv_montant;
        }
        
        // *********** Gestion des arrondis *********
        
        if ($reste_dev_ref_arrondi_billet > 0) {
            $array_cptes = array();
            $array_cptes["int"]["debit"] = $subst["int"]["debit"];
            $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
            
            // Recherche du montant à changer dans la devise de départ
            $cv_reste_dev_achat = self::calculeCV($devise_vente, $devise_achat, $reste_dev_vente);
            
            switch ($destination_reste) {
                case 1: // Versement au guichet
                    global $global_id_guichet;
                    $id_gui = $global_id_guichet; // FIXME Je sais que je ne devrais pas ...
                    $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"]; // AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                    $type_oper_rest = 455;
                    break;
                
                case 2: // Versement sur compte de base
                    global $global_remote_id_client; // FIXME Je sais que je ne devrais pas ...
                    $id_cpt_base = $CompteObj->getBaseAccountID($global_remote_id_client);
                    $array_cptes["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($id_cpt_base);
                    
                    if ($array_cptes["cpta"]["credit"] == NULL) {
                        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                    }
                    
                    $array_cptes["int"]["credit"] = $id_cpt_base;
                    $type_oper_rest = 456;
                    break;
                
                case 3:
                    $type_oper_rest = 457;
                    break;
                
                default:
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "le paramètre 'destination_change' est incorrect : $destination_change"
            }
            
            $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $cv_reste_dev_achat, $type_oper_rest, $array_cptes, $comptable);
            
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
            $mnt_change -= $cv_reste_dev_achat;
        }
        // Fin : Gestion des arrondies
              
        // Change :
        $myErr = self::effectueChangePrivate($devise_achat, $devise_vente, $mnt_change, $type_oper, $subst, $comptable, true, $cv_montant, NULL, $infos_sup);
        
        unset($ComptaObj);
        unset($CompteObj);
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        return new ErrorObj(NO_ERR);
    }

    /**
     * Effectue une opération de change de haut niveau sur l'agence distante lors d'une echec d'un retrait en deplacé
     *
     * @author BD
     * @param char(3) $devise_achat
     *            Code ISO de la devise achetée
     * @param char(3) $devise2
     *            Code ISO de la devise de la devise vendue
     * @param double $montant
     *            Montant à changer exprimé dans la devise d'achat
     * @param double $cv_montant
     *            Montant que l'on est censé obtenir après l'opération de change. Ce paramètre est nécessaire pour vérifier que l'utilisateur a utilisé correctement le logiciel.
     * @param int $type_oper
     *            Le type d'opération ayant entrainé le change
     * @param array $subst
     *            Le tableau de substitution tel que passé à passageEcrituresComptablesAuto
     * @param &array $comptable
     *            Liste de mouvements comptables précédemment enregistrés
     * @param defined(1,2,3) $destination_reste
     *            Indique que faire du reste de l'opération de change (1 = versement au guichet, 2 = versement sur compte de base, 3 = intégration dans produits)
     * @param double $commission
     *            Montant prélevé au titre de commission (optionnel)
     * @param double $taxe
     *            Montant prélevé au titre de taxe (optionnel)
     * @param float $taux
     *            : Taux appliqué afind e réaliser un bénéfice supplémentaire sur le taux
     * @param boolean $is_guichet
     *            : true si le change se fait au guichet
     * @param
     *            int info_ecriture : information de l'ecriture comtable à passer : (dans le cas de l'epargne le id_cpte du compte source)
     *            
     * @return array Array comptable à passer à ajout_historique
     */
    public function changeRetraitRemoteRevert($devise_achat, $devise_vente, $montant, $cv_montant, $type_oper, $subst, &$comptable, $destination_reste, $commissionnette = NULL, $taux = NULL, $is_guichet = NULL, $infos_sup = NULL)
    {
        require_once 'lib/dbProcedures/agence.php';
        
        global $global_remote_monnaie, $global_id_agence;
        
        $dev_ref = $global_remote_monnaie;
        
        $DEV_A = self::getInfoDevise($devise_achat);
        $DEV_V = self::getInfoDevise($devise_vente);
        
        // Init class
        $CompteObj = new Compte($this->getDbConn(), $this->getIdAgence());
        $ComptaObj = new Compta($this->getDbConn(), $this->getIdAgence());
        
        // Init :
        $commission = 0;
        $taxe = 0;
        $benef_taux = 0;
        
        $toPreleveCommissionsDansAgenceRemote = false;
        
        // verification si les commissions doivent etre prelevés sur l'agence distante
        $toPreleveCommissionsDansAgenceLocal = getWherePerceptionCommissionsMultiAgence();
        
        if (! $toPreleveCommissionsDansAgenceLocal) {
            $toPreleveCommissionsDansAgenceRemote = true;
        }
        
        // Récupère la commission de change si non précisé
        if (! isset($commissionnette)) {
            $commission = $this->calculeCommissionChange($montant, $devise_achat, $devise_vente);
            $taxe = $this->calculeTaxeChange($commission, $devise_achat, $devise_vente);
        } else {
            $SPLIT = $this->splitCommissionNette($commissionnette, $devise_achat, $devise_vente);
            $commission = $SPLIT["commission"];
            $taxe = $SPLIT["taxe"];
        }
        
        // Récupère le bénéfice sur le taux si le taux n'est pas précisé
        if (! isset($taux)) {
            $taux = $this->getTauxChange($devise_achat, $devise_vente, true, 1);
        }
        $benef_taux = $this->calculeBeneficeTaux(($montant - $commission - $taxe), $devise_achat, $devise_vente, $taux);
        
        // Inverser les montants pour l'operation de renversement du retrait
        if($montant < 0) {
            $commission = 0 - $commission;
            $taxe = 0 - $taxe;
            $benef_taux = 0 - $benef_taux;
        }      
        
        // Traitement si les commissions doivent etre prelevés dans l'agence distante
        if ($toPreleveCommissionsDansAgenceRemote) {
            // Prélèvement de la commission
            if ($commission != 0) {
                // Construction de l'array de substitution :
                
                // Compte au D = compte source du change
                $array_cptes = array();
                $array_cptes["int"]["debit"] = $subst["int"]["debit"];
                $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
                
                // Compte au C = compte de produit de commission de la devise d'achat
                $array_cptes["cpta"]["credit"] = $DEV_A["cpte_produit_commission"];
                
                $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $commission, 450, $array_cptes, $comptable, TRUE, NULL, $info_ecriture);
                
                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            } // end :prelevement commissions         
        }         
        else {
            /*
             * Si les commissions ne sont pas prelevés dans l'agence distante, il faut prelever le montant de la commission depuis le compte du client pour passer au compte de liaison de l'agence locale dans la devise d'achat (KMF).
             */
            
            if ($commission != 0) {
                $array_cptes = array();
                $array_cptes["int"]["debit"] = $subst["int"]["debit"];
                $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
                $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"]; // AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                
                $result = $CompteObj->passageEcrituresComptablesAuto(450, $commission, $comptable, $array_cptes, $devise_achat, NULL, $info_ecriture, NULL);
                
                if ($result->errCode != NO_ERR) {
                    return $result;
                }
            }
            // @todo : taxes etc. ?
        }
        
        // We need to amend the effective amount to be changed by subtracting the commissions
        
        // Opération de change proprement dite
        // Le montant brut = le montant à changer uen fois les commissions et taxes retirées
        $mnt_brut = $montant - $commission - $taxe;
        // Le montant réel à changer = le montant réel qui est changé (par rapport au taux indicatif)
        $mnt_change = $mnt_brut - $benef_taux;
        // La C/V calculée = la C/V du montant réellement changée
        $cv_calculee = self::calculeCV($devise_achat, $devise_vente, $mnt_change);
        // On peut aussi calculer la C/V en diminuant le risque d'erreur sur base du montant brut
        $cv_calculee_rapport_mnt_brut = round(($mnt_brut * $taux), $DEV_V["precision"]);
        
        // Vérifie que ce montant est bien conforme aux attentes
        // Si il s'agit d'une opération au guichet, la C/V passée est une C/V arrondie inférieure au plus petit billet. Il faut donc y ajouter le reste en devise vente avant de faire le test d'équivalence
        $cpt_credit = $subst["cpta"]["credit"];
        $isCompteGuichet = $ComptaObj->isCompteGuichet($cpt_credit);
        
        if ($isCompteGuichet || $is_guichet) {
            $reste_dev_vente = ($cv_calculee_rapport_mnt_brut + $perte_change) - (Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $cv_calculee_rapport_mnt_brut + $perte_change, - 1, $devise_vente));
            
            if (round($reste_dev_vente, $DEV_V["precision"] > 0)) { // Il y a un reliquiat à traiter
                                                                    // On cherche la C/V en devise de référence de ce reste
                $reste_dev_ref = self::calculeCV($devise_vente, $dev_ref, $reste_dev_vente);
                // On ne fait le change que si le montant peut tre remis au client
                $reste_dev_ref_arrondi_billet = Divers::arrondiMonnaie($this->getDbConn(), $this->getIdAgence(), $reste_dev_ref, - 1, $dev_ref);
            } else {
                $reste_dev_ref_arrondi_billet = 0;
            }
        } else {
            $reste_dev_ref_arrondi_billet = 0;
        }
        
        $isEquivalent = self::estEquivalent($mnt_change, $devise_achat, $cv_montant + $reste_dev_vente, $devise_vente);
        
        if (! $isEquivalent) {
            // Quelque chose n'est pas clair ...
            return new ErrorObj(ERR_MNT_NON_EQUIV, _("pas d'équivalence'"));
        } else { // A partir de maintenant c'est $cv_montant qui fera foi
            $cv_mnt_change = $cv_montant;
        }
        
        // *********** Gestion des arrondis *********
        
        if ($reste_dev_ref_arrondi_billet > 0) {
            $array_cptes = array();
            $array_cptes["int"]["debit"] = $subst["int"]["debit"];
            $array_cptes["cpta"]["debit"] = $subst["cpta"]["debit"];
            
            // Recherche du montant à changer dans la devise de départ
            $cv_reste_dev_achat = self::calculeCV($devise_vente, $devise_achat, $reste_dev_vente);
            
            switch ($destination_reste) {
                case 1: // Versement au guichet
                    global $global_id_guichet;
                    $id_gui = $global_id_guichet; // FIXME Je sais que je ne devrais pas ...
                    $array_cptes["cpta"]["credit"] = $subst["cpta"]["credit"]; // AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                    $type_oper_rest = 455;
                    break;
                
                case 2: // Versement sur compte de base
                    global $global_remote_id_client; // FIXME Je sais que je ne devrais pas ...
                    $id_cpt_base = $CompteObj->getBaseAccountID($global_remote_id_client);
                    $array_cptes["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($id_cpt_base);
                    
                    if ($array_cptes["cpta"]["credit"] == NULL) {
                        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                    }
                    
                    $array_cptes["int"]["credit"] = $id_cpt_base;
                    $type_oper_rest = 456;
                    break;
                
                case 3:
                    $type_oper_rest = 457;
                    break;
                
                default:
                    $this->getDbConn()->rollBack(); // Roll back
                    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "le paramètre 'destination_change' est incorrect : $destination_change"
            }
            
            $myErr = self::effectueChangePrivate($devise_achat, $dev_ref, $cv_reste_dev_achat, $type_oper_rest, $array_cptes, $comptable);
            
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
            $mnt_change -= $cv_reste_dev_achat;
        }
        // Fin : Gestion des arrondies
        
        // Change :
        $myErr = self::effectueChangePrivate($devise_achat, $devise_vente, $mnt_change, $type_oper, $subst, $comptable, true, $cv_montant, NULL, $infos_sup);
        
        unset($ComptaObj);
        unset($CompteObj);
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        return new ErrorObj(NO_ERR);
    }
    
    
    /**
     * Renvoie true si le montant 1 exprimé en devise 1 est équivalent au montant 2 exprimé en devise 2
     * On considère que deux montants sont équivalents si la C/V de montant 1 dans la devise 2 est égal à montant 2 +/- le maximum entre la plus petite unité de devise 1 exprimée en devise 2 et la plus petite unité en devise2
     *
     * @param float $mnt1            
     * @param char(3) $devise1            
     * @param float $mnt2            
     * @param char(3) $devise2            
     * @return bool
     * @author BD
     */
    public function estEquivalent($mnt1, $devise1, $mnt2, $devise2)
    {
        $DEV1 = self::getInfoDevise($devise1);
        $DEV2 = self::getInfoDevise($devise2);
        
        $cv_mnt1 = self::calculeCV($devise1, $devise2, $mnt1);
        
        $unite_min_dev1 = pow(10, - $DEV1["precision"]);
        $cv_unite_min_dev1 = self::calculeCV($devise1, $devise2, $unite_min_dev1);
        $tolerance = max($cv_unite_min_dev1, pow(10, - $DEV2["precision"]));
        $borne_inf = round($mnt2 - $tolerance, $DEV2["precision"]);
        $borne_sup = round($mnt2 + $tolerance, $DEV2["precision"]);
        
        if ($borne_inf <= $cv_mnt1 && $cv_mnt1 <= $borne_sup) {
            return true;
        } else {
            return false;
        }
    }
    
}
