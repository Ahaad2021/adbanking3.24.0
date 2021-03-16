<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * @package Epargne
 */

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/tireur_benef.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/net_bank.php';
require_once 'lib/dbProcedures/cheque_interne.php';


/**
 * Enregistre une opération de <B>dépôt en espèce</B> sur un compte d'épargne
 * @author Thomas FASTENAKEL
 * @since 1.0
 * @param int $id_guichet L'ID du guichet ayant encaissé le montant du dépôt
 * @param int $id_cpte L"ID du compte bénéficiaire (table ad_cpt)
 * @param float $montant Montant du dépôt
 * @param int $type_depot Type de dépôt (1 si dépôt express, 2 si dépôt normal)
 * @param array $CHANGE : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfrice fait sur le taux)
 * @return ErrorObj Les erreurs possibles sont <UL>
 *   <LI> Celles renvoyées par {@link #CheckDepot checkDepot} </LI>
 *   <LI> Celles renvoyées par {@link #passageEcrituresComptablesAuto passageEcrituresComptablesAuto} </LI> </UL>
 */
function depot_cpte($id_guichet, $id_cpte, $montant, $InfoProduit, $InfoCpte, $DATA=NULL, $type_depot=NULL, $CHANGE=NULL, $frais_virement=NULL, $infos_sup=NULL) {
  global $dbHandler, $global_id_agence;

  global $global_nom_login, $global_id_agence, $global_monnaie;

  //pour pouvoir commit ou rollback toute la procédure
  $db = $dbHandler->openConnection();

  if ($DATA != NULL) {
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
  }*/

  //Check que le dépôt est possible sur le compte
  $erreur = CheckDepot($InfoCpte, $montant);
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  // Passage des écritures comptables
  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  //débit d'un guichet par le crédit d'un client
  $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);

  /* Arrondi du montant si paiement au guichet*/
  $critere = array();
  $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
  $cpte_gui = getComptesComptables($critere);
  $montant = arrondiMonnaie( $montant, 0, $cpte_gui['devise'] );

  //Produit du compte d'épargne associé
  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["credit"] = $id_cpte;
  if (isset($CHANGE)) {
    $myErr = change ($CHANGE['devise'], $InfoCpte['devise'], $CHANGE['cv'], $montant, 160, $cptes_substitue, $comptable, $CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE['taux'],true,$infos_sup);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  } else {
    $myErr = passageEcrituresComptablesAuto(160, $montant, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte,$infos_sup);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  //--------------- frais d'opérations ---------------------------------------------------
  //en cas de frais d'opérations, débit compte guichet et crédit compte interne de produit
  if ($InfoProduit["frais_depot_cpt"] > 0 ) {
    unset($cptes_substitue["cpta"]["credit"]);
    unset($cptes_substitue["int"]["credit"]);

    // FIXME Bernard : les frais de dépôt étaient précédement pris au guichet.
    // Maintenant, ils sont pris sur le compte. Il faut changer l'intitulé de l'opération 150 (ou en rajouter une nouvelle).
    // Produit du compte d'épargne associé
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte;

    // Passage des écritures comptables
    if ($InfoCpte['devise']==$global_monnaie) {
      $myErr = passageEcrituresComptablesAuto(150, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
    } else {
      $myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $InfoProduit['frais_depot_cpt'], 150, $cptes_substitue, $comptable,true,NULL,$id_cpte);
    }
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  /* Eventuels frais de virement en cas de dépôt par lot pour les virement de salaires ------------- */
  if ($frais_virement != NULL ) {
    unset($cptes_substitue["cpta"]["credit"]);
    unset($cptes_substitue["int"]["credit"]);

    /* Compte comptable associé au produit d'épargne du compte */
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["debit"] = $id_cpte;

    /* Passage des écritures comptables: les frais de virement sont dans la devise de référence */
    if ($InfoCpte['devise']==$global_monnaie) {
      $myErr = passageEcrituresComptablesAuto(151, $frais_virement, $comptable, $cptes_substitue,NULL,NULL,$id_cpte);
    } else {
      $myErr = effectueChangePrivate($InfoCpte['devise'],$global_monnaie,  $frais_virement, 151, $cptes_substitue, $comptable,true,NULL,$id_cpte);
    }

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  if ($type_depot == 1) {//dépôt express
 	    $myErr = ajout_historique(86, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable, $DATA_HIS_EXT);
 	} else if ($type_depot == NULL){//dépôt normal
 	    $myErr = ajout_historique(75, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable, $DATA_HIS_EXT);
 	} else if ($type_depot == 158){//dépôt par lot
 	    $myErr = ajout_historique(158, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable, $DATA_HIS_EXT, NULL);
 	} else if ($type_depot == 159){//dépôt par lot via fichier
 	    $myErr = ajout_historique(159, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable, $DATA_HIS_EXT, NULL);
 	}
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $id_his = $myErr->param;
  $dbHandler->closeConnection(true);

  // le paramètre 'mnt' n'est pour le moment utilisé nulle part
  return new ErrorObj(NO_ERR, array('id'=>$id_his), NULL, array("comptable" => $comptable));

}

/**
 * Enregistre une opération de <B>retrait</B> sur un compte d'épargne (retrait cash ou suite à réception chèque / OP / achat traveler's cheques ou autre)
 * @author Inconnu
 * @author Dernière modification : Bernard De Bois 03/05/05
 * @author Dernière modification : Stefano A. Mai 2007
 * @param int $id_guichet L'ID du guichet d'où sera retiré le retrait
 * @param int $id_cpte L'ID du compte client qui sera débité
 * @param array $InfoProduit : les données sur le produit d'épargne (notamment les frais de retrait).
 * @param array $InfoCpte : les données sur le compte sélectionné.
 * @param float $montant Montant du retrait
 * @param int $type_retrait : Type de retrait (1 espèce, 15 chèque guichet, 3 ordre de paiement, 4 Autorisation de retrait sans livret/chèque, 5 travelers, 6 : Recharge Carte Ferlo)
 * @param array $data_cheque : les données figurant sur le chèque + la remarque
 * @param array $CHANGE : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfice fait sur le taux)
 * @return ErrorObj erreur
 */
function retrait_cpte($id_guichet, $id_cpte, $InfoProduit, $InfoCpte, $montant, $type_retrait, $id_mandat, $data_cheque=NULL, $CHANGE=NULL,$dataBef=NULL,$isDureeMinEntreRetraits=NULL) {
  global $global_id_client, $global_nom_login, $global_id_agence, $global_id_guichet;
  global $dbHandler, $global_multidevise;
  global $global_monnaie;
  $comptable = array();
   $is_insert_chq = FALSE;
  $db = $dbHandler->openConnection();
  //vérifier d'abord qu'on peut retirer
  switch ($type_retrait) { //1:espèce, 2:chèque, 3:ordre de paiement, 4:chèque guichet, 5:travelers cheque, 6:Recharge Ferlo
  case 1:
  case 4:
  case 5:
  case 6:
    $retrait_transfert = 0;//il s'agit d'un retrait (il faut prélever des frais de retrait)
    break;
  case 3:
    $retrait_transfert = 1;//il s'agit d'un transfert (il faut prélever des frais de transfert)
    break;
  case 8:
  case 15:
    if ($data_cheque['id_correspondant'] == 0)
      $retrait_transfert = 0; //il s'agit d'un chèque-guichet
    else
      $retrait_transfert = 1;//il s'agit d'un chèque transmis par une banque
    break;
  case 55: // Retrait par lot
      if ($data_cheque['id_correspondant'] == 0)
          $retrait_transfert = 0; //il s'agit d'un chèque-guichet
      else
          $retrait_transfert = 1;//il s'agit d'un chèque transmis par une banque
      break;
  default:
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $erreur = new ErrorObj(NO_ERR);
  if ($isDureeMinEntreRetraits != null){ // ticket 805 : laisser passer erreur duree min entre 2 retraits
    $erreur = CheckRetrait($InfoCpte, $InfoProduit, $montant, $retrait_transfert, $id_mandat, true);
  }
  else{
    $erreur = CheckRetrait($InfoCpte, $InfoProduit, $montant, $retrait_transfert, $id_mandat);
  }
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }


  // Passage de l'écriture de retrait
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  if ($type_retrait == 8) {

      $num_cheque = trim($data_cheque["num_piece"]);

      // Vérifié l'existence du numéro du chèque
      if (!ChequeCertifie::isChequeCertifie($num_cheque, ChequeCertifie::ETAT_CHEQUE_CERTIFIE_NON_TRAITE)) {
          $titre = "Retrait chèque certifié impossible ";
          $ecran_retour = "Gen-10";
          sendMsgErreur($titre, "Aucun chèque certifié n'a été trouvé !", $ecran_retour);
      }

      // Recup du produit épargne chèque certifié
      $EPG_CHQ_CERTIF = ChequeCertifie::getProdEpargneChequeCertifie();
      $INFO_CHQ = ChequeCertifie::getChequeCertifie($num_cheque, $id_cpte);

      // Débit du compte client lié au chèque certifié
      $cpta_debit = $EPG_CHQ_CERTIF["cpte_cpta_prod_ep_int"];

      $int_debit = $INFO_CHQ['num_cpte_cheque'];
  }
  else
  {
      // Débit du compte client
      $cpta_debit = getCompteCptaProdEp($id_cpte);

      $int_debit = $id_cpte;
  }

    // Débit du compte client
    $cptes_substitue["cpta"]["debit"] = $cpta_debit;
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $int_debit;

  /* Arrondi du montant si opération au guichet*/
  if ( ( $type_retrait == 1 ) || ( $type_retrait ==  4 ) ) {
    $critere = array();
    $critere['num_cpte_comptable'] = getCompteCptaGui($id_guichet);
    $cpte_gui = getComptesComptables($critere);    
    $montant = arrondiMonnaiePrecision($montant, $cpte_gui['devise']);
  }

  //crédit, selon les cas : du guichet / du compte correspondant / du compte Travelers
  switch ($type_retrait) {
    //retrait par le client en espèce
  case 1:
    $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
    $operation=140;
    $fonction=70;
    break;
    //Recharge Carte ferlo par Compte Epargne
  case 6:
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
    $operation=141;
    $fonction=81;
    break;
  //retrait par chèque
  case 8:
  case 15:
  case 3:
  case 4://il s'agit d'un chèque présenté au guichet par un individu (retrait cash) (type = 2 ou 4)
    if ($data_cheque['id_correspondant']==0 || !isset($data_cheque['id_correspondant'])) {
      $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
      if ($type_retrait == 8) {
        $operation=532;
      } else {
        $operation=512;
      }
      $fonction=70;
      if ($type_retrait == 8 || $type_retrait == 15) {
      	$is_insert_chq = TRUE;
      }
    }
    //il s'agit d'un chèque reçu d'un organisme bancaire ou d'un virement. (type = 2 ou 3)
    else {
      if ($type_retrait == 15) {

        $num_cheque = trim($data_cheque['num_piece']);
        $result = valideCheque($num_cheque,$id_cpte);

        if ($result->errCode != NO_ERR)
        {
            return $result;
        }
        //Réception d'un chèque client (Chèque ordinaire)
        $operation=529;
        $fonction=76;
        $is_insert_chq = TRUE;

        $isChequeCertifie = ChequeCertifie :: isChequeCertifie($num_cheque,ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS);

      //Vérification si le chèque est certifié
        if($isChequeCertifie)
          {
              $isChequeTraite = ChequeCertifie :: isChequeCertifie($num_cheque,ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TRAITE);

              //si le chèque est traité ne pas continué
              if($isChequeTraite){
                  return new ErrorObj(ERR_CHEQUE_USE);
              }

              $EPG_CHQ_CERTIF = ChequeCertifie::getProdEpargneChequeCertifie();
              $INFO_CHQ = ChequeCertifie::getChequeCertifie($num_cheque, $id_cpte);

              $int_debit =   $INFO_CHQ['num_cpte_cheque'];

              //Réception d'un chèque certifié client (Chèque certifié)
              $operation=540;
              $cptes_substitue["cpta"]["debit"] = $EPG_CHQ_CERTIF["cpte_cpta_prod_ep_int"] ;
              $cptes_substitue["int"]["debit"] = $int_debit;
          }

            $comptesCompensation = getComptesCompensation($data_cheque['id_correspondant']);
            $cptes_substitue["cpta"]["credit"] = $comptesCompensation['compte'];
        
       // $dataBef
      } else if ($type_retrait == 3) {
        $comptesCompensation = getComptesCompensation($data_cheque['id_correspondant']);
        $cptes_substitue["cpta"]["credit"] = $comptesCompensation['credit'];
        $operation=513;
        $fonction=76;
      }
    }
    break;
    //retrait de Travelers cheque
  case 5:
    $AG = getAgenceDatas($global_id_agence);
    $cptes_substitue["cpta"]["credit"] = $AG['num_cpte_tch'];
    $operation=511;
    $fonction=70;
    break;
  case 55: // Retrait par lot
      if (!isset($data_cheque['id_correspondant']) || $data_cheque['id_correspondant']==0) {
          $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($id_guichet);
          $operation=140;
      }
      else {
          if ($data_cheque['type_ret'] == 3){
            $comptesCompensation = getComptesCompensation($data_cheque['id_correspondant']);
            $cptes_substitue["cpta"]["credit"] = $comptesCompensation['compte'];
            $operation=513;
          }else {
            $comptesCompensation = getComptesCompensation($data_cheque['id_correspondant']);
            $cptes_substitue["cpta"]["credit"] = $comptesCompensation['credit'];
            $operation = 513;
          }

          if ($data_cheque['type_piece']==15) {
              $is_insert_chq = TRUE;
          }
      }
      $fonction=154;
      break;
  }
  if ($type_retrait==3 || ($type_retrait==55 && $data_cheque['type_piece']==3 && $data_cheque['type_ret'] != 3)) { //Si c'est un ordre de paiement, on crée une attente
    unset($data_cheque['type_ret']);unset($data_cheque['dest_fond']);
    $erreur = insertAttente($data_cheque);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
  }

  if (is_array($CHANGE) && ($InfoCpte['devise'] != $CHANGE["devise"])) {
    $myErr = change($InfoCpte['devise'], $CHANGE["devise"], $montant, $CHANGE["cv"], $operation, $cptes_substitue, $comptable, 1, $CHANGE["comm_nette"], $CHANGE["taux"]);
  } else {

    $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue, $InfoCpte['devise']);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  // En cas de frais d'opérations, crédit compte interne de produit et débit compte client
  $frais_retrait = 0;
  if (($retrait_transfert == 0) && ($InfoProduit['frais_retrait_cpt'] > 0)) {
    if ($InfoCpte["frais_retrait_spec"] == 'f') {
      $frais_retrait = $InfoProduit['frais_retrait_cpt'];
      $operation = 131;
    } else {
      if ($type_retrait==1) {
        $frais_retrait = $InfoProduit['frais_retrait_cpt'];
        $operation = 130;
      }
      elseif (($type_retrait==15)|| ($type_retrait==4)){
        $frais_retrait = $InfoProduit['frais_retrait_cpt'];
        $operation = 134;
      }
      elseif ($type_retrait==5){
        $frais_retrait = $InfoProduit['frais_retrait_cpt'];
        $operation = 136;
      }
      elseif ($type_retrait==8) {
        $frais_retrait = $InfoProduit['frais_retrait_cpt'];
        $operation = 138;
      }

    }
  }
  if (($retrait_transfert == 1) && ($InfoProduit['frais_transfert'] > 0)) {
    $frais_retrait=$InfoProduit['frais_transfert'];
    $operation = 152;
  }
  if ($frais_retrait > 0) {
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte;

    $myErr = effectueChangePrivate($InfoCpte['devise'], $global_monnaie, $frais_retrait, $operation, $cptes_substitue, $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  // Si la duree minimum entre deux retraits n'est pas atteinte, prélever les frais  : Ticket 805
  if ($isDureeMinEntreRetraits != null && $isDureeMinEntreRetraits == 't'){
    if (intval(getNbrJoursEntreDeuxRetrait($operation,$id_cpte)) <= intval($InfoProduit['duree_min_retrait_jour'])){
      $myErr = preleveFraisDureeMinEntre2Retraits($id_cpte, $comptable);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
  }

  // Si le compte est passé en découvert, prélever les frais de dossier découvert
  $myErr = preleveFraisDecouvert($id_cpte, $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($id_mandat != NULL && $id_mandat != 'CONJ') {
    $MANDAT = getInfosMandat($id_mandat);
    $data_cheque['id_pers_ext'] = $MANDAT['id_pers_ext'];
  }
  //
  if ($data_cheque != NULL) {
    $data_his_ext = creationHistoriqueExterieur($data_cheque);
  } else {
    $data_his_ext = NULL;
  }
 if( $is_insert_chq ) {
 	if (is_array($dataBef)){
 		$id = insere_tireur_benef($dataBef);
 		$data_his_ext['id_tireur_benef']=$id;
 	}
 	$data_ch['id_cheque']=$data_cheque['num_piece'];
	$data_ch['date_paiement']=$data_cheque['date_piece'];
     if ($type_retrait == 8 or ($type_retrait == 15 and $isChequeCertifie)) {
         $data_ch['etat_cheque']=4; // Certifié
     } else {
         $data_ch['etat_cheque']=1; // Encaissé
     }
	$data_ch['id_benef'] =$data_his_ext['id_tireur_benef']; 
	$rep=insertCheque($data_ch, $id_cpte);
	if ($rep->errCode != NO_ERR ) {
		$dbHandler->closeConnection(false);
		return $rep;
	} else {
        if ($type_retrait == 8 or ($type_retrait == 15 and $isChequeCertifie)) {
            // Mettre à jour le statut d'un chèque certifié à Traité
            $erreur = ChequeCertifie::updateChequeCertifieToTraite($num_cheque, $id_cpte, $int_debit, "Retrait chèque interne certifié No. ". $num_cheque);

            if ($erreur->errCode != NO_ERR) {
                $dbHandler->closeConnection(false);
                return $erreur;
            } else {
                // Fermeture du compte de chèque certifié
                $erreur = ChequeCertifie::closeCompteChequeCertifie($int_debit);

                if ($erreur->errCode != NO_ERR) {
                    $dbHandler->closeConnection(false);
                    return $erreur;
                }
            }
        }
    }
 }	
  $myErr = ajout_historique($fonction, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable, $data_his_ext);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $id_his = $myErr->param;

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, array('id'=>$id_his));
}

/**
 * Renvoie tous les comptes d'épargne d'un client qui sont services financiers
 * @param int $id_client L'identifiant du client
 * @param str $devise La devise dans laquelle on cherche les comptes
 * @return array Tableau associatif avec les comptes trouvés, indicé par les identifiants du compte.
 */
function get_comptes_epargne($id_client, $devise=NULL) {
   global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT a.*, b.* FROM ad_cpt a,adsys_produit_epargne b WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence AND a.id_prod = b.id AND ";
  $sql .= "a.id_titulaire = '$id_client' and b.service_financier = true and b.classe_comptable <> 8";
  // On ne prend pas les comptes bloqués
  if (!is_client_radie()){
  $sql .= " AND (a.etat_cpte <> 2)";
}
  if ($devise != NULL)
    $sql .= " AND a.devise = '$devise'";

  $sql .= " ORDER BY a.num_complet_cpte";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;

  $TMPARRAY = array();
  while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $TMPARRAY[$prod["id_cpte"]] = $prod;
    $TMPARRAY[$prod["id_cpte"]]["soldeDispo"] = getSoldeDisponible($prod["id_cpte"]);
  }

  return $TMPARRAY;
}

/**
 * Renvoie  les comptes d'épargne   parts sociale d'un client 
 * @param int $id_client L'identifiant du client
 * @param str $devise La devise dans laquelle on cherche les comptes
 * @return array Tableau associatif avec les comptes trouvés, indicé par les identifiants du compte.
 * ticket_361
 */
function get_comptes_epargne_parts_sociale($id_client, $devise=NULL) {
	global $dbHandler,$global_id_agence;

	$db = $dbHandler->openConnection();
	$sql = "SELECT a.*, b.* FROM ad_cpt a,adsys_produit_epargne b WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence AND b.id = 2 AND a.id_prod = 2 AND ";
	$sql .= "a.id_titulaire = '$id_client' ";
	// On ne prend pas les comptes bloqués
	$sql .= " AND (a.etat_cpte <> 2)";
	if ($devise != NULL)
		$sql .= " AND a.devise = '$devise'";

	$sql .= " ORDER BY a.num_complet_cpte";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	$dbHandler->closeConnection(true);

	if ($result->numRows() == 0) return NULL;

	$TMPARRAY = array();
	while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$TMPARRAY[$prod["id_cpte"]] = $prod;
		$TMPARRAY[$prod["id_cpte"]]["soldeDispo"] = getSoldeDisponible($prod["id_cpte"]);
	}

	return $TMPARRAY;
}


/**
 * Renvoie  les comptes d'épargne compte courant d'un client
 * NOTE: exclu le compte de part sociale du client meme s'il ramene tous les comptes classe_comptable =1
 * @param int $id_client L'identifiant du client
 * @param str $devise La devise dans laquelle on cherche les comptes
 * @return array Tableau associatif avec les comptes trouvés, indicé par les identifiants du compte.
 * ticket_361
 */
function get_comptes_epargne_compte_courant($id_client, $devise=NULL) {
	global $dbHandler,$global_id_agence;
	//$AGC = getAgenceDatas ( $global_id_agence );
	
	$db = $dbHandler->openConnection();
	$sql = "SELECT a.*, b.* FROM ad_cpt a,adsys_produit_epargne b WHERE a.id_prod=b.id AND a.id_ag=b.id_ag and a.id_ag=$global_id_agence AND b.classe_comptable = 1 AND  a.id_prod != 2 AND a.id_prod !=3 AND ";
	$sql .= "a.id_titulaire = '$id_client' ";
	// On ne prend pas les comptes bloqués
	$sql .= " AND (a.etat_cpte <> 2)";
	if ($devise != NULL)
		$sql .= " AND a.devise = '$devise'";

	$sql .= " ORDER BY a.num_complet_cpte";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	$dbHandler->closeConnection(true);

	if ($result->numRows() == 0) return NULL;

	$TMPARRAY = array();
	while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
		$TMPARRAY[$prod["id_cpte"]] = $prod;
		$TMPARRAY[$prod["id_cpte"]]["soldeDispo"] = getSoldeDisponible($prod["id_cpte"]);
	}

	return $TMPARRAY;
}


/**
 * Renvoie la liste de tous les comptes d'un client sauf les comptes de crédit (id_prod = 4)
 * @param int $id_client L'ID du client
 * @param bool $include_closed_accounts Si true, alors renvoie aussi les compte fermés, sinon ne renvoie que les comptes ouvers ou bloqués
 * @return Array ($id_cpte => Caractéritiques du compte)
 */
function getAllAccounts ($id_client, $condition = null, $include_closed_accounts=false) {
  global $dbHandler;
  global $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_prod_cpte_credit FROM ad_agc WHERE id_ag = $global_id_agence;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $tmprow = $result->fetchRow();
  $id_prod_cre = $tmprow[0];
  $sql = "SELECT a.*, b.*, c.id_doss, c.cre_mnt_bloq FROM ad_cpt a,adsys_produit_epargne b, ad_dcr c
          WHERE a.id_ag=b.id_ag and a.id_ag = $global_id_agence AND a.id_prod = b.id 
          AND c.id_client = a.id_titulaire AND a.id_titulaire = '$id_client' 
          AND b.id <> $id_prod_cre  AND b.classe_comptable <> 8 ";

  if ($include_closed_accounts == false)
    $sql .= " AND a.etat_cpte <> 2 ";

  if(!empty($condition))
    $sql .= " AND ".$condition;
  $sql .= " ORDER BY a.num_cpte";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;

  $TMPARRAY = array();
  while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $TMPARRAY[$prod['id_cpte']] = $prod;
  }

  return $TMPARRAY;
}

/**
 * Renvoie la liste de tous les comptes dormants  d'un client 
 * @param int $id_client L'ID du client
 * @return Array ($id_cpte => Caractéritiques du compte)
 */
function getComptesDormants ($id_client) {
  global $dbHandler;
  global $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT a.*, b.* FROM ad_cpt a,adsys_produit_epargne b WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence AND a.id_prod = b.id AND ";
  $sql .= "a.id_titulaire = '$id_client' ";
  $sql .= " AND a.etat_cpte = 4  ";
  $sql .= " ORDER BY a.num_cpte";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;

  $TMPARRAY = array();
  while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $TMPARRAY[$prod["id_cpte"]] = $prod;
  }

  return $TMPARRAY;
}
/**
 * Fonction qui permet d'activer les comptes dormants
 */
function activerCompteDormant (array $tabComptes) {
	global $global_monnaie,$global_id_agence,$global_nom_login;
	global $dbHandler;
	$comptable = array();
	$last_ecriture = array();
	$type_oper = 170;
	foreach ($tabComptes as $id_cpte => $valeur  ) {
		//$id_cpte = $tabComptes['id_cpte'];
		$db = $dbHandler->openConnection();
		$sql = " select * from ad_mouvement where id_ecriture in  ";
		$sql .=" ( SELECT  max(id_ecriture) from ad_ecriture where  type_operation = $type_oper AND info_ecriture::integer = $id_cpte )  ";
		
		//$sql .=" type_operation =170 and  cpte_interne_cli = $tabComptes['id_cpte'] ";
	 	$result = $db->query($sql);
	  	if (DB::isError($result)) {
	  		$dbHandler->closeConnection(false);
	  	 	signalErreur(__FILE__,__LINE__,__FUNCTION__);
	  	}
	  	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
	  	 	$last_ecriture [$row['sens']] = $row;
	  	}
	  
		$cptes_substitue = array();
	    $cptes_substitue["cpta"] = array();
	    $cptes_substitue["int"] = array();
      $cptes_substitue["int"]["debit"] = $id_cpte;
      $cptes_substitue["int"]["credit"] = $id_cpte;
	    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
	    if ($cptes_substitue["cpta"]["credit"] == NULL) {
	      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
	    }
	   
	    $MyError = getDetailsOperation($type_oper);
	    if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
	    	$dbHandler->closeConnection(false);
	    	return $MyError;
	  	} else {
	    	$DetailsOperation = $MyError->param;
	  	}
	  	
	 	$cptes_substitue["cpta"]["debit"] = $DetailsOperation["credit"]["compte"];
	    if ($cptes_substitue["cpta"]["debit"] == NULL) {
	      return new ErrorObj(ERR_NO_ASSOCIATION, sprintf(_("Compte au débit de l'opération %s"), $type_oper));
	    }
	
	    $montant = $last_ecriture[SENS_DEBIT]['montant'];
	
	    //$cptes_substitue["int"]["debit"] = $tabComptes['id_cpte'];
	    $err = passageEcrituresComptablesAuto(170, $montant, $comptable, $cptes_substitue,$last_ecriture[SENS_DEBIT]["devise"],NULL,$id_cpte,$infos_sup);
	    if ($err->errCode != NO_ERR) {
	    	$dbHandler->closeConnection(false);
	      return $err;
	    }
	    	
	 	$myErr = ajout_historique(91, $valeur["id_titulaire"],'', $global_nom_login, date("r"), $comptable);
	  	if ($myErr->errCode != NO_ERR) {
	    	$dbHandler->closeConnection(false);
	    	return $myErr;
	  	}
		$sql = buildUpdateQuery ("ad_cpt", array("etat_cpte "=>'1','raison_blocage'=>NULL), 
		array("id_cpte"=>$id_cpte,'id_ag'=>$global_id_agence));
	  	$result = $db->query($sql);
	  	if (DB::isError($result)) {
	    	$dbHandler->closeConnection(false);
	    	signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
	  	}
	  	debloqGarantie($id_cpte,$montant);
	}
  	$dbHandler->closeConnection(true);
  	return  new ErrorObj(NO_ERR);
	}
/**
 * Fonction qui permet d'déactiver les comptes dormants
 */
function deactiverCompteDormant (array $tabComptes,array & $comptable) {
	global $global_monnaie;
	global $dbHandler;
	//$comptable = array();
	if(count($tabComptes) > 0 && isset($tabComptes['id_cpte'])) {
		$db = $dbHandler->openConnection();
		$cptes_substitue = array();
	    $cptes_substitue["cpta"] = array();
	    $cptes_substitue["int"] = array();
      $cptes_substitue["int"]["credit"] = $tabComptes['id_cpte'];
      $cptes_substitue["int"]["debit"] = $tabComptes['id_cpte'];
	    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($tabComptes['id_cpte']);
	    if ($cptes_substitue["cpta"]["debit"] == NULL) {
	      return new ErrorObj(ERR_CPTE_NON_PARAM, $tabComptes['id_cpte']);
	    }
	
	    //$cptes_substitue["int"]["debit"] = $tabComptes['id_cpte'];
	    $err = passageEcrituresComptablesAuto(170, $tabComptes['solde'], $comptable, $cptes_substitue,$tabComptes["devise"],NULL,$tabComptes['id_cpte'],$infos_sup);
	    if ($err->errCode != NO_ERR) {
	    	$dbHandler->closeConnection(false);
	      return $err;
	    }
	    ///bloqué le montant
	    bloqGarantie($tabComptes['id_cpte'],$tabComptes['solde']);
	 	$dbHandler->closeConnection(true);
	  	return $err;
	} ELSE {
		return new ErrorObj(NO_ERR);
	}
}

/**
 * Fonction qui permet d'déactiver les comptes inactifs
 */
function deactiverCompteInactif(array $tabComptes,array & $comptable) {
    global $global_monnaie;
    global $dbHandler;
    //$comptable = array();
    if(count($tabComptes) > 0 && isset($tabComptes['id_cpte'])) {
        $db = $dbHandler->openConnection();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
        $cptes_substitue["int"]["credit"] = $tabComptes['id_cpte'];
        $cptes_substitue["int"]["debit"] = $tabComptes['id_cpte'];
        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($tabComptes['id_cpte']);
        if ($cptes_substitue["cpta"]["debit"] == NULL) {
            return new ErrorObj(ERR_CPTE_NON_PARAM, $tabComptes['id_cpte']);
        }

        //$cptes_substitue["int"]["debit"] = $tabComptes['id_cpte'];
        $err = passageEcrituresComptablesAuto(171, $tabComptes['solde'], $comptable, $cptes_substitue,$tabComptes["devise"],NULL,$tabComptes['id_cpte'],$infos_sup);
        if ($err->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $err;
        }
        ///bloqué le montant
        bloqGarantie($tabComptes['id_cpte'],$tabComptes['solde']);
        $dbHandler->closeConnection(true);
        return $err;
    } ELSE {
        return new ErrorObj(NO_ERR);
    }
}
/**
 * Renvoie toutes les informations concernant un compte d'épargne en particulier.
 *
 * @param int $id_compte L'identifiant du compte dont on veut les informations.
 * @return array Tableau contenant les informations du compte (ad_cpt).
 */
function get_compte_epargne_info($id_compte) {
  global $dbHandler,$global_id_agence;

  $db=$dbHandler->openConnection();
  $sql = "SELECT * FROM ad_cpt WHERE id_ag=$global_id_agence AND id_cpte = '$id_compte'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Aucune ou plusieurs occurences du compte !"));
  }
  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $retour;
}

/**
 * Renvoie l'id d'un compte en fonction de son numéro de compte complet.
 *
 * @param string $num_complet_cpte Le numéro de compte complet.
 * @return int L'identifiant du compte.
 */
function get_id_compte($num_complet_cpte) {
  global $dbHandler,$global_id_agence;

  $db=$dbHandler->openConnection();
  $sql = "SELECT id_cpte FROM ad_cpt WHERE id_ag=$global_id_agence AND num_complet_cpte = '$num_complet_cpte'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() > 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "plusieurs occurences du compte!"
  }
  if ($result->numrows() == 0) {
    $dbHandler->closeConnection(false);
    return NULL;
  }
  $retour = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $retour[0];
}

/**
 * Renvoie le matricule d'un compte en fonction de son numéro.
 *
 * @param string $num_complet_cpte Le numéro de compte complet.
 * @return int L'identifiant du compte.
 */
function get_matricule($num_matricule) {
  global $dbHandler,$global_id_agence;

  $db=$dbHandler->openConnection();
  $sql = "SELECT count(*) FROM ad_cli WHERE id_ag=$global_id_agence AND matricule = '$num_matricule'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() > 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "plusieurs occurences du compte!"
  }
  if ($result->numrows() == 0) {
    $dbHandler->closeConnection(false);
    return NULL;
  }
  $retour = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $retour[0];
}

/**
 * Renvoie si le numero de la carte existe
 *
 * @param string $num_complet_cpte Le numéro de compte complet.
 * @return int L'identifiant du compte.
 */
function get_carte_uba($num_carte) {
  global $dbHandler,$global_id_agence;

  $db=$dbHandler->openConnection();
  $sql = "SELECT count(*) FROM ad_cli WHERE id_ag=$global_id_agence AND id_card = '$num_carte'";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() > 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "plusieurs occurences du compte!"
  }
  if ($result->numrows() == 0) {
    $dbHandler->closeConnection(false);
    return NULL;
  }
  $retour = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $retour[0];
}

/**
 * Vérifie que le dépôt est possible sur le compte.
 *
 * @param array $InfoCpte Tableau avec les infos sur le compte
 * @param int $montant Montant à déposer sur le compte.
 * @return ErrorObj
 */
function CheckDepot($InfoCpte, $montant) {
  $InfoProduit = getProdEpargne($InfoCpte['id_prod']);


  //pour le dépôt unique, on vérifie s'il s'agit du versement initial avec solde = 0
  if (($InfoProduit["depot_unique"] == 't') && ($InfoCpte["solde"] != 0))
    return new ErrorObj(ERR_DEPOT_UNIQUE, $InfoCpte['id_cpte']);

  $id_cpte  = $InfoCpte["id_cpte"];
 	$id_client = $InfoCpte["id_titulaire"];
  // On ne peut pas déposer sur un compte bloqué
	if ($InfoCpte["etat_cpte"] == 3){
 	    $num_complet_cpte  = $InfoCpte["num_complet_cpte"];
 	    $id_client = $InfoCpte["id_titulaire"];
 	    return new ErrorObj(ERR_CPTE_BLOQUE, sprintf(_("Le Compte n° %s du client n° %s est bloqué.") , $num_complet_cpte, $id_client));
 	} else if($InfoCpte["etat_cpte"] == 6){
	 		$num_complet_cpte  = $InfoCpte["num_complet_cpte"];
	 	  $id_client = $InfoCpte["id_titulaire"];
	 	  return new ErrorObj(ERR_CPTE_BLOQUE_DEPOT, sprintf(_("Le Compte n° %s du client n° %s est bloqué pour les dépôts.") , $num_complet_cpte, $id_client));
 	} else if($InfoCpte["etat_cpte"] == 4){
	 		$num_complet_cpte  = $InfoCpte["num_complet_cpte"];
	 	  $id_client = $InfoCpte["id_titulaire"];
	 	  return new ErrorObj(ERR_CPTE_DORMANT, sprintf(_("Le Compte n° %s du client n° %s est dormant.") , $num_complet_cpte, $id_client));
 	}

  //vérifier dépassement montant maximum
  if ($InfoProduit["mnt_max"] > 0) {
    //on suppose que le montant bloqué sur le compte est intégré au solde
    if (($InfoCpte["solde"] + $montant) > $InfoProduit["mnt_max"]){
    	return  new ErrorObj(ERR_MNT_MAX_DEPASSE, $InfoCpte['id_cpte']);
    }
  }

  //vérifier montant minimum non dépassé
  if ($InfoCpte["mnt_min_cpte"] > 0) {
    //il se peut qu'on fasse un premier dépôt qui soit inférieur au montant mini et dans ce cas, interdire que ce premier dépôt soit inférieur au mini autorisé pour le compte
    if (($InfoCpte["solde"] + $InfoCpte["decouvert_max"] + $montant) < $InfoCpte["mnt_min_cpte"]){
    	return  new ErrorObj(ERR_MNT_MIN_DEPASSE, sprintf(_("Montant du dépôt inférieur au montant minimum sur le compte n° %s du client n° %s."),$id_cpte, $id_client));
    }
  }

  return new ErrorObj(NO_ERR);

}

/**
 *  Vérifie que le dépôt est possible sur le compte par Ewallet
 * @param $InfoCpte
 * @param $montant
 * @return ErrorObj
 */
