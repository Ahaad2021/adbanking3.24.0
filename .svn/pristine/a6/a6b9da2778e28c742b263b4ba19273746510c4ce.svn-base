<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Perception des frais d'adhésion pour un client en cours de validation
 * @author Thomas Fastenakel
 * @since 08/04/2002
 * @package Clients
 **/

require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/agence.php');
require_once('lib/dbProcedures/compte.php');
require_once('lib/dbProcedures/epargne.php');
require_once('lib/misc/divers.php');
require_once('lib/misc/VariablesSession.php');
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once('modules/epargne/recu.php');

/*{{{ Pfh-1 : Confirmation du montant à payer */
if ($global_nom_ecran == "Pfh-1") {
  global $global_id_client;

  $AG_DATA = getAgenceDatas($global_id_agence);
  $PROD = getProdEpargne(getBaseProductID($global_id_agence));
  $ACC = getAccountDatas(getBaseAccountID($global_id_client));
  $CLI = getClientDatas($global_id_client);
  $mnt_droits_adhesion = getMontantDroitsAdhesion($CLI["statut_juridique"]);
  $mnt_min = $ACC['mnt_min_cpte'];
  if ($mnt_min == "") $mnt_min = 0;
  $soldeFrais = getSoldeFraisAdhesion($global_id_client);
  $soldeFraisRestant = $soldeFrais->param[0]['solde_frais_adhesion_restant'];
  debug($soldeFraisRestant);


  if ($soldeFraisRestant > 0 && $CLI['etat']!=1  ) {
    // Droits d'adhésion
    $Title = _("Perception tranche frais d'adhésion");
    $myForm = new HTML_GEN2($Title);

    //recuperation du montant des frais d'adhesion deja payé
    $fraisAdhesionpaye=getFraisAdhesionPaye($global_id_client);
    if ($result->errCode != NO_ERR) {
    $myForm->setTitle(_("Erreur lors de la  perception de frais d'adhésion : ").$result->param[0]);
  	} else {
    $mnt_droits_adhesion=$fraisAdhesionpaye->param[0]+$soldeFraisRestant;
  	}
     $soldeFraisPaye = $fraisAdhesionpaye->param[0];
     $SESSION_VARS["montant_frais_adhesion"]=$mnt_droits_adhesion;

    $solde = $ACC['solde']- $ACC['mnt_min_cpte'];
    //solde cpte de base
    $myForm->addField("solde", _("Solde compte de base"), TYPC_MNT);
    $myForm->setFieldProperties("solde", FIELDP_DEFAULT, $solde);

    $myForm->addField("mnt_adhesion", _("Montant des droits d'adhésion"), TYPC_MNT);
    $myForm->setFieldProperties("mnt_adhesion", FIELDP_DEFAULT,$mnt_droits_adhesion);
    //$myForm->setFieldProperties("mnt_adh", FIELDP_JS_EVENT, array("onchange" => "updateMin();"));

    //montant déja payé des frais
    $myForm->addField("mnt_adh_paye", _("Montant frais déja payé"), TYPC_MNT);
    $myForm->setFieldProperties("mnt_adh_paye", FIELDP_DEFAULT,$soldeFraisPaye);

    //montant restant des frais
    $myForm->addField("mnt_adh", _("Montant restant des  droits d'adhésion"), TYPC_MNT);
    $myForm->setFieldProperties("mnt_adh", FIELDP_DEFAULT,$soldeFraisRestant);

    $myForm->addField("paye", _("Montant du versement"), TYPC_MNT);
    $myForm->setFieldProperties("paye", FIELDP_IS_REQUIRED, true);
    $myForm->addField("confpaye", _("Confirmation du montant"), TYPC_MNT);
    $myForm->setFieldProperties("confpaye", FIELDP_IS_REQUIRED, true);

    $myForm->addJS(JSP_BEGIN_CHECK, "js1", "if (recupMontant(document.ADForm.paye.value) > recupMontant(document.ADForm.solde.value)) { msg += '- "._("Le solde du compte de base est insuffisant").".\\n'; document.ADForm.solde.value='';ADFormValid = false;}");
    $myForm->addJS(JSP_BEGIN_CHECK, "js3", "if (recupMontant(document.ADForm.paye.value) > recupMontant(document.ADForm.solde.value)) { document.ADForm.solde.value=''; ADFormValid = false;}");
    $myForm->addJS(JSP_BEGIN_CHECK, "js2", "if (recupMontant(document.ADForm.paye.value) != recupMontant(document.ADForm.confpaye.value)) { msg += '- "._("Les montants ne correspondent pas").".\\n';ADFormValid = false;}");

    if ($global_billlet_req) {
      $myForm->setFieldProperties("confpaye", FIELDP_HAS_BILLET, true);
    }
    //labels
    $myForm->setFieldProperties("confpaye", FIELDP_HAS_BILLET, true);
    $myForm->setFieldProperties("solde", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("mnt_adh", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("mnt_adh_paye", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("mnt_adhesion", FIELDP_IS_LABEL, true);


    $myForm->addFormButton(1,1, "valider",_("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1,2, "annuler",_("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Pfh-2');
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $myForm->buildHTML();
    echo $myForm->getHTML();
  } else {
    $Title = _("Perception des frais d'adhésion");
    $myForm = new HTML_GEN2($Title);

    // Droits d'adhésion
    $Title = "Perception des frais d'adhésion";
    $myForm = new HTML_GEN2($Title);
    $myForm->addField("mnt_adh", _("Montant des droits d'adhésion"), TYPC_MNT);
    $myForm->setFieldProperties("mnt_adh", FIELDP_DEFAULT,$mnt_droits_adhesion);
    $myForm->setFieldProperties("mnt_adh", FIELDP_JS_EVENT, array("onchange" => "updateMin();"));

    // Le client va-t-il utiliser son compte de base ?
    if ($global_type_structure == 1) { // MEC
      $choix = array(1 => _("Oui"));
    } else if ($global_type_structure == 2) { // Crédit direct
      $choix = array(0 => _("Non"));
    } else if ($global_type_structure == 3) { // Banque
      $choix = array(1 => "Oui", 0 => _("Non"));
    }

    $myForm->addField("ouvre_cpt_base", (_("Le client ouvre un compte de base en ").$global_monnaie), TYPC_LSB);
    $myForm->setFieldProperties("ouvre_cpt_base", FIELDP_ADD_CHOICES, $choix);
    $myForm->setFieldProperties("ouvre_cpt_base", FIELDP_HAS_CHOICE_AUCUN, false);
    if ($global_type_structure != 2) {
      $myForm->setFieldProperties("ouvre_cpt_base", FIELDP_DEFAULT, 1);
    } else {
      $myForm->setFieldProperties("ouvre_cpt_base", FIELDP_DEFAULT, 0);
    }
    $myForm->setFieldProperties("ouvre_cpt_base", FIELDP_JS_EVENT, array("onchange" => "updateMin();"));

    // Intitulé du compte
    $myForm->addTable("ad_cpt", OPER_INCLUDE, array("intitule_compte"));
    $myForm->setFieldProperties("intitule_compte", FIELDP_DEFAULT, getClientName($global_id_client));

    $myForm->addField("soldemin", _("Solde minimum compte de base"), TYPC_MNT);
    $myForm->setFieldProperties("soldemin", FIELDP_DEFAULT, $mnt_min);

    // Fonction JS pour la MAJ du champ montantmin
    $js  =  "function updateMin()\n";
    $js .=  "{\n";
    $js .=  "  if (document.ADForm.HTML_GEN_LSB_ouvre_cpt_base.value == 0)\n";
    $js .=  "  {\n";
    $js .=  "    document.ADForm.depot_min.value = formateMontant(recupMontant(document.ADForm.mnt_adh.value));\n";
    $js .=  "    document.ADForm.intitule_compte.disabled = true;\n";
    $js .=  "  }\n";
    $js .=  "  else\n";
    $js .=  "  {\n";
    $js .=  "    document.ADForm.depot_min.value = formateMontant((recupMontant(document.ADForm.mnt_adh.value) + recupMontant(document.ADForm.soldemin.value)));\n";
    $js .=  "    document.ADForm.intitule_compte.disabled = false;\n";
    $js .=  "  }\n";
    $js .=  "} updateMin();\n";

    $myForm->addJS(JSP_FORM, "updateMin", $js);

    $myForm->addField("depot_min", _("Montant minimum à verser"), TYPC_MNT);

    $myForm->addField("paye", _("Montant du versement"), TYPC_MNT);
    $myForm->addField("confpaye", _("Confirmation du montant"), TYPC_MNT);
    $myForm->setFieldProperties("paye", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("confpaye", FIELDP_HAS_BILLET, true);
    $myForm->setFieldProperties("mnt_adh", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("mnt_adh", FIELDP_CAN_MODIFY, true);
    $myForm->setFieldProperties("soldemin", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("depot_min", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("confpaye", FIELDP_JS_EVENT, array("onkeypress" => "return true;"));

    if ($global_billlet_req) {
      $myForm->setFieldProperties("confpaye", FIELDP_HAS_BILLET, true);
    }

    $myForm->addFormButton(1,1, "valider",_("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1,2, "annuler",_("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Pfh-2');
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $js  = "if (document.ADForm.HTML_GEN_LSB_ouvre_cpt_base.value == 0)\n";
    $js .= "{\n";
    $js .= "  if (recupMontant(document.ADForm.paye.value) != recupMontant(document.ADForm.depot_min.value))\n";
    $js .= "  {\n";
    $js .= "    msg += '- "._("La somme encaissée doit être égale au montant des frais d\'adhésion.")." \\n';\n";
    $js .= "    ADFormValid = false;\n";
    $js .= "  }\n";
    $js .= "}\n";
    $js .= "else if (document.ADForm.HTML_GEN_LSB_ouvre_cpt_base.value == 1)\n";
    $js .= "{\n";
    $js .= "  if (recupMontant(document.ADForm.paye.value) < recupMontant(document.ADForm.depot_min.value))\n";
    $js .= "  {\n;";
    $js .= "    msg += '- "._("La somme encaissée doit être supérieure ou égale au minimum à verser.")." \\n';\n";
    $js .= "    ADFormValid = false;\n";
    $js .= "  }\n";
    $js .= "}\n";

    $myForm->addJS(JSP_BEGIN_CHECK, "checkMoney", $js);

    $js  = "if (recupMontant(document.ADForm.paye.value) != recupMontant(document.ADForm.confpaye.value))\n";
    $js .= "{\n";
    $js .= "  msg +='- "._("Les montants entrés ne correspondent pas.")." \\n';\n";
    $js .= "  ADFormValid = false;\n";
    $js .= "}\n";

    $myForm->addJS(JSP_BEGIN_CHECK, "checkMoney2", $js);

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
}
/*}}}*/

/*{{{ Pfh-2 : Confirmation du versement */
else if ($prochain_ecran == 'Pfh-2') {
  global $global_id_client;
  global $dbHandler;


  $AG_DATA = getAgenceDatas($global_id_agence);
  $ACC = getAccountDatas(getBaseAccountID($global_id_client));
  $CLI = getClientDatas($global_id_client);
  $mnt_adh = recupMontant($mnt_adh);
  $montant = recupMontant($paye);
  $comptable_his = array ();

  $mnt_droits_adhesion = getMontantDroitsAdhesion($CLI["statut_juridique"]);
  $id_cpte_base = getBaseAccountID($global_id_client);
  $soldeFrais = getSoldeFraisAdhesion($global_id_client);
  $soldeFraisRestant = $soldeFrais->param[0]['solde_frais_adhesion_restant'];
  debug($soldeFraisRestant);
  debug($mnt_droits_adhesion);
  if ($soldeFraisRestant > 0 && $CLI['etat']!=1) {

    $myErr1 = perceptionTrancheFraisAdhesion($global_id_client, $comptable_his, $montant);

    if ($myErr1->errCode == NO_ERR) { // Si paiement est OK
      $soldeFraisAdh = getSoldeFraisAdhesion($global_id_client);
      $new_solde = $soldeFraisAdh->param[0]['solde_frais_adhesion_restant'] - $montant;
      if ($new_solde < 0) {
        $new_solde = 0;
      }
      $err_update = updateSodeRestantFraisAdhesion($global_id_client, $new_solde);
      if ($err_update->errCode != NO_ERR) {
        $html_err = new HTML_erreur(_("Echec de la mise à jour du solde."));
        $html_err->setMessage("Erreur : " . $error[$myErr->errCode] . "<BR>Paramètre : " . $myErr->param);
        $html_err->addButton("BUTTON_OK", 'Gen-3');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

      print_recu_tranche_adhesion ($global_id_client, $myErr1->param, $montant,$SESSION_VARS["montant_frais_adhesion"]);
      unset($SESSION_VARS["montant_frais_adhesion"]);
      //print_recu_adhesion($global_id_client, $montant, $myErr1->param);

      $global_etat_client = 2;  // Le client est passé à l'état 'actif'
      $myMsg = new HTML_message(_("Confirmation"));
      $msg .= sprintf(_("La tranche: %s des frais d'adhésion a bien été perçue"),afficheMontant($montant, true));
      $msg .= "<br/><br/>"._("N° de transaction")." : <b><code>".sprintf("%09d", $myErr1->param)."</code></b>";
      $myMsg->setMessage($msg);
      $myMsg->addButton(BUTTON_OK, 'Gen-9');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;

    } else {
      $html_err = new HTML_erreur(_("Echec lors de la perception des frais."));
      $html_err->setMessage(_("La perception des frais n'a pas été effectuée.")." ".$error[$myErr1->errCode].$myErr1->param);
      $html_err->addButton(BUTTON_OK, 'Gen-9');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  } else {
    $myErr = perceptionFraisAdhesionInt ($global_id_client, $global_id_guichet, $montant, $ouvre_cpt_base, $mnt_adh);

    if ($myErr->errCode == NO_ERR) { // Si tout est OK
      $new_solde = 0;
      $err_update = updateSodeRestantFraisAdhesion($global_id_client, $new_solde);
      if ($err_update->errCode != NO_ERR) {
        $html_err = new HTML_erreur(_("Echec de la mise à jour du solde."));
        $html_err->setMessage(_("Erreur")." : " . $error[$myErr->errCode] . "<br/>"._("Paramètre")." : " . $myErr->param);
        $html_err->addButton("BUTTON_OK", 'Gen-3');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

      print_recu_adhesion($global_id_client, $montant, $myErr->param, NULL, $mnt_adh);

      $global_etat_client = 2;  // Le client est passé à l'état 'actif'
      $myMsg = new HTML_message(_("Confirmation"));
      $msg .= _("Les frais d'adhésion ont bien été perçus");
      $msg .= "<br/><br/>"._("N° de transaction")." : <b><code>".sprintf("%09d", $myErr->param)."</code></b>";
      $myMsg->setMessage($msg);
      $myMsg->addButton(BUTTON_OK, 'Gen-9');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec lors de la perception des frais."));
      $html_err->setMessage(_("La perception des frais n'a pas été effectuée.")." ".$error[$myErr->errCode].$myErr->param);
      $html_err->addButton(BUTTON_OK, 'Gen-9');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>
