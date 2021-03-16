<?php

//--------- Man-1 : Liste des mandats --------------------------------------------------
//--------- Man-2 : Ajout d'un nouveau mandat ------------------------------------------
//--------- Man-3 : Confirmation de l'ajout d'un mandat --------------------------------
//--------- Man-4 : Modification d'un mandat -------------------------------------------
//--------- Man-5 : Confirmation de la modification d'un mandat ------------------------
//--------- Man-6 : Validation / invalidation d'un mandat ------------------------------
//--------- Man-7 : Confirmation de la validation / invalidation d'un mandat -----------

//--------------------------------------------------------------------------------------
//--------- Man-1 : Liste des mandats --------------------------------------------------
//--------------------------------------------------------------------------------------
if ($global_nom_ecran == 'Man-1') {
  // Génération du titre
  $myForm = new HTML_GEN2(_("Liste des mandats"));

  // Liste des comptes
  $COMPTES = get_comptes_epargne($global_id_client);
  foreach ($COMPTES as $key=>$value) {
    $table =& $myForm->addHTMLTable($key."_1", 6, TABLE_STYLE_ALTERN);
    $table->add_cell(new TABLE_cell($value['num_complet_cpte']."/".$value['devise']." ".$value['libel'], 6, 1));
    $table->add_cell(new TABLE_cell(_("Dénomination"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Type de pouvoir de signature"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Validité du mandat"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Modification"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Validation / Invalidation"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Informations"), 1, 1));
    $MANDATS = getMandats($value['id_cpte']);
    if ($MANDATS != NULL) {
      foreach($MANDATS as $key=>$value) {
        $table->add_cell(new TABLE_cell($value['denomination'], 1, 1));
        $table->add_cell(new TABLE_cell(adb_gettext($adsys['adsys_type_pouv_sign'][$value['type_pouv_sign']]), 1, 1));
        if ($value['valide'] == 't') {
          $table->add_cell(new TABLE_cell(_("Valide"), 1, 1));
        } else {
          $table->add_cell(new TABLE_cell(_("Invalide"), 1, 1));
        }
        $table->add_cell(new TABLE_cell_link(_("Modifier"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Man-4&id_mandat=$key"));
        if ($value['valide'] == 't') {
          $table->add_cell(new TABLE_cell_link(_("Invalider"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Man-6&id_mandat=$key&action=invalider"));
        } else {
          $table->add_cell(new TABLE_cell_link(_("Valider"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Man-6&id_mandat=$key&action=valider"));
        }
        $table->add_cell(new TABLE_cell(_("<A href='#' onclick=\"OpenBrw('../modules/externe/info_mandat.php?m_agc=".$_REQUEST['m_agc']."&id_mandat=".$key."')\">Afficher</A>"), 1,1));
      }
    }
  }

  // Boutons
  $myForm->addFormButton(1, 1, "ajouter", _("Ajouter"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ajouter", BUTP_PROCHAIN_ECRAN, "Man-2");
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-10");
  $myForm->setFormButtonProperties("ajouter", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  
  // Enregistrement - Gestion des mandats
  ajout_historique(90, $global_id_client, 'Gestion des mandats', $global_nom_login, date("r"), NULL);

  // Génération du code
  $myForm->buildHTML();
  echo $myForm->getHTML();
}

//--------------------------------------------------------------------------------------
//--------- Man-2 : Ajout d'un nouveau mandat ------------------------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Man-2') {
  // Changement de monnaie courante
  setMonnaieCourante(NULL);

  // Génération du titre
  $myForm = new HTML_GEN2(_("Ajout d'un mandat"));

  // Ajout des champs
  $myForm->addField("id_cpte", _("ID compte"), TYPC_LSB);
  $COMPTES = get_comptes_epargne($global_id_client);
  foreach ($COMPTES as $key=>$value) {
    $myForm->setFieldProperties("id_cpte", FIELDP_ADD_CHOICES, array($key => $value['num_complet_cpte']."/".$value['devise']." ".$value['libel']));
  }
  $myForm->setFieldProperties("id_cpte", FIELDP_IS_REQUIRED, true);

  $include = array("denomination");
  $myForm->addTable("ad_pers_ext", OPER_INCLUDE, $include);
  $myForm->setFieldProperties("denomination", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("denomination", FIELDP_IS_REQUIRED, true);
  $myForm->addLink("denomination", "rechercher", _("Rechercher"), "#");
  $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/gest_pers_ext.php?m_agc=".$_REQUEST['m_agc']."&denom=denomination&pers_ext=id_pers_ext');return false;"));

  $myForm->addHiddenType("id_pers_ext");

  $include = array("type_pouv_sign", "limitation", "date_exp");
  $myForm->addTable("ad_mandat", OPER_INCLUDE, $include);
  $myForm->setOrder(NULL, $include);
  $myForm->setFieldProperties("limitation", FIELDP_IS_REQUIRED, false);
  $myForm->setFieldProperties("date_exp", FIELDP_IS_REQUIRED, false);

  $order = array("id_cpte", "denomination", "type_pouv_sign", "limitation", "date_exp");
  $myForm->setOrder(NULL, $order);

  // Activation - désactivation de limitation et date_exp pendant l'utilisation
  $JS_type_pouv_sign =
    "if (document.ADForm.HTML_GEN_LSB_type_pouv_sign.value == '1')
  {
    document.ADForm.limitation.disabled = false;
    document.ADForm.HTML_GEN_date_date_exp.disabled = false;
  }
    else
  {
    document.ADForm.limitation.disabled = true;
    document.ADForm.limitation.value = '';
    document.ADForm.HTML_GEN_date_date_exp.disabled = true;
    document.ADForm.HTML_GEN_date_date_exp.value = '';
  }";
  //$myForm->setFieldProperties("type_pouv_sign", FIELDP_JS_EVENT, array("onchange" => $JS_type_pouv_sign)); Ticket REL-63

  // Check sur la dénomination
  $JS_check =
    "if (document.ADForm.denomination.value == '' || document.ADForm.id_pers_ext.value == '')
  {
    msg += '- "._("Le champ \"Dénomination\" doit être renseigné")."\\n';
    ADFormValid = false;
  }";

  $myForm->addJS(JSP_BEGIN_CHECK, "test", $JS_check);

  // Boutons
  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Man-3");
  $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, "Man-1");
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-10");
  $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $myForm->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // Génération du code
  $myForm->buildHTML();
  echo $myForm->getHTML();

  // Changement de monnaie courante
  setMonnaieCourante($global_monnaie_courante);
}

//--------------------------------------------------------------------------------------
//--------- Man-3 : Confirmation de l'ajout d'un mandat --------------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Man-3') {
  debug($_POST);

  $DATA = array('id_cpte' => $id_cpte, 'id_pers_ext' => $SESSION_VARS['gpe']['id_pers_ext'], 'type_pouv_sign' => $type_pouv_sign, 'limitation' => recupMontant($limitation), 'date_exp' => $date_exp);
  $DATA_PERS_EXT = getPersExtDatas($id_pers_ext); debug($DATA);
  if ($DATA_PERS_EXT["id_client"] != NULL)
    $etat = getEtatClient($DATA_PERS_EXT["id_client"]);
  if (($etat == 3) || ($etat == 7)){
  	$MyPage = new HTML_erreur(_("Echec de l'ajout d'un mandat"));
    $msg = _("Le mandataire que vous voulez ajouter est décédé");
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Man-2");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else{
  $result = ajouterMandat($DATA);

  if ($result->errCode == NO_ERR) {
    $myForm = new HTML_message(_("Confirmation de l'ajout d'un mandat"));
    $msg = _("L'ajout du mandat s'est déroulé avec succès");
    $myForm->setMessage($msg);
    $myForm->addButton(BUTTON_OK, "Gen-10");
    $myForm->buildHTML();
    echo $myForm->HTML_code;
  }
 }

}

//--------------------------------------------------------------------------------------
//--------- Man-4 : Modification d'un mandat -------------------------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Man-4') {
  $SESSION_VARS['id_mandat'] = $id_mandat;

  // Changement de monnaie courante
  setMonnaieCourante(NULL);

  // Génération du titre
  $myForm = new HTML_GEN2(_("Modification d'un mandat"));

  // Ajout des champs
  $myForm->addField("id_cpte", _("ID compte"), TYPC_LSB);
  $COMPTES = get_comptes_epargne($global_id_client);
  foreach ($COMPTES as $key=>$value) {
    $myForm->setFieldProperties("id_cpte", FIELDP_ADD_CHOICES, array($key => $value['num_complet_cpte']."/".$value['devise']." ".$value['libel']));
  }
  $myForm->setFieldProperties("id_cpte", FIELDP_IS_REQUIRED, true);

  $include = array("denomination");
  $myForm->addTable("ad_pers_ext", OPER_INCLUDE, $include);
  $myForm->setFieldProperties("denomination", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("denomination", FIELDP_IS_REQUIRED, true);
  $myForm->addLink("denomination", "rechercher", _("Rechercher"), "#");
  $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/gest_pers_ext.php?m_agc=".$_REQUEST['m_agc']."&denom=denomination&pers_ext=id_pers_ext');return false;"));

  $js_6 = "function removeAPOS(){
    var node = document.ADForm.denomination;
    node.value = node.value.replace('&apos;', '\''); 
  }
  
  removeAPOS();
  ";

  $myForm->addJS(JSP_FORM, 'js6', $js_6);
  $include = array("type_pouv_sign", "limitation", "date_exp");
  $myForm->addTable("ad_mandat", OPER_INCLUDE, $include);
  $myForm->setOrder(NULL, $include);
  $myForm->setFieldProperties("limitation", FIELDP_IS_REQUIRED, false);
  $myForm->setFieldProperties("date_exp", FIELDP_IS_REQUIRED, false);

  $order = array("id_cpte", "denomination", "type_pouv_sign", "limitation", "date_exp");
  $myForm->setOrder(NULL, $order);

  // Récupération des informations
  $INFOS = getInfosMandat($id_mandat);
  $myForm->setFieldProperties("id_cpte", FIELDP_DEFAULT, $INFOS['id_cpte']);
  $myForm->setFieldProperties("denomination", FIELDP_DEFAULT, $INFOS['denomination']);
  $myForm->setFieldProperties("type_pouv_sign", FIELDP_DEFAULT, $INFOS['type_pouv_sign']);
  $myForm->setFieldProperties("limitation", FIELDP_DEFAULT, $INFOS['limitation']);
  $myForm->setFieldProperties("date_exp", FIELDP_DEFAULT, $INFOS['date_exp']);
  $myForm->addHiddenType("id_pers_ext", $INFOS['id_pers_ext']);

  // Activation - désactivation de limitation et date_exp pendant l'utilisation
  $JS_type_pouv_sign =
    "if (document.ADForm.HTML_GEN_LSB_type_pouv_sign.value == '1')
  {
    document.ADForm.limitation.disabled = false;
    document.ADForm.HTML_GEN_date_date_exp.disabled = false;
  }
    else
  {
    document.ADForm.limitation.disabled = true;
    document.ADForm.limitation.value = '';
    document.ADForm.HTML_GEN_date_date_exp.disabled = true;
    document.ADForm.HTML_GEN_date_date_exp.value = '';
  }";
  //$myForm->setFieldProperties("type_pouv_sign", FIELDP_JS_EVENT, array("onchange" => $JS_type_pouv_sign)); Ticket REL-63

  // Check sur la dénomination
  $JS_check =
    "if (document.ADForm.denomination.value == '' || document.ADForm.id_pers_ext.value == '')
  {
    msg += '- "._("Le champ \"Dénomination\" doit être renseigné")."\\n';
    ADFormValid = false;
  }";

  $JS_check .= "if (document.ADForm.limitation.value < 0){
    msg += '- "._("Le champ \"Limitation\" ne peut pas être négatif")."\\n';
    ADFormValid = false;
  }";

  $myForm->addJS(JSP_BEGIN_CHECK, "test", $JS_check);

  // Boutons
  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Man-5");
  $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, "Man-1");
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-10");
  $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $myForm->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // Génération du code
  $myForm->buildHTML();
  echo $myForm->getHTML();



  // Changement de monnaie courante
  setMonnaieCourante($global_monnaie_courante);
}

//--------------------------------------------------------------------------------------
//--------- Man-5 : Confirmation de la modification d'un mandat ------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Man-5') {
  $id_mandat = $SESSION_VARS['id_mandat'];

  $DATA = array('id_cpte' => $id_cpte, 'id_pers_ext' => $id_pers_ext, 'type_pouv_sign' => $type_pouv_sign, 'limitation' => recupMontant($limitation), 'date_exp' => $date_exp);
  $DATA_PERS_EXT = getPersExtDatas($id_pers_ext); debug($DATA);
  if ($DATA_PERS_EXT["id_client"] != NULL)
    $etat = getEtatClient($DATA_PERS_EXT["id_client"]);
  if (($etat == 3) || ($etat == 7)){
  	$MyPage = new HTML_erreur(_("Echec de la modification d'un mandat"));
    $msg = _("Le mandataire que vous voulez ajouter est décédé");
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Man-1");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else{
  	$result = modifierMandat($id_mandat, $DATA);

  if ($result->errCode == NO_ERR) {
    $myForm = new HTML_message(_("Confirmation de la modification d'un mandat"));
    $msg = _("La modification du mandat s'est déroulée avec succès");
    $myForm->setMessage($msg);
    $myForm->addButton(BUTTON_OK, "Gen-10");
    $myForm->buildHTML();
    echo $myForm->HTML_code;
  } 
 }
}

//--------------------------------------------------------------------------------------
//--------- Man-6 : Validation / invalidation d'un mandat ------------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Man-6') {
  $SESSION_VARS['action'] = $action;
  $SESSION_VARS['id_mandat'] = $id_mandat;

  if ($action == "invalider") {
    $myForm = new HTML_message(_("Demande de confirmation de l'invalidation d'un mandat"));
    $msg = _("Voulez-vous invalider ce mandat ?");
    $myForm->setMessage($msg);
    $myForm->addCustomButton("oui", _("Oui"), "Man-7");
    $myForm->addCustomButton("non", _("Non"), "Man-1");
    $myForm->addCustomButton("annuler", _("Annuler"), "Gen-10");
    $myForm->buildHTML();
    echo $myForm->HTML_code;
  } else if ($action == "valider") {
    $myForm = new HTML_message(_("Demande de confirmation de la validation d'un mandat"));
    $msg = _("Voulez-vous valider ce mandat ?");
    $myForm->setMessage($msg);
    $myForm->addCustomButton("oui", _("Oui"), "Man-7");
    $myForm->addCustomButton("non", _("Non"), "Man-1");
    $myForm->addCustomButton("annuler", _("Annuler"), "Gen-10");
    $myForm->buildHTML();
    echo $myForm->HTML_code;
  }
}

//--------------------------------------------------------------------------------------
//--------- Man-7 : Confirmation de la validation / invalidation d'un mandat -----------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Man-7') {
  $action = $SESSION_VARS['action'];
  $id_mandat = $SESSION_VARS['id_mandat'];

  if ($action == "invalider") {
    $result = invaliderMandat($id_mandat);
    if ($result->errCode == NO_ERR) {
      $myForm = new HTML_message(_("Confirmation de l'invalidation d'un mandat"));
      $msg = _("L'invalidation du mandat s'est déroulée correctement");
      $myForm->setMessage($msg);
      $myForm->addButton(BUTTON_OK, "Gen-10");
      $myForm->buildHTML();
      echo $myForm->HTML_code;
    }
  } else if ($action == "valider") {
    $result = validerMandat($id_mandat);
    if ($result->errCode == NO_ERR) {
      $myForm = new HTML_message(_("Confirmation de la validation d'un mandat"));
      $msg = _("La validation du mandat s'est déroulée correctement");
      $myForm->setMessage($msg);
      $myForm->addButton(BUTTON_OK, "Gen-10");
      $myForm->buildHTML();
      echo $myForm->HTML_code;
    }
  }

}

//--------------------------------------------------------------------------------------
//--------- Erreur ---------------------------------------------------------------------
//--------------------------------------------------------------------------------------
else signalErreur(__FILE__,__LINE__,__FUNCTION__);
?>