function CheckDepotEwallet($InfoCpte, $montant) {
  // On ne peut pas déposer sur un compte fermer
  if ($InfoCpte["etat_cpte"] == 2){
    $num_complet_cpte  = $InfoCpte["num_complet_cpte"];
    $id_client = $InfoCpte["id_titulaire"];
    return new ErrorObj(ERR_CPTE_FERME, sprintf(_("Le Compte n° %s du client n° %s est fermé.") , $num_complet_cpte, $id_client));
  } else {
    return CheckDepot($InfoCpte, $montant);
  }

}

/**
 * Vérifie que le retrait est possible sur le compte
 * @author Inconnu - Dernière modification : Bernard De Bois
 * @param array $InfoCpte : données du compte d'épargne sélectionné
 * @param array $InfoProduit : données du produit d'épargne
 * @param float $montant : montant à débiter du compte
 * @param int $operation : (0 prend en compte les frais de retrait, 1 prend en compte les frais de transfert, 2 concerne le retrait ewallet)
 * @return ErrorObj Objet Erreur
 */
function CheckRetrait($InfoCpte, $InfoProduit, $montant, $operation, $id_mandat, $test_delai = false) {
  //vérification de l'état du compte : ouvert
  if ($InfoCpte["etat_cpte"] == 3){
  	return new ErrorObj(ERR_CPTE_BLOQUE, $InfoCpte["id_cpte"]);
  }
  if ($InfoCpte["etat_cpte"] == 4){
  	return new ErrorObj(ERR_CPTE_ATT_FERM, $InfoCpte["id_cpte"]);
  }
  if ($InfoCpte["etat_cpte"] == 7){
  	return new ErrorObj(ERR_CPTE_BLOQUE_RETRAIT, $InfoCpte["id_cpte"]);
  }
  //vérifier possibilité retrait
  if ($InfoProduit["retrait_unique"] == 't'){
 	   return new ErrorObj(ERR_RETRAIT_UNIQUE, $InfoCpte['id_cpte']);
 	}

  // Recherche des frais à appliquer en fonction du type d'opération
  $frais = 0;
  if ($operation == 0) { // Retrait cash
    $frais = $InfoProduit['frais_retrait_cpt']+$InfoProduit['frais_duree_min2retrait']; // Ticket 805 : ajout frais de non respect de la duree min entre 3 retraits
  } else if ($operation == 1) { // Retrait par transfert
    $frais = $InfoProduit['frais_transfert'] +$InfoProduit['frais_duree_min2retrait']; // Ticket REL-76 : Manque de possibilité de continuer l'operation de transfert entre comptes lorsque la durée minimum entre deux retrait n'est pas atteinte.
  }

  $solde_disponible = getSoldeDisponible($InfoCpte['id_cpte']);
  if ( ($solde_disponible - $frais) < 0){
 	   return  new ErrorObj(ERR_MNT_MIN_DEPASSE, $InfoCpte["id_cpte"]);
 	}

  if($test_delai == false){
  	//vérifier si durée mini entre deux retraits
	  if ($InfoProduit["duree_min_retrait_jour"] > 0) {	    
	  $erreur = CheckDureeMinRetrait($InfoCpte["id_cpte"], $InfoProduit["duree_min_retrait_jour"], $InfoProduit["type_duree_min2retrait"]);
	    if ($erreur->errCode != NO_ERR){
	    	 return $erreur;
	    }
	  }
  }


  // Vérifications sur le mandat
  if ($id_mandat != NULL && $id_mandat != 'CONJ' && $id_mandat != 0) {
    $MANDAT = getInfosMandat($id_mandat);
    if (($MANDAT['limitation'] != NULL && $MANDAT['limitation'] != 0 && $MANDAT['limitation'] < $montant) || $MANDAT['id_cpte'] != $InfoCpte['id_cpte']) {
      return new ErrorObj(ERR_MANDAT_INSUFFISANT, $InfoCpte['id_cpte']);
    }
  }

  return new ErrorObj(NO_ERR);
}

/**
 * Vérifie que le retrait par ewallet est possible sur le compte
 * @param $InfoCpte
 * @param $montant
 * @return ErrorObj
 */
function CheckRetraitEwallet($InfoCpte, $montant)
{
  $InfoProduit = getProdEpargne($InfoCpte['id_prod']);

  //vérification de l'état du compte : ouvert
  if ($InfoCpte["etat_cpte"] == 2){
    return new ErrorObj(ERR_CPTE_FERME, $InfoCpte["id_cpte"]);
  }
  else {
    return CheckRetrait($InfoCpte, $InfoProduit, $montant, null, null, false);
  }
}

function CheckDureeMinRetrait($id_cpte, $duree_min_retrait, $type_duree=null) {
  /*
    Vérifie si la durée minimum entre 2 retraits est respectée.
    On cherche d'abord la dernière date de retrait pour le compte sélectionné, on additionne la durée minimum de retrait en jours et on la compare à la date d'aujourd'hui

  */

  global $dbHandler,$global_id_agence;


  if ($type_duree == 1) {
    $db = $dbHandler->openConnection();
    //prendre le dernier mouvement débiteur sur le compte du client pour un retrait ou un transfert
    $sql = "select a.date from ad_his a, ad_ecriture b, ad_mouvement c  where (a.id_ag=b.id_ag) and (b.id_ag=c.id_ag) and (a.id_ag=$global_id_agence) AND (a.type_fonction=70 OR a.type_fonction=76 or a.type_fonction=85 or a.type_fonction=92) ";
    $sql .= "and a.id_his=b.id_his and b.id_ecriture=c.id_ecriture and c.cpte_interne_cli='$id_cpte' and c.sens='d' ";
    $sql .= "order by a.date DESC limit 1;";

    $result = $db->query($sql);

    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();

    $dbHandler->closeConnection(true);

    if (isset($row[0])) {

      $date_dernier_retrait = pg2phpDatebis($row[0]); //array sous la forme M/J/Y

      $date_prochain_retrait = mktime(0, 0, 0, $date_dernier_retrait[0],
        $date_dernier_retrait[1] + $duree_min_retrait,
        $date_dernier_retrait[2]);//quel est la date du prochain retrait ?

      $today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));//on va comparer avec aujourd'hui

      $temp = mktime(0, 0, 0, $date_dernier_retrait[0], $date_dernier_retrait[1], $date_dernier_retrait[2]);
      if ($today < $date_prochain_retrait) return new ErrorObj(ERR_DUREE_MIN_RETRAIT, $id_cpte);
    }
  } else if ($type_duree == 2){
    $db = $dbHandler->openConnection();
    // AT-36 : calcul de la date de debut et date de fin du mois actuelle
    $date_jour = date("d");
    $date_mois = date("m");
    $date_annee = date("Y");
    $query_date = $date_jour."-".$date_mois."-".$date_annee;
     // First day of the month.
    $first_month_day = date('Y-m-01', strtotime($query_date));
    // Last day of the month.
    $last_month_day =  date('Y-m-t', strtotime($query_date));

    //prendre la somme de toutes les transactions retrait/retrait express/transfert compte/ retrait en deplace
    $sql = "select count(a.*) as nbre from ad_his a, ad_ecriture b, ad_mouvement c  where (a.id_ag=b.id_ag) and (b.id_ag=c.id_ag) and (a.id_ag=$global_id_agence) AND (a.type_fonction=70 OR a.type_fonction=76 or a.type_fonction=85 or a.type_fonction=92) and b.type_operation <> 158";
    $sql .= "and a.id_his=b.id_his and b.id_ecriture=c.id_ecriture and c.cpte_interne_cli='$id_cpte' and c.sens='d' and a.date>= date('$first_month_day') and a.date <=date('$last_month_day')";


    $result = $db->query($sql);

    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $row = $result->fetchrow();

    $dbHandler->closeConnection(true);

    if (isset($row[0])) {
      if ($duree_min_retrait <= $row[0]) {
        return new ErrorObj(ERR_DUREE_MIN_RETRAIT, $id_cpte);
      }
    }
  }

  return new ErrorObj(NO_ERR);
}

/**
 * Renvoie le solde disponible sur un compte client en tenant compte de
 *  - Compte bloqué => solde = 0
 *  - Retrait unique => solde = 0
 *  - Montant bloqué
 *  - Montant minimum
 *  - Découvert maximum autorisé
 *  - Si solde dispo négatif alors solde disponible = 0
 * @param int $id_cpte Numéro du compte
 * @return float Solde disponible
 */
function getSoldeDisponible($id_cpte) {
  // Remplir 2 tableaux avec toutes les infos sur le compte et le produit associé
  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  if ($InfoProduit["retrait_unique"] == 't' || $InfoCpte["etat_cpte"] == 3)
    $solde_dispo = 0;
  else
    $solde_dispo = $InfoCpte["solde"] - $InfoCpte["mnt_bloq"] - $InfoCpte["mnt_min_cpte"] + $InfoCpte["decouvert_max"] - $InfoCpte["mnt_bloq_cre"];

  if ($solde_dispo < 0)
    $solde_dispo = 0;

  return $solde_dispo;
}

function getListProdEpargneDispo($id_client) {
  /*
    renvoie la liste des produits d'épargne pour lesquels un client peut créer un compte
    en fonction des comptes qu'il a déjà créé sous la forme d'un tableau associatif

  */

  //liste des produits d'épargne pour lesquels on peut ouvrir un compte client
  $prod_epargne = array();
  //nbre de produits d'épargne pour lesquels le client a déjà ouvert un compte sous la forme array[id_prod_epargne]=nbre_comptes_client
  $nb_prod = array();

  $prod_epargne = getListProdEpargne();
  $nb_prod = getNbProdClient($id_client);

  foreach($nb_prod as $key=>$value) {
    foreach($prod_epargne as $k=>$v)
    //si le client a déjà atteint le nombre d'occurrences pour ce produit, enlever ce produit de la liste
    if (( $v["id"] == $key ) && ( $value == $v["nbre_occurrences"] ) && ( $v["nbre_occurrences"] > 0) )
      unset($prod_epargne[$k]);
  };

  return $prod_epargne;

}

function getNbProdClient($id_client) {
  /*
    Retourne le nombre de types de produits d'épargne que le client possède déjà
    sous forme de comptes au niveau de la table ad_cpt sous forme d'un tableau associatif :
    ARRAY(id_prod, count(id_prod))
    on exclut les comptes déjà fermés
  */
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_prod, COUNT(id_prod) FROM ad_cpt WHERE id_ag=$global_id_agence AND id_titulaire = $id_client and (etat_cpte <> 2) GROUP BY id_prod";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  $dbHandler->closeConnection(true);

  $produits = array();
  while ($tmp = $result->fetchrow())
    $produits[$tmp[0]] = $tmp[1];

  return $produits;
}

/**
 	* Renvoie la liste de tous les comptes qui sont liés à un ordre permanent actif
 	* @author Djibril NIANG
 	* @version 3.0.6
 	* @return $cpte_ord_perm : tableau des comptes liés à un ordre permanent actif
*/
function getCpteOrdrePermanent() {
  global $dbHandler;
  global $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT cpt_from,cpt_to from ad_ord_perm WHERE actif = true AND id_ag = $global_id_agence;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $cpte_ord_perm = array();
  while ($cpte = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
   if($cpte['cpt_from'] != '' && $cpte['cpt_to'] != '')
      $cpte_ord_perm[$cpte['cpt_from']] = $cpte;
  }
 	$dbHandler->closeConnection(true);
 	return $cpte_ord_perm;
}
/**
 	* Retourne l'état d'un compte d'épargne
 	* @author Djibril NIANG
 	* @version 3.0.6
  * @param int $id_cpte le numéro du compte
 	* @return Int $etat_cpte : état du compte.
*/
function getEtatCpte($id_cpte) {
  global $dbHandler;
  global $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT etat_cpte FROM ad_cpt WHERE id_cpte = $id_cpte AND id_ag = $global_id_agence;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $etat_cpte = $row[0];
  $dbHandler->closeConnection(true);
  return $etat_cpte;
}

/**
 * Renvoie la liste de tous les comptes du client sur lesquels le dépt est possible
 * @param $ListeComptes (liste des comptes à filtrer) FIXME : Pas très propre : à changer
 */
function getComptesDepotPossible($ListeDeComptes) {

  if (is_array($ListeDeComptes)) {
    foreach($ListeDeComptes as $key=>$value) {
        if (($value["depot_unique"] == 't') || ($value["etat_cpte"] != 1 && $value["etat_cpte"] != "7")) {
            unset($ListeDeComptes[$key]);
        }
    }
  }

  return $ListeDeComptes;
}

function getComptesRetraitPossible($ListeDeComptes) {
  /*
    Renvoie la lisre des comptes sur lesquels le client peut faire un retrait
    i.e. les comptes qui ne sont pas à retrait unique et qui sont ouverts
    et dont la classe comptable est 1 (DAV)
  */

  foreach($ListeDeComptes as $key=>$value) {
    //FIXME : le test sur classe_comptable n'est pas bon car on doit pouvoir
      $cli = getClientDatas($value["id_titulaire"]);
      if (($value["retrait_unique"] == 't') || ($value["etat_cpte"] != "1" && $value["etat_cpte"] != "6") || $value["classe_comptable"] != 1) {
          if ($cli['etat'] != 1) {
              unset($ListeDeComptes[$key]);
          }
      }
    //$soldeDispo = getSoldeDisponible($key);
    //if ($soldeDispo == 0){
      //if ($cli['etat'] != 1 && $cli['etat'] != 2){
        //unset($ListeDeComptes[$key]);
      //}
    //}
  }

  return $ListeDeComptes;

}

/**
 * Renvoie la liste de tous les comptes d'un client pour lesquels la cloture est possible
 * Pour les règles de gestion, cfr checkCloture
 * @author Thomas FASTENAKEL
 * @param int $id_client
 * @return ErrorObj Objet Erreur
 */
function getComptesCloturePossible($id_client) {
  $ACCS = get_comptes_epargne($id_client);
  if (is_array($ACCS))
    foreach($ACCS as $id_cpte => $ACC) {
    $myErr = checkCloture($id_cpte);
    if ($myErr->errCode != NO_ERR)
      unset($ACCS[$id_cpte]);
  }
  return $ACCS;
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
function getMvtsCpteClientParDates($id_client, $id_cpte, $date_debut, $date_fin) {
  global $global_id_agence;
  $result = executeDirectQuery("SELECT a.id_his,a.infos,type_fonction,date,libel_ecriture,montant,sens,type_operation,info_ecriture FROM ad_his a, ad_ecriture b, ad_mouvement c WHERE (a.id_ag=b.id_ag) and (b.id_ag=c.id_ag) and (a.id_ag=$global_id_agence) AND date(a.date) BETWEEN '$date_debut' AND '$date_fin' AND c.cpte_interne_cli = $id_cpte AND a.id_his = b.id_his AND c.id_ecriture = b.id_ecriture ORDER BY b.id_ecriture DESC");
  if ($result->errCode == NO_ERR) {
    return($result->param);
  } else {
    return(NULL);
  }
}

/**
 * Renvoie les n derniers mouvements de l'historique du client
 *
 * @param int $id_client Identifiant client
 * @param int $id_cpte Identifiant compte
 * @param int $numero Nombre de mouvements à rechercher (n)
 * @access public
 * @return array La liste des mouvements recherchés
 */
function getMvtsCpteClientParNumero($id_client, $id_cpte, $numero) {
  global $global_id_agence;
  $sql = "SELECT * FROM ad_his a, ad_ecriture b, ad_mouvement c ";
  $sql .= "WHERE (a.id_ag = b.id_ag) and (b.id_ag = c.id_ag) and (a.id_ag = $global_id_agence) AND c.cpte_interne_cli = $id_cpte AND a.id_his = b.id_his AND c.id_ecriture = b.id_ecriture ";
 
  $sql .= "ORDER BY b.id_ecriture DESC LIMIT $numero";
  $result = executeDirectQuery($sql);

  if ($result->errCode == NO_ERR) {
    return($result->param);
  } else {
    return(NULL);
  }
}

/**
 * Renvoie la date de création d'un compte
 * @param int $id_cpte Identifiant compte
 * @access public
 * @return DATE $date_ouvert La date de création du compte des mouvements recherchés
 */
function getDateCreationCompte($id_cpte) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT date_ouvert FROM ad_cpt ";
  $sql .= "WHERE id_cpte = $id_cpte AND id_ag = $global_id_agence ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $retour = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $retour[0];
}

/**
 * Effectue un transfert groupé à parti d'un compte vers d'autres comptes
 * Il y a donc un seul débit pour n crédits
 * Les frais de transfert sont prélevés soit une seule fois dans le compte source soit dans chaque compte de destination
 * Par contre il y a n+1 extraits de comptes générés
 * @author Mamadou Mbaye
 * @param int $id_cpte_source ID du compte source
 * @param Array $VAR Liste des comptes à créditeur avec leurs montants
 * @param float $frais_transfert montant des frais si le compte source paie les frais de transfet
 * @return ErorObj Objet Erreur
 */
function transfertCpteGroupe($id_cpte_source, $VAR, $id_mandat, $data_transfert, $frais_transfert=NULL,$delai_retrait = false) {

  global $dbHandler;
  global $global_id_client, $global_nom_login, $global_id_agence, $global_monnaie;

  $db = $dbHandler->openConnection();

  // Vérifier que le dépôt est possible sur tous les comptes de destination, et calculer le montant total du transfert
  $montant_total = 0;
  foreach($VAR as $key => $value) {
    $InfoCpteDestination = getAccountDatas($value["id_cpte"]);
    $InfoProduitDestination = getProdEpargne($InfoCpteDestination["id_prod"]);
    $montant = $value["mnt_dest"];
    $erreur = CheckDepot($InfoCpteDestination, $montant);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    $montant_total += $value["mnt_src"];
  }

  // Récupération des données du compte source et de son produit associé
  $InfoCpteSource = getAccountDatas($id_cpte_source);
  $InfoProduitSource = getProdEpargne($InfoCpteSource["id_prod"]);

  // Récupérer les frais éventuellement modifiés
  if (isset($frais_transfert))
    $InfoProduitSource["frais_transfert"] = $frais_transfert;


  // Vérifier que le retrait est possible sur le compte source
  $erreur = CheckRetrait($InfoCpteSource, $InfoProduitSource, $montant_total, 1, $id_mandat,$delai_retrait);
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  // Passage des écritures comptables
  $comptable = array();

  // Si le compte source paie les frais, les prélever une seule fois
  if ($frais_transfert > 0) {
    $type_oper = 152;
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    // Débit du compte source pour chaque transfert et éventuellement le paiement des frais
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["debit"] = $id_cpte_source;

    // si la devise des frais n'est pas la même que la devise de référence, faire le change
    if ($InfoCpteSource['devise'] != $global_monnaie)
      $myErr = effectueChangePrivate($InfoCpteSource['devise'], $global_monnaie, $frais_transfert,
                                     $type_oper, $cptes_substitue, $comptable);
    else
      $myErr = passageEcrituresComptablesAuto($type_oper, $frais_transfert, $comptable, $cptes_substitue);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  // Prélèvement des frais minimum de la duree entre deux retraits non_respecter
  if ($delai_retrait == true) {
    if ($InfoProduitSource['frais_duree_min2retrait'] > 0) {
      $type_oper = 158;
      // Passage des écritures comptables : débit client
      $cptes_substitue = array();
      $cptes_substitue["cpta"] = array();
      $cptes_substitue["int"] = array();

      // si le compte de destination paie les frais
      /*if ($cpte_preleve == $id_cpte_destination) {
        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_destination);
        $cptes_substitue["int"]["debit"] = $id_cpte_destination;
        $devise_frais = $InfoCpteDestination['devise'];
      } else { // le compte source du transfert paie les frais*/
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
      $cptes_substitue["int"]["debit"] = $id_cpte_source;
      $devise_frais = $InfoCpteSource['devise'];
      //}

      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }

      // si la devise des frais n'est pas la même que la devise de référence, faire le change
      if ($devise_frais != $global_monnaie)
        $myErr = effectueChangePrivate($devise_frais, $global_monnaie, $InfoProduitSource['frais_duree_min2retrait'], $type_oper, $cptes_substitue, $comptable);
      else
        $myErr = passageEcrituresComptablesAuto($type_oper, $InfoProduitSource['frais_duree_min2retrait'], $comptable, $cptes_substitue);

      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
  }

  // Transferts vers les comptes destination
  foreach($VAR as $key => $value) {

    // Débit du compte source pour chaque transfert et éventuellement le paiement des frais
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["debit"] = $id_cpte_source;

    // Crédit du compte de destination
    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($value["id_cpte"]);
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["credit"] = $value["id_cpte"];

      // information suplémentaire lors des transferts sur les numero des comptes source et destination
      $infoEcritures = $InfoCpteSource['num_complet_cpte']."|".$value['num_complet_cpte'];

    // Si la devise du compte source n'est pas la même que celle du compte de destination
    if ($InfoCpteSource['devise'] != $value['devise'])
      $myErr = effectueChangePrivate($InfoCpteSource['devise'], $value['devise'], $value["mnt_src"],
                                     120, $cptes_substitue, $comptable);
    else
      $myErr = passageEcrituresComptablesAuto(120, $value["mnt_dest"], $comptable, $cptes_substitue,null,null,$infoEcritures);//la variable infoEcriture contient les numero des comptes de la transaction

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    // Si les comptes de destination paient les frais de transfert
    if ($value['mnt_frais'] > 0) {
      $type_oper = 152;
      // débit du compte de destination
      $cptes_substitue["cpta"] = array();
      $cptes_substitue["int"] = array();
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($value["id_cpte"]);
      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }
      $cptes_substitue["int"]["debit"] = $value["id_cpte"];

      // si la devise des frais n'est pas la même que la devise de référence, faire le change
      if ($value['devise'] != $global_monnaie)
        $myErr = effectueChangePrivate($value['devise'], $global_monnaie, $value['mnt_frais'],
                                       $type_oper, $cptes_substitue, $comptable);
      else
        $myErr = passageEcrituresComptablesAuto($type_oper, $value['mnt_frais'], $comptable, $cptes_substitue);

      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }

    }

  }

  if ($id_mandat != NULL && $id_mandat != "" && $id_mandat != 'CONJ') {
    $MANDAT = getInfosMandat($id_mandat);
    $data_transfert['id_pers_ext'] = $MANDAT['id_pers_ext'];
  }

  if ($data_transfert != NULL) {
    $data_his_ext = creationHistoriqueExterieur($data_transfert);
  } else {
    $data_his_ext = NULL;
  }

  $myErr = ajout_historique(76, $InfoCpteSource["id_titulaire"],'', $global_nom_login, date("r"), $comptable, $data_his_ext);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);
  return $myErr;
}

/**
 * Transfère un montant d'un compte vers un autre à l'intérieur de l'institution
 * @author Bernard de Bois
 * @param int $id_cpte_source Compte source
 * @param int $id_cptedestination Compte destination
 * @param float $montant Montant à tansférer
 * @param float $montant_frais_transfert Montant des frais de transfert
 * @param array $CHANGE Tableau avec toutes les infos en cas de change
 * @param array $data_virement Données concernant la pièce justificative si transfert extérieur
 * @param array $DATA_SWIFT Pour transfert NetBank ??
 * @param int $cpte_preleve identifiant du compte de prélèvement des frais de transfert
 * @param array $a_his_compta Historique des mouvements comptables si mouvements précédents (c-à-d batch)
 * @return ErorObj Objet Erreur
 */
