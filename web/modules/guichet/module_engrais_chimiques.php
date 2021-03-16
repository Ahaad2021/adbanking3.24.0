<?php

require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/parametrage.php');
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/engrais_chimiques.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';
require_once "lib/html/HTML_menu_gen.php";
require_once 'modules/guichet/recu_modules_specifiques.php';
require_once 'lib/dbProcedures/historique.php' ;
require_once 'modules/rapports/xml_engrais_chimiques.php' ;
//require_once '/usr/share/adbanking/web/ad_acu/agence_Remote.php';

global $global_id_benef;

function resetVariablesGlobalesClient() { /*{{{ */
  global $global_client;
  global $global_id_client;
  global $global_client_debiteur;
  global $global_id_client_formate;
  global $global_alerte_DAT;
  global $global_credit_niveau_retard;
  global $global_suspension_pen;
  global $global_cli_epar_obli;
  global $global_photo_client;
  global $global_signature_client;

  $global_client = "";
  $global_id_client = "";
  $global_client_debiteur = "";
  $global_id_client_formate = "";
  $global_alerte_DAT = "";
  $global_suspension_pen = false;
  $global_cli_epar_obli = "";
  $global_credit_niveau_retard = array();
  $global_signature_client = NULL;
  $global_photo_client = NULL;
} /*}}} */



if ($global_nom_ecran == "Pns-3") {
  resetVariablesGlobalesClient();
  if(isset($SESSION_VARS['id_beneficiaire'])) {
    unset($SESSION_VARS['id_beneficiaire']); // = null;
    unset($SESSION_VARS);
  }

  $jsIsEmptyBenef = "if (document.ADForm.id_beneficiaire.value == ''){\n";
  $jsIsEmptyBenef .= "alert('Le Numéro du Beneficiaire doit etre renseigné!'); \n";
  $jsIsEmptyBenef .= "return false;";
  $jsIsEmptyBenef .= "}";


  $myForm = new HTML_GEN2(_("Recherche du bénéficiaire"));
  $myForm->addField("id_beneficiaire", _("N° de bénéficiaire "), TYPC_INT);
  $myForm->addLink("id_beneficiaire", "rechercher", _("Rechercher"), "#");
  $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/clients/rech_beneficiaire.php?m_agc=".$_REQUEST['m_agc']."&field_name=id_beneficiaire', '"._("Recherche")."');return false;"));
  //$myForm->setFieldProperties("id_beneficiaire", FIELDP_JS_EVENT, array("onkeypress" => "return true;"));
  $myForm->addButton("id_beneficiaire", "ok", _("OK"), TYPB_SUBMIT);
  $myForm->setButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Pns-2');
  $myForm->setButtonProperties("ok", BUTP_JS_EVENT, array("onclick" => $jsIsEmptyBenef));
  $myForm->setButtonProperties("ok", BUTP_AXS, 171);

  //$myForm->setFieldProperties("id_beneficiaire", FIELDP_IS_REQUIRED, true);

  // Petite astuces pour permettre à l'utilisateur d'utiliser la touche 'Entrée' depuis le champs
  $ControlNumClient =" function control(){\n";
  $ControlNumClient .=" var chaine;\n";
  $ControlNumClient .=" chaine=document.ADForm.id_beneficiaire.value;\n";
  $ControlNumClient .=" var tab=chaine.split('-');\n";
  $ControlNumClient .=" if( tab.length >1 )\n";
  $ControlNumClient .=" document.ADForm.id_beneficiaire.value=tab[1];\n";
  $ControlNumClient .=" else \n";
  $ControlNumClient .=" document.ADForm.id_beneficiaire.value=tab[0];\n";
  $ControlNumClient .=" } \n";
  //$JS = "hasChecked = false;";
  //$myForm->addJS(JSP_FORM, "JS", $JS);
  //$myForm->addJS(JSP_FORM, "JS_NUM", $ControlNumClient);
  //$myForm->addJS(JSP_BEGIN_CHECK, "changeChecked", "hasChecked = true;");
  //$myForm->setButtonProperties("ok", BUTP_JS_EVENT, array("onClick" => "control();"));
  //$myForm->setFormProperties(FORMP_JS_EVENT, array("onsubmit" => "if (hasChecked == false) {assign('Gen-8');checkForm();} hasChecked = false;"));

  //$myForm->addFormButton(1,1, "butaj", _("Ajouter"), TYPB_SUBMIT);
  //$myForm->setFormButtonProperties("butaj", BUTP_PROCHAIN_ECRAN, "Pnb-1");
  $myForm->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-1");
  //$myForm->setFormButtonProperties("butaj", BUTP_AXS, 177);
  //$myForm->setFormButtonProperties("butaj", BUTP_CHECK_FORM, false);




  $myForm->buildHTML();
  /*echo $myForm->HTMLFormHead;
  echo $myForm->HTMLFormBody;
  echo $myForm->HTMLFormButtons;
  echo $myForm->HTMLFormFooter;
  echo "<br><br>";*/
  echo $myForm->getHTML();
}

