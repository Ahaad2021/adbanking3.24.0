<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [81] AUT Recharge Carte Ferlo par Compte epargne
 * [160] RFV Recharge Carte Ferlo par Versement Espece
 *
 * @Desc Ce module permet la gestion des transactions entre ADbanking et le sytème de Carte Ferlo
 * 			Il comprend les écrans:
 * 				- Aut-1 : Demande d'autorisation
 * 				- Aut-2 : Confirmation de la Demande
 * 				- Aut-3 : Génération du XML, Génération du Rapport d'autorisation, Attente du traitement Ferlo
 * 				- Aut-4 : Récupération du Fichier de Recharge, Passation ecriture comptable
 *
 * @version 2.10
 * @since Mai 2007
 * @author Aminata - Stefano
 * @package Epargne
 */

require_once 'lib/dbProcedures/epargne.php';
require_once 'modules/rapports/xml_epargne.php';
require_once 'modules/epargne/recu.php';

if ($global_nom_ecran == "Aut-1" || $global_nom_ecran == "Rfv-1") {
  if ($global_nom_ecran == 'Aut-1') {
    $SESSION_VARS["typeRecharge"] = "RFC"; // Rechage Ferlo par Compte
    $result = isConfigurerOperation(141);
  }
  if ($global_nom_ecran == 'Rfv-1') {
    $SESSION_VARS["typeRecharge"] = "RFV"; // Rechage Ferlo par Versement
    $result = isConfigurerOperation(143);
  }

  if ($result->errCode != NO_ERR) {
    // {{{ Vérification des paramétrages
    debug($result, "retour verif_parametrage");
    $html = new HTML_GEN2(_("Vérification du parametrage"));
    $html->addHTMLExtraCode("Erreur", "<table align=\"center\" valign=\"middle\" bgcolor=\"" . $colb_tableau . "\">
                            <tr><td align=\"center\"><font color=\"" . $colt_error . "\">
                            "._("Recharge carte Ferlo par Compte Epargne impossible.")."</font></td></tr>
                            <tr><td align=\"center\"><font color=\"" . $colt_error . "\">
                            "._("Vous devriez")." ". $error[$result->errCode] . "<b>" . $result->param . "</b></font></td></tr>
                            <tr><td>&nbsp;</td></tr></table>");
    debug($result->param, "result");
    // Le profil a-t-il accès au paramétrage nécessaire ?
    if (check_access(420)) {
      $html->addFormButton(1, 1, "parametrer", _("Paramétrer"), TYPB_SUBMIT);
      $html->setFormButtonProperties("parametrer", BUTP_PROCHAIN_ECRAN, 'Gop-1');
      $html->addHiddenType("id_oper", $result->param);
    }
    $html->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    if ($SESSION_VARS["typeRecharge"] == "RFC") {
      $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-8');
    }
    if ($SESSION_VARS["typeRecharge"] == "RFV") {
      $html->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-6');
    }
    $html->buildHTML();
    echo $html->getHTML();
    // }}}
  }
  elseif ($result->errCode == NO_ERR) {
    // {{{ ECRANS Aut-1  ou Rfv-1: Demande d'autorisation
    /**
    	* Formulaire de demande d'autorisation de recharge
    	*     - code de l'agence
    	*     - code de l'antenne
    	*     - code de la carte du client
    	*     - numéro complet du compte du client
    	*     - date de la demande
    	*     - montant demandé
    	*     - devise du montant demandé
    */

    global $global_id_agence;
    $agc = getAgenceDatas($global_id_agence);
    $SESSION_VARS["codeAntenne"] = $agc['code_antenne'];
    $SESSION_VARS["codeAgence"] = $global_id_agence;
    $SESSION_VARS["devise"] = getComptesComptables(array("num_cpte_comptable"=>getCompteCptaGui($global_id_guichet)));
		$SESSION_VARS["devise"] = $SESSION_VARS["devise"][getCompteCptaGui($global_id_guichet)]["devise"];

    if ($SESSION_VARS["typeRecharge"] == "RFC") {
      $id_cpte = getBaseAccountID($global_id_client);
      $InfoCpteBase = getAccountDatas($id_cpte);
      $InfoProduitBase = getProdEpargne($InfoCpteBase["id_prod"]);
      $SESSION_VARS["idProd"] = $InfoCpteBase["id_prod"];
      $SESSION_VARS["NumCpte"] = $id_cpte;
      $SESSION_VARS["compteRecharge"] = $InfoCpteBase["num_complet_cpte"];
      $SESSION_VARS["codeTitulaire"] = $global_id_client;
      $SESSION_VARS["devise"] = $InfoProduitBase["devise"];
    }

    $html = new HTML_GEN2();
    $html->setTitle(_("Demande Autorisation Recharge"));
    if ($SESSION_VARS["typeRecharge"] == "RFC") {
      $html->addField("compteRecharge", _("Numéro de compte"), TYPC_TXT);
      $html->setFieldProperties("compteRecharge", FIELDP_DEFAULT, $InfoCpteBase["num_complet_cpte"]); //Compte de base par défaut
      $html->setFieldProperties("compteRecharge", FIELDP_IS_LABEL, true);

            //Contrôle sur l'accès au solde
	    $access_solde = get_profil_acces_solde($global_id_profil, $InfoCpteBase["id_prod"]);
	    $access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $global_id_client);
	    if(manage_display_solde_access($access_solde, $access_solde_vip)){
	    	$html->addField("solde_reel", _("Solde réel"), TYPC_MNT);
	      $html->setFieldProperties("solde_reel", FIELDP_IS_LABEL, true);
	      $html->setFieldProperties("solde_reel", FIELDP_DEFAULT, $InfoCpteBase["solde"]);

	      $html->addField("solde", _("Solde disponible"), TYPC_MNT);
	      $html->setFieldProperties("solde", FIELDP_IS_LABEL, true);
	      $html->setFieldProperties("solde", FIELDP_DEFAULT, getSoldeDisponible($id_cpte));
	    }else{
	    	$html->addHiddenType("solde_reel", $InfoCpteBase["solde"]);
	    	$html->addHiddenType("solde", getSoldeDisponible($id_cpte));
	    }

    }

    $JS = "var max=19;\n";
    $JS .= "function compter() {\n";
    $JS .= "var txt=document.ADForm.code_carte.value;\n";
    $JS .= "var nb=txt.length;\n";
    $JS .= "if (nb==4 || nb==9 || nb==14) { \n";
    $JS .= "document.ADForm.code_carte.value=txt+\"-\";\n";
    $JS .= "}\n";
    $JS .= "if (nb>max) { \n";
    $JS .= "alert(\"".("Le numéro de carte ne peut pas avoir plus de 16 chiffres")."\");\n";
    $JS .= "document.ADForm.code_carte.value=txt.substring(0,max);\n";
    $JS .= "nb=max;\n";
    $JS .= "}\n";
    $JS .= "}\n";
    $JS .= "function timer() {\n";
    $JS .= "compter();\n";
    $JS .= "setTimeout(\"timer()\",100);\n";
    $JS .= "}";
    $html->addJS(JSP_FORM, "JS", $JS);

    $html->addField("code_carte", _("Numéro Carte Ferlo"), TYPC_TXT);
    $html->setFieldProperties("code_carte", FIELDP_IS_REQUIRED, true);
    $html->setFieldProperties("code_carte", FIELDP_JS_EVENT, array (
                                "onkeypress" => "compter()"
                              ));
    $html->addField("date_demande", _("Date de la demande"), TYPC_DTE);
    $html->setFieldProperties("date_demande", FIELDP_DEFAULT, date("d/m/Y", mktime(0, 0, 0, date("m"), date("d"), date("Y"))));
    $html->addField("mnt", _("Montant à retirer"), TYPC_MNT);
    $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);

    if ($SESSION_VARS["typeRecharge"] == "RFC") {
      $ChkJSexpress = "\n\tif (recupMontant(document.ADForm.mnt.value) > recupMontant(document.ADForm.solde.value))";
      $ChkJSexpress .= "{msg += '- "._("Le montant du retrait est supérieur au solde disponible")."\\n'; ADFormValid=false;};\n";
    }

    $html->addJS(JSP_BEGIN_CHECK, "myJS", $ChkJSexpress);

    $ordre = array ();
    if ($SESSION_VARS["typeRecharge"] == "RFC") {
    	if(manage_display_solde_access($access_solde, $access_solde_vip))
      	$ordre = array_merge($ordre, array (
                             "compteRecharge",
                             "code_carte",
                             "date_demande",
                             "solde_reel",
                             "solde",
                             "mnt"
                           ));
      else
      	$ordre = array_merge($ordre, array (
                             "compteRecharge",
                             "code_carte",
                             "date_demande",
                             "mnt"
                           ));
    }
    if ($SESSION_VARS["typeRecharge"] == "RFV") {
      $ordre = array_merge($ordre, array (
                             "code_carte",
                             "date_demande",
                             "mnt"
                           ));
    }
    $html->setOrder(NULL, $ordre);

    //Boutons
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    if ($SESSION_VARS["typeRecharge"] == "RFC") {
      $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Aut-2');
      $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-8');
    }
    if ($SESSION_VARS["typeRecharge"] == "RFV") {
      $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rfv-2');
      $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-6');
    }

    $html->buildHTML();
    echo $html->getHTML();
    // }}}
  }
}
elseif ($global_nom_ecran == "Aut-2" || $global_nom_ecran == "Rfv-2") {
  //  {{{ Ecran Aut-2 ou Rfv2: Confirmation Demande Autorisation

  $SESSION_VARS["mnt"] = recupMontant($mnt);
  if ($SESSION_VARS["typeRecharge"] == "RFC") {
    // récupérer les infos sur le produit associé au compte sélectionné
    $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  }
  $SESSION_VARS["numCarte"] = $code_carte;
  $SESSION_VARS["dateDmde"] = $date_demande;

  $SESSION_VARS['numSeqAuto'] = 1 + getNumSeqAuto($SESSION_VARS["codeAgence"]);

  $html = new HTML_GEN2(_("Confirmation Demande Autorisation"));

  $html->addField("numSeqAuto", _("Numéro de séquence d'enregistrement"), TYPC_TXT);
  $html->setFieldProperties("numSeqAuto", FIELDP_DEFAULT, $SESSION_VARS["numSeqAuto"]);
  $html->setFieldProperties("numSeqAuto", FIELDP_IS_LABEL, true);

  if ($SESSION_VARS["typeRecharge"] == "RFC") {
    $html->addField("codeTitulaire", _("Code du titulaire"), TYPC_TXT);
    $html->setFieldProperties("codeTitulaire", FIELDP_DEFAULT, $SESSION_VARS["codeTitulaire"]);
    $html->setFieldProperties("codeTitulaire", FIELDP_IS_LABEL, true);
    $html->addField("compteRecharge", _("Numéro complet du compte du client"), TYPC_TXT);
    $html->setFieldProperties("compteRecharge", FIELDP_DEFAULT, $SESSION_VARS["compteRecharge"]);
    $html->setFieldProperties("compteRecharge", FIELDP_IS_LABEL, true);
  }

  $JS = "var max=19;\n";
  $JS .= "function compter() {\n";
  $JS .= "var txt=document.ADForm.numCarte_reel.value;\n";
  $JS .= "var nb=txt.length;\n";
  $JS .= "if (nb==4 || nb==9 || nb==14) { \n";
  $JS .= "document.ADForm.numCarte_reel.value=txt+\"-\";\n";
  $JS .= "}\n";
  $JS .= "if (nb>max) { \n";
  $JS .= "alert(\""._("Le numéro de carte ne peut pas avoir plus de 16 chiffres")."\");\n";
  $JS .= "document.ADForm.numCarte_reel.value=txt.substring(0,max);\n";
  $JS .= "nb=max;\n";
  $JS .= "}\n";
  $JS .= "}\n";
  $JS .= "function timer() {\n";
  $JS .= "compter();\n";
  $JS .= "setTimeout(\"timer()\",100);\n";
  $JS .= "}";
  $html->addJS(JSP_FORM, "js_confirm", $JS);

  $html->addField("numCarte", _("Numéro Carte Ferlo"), TYPC_TXT);
  $html->setFieldProperties("numCarte", FIELDP_DEFAULT, $SESSION_VARS["numCarte"]);
  $html->setFieldProperties("numCarte", FIELDP_IS_LABEL, true);
  $html->addField("numCarte_reel", _("Confirmation du numero de la Carte ferlo"), TYPC_TXT);
  $html->setFieldProperties("numCarte_reel", FIELDP_IS_REQUIRED, true);
  $html->setFieldProperties("numCarte_reel", FIELDP_JS_EVENT, array (
                              "onkeypress" => "compter()"
                            ));

  $html->addField("dateDmde", _("Date de la demande"), TYPC_TXT);
  $html->setFieldProperties("dateDmde", FIELDP_DEFAULT, $SESSION_VARS["dateDmde"]);
  $html->setFieldProperties("dateDmde", FIELDP_IS_LABEL, true);

  $html->addField("mnt", _("Montant à retirer"), TYPC_MNT);
  $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS["mnt"]);
  $html->setFieldProperties("mnt", FIELDP_IS_LABEL, true);
  $html->addField("mnt_reel", _("Confirmation du montant"), TYPC_MNT);
  $html->setFieldProperties("mnt_reel", FIELDP_IS_REQUIRED, true);

  global $global_billet_req;
  if ($global_billet_req) {
    $html->setFieldProperties("mnt_reel", FIELDP_HAS_BILLET, true);
    $html->setFieldProperties("mnt_reel", FIELDP_SENS_BIL, SENS_BIL_OUT);
  }

  $ChkJS1 = "\t\tif (document.ADForm.numCarte_reel.value != document.ADForm.numCarte.value)";
  $ChkJS1 .= "{\nmsg += '- "._("Incohérence sur les numéros de Carte fournies")."\\n'; ADFormValid=false;};\n";
  $html->addJS(JSP_BEGIN_CHECK, "JS1", $ChkJS1);

  $ChkJS2 = "\t\tif (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.mnt.value))";
  $ChkJS2 .= "{\nmsg += '- "._("Le montant saisi ne correspond pas au montant à retirer")."\\n'; ADFormValid=false;};\n";
  $html->addJS(JSP_BEGIN_CHECK, "JS", $ChkJS2);

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
  $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  if ($SESSION_VARS["typeRecharge"] == "RFC") {
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Aut-3');
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Aut-1');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-8');
  }
  if ($SESSION_VARS["typeRecharge"] == "RFV") {
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rfv-3');
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Rfv-1');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-6');
  }

  $html->buildHTML();
  echo $html->getHTML();
  // }}}
}
elseif ($global_nom_ecran == "Aut-3" || $global_nom_ecran == "Rfv-3") {
  // {{{ Aut-3 ou Rfv-3 : Génération du XML, Génération du Rapport d'autorisation, Attente du traitement Ferlo

  //Préparation des données pour le XML et les RAPPORT

  $DATA = array ();

  $DATA["NumCpte"] = $SESSION_VARS["NumCpte"];
  $DATA["compteRecharge"] = $SESSION_VARS["compteRecharge"];
  $DATA["codeAntenne"] = $SESSION_VARS["codeAntenne"];
  $DATA["codeAgence"] = $SESSION_VARS["codeAgence"];
  $DATA["codeTitulaire"] = $SESSION_VARS["codeTitulaire"];
  $DATA["devise"] = $SESSION_VARS["devise"];
  $DATA["montant"] = $SESSION_VARS["mnt"];
  $SESSION_VARS["numCarte"] = str_replace("-", "", $SESSION_VARS["numCarte"]);
  $DATA["numCarte"] = $SESSION_VARS["numCarte"];
  $DATA["dateDmde"] = $SESSION_VARS["dateDmde"];
  $DATA['numSeqAuto'] = $SESSION_VARS['numSeqAuto'];

  if ($SESSION_VARS["typeRecharge"] == "RFC") {
    $erreur = checkAutorisation($SESSION_VARS["NumCpte"], $SESSION_VARS["idProd"]);
    if ($erreur->errCode != NO_ERR) {
      $html_err = new HTML_erreur(_("Echec demande d'autorisation de recharge. "));
      $html_err->setMessage(_("Erreur")." : " . $error[$erreur->errCode] . " " . $erreur->param);
      $html_err->addButton("BUTTON_OK", 'Gen-8');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }

  //incrémentataion des numéro dans ad_cli et ad_gc
  $db = $dbHandler->openConnection();
  $sql = "UPDATE ad_agc SET num_seq_auto = " . $SESSION_VARS['numSeqAuto'] . " WHERE id_ag = " . $SESSION_VARS["codeAgence"];
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  // Génération du XML et du PDF
  $nomFichier = autorisationTxt($DATA);
  $SESSION_VARS["nomFichier"] = $nomFichier;

  $html_msg = new HTML_message(_("Confirmation de l'envoi de la Demande d'autorisation - Attente de la recharge"));
  $msg = sprintf(_("La demande d'autorisation N° %s a été enrégistré"),$DATA['numSeqAuto']) . "<br /><br />";
  $msg .= _("veuillez patienter pendant que Ferlo génére le fichier de recharge");
  $msg .= "<br /><br />"._("Au signal du terminal Ferlo").", <br />".sprintf(_("cliquer sur %s OK %s pour terminer la transaction liée à la Recharge"),"<font color=\"red\">","</font>");
  $html_msg->setMessage($msg);
  if ($SESSION_VARS["typeRecharge"] == "RFC") {
    $html_msg->addCustomButton("annuler", _("Annuler"), 'Aut-1');
    $html_msg->addButton("BUTTON_OK", 'Aut-4');
  }
  if ($SESSION_VARS["typeRecharge"] == "RFV") {
    $html_msg->addCustomButton("annuler", _("Annuler"), 'Rfv-1');
    $html_msg->addButton("BUTTON_OK", 'Rfv-4');
  }
  $html_msg->buildHTML();
  echo $html_msg->HTML_code;
  // }}}
}
elseif ($global_nom_ecran == "Aut-4") {
  // {{{ ECRAN Aut-4 : Récupération du Fichier de Recharge, Passation ecriture comptable
  global $REMOTE_ADDR;
  global $lib_path;
  $fichierRecharge = $lib_path . "/ferlo/recharge/REC_" . $SESSION_VARS["nomFichier"] . ".xml";

  global $global_id_guichet;
  $SESSION_VARS['id_mandat'] = $mandat;

  if (is_file($fichierRecharge)) {
    $XMLarray = traiteFichierXML($fichierRecharge);
    $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
    $InfoProduit = getProdEpargne($SESSION_VARS["idProd"]);

    $erreur = retrait_cpte($global_id_guichet, $SESSION_VARS['NumCpte'], $InfoProduit, $InfoCpte, $XMLarray["XMLFILE"]["recharge"]["montant"], 6, $SESSION_VARS['id_mandat']);

    if ($erreur->errCode == NO_ERR) {
      $infos = get_compte_epargne_info($SESSION_VARS['NumCpte']);
      print_recu_retrait($XMLarray["XMLFILE"]["recharge"]["codeTitulaire"], $global_client, $InfoProduit, $infos, $XMLarray["XMLFILE"]["recharge"]["montant"], $erreur->param['id'], 'REC-FER', null, _("Recharge par compte épargne client"), '', $SESSION_VARS["numCarte"]);

      unlink($lib_path . "/ferlo/recharge/REC_" . $SESSION_VARS["nomFichier"] . ".xml");
      unlink($lib_path . "/ferlo/autorisation/AUT_" . $SESSION_VARS["nomFichier"] . ".txt");

      $html_msg = new HTML_message(_("Confirmation de recharge carte Ferlo"));
      $msg = _("Le compte a été débité de") . " : " . afficheMontant($SESSION_VARS["mnt"]) . " $global_monnaie<br />" . _("Recu imprimé") . ".";
      $msg .= "<br /><br />"._("N° de transaction")." : <B><code>" . sprintf("%09d", $erreur->param['id']) . "</code></B>";
      $html_msg->setMessage($msg);
      $html_msg->addButton("BUTTON_OK", 'Gen-8');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec du recharge de la carte. "));
      $html_err->setMessage(_("Erreur")." : " . $error[$erreur->errCode] . "<br />"._("Paramètre")." : " . $erreur->param);
      $html_err->addButton("BUTTON_OK", 'Aut-1'); //Si express
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  } else {
    $html_msg = new HTML_message(_("Attente de la génération du fichier de Recharge"));
    $msg = sprintf(_("La demande d'autorisation N° %s a été enrégistré"), $SESSION_VARS['numSeqAuto']) . ".<br /><br />";
    $msg .= _("veuillez patienter pendant que Ferlo génére le fichier de recharge");
    $msg .= "<br /><br />"._("Au signal du terminal Ferlo,")." <br />".sprintf(_("cliquer sur %s OK %s pour terminer la transaction liée à la Recharge"),"<font color=\"red\">","</font>");
    $html_msg->setMessage($msg);
    $html_msg->addCustomButton("annuler", _("Annuler"), 'Aut-1');
    $html_msg->addButton("BUTTON_OK", 'Aut-4');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
  // }}}
}
elseif ($global_nom_ecran == "Rfv-4") {
  // {{{ ECRAN Rfv-4 : Récupération du Fichier de Recharge, Passation ecriture comptable
  global $REMOTE_ADDR;
  global $lib_path;
  $fichierRecharge = $lib_path . "/ferlo/recharge/REC_" . $SESSION_VARS["nomFichier"] . ".xml";

  global $global_id_guichet;

  if (is_file($fichierRecharge)) {
    $XMLarray = traiteFichierXML($fichierRecharge);

    $erreur = recharge_versement($global_id_guichet, $XMLarray["XMLFILE"]["recharge"]["montant"], 7);

    if ($erreur->errCode == NO_ERR) {
      print_recu_retrait(null, null, null, null, $XMLarray["XMLFILE"]["recharge"]["montant"], $erreur->param['id'], 'REC-FER', null, _("Recharge par versement Espèces"), '', $SESSION_VARS["numCarte"]);
      unlink($lib_path . "/ferlo/recharge/REC_" . $SESSION_VARS["nomFichier"] . ".xml");
      unlink($lib_path . "/ferlo/autorisation/AUT_" . $SESSION_VARS["nomFichier"] . ".txt");

      $html_msg = new HTML_message(_("Confirmation de recharge carte Ferlo"));
      $msg = _("Le compte a été débité de") . " : " . afficheMontant($SESSION_VARS["mnt"]) . " $global_monnaie<br />" . _("Recu imprimé") . ".";
      $msg .= "<br /><br />"._("N° de transaction")." : <B><code>" . sprintf("%09d", $erreur->param['id']) . "</code></B>";
      $html_msg->setMessage($msg);
      $html_msg->addButton("BUTTON_OK", 'Gen-6');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec du recharge de la carte.")." ");
      $html_err->setMessage(_("Erreur")." : " . $error[$erreur->errCode] . "<br />"._("Paramètre")." : " . $erreur->param);
      $html_err->addButton("BUTTON_OK", 'Rfv-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  } else {
    $html_msg = new HTML_message(_("Attente de la génération du fichier de Recharge"));
    $msg = sprintf(_("La demande d'autorisation N° %s a été enrégistré"),$SESSION_VARS['numSeqAuto']) . ".<br /><br />";
    $msg .= _("veuillez patienter pendant que Ferlo génére le fichier de recharge");
    $msg .= "<br /><br />"._("Au signal du terminal Ferlo,")." <br />".sprintf(_("cliquer sur %s OK %s pour terminer la transaction liée à la Recharge"),"<font color=\"red\">","</font>");
    $html_msg->setMessage($msg);
    $html_msg->addCustomButton("annuler", _("Annuler"), 'Rfv-1');
    $html_msg->addButton("BUTTON_OK", 'Rfv-4');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
  // }}}
}
else {
  signalErreur(__FILE__, __LINE__, __FUNCTION__); // "L'écran '$global_nom_ecran' n'a pas été trouvé"
}
?>