<?php

// gestion_ODC.php
// 27/12/2004
// interface pour la gestion des opérations diverses de caisse/compte
// Ajouté dans ADbanking version 2.0.0

require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/html/FILL_HTML_GEN2.php';

if ($global_nom_ecran == 'Odc-1') { // Gestion des opérations diverses de caisse/compte
  $ODC=array();
  $ODC = getODC(); // Récupère de toutes les opérations diverses de caisse/compte

  $myForm = new HTML_GEN2(_("Gestion des opérations diverses de caisse/compte"));

  $xtHTML = "<br /><TABLE align=\"center\" cellpadding=\"5\" width=\"95%\">";
  $xtHTML .= "\n<tr align=\"center\" bgcolor=\"$colb_tableau\"><td><b>"._("N°")."</b></td><td><b>"._("LIBELLE")."</b></td><td><b></b></td></tr>";
  $color = $colb_tableau;
  foreach($ODC as $key=>$value) {
    // Génération du HTML en conséquence
    $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);
    $xtHTML .= "\n<tr bgcolor=\"$color\">";
    $xtHTML .= "<td width=\"10%\">".$value['type_operation']."</td>";
    $libel_ope = new Trad($value['libel_ope']);
    $xtHTML .= "<td>".$libel_ope->traduction();

    $xtHTML .= "<td align=\"right\" width=\"10%\"><a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Odc-4&id_oper=".$value['type_operation']."\">"._("modifier")."</a>";
    $xtHTML .= "&nbsp&nbsp<a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Odc-6&id_oper=".$value['type_operation']."\">"._("supprimer")."</a></td>";
    $xtHTML .= "</tr>";

  }
  $xtHTML .= "</TABLE>";
  $myForm->addHTMLExtraCode("xtHTML", $xtHTML);

  $myForm->addFormButton(1, 1, "ajout", _("Ajouter"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, 'Odc-2');

  $myForm->addFormButton(1, 2, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-14');

  $myForm->buildHTML();
  echo $myForm->getHTML();
} else if ($global_nom_ecran == 'Odc-2') { // Saisie opération diverse de caisse/compte
  global $global_id_agence;
  $myForm = new HTML_GEN2(_("Saisie opération diverse de caisse/compte"));

  // Libellé de l'opération
  $myForm->addField("libel",_("Libellé opération"), TYPC_TTR);
  $myForm->setFieldProperties("libel", FIELDP_IS_REQUIRED, true);

  // Sens de l'opération
  $sens=array("d"=>_("Débit"),"c"=>_("Crédit"));
  $myForm->addField("sens_operation",_("Sens caisse/compte"), TYPC_LSB);
  $myForm->setFieldProperties("sens_operation", FIELDP_ADD_CHOICES, $sens);
  $myForm->setFieldProperties("sens_operation", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("sens_operation", FIELDP_IS_REQUIRED, true);

  // Type de mouvement
  $type=array("2"=>_("Mouvement de caisse"),"3"=>_("Mouvement de compte"));
  $myForm->addField("type_mouv",_("Type de mouvement"), TYPC_LSB);
  $myForm->setFieldProperties("type_mouv", FIELDP_ADD_CHOICES, $type);
  $myForm->setFieldProperties("type_mouv", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("type_mouv", FIELDP_IS_REQUIRED, true);

  // Liste de tous les comptes comptables
  $CC = getComptesComptables();

  // Comptes comptables des guichets
  $cptes_gui = array();
  $guichets = getLibelGuichet();
  if (isset($guichets))
    foreach($guichets as $key=>$value)
    array_push($cptes_gui,$key);

  // Exclure de cette liste les guichets et les comptes centralisateurs
  $comptes = array();
  if (isset($CC))
    foreach( $CC as $compte)
    if (!isCentralisateur($compte['num_cpte_comptable']) && !in_array($compte['num_cpte_comptable'], $cptes_gui) )
      $comptes[$compte['num_cpte_comptable']]=$compte['num_cpte_comptable']." ". $compte['libel_cpte_comptable'];

  // Compte de contrepartie
  $myForm->addField("contrepartie",_("Compte contrepartie"), TYPC_LSB);
  $myForm->setFieldProperties("contrepartie", FIELDP_ADD_CHOICES, $comptes);
  $myForm->setFieldProperties("contrepartie", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("contrepartie", FIELDP_IS_REQUIRED, true);

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Odc-3');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Odc-1');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

} else if ($global_nom_ecran == 'Odc-3') { // Ajout opération diverse
  //$id_oper = $SESSION_VARS["id_oper"];
  $myErr=creationODC($libel,$sens_operation,$type_mouv,$contrepartie);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec création opération diverse de caisse/compte"));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Odc-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("L'opération a été enregistrée avec succès"));
    $myMsg->addButton(BUTTON_OK, 'Odc-1');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }

} else if ($global_nom_ecran == 'Odc-4') { // Saisie modification
  global $global_id_agence;
  $myForm = new HTML_GEN2(_("Modification opération diverse de caisse/compte"));
  if (!strstr($global_nom_ecran_prec,"Odc-1")) {
		$id_oper = $SESSION_VARS["id_oper"];
	}
  $SESSION_VARS["id_oper"] = $id_oper;

  $ODC=array();
  $ODC = getODC(); // Récupère de toutes les opérations diverses de caisse/compte

  $myForm->addTable("ad_cpt_ope", OPER_EXCLUDE,array("categorie_ope"));
  $myForm->setFieldProperties("type_operation", FIELDP_DEFAULT,$id_oper);
  $myForm->setFieldProperties("type_operation", FIELDP_IS_LABEL, true);
  $libel_ope = new Trad($ODC[$id_oper]["libel_ope"]);
  $myForm->setFieldProperties("libel_ope", FIELDP_DEFAULT,$libel_ope);
  $order = array("type_operation", "libel_ope");
  $myForm->setOrder(NULL, $order);

  //comptes au débit et crédit dans le schemas comptable
  $MyError = getDetailsOperation($id_oper);
  $DetailsOperation = $MyError->param;

  // Sens du compte et type de mouvement
  if ($DetailsOperation["debit"]["categorie"] == 2 || $DetailsOperation["debit"]["categorie"] == 4) { //la caisse/compte est en premier lieu
    $sensoperation = $DetailsOperation["debit"]["sens"];
    $ctrepartie = $DetailsOperation["credit"]["compte"];
    if ($DetailsOperation["debit"]["categorie"] == 2) { // mouvement de compte
      $typemouv = "3";
    } else { // mouvement de caisse
      $typemouv = "2";
    }
  } else if ($DetailsOperation["credit"]["categorie"] == 2 || $DetailsOperation["credit"]["categorie"] == 4) { // la caisse/compte est en deuxième lieu
    $sensoperation=$DetailsOperation["credit"]["sens"];
    $ctrepartie = $DetailsOperation["debit"]["compte"];
    if ($DetailsOperation["credit"]["categorie"] == 2) { // mouvement de compte
      $typemouv = "3";
    } else { // mouvement de caisse
      $typemouv = "2";
    }
  }

  // Sens de l'opération
  $sens=array("d"=>_("Débit"),"c"=>_("Crédit"));
  $myForm->addField("sens_operation",_("Sens opération"), TYPC_LSB);
  $myForm->setFieldProperties("sens_operation", FIELDP_ADD_CHOICES, $sens);
  $myForm->setFieldProperties("sens_operation", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("sens_operation", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("sens_operation", FIELDP_DEFAULT,$sensoperation);

  // Type de mouvement
  $type=array("2"=>_("Mouvement de caisse"),"3"=>_("Mouvement de compte"));
  $myForm->addField("type_mouv",_("Type de mouvement"), TYPC_LSB);
  $myForm->setFieldProperties("type_mouv", FIELDP_ADD_CHOICES, $type);
  $myForm->setFieldProperties("type_mouv", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("type_mouv", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("type_mouv", FIELDP_DEFAULT,$typemouv);

  // Compte de contrepartie
  $CC = getComptesComptables();
  //Retirer de la liste les comptes associés aux états de crédits, aux produits d'épargne et les comptes de part sociales : voir #1990.
  foreach($CC as $key=>$value){
		if(isComptesCredits($value['num_cpte_comptable']) || isComptesEpargne($value['num_cpte_comptable']) || isComptesGaranties($value['num_cpte_comptable'])){
			unset($CC[$key]);
		}
	}
  $comptes = array();
  if (isset($CC))
    foreach( $CC as $compte)
    $comptes[$compte['num_cpte_comptable']]=$compte['num_cpte_comptable']." ". $compte['libel_cpte_comptable'];

  $myForm->addField("contrepartie",_("Compte contrepartie"), TYPC_LSB);
  $myForm->setFieldProperties("contrepartie", FIELDP_ADD_CHOICES, $comptes);
  $myForm->setFieldProperties("contrepartie", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("contrepartie", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("contrepartie", FIELDP_DEFAULT,$ctrepartie);

  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ajouTaxe", _("Associer taxe"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Odc-5');
  $myForm->setFormButtonProperties("ajouTaxe", BUTP_PROCHAIN_ECRAN, 'Odc-8');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Odc-1');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

} else if ($global_nom_ecran == 'Odc-5') { // modification

  $type_oper = $SESSION_VARS["id_oper"];
  $myErr=modificationODC($type_oper,$libel_ope,$sens_operation,$type_mouv,$contrepartie);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec modification opération diverse de caisse/compte"));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Odc-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("L'opération a été enregistrée avec succès"));
    $myMsg->addButton(BUTTON_OK, 'Odc-1');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }

} else if ($global_nom_ecran == 'Odc-6') { // demande de confirmation suppression
  $SESSION_VARS["id_oper"]= $id_oper;

  $myForm = new HTML_GEN2(_("Confirmation suppression"));

  $ODC=array();
  $ODC = getODC(); // Récupère de toutes les opérations diverses de caisse

  $myForm->addTable("ad_cpt_ope", OPER_EXCLUDE,array("categorie_ope"));
  $myForm->setFieldProperties("type_operation", FIELDP_DEFAULT,$id_oper);
  $myForm->setFieldProperties("type_operation", FIELDP_IS_LABEL, true);
  $libel_ope = new Trad($ODC[$id_oper]["libel_ope"]);
  $myForm->setFieldProperties("libel_ope", FIELDP_DEFAULT,$libel_ope);
  $myForm->setFieldProperties("libel_ope", FIELDP_IS_LABEL, true);


  $myForm->addFormButton(1, 1, "sup", _("Supprimer"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("sup", BUTP_PROCHAIN_ECRAN, 'Odc-7');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Odc-1');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();

} else if ($global_nom_ecran == 'Odc-7') { // suppression
  $type_oper = $SESSION_VARS["id_oper"];
  // supprimer les taxes auquelles l'opération est liée
  $myErr = deleteTaxeOperation($type_oper);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec suppression taxes liées à l'opération diverse de caisse/compte"));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Odc-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

  $myErr=suppressionODC($type_oper);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec suppression opération diverse de caisse/compte"));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Odc-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("L'opération a été supprimée avec succès"));
    $myMsg->addButton(BUTTON_OK, 'Odc-1');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }
} else if ($global_nom_ecran == 'Odc-8') {
	$myForm = new HTML_GEN2(_("Ajout de taxe à une opération"));
	$id_oper = $SESSION_VARS["id_oper"];

	$taxesOperation = getTaxesOperation($id_oper);
	$taxesOperation = $taxesOperation->param;
	if (sizeof($taxesOperation) > 0){
		$retour1 .= "<br/><TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0 border=1>\n";
	  $retour1 .= "<TR bgcolor=\"$colb_tableau\">\n";
	  $retour1 .= "<TD colspan=8 align=\"left\"><b>".sprintf(_("Taxes associées à l'opération %s"),$id_taxe)."</b></TD>\n";
	  $retour1 .= "</TR>\n";
	  $retour1 .= "<TR bgcolor=\"$colb_tableau\">\n";
	  $retour1 .= "<TD align=\"center\">"._("ID taxe")."</TD>\n";
	  $retour1 .= "<TD align=\"center\">"._("libel")."</TD>\n";
	  $retour1 .= "<TD align=\"center\">"._("Taux")."</TD>\n";
	  $retour1 .= "<TD align=\"center\">"._("Supprimer taxe")."</TD>\n";
	  $retour1 .= "</TR>\n";

	  foreach ($taxesOperation as $key=>$val) {
	    // Affichage
	  	$num_remb = $val['num_remb'];
	    $retour1 .= "<TR bgcolor=\"$colb_tableau\">\n";
	    $retour1 .= "<TD align=\"center\">".$val['id_taxe']."</TD>\n";
	    $retour1 .= "<TD align=\"left\">".$val['libel_taxe']."</TD>\n";
	    $retour1 .= "<TD align=\"right\">".$val["taux"]*(100)."</TD>\n";
	    $retour1 .= "<TD align=\"center\"><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Odc-10&id_oper=".$id_oper."&id_taxe=".$val['id_taxe']."\">"._("Sup")."</A></TD>";
	    $retour1 .= "</TR>\n";
	   }
	   $retour1 .= "</TABLE><BR>";
	   $myForm->addHTMLExtraCode("remb".$id_oper, $retour1);
	}

	 $liste_taxe = getListeTaxes();
	 $myForm->addField("id_taxe", _("Taxe à appliquer"), TYPC_LSB);
   $myForm->setFieldProperties("id_taxe", FIELDP_ADD_CHOICES, $liste_taxe);
   $myForm->setFieldProperties("id_taxe", FIELDP_JS_EVENT, array("OnChange"=>"updateTaxe();verifyTypeTaxe();"));
   $myForm->setFieldProperties("id_taxe", FIELDP_IS_REQUIRED,true);
   $myForm->addHiddenType("type_taxe");
   $myForm->addField("taux_taxe", _("Taux de la taxe"), TYPC_PRC);
   $myForm->setFieldProperties("taux_taxe", FIELDP_IS_LABEL,false);
   $myForm->setFieldProperties("taux_taxe", FIELDP_IS_LABEL,true);

	 $taxesInfos = getTaxesInfos();
   $js = "function updateTaxe()\n{\n ";
   foreach($taxesInfos as $key=>$value) {
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value == ".$value["id"].")) {";
     $js .= "document.ADForm.taux_taxe.value = '".$value["taux"]*(100)."';";
     $js .= "document.ADForm.type_taxe.value = '".$value["type_taxe"]."';";
     $js .= "}";

   }
	 $js .= "};";
	 $js .= "updateTaxe();";
	 $myForm->addJS(JSP_FORM, "jstest", $js);

	  $js = "function verifyTypeTaxe()\n{\n ";
   foreach($taxesOperation as $key=>$value) {
     $js .= "if ((document.ADForm.HTML_GEN_LSB_id_taxe.value != 0) && (document.ADForm.type_taxe.value == ".$value["type_taxe"].")) {";
     $js .= "window.alert('".sprintf(_('- L opération est déjà liée à une taxe de ce type.\n Cette dernière sera remplacée, si vous continuez la procédure!'))."');";
     $js .= "}";
   }
	 $js .= "};";
	 $js .= "verifyTypeTaxe();";
	 $myForm->addJS(JSP_FORM, "jsverify", $js);

	 $myForm->addFormButton(1, 1, "ajouter", _("Ajouter"), TYPB_SUBMIT);
	 $myForm->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
	 //$myForm->setFormButtonProperties("ajouter", BUTP_JS_EVENT, array("onclick" => "verifyTypeTaxe()"));
	 $myForm->setFormButtonProperties("ajouter", BUTP_PROCHAIN_ECRAN, 'Odc-9');
	 $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Odc-4');
	 $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);



	 $myForm->buildHTML();
	 echo $myForm->getHTML();

}else if ($global_nom_ecran == 'Odc-9') {

 $temp['type_operation'] = $SESSION_VARS["id_oper"];
 // insertion des taxes
	if (isset($id_taxe)){


		// insertion
		$myErr = insertTaxeOperation($temp['type_operation'], $id_taxe);
		if ($myErr->errCode != NO_ERR) {
	 		$html_err = new HTML_erreur(_("Echec lors de l'ajout. "));
	    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
	    $html_err->addButton("BUTTON_OK", 'Odc-8');
	    $html_err->buildHTML();
	    echo $html_err->HTML_code;

   }else {
	    $myMsg = new HTML_message(_("Confirmation ajout de taxe"));
	    $myMsg->setMessage(_("La taxe est bien associée à l'opération"));
	    $myMsg->addButton(BUTTON_OK, 'Odc-8');
	    $myMsg->buildHTML();
	    echo $myMsg->HTML_code;
  }
	}
	else {
    $html_err = new HTML_erreur(_("Echec lors de l'ajout. "));
    $html_err->setMessage(_("Erreur: il faut choisir une taxe"));
    $html_err->addButton("BUTTON_OK", 'Odc-8');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

}else if ($global_nom_ecran == 'Odc-10') {

  $temp['type_operation'] = $SESSION_VARS["id_oper"];
 // suppression des taxes
	if (isset($id_taxe)){
		$myErr = deleteTaxeOperation($temp['type_operation'], $id_taxe);
	}
	else {
    $html_err = new HTML_erreur(_("Echec lors de la suppression de la taxe. "));
    $html_err->setMessage(_("Erreur: il faut choisir une taxe"));
    $html_err->addButton("BUTTON_OK", 'Odc-8');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }
  if ($myErr->errCode != NO_ERR) {
 		$html_err = new HTML_erreur(_("Echec lors de la suppression de la taxe. "));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Odc-8');
    $html_err->buildHTML();
    echo $html_err->HTML_code;

  } else {
    $myMsg = new HTML_message(_("Confirmation suppression de taxe"));
    $myMsg->setMessage(_("La taxe a été bien supprimée"));
    $myMsg->addButton(BUTTON_OK, 'Odc-8');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }

}else
  signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu !"
?>