function transfertCpteClient($id_cpte_source, $id_cpte_destination, $montant, $id_mandat, $montant_frais_transfert=NULL, $CHANGE=NULL, $data_virement=NULL, $DATA_SWIFT=NULL, $cpte_preleve=NULL, &$a_his_compta=NULL, $test_delai = false,$data_cheque_benef = NULL,$dure_mini_retrait = false) {
  global $dbHandler;
  global $global_id_client, $global_nom_login, $global_id_agence, $global_monnaie;
  $comptable = array();
   // On veut pouvoir commit ou rollback toute la procédure
  $db = $dbHandler->openConnection();

  // Infos compte source
  $InfoCpteSource = getAccountDatas($id_cpte_source);
  $InfoProduitSource = getProdEpargne($InfoCpteSource["id_prod"]);
  if (isset($montant_frais_transfert))
    $InfoCpteSource['frais_transfert'] =  $montant_frais_transfert;

  // Infos compte destination
  $InfoCpteDestination = getAccountDatas($id_cpte_destination);
  $InfoProduitDestination = getProdEpargne($InfoCpteDestination["id_prod"]);

  // D'abord vérifier qu'on peut retirer du compte source
  $erreur = CheckRetrait($InfoCpteSource, $InfoProduitSource, $montant, 1, $id_mandat, $test_delai);
  if ($erreur->errCode != NO_ERR)  {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  // Ensuite vérifier qu'on peut déposer sur le compte destination
  $erreur = CheckDepot($InfoCpteDestination, $montant);
  if ($erreur->errCode != NO_ERR)  {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  // Si le compte de destination paie les frais, vérifier que le retrait est autorisé dans ce compte
  if ($cpte_preleve == $id_cpte_destination) {
    $erreur = CheckRetrait($InfoCpteDestination, $InfoProduitDestination, $montant_frais_transfert, NULL, NULL, $test_delai);
    if ($erreur->errCode != NO_ERR)  {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
  }


  $type_operation_trans = 120;

  //Vérification si une transaction par chèque a été effectuée
  if(!is_null($data_cheque_benef)){

    $num_cheque = trim($data_virement['num_piece']);
    // Vérification si le chèque est valide
    $result = valideCheque($num_cheque,$id_cpte_source);

    if ($result->errCode != NO_ERR) {
      return $result;
    }

    $isChequeCertifie = ChequeCertifie :: isChequeCertifie($num_cheque,ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TOUS);

    //Vérification si le chèque est certifié
    if($isChequeCertifie){

      $isChequeTraite = ChequeCertifie :: isChequeCertifie($num_cheque,ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TRAITE);

      //si le chèque est traité ne pas continué
      if($isChequeTraite){
        return new ErrorObj(ERR_CHEQUE_USE);
      }
    }
    $type_operation_trans = 534;
  }

  //transaction normal
  if(!$isChequeCertifie) {
    // Passage de l'écriture comptable du transfert
    // Passage des écritures comptables : débit client / crédit client
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

  //débit d'un client par le crédit d'un autre client
  $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
  if ($cptes_substitue["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["debit"] = $id_cpte_source;
  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_destination);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["credit"] = $id_cpte_destination;

    // information suplémentaire lors des transferts sur les numero des comptes source et destination
    $infoEcriture=$InfoCpteSource['num_complet_cpte']."|".$InfoCpteDestination['num_complet_cpte'];

    if ($InfoCpteSource['devise'] != $InfoCpteDestination['devise']) {
      if (empty($CHANGE)) {
        // On prend les valeurs du moment
        $CHANGE['cv'] = calculeCV($InfoCpteSource['devise'], $InfoCpteDestination['devise'], $montant);
        $CHANGE['dest_reste'] = 2; // reste sur compte de base
        $CHANGE['comm_nette'] = NULL; // calculé automatiquement
        $CHANGE['taux'] = NULL; // calculé automatiquement
      }
      $myErr = change($InfoCpteSource['devise'], $InfoCpteDestination['devise'], $montant, $CHANGE['cv'], $type_operation_trans, $cptes_substitue, $comptable, $CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE['taux']);
    } else {
      $myErr = passageEcrituresComptablesAuto($type_operation_trans, $montant, $comptable, $cptes_substitue, $InfoCpteSource['devise'], NULL, $infoEcriture); //la variable infoEcriture contients les numero cmpt source et destination de la transaction
    }

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }
  else{

    $type_operation_trans = 533;
    $EPG_CHQ_CERTIF = ChequeCertifie::getProdEpargneChequeCertifie();
    $INFO_CHQ = ChequeCertifie::getChequeCertifie($num_cheque, $id_cpte_source);

    // Passage de l'écriture de chèque certifié
    $comptable = array(); // Mouvements comptable
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    // Débit compte de prélèvement / Crédit compte chèque certifié
    $cptes_substitue["cpta"]["debit"] = $EPG_CHQ_CERTIF["cpte_cpta_prod_ep_int"];
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au chèque"));
    }
    $cptes_substitue["int"]["debit"] = $INFO_CHQ['num_cpte_cheque'];

    $cptes_substitue["cpta"]["credit"] =  getCompteCptaProdEp($id_cpte_destination);
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["credit"] = $id_cpte_destination;

    $myErr = passageEcrituresComptablesAuto($type_operation_trans, $montant, $comptable, $cptes_substitue, $InfoCpteSource['devise']);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  // Prélèvement des frais de transfert : débit du compte de prélèvement
  if ($montant_frais_transfert > 0) {
    $type_oper = 152;
    // Passage des écritures comptables : débit client
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    // si le compte de destination paie les frais
    if ($cpte_preleve == $id_cpte_destination) {
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_destination);
      $cptes_substitue["int"]["debit"] = $id_cpte_destination;
      $devise_frais = $InfoCpteDestination['devise'];
    } else { // le compte source du transfert paie les frais
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
      $cptes_substitue["int"]["debit"] = $id_cpte_source;
      $devise_frais = $InfoCpteSource['devise'];
    }

    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    // si la devise des frais n'est pas la même que la devise de référence, faire le change
    if ($devise_frais != $global_monnaie)
      $myErr = effectueChangePrivate($devise_frais,$global_monnaie, $montant_frais_transfert, $type_oper, $cptes_substitue, $comptable);
    else
      $myErr = passageEcrituresComptablesAuto($type_oper, $montant_frais_transfert, $comptable, $cptes_substitue);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  // Prélèvement des frais minimum de la duree entre deux retraits non_respecter
  if ($dure_mini_retrait == true) {
    if ($InfoProduitSource['frais_duree_min2retrait'] > 0) {
      $type_oper = 158;
      // Passage des écritures comptables : débit client
      $cptes_substitue = array();
      $cptes_substitue["cpta"] = array();
      $cptes_substitue["int"] = array();

      // si le compte de destination paie les frais
      /*if ($cpte_preleve == $id_cpte_destination) {
        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_destination);
        $cptes_substitue["int"]["debit"] = $id_cpte_destination;
        $devise_frais = $InfoCpteDestination['devise'];
      } else { // le compte source du transfert paie les frais*/
        $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
        $cptes_substitue["int"]["debit"] = $id_cpte_source;
        $devise_frais = $InfoCpteSource['devise'];
      //}

      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }

      // si la devise des frais n'est pas la même que la devise de référence, faire le change
      if ($devise_frais != $global_monnaie)
        $myErr = effectueChangePrivate($devise_frais, $global_monnaie, $InfoProduitSource['frais_duree_min2retrait'], $type_oper, $cptes_substitue, $comptable);
      else
        $myErr = passageEcrituresComptablesAuto($type_oper, $InfoProduitSource['frais_duree_min2retrait'], $comptable, $cptes_substitue);

      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
  }


  $myErr = preleveFraisDecouvert($id_cpte_source, $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($id_mandat != NULL && $id_mandat != 'CONJ') {
    $MANDAT = getInfosMandat($id_mandat);
    $data_virement['id_pers_ext'] = $MANDAT['id_pers_ext'];
  }

  if ($data_virement != NULL) {
    $data_his_ext = creationHistoriqueExterieur($data_virement);
  } else {
    $data_his_ext = NULL;
  }
  if(!is_null($data_cheque_benef)) {
    $id = insere_tireur_benef($data_cheque_benef);
 	$data_his_ext['id_tireur_benef']=$id;
 	$data_ch['id_cheque']=$data_virement['num_piece'];
	$data_ch['date_paiement']=$data_virement['date_piece'];
	$data_ch['etat_cheque']=$isChequeCertifie?4:1;
	$data_ch['id_benef'] =$id; 
	$rep=insertCheque($data_ch,$id_cpte_source);
	if ($rep->errCode != NO_ERR ) {
		$dbHandler->closeConnection(false);
		return $rep;
	}
  }

  if($isChequeCertifie){

    // Mettre à jour le statut d'un chèque certifié à Traité
    $erreur = ChequeCertifie::updateChequeCertifieToTraite($num_cheque, $id_cpte_source, $INFO_CHQ['num_cpte_cheque'], "Retrait chèque interne certifié No. ". $num_cheque);

    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
    $int_debit=$INFO_CHQ['num_cpte_cheque'];

    // Fermeture du compte de chèque certifié
    $sql = "UPDATE ad_cpt SET etat_cpte = 2 WHERE id_ag = $global_id_agence AND id_cpte = $int_debit;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }

  }

  /* Mise à jour des données de traitement de l'ordre de virement */
  if ($DATA_SWIFT != NULL) {
    if ( $DATA_SWIFT["type"] == 1)
      updateSwiftDomestique($DATA_SWIFT["id"], $DATA_SWIFT["statut"], $DATA_SWIFT["$mess_err"],$DATA_SWIFT["cpte_don"],$DATA_SWIFT["cpte_ben"]);
    else if ( $DATA_SWIFT["type"] == 2)
      updateSwiftEtranger($DATA_SWIFT["id"], $DATA_SWIFT["statut"], $DATA_SWIFT["$mess_err"],$DATA_SWIFT["cpte_don"],$DATA_SWIFT["cpte_ben"]);
  }

  if (is_array($a_his_compta )) {
  	// On a déjà un historique comptable, on y ajoute les mouvements du transfert
    $a_his_compta = array_merge($a_his_compta, $comptable);
  } else {
  	// Si on n'a pas passé d'historique comptable, c'est qu'on n'est pas appelé par le batch, il faut donc ajouter à l'historique
    $myErr = ajout_historique(76, $InfoCpteSource["id_titulaire"],'', $global_nom_login, date("r"), $comptable, $data_his_ext);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  $dbHandler->closeConnection(true);
  return $myErr;
}

/**
 * Effectue la cloture d'un compte à la demande de l'utilisateur
 * @param Cfr {@link #clotureCompteEpargne}
 * @return ErrorObj Objet Error avec en paramètre le montant du solde de cloture
 */
function cloture_cpte_interface($id_cpte, $raison_cloture, $dest, $id_cpte_dest, $frais, $data_ext,$interet=null) {
  global $dbHandler, $global_id_client, $global_nom_login;

  $comptable = array();

  $db = $dbHandler->openConnection();

  $erreur = clotureCompteEpargne($id_cpte, $raison_cloture, $dest, $id_cpte_dest, $comptable, $frais);
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  if ($data_ext != NULL) {
    $data_his_ext = creationHistoriqueExterieur($data_ext);
  } else {
    $data_his_ext = NULL;
  }

  // Fonction qui prélève les impot sur les DAT arrivant à échéance.
  $myErr1 = prelevementTaxDat($id_cpte,$interet,$comptable);

  if ($myErr1->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr1;
  }

  $myErr = ajout_historique(54, $global_id_client,$id_cpte, $global_nom_login, date("r"), $comptable, $data_his_ext);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $id_his = $myErr->param;

  //REL-101 : recuperation id_ecriture_reprise pour la mise a jour correcte de la table ad_calc_int_paye_his
  $id_ecri_reprise=recupIdEcritureRepriseIAP($id_cpte,$id_his);

  //#356 : update calcul interets
  $myErr2 = clotureIntCalcCpteEpargne($id_cpte, date("r"), $id_his, $id_ecri_reprise);

  if ($myErr2->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr2;
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array("id_his" => $id_his));

}

/**
 * Vérifie si le compte $id_cpte peut etre cloturé
 * Un compte peut etre cloturé si
 *  - il n'est pas bloqu"
 *  - il n'est pas dormant"
 *  - il n'est pas lié à un crédit non soldé
 *  - il n'est pas compte de prélèvement d'une quelconque garantie pour un crédit non soldé
 *  - il ne s'agit pas du compte de base
 */
function checkCloture($id_cpte) {

  global $dbHandler,$global_id_agence;

  $InfoCpte = getAccountDatas($id_cpte);

  //vérifier qu'il ne s'agit pas du compte de base
  $id_cpte_base =  getBaseAccountID ($InfoCpte["id_titulaire"]);

  if ($id_cpte == $id_cpte_base) {
    return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);
  }

  if ($InfoCpte["etat_cpte"] == 3) {
    return new ErrorObj(ERR_CPTE_BLOQUE);
  }
  
  if ($InfoCpte["etat_cpte"] == 4)
    return new ErrorObj(ERR_CPTE_DORMANT);

  $db = $dbHandler->openConnection();

  // Vérifier si ce compte n'est pas un compte de prélèvement d'une garantie bloquée
  $sql = "SELECT count(*) FROM ad_gar WHERE id_ag=$global_id_agence AND gar_num_id_cpte_prelev = $id_cpte AND etat_gar IN (1,2)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $tmprow = $result->fetchrow();

  if ($tmprow[0] > 0) { // Il y a au moins un crédit ou compte de garanties lié
    $dbHandler->closeConnection(true);
    return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);
  }

  /* Vérifié si le compte n'est pas un compte de liaison d'un crédit en cours */
  $sql = "SELECT count(*) FROM ad_dcr WHERE id_ag=$global_id_agence AND etat IN (1,2,5,7,14,15) AND cpt_liaison = $id_cpte";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $tmprow = $result->fetchrow();

  $dbHandler->closeConnection(true);

  if ($tmprow[0] > 0) // Il y a au moins un crédit ou compte de garanties lié
    return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);

  return new ErrorObj(NO_ERR);
}

/**
 * Vérifie si une cloture est possible pour le compte mentionné
 * @param int $id_cpte ID du compte à cloturer
 * @param &array $infos_cloture : contient le solde après cloture, les intérêts à verser, les frais et les pénalités à prélever
 * @return ErrorObj
 */
function autoriseCloture($id_cpte, &$infos_cloture) {
  $InfoCpte = getAccountDatas($id_cpte);

  //vérifier qu'il ne s'agit pas du compte de base
  $id_cpte_base =  getBaseAccountID ($InfoCpte["id_titulaire"]);

  if ($id_cpte == $id_cpte_base)
    return new ErrorObj(ERR_CLOTURE_NON_AUTORISEE);

  if ($InfoCpte["etat_cpte"] == 3)
    return new ErrorObj(ERR_CPTE_BLOQUE);
    
   if ($InfoCpte["etat_cpte"] == 4)
    return new ErrorObj(ERR_CPTE_DORMANT);

  $infos_cloture = simulationArrete($id_cpte);
  //$solde_cloture = $infos_simul["solde_cloture"];

  if ($infos_cloture["solde_cloture"] < 0)
    return new ErrorObj(ERR_SOLDE_INSUFFISANT);

  return new ErrorObj(NO_ERR);
}

//#365:Param $int en entré si le calcule est fait avec les intérets générés à la base
function calculPenalites($id_cpte, $solde,$int=NULL) {
  /*
  Calcul des pénalités sur un compte à terme non échu
  */
  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  $devise = $InfoCpte["devise"];
  $DEV = getInfoDevise($devise);
  if($InfoProduit["mode_calcul_penal_rupt"] == 1) {
    //si le param $int est renseigné, le calcule se fait à partir des intérets générés au lieu du capital.
  $penalites = round(($InfoProduit["penalite_const"] + ((isset($int)?$int:$solde) * $InfoProduit["penalite_prop"])),$DEV["precision"]);
  }
  elseif($InfoProduit["mode_calcul_penal_rupt"] == 2) {
  $cpte_date_fin = $InfoCpte["dat_date_fin"];
  $cpte_date_ouvert = $InfoCpte["date_ouvert"];

  $today = date("d/m/Y");
  $today = explode("/", $today);
  $today = mktime(0,0,0,$today[1],$today[0],$today[2]);

  $date_fin = pg2phpDate($cpte_date_fin);
  $date_fin = explode("/", $date_fin);
  $date_fin = mktime(0,0,0,$date_fin[1],$date_fin[0],$date_fin[2]);

  $date_ouvert = pg2phpDate($cpte_date_ouvert);
  $date_ouvert = explode("/",$date_ouvert);
  $date_ouvert = mktime(0,0,0,$date_ouvert[1],$date_ouvert[0],$date_ouvert[2]);

  if ($date_fin > $today) {
    $jours_restant = ($date_fin - $today)/(60*60*24);
    $duree_totale_epargne = ($date_fin - $date_ouvert)/(60*60*24);
  }
    //si le param $int est renseigné, le calcule se fait à partir des intérets générés au lieu du capital.
  $penalites = round(($InfoProduit["penalite_const"] + ((isset($int)?$int:$solde)  * $InfoProduit["penalite_prop"] * $jours_restant)/($duree_totale_epargne)),$DEV["precision"]);
  }
  else $penalites = 0;
  return $penalites;
}

/**********
 * Calcule le solde de cloture d'un compte d'épargne s'il était fermé maitnenant
 * @author
 * @since 1.0
 * @param int $id_cpte : l'ID du compte à fermer
 * @param numeric $frais_fermeture : les frais de fermeture à prélever
 * @param numeric $penalites : les pénalités à prélever
 * @return array $infos_simulation : tableau contenant : <UL>
 *   <LI> Le solde à la fermeture </LI>
 *   <LI> Les intérêts versés sur le compte </LI>
 *   <LI> Les frais de fermeture prélevés sur le compte </LI>
 *   <LI> Les frais de tenue de compte prélevés sur le compte </LI>
 *   <LI> Les pénalités de fermeture prélevés sur le compte </LI> </UL>
 */
function simulationArrete($id_cpte, $frais_fermeture=NULL, $penalites=NULL, $frais_tenue=NULL) {
  global $dbHandler, $global_id_agence;

  /* Initialisation des données */
  $infos_simulation = array();
  $infos_simulation["interets"] = 0 ;

  if ($frais_fermeture == NULL)
    $infos_simulation["frais_fermeture"] = 0;
  else
    $infos_simulation["frais_fermeture"] = $frais_fermeture;

  if ($penalites == NULL)
    $infos_simulation["penalites"] = 0;
  else
    $infos_simulation["penalites"] = $penalites;

  if ($frais_tenue == NULL)
    $infos_simulation["frais_tenue"] = 0;
  else
    $infos_simulation["frais_tenue"] = $frais_tenue;

  /* Récupération des infos sur le compte */
  $InfoCpte = getAccountDatas($id_cpte);

  /* Récupération des infos sur le produit associé au compte */
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  /* Récupération du taux de base de l'épargne de l'agence */
  $AG = getAgenceDatas($global_id_agence);
  if ($AG["base_taux_epargne"] == 1)
    $base_taux = 360;
  elseif($AG["base_taux_epargne"] == 2)
  $base_taux = 365;

  /* Initialisation du solde de clôture */
  $infos_simulation["solde_cloture"] = $InfoCpte["solde"];

  // Si le compte est en attente de fermeture, son solde après arrêté est = à son solde courant
  if ($InfoCpte["etat_cpte"] == 5)
    return $infos_simulation;

  /* Si compte à terme (DAT ou CAT), à ce stade on est sûr que c'est une rupture anticipée. Calculer donc les intérêts à la rupture */
  if ($InfoCpte["terme_cpte"] > 0) {
    if ($InfoProduit["mode_calcul_int_rupt"] == 1)  /* 'Aucun intérêt à la rupture' */
      $infos_simulation["interets"] = 0;
    elseif ($InfoProduit["mode_calcul_int_rupt"] == 2 or $InfoProduit["mode_calcul_int_rupt"] == 3) {
      /* 'Intérêts au prorata' ou 'Intérêts pour le reste du terme' */
      $date_ouv = $InfoCpte["date_ouvert"];
      $date_las_cap = $InfoCpte["date_calcul_interets"];
      $mode_paie = $InfoCpte["mode_paiement_cpte"];
      $freq_cap = $InfoCpte["freq_calcul_int_cpte"];
      $terme = $InfoCpte["terme_cpte"];

      /* au prorata, récupérer nombre de mois entre date du jour et dernière capitalisation (ou date ouverture si jamais de capita)*/
      if ($InfoProduit["mode_calcul_int_rupt"] == 2)
        $date_cap = date("d/m/Y");

      /* Intérêts pour le reste du terme, récupérer nombre de mois entre date fin du compte  et dernière capitalisation  */
      if ($InfoProduit["mode_calcul_int_rupt"] == 3)
        if (isset($InfoCpte["dat_date_fin"]))
          $date_cap = $InfoCpte["dat_date_fin"];
        else
          $date_cap = date("d/m/Y");

      $nb_jours = getJoursCapitalisation($date_cap, $date_ouv, $date_las_cap, pg2phpDate($InfoCpte["dat_date_fin"]));

      //Jira MAE-22/27 formule calcul interet
      if ($AG['appl_date_val_classique'] == 't'){
        /* Calcul des intérêts à payer à la rupture */
        $infos_simulation["interets"] = ($InfoCpte['solde_calcul_interets'] * $InfoCpte["tx_interet_cpte"] * $nb_jours )/ $base_taux;
      }
      else{
        /* Calcul des intérêts à payer à la rupture */
        $infos_simulation["interets"] = ($InfoCpte['solde_calcul_interets'] * $InfoCpte["tx_interet_cpte"] * $nb_jours )/ $base_taux;
       if($InfoCpte['mode_calcul_int_cpte'] == 12){// Si mode épargne à la source, intérêts prend la valeur du champs interet_a_capitaliser qui cumule les intérêts entre deux dates de capitalisation
          $infos_simulation["interets"] = $InfoCpte['interet_a_capitaliser'];
        }
      }
    } /* Fin intérêts au prorata ou pour le reste du terme */

    /* Pour une cloture,les intérêts sont versés dans le compte lui-mêmede */
    $infos_simulation["solde_cloture"] += $infos_simulation["interets"];


    if ($InfoProduit["calcul_pen_int"] == 2) {
        $calc_pen_int = true;
    }

    $int=$infos_simulation["interets"];

    /* Calcul des pénalités de rupture anticipée */
    if (!isset($penalites)) {
      if (($InfoProduit["penalite_const"] > 0) || ($InfoProduit["penalite_prop"] > 0))
        $infos_simulation["penalites"] = calculPenalites($id_cpte, $infos_simulation["solde_cloture"],($calc_pen_int==true?$int:null));
    }

  } /* Fin si compte à terme */
  else
    $infos_simulation["penalites"] = 0; /* Pas de pénalités de rupture pour les autres type de compte */

  if ($infos_simulation["penalites"] > 0)
    $infos_simulation["solde_cloture"] -= $infos_simulation["penalites"];

  /* Prélèvement des frais de fermeture */
  if (!isset($frais_fermeture)) /* Si les frais ne sont pas renseignés, prendre par défaut les frais paramétrés dans le produit */
    $infos_simulation["frais_fermeture"] = $InfoProduit["frais_fermeture_cpt"];

  if ($infos_simulation["frais_fermeture"] > 0)
    $infos_simulation["solde_cloture"] -= $infos_simulation["frais_fermeture"];

  /* Si les frais de tenue ne sont pas renseignés, prendre par défaut les frais paramétrés dans le produit */
  if (!isset($frais_tenue))
    $infos_simulation["frais_tenue"] = $InfoProduit["frais_tenue_cpt"];

  if ($infos_simulation["frais_tenue"] > 0)
    $infos_simulation["solde_cloture"] -= $infos_simulation["frais_tenue"];

  if ($infos_simulation["frais_fermeture"] > 0){
    $type_operation_tva = 60;
    $taxesOperation = getTaxesOperation($type_operation_tva);
    $details_taxesOperation = $taxesOperation->param;
    if(sizeof($details_taxesOperation)>0){
      $tvaFraisfermeture = $infos_simulation["frais_fermeture"] * $details_taxesOperation[1]['taux'];
      $tvaFraisfermeture = arrondiMonnaiePrecision($tvaFraisfermeture,$InfoCpte['devise']);
    }
  }

  if ($infos_simulation["frais_tenue"] > 0){
    $type_operation_tva = 50;
    $taxesOperation = getTaxesOperation($type_operation_tva);
    $details_taxesOperation = $taxesOperation->param;
    if(sizeof($details_taxesOperation)>0){
      $tvaFraistenue = $infos_simulation["frais_tenue"] * $details_taxesOperation[1]['taux'];
      $tvaFraistenue = arrondiMonnaiePrecision($tvaFraistenue,$InfoCpte['devise']);
    }
  }

  if ($tvaFraisfermeture > 0){
    $infos_simulation["solde_cloture"] -= $tvaFraisfermeture;
  }

  if ($tvaFraistenue > 0){
    $infos_simulation["solde_cloture"] -= $tvaFraistenue;
  }

  /* Arrondi du solde de clôture */
  $DEV = getInfoDevise($InfoCpte['devise']);
  $infos_simulation["solde_cloture"] = round($infos_simulation["solde_cloture"], $DEV["precision"]);

  return $infos_simulation;
}

function getPeriode($freq_calcul_interet) {
  /*
    Renvoie le nombre de jours écoulés depuis le début d'une période
  FIXME : cette fonction doit plutôt calculer pour un compte donné le nombre de jours à
  rémunérer. En fait, nous avons supposé que la rémunération est de 30 jours pour tous les
  produits mensuels, l'exception étant pour le premier mois d'ouverture du compte. A clarifier
  */

  switch ($freq_calcul_interet) {
  case 1://mensuelle
    //FIXME : apparemment, il y a un bug : ne doit-on pas faire fin du mois - date
    //actuelle pour connaître le nombre de jours écoulés entre le jour d'ouverture
    //du compte et la fin du mois ?
    //si on a une base taux de 360 jours, on fait 30-date ouvert compte
    //si on a une base taux 365 jours, il faut tenir compte du vrai jour de fin de mois
    $nb_jour = (int)date("d");//nb de jours depuis le début du mois
    break;
  case 2://trimestrielle
    //      $trimestre = ceil(date("n",mktime(0,0,0, date("n"), date("d"), date("Y") )) / 3);
    $trimestre = ceil(date("n") / 3);//déterminer le trimestre dans lequel on se trouve
    switch ($trimestre) {
    case 1://on n' a pas fini le premier trimestre
      $nb_jour = (mktime(0,0,0, date("m"), date("d"), date("Y")) - mktime(0,0,0, 01, 00, date("Y"))) / (3600 *24);//on prend day=00 pour avoir le dernier jour du mois précédent
      break;
    case 2:
      $nb_jour = (mktime(0,0,0, date("m"), date("d"), date("Y")) - mktime(0,0,0, 04, 00, date("Y"))) / (3600 *24);
      break;
    case 3:
      $nb_jour = (mktime(0,0,0, date("m"), date("d"), date("Y")) - mktime(0,0,0, 07, 00, date("Y"))) / (3600 *24);
      break;
    case 4:
      $nb_jour = (mktime(0,0,0, date("m"), date("d"), date("Y")) - mktime(0,0,0, 10, 00, date("Y"))) / (3600 *24);
      break;
    }
    break;
  case 3://semestrielle
    // $semestre = ceil(date("n",mktime(0,0,0, date("n")+5, date("d"), date("Y") )) / 6);
    $semestre = ceil(date("n",mktime(0,0,0, date("n")+5, date("d"), date("Y") )) / 6);//déterminer le trimestre dans lequel on se trouve
    switch ($semestre) {
    case 1://on n' a pas fini le premier semestre
      $nb_jour = (mktime(0,0,0, date("m"), date("d"), date("Y")) - mktime(0,0,0, 01, 00, date("Y"))) / (3600 *24);
      break;
    case 2:
      $nb_jour = (mktime(0,0,0, date("m"), date("d"), date("Y")) - mktime(0,0,0, 07, 00, date("Y"))) / (3600 *24);
    }
    break;
  case 4://annuelle
    $nb_jour = (mktime(0,0,0, date("m"), date("d"), date("Y")) - mktime(0,0,0, 01, 00, date("Y"))) / (3600 *24);
  }

  return $nb_jour;

}

/**
 * Cette fonction insère une attente dans la table Attente, après avoir vérifié que celle-ci n'avait pas déjà été encodée, en vérifiant s'il n'y a pas une attente avec les mêmes numéro, correspondant et date.
 * @author Bernard De Bois
 * @param array $data : tous les champs à insérer dans la table attentes_credit
 * @return ErrorObj Les erreurs possibles et s'il n'y a pas d'erreur, renvoie en paramètre l'identifiant de l'attente insérée.
 */
function insertAttente($data) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  //vérifier que le chèque n'a pas déjà été saisi
  $sql = "SELECT * FROM attentes where ";
  $sql .= " (num_piece= '".$data["num_piece"]."')";
  if ($data['type_piece']==5)//Si c'est un Travelers cheque, il n'y a pas de correspondant.
    $sql .= " AND (id_correspondant IS NULL";
  else
    $sql .= " AND (id_correspondant= ".$data["id_correspondant"];
  $sql .= ") AND (date_piece= date('".$data["date_piece"]."')) AND (id_ag=$global_id_agence) ;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() != 0) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_DUPLICATE_CHEQUE);
  }

  //insertion dans la table des attentes
  $data['id_ag']= $global_id_agence;
  $sql = buildInsertQuery ("attentes", $data);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  //on récupère le id du chèque qu'on vient d'insérer pour le mettre dans l'historique
  $sql = "select max(id) from attentes where id_ag=$global_id_agence ;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $tmprow = $result->fetchrow();
  $id_cheque = $tmprow[0];

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $id_cheque);
}

/**
 * Crée une attente manuelle. A DOCUMENTER
 * @author Bernard de Bois
 */
function attenteManuelle($data, $frais) {
  global $dbHandler, $global_monnaie, $global_id_guichet, $global_nom_login;

  $db = $dbHandler->openConnection();

  //on met à jour le champ banque du tireur/bénéficiaire concerné
  if ($data['type_piece']==6) { //mise à disposition
    $idTireur = $data['id_ext_ordre'];
  } else if ($data['type_piece']==7) { //envoi d'argent
    $idTireur = $data['id_ext_benef'];
  }
  $champBanque=array("id_banque" => $data['id_banque']);
  setBeneficiaire($data['id_ext_benef']);
  setTireur($data['id_ext_ordre']);
  $myErr = updateTireurBenef($idTireur, $champBanque);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $myErr = insertAttente($data);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($data['sens']=='out') { //envoi d'argent
    //débit du guichet par le crédit du compte d'attente du correspondant.
    $comptable = array();
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $operation=514;
    $comptesCompensation = getComptesCompensation($data['id_correspondant']);
    $cptes_substitue["cpta"]["credit"] = $comptesCompensation['credit'];
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($global_id_guichet);

    /* Arrondi du montant si opération au guichet*/
    $critere = array();
    $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["debit"];
    $cpte_gui = getComptesComptables($critere);
    $data["montant"] = arrondiMonnaie( $data["montant"], 0, $cpte_gui['devise'] );

    $myErr = passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $data['devise']);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  } else if ($data['sens']=='in ') { //mise à disposition
    //débit du compte bancaire du correspondant par le crédit du compte d'attente du correspondant.
    $comptable = array();
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $operation=521;
    $comptesCompensation = getComptesCompensation($data['id_correspondant']);
    $cptes_substitue["cpta"]["credit"] = $comptesCompensation['credit'];
    $cptes_substitue["cpta"]["debit"] = $comptesCompensation['compte'];

    $myErr = passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $data['devise']);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  } else signalErreur(__FILE__,__LINE__,__FUNCTION__);

  if (isset($frais) && $frais > 0) {
    //perception des frais de transfert
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    if ($data['sens']=='out') { //envoi d'argent
      $operation=515;
      $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($global_id_guichet);
    } else { //mise à disposition
      $operation=522;
      $cptes_substitue["cpta"]["credit"] = $comptesCompensation['compte'];
    }

    if ($data['devise']==$global_monnaie) {
      $myErr = passageEcrituresComptablesAuto($operation, $frais, $comptable, $cptes_substitue, $data['devise']);
    } else {
      if ($data['sens']=='in ') {
        $myErr = effectueChangePrivate($global_monnaie, $data['devise'],$frais,$operation,$cptes_substitue,$comptable, false);
      } else {
        $myErr = effectueChangePrivate($data['devise'],$global_monnaie, $frais,$operation,$cptes_substitue,$comptable, true);
      }
    }
  }

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  //création des données pour l'enregistrement de l'historique extérieur
  $data_his_ext_temp['id_ext_benef']    = $data['id_ext_benef'];
  $data_his_ext_temp['id_ext_ordre']    = $data['id_ext_ordre'];
  $data_his_ext_temp['communication']   = $data['communication'];
  $data_his_ext_temp['type_piece']      = $data['type_piece'];
  $data_his_ext_temp['remarque']        = $data['remarque'];
  $data_his_ext_temp['sens']            = $data['sens'];
  $data_his_ext_temp['num_piece']       = $data['num_piece'];
  $data_his_ext_temp['date_piece']      = $data['date_piece'];
  $data_his_ext = creationHistoriqueExterieur($data_his_ext_temp);

  $MyErr = ajout_historique(188, NULL, $data['num_piece'], $global_nom_login, date("r"), $comptable, $data_his_ext);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyErr;
  }


  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $MyErr->param);

}

/**
 * Cette fonction constate la réception d'un chèque ou d'une opération de paiement et le met éventuellement en attente.
 * @author Bernard De Bois
 * @param array $data : toutes les informations utiles à la mise à jour de la table attentes_credit
 * @param array $InfoCpte : Les champs du compte client
 * @param array $InfoProduit : les champs du produit d'épargne lié au compte client
 * @param float $mntCompte : Montant qui sera déposé sur le compte (en cas de crédit direct)
 * @param boolean $credit_direct : s'il est à TRUE, il y a un crédit direct sauf bonne fin. Il ne faut pas mettre le montant en attente, mais prélever des frais de commission
 * @param array $mnt_comm : montant de la commission si crédit direct sauf bonne fin.
 * @param array $CHANGE : toutes les données concernant le change
 * @return ErrorObj Les erreurs possibles
 */
function receptionCheque($data, $InfoCpte, $InfoProduit, $mntCompte, $credit_direct=false, $mnt_comm=NULL, $CHANGE=NULL, &$montant_remboursement = NULL) {
  global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_monnaie;

  $db = $dbHandler->openConnection();

  if ($data!=NULL)
    $data_his_ext = creationHistoriqueExterieur($data);
  else
    $data_his_ext = NULL;

  unset($data['id_pers_ext']);

  $comptable = array();

  //Check que le dépôt est possible sur le compte
  $erreur = CheckDepot($InfoCpte, $data["montant"]);
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  //On ajoute une attente (seulement s'il ne s'agit pas d'un crédit direct)
  if ($credit_direct) {

    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    //Mouvement des comptes "comptables" associés

    $operation='503';
    if (isset($CHANGE))
      $deviseCheque = $CHANGE['devise'];
    else
      $deviseCheque = $InfoCpte['devise'];
    if ($data['type_piece'] == 2) { // en cas de chèque on mouvemente les comptes de compensation
      $comptesCompensation = getComptesCompensation($data['id_correspondant']);
      $cptes_substitue["cpta"]["debit"] = $comptesCompensation['compte'];
    } else if ($data['type_piece'] == 5) { //en cas de Travelers Cheque, on mouvement le compte Travelers Cheque de l'agence
      $dataAgence = getAgenceDatas($global_id_agence);
      $cptes_substitue["cpta"]["debit"] = $dataAgence['num_cpte_tch'];
    }
    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($InfoCpte['id_cpte']);
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé aux Travelers Cheque (agence)"));
    }

    $cptes_substitue["int"]["credit"] = $InfoCpte['id_cpte'];
    $dev1 = $CHANGE['devise'];
    $dev2 = $InfoCpte['devise'];

    if (isset($CHANGE)) {
      $myErr = change($dev1, $dev2, $CHANGE["cv"], $mntCompte, $operation, $cptes_substitue, $comptable,$CHANGE["dest_reste"], $CHANGE["comm_nette"], $CHANGE["taux"]);
    } else {
      $myErr = passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $deviseCheque);
    }

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    //perception des frais liés au crédit direct sauf bonne fin
    if ($mnt_comm != NULL) {
        $montant_remboursement -= $mnt_comm;
      $cptes_substitue = array();
      $cptes_substitue["cpta"] = array();
      $cptes_substitue["int"] = array();
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($InfoCpte['id_cpte']);
      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("Compte comptable associé au produit d'épargne"));
      }
      $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];
      global $global_monnaie;
      $myErr = effectueChangePrivate($dev2, $global_monnaie, $mnt_comm, 510, $cptes_substitue, $comptable);

      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }

    }
  } else { // Pas de crédit direct, on crée tout simplement une attente de crédit
    $erreur = insertAttente($data);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    } else {
      $id_cheque = $erreur->param;
    }

    if ($data['type_piece']==2)//on ne fait de mouvement qu'en cas de chèque (pas en cas de Travelers cheque)
      //dans le cas du Travelers cheque, il y aura des écritures dans la fonction accepteAttente
    {
      $comptable = array();
      $cptes_substitue = array();
      $cptes_substitue["cpta"] = array();
      $cptes_substitue["int"] = array();
      // Mouvement des comptes "comptables" associés
      $operation='500';
      if (isset($CHANGE)) $deviseCheque = $CHANGE['devise'];
      else $deviseCheque = $InfoCpte['devise'];
      $comptesCompensation = getComptesCompensation($data['id_correspondant']);
      $cptes_substitue["cpta"]["credit"] = $comptesCompensation['credit'];
      $cptes_substitue["cpta"]["debit"] = $comptesCompensation['debit'];
      $myErr = passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $deviseCheque);

      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
  }

  // S'il y a des frais de dépt, percevoir ceux-ci
  if ($InfoProduit["frais_depot_cpt"] > 0) {
    $montant_remboursement -= $InfoProduit["frais_depot_cpt"];
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($InfoCpte['id_cpte']);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("Compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];
    global $global_monnaie;
    $myErr = effectueChangePrivate($InfoCpte["devise"], $global_monnaie, $InfoProduit["frais_depot_cpt"], 150, $cptes_substitue, $comptable);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  $MyError = ajout_historique(75, $InfoCpte["id_titulaire"],$id_cheque, $global_nom_login, date("r"), $comptable, $data_his_ext);

  if ($MyError->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyError;
  }

  $id = $MyError->param;

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $id, null, array("comptable" => $comptable));
}

function getFilter($filters, $name, $default = null)
{
  return isset($filters[$name]) ? trim($filters[$name]) : $default;
}

/**
 * Fonction qui recherche les details d'une transaction apres un depot compte
 * @param Array $filtres Un tableau avec les critères de recherche
 */
function getDetailsTransaction($filtres) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $type_depot = getFilter($filtres, 'type_depot');

  $sql = "
    SELECT h.id_his as id_transaction_adbanking, he.communication as id_transaction_source, he.remarque, c.id_client,
    c.num_tel, c.pp_prenom, c.pp_nom, c.pm_raison_sociale, c.statut_juridique, c.adresse, c.pays, c.code_postal,
    c.ville, cp.id_cpte, cp.num_complet_cpte, cp.solde, calculsoldecpte(cp.id_cpte, null,
    date(now())) as solde_disponible, tb.denomination as tireur_benef, h.date as date_transaction, he.type_piece
    FROM ad_his h
    INNER JOIN ad_his_ext he ON he.id = h.id_his_ext
    INNER JOIN tireur_benef tb on tb.id = he.id_tireur_benef
    INNER JOIN ad_cli c ON c.id_client = h.id_client
    INNER JOIN ad_ecriture e ON e.id_his = h.id_his
    INNER JOIN ad_mouvement m ON m.id_ecriture = e.id_ecriture
    INNER JOIN ad_cpt cp ON cp.id_titulaire = c.id_client AND m.cpte_interne_cli = cp.id_cpte
    WHERE he.type_piece = $type_depot
    AND c.id_ag = $global_id_agence
  ";

  if ($id_ag = getFilter($filtres, 'id_ag')) {
    $sql .= " AND c.id_ag = '$id_ag'";
  }

  if ($id_transaction_adbanking = getFilter($filtres, 'id_transaction_adbanking')) {
    $sql .= " AND h.id_his = $id_transaction_adbanking";
  }

  if ($id_transaction_source = getFilter($filtres, 'id_transaction_source')) {
    $sql .= " AND he.communication = '$id_transaction_source'";
  }

  $sql .= ";";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return null;
  }

  $DATAS = array();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($DATAS, $tmprow);

  $dbHandler->closeConnection(true);
  return $DATAS;
}


/**
 * Cette fonction traite la réception d'un virement
 * @author Bernard De Bois
 * @param array $data : toutes les informations utiles au traitement et à l'écriture de l'historique
 * @param array $InfoCpte : Les champs du compte client
 * @param array $InfoProduit : les champs du produit d'épargne lié au compte client
 * @param array $CHANGE : les données concernant le change
 * @param array $frais_virement : eventuels frais de virement(exemple dépôt par lot pour les virements des salaires)
 * @param integer $type_fonction le numero de la fonction, par defaut 75=depot
 * @return ErrorObj Les erreurs possibles
 */

function receptionVirement($data, $InfoCpte, $InfoProduit, $CHANGE=NULL, $frais_virement=NULL,$type_fonction=75, $infos_sup, &$montant_remboursement = NULL) {
  global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_monnaie;

  //pour pouvoir commit ou rollback toute la procédure
  $db = $dbHandler->openConnection();
  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();
  $montant_remboursement = $data["montant"];

  //Check que le dépôt est possible sur le compte
  $erreur = CheckDepot($InfoCpte, $data["montant"]);
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }
	//Création historique extérieure
  if ($data!=NULL) $data_his_ext = creationHistoriqueExterieur($data);
  else $data_his_ext = NULL;
  
  //vérifier que le compte est ouvert ou bloqué
  if ($InfoCpte['etat_cpte'] == "2") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_FERME);
  } else if ($InfoCpte['etat_cpte'] == "4") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_ATT_FERM);
  }

  if ($InfoProduit["mnt_max"] > 0) {
    $new_solde = $InfoCpte["solde"] + $data["montant"];
    if ($new_solde  > $InfoProduit["mnt_max"])
      return  new ErrorObj(ERR_MNT_MAX_DEPASSE);//on suppose que le montant bloqué sur le compte est intégré au solde
  };

  // Passage des écritures comptables
  $comptable = array();

  //Opération 508 : débit compte correspondant / crédit compte client
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  $comptesCompensation = getComptesCompensation($data['id_correspondant']);
  $cptes_substitue["cpta"]["debit"] = $comptesCompensation['compte'];

  $operation = 508;

  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($InfoCpte['id_cpte']);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["credit"] = $InfoCpte['id_cpte'];

  if (isset($CHANGE)) {
    $myErr = change($CHANGE['devise'],$data['devise'], $CHANGE['cv'], $data['montant'], $operation, $cptes_substitue, $comptable, $CHANGE["dest_reste"], $CHANGE["comm_nette"], $CHANGE["taux"],NULL,$infos_sup);
  } else {
    $myErr = passageEcrituresComptablesAuto($operation, $data["montant"], $comptable, $cptes_substitue, $data['devise'], NULL, NULL, $infos_sup);
  }

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  //Opération 150 (perception des frais de dépôt) : débit compte client / frais de dépôt
  if ($InfoProduit["frais_depot_cpt"] > 0 ) {
    // Passage des écritures comptables
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    $montant_remboursement -= $InfoProduit["frais_depot_cpt"];
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($InfoCpte['id_cpte']);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];
    if ($InfoProduit['devise']!=$global_monnaie) {
      $myErr = effectueChangePrivate($InfoProduit['devise'],$global_monnaie,$InfoProduit['frais_depot_cpt'],150,$cptes_substitue,$comptable);
    } else {
      $myErr = passageEcrituresComptablesAuto(150, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue);
    }
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  /* Perception d'éventuels frais de virement. Par exemple virement des salaires par dépot par lot  */
  if ($frais_virement !=NULL ) {
    // Passage des écritures comptables
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    $montant_remboursement =  $montant_remboursement - $frais_virement;
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

  $MyError = ajout_historique($type_fonction, $InfoCpte["id_titulaire"], NULL, $global_nom_login, date("r"), $comptable, $data_his_ext, NULL, $infos_sup);
  if ($MyError->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $MyError;
  }

    $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array("id" => $MyError->param), null, array("comptable" => $comptable));
}

/**
 * Cette fonction rejette l'attente : elle passe les écritures comptables et met l'état de l'attente à Rejetée.
 * @author Bernard De Bois
 * @param integer $id_attente : id de l'attente à refuser
 * @return objet ErrorObj
 */
