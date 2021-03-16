<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

// gestion_operations.php
// TF - 27/08/2003
// interface pour la gestion de l'association des comptes comptables débités ou crédités
// selon les opérations financières
// Ajouté dans ADbanking version 1.0

require_once 'lib/dbProcedures/compta.php';
require_once 'modules/compta/xml_compta.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/misc/divers.php';
require_once('lib/misc/tableSys.php');
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';

/*{{{ Gop-1 : Gestion des opérations */
if ($global_nom_ecran == 'Gop-1') {
  // Récupère tous comptes associés par opération
  $myErr = getOperations(0); // Récupère tous comptes associés par opération

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec du traitement. "));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-14');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  } else
    $OP = $myErr->param;

  // $allAssigned = true; // Variable utilisée pour savoir si toutes les opérations ont été assignées.

  $myForm = new HTML_GEN2(_("Gestion des opérations financières"));

  $xtHTML = "<br><TABLE align=\"center\" cellpadding=\"5\" width=\"95%\">";
  $xtHTML .= "\n<tr align=\"center\" bgcolor=\"$colb_tableau\"><td><b>"._("N°")."</b></td><td><b>"._("LIBELLE")."</b></td></tr>";
  $color = $colb_tableau;
  $allAssigned = true;
  while (list(, $operation) = each($OP)) {
    if ($operation["type_operation"] < 1000) { // Exclure les opération diverses (>= 1000)
      $problem = false;
      // Génération du HTML en conséquence
      $color = ($color == $colb_tableau_altern ? $colb_tableau : $colb_tableau_altern);
      if (($operation["cptes"]["d"]["categorie_cpte"] == 0 && $operation["cptes"]["d"]["num_cpte"] == NULL) || ($operation["cptes"]["c"]["categorie_cpte"] == 0 && $operation["cptes"]["c"]["num_cpte"] == NULL)) {
        // Cette opération n'a pas encore été paramétrée
        $allAssigned = false ;
        $problem = true;
      }
      $xtHTML .= "\n<tr bgcolor=\"$color\">";
      $xtHTML .= "<td><a href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gop-2&id_oper=".$operation['type_operation']."\">".$operation['type_operation']."</a></td><td>";
      if ($problem)
        $xtHTML .= "<FONT COLOR=\"red\">";
      $libel_ope = new Trad($operation['libel_ope']);
      $xtHTML .= $libel_ope->traduction();
      if ($problem)
        $xtHTML .= "</FONT>";
      $xtHTML .= "</td></tr>";
    }
  }


  $xtHTML .= "</TABLE>";
  $myForm->addHTMLExtraCode("xtHTML", $xtHTML);
  $myForm->addFormButton(1, 1, "imprimer", _("Imprimer"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("imprimer", BUTP_PROCHAIN_ECRAN, 'Gop-4');
  $myForm->addFormButton(1, 2, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-14');

  if ($allAssigned == false) // Si au moins une fonction n'a pas été assignée
    $myForm->setFormButtonProperties("retour", BUTP_JS_EVENT, array("onclick" =>
                                     "if (!confirm('"._("ATTENTION")."\\n "._("Certaines opérations n\'ont pas été renseignées (opérations en rouge). \\nPar conséquent, le logiciel peut ne pas fonctionner correctement.\\nEtes-vous sur de vouloir quitter l\'écran ? ")."')) return false;"));

  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*}}}*/

/*{{{ Gop-2 : Modification d'une opération */
else if ($global_nom_ecran == 'Gop-2') {
	$myForm = new HTML_GEN2(_("Modification d'une opération"));

	if (!strstr($global_nom_ecran_prec,"Gop-1")) {
		$id_oper = $SESSION_VARS["id_oper"];
	}
	//$myForm->addTable("ad_cpt_ope", OPER_NONE, NULL);
	$myForm->addTable("ad_cpt_ope",OPER_EXCLUDE,array("categorie_ope"));
	//$myForm->addTable("ad_cpt_ope_cptes", OPER_EXCLUDE,array("type_operation"));

	$def = new FILL_HTML_GEN2();
	$def->addFillClause("num", "ad_cpt_ope");
	$def->addCondition("num", "type_operation", $id_oper);
	$def->addCondition("num", "id_ag", $global_id_agence);
	//$def->addManyFillFields("num", OPER_NONE, NULL);
	$def->addManyFillFields("num", OPER_EXCLUDE,array("categorie_ope"));
	$def->fill($myForm);
	//comptes au débit et crédit dans le schemas comptable
	$MyError = getDetailsOperation($id_oper);
	$DetailsOperation = $MyError->param;

	//Recupere le numero des comptes comptables à afficher dans la liste box
	$CC = getComptesComptables();

	$cpte_comptable = array();
	if (isset($CC))
	while (list(,$compte) = each($CC)) {
		$mouve =isCentralisateur($compte['num_cpte_comptable']);
		$devise = $compte['devise'];

		global $global_multidevise;
		// En mode unidevise, on ne peut pas assigner de compte centralisateur
		if ($global_multidevise == false) {
			if ($mouve == false )
			$cpte_comptable[$compte['num_cpte_comptable']]=$compte['num_cpte_comptable']." ". $compte['libel_cpte_comptable'];
		} else { // En mode multidevise, on peut assigner des opérations à des comptes centralisateurs si ces comptes n'ont que des sous comptes en devise différente, càd sans devise associée
			if ($devise == false || $mouve == false)
			$cpte_comptable[$compte['num_cpte_comptable']]=$compte['num_cpte_comptable']." ". $compte['libel_cpte_comptable'];
		}

	}
	//Compte au debit
	if ($DetailsOperation['debit']['categorie'] != 0) {
		$myForm->addField("cpte_debit", _("Compte au débit"), TYPC_TXT);
		$myForm->setFieldProperties("cpte_debit", FIELDP_DEFAULT, adb_gettext($adsys["adsys_categorie_compte"][$DetailsOperation['debit']['categorie']]));
		$myForm->setFieldProperties("cpte_debit", FIELDP_IS_LABEL,true);
	} else {
		$myForm->addField("cpte_debit", _("Compte au débit"), TYPC_LSB);
		$myForm->setFieldProperties("cpte_debit", FIELDP_ADD_CHOICES, $cpte_comptable);
		$myForm->setFieldProperties("cpte_debit", FIELDP_DEFAULT, $DetailsOperation['debit']['compte']);
		$myForm->setFieldProperties("cpte_debit", FIELDP_IS_REQUIRED,true);
	}
	//Compte au credit
	if ($DetailsOperation['credit']['categorie'] != 0) {
		$myForm->addField("cpte_credit", _("Compte au credit"), TYPC_TXT);
		$myForm->setFieldProperties("cpte_credit", FIELDP_DEFAULT,adb_gettext($adsys["adsys_categorie_compte"][$DetailsOperation['credit']['categorie']]));
		$myForm->setFieldProperties("cpte_credit", FIELDP_IS_LABEL,true);
	} else {
		$myForm->addField("cpte_credit", _("Compte au crédit"), TYPC_LSB);
		$myForm->setFieldProperties("cpte_credit", FIELDP_ADD_CHOICES, $cpte_comptable);
		$myForm->setFieldProperties("cpte_credit", FIELDP_DEFAULT, $DetailsOperation['credit']['compte']);
		$myForm->setFieldProperties("cpte_credit", FIELDP_IS_REQUIRED,true);
	}


	//$myForm->addJS(JSP_FORM,"comput",$JScode_1);

	// $myForm->addField("libel_ope", _("Libellé"), TYPC_TXT);
	// $myForm->setFieldProperties("libel_ope", FIELDP_DEFAULT, $adsys["adsys_type_operation"][$id_oper]);
	$myForm->setFieldProperties("libel_ope", FIELDP_IS_LABEL, true);
	$myForm->setFieldProperties("type_operation", FIELDP_IS_LABEL, true);

	$order = array("type_operation", "libel_ope");
	$myForm->setOrder(NULL, $order);





	$myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
	$myForm->addFormButton(1, 2, "ajouTaxe", _("Associer taxe"), TYPB_SUBMIT);
	$myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
	$myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Gop-3');
	$myForm->setFormButtonProperties("ajouTaxe", BUTP_PROCHAIN_ECRAN, 'Gop-5');
	$myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gop-1');
	$myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

	$myForm->buildHTML();
	echo $myForm->getHTML();

	// Enregistrement du numéro de compte
	$SESSION_VARS["id_oper"] = $id_oper;
}
/*}}}*/

/*{{{ Gop-3 : Confirmation de la modification */
else if ($global_nom_ecran == 'Gop-3') {
	 $temp = array();
  $temp['type_operation'] = $SESSION_VARS["id_oper"];

	// mise à jour de l'opération
  $OP = getOperations($temp['type_operation']);
  if (count($OP) > 0) // Il s'agit d'une modification
    $myErr = updateOperation($SESSION_VARS["id_oper"], $cpte_debit, $cpte_credit);
  else if ($myErr->errCode == ERR_NO_ASSOCIATION) // Il s'agit d'une nouvelle entrée
    $myErr = ajoutOperation($SESSION_VARS["id_oper"], $num_cpte_debit, $num_cpte_credit);
  else {
    $html_err = new HTML_erreur(_("Echec lors de la modification. "));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gop-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec lors de la modification. "));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gop-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;

  } else {
    $myMsg = new HTML_message(_("Confirmation"));
    $myMsg->setMessage(_("Les modifications ont été enregistrées"));
    $myMsg->addButton(BUTTON_OK, 'Gop-1');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }
}
/*}}}*/

/*{{{ Gop-4 : Impression des schémas comptables */
else if ($global_nom_ecran == 'Gop-4') {

  $schemas = getschemas();

  $xml = xml_schemas($schemas);

  //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'schemas_comptables.xslt');

  //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
  echo get_show_pdf_html("Gop-1", $fichier_pdf);


}
/*}}}*/

/*{{{ Gop-5 : Association de taxes */
else if ($global_nom_ecran == 'Gop-5') {
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
	    $retour1 .= "<TD align=\"center\"><A href=\"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gop-7&id_oper=".$id_oper."&id_taxe=".$val['id_taxe']."\">"._("Sup")."</A></TD>";
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
	 $myForm->setFormButtonProperties("ajouter", BUTP_PROCHAIN_ECRAN, 'Gop-6');
	 $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gop-2');
	 $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);



	 $myForm->buildHTML();
	 echo $myForm->getHTML();

}
/*}}}*/

