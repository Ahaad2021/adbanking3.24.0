<?php

/**
 * Description de la classe Retrait
 *
 * @author danilo
 */
class Retrait {

    /** Properties */
    private $_id_guichet;
    private $_id_cpte;
    private $_info_produit;
    private $_info_cpte;
    private $_montant;
    private $_type_retrait;
    private $_id_mandat;
    private $_data_cheque;
    private $_change;
    private $_data_bef;
    private $_nom_login;
    private $_id_agence;
    private $_id_client;
    private $_multidevise;
    private $_monnaie;

    public function __construct($nom_login, $id_agence, $monnaie, $id_guichet, $id_cpte, $info_produit_arr, $info_cpte_arr, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $change = NULL, $data_bef = NULL) {

        /*
          $this->setNomLogin($nom_login);
          $this->setIdAgence($id_agence);
          $this->setMonnaie($monnaie);
          $this->setIdGuichet($id_guichet);
          $this->setIdCpte($id_cpte);
          $this->setInfoProduit($info_produit_arr);
          $this->setInfoCpte($info_cpte_arr);
          $this->setMontant($montant);
          $this->setTypeRetrait($type_retrait);
          $this->setIdMandat($id_mandat);
          $this->setDataCheque($data_cheque);
          $this->setChange($change);
          $this->setDataBef($data_bef);
         */
    }