function rejetAttente($id_attente, $frais_rejet=NULL, $frais_correspondant=NULL) {
  global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_monnaie;
  $db = $dbHandler->openConnection();

  //on récupère les données de l'attente
  $dataAttente = getInfoAttente($id_attente);

  //on récupère les données du client
  $id_cpte=NULL;
  if ($dataAttente['id_cpt_benef'] != '')
    $id_cpte = $dataAttente['id_cpt_benef'];
  else if ($dataAttente['id_cpt_ordre'] != '')
    $id_cpte = $dataAttente['id_cpt_ordre'];

  if (is_null($id_cpte)) {
    // Si le bénéficiaire n'est pas client, alors le type de pièce doit etre une mise à disposition
    if ($dataAttente['type_piece']!=6)
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else {
    //vérifier que le compte est ouvert ou bloqué
    $InfoCpte = getAccountDatas($id_cpte);
    $etat_cpte = $InfoCpte['etat_cpte'];
    if ($etat_cpte == "2") {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_FERME);
    } else if ($etat_cpte == "4") {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_ATT_FERM);
    }

    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  }
  $comptable = array();

  // On annule les écritures passées sur les comptes d'attentes du correspondant
  // Pour les attentes IN, on ne peut annuler que les chèques et les mises à disposition
  if (($dataAttente['type_piece']==2 || $dataAttente['type_piece']==6) && $dataAttente['sens']=='in ') {
    // Annulation d'une attente IN : aucun mouvement sur le compte du client
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    //on passe les écritures inverses à celle de l'opération 500.
    $comptesCompensation = getComptesCompensation($dataAttente['id_correspondant']);
    $cptes_substitue["cpta"]["debit"]  = $comptesCompensation['credit'];
    $cptes_substitue["cpta"]["credit"] = $comptesCompensation['compte'];
    if ($dataAttente['type_piece']==2) $operation = 504;//refus d'un chèque extérieur
    if ($dataAttente['type_piece']==6) $operation = 528;//rejet d'une mise à disposition
    $myErr = passageEcrituresComptablesAuto($operation, $dataAttente["montant"], $comptable, $cptes_substitue, $dataAttente['devise']);
    setMonnaieCourante($dataAttente["devise"]);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  // On annule les écritures passées lors du transfert d'un compte client vers le correspondant
  // Pour les attentes OUT, on ne peut annuler que les OP
  if ($dataAttente['type_piece'] == 3 && $dataAttente['sens'] == 'out') {
    // Rejet d'un attente OUT, le montant de l'attente est ramené sur le compte du client
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    //on passe les écritures inverses à celle de l'opération 513.
    $comptesCompensation = getComptesCompensation($dataAttente['id_correspondant']);
    $cptes_substitue["cpta"]["debit"]  = $comptesCompensation['compte'];
    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($dataAttente['id_cpt_ordre']);
    $cptes_substitue["int"]["credit"] = $id_cpte;
    $ACC = getAccountDatas($id_cpte);
    $myErr = effectueChangePrivate($dataAttente["devise"], $ACC["devise"], $dataAttente["montant"], 527, $cptes_substitue, $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  // Perception des frais de rejet (à débiter du compte client)
  if ($frais_rejet!=NULL) {
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($InfoCpte['id_cpte']);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $InfoCpte['id_cpte'];
    if ($InfoCpte['devise']==$global_monnaie) {
      $myErr = passageEcrituresComptablesAuto(505, $frais_rejet, $comptable, $cptes_substitue, $InfoCpte['devise']);
    } else {
      $myErr = effectueChangePrivate($InfoCpte['devise'],$global_monnaie,$frais_rejet,505,$cptes_substitue,$comptable);
    }
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  //Perception des frais de rejet (à payer au correspondant)
  if ($frais_correspondant!=NULL) {
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $dataCorrespondant=getInfosCorrespondant($dataAttente['id_correspondant']);
    $cptes_substitue["cpta"]["credit"] = $dataCorrespondant['cpte_bqe'] ;
    if ($dataCorrespondant['devise']==$global_monnaie) {
      $myErr = passageEcrituresComptablesAuto(506, $frais_correspondant, $comptable, $cptes_substitue, $dataCorrespondant['devise']);
    } else {
      $myErr = effectueChangePrivate($global_monnaie,$dataCorrespondant['devise'],$frais_correspondant,506,$cptes_substitue,$comptable, false);
    }
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  $myErr = ajout_historique(188, $InfoCpte["id_titulaire"],$id_attente, $global_nom_login, date("r"), $comptable, NULL);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  //FIXME Bernard : attention, le code état varie selon le type de pièce et le sens
  updateEtatAttente($id_attente, 4);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $myErr->param);
}

/**
 * Cette fonction rejette l'opération d'envoi d'argent à l'étranger
 * @author Bernard De Bois
 * @param integer $id_attente : id de l'attente à refuser
 * @return objet ErrorObj
 */
function rejetEnvoi($id_attente, $frais_correspondant=NULL) {
  global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_monnaie;
  $db = $dbHandler->openConnection();

  //on récupère les données de l'attente
  $dataAttente = getInfoAttente($id_attente);

  //On annule les écritures passées sur les comptes d'attentes du correspondant
  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  //on passe les écritures inverses à celle de l'opération 516.
  $comptesCompensation = getComptesCompensation($dataAttente['id_correspondant']);
  $cptes_substitue["cpta"]["debit"]  = $comptesCompensation['compte'];
  $cptes_substitue["cpta"]["credit"] = $comptesCompensation['credit'];
  $myErr = passageEcrituresComptablesAuto(517, $dataAttente["montant"], $comptable, $cptes_substitue, $dataAttente['devise']);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  //Paiement des frais de rejet (à payer au correspondant)
  if ($frais_correspondant!=NULL) {
    $frais_correspondant=recupMontant($frais_correspondant);
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["cpta"]["credit"] = $comptesCompensation['compte'];
    if ($dataAttente['devise']==$global_monnaie) {
      $myErr = passageEcrituresComptablesAuto(518, $frais_correspondant, $comptable, $cptes_substitue, $dataAttente['devise']);
    } else {
      $myErr = effectueChangePrivate($global_monnaie,$dataAttente['devise'],$frais_correspondant,518,$cptes_substitue,$comptable, false);
    }
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }
  $myErr = ajout_historique(188, $dataAttente['id_ext_benef'], $id_attente, $global_nom_login, date("r"), $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  updateEtatAttente($id_attente, 5);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $myErr->param);
}

/**
 * Cette fonction remplit les champs nécessaires à l'écriture dans la table de l'historique des transferts avec l'extérieur.
 * @author Bernard De Bois
 * @param array $data : toutes les informations utiles à la mise à jour de la table attentes_credit
 * @return array : un tableau avec tous les champs nécessaires à l'écriture dans la table ad_his_ext
 */
function creationHistoriqueExterieur($data) {
  $data_ext=array();

  $data_ext['type_piece']	= $data['type_piece'];
  $data_ext['remarque']	= $data['remarque'];
  $data_ext['sens']		= $data['sens'];
  $data_ext['num_piece']	= $data['num_piece'];
  $data_ext['date_piece']	= $data['date_piece'];
  $data_ext['communication']	= $data['communication'];
  $data_ext['id_pers_ext']	= $data['id_pers_ext'];
  switch ($data['sens']) {
  case 'in ' :
    $data_ext['id_tireur_benef'] = $data['id_ext_ordre'];
    break;
  case 'out' :
    $data_ext['id_tireur_benef'] = $data['id_ext_benef'];
    break;
  case '---' :
    $data_ext['id_tireur_benef'] = NULL;
    break;
  default :
    signalErreur(__LINE__,__FILE__,__FUNCTION__);
  }
  if ($data_ext['type_piece']==5) unset($data_ext['id_tireur_benef']);//dans le cas d'un Travelers cheque, pas de tireur/benef

  return $data_ext;
}

function getListeAttentes($criteres) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql ="
        SELECT a.*, b.nom_banque
        FROM attentes a, adsys_banque b
        WHERE (a.id_ag=b.id_ag) and (a.id_ag=$global_id_agence) AND (a.id_banque = b.id_banque)";

  $criteresSQL="";
  if (isset($criteres['id_banque']))     $criteresSQL.= " AND (a.id_banque = ".$criteres['id_banque'].")";
  if (isset($criteres['correspondant'])) $criteresSQL.= " AND (a.id_correspondant = ".$criteres['correspondant'].")";
  if (isset($criteres['etat_chq'])) {
    if ($criteres['etat_chq']==99) { //si l'etat_chq = 99, on affiche toutes les attentes qui peuvent avoir un traitement (1: en attente, 2:envoyée, 5:à rembourser)
      $criteresSQL.= "AND (a.etat = 1
                     OR a.etat = 2
                     OR a.etat = 5)";
    } else {
      $criteresSQL.= " AND (a.etat = ".$criteres['etat_chq'].")";
    }
  }
  if (isset($criteres['type_piece']))    $criteresSQL.= " AND (a.type_piece = ".$criteres['type_piece'].")";
  if (isset($criteres['sens']))      $criteresSQL.= " AND (a.sens = '".$criteres['sens']."')";
  if (isset($criteres['id_ben'])) {
    $id_ben=$criteres['id_ben'];
    $criteresSQL.= " AND (a.id_ext_benef = $id_ben OR a.id_ext_ordre = $id_ben)";
  }
  if (isset($criteres['num_client'])) {
    $id_client = $criteres['num_client'];
    $comptesClient = get_comptes_epargne($id_client);
    $criteresSQL.=" AND (";
    foreach ($comptesClient as $key=>$value) {
      $criteresSQL.= "a.id_cpt_benef = $key OR a.id_cpt_ordre = $key OR ";
    }
    $criteresSQL = substr($criteresSQL, 0, -4);
    $criteresSQL.= ")";
  }

  $sql.=$criteresSQL;
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };
//on recherche aussi les attentes qui n'ont pas de correspondant bancaires (notamment les travelers cheque)
  $sql ="
        SELECT *
        FROM attentes a
        WHERE (a.id_ag=$global_id_agence) AND (a.id_banque is NULL)";

  $sql.=$criteresSQL;
  $result2 = $db->query($sql);
  if (DB::isError($result2)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };


  $dbHandler->closeConnection(true);

  if (($result->numRows() == 0) && ($result2->numRows()==0)) return NULL;

  $TMPARRAY = array();
  while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $TMPARRAY[$prod["id"]] = $prod;
  while ($prod = $result2->fetchrow(DB_FETCHMODE_ASSOC)) {
    $TMPARRAY[$prod["id"]] = $prod;
    $TMPARRAY[$prod["id"]]['nom_banque']=_("travelers cheque");
  }
  //on rajoute les données du client et du tireur/bénéficiaire
  foreach ($TMPARRAY as $key=>$value) {
    $compteClient = NULL;
    if ($value['id_cpt_benef']!='') {
      $compteClient = $value['id_cpt_benef'];
      $donneesCompteClient = getAccountDatas($compteClient);
      $nomClient = getClientName($donneesCompteClient['id_titulaire']);
      $TMPARRAY[$key]['beneficiaire']=$nomClient." (".$donneesCompteClient['num_complet_cpte'].")";
    }
    if ($value['id_cpt_ordre']!='') {
      $compteClient = $value['id_cpt_ordre'];
      $donneesCompteClient = getAccountDatas($compteClient);
      $nomClient = getClientName($donneesCompteClient['id_titulaire']);
      $TMPARRAY[$key]['donneurOrdre']=$nomClient." (".$donneesCompteClient['num_complet_cpte'].")";
    }
    if ($value['id_ext_benef'] != '') {
      $infoTireur = getTireurBenefDatas($value['id_ext_benef']);
      $TMPARRAY[$key]['beneficiaire']=$infoTireur['denomination'];
      if ($value['type_piece']!=6) $TMPARRAY[$key]['beneficiaire'].= " (".$value['nom_banque'].")";// si le bénéficiaire est externe et qu'il s'agit d'une mise à disposition, on ne rajoute pas le nom de la banque qui envoie l'argent.
    }
    if ($value['id_ext_ordre'] != '') {
      $infoTireur = getTireurBenefDatas($value['id_ext_ordre']);
      $TMPARRAY[$key]['donneurOrdre']=$infoTireur['denomination'];
      if ($value['type_piece']!=7) $TMPARRAY[$key]['donneurOrdre'].= " (".$value['nom_banque'].")";// si le donneur d'ordre est externe et qu'il s'agit d'un envoi d'argent, on ne rajoute pas le nom de la banque qui envoie l'argent.
    }
  }
  return $TMPARRAY;
}

function getInfoAttente($id) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM attentes WHERE id_ag=$global_id_agence AND id=$id;";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "La banque ".$data_chqe["id_bqe"]." n'existe pas"
  }
  $dbHandler->closeConnection(true);

  return $result->fetchrow(DB_FETCHMODE_ASSOC);
}

/**
 * Cette fonction traite l'acceptation d'une attente.
 * - dans le cas d'un chèque ou d'un Travelers cheque déposé sur un compte client (type de pièce 2 et 5) (sens IN)
 * Trois opérations comptables sont passées et l'attente est mise à Acceptée.
 * @author Bernard De Bois
 * @param int $id_attente : id de l'attente acceptée
 * @return objet : ErrorObj.
 */
function accepteAttente($id_attente) {
  global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_monnaie;
  $db = $dbHandler->openConnection();

  //on récupère les données de l'attente
  $dataAttente = getInfoAttente($id_attente);
  if ($dataAttente['id_cpt_benef']!='')
    $id_cpte = $dataAttente['id_cpt_benef'];
  else if ($dataAttente['id_cpt_ordre']!='')
    $id_cpte = $dataAttente['id_cpt_ordre'];
  else
    signalErreur(__FILE__,__LINE__,__FUNCTION__);

  //vérifier que le compte est ouvert ou bloqué
  $InfoCpte = getAccountDatas($id_cpte);
  $etat_cpte = $InfoCpte['etat_cpte'];
  if ($etat_cpte == "2") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_FERME);
  } else if ($etat_cpte == "4") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_ATT_FERM);
  }

  //FIXME : LOCK table ad_cpt pour concurrence ?
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  $new_solde = $InfoCpte["solde"] + $dataAttente["montant"];
  if ($InfoProduit["mnt_max"] > 0) {
    if ($new_solde  > $InfoProduit["mnt_max"])
      return  new ErrorObj(ERR_MNT_MAX_DEPASSE);//on suppose que le montant bloqué sur le compte est intégré au solde
  };
    //initialisation données de la table hist_ext
    $data_his_ext = NULL;

  // Passage des écritures comptables
  $comptable = array();

  //Opération 501 : débit compte créditeur correspondant / crédit compte client
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  // les comptes de débit sont différent s'il s'agit d'un chèque ou d'un travelers cheque.
  if ($dataAttente['type_piece']==2) { //dans le cas d'un chèque
    $comptesCorrespondant = getComptesCompensation($dataAttente['id_correspondant']);
    $cptes_substitue["cpta"]["debit"] = $comptesCorrespondant['credit'];

    $operation = 501;
    $num_cheque = $dataAttente['num_piece'];
    $date_cheque =   $dataAttente['date_piece'];

    // Log numéro chèque accepté dans la table ad_his_ext
    $data_cheque = array();
    $data_cheque["num_piece"] = $num_cheque;
    $data_cheque["date_piece"] = $date_cheque;
    $data_cheque["remarque"] = "Acceptation chèque externe No : " . $num_cheque;
    $data_cheque["sens"] = "---";

    $data_his_ext = creationHistoriqueExterieur($data_cheque);

  } else if ($dataAttente['type_piece']==5) { //dans le cas d'un travelers cheque
    $dataAgence = getAgenceDatas($global_id_agence);

    $cptes_substitue["cpta"]["debit"] = $dataAgence['num_cpte_tch'];

    $operation = 507;

  }

  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["credit"] = $id_cpte;

  if ($InfoProduit['devise'] == $dataAttente['devise']) {
    $myErr = passageEcrituresComptablesAuto($operation, $dataAttente["montant"], $comptable, $cptes_substitue, $dataAttente["devise"]);
  } else {
    //FIXME Bernard : on calcule la contrevaleur de manière identique à celle de change(). Cette info n'est plus disponible, vu qu'on peut être plusieurs jours après l'encodage du chèque.
    $montant = $dataAttente['montant'];
    $dev1 = $dataAttente['devise'];
    $dev2 = $InfoProduit['devise'];
    $commission = calculeCommissionChange($montant, $dev1, $dev2);
    $taxe = calculeTaxeChange($commission, $dev1, $dev2);
    $com_nette = $commission + $taxe;
    $taux = getTauxChange($dev1, $dev2, true, 2);
    $benef_taux = calculeBeneficeTaux(($montant - $commission - $taxe), $dev1, $dev2, $taux);
    $cvMontant = calculeCV($dev1, $dev2, ($montant - $commission - $taxe - $benef_taux));

    $myErr = change($dev1, $dev2, $montant,$cvMontant, $operation, $cptes_substitue, $comptable, 3, $com_nette, $taux);
  }
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $myErr = ajout_historique(188, $InfoCpte["id_titulaire"],$id_attente, $global_nom_login, date("r"), $comptable, $data_his_ext);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  //FIXME Bernard : attention, le code état varie selon le type de pièce et le sens
  updateEtatAttente($id_attente, 3);

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $myErr->param);
}

/**
 * Cette fonction traite l'envoi d'une attente.
 * elle mouvemente les comptes d'attente du correspondant par le compte du correspondant.
 * Sauf dans le cas d'un traveler's cheque où on se contente de basculer le statut d l'attente à "Envoyé"
 * @author Bernard De Bois
 * @param int $id_attente : id de l'attente envoyée
 * @return objet : ErrorObj.
 */
