<?php

/**
 * Description de la classe Depot
 *
 * @author danilo
 */
class Depot {

    /** Properties */
    private $_id_guichet;
    private $_id_cpte;
    private $_info_produit;
    private $_info_cpte;
    private $_data;
    private $_montant;
    private $_type_depot;
    private $_change;
    private $_frais_virement;
    private $_infos_sup;
    private $_nom_login;
    private $_id_agence;
    private $_monnaie;
    private $_id_client;
    private $_multidevise;

    public function __construct($nom_login, $id_agence, $monnaie, $id_guichet, $id_cpte, $montant, $info_produit_arr, $info_cpte_arr, $data = NULL, $type_depot = NULL, $change = NULL, $frais_virement = NULL, $infos_sup = NULL) {

        $this->setNomLogin($nom_login);
        $this->setIdAgence($id_agence);
        $this->setMonnaie($monnaie);
        $this->setIdGuichet($id_guichet);
        $this->setIdCpte($id_cpte);
        $this->setMontant($montant);
        $this->setInfoProduit($info_produit_arr);
        $this->setInfoCpte($info_cpte_arr);
        $this->setData($data);
        $this->setTypeDepot($type_depot);
        $this->setChange($change);
        $this->setFraisVirement($frais_virement);
        $this->setInfosSup($infos_sup);
    }
   
    
    /**
     *
     * Enregistre une opération de <B>dépôt en espèce</B> sur un compte d'épargne en local
     *
     * @param int $id_agence_remote L'ID de l'agence remote
     * @param int $id_guichet L'ID du guichet ayant encaissé le montant du dépôt
     * @param float $montant Montant du dépôt
     * @param int $type_depot Type de dépôt (1 si dépôt express, 2 si dépôt normal)
     * @param array $CHANGE : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfrice fait sur le taux)
     *
     * @return ErrorObj Les erreurs possibles sont <UL>
     *   <LI> Celles renvoyées par {@link #CheckDepot checkDepot} </LI>
     *   <LI> Celles renvoyées par {@link #passageEcrituresComptablesAuto passageEcrituresComptablesAuto} </LI> </UL>
     *
     *
     */
    public static function depoCpteLocal($id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL) {
        global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
        global $dbHandler, $global_multidevise;
        global $global_monnaie, $global_remote_monnaie;

        //pour pouvoir commit ou rollback toute la procédure
        $db = $dbHandler->openConnection();

        if ($DATA != NULL && $type_depot != 1) {
            $DATA_HIS_EXT = creationHistoriqueExterieur($DATA);
        } else {
            $DATA_HIS_EXT = NULL;
        }

        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /* if ($InfoCpte["etat_cpte"] == 4) {
          $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
          $result = $db->query($sql);
          if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
          }
          } */

        //Check que le dépôt est possible sur le compte

        /*
          $erreur = CheckDepot($InfoCpte, $montant);

          if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
          }
         */

        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        //débit d'un guichet par le crédit d'un client en deplacé
        $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);

        /* Arrondi du montant si paiement au guichet */
        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']);

