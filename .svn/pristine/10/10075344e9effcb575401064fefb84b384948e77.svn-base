<?php

// Gestion des relations d'un client
// Fonction 11

require_once('lib/dbProcedures/client.php');
require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/html_table_gen.php');
require_once('lib/html/FILL_HTML_GEN2.php');

// Ecran principal
if ($global_nom_ecran == "Rel-1") {
  // Récupère les données sur les relations
  $RELS = getRelationsClient($global_id_client);

  // Création du formulaire
  $MyPage = new HTML_GEN2(_("Gestion des relations"));

  $table =& $MyPage->addHTMLTable('tablerels', /*nbre colonnes*/ 5, TABLE_STYLE_ALTERN);

  $table->add_cell(new TABLE_cell(_("Dénomination"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
  $table->add_cell(new TABLE_cell(_("Relation"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
  $table->add_cell(new TABLE_cell(_("Modification"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
  $table->add_cell(new TABLE_cell(_("Suppression"), 	/*colspan*/ 1, 	/*rowspan*/ 1));
  $table->add_cell(new TABLE_cell(_("Informations"), 	/*colspan*/ 1, 	/*rowspan*/ 1));

  // Construction de la table
  if (is_array($RELS)) {
    foreach($RELS as $key=>$REL) {
      $table->add_cell(new TABLE_cell($REL["denomination"]));
      $table->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_type_relation"][$REL["typ_rel"]])));
      $table->add_cell(new TABLE_cell_link(_("Modifier"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rel-4&id_rel=".$REL['id_rel']."&id_pers_ext=".$REL['id_pers_ext']."&denomination=".$REL['denomination']));
      $table->add_cell(new TABLE_cell_link(_("Supprimer"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rel-6&id_rel=".$REL['id_rel']."&id_pers_ext=".$REL['id_pers_ext']."&denomination=".$REL['denomination']));
      $table->add_cell(new TABLE_cell(_("<A href='#' onclick=\"OpenBrw('../modules/externe/info_pers_ext.php?m_agc=".$_REQUEST['m_agc']."&id_rel=".$REL['id_rel']."&id_pers_ext=".$REL['id_pers_ext']."')\">Afficher</A>"), 1,1));
    }
  }

  // Boutons
  $MyPage->addFormButton(1, 1, "ajout", _("Ajout d'une relation"), TYPB_SUBMIT);
  $MyPage->addFormButton(2, 1, "retour", _("Retour menu"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, "Rel-2");
  $MyPage->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gen-9");

  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}

// Ajout d'une nouvelle relation
else if ($prochain_ecran == 'Rel-2') {
  $JS_check =
    "if (document.ADForm.denomination.value == '' || document.ADForm.id_pers_ext.value == '')
  {
    msg += '- "._("Le champ \"Dénomination\" doit être renseigné")."\\n';
    ADFormValid = false;
  }";

  $myForm = new HTML_GEN2(_("Ajout d'une relation"));

  $include = array('typ_rel');

  $myForm->addTable('ad_rel', OPER_INCLUDE, $include);

  $include = array('denomination');

  $myForm->addTable('ad_pers_ext', OPER_INCLUDE, $include);

  $myForm->setFieldProperties('denomination', FIELDP_IS_LABEL, true);

  $myForm->setFieldProperties('denomination', FIELDP_IS_REQUIRED, true);

  $myForm->addLink('denomination', 'rechercher', _("Rechercher"), "#");

  $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/gest_pers_ext.php?m_agc=".$_REQUEST['m_agc']."&denom=denomination&pers_ext=id_pers_ext');return false;"));

  $myForm->addHiddenType("id_pers_ext");

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Rel-3');
  $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Rel-1');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
  $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $myForm->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->addJS(JSP_BEGIN_CHECK, "test", $JS_check);

  $myForm->buildHTML();
  echo $myForm->getHTML();
}

// Confirmation de l'ajout
else if ($prochain_ecran == 'Rel-3') {
  $DATA = array('typ_rel' => $typ_rel, 'valide' => 't', 'id_client' => $global_id_client, 'id_pers_ext' => $id_pers_ext);
  $DATA_PERS_EXT = getPersExtDatas($id_pers_ext); 
  if ($DATA_PERS_EXT["id_client"] != NULL)
    $etat = getEtatClient($DATA_PERS_EXT["id_client"]);
  if (($etat == 3) || ($etat == 7)){
  	$MyPage = new HTML_erreur(_("Echec de l'ajout d'une relation"));
    $msg = _("La relation que vous voulez ajouter est décédée");
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Rel-2");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }else{
  $result = ajouterRelation($DATA);

  if ($result->errCode == NO_ERR) {
    $MyPage = new HTML_message(_("Confirmation de l'ajout d'une relation"));
    $msg = _("L'ajout de la relation s'est déroulé avec succès");
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Gen-9");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
 }
}

// Modification d'une relation
else if ($prochain_ecran == 'Rel-4') {
  $SESSION_VARS['id_rel'] = $id_rel;
  $SESSION_VARS['id_pers_ext'] = $id_pers_ext;

  $JS_check =
    "if (document.ADForm.denomination.value == '')
  {
    msg += '- "._("Le champ \"Dénomination\" doit être renseigné")."\\n';
    ADFormValid = false;
  }";

  $myForm = new HTML_GEN2(_("Modification d'une relation"));

  $include = array('typ_rel');

  $myForm->addTable("ad_rel", OPER_INCLUDE, $include);

  $myFill = new FILL_HTML_GEN2();
  $myFill->addFillClause('rel_clause', 'ad_rel');
  $myFill->addCondition('rel_clause', 'id_rel', $id_rel);
  $myFill->addManyFillFields('rel_clause', OPER_INCLUDE, $include);
  $myFill->fill($myForm);

  $include = array('denomination');

  $myForm->addTable('ad_pers_ext', OPER_INCLUDE, $include);

  $myForm->setFieldProperties('denomination', FIELDP_DEFAULT, $denomination);
  $myForm->setFieldProperties('denomination', FIELDP_IS_LABEL, true);

  $myForm->addLink('denomination', 'rechercher', _("Rechercher"), "#");

  $myForm->setLinkProperties('rechercher', LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/gest_pers_ext.php?m_agc=".$_REQUEST['m_agc']."&denom=denomination&pers_ext=id_pers_ext');return false;"));

  $myForm->addHiddenType('id_pers_ext');

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Rel-5');
  $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Rel-1');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
  $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $myForm->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->addJS(JSP_BEGIN_CHECK, "test", $JS_check);

  $myForm->buildHTML();
  echo $myForm->getHTML();
}

// Confirmation de la modification
else if ($prochain_ecran == 'Rel-5') {
  $id_rel = $SESSION_VARS['id_rel'];

  if ($id_pers_ext == NULL) {
    $id_pers_ext = $SESSION_VARS['id_pers_ext'];
  }

  $DATA = array('typ_rel' => $typ_rel, 'valide' => 't', 'id_client' => $global_id_client, 'id_pers_ext' => $id_pers_ext);
  $DATA_PERS_EXT = getPersExtDatas($id_pers_ext); 
  if ($DATA_PERS_EXT["id_client"] != NULL)
    $etat = getEtatClient($DATA_PERS_EXT["id_client"]);
    if (($etat == 3) || ($etat == 7)){
  	  $MyPage = new HTML_erreur(_("Echec de la modification d'une relation"));
      $msg = _("La relation que vous voulez ajouter est décédée");
      $MyPage->setMessage($msg);
      $MyPage->addButton(BUTTON_OK, "Rel-1");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    }else {
    $result = modifierRelation($id_rel, $DATA);

    if ($result->errCode == NO_ERR) {
      $MyPage = new HTML_message(_("Confirmation de la modification d'une relation"));
      $msg = _("La modification de la relation s'est déroulée avec succès");
      $MyPage->setMessage($msg);
      $MyPage->addButton(BUTTON_OK, "Gen-9");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    }
   }

}

// Suppression d'une relation
else if ($prochain_ecran == 'Rel-6') {
  $SESSION_VARS['id_rel'] = $id_rel;

  $myForm = new HTML_GEN2(_("Suppression d'une relation"));

  $include = array('typ_rel');

  $myForm->addTable("ad_rel", OPER_INCLUDE, $include);

  $myFill = new FILL_HTML_GEN2();
  $myFill->addFillClause('rel_clause', 'ad_rel');
  $myFill->addCondition('rel_clause', 'id_rel', $id_rel);
  $myFill->addManyFillFields('rel_clause', OPER_INCLUDE, $include);
  $myFill->fill($myForm);

  $myForm->setFieldProperties('typ_rel', FIELDP_IS_LABEL, true);

  $include = array('denomination');

  $myForm->addTable('ad_pers_ext', OPER_INCLUDE, $include);

  $myForm->setFieldProperties('denomination', FIELDP_DEFAULT, $denomination);

  $myForm->setFieldProperties('denomination', FIELDP_IS_LABEL, true);

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Rel-7');
  $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Rel-1');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9');
  $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $myForm->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->addJS(JSP_BEGIN_CHECK, "test", $JS_check);

  $myForm->buildHTML();
  echo $myForm->getHTML();

}

// Confirmation de la suppression
else if ($prochain_ecran == 'Rel-7') {
  $id_rel = $SESSION_VARS['id_rel'];

  $result = supprimerRelation($id_rel);

  if ($result->errCode == NO_ERR) {
    $MyPage = new HTML_message(_("Confirmation de la suppression d'une relation"));
    $msg = _("La suppression de la relation s'est déroulée avec succès");
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Gen-9");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }

}

else {
  signalErreur(__FILE__,__LINE__,__FUNCTION__);
}