function envoiAttente($id_attente) {
  global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_monnaie;
  $db = $dbHandler->openConnection();

  $dataAttente = getInfoAttente($id_attente);

  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();

  // Si attente autre que traveler's cheque
  if ($dataAttente["type_piece"] != 5) {
    $comptesCompensation = getComptesCompensation($dataAttente['id_correspondant']);
    if ($dataAttente['sens']=='out') {
      $cptes_substitue["cpta"]["debit"] = $comptesCompensation['credit'];
      $cptes_substitue["cpta"]["credit"] = $comptesCompensation['compte'];
      if ($dataAttente['type_piece']==7) $operation = 516; // envoi d'argent d'un non client
      if ($dataAttente['type_piece']==2 || $dataAttente['type_piece']==3) $operation = 526;// envoi d'un OP ou d'un chèque à débiter à un client.
    } else { //envoi d'un chèque de la banque externe à encaisser
      $cptes_substitue["cpta"]["debit"] = $comptesCompensation['compte'];
      $cptes_substitue["cpta"]["credit"] = $comptesCompensation['debit'];
      if ($dataAttente['type_piece']==2) $operation = 502;
    }

    $myErr = passageEcrituresComptablesAuto($operation, $dataAttente["montant"], $comptable, $cptes_substitue, $dataAttente['devise']);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  $myErr = ajout_historique(188, NULL,$id_attente, $global_nom_login, date("r"), $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  updateEtatAttente($id_attente, 2);

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $myErr->param);
}

/**
 * Cette fonction traite l'acceptation d'un envoi d'argent d'un non-client vers une banque externe.
 * elle change le statut de l'attente
 * @author Bernard De Bois
 * @param int $id_attente : id de l'attente envoyée
 * @return objet : ErrorObj.
 */
function accepteEnvoiArgent($id_attente) {
  global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_monnaie;
  $db = $dbHandler->openConnection();

  updateEtatAttente($id_attente, 3);

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/**
 * Cette fonction traite du paiement au guichet à un non-client :
 * - soit qu'il vienne toucher une mise à disposition.
 * - soit qu'il vienne récupérer un envoi d'argent qui a été refusé.
 * elle mouvemente les comptes d'attente du correspondant et perçoit des frais.
 * @author Bernard De Bois
 * @param int $id_attente : id de l'attente envoyée
 * @return objet : ErrorObj.
 */
function paiementGuichet($id_attente, $frais) {
  global $dbHandler, $global_id_agence, $global_id_client, $global_nom_login, $global_id_guichet, $global_monnaie;
  $db = $dbHandler->openConnection();

  $dataAttente = getInfoAttente($id_attente);

  //on verse l'argent de la mise au disposition au guichet (débit du compte créditeur correspondant)
  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $comptesCompensation = getComptesCompensation($dataAttente['id_correspondant']);
  $cptes_substitue["cpta"]["debit"] = $comptesCompensation['credit'];
  $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($global_id_guichet);

  /* Arrondi du montant: opération au guichet*/
  $critere = array();
  $critere['num_cpte_comptable'] = $cptes_substitue["cpta"]["credit"];
  $cpte_gui = getComptesComptables($critere);
  $dataAttente["montant"] = arrondiMonnaie( $dataAttente["montant"], 0, $cpte_gui['devise'] );

  if ($dataAttente['type_piece']==6) $operation = 523;//perception d'une mise à disposition
  if ($dataAttente['type_piece']==7) $operation = 519;//remboursement d'un envoi d'argent refusé

  $myErr = passageEcrituresComptablesAuto($operation, $dataAttente["montant"], $comptable, $cptes_substitue, $dataAttente['devise']);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if (isset($frais)) {
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($global_id_guichet);
    if ($dataAttente['type_piece']==6) $operation = 524;// frais de perception d'une mise à disposition
    if ($dataAttente['type_piece']==7) $operation = 520;//frais de remboursement d'un envoi d'argent refusé
    $myErr = effectueChangePrivate($dataAttente['devise'], $global_monnaie, $frais, $operation, $cptes_substitue, $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  $myErr = ajout_historique(188, NULL,$id_attente, $global_nom_login, date("r"), $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($dataAttente['type_piece']==6)
    updateEtatAttente($id_attente, 3);// statut passe à "payé"
  else if ($dataAttente['type_piece']==7)
    updateEtatAttente($id_attente, 6);// statut passe à "remboursé"

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $myErr->param);
}

/**
 * Cette fonction met à jour le champ Etat de la table attente. Soit à Accepté, soit à rejeté.
 * @author Bernard De Bois
 * @param int $id_attente : id de l'attente à mettre à jour.
 * @param boolean $accepte : true si l'attente est acceptée, false si l'attente est rejetée.
 * @return aucun retour.
 */
function updateEtatAttente($id_attente,$etat) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "UPDATE attentes SET etat = $etat WHERE id_ag=$global_id_agence AND id = $id_attente;";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };

  $dbHandler->closeConnection(true);
}

/**
 * Cette fonction gère les frais d'ouverture d'un compte et les frais d'opération.
 * Si le versement initial a été fait dans une devise autre que celle du compte, les frais de change sont aussi enregistrés.
 * @author Inconnu
 * @param array $DATA contient toutes les informations liés au compte.
 * @param int $montant : montant versé par le client au guichet.
 * @param array $frais : Tableau contenant les frais modifiables par l'utilisateur
 * @param array $CHANGE : Optionnel, contient les infos en cas de change ("devise" => Devise de sortie, "commission" => Commission de change, "taxe" => Taxe de change, "benef_taux" => Bénéfrice fait sur le taux)
 * @return ErrorObj Les erreurs possibles
 */
function ouverture_cpte_guichet($DATA, $montant, $frais, $data_ext, $CHANGE=NULL) {
  // FIXME : J'utilise les variables globales pour ne pas devoir les envoyer en paramètres à la PS --> on peut faire un tableau d'arguments pour gérer le cas
  //de ces variables liées au front-office

  global $dbHandler, $global_id_client, $global_id_guichet, $global_id_agence, $global_nom_login, $global_monnaie, $db;
  $comptable = array();

  $db = $dbHandler->openConnection();
  //Recupérer le disponible pour num_cpte
  if ($DATA["num_cpte"] == NULL) {
    $DATA["num_cpte"] = getRangDisponible($global_id_client);
  }

  $has_changed_param_epargne = $DATA["has_changed_param_epargne"];
  unset($DATA["has_changed_param_epargne"]);
  
  //insérer les données dans ad_cpt pour avoir un n° de cpte
  $id_cpte = creationCompte($DATA);
  //remplir 2 tableaux avec toutes les infos sur le compte et le produit associé
  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  // chamgement des frais s'il y'a modiffication
  $can_modif = array("frais_depot_cpt","frais_ouverture_cpt");
  if (check_access(299))
    foreach($can_modif as $value)
    $InfoProduit[$value]=$frais[$value];

  //d'abord faire l'opération normale de dépôt; les frais sur opération sont traités ensuite
  //vérifier dépassement montant maximum, on suppose que le  solde est nul au départ
  //FIXME : confirmer le contrôle sur les montants max
  $montantDepose=$montant-$InfoProduit['frais_depot_cpt']-$InfoProduit['frais_ouverture_cpt'];
  if (($InfoProduit["mnt_max"] > 0) && ($montantDepose > $InfoProduit["mnt_max"])) {
    $dbHandler->closeConnection(false);
    return  new ErrorObj(ERR_MNT_MAX_DEPASSE);
  }

  /* Arrondi du montant opération au guichet */
  $critere = array();
  $critere['num_cpte_comptable'] = getCompteCptaGui($global_id_guichet);
  $cpte_gui = getComptesComptables($critere);
  $montant = arrondiMonnaie( $montant, 0, $cpte_gui['devise'] );

  //vérifier montant minimum non dépassé, il se peut qu'on fasse un premier dépôt qui soit inférieur au montant mini et dans ce cas, interdire que ce premier dépôt soit inférieur au mini autorisé pour le compte
  if (($InfoProduit["mnt_min"] > 0) && ($montantDepose < $InfoProduit["mnt_min"])) {
    $dbHandler->closeConnection(false);
    return  new ErrorObj(ERR_MNT_MIN_DEPASSE);
  }

  // Mouvement des comptes "comptables" associés.Passage des écritures comptables
  if ($montant > 0) {
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    //débit d'un guichet par le crédit d'un client
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($global_id_guichet);

    //Produit du compte d'épargne associé
    $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["credit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["credit"] = $id_cpte;

    if (is_array($CHANGE) && $InfoProduit['devise']!=$CHANGE['devise']) {
      $CHANGE['cv'] = recupMontant($CHANGE['cv']);
      $myErr = change($CHANGE['devise'],$InfoProduit['devise'],$CHANGE['cv'],$montant,160,$cptes_substitue,$comptable,$CHANGE['dest_reste'], $CHANGE['comm_nette'], $CHANGE["taux"]);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    } else {
      $myErr = passageEcrituresComptablesAuto(160, $montant, $comptable, $cptes_substitue, $DATA["devise"]);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  // --------------- frais d'opérations ---------------------------------------------------

  // en cas de frais d'opérations, débit compte guichet et crédit compte produit
  if ($InfoProduit["frais_depot_cpt"] > 0 ) {
    // Passage des écritures comptables
    unset($cptes_substitue["cpta"]["credit"]);
    unset($cptes_substitue["int"]["credit"]);

    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte;
    if ($InfoProduit['devise']!=$global_monnaie) {
      $myErr = effectueChangePrivate($InfoProduit['devise'],$global_monnaie,$InfoProduit['frais_depot_cpt'],150,$cptes_substitue,$comptable);
    } else {
      $myErr = passageEcrituresComptablesAuto(150, $InfoProduit["frais_depot_cpt"], $comptable, $cptes_substitue, $DATA["devise"]);
    }
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  //traitement des frais d'ouverture avec mvt des comptes
  if ($InfoProduit["frais_ouverture_cpt"] > 0 ) {

    //débit du guichet, crédit des frais d'ouverture
    unset($cptes_substitue["cpta"]["credit"]);
    unset($cptes_substitue["int"]["credit"]);

    // Passage des écritures comptables
    if ($InfoProduit['devise']!=$global_monnaie) {
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }

      $cptes_substitue["int"]["debit"] = $id_cpte;
      $myErr = effectueChangePrivate($InfoProduit['devise'],$global_monnaie,$InfoProduit['frais_ouverture_cpt'],100,$cptes_substitue,$comptable);
    } else {
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }

      $cptes_substitue["int"]["debit"] = $id_cpte;
      $myErr = passageEcrituresComptablesAuto(100, $InfoProduit["frais_ouverture_cpt"], $comptable, $cptes_substitue, $DATA["devise"]);
    }

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  if ($data_ext != NULL) {
    $data_his_ext = creationHistoriqueExterieur($data_ext);
  } else {
    $data_his_ext = NULL;
  }

  // #537 -  Traçabilité des modifications des paramètres d’épargne :
  if(!empty ($has_changed_param_epargne)) {
    ajout_historique(58,$global_id_client, NULL, $global_nom_login, date("r"), NULL);
  }

  $myErr =  ajout_historique(53, $global_id_client,'', $global_nom_login, date("r"), $comptable, $data_his_ext);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $id_his = $myErr->param;
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $id_his);

}

/**
 * Effectue une ouverture de compte à partir d'un autre compte du meme client
 * @param Array $DATA Données à insérer dans ad_cpt
 * @param int $id_cpte_source ID du compte source
 * @param float $montant Montant du transfert du compte source
 * @param Array $CHANGE Le tableau change
 */
function ouverture_cpte_transfert($DATA, $id_cpte_source, $montant, $id_mandat, $data_ext, $frais=NULL, $CHANGE=NULL) {

  global  $dbHandler, $db, $global_id_client, $global_id_agence, $global_nom_login, $global_monnaie;

  $comptable = array();

  $db = $dbHandler->openConnection();

  // Recupérer numero de rang disponible pour num_cpte
  if ($DATA["num_cpte"] == NULL) {
    $DATA["num_cpte"] = getRangDisponible($global_id_client);
  }

  $has_changed_param_epargne = $DATA["has_changed_param_epargne"];
  unset($DATA["has_changed_param_epargne"]);

  //insérer les données dans ad_cpt pour avoir un n° de cpte
  $id_cpte_destination = creationCompte($DATA);
  $id_cpte_base = getBaseAccountID ($global_id_client);

  $InfoCpteBase = getAccountDatas($id_cpte_base);

  $InfoCpteSource = getAccountDatas($id_cpte_source);

  $InfoProduitSource = getProdEpargne($InfoCpteSource["id_prod"]);

  $InfoCpteDestination = getAccountDatas($id_cpte_destination);

  $InfoProduitDestination = getProdEpargne($InfoCpteDestination["id_prod"]);

  // Si autorisé, on remplace les frais d"ouverture et les frais de transfert par ceux fournis dans le tableau $frais
  if (check_access(299)) {
    $InfoProduitSource["frais_transfert"] = $frais["frais_transfert"];
    $InfoProduitDestination["frais_ouverture_cpt"] = $frais["frais_ouverture_cpt"];
  }
  //vérifier d'abord qu'on peut retirer du compte source et déposer sur le compte destination
  $erreur = CheckRetrait($InfoCpteSource, $InfoProduitSource, $montant, 1, $id_mandat);//le 1= bypass frais retrait cause transfert

  if ($erreur->errCode != NO_ERR)  {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  //vérifier dépassement montant maximum, on suppose que le  solde est nul au départ
  if (($InfoProduitDestination["mnt_max"] > 0) && ($montant > $InfoProduitDestination["mnt_max"])) {
    $dbHandler->closeConnection(false);
    return  new ErrorObj(ERR_MNT_MAX_DEPASSE);
  }

  //vérifier montant minimum non dépassé, il se peut qu'on fasse un premier dépôt qui soit inférieur au montant mini et dans ce cas, interdire que ce premier dépôt soit inférieur au mini autorisé pour le compte
  if (($InfoProduitDestination["mnt_min"] > 0) && ($montant < $InfoProduitDestination["mnt_min"])) {
    $dbHandler->closeConnection(false);
    return  new ErrorObj(ERR_MNT_MIN_DEPASSE);
  }

  // Passage des écritures comptables : débit client par crédit client
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  //débit du compte source par le crédit du compte destination
  $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
  if ($cptes_substitue["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["debit"] = $id_cpte_source;
  $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_destination);
  if ($cptes_substitue["cpta"]["credit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  $cptes_substitue["int"]["credit"] = $id_cpte_destination;
  if (is_array($CHANGE)) {
    $myErr = change($InfoCpteSource['devise'],$InfoCpteDestination['devise'],$CHANGE['cv'],$montant, 120, $cptes_substitue, $comptable, NULL, $CHANGE['comm_nette'], $CHANGE["taux"]);
  } else {
    $myErr = passageEcrituresComptablesAuto(120, $montant, $comptable, $cptes_substitue, $DATA["devise"]);
  }

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

// Passage des écritures comptables : perception des frais de transfert
  if ($InfoProduitSource['frais_transfert'] > 0) {
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    //débit d'un guichet par le crédit d'un client
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_source);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte_source;
    if ($InfoCpteSource['devise']!=$global_monnaie) {
      $myErr = effectueChangePrivate($InfoCpteSource['devise'], $global_monnaie, $InfoProduitSource['frais_transfert'], 152, $cptes_substitue, $comptable);
    } else {
      $myErr = passageEcrituresComptablesAuto(152, $InfoProduitSource['frais_transfert'], $comptable, $cptes_substitue, $InfoCpteSource["devise"]);
    }
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  // Gestion des frais d'ouverture de compte qui sont débités sur le compte nouvellement ouvert
  if ($InfoProduitDestination["frais_ouverture_cpt"] > 0 ) {

    // Passage des écritures comptables
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte_destination);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte_destination;
    if ($InfoProduitDestination['devise']!=$global_monnaie) {
      //	$cvFraisOuverture=calculeCV($InfoProduitDestination['devise'],$global_monnaie,$InfoProduitDestination['frais_ouverture_cpt']);
      $myErr = effectueChangePrivate($InfoProduitDestination['devise'],$global_monnaie,$InfoProduitDestination['frais_ouverture_cpt'],100, $cptes_substitue, $comptable);
    } else {
      $myErr = passageEcrituresComptablesAuto(100, $InfoProduitDestination["frais_ouverture_cpt"], $comptable, $cptes_substitue, $DATA["devise"]);
    }

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

  }

  $myErr = preleveFraisDecouvert($id_cpte_source, $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($id_mandat != NULL) {
    $MANDAT = getInfosMandat($id_mandat);
    $data_ext['id_pers_ext'] = $MANDAT['id_pers_ext'];
  }

  if ($data_ext != NULL) {
    $data_his_ext = creationHistoriqueExterieur($data_ext);
  } else {
    $data_his_ext = NULL;
  }

  // #537 -  Traçabilité des modifications des paramètres d’épargne :
  if(!empty ($has_changed_param_epargne)) {
    ajout_historique(58,$global_id_client, NULL, $global_nom_login, date("r"), NULL);
  }

  $myErr = ajout_historique(53, $global_id_client, '',$global_nom_login, date("r"), $comptable, $data_his_ext);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);

  return $myErr;

}

function getComptesCalculInteret($freq) {
  /*
    Renvoie un tableau associatif contenant la liste des comptes que doit traiter le batch épargne pour le calul de l'intérêt à verser
    En entrée, on a un tableau indiquant les comptes qui sont arrivés à échénce en fonction de la fréquence de calcul des intérêts
  */

//déterminer les comptes rémunérés
//voir si la période de calcul est atteinte
//traiter les DAT

  global $dbHandler,$global_id_agence;
  $ListeComptes = array();

  $db = $dbHandler->openConnection();

  $sql = "SELECT  a.*, b.*  FROM ad_cpt a, adsys_produit_epargne b ";
  $sql .= " WHERE a.id_ag=b.id_ag and  a.id_ag=$global_id_agence AND a.id_prod=b.id AND (a.etat_cpte = 1 OR a.etat_cpte = 5) AND b.service_financier=true AND b.tx_interet > 0 AND (a.etat_cpte=1)";
  //traiter les DAT à part
  $sql .= " AND NOT ((b.depot_unique = true) AND (b.retrait_unique = true)) ";
  $sql .= " AND (b.freq_calcul_int <= $freq) ";
  $sql .= " ORDER BY a.id_cpte;";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  while ($cpte = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($ListeComptes, $cpte);
  }

  $dbHandler->closeConnection(true);

  return $ListeComptes;
}

function getListeDAT($today) {
  /*
    Retourne dans un tableau associatif la liste des comptes DAT actifs expirant "aujourd'hui"
  */

  //sélectionner tous les DAT
  global $dbHandler,$global_id_agence;
  $ListeDAT = array();

  $db = $dbHandler->openConnection();

  $sql = "SELECT  a.*, b.*  ";
  $sql .= "FROM ad_cpt a, adsys_produit_epargne b ";
  $sql .= "WHERE a.id_ag=b.id_ag and  a.id_ag=$global_id_agence AND a.id_prod=b.id AND b.service_financier=true ";
  $sql .= "AND b.tx_interet > 0 AND (a.etat_cpte=1)";
  $sql .= "AND ((b.depot_unique = 't') AND (b.retrait_unique = 't')) ";
  $sql .= "AND date(a.dat_date_fin) = date('$today') ";
  $sql .= "ORDER BY a.id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  while ($DAT = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($ListeDAT, $DAT);
  }

  $dbHandler->closeConnection(true);

  if (! empty($ListeDAT)) return $ListeDAT;
  else return NULL;

}

function getListeCAT($today) {
  /*
    Retourne dans un tableau associatif la liste des comptes CAT actifs expirant "aujourd'hui"
  */

  global $dbHandler,$global_id_agence;

  $ListeCAT = array();
  //FIXME : ne pas prendre les comptes classe 1. Vérifier qu'on ne crée pas de
  //CAT avec classe_comptable 1. TRES IMPORTANT
  //sélectionner tous les CAT
  $db = $dbHandler->openConnection();
  $sql = "SELECT a.id_cpte,a.dat_prolongation,a.id_titulaire ";
  $sql .= "FROM ad_cpt a, adsys_produit_epargne b ";
  $sql .= "WHERE a.id_ag=b.id_ag and  a.id_ag=$global_id_agence AND a.id_prod=b.id AND b.service_financier=true ";
  $sql .= "AND b.tx_interet > 0 AND (a.etat_cpte=1) ";
  $sql .= "AND (b.classe_comptable =5) ";
  $sql .= "AND date(a.dat_date_fin) = date('$today') ";
  $sql .= "order by a.id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  while ($CAT = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($ListeCAT, $CAT);
  }

  $dbHandler->closeConnection(true);

  if (! empty($ListeCAT)) return $ListeCAT;
  else return NULL;

}

/**********
 * Récupération des comptes à terme (DAT, CAT et épargnes à la source) actifs expirant à la date donnée
 * @author Papa
 * @since 2.3
 * @param date $today la date du batch
 * @return array tableau associatif de la liste des comptes à terme actifs expirant à la date $today
 */
function getComptesTermeEchus($today) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $Liste_cptes = array();

  $sql = "SELECT a.id_cpte, a.dat_prolongation, a.id_titulaire, b.classe_comptable FROM ad_cpt a, adsys_produit_epargne b ";
  $sql .= "WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence AND a.id_prod = b.id AND b.service_financier = true AND a.etat_cpte = 1 ";
  $sql .= "AND (b.classe_comptable = 2 OR b.classe_comptable = 5) AND date(a.dat_date_fin) = date('$today') ";
  $sql .= "order by a.id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($Liste_cptes, $row);

  $dbHandler->closeConnection(true);

  return $Liste_cptes;
}

/**********
 * Récupération des comptes d'épargne à la source actifs expirant à la date donnée
 * @author Ibou Ndiaye
 * @since 3.2
 * @param date $today la date du batch
 * @return array tableau associatif de la liste des comptes d'épargne à la source actifs expirant à la date $today
 */
function getComptesEpSourceEchus($today) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $Liste_cptes = array();

  $sql = "SELECT a.id_cpte, a.id_titulaire, b.classe_comptable FROM ad_cpt a, adsys_produit_epargne b ";
  $sql .= "WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence AND a.id_prod = b.id AND b.service_financier = true AND a.etat_cpte = 1 ";
  $sql .= "AND (b.classe_comptable = 6) AND date(a.dat_date_fin) = date('$today') ";
  $sql .= "order by a.id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  };

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($Liste_cptes, $row);

  $dbHandler->closeConnection(true);

  return $Liste_cptes;
}

/**
 * Ferme un compte brutalement
 * Le compte doit avoir un solde nul pour que l'opération puisse avoir lieu
 * Mais on ne peut pas le vérfiier du fait que des écritures sont p-e en attente dans una rray $comptable
 * @param $int $id_cpte Le numéro du compte
 * @param int $raison_cloture La raison de la cloture (cfr adsys_raison_cloture)
 * @param float $solde_cloture Le solde que possédait le compte au moment où il a été cloturé
 * @return ErrorObj Objet Erreur
 */
function fermeCompte ($id_cpte, $raison_cloture, $solde_cloture, $date_cloture=NULL) {
  /*  $ACC = getAccountDatas($id_cpte);
  if ($ACC["solde"] != $solde_cloture)
    return new ErrorObj(ERR_CPTE_SOLDE_NON_NUL, ($ACC["solde"] - $solde_cloture));
  */

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $fields_array = array();
  $fields_array["etat_cpte"] = 2; // Compte fermé
  $fields_array["raison_clot"] = $raison_cloture;
  if ($date_cloture == NULL)
    $fields_array["date_clot"] = date("d/m/Y");
  else
    $fields_array["date_clot"] = $date_cloture;

  $fields_array["solde_clot"] = $solde_cloture;

  $sql = buildUpdateQuery ("ad_cpt", $fields_array, array("id_cpte"=>$id_cpte,'id_ag'=>$global_id_agence));

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

function clientHasCompteATerme($id_client) {
  /*
       Fonction qui renvoie la liste des comptes à terme actifs pour le client $id_client
       IN : $id_client pour l'ID du client dont on recherche les comptes à terme
       OUT: Array associatif avec les caractéristiques du compte (venant de a_cpt et de adsys_produit_epargne)
       REM: IDEM que fonction suivante mais on renvoie aussi les CAT pour lesquels une décision a déjà été prise par le client
  */
//FIXME : cette fonction suppose que tout compte à terme est forcément à retrait unique

  $listeComptesClient = get_comptes_epargne($id_client);//prendre tous les comptes d'épargnes du client. Il y a au moins un compte qui est le compte de base

  $listeDAT = array();

  if (count($listeComptesClient) > 1 ) {//à part le cpte de base, y a-t-il un autre cpte
    foreach($listeComptesClient as $value) {
      if (($value["retrait_unique"] == 't') && ($value["terme_cpte"] > 0) && ($value["tx_interet_cpte"] > 0) && ($value["dat_prolongeable"]=='t'))
        array_push($listeDAT, $value);
    }
  }
  if (! empty($listeDAT))
    return $listeDAT;
  else
    return NULL;
}

function clientHasDATAttenteProlongation($id_client) {
  /*
    Renvoie la liste des _comptes à terme_ (modifié par TF au 30/08/2002) ouverts et qui sont susceptibles d'être prolongés pour ce client
    sous la forme d'un array et NULL s'il n'y a rien. Il s'agit ici des _comptes à terme_ pour lesquels le client ne s'est pas décidé à
    prolonger.
  */

  $listeComptesClient = get_comptes_epargne($id_client);//prendre tous les comptes d'épargnes du client. Il y a au moins un compte qui est le compte de base

  $listeDAT = array();

  if (count($listeComptesClient) > 1 ) {//à part le cpte de base, y a-t-il un autre cpte
    foreach($listeComptesClient as $value) {
      if (($value["retrait_unique"] == 't') && ($value["terme_cpte"] > 0) && ($value["tx_interet_cpte"] > 0) && (! decisionClientDAT($value["id_cpte"])))
        array_push($listeDAT, $value);
    }
  }

  if (! empty($listeDAT))
    return $listeDAT;
  else
    return NULL;
}

function nbreJoursAvantEcheanceDAT($id_cpte_DAT) {
  /*
    Renvoie le nombre de jours restant à courir avant l'échéance du DAT
  */

  $today = mktime(0,0,0,date("m"), date("d"), date("y"));

  $InfoCpteDAT = getAccountDatas($id_cpte_DAT);
  $tmp_date = pg2phpDatebis($InfoCpteDAT["dat_date_fin"]); //array(mm,dd,yyyy)

  $date_fermeture = mktime(0,0,0, $tmp_date[0], $tmp_date[1], $tmp_date[2]);

  $nbre_jours_avant_echeance = ($date_fermeture - $today) / (3600 * 24);

  return $nbre_jours_avant_echeance;

}

function decisionClientDAT($id_cpte_DAT) {
  /*
    Renvoie true si le client a pris la décision concernant la prolongation ou non de son DAT
  */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT dat_decision_client FROM ad_cpt WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte_DAT;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  $row = $result->fetchrow();

  $dbHandler->closeConnection(true);

  $decision_client = $row[0];

  if ($decision_client == 't') return TRUE;
  else return FALSE;

}

function alerteEcheanceDAT($id_client) {
  /*
    Indique s'il faut déclencher l'alerte pour un client ayant un DAT prolongeable qui arrive à expiration et pour lequel il n'a pas pris de décision
    Renvoie TRUE s'il faut déclencher l'alerte
  */

  global $global_id_agence;

  $listeDATProlongeables = clientHasDATAttenteProlongation($id_client);

  // TF - 05/09/2002 : récupération de nombre de jours à partir duquel l'alerte sur un DAT doit se déclencher
  $AG = getAgenceDatas($global_id_agence);
  $nbr_jours_alerte = $AG["alerte_dat_jours"];
  // ** Fin ajout **

  if ($listeDATProlongeables != NULL)
    foreach($listeDATProlongeables as $value) {
    //cette boucle s'arrête bien évidemment au premier DAT remplissant la condition
    if (nbreJoursAvantEcheanceDAT($value["id_cpte"]) <= $nbr_jours_alerte)
      return TRUE;
  }

  return FALSE;
}

function decisionDAT($id_cpte_DAT, $decision) {
  /*
    Met à jour la BD lorsque le client a pris la décision de prolonger ou non le DAT.
    $decision est TRUE s'il faut prolonger et FALSE dans le cas contraire
  */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = buildUpdateQuery ("ad_cpt", array("dat_prolongation"=>($decision == true? 't' : 'f')), array("id_cpte"=>$id_cpte_DAT,'id_ag'=>$global_id_agence));
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }

  //dans tous les cas, on note que le client a pris sa décision
  $sql = buildUpdateQuery ("ad_cpt", array("dat_decision_client"=>'t', "dat_date_decision_client"=>date("d/m/Y")), array("id_cpte"=>$id_cpte_DAT,'id_ag'=>$global_id_agence));
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }

  $dbHandler->closeConnection(true);

}

function updateEtatCheque($id_cheque,$encaisse) {
  /*
    Met à jour le flag d'encaissement d'un chèque si $encaisse=TRUE
    Sinon, met à true le flag rejet
  */

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "UPDATE  ad_chq ";
  if ($encaisse == TRUE)
    $sql .= "SET encaisse=true ";
  else if ($encaisse == FALSE)
    $sql .= "SET rejet=true ";
  $sql .= "WHERE id_ag=$global_id_agence AND id=$id_cheque;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  $dbHandler->closeConnection(true);

}

 /**
  * Verse l'intéret sur un compte et alimente le array comptable
  * Ici, en principe, l'échéance est arrivée et on n'a pas à le vérifier
  *
  * @param int $id_cpte le compte pour lequel on paie
  * @param int $id_cpte_dest le compte de destination des intérets
  * @param int $interets montant à payer
  * @param array &$comptable tableau des écritures comptables
  * @return ErrObj Objet Erreur
  */
function payeInteret($id_cpte, $id_cpte_dest, $interets, &$comptable)
{
  // FIXME : que fait-on avec les comptes bloqués ?

  global $global_id_agence, $dbHandler, $global_monnaie;

  $db = $dbHandler->openConnection();

  $ACC = getAccountDatas($id_cpte);
  $devise = $ACC["devise"];
  $interets_calcules = 0;
  $interets_diff = 0;
  $cpte_cpta_int_paye = '';

  //calcul des interets a payer : #356
  $erreur = getIntCptEpargneCalculInfos($id_cpte);

  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  if(!is_null($erreur->param)) {
    $infosCalc = $erreur->param;
    $interets_calcules = $infosCalc['interets_calcules'];
    $cpte_cpta_int_paye = $infosCalc['cpte_cpta_int_paye'];
  }

  //versement de l'intérêt : débit compte charge / crédit compte client
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();

  $cpte_compta_int_prod = getCompteCptaProdEpInt($id_cpte);
  if ($cpte_compta_int_prod == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable des intérêts associé au produit d'épargne"));
  }

  $cpte_compta_assoc_prod = getCompteCptaProdEp($id_cpte_dest);
  if ($cpte_compta_assoc_prod == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }

  // Si on des interets calculee pour le compte, a comptabiliser
  if($interets_calcules > 0  && !is_null($cpte_cpta_int_paye))
  {
    $interets_diff = $interets - $interets_calcules;

    $cptes_substitue["cpta"]["debit"] = $cpte_cpta_int_paye;
    $cptes_substitue["cpta"]["credit"] = $cpte_compta_assoc_prod;
    $cptes_substitue["int"]["credit"] = $id_cpte_dest;

    // Comptabiliser les interets a calculer
    // Les intérts sont comptabilisés au débit en devise de référence, il faut donc appeler effectueChangePrivate en mettant la varialbe mnt_debit ) false car c'est le montant au crédit qui est fourni
    $erreur = effectueChangePrivate($global_monnaie, $devise, $interets_calcules, 40, $cptes_substitue, $comptable, false);

    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    // Comptabiliser la difference
    if ($interets_diff > 0)
    {
      $cptes_substitue["cpta"]["debit"] = $cpte_compta_int_prod;
      $cptes_substitue["cpta"]["credit"] = $cpte_compta_assoc_prod;
      $cptes_substitue["int"]["credit"] = $id_cpte_dest;

      $erreur = effectueChangePrivate($global_monnaie, $devise, $interets_diff, 40, $cptes_substitue, $comptable, false);

      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
    }
  }
  else  // Aucun interet calculee
  {
    $cptes_substitue["cpta"]["debit"] = $cpte_compta_int_prod;
    $cptes_substitue["cpta"]["credit"] = $cpte_compta_assoc_prod;
    $cptes_substitue["int"]["credit"] = $id_cpte_dest;

    // Les intérts sont comptabilisés au débit en devise de référence, il faut donc appeler effectueChangePrivate en mettant la varialbe mnt_debit ) false car c'est le montant au crédit qui est fourni
    $erreur = effectueChangePrivate($global_monnaie, $devise, $interets, 40, $cptes_substitue, $comptable, false);

    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
  }

  //FIXME : Doit-on mettre à jour solde calcul intérêts à solde ?

  // màj champ interets-annuels du compte
  // FIXME : il faut réinitialiser ce champ en fin d'exo
  $sql = "UPDATE ad_cpt SET interet_annuel = interet_annuel + $interets WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);

}

/**********
 * Cloture les comptes à terme (DAT et CAT) et les épargnes à la source actifs expirant à la date donnée
 * Ne fait pas la capitalisation des intérêts.
 * @author
 * @since 2.3
 * @param int $id_cpte numéro du compte à terme à clôturer
 * @param array $comptable, tableau modifiable de mouvements comptables
 * @param array $comptable_att , tableau modifiable de mouvements en attente
 * @param date $date_work la date du batch
 * @return array tableau associatif contenant le solde à la clôture et les frais de fermeture
 */
function clotureCompteTerme($id_cpte, &$comptable, &$comptable_att, $date_work) {
  global $dbHandler,$global_id_agence;
  global $global_id_client,$global_client_debiteur, $global_monnaie;

  /*Infos sur le compte à terme à clôturer */
  $InfoCpte = getAccountDatas($id_cpte);
  $solde_clot = $InfoCpte["solde"]; /* solde courant du compte à terme */
  $devise = $InfoCpte["devise"]; /* Devise du compte à terme */
  $cpte_virement = $InfoCpte["cpte_virement_clot"]; /* compte de virement à la clôture */

  /* Déduire du solde les mouvements dans le array $comptable */
  foreach($comptable as $cle=>$valeur) {
    if ($valeur['cpte_interne_cli'] == $id_cpte and $valeur['sens'] == SENS_CREDIT)
      $solde_clot = $solde_clot + $valeur['montant'];
    elseif($valeur['cpte_interne_cli'] == $id_cpte and $valeur['sens'] == SENS_DEBIT)
    $solde_clot = $solde_clot - $valeur['montant'];
  }
  /* Il faut également déduire les mouvements en attente s'il y en a pour ce compte */
  $fraisAttente = getFraisAttente($id_cpte);
  if (!empty($fraisAttente)) {
    foreach($fraisAttente as $frais) {
    	$solde_clot -= $frais['montant'];
    }
  }

  /* Infos sur le produit associé */
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  /* Infos sur le compte de base du titulaire du compte à terme */
  $id_cpte_base = getBaseAccountID($InfoCpte["id_titulaire"]);
  $CB = getAccountDatas($id_cpte_base);

  $db = $dbHandler->openConnection();

  /* traitement des frais de fermeture */
  $attente = false;
  $frais_ferme = $InfoProduit['frais_fermeture_cpt'];
  if ($frais_ferme > 0) {
    // Calcul de la TVA sur frais
    $type_operation_tva = 60;
    $taxesOperation = getTaxesOperation($type_operation_tva);
    $details_taxesOperation = $taxesOperation->param;
    if(sizeof($details_taxesOperation)>0){
      $mnt_TVA = $frais_ferme * $details_taxesOperation[1]['taux'];
    }

    /* si assez d'argent pour prendre les frais de fermeture */
    if ($solde_clot > $frais_ferme + $mnt_TVA) {

      //débit compte de base / crédit compte de produit
      $type_ope = 60;
      $subst = array();
      $subst["cpta"] = array();
      $subst["int"] = array();
      $subst["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
      if ($subst["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé au produit d'épargne"));
      }
      $subst["int"]["debit"] = $id_cpte;

      $myErr = reglementTaxe($type_ope, $frais_ferme, SENS_CREDIT, $devise, $subst, $comptable);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
      // prelevemetn des frais  de fermeture
      $cptes_substitue = array();
      $cptes_substitue["cpta"] = array();
      $cptes_substitue["int"] = array();
      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }
      $cptes_substitue["int"]["debit"] = $id_cpte;
      $erreur = effectueChangePrivate($devise, $global_monnaie, $frais_ferme, 60, $cptes_substitue, $comptable,true,NULL,$id_cpte);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
      /* Diminuer le solde des frais de fermeture */
      $solde_clot -= $frais_ferme + $mnt_TVA;
    } else {
      // Si les frais sont plus grands que le solde, il faudra faire une fermeture manuelle pour résoudre le problème */
      $new_etat = 5;
      $attente = true;
    }
  }

  /* Virement du solde du compte à clôturer */
  if ($solde_clot > 0 && !$attente) {
    /* Si un compte de virement actif est paramétré */
    if ($cpte_virement != NULL) {
      $cpt_vir_infos = getAccountDatas($cpte_virement);
      if ($cpt_vir_infos["etat_cpte"] == 1)
        $devise_cv = $cpt_vir_infos["devise"]; /* Devise du compte de virement */
      else {
        $cpte_virement = NULL;
        $devise_cv = NULL;
      }
    }

    /* Si aucun compte de virement n'est paramétré ou si celui est inactif, On essaie de virer sur le compte de base si possible */
    if ($cpte_virement == NULL) {
      /* Possible seulement si la devise du compte à cloturer est la devise de référence et si le compte de base est bien actif */
      if (($devise == $global_monnaie) && ($CB["etat_cpte"] == 1)) {
        $cpte_virement = $id_cpte_base;
        $devise_cv = $global_monnaie;
      }
    }

    /* Si un compte de virement adéquat est trouvé  */
    if ($cpte_virement != NULL) {
      $cptes_substitue = array();
      $cptes_substitue["cpta"] = array();
      $cptes_substitue["int"] = array();

      $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
      if ($cptes_substitue["cpta"]["debit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }

      $cptes_substitue["int"]["debit"] = $id_cpte;
      $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($cpte_virement);
      if ($cptes_substitue["cpta"]["credit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }

      $cptes_substitue["int"]["credit"] = $cpte_virement;

      if ($devise == $devise_cv)
        $erreur = passageEcrituresComptablesAuto(62, $solde_clot, $comptable, $cptes_substitue, $devise,NULL,$id_cpte);
      else
        $erreur = effectueChangePrivate($devise, $devise_cv, $solde_clot, 62, $cptes_substitue, $comptable,TRUE,NULL,$id_cpte);

      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }

      $new_etat = 2; /* Le comtpe est bien fermé */
    } else { /* Un compte de virement adéquat n'est pas trouvé */
      /*Le compte passe à 'En attente fermeture manuelle'.On fait la fermeture temporaire sans constation de mouvements */
      $new_etat = 5;
      $attente = true;
    }
  } else {
    /* solde de cloture null ou négatif ! */
    /* il faudra faire une fermeture manuelle pour résoudre le problème */
    $new_etat = 5;
    $attente = true;
  }

  // Fermeture du compte proprement dite
  $sql = "UPDATE ad_cpt SET etat_cpte = $new_etat WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  //mise à jour raison clôture
  $sql = "UPDATE ad_cpt ";
  $sql .= "SET raison_clot = 4 ";//FIXME: ce 4 n'est pas portable
  $sql .= "WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  };

  //mise à jour date de clôture : le jour du batch
  $sql = "UPDATE ad_cpt ";
  $sql .= "SET date_clot = '".php2pg($date_work)."', ";
  $sql .= "solde_clot = $solde_clot ";
  $sql .= "WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte;";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }

  //écriture dans l'historique

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, array('solde_cloture' => $solde_clot, 'mnt_frais_fermeture' => $frais_ferme, 'attente' => $attente, 'cpte_virement' => $cpte_virement));

}

/**
 * Effectue la prolongation d'un compte à terme
 * @author Papa Ndiaye
 * @param int $id_cpte ID du compte à prolonger
 * @return ErrorObj Objet Erreur contenant le solde du compte
 */
function prolongeCompteTerme($id_cpte) {
  global $dbHandler, $date_total,$global_id_agence;

  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
   $solde_arrete = $InfoCpte['solde'];

  /* le produit est-il prolongeable ? */
  if ($InfoProduit["dat_prolongeable"] != 't')
    return new ErrorObj(ERR_DAT_NON_PROLONGEABLE);

  /* Mettre à jour: la prochaine échéance, la date de début comtabilisation des intérêts, prolongation, nbre prolongation */
  $tmp_date = pg2phpDatebis($InfoCpte["dat_date_fin"]); //array(mm,dd,yyyy)
  $nouvelle_date_fin = date("d/m/Y", mktime(0,0,0, $tmp_date[0] + $InfoCpte["terme_cpte"], $tmp_date[1], $tmp_date[2]));

  $db = $dbHandler->openConnection();
  
  $dataUpdate ['dat_nb_reconduction'] =  $InfoCpte['dat_nb_reconduction']-1; 
  $decision_reconduction = ($dataUpdate ['dat_nb_reconduction'] > 0) ? 't':'f';
  $dataUpdate ['dat_prolongation'] = $decision_reconduction;
  $dataUpdate ['dat_decision_client'] = $decision_reconduction;
  $dataUpdate ['dat_nb_prolong'] = $InfoCpte['dat_nb_prolong'] + 1;
  $dataUpdate ['dat_date_fin'] = $nouvelle_date_fin;
  $sql = buildUpdateQuery('ad_cpt', $dataUpdate, array('id_ag'=>$global_id_agence , 'id_cpte'=>$id_cpte));
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('solde_arrete'=> $solde_arrete));
}

/**
 * Effectue la cloture d'un compte d'épargne service financier
 * @param int $id_cpte ID du compte à cloturer
 * @param int $raison_cloture Raison pour laquelle le compte est cloturé (cfr adsys_raison_cloture)
 * @param defined(1,2) $dest : Destinationd es fonds (1 = guichet, 2 = autre compte)
 * @param int $id_cpte_dest Si $dest = 2, ID du comtpe de destination. Si NULL et $dest =2 et le compte de base n'est pas actif, on considère qu'il s'agit d'une cloture par batch et le compte n'est pas réellement cloturé mais les intér ne seront plus capitalisés.
 * @param array $comptable Un éventuel array comptable de mouvements précédant
 * @param array $frais Contient les frais modifiés par l'utilisateur s'il y a lieu
 * @return ErrorObj Objet Erreur
 */
function clotureCompteEpargne($id_cpte, $raison_cloture, $dest, $id_cpte_dest, &$comptable,$frais=array()) {

  global $dbHandler, $global_id_client, $global_id_agence, $global_nom_login, $global_monnaie;

  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  // Bloquer d'abord le compte pour qu'il n'y ait pas d'opérations financières dessus
  blocageCompteInconditionnel($id_cpte);

  // A partir de ce moment, nous sommes à l'intérieur d'une transaction, les autres utilisateurs voient le compte comme bloqué
  $db = $dbHandler->openConnection();

  deblocageCompteInconditionnel($id_cpte);

  $erreur = checkCloture($id_cpte);

  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  if (isset($frais["fermeture"]) && check_access(299))
    $frais_fermeture_modif = $frais["fermeture"];

  if (isset($frais["tenue"]) && check_access(299))
    $frais_tenue_modif = $frais["tenue"];

  if (isset($frais["penalites"]) && check_access(299))
    $penalites_modif = $frais["penalites"];

  $id_cpte_base =  getBaseAccountID ($InfoCpte["id_titulaire"]);

  $devise = $InfoCpte["devise"];
  $dev_ref = $global_monnaie;

  $RET = array(); // Tableau qui sera renvoyé à l'appelant

  // Si le compte était en attente de fermeture, on procède directement au virement du solde
  if ($InfoCpte["etat_cpte"] == 5) {
    $solde_cloture = $InfoCpte["solde"];
  } else {
    // calcul des intérêts en fonction du paramétrage du produit
    // si 30j ou Fin de mois, et en cas de rupture anticipée : aucun, prorata, tout

    // Dans le cadre d'une cloture, les intérets sont toujours versés sur le compte lui-meme
    $InfoCpte["cpt_vers_int"] = $InfoCpte["id_cpte"];

    $erreur = arreteCompteEpargne($InfoCpte, $InfoProduit, $comptable);

    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    $solde_cloture = $erreur->param["solde_cloture"];
    $RET["mnt_int"] = $erreur->param["int"];

    // Prélèvement des pénalités
    if ($InfoProduit["terme"] > 0) {
      $erreur = prelevePenalitesEpargne($id_cpte, $comptable, $penalites_modif);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
      $solde_cloture -= $erreur->param;
      $RET["mnt_pen"] = $erreur->param;
    }

    // Frais de tenue de compte
    $erreur = preleveFraisDeTenue($id_cpte, $comptable, $frais_tenue_modif);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
    $solde_cloture -= $erreur->param;
    $RET["mnt_frais_tenue"] = $erreur->param;

    //frais de fermeture
    $erreur = preleveFraisFermeture($id_cpte, $comptable, $frais_fermeture_modif);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }
    $solde_cloture -= $erreur->param;
    $RET["mnt_frais_fermeture"] = $erreur->param;
  }

  // Cas spécifique de la cloture par le batch : dans ce cas $dest = 2 mais aucun compte n'a été spécifié

  if ($dest == 2 && $id_cpte_dest == NULL) {
    global $global_cpt_base_ouvert;
    if ($devise == $dev_ref) { // On peut transférer sur le compte de base
      if ($global_cpt_base_ouvert) {
        $id_cpte_dest = getBaseAccountID($InfoCpte["id_titulaire"]);
        $attente_cloture = false;
      } else {
        $attente_cloture = true;
      }
    } else {
      $attente_cloture = true;
    }
  } else {
    $attente_cloture = false;
  }

  if ($attente_cloture == true) {
    // Il faut mettre le compte dans un état intermédiaire. On ne veut pas forcer la conversion
    $updateFields = array("etat_cpte" => 5);
    $where = array("id_cpte" => $id_cpte,'id_ag'=>$global_id_agence);
    $sql = buildUpdateQuery("ad_cpt", $updateFields, $where);
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
    }
    $RET['attente'] = true;
  }

  else {

    // Virement du solde du compte à clôturer
    $erreur = vireSoldeCloture ($id_cpte, $solde_cloture, $dest, $id_cpte_dest, $comptable);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

    if(($raison_cloture != 2 ) || ($InfoProduit["classe_comptable"] != 6)){// on ne ferme pas le compte pour les épargnes à la source si c'est une demande du client
    	//fermeture du compte, raison clôture "Sur demande du client"
    	$erreur = fermeCompte($id_cpte, $raison_cloture, $solde_cloture);
    	if ($erreur->errCode != NO_ERR) {
    		$dbHandler->closeConnection(false);
    		return $erreur;
    	}
    }else{// on intialise certains champs (solde de calcul des intérêts,...) pour les épargnes à la source en cas de demande du client
    	$updateFields = array("solde_calcul_interets" => 0, "date_calcul_interets"=>date("d/m/Y"), "date_solde_calcul_interets"=>date("d/m/Y"), "interet_a_capitaliser"=>0, "interet_annuel"=>0);
    	$where = array("id_cpte" => $id_cpte,'id_ag'=>$global_id_agence);
    	$sql = buildUpdateQuery("ad_cpt", $updateFields, $where);
    	$result = $db->query($sql);
    	if (DB::isError($result)) {
    		$dbHandler->closeConnection(false);
    		signalErreur(__FILE__,__LINE__,__FUNCTION__); //  $result->getMessage()
    	}
    }
    $RET['attente'] = false;
  }

  $RET["solde_cloture"] = $solde_cloture;

  // Invalidation des mandats liés au compte
  $MANDATS = getMandats($id_cpte);
  if (is_array($MANDATS))
    foreach ($MANDATS as $key=>$value) {
    invaliderMandat($key);
  }

  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $RET);
}

function arreteCompteEpargne($InfoCpte=array(), $InfoProduit=array(), &$comptable) {
  /*
    Calcul les intérêts pour les comptes rémunérés DAV, DAT, Autres dépôts, Capital social arrivés à échéance
    en principe appelée pour une rupture anticipée
    OUT  objet Error contenant en paramètre le solde de cloture
  */

  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  /* Récupération du taux de base de l'épargne de l'agence */
  $AG = getAgenceDatas($global_id_agence);
  if ($AG["base_taux_epargne"] == 1)
    $base_taux = 360;
  elseif($AG["base_taux_epargne"] == 2)
  $base_taux = 365;

  /* Initialisation des intérêts à la rupture */
  $interets = 0;

  /* Si c'est un compte à terme ( DAT ou CAT ) qui n'est pas en attente */
  if ($InfoCpte["terme_cpte"] > 0 and $InfoCpte["etat_cpte"] != 5) {
    $today = date("d/m/Y");
    $temp_today = explode("/", $today);
    $temp_today = mktime(0,0,0,$temp_today[1],$temp_today[0],$temp_today[2]);

    $date_fin = pg2phpDate($InfoCpte["dat_date_fin"]);
    $temp_date_fin = explode("/", $date_fin);
    $temp_date_fin = mktime(0,0,0,$temp_date_fin[1],$temp_date_fin[0],$temp_date_fin[2]);

    /* s'agit-il d'une rupture anticipée ? */
    if ($temp_today <= $temp_date_fin) {
      if ($InfoProduit["mode_calcul_int_rupt"]== 1)  /* Sans intérêts à la rupture */
        $interets = 0 ;
      elseif ($InfoProduit["mode_calcul_int_rupt"] == 2 or $InfoProduit["mode_calcul_int_rupt"] == 3) {
        /* Intérêts au prorata ou Intérêts sur le reste du terme */
        $date_ouv = $InfoCpte["date_ouvert"];
        $date_las_cap = $InfoCpte["date_calcul_interets"];
        $mode_paie = $InfoCpte["mode_paiement_cpte"];
        $freq_cap = $InfoCpte["freq_calcul_int_cpte"];
        $terme = $InfoCpte["terme_cpte"];

        /* Intérêts au prorata, récupérer nombre de mois entre date du jour et dernière capitalisation (ou date ouverture)  */
        if ($InfoProduit["mode_calcul_int_rupt"] == 2)
          $date_cap = date("d/m/Y");

        /* Intérêts pour le reste du terme, récupérer nombre de mois entre date fin du compte  et dernière capitalisation  */
          if ($InfoProduit["mode_calcul_int_rupt"] == 3)
          if (isset($InfoCpte["dat_date_fin"]))
          $date_cap = $InfoCpte["dat_date_fin"];
          else
          $date_cap = date("d/m/Y");

          $nb_jours = getJoursCapitalisation($date_cap, $date_ouv, $date_las_cap, pg2phpDate($InfoCpte["dat_date_fin"]));

          //Jira MAE-22/27 formule calcul interet
          if ($AG['appl_date_val_classique'] == 't'){
            $interets = ($InfoCpte['solde_calcul_interets'] * $InfoCpte["tx_interet_cpte"] * $nb_jours)/ $base_taux;
          }
          else{
            /* Calcul des intérêts à payer à la rupture */
            $interets = ($InfoCpte['solde_calcul_interets'] * $InfoCpte["tx_interet_cpte"] * $nb_jours)/ $base_taux;
            if($InfoCpte['mode_calcul_int_cpte'] == 12){// Si mode épargne à la source, intérêts prend la valeur du champs interet_a_capitaliser qui cumule les intérêts entre deux dates de capitalisation
              $interets = $InfoCpte['interet_a_capitaliser'];
            }
          }
          // #356 : ne pas arrondir
          // REL-101 : arrondir les interets
          $interets = arrondiMonnaie($interets, 0, $InfoCpte['devise']);
      }else $interets = 0;

    } /* Fin si date du jour < date de fin  */

    /* Si le compte de versement des intérêts n'est pas renseigné, prendre le compte lui-même */
    if (!isset($InfoCpte["cpt_vers_int"]) or $InfoCpte["cpt_vers_int"] == NULL)
      $InfoCpte["cpt_vers_int"] = $InfoCpte["id_cpte"];

    if ($InfoCpte["cpt_vers_int"] == $InfoCpte["id_cpte"])
      $solde_cloture = $InfoCpte["solde"] + $interets;
    else
      $solde_cloture = $InfoCpte["solde"];

    /* Versement des intérêts */
    if ($interets > 0) {
      $erreur = payeInteret($InfoCpte["id_cpte"], $InfoCpte["cpt_vers_int"], $interets, $comptable);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
    }
  } else { /* C'est pas un compte à terme */
    /* Pas de calcul d'intérêts ni de pénalités */
    $solde_cloture = $InfoCpte["solde"]; /* solde courant du compte */

    /* Cas des comptes de garantie.On considère que le crédit est soldé et on qu'on veut clôturer le compte de garantie */
    /* On considère que solde clôture = solde courant + les garanties incluses dans les derniers remboursements non encore commités */

    if ($InfoCpte["id_prod"]== 4) { /* Si c'est un compte de garantie */
      /* Parcours des écritures comptables en attente */
      if (is_array($comptable))
        foreach($comptable as $key=>$value) {
        /* S'il y a des mouvements en attente pour le compte de garantie */
        if ($value["cpte_interne_cli"] == $InfoCpte["id_cpte"]) {
          if ($value["sens"] == SENS_CREDIT and $value["montant"] > 0)
            $solde_cloture += $value["montant"];
          elseif($value["sens"] == SENS_DEBIT and $value["montant"] > 0)
          $solde_cloture -= $value["montant"];
        }
      }
    }
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('solde_cloture' => $solde_cloture, 'int' => $interets));

}

/**
 * Vire le solde d'un compte une fois celui-ci cloturé
 * Appelé dans le cadre d'une cloture par transfert
 * Peut  amené à liquider un reste en devise de référence si les billets disponibles ne permettent pas une liquidation en cash
 * @param int $id_cpte ID du compte cloturé
 * @param defined (1,2) $dest Destination des fonds (cfr {@link #clotureCompteEpargne}
 * @param int $id_cpte_dest Id du comtep de destinationd es fonds
 * @param array $comptable
 */
function vireSoldeCloture($id_cpte, $solde_cloture, $dest, $id_cpte_dest, &$comptable, $type_oper=NULL) {

  global $dbHandler, $global_id_guichet, $global_monnaie;

  $ACC = getAccountDatas($id_cpte);
  $classe_comptable = $ACC["classe_comptable"];
  $devise = $ACC["devise"];
  $dev_ref = $global_monnaie;

  $db = $dbHandler->openConnection();

  if ($solde_cloture != 0) {

    if($type_oper == NULL ) {
    	switch ($classe_comptable) {
    		case 1:
		    case 2:
		    case 3:
		    case 5:
		    case 6:
		      $type_oper = ($dest == 1? 61 : 62);
		      break;
		    case 4:
		      $type_oper = 81;
		      break;

		    default:
		      $dbHandler->closeConnection(false);
		      signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Classe comptable incorrecte !"
    	}
    }

    // Passage écritures comptables
    //débit compte client / crédit compte de base client
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }
    $cptes_substitue["int"]["debit"] = $id_cpte;

    if ($dest == 1) { // Destination guichet
      $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($global_id_guichet);

      // Traitement des arrondis
      $mnt_dec = arrondiMonnaie($solde_cloture, -1, $devise);
      if ($solde_cloture != $mnt_dec && $devise != $dev_ref) {
        $diff = $solde_cloture - $mnt_dec;
        $diff_dev_ref = calculeCV($devise, $dev_ref, $diff);
        if ($diff_dev_ref > 0) {
          // Passer d'abord une écriture de change pour le reliquiat
          $myErr = effectueChangePrivate($devise, $dev_ref, $diff, 455, $cptes_substitue, $comptable);
          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          }
          $solde_cloture -= $diff;
        }
      }

      $erreur = passageEcrituresComptablesAuto ($type_oper, $solde_cloture, $comptable, $cptes_substitue, $devise);
      if ($erreur->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $erreur;
      }
    } else { // Destination $id_cpte_dest
      $cptes_substitue["cpta"]["credit"] = getCompteCptaProdEp($id_cpte_dest);
      if ($cptes_substitue["cpta"]["credit"] == NULL) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
      }
      $cptes_substitue["int"]["credit"] = $id_cpte_dest;

      /* Vérifier que les comptes source et destination ont la même devise */
      $CPT_DEST = getAccountDatas($id_cpte_dest);
      if ($devise == $CPT_DEST['devise']) {
        $erreur = passageEcrituresComptablesAuto ($type_oper, $solde_cloture, $comptable, $cptes_substitue, $devise);
        if ($erreur->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $erreur;
        }
      } else { /* les comptes sont de devises différentes */
        $myErr = effectueChangePrivate($devise, $CPT_DEST['devise'], $solde_cloture, $type_oper, $cptes_substitue, $comptable);
        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }
    }
  }


  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);

}

/**
 * Retire les pénalités en cas de rupture anticipée d'un compte d'épargne
 */
function prelevePenalitesEpargne($id_cpte, &$comptable, $penalites=NULL) {
    global $dbHandler, $global_monnaie_prec, $global_client_debiteur, $global_monnaie;

  $ACC = getAccountDatas($id_cpte);
  $PROD = getProdEpargne($ACC["id_prod"]);

  // On vérifie d'abord qu'il s'agit bien d'un compte à terme
  if ($PROD["terme"] > 0) {

    $db = $dbHandler->openConnection();

    $cpte_date_fin = $ACC["dat_date_fin"];
    $solde = $ACC["solde"];
    if (isset($penalites)) {
      $penalites_const = $penalites;
      $penalites_prop = 0;
    } else {
      $penalites_const = $PROD["penalite_const"];
      $penalites_prop = $PROD["penalite_prop"];
    }
    $devise = $ACC["devise"];
    $dev_ref = $global_monnaie;
    $DEV = getInfoDevise($devise);

    $today = date("d/m/Y");
    $today = explode("/", $today);
    $today = mktime(0,0,0,$today[1],$today[0],$today[2]);

    $date_fin = pg2phpDate($cpte_date_fin);
    $date_fin = explode("/", $date_fin);
    $date_fin = mktime(0,0,0,$date_fin[1],$date_fin[0],$date_fin[2]);

    //si rupture anticipée
    if ( $date_fin > $today ) {

      if (($penalites_const > 0) || ($penalites_prop > 0)) {
        //FIXME : on prend quel solde pour calculer les pénalités ?
        $penalites = ($penalites_const + ($solde * $penalites_prop));
        $penalites = round($penalites, $DEV["precision"]);

        if ($penalites > 0) {
          //FIXME : est-ce que c'est la bonne manière de faire ?
          //Si le client est débiteur , on ne pourra pas prendre les penalites  sur le compte de base
          /* OBSOLETE
          if ($global_client_debiteur)
            {
              $dbHandler->closeConnection(false);
              return new ErrorObj(ERR_CLIENT_DEBITEUR);
              } */

          //débit compte à cloturer / crédit compte de produit
          $cptes_substitue = array();
          $cptes_substitue["cpta"] = array();
          $cptes_substitue["int"] = array();

          $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
          if ($cptes_substitue["cpta"]["debit"] == NULL) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
          }

          $cptes_substitue["int"]["debit"] = $id_cpte;

          $erreur = effectueChangePrivate($devise, $dev_ref, $penalites, 110, $cptes_substitue, $comptable);
          if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
          }

        }//if pénalités > 0

      }//if pénalités

    } //if date > today
    $dbHandler->closeConnection(true);
  } else {
    return new ErrorObj(ERR_NON_CAT);
  }


  return new ErrorObj(NO_ERR, $penalites);
}

