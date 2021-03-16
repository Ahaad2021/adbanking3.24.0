<?php
/* Finalisation défection d'un client.
   TF - 05/03/2002 */

require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/agence.php');
require_once('lib/dbProcedures/compte.php');
require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/credit.php');
require_once('lib/misc/divers.php');
require_once('lib/misc/VariablesSession.php');
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once 'modules/epargne/recu.php';

if ($global_nom_ecran == 'Fcl-1') {
  $CLI = getClientDatas($global_id_client);
  if ($CLI['etat'] != 7) {
    $erreur = new HTML_erreur(_("Finalisation défection non autorisée"));
    $erreur->setMessage(_("Le client doit être en état 'Attente d'enregistrement du décès' pour que cette fonction puisse être appelée. Effectuez d'abord la procédure de défection."));
    $erreur->addButton(BUTTON_OK,"Gen-9");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  } else {
    $myForm = new HTML_GEN2(_("Situation finale du client"));

    /* Récupération des balances de toutes les devises:finalisation défection est effectuée dans le cas de décès => getBalanceDeces */
    $balances = getBalanceDeces($global_id_client);
    $SESSION_VARS['balance'] = $balances;

    /* tous les comptes d'épargne du client non fermés y compris le compte de garantie et le compte de parts sociales */
    $CPTS = getAllAccounts($global_id_client);

    /* Si le compte de garantie n'appartient pas au client, l'afficher quand même */
    /* Récupération des dossiers de crédit à l'état 'Fonds déboursés' ou 'En attente de rééchel/Moratoire' ? */
    $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
    $dossiers = getIdDossier($global_id_client, $whereCl);
    $myForm->addHTMLExtraCode("titre1","<h4  align=\"center\">"._("Situation des comptes")."</h4>");
    foreach($dossiers as $id_doss=>$value) {
      /* Récupération de l'épargne nantie numéraire du dossier */
      $liste_gar = getListeGaranties($id_doss);
      foreach($liste_gar as $key=>$val) {
        /* la garantie doit être numéraire, non restituée et non réalisée  */
        if ($val['type_gar'] == 1 and $val['etat_gar'] != 4 and $val['etat_gar'] != 5 ) {
          $nantie = $val['gar_num_id_cpte_nantie'] ;
          $CPT_NANTIE = getAccountDatas($nantie);
          /* On affiche tout le solde mais la fonction defection calculera le montant à prélever */
          if ($CPT_NANTIE["id_titulaire"] != $global_id_client)
            $CPTS[$nantie] = $CPT_NANTIE;
        }
      }
    }

    while (list($key, $value) = each($CPTS)) {
      $infos_simul = simulationArrete($key);
      $soldeCloture = $infos_simul["solde_cloture"];

      $myForm->addField("num_cpte".$key, _("Numéro de compte"), TYPC_TXT);
      $myForm->addField("type_cpte".$key, _("Produit"), TYPC_TXT);
      $myForm->addField("solde".$key, _("Solde"), TYPC_MNT);
      $myForm->setFieldProperties("num_cpte".$key, FIELDP_DEFAULT, $value["num_complet_cpte"]);
      $myForm->setFieldProperties("type_cpte".$key, FIELDP_DEFAULT, $value["libel"]);
      $myForm->setFieldProperties("solde".$key, FIELDP_DEFAULT, $soldeCloture);
      $myForm->setFieldProperties("solde".$key, FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("num_cpte".$key, FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("type_cpte".$key, FIELDP_IS_LABEL, true);
      $myForm->addHTMLExtraCode("line".$key, "<BR>");
    }

    /* Solde d'un éventuel crédit */
    /* Récupération des dossiers de crédit à l'état 'Fonds déboursés' ou 'En attente de rééchel/Moratoire' ? */
    $myForm->addHTMLExtraCode("titre2","<h4  align=\"center\">".("Simulation arrêté de compte crédit")."</h4>");
    $whereCl=" AND (etat = 5 OR etat = 7 OR etat = 14 OR etat = 15)";
    $dossiers = getIdDossier($global_id_client, $whereCl);
    foreach($dossiers as $id_doss=>$value) {
      $solde_credit = 0;
      $myErr = simulationArreteCpteCredit($solde_credit,$id_doss);
      if ($myErr->errCode == NO_ERR) {
        $myForm->addField("credit".$id_doss, _("Solde crédit en cours"), TYPC_MNT);
        $myForm->setFieldProperties("credit".$id_doss, FIELDP_DEFAULT, ($solde_credit * -1));
        $myForm->setFieldProperties("credit".$id_doss, FIELDP_IS_LABEL, true);
        $myForm->addHTMLExtraCode("line_cre".$id_doss, "<BR>");
      }
    }

    /* Balance du client dans toutes les devises */
    foreach($balances as $devise=>$balance) {
      setMonnaieCourante($devise);
      $myForm->addField("balance$devise", _("Balance"), TYPC_MNT);
      $myForm->setFieldProperties("balance$devise", FIELDP_DEFAULT, $balance);
      $myForm->setFieldProperties("balance$devise", FIELDP_IS_LABEL, true);
    }

    $myForm->addFormButton(1,1,"ok", _("OK"), TYPB_SUBMIT);
    $myForm->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Fcl-2');
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
    $myForm->buildHTML();
    echo $myForm->getHTML();

  }
} else if ($global_nom_ecran == 'Fcl-2') {
  /* Récupération des ayant-droit du client */
  $AyantDroit = existeAyantDroit($global_id_client);
 
  if (count($AyantDroit) == 0 && $DOSS['gs_cat'] != 2) {
    /* Le client n'a pas d'ayant-droit, incorporation du solde aux pertes ou aux profits de l'agence */
    $myMsg = new HTML_message(_("ATTENTION"));
    $myMsg->setMessage(_("Aucun ayant-droit n'a été désigné. Si le client est créditeur, son solde sera incorporé aux produits de l'agence. Si le client est débiteur, son solde sera incorporé aux pertes de l'agence. S'il a un crédit solidaire il sera pris en charge par le groupe auquel il appartient.")."<br/><br/> "._("Voulez-vous continuer l'opération ?"));
    $myMsg->addButton(BUTTON_OUI, 'Fcl-4');
    $myMsg->addButton(BUTTON_NON, 'Gen-9');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  } elseif (count($AyantDroit) > 0) { /* Le client possède au moins un ayant droit */
    /* Vérifier qu'il n'existe pas de devise étrangère avec une balance non nulle ? */
    foreach($SESSION_VARS['balance'] as $devise=>$balance)
    if ($devise != $global_monnaie and  $balance != 0) {
      setMonnaieCourante($devise);
      $msg .= _("Le client possède une balance non nulle dans la devise "). $devise." (".afficheMontant($balance, true).")<BR>"._("La défection ne peut avoir lieu que si tous les avoirs et dettes du client en devise étrangère ont été annulés")."<br/>";
      $erreur = new HTML_erreur(_("Défection client"));
      $erreur->setMessage($msg);
      $erreur->addButton(BUTTON_OK,"Gen-9");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      exit();
    }

    /* A ce niveau, le client possède au moins un ayant-droit et toutes les devises étrangères ont chacune une balance nulle */
    $Title = "Solde des comptes";
    $myForm = new HTML_GEN2($Title);

    $myForm->addHTMLExtraCode ("Titre", "<h3 align=\"center\"> "._("Informations sur les ayant-droit" )."</h3>");

    $table =& $myForm->addHTMLTable('tablerels', /*nbre colonnes*/ 4, TABLE_STYLE_ALTERN);
    $table->add_cell(new TABLE_cell(_("Dénomination"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
    $table->add_cell(new TABLE_cell(_("Adresse"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
    $table->add_cell(new TABLE_cell(_("Date de naissance"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
    $table->add_cell(new TABLE_cell(_("Lieu de naissance"), 	/*colspan*/ 1, 	/*rowspan*/ 1));

    $ayant_droit = array(); 
    // Construction de la table
    foreach($AyantDroit as $key=>$REL) {
      $infos_rel = getPersExtDatas($REL["id_pers_ext"]);
      $table->add_cell(new TABLE_cell($infos_rel["denomination"]));
      $table->add_cell(new TABLE_cell($infos_rel["adresss"]));
      $table->add_cell(new TABLE_cell($infos_rel["date_naiss"]));
      $table->add_cell(new TABLE_cell($infos_rel["lieu_naiss"]));
      $ayant_droit[0] = "Aucun"; 
 	    $ayant_droit[$REL["id_pers_ext"]] = $infos_rel['denomination']; 
 	  } 
 	 
 	  //Précisez le nom de l'ayant droit qui s'est présenté. 
 	  $myForm->addHTMLExtraCode ("Séparateur1", "<BR>"); 
 	  $myForm->addField("nom_ayant_droit", _("Ayant droit présent"), TYPC_LSB); 
 	  $myForm->setFieldProperties("nom_ayant_droit", FIELDP_ADD_CHOICES, $ayant_droit); 
 	  $myForm->setFieldProperties("nom_ayant_droit", FIELDP_HAS_CHOICE_AUCUN, false);    
 	  $myForm->setFieldProperties("nom_ayant_droit", FIELDP_IS_REQUIRED, true);
   

    /* Si la balance dans la devise de référence est positive */
    if ($SESSION_VARS["balance"][$global_monnaie] > 0) {
      $SESSION_VARS["balanceArrondie"] = arrondiMonnaie($SESSION_VARS['balance'][$global_monnaie], -1);

      $myForm->addHTMLExtraCode ("Séparateur", "<br/>");
      $myForm->addField("adecaisser", _("Somme à décaisser"), TYPC_MNT);
      $myForm->setFieldProperties("adecaisser", FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("adecaisser", FIELDP_DEFAULT, arrondiMonnaie($SESSION_VARS['balance'][$global_monnaie], -1));
      $myForm->addField("decaisse", _("Somme décaissée"), TYPC_MNT);
      $myForm->setFieldProperties("decaisse", FIELDP_IS_REQUIRED, true);
      $myForm->addJS(JSP_BEGIN_CHECK, "checkMoney", "if (".arrondiMonnaie($SESSION_VARS['balance'][$global_monnaie], -1)." != recupMontant(document.ADForm.decaisse.value)) { ADFormValid = false; msg += 'La somme décaissée doit être égale à la somme à décaisser';}");

      if ($global_billet_req) {
        $myForm->setFieldProperties("decaisse", FIELDP_HAS_BILLET, true);
        $myForm->setFieldProperties("decaisse", FIELDP_SENS_BIL, SENS_BIL_OUT);
      }
      $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
      $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
      $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
      $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Fcl-3');
      $myForm->buildHTML();
      echo $myForm->getHTML();
    }
    elseif ($SESSION_VARS["balance"][$global_monnaie] < 0) {
      /* Il existe un ayant-droit pour ce client & balance dans la devise de référence est < 0 */
      $SESSION_VARS["balanceArrondie"] = -(arrondiMonnaie(abs($SESSION_VARS['balance'][$global_monnaie]),1));

      $myForm->addHTMLExtraCode ("Séparateur", "<br/>");
      $myForm->addField("aencaisser", _("Somme à encaisser"), TYPC_MNT);
      $myForm->setFieldProperties("aencaisser", FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("aencaisser", FIELDP_DEFAULT, arrondiMonnaie(abs($SESSION_VARS['balance'][$global_monnaie]),1));
      $myForm->addField("encaisse", _("Somme encaissée"), TYPC_MNT);
      $myForm->setFieldProperties("encaisse", FIELDP_IS_REQUIRED, true);
      if ($global_billet_req) {
        $myForm->setFieldProperties("encaisse", FIELDP_HAS_BILLET, true);
        $myForm->setFieldProperties("encaisse", FIELDP_SENS_BIL, SENS_BIL_IN);
      }
      $myForm->addJS(JSP_BEGIN_CHECK, "checkMoney", "if (".arrondiMonnaie(abs($SESSION_VARS['balance'][$global_monnaie]),1)." != recupMontant(document.ADForm.encaisse.value)) { ADFormValid = false; msg += '"._("La somme encaissée doit être égale à la somme à encaisser")."';}");
      $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
      $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
      $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
      $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Fcl-3');
      $myForm->buildHTML();
      echo $myForm->getHTML();
    }
    else { /* Il existe un ayant-droit et la balance dans la devise de référence est à 0 */
      $myErr = finalisationDefectionClientAvecAyantDroit ($global_id_client, $global_id_guichet);
      if ($myErr->errCode != NO_ERR) {
        $html_err = new HTML_erreur(_("Erreur lors de la finalisation de la défection"));
        $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]);
        $html_err->addButton("BUTTON_OK", 'Gen-8');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit();
      }

      $global_id_client = '';
      $global_nom_client = '';
      $myMsg = new HTML_message(_("Finalisation Défection terminée"));
      $myMsg->setMessage(_("L'opération de finalisation de la défection s'est terminée avec succès"));
      $myMsg->addButton(BUTTON_OK, 'Gen-3');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
    }
  } /* Fin else du if (count($AyantDroit) */
  elseif ($DOSS['gs_cat'] == 2){
    // Affichage des membres du groupe
    $myForm = new HTML_GEN2();
    $listeGroupSol = getGroupSol($global_id_client);
    $myForm->addHTMLExtraCode ("infos", "<h3 align=\"center\"> "._("Informations sur les groupes")." </h3>");
    $table =& $myForm->addHTMLTable('membres', /*nbre colonnes*/ 3, TABLE_STYLE_ALTERN);
   	$table->add_cell(new TABLE_cell(_("Nom groupe"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
    $table->add_cell(new TABLE_cell(_("Adresse"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
    $table->add_cell(new TABLE_cell(_("Date d'adhésion"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
    foreach($listeGroupSol->param as $cle => $valeur) {
      $infos_membre = getClientDatas($valeur['id_grp_sol']);
      $table->add_cell(new TABLE_cell($infos_membre["gi_nom"]));
      $table->add_cell(new TABLE_cell($infos_membre["adresse"]));
      $table->add_cell(new TABLE_cell($infos_membre["date_adh"]));
    }
   
    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Fcl-5');
    $myForm->buildHTML();
    echo $myForm->getHTML();
  } /*fin else if (count($listeGroupSol)) */   
} elseif ($global_nom_ecran == 'Fcl-3') {     // L'ayant-droit récupère le solde créditeur / paye le solde débiteur
  /* FIXME : Il faut demander quand même au guichetier de faire la saisie */
  $nomination = getPersExtDatas($nom_ayant_droit); 
 	$nom_ayant_droit = $nomination['denomination'];
  $myErr = finalisationDefectionClientAvecAyantDroit ($global_id_client, $global_id_guichet);
  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Erreur lors de la finalisation de la défection"));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]);
    $html_err->addButton("BUTTON_OK", 'Gen-8');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    // Impression du reçu de défection
    $id_his = $myErr->param;
    print_recu_defection($global_id_client, 3, $SESSION_VARS["balanceArrondie"], $id_his, $nom_ayant_droit);

    $myMsg = new HTML_message(_("Finalisation Défection terminée"));
    $myMsg->setMessage(_("L'opération de finalisation de la défection s'est terminée avec succès"));
    $myMsg->addButton(BUTTON_OK, 'Gen-3');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }
} elseif ($global_nom_ecran == 'Fcl-4') { // Ecran de passage en produits du solde créditeur / passage en perte du solde débiteur
  $myErr = defectionClientDecedeSansAyantDroit ($global_id_client);
  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Erreur lors de la finalisation de la défection"));
    $html_err->setMessage(_("La déféction n'a pas été effectuée")." : ".$error[$myErr->errCode] ." ".$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-8');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  $myMsg = new HTML_message(_("Finalisation Défection terminée"));
  $myMsg->setMessage(_("L'opération de finalisation de la défection s'est terminée avec succès"));
  $myMsg->addButton(BUTTON_OK, 'Gen-3');
  $myMsg->buildHTML();
  echo $myMsg->HTML_code;
} elseif ($global_nom_ecran == 'Fcl-5'){
	$myErr = defectionClientDecedeSansAyantDroit ($global_id_client);
    if ($myErr->errCode != NO_ERR) {
	  $html_err = new HTML_erreur(_("Erreur lors de la finalisation de la défection"));
	  $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]);
	  $html_err->addButton("BUTTON_OK", 'Gen-8');
	  $html_err->buildHTML();
	  echo $html_err->HTML_code;
  } else {
      $myMsg = new HTML_message(_("Finalisation Défection terminée"));
      $myMsg->setMessage(_("L'opération de finalisation de la défection s'est terminée avec succès"));
      $myMsg->addButton(BUTTON_OK, 'Gen-3');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
  }
}else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' non pris en charge"

?>
