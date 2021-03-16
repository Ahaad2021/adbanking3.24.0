<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [475] Radiation d'un dossier de crédit
 * Cette opération comprends les écrans :
 * - Rad-1 : sélection de la date de passage en perte des crédits
 * - Rad-2 : affichage des dossiers de crédit
 * - Rad-3 : confirmation de la suppression
 * @package Compta
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';

/*{{{ Rad-1 : Sélection du ou des dossier (s) de crédit */
if ($global_nom_ecran == "Rad-1") {
  unset($SESSION_VARS["exercice"]);
  $myForm = new HTML_GEN2(_("Choix de la date"));
  //Choix de la date de passage en perte du crédit
  //$myForm->addField("date", _("Date"), TYPC_DTE);
  $myForm->addField("date_debut", _("Date début déclassement"), TYPC_DTE);
  $myForm->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
  $myForm->addField("date_fin", _("Date fin déclassement"), TYPC_DTE);
  $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));
  //Champs 'Critère de répartition'
  $myForm->addField("critere", _("Etat crédit"), TYPC_LSB);
  $choix = array (    "cred_radier" => _("Crédits à radier"),
                       "autre_credit" => _("Autres crédits")
                     );
  $myForm->setFieldProperties("critere", FIELDP_ADD_CHOICES, $choix);
  $myForm->setFieldProperties("critere", FIELDP_IS_REQUIRED, true);
  $js_fct = "function set_disabled(num_client){";
  $js_fct .= "  document.ADForm.num_client.disabled = num_client; ";
  $js_fct .= "}";
  $js .= "if (document.ADForm.HTML_GEN_LSB_critere.value == 'autre_credit') {set_disabled(false); document.ADForm.num_client.required = true;}
  			 else {document.ADForm.num_client.value=''; set_disabled(true);}";

  $js_chercheClient = " if (document.ADForm.HTML_GEN_LSB_critere.value == 'autre_credit')
              {
                OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client', '"._("Recherche")."');return false;
              } else {document.ADForm.num_client.value=''};";

  $myForm->setFieldProperties("critere", FIELDP_JS_EVENT, array (
                                          "onchange" => $js));
  $myForm->addJS(JSP_FORM, "js1", $js_fct);
  $myForm->addField("num_client", _("N° de client"), TYPC_INT);
  $myForm->setFieldProperties("num_client", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("num_client", FIELDP_IS_LABEL, true);
  $myForm->addLink("num_client", "rechercher", _("Rechercher"), "#");
  $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => $js_chercheClient));

  //Boutons
  $myForm->addFormButton(1,1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rad-2");
  $myForm->addFormButton(1,2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}

/*{{{ Rad-2 : Sélection du ou des dossier (s) de crédit */
if ($global_nom_ecran == "Rad-2") {
  $dateValide = true;

  // Contrôle de la date si elle est saisie
  if (isset($date_debut) && isset($date_fin)) {
    $date_debut = php2pg($date_debut);
    $date_fin = php2pg($date_fin);
    if($date_debut > $date_fin)
    	$dateValide = false;
  }
   // Vérification de l'existence du numéro de compte
    if (($num_client == '')&&($critere=='autre_credit')) {
      $myForm = new HTML_erreur(_("Erreur de client"));
      $myForm->setMessage(_("Erreur")." : "._("vous devez choisir un client pour cette option"));
      $myForm->addButton(BUTTON_OK, "Rad-1");
      $myForm->buildHTML();
      echo $myForm->HTML_code;
      die();
    }
  if ($dateValide) {
    $ok = true;
    $etat_credits = getCreditARadier($num_client, $date_debut, $date_fin);
    $SESSION_VARS["dossiers_perte"] = $etat_credits;
    if ($etat_credits == NULL) {
      $erreur = new HTML_erreur(_("Dossiers inexistants"));
      $erreur->setMessage(_("Il n'y a aucun dossier à passer en perte"));
      $erreur->addButton("BUTTON_OK","Rad-1");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = false;
    } else {
      $myForm = new HTML_GEN2(_("Passage en perte de crédits"));
      $myTable =& $myForm->addHTMLTable("dossiers_perte", 5, TABLE_STYLE_ALTERN);
      $myTable->add_cell(new TABLE_cell(_("Id dossier"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Id client"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Montant en perte"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Nb jours de retard"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Cocher"), 1, 1));

      while (list(,$credit) = each($etat_credits)) {
        $id_doss = $credit['id_doss'];
        $myTable->add_cell(new TABLE_cell($credit['id_doss'], 1, 1));
        $myTable->add_cell(new TABLE_cell($credit['id_client'], 1, 1));
        $myTable->add_cell(new TABLE_cell($credit['solde'], 1, 1));
        $myTable->add_cell(new TABLE_cell($credit['nbre_jours'], 1, 1));
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'check_$id_doss'  id =$id_doss />", 1, 1));

      }
      //Boutons
      $myForm->addFormButton(1,1, "valider", _("Valider"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rad-3");
      $myForm->addFormButton(1,2, "precedent", _("Précédent"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, "Rad-1");
      $myForm->addFormButton(1,3, "annuler", _("Annuler"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
      $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
      $myForm->addFormButton(1,4, "coche_tout", _("Cocher tout"), TYPB_BUTTON);
      $MyjsCk = 'var checker = "false";
				function check(formulaire_rad_cre) {
					if (checker == "false") {
  						for (i = 0; i < formulaire_rad_cre.length; i++) {
  							formulaire_rad_cre[i].checked = true;}
  							checker = "true";
  						 }
					else {
  						for (i = 0; i < formulaire_rad_cre.length; i++) {
  							formulaire_rad_cre[i].checked = false;}
  							checker = "false";
  						 }
				}';
      $myForm->addJS(JSP_FORM, "js_check", $MyjsCk);
      $myForm->setFormButtonProperties("coche_tout", BUTP_JS_EVENT, array("onclick" => "check(document.ADForm);"));
      $myForm->buildHTML();
      echo $myForm->getHTML();
    }
  }else{
  	$myForm = new HTML_erreur(_("Erreur de date"));
    $myForm->setMessage(_("Erreur : Date invalide: date de début doit être antérieure à la date de fin"));
    $myForm->addButton(BUTTON_OK, "Rad-1");
    $myForm->buildHTML();
    echo $myForm->HTML_code;
    die();
  }
}

/*{{{ Rad-3 : Passage en perte des crédits sélectionnés */
if ($global_nom_ecran == "Rad-3") {
  global $global_mouvements;
  $myForm = new HTML_GEN2(_("Confirmation"));

	$SESSION_VARS["date_jour"] = date("d/m/Y");
	foreach($SESSION_VARS["dossiers_perte"] as $id => $infos_doss) {
		if ($ {'check_'.$infos_doss['id_doss']} == 'on')	{
			$myErr = radierCredit($infos_doss['id_doss'], $infos_doss['id_client'], $SESSION_VARS["date_jour"]);
			if ($myErr->errCode != NO_ERR){
				$html_err = new HTML_erreur(_("Echec de radiation de crédit."));
				$html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]."<br/>"._("Paramètre")." : ".$myErr->param);
				$html_err->addButton("BUTTON_OK", 'Gen-14');
				$html_err->buildHTML();
				echo $html_err->HTML_code;
				die();
			}
		}
	}
    $msg = new HTML_message(_("Confirmation radiation de crédit"));
    $msg->setMessage(_("La radiation de crédits a été effectuée avec succés !"));
    $msg->addButton(BUTTON_OK,"Gen-14");
    $msg->buildHTML();
    echo $msg->HTML_code;
}

?>