/**
 * Prélève les frais de tenue de compte
 * @param int $id_cpte ID du compte
 * @param array $comptable
 */
function preleveFraisDeTenue($id_cpte, &$comptable, $frais_tenue= NULL) {
  global $dbHandler, $global_client_debiteur, $global_monnaie;

  $ACC = getAccountDatas($id_cpte);
  $PROD = getProdEpargne($ACC["id_prod"]);

  if (!isset($frais_tenue))
    $frais_tenue = $PROD["frais_tenue_cpt"];

  $devise = $ACC["devise"];
  $dev_ref = $global_monnaie;

  $db = $dbHandler->openConnection();

  if ($frais_tenue > 0) {
    //ne pas mvter les cptes si le montant est nul
    $type_ope = 50;
    $subst = array();
    $subst["cpta"] = array();
    $subst["int"] = array();
    $subst["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($subst["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé au produit d'épargne"));
    }
    $subst["int"]["debit"] = $id_cpte;

    $myErr = reglementTaxe($type_ope, $frais_tenue, SENS_CREDIT, $devise, $subst, $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
    $montant_tva = $myErr->param['montant_credit'];

    //débit compte de base / crédit compte de produit
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte;

    $erreur = effectueChangePrivate($devise, $dev_ref, $frais_tenue, 50, $cptes_substitue, $comptable);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

  }
  if ($montant_tva >0){
    $frais_tenue += $montant_tva;
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, $frais_tenue);

}

function preleveFraisFermeture($id_cpte, &$comptable, $frais_fermeture = NULL) {
  /*
    Lors de la fermeture d'un compte d'épargne, on prend les frais de fermeture s'il y en a
  */

  global $dbHandler, $global_client_debiteur, $global_monnaie;

  $ACC = getAccountDatas($id_cpte);
  if (!isset($frais_fermeture))
    $frais_fermeture = $ACC["frais_fermeture_cpt"];

  $devise = $ACC["devise"];
  $dev_ref = $global_monnaie;

  $db = $dbHandler->openConnection();

  if ($frais_fermeture > 0) {
    // Calcul de la TVA sur frais
    /*$taxesOperation = getTaxesOperation($type_operation);
    $details_taxesOperation = $taxesOperation->param;
    if(sizeof($details_taxesOperation)>0){
      $mnt_TVA = $frais_fermeture * $details_taxesOperation[1]['taux'];
    }
    */
    //débit compte de base / crédit compte de produit
    $type_ope = 60;
    $subst = array();
    $subst["cpta"] = array();
    $subst["int"] = array();
    $subst["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($subst["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé au produit d'épargne"));
    }
    $subst["int"]["debit"] = $id_cpte;

    $myErr = reglementTaxe($type_ope, $frais_fermeture, SENS_CREDIT, $devise, $subst, $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
    $montant_tva = $myErr->param['montant_credit'];


    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $id_cpte;

    $erreur = effectueChangePrivate($devise, $dev_ref, $frais_fermeture, 60, $cptes_substitue, $comptable);
    if ($erreur->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $erreur;
    }

  }//if frais fermeture > 0

  if ($montant_tva >0){
    $frais_fermeture += $montant_tva;
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $frais_fermeture);

}

/**
 * Modification des informations du compte
 *
 * @author Mamadou Mbaye, Antoine Delvaux, Saourou MBODJ
 * @param int $a_decouvert nouveau decouvert
 * @param int $a_id_cpte Le numéro du compte
 * @param int $a_frais Les frais à prélever lors de la modification de ce découvert
 * @param int $mnt_min le montant minimum du compte
 * @param int $cpt_vers_int le compte dans lequel on verse les intérêts
 * @return ErrorObj Objet Erreur
 */
function updateInfosCompte($intitule_compte, $a_decouvert, $a_id_cpte, $a_frais,$mnt_min,$cpt_vers_int, $cpte_virement_clot, $export_netbank = false) {
  global $dbHandler,$global_id_agence,$global_nom_login;
  $comptable = array();

  $db = $dbHandler->openConnection();

  // Informations courantes du compte
  $infos_cpte = getAccountDatas($a_id_cpte);

  // Prélèvement des frais de dossier de découvert
  $err = preleveFraisDecouvert($a_id_cpte, $comptable, $a_frais);
  if ($err->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $err;
  }

  // Création du tableau pour la mise à jour des données
  $new_values = array();
  $new_values["intitule_compte"] = $intitule_compte; // Nouvel intitulé
  $new_values["decouvert_max"] = $a_decouvert; // Nouveau découvert
  $new_values["mnt_min_cpte"]  =$mnt_min;      //Nouveau montant minimum
  $new_values["cpt_vers_int"]  =$cpt_vers_int;      //Nouveau montant minimum
  $new_values["cpte_virement_clot"]  =$cpte_virement_clot;      //Compte de virement à la clôture
  $new_values["export_netbank"] = $export_netbank; //pour export netbank
  if ($infos_cpte["decouvert_date_util"] != NULL) {
    // Le découvert était déjà utilisé, mais on place sa date de première utilisation à aujourd'hui
    $new_values["decouvert_date_util"] = date("d/m/Y");
  }

  // Appelle de la fontion de mise a jour des données
  $sql = buildUpdateQuery ("ad_cpt", $new_values, array("id_cpte"=>$a_id_cpte,'id_ag'=>$global_id_agence));
  $result = executeQuery($db, $sql);
  if ($result->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $result;
  }

  $err = ajout_historique(88, $infos_cpte["id_titulaire"], '', $global_nom_login, date("r"), $comptable, NULL, NULL);
  if ($err->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $err;
  }
  $id_his = $err->param;
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, array('id'=>$id_his));
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
 * @author Thomas Fastenakel, Antoine Delvaux
 * @param int $a_id_cpte : Compte d'épargne concerné
 * @param array $comptable Liste de mouvements comptables précédemment enregistrés et qui sera finalement passée à ajout_historique.
 * @param bool $a_frais Le montant des frais demandés ou NULL si ce sont les frais lors d'une opération qui sont prélevés.
 * @return ErrorObj Objet Erreur avec les frais prélevés en paramètre
 */
function preleveFraisDecouvert($a_id_cpte, &$comptable, $a_frais = NULL) {
  global $global_monnaie;
  $cpte = getAccountDatas($a_id_cpte);
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
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($a_id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $a_id_cpte;
    $err = effectueChangePrivate($cpte["devise"], $global_monnaie, $frais, 470, $cptes_substitue, $comptable);
    if ($err->errCode != NO_ERR) {
      return $err;
    }
  }

  return new ErrorObj(NO_ERR, $frais);
}

/**
 * Fonction permettant de connaître le nombre de crédits non soldés dont le compte passé en paramètre est le compte de liaison et/ou le compte de prélèvement de garantie
 * @author Antoine Guyette
 * @param integer $id_cpte identifiant d'un compte de client
 * @return integer nombre de crédits répondant aux exigences
 */
function nbrCredAttache($id_cpte) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select count(*) from ad_dcr d, ad_gar g WHERE d.id_ag=g.id_ag and  d.id_ag=$global_id_agence AND d.id_doss = g.id_doss and d.etat <> '6' and (d.cpt_liaison = '$id_cpte' or g.gar_num_id_cpte_prelev = '$id_cpte');";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $row = $result->fetchrow();

  $nbr = $row[0];

  $dbHandler->closeConnection(true);

  return $nbr;
}

/**
 * Fonction permettant de bloquer un compte
 * @author Antoine Guyette
 * @author Djibril NIANG (voir #1549)
 * since 3.2
 * @param integer $id_cpte identifiant d'un compte de client
 * @return ErrorObj Objet Erreur
 */
function bloquerCompte($id_cpte, $raison = NULL, $login = NULL, $type_blocage = NULL) {
  global $dbHandler,$global_id_agence,$global_nom_login;
  $db = $dbHandler->openConnection();

  $ACC = getAccountDatas($id_cpte);
  $etat_cpte = $ACC['etat_cpte'];
  $id_prod = $ACC['id_prod'];

  // Compte fermé
  if ($etat_cpte == "2") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_FERME);
  }

  // Compte bloqué
  if($type_blocage != 3){
  	if ($etat_cpte == "3") {
	    $dbHandler->closeConnection(false);
	    return new ErrorObj(ERR_CPTE_BLOQUE);
	  }
  }

  // Compte dormant
  if ($etat_cpte == "4") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_DORMANT);
  }

  // Compte en attente de fermerure manuelle
  if ($etat_cpte == "5") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_ATT_FERM);
  }

  // Compte état inexistant
  if ($etat_cpte > "7") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_GENERIQUE);
  }

  // Compte de part sociale
  if ($id_prod == "2") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_PART_SOC);
  }

  // Compte d'épargne nantie
  if ($id_prod == "4") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_GARANTIE);
  }

  // Compte lié à un crédit non soldé et/ou compte source de prélèvement pour un crédit non soldé
  if (nbrCredAttache($id_cpte) != 0) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_GENERIQUE);
  }

  // Compte ouvert
  $date_encours=date("d/m/Y");
  if($type_blocage != NULL){
  	if (($raison==NULL) && ($login==NULL)){
  	$sql = "update ad_cpt set etat_cpte = $type_blocage where id_ag=$global_id_agence AND id_cpte = '$id_cpte';";
	  } else {
	  	$sql = "update ad_cpt set etat_cpte = $type_blocage,date_blocage= '$date_encours', raison_blocage = '$raison', utilis_bloquant = '$login' where id_ag=$global_id_agence AND id_cpte = '$id_cpte';";
	  }
  } else {
  	if (($raison==NULL) && ($login==NULL)){
  	$sql = "update ad_cpt set etat_cpte = '3' where id_ag=$global_id_agence AND id_cpte = '$id_cpte';";
	  } else {
	  	$sql = "update ad_cpt set etat_cpte = '3',date_blocage= '$date_encours', raison_blocage = '$raison', utilis_bloquant = '$login' where id_ag=$global_id_agence AND id_cpte = '$id_cpte';";
	  }
  }


  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $myErr = ajout_historique(89, $ACC['id_titulaire'], '', $global_nom_login, date('r'));

  $id_his = $myErr->param;

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('id'=>$id_his));

}

/**
 * Fonction permettant de debloquer un compte
 * @author Antoine Guyette
 * @author Djibril NIANG (voir #1549)
 * since 3.2
 * @param integer $id_cpte identifiant d'un compte de client
 * @return ErrorObj Objet Erreur
 */
function debloquerCompte($id_cpte, $type_deblocage = NULL) {
  global $dbHandler, $global_nom_login,$global_id_agence;
  $db = $dbHandler->openConnection();

  $ACC = getAccountDatas($id_cpte);
  $etat_cpte = $ACC['etat_cpte'];
  $id_prod = $ACC['id_prod'];

  // Compte ouvert
  if ($etat_cpte == "1") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_OUVERT);
  }

  // Compte fermé
  if ($etat_cpte == "2") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_FERME);
  }

  // Compte dormant
  if ($etat_cpte == "4") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_DORMANT);
  }

  // Compte en attente de fermerure manuelle
  if ($etat_cpte == "5") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_ATT_FERM);
  }

  // Compte état inexistant
  if ($etat_cpte > "7") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_GENERIQUE);
  }

  // Compte de part sociale
  if ($id_prod == "2") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_PART_SOC);
  }

  // Compte d'épargne nantie
  if ($id_prod == "4") {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_GARANTIE);
  }

  // Compte lié à un crédit non soldé et/ou compte source de prélèvement pour un crédit non soldé
  if (nbrCredAttache($id_cpte) != 0) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_GENERIQUE);
  }
  //définir le type de déblocage
  if($type_deblocage == 3){
  	//déblocage dépôts et retraits
  	$sql = "update ad_cpt set etat_cpte = '1' where id_ag=$global_id_agence AND id_cpte = '$id_cpte';";
  } else if($type_deblocage == 6 && $etat_cpte == "3"){
  	//déblocage dépôts
  	$sql = "update ad_cpt set etat_cpte = '7' where id_ag=$global_id_agence AND id_cpte = '$id_cpte';";
  } else if($type_deblocage == 7 && $etat_cpte == "3"){
  	//déblocage retraits
  	$sql = "update ad_cpt set etat_cpte = '6' where id_ag=$global_id_agence AND id_cpte = '$id_cpte';";
  }
  else {
  	$sql = "update ad_cpt set etat_cpte = '1' where id_ag=$global_id_agence AND id_cpte = '$id_cpte';";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $myErr = ajout_historique(89, $ACC['id_titulaire'], '', $global_nom_login, date('r'));

  $id_his = $myErr->param;

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, array('id'=>$id_his));

}

/**
 * Ajouter un mandat dans la base de donnée
 * @author Antoine Guyette
 * @param Array $DATA données sur le mandat
 * @return ErrorObj
 */
function ajouterMandat($DATA) {
  global $dbHandler,$global_id_agence, $global_nom_login, $global_id_client;
  $db = $dbHandler->openConnection();

  $DATA['valide'] = 't';
  $DATA['id_ag'] = $global_id_agence;

  $sql = buildInsertQuery('ad_mandat', $DATA);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  
  // Enregistrement - Ajout d'un mandat
  ajout_historique(95, $global_id_client, 'Ajout d\'un mandat', $global_nom_login, date("r"), NULL);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Modifier un mandat dans la base de donnée
 * @author Antoine Guyette
 * @param integer $id_mandat identifiant du mandat
 * @param Array $DATA données sur le mandat
 * @return ErrorObj
 */
function modifierMandat($id_mandat, $DATA) {
  global $dbHandler,$global_id_agence, $global_nom_login, $global_id_client;
  $db = $dbHandler->openConnection();

  $WHERE['id_mandat'] = $id_mandat;
  $WHERE['id_ag'] = $global_id_agence;

  $sql = buildUpdateQuery('ad_mandat', $DATA, $WHERE);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  
  // Enregistrement - Modification d'un mandat
  ajout_historique(96, $global_id_client, 'Modification d\'un mandat', $global_nom_login, date("r"), NULL);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Invalider un mandat dans la base de donnée
 * @author Antoine Guyette
 * @param integer $id_mandat identifiant du mandat
 * @return ErrorObj
 */
function invaliderMandat($id_mandat) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $DATA['valide'] = 'f';
  $WHERE['id_mandat'] = $id_mandat;
  $WHERE['id_ag'] = $global_id_agence;

  $sql = buildUpdateQuery('ad_mandat', $DATA, $WHERE);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Valider un mandat dans la base de donnée
 * @author Antoine Guyette
 * @param integer $id_mandat identifiant du mandat
 * @return ErrorObj
 */
function validerMandat($id_mandat) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $DATA['valide'] = 't';
  $WHERE['id_mandat'] = $id_mandat;
  $WHERE['id_ag'] = $global_id_agence;

  $sql = buildUpdateQuery('ad_mandat', $DATA, $WHERE);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Récupérer la liste des mandats liés à un compte
 * @author Antoine Guyette
 * @param integer $id_cpte identifiant du compte
 * @return Array liste des mandats du compte
 */
function getMandats($id_cpte) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  if ($id_cpte == NULL) {
    return NULL;
  }

  $WHERE['id_cpte'] = $id_cpte;
  $WHERE['id_ag'] = $global_id_agence;

  $sql = buildSelectQuery('ad_mandat', $WHERE);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $PERS_EXT = getPersonneExt(array('id_pers_ext' => $row['id_pers_ext']));
    $ACC = getAccountDatas($id_cpte);
    $row['denomination'] = $PERS_EXT[0]['denomination'];
    $row['devise'] = $ACC['devise'];
    $id_mandat = $row['id_mandat'];
    unset($row['id_mandat']);
    $TMPARRAY[$id_mandat] = $row;
  }

  $dbHandler->closeConnection(true);
  return $TMPARRAY;
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
 * @author Antoine Guyette
 * @param integer $id_cpte identifiant du compte
 * @return Array liste des mandats du compte
 */
function getListeMandatairesActifs($id_cpte, $is_non_join=NULL, $have_CONJ_id=NULL) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $include_CONJ = true;
  $MANDATAIRES = getMandats($id_cpte);

  if ($MANDATAIRES == NULL) {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  foreach ($MANDATAIRES as $key=>$value) { // si on a un des mandataires qui signent conjointement, son mandat a expiré, alors le mandataire restant ne maintient pas le droit
    if ($value['type_pouv_sign'] == 2 && !dateExpValide($value['date_exp'])) {
      $include_CONJ = false;
    }
    if ($value['type_pouv_sign'] == 2 && $value['valide'] == 'f') {
      $include_CONJ = false;
    }
  }

  foreach ($MANDATAIRES as $key=>$value) {
    if ($value['valide'] == 't' && dateExpValide($value['date_exp'])) {
      if ($value['type_pouv_sign'] == 1 || $is_non_join) {
        $TMPARRAY[$key]['libelle'] = $value['denomination'];
        if ($value['limitation'] != NULL) {
          $TMPARRAY[$key]['libelle'] .= " - ".afficheMontant($value['limitation'])." ".$value['devise'];
          $TMPARRAY[$key]['limitation'] = $value['limitation'];
        }
      } else {
        if ($include_CONJ){ // on inclut les mandataires qui signent conjointement, si au cas pour tous, leurs mandats ne sont pas expiré
          $TMPARRAY['CONJ']['libelle'] .= $value['denomination'].", ";
          if ($have_CONJ_id){
            $TMPARRAY['CONJ_id']['id'] .= "$key-";
          }
        }
      }
    }
  }

  if ($TMPARRAY['CONJ'] != NULL) {
    $TMPARRAY['CONJ']['libelle'] = substr($TMPARRAY['CONJ']['libelle'], 0, $TMPARRAY['CONJ']['libelle'] - 2);
  }

  $dbHandler->closeConnection(true);
  return $TMPARRAY;
}

/**
 * Récupérer toutes les informations d'un mandat
 * @author Djibril NIANG
 * @param Text $denomination le nom de la personne extérieure
 * @return Array informations sur la personne
 */
function getInfosPersExt($denomination) {
	global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

	$db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_pers_ext ";
  $sql .= "WHERE denomination = '$denomination' AND id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $row;
}

/**
 * Récupérer les infos d'une personne externe de la table ad_pers_externe
 * @author 
 * @param identifiant de la personne externe
 * @return type identité et  numero d'identité
 */
function getInfoDonneurDordre($id_pers_ex) {
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();

	$db = $dbHandler->openConnection();
	$sql = "SELECT a.libel, b.num_piece_id from adsys_type_piece_identite a join ad_pers_ext b ";
	$sql .= "ON a.id = b.type_piece_id ";
	$sql .= "WHERE b.id_pers_ext = '$id_pers_ex' AND b.id_ag = $global_id_agence ;" ;

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}
	$row = $result->fetchrow(DB_FETCHMODE_ASSOC);
	$dbHandler->closeConnection(true);
	return $row;
}




/**
 * Récupérer toutes les informations d'un mandat
 * @author Antoine Guyette
 * @param integer $id_mandat identifiant du mandat
 * @return Array informations sur le mandat
 */
function getInfosMandat($id_mandat) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $WHERE['id_mandat'] = $id_mandat;
  $WHERE['id_ag'] = $global_id_agence;
  $sql = buildSelectQuery('ad_mandat', $WHERE);

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $PERS_EXT = getPersonneExt(array('id_pers_ext' => $row['id_pers_ext']));
  $row['id_pers_ext'] = $PERS_EXT[0]['id_pers_ext'];
  $row['denomination'] = $PERS_EXT[0]['denomination'];
  $row['adresse'] = $PERS_EXT[0]['adresse'];
  $row['code_postal'] = $PERS_EXT[0]['code_postal'];
  $row['ville'] = $PERS_EXT[0]['ville'];
  $row['pays'] = $PERS_EXT[0]['pays'];
  $row['num_tel'] = $PERS_EXT[0]['num_tel'];
  $row['type_piece_id'] = $PERS_EXT[0]['type_piece_id'];
  $row['num_piece_id'] = $PERS_EXT[0]['num_piece_id'];
  $row['lieu_piece_id'] = $PERS_EXT[0]['lieu_piece_id'];
  $row['date_piece_id'] = $PERS_EXT[0]['date_piece_id'];
  $row['date_exp_piece_id'] = $PERS_EXT[0]['date_exp_piece_id'];
  $row['photo'] = $PERS_EXT[0]['photo'];
  $row['signature'] = $PERS_EXT[0]['signature'];

  if ($row['type_piece_id'] != NULL) {
    $sql = "select b.traduction from adsys_type_piece_identite a, ad_traductions b where a.id_ag=$global_id_agence and  a.id = ".$row['type_piece_id']." and a.libel = b.id_str";

    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC);

    $row['libel_type_piece_id'] = $tmprow['traduction'];
  }

  $dbHandler->closeConnection(true);
  return $row;
}

/**
 * Renvoie le nombre de mois à rémunérer pour un compte d'épargne
 * @author Papa
 * @since 2.3
 * @param date $date_cap date de capitalisation
 * @param date $date_ouv date d'ouverture du compte
 * @param date $date_last_cap date de la dernière capitalisation du compte
 * @param int $mode_paie mode de paiement des intérêts : 1=> 'Paiement fin de mois ', 2=> 'Paiement date ouverture'
 * @param int $freq_cap la fréquence de capitalisation
 * @param int $terme le terme du mois
 * @return int le nombre de mois à payer
 */
function getJoursCapitalisation($date_cap, $date_ouv, $date_las_cap, $dat_date_fin) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT getPeriodeCapitalisation(date('$date_cap'), date('$date_ouv'), ";
  if ($date_las_cap == NULL) /* Si c'est la première rémunération */
    $sql .="NULL,date('$dat_date_fin'));";
  else
    $sql .= "date('$date_las_cap'),date('$dat_date_fin'));";

  $result = $db->query($sql);
  if (DB::isError($result))
    erreur("getJoursCapitalisation()", $result->getMessage());

  $row = $result->fetchrow();
  $nb_jours = $row[0];

  $dbHandler->closeConnection(true);
  return $nb_jours;
}


/**
 * Permet de savoir si le client a des frais en attente
 * @author Antoine Guyette
 * @since 2.4.1
 * @param int $id_client identifiant du client
 * @return boolean true si le client a des frais en attente
 */
function hasFraisAttente($id_client) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $result = getFraisAttente(NULL,$id_client);
  $frais = $result->param;
  if (count($frais) > 0) {
    $dbHandler->closeConnection(true);
    return true;
  }

  $dbHandler->closeConnection(true);
  return false;
}

/**
 * Permet de savoir s'il ya des frais en attente sur un compte d'un client
 * @author Djibril NIANG
 * @since 3.0.4
 * @param int $id_cpte numero du compte
 * @return boolean true si le compte a des frais en attente
 */
function hasFraisAttenteCompte($id_cpte) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $result = getFraisAttenteCompte($id_cpte);
  $frais = $result->param;
  if (count($frais) > 0) {
    $dbHandler->closeConnection(true);
    return true;
  }

  $dbHandler->closeConnection(true);
  return false;
}

/**
 * @desc liste des frais en attente sur un compte
 * @author Djibril NIANG
 * @since 3.0.4
 * @param int $id_cpte identifiant du compte ayant des frais en attente
 * @return array liste des faris en attente
 */
function getFraisAttenteCompte($id_cpte ) {
  global $global_monnaie, $global_id_agence;

  $sql = "SELECT * FROM ad_frais_attente ";
  $sql .= " WHERE id_cpte = $id_cpte  AND id_ag = $global_id_agence ";

  return executeDirectQuery($sql);

}

/**
 * @desc Paiement de frais en attente
 * @author papa
 * @since 2.8
 * @param int $id_cpte identifiant du compte de prélèvement
 * @param int $type_op numéro de l'opération
 * @param real $montant_frais le montant des frais à payer
 * @return ErrorObj Objet Error avec en paramètre 0 si pas erreur sinon le code de l'erreur rencontré
 */
function paieFraisAttente($id_cpte, $type_op, $montant_frais, &$comptable) {
  global $dbHandler, $global_monnaie;
  $db = $dbHandler->openConnection();

  // Infos sur le compte source
  $InfoCpte = getAccountDatas($id_cpte);
  $devise = $InfoCpte["devise"];

  // Prelevement Tva sur Frais attente si type frais operation 50 #804
  if ($type_op == 50){
    //$type_ope = 50;
    $subst = array();
    $subst["cpta"] = array();
    $subst["int"] = array();
    $subst["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
    if ($subst["cpta"]["debit"] == NULL) {
      $dbHandler->closeConnection(false);
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte associé au produit d'épargne"));
    }
    $subst["int"]["debit"] = $id_cpte;

    $myErr = reglementTaxe($type_op, $montant_frais, SENS_CREDIT, $devise, $subst, $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array();
  $cptes_substitue["int"] = array();
  $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($id_cpte);
  if ($cptes_substitue["cpta"]["debit"] == NULL) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
  }
  $cptes_substitue["int"]["debit"] = $id_cpte;

  $erreur = effectueChangePrivate($devise, $global_monnaie, $montant_frais, $type_op, $cptes_substitue, $comptable,TRUE,NULL,$id_cpte);
  if ($erreur->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $erreur;
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Renvoie les ordres permanents pour un client
 * @author Pierre Timmermans
 * @since 2.10
 * @param $id_client identifiant du client
 * @return la liste des ordres permanents
 */
function getOrdresPermParClient($id_client) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  if(($id_client == null) or ($id_client == '')){
 	    erreur("getOrdresPermParClient", sprintf(_("Le numéro du client n'est pas renseigné.")));
 	}else {
 	    $sql = "SELECT id_ord, cpt_from, cpt_to, type_transfert, id_cor, id_benef, date_prem_exe, date_fin,montant, frais_transfert, periodicite, interv, actif,mnt_total_prevu, etat_clos ";
 	    $sql .= " FROM ad_ord_perm, ad_cpt WHERE ad_ord_perm.id_ag = ad_cpt.id_ag AND ad_cpt.id_ag = $global_id_agence";
 	    $sql .= " AND id_cpte = cpt_from AND id_titulaire = $id_client ORDER BY id_ord";
 	}
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) return NULL;
  $LIST = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($LIST, $row);
  }
  $dbHandler->closeConnection(true);

  return $LIST;
}
/**
 * Ajouter un ordre permanent
 * @author Pierre Tim
 * @param Array $DATA données sur le mandat
 * @return ErrorObj
 */
function ajouterOrdrepermanent($DATA) {
	global $dbHandler,$global_id_agence, $global_nom_login, $global_id_client;
  $db = $dbHandler->openConnection();
  $DATA['id_ag']= $global_id_agence;

  $sql .= buildInsertQuery('ad_ord_perm',$DATA);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  // Enregistrement - Ajout d'un ordre permanent
  ajout_historique(56, $global_id_client, 'Ajout d\'un ordre permanent', $global_nom_login, date("r"), NULL);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Ajouter un ordre permanent via module de reprise epargne
 * @author Pierre Tim
 * @param Array $DATA données sur le mandat
 * @return ErrorObj
 */
function ajouterOrdrepermanentModule($DATA,$id_client,$login) {
  global $dbHandler,$global_id_agence, $global_nom_login, $global_id_client;
  $db = $dbHandler->openConnection();
  $DATA['id_ag']= $global_id_agence;

  $sql .= buildInsertQuery('ad_ord_perm',$DATA);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  // Enregistrement - Ajout d'un ordre permanent
  ajout_historique(56, $id_client, 'Ajout d\'un ordre permanent via Module', $login, date("r"), NULL);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}
/**
 * Modifier un ordre permanent
 * @author Pierre Tim
 * @param integer $id_ord identifiant de l'ordre permanent
 * @param Array $DATA données
 * @return ErrorObj
 */
function modifierOrdrepermanent($id_ord, $DATA) {
	global $dbHandler,$global_id_agence, $global_nom_login, $global_id_client;
  $db = $dbHandler->openConnection();

  $WHERE['id_ord'] = $id_ord;
  $WHERE['id_ag'] = $global_id_agence;
  $sql = buildUpdateQuery('ad_ord_perm', $DATA, $WHERE);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getCode() . ":" . $result->getMessage());
  } 
  // Enregistrement - Modification d'un ordre permanent
  ajout_historique(57, $global_id_client, 'Modification d\'un ordre permanent', $global_nom_login, date("r"), NULL);
  

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Supprimer un ordre permanent
 * @author Antoine Delvaux
 * @param integer $id_ord identifiant de l'ordre permanent
 * @return ErrorObj
 */
function deleteOrdrepermanent($a_id_ord) {
  global $global_id_agence;

  return (executeDirectQuery("DELETE FROM ad_ord_perm WHERE id_ag = $global_id_agence AND id_ord = $a_id_ord"));
}

/**
 * @desc liste des frais en attente
 * @author papa
 * @since 2.8
 * @param int $id_cpte identifiant du compte ayant des frais en attente
 * @param int $id_client identifiant du client ayant des frais en attente
 * @param int $type_ope numéro de l'opération sur lequel porte les frais
 * @param date $date_frais date de mise en attente des frais
 * @return array liste des faris en attente
 */
function getFraisAttente($id_cpte =NULL, $id_client=NULL, $type_ope=NULL, $date_frais=NULL, $id_agence=NULL) {
  global $global_monnaie, $global_id_agence;


  if ($id_agence == NULL)
    $id_agence = $global_id_agence;
  $sql = "SELECT a.*, calculeCV(a.montant, b.devise, '$global_monnaie') AS cv, b.id_titulaire, b.num_complet_cpte, b.devise, c.pp_nom, c.pp_prenom, c.pm_raison_sociale, c.gi_nom, c.statut_juridique, d.libel_ope ";
  // jointure avec la table des clients
  //if ($id_client != NULL)
    //$sql .= ", ad_cli c ";
	 $sql .=" FROM ad_frais_attente a, ad_cpt b, ad_cli c, ad_cpt_ope d ";
  $sql .= " WHERE a.id_ag=b.id_ag AND b.id_ag=d.id_ag AND a.id_ag=$global_id_agence AND a.id_cpte=b.id_cpte AND c.id_client=b.id_titulaire AND a.type_frais=d.type_operation ";

  //si un critère de sélection est renseigné
  if ($id_cpte != NULL)
    $sql .= "AND a.id_cpte=$id_cpte ";
  if ($id_client != NULL)
    $sql .= " AND (b.id_titulaire=$id_client) ";
  if ($type_ope != NULL)
    $sql .= " AND a.type_frais='$type_ope' ";
  if ($date_frais !=NULL)
    $sql .= " AND date(a.date_frais)=date('$date_frais') ";
  if ($id_client != NULL)
    $sql .= " AND b.id_ag=c.id_ag ";
  //$sql = substr($sql, 0 , -4);

  return executeDirectQuery($sql, false);

}

/**
 * Executer l'ordre permanent donné en paramètre, appelé par le batch
 *
 * TODO : transferts vers l'extérieur d'ADbanking
 * @author Pierre Timmermans, Antoine Delvaux
 * @since 3.0
 * @param int $a_id_ord Identifiant de l'ordre
 * @param date $a_date_exec La date à laquelle on exécute l'ordre
 * @param array &$a_his_compta L'historique comptable des mouvements précédents (du batch)
 * @return ErrorObj
 */
function executeOrdPermanent($a_id_ord, $a_date_exec, &$a_his_compta, $test_delai = false) {
  global $global_id_agence, $erreur;

  $result = executeDirectQuery("SELECT * FROM ad_ord_perm WHERE id_ag = $global_id_agence AND id_ord = $a_id_ord");
  if ($result->errCode != NO_ERR) {
  	return $result;
  }
  $ordre = $result->param[0];

  // Transfert interne
  if ($ordre['type_transfert'] <= 2) {
    $result = transfertCpteClient($ordre['cpt_from'], $ordre['cpt_to'], $ordre['montant'], NULL, $ordre['frais_transfert'], NULL, NULL, NULL, NULL, $a_his_compta, $test_delai);
    if ($result->errCode != NO_ERR){
 	     return $result;
 	  }
  } else {
  	// Transfert extérieur pas encore implémenté
    return ErrorObj(ERR_GENERIQUE, _("pas encore implémenté"));
  }

  $updsql = "UPDATE ad_ord_perm set date_dern_exe_th = '" . $a_date_exec . "', date_dern_exe_ef = date(now())";
  if ($result->errCode == NO_ERR) {
    $updsql .= ",dern_statut = 1 WHERE id_ord = " . $ordre['id_ord'];
    $statut = true;
  } else {
    $updsql .= ",dern_statut = 2 WHERE id_ord = " . $ordre['id_ord'];
    $statut = false;
  }
  $result = executeDirectQuery($updsql);
  if ($result->errCode == NO_ERR) {
    $result->param = $statut;
  }

  return $result;
}

/**
 * Fonction qui verifie que  :
 * 		- le solde du compte du client est suffisant pour faire un rechargement de sa carte
 * 		- le montant minimum disponible n'est pas atteint
 * 		- la durée minimum entre deux retraits est atteinte
 * 		- le compte en lui même n'est pas bloqué
 *
 * @param integer $idcli : numero client
 * @param integer $idprod : code produit
 * @return objet erreur ErrorObj
 * @since mai 2007
 * @version 3.0
 * @author Aminata, Stefano
 */
function checkAutorisation($idcli, $idprod) {
  // récupérer le infos sur le produit associé au compte sélectionné
  $InfoCpte = getAccountDatas($idcli);
  $InfoProduit = getProdEpargne($idprod);

  //vérification de l'état du compte : ouvert
  if ($InfoCpte["etat_cpte"] == 3) return new ErrorObj(ERR_CPTE_BLOQUE);
  if ($InfoCpte["etat_cpte"] == 4) return new ErrorObj(ERR_CPTE_ATT_FERM);

  //vérifier possibilité retrait
  if ($InfoProduit["retrait_unique"] == 't') return new ErrorObj(ERR_RETRAIT_UNIQUE);

  // Recherche des frais à appliquer en fonction du type d'opération
  $frais = $InfoProduit['frais_retrait_cpt'];

  $solde_disponible = getSoldeDisponible($InfoCpte['id_cpte']);
  if ( ($solde_disponible - $frais) < 0)
    return new ErrorObj(ERR_MNT_MIN_DEPASSE);

  //vérifier si durée mini entre deux retraits
  if ($InfoProduit["duree_min_retrait_jour"] > 0) {
    $erreur = CheckDureeMinRetrait($InfoCpte["id_cpte"], $InfoProduit["duree_min_retrait_jour"],$InfoProduit['type_duree_min2retrait']);
    if ($erreur->errCode != NO_ERR) return $erreur;
  }

  return new ErrorObj(NO_ERR);
}

function recharge_versement($id_guichet, $montant, $type_retrait) {
  global $global_nom_login, $global_id_agence, $global_id_guichet;
  global $dbHandler, $global_multidevise;
  global $global_monnaie;

  $comptable = array();
  $db = $dbHandler->openConnection();
  //Recharge Carte ferlo par Compte Epargne
  $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($id_guichet);
  $operation=143;
  $fonction=160;

  $myErr = passageEcrituresComptablesAuto($operation, $montant, $comptable, $cptes_substitue);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $myErr = ajout_historique($fonction, 0,'', $global_nom_login, date("r"), $comptable, NULL);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }
  $id_his = $myErr->param;
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR, array('id'=>$id_his));
}

/**
 * Liste des produits d'épargne par agence
 * @param int $id_ag identifiant de l'agence
 * @return array tableau contenant la liste des produits d'épargne
 */

function getListeProduitEpargne($condition = null) {

  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_produit_epargne where id_ag=$global_id_agence ";

  if ($condition != null) {
    $sql .= " AND ".$condition;
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) )
    $DATAS[$row["id"]] = $row['libel'];

  return $DATAS;
}

/**
 * renvoie le montant  des frais d'adhésion payé par un client
 * @param int $a_id_client N° du client concerné
 * @return ErrorObj Objet Erreur
 * @author Ares voukissi
 * @since 3.0
 */
