<?php

// gestion_ecriture_libre.php
// interface pour la gestion des libellés des écritures libres
// Ajouté dans ADbanking version 3.2.1

require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/html/FILL_HTML_GEN2.php';

if ($global_nom_ecran == 'Gel-1') { // Gestion des écritures libres
  $Gel=array();
  $Gel = getLEL(); // Récupère de tous les libellés des écritures libres

  $myForm = new HTML_GEN2(_("Gestion des écritures libres"));

  $xtHTML = "<br /><TABLE align=\"center\" cellpadding=\"5\" width=\"95%\">";
  $xtHTML .= "\n<tr align=\"center\" bgcolor=\"$colb_tableau\"><td><b>"._("N°")."</b></td><td><b>"._("LIBELLE")."</b></td><td><b></b></td></tr>";
  $color = $colb_tableau;
  foreach($Gel as $key=>$value) {
    // Génération du HTML en conséquence
    $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);
    $xtHTML .= "\n<tr bgcolor=\"$color\">";
    $xtHTML .= "<td width=\"10%\">".$value['type_operation']."</td>";
    $libel_ope = new Trad($value['libel_ope']);
    $xtHTML .= "<td>".$libel_ope->traduction();

    $xtHTML .= "<td align=\"right\" width=\"10%\"><a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gel-4&id_oper=".$value['type_operation']."\">"._("modifier")."</a>";
    $xtHTML .= "&nbsp&nbsp<a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gel-6&id_oper=".$value['type_operation']."\">"._("supprimer")."</a></td>";
    $xtHTML .= "</tr>";

  }
  $xtHTML .= "</TABLE>";
  $myForm->addHTMLExtraCode("xtHTML", $xtHTML);

  $myForm->addFormButton(1, 1, "ajout", _("Ajouter"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, 'Gel-2');

  $myForm->addFormButton(1, 2, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-14');

  $myForm->buildHTML();
  echo $myForm->getHTML();
} else if ($global_nom_ecran == 'Gel-2') { // Saisie libellés des écritures libres
  global $global_id_agence;
  $myForm = new HTML_GEN2(_("Saisie libellé écriture libre"));

  // Libellé de l'opération
  $myForm->addField("libel",_("Libellé écriture libre"), TYPC_TTR);
  $myForm->setFieldProperties("libel", FIELDP_IS_REQUIRED, true);

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Gel-3');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gel-1');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

} else if ($global_nom_ecran == 'Gel-3') { // Ajout libellé écriture libre
  //$id_oper = $SESSION_VARS["id_oper"];
  $myErr=creationLEL($libel);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec création libellé écriture libre"));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gel-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("L'opération a été enregistrée avec succès"));
    $myMsg->addButton(BUTTON_OK, 'Gel-1');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }

} else if ($global_nom_ecran == 'Gel-4') { // Saisie modification
  global $global_id_agence;
  $myForm = new HTML_GEN2(_("Modification libellé écriture libre"));
  if (!strstr($global_nom_ecran_prec,"Gel-1")) {
		$id_oper = $SESSION_VARS["id_oper"];
	}
  $SESSION_VARS["id_oper"] = $id_oper;

  $Gel=array();
  $Gel = getLEL(); // Récupère de tous les libellés des écritures libres

  $myForm->addTable("ad_cpt_ope", OPER_EXCLUDE,array("categorie_ope"));
  $myForm->setFieldProperties("type_operation", FIELDP_DEFAULT,$id_oper);
  $myForm->setFieldProperties("type_operation", FIELDP_IS_LABEL, true);
  $libel_ope = new Trad($Gel[$id_oper]["libel_ope"]);
  $myForm->setFieldProperties("libel_ope", FIELDP_DEFAULT,$libel_ope);

  $order = array("type_operation", "libel_ope");
  $myForm->setOrder(NULL, $order);

  //comptes au débit et crédit dans le schemas comptable
  $MyError = getDetailsOperation($id_oper);
  $DetailsOperation = $MyError->param;

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Gel-5');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gel-1');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

} else if ($global_nom_ecran == 'Gel-5') { // modification

  $type_oper = $SESSION_VARS["id_oper"];
  $myErr=modificationLEL($type_oper, $libel_ope);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec modification libellé écriture libre"));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gel-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("Le libellé a été enregistrée avec succès"));
    $myMsg->addButton(BUTTON_OK, 'Gel-1');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }

} else if ($global_nom_ecran == 'Gel-6') { // demande de confirmation suppression
  $SESSION_VARS["id_oper"]= $id_oper;

  $myForm = new HTML_GEN2(_("Confirmation suppression"));

  $Gel=array();
  $Gel = getLEL(); // Récupère de toutes les opérations diverses de caisse

  $myForm->addTable("ad_cpt_ope", OPER_EXCLUDE,array("categorie_ope"));
  $myForm->setFieldProperties("type_operation", FIELDP_DEFAULT,$id_oper);
  $myForm->setFieldProperties("type_operation", FIELDP_IS_LABEL, true);
  $libel_ope = new Trad($Gel[$id_oper]["libel_ope"]);
  $myForm->setFieldProperties("libel_ope", FIELDP_DEFAULT,$libel_ope);
  $myForm->setFieldProperties("libel_ope", FIELDP_IS_LABEL, true);

  $myForm->addFormButton(1, 1, "sup", _("Supprimer"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("sup", BUTP_PROCHAIN_ECRAN, 'Gel-7');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gel-1');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

} else if ($global_nom_ecran == 'Gel-7') { // suppression
  $type_oper = $SESSION_VARS["id_oper"];
  $myErr=suppressionLEL($type_oper);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec suppression libellé écriture libre"));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gel-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("Le libellé a été supprimée avec succès"));
    $myMsg->addButton(BUTTON_OK, 'Gel-1');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }
} else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu !"
?>