        // Recuperation du compte de liason qui va etre credité
        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison locale
            $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        } else {
            $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        }

        if ($cptes_substitue["cpta"]["credit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte liaison comptable de l'agence en déplacée"));
        }
   
        $text_opt_id = Divers::getLocalTextId("Dépôt en déplacé");

        if ($text_opt_id > 0) {
        	$infos_sup["autre_libel_ope"] = $text_opt_id;
        }

       	// 160 : "Dépôt espèces"
        $operation = 160;
        $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
        	$dbHandler->closeConnection(false);
         	return $myErr;
        }
        

        /*
          if ($type_depot == 1) {//dépôt express
          $fonction = 86;
          } else if ($type_depot == NULL) {//dépôt normal
          $fonction = 75;
          } else if ($type_depot == 158) {//dépôt par lot
          $fonction = 158;
          } else if ($type_depot == 159) {//dépôt par lot via fichier
          $fonction = 159;
          }
         */

        $fonction = 93; // Dépôt en déplacé

        $infos_his = "agc=" . $id_agence . " - client=" . $global_id_client;

        $myErr = ajout_historique($fonction, NULL, $infos_his, $global_nom_login, date("r"), $comptable, $DATA_HIS_EXT);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        $id_his = $myErr->param;
        $id_ecriture = Divers::getIDEcritureByIDHis($id_his);
        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));     
    }

    /**
     *
     * Enregistre une opération de <B>dépôt en espèce</B> sur un compte d'épargne en remote
     *
     * @param int $id_agence L'ID de l'agence remote
     * @param int $id_guichet L'ID du guichet de l'agence local ayant encaissé le montant du dépôt
     * @param int $id_cpte L"ID du compte bénéficiaire remote (table ad_cpt dans base remote)
     * @param float $montant Montant du dépôt
     * @param int $type_depot Type de dépôt (1 si dépôt express, 2 si dépôt normal)
     * @param array $CHANGE : Optionnel,      les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfrice fait sur le taux)
     *
     * @return ErrorObj Les erreurs possibles sont <UL>
     *   <LI> Celles renvoyées par {@link #CheckDepot checkDepot} </LI>
     *   <LI> Celles renvoyées par {@link #passageEcrituresComptablesAuto passageEcrituresComptablesAuto} </LI> </UL>
     *
     *
     */
    public static function depotCpteRemote(&$dbc, $id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL, &$montant_remboursement) {
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $global_multidevise;
        global $global_remote_monnaie, $global_monnaie;
        global $global_remote_id_client;

        // Le login configuré pour loggé les transactions distantes
        $login_remote = 'distant';

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);
        $EpargneObj = new Epargne($dbc, $id_agence);
        $CompteObj = new Compte($dbc, $id_agence);
        $ComptaObj = new Compta($dbc, $id_agence);
        $DeviseObj = new Devise($dbc, $id_agence);
        $ClientObj = new Client($dbc, $id_agence);

        if ($DATA != NULL) {
            $DATA_HIS_EXT = $HistoriqueObj->creationHistoriqueExterieur($DATA);
        } else {
            $DATA_HIS_EXT = NULL;
        }

        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /* if ($InfoCpte["etat_cpte"] == 4) {
          $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
          $result = $db->query($sql);
          if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
          }
          } */

        //Check que le dépôt est possible sur le compte       
        $erreur = $EpargneObj->CheckDepot($InfoCpte, $montant);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }

        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array(); // copmte comptable produit epargne
        $cptes_substitue["int"] = array(); // compte client
        
        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison remote
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        } else {
            // [DEBIT] Compte liaison Agence Local
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        }

        // [CREDIT] Compte Comptable Produit Epargne Remote    	
        $cptes_substitue["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

        if ($cptes_substitue["cpta"]["credit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        // [CREDIT] Compte client remote
        $cptes_substitue["int"]["credit"] = $id_cpte;

        // Arrondi du montant si paiement au guichet
        $montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);

        /* Arrondi du montant si paiement au guichet */
        /*
          $critere = array();
          $critere['num_cpte_comptable'] = $CompteObj->getCompteCptaGui($id_guichet); // recup un compte guichet
          $cpte_gui = $ComptaObj->getComptesComptables($critere);
         */

        // @todo :arrondie a partir devise
        // recup le montant dans la devise remote
        //$montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $cpte_gui['devise']);

        // 160 : Dépôt espèces    	
        $operation = 160;

        // Recuperation de la libelle du depot
        $text_opt_id = Divers::getRemoteTextId($dbc, "Dépôt en déplacé");

        if ($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }

        $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        //--------------- frais d'opérations ---------------------------------------------------
        //en cas de frais d'opérations, débit compte guichet et crédit compte interne de produit

        if ($InfoProduit["frais_depot_cpt"] > 0) {
            unset($cptes_substitue["cpta"]["credit"]);
            unset($cptes_substitue["int"]["credit"]);
            $montant_remboursement -= $InfoProduit["frais_depot_cpt"];

            $operation_frais = 150;

            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            // Passage des écritures comptables
            if ($InfoCpte['devise'] == $global_remote_monnaie) {
                //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte);    	        
                $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue, NULL, NULL, $id_cpte);
            } else {
                //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable, NULL, NULL, $id_cpte);
            }
            if ($myErr->errCode != NO_ERR) {
                //$dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        // en cas de frais SMS transactionnel
        $listeTypeOpt = $EpargneObj->getListeTypeOptDepPourPreleveFraisSMS();

        if (in_array($operation, $listeTypeOpt)) {
            $SMSTransactionnel = $EpargneObj->getTarificationDatas();
            $clientSMS = $ClientObj->checkIfClientAbonnerSMS($global_id_client);

            if (!empty($SMSTransactionnel) && $clientSMS == true) {
                unset($cptes_substitue["cpta"]["credit"]);
                unset($cptes_substitue["int"]["credit"]);

                $montant_remboursement -= $SMSTransactionnel["valeur"];
                $operation_frais = 188;

                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }

                $cptes_substitue["int"]["debit"] = $id_cpte;

                // Passage des écritures comptables
                if ($InfoCpte['devise'] == $global_remote_monnaie) {
                    //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
                    $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_frais, $SMSTransactionnel["valeur"], $comptable, $cptes_substitue, NULL, NULL, $id_cpte);
                } else {
                    //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
                    $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $SMSTransactionnel["valeur"], $operation_frais, $cptes_substitue, $comptable, NULL, NULL, $id_cpte);
                }
                if ($myErr->errCode != NO_ERR) {
                    //$dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
        }

        //  Debut process == >> Commmission sur operation en deplace
        if ($DATA['commission_op_deplace'] > 0){

            unset($cptes_substitue["cpta"]["credit"]);
            unset($cptes_substitue["int"]["credit"]);
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            $operation_od_commission = 156;
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $montant_remboursement -= $DATA['commission_op_deplace'];
            $cptes_substitue["int"]["debit"] = $id_cpte;
            // Passage des écritures comptables
            if ($InfoCpte['devise'] == $global_remote_monnaie) {
                //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
                $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_od_commission, $DATA['commission_op_deplace'] , $comptable, $cptes_substitue, NULL, NULL, $id_cpte);

            } else {
                //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $DATA['commission_op_deplace'] , $operation_od_commission, $cptes_substitue, $comptable, NULL, NULL, $id_cpte);

            }
            if ($myErr->errCode != NO_ERR) {
                //$dbHandler->closeConnection(false);
                return $myErr;
            }//exit();
        }

        //  Fin Process == >> Commission sur operation en deplace

        /* Eventuels frais de virement en cas de dépôt par lot pour les virement de salaires ------------- */

        /* PAS DE FRAIS DE VIREMENT EN REMOTE ?
         *
          if ($frais_virement != NULL )
          {
          unset($cptes_substitue["cpta"]["credit"]);
          unset($cptes_substitue["int"]["credit"]);

          //Compte comptable associé au produit d'épargne du compte
          $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
          if ($cptes_substitue["cpta"]["debit"] == NULL) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
          }
          $cptes_substitue["int"]["debit"] = $id_cpte;

          //Passage des écritures comptables: les frais de virement sont dans la devise de référence
          if ($InfoCpte['devise']==$global_monnaie) {
          $myErr = passageEcrituresComptablesAuto(151, $frais_virement, $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
          } else {
          $myErr = effectueChangePrivate($InfoCpte['devise'],$global_monnaie,  $frais_virement, 151, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
          }

          if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
          }
          }
         */

        if ($type_depot == 1) {//dépôt express
            $fonction = 86;
        } else if ($type_depot == NULL) {//dépôt normal
            $fonction = 75;
        } else if ($type_depot == 158) {//dépôt par lot
            $fonction = 158;
        } else if ($type_depot == 159) {//dépôt par lot via fichier
            $fonction = 159;
        }

        $fonction = 93; // Dépôt en déplacé    	
        $login = $id_agence . ' - ' . $global_nom_login;

        $infos_his = 'agc=' . $global_id_agence . ' - login=' . $global_nom_login;
        $login = $login_remote;

        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], $infos_his, $login, date("r"), $comptable, $DATA_HIS_EXT);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));

        /*
          $id_his = $myErr->param;
          return new ErrorObj(NO_ERR, array('id'=>$id_his));
         */
    }

    /**
     *
     * Renverse une opération de <B>dépôt en espèce</B> sur un compte d'épargne en remote
     *
     * @param int $id_agence L'ID de l'agence remote
     * @param int $id_guichet L'ID du guichet de l'agence local ayant encaissé le montant du dépôt
     * @param int $id_cpte L"ID du compte bénéficiaire remote (table ad_cpt dans base remote)
     * @param float $montant Montant du dépôt
     * @param int $type_depot Type de dépôt (1 si dépôt express, 2 si dépôt normal)
     * @param array $CHANGE : Optionnel, les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfrice fait sur le taux)
     *
     * @return ErrorObj Les erreurs possibles sont <UL>
     *   <LI> Celles renvoyées par {@link #CheckDepot checkDepot} </LI>
     *   <LI> Celles renvoyées par {@link #passageEcrituresComptablesAuto passageEcrituresComptablesAuto} </LI> </UL>
     *
     *
     */
    public static function depotCpteRemoteRevert(&$dbc, $id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL, $errMsg = "") {
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $global_multidevise;
        global $global_remote_monnaie, $global_monnaie;
        global $global_remote_id_client;

        // Le login configuré pour loggé les transactions distantes
        $login_remote = 'distant';

        //renverse le montant:
        $montant = 0 - $montant;

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);
        $EpargneObj = new Epargne($dbc, $id_agence);
        $CompteObj = new Compte($dbc, $id_agence);
        $ComptaObj = new Compta($dbc, $id_agence);
        $DeviseObj = new Devise($dbc, $id_agence);


        /*
          if ($DATA != NULL) {
          $DATA_HIS_EXT = $HistoriqueObj->creationHistoriqueExterieur($DATA);
          } else {
          $DATA_HIS_EXT = NULL;
          }
         */

        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /* if ($InfoCpte["etat_cpte"] == 4) {
          $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
          $result = $db->query($sql);
          if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
          }
          } */

        //Check que le dépôt est possible sur le compte
        $erreur = $EpargneObj->CheckDepot($InfoCpte, $montant);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }

        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array(); // copmte comptable produit epargne
        $cptes_substitue["int"] = array(); // compte client
        
        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison remote
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        } else {
            // [DEBIT] Compte liason Agence Local
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        }

        // [CREDIT] Compte Comptable Produit Epargne Remote
        $cptes_substitue["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

        if ($cptes_substitue["cpta"]["credit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        // [CREDIT] Compte client remote
        $cptes_substitue["int"]["credit"] = $id_cpte;

        // Arrondi du montant si paiement au guichet
        $montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);

        /* Arrondi du montant si paiement au guichet */
        /*
          $critere = array();
          $critere['num_cpte_comptable'] = $CompteObj->getCompteCptaGui($id_guichet); // recup un compte guichet
          $cpte_gui = $ComptaObj->getComptesComptables($critere);
         */

        // @todo :arrondie a partir devise
        // recup le montant dans la devise remote
        //$montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $cpte_gui['devise']);
        
        // @todo : revert multidevises 
        /*
          if (isset($CHANGE)) {
          //$myErr = Devise::change($dbc, $id_agence, $CHANGE['devise'], $InfoCpte['devise'], $CHANGE['cv'], $montant, 160, $cptes_substitue, $comptable, $CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE['taux'],true, $infos_sup);
          $myErr = $DeviseObj->change($CHANGE['devise'], $InfoCpte['devise'], $CHANGE['cv'], $montant, 160, $cptes_substitue, $comptable, $CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE['taux'],true, $infos_sup);

          if ($myErr->errCode != NO_ERR) {
          return $myErr;
          }
          } else {
          // 160 : Dépôt espèces
          $myErr = $CompteObj->passageEcrituresComptablesAuto(160, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte,$infos_sup);

          if ($myErr->errCode != NO_ERR) {
          return $myErr;
          }
          }
         */

        // 160 : Dépôt espèces
        $operation = 160;

        // Recuperation de la libelle du depot
        $text_opt_id = Divers::getRemoteTextId($dbc, "Dépôt en déplacé");

        if ($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }

        $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        //--------------- frais d'opérations ---------------------------------------------------
        //en cas de frais d'opérations, débit compte guichet et crédit compte interne de produit

        if ($InfoProduit["frais_depot_cpt"] > 0) {
            unset($cptes_substitue["cpta"]["credit"]);
            unset($cptes_substitue["int"]["credit"]);

            $operation_frais = 150;

            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $frais_depot = 0 - $InfoProduit["frais_depot_cpt"];

            // Passage des écritures comptables
            if ($InfoCpte['devise'] == $global_remote_monnaie) {
                //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
                $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_frais, $frais_depot, $comptable, $cptes_substitue, NULL, NULL, $id_cpte);
            } else {
                //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $frais_depot, $operation_frais, $cptes_substitue, $comptable, NULL, NULL, $id_cpte);
            }
            if ($myErr->errCode != NO_ERR) {
                //$dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        //----------------------------Remb Commission OD ---------------------------------------------------------
        if ($DATA['commission_op_deplace'] > 0){

            unset($cptes_substitue["cpta"]["credit"]);
            unset($cptes_substitue["int"]["credit"]);

            $operation_od_commission = 156;
            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            $commission_od_prelev_annul = 0 - $InfoProduit["frais_depot_cpt"];

            // Passage des écritures comptables
            if ($InfoCpte['devise'] == $global_remote_monnaie) {
                //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
                $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_od_commission, $commission_od_prelev_annul , $comptable, $cptes_substitue, NULL, NULL, $id_cpte);

            } else {
                //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $commission_od_prelev_annul , $operation_od_commission, $cptes_substitue, $comptable, NULL, NULL, $id_cpte);

            }
            if ($myErr->errCode != NO_ERR) {
                //$dbHandler->closeConnection(false);
                return $myErr;
            }
        }


        $fonction = 93; // Dépôt en déplacé
        $login = $id_agence . ' - ' . $global_nom_login;

        $infos_his = 'agc=' . $global_id_agence . ' - login=' . $global_nom_login . ' - ' . $errMsg;
        $login = $login_remote;

        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], $infos_his, $login, date("r"), $comptable, $DATA_HIS_EXT);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));

        /*
          $id_his = $myErr->param;
          return new ErrorObj(NO_ERR, array('id'=>$id_his));
         */
    }

    /**
     *
     *
     * Enregistre une opération de <B>dépôt en espèce</B> sur un compte d'épargne en remote, en mode multidevises
     *
     * @param int $id_agence
     *        	L'ID de l'agence remote
     * @param int $id_guichet
     *        	L'ID du guichet de l'agence local ayant encaissé le montant du dépôt
     * @param int $id_cpte
     *        	L"ID du compte bénéficiaire remote (table ad_cpt dans base remote)
     * @param float $montant
     *        	Montant du dépôt
     * @param int $type_depot
     *        	Type de dépôt (1 si dépôt express, 2 si dépôt normal)
     * @param array $CHANGE
     *        	: Optionnel, les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfrice fait sur le taux)
     *        	
     * @return ErrorObj Les erreurs possibles sont <UL>
     *         <LI> Celles renvoyées par {@link #CheckDepot checkDepot} </LI>
     *         <LI> Celles renvoyées par {@link #passageEcrituresComptablesAuto passageEcrituresComptablesAuto} </LI> </UL>
     *        
     *        
     */
    public static function depotCpteRemoteMultidevises(&$dbc, $id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL) {
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $global_multidevise;
        global $global_remote_monnaie, $global_monnaie;
        global $global_remote_id_client;

        // Le login configuré pour loggé les transactions distantes
        $login_remote = 'distant';

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);
        $EpargneObj = new Epargne($dbc, $id_agence);
        $CompteObj = new Compte($dbc, $id_agence);
        $ComptaObj = new Compta($dbc, $id_agence);
        $DeviseObj = new Devise($dbc, $id_agence);

        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /*
         * if ($InfoCpte["etat_cpte"] == 4) { $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte"; $result = $db->query($sql); if (DB::isError($result)) { $dbHandler->closeConnection(false); signalErreur(__FILE__,__LINE__,__FUNCTION__); } }
         */

        // Verifcation des taux d'échange dans les 2 agences :
        if (isset($CHANGE)) {
            $erreur = Divers::checkTauxDeviseForOperationDeplacer($CHANGE['devise'], $InfoCpte['devise'], $dbc);
            
            if($erreur->errCode != NO_ERR) {
                return $erreur;
            }

            $CHANGE['cv'] -= arrondiMonnaiePrecision($DATA['commission_ope_deplace'],$CHANGE['devise']);
            $montant -= arrondiMonnaiePrecision($DATA['commission_ope_deplace_cv'],$InfoCpte['devise']);

        }
        
        // Check que le dépôt est possible sur le compte
        $erreur = $EpargneObj->CheckDepot($InfoCpte, $montant);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }
        
        if ($DATA != NULL) {
            $DATA_HIS_EXT = $HistoriqueObj->creationHistoriqueExterieur($DATA);
        } else {
            $DATA_HIS_EXT = NULL;
        }
        
        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue ["cpta"] = array(); // copmte comptable produit epargne
        $cptes_substitue ["int"] = array(); // compte client
        
        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison remote
            $cptes_substitue["cpta"] ["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        } else {
            // [DEBIT] Compte liason Agence Local
            $cptes_substitue["cpta"] ["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        }

        // [CREDIT] Compte Comptable Produit Epargne Remote
        $cptes_substitue["cpta"] ["credit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

        if ($cptes_substitue["cpta"] ["credit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        // [CREDIT] Compte client remote
        $cptes_substitue ["int"] ["credit"] = $id_cpte;
        
        if ($type_depot == 11) {
            // Transfert entre comptes d'epargne
            $operation = 120;
            $fonction = 77; // Transfert compte API
        } else {
            // Transfert entre comptes d'epargne
            $operation = 160;
            $fonction = 93; // Dépôt en déplacé multi-devise

            // Arrondi du montant si paiement au guichet
            $montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte ['devise']);
        }
        // Deal with multi agence multi devise transactions
        if (isset($CHANGE)) {
            $myErr = $DeviseObj->changeDepotRemote($CHANGE ['devise'], $InfoCpte ['devise'], $CHANGE['cv'] , $montant, $operation, $cptes_substitue, $comptable, $CHANGE ['dest_reste'], $CHANGE ['comm_nette'], $CHANGE ['taux'], true, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        } else {            
            // 160 : Dépôt espèces
            $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte ['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        //TODO : inserer les mouvements + les operations comptables + et reduire le montant passer en haut sur la fonction changeDepotRemote.
        if ($DATA['commission_ope_deplace'] > 0 ){
            unset($cptes_substitue["cpta"]["credit"]);
            unset($cptes_substitue["int"]["credit"]);
            if (isset($CHANGE)){

                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"] ["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    // [DEBIT] Compte liason Agence Local
                    $cptes_substitue["cpta"] ["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 156;

                $myErr = $DeviseObj->changeDepotRemote($CHANGE ['devise'], $InfoCpte ['devise'], $DATA['commission_ope_deplace'], $DATA['commission_ope_deplace_cv'], $operation, $cptes_substitue, $comptable, $CHANGE ['dest_reste'], null, null, true, $infos_sup);

                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            }
            else{ // perception des commission en deplace operation : 156

                unset($cptes_substitue["cpta"]["credit"]);
                unset($cptes_substitue["int"]["credit"]);
                $cptes_substitue["cpta"] ["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

                $cptes_substitue ["int"] ["debit"] = $id_cpte;

                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }
                $operation = 156;

                $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $DATA['commission_ope_deplace'], $comptable, $cptes_substitue, $InfoCpte ['devise'], NULL, $id_cpte, $infos_sup);

                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }


            }
        }


        // --------------- frais d'opérations ---------------------------------------------------
        // en cas de frais d'opérations, débit compte guichet et crédit compte interne de produit

        /*
         * if ($InfoProduit["frais_depot_cpt"] > 0 ) { unset($cptes_substitue["cpta"]["credit"]); unset($cptes_substitue["int"]["credit"]); $operation_frais = 150; $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte); if ($cptes_substitue["cpta"]["debit"] == NULL) { return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne")); } $cptes_substitue["int"]["debit"] = $id_cpte; // Passage des écritures comptables if ($InfoCpte['devise'] == $global_remote_monnaie) { //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte); $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte); } else { //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte); $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte); } if ($myErr->errCode != NO_ERR) { //$dbHandler->closeConnection(false); return $myErr; } }
         */


        /* Eventuels frais de virement en cas de dépôt par lot pour les virement de salaires ------------- */

        /* PAS DE FRAIS DE VIREMENT EN REMOTE ?
         *
          if ($frais_virement != NULL )
          {
          unset($cptes_substitue["cpta"]["credit"]);
          unset($cptes_substitue["int"]["credit"]);

          //Compte comptable associé au produit d'épargne du compte
          $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
          if ($cptes_substitue["cpta"]["debit"] == NULL) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
          }
          $cptes_substitue["int"]["debit"] = $id_cpte;

          //Passage des écritures comptables: les frais de virement sont dans la devise de référence
          if ($InfoCpte['devise']==$global_monnaie) {
          $myErr = passageEcrituresComptablesAuto(151, $frais_virement, $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
          } else {
          $myErr = effectueChangePrivate($InfoCpte['devise'],$global_monnaie,  $frais_virement, 151, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
          }

          if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
          }
          }
         */
     
        //$fonction = 93; // Dépôt en déplacé
        $infos_his = 'agc=' . $global_id_agence . ' - login=' . $global_nom_login;
        $login = $login_remote;      
        
        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte ["id_titulaire"], $infos_his, $login, date("r"), $comptable, $DATA_HIS_EXT);
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        $id_his = $myErr->param ['id_his'];
        $id_ecriture = $myErr->param ['id_ecriture'];
        
        // Unset used objects
        unset($HistoriqueObj);
        unset($DeviseObj);
        unset($EpargneObj);
        unset($CompteObj);
        unset($ComptaObj);

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }

    /**
     *
     *
     * Enregistre une opération de <B>dépôt en espèce</B> sur un compte d'épargne en remote, en mode multidevises
     *
     * @param int $id_agence
     *            L'ID de l'agence remote
     * @param int $id_guichet
     *            L'ID du guichet de l'agence local ayant encaissé le montant du dépôt
     * @param int $id_cpte
     *            L"ID du compte bénéficiaire remote (table ad_cpt dans base remote)
     * @param float $montant
     *            Montant du dépôt
     * @param int $type_depot
     *            Type de dépôt (1 si dépôt express, 2 si dépôt normal)
     * @param array $CHANGE
     *            : Optionnel, les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfrice fait sur le taux)
     *            
     * @return ErrorObj Les erreurs possibles sont <UL>
     *         <LI> Celles renvoyées par {@link #CheckDepot checkDepot} </LI>
     *         <LI> Celles renvoyées par {@link #passageEcrituresComptablesAuto passageEcrituresComptablesAuto} </LI> </UL>
     *        
     *        
     */
    public static function depotCpteRemoteMultidevisesRevert(&$dbc, $id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL, $errMsg = NULL)
    { 
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $global_multidevise;
        global $global_remote_monnaie, $global_monnaie;
        global $global_remote_id_client;
        
        // Le login configuré pour loggé les transactions distantes
        $login_remote = 'distant';
        
        
        // renverse le montant
        if($montant > 0) {
            $montant = 0 - $montant;
        }       
        
        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);
        $EpargneObj = new Epargne($dbc, $id_agence);
        $CompteObj = new Compte($dbc, $id_agence);
        $ComptaObj = new Compta($dbc, $id_agence);
        $DeviseObj = new Devise($dbc, $id_agence);
        
        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /*
         * if ($InfoCpte["etat_cpte"] == 4) { $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte"; $result = $db->query($sql); if (DB::isError($result)) { $dbHandler->closeConnection(false); signalErreur(__FILE__,__LINE__,__FUNCTION__); } }
         */
        
        // Verification des taux d'échange dans les 2 agences :       
        if (isset($CHANGE)) {
            $erreur = Divers::checkTauxDeviseForOperationDeplacer($CHANGE['devise'], $InfoCpte['devise'], $dbc);
        
            if($erreur->errCode != NO_ERR) {
                return $erreur;
            }
        }
        
        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array(); // copmte comptable produit epargne
        $cptes_substitue["int"] = array(); // compte client

        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison remote
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        } else {
            // [DEBIT] Compte liason Agence Local
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        }
        
        // [CREDIT] Compte Comptable Produit Epargne Remote
        $cptes_substitue["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
        
        if ($cptes_substitue["cpta"]["credit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        // [CREDIT] Compte client remote
        $cptes_substitue["int"]["credit"] = $id_cpte;
        
        // Arrondi du montant si paiement au guichet
        $montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);
        
        // Deal with multi agence multi devise transactions
        if (isset($CHANGE)) {
            $montant_cv = 0 - $CHANGE["cv"];            
            $myErr = $DeviseObj->changeDepotRemoteRevert($CHANGE['devise'], $InfoCpte['devise'], $montant_cv, $montant, 160, $cptes_substitue, $comptable, $CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE['taux'], true, $infos_sup);
            
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        } else {
            // 160 : Dépôt espèces
            $myErr = $CompteObj->passageEcrituresComptablesAuto(160, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);
            
            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        
        // --------------- frais d'opérations ---------------------------------------------------
        // en cas de frais d'opérations, débit compte guichet et crédit compte interne de produit
        
        /*
         * if ($InfoProduit["frais_depot_cpt"] > 0 ) { unset($cptes_substitue["cpta"]["credit"]); unset($cptes_substitue["int"]["credit"]); $operation_frais = 150; $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte); if ($cptes_substitue["cpta"]["debit"] == NULL) { return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne")); } $cptes_substitue["int"]["debit"] = $id_cpte; // Passage des écritures comptables if ($InfoCpte['devise'] == $global_remote_monnaie) { //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte); $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte); } else { //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte); $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte); } if ($myErr->errCode != NO_ERR) { //$dbHandler->closeConnection(false); return $myErr; } }
         */
        
        /* Eventuels frais de virement en cas de dépôt par lot pour les virement de salaires ------------- */
        
        /*
         * PAS DE FRAIS DE VIREMENT EN REMOTE ?
         *
         * if ($frais_virement != NULL )
         * {
         * unset($cptes_substitue["cpta"]["credit"]);
         * unset($cptes_substitue["int"]["credit"]);
         *
         * //Compte comptable associé au produit d'épargne du compte
         * $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
         * if ($cptes_substitue["cpta"]["debit"] == NULL) {
         * $dbHandler->closeConnection(false);
         * return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
         * }
         * $cptes_substitue["int"]["debit"] = $id_cpte;
         *
         * //Passage des écritures comptables: les frais de virement sont dans la devise de référence
         * if ($InfoCpte['devise']==$global_monnaie) {
         * $myErr = passageEcrituresComptablesAuto(151, $frais_virement, $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
         * } else {
         * $myErr = effectueChangePrivate($InfoCpte['devise'],$global_monnaie, $frais_virement, 151, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
         * }
         *
         * if ($myErr->errCode != NO_ERR) {
         * $dbHandler->closeConnection(false);
         * return $myErr;
         * }
         * }
         */

         $fonction = 93; // Dépôt en déplacé multidevises
       
        $infos_his = 'agc=' . $global_id_agence . ' - login=' . $global_nom_login . ' - error : ' . $errMsg;        
        $login = $login_remote;
        
        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], $infos_his, $login, date("r"), $comptable, $DATA_HIS_EXT);
        
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];
        
        // Unset used objects
        unset($HistoriqueObj);
        unset($DeviseObj);
        unset($EpargneObj);
        unset($CompteObj);
        unset($ComptaObj);
        
        return new ErrorObj(NO_ERR, array(
            'id_his' => $id_his,
            'id_ecriture' => $id_ecriture
        ));
    }
    
    
    
    /**
     *
     * Enregistre une opération de <B>dépôt en espèce</B> sur un compte d'épargne en local, en mode multidevises
     *
     * @param int $id_agence_remote L'ID de l'agence remote
     * @param int $id_guichet L'ID du guichet ayant encaissé le montant du dépôt
     * @param float $montant Montant du dépôt
     * @param int $type_depot Type de dépôt (1 si dépôt express, 2 si dépôt normal)
     * @param array $CHANGE : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfrice fait sur le taux)
     *
     * @return ErrorObj Les erreurs possibles sont <UL>
     *   <LI> Celles renvoyées par {@link #CheckDepot checkDepot} </LI>
     *   <LI> Celles renvoyées par {@link #passageEcrituresComptablesAuto passageEcrituresComptablesAuto} </LI> </UL>
     *
     *
     */
    public static function depoCpteLocalMultidevises($id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL) {
        global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
        global $dbHandler, $global_multidevise;
        global $global_monnaie, $global_remote_monnaie;
        
        //pour pouvoir commit ou rollback toute la procédure
        $db = $dbHandler->openConnection();

        if ($DATA != NULL && $type_depot != 1) {
            $DATA_HIS_EXT = creationHistoriqueExterieur($DATA);
        } else {
            $DATA_HIS_EXT = NULL;
        }

        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /* if ($InfoCpte["etat_cpte"] == 4) {
          $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
          $result = $db->query($sql);
          if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
          }
          } */

        //Check que le dépôt est possible sur le compte

        /*
          $erreur = CheckDepot($InfoCpte, $montant);

          if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
          }
         */

        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        if ($type_depot == 11) {

            $erreur = CheckRetrait($InfoCpte, $InfoProduit, $montant, 1, NULL);
            if ($erreur->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);
              return $erreur;
            }

            //débit d'un guichet par le crédit d'un client en deplacé
            $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
            $cptes_substitue ["int"] ["debit"] = $id_cpte;
            
            $text_opt_id = Divers::getLocalTextId("Transfert entre comptes d'epargne autre agence");

            // Transfert entre comptes d'epargne
            $operation = 120;

            $fonction = 77; // Transfert compte API
        } else {
            //débit d'un guichet par le crédit d'un client en deplacé
            $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
            
            /* Arrondi du montant si paiement au guichet */
            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']);
            
            $text_opt_id = Divers::getLocalTextId("Dépôt en déplacé");
            
            // 160 : "Dépôt espèces"
            $operation = 160;

            $fonction = 93; // Dépôt en déplacé multi devises
        }

        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison locale
            $cpte_liaison = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        } else {
            // Recuperation du compte de liaison qui va etre credité	
            $cpte_liaison = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        }

        if ($cpte_liaison == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte liaison comptable de l'agence en déplacée"));
        }

        $cptes_substitue["cpta"]["credit"] = $cpte_liaison;

        if ($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }
        
        if(isset($CHANGE)) 
        {
            //verification si les commissions doivent etre prelevés sur l'agence locale
            $toPreleveCommissionsDansAgenceLocal = getWherePerceptionCommissionsMultiAgence();

            // Traitement si les commissions doivent etre prelevés dans l'agence locale
            if($toPreleveCommissionsDansAgenceLocal)
            {
                $myErr = changeDepotLocalAvecCommissions($CHANGE['devise'], $InfoCpte['devise'], $CHANGE['cv'], $montant, $operation, $cptes_substitue, $comptable, $CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE['taux'], true, $infos_sup, false, true);

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

        } else {
            $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        $infos_his = "agc=" . $id_agence . " - client=" . $global_id_client;

        $myErr = ajout_historique($fonction, NULL, $infos_his, $global_nom_login, date("r"), $comptable, $DATA_HIS_EXT);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        $id_his = $myErr->param;
        $id_ecriture = Divers::getIDEcritureByIDHis($id_his);
        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }

    public static function depotCpteRemoteAgent(&$dbc, $id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL) {
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $global_multidevise;
        global $global_remote_monnaie, $global_monnaie;
        global $global_remote_id_client;

        // Le login configuré pour loggé les transactions distantes
        $login_remote = 'distant';

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);
        $EpargneObj = new Epargne($dbc, $id_agence);
        $CompteObj = new Compte($dbc, $id_agence);
        $ComptaObj = new Compta($dbc, $id_agence);
        $DeviseObj = new Devise($dbc, $id_agence);
        $ClientObj = new Client($dbc, $id_agence);
        $agent_data = getDatasLogin($global_nom_login);

        if ($DATA != NULL) {
            $DATA_HIS_EXT = $HistoriqueObj->creationHistoriqueExterieur($DATA);
        } else {
            $DATA_HIS_EXT = NULL;
        }

        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /* if ($InfoCpte["etat_cpte"] == 4) {
          $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
          $result = $db->query($sql);
          if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
          }
          } */

        //Check que le dépôt est possible sur le compte
        $erreur = $EpargneObj->CheckDepot($InfoCpte, $montant);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }

        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array(); // copmte comptable produit epargne
        $cptes_substitue["int"] = array(); // compte client

        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison remote
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        } else {
            // [DEBIT] Compte liaison Agence Local
            $cptes_substitue["cpta"]["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        }
        $cmpte_temp = $cptes_substitue["cpta"]["debit"];

        // [CREDIT] Compte Comptable Produit Epargne Remote
        $cptes_substitue["cpta"]["credit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

        if ($cptes_substitue["cpta"]["credit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        // [CREDIT] Compte client remote
        $cptes_substitue["int"]["credit"] = $id_cpte;

        // Arrondi du montant si paiement au guichet
        $montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte['devise']);

        /* Arrondi du montant si paiement au guichet */
        /*
          $critere = array();
          $critere['num_cpte_comptable'] = $CompteObj->getCompteCptaGui($id_guichet); // recup un compte guichet
          $cpte_gui = $ComptaObj->getComptesComptables($critere);
         */

        // @todo :arrondie a partir devise
        // recup le montant dans la devise remote
        //$montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $cpte_gui['devise']);

        // 160 : Dépôt espèces
        $operation = 160;

        // Recuperation de la libelle du depot
        $text_opt_id = Divers::getRemoteTextId($dbc, "Dépôt en déplacé");

        if ($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }

        $myErr = $CompteObj->passageEcrituresComptablesAuto(621, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        $cpte_inst_depot_remote = $CompteObj->getCpteComptaInstRemote();
        $choix_depot_comm = getParamCommissionInsti();
        if(empty($cpte_inst_depot_remote)) {
            return new ErrorObj(ERR_CPTE_COMM_INST, _("compte comptable associé au produit d'épargne"));
        }

        $cpte_ins = $cpte_inst_depot_remote[0]['cpte_compta_comm_depot'];
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2')?$agent_data['cpte_base_agent']:$agent_data['cpte_flotte_agent'];

        //choix depot est supporté par l'institution
        if(strcmp($choix_depot_comm['choix_depot_comm'], '1') == 0 ){
            $cptes_substitue["cpta"]["debit"] = $cpte_ins;
            $cptes_substitue["cpta"]["credit"] = $cmpte_temp;
            $cptes_substitue["int"]["credit"] = null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

            $myErr = $CompteObj->passageEcrituresComptablesAuto(628, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }else{
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            $cptes_substitue["cpta"]["credit"] = $cmpte_temp;
            $cptes_substitue["int"]["debit"] = $id_cpte;
            $cptes_substitue["int"]["credit"] = null;
            $mnt_comm_total = $DATA['commission']['agent'] + $DATA['commission']['institution'];

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($mnt_comm_total, 0, $cpte_gui['devise'] );

            $myErr = $CompteObj->passageEcrituresComptablesAuto(628, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }

//            $critere = array();
//            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
//            $cpte_gui = getComptesComptables($critere);
//            $montant = arrondiMonnaie($DATA['commission']['institution'], 0, $cpte_gui['devise'] );
//
//            $myErr = $CompteObj->passageEcrituresComptablesAuto(623, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);
//
//            if ($myErr->errCode != NO_ERR) {
//                return $myErr;
//            }
        }
//
        //--------------- frais d'opérations ---------------------------------------------------
        //en cas de frais d'opérations, débit compte guichet et crédit compte interne de produit

        if ($InfoProduit["frais_depot_cpt"] > 0) {
            unset($cptes_substitue["cpta"]["credit"]);
            unset($cptes_substitue["int"]["credit"]);

            $operation_frais = 150;

            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

            if ($cptes_substitue["cpta"]["debit"] == NULL) {
                return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
            }

            $cptes_substitue["int"]["debit"] = $id_cpte;

            // Passage des écritures comptables
            if ($InfoCpte['devise'] == $global_remote_monnaie) {
                //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
                $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue, NULL, NULL, $id_cpte);
            } else {
                //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
                $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable, NULL, NULL, $id_cpte);
            }
            if ($myErr->errCode != NO_ERR) {
                //$dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        // en cas de frais SMS transactionnel
        $listeTypeOpt = $EpargneObj->getListeTypeOptDepPourPreleveFraisSMS();

        if (in_array($operation, $listeTypeOpt)) {
            $SMSTransactionnel = $EpargneObj->getTarificationDatas();
            $clientSMS = $ClientObj->checkIfClientAbonnerSMS($global_id_client);

            if (!empty($SMSTransactionnel) && $clientSMS == true) {
                unset($cptes_substitue["cpta"]["credit"]);
                unset($cptes_substitue["int"]["credit"]);

                $operation_frais = 188;

                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }

                $cptes_substitue["int"]["debit"] = $id_cpte;

                // Passage des écritures comptables
                if ($InfoCpte['devise'] == $global_remote_monnaie) {
                    //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
                    $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_frais, $SMSTransactionnel["valeur"], $comptable, $cptes_substitue, NULL, NULL, $id_cpte);
                } else {
                    //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
                    $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $SMSTransactionnel["valeur"], $operation_frais, $cptes_substitue, $comptable, NULL, NULL, $id_cpte);
                }
                if ($myErr->errCode != NO_ERR) {
                    //$dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
        }
//
        //  Debut process == >> Commmission sur operation en deplace
        if($InfoCpte['appl_comm_deplace'] == 't') {
            if ($DATA['commission_op_deplace'] > 0) {

                unset($cptes_substitue["cpta"]["credit"]);
                unset($cptes_substitue["int"]["credit"]);
                $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
                $operation_od_commission = 156;
                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }

                $cptes_substitue["int"]["debit"] = $id_cpte;
                // Passage des écritures comptables
                if ($InfoCpte['devise'] == $global_remote_monnaie) {
                    //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
                    $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_od_commission, $DATA['commission_op_deplace'], $comptable, $cptes_substitue, NULL, NULL, $id_cpte);

                } else {
                    //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
                    $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $DATA['commission_op_deplace'], $operation_od_commission, $cptes_substitue, $comptable, NULL, NULL, $id_cpte);

                }
                if ($myErr->errCode != NO_ERR) {
                    //$dbHandler->closeConnection(false);
                    return $myErr;
                }//exit();
            }
        }
//
//        //  Fin Process == >> Commission sur operation en deplace
//
//        /* Eventuels frais de virement en cas de dépôt par lot pour les virement de salaires ------------- */
//
//        /* PAS DE FRAIS DE VIREMENT EN REMOTE ?
//         *
//          if ($frais_virement != NULL )
//          {
//          unset($cptes_substitue["cpta"]["credit"]);
//          unset($cptes_substitue["int"]["credit"]);
//
//          //Compte comptable associé au produit d'épargne du compte
//          $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
//          if ($cptes_substitue["cpta"]["debit"] == NULL) {
//          $dbHandler->closeConnection(false);
//          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
//          }
//          $cptes_substitue["int"]["debit"] = $id_cpte;
//
//          //Passage des écritures comptables: les frais de virement sont dans la devise de référence
//          if ($InfoCpte['devise']==$global_monnaie) {
//          $myErr = passageEcrituresComptablesAuto(151, $frais_virement, $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
//          } else {
//          $myErr = effectueChangePrivate($InfoCpte['devise'],$global_monnaie,  $frais_virement, 151, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
//          }
//
//          if ($myErr->errCode != NO_ERR) {
//          $dbHandler->closeConnection(false);
//          return $myErr;
//          }
//          }
//         */
//
//        if ($type_depot == 1) {//dépôt express
//            $fonction = 86;
//        } else if ($type_depot == NULL) {//dépôt normal
//            $fonction = 75;
//        } else if ($type_depot == 158) {//dépôt par lot
//            $fonction = 158;
//        } else if ($type_depot == 159) {//dépôt par lot via fichier
//            $fonction = 159;
//        }
//

        $fonction = 93; // Dépôt en déplacé
        $login = $id_agence . ' - ' . $global_nom_login;

        $infos_his = 'agc=' . $global_id_agence . ' - login=' . $global_nom_login;
        $login = $login_remote;
//
        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte["id_titulaire"], $infos_his, $login, date("r"), $comptable, $DATA_HIS_EXT);
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
        
        $id_his = $myErr->param['id_his'];
        $id_ecriture = $myErr->param['id_ecriture'];

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));

        /*
          $id_his = $myErr->param;
          return new ErrorObj(NO_ERR, array('id'=>$id_his));
         */
    }

    public static function depoCpteLocalAgent($id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL) {
        global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
        global $dbHandler, $global_multidevise;
        global $global_monnaie, $global_remote_monnaie;

        //pour pouvoir commit ou rollback toute la procédure
        $db = $dbHandler->openConnection();

        if ($DATA != NULL && $type_depot != 1) {
            $DATA_HIS_EXT = creationHistoriqueExterieur($DATA);
        } else {
            $DATA_HIS_EXT = NULL;
        }

        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /* if ($InfoCpte["etat_cpte"] == 4) {
          $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
          $result = $db->query($sql);
          if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
          }
          } */

        //Check que le dépôt est possible sur le compte

        /*
          $erreur = CheckDepot($InfoCpte, $montant);

          if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
          }
         */

        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        $choix_depot_comm = getParamCommissionInsti();
        $agent_data = getDatasLogin($global_nom_login);
        $cpte_ins = $choix_depot_comm['cpte_compta_comm_depot'];
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2')?$agent_data['cpte_base_agent']:$agent_data['cpte_flotte_agent'];

        $critere = array();
        $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
        $cpte_gui = getComptesComptables($critere);
        $montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']);

        // Recuperation du compte de liason qui va etre credité
        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison locale
            $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        } else {
            $cptes_substitue["cpta"]["credit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        }


        if ($cptes_substitue["cpta"]["credit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte liaison comptable de l'agence en déplacée"));
        }

        $text_opt_id = Divers::getLocalTextId("Dépôt en déplacé");

        if ($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }

        $cmpte_temp = $cptes_substitue["cpta"]["credit"];
        $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
        //$cptes_substitue["int"]["debit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
        $cptes_substitue["cpta"]["credit"] = $cmpte_temp;

        // 160 : "Dépôt espèces"
        $operation = 621;
        $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        $info_impot = getInfoImpot();
        $cpte_intermediare = getCpteCommIntermediaire();

        if(strcmp($choix_depot_comm['choix_depot_comm'], '1') == 0 ){
            if($info_impot['appl_impot_agent'] == 't'){
                $montant_comm_impot = ($info_impot['prc_import'] / 100) * $DATA['commission']['agent'];
                $cptes_substitue["cpta"]["debit"] = $cmpte_temp;
                $cptes_substitue["cpta"]["credit"] = $info_impot['cpte_impot'];
                $cptes_substitue["int"]["credit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = arrondiMonnaie($montant_comm_impot, 0, $cpte_gui['devise'] );

                $myErr = passageEcrituresComptablesAuto(629, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                $montant_comm_agent = arrondiMonnaie($DATA['commission']['agent'] - $montant_comm_impot, 0);
                $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
                $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = arrondiMonnaie($montant_comm_agent, 0, $cpte_gui['devise'] );

                $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }else{
                $cptes_substitue["cpta"]["debit"] = $cmpte_temp;
                $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
                $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

                $myErr = passageEcrituresComptablesAuto(622, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }

        }

        else{
            $cptes_substitue["cpta"]["debit"] = $cmpte_temp;
            if($info_impot['appl_impot_agent'] == 't'){
                //commission impot
                $montant_comm_impot = $info_impot['prc_import'] / 100;
                $mnt_comm_agent = arrondiMonnaie($DATA['commission']['agent'] * $montant_comm_impot, 0);

                $cptes_substitue["cpta"]["credit"] = $info_impot['cpte_impot'];
                $cptes_substitue["int"]["credit"] = null;
                $cptes_substitue["int"]["debit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = arrondiMonnaie($mnt_comm_agent, 0, $cpte_gui['devise'] );

                $myErr = passageEcrituresComptablesAuto(629, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }

                //commission agent
                $mnt_comm_agent = $DATA['commission']['agent'] - $mnt_comm_agent;
                $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
                $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
                $cptes_substitue["int"]["debit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = arrondiMonnaie($mnt_comm_agent, 0, $cpte_gui['devise'] );

                $operation = 622;
                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }else{

                $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
                $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;
                $cptes_substitue["int"]["debit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

                $operation = 622;
                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }


            if($info_impot['appl_impot_institution'] == 't'){
                //commission impot
                $montant_comm_impot = $info_impot['prc_import'] / 100;
                $mnt_impot_institution = arrondiMonnaie($DATA['commission']['institution'] * $montant_comm_impot, 0);

                $cptes_substitue["cpta"]["credit"] = $info_impot['cpte_impot'];
                $cptes_substitue["int"]["credit"] = null;
                $cptes_substitue["int"]["debit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = arrondiMonnaie($mnt_impot_institution, 0, $cpte_gui['devise'] );

                $myErr = passageEcrituresComptablesAuto(629, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
                $mnt_comm_inst = $DATA['commission']['institution'] - $mnt_impot_institution;
                $cptes_substitue["cpta"]["credit"] = $cpte_ins;
                $cptes_substitue["int"]["credit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = arrondiMonnaie($mnt_comm_inst, 0, $cpte_gui['devise'] );

                $operation = 623;
                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }


            }
            else{
                $cptes_substitue["cpta"]["credit"] = $cpte_ins;
                $cptes_substitue["int"]["credit"] = null;
                $cptes_substitue["int"]["debit"] = null;

                $critere = array();
                $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
                $cpte_gui = getComptesComptables($critere);
                $montant = arrondiMonnaie($DATA['commission']['institution'], 0, $cpte_gui['devise'] );

                $operation = 622;
                $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte);

                if ($myErr->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $myErr;
                }
            }
        }
        /*
          if ($type_depot == 1) {//dépôt express
          $fonction = 86;
          } else if ($type_depot == NULL) {//dépôt normal
          $fonction = 75;
          } else if ($type_depot == 158) {//dépôt par lot
          $fonction = 158;
          } else if ($type_depot == 159) {//dépôt par lot via fichier
          $fonction = 159;
          }
         */

        $fonction = 775; // Dépôt en déplacé

        $infos_his = "agc=" . $id_agence . " - client=" . $global_id_client;

        $myErr = ajout_historique($fonction, NULL, $infos_his, $global_nom_login, date("r"), $comptable, $DATA_HIS_EXT);

        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        $id_his = $myErr->param;
        $id_ecriture = Divers::getIDEcritureByIDHis($id_his);
        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }

    public static function depotCpteRemoteMultidevisesAgent(&$dbc, $id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL) {
        global $global_id_client, $global_nom_login, $global_id_guichet, $global_id_agence;
        global $global_multidevise;
        global $global_remote_monnaie, $global_monnaie;
        global $global_remote_id_client;

        // Le login configuré pour loggé les transactions distantes
        $login_remote = 'distant';

        // Init class
        $HistoriqueObj = new Historique($dbc, $id_agence);
        $EpargneObj = new Epargne($dbc, $id_agence);
        $CompteObj = new Compte($dbc, $id_agence);
        $ComptaObj = new Compta($dbc, $id_agence);
        $DeviseObj = new Devise($dbc, $id_agence);
        $agent_data = getDatasLogin($global_nom_login);

        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /*
         * if ($InfoCpte["etat_cpte"] == 4) { $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte"; $result = $db->query($sql); if (DB::isError($result)) { $dbHandler->closeConnection(false); signalErreur(__FILE__,__LINE__,__FUNCTION__); } }
         */

        // Verifcation des taux d'échange dans les 2 agences :
        if (isset($CHANGE)) {
            $erreur = Divers::checkTauxDeviseForOperationDeplacer($CHANGE['devise'], $InfoCpte['devise'], $dbc);

            if($erreur->errCode != NO_ERR) {
                return $erreur;
            }

            $CHANGE['cv'] -= arrondiMonnaiePrecision($DATA['commission_ope_deplace'],$CHANGE['devise']);
            $montant -= arrondiMonnaiePrecision($DATA['commission_ope_deplace_cv'],$InfoCpte['devise']);

        }

        // Check que le dépôt est possible sur le compte
        $erreur = $EpargneObj->CheckDepot($InfoCpte, $montant);

        if ($erreur->errCode != NO_ERR) {
            return $erreur;
        }

        if ($DATA != NULL) {
            $DATA_HIS_EXT = $HistoriqueObj->creationHistoriqueExterieur($DATA);
        } else {
            $DATA_HIS_EXT = NULL;
        }

        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue ["cpta"] = array(); // copmte comptable produit epargne
        $cptes_substitue ["int"] = array(); // compte client

        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison remote
            $cptes_substitue["cpta"] ["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        } else {
            // [DEBIT] Compte liason Agence Local
            $cptes_substitue["cpta"] ["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        }

        $cmpte_temp = $cptes_substitue["cpta"]["debit"];

        // [CREDIT] Compte Comptable Produit Epargne Remote
        $cptes_substitue["cpta"] ["credit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

        if ($cptes_substitue["cpta"] ["credit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
        }
        // [CREDIT] Compte client remote
        $cptes_substitue ["int"] ["credit"] = $id_cpte;

        if ($type_depot == 11) {
            // Transfert entre comptes d'epargne
            $operation = 120;
            $fonction = 77; // Transfert compte API
        } else {
            // Transfert entre comptes d'epargne
            $operation = 160;
            $fonction = 93; // Dépôt en déplacé multi-devise

            // Arrondi du montant si paiement au guichet
            $montant = Divers::arrondiMonnaie($dbc, $id_agence, $montant, 0, $InfoCpte ['devise']);
        }
        // Deal with multi agence multi devise transactions
        if (isset($CHANGE)) {
            $myErr = $DeviseObj->changeDepotRemote($CHANGE ['devise'], $InfoCpte ['devise'], $CHANGE['cv'] , $montant, $operation, $cptes_substitue, $comptable, $CHANGE ['dest_reste'], $CHANGE ['comm_nette'], $CHANGE ['taux'], true, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        } else {
            // 160 : Dépôt espèces
            $myErr = $CompteObj->passageEcrituresComptablesAuto(621, $montant, $comptable, $cptes_substitue, $InfoCpte ['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        $cpte_inst_depot_remote = $CompteObj->getCpteComptaInstRemote();
        $choix_depot_comm = getParamCommissionInsti();
        if(empty($cpte_inst_depot_remote)) {
            return new ErrorObj(ERR_CPTE_COMM_INST, _("compte comptable associé au produit d'épargne"));
        }

        $cpte_ins = $cpte_inst_depot_remote[0]['cpte_compta_comm_depot'];
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2')?$agent_data['cpte_base_agent']:$agent_data['cpte_flotte_agent'];

        //choix depot est supporté par l'institution
        if(strcmp($choix_depot_comm['choix_depot_comm'], '1') == 0 ){
            $cptes_substitue["cpta"]["debit"] = $cpte_ins;
            $cptes_substitue["cpta"]["credit"] = $cmpte_temp;
            $cptes_substitue["int"]["credit"] = null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

            $myErr = $CompteObj->passageEcrituresComptablesAuto(622, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }else{
            $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);
            $cptes_substitue["cpta"]["credit"] = $cmpte_temp;
            $cptes_substitue["int"]["debit"] = $id_cpte;
            $cptes_substitue["int"]["credit"] = null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

            $myErr = $CompteObj->passageEcrituresComptablesAuto(622, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($DATA['commission']['institution'], 0, $cpte_gui['devise'] );

            $myErr = $CompteObj->passageEcrituresComptablesAuto(623, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                return $myErr;
            }
        }
        //TODO : inserer les mouvements + les operations comptables + et reduire le montant passer en haut sur la fonction changeDepotRemote.
        if ($DATA['commission_ope_deplace'] > 0 ){
            unset($cptes_substitue["cpta"]["credit"]);
            unset($cptes_substitue["int"]["credit"]);
            if (isset($CHANGE)){

                if(isCompensationSiege() && isMultiAgenceSiege()) {
                    // Utilise le compte de liaison remote
                    $cptes_substitue["cpta"] ["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
                } else {
                    // [DEBIT] Compte liason Agence Local
                    $cptes_substitue["cpta"] ["debit"] = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
                }
                $operation = 156;

                $myErr = $DeviseObj->changeDepotRemote($CHANGE ['devise'], $InfoCpte ['devise'], $DATA['commission_ope_deplace'], $DATA['commission_ope_deplace_cv'], $operation, $cptes_substitue, $comptable, $CHANGE ['dest_reste'], null, null, true, $infos_sup);

                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }
            }
            else{ // perception des commission en deplace operation : 156

                unset($cptes_substitue["cpta"]["credit"]);
                unset($cptes_substitue["int"]["credit"]);
                $cptes_substitue["cpta"] ["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte);

                $cptes_substitue ["int"] ["debit"] = $id_cpte;

                if ($cptes_substitue["cpta"]["debit"] == NULL) {
                    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
                }
                $operation = 156;

                $myErr = $CompteObj->passageEcrituresComptablesAuto($operation, $DATA['commission_ope_deplace'], $comptable, $cptes_substitue, $InfoCpte ['devise'], NULL, $id_cpte, $infos_sup);

                if ($myErr->errCode != NO_ERR) {
                    return $myErr;
                }


            }
        }


        // --------------- frais d'opérations ---------------------------------------------------
        // en cas de frais d'opérations, débit compte guichet et crédit compte interne de produit

        /*
         * if ($InfoProduit["frais_depot_cpt"] > 0 ) { unset($cptes_substitue["cpta"]["credit"]); unset($cptes_substitue["int"]["credit"]); $operation_frais = 150; $cptes_substitue["cpta"]["debit"] = $CompteObj->getCompteCptaProdEp($id_cpte); if ($cptes_substitue["cpta"]["debit"] == NULL) { return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne")); } $cptes_substitue["int"]["debit"] = $id_cpte; // Passage des écritures comptables if ($InfoCpte['devise'] == $global_remote_monnaie) { //$myErr = passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte); $myErr = $CompteObj->passageEcrituresComptablesAuto($operation_frais, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte); } else { //$myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte); $myErr = $DeviseObj->effectueChangePrivate($InfoCpte['devise'], $global_remote_monnaie, $InfoProduit['frais_depot_cpt'], $operation_frais, $cptes_substitue, $comptable,NULL,NULL,$id_cpte); } if ($myErr->errCode != NO_ERR) { //$dbHandler->closeConnection(false); return $myErr; } }
         */


        /* Eventuels frais de virement en cas de dépôt par lot pour les virement de salaires ------------- */

        /* PAS DE FRAIS DE VIREMENT EN REMOTE ?
         *
          if ($frais_virement != NULL )
          {
          unset($cptes_substitue["cpta"]["credit"]);
          unset($cptes_substitue["int"]["credit"]);

          //Compte comptable associé au produit d'épargne du compte
          $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
          if ($cptes_substitue["cpta"]["debit"] == NULL) {
          $dbHandler->closeConnection(false);
          return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
          }
          $cptes_substitue["int"]["debit"] = $id_cpte;

          //Passage des écritures comptables: les frais de virement sont dans la devise de référence
          if ($InfoCpte['devise']==$global_monnaie) {
          $myErr = passageEcrituresComptablesAuto(151, $frais_virement, $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
          } else {
          $myErr = effectueChangePrivate($InfoCpte['devise'],$global_monnaie,  $frais_virement, 151, $cptes_substitue, $comptable,NULL,NULL,$id_cpte);
          }

          if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
          }
          }
         */

        //$fonction = 93; // Dépôt en déplacé
        $infos_his = 'agc=' . $global_id_agence . ' - login=' . $global_nom_login;
        $login = $login_remote;

        $myErr = $HistoriqueObj->ajoutHistorique($fonction, $InfoCpte ["id_titulaire"], $infos_his, $login, date("r"), $comptable, $DATA_HIS_EXT);

        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }

        $id_his = $myErr->param ['id_his'];
        $id_ecriture = $myErr->param ['id_ecriture'];

        // Unset used objects
        unset($HistoriqueObj);
        unset($DeviseObj);
        unset($EpargneObj);
        unset($CompteObj);
        unset($ComptaObj);

        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }
    public static function depoCpteLocalMultidevisesAgent($id_agence, $id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA = NULL, $type_depot = NULL, $CHANGE = NULL, $frais_virement = NULL, $infos_sup = NULL) {
        global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
        global $dbHandler, $global_multidevise;
        global $global_monnaie, $global_remote_monnaie;

        //pour pouvoir commit ou rollback toute la procédure
        $db = $dbHandler->openConnection();

        if ($DATA != NULL && $type_depot != 1) {
            $DATA_HIS_EXT = creationHistoriqueExterieur($DATA);
        } else {
            $DATA_HIS_EXT = NULL;
        }

        // Si le compte était dormant, le faire passer à l'état ouvert
        // FIXME : On devrait pouvoir supprimer ceci
        /* if ($InfoCpte["etat_cpte"] == 4) {
          $sql = "UPDATE ad_cpt SET etat_cpte = 1 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte";
          $result = $db->query($sql);
          if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
          }
          } */

        //Check que le dépôt est possible sur le compte

        /*
          $erreur = CheckDepot($InfoCpte, $montant);

          if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
          }
         */

        // Passage des écritures comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();

        $choix_depot_comm = getParamCommissionInsti();
        $agent_data = getDatasLogin($global_nom_login);
        $cpte_ins = $choix_depot_comm['cpte_compta_comm_depot'];
        $cpte_agent = ($agent_data['cpte_comm_agent'] == '2')?$agent_data['cpte_base_agent']:$agent_data['cpte_flotte_agent'];

        if ($type_depot == 11) {
            $erreur = CheckRetrait($InfoCpte, $InfoProduit, $montant, 1, NULL);
            if ($erreur->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $erreur;
            }

            //débit d'un guichet par le crédit d'un client en deplacé
            $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
            $cptes_substitue ["int"] ["debit"] = $id_cpte;

            $text_opt_id = Divers::getLocalTextId("Transfert entre comptes d'epargne autre agence");

            // Transfert entre comptes d'epargne
            $operation = 120;

            $fonction = 77; // Transfert compte API
        } else {
            //débit d'un guichet par le crédit d'un client en deplacé
            $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);

            /* Arrondi du montant si paiement au guichet */
            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($montant, 0, $cpte_gui['devise']);

            $text_opt_id = Divers::getLocalTextId("Dépôt en déplacé");

            // 160 : "Dépôt espèces"
            $operation = 160;

            $fonction = 93; // Dépôt en déplacé multi devises
        }

        if(isCompensationSiege() && isMultiAgenceSiege()) {
            // Utilise le compte de liaison locale
            $cpte_liaison = AgenceRemote::getRemoteAgenceCompteLiaison($global_id_agence);
        } else {
            // Recuperation du compte de liaison qui va etre credité
            $cpte_liaison = AgenceRemote::getRemoteAgenceCompteLiaison($id_agence);
        }

        if ($cpte_liaison == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte liaison comptable de l'agence en déplacée"));
        }

        $cptes_substitue["cpta"]["credit"] = $cpte_liaison;

        if ($text_opt_id > 0) {
            $infos_sup["autre_libel_ope"] = $text_opt_id;
        }


        if(isset($CHANGE))
        {
            //verification si les commissions doivent etre prelevés sur l'agence locale
            $toPreleveCommissionsDansAgenceLocal = getWherePerceptionCommissionsMultiAgence();

            // Traitement si les commissions doivent etre prelevés dans l'agence locale
            if($toPreleveCommissionsDansAgenceLocal)
            {
                $myErr = changeDepotLocalAvecCommissions($CHANGE['devise'], $InfoCpte['devise'], $CHANGE['cv'], $montant, $operation, $cptes_substitue, $comptable, $CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE['taux'], true, $infos_sup, false, true);

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

        } else {
            $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        if(strcmp($choix_depot_comm['choix_depot_comm'], '1') == 0 ){
            $cptes_substitue["cpta"]["debit"] = $cpte_liaison;
            $cptes_substitue["cpta"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?getCompteCptaProdEp($cpte_agent):$cpte_agent;
            $cptes_substitue["int"]["credit"] = ($agent_data['cpte_comm_agent'] == '2')?$cpte_agent:null;

            $critere = array();
            $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
            $cpte_gui = getComptesComptables($critere);
            $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

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
            $montant = arrondiMonnaie($DATA['commission']['agent'], 0, $cpte_gui['devise'] );

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
            $montant = arrondiMonnaie($DATA['commission']['institution'], 0, $cpte_gui['devise'] );

            $operation = 623;
            $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'], NULL, $id_cpte, $infos_sup);

            if ($myErr->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $myErr;
            }
        }

        $infos_his = "agc=" . $id_agence . " - client=" . $global_id_client;

        $myErr = ajout_historique($fonction, NULL, $infos_his, $global_nom_login, date("r"), $comptable, $DATA_HIS_EXT);
        if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
        }

        $id_his = $myErr->param;
        $id_ecriture = Divers::getIDEcritureByIDHis($id_his);
        return new ErrorObj(NO_ERR, array('id_his' => $id_his, 'id_ecriture' => $id_ecriture));
    }

    /** Getters & Setters **/
    
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

    public function getData() {
        return $this->_data;
    }

    public function setData($value) {
        $this->_data = $value;
    }

    public function getMontant() {
        return $this->_montant;
    }

    public function setMontant($value) {
        $this->_montant = $value;
    }

    public function getTypeDepot() {
        return $this->_type_depot;
    }

    public function setTypeDepot($value) {
        $this->_type_depot = $value;
    }

    public function getChange() {
        return $this->_change;
    }

    public function setChange($value) {
        $this->_change = $value;
    }

    public function getFraisVirement() {
        return $this->_frais_virement;
    }

    public function setFraisVirement($value) {
        $this->_frais_virement = $value;
    }

    public function getInfosSup() {
        return $this->_infos_sup;
    }

    public function setInfosSup($value) {
        $this->_infos_sup = $value;
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

    public function getMonnaie() {
        return $this->_monnaie;
    }

    public function setMonnaie($value) {
        $this->_monnaie = $value;
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

}