function getFraisAdhesionPaye($a_id_client){
	global $global_id_agence;
	// recuperation du num du compte comptable  du 'compte de base'
	$AG_DATA = getAgenceDatas($global_id_agence);

  //$PROD = getProdEpargne(getBaseProductID($global_id_agence));
  $ACC = getAccountDatas(getBaseAccountID($a_id_client));
  $PROD = getProdEpargne($ACC['id_prod']);

  $cpte_cpta_prod_ep = $PROD['cpte_cpta_prod_ep'];

  //recuperation du numero du compte comptable lié à l'operation 90
  $type_oper=90; // perception frais d'adhesion
  // comptes au débit et crédit
  $DetailsOperation = array();

  $MyError = getDetailsOperation($type_oper);
  if ($MyError->errCode != NO_ERR && $type_oper < 1000) {
    $dbHandler->closeConnection(false);
    return $MyError;
  } else {
    $DetailsOperation = $MyError->param;
  }
  $cpte_credit_oper = $DetailsOperation["credit"]["compte"];
  //somme au debit des mvts Compte de base ==> Compte au crédit de l'opération 90
	$sql="	  SELECT sum(montant) FROM ad_ecriture e, ad_mouvement m, ad_his h
			         WHERE  e.id_ag = $global_id_agence AND e.id_ag = m.id_ag AND e.id_ag = h.id_ag
			         AND e.id_ecriture = m.id_ecriture AND e.id_his = h.id_his
			         AND sens = 'd'  AND compte='$cpte_cpta_prod_ep' AND id_client=$a_id_client
			         AND e.id_ecriture  in (
					         SELECT e.id_ecriture  FROM ad_ecriture e, ad_mouvement m, ad_his h
					         WHERE  e.id_ag = m.id_ag AND e.id_ag = h.id_ag
					         AND e.id_ecriture = m.id_ecriture AND e.id_his = h.id_his
					         AND sens = 'c' AND compte='$cpte_credit_oper' AND id_client=$a_id_client)
			  ";

	$result = executeDirectQuery($sql, TRUE);
  return $result;


}

/**
 * renvoie,pour un client,les infos des demandes de transfert PS dont les états correspondent aux états spécifiés dans la condition whereCl
 * @author Kheshan
 * @since 1.0
 * @param int $id_client l'identifiant du client titulaire de demande
 * @param text $whereCl la conditions spécifiant les états demande
 * @return array tableau de la forme (index => infos compte) : les index sont les identifiants des dossiers
 */
function getInfoDemandePS($id_client, $whereCl) {
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	
	$sql = "SELECT * from ad_transfert_ps_his where id_client_src = '$id_client' and id_ag = '$global_id_agence' $whereCl ORDER BY id ; ";
	
	$result=$db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
	}

	$retour = array();
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
		$retour[$row["id"]] = $row;

	$dbHandler->closeConnection(true);
	return $retour;
}

/**
 * @desc récuperation du paramètre d'affichage de billetage sur les recu.
 * @author Bnd
 * @return array boolean
 */
function getParamAffichageBilletage() {
   global $dbHandler,  $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT param_affiche_billetage FROM ad_agc ";
    $sql .= "WHERE id_ag = $global_id_agence ";
    $result = $db->query($sql);

    if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    $dbHandler->closeConnection(true);
    if ($result->numRows() == 0) {
        return NULL;
    }

    $row = $result->fetchrow();
    return $row[0];


}


/**
 * @desc récuperation du paramètre d'affichage de solde sur les recu.
 * @author B&d
 * @return array boolean
 */

function getParamAffichageSolde() {
  global $dbHandler,  $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT param_affiche_solde FROM ad_agc ";
  $sql .= "WHERE id_ag = $global_id_agence ";
  $result = $db->query($sql);

  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) {
    return NULL;
  }

  $row = $result->fetchrow();
  return $row[0];


}


/**
 * Renvoie un tableau associatif tous les produits d'épargne qui sont de type services financiers=true
 *
 * @return array tableau associatif des produits d'épargne ou NULL si aucun
 */
function lastProdDepotAVueActif() {
  global $global_id_agence;

  $sql = "select count(*) from adsys_produit_epargne where classe_comptable = 1 and is_produit_actif = TRUE and id_ag = $global_id_agence ;";
  $result = executeDirectQuery($sql);

  if ($result->errCode != NO_ERR) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__, $result->getMessage());
  }
  else {
    $count_prods = $result->param[0]['count'];
    if($count_prods > 1) return false; // if we have at least one prod, its not the last!
    else return true;
  }

}

/**
 * @desc recuperation du numéro de chèque depuis une transaction chéquier.
 * @author Steven
 * @param int $id_his de la transaction en question
 * @param string $libel_ecriture le libellé de l'opération de la transaction.
 * @param string $info_ecriture Numéro du chèque.
 * @return string libellé de l'opération + le num du chèque.
 */
function getChequeno($id_his,$libel_ecriture,$info_ecriture=null){

  global $dbHandler,  $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = " SELECT ext.num_piece from
            ad_his_ext ext inner join ad_his his
            on ext.id=his.id_his_ext
            where id_his=$id_his and his.id_ag = $global_id_agence ";

  $result = $db->query($sql);

  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  //si le numéro de chèque n'est pas remonter
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) {
    if ($info_ecriture != null && trim($info_ecriture)!="") {
      return $libel_ecriture." No: ".$info_ecriture;
    } else {
      return $libel_ecriture;
    }
  }

  $row = $result->fetchrow();
  $res = $libel_ecriture." No: ".$row[0];
  return $res;
}

/**
 * Insertion dans la table ad_retrait_attente
 *
 * @param Integer $id_client
 * @param Integer $id_cpte
 * @param Integer $type_retrait (1-Retrait ordinaire, 2-Retrait express)
 * @param Integer $choix_retrait
 * @param Double $montant_retrait
 * @param Text $devise
 * @param Double $mnt_devise
 * @param Double $mnt_reste
 * @param Double $taux_devise
 * @param Double $taux_commission
 * @param Double $dest_reste
 * @param Text $login
 * @param Text $communication
 * @param Text $remarque
 * @param Integer $id_pers_ext
 * @param Text $mandat
 * @param Text $num_chq
 * @param Date $date_chq
 * @param Integer $id_ben
 * @param Text $beneficiaire
 * @param Text $nom_ben
 * @param Text $denomination
 * @param Double $frais_retrait_cpt
 * @param Integer $id_his
 * @param Integer $etat_retrait (1-demandé, 2-autorisé, 3-refusé, 4-payé)
 * @param Text $comments
 *
 * @return ErrorObj = NO_ERR si tout s'est bien passé, Signal Erreur si pb de la BD
 */
function insertRetraitAttente($id_client, $id_cpte, $type_retrait, $choix_retrait, $montant_retait,$devise,$mnt_devise, $mnt_reste,$taux_devise,$taux_commission,$dest_reste, $login, $communication = null, $remarque = null, $id_pers_ext = null, $mandat = null, $num_chq = null, $date_chq = null, $id_ben = null, $beneficiaire = null, $nom_ben = null, $denomination = null, $frais_retrait_cpt = null,$num_piece=null, $lieu_delivrance=null, $id_his = null, $etat_retrait = 1, $comments = '')
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $tableFields = array(
      "montant_retrait" => recupMontant($montant_retait),
      "devise" => $devise,
      "mnt_devise" => recupMontant($mnt_devise),
      "mnt_reste" => recupMontant($mnt_reste),
      "taux_devise" => recupMontant($taux_devise),
      "taux_commission" => recupMontant($taux_commission),
      "dest_reste" => $dest_reste,
      "etat_retrait" => $etat_retrait,
      "type_retrait" => $type_retrait,
      "choix_retrait" => $choix_retrait,
      "id_client" => $id_client,
      "id_cpte" => $id_cpte,
      "id_his" => $id_his,
      "login" => trim($login),
      "communication" => trim($communication),
      "remarque" => trim($remarque),
      "id_pers_ext" => $id_pers_ext,
      "mandat" => $mandat,
      "num_chq" => trim($num_chq),
      "date_chq" => $date_chq,
      "id_ben" => $id_ben,
      "beneficiaire" => trim($beneficiaire),
      "nom_ben" => trim($nom_ben),
      "denomination" => trim($denomination),
      "frais_retrait_cpt" => recupMontant($frais_retrait_cpt),
      "num_piece" => trim($num_piece),
      "lieu_delivrance" => trim($lieu_delivrance),
      "comments" => trim($comments),
      "date_crea" => date("r"),
      "id_ag" => $global_id_agence,
  );

  $sql = buildInsertQuery("ad_retrait_attente", $tableFields);

  $result = $db->query($sql);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/**
 * Vérifié si il y a une attente de retrait pour ce client
 *
 * @param int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:payé / 4:refusé
 *
 * @return boolean
 */
function hasRetraitAttente($id_client = null, $etat_retrait = 1)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_client FROM ad_retrait_attente WHERE id_ag = $global_id_agence ";

  if($id_client != null){
   $sql .=" AND id_client = $id_client ";
  }

  if ($etat_retrait != 0) {
    $sql .= " AND etat_retrait = $etat_retrait ";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();

  $dbHandler->closeConnection(true);

  return ($tmpRow[0]) ? true : false;
}


/**
 * Jira At-39
 * Vérifié si il y a un approvisionnement / delestage
 *
 * @param int $id_guichet
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:payé / 4:refusé
 *
 * @return boolean
 */
function hasApproDelestageAttente($id_guichet = null, $etat_appro_delestage = 1)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_guichet FROM ad_approvisionnement_delestage_attente WHERE id_ag = $global_id_agence ";

  if($id_guichet != null){
    $sql .=" AND id_guichet = $id_guichet ";
  }

  if ($etat_appro_delestage != 0) {
    $sql .= " AND etat_appro_delestage = $etat_appro_delestage ";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();

  $dbHandler->closeConnection(true);

  return ($tmpRow[0]) ? true : false;
}

/**Ticket Jira AT-44
 * Vérifié si il y a une attente de retrait en déplacé pour ce client
 *
 * @param int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:payé / 4:refusé
 *
 * @return boolean
 */
function hasRetraitDeplaceAttente($id_client = null, $etat_retrait = 1)
{
  global $dbHandler, $global_id_agence,$global_remote_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_client_distant FROM ad_retrait_deplace_attente WHERE id_ag_distant = $global_remote_id_agence and id_ag_local= $global_id_agence";

  if($id_client != null){
    $sql .=" AND id_client_distant = $id_client ";
  }

  if ($etat_retrait != 0) {
    $sql .= " AND etat_retrait = $etat_retrait ";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();

  $dbHandler->closeConnection(true);

  return ($tmpRow[0]) ? true : false;
}

/**
 * Vérifié si une demande de retrait existe pour ce client
 *
 * @param int $id_client
 *
 * @return boolean
 */
function hasRetraitAttenteDemande($id_client=null)
{
  return hasRetraitAttente(null, 1);
}

/**
 * Vérifié si une autorisation de retrait existe pour ce client
 *
 * @param int $id_client
 *
 * @return boolean
 */
function hasRetraitAttenteAutorise($id_client)
{
  return hasRetraitAttente($id_client, 2);
}

/**
 * Vérifié si une autorisation de approvisionenemnt/delestage pour ce guichet
 *
 * @param int $id_client
 *
 * @return boolean
 */
function hasApproDelestageAutoriser($id_guichet)
{
  return hasApproDelestageAttente($id_guichet, 2);
}

/**
 * Vérifié si une autorisation de retrait en deplacé existe pour ce client
 *
 * @param int $id_client
 *
 * @return boolean
 */
function hasRetraitDeplaceAttenteAutorise($id_client)
{
  return hasRetraitDeplaceAttente($id_client, 2);
}

/**
 * Vérifié si une autorisation de transfert existe pour ce client
 *
 * @param int $id_client
 *
 * @return boolean
 */
function hasTransfertAttenteAutorise($id_client)
{
  return hasTransfertAttente($id_client, 2);
}

/**
 * Récupère une liste de demande de retrait
 *
 * @param null|int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:payé / 4:refusé
 *
 * @return array|null
 */
function getListeRetraitAttente($id_client = null, $etat_retrait = 1)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_retrait_attente WHERE id_ag = $global_id_agence ";

  if ($id_client != null) {
    $sql .= " AND id_client = $id_client ";
  }

  if ($etat_retrait != 0) {
    $sql .= " AND etat_retrait = $etat_retrait ";
  }

  $sql .= " ORDER BY id ASC";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $tmp_arr = array();

  while ($ListDemandes = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $tmp_arr[$ListDemandes['id']] = $ListDemandes;
  }

  $dbHandler->closeConnection(true);

  return $tmp_arr;
}

/**
 * Récupère une liste de demande de retrait
 *
 * @param null|int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:transferé / 4:refusé
 *
 * @return array|null
 */
function getListeTransfertAttente($id_client = null, $etat_transfert = 1)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_transfert_attente WHERE id_ag = $global_id_agence ";

  if ($id_client != null) {
    $sql .= " AND id_client_src = $id_client ";
  }

  if ($etat_transfert != 0) {
    $sql .= " AND etat_transfert = $etat_transfert ";
  }

  $sql .= " ORDER BY id ASC";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $tmp_arr = array();

  while ($ListDemandes = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $tmp_arr[$ListDemandes['id']] = $ListDemandes;
  }

  $dbHandler->closeConnection(true);

  return $tmp_arr;
}
/**
 * Récupère une liste de demande de retrait
 *
 * @param null|int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:transferé / 4:refusé
 *
 * @return array|null
 */
function getListeTransfertAttenteDetails($id_client = null, $id_transation = null, $etat_transfert = 1)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_transfert_attente WHERE id_ag = $global_id_agence ";

  if ($id_client != null) {
    $sql .= " AND id_client_src = $id_client ";
  }
  if ($id_transation != null) {
    $sql .= " AND id = $id_transation ";
  }

  if ($etat_transfert != 0) {
    $sql .= " AND etat_transfert = $etat_transfert ";
  }

  $sql .= " ORDER BY id ASC";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $tmp_arr = array();

  while ($ListDemandes = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $tmp_arr[$ListDemandes['id']] = $ListDemandes;
  }

  $dbHandler->closeConnection(true);

  return $tmp_arr;
}



/**
 * Récupère les infos d'une demande retrait
 *
 * @param int $id_demande
 * @param null|int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:payé / 4:refusé
 * @param int $type_retrait (1-Retrait ordinaire, 2-Retrait express)
 *
 * @return mixed
 */
function getRetraitAttente($id_demande, $id_client = null, $etat_retrait = 0, $type_retrait = 1)
{
  global $global_id_agence;

  $sql = "SELECT * FROM ad_retrait_attente WHERE id_ag = $global_id_agence AND id = $id_demande";

  if ($id_client != null) {
    $sql .= " AND id_client = $id_client ";
  }

  if ($etat_retrait != 0) {
    $sql .= " AND etat_retrait = $etat_retrait ";
  }

  if ($type_retrait == 2) {
    $sql .= " AND type_retrait = 2 ";
  } else {
    $sql .= " AND type_retrait = 1 ";
  }

  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}

/** Ticket Jira AT-44
 * Récupère les infos d'une demande retrait en deplace
 *
 * @param int $id_demande
 * @param null|int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:payé / 4:refusé
 * @param int $type_retrait (1-Retrait ordinaire, 2-Retrait express)
 *
 * @return mixed
 */
function getRetraitDeplaceAttente($id_demande, $id_client = null, $etat_retrait = 0)
{
  global $global_id_agence;

  $sql = "SELECT * FROM ad_retrait_deplace_attente WHERE id_ag_local = $global_id_agence AND id = $id_demande";

  if ($id_client != null) {
    $sql .= " AND id_client_distant = $id_client ";
  }

  if ($etat_retrait != 0) {
    $sql .= " AND etat_retrait = $etat_retrait ";
  }

  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}



/**
 * Récupère les infos d'une demande autorisation retrait ordinaire
 *
 * @param int $id_demande
 * @param int $id_client
 *
 * @return mixed
 */
function getRetraitAttenteOrdinaireAutorise($id_demande, $id_client)
{
  return getRetraitAttente($id_demande, $id_client, 2, 1);
}
/** Ticket Jira AT-44
 * Récupère les infos d'une demande autorisation retrait en deplace
 *
 * @param int $id_demande
 * @param int $id_client
 *
 * @return mixed
 */
function getRetraitDeplaceAttenteAutorise($id_demande, $id_client)
{
  return getRetraitDeplaceAttente($id_demande, $id_client, 2);
}

/**
 * Récupère les infos d'une demande autorisation retrait express
 *
 * @param int $id_demande
 * @param int $id_client
 *
 * @return mixed
 */
function getRetraitAttenteExpressAutorise($id_demande, $id_client)
{
  return getRetraitAttente($id_demande, $id_client, 2, 2);
}

/**
 * Traitement des demandes autorisation retrait
 *
 * @param Array $data
 * @param null|int $id_client
 *
 * @return ErrorObj = NO_ERR si tout s'est bien passé, Signal Erreur si pb de la BD
 */
function processAutorisationRetraitAttente($data = null, $id_client = null)
{
  global $dbHandler, $global_id_agence, $global_nom_login;

  // Get liste demande de retrait
  $listeDemandeRetrait = getListeRetraitAttente($id_client);

  $demande_count = 0;
  foreach ($listeDemandeRetrait as $id => $demandeRetrait) {

    $db = $dbHandler->openConnection();

    $isValidationOK = false;
    $isAutorisationOK = false;

    $id_demande = trim($demandeRetrait["id"]);

    if (isset($data['btn_process_demande'])) {

      if (isset($data['check_valid_' . $id_demande])) {

        $fonction = 157; // Autorisation retrait

        $isValidationOK = true;
        $isAutorisationOK = true;

      } elseif (isset($data['check_rejet_' . $id_demande])) {

        $fonction = 73; // Refus retrait

        $isValidationOK = true;
      }

      if ($isValidationOK == true) {

        $myErr = ajout_historique($fonction, $id_client, "Demande autorisation retrait No. ".$id_demande, $global_nom_login, date("r"));

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        } else {

          // Mettre à jour le statut d'une demande de retrait à Autorisé / Refusé
          $erreur = updateRetraitAttenteEtat($id_demande, (($isAutorisationOK) ? 2 : 4), sprintf("Demande autorisation retrait : %s", (($isAutorisationOK) ? "Autorisé" : "Refusé")), $myErr->param);

          if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
          } else {
            // Commit
            $dbHandler->closeConnection(true);

            $demande_count++;
          }
        }
      } else {
        $dbHandler->closeConnection(false);
      }
    } else {
      $dbHandler->closeConnection(false);
    }
  }

  return new ErrorObj(NO_ERR, $demande_count);
}

/*******************************************************************************************************************/
function processAutorisationTransfertAttente($data = null, $id_client = null)
{
  global $dbHandler, $global_id_agence, $global_nom_login;

  // Get liste demande de retrait
  $listeDemandeTransfert = getListeTransfertAttente($id_client);

  $demande_count = 0;
  foreach ($listeDemandeTransfert as $id => $demandeTransfert) {

    $db = $dbHandler->openConnection();

    $isValidationOK = false;
    $isAutorisationOK = false;

    $id_demande = trim($demandeTransfert["id"]);

    if (isset($data['btn_process_demande'])) {

      if (isset($data['check_valid_' . $id_demande])) {

        $fonction = 152; // Autorisation retrait

        $isValidationOK = true;
        $isAutorisationOK = true;

      } elseif (isset($data['check_rejet_' . $id_demande])) {

        $fonction = 94; // Refus retrait

        $isValidationOK = true;
      }

      if ($isValidationOK == true) {

        $myErr = ajout_historique($fonction, $id_client, "Demande autorisation Transfert No. ".$id_demande, $global_nom_login, date("r"));

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        } else {

          // Mettre à jour le statut d'une demande de retrait à Autorisé / Refusé
          $erreur = updateTransfertAttenteEtat($id_demande, (($isAutorisationOK) ? 2 : 4), sprintf("Demande autorisation transfert : %s", (($isAutorisationOK) ? "Autorisé" : "Refusé")), $myErr->param);

          if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
          } else {
            // Commit
            $dbHandler->closeConnection(true);

            $demande_count++;
          }
        }
      } else {
        $dbHandler->closeConnection(false);
      }
    } else {
      $dbHandler->closeConnection(false);
    }
  }

  return new ErrorObj(NO_ERR, $demande_count);
}

/*******************************************************************************************************************/

/**
 * Retourne le libellé du retrait
 *
 * @param int $choix_retrait
 *
 * @return string Libellé de retrait
 */
function getLabelChoixRetrait($choix_retrait) {

  $retrait_label = "";

  switch($choix_retrait) {
    case 1:
      $retrait_label = "Retrait en espèces";
      break;
    case 15:
    case 4:
      $retrait_label = "Retrait cash par chèque interne";
      break;
    case 5:
      $retrait_label = "Retrait travelers cheque";
      break;
    case 8:
      $retrait_label = "Retrait chèque interne certifié";
      break;
  }

  return $retrait_label;
}

/**
 * Retourne le libellé du transfert
 *
 * @param int $choix_transfert
 *
 * @return string Libellé de transfert
 */
function getLabelChoixTransfert($choix_transfert) {

  $transfert_label = "";

  switch($choix_transfert) {
    case 1:
      $transfert_label = "Même client ";
      break;
    case 2:
      $transfert_label = "Virement interne";
      break;
    case 3:
      $transfert_label = "Virement externe";
      break;
    case 4:
    $transfert_label = "Transfert groupé";
      break;
  }

  return $transfert_label;
}

/** Ticket Jira AT-44
 * Retourne le libellé du retrait en deplace
 *
 * @param int $choix_retrait_deplace
 *
 * @return string Libellé de retrait en deplace
 */
function getLabelChoixRetraitDeplace($choix_retrait_deplace) {

  $retrait_deplace_label = "";

  switch($choix_retrait_deplace) {
    case 1:
      $retrait_deplace_label = "Retrait Cash avec impression reçu ";
      break;
    case 15:
      $retrait_deplace_label = "Retrait Cash sur présentation d'un chèque guichet";
      break;
    case 4:
      $retrait_deplace_label = "Retrait Cash sur présentation d'une autorisation de retrait sans livret/chèque";
      break;

  }

  return $retrait_deplace_label;
}


/**
 * Mettre à jour le statut d'une demande de retrait à Autorisé / Refusé
 *
 * @param int $id_demande
 * @param int $etat_retrait
 * @param string $comments
 * @param null|int $id_his
 *
 * @return ErrorObj
 */
function updateRetraitAttenteEtat($id_demande, $etat_retrait, $comments = '', $id_his = null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $tableFields = array(
      "id_his" => $id_his,
      "etat_retrait" => $etat_retrait,
      "date_modif" => date("r"),
      "comments" => trim($comments)
  );

  $sql_update = buildUpdateQuery("ad_retrait_attente", $tableFields, array('id' => $id_demande, 'id_ag' => $global_id_agence));

  $result = $db->query($sql_update);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/**
 * Mettre à jour le statut d'une demande de transfert à Autorisé / Refusé
 *
 * @param int $id_demande
 * @param int $etat_transfert
 * @param string $comments
 * @param null|int $id_his
 *
 * @return ErrorObj
 */
function updateTransfertAttenteEtat($id_demande, $etat_transfert, $comments = '', $id_his = null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $tableFields = array(
    "id_his" => $id_his,
    "etat_transfert" => $etat_transfert,
    "date_modif" => date("r"),
    "comments" => trim($comments)
  );

  $sql_update = buildUpdateQuery("ad_transfert_attente", $tableFields, array('id' => $id_demande, 'id_ag' => $global_id_agence));

  $result = $db->query($sql_update);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/** AT-44
 * Mettre à jour le statut d'une demande de retrait en deplace à Autorisé / Refusé
 *
 * @param int $id_demande
 * @param int $etat_transfert
 * @param string $comments
 * @param null|int $id_his
 *
 * @return ErrorObj
 */
function updateRetraitDeplaceAttenteEtat($id_demande, $etat_transfert, $comments = '', $id_his = null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $tableFields = array(
    "id_his" => $id_his,
    "etat_retrait" => $etat_transfert,
    "date_modif" => date("r"),
    "comments" => trim($comments)
  );

  $sql_update = buildUpdateQuery("ad_retrait_deplace_attente", $tableFields, array('id' => $id_demande));

  $result = $db->query($sql_update);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/** AT-39
 * Mettre à jour le statut d'une demande de retrait en deplace à Autorisé / Refusé
 *
 * @param int $id_demande
 * @param int $etat_transfert
 * @param string $comments
 * @param null|int $id_his
 *
 * @return ErrorObj
 */
function updateEtatApprovisionnementDelestage($id_demande, $etat_appro_delestage, $id_his = null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $tableFields = array(
    "id_his" => $id_his,
    "etat_appro_delestage" => $etat_appro_delestage,
    "date_modif" => date("r")
  );

  $sql_update = buildUpdateQuery("ad_approvisionnement_delestage_attente", $tableFields, array('id' => $id_demande));

  $result = $db->query($sql_update);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/*
 * Prélèvement des impots mobilier sur les DAT arrivant à échéance
 *
 * @param int $id_cpte
 * @param int interets peut etre null
 * @param string $comptable
 *
 * @return ErrorObj*/
function prelevementTaxDat($id_cpte,$interets=null,&$comptable)
  {
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $InfoCpte = getAccountDatas($id_cpte);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

    // Passage des écritures comptables
    //$comptable = array();
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();

    //Vérifier si le produit en question doit faire objet de prélrvement d'impot
    $isPrelevImpot = $InfoProduit['prelev_impot_imob'];

    if($isPrelevImpot=='t')
    {
        //si on prends les $interets en paramètre
        if($interets != null)
        {
            $int = $interets;
        }
        else
        {
            $int = $InfoCpte['interet_annuel'];
        }

        // Calcule d'impot
        $mnt_impot = calculImpotTax($int);
        $cptVersement = $InfoCpte['cpte_virement_clot'];

        //Si le compte de versement n'est pas renseigné prendre le compte base du client
        if($cptVersement == null)
          {
            $id_cpte_base =  getBaseAccountID ($InfoCpte["id_titulaire"]);
            $cpt = $id_cpte_base;
          }
        else
          {
            $cpt = $cptVersement;
          }
          //$id_cpte_base =  getBaseAccountID ($InfoCpte["id_titulaire"]);
          $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($cpt);

          if ($cptes_substitue["cpta"]["debit"] == NULL)
          {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
          }

          $cptes_substitue["int"]["debit"] = $cpt;

          //credit compte de taxe:
          $infoTax = getInfoTaxes();
          $cptes_substitue["cpta"]["credit"] = $infoTax['cpte_tax_col'];

          if ($cptes_substitue["cpta"]["credit"] == NULL)
          {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au taxes"));
          }

          $myErr = passageEcrituresComptablesAuto(476, $mnt_impot, $comptable, $cptes_substitue, $InfoCpte['devise'],NULL,$id_cpte);

          if ($myErr->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr;
          }
    }

    $dbHandler->closeConnection(true);
    return $myErr;
  }

/*
 * Récupération des données de la table ad_taxes
 *
 * @param int $type : type de taxe voir tableSys.php => adsys["adsys_type_taxe"]
 *
 * @return array*/

function getInfoTaxes($type=2)
{
    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();
    $sql = "select * from adsys_taxes where type_taxe = $type and id_ag = $global_id_agence ";

    $result = $db->query($sql);

    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }

    if ($result->numRows() == 0) {
      return NULL;
    }

    $tmp_arr = array();

    while ($rest = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
      $tmp_arr = $rest;
    }

    $dbHandler->closeConnection(true);

    return $tmp_arr;
}


/*
 * Calculation de la taxe
 *
 * @param int $type : type de taxe. voir tableSys.php => adsys["adsys_type_taxe"]
 *
 * @return array
 * */

function calculImpotTax($int,$type_tax = 2)
{

  $infoTax = getInfoTaxes($type_tax);
  $pctgTax = $infoTax['taux'];

  $mnt_impot = ($pctgTax * $int);

  return $mnt_impot;
}

/**
 * Récupère le montant des intérêts calculées à payer pour un compte d’épargne et le compte comptable des calcul d’intérêts
 * @param $id_cpte
 * @return ErrorObj
 */
function getIntCptEpargneCalculInfos($id_cpte)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $date_calcul = date("d/m/Y"); // date du jour
  $sql = "SELECT SUM(montant_int) FROM ad_calc_int_paye_his
          WHERE id_cpte = $id_cpte AND date_calc <= date('$date_calcul') AND etat_calc_int = 1 AND id_ag = $global_id_agence;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if($result->numRows() == 0) {
    $dbHandler->closeConnection(true);//$dbHandler->closeConnection(false);
    return new ErrorObj(NO_ERR, NULL);
  }

  $row = $result->fetchrow();
  $interets_calcules =$row[0];

  if(is_null($interets_calcules)) {
    $dbHandler->closeConnection(true);//$dbHandler->closeConnection(false);
    return new ErrorObj(NO_ERR, NULL);
  }

  $sql = "SELECT cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE id_ag = $global_id_agence LIMIT 1;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $row = $result->fetchrow();
  $cpte_cpta_int_paye =$row[0];

  if($interets_calcules > 0 && is_null($cpte_cpta_int_paye)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable des calculs d'intérêts sur les comptes d'épargnes"));
  }

  $infos = array('interets_calcules' => $interets_calcules, 'cpte_cpta_int_paye' => $cpte_cpta_int_paye);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $infos);
}

/**
 *
 * Mets a jour l'historique des comptes reprises pour les calculs d'interets sur comptes d'epargne
 *
 * @param $id_cpte
 * @param $date_valeur
 * @param $id_his_reprise
 * @param $id_ecriture_reprise
 * @return bool
 */
function clotureIntCalcCpteEpargne($id_cpte, $date_valeur = null, $id_his_reprise = null, $id_ecriture_reprise = null)
{
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  if(is_null($date_valeur))
      $date_valeur = date("d/m/Y"); // date du jour

  if(!is_null($date_valeur)) {
    $sql = "UPDATE ad_calc_int_paye_his SET date_reprise = date('$date_valeur')
            WHERE id_cpte=$id_cpte AND etat_calc_int=1 AND id_ag=$global_id_agence;";

    $result = $db->query($sql);

    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
  }

  if(!is_null($id_his_reprise)) {
    $sql = "UPDATE ad_calc_int_paye_his SET id_his_reprise = $id_his_reprise
            WHERE id_cpte=$id_cpte AND etat_calc_int=1 AND id_ag=$global_id_agence;";

    $result = $db->query($sql);

    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
  }

  if(!is_null($id_ecriture_reprise)) {
    $sql = "UPDATE ad_calc_int_paye_his SET id_ecriture_reprise = $id_ecriture_reprise
            WHERE id_cpte=$id_cpte AND etat_calc_int=1 AND id_ag=$global_id_agence;";

    $result = $db->query($sql);

    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
  }

  $sql = "UPDATE ad_calc_int_paye_his SET etat_calc_int=2 WHERE id_cpte=$id_cpte AND etat_calc_int=1 AND id_ag=$global_id_agence;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

function getFraisRetrait($code_abo,$type) {
  global $global_id_agence;
  $sql = "select * from adsys_tarification where code_abonnement = '$code_abo' AND type_de_frais = '$type';";
  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}

function getDataRetraitAttente($id_client, $id_cpte, $etat_retrait)
{
  global $global_id_agence;

  $sql = "SELECT max(id) as max_id FROM ad_retrait_attente WHERE id_ag = $global_id_agence AND id_client = $id_client and id_cpte = $id_cpte and etat_retrait = $etat_retrait";

  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}

function getDataTransfertAttente($id_client, $id_cpte, $etat_transfert)
{
  global $global_id_agence;

  $sql = "SELECT max(id) as max_id FROM ad_transfert_attente WHERE id_ag = $global_id_agence AND id_client_src = $id_client and id_cpte_client_src = '$id_cpte' and etat_transfert = $etat_transfert";

  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}

function getDataRetraitDeplaceAttente($id_client_distant, $id_cpte_distant, $etat_retrait,$id_ag_distant)
{
  global $global_id_agence;

  $sql = "SELECT max(id) as max_id FROM ad_retrait_deplace_attente WHERE id_ag_distant = $id_ag_distant AND id_client_distant = $id_client_distant and id_cpte_distant = $id_cpte_distant and etat_retrait = $etat_retrait";

  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}

function getTireurBenef($id_ben)
{
  global $global_id_agence;

  $sql = "SELECT denomination FROM tireur_benef WHERE id_ag = $global_id_agence AND id = $id_ben";

  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}

function insertTransfertAttente($id_ag , $id_client_src, $id_cpte_client_src, $montant_transfert, $etat_transfert, $type_transfert, $id_client_dest = null, $id_cpte_client_dest = null, $id_beneficiaire = null, $id_cpte_ben = null, $id_correspondant =null,$groupe_clients = null, $type_frais_prelev,$mnt_frais_type, $id_cpte_frais_transfert_prelev, $devise_cpte_frais, $mnt_frais, $devise_frais, $type_piece_justificatif, $num_chq_virement =null, $date_chq_virement= null, $type_retrait=null, $id_mandat =null, $communication =null, $remarque =null, $id_his=null, $login, $date_crea = null, $date_modif= null,$comments=null,$comm_mini_2retrait = null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $date_crea = date('r');
  $tableFields = array(
    "id_ag"=>$id_ag,
    "id_client_src"=>$id_client_src,
    "id_cpte_client_src"=>$id_cpte_client_src,
    "montant_transfert"=>recupMontant($montant_transfert),
    "etat_transfert"=>$etat_transfert,
    "type_transfert"=>$type_transfert,
    "id_client_dest"=>$id_client_dest,
    "id_cpte_client_dest"=>$id_cpte_client_dest,
    "id_beneficiaire"=>$id_beneficiaire,
    "id_cpte_ben"=>$id_cpte_ben,
    "id_correspondant"=>$id_correspondant,
    "groupe_clients"=>$groupe_clients,
    "type_frais_prelev"=>$type_frais_prelev,
    "mnt_frais_type"=>$mnt_frais_type,
    "id_cpte_frais_transfert_prelev"=>$id_cpte_frais_transfert_prelev,
    "devise_cpte_frais"=>$devise_cpte_frais,
    "mnt_frais"=>$mnt_frais,
    "devise_frais"=>$devise_frais,
    "type_piece_justificatif"=>$type_piece_justificatif,
    "num_chq_virement"=>$num_chq_virement,
    "date_chq_virement"=>$date_chq_virement,
    "type_retrait"=>$type_retrait,
    "mandat"=>$id_mandat,
    "communication"=>trim($communication),
    "remarque"=>trim($remarque),
    "id_his"=>$id_his,
    "login"=>trim($login),
    "date_crea"=>$date_crea,
    "date_modif"=>$date_modif,
    "commission_duree_2retrait" =>$comm_mini_2retrait,
    "comments"=>"Demande d'autorisation de transfert : En cours"
  );

  $sql = buildInsertQuery("ad_transfert_attente", $tableFields);

  $result = $db->query($sql);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);

}

/**
 * Vérifié si il y a une attente de retrait pour ce client
 *
 * @param int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:payé / 4:refusé
 *
 * @return boolean
 */
function hasTransfertAttente($id_client = null, $etat_retrait = 1)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_client_src FROM ad_transfert_attente WHERE id_ag = $global_id_agence ";

  if($id_client != null){
    $sql .=" AND id_client_src = $id_client ";
  }

  if ($etat_retrait != 0) {
    $sql .= " AND etat_transfert = $etat_retrait ";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $tmpRow = $result->fetchrow();

  $dbHandler->closeConnection(true);

  return ($tmpRow[0]) ? true : false;
}

/**
 * Récupère les infos d'une demande retrait
 *
 * @param int $id_demande
 * @param null|int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:payé / 4:refusé
 * @param int $type_retrait (1-Retrait ordinaire, 2-Retrait express)
 *
 * @return mixed
 */
function getTransfertAttente($id_demande, $id_client = null, $etat_retrait = 0)
{
  global $global_id_agence;

  $sql = "SELECT * FROM ad_transfert_attente WHERE id_ag = $global_id_agence AND id = $id_demande";

  if ($id_client != null) {
    $sql .= " AND id_client_src = $id_client ";
  }

  if ($etat_retrait != 0) {
    $sql .= " AND etat_transfert = $etat_retrait ";
  }

  /*if ($type_retrait == 2) {
    $sql .= " AND type_retrait = 2 ";
  } else {
    $sql .= " AND type_retrait = 1 ";
  }*/
  $result = executeDirectQuery($sql, FALSE);
  if ($result->errCode != NO_ERR) {
    return $result;
  } else {
    if (empty($result->param)) {
      return NULL;
    } else {
      return $result->param[0];
    }
  }
}

/**
 * Récupère les infos d'une demande autorisation retrait ordinaire
 *
 * @param int $id_demande
 * @param int $id_client
 *
 * @return mixed
 */
function getTransfertAttenteAutorise($id_demande, $id_client)
{
  return getTransfertAttente($id_demande, $id_client, 2);
}

/**
 * Ticket 792
 * Fonction pour recuperer le compte comptable associer au function IAP : Interet a Payer
 * NO PARAM
 * Return string
 */
function getCompteIAP(){
  global $global_id_agence, $global_monnaie;
  global $dbHandler;

  $db = $dbHandler->openConnection();
  $global_id_agence = getNumAgence();

  // Recuperation du parametrage compte comptable des interets a payer
  $sql = "SELECT cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE id_ag = $global_id_agence;";
  $result=$db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    erreur("calcul_interets_a_payer()", $result->getUserInfo());
  }

  $tmprow = $result->fetchrow();
  $cpte_calc_IAP = $tmprow[0];

  $dbHandler->closeConnection(true);
  return $cpte_calc_IAP;
}