    /**
     * Enregistre une opération de <B>retrait</B> sur un compte d'épargne (retrait cash ou suite à réception chèque / OP / achat traveler's cheques ou autre)
     * 
     * @param int $id_guichet L'ID du guichet d'où sera retiré le retrait
     * @param int $id_cpte L'ID du compte client qui sera débité
     * @param array $InfoProduit : les données sur le produit d'épargne (notamment les frais de retrait).
     * @param array $InfoCpte : les données sur le compte sélectionné.
     * @param float $montant Montant du retrait
     * @param int $type_retrait : Type de retrait (1 espèce, 15 chèque guichet, 3 ordre de paiement, 4 Autorisation de retrait sans livret/chèque, 5 travelers, 6 : Recharge Carte Ferlo)
     * @param array $data_cheque : les données figurant sur le chèque + la remarque
     * @param array $CHANGE : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfice fait sur le taux)
     * 
     * @return ErrorObj erreur
     */
    public static function retraitCpteLocal($id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $CHANGE = NULL, $dataBef = NULL) {
        global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
        global $dbHandler, $global_multidevise;
        global $global_monnaie, $global_remote_monnaie;

        $comptable = array();

        $db = $dbHandler->openConnection();
        //vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { //1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
            case 15:
                if ($data_cheque['id_correspondant'] == 0)
                    $retrait_transfert = 0; //il s'agit d'un chèque-guichet
                else
                    $retrait_transfert = 1; //il s'agit d'un chèque transmis par une banque
                break;
            default:
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        //débit du compte client
        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison locale
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        } else {
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        }
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte liaison comptable de l'agence en déplacé"));
        }

        /* Arrondi du montant si opération au guichet */
        if (( $type_retrait == 1 ) || ( $type_retrait == 4 )) {
            $critere = array();
            $critere['num_cpte_comptable'] = getCompteCptaGui($id_guichet);
            $cpte_gui = getComptesComptables($critere);
            //$montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']);
            $montant = arrondiMonnaiePrecision($montant, $cpte_gui['devise']);
        }

        //crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
        switch ($type_retrait) {
            //retrait par le client en espèce
            case 1:
                $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
                $operation = 140;
                $fonction = 92;
                break;
            //retrait par chèque
            case 15:
            case 4://il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if ($data_cheque['id_correspondant'] == 0 || !isset($data_cheque['id_correspondant'])) {
                    $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
                    $operation = 512;
                    $fonction = 92;
                }
                break;
        }
        
        $infos_sup = array();

        $text_opt_id = Divers::getLocalTextId("Retrait en déplacé");

        if($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }          

        $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, NULL, $infos_sup);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
        
        /*
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
        */

        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
        /*
        $frais_retrait = 0;
        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = $InfoProduit['frais_retrait_cpt'];
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = $InfoProduit['frais_transfert'];
            $operation = 152;
        }
        if ($frais_retrait > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé à la caisse"));
            }

            $myErr = effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }
        */

        //
        if ($data_cheque != NULL) {
            $data_his_ext = creationHistoriqueExterieur($data_cheque);
        } else {
            $data_his_ext = NULL;
        }

        $myErr = ajout_historique($fonction, NULL, 'agc='.$id_agence . ' - client=' . $global_id_client, $global_nom_login, date("r"), $comptable, $data_his_ext, NULL);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        $id_his = $myErr->param;
        $id_ecriture = Divers::getIDEcritureByIDHis($id_his);

        // $dbHandler->closeConnection(true); // commit transaction moved outside function

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }
    
    
    /**
     * Enregistre une opération de <B>retrait en deplacé</B> sur un compte d'épargne (retrait cash ou suite à réception chèque / OP / achat traveler's cheques ou autre)
     *
     * @param int $id_guichet L'ID du guichet d'où sera retiré le retrait
     * @param int $id_cpte L'ID du compte client qui sera débité
     * @param array $InfoProduit : les données sur le produit d'épargne (notamment les frais de retrait).
     * @param array $InfoCpte : les données sur le compte sélectionné.
     * @param float $montant Montant du retrait
     * @param int $type_retrait : Type de retrait (1 espèce, 15 chèque guichet, 3 ordre de paiement, 4 Autorisation de retrait sans livret/chèque, 5 travelers, 6 : Recharge Carte Ferlo)
     * @param array $data_cheque : les données figurant sur le chèque + la remarque
     * @param array $CHANGE : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfice fait sur le taux)
     *
     * @return ErrorObj erreur
     */
    public static function retraitCpteLocalMultidevises($id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $CHANGE = NULL, $dataBef = NULL) {
        global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
        global $dbHandler, $global_multidevise;
        global $global_monnaie, $global_remote_monnaie;

        $comptable = array();
        
        // Open connection here but closed in caller function        
        $db = $dbHandler->openConnection();
        
        //vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { //1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
            case 15:
                if ($data_cheque['id_correspondant'] == 0)
                    $retrait_transfert = 0; //il s'agit d'un chèque-guichet
                else
                    $retrait_transfert = 1; //il s'agit d'un chèque transmis par une banque
                break;
            default:
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        //débit du compte client
        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison locale
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        } else {
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        }
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte liaison comptable de l'agence en déplacé"));
        }

        /* Arrondi du montant si opération au guichet */
        if (( $type_retrait == 1 ) || ( $type_retrait == 4 )) {
            $critere = array();
            $critere['num_cpte_comptable'] = getCompteCptaGui($id_guichet);
            $cpte_gui = getComptesComptables($critere);
            //$montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']);
            $montant = arrondiMonnaiePrecision($montant, $cpte_gui['devise']);
        }

        //crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
        switch ($type_retrait) {
            //retrait par le client en espèce
            case 1:
                $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
                $operation = 140;
                $fonction = 92;
                break;
            //retrait par chèque
            case 15:
            case 4://il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if ($data_cheque['id_correspondant'] == 0 || !isset($data_cheque['id_correspondant'])) {
                    $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
                    $operation = 512;
                    $fonction = 92;
                }
                break;
        }
        
        $infos_sup = array();

        $text_opt_id = Divers::getLocalTextId("Retrait en déplacé");

        if($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }  
        
        if(($type_retrait == 1) && is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"])) 
        {
            //verification si les commissions doivent etre prelevés sur l'agence locale
            $toPreleveCommissionsDansAgenceLocal = getWherePerceptionCommissionsMultiAgence();
                                               
            // Traitement si les commissions doivent etre prelevés dans l'agence locale
            if($toPreleveCommissionsDansAgenceLocal) {  
                $myErr = changeRetraitLocalAvecCommissions($InfoCpte['devise'], $CHANGE['devise'], $montant, $CHANGE['cv'], $operation, $cptes_substitue, $comptable, 1, $CHANGE['comm_nette'], $CHANGE['taux']);
                              
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
            // Traitement si les commissions ne seront pas prelevés dans l'agence locale
            else
           {
                // On recupere les infos pour passer une ecriture comptable en devise entre le compte de caisse en devise et le compte liaison dans la meme devise                          
                $montant = $CHANGE['cv'];
                $devise = $CHANGE['devise'];
                $cptes_substitue["cpta"]["debit"] = checkCptDeviseOK($cptes_substitue["cpta"]["debit"], $devise);
                $cptes_substitue["cpta"]["credit"] = checkCptDeviseOK($cptes_substitue["cpta"]["credit"], $devise);
            
                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $devise, NULL, $id_cpte, $infos_sup);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
        }
        else {
            $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, NULL, $infos_sup);
        }

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
        /*
        $frais_retrait = 0;
        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = $InfoProduit['frais_retrait_cpt'];
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = $InfoProduit['frais_transfert'];
            $operation = 152;
        }
        if ($frais_retrait > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé à la caisse"));
            }

            $myErr = effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }
        */

        //
        if ($data_cheque != NULL) {
            $data_his_ext = creationHistoriqueExterieur($data_cheque);
        } else {
            $data_his_ext = NULL;
        }
        
        $myErr = ajout_historique($fonction, NULL, 'agc='.$id_agence . ' - client=' . $global_id_client, $global_nom_login, date("r"), $comptable, $data_his_ext, NULL); // $InfoCpte["id_titulaire"]
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        $id_his = $myErr->param;
        $id_ecriture = Divers::getIDEcritureByIDHis($id_his);

        // commit transaction moved outside function        
        //$dbHandler->closeConnection(true);
        
        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }

    /**
     * Enregistre une opération de <B>retrait</B> sur un compte d'épargne (retrait cash ou suite à réception chèque / OP / achat traveler's cheques ou autre)
     * 
     * @param int $id_guichet L'ID du guichet d'où sera retiré le retrait
     * @param int $id_cpte L'ID du compte client qui sera débité
     * @param array $InfoProduit : les données sur le produit d'épargne (notamment les frais de retrait).
     * @param array $InfoCpte : les données sur le compte sélectionné.
     * @param float $montant Montant du retrait
     * @param int $type_retrait : Type de retrait (1 espèce, 15 chèque guichet, 3 ordre de paiement, 4 Autorisation de retrait sans livret/chèque, 5 travelers, 6 : Recharge Carte Ferlo)
     * @param array $data_cheque : les données figurant sur le chèque + la remarque
     * @param array $CHANGE : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfice fait sur le taux)
     * 
     * @return ErrorObj erreur
     */
    public static function retraitCpteRemote(&$dbc, $id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $CHANGE = NULL, $dataBef = NULL) {

        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $dbHandler, $global_multidevise;
        global $global_remote_monnaie;
        global $global_remote_id_client;

        $comptable = array();
        $is_insert_chq = FALSE;

        //vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { //1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
                $retrait_transfert = 0; //il s'agit d'un retrait (il faut prélever des frais de retrait)
                break;
            case 15:
                if ($data_cheque['id_correspondant'] == 0)
                    $retrait_transfert = 0; //il s'agit d'un chèque-guichet
                else
                    $retrait_transfert = 1; //il s'agit d'un chèque transmis par une banque
                break;
            default:
                $dbc->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Init class
        $EpargneObj = new Epargne($dbc, $id_agence);

        $mntDispo = $EpargneObj->getSoldeDisponible($id_cpte);
        $mntRetirer = $montant + recupMontant($InfoProduit['frais_retrait_cpt']) + recupMontant($data_cheque["commission_od_retrait"]);
        if (recupMontant($mntRetirer) > recupMontant($mntDispo)){
            $html_err = new HTML_erreur("Echec du retrait en deplacé.");

            $html_err->setMessage("Solde du compte est insuffisant pour continuer l'operation");

            $html_err->addButton("BUTTON_OK", 'Ope-13');

            $html_err->buildHTML();
            echo $html_err->HTML_code;
            die();
        }

        $erreur = $EpargneObj->CheckRetrait($InfoCpte, $InfoProduit, $montant, $retrait_transfert, $id_mandat,$data_cheque["commission_op_deplace"]);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }

        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        // Init class
        $CompteObj = new Compte($dbc, $id_agence);

        //débit du compte client
        $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        $cptes_substitue["int"]["debit"] = $id_cpte;

        /* Arrondi du montant si opération au guichet */
        if (( $type_retrait == 1 ) || ( $type_retrait == 4 )) {

           // $montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);
             $montant = arrondiMonnaiePrecision($montant, $InfoCpte['devise']);
        }

        //crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
        switch ($type_retrait) {
            //retrait par le client en espèce
            case 1:
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 140;
                $fonction = 92;
                $operation_comptable = 140;
                break;
            //retrait par chèque
            case 15:
            //case 3:
            case 4://il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 512;
                $fonction = 92;
                $operation_comptable = 512;
                if ($type_retrait == 15) {
                    $is_insert_chq = TRUE;
                }
                break;
        }

        // Init class
        $DeviseObj = new Devise($dbc, $id_agence);

        /*
        if (is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"])) {

            $myErr = $DeviseObj->change($InfoCpte['devise'], $CHANGE["devise"], $montant, $CHANGE["cv"], $operation, $cptes_substitue, $comptable, 1, $CHANGE["comm_nette"], $CHANGE["taux"]);
        }
        else
        */
        {
            $infos_sup = array();

            $text_opt_id = Divers::getRemoteTextId($dbc, "Retrait en déplacé");

            if($text_opt_id > 0) {
                $infos_sup["autre_libel_ope"] = $text_opt_id;
            }
            
            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, NULL, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        /*
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        */

        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
        $frais_retrait = 0;

        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = $InfoProduit['frais_retrait_cpt'];
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = $InfoProduit['frais_transfert'];
            $operation = 152;
        }
        if ($frais_retrait > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }


        // en cas de frais SMS transactionnel
        $ClientObj = new Client($dbc, $id_agence);

        $listeTypeOpt = $EpargneObj->getListeTypeOptDepPourPreleveFraisSMS();

        if (in_array($operation_comptable, $listeTypeOpt)) {
            $SMSTransactionnel = $EpargneObj->getTarificationDatas();
            $clientSMS = $ClientObj->checkIfClientAbonnerSMS($global_id_client);

            if (!empty($SMSTransactionnel) && $clientSMS == true) {
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();

                $operation = 188;

                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }

                $cptes_substitue["int"]["debit"] = $id_cpte;

                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $SMSTransactionnel["valeur"], $operation, $cptes_substitue, $comptable);

                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            }
        }

        //  prelevement des commission sur operation en deplace
        if ($data_cheque["commission_op_deplace"] > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $operation = 157;

            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $data_cheque["commission_op_deplace"], $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }

        // Destroy object
        unset($DeviseObj);

        // Si le compte est passé en découvert, prélever les frais de dossier découvert
        /*
        $myErr2 = $EpargneObj->preleveFraisDecouvert($id_cpte, $comptable);
        if ($myErr2->errCode != NO_ERR) {
            return $myErr2;
        }
        */

        if ($id_mandat != NULL) {
            $MANDAT = $EpargneObj->getInfosMandat($id_mandat);
            $data_cheque['id_pers_ext'] = $MANDAT['id_pers_ext'];
        }

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);

        if ($data_cheque != NULL) {
            $data_his_ext = $HistoriqueObj->creationHistoriqueExterieur($data_cheque);
        } else {
            $data_his_ext = NULL;
        }
        if ($is_insert_chq) {
            if (is_array($dataBef) && count($dataBef)>0) {
                // Init class
                $TireurBenefObj = new TireurBenef($dbc, $id_agence);

                $id = $TireurBenefObj->insereTireurBenef($dataBef);

                // Destroy object
                unset($TireurBenefObj);

                $data_his_ext['id_tireur_benef'] = $id;
            }
            $data_ch['id_cheque'] = $data_cheque['num_piece'];
            $data_ch['date_paiement'] = $data_cheque['date_piece'];
            $data_ch['etat_cheque'] = 1;
            $data_ch['id_benef'] = $data_his_ext['id_tireur_benef'];

            $rep = $CompteObj->insertCheque($data_ch, $id_cpte);

            if ($rep->errCode != NO_ERR) {
                return $rep;
            }
        }

        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], 'agc='.$global_id_agence . ' - login=' . $global_nom_login, 'distant', date("r"), $comptable, $data_his_ext);

        // Destroy object
        unset($CompteObj);
        unset($EpargneObj);
        unset($HistoriqueObj);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }
        
    
    public static function retraitCpteRemoteRevert(&$dbc, $id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $data_cheque, $errMsg="") {
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $dbHandler, $global_multidevise;
        global $global_remote_monnaie;
        global $global_remote_id_client;

        $comptable = array();

        //vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { //1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
            case 15:
                $retrait_transfert = 0; //il s'agit d'un retrait (il faut prélever des frais de retrait) ou il s'agit d'un chèque-guichet
                break;
            default:
                $dbc->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Init class
        $EpargneObj = new Epargne($dbc, $id_agence);

        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        // Init class
        $CompteObj = new Compte($dbc, $id_agence);

        //débit du compte client
        $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        $cptes_substitue["int"]["debit"] = $id_cpte;

        /* Arrondi du montant si opération au guichet */
        if (( $type_retrait == 1 ) || ( $type_retrait == 4 )) {
            //$montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);
            $montant = arrondiMonnaiePrecision($montant, $InfoCpte['devise']);
        }

        // Crédit, selon les cas : du guichet / du compte correspondant
        switch ($type_retrait) {
            //retrait par le client en espèce
            case 1:
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 140;
                $fonction = 92;
                break;
            //retrait par chèque
            case 15:
            case 4://il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 512;
                $fonction = 92;
                break;
        }

        // Init class
        $DeviseObj = new Devise($dbc, $id_agence);

        /*
        if (is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"])) {

            $myErr = $DeviseObj->change($InfoCpte['devise'], $CHANGE["devise"], $montant, $CHANGE["cv"], $operation, $cptes_substitue, $comptable, 1, $CHANGE["comm_nette"], $CHANGE["taux"]);
        }
        else
        */
        {
            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise']);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        /*
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        */

        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
        $frais_retrait = 0;

        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = (0 - $InfoProduit['frais_retrait_cpt']);
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = (0 - $InfoProduit['frais_transfert']);
            $operation = 152;
        }
        if ($frais_retrait < 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }

        //-----------------Annulation des commissions sur operation en deplacer-------------------------------------
        if ($data_cheque['commission_op_deplace'] < 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $operation = 157;
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $data_cheque['commission_op_deplace'], $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }

        // Destroy object
        unset($DeviseObj);

        // Si le compte est passé en découvert, prélever les frais de dossier découvert
        /*
        $myErr2 = $EpargneObj->preleveFraisDecouvert($id_cpte, $comptable);
        if ($myErr2->errCode != NO_ERR) {
            return $myErr2;
        }
        */

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);

        $data_his_ext = NULL;

        if($type_retrait==15 && is_array($data_cheque) && count($data_cheque)>0 && isset($data_cheque["num_piece"]))
        {
            $id_cheque = $data_cheque['num_piece'];

            $rep = $CompteObj->deleteCheque($id_cheque);

            if ($rep->errCode != NO_ERR) {
                return $rep;
            }
        }

        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], 'agc='.$global_id_agence . ' - login=' . $global_nom_login.$errMsg, 'distant', date("r"), $comptable, $data_his_ext);

        // Destroy object
        unset($CompteObj);
        unset($EpargneObj);
        unset($HistoriqueObj);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }   
    
    /**
     * Enregistre une opération de <B>retrait en deplacé multidevises</B> sur un compte d'épargne
     *
     * @param int $id_guichet
     *            L'ID du guichet d'où sera retiré le retrait
     * @param int $id_cpte
     *            L'ID du compte client qui sera débité
     * @param array $InfoProduit
     *            : les données sur le produit d'épargne (notamment les frais de retrait).
     * @param array $InfoCpte
     *            : les données sur le compte sélectionné.
     * @param float $montant
     *            Montant du retrait
     * @param int $type_retrait
     *            : Type de retrait (1 espèce, 15 chèque guichet, 3 ordre de paiement, 4 Autorisation de retrait sans livret/chèque, 5 travelers, 6 : Recharge Carte Ferlo)
     * @param array $data_cheque
     *            : les données figurant sur le chèque + la remarque
     * @param array $CHANGE
     *            : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfice fait sur le taux)
     *            
     * @return ErrorObj erreur
     */
    public static function retraitCpteRemoteMultidevises(&$dbc, $id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $CHANGE = NULL, $dataBef = NULL)
    {
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $dbHandler, $global_multidevise;
        global $global_remote_monnaie;
        global $global_remote_id_client;
        
        $comptable = array();
        $is_insert_chq = FALSE;
        
        // vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { // 1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
                $retrait_transfert = 0; // il s'agit d'un retrait (il faut prélever des frais de retrait)
                break;
            case 15:
                if ($data_cheque['id_correspondant'] == 0)
                    $retrait_transfert = 0; // il s'agit d'un chèque-guichet
                else
                    $retrait_transfert = 1; // il s'agit d'un chèque transmis par une banque
                break;
            default:
                $dbc->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        // Init class
        $EpargneObj = new Epargne($dbc, $id_agence);

        $mntDispo = $EpargneObj->getSoldeDisponible($id_cpte);
        $mntRetirer = $montant + recupMontant($InfoProduit['frais_retrait_cpt']) + recupMontant($data_cheque["commission_od_retrait"]);
        if (recupMontant($mntRetirer) > recupMontant($mntDispo)){
                $html_err = new HTML_erreur("Echec du retrait en deplacé.");

                $html_err->setMessage("Solde du compte est insuffisant pour continuer l'operation");

                $html_err->addButton("BUTTON_OK", 'Ope-13');

                $html_err->buildHTML();
                echo $html_err->HTML_code;
            die();
             }

            
        // Verification des taux d'échange dans les 2 agences:
        if (isset($CHANGE)) {
            $erreur = Divers::checkTauxDeviseForOperationDeplacer($InfoCpte['devise'], $CHANGE["devise"], $dbc);
            
            if ($erreur->errCode != NO_ERR) {
                return $erreur;
            }
        }

        $erreur = $EpargneObj->CheckRetrait($InfoCpte, $InfoProduit, $montant, $retrait_transfert, $id_mandat);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }

        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
        
        // Init class
        $CompteObj = new Compte($dbc, $id_agence);
        
        // débit du compte client
        $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        
        $cptes_substitue["int"]["debit"] = $id_cpte;
        
        /* Arrondi du montant si opération au guichet */
        if (($type_retrait == 1) || ($type_retrait == 4)) {
            //$montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);
            $montant = arrondiMonnaiePrecision($montant, $InfoCpte['devise']);
            
        }
        
        // crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
        switch ($type_retrait) {
            // retrait par le client en espèce
            case 1:
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 140;
                $fonction = 92;
                break;
            // retrait par chèque
            case 15:
            // case 3:
            case 4: // il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 512;
                $fonction = 92;
                if ($type_retrait == 15) {
                    $is_insert_chq = TRUE;
                }
                break;
        }
        
        // Init class
        $DeviseObj = new Devise($dbc, $id_agence);
        
        $infos_sup = array();
        
        $text_opt_id = Divers::getRemoteTextId($dbc, "Retrait en déplacé");
        
        if ($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }

        // Mode multidevise
        if (($type_retrait == 1) && is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"])) {                    
            $myErr = $DeviseObj->changeRetraitRemote($InfoCpte['devise'], $CHANGE["devise"], $montant, $CHANGE["cv"], $operation, $cptes_substitue, $comptable, 1, $CHANGE["comm_nette"], $CHANGE["taux"], false, $infos_sup);
        } else { // Mode mono devise            
            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, NULL, $infos_sup);
        }
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client                
        $frais_retrait = 0;
        
        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = $InfoProduit['frais_retrait_cpt'];
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = $InfoProduit['frais_transfert'];
            $operation = 152;
        }
        if ($frais_retrait > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }
            $cptes_substitue["int"]["debit"] = $id_cpte;
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        



        // Frais de commission sur od retrait
        if ($data_cheque["commission_od_retrait_cv"] > 0 && (isset($CHANGE))) {

            $operation = 157;
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }
            $cptes_substitue["int"]["debit"] = $id_cpte;
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $data_cheque["commission_od_retrait_cv"], $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        else {
            if ($data_cheque["commission_od_retrait"] > 0) {
                $operation = 157;
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();
                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }
                $cptes_substitue["int"]["debit"] = $id_cpte;
                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $data_cheque["commission_od_retrait"], $operation, $cptes_substitue, $comptable);

                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            }

        }
        // Fin operation de commission sur od retrait
        
        // Si le compte est passé en découvert, prélever les frais de dossier découvert
        /*
         * $myErr2 = $EpargneObj->preleveFraisDecouvert($id_cpte, $comptable); if ($myErr2->errCode != NO_ERR) { return $myErr2; }
         */
        // Destroy object
        unset($DeviseObj);
        if ($id_mandat != NULL) {
            $MANDAT = $EpargneObj->getInfosMandat($id_mandat);
            $data_cheque['id_pers_ext'] = $MANDAT['id_pers_ext'];
        }
        
        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);
        
        if ($data_cheque != NULL) {
            $data_his_ext = $HistoriqueObj->creationHistoriqueExterieur($data_cheque);
        } else {
            $data_his_ext = NULL;
        }
        if ($is_insert_chq) {
            if (is_array($dataBef) && count($dataBef) > 0) {
                // Init class
                $TireurBenefObj = new TireurBenef($dbc, $id_agence);
                
                $id = $TireurBenefObj->insereTireurBenef($dataBef);
                
                // Destroy object
                unset($TireurBenefObj);
                
                $data_his_ext['id_tireur_benef'] = $id;
            }
            $data_ch['id_cheque'] = $data_cheque['num_piece'];
            $data_ch['date_paiement'] = $data_cheque['date_piece'];
            $data_ch['etat_cheque'] = 1;
            $data_ch['id_benef'] = $data_his_ext['id_tireur_benef'];
            
            $rep = $CompteObj->insertCheque($data_ch, $id_cpte);
            
            if ($rep->errCode != NO_ERR) {
                return $rep;
            }
        }        
               
        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], 'agc=' . $global_id_agence . ' - login=' . $global_nom_login, 'distant', date("r"), $comptable, $data_his_ext);
        
        // Destroy object
        unset($CompteObj);
        unset($EpargneObj);
        unset($HistoriqueObj);
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];
        
        return new ErrorObj(NO_ERR, array(
            'id_his' => $id_his,
            'id_ecriture' => $id_ecriture
        ));
    }

    /**
     * Renverse une opération de <B>retrait en deplacé multidevises</B> sur un compte d'épargne en passant un montant inverse de
     * celui passé dans l'operation de retrait initial
     *
     * @param int $id_guichet
     *            L'ID du guichet d'où sera retiré le retrait
     * @param int $id_cpte
     *            L'ID du compte client qui sera débité
     * @param array $InfoProduit
     *            : les données sur le produit d'épargne (notamment les frais de retrait).
     * @param array $InfoCpte
     *            : les données sur le compte sélectionné.
     * @param float $montant
     *            Montant du retrait
     * @param int $type_retrait
     *            : Type de retrait (1 espèce, 15 chèque guichet, 3 ordre de paiement, 4 Autorisation de retrait sans livret/chèque, 5 travelers, 6 : Recharge Carte Ferlo)
     * @param array $data_cheque
     *            : les données figurant sur le chèque + la remarque
     * @param array $CHANGE
     *            : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfice fait sur le taux)
     *            
     * @return ErrorObj erreur
     */
    public static function retraitCpteRemoteMultidevisesRevert(&$dbc, $id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $CHANGE = NULL, $dataBef = NULL, $errMsg = NULL)
    {  
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $dbHandler, $global_multidevise;
        global $global_remote_monnaie;
        global $global_remote_id_client;
        
        $comptable = array();
        
        // Inverser le montant pour l'operation REVERT :
        $montant = 0 - $montant;
        
        // vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { // 1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
                $retrait_transfert = 0; // il s'agit d'un retrait (il faut prélever des frais de retrait)
                break;
            case 15:
                if ($data_cheque['id_correspondant'] == 0)
                    $retrait_transfert = 0; // il s'agit d'un chèque-guichet
                else
                    $retrait_transfert = 1; // il s'agit d'un chèque transmis par une banque
                break;
            default:
                $dbc->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        
        // Init class
        $EpargneObj = new Epargne($dbc, $id_agence);
               
        // Verification des taux d'échange dans les 2 agences:
        if (isset($CHANGE)) {
            $erreur = Divers::checkTauxDeviseForOperationDeplacer($InfoCpte['devise'], $CHANGE["devise"], $dbc);
        
            if ($erreur->errCode != NO_ERR) {
                return $erreur;
            }
        }
        
        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
        
        // Init class
        $CompteObj = new Compte($dbc, $id_agence);
        
        // débit du compte client
        $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        
        $cptes_substitue["int"]["debit"] = $id_cpte;
        
        /* Arrondi du montant si opération au guichet */
        if (($type_retrait == 1) || ($type_retrait == 4)) {
            //$montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);
            $montant = arrondiMonnaiePrecision($montant, $InfoCpte['devise']);
        }
        
        // crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
        switch ($type_retrait) {
            // retrait par le client en espèce
            case 1:
                 if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                 } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                 }
                $operation = 140;
                $fonction = 92;
                break;
            // retrait par chèque
            case 15:
            // case 3:
            case 4: // il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 512;
                $fonction = 92;
                break;
        }
        
        // Init class
        $DeviseObj = new Devise($dbc, $id_agence);
        
        $infos_sup = array();
        
        $text_opt_id = Divers::getRemoteTextId($dbc, "Retrait en déplacé");
        
        if ($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }
        
        // Mode multidevise
        if (($type_retrait == 1) && is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"])) {
            // Inverser le montant c/v
            $montant_cv = 0 - $CHANGE["cv"];
            $myErr = $DeviseObj->changeRetraitRemoteRevert($InfoCpte['devise'], $CHANGE["devise"], $montant, $montant_cv, $operation, $cptes_substitue, $comptable, 1, $CHANGE["comm_nette"], $CHANGE["taux"], false, $infos_sup);
        } else { // Mode mono devise
            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, NULL, $infos_sup);
        }
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
        $frais_retrait = 0;
        
        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = $InfoProduit['frais_retrait_cpt'];
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = $InfoProduit['frais_transfert'];
            $operation = 152;
        }
        if ($frais_retrait > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }
            $cptes_substitue["int"]["debit"] = $id_cpte;
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        
        // Destroy object
        unset($DeviseObj);
        
        // Si le compte est passé en découvert, prélever les frais de dossier découvert
        /*
         * $myErr2 = $EpargneObj->preleveFraisDecouvert($id_cpte, $comptable); if ($myErr2->errCode != NO_ERR) { return $myErr2; }
         */
        
        if ($id_mandat != NULL) {
            $MANDAT = $EpargneObj->getInfosMandat($id_mandat);
            $data_cheque['id_pers_ext'] = $MANDAT['id_pers_ext'];
        }
        
        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);
        
        $data_his_ext = NULL;
        
        if ($type_retrait == 15 && is_array($data_cheque) && count($data_cheque) > 0 && isset($data_cheque["num_piece"])) {
            $id_cheque = $data_cheque['num_piece'];
            
            $rep = $CompteObj->deleteCheque($id_cheque);
            
            if ($rep->errCode != NO_ERR) {
                return $rep;
            }
        }
        
        $infos = 'agc=' . $global_id_agence . ' - login=' . $global_nom_login . ' - error : ' . $errMsg;
        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], $infos, 'distant', date("r"), $comptable, $data_his_ext);
             
        // Destroy object
        unset($CompteObj);
        unset($EpargneObj);
        unset($HistoriqueObj);
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];
        
        return new ErrorObj(NO_ERR, array(
            'id_his' => $id_his,
            'id_ecriture' => $id_ecriture
        ));
    }


    /** AGB : Retrait compte remote via agent */
    /**
     * Enregistre une opération de <B>retrait</B> sur un compte d'épargne (retrait cash ou suite à réception chèque / OP / achat traveler's cheques ou autre)
     *
     * @param int $id_guichet L'ID du guichet d'où sera retiré le retrait
     * @param int $id_cpte L'ID du compte client qui sera débité
     * @param array $InfoProduit : les données sur le produit d'épargne (notamment les frais de retrait).
     * @param array $InfoCpte : les données sur le compte sélectionné.
     * @param float $montant Montant du retrait
     * @param int $type_retrait : Type de retrait (1 espèce, 15 chèque guichet, 3 ordre de paiement, 4 Autorisation de retrait sans livret/chèque, 5 travelers, 6 : Recharge Carte Ferlo)
     * @param array $data_cheque : les données figurant sur le chèque + la remarque
     * @param array $CHANGE : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfice fait sur le taux)
     *
     * @return ErrorObj erreur
     */
    public static function retraitCpteRemoteAgent(&$dbc, $id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $CHANGE = NULL, $dataBef = NULL, $comm_agent=0, $comm_inst = 0) {

        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $dbHandler, $global_multidevise;
        global $global_remote_monnaie;
        global $global_remote_id_client;

        $comptable = array();
        $is_insert_chq = FALSE;

        //vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { //1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
                $retrait_transfert = 0; //il s'agit d'un retrait (il faut prélever des frais de retrait)
                break;
            case 15:
                if ($data_cheque['id_correspondant'] == 0)
                    $retrait_transfert = 0; //il s'agit d'un chèque-guichet
                else
                    $retrait_transfert = 1; //il s'agit d'un chèque transmis par une banque
                break;
            default:
                $dbc->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Init class
        $EpargneObj = new Epargne($dbc, $id_agence);

        $mntDispo = $EpargneObj->getSoldeDisponible($id_cpte);
        $mntRetirer = $montant + recupMontant($InfoProduit['frais_retrait_cpt']) + recupMontant($data_cheque["commission_od_retrait"]);
        if (recupMontant($mntRetirer) > recupMontant($mntDispo)){
            $html_err = new HTML_erreur("Echec du retrait en deplacé.");

            $html_err->setMessage("Solde du compte est insuffisant pour continuer l'operation");

            $html_err->addButton("BUTTON_OK", 'Ope-13');

            $html_err->buildHTML();
            echo $html_err->HTML_code;
            die();
        }

        $erreur = $EpargneObj->CheckRetrait($InfoCpte, $InfoProduit, $montant, $retrait_transfert, $id_mandat,$data_cheque["commission_op_deplace"]);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }

        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
        $choix_retrait_comm = getParamCommissionInsti();
        $agent_data = getDatasLogin($global_nom_login);
        $cpte_ins = $choix_retrait_comm['cpte_compta_comm_retrait'];
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2')?$agent_data['cpte_base_agent']:$agent_data['cpte_flotte_agent'];


        // Init class
        $CompteObj = new Compte($dbc, $id_agence);

        //débit du compte client
        $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        $cptes_substitue["int"]["debit"] = $id_cpte;

        /* Arrondi du montant si opération au guichet */
        if (( $type_retrait == 1 ) || ( $type_retrait == 4 )) {

            // $montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);
            $montant = arrondiMonnaiePrecision($montant, $InfoCpte['devise']);
        }

        //crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
        switch ($type_retrait) {
            //retrait par le client en espèce
            case 1:
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $cpte_liaison = $cptes_substitue["cpta"]["credit"];
                $operation = 624;
                $fonction = 92;
                $operation_comptable = 140;
                break;
            //retrait par chèque
            case 15:
                //case 3:
            case 4://il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 512;
                $fonction = 92;
                $operation_comptable = 512;
                if ($type_retrait == 15) {
                    $is_insert_chq = TRUE;
                }
                break;
        }

        // Init class
        $DeviseObj = new Devise($dbc, $id_agence);

        /*
        if (is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"])) {

            $myErr = $DeviseObj->change($InfoCpte['devise'], $CHANGE["devise"], $montant, $CHANGE["cv"], $operation, $cptes_substitue, $comptable, 1, $CHANGE["comm_nette"], $CHANGE["taux"]);
        }
        else
        */
        {
            $infos_sup = array();

            $text_opt_id = Divers::getRemoteTextId($dbc, "Retrait en déplacé");

            if($text_opt_id > 0) {
                $infos_sup["autre_libel_ope"] = $text_opt_id;
            }

            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, NULL, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        /*
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        */

        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
        $frais_retrait = 0;

        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = $InfoProduit['frais_retrait_cpt'];
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = $InfoProduit['frais_transfert'];
            $operation = 152;
        }
        if ($frais_retrait > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }


        // en cas de frais SMS transactionnel
        $ClientObj = new Client($dbc, $id_agence);

        $listeTypeOpt = $EpargneObj->getListeTypeOptDepPourPreleveFraisSMS();

        if (in_array($operation_comptable, $listeTypeOpt)) {
            $SMSTransactionnel = $EpargneObj->getTarificationDatas();
            $clientSMS = $ClientObj->checkIfClientAbonnerSMS($global_id_client);

            if (!empty($SMSTransactionnel) && $clientSMS == true) {
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();

                $operation = 188;

                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }

                $cptes_substitue["int"]["debit"] = $id_cpte;

                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $SMSTransactionnel["valeur"], $operation, $cptes_substitue, $comptable);

                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            }
        }

        //  prelevement des commission sur operation en deplace
        if ($data_cheque["commission_op_deplace"] > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $operation = 157;

            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $data_cheque["commission_op_deplace"], $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        $cpte_inst_rerait_remote = $CompteObj->getCpteComptaInstRemote();
        if(empty($cpte_inst_rerait_remote)) {
            return new ErrorObj(ERR_CPTE_COMM_INST, _("compte comptable associé au produit d'épargne"));
        }

        //  prelevement des commission pour agent
        if(strcmp($choix_retrait_comm['choix_retrait_comm'], '1') == 0 ){
            $cptes_substitue["cpta"]["debit"] = $cpte_inst_rerait_remote[0]['cpte_compta_comm_retrait'];
            $cptes_substitue["cpta"]["credit"] = $cpte_liaison;
            $cptes_substitue["int"]["debit"] = $id_cpte;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($data_cheque['commission_agent'], 0, $cpte_gui['devise'] );

            //$myErr = passageEcrituresComptablesAuto(622, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $montant, 628, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }else{
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            $cptes_substitue["cpta"]["credit"] = $cpte_liaison;
            $cptes_substitue["int"]["debit"] = $id_cpte;


            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $mnt_commission_total = $data_cheque['commission_agent'] + $data_cheque['commission_institution'];
            $montant = arrondiMonnaie($mnt_commission_total, 0, $cpte_gui['devise'] );


            $operation = 628;
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $montant, $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        // Destroy object
        unset($DeviseObj);

        // Si le compte est passé en découvert, prélever les frais de dossier découvert
        /*
        $myErr2 = $EpargneObj->preleveFraisDecouvert($id_cpte, $comptable);
        if ($myErr2->errCode != NO_ERR) {
            return $myErr2;
        }
        */

        if ($id_mandat != NULL) {
            $MANDAT = $EpargneObj->getInfosMandat($id_mandat);
            $data_cheque['id_pers_ext'] = $MANDAT['id_pers_ext'];
        }

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);

        if ($data_cheque != NULL) {
            $data_his_ext = $HistoriqueObj->creationHistoriqueExterieur($data_cheque);
        } else {
            $data_his_ext = NULL;
        }
        if ($is_insert_chq) {
            if (is_array($dataBef) && count($dataBef)>0) {
                // Init class
                $TireurBenefObj = new TireurBenef($dbc, $id_agence);

                $id = $TireurBenefObj->insereTireurBenef($dataBef);

                // Destroy object
                unset($TireurBenefObj);

                $data_his_ext['id_tireur_benef'] = $id;
            }
            $data_ch['id_cheque'] = $data_cheque['num_piece'];
            $data_ch['date_paiement'] = $data_cheque['date_piece'];
            $data_ch['etat_cheque'] = 1;
            $data_ch['id_benef'] = $data_his_ext['id_tireur_benef'];

            $rep = $CompteObj->insertCheque($data_ch, $id_cpte);

            if ($rep->errCode != NO_ERR) {
                return $rep;
            }
        }

        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], 'agc='.$global_id_agence . ' - login=' . $global_nom_login, 'distant', date("r"), $comptable, $data_his_ext);

        // Destroy object
        unset($CompteObj);
        unset($EpargneObj);
        unset($HistoriqueObj);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }

    public static function retraitCpteLocalAgent($id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $CHANGE = NULL, $dataBef = NULL) {
        global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
        global $dbHandler, $global_multidevise;
        global $global_monnaie, $global_remote_monnaie;

        $comptable = array();

        $db = $dbHandler->openConnection();
        //vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { //1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
            case 15:
                if ($data_cheque['id_correspondant'] == 0)
                    $retrait_transfert = 0; //il s'agit d'un chèque-guichet
                else
                    $retrait_transfert = 1; //il s'agit d'un chèque transmis par une banque
                break;
            default:
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        $choix_retrait_comm = getParamCommissionInsti();
        $agent_data = getDatasLogin($global_nom_login);
        $cpte_ins = $choix_retrait_comm['cpte_compta_comm_retrait'];
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2')?$agent_data['cpte_base_agent']:$agent_data['cpte_flotte_agent'];

        //débit du compte client
        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison locale
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        } else {
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        }
        $cpte_liaison = $cptes_substitue["cpta"]["debit"];
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte liaison comptable de l'agence en déplacé"));
        }

        /* Arrondi du montant si opération au guichet */
        if (( $type_retrait == 1 ) || ( $type_retrait == 4 )) {
            $critere = array();
            $critere['num_cpte_comptable'] = getCompteCptaGui($id_guichet);
            $cpte_gui = getComptesComptables($critere);
            //$montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']);
            $montant = arrondiMonnaiePrecision($montant, $cpte_gui['devise']);
        }

        //crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
        switch ($type_retrait) {
            //retrait par le client en espèce
            case 1:
                $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
                $operation = 624;
                $fonction = 774;
                break;
            //retrait par chèque
            case 15:
            case 4://il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if ($data_cheque['id_correspondant'] == 0 || !isset($data_cheque['id_correspondant'])) {
                    $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
                    $operation = 512;
                    $fonction = 92;
                }
                break;
        }

        $infos_sup = array();

        $text_opt_id = Divers::getLocalTextId("Retrait en déplacé");

        if($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }

        $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, NULL, $infos_sup);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        /** AGB : commission pour agent et institution*/
        if(strcmp($choix_retrait_comm['choix_retrait_comm'], '1') == 0 ){
            $info_impot = getInfoImpot();
            if (sizeof($info_impot) > 0 && $info_impot['appl_impot_agent'] == 't') {
                $calculator_prc = $info_impot['prc_import'] / 100;
                $mnt_impot = $data_cheque['commission_agent'] * $calculator_prc;
                $mnt_impot = arrondiMonnaie($mnt_impot, 0, $cpte_gui['devise'] );

                $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
                $cptes_substitue["cpta"]["credit"] = $info_impot['cpte_impot'];

                $myErr = passageEcrituresComptablesAuto(629, $mnt_impot, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
                $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
                $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
                $cptes_substitue["int"]["debit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = $data_cheque['commission_agent'];
                $montant = arrondiMonnaie($montant, 0, $cpte_gui['devise'] );

                $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

            }else {

                $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
                $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? getCompteCptaProdEp($cpte_agent) : $cpte_agent;
                $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2') ? $cpte_agent : null;
                $cptes_substitue["int"]["debit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = $data_cheque['commission_agent'];
                $montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']);

                $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
        }

        else{
            $info_impot = getInfoImpot();
            if (sizeof($info_impot) > 0 && $info_impot['appl_impot_agent'] == 't') {

                $calculator_prc = $info_impot['prc_import'] / 100;
                $mnt_impot_agent = $data_cheque['commission_agent'] * $calculator_prc;
                $mnt_impot_agent = arrondiMonnaie($mnt_impot_agent, 0, $cpte_gui['devise'] );


                $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
                $cptes_substitue["cpta"]["credit"] = $info_impot['cpte_impot'];
                $cptes_substitue["int"]["credit"] = NULL;
                $operation = 629;

                $myErr = passageEcrituresComptablesAuto($operation, $mnt_impot_agent, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
                $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
                $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
                $cptes_substitue["int"]["debit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant_agent = $data_cheque['commission_agent'] - $mnt_impot_agent;
                $montant_agent = arrondiMonnaie($montant_agent, 0, $cpte_gui['devise'] );

                $operation = 622;
                $myErr = passageEcrituresComptablesAuto($operation, $montant_agent, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
            else {
                $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
                $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
                $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
                $cptes_substitue["int"]["debit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant_agent = $data_cheque['commission_agent'] ;
                $montant_agent = arrondiMonnaie($montant_agent, 0, $cpte_gui['devise'] );

                $operation = 622;
                $myErr = passageEcrituresComptablesAuto($operation, $montant_agent, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }

            if (sizeof($info_impot) > 0 && $info_impot['appl_impot_institution'] == 't') {
                $calculator_prc = $info_impot['prc_import'] / 100;

                $mnt_impot_institution = $data_cheque['commission_institution'] * $calculator_prc;
                $mnt_impot_institution = arrondiMonnaie($mnt_impot_institution, 0, $cpte_gui['devise'] );

                $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
                $cptes_substitue["cpta"]["credit"] = $info_impot['cpte_impot'];
                $cptes_substitue["int"]["credit"] = NULL;
                $operation = 629;

                $myErr = passageEcrituresComptablesAuto($operation, $mnt_impot_institution, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                $cptes_substitue["cpta"]["credit"] = $cpte_ins;
                $cptes_substitue["int"]["credit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant_institution = $data_cheque['commission_institution'] - $mnt_impot_institution;
                $montant_institution = arrondiMonnaie($montant_institution, 0, $cpte_gui['devise'] );

                $operation = 623;
                $myErr = passageEcrituresComptablesAuto($operation, $montant_institution, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
            else{
                $cptes_substitue["cpta"]["credit"] = $cpte_ins;
                $cptes_substitue["int"]["credit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant_institution = $data_cheque['commission_institution'];
                $montant_institution = arrondiMonnaie($montant_institution, 0, $cpte_gui['devise'] );

                $operation = 623;
                $myErr = passageEcrituresComptablesAuto($operation, $montant_institution, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
        }

        /*
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }
        */

        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
        /*
        $frais_retrait = 0;
        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = $InfoProduit['frais_retrait_cpt'];
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = $InfoProduit['frais_transfert'];
            $operation = 152;
        }
        if ($frais_retrait > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé à la caisse"));
            }

            $myErr = effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }
        */

        //
        if ($data_cheque != NULL) {
            $data_his_ext = creationHistoriqueExterieur($data_cheque);
        } else {
            $data_his_ext = NULL;
        }

        $myErr = ajout_historique($fonction, NULL, 'agc='.$id_agence . ' - client=' . $global_id_client, $global_nom_login, date("r"), $comptable, $data_his_ext, NULL);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        $id_his = $myErr->param;
        $id_ecriture = Divers::getIDEcritureByIDHis($id_his);

        // $dbHandler->closeConnection(true); // commit transaction moved outside function

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }


    public static function retraitCpteRemoteMultidevisesAgent(&$dbc, $id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $CHANGE = NULL, $dataBef = NULL)
    {
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $dbHandler, $global_multidevise;
        global $global_remote_monnaie;
        global $global_remote_id_client;

        $comptable = array();
        $is_insert_chq = FALSE;

        // vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { // 1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
                $retrait_transfert = 0; // il s'agit d'un retrait (il faut prélever des frais de retrait)
                break;
            case 15:
                if ($data_cheque['id_correspondant'] == 0)
                    $retrait_transfert = 0; // il s'agit d'un chèque-guichet
                else
                    $retrait_transfert = 1; // il s'agit d'un chèque transmis par une banque
                break;
            default:
                $dbc->rollBack(); // Roll back
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Init class
        $EpargneObj = new Epargne($dbc, $id_agence);

        $mntDispo = $EpargneObj->getSoldeDisponible($id_cpte);
        $mntRetirer = $montant + recupMontant($InfoProduit['frais_retrait_cpt']) + recupMontant($data_cheque["commission_od_retrait"]);
        if (recupMontant($mntRetirer) > recupMontant($mntDispo)){
            $html_err = new HTML_erreur("Echec du retrait en deplacé.");

            $html_err->setMessage("Solde du compte est insuffisant pour continuer l'operation");

            $html_err->addButton("BUTTON_OK", 'Ope-13');

            $html_err->buildHTML();
            echo $html_err->HTML_code;
            die();
        }


        // Verification des taux d'échange dans les 2 agences:
        if (isset($CHANGE)) {
            $erreur = Divers::checkTauxDeviseForOperationDeplacer($InfoCpte['devise'], $CHANGE["devise"], $dbc);

            if ($erreur->errCode != NO_ERR) {
                return $erreur;
            }
        }

        $erreur = $EpargneObj->CheckRetrait($InfoCpte, $InfoProduit, $montant, $retrait_transfert, $id_mandat);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }

        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
        $choix_retrait_comm = getParamCommissionInsti();
        $agent_data = getDatasLogin($global_nom_login);
        $cpte_ins = $choix_retrait_comm['cpte_compta_comm_retrait'];
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2')?$agent_data['cpte_base_agent']:$agent_data['cpte_flotte_agent'];



        // Init class
        $CompteObj = new Compte($dbc, $id_agence);

        // débit du compte client
        $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }

        $cptes_substitue["int"]["debit"] = $id_cpte;

        /* Arrondi du montant si opération au guichet */
        if (($type_retrait == 1) || ($type_retrait == 4)) {
            //$montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);
            $montant = arrondiMonnaiePrecision($montant, $InfoCpte['devise']);

        }

        // crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
        switch ($type_retrait) {
            // retrait par le client en espèce
            case 1:
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $cpte_liaison = $cptes_substitue["cpta"]["credit"];
                $operation = 624;
                $fonction = 92;
                break;
            // retrait par chèque
            case 15:
                // case 3:
            case 4: // il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 512;
                $fonction = 92;
                if ($type_retrait == 15) {
                    $is_insert_chq = TRUE;
                }
                break;
        }

        // Init class
        $DeviseObj = new Devise($dbc, $id_agence);

        $infos_sup = array();

        $text_opt_id = Divers::getRemoteTextId($dbc, "Retrait en déplacé");

        if ($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }

        // Mode multidevise
        if (($type_retrait == 1) && is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"])) {
            $myErr = $DeviseObj->changeRetraitRemote($InfoCpte['devise'], $CHANGE["devise"], $montant, $CHANGE["cv"], $operation, $cptes_substitue, $comptable, 1, $CHANGE["comm_nette"], $CHANGE["taux"], false, $infos_sup);
        } else { // Mode mono devise
            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, NULL, $infos_sup);
        }

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
        $frais_retrait = 0;

        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = $InfoProduit['frais_retrait_cpt'];
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = $InfoProduit['frais_transfert'];
            $operation = 152;
        }
        if ($frais_retrait > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }
            $cptes_substitue["int"]["debit"] = $id_cpte;
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }




        // Frais de commission sur od retrait
        if ($data_cheque["commission_od_retrait_cv"] > 0 && (isset($CHANGE))) {

            $operation = 157;
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }
            $cptes_substitue["int"]["debit"] = $id_cpte;
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $data_cheque["commission_od_retrait_cv"], $operation, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        else {
            if ($data_cheque["commission_od_retrait"] > 0) {
                $operation = 157;
                $cptes_substitue = array();
                $cptes_substitue["cpta"] = array();
                $cptes_substitue["int"] = array();
                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }
                $cptes_substitue["int"]["debit"] = $id_cpte;
                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $data_cheque["commission_od_retrait"], $operation, $cptes_substitue, $comptable);

                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            }

        }

        /** Recuperation des commissions pour agents/inst */
        $cpte_inst_rerait_remote = $CompteObj->getCpteComptaInstRemote();
        if(empty($cpte_inst_rerait_remote)) {
            return new ErrorObj(ERR_CPTE_COMM_INST, _("compte comptable associé au produit d'épargne"));
        }

        //  prelevement des commission pour agent
        if(strcmp($choix_retrait_comm['choix_retrait_comm'], '1') == 0 ){
            $cptes_substitue["cpta"]["debit"] = $cpte_inst_rerait_remote[0]['cpte_compta_comm_retrait'];
            $cptes_substitue["cpta"]["credit"] = $cpte_liaison;
            $cptes_substitue["int"]["debit"] = $id_cpte;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($data_cheque['commission_agent'], 0, $cpte_gui['devise'] );

            //$myErr = passageEcrituresComptablesAuto(622, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $montant, 622, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }else{
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            $cptes_substitue["cpta"]["credit"] = $cpte_liaison;
            $cptes_substitue["int"]["debit"] = $id_cpte;


            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($data_cheque['commission_agent'], 0, $cpte_gui['devise'] );

            $operation = 622;
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $montant, 622, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }

            $cptes_substitue["cpta"]["credit"] = $cpte_liaison;
            $cptes_substitue["int"]["credit"] = null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($data_cheque['commission_institution'], 0, $cpte_gui['devise'] );

            $operation = 623;
            $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $montant, 623, $cptes_substitue, $comptable);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        /** Fin */



        // Fin operation de commission sur od retrait

        // Si le compte est passé en découvert, prélever les frais de dossier découvert
        /*
         * $myErr2 = $EpargneObj->preleveFraisDecouvert($id_cpte, $comptable); if ($myErr2->errCode != NO_ERR) { return $myErr2; }
         */
        // Destroy object
        unset($DeviseObj);
        if ($id_mandat != NULL) {
            $MANDAT = $EpargneObj->getInfosMandat($id_mandat);
            $data_cheque['id_pers_ext'] = $MANDAT['id_pers_ext'];
        }

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);

        if ($data_cheque != NULL) {
            $data_his_ext = $HistoriqueObj->creationHistoriqueExterieur($data_cheque);
        } else {
            $data_his_ext = NULL;
        }
        if ($is_insert_chq) {
            if (is_array($dataBef) && count($dataBef) > 0) {
                // Init class
                $TireurBenefObj = new TireurBenef($dbc, $id_agence);

                $id = $TireurBenefObj->insereTireurBenef($dataBef);

                // Destroy object
                unset($TireurBenefObj);

                $data_his_ext['id_tireur_benef'] = $id;
            }
            $data_ch['id_cheque'] = $data_cheque['num_piece'];
            $data_ch['date_paiement'] = $data_cheque['date_piece'];
            $data_ch['etat_cheque'] = 1;
            $data_ch['id_benef'] = $data_his_ext['id_tireur_benef'];

            $rep = $CompteObj->insertCheque($data_ch, $id_cpte);

            if ($rep->errCode != NO_ERR) {
                return $rep;
            }
        }

        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], 'agc=' . $global_id_agence . ' - login=' . $global_nom_login, 'distant', date("r"), $comptable, $data_his_ext);

        // Destroy object
        unset($CompteObj);
        unset($EpargneObj);
        unset($HistoriqueObj);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];

        return new ErrorObj(NO_ERR, array(
          'id_his' => $id_his,
          'id_ecriture' => $id_ecriture
        ));
    }


    public static function retraitCpteLocalMultidevisesAgent($id_agence, $id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque = NULL, $CHANGE = NULL, $dataBef = NULL) {
        global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
        global $dbHandler, $global_multidevise;
        global $global_monnaie, $global_remote_monnaie;

        $comptable = array();

        // Open connection here but closed in caller function
        $db = $dbHandler->openConnection();

        //vérifier d'abord qu'on peut retirer
        switch ($type_retrait) { //1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
            case 1:
            case 4:
            case 15:
                if ($data_cheque['id_correspondant'] == 0)
                    $retrait_transfert = 0; //il s'agit d'un chèque-guichet
                else
                    $retrait_transfert = 1; //il s'agit d'un chèque transmis par une banque
                break;
            default:
                signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }

        // Passage de l'écriture de retrait
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
        $choix_retrait_comm = getParamCommissionInsti();
        $agent_data = getDatasLogin($global_nom_login);
        $cpte_ins = $choix_retrait_comm['cpte_compta_comm_retrait'];
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2')?$agent_data['cpte_base_agent']:$agent_data['cpte_flotte_agent'];

        //débit du compte client
        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison locale
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        } else {
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        }
        $cpte_liaison = $cptes_substitue["cpta"]["debit"];
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte liaison comptable de l'agence en déplacé"));
        }

        /* Arrondi du montant si opération au guichet */
        if (( $type_retrait == 1 ) || ( $type_retrait == 4 )) {
            $critere = array();
            $critere['num_cpte_comptable'] = getCompteCptaGui($id_guichet);
            $cpte_gui = getComptesComptables($critere);
            //$montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']);
            $montant = arrondiMonnaiePrecision($montant, $cpte_gui['devise']);
        }

        //crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
        switch ($type_retrait) {
            //retrait par le client en espèce
            case 1:
                $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
                $operation = 140;
                $fonction = 92;
                break;
            //retrait par chèque
            case 15:
            case 4://il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
                if ($data_cheque['id_correspondant'] == 0 || !isset($data_cheque['id_correspondant'])) {
                    $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
                    $operation = 512;
                    $fonction = 92;
                }
                break;
        }

        $infos_sup = array();

        $text_opt_id = Divers::getLocalTextId("Retrait en déplacé");

        if($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }

        if(($type_retrait == 1) && is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"]))
        {
            //verification si les commissions doivent etre prelevés sur l'agence locale
            $toPreleveCommissionsDansAgenceLocal = getWherePerceptionCommissionsMultiAgence();

            // Traitement si les commissions doivent etre prelevés dans l'agence locale
            if($toPreleveCommissionsDansAgenceLocal) {
                $myErr = changeRetraitLocalAvecCommissions($InfoCpte['devise'], $CHANGE['devise'], $montant, $CHANGE['cv'], $operation, $cptes_substitue, $comptable, 1, $CHANGE['comm_nette'], $CHANGE['taux']);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
            // Traitement si les commissions ne seront pas prelevés dans l'agence locale
            else
            {
                // On recupere les infos pour passer une ecriture comptable en devise entre le compte de caisse en devise et le compte liaison dans la meme devise
                $montant = $CHANGE['cv'];
                $devise = $CHANGE['devise'];
                $cptes_substitue["cpta"]["debit"] = checkCptDeviseOK($cptes_substitue["cpta"]["debit"], $devise);
                $cptes_substitue["cpta"]["credit"] = checkCptDeviseOK($cptes_substitue["cpta"]["credit"], $devise);

                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $devise, NULL, $id_cpte, $infos_sup);
                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
        }
        else {
            $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, NULL, $infos_sup);
        }

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }


        if(strcmp($choix_retrait_comm['choix_retrait_comm'], '1') == 0 ){
            $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
            $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
            $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
            $cptes_substitue["int"]["debit"] = null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($data_cheque['commission_agent'], 0, $cpte_gui['devise'] );

            $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }else{
            $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
            $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
            $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
            $cptes_substitue["int"]["debit"] = null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($data_cheque['commission_agent'], 0, $cpte_gui['devise'] );

            $operation = 622;
            $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }

            $cptes_substitue["cpta"]["credit"] = $cpte_ins;
            $cptes_substitue["int"]["credit"] = null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($data_cheque['commission_institution'], 0, $cpte_gui['devise'] );

            $operation = 623;
            $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
        /*
        $frais_retrait = 0;
        if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
            $frais_retrait = $InfoProduit['frais_retrait_cpt'];
            $operation = 131;
        }
        if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
            $frais_retrait = $InfoProduit['frais_transfert'];
            $operation = 152;
        }
        if ($frais_retrait > 0) {
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();

            $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                $dbHandler->closeConnection(false);
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé à la caisse"));
            }

            $myErr = effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);
            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }
        */

        //
        if ($data_cheque != NULL) {
            $data_his_ext = creationHistoriqueExterieur($data_cheque);
        } else {
            $data_his_ext = NULL;
        }

        $myErr = ajout_historique($fonction, NULL, 'agc='.$id_agence . ' - client=' . $global_id_client, $global_nom_login, date("r"), $comptable, $data_his_ext, NULL); // $InfoCpte["id_titulaire"]
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        $id_his = $myErr->param;
        $id_ecriture = Divers::getIDEcritureByIDHis($id_his);

        // commit transaction moved outside function
        //$dbHandler->closeConnection(true);

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }
        
    
    /** Getters & Setters */
    public function getIdGuichet() {
        return $this->_id_guichet;
    }

    public function setIdGuichet($value) {
        $this->_id_guichet = $value;
    }

    public function getIdCpte() {
        return $this->_id_cpte;
    }

    public function setIdCpte($value) {
        $this->_id_cpte = $value;
    }

    public function getInfoProduit() {
        return $this->_info_produit;
    }

    public function setInfoProduit($value) {
        $this->_info_produit = $value;
    }

    public function getInfoCpte() {
        return $this->_info_cpte;
    }

    public function setInfoCpte($value) {
        $this->_info_cpte = $value;
    }

    public function getMontant() {
        return $this->_montant;
    }

    public function setMontant($value) {
        $this->_montant = $value;
    }

    public function getTypeRetrait() {
        return $this->_type_retrait;
    }

    public function setTypeRetrait($value) {
        $this->_type_retrait = $value;
    }

    public function getIdMandat() {
        return $this->_id_mandat;
    }

    public function setIdMandat($value) {
        $this->_id_mandat = $value;
    }

    public function getDataCheque() {
        return $this->_data_cheque;
    }

    public function setDataCheque($value) {
        $this->_data_cheque = $value;
    }

    public function getChange() {
        return $this->_change;
    }

    public function setChange($value) {
        $this->_change = $value;
    }

    public function getDataBef() {
        return $this->_data_bef;
    }

    public function setDataBef($value) {
        $this->_data_bef = $value;
    }

    public function getNomLogin() {
        return $this->_nom_login;
    }

    public function setNomLogin($value) {
        $this->_nom_login = $value;
    }

    public function getIdAgence() {
        return $this->_id_agence;
    }

    public function setIdAgence($value) {
        $this->_id_agence = $value;
    }

    public function getIdClient() {
        return $this->_id_client;
    }

    public function setIdClient($value) {
        $this->_id_client = $value;
    }

    public function getMultiDevise() {
        return $this->_multidevise;
    }

    public function setMultiDevise($value) {
        $this->_multidevise = $value;
    }

    public function getMonnaie() {
        return $this->_monnaie;
    }

    public function setMonnaie($value) {
        $this->_monnaie = $value;
    }

}