/*{{{ Gop-6 : Confirmation ajout taxe */
else if ($global_nom_ecran == 'Gop-6') {

 $temp['type_operation'] = $SESSION_VARS["id_oper"];
 // insertion des taxes
	if (isset($id_taxe)){


		// insertion
		$myErr = insertTaxeOperation($temp['type_operation'], $id_taxe);
		if ($myErr->errCode != NO_ERR) {
	 		$html_err = new HTML_erreur(_("Echec lors de l'ajout. "));
	    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
	    $html_err->addButton("BUTTON_OK", 'Gop-5');
	    $html_err->buildHTML();
	    echo $html_err->HTML_code;

   }else {
	    $myMsg = new HTML_message(_("Confirmation ajout de taxe"));
	    $myMsg->setMessage(_("La taxe est bien associée à l'opération"));
	    $myMsg->addButton(BUTTON_OK, 'Gop-5');
	    $myMsg->buildHTML();
	    echo $myMsg->HTML_code;
  }
	}
	else {
    $html_err = new HTML_erreur(_("Echec lors de l'ajout. "));
    $html_err->setMessage(_("Erreur: il faut choisir une taxe"));
    $html_err->addButton("BUTTON_OK", 'Gop-5');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }


}
/*}}}*/

/*{{{ Gop-7 : Confirmation suppression taxe */
else if ($global_nom_ecran == 'Gop-7') {

  $temp['type_operation'] = $SESSION_VARS["id_oper"];
 // suppression des taxes
	if (isset($id_taxe)){
		$myErr = deleteTaxeOperation($temp['type_operation'], $id_taxe);
	}
	else {
    $html_err = new HTML_erreur(_("Echec lors de la suppression de la taxe. "));
    $html_err->setMessage(_("Erreur: il faut choisir une taxe"));
    $html_err->addButton("BUTTON_OK", 'Gop-5');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }
  if ($myErr->errCode != NO_ERR) {
 		$html_err = new HTML_erreur(_("Echec lors de la suppression de la taxe. "));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode].$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gop-5');
    $html_err->buildHTML();
    echo $html_err->HTML_code;

  } else {
    $myMsg = new HTML_message(_("Confirmation suppression de taxe"));
    $myMsg->setMessage(_("La taxe a été bien supprimée"));
    $myMsg->addButton(BUTTON_OK, 'Gop-5');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }

}
/*}}}*/
else
  signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>