/**
 * Fonction pour recuperer le nombre de jours entre deux retraits (ticket 805 : ajout frais de non respect de la duree minimum entre 2 retraits)
 * PARAM : type operation et compte interne cli
 * RETURN integer
 */
function getNbrJoursEntreDeuxRetrait($type_ope,$id_cpte){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();

  // récupérer le infos sur le produit associé au compte sélectionné
  $InfoCpte = getAccountDatas($id_cpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  $nombreJours = ($InfoProduit['duree_min_retrait_jour'] + 1); //nombre jours a prendre si aucun mouvements

  $cpte_cpta_prod = getCompteCptaProdEp($id_cpte);

  //Recupere le nombre de jours
  $sql = "SELECT COALESCE(extract(day from now()::timestamp - date_valeur),".$nombreJours.") AS days FROM ad_mouvement m INNER JOIN ad_ecriture e ON m.id_ecriture = e.id_ecriture WHERE e.type_operation IN ($type_ope) AND m.compte = '$cpte_cpta_prod' AND m.cpte_interne_cli = $id_cpte AND m.sens = 'd' ORDER BY m.date_valeur DESC LIMIT 1 ";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  while ($tmprow = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
    $nbrJours = $tmprow['days'];
  }

  $dbHandler->closeConnection(true);
  return $nbrJours;
}

/**
 * ticket 805 : ajout frais de non respect de la duree minimum entre 2 retraits
 *
 * Cette fonction peut être appellée dans le contexte :
 *  * lors d'une opération retrait lorsque la duree minimum entre deux retraits n'est pas atteinte alors on preleve le frais duree minimum entre deux retraits, parametré pour ce produit, sur le compte de client,
 *    dans ce cas la fonction est normalement appellée uniquement lors d'une opération initiée par un client(utilisateur en vrais sens)), *
 *
 * @author Roshan Bolah
 * @param int $a_id_cpte : Compte d'épargne concerné
 * @param array $comptable Liste de mouvements comptables précédemment enregistrés et qui sera finalement passée à ajout_historique.
 * @param bool $a_frais Le montant des frais demandés ou NULL si ce sont les frais lors d'une opération qui sont prélevés.
 * @return ErrorObj Objet Erreur avec les frais prélevés en paramètre
 */
function preleveFraisDureeMinEntre2Retraits($a_id_cpte, &$comptable, $a_frais = NULL) {
  global $global_monnaie;
  $cpte = getAccountDatas($a_id_cpte);
  $frais = $cpte["frais_duree_min2retrait"];

  // Quel type de frais doit-on prélever ?
  if ($a_frais != NULL) {
    $frais = $a_frais;
  } else {
    // On va calculer le solde actuel en parcourant l'array comptable
    $solde = getSoldeDisponible($a_id_cpte);//$cpte["solde"];
    reset($comptable);
    foreach ($comptable as $key => $ligne) {
      if ($ligne["cpte_interne_cli"] == $a_id_cpte) {
        if ($ligne["sens"] == 'd')
          $solde -= $ligne['montant'];
        else if ($ligne["sens"] == 'c')
          $solde += $ligne['montant'];
      }
    }
    if ($solde < 0 || $solde < $frais) {
      /*global $global_client_debiteur;
      $global_client_debiteur = true;
      $frais = $cpte["frais_duree_min2retrait"];*/
      return  new ErrorObj(ERR_SOLDE_INSUFFISANT, $cpte["id_cpte"]);
    }
  }

  // S'il y a des frais à prélever, préparer les écritures comptables correspondantes
  if ($frais > 0) {
    //débit du compte d'épargne par le crédit d'un compte de produit
    $cptes_substitue = array();
    $cptes_substitue["cpta"] = array();
    $cptes_substitue["int"] = array();
    $cptes_substitue["cpta"]["debit"] = getCompteCptaProdEp($a_id_cpte);
    if ($cptes_substitue["cpta"]["debit"] == NULL) {
      return new ErrorObj(ERR_CPTE_NON_PARAM, _("compte comptable associé au produit d'épargne"));
    }

    $cptes_substitue["int"]["debit"] = $a_id_cpte;
    $err = effectueChangePrivate($cpte["devise"], $global_monnaie, $frais, 158, $cptes_substitue, $comptable);
    if ($err->errCode != NO_ERR) {
      return $err;
    }
  }

  return new ErrorObj(NO_ERR, $frais);
}


/**
 * Renvoie  la dernier entre pour le montant de quotite
 * @param int $id_client L'identifiant du client
 * @return one row
 */
function get_quotite_client($id_client) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * from ad_quotite where id_client = $id_client order by id desc limit 1";

	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	if ($result->numRows() == 0){
		$sql_cli = "SELECT mnt_quotite from ad_cli where id_client = $id_client";
		$result_cli = $db->query($sql_cli);
		if (DB::isError($result_cli)) {
			$dbHandler->closeConnection(false);
			signalErreur(__FILE__,__LINE__,__FUNCTION__);
		}
		$retour_cli = $result_cli->fetchrow(DB_FETCHMODE_ASSOC);
		$dbHandler->closeConnection(true);
		return $retour_cli;
	}
		$retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
		$dbHandler->closeConnection(true);
		return $retour;
}

/**
 * Ajouter un quotite
 * @author Pierre Tim
 * @param Array $DATA données sur le mandat
 * @return ErrorObj
 */
function ajouterQuotite($DATA) {
  global $dbHandler,$global_id_agence, $global_nom_login, $global_id_client;
  $db = $dbHandler->openConnection();
  $DATA['id_ag']= $global_id_agence;

  $sql = buildInsertQuery('ad_quotite',$DATA);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  // Enregistrement - Ajout d'un ordre permanent
  //ajout_historique(56, $global_id_client, 'Ajout d\'un ordre permanent', $global_nom_login, date("r"), NULL);

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Renvoie  la dernier entre pour le montant de quotite
 * @param int $id_client L'identifiant du client
 * @return one row
 */
function get_ordre_per($id_ord) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * from ad_ord_perm where id_ord = $id_ord";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $retour;
}
/**
 * Renvoie les ordres permanents pour un client pour informations financiers
 * @author BOLAH Roshan
 * @since 3.20
 * @param $id_client identifiant du client
 * @return la liste des ordres permanents
 */
function getOrdresPermParClientInfo($id_client) {
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  if(($id_client == null) or ($id_client == '')){
    erreur("getOrdresPermParClientInfo", sprintf(_("Le numéro du client n'est pas renseigné.")));
  }else {
    $sql = "SELECT id_ord, cpt_to, id_prod, (SELECT p.libel FROM adsys_produit_epargne p INNER JOIN ad_cpt c ON p.id = c.id_prod WHERE c.id_cpte = cpt_to) AS prod_libel, (SELECT num_complet_cpte FROM ad_cpt WHERE id_cpte = cpt_to) AS cpte_destination, type_transfert, date_prem_exe AS date_ouverture, date_fin, montant AS montant_virement, periodicite, (SELECT solde FROM ad_cpt WHERE id_cpte = cpt_to) AS solde";
    $sql .= " FROM ad_ord_perm, ad_cpt WHERE ad_ord_perm.id_ag = ad_cpt.id_ag AND ad_cpt.id_ag = $global_id_agence";
    $sql .= " AND id_cpte = cpt_from AND id_titulaire = $id_client ORDER BY id_ord";
  }
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0) return NULL;
  $LIST = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $LIST[$row['id_ord']] =  $row;
  }
  $dbHandler->closeConnection(true);

  return $LIST;
}

/**
 *Fonction pour verifier si le profil a d'access au fonction
 * PARAM : id profil et fonction
 * RETURN boolean
 */
function checkAcessFunc($fonction,$profil){
  global $dbHandler,$global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT id FROM adsys_profils_axs WHERE profil = $profil AND fonction = $fonction";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() > 0) return true;

  return false;
}

/**
 * Duplication du fonction existant dateExpValide - ticket REL-63
 * Petite modification $dateExp >= $now au lieu de $dateExp > $now
 */
function dateExpValideV2($sDateExp) {
  if($sDateExp == null){
    return true;
  }
  $dateExp = date_parse_from_format('Y-m-d H:i:s', $sDateExp . " 00:00:00");
  $now = date_parse_from_format('Y-m-d H:i:s', date("Y") . '-' . date("m") . '-' .date("d") . " 00:00:00");
  return $dateExp >= $now;
}

/**
 * Duplication du fonction existant getListeMandatairesActifs avec une petite modification en utilisant dateExpValideV2 au lieu de dateExpValide - ticket REL-63
 * Fabriquer la liste de tous les mandataires liés à un compte avec leur dénomination et le débit maximum
 * @author Roshan Bolah
 * @param integer $id_cpte identifiant du compte
 * @return Array liste des mandats du compte
 */
function getListeMandatairesActifsV2($id_cpte, $is_non_join=NULL, $have_CONJ_id=NULL) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $include_CONJ = true;
  $MANDATAIRES = getMandats($id_cpte);

  if ($MANDATAIRES == NULL) {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  foreach ($MANDATAIRES as $key=>$value) { // si on a un des mandataires qui signent conjointement, son mandat a expiré, alors le mandataire restant ne maintient pas le droit
    if ($value['type_pouv_sign'] == 2 && !dateExpValideV2($value['date_exp'])) {
      $include_CONJ = false;
    }
    if ($value['type_pouv_sign'] == 2 && $value['valide'] == 'f') {
      $include_CONJ = false;
    }
  }

  foreach ($MANDATAIRES as $key=>$value) {
    if ($value['valide'] == 't' && dateExpValideV2($value['date_exp'])) {
      if ($value['type_pouv_sign'] == 1 || $is_non_join) {
        $TMPARRAY[$key]['libelle'] = $value['denomination'];
        if ($value['limitation'] != NULL) {
          $TMPARRAY[$key]['libelle'] .= " - ".afficheMontant($value['limitation'])." ".$value['devise'];
          $TMPARRAY[$key]['limitation'] = $value['limitation'];
        }
      } else {
        if ($include_CONJ){ // on inclut les mandataires qui signent conjointement, si au cas pour tous, leurs mandats ne sont pas expiré
          $TMPARRAY['CONJ']['libelle'] .= $value['denomination'].", ";
          if ($have_CONJ_id){
            $TMPARRAY['CONJ_id']['id'] .= "$key-";
          }
        }
      }
    }
  }

  if ($TMPARRAY['CONJ'] != NULL) {
    $TMPARRAY['CONJ']['libelle'] = substr($TMPARRAY['CONJ']['libelle'], 0, $TMPARRAY['CONJ']['libelle'] - 2);
  }

  $dbHandler->closeConnection(true);
  return $TMPARRAY;
}

// ticket Jira AT-44
function insertRetraitDeplaceAttente($id_ag_distant, $id_client, $id_cpte, $type_retrait, $montant_retrait, $login, $communication = null, $remarque = null, $id_pers_ext = null, $mandat = null, $num_chq = null, $date_chq = null, $id_ben = null, $beneficiaire = null, $nom_ben = null, $denomination = null,$frais_retrait_cpt =null, $tib = null,$id_his = null, $etat_retrait = 1)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $tableFields = array(
    "montant_retrait" => recupMontant($montant_retrait),
    "frais_retrait_cpte" => recupMontant($frais_retrait_cpt),
    "etat_retrait" => $etat_retrait,
    "type_retrait" => $type_retrait,
    "id_ag_local" => $global_id_agence ,
    "id_ag_distant" => $id_ag_distant ,
    "id_client_distant" => $id_client,
    "id_cpte_distant" => $id_cpte,
    "id_his" => $id_his,
    "login" => trim($login),
    "communication" => trim($communication),
    "remarque" => trim($remarque),
    "id_pers_ext" => $id_pers_ext,
    "mandat" => $mandat,
    "num_chq" => trim($num_chq),
    "date_chq" => $date_chq,
    "id_ben" => $id_ben,
    "beneficiaire" => trim($beneficiaire),
    "nom_ben" => trim($nom_ben),
    "denomination" => trim($denomination),
    "date_creation" => date("r"),
    "tib" => $tib,
  );

  $sql = buildInsertQuery("ad_retrait_deplace_attente", $tableFields);

  $result = $db->query($sql);

  if (DB:: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  return new ErrorObj(NO_ERR);
}

/** Ticket Jira AT-44
 * Récupère une liste de demande de retrait en deplace
 *
 * @param null|int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:transferé / 4:refusé
 *
 * @return array|null
 */
function getListeRetraitDeplaceAttente($id_client = null, $etat_retrait_deplace = 1, $id_agence_remote= null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_retrait_deplace_attente WHERE id_ag_local = $global_id_agence ";

  if ($id_client != null) {
    $sql .= " AND id_client_distant = $id_client ";
  }

  if ($id_agence_remote != null){
    $sql .= " AND id_ag_distant = $id_agence_remote ";
  }

  if ($etat_retrait_deplace != 0) {
    $sql .= " AND etat_retrait = $etat_retrait_deplace ";
  }

  $sql .= " ORDER BY id ASC";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $tmp_arr = array();

  while ($ListDemandes = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $tmp_arr[$ListDemandes['id']] = $ListDemandes;
  }

  $dbHandler->closeConnection(true);

  return $tmp_arr;
}


/*******************************************************************************************************************/
// Ticket Jira AT-44
function processAutorisationRetraitDeplaceAttente($data = null, $id_client = null)
{
  global $dbHandler, $global_id_agence, $global_nom_login;

  // Get liste demande de retrait
  $listeDemandeRetraitDeplace = getListeRetraitDeplaceAttente($id_client);

  $demande_count = 0;
  foreach ($listeDemandeRetraitDeplace as $id => $demandeRetrait) {

    $db = $dbHandler->openConnection();

    $isValidationOK = false;
    $isAutorisationOK = false;

    $id_demande = trim($demandeRetrait["id"]);

    if (isset($data['btn_process_demande'])) {

      if (isset($data['check_valid_' . $id_demande])) {

        $fonction = 152; // Autorisation retrait

        $isValidationOK = true;
        $isAutorisationOK = true;

      } elseif (isset($data['check_rejet_' . $id_demande])) {

        $fonction = 94; // Refus retrait

        $isValidationOK = true;
      }

      if ($isValidationOK == true) {

        $myErr = ajout_historique($fonction, $id_client, "Demande autorisation de retrait en déplacé No. ".$id_demande, $global_nom_login, date("r"));

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        } else {

          // Mettre à jour le statut d'une demande de retrait à Autorisé / Refusé
          $erreur = updateRetraitDeplaceAttenteEtat($id_demande, (($isAutorisationOK) ? 2 : 4), sprintf("Demande autorisation de retrait en déplacé : %s", (($isAutorisationOK) ? "Autorisé" : "Refusé")), $myErr->param);

          if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
          } else {
            // Commit
            $dbHandler->closeConnection(true);

            $demande_count++;
          }
        }
      } else {
        $dbHandler->closeConnection(false);
      }
    } else {
      $dbHandler->closeConnection(false);
    }
  }

  return new ErrorObj(NO_ERR, $demande_count);
}

/*******************************************************************************************************************/
/*******************************************************************************************************************/
// Ticket Jira AT-39
function processAutorisationApprovisionnementDelestage($data = null)
{
  global $dbHandler, $global_id_agence, $global_nom_login;

  // Get liste demande approvisionnement/delestage
  $listeDemandeApproDelestage = getListeApprovisionnementDelestage();

  $demande_count = 0;
  foreach ($listeDemandeApproDelestage as $id => $demandeApproDelestage) {

    $db = $dbHandler->openConnection();

    $isValidationOK = false;
    $isAutorisationOK = false;

    $id_demande = trim($demandeApproDelestage["id"]);

    if (isset($data['btn_process_demande'])) {

      if (isset($data['check_valid_' . $id_demande])) {

        $fonction = 804; // Autorisation retrait

        $isValidationOK = true;
        $isAutorisationOK = true;

      } elseif (isset($data['check_rejet_' . $id_demande])) {

        $fonction = 805; // Refus retrait

        $isValidationOK = true;
      }

      if ($isValidationOK == true) {

        $myErr = ajout_historique($fonction, null, "Demande autorisation approvisionnement/delestage No. ".$id_demande, $global_nom_login, date("r"));

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        } else {

          // Mettre à jour le statut d'une demande de retrait à Autorisé / Refusé
          $erreur = updateEtatApprovisionnementDelestage($id_demande, (($isAutorisationOK) ? 2 : 4), $myErr->param);

          if ($erreur->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $erreur;
          } else {
            // Commit
            $dbHandler->closeConnection(true);

            $demande_count++;
          }
        }
      } else {
        $dbHandler->closeConnection(false);
      }
    } else {
      $dbHandler->closeConnection(false);
    }
  }

  return new ErrorObj(NO_ERR, $demande_count);
}

/*****************************************************************************/
/** Ticket Jira AT-39
 * Récupère une liste de demande approvisionnement/delestage
 *
 * @param null|int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:transferé / 4:refusé
 *
 * @return array|null
 */
function getListeApprovisionnementDelestage($id_guichet = null, $etat_appro_delestage = 1)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_approvisionnement_delestage_attente WHERE id_ag = $global_id_agence ";

  if ($id_guichet != null) {
    $sql .= " AND id_guichet = $id_guichet ";
  }

  if ($etat_appro_delestage != 0) {
    $sql .= " AND etat_appro_delestage = $etat_appro_delestage ";
  }


  $sql .= " ORDER BY id ASC";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }

  $tmp_arr = array();

  while ($ListDemandes = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $tmp_arr[$ListDemandes['id']] = $ListDemandes;
  }

  $dbHandler->closeConnection(true);

  return $tmp_arr;
}
/***************************************************************************************/
/*****************************************************************************/
/** Ticket Jira AT-39
 * Récupère une liste de demande approvisionnement/delestage specifique
 *
 * @param null|int $id_client
 * @param int $etat_retrait 0:Tous / 1:demandé / 2:autorisé / 3:transferé / 4:refusé
 *
 * @return array|null
 */
function getListeApprovisionnementDelestageSpecifique($id_guichet = null, $etat_appro_delestage = 1,$id_dem = null)
{
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_approvisionnement_delestage_attente WHERE id_ag = $global_id_agence ";

  if ($id_guichet != null) {
    $sql .= " AND id_guichet = $id_guichet ";
  }

  if ($etat_appro_delestage != 0) {
    $sql .= " AND etat_appro_delestage = $etat_appro_delestage ";
  }
  if ($id_dem != 0) {
    $sql .= " AND id = $id_dem ";
  }

  $sql .= " ORDER BY id ASC";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $dbHandler->closeConnection(true);

  return $retour;
}
/***************************************************************************************/

/**
 * Fonction pour recuperer l'id_ecriture si transaction/ mouvement est relié a la reprise IAP
 * PARAM : id_cpte, id_his
 * RETURN : integer id_ecriture
 */
function recupIdEcritureRepriseIAP($id_cpte=null, $id_his)
{
  global $dbHandler, $global_id_agence;
  $id_ecriture = 0;

  $db = $dbHandler->openConnection();

  $get_CompteIAP = getCompteIAP();

  if ($get_CompteIAP != '' || $get_CompteIAP != null) {
    if ($id_cpte != null || $id_cpte != ''){
      $sql_IAP="SELECT e.id_ecriture FROM ad_ecriture e INNER JOIN ad_mouvement m ON e.id_ecriture = m.id_ecriture WHERE e.type_operation = 40 AND e.id_his = $id_his AND m.cpte_interne_cli = $id_cpte AND m.sens = 'c'";
    }
    else{
      $sql_IAP="SELECT e.id_ecriture FROM ad_ecriture e INNER JOIN ad_mouvement m ON e.id_ecriture = m.id_ecriture WHERE e.type_operation = 40 AND e.id_his = $id_his AND m.cpte_interne_cli IS NULL AND m.compte = '$get_CompteIAP' AND m.sens = 'd'";
    }
    $result_IAP = $db->query($sql_IAP);
    if (DB::isError($result_IAP)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Erreur dans la requete SQL")));
    }
    if ($result_IAP->numRows() > 0) {
      $id_ecriture = $result_IAP->fetchrow();
      $id_ecriture = $id_ecriture[0];
    }
  }

  $dbHandler->closeConnection(true);

  return $id_ecriture;

}
/**
 * Fonction pour recuperer l'id_cpte client si transaction/ mouvement est relié a la reprise IAP
 * PARAM : id_his
 * RETURN : integer id_cpte
 */
function recupIdCpteClientRepriseIAP($id_his)
{
  global $dbHandler, $global_id_agence;
  $id_cpte = 0;

  $db = $dbHandler->openConnection();

  $get_CompteIAP = getCompteIAP();

  if ($get_CompteIAP != '' || $get_CompteIAP != null) {
    $sql_IAP="SELECT m.cpte_interne_cli FROM ad_ecriture e INNER JOIN ad_mouvement m ON e.id_ecriture = m.id_ecriture WHERE e.type_operation = 40 AND e.id_his = $id_his AND m.cpte_interne_cli IS NOT NULL AND m.sens = 'c' AND e.id_ecriture IN (SELECT e.id_ecriture FROM ad_ecriture e INNER JOIN ad_mouvement m ON e.id_ecriture = m.id_ecriture WHERE e.type_operation = 40 AND e.id_his = $id_his AND m.cpte_interne_cli IS NULL AND m.compte = '$get_CompteIAP' AND m.sens = 'd')";

    $result_IAP = $db->query($sql_IAP);
    if (DB::isError($result_IAP)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Erreur dans la requete SQL")));
    }
    if ($result_IAP->numRows() > 0) {
      $id_cpte = $result_IAP->fetchrow();
      $id_cpte = $id_cpte[0];
    }
  }

  $dbHandler->closeConnection(true);

  return $id_cpte;

}
/**
 * Fonction pour verifier si le montant, relié a une reprise IAP, est non arrondie
 * PARAM : id_his
 * RETURN : BOOLEAN $hasDecimal
 */
function hasDecimalMntRepriseIAP($id_his)
{
  global $dbHandler, $global_id_agence;
  $hasDecimal = false;
  $mntIAP = -1;
  //$devise = '';

  $db = $dbHandler->openConnection();

  $get_CompteIAP = getCompteIAP();

  if ($get_CompteIAP != '' || $get_CompteIAP != null) {
    $sql_IAP="SELECT m.montant, m.devise FROM ad_ecriture e INNER JOIN ad_mouvement m ON e.id_ecriture = m.id_ecriture WHERE e.type_operation = 40 AND e.id_his = $id_his AND m.sens = 'd' AND m.compte IN (SELECT cpte_cpta_int_paye FROM adsys_calc_int_paye WHERE id_ag = numagc())";

    $result_IAP = $db->query($sql_IAP);
    if (DB::isError($result_IAP)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("Erreur dans la requete SQL")));
    }
    if ($result_IAP->numRows() > 0) {
      $valIAP = $result_IAP->fetchrow();
      $mntIAP = $valIAP[0];
      //$devise = $valIAP[1];
      $mntIAP_Arrondie = ROUND($mntIAP);
      $diff = abs($mntIAP - $mntIAP_Arrondie);
      if ($diff > 0){
        $hasDecimal = true;
      }
    }
  }

  $dbHandler->closeConnection(true);

  return $hasDecimal;

}

/**
 * Vérifie que le retrait par ewallet est possible sur le compte
 * @param $InfoCpte
 * @param $montant
 * @return ErrorObj
 */
function CheckRetraitEwalletMobile($InfoCpte, $montant)
{
    $InfoProduit = getProdEpargne($InfoCpte['id_prod']);

    //vérification de l'état du compte : ouvert
    if ($InfoCpte["etat_cpte"] == 2){
        return new ErrorObj(ERR_CPTE_FERME, $InfoCpte["id_cpte"]);
    }
    else {
        return CheckRetrait($InfoCpte, $InfoProduit, $montant, 2, null, false); // Operation 2 for retrait ewallet
    }
}

/**
 * Verifie si la transaction a eu lieu sur ADBanking
 *
 * @param $typeOperation
 * @param $idTransactionExterne
 * @param $sens
 * @param $montant
 * @param $idCpte
 * @param $idAg
 * @return |null
 */
function checkTransactionEwallet($typeOperation, $idTransactionExterne, $sens, $montant, $idCpte, $idAg)
{
  global $dbHandler;

  $db = $dbHandler->openConnection();

  $sql = "SELECT e.id_his FROM ad_ecriture e INNER JOIN ad_mouvement m ON e.id_ecriture = m.id_ecriture AND e.id_ag = m.id_ag";
  $sql .= " WHERE e.info_ecriture = '$idTransactionExterne'";
  $sql .= " AND e.type_operation = $typeOperation";
  $sql .= " AND e.id_ag = $idAg";
  $sql .= " AND m.cpte_interne_cli = $idCpte";
  $sql .= " AND m.sens = '$sens'";
  $sql .= " AND m.montant = $montant";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return NULL;
  }
  $retour = $result->fetchrow(DB_FETCHMODE_ASSOC);

  $dbHandler->closeConnection(true);

  return $retour;
}
function debloqMontantCredit($id_client, $id_cpte, $id_doss, $mnt_bloq_cre, $cre_mnt_bloq){
    global $dbHandler, $global_id_agence, $global_nom_login;
    $db = $dbHandler->openConnection();

    $sql = "SELECT DISTINCT d.id_doss, d.id_client, d.cpt_liaison FROM ad_dcr d INNER JOIN ad_etr e ON e.id_doss = d.id_doss AND e.id_ag = d.id_ag 
           WHERE d.id_ag = $global_id_agence AND d.cpt_liaison = $id_cpte AND d.is_ligne_credit = FALSE 
           AND (d.etat = 5 OR d.etat = 7 OR d.etat = 9 OR d.etat = 13 OR d.etat = 14 OR d.etat = 15) AND e.date_ech <= (NOW() + 
           ".'"interval"'."(nb_jr_bloq_cre_avant_ech::INTEGER||' days')) AND e.remb = 'f' AND 1 = check_cre_mnt_bloq_ech_date(e.date_ech, 
           d.nb_jr_bloq_cre_avant_ech::INTEGER) AND d.id_doss = $id_doss";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        return new ErrorObj(ERR_DB_SQL);
    }

    $tmp_arr = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $tmp_arr[] = $row;
    }

    foreach ($tmp_arr as $value){
        $sql_update_adcpt = "UPDATE ad_cpt SET mnt_bloq_cre = $mnt_bloq_cre WHERE id_cpte = ".$value['cpt_liaison'];

        $result_adcpt = $db->query($sql_update_adcpt);
        if (DB::isError($result_adcpt)) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_DB_SQL);
        }

        $sql_update_addcr = "UPDATE ad_dcr SET cre_mnt_bloq = $cre_mnt_bloq WHERE id_doss = ".$value['id_doss'];

        $result_addcr = $db->query($sql_update_addcr);
        if (DB::isError($result_addcr)) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_DB_SQL);
        }
    }

    $myErr = ajout_historique(89, $id_client, '', $global_nom_login, date('r'));
    $id_his = $myErr->param;

    $dbHandler->closeConnection(true);
    return new ErrorObj(NO_ERR, array('id'=>$id_his));
}
function getAllDossier($id_client, $condition = null, $include_closed_accounts=false) {
    global $dbHandler;
    global $global_id_agence;
    $db = $dbHandler->openConnection();
    $sql = "SELECT id_prod_cpte_credit FROM ad_agc WHERE id_ag = $global_id_agence;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $tmprow = $result->fetchRow();
    $id_prod_cre = $tmprow[0];
    $sql = "SELECT a.*, b.*, c.id_doss, c.cre_mnt_bloq FROM ad_cpt a,adsys_produit_epargne b, ad_dcr c, ad_etr e
        WHERE a.id_ag=b.id_ag and a.id_ag=$global_id_agence AND a.id_prod = b.id 
        AND e.id_doss = c.id_doss
        AND c.id_client = a.id_titulaire AND a.id_titulaire = $id_client
        AND b.id <> $id_prod_cre  AND b.classe_comptable <> 8 
        AND a.etat_cpte <> 2
        AND mnt_bloq_cre > 0 AND cre_mnt_bloq > 0
        AND c.etat IN (5, 7, 9 ,13, 14, 15)
        AND e.date_ech <= (NOW() + \"interval\"(nb_jr_bloq_cre_avant_ech::INTEGER||' days')) AND e.remb = 'f' 
        AND 1 = check_cre_mnt_bloq_ech_date(e.date_ech, c.nb_jr_bloq_cre_avant_ech::INTEGER)
        ORDER BY a.num_cpte";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) return NULL;

    $TMPARRAY = array();
    while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $TMPARRAY[$prod['id_doss']] = $prod;
    }

    return $TMPARRAY;
}
function getAccountStatement($id_client, $condition = null, $include_closed_accounts=false) {
    global $dbHandler;
    global $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT id_prod_cpte_credit FROM ad_agc WHERE id_ag = $global_id_agence;";
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $tmprow = $result->fetchRow();
    $id_prod_cre = $tmprow[0];
    $sql = "SELECT a.*, b.* FROM ad_cpt a,adsys_produit_epargne b
          WHERE a.id_ag=b.id_ag and a.id_ag = $global_id_agence AND a.id_prod = b.id 
          AND a.id_titulaire = '$id_client' AND b.id <> $id_prod_cre  AND b.classe_comptable <> 8 ";

    if ($include_closed_accounts == false)
        $sql .= " AND a.etat_cpte <> 2 ";

    if(!empty($condition))
        $sql .= " AND ".$condition;
    $sql .= " ORDER BY a.num_cpte";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) return NULL;

    $TMPARRAY = array();
    while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $TMPARRAY[$prod['id_cpte']] = $prod;
    }

    return $TMPARRAY;
}
function rembourseMontantDepot($id_client, $montant_remboursement){
    global $global_id_guichet;
    $dossier_ml_demande = getDemandeCredit($id_client, " statut_demande IN (2, 5) ORDER BY id_doss ASC limit 1");
    if((sizeof($dossier_ml_demande) > 0) && ($montant_remboursement > 0)){
        $id_doss = $dossier_ml_demande[0]["id_doss"];
        $data_remboursement["infos_doss"][$id_doss] = getDossierCrdtInfo($id_doss);
        $data_remboursement["infos_doss"][$id_doss]["infos_credit"] = get_info_credit($id_doss);
        $data_remboursement["infos_doss"][$id_doss]["DATA_GAR"] = array();
        $data_remboursement["infos_doss"][$id_doss]["gar_num_mob"] = NULL;
        $data_remboursement["infos_doss"][$id_doss]["ech_paye"] = NULL;
        $data_remboursement["infos_doss"][$id_doss]["derniereech"] = NULL;
        $data_remboursement["infos_doss"][$id_doss]["mnt_remb"] = $montant_remboursement;
        $myErr = rembourse_montantInt($data_remboursement["infos_doss"], 2, $global_id_guichet, NULL, null);
        if ($myErr->errCode != NO_ERR) {
            return $myErr;
        }
    }

    return new ErrorObj(NO_ERR);
}

function getAccountClient($id_client, $condition = null, $include_closed_accounts=false) {
    global $dbHandler;
    global $global_id_agence, $global_id_client;
    $db = $dbHandler->openConnection();

    $sql = "SELECT c.* FROM ad_cpt c INNER JOIN adsys_produit_epargne e on e.id = c.id_prod where c.id_titulaire =  $id_client and e.classe_comptable = 1";

    if ($include_closed_accounts == false)
        $sql .= " AND c.etat_cpte <> 2 ";

    if(!empty($condition))
        $sql .= " AND ".$condition;
    $sql .= " ORDER BY c.num_cpte";

    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }

    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) return NULL;

    $TMPARRAY = array();
    while ($prod = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $TMPARRAY[$prod['id_cpte']] = $prod['num_complet_cpte']." ".$prod["intitule_compte"];
    }

    return $TMPARRAY;
}

/**
 * Return array of mouvement for a particular id_cpte
 * 
 * @param $id_cpte
 * @param $limit
 * @return array|null
 */
function getMouvementHistData($id_cpte, $limit){

  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = sprintf(
  "SELECT
  case when h.id_client is null then cpt.id_titulaire else h.id_client end as id_client,
  m.id_ag,
  m.cpte_interne_cli as id_cpte,
  m.id_ecriture as id_transaction,
  m.id_mouvement,
  h.date as date_transaction,
  e.ref_ecriture,
  e.type_operation as type_opt,
  t.traduction as libelle_ecriture,
  m.montant,
  m.sens,
  m.devise,
  histo_ext.communication,
  case when h.type_fonction in (70,75) then histo_ext.tireur else null end as tireur,
  histo_ext.nom_client AS donneur,
  histo_ext.numero_cheque
  from ad_mouvement m
  inner join ad_ecriture e on e.id_ag=m.id_ag and e.id_ecriture=m.id_ecriture
  inner join ad_his h on h.id_ag=e.id_ag and h.id_his=e.id_his
  left join (select
             ext.id_ag,
             ext.id,
	     p.nom_client,
	     tb.denomination as tireur,
             case when ext.type_piece in (2,4,5,15) then ext.num_piece else null end AS numero_cheque,
             ext.communication
	     from ad_his_ext ext
             left join
			(select pers.id_ag,pers.id_client,pers.id_pers_ext,
			COALESCE (CASE statut_juridique WHEN '1' THEN pp_nom||' '||pp_prenom
			WHEN '2' THEN pm_raison_sociale WHEN '3'  THEN gi_nom WHEN '4'  THEN gi_nom END, pers.denomination)  AS nom_client
			FROM ad_pers_ext pers
			left join  ad_cli cli on cli.id_ag = pers.id_ag and cli.id_client = pers.id_client) p on ext.id_ag  = p.id_ag and ext.id_pers_ext = p.id_pers_ext
			left join tireur_benef tb on ext.id_tireur_benef = tb.id and ext.id_ag = tb.id_ag
			) histo_ext on histo_ext.id_ag=h.id_ag and h.id_his_ext = histo_ext.id
  inner join ad_traductions t on t.id_str =e.libel_ecriture
  inner join ad_cpt cpt on m.id_ag = cpt.id_ag and m.cpte_interne_cli = cpt.id_cpte
  where  m.cpte_interne_cli = %d
  ORDER BY m.date_valeur DESC, id_mouvement DESC
  LIMIT %d
    ", $id_cpte, $limit);

  $result = $db->query($sql);
  if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0) {
      return NULL;
  }

  $DATAS=array();
  
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ){
    $DATAS[] = $row;
  }

  return $DATAS;
}

/**
 * Vérifie que le retrait ATM est possible sur le compte
 * @param $InfoCpte
 * @param $montant
 * @return ErrorObj
 */
function CheckRetraitATM($InfoCpte, $montant)
{
    $InfoProduit = getProdEpargne($InfoCpte['id_prod']);

    //vérification de l'état du compte : ouvert
    if ($InfoCpte["etat_cpte"] == 2){
        return new ErrorObj(ERR_CPTE_FERME, $InfoCpte["id_cpte"]);
    }
    else {
        return CheckRetrait($InfoCpte, $InfoProduit, $montant, 2, null, false); // Operation 2 for retrait ewallet
    }
}

/**
 *  Vérifie que le dépôt est possible sur le compte par ATM
 * @param $InfoCpte
 * @param $montant
 * @return ErrorObj
 */
function CheckDepotATM($InfoCpte, $montant) {
  // On ne peut pas déposer sur un compte fermer
  if ($InfoCpte["etat_cpte"] == 2){
    $num_complet_cpte  = $InfoCpte["num_complet_cpte"];
    $id_client = $InfoCpte["id_titulaire"];
    return new ErrorObj(ERR_CPTE_FERME, sprintf(_("Le Compte n° %s du client n° %s est fermé.") , $num_complet_cpte, $id_client));
  } else {
    return CheckDepot($InfoCpte, $montant);
  }

}
?>