else if ($global_nom_ecran == "Pns-2") {
  global $global_id_benef, $global_client, $global_id_client;
  $ok = true;
  unset($SESSION_VARS['id_comm']);
  unset($SESSION_VARS['id_commande']);
  unset($SESSION_VARS['id_remb']);

  $MyMenu = new HTML_menu_gen(_("Menu Engrais Chimiques"));

  if (!isset($SESSION_VARS['id_beneficiaire'])){
    $whereExist = array();
    $whereExist['id_beneficiaire'] = $id_beneficiaire;
    $existBenef = getMatchedBeneficiaire($whereExist);
    if ($existBenef == null){
      $erreur = new HTML_erreur(_("Beneficiaire inexistant"));
      $erreur->setMessage(_("Le numéro du beneficiaire entré ne correspond à aucun beneficiaire valide"));
      $erreur->addButton(BUTTON_OK,"Pns-1");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = false;
    }
    else{
      $ok = true;
      $SESSION_VARS['id_beneficiaire']=$id_beneficiaire;
    }
  }

  $global_id_benef = $SESSION_VARS['id_beneficiaire'];
  // Affichage  du nom du bénéficiaire sur la page principale
  if (isset($id_beneficiaire) && $id_beneficiaire != null && $ok == true){
    $global_id_client = $id_beneficiaire;
    $global_id_benef = $id_beneficiaire;
  }
  else {
    $global_id_client = $SESSION_VARS['id_beneficiaire'];
    $global_id_benef = $SESSION_VARS['id_beneficiaire'];
  }
  $where["id_beneficiaire"] = $global_id_client;
  $benef_details = getMatchedBeneficiaire($where, "*");
  if ($ok == true){
    $global_client = "$global_id_client - ".$benef_details[0]["nom_prenom"];
  }

  if ($ok == true && isset($SESSION_VARS['id_beneficiaire'])){

    $id_annee = getAnneeAgricoleActif();
    if ($id_annee == null){
      $html_err = new HTML_erreur();

      $err_msg = "Veuillez vérifier que l'année agricole est ouverte";

      $html_err->setMessage(sprintf("Attention : %s !", $err_msg));

      $html_err->buildHTML();
      echo $html_err->HTML_code;

    }

    $MyMenu->addItem(_("Modification bénéficiaire"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pnm-1", 178, "$http_prefix/images/modif_client.gif","1");
    $MyMenu->addItem(_("Ajout commande"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pnc-1", 172, "$http_prefix/images/retraitcheq.gif","2");
    $MyMenu->addItem(_("Paiement montant minimum commande en attente"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pvc-1", 183, "$http_prefix/images/retrait.gif","7");
    $MyMenu->addItem(_("Details commandes"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pnd-1", 0, "$http_prefix/images/extraits_compte.gif","3");
    $MyMenu->addItem(_("Annulation commandes"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pnn-1", 176, "$http_prefix/images/annul_dossier.gif","3");
    $MyMenu->addItem(_("Paiement commande"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pca-1", 169, "$http_prefix/images/autorisation_decouvert.gif","4");
    $MyMenu->addItem(_("Paiement commande en attente"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pnp-1", 173, "$http_prefix/images/remboursement.gif","4");
    $MyMenu->addItem(_("Approbation dérogation"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pna-1", 174, "$http_prefix/images/approb_dossier.gif","5");// check access 174
    $MyMenu->addItem(_("Effectuer dérogation"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pne-1", 175, "$http_prefix/images/retrait.gif","6");
    $MyMenu->addItem(_("Distribution des bons d'achats"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pdd-1", 801, "$http_prefix/images/remboursement.gif","7");
    $MyMenu->addItem(_("Retour"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pns-3", 0, "$http_prefix/images/back.gif","0");
    $MyMenu->buildHTML();
    echo $MyMenu->HTMLCode;
  }

}

else if ($global_nom_ecran == "Pns-1"){
  unset($SESSION_VARS['page_reload']);
  unset($SESSION_VARS['data_produit']);
  unset($SESSION_VARS['choix']);
  unset($SESSION_VARS['date_saison']);
  unset($SESSION_VARS['paiement_commande']);
  unset($SESSION_VARS['date_saison_selected']);
  unset($SESSION_VARS['criteres']);
  unset($SESSION_VARS['contenu']);
  unset($SESSION_VARS['id_annee_selected']);
  unset($SESSION_VARS['choix_period']);

  $MyMenu = new HTML_menu_gen(_("Gestions des Operations & Rapports"));
  $MyMenu->addItem(_("Gestions des Operations"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pns-3", 171, "$http_prefix/images/menu_compta.gif","1");
  $MyMenu->addItem(_("Liste des Rapports"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pnr-1", 179, "$http_prefix/images/rapport.gif","2");
  $MyMenu->addItem(_("Visualisation des transactions PNSEB_FENACOBU"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pnt-1", 182, "$http_prefix/images/visualisation_toutes_trans.gif","3");
  $MyMenu->addItem(_("Enregistrement des livraisons des bons d'achats"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pba-1",184, "$http_prefix/images/sauve_data.gif","4");
  $MyMenu->addItem(_("Consultation des stocks"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pst-1", 185, "$http_prefix/images/consultation_client.gif","5");
  $MyMenu->addItem(_("Consultation des bons d'achats agent"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Psa-1", 802, "$http_prefix/images/consultation_client.gif","6");
  $MyMenu->addItem(_("Consultation des bons d'achats de tous les agents"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Paa-1", 803, "$http_prefix/images/consultation_client.gif","7");
  $MyMenu->addItem(_("Gestion des stocks bons d'achats"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Psb-1", 199, "$http_prefix/images/gestion_plan_comptable.gif","8");
  $MyMenu->addItem(_("Retour Menu Guichet"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-6", 0, "$http_prefix/images/back.gif","0");
  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;

}


else if ($global_nom_ecran == "Pnb-1"){

  $label_table= "des bénéficiaires";
  $table_ajout = "ec_beneficiaire";

  $MyPage = new HTML_GEN2(_("Ajout d'une entrée dans la table")." '".$label_table."'");

  //Nom table
  $color = $colb_tableau;
  $html = "<link rel=\"stylesheet\" href=\"/lib/misc/js/chosen/css/chosen.css\">";
  $html .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $html .= "<script src=\"/lib/misc/js/chosen/chosen.jquery.js\" type=\"text/javascript\"></script>";

  //$html .= "<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=0 cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("Nom et Prenom")."</b></TD>";
  $html .= "<TD>\n";
  if (isset($nom_prenom) && $nom_prenom != null){
    $html .= "<input type=\"text\" NAME=\"nom_prenom\" style=\"width:250px\" VALUE=\"$nom_prenom\" required ";
  }
  else{
    $html .= "<input type=\"text\" NAME=\"nom_prenom\" style=\"width:250px\" required ";
  }
  $html .= "onchange=\"\" >";
  $html .= "</input>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("NIC")."</b></TD>";
  $html .= "<TD>\n";
  if (isset($nic) && $nic != null){
    $html .= "<input type=\"text\" NAME=\"nic\" style=\"width:250px\" VALUE=\"$nic\" required ";
  }
  else{
    $html .= "<input type=\"text\" NAME=\"nic\" style=\"width:250px\" required ";
  }
  $html .= "onchange=\"\" >";
  $html .= "</input>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $condi="type_localisation = 1";
  $loc_province = getListelocalisationPNSEB($condi);
  if ($loc_province != null){
    natcasesort($loc_province);
  }
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("Province")."</b></TD>";
  $html .= "<TD>\n";
  $html .= "<select class=\"chosen-select\" NAME=\"id_province\" style=\"width:250px\" ";
  $html .= "onchange=\"assign('Pnb-1'); this.form.submit(); \">\n";
  if (!isset($id_province) && $id_province == null) {
    $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
  }
  if (isset($loc_province))
    foreach($loc_province as $key=>$value){
      if (isset($id_province) && $id_province != null && $key == $id_province){
        $html .= "<option value=$key selected>".$value."</option>\n";
      }
      else {
        $html .= "<option value=$key>".$value."</option>\n";
      }
    }
  $html .= "</select>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  if (isset($id_province) && $id_province != null){
    $condi="type_localisation = 2 AND parent = ".$id_province;
    $loc_commune = getListelocalisationPNSEB($condi);
    if ($loc_commune != null){
      natcasesort($loc_commune);
    }
    $html .= "<TR bgcolor=$color>";
    $html.="<TD align=\"left\"><b>"._("Commune")."</b></TD>";
    $html .= "<TD>\n";
    $html .= "<select class=\"chosen-select\" NAME=\"id_commune\" style=\"width:250px\" ";
    $html .= "onchange=\"assign('Pnb-1'); this.form.submit();\">\n";
    if (!isset($id_commune) && $id_commune == null) {
      $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
    }
    if (isset($loc_commune))
      foreach($loc_commune as $key=>$value){
        if (isset($id_commune) && $id_commune != null && $key == $id_commune){
          $html .= "<option value=$key selected>".$value."</option>\n";
        }
        else {
          $html .= "<option value=$key>".$value."</option>\n";
        }
      }
    $html .= "</select>\n";
    $html .= "</TD>";
    $html .= "</TR>\n";
  }
  if (isset($id_commune) && $id_commune != null){
    $condi="type_localisation = 3 AND parent = ".$id_commune;
    $loc_zone = getListelocalisationPNSEB($condi);
    if ($loc_zone != null){
      natcasesort($loc_zone);
    }
    $html .= "<TR bgcolor=$color>";
    $html.="<TD align=\"left\"><b>"._("Zone")."</b></TD>";
    $html .= "<TD>\n";
    $html .= "<select class=\"chosen-select\" NAME=\"id_zone\" style=\"width:250px\" ";
    $html .= "onchange=\"assign('Pnb-1'); this.form.submit();\">\n";
    if (!isset($id_zone) && $id_zone == null) {
      $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
    }
    if (isset($loc_zone))
      foreach($loc_zone as $key=>$value){
        if (isset($id_zone) && $id_zone != null && $key == $id_zone){
          $html .= "<option value=$key selected>".$value."</option>\n";
        }
        else {
          $html .= "<option value=$key>".$value."</option>\n";
        }
      }
    $html .= "</select>\n";
    $html .= "</TD>";
    $html .= "</TR>\n";
  }
  if (isset($id_zone) && $id_zone != null){
    $condi="type_localisation = 4 AND parent = ".$id_zone;
    $loc_colline = getListelocalisationPNSEB($condi);
    if ($loc_colline != null){
      natcasesort($loc_colline);
    }
    $html .= "<TR bgcolor=$color>";
    $html.="<TD align=\"left\"><b>"._("Colline")."</b></TD>";
    $html .= "<TD>\n";
    $html .= "<select class=\"chosen-select\" NAME=\"id_coline\" style=\"width:250px\" ";
    $html .= "onchange=\"assign('Pnb-1'); this.form.submit();\">\n";
    if (!isset($id_coline) && $id_coline == null) {
      $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
    }
    if (isset($loc_colline))
      foreach($loc_colline as $key=>$value){
        if (isset($id_coline) && $id_coline != null && $key == $id_coline){
          $html .= "<option value=$key selected>".$value."</option>\n";
        }
        else {
          $html .= "<option value=$key>".$value."</option>\n";
        }
      }
    $html .= "</select>\n";
    $html .= "</TD>";
    $html .= "</TR>\n";
  }
  $html .= "</TABLE>\n";

  $html .= "<script type=\"text/javascript\">\n";
  $html .= "var config = { '.chosen-select' : {} }\n";
  $html .= "for (var selector in config) {\n";
  $html .= "$(selector).chosen(config[selector]); }\n";
  $html .= "</script>\n";

  //Bouton
  $MyPage->addFormButton(1,1,"butvalajout", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butvalajout", BUTP_PROCHAIN_ECRAN, "Pnb-2");

  $MyPage->addFormButton(1,2,"butretajout", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butretajout", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butretajout", BUTP_PROCHAIN_ECRAN, "Pns-1");

  $MyPage->addHTMLExtraCode("html",$html);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

/**
 * Confirmation Ajout/Modification d'un beneficiaire
 */
else if ($global_nom_ecran == "Pnb-2"){
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  if (!isset($id_province) || $id_province ==0){
    $erreur = new HTML_erreur(_("Localisation inconnu"));
    $erreur->setMessage(_("Veuillez selectionner une province pour procéder à la création du bénéficiaire"));
    $erreur->addButton(BUTTON_OK,"Pns-3");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  } else if (!isset($id_commune) || $id_commune ==0){
    $erreur = new HTML_erreur(_("Localisation inconnu"));
    $erreur->setMessage(_("Veuillez selectionner une commune pour procéder à la création du bénéficiaire"));
    $erreur->addButton(BUTTON_OK,"Pns-3");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }else if (!isset($id_zone) || $id_zone ==0){
    $erreur = new HTML_erreur(_("Localisation inconnu"));
    $erreur->setMessage(_("Veuillez selectionner une zone pour procéder à la création du bénéficiaire"));
    $erreur->addButton(BUTTON_OK,"Pns-3");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }else if(!isset($id_coline) || $id_coline ==0) {
    $erreur = new HTML_erreur(_("Localisation inconnu"));
    $erreur->setMessage(_("Veuillez selectionner une colline pour procéder à la création du bénéficiaire"));
    $erreur->addButton(BUTTON_OK,"Pns-3");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }

  $DATA_BENEF = array(
    'nom_prenom'=>$nom_prenom,
    'nic' => $nic,
    'id_province' => $id_province,
    'id_zone' => $id_zone,
    'id_commune' => $id_commune,
    'id_colline' => $id_coline,
    'id_ag' => $global_id_agence);

  if (isset($butvalajout)){
    $result = executeQuery($db, buildInsertQuery("ec_beneficiaire", $DATA_BENEF));

    $msg_confirmation = "Confirmation de l'ajout beneficiaire";
    $fonction = 177;
  }
  else{
    $whereToUpdate = array("id_beneficiaire" => $id_beneficiaire);

    $result = executeQuery($db, buildUpdateQuery("ec_beneficiaire", $DATA_BENEF, $whereToUpdate));

    $msg_confirmation = "Confirmation modification d'un beneficiaire";
    $fonction = 178;
  }

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  else {
    ajout_historique($fonction, NULL, $msg_confirmation, $global_nom_login, date("r"), NULL);
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message($msg_confirmation);
    $msgConfirmation = "L'entrée a été enregistrée avec succès";
    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ",  $msgConfirmation));
    if (isset($butvalajout)){
      $html_msg->addButton("BUTTON_OK", 'Pns-1');
    }
    else{
      $html_msg->addButton("BUTTON_OK", 'Pns-2');
    }
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}

else if ($global_nom_ecran == "Pnc-1"){
  $SESSION_VARS['id_beneficiaire'] = $SESSION_VARS['id_beneficiaire'] ;


  $Myform = new HTML_GEN2(_("Ajout commande"));
  $condition_annee_agri = "date(now()) between date_debut AND date_fin";
  $annee_agri_actuelle =getRangeDateAnneeAgri($condition_annee_agri);
  //$ListeAgences = agence_Remote::getListRemoteAgenceComp(); 

  $condition_saison_cultu = "id_annee = ".$annee_agri_actuelle['id_annee']." and etat_saison = 1";
  $saison_cultu_acutelle = getDetailSaisonCultu($condition_saison_cultu);
  $SESSION_VARS['id_annee']= $annee_agri_actuelle['id_annee'];
  $SESSION_VARS['id_saison']= $saison_cultu_acutelle['id_saison'];
  $SESSION_VARS['plafond_engrais']= $saison_cultu_acutelle['plafond_engrais'];
  $SESSION_VARS['plafond_amendement']= $saison_cultu_acutelle['plafond_amendement'];

  $Myform->addField("annee_agri", _("Année agricole"), TYPC_TXT,$annee_agri_actuelle['libel']);
  $Myform->setFieldProperties("annee_agri", FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties("annee_agri", FIELDP_IS_LABEL, true);

  $Myform->addField("saison_cultu", _("Saison culturale"), TYPC_TXT,$saison_cultu_acutelle['nom_saison']);
  $Myform->setFieldProperties("saison_cultu", FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties("saison_cultu", FIELDP_IS_LABEL, true);

  $html  ="<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

// En-tête du tableau
  $html .= "<TR bgcolor=$colb_tableau>";
  $html.="<TD><b>"._("N°")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Produit")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Prix Minimum")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Quantité")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Montant")."<br />"._("à déposer")."</b></TD>";
  $html.="</TR>\n";

  $produit_actif = getListeProduitPNSEB("etat_produit = 1",true);
  $js="";
  $my_js="";
  $SESSION_VARS["nb_ligne"]=10;
//foreach ($temp as $key => $value)
  for ($key=1 ; $key <= $SESSION_VARS["nb_ligne"] ; $key++) {
    $i=$key;
    // On alterne la couleur de fond
    if ($i%2)
      $color = $colb_tableau;
    else
      $color = $colb_tableau_altern;

    // une ligne de saisie
    $html .= "<TR bgcolor=$color>\n";

    //numéro de la ligne
    $html .= "<TD><b>$i</b></TD>";

    //Montant
    $html.="<TD><select NAME=\"HTML_GEN_LSB_produit$key\" Onchange=\"verifieProduitSimilaire(this,$key);\">";
    $html.="<option value=0>[Aucun]</option>";
    foreach( $produit_actif as $key1=>$value)
      $html.="<option value=$key1>".$value['libel']."</option>";
    $html.= "</select></TD>\n";

    $html.="<TD><select NAME=\"HTML_GEN_LSB_mnt_mini$key\" hidden >";
    $html.="<option value=0>[Aucun]</option>";
    foreach( $produit_actif as $key2=>$value)
      $html.="<option value=".$value['id_produit'].">".round($value['montant_minimum'],2)."</option>";
    $html.= "</select>\n";
    $html.="<INPUT TYPE=\"text\" NAME=\"mnt_mini_recup1$key\" size=14 value='' readonly>";
    $html.="<INPUT TYPE=\"hidden\" NAME=\"mnt_mini_recup$key\" size=14 value='' > ";
    //$html.="<TD><INPUT TYPE=\"text\" NAME=\"mnt_mini$key\" size=14 value='';\"";
    $html.="</TD><TD><INPUT TYPE=\"number\" NAME=\"quantite$key\" size=14 value='' Onchange=\"changeMontant($key);\"";
    $html.=">";
    // numéro du bordoreau
    $html.="<TD><INPUT TYPE=\"text\" NAME=\"montantdepot$key\" size=14 value='' disabled></TD>\n";
    // Devise

    //  $html.="<TD><INPUT TYPE=\"text\" NAME=\"devise$key\" size=14 value='$key'disabled=true ></TD>\n";
    $html.="</TR>";
  }


  /*$js.=" function changeMontant(i)
     {
      var prod = eval('document.ADForm.HTML_GEN_LSB_produit'+i);
      //produit = eval('document.ADForm.HTML_GEN_LSB_produit'+i+'.options[]value');
      produit = prod.options[prod.selectedIndex].value;
      mnt_mini = eval('document.ADForm.HTML_GEN_LSB_mnt_mini'+i+'.value');
      var slt = eval('document.ADForm.HTML_GEN_LSB_mnt_mini'+i);
      var mnt_min_recup = eval('document.ADForm.mnt_mini_recup'+i);
      var mnt_min_recup1 = eval('document.ADForm.mnt_mini_recup1'+i);
      var mnt_depot = eval('document.ADForm.montantdepot'+i);
      var qtite = eval('document.ADForm.quantite'+i);
      mnt_min_recup.value = slt.options[produit].value;
      mnt_min_recup1.value = slt.options[produit].value;
      //console.log(slt.options[i].value);
      console.log(slt.options.length);
      for(var y=0; y < slt.options.length ;y++){ alert('waa ici top ');
        if (slt.options[y].value == produit){ //alert('waa ici sa ');
        console.log(slt.options[y].value+' '+produit);
        //mnt_depot.value = slt.options[y].value * qtite.value;
        }

      }


     }";*/
  $js.=" function changeMontant(i) {
    var prod = eval('document.ADForm.HTML_GEN_LSB_produit'+i+'.value');
    mnt_mini = eval('document.ADForm.HTML_GEN_LSB_mnt_mini'+i+'.value');
    var slt = eval('document.ADForm.HTML_GEN_LSB_mnt_mini'+i);
    var mnt_min_recup = eval('document.ADForm.mnt_mini_recup'+i);
    var mnt_min_recup1 = eval('document.ADForm.mnt_mini_recup1'+i);
    var mnt_depot = eval('document.ADForm.montantdepot'+i);
    var qtite = eval('document.ADForm.quantite'+i);

    if (qtite.value < 0){
      alert('Les quantités doivent être supérieur à 0!');
      qtite.value = 0;
    }

    for(var y=0; y < slt.options.length ;y++){
        if (slt.options[y].value == prod){
        mnt_min_recup.value = slt.options[y].value;
        mnt_min_recup1.value = slt.options[y].text;
        console.log(slt.options[y].value+' '+prod);
        mnt_depot.value = slt.options[y].text * qtite.value;
        }
  }

  }
  ";

  $JsCheckProd = "function verifieProduitSimilaire(SelectedValue,id){\n";
  $JsCheckProd .= "\tvar nbr_select_available = 10;\n";
  $JsCheckProd .= "\tvar doChangeMontant = 1;\n";
  $JsCheckProd .= "\tfor (var i = 1; i <= nbr_select_available; i++ ){\n";
  $JsCheckProd .= "\t\tvar ForLoopValue = document.getElementsByName('HTML_GEN_LSB_produit'+i).item(0).value;\n";
  $JsCheckProd .= "\t\tif (ForLoopValue != 0 &&  ForLoopValue == SelectedValue.value && i != id ){\n";
  $JsCheckProd .= "\t\t\talert('Vous avez choisi deux produit similaires!');\n";
  $JsCheckProd .= "\t\t\tSelectedValue.value = 0;\n";
  $JsCheckProd .= "\t\t\tdoChangeMontant = 0;\n";
  $JsCheckProd .= "\t\t}\n";
  $JsCheckProd .= "\t}\nif (doChangeMontant == 1){\n\tchangeMontant(id);\n\t}\n";
  $JsCheckProd .="}\n";
  $Myform->addJS(JSP_FORM,"verifieProduitSimilaire",$JsCheckProd);

  $html.="</TABLE>";
  $Myform->addHTMLExtraCode("html",$html);
  $Myform->addJS(JSP_FORM, "modif", $js);

  $Myform->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pnc-2");

  $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");
  $Myform->buildHTML();
  echo $Myform->getHTML();
}

else if ($global_nom_ecran == "Pnc-2"){



  $qtite_engrais = 0;
  $qtite_amendement = 0;
  $mnt_engrais = 0;
  $mnt_amendement = 0 ;

  $i=0;
  for ($key=1;$key<=$SESSION_VARS["nb_ligne"];$key++) {
    if ((empty(${"produit1"})) && (!isset($_GET['id_dem']))){
      $erreur = new HTML_erreur(_("Commande vide"));
      $erreur->setMessage(_("Veuillez préciser au moins un produit pour la commande"));
      $erreur->addButton(BUTTON_OK, "Pns-2");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      exit();
    }
    if (isset(${"produit".$key}) && empty(${"quantite".$key})) {
      $erreur = new HTML_erreur(_("Commande invalide"));
      $condition_check = " id_produit = ".${"produit".$key};
      $detailProdCheck =getDetailsProduits($condition_check);
      $erreur->setMessage(_("Veuillez préciser la quantite du produit : ".$detailProdCheck["libel"]));
      $erreur->addButton(BUTTON_OK, "Pns-2");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      exit();
    }

    if (!empty(${"produit" . $key})) {
      $produit = ${"produit" . $key};
      $mnt_mini = ${"mnt_mini_recup1" . $key};
      $qtite = ${"quantite" . $key};
      $mnt_depot = $mnt_mini * $qtite;
      $condi_prod = " id_produit = ".$produit;
      $DATA[$i]["produit"]=$produit;
      $DATA[$i]["mnt_mini_recup"]=$mnt_mini;
      $DATA[$i]["quantite"]=$qtite;
      $DATA[$i]["mnt_depot"]=$mnt_depot;
      $i++;
      $detailsProd =getDetailsProduits($condi_prod);
      if($detailsProd['type_produit']== 1 && $qtite >0){
        $qtite_engrais += $qtite;
        $mnt_engrais +=$mnt_depot;
      }
      else if ($detailsProd['type_produit']== 2 && $qtite >0) {
        $qtite_amendement += $qtite;
        $mnt_amendement +=$mnt_depot;
      }
    }
  }
  $mnt_total = $mnt_engrais + $mnt_amendement;
  $DATA["total"]=$mnt_total;

  if (sizeof($DATA) == 0) {
    $message .= _("Vous devez remplir au moins une ligne");
  }
  $SESSION_VARS['donnee']=$DATA;

  global $global_nom_login, $global_id_agence, $colb_tableau;
  $info_login = get_login_full_info($global_nom_login);
  $info_agence = getAgenceDatas($global_id_agence);
  $SESSION_VARS['demande_derogation']= "true";
  $SESSION_VARS['qtite_engrais']=$qtite_engrais;
  $SESSION_VARS['qtite_amendement']=$qtite_amendement;

  if(!isset($_GET['id_dem']) ) {
    $type_prod_commande = getNbreProduitCommande($SESSION_VARS['id_saison'], $SESSION_VARS['id_beneficiaire']);
    if ($type_prod_commande > 0) {
      while (list($key, $COM) = each($type_prod_commande)) {
        if ($COM['type_produit'] == 1 && $SESSION_VARS['qtite_engrais'] >0) {
          $qtite_engrais += $COM['quantite'];
        } else if ($COM['type_produit'] == 2 && $SESSION_VARS['qtite_amendement']>0) {
          $qtite_amendement += $COM['quantite'];
        }
      }
    }
  }

  $msg = "";


  if( ($qtite_engrais > $SESSION_VARS["plafond_engrais"]) || ($qtite_amendement > $SESSION_VARS["plafond_amendement"] )){

    //$msg = "<center>"._("Le montant demandé dépasse le montant plafond de retrait autorisé. Ce login n'est pas habilité à le faire.");
    //$msg .= " "._("Veuillez contacter votre administrateur.")."</center>";

    // Affichage de la confirmation
    if(($qtite_engrais > $SESSION_VARS["plafond_engrais"]) && $qtite_amendement < $SESSION_VARS["plafond_amendement"]) {
      $html_msg = new HTML_message("Demande autorisation de depassement de plafond de commande");

      $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le quantité de commande des engrais dépasse la quantité plafond des engrais autorisés.</span><br /><br />Quantité demandé = <span style='color: #FF0000;font-weight: bold;'>" . $qtite_engrais . " Sacs</span><br/>Quantité plafond de commande engrais autorisé = " . $SESSION_VARS["plafond_engrais"] . " Sacs<br /><br />Veuillez choisir une option ci-dessous ?<br /><br/></center><input type=\"hidden\" name=\"qtite_engrais\" value=\"" . recupMontant($qtite_engrais) . "\" />");
    } else if (($qtite_amendement > $SESSION_VARS["plafond_amendement"]) && ($qtite_engrais < $SESSION_VARS["plafond_engrais"]) ){
      $html_msg = new HTML_message("Demande autorisation de depassement de plafond de commande");

      $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le quantité de commande dépasse la quantité plafond des amenedements autorisés.</span><br /><br />Quantité demandé = <span style='color: #FF0000;font-weight: bold;'>" .$qtite_amendement . " Sacs</span><br/>Quantité plafond de commande amendement autorisé = " . $SESSION_VARS["plafond_amendement"] . " Sacs<br /><br />Veuillez choisir une option ci-dessous ?<br /><br/></center><input type=\"hidden\" name=\"qtite_amendement\" value=\"" . recupMontant($qtite_amendement) . "\" />");

    }
    else if( ($qtite_engrais > $SESSION_VARS["plafond_engrais"]) && ($qtite_amendement > $SESSION_VARS["plafond_amendement"] )){
      $html_msg = new HTML_message("Demande autorisation de depassement de plafond de commande");

      $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Les quantités de la commande dépasse les quantités du plafond d'engrais et amendements autorisés.</span><br /><br />Quantité demandé = <span style='color: #FF0000;font-weight: bold;'>" . $qtite_engrais . " Sacs</span><br/>Quantité plafond de commande engrais  autorisé = " . $SESSION_VARS["plafond_engrais"] . " Sacs<br /><br />Quantité demandé = <span style='color: #FF0000;font-weight: bold;'>" . $qtite_amendement . " Sacs</span><br/>Montant plafond de commande amendement autorisé = " . $SESSION_VARS["plafond_amendement"]. " Sacs<br /><br />Veuillez choisir une option ci-dessous ?<br /><br/></center><input type=\"hidden\" name=\"qtite_engrais\" value=\"" . recupMontant($qtite_engrais) . "\" /><input type=\"hidden\" name=\"qtite_amendement\" value=\"" . recupMontant($qtite_amendement) . "\" />");

    }

    $html_msg->addCustomButton("btn_demande_autorisation_retrait", "Demande d’autorisation", 'Pnc-3');
    $html_msg->addCustomButton("btn_annuler", "Annuler", 'Pns-2');

    $html_msg->buildHTML();

    echo $html_msg->HTML_code;
    die();
  }
  else{

    unset($SESSION_VARS['id_dem']);
    if (isset($_GET['id_dem']) && $_GET['id_dem'] > 0) {
      $SESSION_VARS['demande_derogation'] = "false";
      $SESSION_VARS['id_dem'] = $_GET['id_dem'];
      $SESSION_VARS['id_commande_derogation'] = $_GET['id_commande'];
    }
    else {
      $SESSION_VARS['demande_derogation'] = "false";
    }

    $SESSION_VARS['demande_derogation'] = "false";
    $html_msg = new HTML_GEN2(_("Confirmation de la commande"));

    $html_msg = new HTML_message("Demande de confirmation de la commande en attente");
    $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le commande sera passer en attente de paiement d'avances.</span><br />Veuillez valider la commande ?<br />");


    $html_msg->addCustomButton("btn_demande_commande_attente", "Passer la commande en attente", 'Pnc-3');
    $html_msg->addCustomButton("btn_annuler_attente", "Annuler", 'Pns-2');
    $html_msg->buildHTML();

    echo $html_msg->HTML_code;
    die();

    $html->buildHTML();
    echo $html->getHTML();
  }
}

else if ($global_nom_ecran == "Pnc-3"){
  global $dbHandler,$global_id_agence,$global_id_guichet;
  $db = $dbHandler->openConnection();

  $operation=615;
  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array(); // copmte comptable produit
  $cptes_substitue["int"] = array(); // compte client

  if (isset($SESSION_VARS['id_dem']) && $SESSION_VARS['id_dem'] > 0){

    $Fields["etat_commande"] = 7;
    $Fields["date_modif"] = date("Y-m-d");
    $Where["id_commande"] = $SESSION_VARS['id_commande_derogation'];
    $result_update = buildUpdateQuery("ec_commande", $Fields, $Where);
    $result1 = $db->query($result_update);
    if (DB::isError($result1)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$result1."\n".$result1->getMessage());
    }

    $Fields1["etat"] = 4;
    $Where1["id_derogation"] = $SESSION_VARS['id_dem'];
    $result_update_derogation = buildUpdateQuery("ec_derogation", $Fields1, $Where1);
    $result2 = $db->query($result_update_derogation);
    if (DB::isError($result2)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$result2."\n".$result2->getMessage());
    }
    $condi6 = "id_commande = ".$SESSION_VARS['id_commande_derogation'];
    $commande_detail_ecriture =getCommandeDetail($condi6);

    $infos_his = 'id_benef=' . $SESSION_VARS['id_beneficiaire'] . '- login =' . $global_nom_login . ' - comm= operation ajout commande par derogation';
    $myErr = ajout_historique(175, null, $infos_his, $global_nom_login, date("r"), $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }

    $dbHandler->closeConnection(true);

  }
  else {
    $sql = "SELECT nextval('ec_commande_id_commande_seq')";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, $result->getMessage());
    }
    if ($result->numRows() == 0) return NULL;
    $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

    $DATA_COMMANDE = array(
      'id_commande' => $DATAS['nextval'],
      'id_benef' => $SESSION_VARS['id_beneficiaire'],
      'id_saison' => $SESSION_VARS['id_saison'],
      'montant_total' => '',
      'montant_depose' => $SESSION_VARS['donnee']['total'],
      'etat_commande' => '7',
      'date_creation' => date('d/m/Y'),
      'date_modif' => '',
      'id_ag' => $global_id_agence);
    if ($SESSION_VARS['demande_derogation'] == "true") {
      $DATA_COMMANDE['etat_commande'] = '6';
    }
    $result = executeQuery($db, buildInsertQuery("ec_commande", $DATA_COMMANDE));

    $i = 0;
    for ($i = 0; $i <= $SESSION_VARS["nb_ligne"]; $i++) {
      if (!empty($SESSION_VARS['donnee'][$i])) {
        $produit = $SESSION_VARS['donnee'][$i]['produit'];
        $mnt_mini_recup = $SESSION_VARS['donnee'][$i]['mnt_mini_recup'];
        $quantite = $SESSION_VARS['donnee'][$i]['quantite'];
        $mnt_depot = $SESSION_VARS['donnee'][$i]['mnt_depot'];
        $DATA_COMMANDE_DETAIL = array(
          'id_commande' => $DATAS['nextval'],
          'id_produit' => $produit,
          'quantite' => $quantite,
          'prix_total' => '',
          'montant_depose' => $mnt_depot,
          'date_creation' => date('d/m/Y'),
          'date_modif' => '',
          'id_ag' => $global_id_agence);
        $result = executeQuery($db, buildInsertQuery("ec_commande_detail", $DATA_COMMANDE_DETAIL));

      }
    }
    if ($SESSION_VARS['demande_derogation'] == "true") {
      $DATA_DEROGATION = array(
        'id_benef' => $SESSION_VARS['id_beneficiaire'],
        'id_commande' => $DATAS['nextval'],
        'etat' => '1',
        'nbre_engrais' => $SESSION_VARS['qtite_engrais'],
        'nbre_amendement' => $SESSION_VARS['qtite_amendement'],
        'date_creation' => date('d/m/Y'),
        'date_modif' => '',
        'login_uti' => $global_nom_login,
        'comment' => 'En attente approbation',
        'id_his' => '',
        'id_ag' => $global_id_agence);
      $result = executeQuery($db, buildInsertQuery("ec_derogation", $DATA_DEROGATION));
    }
  }

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  else {
    if ($SESSION_VARS['demande_derogation']=="true") {

      $dbHandler->closeConnection(true);
      $html_msg = new HTML_message("Confirmation de la demande de dérogation");

      $demande_msg = "Votre demande de dérogation a été enregistrée avec succès!";


      $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

      $html_msg->addButton("BUTTON_OK", 'Pns-2');
      $now = date("Y-m-d");
      $condi5['id_beneficiaire']=$SESSION_VARS['id_beneficiaire'];

      $data_beneficiaire = getMatchedBeneficiaire($condi5);

      print_recu_demande_autorisation_commande($SESSION_VARS['id_beneficiaire'],$data_beneficiaire[0]['nom_prenom'],$DATAS['nextval'],$SESSION_VARS['qtite_engrais'], $SESSION_VARS['qtite_amendement'] ,$now, $global_nom_login);

      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    }else {

      $dbHandler->closeConnection(true);
      $html_msg = new HTML_message("Confirmation de la commande");

      $demande_msg = "Votre commande a été enregistrée et en attente de validation de paiement!";


      $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

      $html_msg->addButton("BUTTON_OK", 'Pns-2');
      /*$now = date("Y-m-d");
      $condi5['id_beneficiaire']=$SESSION_VARS['id_beneficiaire'];

      $data_beneficiaire = getMatchedBeneficiaire($condi5);

      if (isset($SESSION_VARS['id_dem'])){
        $condi7 = "id_derogation = ".$SESSION_VARS['id_dem'];
        $data_recu_comm_dero= getDerogationCommande($condi7);
        $engrais = $data_recu_comm_dero[$SESSION_VARS['id_dem']]['nbre_engrais'];
        $amendement = $data_recu_comm_dero[$SESSION_VARS['id_dem']]['nbre_amendement'];
        $id_comm_recu = $data_recu_comm_dero[$SESSION_VARS['id_dem']]['id_commande'];
      }else {
        $engrais =$SESSION_VARS['qtite_engrais'];
        $amendement = $SESSION_VARS['qtite_amendement'];
        $id_comm_recu = $DATAS['nextval'];
        $mnt_deposer = $mnt_deposer;
      }

      print_recu_commande($SESSION_VARS['id_beneficiaire'],$data_beneficiaire[0]['nom_prenom'],$id_comm_recu,$engrais, $amendement ,$mnt_deposer,$now, $global_nom_login);
*/

      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    }


  }

}
else if ($global_nom_ecran == "Pnd-1"){

  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Details commandes"));
  $condi = "etat_commande in (1,2,3,4,5,6,7,8) and id_benef=".$SESSION_VARS['id_beneficiaire'];
  $commande_actif= getCommande($condi,"id_commande");

  $xtHTML = "<br /><table align=\"center\" cellpadding=\"6\" width=\"90% \" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
    <tr align=\"left\" bgcolor=\"$colb_tableau\"><th>"._("No Commmande")."</th><th>"._("Etat Commande")."</th><th>"._("Montant avance paye")."</th><th>"._("Montant commande")."</th><th>"._("Date Commande")."</th><th>"._("Voir Details")."</th></tr>";
  while (list($key, $COM) = each($commande_actif)) {
    $id_comm = $COM['id_commande'];
    if($COM['montant_total']==null){
      $mnt_total = "Non renseigne";
    }else {
      $mnt_total =afficheMontant($COM['montant_total'],true);
    }

    $details = '<a href = "#" onClick ="';
    $idBenef = $SESSION_VARS["id_beneficiaire"];
    $details .= "OpenBrw('$SERVER_NAME/modules/guichet/module_engrais_chimiques_situation_commande.php?id_comm=".$id_comm."&id_benef=".$idBenef."');return false;";
    //$details .= "javascript:window.open('$SERVER_NAME/modules/guichet/module_engrais_chimiques_situation_commande.php?id_comm=".$id_comm."&id_benef=".$SESSION_VARS['id_beneficiaire'];
    //$details .= "' , 'Detail Commande', 'width=700,height=500');";
    $details .= '" >Details</a>';
    $date_com = pg2phpDatebis($COM['date_creation']);
    $date_commande = date("d/m/Y", mktime(0, 0, 0, (int)$date_com[0], $date_com[1], $date_com[2]));

    $xtHTML .= "\n<tr bgcolor=\"$color\"><td>" . $id_comm . "</td><td>" .$adsys['adsys_etat_commande'][$COM['etat_commande']] . "</td><td>" . afficheMontant($COM["montant_depose"],true) . "</td><td>" . $mnt_total. "</td><td>" . $date_commande . "</td><td>" . $details . "</td></tr>";
  }

  $xtHTML .= "</table><br /><br/><br />";


  $myForm->addHTMLExtraCode("xtHTML".$id_comm, $xtHTML);

  $myForm->addFormButton(1, 1, "retour", _("Retour"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Pns-2');

  $myForm->buildHTML();
  echo $myForm->getHTML();
}

// Modification d'un beneficiaire

else if ($global_nom_ecran == "Pnm-1"){

  if (isset($SESSION_VARS['id_beneficiaire'])){
    $id_benef = $SESSION_VARS['id_beneficiaire'];
  }

  $label_table= "des bénéficiaires";
  $table_ajout = "ec_beneficiaire";

  //recupere details du beneficiaire
  $where_data_benef = "id_beneficiaire = ".$id_benef;
  $data_benef = getDetailsBeneficiaire($where_data_benef);
  $nm_prenom = $data_benef['nom_prenom'];
  $_nic = $data_benef['nic'];
  if (isset($id_province) && $id_province != null){
    $province = $id_province;
  }
  else{
    $province = $data_benef['id_province'];
  }
  if (isset($id_commune) && $id_commune != null){
    $commune = $id_commune;
  }
  else{
    $commune = $data_benef['id_commune'];
  }
  if (isset($id_zone) && $id_zone != null){
    $zone = $id_zone;
  }
  else{
    $zone = $data_benef['id_zone'];
  }
  if (isset($id_coline) && $id_coline != null){
    $colline = $id_coline;
  }
  else{
    $colline = $data_benef['id_colline'];
  }

  $MyPage = new HTML_GEN2(_("Modification d'une entrée dans la table")." '".$label_table."'");

  $color = $colb_tableau;
  $html = "<link rel=\"stylesheet\" href=\"/lib/misc/js/chosen/css/chosen.css\">";
  $html .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $html .= "<script src=\"/lib/misc/js/chosen/chosen.jquery.js\" type=\"text/javascript\"></script>";

  //$html .= "<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=0 cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b></b></TD>";
  $html .= "<TD>\n";
  $html .= "<input type=\"text\" NAME=\"id_beneficiaire\" style=\"width:250px\" value=$id_benef hidden ";
  $html .= "onchange=\"\" >";
  $html .= "</input>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("Nom et Prenom")."</b></TD>";
  $html .= "<TD>\n";
  if (isset($nom_prenom) && $nom_prenom != null){
    $html .= "<input type=\"text\" ID=\"nom_prenom\"  NAME=\"nom_prenom\" style=\"width:250px\" VALUE=\"$nom_prenom\" ";
  }
  else{
    $html .= "<input type=\"text\" ID=\"nom_prenom\" NAME=\"nom_prenom\" style=\"width:250px\" VALUE=\"$nm_prenom\" ";
  }
  $html .= "onchange=\"\" >";
  $html .= "</input>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("NIC")."</b></TD>";
  $html .= "<TD>\n";
  if (isset($nic) && $nic != null){
    $html .= "<input type=\"text\" ID=\"nic\"  NAME=\"nic\" style=\"width:250px\" VALUE=\"$nic\" ";
  }
  else{
    $html .= "<input type=\"text\" ID=\"nic\" NAME=\"nic\" style=\"width:250px\" VALUE=\"$_nic\" ";
  }
  $html .= "onchange=\"\" >";
  $html .= "</input>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $condi="type_localisation = 1";
  $loc_province = getListelocalisationPNSEB($condi);
  natcasesort($loc_province);
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("Province")."</b></TD>";
  $html .= "<TD>\n";
  $html .= "<select class=\"chosen-select\" NAME=\"id_province\" style=\"width:250px\" ";
  $html .= "onchange=\"assign('Pnm-1'); this.form.submit();\">\n";
  $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
  if (isset($loc_province))
    foreach($loc_province as $key=>$value){
      if ($key == $province){
        $html .= "<option value=$key selected>".$value."</option>\n";
      }
      else{
        $html .= "<option value=$key>".$value."</option>\n";
      }
    }
  $html .= "</select>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $condi="type_localisation = 2 AND parent = ".$province;
  $loc_commune = getListelocalisationPNSEB($condi);
  natcasesort($loc_commune);
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("Commune")."</b></TD>";
  $html .= "<TD>\n";
  $html .= "<select class=\"chosen-select\" NAME=\"id_commune\" style=\"width:250px\" ";
  $html .= "onchange=\"assign('Pnm-1'); this.form.submit();\">\n";
  $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
  if (isset($loc_commune))
    foreach($loc_commune as $key=>$value){
      if ($key == $commune){
        $html .= "<option value=$key selected>".$value."</option>\n";
      }
      else{
        $html .= "<option value=$key>".$value."</option>\n";
      }
    }
  $html .= "</select>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $condi="type_localisation = 3 AND parent = ".$commune;
  $loc_zone = getListelocalisationPNSEB($condi);
  natcasesort($loc_zone);
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("Zone")."</b></TD>";
  $html .= "<TD>\n";
  $html .= "<select class=\"chosen-select\" NAME=\"id_zone\" style=\"width:250px\" ";
  $html .= "onchange=\"assign('Pnm-1'); this.form.submit();\">\n";
  $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
  if (isset($loc_zone))
    foreach($loc_zone as $key=>$value){
      if ($key == $zone){
        $html .= "<option value=$key selected>".$value."</option>\n";
      }
      else{
        $html .= "<option value=$key>".$value."</option>\n";
      }
    }
  $html .= "</select>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $condi="type_localisation = 4 AND parent = ".$zone;
  $loc_colline = getListelocalisationPNSEB($condi);
  natcasesort($loc_colline);
  $html .= "<TR bgcolor=$color>";
  $html.="<TD align=\"left\"><b>"._("Colline")."</b></TD>";
  $html .= "<TD>\n";
  $html .= "<select class=\"chosen-select\" NAME=\"id_coline\" style=\"width:250px\" ";
  $html .= "onchange=\"assign('Pnm-1'); this.form.submit();\">\n";
  $html .= "<option value=\"0\">["._("Aucun")."]</option>\n";
  if (isset($loc_colline))
    foreach($loc_colline as $key=>$value){
      if ($key == $colline){
        $html .= "<option value=$key selected>".$value."</option>\n";
      }
      else{
        $html .= "<option value=$key>".$value."</option>\n";
      }
    }
  $html .= "</select>\n";
  $html .= "</TD>";
  $html .= "</TR>\n";
  $html .= "</TABLE>\n";

  $html .= "<script type=\"text/javascript\">\n";
  $html .= "var config = { '.chosen-select' : {} }\n";
  $html .= "for (var selector in config) {\n";
  $html .= "$(selector).chosen(config[selector]); }\n";
  $html .= "</script>\n";

  //Bouton
  $MyPage->addFormButton(1,1,"butvalmodif", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butvalmodif", BUTP_PROCHAIN_ECRAN, "Pnb-2");

  $MyPage->addFormButton(1,2,"butretmodif", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butretmodif", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butretmodif", BUTP_PROCHAIN_ECRAN, "Pns-2");

  $MyPage->addHTMLExtraCode("html",$html);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
else if ($global_nom_ecran == "Pna-1"){
  global $global_id_client;


  $my_page = new HTML_GEN2("Liste de demandes d'autorisation de commandes");

  $jsBuildBol = "
                    function manageCheckbox(obj, chk_num) {

                        // Uncheck all
                        if (obj.checked) {
                            var valid = document.getElementsByName('check_valid_' + chk_num)[0].checked = false;
                            var rejet = document.getElementsByName('check_rejet_' + chk_num)[0].checked = false;
                        }

                        obj.checked = !obj.checked;

                        return false;
                    }

                    function checkAll(obj) {

                        if (obj.className == 'rejet' && obj.checked) {
                            var el = document.getElementsByClassName('valid');

                            var i;
                            for (i = 0; i < el.length; i++) {
                                el[i].checked = false;
                            }
                        }
                        else if (obj.className == 'valid' && obj.checked) {
                            var el = document.getElementsByClassName('rejet');

                            var i;
                            for (i = 0; i < el.length; i++) {
                                el[i].checked = false;
                            }
                        }

                        var el = document.getElementsByClassName(obj.className);

                        var i;
                        for (i = 0; i < el.length; i++) {
                            el[i].checked = obj.checked;
                        }

                        return false;
                    }
    ";

  $my_page->addHTMLExtraCode("header_msg","<h3 align=\"center\" style=\"font:12pt arial;\">Veuillez s'il vous plaît cocher au moins une case par demande</h3><br/>");

  // Header row
  $my_page->addField("checkall_valid","<span style='width: 60px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>N°</span><span style='width:120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>N° Bénéficiaire</span><span style='width:180px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Montant Avance Payé</span><span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Login</span><span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Date demande</span><span style='width: 80px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-bottom-width: 1px;border-right-width: 1px;border-bottom-style: solid;border-right-style: solid;border-bottom-color: #007777;border-right-color: #007777;font-weight: bold;'>Details</span>", TYPC_BOL);


  $jsBuildBol .= "
                    var objBolEl = document.getElementsByName('HTML_GEN_BOL_checkall_valid')[0];

                    objBolEl.setAttribute(\"class\", \"valid\");
                    objBolEl.setAttribute(\"alt\", \"Tous Autoriser\");
                    objBolEl.setAttribute(\"title\", \"Tous Autoriser\");
                    objBolEl.setAttribute(\"id\", \"checkall_valid\");
                    objBolEl.setAttribute(\"name\", \"checkall_valid\");
                    objBolEl.setAttribute(\"onclick\", \"checkAll(this)\");

                    var objTrEl = objBolEl.parentNode;

                    var objInputChk = '<span style=\"padding-left: 45px;\">&nbsp;</span><input type=\"checkbox\" id=\"checkall_rejet\" name=\"checkall_rejet\" class=\"rejet\" alt=\"Tous Refuser\" title=\"Tous Refuser\" onclick=\"checkAll(this)\">';

                    objTrEl.innerHTML = '<span style=\"padding-left: 15px;\">&nbsp;</span>' + objTrEl.innerHTML + objInputChk;
        ";

  // Get liste demande de retrait
  //$listeDemandeRetrait = getListeRetraitAttente();
  $id_benef =$SESSION_VARS['id_beneficiaire'];
  $condi = "etat = 1 and id_benef =".$id_benef;
  $commande_actif= getDerogationCommande($condi);

  $displayHeader = true;
  foreach ($commande_actif as $id => $demande_derogation) {

    $id_demande = trim($demande_derogation["id_derogation"]);
    $id_beneficiaire = $demande_derogation["id_benef"];
    $login = $demande_derogation["login_uti"];
    $date_demande = pg2phpDate($demande_derogation["date_creation"]);
    $id_comm = $demande_derogation["id_commande"];


    $mnt_commande = getCommande("id_commande =".$id_comm);
    $mnt_ = afficheMontant($mnt_commande[$id_comm]["montant_depose"],true);

    $details = '<a href = "#" onClick="';
    $details .= "javascript:window.open('../modules/guichet/module_engrais_chimique_details_commande.php?id_comm=".$id_comm;
    $details .= "' , 'Detail Derogation', 'width=700,height=500'); return false;";
    $details .= '" >Details</a>';

    $libelle_demande =
      sprintf("<span style='width: 60px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span>
<span style='width: 120px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span>
<span style='width: 180px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span>
<span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span>
<span style='width: 130px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span>
<span style='width: 80px;padding-left: 10px;padding-right: 10px;text-align: center;display: block;float: left;border-right-width: 1px;border-right-style: solid;border-right-color: #007777;'>%s</span>", $id_demande,$id_beneficiaire,$mnt_, $login,  $date_demande,$details);

    $my_page->addField("check_valid_" . $id_demande, "$libelle_demande", TYPC_BOL);

    $jsBuildBol .= "
                    var objBolEl$id_demande = document.getElementsByName('HTML_GEN_BOL_check_valid_$id_demande')[0];

                    objBolEl$id_demande.setAttribute(\"class\", \"valid\");
                    objBolEl$id_demande.setAttribute(\"alt\", \"Autoriser\");
                    objBolEl$id_demande.setAttribute(\"title\", \"Autoriser\");
                    objBolEl$id_demande.setAttribute(\"value\", \"$id_demande\");
                    objBolEl$id_demande.setAttribute(\"id\", \"check_valid_$id_demande\");
                    objBolEl$id_demande.setAttribute(\"name\", \"check_valid_$id_demande\");
                    objBolEl$id_demande.setAttribute(\"onclick\", \"manageCheckbox(this, $id_demande)\");

                    var objTrEl$id_demande = objBolEl$id_demande.parentNode;

                    var objInputChkRejet$id_demande = '<span style=\"padding-left: 45px;\">&nbsp;</span><input type=\"checkbox\" id=\"check_rejet_$id_demande\" name=\"check_rejet_$id_demande\" class=\"rejet\" alt=\"Refuser\" title=\"Refuser\" onclick=\"manageCheckbox(this, $id_demande)\" value=\"$id_demande\" value=\"$id_demande\">';

                    objTrEl$id_demande.innerHTML = '<span style=\"padding-left: 15px;\">&nbsp;</span>' + objTrEl$id_demande.innerHTML + objInputChkRejet$id_demande;
        ";

    if ($displayHeader == true) {
      $jsBuildBol .= "
                    var objBody$id_demande = objTrEl$id_demande.parentNode.parentNode;

                    objBody$id_demande.innerHTML = '<tr bgcolor=\"#FDF2A6\"><td align=\"left\"></td><td align=\"left\"> Autoriser <b>OU</b> Refuser</td><td align=\"left\"></td></tr>' + objBody$id_demande.innerHTML;
        ";
      $displayHeader = false;
    }
  }

  $jsBuildBol .= "
                    // Default check all Valid
                    var bolCheckAll = document.getElementsByName('checkall_valid')[0];
                    bolCheckAll.checked = true;
                    checkAll(bolCheckAll);
        ";

  $my_page->addJS(JSP_FORM, "JS_BUILD_BOL", $jsBuildBol);

  $code_bol_js = "
                      function validateBolFields() {

                        var bol_valid_checked = false;

                        var el_valid = document.getElementsByClassName('valid');
                        var el_rejet = document.getElementsByClassName('rejet');

                        var i;
                        for (i = 0; i < el_valid.length; i++) {
                            if (el_valid[i].checked) {
                                bol_valid_checked = true;
                                break;
                            }
                        }
                        for (i = 0; i < el_rejet.length; i++) {
                            if (el_rejet[i].checked) {
                                bol_valid_checked = true;
                                break;
                            }
                        }

                        if (!bol_valid_checked) {
                            msg += '- Veuillez cocher au moins une case de demande \\n';
                            ADFormValid=false;
                        }
                      }
                      validateBolFields();
        ";

  $my_page->addJS(JSP_BEGIN_CHECK, "JS_VALID_BOL", $code_bol_js);

  $my_page->addHTMLExtraCode("espace","<br/>");

  $my_page->addFormButton(1, 1, "btn_process_demande", _("Valider"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("btn_process_demande", BUTP_PROCHAIN_ECRAN, 'Pna-2');
  $my_page->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Pns-2");
  $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

  $my_page->show();
}
else if ($global_nom_ecran == "Pna-2") {
  global $global_id_client,$global_nom_login;

  $erreur = processAutorisationCommandeAttente($_POST, $SESSION_VARS['id_beneficiaire']);

  if ($erreur->errCode == NO_ERR) {

    // Affichage de la confirmation
    $html_msg = new HTML_message("Confirmation autorisation commande");

    if ($erreur->param > 1) {
      $demande_msg = "demandes de derogation de commandes ont été traitées";
    } else {
      $demande_msg = "demande de derogation commande a été traitée";
    }

    $html_msg->setMessage(sprintf(" <br />%s %s !<br /> ", $erreur->param, $demande_msg));

    $html_msg->addButton("BUTTON_OK", 'Pns-2');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    $html_err = new HTML_erreur("Echec lors de la demande autorisation de commande.");

    $err_msg = $error[$erreur->errCode];

    $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

    $html_err->addButton("BUTTON_OK", 'Pns-2');

    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
  ajout_historique(174, NULL, $demande_msg, $global_nom_login, date("r"), NULL);

}

else if ($global_nom_ecran == "Pne-1") {

  require_once 'lib/html/HTML_GEN2.php';
  require_once 'lib/html/FILL_HTML_GEN2.php';
  require_once 'lib/html/HTML_erreur.php';
  require_once 'lib/misc/VariablesGlobales.php';
  require_once 'lib/dbProcedures/agence.php';
  require_once 'lib/dbProcedures/epargne.php';
  require_once 'lib/misc/divers.php';
  require_once 'lib/misc/tableSys.php';

  global $global_id_client;

  // Affichage de la liste des mouvements
  $table = new HTML_TABLE_table(5, TABLE_STYLE_ALTERN);
  $table->set_property("title", "Liste de demandes des commandes autorisé");
  $table->add_cell(new TABLE_cell("N°"));
  $table->add_cell(new TABLE_cell("Numero beneficiaire"));
  $table->add_cell(new TABLE_cell("Utilisateur"));
  $table->add_cell(new TABLE_cell("Date demande"));
  $table->add_cell(new TABLE_cell(""));

  // Get liste autorisation de retrait+
  $id_benef =$SESSION_VARS['id_beneficiaire'];
  $condi = "etat = 2 and id_benef =".$id_benef;
  $listecommande_actif= getDerogationCommande($condi);
  //$listeAutoriseRetrait = getListeRetraitAttente($global_id_client, 2);

  foreach ($listecommande_actif as $id => $autoriseCommande) {

    $id_demande = trim($autoriseCommande["id_derogation"]);
    $beneficiaire = trim($autoriseCommande["id_benef"]);
    $login = $autoriseCommande["login_uti"];
    $date_demande = pg2phpDate($autoriseCommande["date_creation"]);
    $date_modif= pg2phpDate($autoriseCommande["date_modif"]);
    $id_commande= $autoriseCommande["id_commande"];

    $prochain_ecran = "Pnc-2";


    $table->add_cell(new TABLE_cell($id_demande));
    $table->add_cell(new TABLE_cell($beneficiaire));
    $table->add_cell(new TABLE_cell($login));
    $table->add_cell(new TABLE_cell($date_demande));
    $table->add_cell(new TABLE_cell("<a href=" . $PHP_SELF . "?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=" . $prochain_ecran . "&id_dem=" . $id_demande . "&id_benef=".$beneficiaire."&id_commande=".$id_commande.">Effectuer la commande</a>"));
    $table->set_row_property("height", "35px");
  }
  $message = "Effectuer derogation";
  ajout_historique(175, NULL, $message, $global_nom_login, date("r"), NULL);

  // Génération du tableau des demandes de retrait
  echo $table->gen_HTML();


}

else if ($global_nom_ecran == "Pnn-1") {
  global $global_id_benef, $global_id_client;
  $Myform = new HTML_GEN2(_("Annulation commande"));
  if ($global_id_guichet > 0){
    $condition_annulation = "etat_commande in (1) and (date_creation between date(now()) and date(now()) + interval '1 day') and id_benef = ".$SESSION_VARS['id_beneficiaire'];
  }else{
    $condition_annulation = "etat_commande in (6,7) and (date_creation between date(now()) and date(now()) + interval '1 day') and id_benef = ".$SESSION_VARS['id_beneficiaire'];
  }

  $commande_annul = getCommande($condition_annulation);
  $choix = array();
  if (isset($commande_annul)) {
    foreach($commande_annul as $key=>$value) $choix[$key] = $value["id_commande"];
  };

  $Myform->addField("id_commande", _("Numéro de commande"), TYPC_LSB);
  $Myform->setFieldProperties("id_commande",FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties('id_commande', FIELDP_ADD_CHOICES, $choix);



  $Myform->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pnn-2");

  $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");
  $Myform->buildHTML();
  echo $Myform->getHTML();
}

else if ($global_nom_ecran == "Pnn-2") {
  global $global_id_benef, $global_id_client;
  $get_id_commande = $_POST['id_commande'];

  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Annulation commmande"));
  $condi = "id_commande= ".$get_id_commande;
  $commande_actif= getCommande($condi);


  while (list($key, $COM) = each($commande_actif)) {
    $id_comm = $COM['id_commande'];
    $SESSION_VARS['id_commande_annulation']= $COM['id_commande'];
    // select depuis la table année/saison
    $condi2 = "id_saison = ".$COM['id_saison'];
    $saison_cultu_acutelle = getDetailSaisonCultu($condi2);

    $condition_annee_agri_annul = "id_annee =".$saison_cultu_acutelle['id_annee'];
    $annee_agri_actuelle =getRangeDateAnneeAgri($condition_annee_agri_annul);

    $alert_message = "";
    if ($COM['etat_commande']==6){
      $alert_message = sprintf("<font color='red'>Cette Commande a fait object d'une demande de derogation!</font>");
      $msg_annulation = "<table align=\"center\" cellpadding=\"5\" width=\"65% \" border=0 cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
    <tr align=\"center\" ><th></th><th></th><th></th><th></th><th></th><th></th></tr><tr><td align=\"center\"  colspan='6'>".$alert_message."</td></tr></table></br>";
      $myForm->addHTMLExtraCode("msg_annulation", $msg_annulation);
    }

    $myForm->addField("commande".$COM['id_commande'],_("Commande numéro"), TYPC_TXT);
    $myForm->setFieldProperties("commande".$COM['id_commande'], FIELDP_DEFAULT,$COM['id_commande']);
    $myForm->setFieldProperties("commande".$COM['id_commande'], FIELDP_IS_LABEL, true);
    $myForm->addField("saison".$COM['id_commande'],_("Saison"), TYPC_TXT);
    $myForm->setFieldProperties("saison".$COM['id_commande'], FIELDP_DEFAULT, $saison_cultu_acutelle['nom_saison']);
    $myForm->setFieldProperties("saison".$COM['id_commande'], FIELDP_IS_LABEL, true);
    $myForm->addField("mnt_tot".$COM['id_commande'],_("Montant total"), TYPC_MNT);
    $myForm->setFieldProperties("mnt_tot".$COM['id_commande'], FIELDP_DEFAULT, $COM['montant_total']);
    $myForm->setFieldProperties("mnt_tot".$COM['id_commande'], FIELDP_IS_LABEL, true);
    $myForm->addField("mnt_depose".$COM['id_commande'],_("Montant déposé"), TYPC_MNT);
    $myForm->setFieldProperties("mnt_depose".$COM['id_commande'], FIELDP_DEFAULT, $COM['montant_depose']);
    $myForm->setFieldProperties("mnt_depose".$COM['id_commande'], FIELDP_IS_LABEL, true);
    $myForm->addField("etat_comm" . $COM['id_commande'], _("Etat commande"), TYPC_TXT);
    $myForm->setFieldProperties("etat_comm" . $COM['id_commande'], FIELDP_DEFAULT, adb_gettext($adsys['adsys_etat_commande'][$COM['etat_commande']]));
    $myForm->setFieldProperties("etat_comm" . $COM['id_commande'], FIELDP_IS_LABEL, true);

if ($COM['etat_commande'] == 7){
  $montant_libel = "Montant d'avance en attente";
}
else {
  $montant_libel = "Montant deposé";
}
    $xtHTML = "<br /><table align=\"center\" cellpadding=\"5\" width=\"65% \" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
    <tr align=\"center\" bgcolor=\"$colb_tableau\"><th>"._("Produit")."</th><th>"._("Quantité")."</th><th>"._("Prix unitaire")."</th><th>"._($montant_libel)."</th><th>"._("Total")."</th></tr>";
    $condi3="id_commande=".$id_comm;
    $commande_detail_actif =getCommandeDetail($condi3);


    while (list($key1, $DET) = each($commande_detail_actif)) {
      $id_detail = $DET['id_detail'];
      $condi4="id_produit=".$DET['id_produit'];
      $id_prod=$DET['id_produit'];
      $libel_produit = getListeProduitPNSEB($condi4,true);
      if($DET['prix_total']==null){
        $prix_total = "(non renseigné)";
      }else{
        $prix_total = $DET['prix_total'];
      }
      $xtHTML .= "\n<tr bgcolor=\"$color\"><td>".$libel_produit[$id_prod]['libel']."</td><td>".$DET['quantite']."</td><td>".afficheMontant($libel_produit[$id_prod]['prix_unitaire'])."</td><td>".afficheMontant($DET['montant_depose'])."</td><td>".$prix_total."</td></tr>";

    }
    $xtHTML .= "</table><br /><br/><br />";


    $myForm->addHTMLExtraCode("xtHTML".$id_comm, $xtHTML);
  }


  $myForm->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pnn-3");
  $myForm->setFormButtonProperties("butval", BUTP_JS_EVENT, array("onclick" =>
    "if (!confirm('"._("ATTENTION")."\\n "._("Cette operation permet l\'annulation de la commande. \\nPar conséquent, la commande ne sera plus valide.\\nEtes-vous sur de vouloir continuer ? ")."')) return false;"));

  $myForm->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");

  $myForm->buildHTML();
  echo $myForm->getHTML();

}
else if ($global_nom_ecran == "Pnn-3") {
  global $dbHandler, $global_id_guichet, $global_id_agence,$global_id_client,$global_id_benef;

  if (isset($SESSION_VARS['id_commande_annulation']) && $SESSION_VARS['id_commande_annulation'] > 0) {

    $db = $dbHandler->openConnection();

    $id_annulation = "id_commande= ".$SESSION_VARS['id_commande_annulation'];
    $commande_actuel= getCommande($id_annulation);

    //mise a jour etat commande dans ec_commande
    $Fields_commande["etat_commande"] = 5;
    $Fields_commande["date_modif"] = date("Y-m-d");
    $Where_commande["id_commande"] = $SESSION_VARS['id_commande_annulation'];
    $result_update_commande = buildUpdateQuery("ec_commande", $Fields_commande, $Where_commande);
    $result1 = $db->query($result_update_commande);
    if (DB::isError($result1)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $result1 . "\n" . $result1->getMessage());
    }

    //mise a jour etat derogation dans ec_derogation
    $Fields_derogation["etat"] = 3;
    $Fields_derogation["date_modif"] = date("Y-m-d");
    $Fields_derogation["comment"] = "Demande autorisation commande : Rejeté";
    $Where_derogation["id_commande"] = $SESSION_VARS['id_commande_annulation'];
    $result_update_derogation = buildUpdateQuery("ec_derogation", $Fields_derogation, $Where_derogation);
    $result2 = $db->query($result_update_derogation);
    if (DB::isError($result2)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Erreur dans la requête SQL") . " : " . $result2 . "\n" . $result2->getMessage());
    }

    if ($commande_actuel[$SESSION_VARS['id_commande_annulation']]['etat_commande']!=6) {
     if ($commande_actuel[$SESSION_VARS['id_commande_annulation']]['etat_commande'] == 1){
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array(); // copmte comptable produit
        $cptes_substitue["int"] = array(); // compte client

        $condi7 = "id_commande = " . $SESSION_VARS['id_commande_annulation'];
        $commande_detail_ecriture = getCommandeDetail($condi7);


        while (list($key_commande, $DET) = each($commande_detail_ecriture)) {

          $cpte_produit_pnseb = getCompteCptaProdPnseb($DET['id_produit']);
          $cptes_substitue["cpta"]["credit"] = getCompteCptaGui($global_id_guichet);
          $cptes_substitue["cpta"]["debit"] = $cpte_produit_pnseb;
          $infos_sup['autre_libel_ope'] = getIdLibelOperationPNSEB(616);//$DET['id_commande'];
          $libel_ecriture = $DET['id_commande'] . "-" . $SESSION_VARS["id_beneficiaire"];
          $operation = 616;
          $devise = getAgenceDatas($global_id_agence);

          $myErr1 = passageEcrituresComptablesAuto($operation, $DET['montant_depose'], $comptable, $cptes_substitue, $devise['code_devise_reference'], NULL, $libel_ecriture, $infos_sup);
          if ($myErr1->errCode != NO_ERR) {
            $dbHandler->closeConnection(false);
            return $myErr1;
          }

        }

        $infos_his = 'id_benef=' . $SESSION_VARS['id_beneficiaire'] . '- login =' . $global_nom_login . ' - comm= operation annulation commande';
        $myErr2 = ajout_historique(176, null, $infos_his, $global_nom_login, date("r"), $comptable);
        if ($myErr2->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr2;
        }
      }
      else if ($commande_actuel[$SESSION_VARS['id_commande_annulation']]['etat_commande'] == 7){
        $infos_his = 'id_benef=' . $SESSION_VARS['id_beneficiaire'] . '- login =' . $global_nom_login . ' - comm= operation annulation commande en attente';
        $myErr2 = ajout_historique(176, null, $infos_his, $global_nom_login, date("r"), null);
        if ($myErr2->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr2;
        }
      }
    }
    else{
      $infos_his = 'id_benef=' . $SESSION_VARS['id_beneficiaire'] . '- login =' . $global_nom_login . ' - comm= operation annulation commande en attente de derogation';
      $myErr2 = ajout_historique(176, null, $infos_his, $global_nom_login, date("r"), null);
      if ($myErr2->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr2;
      }
    }

    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message("Confirmation de l'annulation de la commande");

    $demande_msg = "Votre commande a été annulée avec succès!";


    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

    $html_msg->addButton("BUTTON_OK", 'Pns-2');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}

else if ($global_nom_ecran == "Pnp-1") {
  global $global_id_benef, $global_client, $global_id_client;

  $Myform = new HTML_GEN2(_("Paiement des commandes"));
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee'];
  $saison = getDetailSaisonCultuAll($whereSaison);
  $saisons_available = '';
  foreach ($saison as $key => $value){
    $saisos_available .= $key.',';
  }
  $saisos_available =  rtrim($saisos_available,",");

  $condition_paiement = "etat_commande =8 and id_benef =".$SESSION_VARS['id_beneficiaire']." AND id_saison IN (".$saisos_available.")";
  $commande_paiement = getCommande($condition_paiement);
  $choix = array();
  if (isset($commande_paiement)) {
    foreach($commande_paiement as $key=>$value) $choix[$key] = $value["id_commande"];
  };

  $Myform->addField("id_commande", _("Numéro de commande"), TYPC_LSB);
  $Myform->setFieldProperties("id_commande",FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties('id_commande', FIELDP_ADD_CHOICES, $choix);



  $Myform->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pnp-2");

  $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");
  $Myform->buildHTML();
  echo $Myform->getHTML();
}

else if ($global_nom_ecran == "Pnp-2") {
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Paiement commande"));
  $condi = " id_commande=" . $id_commande;
  $commande_actif = getCommande($condi);

  $Myform->addHTMLExtraCode("espace" . $id_commande, "<br /><b><p align=center><b>" . sprintf(_("Paiement de la commande N° %s"), $id_commande) . "</b></p>");

  //Tableau des echéances
  $retour = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=6 align=\"left\"><b>" . _("Description de la commande") . "</b></TD>\n";
  $retour .= "</TR>\n";
  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD align=\"center\">" . _("Numéro") . "</TD>\n";
  $retour .= "<TD align=\"center\">" . _("Date création") . "</TD>\n";
  $retour .= "<TD align=\"center\">" . _("Montant commande") . "</TD>\n";
  $retour .= "<TD align=\"center\">" . _("Montant Avance Payé") . "</TD>\n";
  $retour .= "<TD align=\"center\">" . _("Total montant attendu") . "</TD>\n";
  $retour .= "</TR>\n";
  while (list($key, $DET) = each($commande_actif)) {
    $id_commande = $DET['id_commande'];
    $date_comm = $DET['date_creation'];
    $mnt_total = $DET['montant_total'];
    $mnt_depot = $DET['montant_depose'];
    $condi_paiement_payer = "id_commande = ".$id_commande." AND etat_paye = 2";
    $commande_deja_payer = getPaiementDetail($condi_paiement_payer);
    $mnt_deja_paye = 0;
    foreach($commande_deja_payer as $key_deja_paye => $value_deja_paye){
      $mnt_deja_paye += $value_deja_paye['montant_paye'];
    }
    $total_attendu = $mnt_total - $mnt_depot;
    $total_attendu -= $mnt_deja_paye;



    // Affichage
    $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
    $retour .= "<TD align=\"center\">" . $id_commande . "</TD>\n";
    $retour .= "<TD align=\"left\">" . pg2phpDate($date_comm) . "</TD>\n";
    $retour .= "<TD align=\"right\">" . afficheMontant($mnt_total, true) . "</TD>\n";
    $retour .= "<TD align=\"right\">" . afficheMontant($mnt_depot, true) . "</TD>\n";
    $retour .= "<TD align=\"right\">" . afficheMontant($total_attendu, true) . "</TD>\n";
    $retour .= "</TR>\n";
    $retour .= "</TABLE>\n";
  }
  $Myform->addHTMLExtraCode("affichage".$id_commande, $retour);
  $Myform->setHTMLExtraCodeProperties("affichage".$id_commande, HTMP_IN_TABLE, true);

  $condi8="id_commande =".$id_commande." AND etat_paye = 1 order by id_remb asc";
  $paiement_commande = getPaiementDetail($condi8);
  $id_remb_max=0;
  if ($paiement_commande != NULL){
    $remb_tab = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
    $remb_tab .= "<TR bgcolor=\"$colb_tableau\">\n";
    $remb_tab .= "<TD colspan=6 align=\"left\"><b>" . _("Paiement en attente") . "</b></TD>\n";
    $remb_tab .= "</TR>\n";
    $remb_tab .= "<TR bgcolor=\"$colb_tableau\">\n";
    $remb_tab .= "<TD align=\"center\">" . _("Numéro paiement") . "</TD>\n";
    $remb_tab .= "<TD align=\"center\">" . _("Date paiement") . "</TD>\n";
    $remb_tab .= "<TD align=\"center\">" . _("Produit") . "</TD>\n";
    $remb_tab .= "<TD align=\"center\">" . _("Quantité") . "</TD>\n";
    $remb_tab .= "<TD align=\"center\">" . _("Montant a payer") . "</TD>\n";
    $remb_tab .= "</TR>\n";

    $row_counter = 0;
    $mnt_a_payer = 0;
    $mnt_ = $total_attendu;
    while (list($key1, $DET1) = each($paiement_commande)) {
      $row_counter++;
      $id_remb = $DET1['id_remb'];
      $date_paiement = $DET1['date_creation'];
      $mnt_paye = $DET1['montant_paye'];
      //$mnt_restant = $total_attendu - $mnt_paye;
      $id_remb_max = $DET1['id_remb'];
      $qtite_paye = $DET1['qtite_paye'];
      $condi_commande_detail = "id_detail = ".$DET1['id_detail_commande'];
      $detail_commande = getCommandeDetail($condi_commande_detail);
      foreach($detail_commande as $key_detail_commande => $value_detail_commande){
        $mnt_paye = $DET1['montant_paye'];
        $mnt_a_payer += $mnt_paye;
        $condi_detail_produit = "id_produit = ".$value_detail_commande['id_produit'];
        $detail_produit = getDetailsProduits($condi_detail_produit);
        $nom_produit = $detail_produit['libel'];
      }

      // Affichage
      $remb_tab .= "<TR bgcolor=\"$colb_tableau\">\n";
      $remb_tab .= "<TD align=\"center\">" . $id_remb . "</TD>\n";
      $remb_tab .= "<TD align=\"left\">" . pg2phpDate($date_paiement) . "</TD>\n";
      $remb_tab .= "<TD align=\"left\">" .$nom_produit. "</TD>\n";
      $remb_tab .= "<TD align=\"left\">" .$qtite_paye. "</TD>\n";
      $remb_tab .= "<TD align=\"left\">" . afficheMontant($mnt_paye, true) . "</TD>\n";
      $remb_tab .= "</TR>\n";

    }
  }
  else {
    $Myform->addHTMLExtraCode("espace" . $id_remb, "<br /><b><p align=center><b>" . sprintf(_("Aucun paiement en attente est associé à la commande %s"), $id_commande) . "</b></p>");
  }
  $remb_tab .= "</TABLE>\n";
  $Myform->addHTMLExtraCode("remb", $remb_tab);
  $Myform->setHTMLExtraCodeProperties("remb", HTMP_IN_TABLE, true);

  if ($paiement_commande != null) {
    $Myform->addHTMLExtraCode("remb" . $id_remb, "<br /><b><p align=center><b>" . sprintf(_("Paiement des soldes en cours")) . "</b></p>");
    $Myform->addField("num_remb", _("Numero remboursement"), TYPC_TXT, $id_remb);
    $Myform->setFieldProperties("num_remb", FIELDP_IS_LABEL, true);

    $Myform->addField("date_remb", _("Date remboursement"), TYPC_DTG);
    $Myform->setFieldProperties("date_remb", FIELDP_DEFAULT, date("d/m/Y"));
    $Myform->setFieldProperties("date_remb", FIELDP_IS_LABEL, true);

    $Myform->addField("mnt_attendu", _("Montant attendu"), TYPC_MNT, $mnt_a_payer);

    //$Myform->addField("mnt_attendu", _("Montant attendu"), TYPC_MNT,$mnt_);
    $Myform->setFieldProperties("mnt_attendu", FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("mnt_attendu", FIELDP_IS_LABEL, true);

    $Myform->addField("mnt_remb", _("Montant du paiement"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_remb", FIELDP_IS_REQUIRED, true);

    $ChkJS_mnt_paye = "\t\tif (recupMontant(document.ADForm.mnt_remb.value) > recupMontant(document.ADForm.mnt_attendu.value))";
    $ChkJS_mnt_paye .= "{\nmsg += '- " . _("Le montant saisi est superieur au montant du paiement") . "\\n'; ADFormValid=false;};\n";
    $ChkJS_mnt_paye .= "\t\tif (recupMontant(document.ADForm.mnt_remb.value) < recupMontant(document.ADForm.mnt_attendu.value))";
    $ChkJS_mnt_paye .= "{\nmsg += '- " . _("Le montant saisi est inférieur au montant du paiement.") . "\\n'; ADFormValid=false;};\n";
    $Myform->addJS(JSP_BEGIN_CHECK, "JS_check_mnt_paye", $ChkJS_mnt_paye);

    $SESSION_VARS['id_comm'] = $id_commande;
    $SESSION_VARS['id_remb'] = $id_remb_max;

    $Myform->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
    $Myform->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pnp-3");
  }

  $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

else if ($global_nom_ecran == "Pnp-3") {
  $Myform = new HTML_GEN2(_("Confirmation du montant à payer"));

  $Myform->addField("mnt_remb", _("Montant à payer"), TYPC_MNT, recupMontant($mnt_remb));
  //$Myform->setFieldProperties("mnt_remb", FIELDP_DEFAULT,$mnt_remb);
  $Myform->setFieldProperties("mnt_remb", FIELDP_IS_LABEL, true);

  $Myform->addField("mnt_remb_conf", _("Confirmation du montant"), TYPC_MNT);
  $Myform->setFieldProperties("mnt_remb_conf", FIELDP_IS_REQUIRED, true);


  $ChkJS_mnt = "\t\tif (recupMontant(document.ADForm.mnt_remb_conf.value) != recupMontant(document.ADForm.mnt_remb.value))";
  $ChkJS_mnt .= "{\nmsg += '- "._("Le montant saisi ne correspond pas au montant du paiement")."\\n'; ADFormValid=false;};\n";
  $Myform->addJS(JSP_BEGIN_CHECK, "JS_check_mnt",$ChkJS_mnt);

  $Myform->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pnp-4");

  $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");


  $Myform->buildHTML();
  echo $Myform->getHTML();
}
else if ($global_nom_ecran == "Pnp-4") {
  global $dbHandler,$global_id_agence,$global_nom_login;


  if (isset($SESSION_VARS['id_comm'])) {
    $id_commande_paiement = $SESSION_VARS['id_comm'];
  }
  $Myform = new HTML_GEN2();
  $Myform->setTitle(_("Confirmation de paiement des soldes"));
  $db = $dbHandler->openConnection();
  $operation=617;
  $devise = getAgenceDatas($global_id_agence);
  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array(); // copmte comptable produit
  $cptes_substitue["int"] = array(); // compte client
  $connection = false;
  $condi_detail = "id_commande = ".$SESSION_VARS['id_comm']." AND etat_paye = 1";
  $details_commande = getPaiementDetail($condi_detail);
  foreach($details_commande as $key_detail_paiement => $value_detail_paiement){
    $DATA_UPDATE_DETAIL = array(
      "etat_paye" => 2
    );
    $DATA_UPDATE_DETAIL_CONDITION = array(
      "id_commande" => $SESSION_VARS['id_comm']
    );
    //$db2 = $dbHandler->openConnection();
    $result = executeQuery($db, buildUpdateQuery("ec_paiement_commande", $DATA_UPDATE_DETAIL, $DATA_UPDATE_DETAIL_CONDITION));
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }else{
      $connection = true;
    }
    $condi_detail = "id_detail =".$value_detail_paiement['id_detail_commande'];
    $commande_detail = getCommandeDetail($condi_detail);
    foreach($commande_detail as $key_detail => $value_detail){
      $id_produit = $value_detail['id_produit'];
    }
    $cpte_produit_pnseb = getCompteCptaProdPnseb($id_produit);
    $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($global_id_guichet);
    $cptes_substitue["cpta"]["credit"] = $cpte_produit_pnseb;
    $infos_sup['autre_libel_ope'] = getIdLibelOperationPNSEB($operation);//$id_commande_paiement;

    $myErr =passageEcrituresComptablesAuto($operation, $value_detail_paiement['montant_paye'], $comptable, $cptes_substitue, $devise['code_devise_reference'], NULL, $SESSION_VARS['id_comm'], $infos_sup);

    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
  }

  // Si tout la commande a ete payer = passage de la commande en etat Soldé??
  $condi11 ="id_commande = ".$SESSION_VARS['id_comm'];
  $total_paiement_commande= getPaiementDetail($condi11);
  $total_payer = 0;
  $total_reste_a_payer = 0;
  if (($total_paiement_commande != null) ) {
    while (list($key2, $DET2) = each($total_paiement_commande)) {
      $total_payer += $DET2['montant_paye'];
    }

    $commande_total = getCommande($condi11);
    $commande_total[$id_commande_paiement]['montant_total'];
    $commande_total[$id_commande_paiement]['montant_depose'];
    $total_reste_a_payer = $commande_total[$id_commande_paiement]['montant_total'] -  $commande_total[$id_commande_paiement]['montant_depose'];
  }
  if ($total_reste_a_payer - $total_payer ==0 ){
    $sql = "update ec_commande set etat_commande = 3 where id_commande = $id_commande_paiement";
    $result_trans=$db->query($sql);
    if (DB::isError($result_trans)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result_trans->getMessage());
    }
  }

  $infos_his = 'id_comande=' .$SESSION_VARS['id_comm'] . ' - login =' . $global_nom_login . ' -  comm= operation paiement commande';
  $myErr = ajout_historique(173, null, $infos_his, $global_nom_login, date("r"), $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  if ($connection == true) {
    $dbHandler->closeConnection(true);

    $html_msg = new HTML_message("Confirmation de paiement de la commande");
    $demande_msg = "Votre paiement a été effectué avec succès!";


    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

    $html_msg->addButton("BUTTON_OK", 'Pns-2');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;

    $condi_benef = "id_beneficiaire =".$SESSION_VARS['id_beneficiaire'];

    $mnt_restant_payer = 0;
    $now = date("Y-m-d");
    $nom_prenom_benef = getDetailsBeneficiaire($condi_benef);

    print_recu_paiement_commande($SESSION_VARS['id_beneficiaire'], $nom_prenom_benef['nom_prenom'], $SESSION_VARS['id_comm'], $mnt_remb_conf, $mnt_restant_payer, $now, $global_nom_login);
  }


/*  if (isset($SESSION_VARS['id_comm'])) {
    $id_commande_paiement = $SESSION_VARS['id_comm'];
  }

  $mnt_remb_conf = recupMontant($mnt_remb_conf);

  /*$Myform = new HTML_GEN2();
  $Myform->setTitle(_("Confirmation de paiement des soldes"));*/

 /* $db = $dbHandler->openConnection();

  $operation=617;
  $devise = getAgenceDatas($global_id_agence);
  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array(); // copmte comptable produit
  $cptes_substitue["int"] = array(); // compte client

  $mnt_injecter_paye = $mnt_remb_conf;
  $condi9 = "id_commande = " . $id_commande_paiement;
  $details_comm = getCommandeDetail($condi9);
  $counter_array=0;
  $DATAS = array();
  while (list($key, $DET) = each($details_comm)) {
    $id_produit = $DET['id_produit'];
    $id_detail = $DET['id_detail'];
    $mnt_total_attendu = $DET['prix_total'];
    $mnt_total_deposer = $DET['montant_depose'];
    $condi10="id_commande = ".$id_commande_paiement." and id_detail_commande = ".$id_detail;

    $detail_paiement = getPaiementDetail($condi10);
    $somme_payer = 0;
    $mnt_deduit_a_payer=0;
    if (($detail_paiement != null && $mnt_remb_conf >0) ){
      while (list($key1, $DET1) = each($detail_paiement)) {
        $somme_payer += $DET1['montant_paye'];
      }

      $mnt_total_attendu -= $mnt_total_deposer;
      $mnt_deduit_a_payer = $mnt_total_attendu - $somme_payer;
      if ($mnt_deduit_a_payer >0) {
        $counter_array++;
        if ($mnt_remb_conf - $mnt_deduit_a_payer > 0) {
          $DATAS[$counter_array]['montant_paye'] = $mnt_deduit_a_payer;
          $mnt_remb_conf -=$mnt_deduit_a_payer;
        } else {
          $DATAS[$counter_array]['montant_paye'] = $mnt_remb_conf;
          $mnt_remb_conf -=$mnt_remb_conf;
        }
        //$id_his = getNextValIdHis();
        $DATAS[$counter_array]['id_commande'] = $id_commande_paiement;
        $DATAS[$counter_array]['id_detail_commande'] = $id_detail;
        $DATAS[$counter_array]['id_remb'] = $SESSION_VARS['id_remb'];
        $DATAS[$counter_array]['type_paiement'] = 1;
        $DATAS[$counter_array]['date_creation'] = date("d/m/Y");
        //$DATAS[$counter_array]['id_his'] = $id_his;
        $DATAS[$counter_array]['id_ag'] = $global_id_agence;
        $cpte_produit_pnseb = getCompteCptaProdPnseb($id_produit);
        $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($global_id_guichet);
        $cptes_substitue["cpta"]["credit"] = $cpte_produit_pnseb;
        $infos_sup['autre_libel_ope'] = getIdLibelOperationPNSEB($operation);//$id_commande_paiement;

        $myErr =passageEcrituresComptablesAuto($operation, $DATAS[$counter_array]['montant_paye'], $comptable, $cptes_substitue, $devise['code_devise_reference'], NULL, $id_commande_paiement, $infos_sup);
        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }
      }

    }
    elseif($mnt_remb_conf >0 ) {
      $counter_array++;
      $mnt_restant_total = $mnt_total_attendu - $mnt_total_deposer;
      if ($mnt_remb_conf - $mnt_restant_total >= 0 ) {
        $mnt_remb_conf -= $mnt_restant_total;
        $DATAS[$counter_array]['montant_paye'] = $mnt_restant_total;
      }else {
        $last_paiement = $mnt_remb_conf;
        $DATAS[$counter_array]['montant_paye'] = $last_paiement;
        $mnt_remb_conf -= $mnt_remb_conf;
      }
      //$id_his = getNextValIdHis();
      $DATAS[$counter_array]['id_commande'] = $id_commande_paiement;
      $DATAS[$counter_array]['id_detail_commande'] = $id_detail;
      $DATAS[$counter_array]['id_remb'] = $SESSION_VARS['id_remb'];
      $DATAS[$counter_array]['type_paiement'] = 1;
      $DATAS[$counter_array]['date_creation'] = date("d/m/Y");
      //$DATAS[$counter_array]['id_his'] = $id_his;
      $DATAS[$counter_array]['id_ag'] = $global_id_agence;

      $cpte_produit_pnseb = getCompteCptaProdPnseb($id_produit);
      $cptes_substitue["cpta"]["debit"] = getCompteCptaGui($global_id_guichet);
      $cptes_substitue["cpta"]["credit"] = $cpte_produit_pnseb;
      $infos_sup['autre_libel_ope'] = getIdLibelOperationPNSEB($operation);//$id_commande_paiement;
      $libelle_ecriture = $id_commande_paiement."-".$SESSION_VARS["id_beneficiaire"];

      $myErr =passageEcrituresComptablesAuto($operation, $DATAS[$counter_array]['montant_paye'], $comptable, $cptes_substitue, $devise['code_devise_reference'], NULL, $libelle_ecriture, $infos_sup);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
  }

  $insert_paiement = InsertPaiement($DATAS,$counter_array);

  $condi11 ="id_commande = ".$id_commande_paiement;
  $total_paiement_commande= getPaiementDetail($condi11);
  $total_payer = 0;
  $total_reste_a_payer = 0;
  if (($total_paiement_commande != null) ) {
    while (list($key2, $DET2) = each($total_paiement_commande)) {
      $total_payer += $DET2['montant_paye'];
    }

    $commande_total = getCommande($condi11);
    $commande_total[$id_commande_paiement]['montant_total'];
    $commande_total[$id_commande_paiement]['montant_depose'];
    $total_reste_a_payer = $commande_total[$id_commande_paiement]['montant_total'] -  $commande_total[$id_commande_paiement]['montant_depose'];
  }
  if ($total_reste_a_payer - $total_payer ==0 ){
    $sql = "update ec_commande set etat_commande = 3 where id_commande = $id_commande_paiement";
    $result_trans=$db->query($sql);
    if (DB::isError($result_trans)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result_trans->getMessage());
    }
  }
  $infos_his = 'id_comande=' .$id_commande_paiement . ' - login =' . $global_nom_login . ' -  comm= operation paiement commande';
  $myErr = ajout_historique(173, null, $infos_his, $global_nom_login, date("r"), $comptable);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    return $myErr;
  }

  $dbHandler->closeConnection(true);

  $html_msg = new HTML_message("Confirmation de paiement de la commande");
  $demande_msg = "Votre paiement a été effectué avec succès!";


  $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

  $html_msg->addButton("BUTTON_OK", 'Pns-2');
  $html_msg->buildHTML();
  echo $html_msg->HTML_code;

  $condi13['id_beneficiaire']=$SESSION_VARS['id_beneficiaire'];

  $mnt_restant_payer =$total_reste_a_payer - $total_payer;
  $now = date("Y-m-d");
  $nom_prenom_benef = getMatchedBeneficiaire($condi13);
  print_recu_paiement_commande($SESSION_VARS['id_beneficiaire'],$nom_prenom_benef[0]['nom_prenom'],$id_commande_paiement,$mnt_injecter_paye, $mnt_restant_payer ,$now, $global_nom_login);
*/
}




// Visualisation transaction et visualisation toutes transactions
////////////////////////  Pnt-1 /////////////////////////////////

else if (($global_nom_ecran == "Pnt-1")) {
  $MyPage = new HTML_GEN2(_("Critères de recherche"));

  //Champs login
  if ($global_nom_ecran != "Vgu-1") {
    $MyPage->addTableRefField("login", _("Login ayant exécuté la fonction"), "ad_log");
    $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_TOUS, true);
  }

  // Multi agence elements
  unset($adsys["adsys_fonction_systeme"][92],$adsys["adsys_fonction_systeme"][93], $adsys["adsys_fonction_systeme"][193],$adsys["adsys_fonction_systeme"][194]);

  // Récupere la liste globale des fonctions dans $adsys
  $liste_globalFonctions = $adsys["adsys_fonction_systeme"];
  asort($liste_globalFonctions);

  // Exclure les fonctions autre que Fonctions PNSEB-FENACOBU dans $adsys
  foreach ($liste_globalFonctions as $fonction => $libelFonction) {
    if ($fonction == 172 || $fonction == 173 || $fonction == 174 || $fonction == 175 || $fonction == 176 || $fonction == 177 || $fonction == 178 || $fonction == 293 || $fonction == 294 || $fonction == 295){
      // Do Nothing
    }
    else{
      unset($adsys["adsys_fonction_systeme"][$fonction]);
    }
  }

  // Récupère la liste des fonctions PNSEB-FENACOBU dans $adsys
  $liste_fonctions = $adsys["adsys_fonction_systeme"];
  asort($liste_fonctions);
  $choiceOrder = array_keys($liste_fonctions);

  //Champs type de fonction, à classer dans l'ordre alphabétique
  $MyPage->addTableRefField("num_fonction", "Fonction", "adsys_fonction_systeme");

  $MyPage->setFieldProperties("num_fonction", FIELDP_ORDER_CHOICES, $choiceOrder);
  $MyPage->setFieldProperties("num_fonction", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("num_fonction", FIELDP_HAS_CHOICE_TOUS, true);

  //Uniquement transactions financières?
  $MyPage->addField("trans_fin", _("Uniquement les transactions financières ?"), TYPC_BOL);

  //Champs client
  $MyPage->addField("num_beneficiaire", _("Numéro bénéficiaire"), TYPC_INT);
  $MyPage->addLink("num_beneficiaire", "rechercher", _("Rechercher"), "#");
  $MyPage->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('../modules/clients/rech_beneficiaire.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_beneficiaire', '"._("Recherche")."');return false;"));

  //Champs date début
  $MyPage->addField("date_min", _("Date min"), TYPC_DTE);

  //Champs date fin
  $MyPage->addField("date_max", _("Date max"), TYPC_DTE);

  //Champs n° transaction min[B
  $MyPage->addField("trans_min", _("N° transaction min"), TYPC_INT);

  //Champs n° transaction max
  $MyPage->addField("trans_max", _("N° transaction max"), TYPC_INT);

  //Boutons
  $MyPage->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pnt-2");
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Pns-1");


  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

////////////////////////  Pnt-2 /////////////////////////////////

else if (($global_nom_ecran == "Pnt-2")) {
  //if ($global_nom_ecran == "Pnt-2") $login = $global_nom_login;
  if ($login == '0') $login = NULL;

  if ($num_fonction == 0) $num_fonction = NULL;
  if (($num_beneficiaire <= 0) || ($num_beneficiaire == "")) $client = NULL;
  if ($date_min == "") $date_min = NULL;
  if ($date_max == "") $date_max = NULL;
  if ($trans_min == "") $trans_min = NULL;
  if ($trans_max == "") $trans_max = NULL;
  if (isset($trans_fin)) $trans_fin = true;
  else $trans_fin = false;
  $SESSION_VARS['criteres'] = array();
  $SESSION_VARS['criteres']['login'] = $login;
  $SESSION_VARS['criteres']['num_fonction'] = $num_fonction;
  $SESSION_VARS['criteres']['num_beneficiaire'] = $num_beneficiaire;
  $SESSION_VARS['criteres']['date_min'] = $date_min;
  $SESSION_VARS['criteres']['date_max'] = $date_max;
  $SESSION_VARS['criteres']['trans_min'] = $trans_min;
  $SESSION_VARS['criteres']['trans_max'] = $trans_max;
  $SESSION_VARS['criteres']['trans_fin'] = $trans_fin;

  $nombre = count_transactions($login, $num_fonction, $num_beneficiaire, $date_min, $date_max, $trans_min, $trans_max, $trans_fin);
  if ($nombre > 300) {

    $MyPage = new HTML_erreur(_("Trop de correspondances"));
    $MyPage->setMessage(sprintf(_("La recherche a renvoyé %s résultats; veuillez affiner vos critères de recherche ou imprimer."),$nombre));
    switch ($global_nom_ecran) {
      case 'Pnt-2':
        $nextScreen = "Pnt-1";
        $printScreen = "Pnt-3";
        break;
      default:
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran non-reconnu ici"
    }
    $MyPage->addButton(BUTTON_OK, $nextScreen);
    $MyPage->addCustomButton("print", _("Imprimer"), $printScreen, TYPB_SUBMIT);
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;

  } else {
    $resultat = get_transactions($login, $num_fonction, $num_beneficiaire, $date_min, $date_max, $trans_min, $trans_max, $trans_fin);

    $html = "<h1 align=\"center\">"._("Résultat recherche")."</h1><br><br>\n";
    $html .= "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\" onsubmit=\"return ADFormValid;\">\n";
    $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    //Ligne titre
    $html .= "<TR bgcolor=$colb_tableau>";

    $html .= "<TD><b>n°</b></TD><TD align=\"center\"><b>"._("Date")."</b></TD><TD align=\"center\"><b>"._("Heure")."</b></TD><TD align=\"center\"><b>"._("Fonction")."</b></TD><TD align=\"center\"><b>"._("Login")."</b></TD><TD align=\"center\"><b>"._("N° bénéficiaire")."</b></TD></TR>\n";

    $SESSION_VARS['id_his'] = array();
    reset($resultat);
    while (list(,$value) = each($resultat)) { //Pour chaque résultat
      //On alterne la couleur de fond
      if ($a) $color = $colb_tableau;
      else $color = $colb_tableau_altern;
      $a = !$a;
      $html .= "<TR bgcolor=$color>\n";

      //n°
      // FIXME/TF Aaaargh quelle horreur !
      if (($value['trans_fin']) || ($adsys["adsys_fonction_systeme"][$value['type_fonction']]==_('Ajustement du solde d\'un compte')) || ($value["id_his_ext"] != ''))
        $html .= "<TD><A href=# onclick=\"OpenBrwXY('$http_prefix/lib/html/detail_transaction_engrais_chimiques.php?m_agc=".$_REQUEST['m_agc']."&id_transaction=".$value['id_his']."','', 800, 600);\">".$value['id_his']."</A></TD>";
      else $html .= "<TD>".$value['id_his']."</TD>";

      //Date
      $html .= "<TD>".pg2phpDate($value['date'])."</TD>";

      //Heure
      $html .= "<TD>".pg2phpHeure($value['date'])."</TD>";

      //Fonction
      $html .= "<TD>".adb_gettext($adsys["adsys_fonction_systeme"][$value['type_fonction']]);
      $html .= "</TD>\n";

      //Login
      $html .= "<TD>".$value['login']."</TD>\n";

      //N° beneficiaire
      $val_benef = explode("-",$value['info_ecriture']);
      if($value['type_fonction']==92 || $value['type_fonction']==93)
      {
        if (trim($value['infos'])!='') {
          $html .= "<TD align=\"center\">".trim($value['infos'])."</TD>\n";
        } else {
          $html .= "<TD></TD>\n";
        }
      }
      else
      {
        if ($val_benef[1] > 0) {
          $html .= "<TD align=\"center\">".sprintf("%06d", $val_benef[1])."</TD>\n";
        } else {
          $html .= "<TD></TD>\n";
        }
      }

      $html .= "</TR>\n";

      array_push($SESSION_VARS['id_his'], $value['id_his']);
    }

    $html .= "<TR bgcolor=$colb_tableau><TD colspan=7 align=\"center\">\n";

    //Boutons
    $html .= "<TABLE align=\"center\"><TR>";

    /*if ($global_nom_ecran == "Vgu-2") {
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Précédent")."\" onclick=\"ADFormValid = true; assign('Vgu-1');\"></TD>";
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Imprimer détails")."\" onclick=\"ADFormValid=true; assign('Vgu-3');\"></TD>";
    }*/
    if ($global_nom_ecran == "Pnt-2") {
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Précédent")."\" onclick=\"ADFormValid = true; assign('Pnt-1');\"></TD>";
      $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Imprimer détails")."\" onclick=\"ADFormValid=true; assign('Pnt-3');\"></TD>";
    }
    $html .= "<TD><INPUT TYPE=\"submit\" VALUE=\""._("Retour menu")."\" onclick=\"ADFormValid=true; assign('Pns-1');\"></TD>";

    $html .= "</TR></TABLE>\n";

    $html .= "</TD></TR></TABLE>\n";
    $html .= "<INPUT TYPE=\"hidden\" NAME=\"prochain_ecran\"><INPUT type=\"hidden\" id=\"m_agc\" name=\"m_agc\"></FORM>\n";

    echo $html;
  }
}

////////////////////////  Pnt-3 /////////////////////////////////

else if ($global_nom_ecran == "Pnt-3") {
  $login = $SESSION_VARS['criteres']['login'];
  $num_fonction = $SESSION_VARS['criteres']['num_fonction'];
  $num_beneficiaire = $SESSION_VARS['criteres']['num_beneficiaire'];
  $date_min = $SESSION_VARS['criteres']['date_min'];
  $date_max = $SESSION_VARS['criteres']['date_max'];
  $trans_min = $SESSION_VARS['criteres']['trans_min'];
  $trans_max = $SESSION_VARS['criteres']['trans_max'];
  $trans_fin = $SESSION_VARS['criteres']['trans_fin'];
  $criteres = array (
    _("Login") => $login,
    _("Fonction") => $adsys["adsys_fonction_systeme"][$num_fonction],
    _("Numéro Bénéficiaire") => $num_beneficiaire,
    _("Date min") => date($date_min),
    _("Date max") => date($date_max),
    _("N° transaction min") => $trans_min,
    _("N° transaction max") => $trans_max
  );
  // Infos sur les transactions
  $DATAS = get_transactions_details($login, $num_fonction, $num_beneficiaire, $date_min, $date_max, $trans_min, $trans_max, $trans_fin);
  $xml = xml_detail_transactions_ec($DATAS, $criteres); //Génération du code XML
  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'detail_transactions_ec.xslt'); //Génération du XSL-FO et du PDF

  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html("Pns-1", $fichier_pdf);

}
/*}}}*/

////////////////////////  Pvc-1 /////////////////////////////////
else if ($global_nom_ecran == "Pvc-1") {

  $table = new HTML_TABLE_table(5, TABLE_STYLE_ALTERN);
  $table->set_property("title", "Liste des commandes en attente");
  $table->add_cell(new TABLE_cell("N°"));
  $table->add_cell(new TABLE_cell("Numero beneficiaire"));
  $table->add_cell(new TABLE_cell("Montant avance"));
  $table->add_cell(new TABLE_cell("Date demande"));
  $table->add_cell(new TABLE_cell(""));

  // Get liste commande en attente
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $saison = getDetailSaisonCultu($whereSaison);
  $id_benef =$SESSION_VARS['id_beneficiaire'];
  $condi = "etat_commande = 7 and id_benef =".$id_benef." AND id_saison =".$saison["id_saison"];
  $listecommande_attente= getCommande($condi);
  //$listeAutoriseRetrait = getListeRetraitAttente($global_id_client, 2);

  foreach ($listecommande_attente as $id => $CommandeAttente) {

    $id_commande = trim($CommandeAttente["id_commande"]);
    $where_benef = "id_beneficiaire = ".$id_benef;
    $details_benef = getDetailsBeneficiaire($where_benef);
    $id_benef = $id_benef;
    $beneficiaire = trim($details_benef['nom_prenom']);
    $montant_depose = afficheMontant($CommandeAttente["montant_depose"]);
    $date_demande = pg2phpDate($CommandeAttente["date_creation"]);
    $date_modif= pg2phpDate($CommandeAttente["date_modif"]);
    //$id_commande= $CommandeAttente["id_commande"];

    $prochain_ecran = "Pvc-2";


    $table->add_cell(new TABLE_cell($id_commande));
    $table->add_cell(new TABLE_cell($beneficiaire));
    $table->add_cell(new TABLE_cell($montant_depose));
    $table->add_cell(new TABLE_cell($date_demande));
    $table->add_cell(new TABLE_cell("<a href=" . $PHP_SELF . "?m_agc=" . $_REQUEST['m_agc'] ."&prochain_ecran=" . $prochain_ecran ."&id_benef=".$id_benef."&id_commande=".$id_commande.">Effectuer la commande</a>"));
    $table->set_row_property("height", "35px");
  }
  //$message = "Effectuer derogation";
  //ajout_historique(175, NULL, $message, $global_nom_login, date("r"), NULL);

  // Génération du tableau des demandes de retrait
  echo $table->gen_HTML();

}
/*}}}*/


////////////////////////  Pvc-1 /////////////////////////////////
else if ($global_nom_ecran == "Pvc-2") {
  unset($SESSION_VARS['id_commande']);
  if (isset($_GET['id_commande']) && $_GET['id_commande'] > 0) {
    //$SESSION_VARS['demande_derogation'] = "false";
    $data_commande =getCommande("id_commande=".$_GET['id_commande']);
    while (list($key, $COM) = each($data_commande)) {
      $id_comm = $COM['id_commande'];
      $html = new HTML_GEN2(_("Confirmation du montant à déposer"));

      $html->addField("mnt_total", _("Montant à payer"), TYPC_MNT);
      $html->setFieldProperties("mnt_total", FIELDP_DEFAULT, $COM['montant_depose']);
      $html->setFieldProperties("mnt_total", FIELDP_IS_LABEL, true);

      $html->addField("mnt_deposer", _("Confirmation du montant"), TYPC_MNT);
      $html->setFieldProperties("mnt_deposer", FIELDP_IS_REQUIRED, true);

      $SESSION_VARS['id_commande'] = $_GET['id_commande'];
    }
  }
  $ChkJSCheckMontant = "\t\tif (recupMontant(document.ADForm.mnt_deposer.value) != recupMontant(document.ADForm.mnt_total.value))";
  $ChkJSCheckMontant .= "{\nmsg += '- "._("Le montant saisi ne correspond pas au montant de la commande")."\\n'; ADFormValid=false;};\n";
  $html->addJS(JSP_BEGIN_CHECK, "JS100",$ChkJSCheckMontant);

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
  $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $SESSION_VARS['envoi'] = 0;
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Pvc-3');
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Pns-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Pns-2');

  $html->buildHTML();
  echo $html->getHTML();
}

else if ($global_nom_ecran == 'Pvc-3'){
  global $dbHandler,$global_id_agence,$global_nom_login;

  $operation=615;
  $comptable = array();
  $cptes_substitue = array();
  $cptes_substitue["cpta"] = array(); // copmte comptable produit
  $cptes_substitue["int"] = array(); // compte client
  $engrais = 0;
  $amendement = 0;

  $db = $dbHandler ->openConnection();

  if (isset($SESSION_VARS['id_commande']) && $SESSION_VARS['id_commande'] > 0){

    $Fields["etat_commande"] = 1;
    $Fields["date_modif"] = date("Y-m-d");
    $Where["id_commande"] = $SESSION_VARS['id_commande'];
    $db1 = $dbHandler->openConnection();
    $result1 = executeQuery($db1,buildUpdateQuery("ec_commande", $Fields, $Where));
    if (DB::isError($result1)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result1->getMessage());
    }else{
      $dbHandler->closeConnection(true);
    }

    $condi6 = "id_commande = ".$SESSION_VARS['id_commande'];
    $commande_detail_ecriture =getCommandeDetail($condi6);

    while (list($key_commande, $DET) = each($commande_detail_ecriture)) {
      $id_detail = $DET['id_detail'];
      $condi4 = "id_produit=" . $DET['id_produit'];
      $id_prod = $DET['id_produit'];
      //$libel_produit = getListeProduitPNSEB($condi4, true);

      $cptes_substitue["cpta"]["debit"]= getCompteCptaGui($global_id_guichet);
      $cpte_produit_pnseb = getCompteCptaProdPnseb($DET['id_produit']);
      $cptes_substitue["cpta"]["credit"]=$cpte_produit_pnseb;
      $infos_sup['autre_libel_ope'] = getIdLibelOperationPNSEB(615);//$DET['id_commande'];
      $libelle_ecriture = $DET['id_commande']."-".$SESSION_VARS["id_beneficiaire"];
      $operation = 615;
      $devise =getAgenceDatas($global_id_agence);

      $myErr =passageEcrituresComptablesAuto($operation, $DET['montant_depose'], $comptable, $cptes_substitue, $devise['code_devise_reference'], NULL, $libelle_ecriture, $infos_sup);

      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }

    }
    $infos_his = 'id_benef=' . $SESSION_VARS['id_beneficiaire'] . '- login =' . $global_nom_login . ' - comm= operation ajout commande';
    $myErr = ajout_historique(175, null, $infos_his, $global_nom_login, date("r"), $comptable);
    if ($myErr->errCode != NO_ERR) {
      $dbHandler->closeConnection(false);
      return $myErr;
    }
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message("Confirmation de la commande");

    $demande_msg = "Votre commande a été enregistrée !";


    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

    $html_msg->addButton("BUTTON_OK", 'Pns-2');
    $now = date("Y-m-d");
    $condi5['id_beneficiaire']=$SESSION_VARS['id_beneficiaire'];

    $data_beneficiaire = getMatchedBeneficiaire($condi5);

    $id_comm_recu = $SESSION_VARS['id_commande'];
    $mnt_deposer = $mnt_deposer;
    $condi_detail_comm = " id_commande = ".$SESSION_VARS['id_commande'];
    $commande_details = getCommandeDetail($condi_detail_comm);
    foreach($commande_details as $key_det => $value_det){
      $id_produit_det = "id_produit = ".$value_det['id_produit'];
      $produit_type = getDetailsProduits($id_produit_det);
      if($produit_type['type_produit'] == 1){
        $engrais += $value_det['quantite'];
      }else{
        $amendement += $value_det['quantite'];
      }
    }
    print_recu_commande($SESSION_VARS['id_beneficiaire'],$data_beneficiaire[0]['nom_prenom'],$id_comm_recu,$engrais, $amendement ,$mnt_deposer,$now, $global_nom_login);


    $html_msg->buildHTML();
    echo $html_msg->HTML_code;

  }

}
else if ($global_nom_ecran == 'Pba-1'){

  $Myform = new HTML_GEN2(_("Enregistrement des bons d'achats"));
  $id_annee_data = getAnneeAgricoleActif();
  if($id_annee_data == null) {
    $erreur = new HTML_erreur(_("Année agricole inexistant"));
    $erreur->setMessage(_("Aucune année est ouverte. Veuillez contacter un administrateur"));
    $erreur->addButton(BUTTON_OK, "Pns-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }

    $date_jour = date("d");
    $date_mois = date("m");
    $date_annee = date("Y");
// Sinon, la date passée doit être la date pour laquelle on exécute le batch, généralement date_last_batch + 1 jour

  $date_total = $date_jour."/".$date_mois."/".$date_annee;


  $html  ="<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

// En-tête du tableau
  $html .= "<TR bgcolor=$colb_tableau>";
  $html.="<TD><b>"._("Date")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Numéro livraison")."</b></TD>";
  //$nbreProdActif = getNbreProduitActif();
  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  foreach($list_produit as $key=>$value){
    $i = $key;
    $html.="<TD align=\"center\"><b>"._($value)."</b></TD>";
  }
  $html .= "</TR>";
  $html .= "<TR>";
  $html.="<TD><INPUT TYPE=\"text\" NAME=\"date\" size=14 value=$date_total readonly></TD>\n";
  $html.="<TD><INPUT TYPE=\"text\" NAME=\"num_livraison\" size=14 value='' ></TD>\n";
  foreach($list_produit as $key=>$value){
    $j = $key;
  $html.="<TD><INPUT TYPE=\"text\" NAME=\"produit$key\" size=14 value='' ></TD>\n";
  }
  $html .= "</TR>";
  $html.="</TABLE>";
  $html.="<BR>";
  $Myform->addHTMLExtraCode("html",$html);

  $js = "
  if (document.ADForm.num_livraison.value == null ){
    alert('Veuillez renseigner le numero de livraison');
  }
  ";
  $Myform->addJS(JSP_FORM, "check_numero_livr", $js);

  $Myform->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Pba-2');
  $Myform->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Pns-1');


  $Myform->buildHTML();
  echo $Myform->getHTML();

}
else if ($global_nom_ecran == 'Pba-2'){


  $Myform = new HTML_GEN2(_("Confirmation des bons d'achats"));

  $html  ="<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nb_prod = sizeof($list_produit) +2;

// En-tête du tableau
  $html .= "<TR bgcolor=$colb_tableau>";
  $html .="<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=$nb_prod align=\"center\"><b>" . _("Details de la livraison") . "</b></TD></TR>";
  $html.="<TD align=\"center\"><b>"._("Date livraison")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Numéro livraison")."</b></TD>";
  //$nbreProdActif = getNbreProduitActif();


  foreach($list_produit as $key=>$value){
    $i = $key;
    $html.="<TD align=\"center\"><b>"._($value)."</b></TD>";
  }
  $html .= "</TR>";

  $html .= "<TR>";



  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nbreProduit = sizeof($list_produit);
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);
  $condi_livraison = "numero_livraison = '".$num_livraison."'";
  $list_livraison = getNumLivraison($id_annee_data['id_annee'],$id_saison['id_saison'],$condi_livraison);
  if ($num_livraison == null){
    $erreur = new HTML_erreur(_("Numéro de livraison non renseigné!"));
    $erreur->setMessage(_(" Veuillez renseigner le numéro correct"));
    $erreur->addButton(BUTTON_OK, "Pns-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }

  if (sizeof($list_livraison) > 0){
    $erreur = new HTML_erreur(_("Numéro de livraison déja existant!"));
    $erreur->setMessage(_("Le numero de livraison existe deja. Veuillez insérer un numéro correct"));
    $erreur->addButton(BUTTON_OK, "Pns-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }

  $html.="<TD><INPUT TYPE=\"text\" NAME=\"date\" size=14 value=$date  disabled></TD>\n";
  $html.="<TD><INPUT TYPE=\"text\" NAME=\"num_livraison\" size=14 value=$num_livraison  disabled></TD>\n";

  foreach($list_produit as $key=>$value) {
    if (${"produit" . $key} > 0){
      $html.="<TD><INPUT TYPE=\"text\" NAME= ".${"produit" . $key}." size=14 value=".${"produit" . $key}." disabled></TD>\n";
    }else{
      $html.="<TD><INPUT TYPE=\"text\" NAME= ".${"produit" . $key}." size=14 value=0 disabled></TD>\n";
    }
  }
  $html .= "</TR>";


  $html.="</TABLE>";
  $html.="<BR>";

  $Myform->addHTMLExtraCode("html",$html);

  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nb_prod_stock = sizeof($list_produit)+1;


  foreach($list_produit as $key=>$value){
    $i = $key;
  }


  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nbreProduit = sizeof($list_produit);


  foreach($list_produit as $key=>$value) {
    if (isset(${"produit".$key})){
      $stockBa = getSpecificStock($key);
      if (sizeof($stockBa) == 0){
          $old_stock = 0;
        $new_stock = $old_stock + ${"produit" . $key};
       // $html1.="<TD><INPUT TYPE=\"text\" NAME= ".${"produit" . $key}." size=14 value=$old_stock disabled></TD>\n";
      }else {
        foreach ($stockBa as $key => $value) {
          $old_stock = $value;
          $new_stock = $old_stock + ${"produit" . $key};
         // $html1.="<TD><INPUT TYPE=\"text\" NAME= ".${"produit" . $key}." size=14 value=$old_stock disabled></TD>\n";
        }
      }
      $SESSION_VARS["new_produit".$key] = $new_stock;
    }
    //Sauvegardes des donees posté
    $SESSION_VARS["post_produit".$key] = ${"produit" . $key};
  }
  //$html1 .= "</TR>";


  //$html1.="</TABLE>";
 // $html1.="<BR>";

  //$Myform->addHTMLExtraCode("html1",$html1);
  $SESSION_VARS["date_livraison"] = $date;
  $SESSION_VARS["num_livraison"] = $num_livraison;

  $Myform->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Pba-3');
  $Myform->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Pns-1');


  $Myform->buildHTML();
  echo $Myform->getHTML();
}

else if ($global_nom_ecran == 'Pba-3'){
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);

  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  foreach($list_produit as $key=>$value) {
    $DATA_LIVRAISON = array(
      'id_annee' => $id_annee_data['id_annee'],
      'id_saison' => $id_saison['id_saison'],
      'numero_livraison' => $SESSION_VARS["num_livraison"],
      'date_livraison' => $SESSION_VARS["date_livraison"],
      'id_produit' => $key,
      'qtite_ba' => $SESSION_VARS["post_produit".$key],
      'id_ag' => $global_id_agence);

    $result = executeQuery($db, buildInsertQuery("ec_livraison_ba", $DATA_LIVRAISON));
    $where_condi = "id_annee = " . $id_annee_data['id_annee']." and id_saison = ".$id_saison['id_saison'];
    $stockBa = getSpecificStock($key,$where_condi);

    if (sizeof($stockBa) > 0){
      //foreach($list_produit as $key1=>$value1) {
        $DATA_STOCK = array(
          'id_annee' => $id_annee_data['id_annee'],
          'id_saison' => $id_saison['id_saison'],
          'id_produit' => $key,
          'qtite_ba' => $SESSION_VARS['new_produit' . $key],
          'id_ag' => $global_id_agence);

        $DATA_WHERE = array(
          'id_produit' => $key
        );
      $result = executeQuery($db, buildUpdateQuery("ec_stock_ba", $DATA_STOCK, $DATA_WHERE));
    }
    else {
      $DATA_STOCK = array(
        'id_annee' => $id_annee_data['id_annee'],
        'id_saison' => $id_saison['id_saison'],
        'id_produit' => $key,
        'qtite_ba' => $SESSION_VARS['post_produit' . $key],
        'id_ag' => $global_id_agence);
      $result = executeQuery($db, buildInsertQuery("ec_stock_ba", $DATA_STOCK));
    }
  }
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
  }
  else {
    $msg_confirmation = "Enregistrement des livraisons";
    ajout_historique(198, NULL, $msg_confirmation, $global_nom_login, date("r"), NULL);
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message($msg_confirmation);
    $msgConfirmation = "L'entrée a été enregistrée avec succès";
    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ",  $msgConfirmation));

    $html_msg->addButton("BUTTON_OK", 'Pns-1');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}
else if ($global_nom_ecran == 'Pst-1') {

  $Myform = new HTML_GEN2(_("Consultation des livraisons"));
  $mnt_avance = 0;
  $mnt_solde = 0;
  $id_annee_data = getAnneeAgricoleActif();
  if($id_annee_data == null) {
    $erreur = new HTML_erreur(_("Année agricole inexistant"));
    $erreur->setMessage(_("Aucune année est ouverte. Veuillez contacter un administrateur"));
    $erreur->addButton(BUTTON_OK, "Pns-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);
  if($id_saison == null) {
    $erreur = new HTML_erreur(_("Saison Culturale inexistant"));
    $erreur->setMessage(_("Aucune saison est ouverte. Veuillez contacter un administrateur"));
    $erreur->addButton(BUTTON_OK, "Pns-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }

  $condi_stock = "id_annee = ". $id_annee_data['id_annee']." AND id_saison = ".$id_saison['id_saison'];
  $list_stock = getListeStockBa($condi_stock);
  foreach ($list_stock as $key_list => $value_list) {
    $condition = " id_produit=".$value_list['id_produit'];
    $produit_details =getListeProduitPNSEB($condition,true);
    foreach ($produit_details as $key_prod => $value_prod) {
      $total_prix_avance = $value_list['qtite_ba'] * $value_prod['montant_minimum'];
      $mnt_avance +=$total_prix_avance;
      $total_prix_solde = $value_list['qtite_ba'] * $value_prod['prix_unitaire'];
      $mnt_solde +=$total_prix_solde;
    }
  }

  $Myform->addField("montant_avance",_("Montant des avances"), TYPC_MNT);
  $Myform->setFieldProperties("montant_avance", FIELDP_DEFAULT,$mnt_avance);
  $Myform->setFieldProperties("montant_avance", FIELDP_IS_LABEL, true);
  $Myform->addField("montant_solde",_("Montant des soldes"), TYPC_MNT);
  $Myform->setFieldProperties("montant_solde", FIELDP_DEFAULT, $mnt_solde);
  $Myform->setFieldProperties("montant_solde", FIELDP_IS_LABEL, true);


  $html  ="<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nb_prod = sizeof($list_produit) +2;

// En-tête du tableau
  $html .= "<TR bgcolor=$colb_tableau>";
  $html .="<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=$nb_prod align=\"center\"><b>" . _("Details de la livraison") . "</b></TD></TR>";
  $html.="<TD align=\"center\"><b>"._("Date livraison")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Numéro livraison")."</b></TD>";
  //$nbreProdActif = getNbreProduitActif();


  foreach($list_produit as $key=>$value){
    $i = $key;
    $html.="<TD align=\"center\"><b>"._($value)."</b></TD>";
  }
  $html .= "</TR>";

  $html .= "<TR>";


  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nbreProduit = sizeof($list_produit);
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);
  $list_livraison = getNumLivraison($id_annee_data['id_annee'],$id_saison['id_saison']);
  foreach($list_livraison as $key=>$value) {
    $html .= "<TD><INPUT TYPE=\"text\" NAME= \"date_livraison\".$key size=14 value=".$value['date_livraison']." disabled></TD>\n";
    $html .= "<TD><INPUT TYPE=\"text\" NAME= \"numero_livraison\".$key size=14 value=".$value['numero_livraison']." disabled></TD>\n";
    $qtite_ba = getDetailLivraison($id_annee_data['id_annee'],$id_saison['id_saison'],$value['numero_livraison']);
    foreach ($qtite_ba as $key10 => $value10) {
      //if ($value10 > 0) {
      if ($value10 == null){
        $value10 = 0;
      }
        $html .= "<TD><INPUT TYPE=\"text\" NAME= \"stock_actuelle\".$key10 size=14 value=$value10 disabled></TD>\n";
      //}
    }
    $html .= "</TR>";

  }
  foreach($list_produit as $key=>$value) {
    if (${"produit" . $key} > 0){
      $html.="<TD><INPUT TYPE=\"text\" NAME= ".${"produit" . $key}." size=14 value=".${"produit" . $key}." disabled></TD>\n";
    }
  }
  $html .= "</TR>";


  $html.="</TABLE>";
  $html.="<BR>";

  $Myform->addHTMLExtraCode("html",$html);

   $html1  ="<br>";
  $html1 .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nb_prod_stock = sizeof($list_produit)+1;
// En-tête du tableau
   $html1 .="<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=$nb_prod_stock align=\"center\"><b>" . _("Details du stock actualisés") . "</b></TD></TR>";
  $html1 .= "<TR bgcolor=$colb_tableau>";
   $html1.="<TD align=\"center\"><b>"._("Description")."</b></TD>";
  $nbreProdActif = getNbreProduitActif();

  foreach($list_produit as $key=>$value){
    $i = $key;
    $html1.="<TD align=\"center\"><b>"._($value)."</b></TD>";
  }
  $html1 .= "</TR>";

  $html1 .= "<TR>";
  $html1.="<TD><INPUT TYPE=\"text\" NAME=\"num_livraison\" size=14 value=\"Stock disponible\"  disabled></TD>\n";

  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nbreProduit = sizeof($list_produit);
  foreach($list_produit as $key=>$value) {
    $condi_stock = "id_annee = ".$id_annee_data['id_annee']." AND id_saison = ".$id_saison['id_saison'];
      $stockBa = getSpecificStock($key,$condi_stock);
        foreach ($stockBa as $key => $value) {
          $html1.="<TD><INPUT TYPE=\"text\" NAME=$key size=14 value=$value disabled></TD>\n";
        }

  }
  $html1 .= "</TR>";


  $html1.="</TABLE>";
   $html1.="<BR>";

  $Myform->addHTMLExtraCode("html1",$html1);

  $Myform->addFormButton(1, 1, "retour", _("retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Pns-1');

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

else if ($global_nom_ecran == 'Psb-1') {
  unset($SESSION_VARS['page_reload']);
  unset($SESSION_VARS['data_produit']);
  unset($SESSION_VARS['choix']);
  unset($SESSION_VARS['date_saison']);
  unset($SESSION_VARS['paiement_commande']);
  unset($SESSION_VARS['date_saison_selected']);
  unset($SESSION_VARS['criteres']);
  unset($SESSION_VARS['contenu']);
  unset($SESSION_VARS['id_annee_selected']);
  unset($SESSION_VARS['choix_period']);

  $MyMenu = new HTML_menu_gen(_("Gestions de stocks de bons d'achats"));
  $MyMenu->addItem(_("Approvisionnement des bons d'achats"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Psb-2", 200, "$http_prefix/images/approvisionnement.gif","1");
  $MyMenu->addItem(_("Délestage des bons d'achats"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Psb-4", 168, "$http_prefix/images/delestage.gif","2");
  $MyMenu->addItem(_("Retour Menu Guichet"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pns-1", 0, "$http_prefix/images/back.gif","0");
  $MyMenu->buildHTML();
  echo $MyMenu->HTMLCode;

}
else if ($global_nom_ecran == 'Psb-2') {
  $Myform = new HTML_GEN2(_("Approvisionnement des bons d'achats"));
  $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $Myform->addHTMLExtraCode("html_js",$html_js);

  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $saison = getDetailSaisonCultu($whereSaison);
  if($id_annee_data == null) {
    $erreur = new HTML_erreur(_("Année agricole inexistant"));
    $erreur->setMessage(_("Aucune année est ouverte. Veuillez contacter un administrateur"));
    $erreur->addButton(BUTTON_OK, "Pns-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }
  $html1  ="<br>";
  $html1 .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nb_prod_stock = sizeof($list_produit)+1;
// En-tête du tableau
  $html1 .="<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=$nb_prod_stock align=\"center\"><b>" . _("Details du stock actualisés") . "</b></TD></TR>";
  $html1 .= "<TR bgcolor=$colb_tableau>";
  $html1.="<TD align=\"center\"><b>"._("Description")."</b></TD>";
  $nbreProdActif = getNbreProduitActif();

  foreach($list_produit as $key=>$value){
    $i = $key;
    $html1.="<TD align=\"center\"><b>"._($value)."</b></TD>";
  }
  $html1 .= "</TR>";

  $html1 .= "<TR>";
  $html1.="<TD><INPUT TYPE=\"text\" NAME=\"num_livraison\" size=14 value=\"Stock disponible\"  disabled></TD>\n";

  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nbreProduit = sizeof($list_produit);
  foreach($list_produit as $key=>$value) {
    $whereCondi = "id_annee = ".$id_annee_data['id_annee']." and id_saison =".$saison["id_saison"];
    $stockBa = getSpecificStock($key,$whereCondi);
    foreach ($stockBa as $key => $value) {
      $html1.="<TD><INPUT TYPE=\"text\" NAME=\"stock_dispo_".$key."\" size=14 value=$value disabled></TD>\n";
    }

  }
  $html1 .= "</TR>";


  $html1.="</TABLE>";
  $html1.="<BR>";

  $Myform->addHTMLExtraCode("html1",$html1);
  $condi_stock = "id_annee = ".$id_annee_data['id_annee']." and id_saison =".$saison["id_saison"];
  $stock_dispo = getListeStockBa($condi_stock);
  if (sizeof($stock_dispo) == null){
    $msg_confirmation = "Stock non Disponible";
    $erreur = new HTML_erreur(_($msg_confirmation));
    $erreur->setMessage(_("Il n'y a pas de stock de bon d'achats disponible"));
    $erreur->addButton(BUTTON_OK, "Pns-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }

  $date_jour = date("d");
  $date_mois = date("m");
  $date_annee = date("Y");
  $date_total = $date_jour."/".$date_mois."/".$date_annee;

  $html  ="<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $list_produit = getListeProduitPNSEB($condi);
  $nb_prod = sizeof($list_produit) +2;

// En-tête du tableau
  $html .= "<TR bgcolor=$colb_tableau>";
  $html .="<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=$nb_prod align=\"center\"><b>" . _("Details approvisionnement") . "</b></TD></TR>";
  $html.="<TD align=\"center\"><b>"._("Date approvisionnement")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Agent")."</b></TD>";
  foreach($list_produit as $key=>$value){
    $i = $key;
    $html.="<TD align=\"center\"><b>"._($value)."</b></TD>";
  }
  $html .= "<TR>";
  $html.="<TD><INPUT TYPE=\"text\" NAME=\"date\" size=14 value=$date_total readonly></TD>\n";
  $condi_login = " is_agent_ec = 't'";
  $utilisateur = getLoginAll($condi_login);
  $html.="<TD><select NAME=\"HTML_GEN_LSB_agent\">";
  $html.="<option value=0>[Aucun]</option>";
  foreach( $utilisateur as $key1=>$value1)
    $html.="<option value=$key1>".$value1['login']."</option>";
  $html.= "</select></TD>\n";

  foreach ($list_produit as $key_prod => $value_prod){
    $html.="<TD><INPUT TYPE=\"text\" NAME=\"produit_".$key_prod."\" size=14 value='0' Onblur=\"changeStock($key_prod);\"></TD>\n";
  }

  $js_qtite ="
 function changeStock(i) {
    var nbre_stock = eval('document.ADForm.stock_dispo_'+i+'.value');
    var nbre_appro  = eval('document.ADForm.produit_'+i+'.value');
    var appro = document.getElementsByName('produit_'+i);

    if (parseInt(nbre_appro) > parseInt(nbre_stock)){
     alert('Le nombre souhaité ('+nbre_appro+') depasse le stock disponible'+nbre_stock+'. Veuillez inserer un nombre correct!');
     document.getElementsByName('produit_'+i).item(0).focus();
      document.getElementsByName('produit_'+i).item(0).value = 0;
     return false;
    }
  }
  ";

  $js_check = "
  function CheckAgent(){
     if (document.ADForm.HTML_GEN_LSB_agent.value == 0){
       isSubmit=false;
       ADFormValid=false;
       alert('Veuillez saisir un agent!');return false;

    }
   }

  ";

  $js_1 = "
    $('[name*=produit]').keypress(function(event){
        if(event.which == 13){
            $(this).blur();
            event.preventDefault();
        }
    });
  ";

  $Myform->addJS(JSP_FORM, "gestion_stock", $js_qtite);
  $Myform->addJS(JSP_FORM, "check_agent", $js_check);
  $Myform->addJS(JSP_FORM, "js_prevent", $js_1);

  $html .= "</TABLE>";
  $html .= "<br>";

    $Myform->addHTMLExtraCode("html",$html);

  $Myform->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Psb-3');
  $Myform->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onClick"=>"CheckAgent();"));
  $Myform->setFormButtonProperties("ok", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Pns-1');

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

else if ($global_nom_ecran == 'Psb-3') {
  global $dbHandler, $global_id_agence, $global_id_utilisateur,$global_nom_login;

  $db = $dbHandler->openConnection();
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);
  $condi = "etat_produit = 1";
  $list_produit = getListeProduitPNSEB($condi);
  $connection = false;
  foreach($list_produit as $key=>$value){
    $stock_appro = 0;
    if (${"produit_".$key} > 0){
      //Verifie si un agent possede deja un stock
      $check_stock_agent = getAgentStockSpecific($agent,$key);
      // si l'agent possede un stock deja, on fait une mise a jour
      if (sizeof($check_stock_agent) > 0){
        $new_stock_agent = 0;
        $new_stock_agent = ${"produit_".$key} + $check_stock_agent['qtite_ba'];
        $DATA_UPDATE =array(
          "id_produit" => $key,
          "qtite_ba" => $new_stock_agent,
          "date_modif" => $date,
          "id_ag" => $global_id_agence
        );
        $DATA_CONDI = array(
          "id_agent" => $check_stock_agent['id_agent'],
          "id" => $check_stock_agent['id']
        );
        $db2 = $dbHandler->openConnection();
        $result1 = executeQuery($db2, buildUpdateQuery("ec_agent_ba", $DATA_UPDATE, $DATA_CONDI));
        if (DB::isError($result1)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__,$result1->getMessage());
        }else{
          $connection = true;
        }$dbHandler->closeConnection(true);

        //entrer dans la table ec_flux_ba ou on repertorie les flux d' approvisionnement
        $DATA_FLUX = array(
          "id_annee" => $id_annee_data['id_annee'],
          "id_saison" => $id_saison['id_saison'],
          "type_flux" => 1,
          "id_agent" => $agent,
          "id_produit" => $key,
          "qtite_ba" => ${"produit_".$key},
          "id_utilisateur" => $global_id_utilisateur,
          "id_ag" => $global_id_agence
        );
        $db3 = $dbHandler->openConnection();
        $result3 = executeQuery($db3, buildInsertQuery("ec_flux_ba", $DATA_FLUX));
        if (DB::isError($result3)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__,$result3->getMessage());
        }else{
          $connection = true;
        }$dbHandler->closeConnection(true);

        $stock_appro = ${"produit_".$key};
        $stockBa = getSpecificStock($key);
        foreach($stockBa as $key_ba=>$value_ba){
          $stock_dispo = $value_ba - ${"produit_".$key};
        }
        $DATA_MISE_A_JOUR_STOCK = array(
          "qtite_ba" => $stock_dispo
        );
        $DATA_MISE_A_JOUR_STOCK_CONDI = array(
          "id_annee" =>$id_annee_data['id_annee'],
          "id_saison" => $id_saison['id_saison'],
          "id_produit" => $key
        );
        $result2 = executeQuery($db, buildUpdateQuery("ec_stock_ba", $DATA_MISE_A_JOUR_STOCK, $DATA_MISE_A_JOUR_STOCK_CONDI));
        if (DB::isError($result2)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__,$result2->getMessage());
        }else{
          $connection = true;
        }
      }
      //si l'agent ne possede pas de stock on fait une insertion
        else {
        $DATA_INSERT = array(
          "id_agent" => $agent,
          "id_produit" => $key,
          "qtite_ba" => ${"produit_".$key},
          "date_modif" => $date,
          "id_ag" => $global_id_agence
        );
          $db3 = $dbHandler->openConnection();
        $result3 = executeQuery($db3, buildInsertQuery("ec_agent_ba", $DATA_INSERT));
        if (DB::isError($result3)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__,$result3->getMessage());
        }else{
          $connection = true;
        }$dbHandler->closeConnection(true);
          //entrer dans la table ec_flux_ba ou on repertorie les flux d' approvisionnement
          $DATA_FLUX = array(
            "id_annee" => $id_annee_data['id_annee'],
            "id_saison" => $id_saison['id_saison'],
            "type_flux" => 1,
            "id_agent" => $agent,
            "id_produit" => $key,
            "qtite_ba" => ${"produit_".$key},
            "id_utilisateur" => $global_id_utilisateur,
            "id_ag" => $global_id_agence
          );
          $db3 = $dbHandler->openConnection();
          $result3 = executeQuery($db3, buildInsertQuery("ec_flux_ba", $DATA_FLUX));
          if (DB::isError($result3)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__,$result3->getMessage());
          }else{
            $connection = true;
          }$dbHandler->closeConnection(true);
          //mise a jour du stock disponible
        $stock_appro = ${"produit_".$key};
        $stockBa = getSpecificStock($key);
        foreach($stockBa as $key_ba=>$value_ba){
          $stock_dispo = $value_ba - ${"produit_".$key};
        }
        $stock_dispo =
        $DATA_MISE_A_JOUR_STOCK = array(
          "qtite_ba" => $stock_dispo
        );
        $DATA_MISE_A_JOUR_STOCK_CONDI = array(
          "id_annee" =>$id_annee_data['id_annee'],
          "id_saison" => $id_saison['id_saison'],
          "id_produit" => $key
        );
          $db4 = $dbHandler->openConnection();
        $result4 = executeQuery($db4, buildUpdateQuery("ec_stock_ba", $DATA_MISE_A_JOUR_STOCK, $DATA_MISE_A_JOUR_STOCK_CONDI));
        if (DB::isError($result4)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__,$result4->getMessage());
        }else{
          $connection = true;
        }$dbHandler->closeConnection(true);
      }
      $msg_confirmation = "Confirmation de l'approvisionnement des bons d'achats des agents";
      $fonction = 200;
    }
  }
  if ($connection == true)
  {
    ajout_historique($fonction, NULL, $msg_confirmation, $global_nom_login, date("r"), NULL);
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message($msg_confirmation);
    $msgConfirmation = "L'approvisionnement a été enregistrée avec succès";
    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ",  $msgConfirmation));
      $html_msg->addButton("BUTTON_OK", 'Psb-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}
else if ($global_nom_ecran == 'Psb-4') {
  if (!isset($agent_selected)){
  $utilisateur = getLoginDelestage();
  $Myform = new HTML_GEN2(_("Choix agent"));
  $choix = array();
  if (isset($utilisateur)) {
    foreach ($utilisateur as $key => $value) $choix[$key] = $value["id_agent"];
  };

  $Myform->addField("agent_selected", _("Nom agent"), TYPC_LSB);
  $Myform->setFieldProperties("agent_selected", FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties('agent_selected', FIELDP_ADD_CHOICES, $choix);

  $Myform->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $Myform->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
    $Myform->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $Myform->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Psb-4');
    $Myform->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Pns-1');

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

   else{
    $Myform = new HTML_GEN2(_("Delestage des bons d'achats"));
    $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $Myform->addHTMLExtraCode("html_js",$html_js);

    //$libel_agent = getInfoUtilisateur($agent_selected);
   // $libel_agent = getLoginAll($agent_selected);
    // $nom_agent = $libel_agent['nom']." ".$libel_agent['prenom'];
   $Myform->addField("agent",_("Nom agent"), TYPC_TXT);
   $Myform->setFieldProperties("agent", FIELDP_DEFAULT,$agent_selected);
   $Myform->setFieldProperties("agent", FIELDP_IS_LABEL, true);
     $SESSION_VARS['id_agent'] = $agent_selected;

    $html = "<br>";
    $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
    $condi = " etat_produit = 1 ";
    $list_produit = getListeProduitPNSEB($condi);
    $nb_prod_stock = sizeof($list_produit) + 1;
// En-tête du tableau
    $html .= "<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=$nb_prod_stock align=\"center\"><b>" . _("Details du stock actualisés") . "</b></TD></TR>";
    $html .= "<TR bgcolor=$colb_tableau>";
    $html .= "<TD align=\"center\"><b>" . _("Description") . "</b></TD>";
    $nbreProdActif = getNbreProduitActif();

    foreach ($list_produit as $key => $value) {
      $i = $key;
      $html .= "<TD align=\"center\"><b>" . _($value) . "</b></TD>";
    }
    $html .= "</TR>";

    $html .= "<TR>";
    $html .= "<TD><INPUT TYPE=\"text\" NAME=\"num_livraison\" size=14 value=\"Stock disponible\"  disabled></TD>\n";

    $condi = " etat_produit = 1 ";
    $list_produit = getListeProduitPNSEB($condi);
    $nbreProduit = sizeof($list_produit);
    foreach ($list_produit as $key => $value) {
      $condi_agent = " id_agent ='".$agent_selected."' AND id_produit =".$key;
      $stockParAgent =getAgentStock($condi_agent);
      if ($stockParAgent == null){
        $html .= "<TD><INPUT TYPE=\"text\" NAME=\"stock_dispo_" . $value['id_produit'] . "\" size=14 value=0 disabled></TD>\n";
      }else {
        foreach ($stockParAgent as $key => $value) {
          $qtite_stock_agent = $value['qtite_ba'];
          $html .= "<TD><INPUT TYPE=\"text\" NAME=\"stock_dispo_" . $value['id_produit'] . "\" size=14 value=$qtite_stock_agent disabled></TD>\n";
        }
      }

    }
    $html .= "</TR>";


    $html .= "</TABLE>";
    $html .= "<BR>";

    $Myform->addHTMLExtraCode("html", $html);

     $date_jour = date("d");
     $date_mois = date("m");
     $date_annee = date("Y");
     $date_total = $date_jour."/".$date_mois."/".$date_annee;

     $html1  ="<br>";
     $html1 .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
     $list_produit = getListeProduitPNSEB($condi);
     $nb_prod = sizeof($list_produit) +1;

// En-tête du tableau
     $html1 .= "<TR bgcolor=$colb_tableau>";
     $html1 .="<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=$nb_prod align=\"center\"><b>" . _("Details approvisionnement") . "</b></TD></TR>";
     $html1.="<TD align=\"center\"><b>"._("Date approvisionnement")."</b></TD>";
     foreach($list_produit as $key=>$value){
       $i = $key;
       $html1.="<TD align=\"center\"><b>"._($value)."</b></TD>";
     }
     $html1 .= "<TR>";
     $html1.="<TD><INPUT TYPE=\"text\" NAME=\"date\" size=14 value=$date_total readonly></TD>\n";

     foreach ($list_produit as $key_prod => $value_prod){
       $html1.="<TD><INPUT TYPE=\"text\" NAME=\"produit_".$key_prod."\" size=14 value='0' Onblur=\"changeStockDelestage($key_prod);\" ></TD>\n";
     }

     //$Myform->addJS(JSP_FORM, "gestion_stock", $js_qtite);
     $html1 .= "</TABLE>";
     $html1 .= "<br>";

     $js_qtite_deles ="
 function changeStockDelestage(i) {
    var nbre_stock = eval('document.ADForm.stock_dispo_'+i+'.value');
    var nbre_appro  = eval('document.ADForm.produit_'+i+'.value');
    var appro = document.getElementsByName('produit_'+i);

    if (parseInt(nbre_appro) > parseInt(nbre_stock)){
     alert('Le nombre souhaité ('+nbre_appro+') depasse le stock disponible ('+nbre_stock+'). Veuillez inserer un nombre correct!');
     document.getElementsByName('produit_'+i).item(0).focus();
      document.getElementsByName('produit_'+i).item(0).value = 0;
     return false;
    }
  }
  ";

  $js_1 = "
    $('[name*=produit]').keypress(function(event){
        if(event.which == 13){
            $(this).blur();
            event.preventDefault();
        }
    });
  ";

     $Myform->addJS(JSP_FORM, "gestion_stock_deles", $js_qtite_deles);
     $Myform->addJS(JSP_FORM, "js_prevent", $js_1);
     $Myform->addHTMLExtraCode("html1",$html1);


     $Myform->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
     $Myform->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
     $Myform->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
     $Myform->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Psb-5');
     $Myform->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Psb-1');
    $Myform->buildHTML();
    echo $Myform->getHTML();
  }
}

else if ($global_nom_ecran == 'Psb-5') {
  global $dbHandler, $global_id_agence,$global_nom_login;

  $db = $dbHandler->openConnection();
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);
  $condi = "etat_produit = 1";
  $list_produit = getListeProduitPNSEB($condi);
  $connection = false;
  $nbe_delest =0;

  foreach($list_produit as $key_prod => $value_prod){

    if (${"produit_".$key_prod} >0 ){
      // Update stock de l'agent
      $check_stock_agent = getAgentStockSpecific($SESSION_VARS['id_agent'],$key_prod);
      $qtite_delestage = 0;
      $qtite_delestage = $check_stock_agent['qtite_ba'] - ${"produit_".$key_prod};
      $DATA_DELESTAGE = array(
        "qtite_ba" =>$qtite_delestage,
      );
      $DATA_DELESTAGE_CONDI = array(
        "id_agent" => $SESSION_VARS['id_agent'],
        "id_produit" => $key_prod
      );
      $db6 = $dbHandler->openConnection();
      $result6 = executeQuery($db6, buildUpdateQuery("ec_agent_ba", $DATA_DELESTAGE, $DATA_DELESTAGE_CONDI));
      if (DB::isError($result6)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result6->getMessage());
      }else{
        $connection = true;
      }$dbHandler->closeConnection(true);

      //entrer dans la table ec_flux_ba ou on repertorie les flux d' approvisionnement
      $DATA_FLUX = array(
        "id_annee" => $id_annee_data['id_annee'],
        "id_saison" => $id_saison['id_saison'],
        "type_flux" => 2,
        "id_agent" => $SESSION_VARS['id_agent'],
        "id_produit" => $key_prod,
        "qtite_ba" => ${"produit_".$key_prod},
        "id_utilisateur" => $global_id_utilisateur,
        "id_ag" => $global_id_agence
      );
      $db8 = $dbHandler->openConnection();
      $result3 = executeQuery($db8, buildInsertQuery("ec_flux_ba", $DATA_FLUX));
      if (DB::isError($result8)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result8->getMessage());
      }else{
        $connection = true;
      }$dbHandler->closeConnection(true);

      //Update Stock generale
      //$condi = "id_annee = ".$id_annee_data['id_annee']." and id_saison =".$id_saison['id_saison']." AND id_produit =".$key_prod;
      $list_stock = getSpecificStock($key_prod);
      if(sizeof($list_stock) > 0) {
        foreach($list_stock as $key_stock => $value_stock){
          $stock_dispo = $value_stock + ${"produit_".$key_prod};
        }
        $DATA_GESTION_STOCK = array(
          "qtite_ba" => $stock_dispo
        );
        $DATA_GESTION_STOCK_CONDI = array(
          "id_annee" => $id_annee_data['id_annee'],
          "id_saison" => $id_saison['id_saison'],
          "id_produit" => $key_prod
        );
        $db7 = $dbHandler->openConnection();
        $result7 = executeQuery($db7, buildUpdateQuery("ec_stock_ba", $DATA_GESTION_STOCK, $DATA_GESTION_STOCK_CONDI));
        if (DB::isError($result7)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__,$result7->getMessage());
        }else{
          $connection = true;
        }$dbHandler->closeConnection(true);
      }
      $msg_confirmation = "Confirmation du delestage des bon d'achats";
      $fonction = 168;
      $nbe_delest += ${"produit_".$key_prod};
    }
  }
  if ($nbe_delest == 0){
    $erreur = new HTML_erreur(_("Delestage invalide"));
    $erreur->setMessage(_("Veuillez remplir les lignes correctement"));
    $erreur->addButton(BUTTON_OK, "Psb-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    exit();
  }

  if ($connection == true)
  {
    ajout_historique($fonction, NULL, $msg_confirmation, $global_nom_login, date("r"), NULL);
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message($msg_confirmation);
    $msgConfirmation = "Le delestage a été enregistrée avec succès";
    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ",  $msgConfirmation));
    $html_msg->addButton("BUTTON_OK", 'Psb-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
}

else if($global_nom_ecran == 'Pca-1'){
  global $global_id_benef, $global_client, $global_id_client;

  $Myform = new HTML_GEN2(_("Paiement des commandes"));
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee'];
  $saison = getDetailSaisonCultuAll($whereSaison);
  $saisons_available = '';
  foreach ($saison as $key => $value){
    $saisos_available .= $key.',';
  }
  $saisos_available =  rtrim($saisos_available,",");

  $condition_paiement = "etat_commande IN (2,8) and id_benef =".$SESSION_VARS['id_beneficiaire']." AND id_saison IN (".$saisos_available.")";
  $commande_paiement = getCommande($condition_paiement);
  $choix = array();
  if (isset($commande_paiement)) {
    foreach($commande_paiement as $key=>$value) $choix[$key] = $value["id_commande"];
  };

  $Myform->addField("id_commande", _("Numéro de commande"), TYPC_LSB);
  $Myform->setFieldProperties("id_commande",FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties('id_commande', FIELDP_ADD_CHOICES, $choix);



  $Myform->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pca-2");

  $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
else if($global_nom_ecran == 'Pca-2'){


  $Myform = new HTML_GEN2(_("Selection produits"));

  $html = "<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $condi = " id_commande = ".$id_commande;
  $list_commande_details = getCommandeDetail($condi);
  $nb_prod_stock = sizeof($list_commande_details) *2;
  $html .= "<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=6 align=\"center\"><b>" . _("Details des commandes") . "</b></TD></TR>";
  $html .= "<TD align=\"center\"><b>" . _("Produit") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Quantite commandée") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Quantite à payer") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Montant à payer") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Quantite Pris") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Montant Pris") . "</b></TD></TR>";

  $html .= "<TR>";
  foreach ($list_commande_details as $key => $value) {
    $i = $key;
    $condi_nom_prod = "id_produit = " . $value['id_produit'];
    $nom_produit = getListeProduitPNSEB($condi_nom_prod);
    $html .= "<INPUT TYPE=\"hidden\" NAME=\"id_detail_commande".$value['id_produit']."\" size=14 value=".$key." >";
    foreach ($nom_produit as $key_nom => $value_nom) {
      $html .= "<TD  align=\"center\">$value_nom</TD>";
    }
    $html.="<TD><INPUT TYPE=\"text\" NAME =\"qtite_commande_".$value['id_produit']."\" size=14 value=".$value['quantite']." readonly ></TD>";
    $condi_comm_payer = "id_commande = ".$id_commande." AND id_detail_commande = ".$key." AND etat_paye in (1,2) ";
    $commande_payer = getPaiementDetail($condi_comm_payer);
    $mnt_paye = 0;
    $qtite_paye = 0;
    foreach($commande_payer as $key_paye => $value_paye){
      $mnt_paye += $value_paye['montant_paye'];
      $qtite_paye +=$value_paye['qtite_paye'];
    }
    $total_qtite_dispo = $value['quantite'] - $qtite_paye;
    //$total_mnt_paye = $value['prix_total'] - $mnt_paye;
    $condi_commande = "id_commande = ".$id_commande;
    $commande = getCommande($condi_commande);
    $condi_mnt_unitaire_prod = "id_produit = ".$value['id_produit'];
    $prix_produit = getDetailsProduits($condi_mnt_unitaire_prod);
    $total_paye_mini =$prix_produit['montant_minimum'] *$value['quantite'];
    $total_restant = $value['prix_total'] - $total_paye_mini;
    $total_restant_a_payer = $total_restant / $value['quantite'];
    $total_restant_a_payer_display = $total_restant_a_payer * $total_qtite_dispo;

    $html.="<TD><INPUT TYPE=\"text\" NAME =\"qtite_dispo_".$value['id_produit']."\" size=14 value=".$total_qtite_dispo." readonly ></TD>\n";
    $html.="<TD><INPUT TYPE=\"text\" NAME =\"mnt_dispo_".$value['id_produit']."\" size=14 value=".afficheMontant($total_restant_a_payer_display,true)." readonly ></TD>\n";


    $html.="<TD><INPUT TYPE=\"text\" NAME =\"qtite_pris_".$value['id_produit']."\" size=12 value=0 Onblur=\"changeStockPaiement(".$value['id_produit'].");\" Onchange =\"UpdateMontant(".$value['id_produit'].");\" > </TD>
    <TD><INPUT TYPE=\"text\" NAME=\"mnt_pris_".$value['id_produit']."\" value = '' readonly>
    <INPUT TYPE=\"hidden\" NAME=\"prix_unitaire_".$value['id_produit']."\" size=14 value=".$total_restant_a_payer." ></TD>\n";
    $html .= "</TR>";
  }

  $html .= "</TABLE>";
  $html .= "<br>";

  $js_gestion_paiement ="
 function changeStockPaiement(i) {
    var nbre_stock = eval('document.ADForm.qtite_dispo_'+i+'.value');
    var nbre_appro  = eval('document.ADForm.qtite_pris_'+i+'.value');
    var appro = document.getElementsByName('produit_'+i);

    if (parseInt(nbre_appro) > parseInt(nbre_stock)){
     alert('Le nombre souhaité ('+nbre_appro+') depasse le stock disponible'+nbre_stock+'. Veuillez inserer un nombre correct!');
     document.getElementsByName('qtite_pris_'+i).item(0).focus();
      document.getElementsByName('qtite_pris_'+i).item(0).value = 0;
     return false;
    }
     if (parseInt(nbre_appro) < 0 ){
     alert('Le nombre souhaité doit être supérieur à 0. Veuillez insérer un nombre correct!');
     document.getElementsByName('qtite_pris_'+i).item(0).focus();
     document.getElementsByName('qtite_pris_'+i).item(0).value = 0;
     return false;
    }
  }
  function UpdateMontant(i){
    var nbre_stock = eval('document.ADForm.qtite_dispo_'+i+'.value');
    var nbre_appro  = eval('document.ADForm.qtite_pris_'+i+'.value');
    var qtite_pris = eval('document.ADForm.qtite_pris_'+i);
    var mnt_pris = eval('document.ADForm.mnt_pris_'+i);
    var prix_unitaire = eval('document.ADForm.prix_unitaire_'+i);
    if (parseInt(nbre_appro) <= parseInt(nbre_stock) && parseInt(nbre_appro) >= 0){
    mnt_pris.value = parseInt(qtite_pris.value) * parseInt(prix_unitaire.value);
    }
  }
  ";
  $Myform->addJS(JSP_FORM, "gestion_paiement", $js_gestion_paiement);

  $Myform->addHTMLExtraCode("html",$html);
  $SESSION_VARS['id_commande'] = $id_commande;

  $Myform->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pca-3");

  $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

else if($global_nom_ecran == 'Pca-3'){
  global $dbHandler, $global_id_agence,$global_id_utilisateur;

  $db = $dbHandler->openConnection();

  $date_jour = date("d");
  $date_mois = date("m");
  $date_annee = date("Y");
  $date_total = $date_jour."/".$date_mois."/".$date_annee;

  $connection = false;
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);
  $condi = "etat_produit = 1";
  $list_produit = getListeProduitPNSEB($condi);
  $detail_paiement_existant = getPaiementMaxRemb($SESSION_VARS['id_commande']);

  //controle sur le nombre de produit inserer
  foreach($list_produit as $key=>$value){
    if(isset(${"qtite_pris_".$key}) && ${"qtite_pris_".$key} > 0 && ${"qtite_pris_".$key} > ${"qtite_dispo_".$key}){
      $msg_confirmation = "Paiement commande passer en attente";
      $html_msg = new HTML_erreur($msg_confirmation);
      $msgConfirmation = "La quantite du produit ".$value." est superieur au nombre de produit restant";
      $html_msg->setMessage(sprintf(" <br />%s  !<br /> ",  $msgConfirmation));
      $html_msg->addButton("BUTTON_OK", 'Pns-2');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
      exit();
    }
  }

  foreach($list_produit as $key=>$value){
    if(isset(${"qtite_pris_".$key}) && ${"qtite_pris_".$key} > 0){
      $DATA_PAIEMENT = array(
        "id_commande" => $SESSION_VARS['id_commande'],
        "id_detail_commande" => ${"id_detail_commande".$key},
        "id_remb" => $detail_paiement_existant['max'] + 1,
        "type_paiement" => 1,
        "montant_paye" => ${"mnt_pris_".$key},
        "date_creation" => $date_total,
        "id_utilisateur" => $global_id_utilisateur,
        "etat_paye" => 1,
        "id_ag" => $global_id_agence,
        "qtite_paye" => ${"qtite_pris_".$key}
      );
      $db1 = $dbHandler->openConnection();
      $result1 = executeQuery($db1, buildInsertQuery("ec_paiement_commande", $DATA_PAIEMENT));
      if (DB::isError($result1)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result1->getMessage());
      }else{
        $connection = true;
      }$dbHandler->closeConnection(true);
    }
    $msg_confirmation = "Paiement commande passer en attente";
    $fonction = 169;
  }
  if ($connection == true)
  {
    $DATA_UPDATE_COMMANDE = array(
      "etat_commande" => 8
    );
    $DATA_UPDATE_COMMANDE_CONDI = array(
      "id_commande" => $SESSION_VARS['id_commande']
    );
    $db2 = $dbHandler->openConnection();
    $result2 = executeQuery($db2, buildUpdateQuery("ec_commande", $DATA_UPDATE_COMMANDE, $DATA_UPDATE_COMMANDE_CONDI));
    if (DB::isError($result2)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result2->getMessage());
    }else{
      $dbHandler->closeConnection(true);
    }

    ajout_historique($fonction, NULL, $msg_confirmation, $global_nom_login, date("r"), NULL);
    $dbHandler->closeConnection(true);
    $html_msg = new HTML_message($msg_confirmation);
    $msgConfirmation = "Le paiement de la commande est passé en attente";
    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ",  $msgConfirmation));
    $html_msg->addButton("BUTTON_OK", 'Pns-2');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
  else {
    $msg_confirmation = "Paiement commande passer en attente";
    $html_msg = new HTML_erreur($msg_confirmation);
    $msgConfirmation = "Veuillez vous assurez que les données ont été renseigné correctement!";
    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ",  $msgConfirmation));
    $html_msg->addButton("BUTTON_OK", 'Pns-2');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
    exit();
  }
}

else if($global_nom_ecran == 'Pdd-1'){
  global $global_id_benef, $global_client, $global_id_client;

  $Myform = new HTML_GEN2(_("Distribution des bons d'achats"));
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee'];
  $saison = getDetailSaisonCultuAll($whereSaison);
  $saisons_available = '';
  foreach ($saison as $key => $value){
    $saisos_available .= $key.',';
  }
  $saisos_available =  rtrim($saisos_available,",");

  $condition_paiement = "etat_commande IN (8,3) and id_benef =".$SESSION_VARS['id_beneficiaire']."AND id_saison IN (".$saisos_available.")";
  $commande_paiement = getCommande($condition_paiement);
  $choix = array();
  if (isset($commande_paiement)) {
    foreach($commande_paiement as $key=>$value) $choix[$key] = $value["id_commande"];
  };

  $Myform->addField("id_commande", _("Numéro de commande"), TYPC_LSB);
  $Myform->setFieldProperties("id_commande",FIELDP_IS_REQUIRED, true);
  $Myform->setFieldProperties('id_commande', FIELDP_ADD_CHOICES, $choix);



  $Myform->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pdd-2");

  $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
else if($global_nom_ecran == 'Pdd-2'){
  global $dbHandler, $global_id_agence,$global_id_utilisateur,$global_nom_login;
  $Myform = new HTML_GEN2(_("Selection produits"));

  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);
  $condit_stockExist = "id_agent ='".$global_nom_login."'";
  $checkStockExist = getAgentStock($condit_stockExist);
  $check_flux_ba = checkStockAgentParFlux($condit_stockExist,$id_saison['id_saison']);
  if (sizeof($checkStockExist) == null || sizeof($check_flux_ba) == null){
    $msg_confirmation = "Distribution des bons d'achats";
    $html_msg = new HTML_erreur($msg_confirmation);
    $msgConfirmation = "Vous ne disposez pas de stocks de bons d'achats. Veuillez contacter l'administrateur!";
    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ",  $msgConfirmation));
    $html_msg->addButton("BUTTON_OK", 'Pns-2');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
    exit();
  }

  $html = "<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $condi = " id_commande = ".$id_commande." AND etat_paye = 2 and bon_achat is null order by id";
  $list_paiement_details = getPaiementDetail($condi);
  //$nb_prod_stock = sizeof($list_commande_details);
  $html .= "<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=6 align=\"center\"><b>" . _("Details remise bon d'achats") . "</b></TD></TR>";
  $html .= "<TD align=\"center\"><b>" . _("ID detail") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Produit") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Montant payé") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Quantite payé") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Stock disponible") . "</b></TD>";
  $html .= "<TD align=\"center\"><b>" . _("Bon d'achats") . "</b></TD></TR>";

  $html .= "<TR>";
  foreach($list_paiement_details as $key => $value ){
    $html.="<TD><INPUT TYPE=\"text\" NAME =\"id_detail_".$key."\" size=14 value=".$value['id']." readonly ></TD>\n";
    $condi_detail = "id_detail =".$value["id_detail_commande"];
    $detail_commande = getCommandeDetail($condi_detail);
    foreach($detail_commande as $key_detail => $value_details){
      $condi_nom = "id_produit = ".$value_details["id_produit"];
      $nom_produit = getDetailsProduits($condi_nom);
      $produit_name = $nom_produit['libel'];
      $html.="<TD><INPUT TYPE=\"hidden\" NAME =\"libel_prod_".$key."\"size=14 readonly   >".$produit_name."</TD>\n";
    }

    $html.="<TD><INPUT TYPE=\"text\" NAME =\"mnt_paye_".$key."\" size=14 value=".afficheMontant($value['montant_paye'],true)." readonly ></TD>\n";
    $html.="<TD><INPUT TYPE=\"text\" NAME =\"qtite_paye_".$key."\" size=14 value=".$value['qtite_paye']." readonly ></TD>\n";
    $condi_detail = "id_detail = ".$value['id_detail_commande'];
    $commande_detail = getCommandeDetail($condi_detail);
    foreach($commande_detail as $key_detail_commande => $value_detail_commande){
      $condi_stock = "id_produit = ".$value_detail_commande['id_produit']. " and id_agent = '".$global_nom_login."'";
      $stock_agent = getAgentStock($condi_stock);
      foreach($stock_agent as $key_agent_stock => $value_agent_stock){
        if ($value_agent_stock['qtite_ba'] == 0 || $value_agent_stock['qtite_ba']== null){
          $html .= "<TD><INPUT TYPE=\"text\" NAME =\"agent_stock_" . $key . "\" size=14 value=0 readonly ></TD>\n";
        }else {
          $html .= "<TD><INPUT TYPE=\"text\" NAME =\"agent_stock_" . $key . "\" size=14 value=" . $value_agent_stock['qtite_ba'] . " readonly ></TD>\n";
        }
      }
    }
    $html.="<TD><INPUT TYPE=\"text\" NAME =\"bon_achat_".$key."\" size=14 value= 0 Onchange=\"checkBonAchat($key);\" ></TD>\n";
    $html .= "<TR>";
  }

  $js_ba = "
  function checkBonAchat(i) {
    var qtite_paye = eval('document.ADForm.qtite_paye_'+i+'.value');
    var ba_pris = eval('document.ADForm.bon_achat_'+i+'.value');
    var agent_stock = eval('document.ADForm.agent_stock_'+i+'.value');
    if (parseInt(document.getElementsByName('bon_achat_'+i).item(0).value) > parseInt(agent_stock))
    {
     alert('Le stock de l agent est insuffisant pour cette transaction. Veuillez contacter un chef de bureau');
      document.getElementsByName('bon_achat_'+i).item(0).focus();
      document.getElementsByName('bon_achat_'+i).item(0).value = 0;
    }
    else {
      if (parseInt(document.getElementsByName('bon_achat_'+i).item(0).value) > parseInt(qtite_paye) )
      {
      alert('les bons achats choisis sont superieurs à la quantité payée');
      document.getElementsByName('bon_achat_'+i).item(0).focus();
      document.getElementsByName('bon_achat_'+i).item(0).value = 0;
      }
       else if (parseInt(document.getElementsByName('bon_achat_'+i).item(0).value) < parseInt(qtite_paye) )
      {
      alert('les bons achats choisis sont inferieur à la quantité payée');
      document.getElementsByName('bon_achat_'+i).item(0).focus();
      document.getElementsByName('bon_achat_'+i).item(0).value = 0;
      }
    }
  }

  ";
  $Myform->addJS(JSP_FORM, "checkBonAchat", $js_ba);

  $Myform->addHTMLExtraCode("html",$html);

  $SESSION_VARS['id_commande'] = $id_commande;
  $Myform->addFormButton(1,1, "butval", _("Valider"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Pdd-3");

  $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-2");

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

else if($global_nom_ecran == 'Pdd-3'){
  global $dbHandler, $global_id_agence,$global_id_utilisateur,$global_nom_login;
  $db = $dbHandler->openConnection();

  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);

  $date_jour = date("d");
  $date_mois = date("m");
  $date_annee = date("Y");
  $date_total = $date_jour."/".$date_mois."/".$date_annee;

  $connection = false;
  $msg_confirmation = "Distribution des bons d'achats";
  $fonction = 801;

  $condi = " id_commande = ".$SESSION_VARS['id_commande']." AND etat_paye = 2 and bon_achat is null";
  $list_paiement_details = getPaiementDetail($condi);

  foreach($list_paiement_details as $key => $value){
    if (isset(${'id_detail_'.$key}) && ${'bon_achat_'.$key} > 0){
      $DATA_update_paiement = array(
        "bon_achat" => ${'bon_achat_'.$key}
      );
      $DATA_update_condi = array(
        "id" => $key
      );
      $db2 = $dbHandler->openConnection();
      $result2 = executeQuery($db2, buildUpdateQuery("ec_paiement_commande", $DATA_update_paiement, $DATA_update_condi));
      if (DB::isError($result2)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result2->getMessage());
      }else{
        $dbHandler->closeConnection(true);
        $connection = true;
      }
      //$condi_detail = "id_detail = ".${'id_detail_'.$key};
      $condi_detail = "id_detail = ".$value['id_detail_commande'];
      $commande_detail = getCommandeDetail($condi_detail);
      foreach($commande_detail as $key_detail_commande => $value_detail_commande) {
        $condi_stock = "id_produit = " . $value_detail_commande['id_produit'] . " and id_agent = '" . $global_nom_login."'";
        $stock_agent = getAgentStock($condi_stock);
        foreach ($stock_agent as $key_agent_stock => $value_agent_stock) {
          $DATA_update_agent = array(
            "qtite_ba" => $value_agent_stock['qtite_ba'] - ${'bon_achat_'.$key}
          );
          $DATA_update_agent_condi = array(
            "id_agent" => $global_nom_login,
            "id_produit" => $value_detail_commande['id_produit']
          );
          $db3 = $dbHandler->openConnection();
          $result3 = executeQuery($db3, buildUpdateQuery("ec_agent_ba", $DATA_update_agent, $DATA_update_agent_condi));
          if (DB::isError($result3)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__,__LINE__,__FUNCTION__,$result3->getMessage());
          }else{
            $dbHandler->closeConnection(true);
            $connection = true;
          }
        }
      }
    }
  }
  if ($connection == true) {
    ajout_historique($fonction, NULL, $msg_confirmation, $global_nom_login, date("r"), NULL);
    $dbHandler->closeConnection(true);
    $msg_confirmation = "Distribution des bons achats";
    $html_msg = new HTML_message($msg_confirmation);
    $msgConfirmation = "Distribution des bons achats reussies";
    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $msgConfirmation));
    $html_msg->addButton("BUTTON_OK", 'Pns-2');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  }
  else {
    $msg_confirmation = "Distribution des bons achats";
    $html_msg = new HTML_erreur($msg_confirmation);
    $msgConfirmation = "Veuillez vous assurez que les données ont été renseigné correctement!";
    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ",  $msgConfirmation));
    $html_msg->addButton("BUTTON_OK", 'Pns-2');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
    exit();
  }
}

else if($global_nom_ecran == 'Psa-1'){
  global $global_id_benef, $global_client, $global_id_client,$global_id_utilisateur,$global_nom_login;
  $checkAgentDataExist = getAgentStock("id_agent = '".$global_nom_login."'");
  if($checkAgentDataExist == null) {
    $erreur = new HTML_erreur(_("Consultation stock des bons achats agents"));
    $erreur->setMessage(_("Aucun stock pour cette agent"));
    $erreur->addButton(BUTTON_OK, "Pns-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }

  $Myform = new HTML_GEN2(_("Consultation des bons d'achats des agents"));
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);

  $html1  ="<br>";
  $html1 .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nb_prod_stock = sizeof($list_produit)+1;
// En-tête du tableau
  $html1 .="<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=$nb_prod_stock align=\"center\"><b>" . _("Details du stock agent") . "</b></TD></TR>";
  $html1 .= "<TR bgcolor=$colb_tableau>";
  $html1.="<TD align=\"center\"><b>"._("Description")."</b></TD>";
  $nbreProdActif = getNbreProduitActif();

  foreach($list_produit as $key=>$value){
    $i = $key;
    $html1.="<TD align=\"center\"><b>"._($value)."</b></TD>";
  }
  $html1 .= "</TR>";

  $html1 .= "<TR>";
  $html1.="<TD><INPUT TYPE=\"text\" NAME=\"num_livraison\" size=14 value=\"Stock disponible\"  disabled></TD>\n";

  $condi = " etat_produit = 1 ";
  $list_produit = getListeProduitPNSEB($condi);
  $nbreProduit = sizeof($list_produit);
  foreach($list_produit as $key=>$value) {
    $stockBa = getAgentStockSpecific($global_nom_login,$key);
    if ($stockBa == null){
      $html1.="<TD><INPUT TYPE=\"text\" NAME=$key size=14 value=0 disabled></TD>\n";
    }else{
      $html1.="<TD><INPUT TYPE=\"text\" NAME=$key size=14 value=".$stockBa['qtite_ba']." disabled></TD>\n";
    }
  }
  $html1 .= "</TR>";


  $html1.="</TABLE>";
  $html1.="<BR>";

  $Myform->addHTMLExtraCode("html1",$html1);

  $Myform->addFormButton(1, 1, "retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Pns-1');


  $Myform->buildHTML();
  echo $Myform->getHTML();
}


else if ($global_nom_ecran == 'Paa-1'){
  global $global_id_benef, $global_client, $global_id_client,$global_id_utilisateur,$global_nom_login;
  $checkAgentDataExist = getAgentStock("id_agent = '".$global_nom_login."'");

  $allAgent = getLoginDelestage();
  if($allAgent == null) {
    $erreur = new HTML_erreur(_("Consultation stock des bons achats agents"));
    $erreur->setMessage(_("Aucun stock pour cette agent"));
    $erreur->addButton(BUTTON_OK, "Pns-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    exit();
  }

  $Myform = new HTML_GEN2(_("Consultation des bons d'achats de tous les agents"));
  $id_annee_data = getAnneeAgricoleActif();
  $whereSaison = "id_annee = " . $id_annee_data['id_annee']." AND etat_saison = 1 ";
  $id_saison = getDetailSaisonCultu($whereSaison);

  $incre = 0;
  foreach ($allAgent as $nom_login => $value_login) {
    $incre++;
    $html.$nom_login = "<br>";
    $html.$nom_login .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
    $condi = " etat_produit = 1 ";
    $list_produit = getListeProduitPNSEB($condi);
    $nb_prod_stock = sizeof($list_produit) + 1;
// En-tête du tableau
    $html.$nom_login .= "<tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=$nb_prod_stock align=\"center\"><b>" . _("Details du stock agent : ".$value_login['id_agent']) . "</b></TD></TR>";
    $html.$nom_login .= "<TR bgcolor=$colb_tableau>";
    $html.$nom_login .= "<TD align=\"center\"><b>" . _("Description") . "</b></TD>";
    $nbreProdActif = getNbreProduitActif();

    foreach ($list_produit as $key => $value) {
      $i = $key;
      $html.$nom_login .= "<TD align=\"center\"><b>" . _($value) . "</b></TD>";
    }
    $html.$nom_login .= "</TR>";

    $html.$nom_login .= "<TR>";
    $html.$nom_login .= "<TD><INPUT TYPE=\"text\" NAME=\"num_livraison\" size=14 value=\"Stock disponible\"  disabled></TD>\n";

    $condi = " etat_produit = 1 ";
    $list_produit = getListeProduitPNSEB($condi);
    $nbreProduit = sizeof($list_produit);
    foreach ($list_produit as $key => $value) {
      $stockBa = getAgentStockSpecific($value_login['id_agent'], $key);
      if ($stockBa == null){
        $html.$nom_login .= "<TD><INPUT TYPE=\"text\" NAME=$key size=14 value=0 disabled></TD>\n";
      }else{
        $html.$nom_login .= "<TD><INPUT TYPE=\"text\" NAME=$key size=14 value=" . $stockBa['qtite_ba'] . " disabled></TD>\n";
      }

    }
    $html.$nom_login .= "</TR>";


    $html.$nom_login .= "</TABLE>";
    $html.$nom_login .= "<BR>";

    $Myform->addHTMLExtraCode("html".$incre, $html.$nom_login);
  }
  $Myform->addFormButton(1, 1, "retour", _("Retour"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Pns-1');


  $Myform->buildHTML();
  echo $Myform->getHTML();

}
/*}}}*/
else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>