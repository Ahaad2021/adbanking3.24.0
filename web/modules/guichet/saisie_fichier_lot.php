<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/*
 * Dépôt par lot via fichier
 * @author Antoine Guyette
 * @since 01/03/2006
 * @package Guichet
 */

require_once 'lib/dbProcedures/guichet.php';

/*{{{ Dlf-1 : Choix de la source des fonds */
if ($global_nom_ecran == "Dlf-1") {

  $MyPage = new HTML_GEN2(_("Dépôt par lot via fichier"));
 	// le libellé de l'écriture

  // Evol sur MA2E
  $choices_ref=array();
  $choices_ref = array(1 => _("Identifiant Client"), 2 => _("Matricule client"));
  $MyPage->addField("type_ref", _("Type référence"), TYPC_LSB);
  $MyPage->setFieldProperties("type_ref", FIELDP_ADD_CHOICES, $choices_ref);
  $MyPage->setFieldProperties("type_ref", FIELDP_IS_REQUIRED, true);

  $choices_ope=array();
  $choices_ope = array(1 => _("Dépôt client"), 2 => _("Mise à jour quotité"));
  $MyPage->addField("type_ope", _("Type opération"), TYPC_LSB);
  $MyPage->setFieldProperties("type_ope", FIELDP_ADD_CHOICES, $choices_ope);
  $MyPage->setFieldProperties("type_ope", FIELDP_IS_REQUIRED, true);

  $jscheckDepot = "function blockDepot()
           {
             if (document.ADForm.HTML_GEN_LSB_type_ope.value == 2)
           {
             document.ADForm.HTML_GEN_LSB_source.disabled = true;
             document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
             document.ADForm.num_piece.disabled = true;
             document.ADForm.communication.disabled = true;
             document.ADForm.remarque.disabled = true;
           }
           else {
             document.ADForm.HTML_GEN_LSB_source.disabled = false;
           }
           }";
  $MyPage->addJS(JSP_FORM, "JS11", $jscheckDepot);
  $MyPage->setFieldProperties("type_ope", FIELDP_JS_EVENT, array("onchange" => "blockDepot()"));


  $choices=array();
 	$list_libel = getLEL(); // Récupère de tous les libellés des écritures libres 
 	$choices[0]=_("Autre libellé"); 
 	foreach ($list_libel as $key => $value) 
  	$choices[$value["type_operation"]]=$value["libel_ope"];       
 	$MyPage->addField("libel_ope_def",_("Liste libellé opération"), TYPC_LSB); 
 	$MyPage->setFieldProperties("libel_ope_def", FIELDP_ADD_CHOICES, $choices); 
 	$MyPage->setFieldProperties("libel_ope_def", FIELDP_HAS_CHOICE_AUCUN, false); 
 	$MyPage->setFieldProperties("libel_ope_def", FIELDP_DEFAULT, $SESSION_VARS["type_operation"]); 
 	$MyPage->setFieldProperties("libel_ope_def", FIELDP_JS_EVENT, array("onChange"=>"changeLibel();")); 
  
 	$MyPage->addField("libel_ope",_("Libellé opération"), TYPC_TTR);
 	$libel_ope = new Trad($SESSION_VARS["libel_ope"]);
 	$MyPage->setFieldProperties("libel_ope", FIELDP_DEFAULT, $libel_ope); 
 	//$html->setFieldProperties("autre_libel_ope", FIELDP_IS_REQUIRED, true); 
 	$MyPage->setFieldProperties("libel_ope", FIELDP_WIDTH, 40); 
     
 	$codejs ="\n\nfunction changeLibel() {"; 
 	$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==0)\n\t"; 
 	$codejs .= "{\n\t\tdocument.ADForm.libel_ope.value ='';"; 
 	//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =false;"; 
 	$codejs .= "}else{\n"; 
 	foreach($choices as $type_operation=>$value) { 
 		$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_libel_ope_def.value ==$type_operation)\n\t"; 
  	$codejs .= "{\n\t\tdocument.ADForm.libel_ope.value =\"" . $value . "\";"; 
  	//$codejs .= "\n\t\tdocument.ADForm.libel_ope.disabled =true;"; 
  	$codejs .= "}\n"; 
 	} 
 	$codejs .= "}}\n"; 
 	$MyPage->addJS(JSP_FORM, "jslibel", $codejs); 	 
  $choices = array(1 => _("Cash"), 2 => _("Correspondant bancaire"));
  $MyPage->addField("source", _("Source des fonds"), TYPC_LSB);
  $MyPage->setFieldProperties("source", FIELDP_ADD_CHOICES, $choices);
  $MyPage->setFieldProperties("source", FIELDP_IS_REQUIRED, false);

  $MyPage->addTable("ad_his_ext", OPER_INCLUDE, array("num_piece", "communication", "remarque"));

  $MyPage->addField("nom_ben", _("Nom du tireur"), TYPC_TXT);
  $MyPage->setFieldProperties("nom_ben", FIELDP_IS_LABEL, true);
  $MyPage->addHiddenType("id_ben", "");

  $MyPage->addLink("nom_ben", "rechercher", _("Rechercher"), "#");
  $MyPage->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=t', '"._("Recherche")."'); return false;"));

  // Correspondant bancaire
  $libel_correspondant = getLibelCorrespondant();
  $MyPage->addField("correspondant", _("Correspondant bancaire"), TYPC_LSB);
  $MyPage->setFieldProperties("correspondant", FIELDP_ADD_CHOICES, $libel_correspondant);

  // Ordonner les champs pour l'affichage
  $order = array("type_ref","type_ope","libel_ope_def", "libel_ope", "source", "correspondant", "nom_ben", "num_piece", "communication", "remarque");
  $MyPage->setOrder(NULL, $order);

  // Transformer les champs en labels non modifiables
  $labels = array_diff($order, array("type_ref","type_ope","libel_ope_def", "libel_ope", "source"));
  foreach ($labels as $value) {
    $MyPage->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  }

  // En fonction du choix du compte, afficher les infos avec le onChange javascript
  $jscheck = "function activateFields()
           {
             if (document.ADForm.HTML_GEN_LSB_source.value == 0)
           {
             document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
             document.ADForm.num_piece.disabled = true;
             document.ADForm.communication.disabled = true;
             document.ADForm.remarque.disabled = true;
           }
             else if (document.ADForm.HTML_GEN_LSB_source.value == 1)
           {
             document.ADForm.HTML_GEN_LSB_correspondant.disabled = true;
             document.ADForm.num_piece.disabled = true;
             document.ADForm.communication.disabled = false;
             document.ADForm.remarque.disabled = false;
           }
             else
           {
             document.ADForm.HTML_GEN_LSB_correspondant.disabled = false;
             document.ADForm.num_piece.disabled = false;
             document.ADForm.communication.disabled = false;
             document.ADForm.remarque.disabled = false;
           }
           }";

  $MyPage->addJS(JSP_FORM, "JS1", $jscheck);
  $MyPage->setFieldProperties("source", FIELDP_JS_EVENT, array("onchange" => "activateFields()"));

  // Checkform
  $jscheck = "if (document.ADForm.HTML_GEN_LSB_source.value == 2)
           {
             if (document.ADForm.HTML_GEN_LSB_correspondant.value == 0)
           {
             msg += '- "._("Le champ correspondant doit être renseigné")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.id_ben.value == '')
           {
             msg += '- "._("Le champ tireur doit être renseigné")."\\n';
             ADFormValid = false;
           }
             if (document.ADForm.num_piece.value == '')
           {
             msg += '- "._("Le champ numéro de pièce doit être renseigné")."\\n';
             ADFormValid = false;
           }
           }
            if (document.ADForm.HTML_GEN_LSB_type_ope.value == 1)
           {
            if (document.ADForm.HTML_GEN_LSB_source.value == 0){
               msg += '- "._("La source des fonds doit être renseigné")."\\n';
             ADFormValid = false;
            }
           }";

  $MyPage->addJS(JSP_BEGIN_CHECK, "JS2", $jscheck);

  $MyPage->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dlf-2');
  $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-6');

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Dlf-2 : Récupération du fichier de données */
else if ($global_nom_ecran == "Dlf-2") {

  // Sauvegarde des valeurs provenant du premier écran
  if ($source != NULL) {
    $INFOSOURCE = array();
    $INFOSOURCE["source"] = $source;
    // Source = correspondant bancaire
    if ($source == 2) {
      $INFOSOURCE["correspondant"] = $correspondant;
      $INFOSOURCE["num_piece"] = $num_piece;
      $INFOSOURCE["id_ben"] = $id_ben;
    }
    $INFOSOURCE["communication"] = $communication;
    $INFOSOURCE["remarque"] = $remarque;
    $SESSION_VARS["SOURCE"] = $INFOSOURCE;
    $SESSION_VARS["libel_ope"] = new Trad();
    $SESSION_VARS["libel_ope"] = serialize($libel_ope);
    
  }
  if ($type_ope == 2){
    $SESSION_VARS["type_ref"] = $type_ref;
    $SESSION_VARS["type_ope"] = $type_ope;
    $SESSION_VARS["libel_ope"] = new Trad();
    $SESSION_VARS["libel_ope"] = serialize($libel_ope);
  }
  if ($type_ope == 1){
    $SESSION_VARS["type_ref"] = $type_ref;
    $SESSION_VARS["type_ope"] = $type_ope;
    $SESSION_VARS["libel_ope"] = new Trad();
    $SESSION_VARS["libel_ope"] = serialize($libel_ope);
  }

  if (file_exists($fichier_lot)) {
    $filename = $fichier_lot.".tmp";
    move_uploaded_file($fichier_lot, $filename);
    exec("chmod a+r ".escapeshellarg($filename));
    $SESSION_VARS['fichier_lot'] = $filename;
  } else {
    $SESSION_VARS['fichier_lot'] = NULL;
  }

  if ($type_destination != NULL) {
    $SESSION_VARS['type_destination'] = $type_destination;
  }
  $libel_ope = unserialize($SESSION_VARS["libel_ope"]);
  $MyPage = new HTML_GEN2(_("Récupération du fichier de données"));
  $htm1 = "<h2 align=\"center\">".$libel_ope->traduction()."</h2><br>\n";
  $htm1 .= "<P align=\"center\">"._("Fichier de données").": <INPUT name=\"fichier_lot\" type=\"file\" /></P>";
  $htm1 .= "<P align=\"center\"> <INPUT type=\"submit\" value=\"Envoyer\" onclick=\"document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Dlf-2';\"/> </P>";
  $htm1 .= "<br />";

  $MyPage->addHTMLExtraCode("htm1", $htm1);

  $choices = array(1 => _("Numéro de compte"), 2 => _("Numéro de client"), 3 => _("Matricule client"));
  $MyPage->AddField("type_destination", _("Type de destination"), TYPC_LSB);
  $MyPage->setFieldProperties("type_destination", FIELDP_ADD_CHOICES, $choices);
  $MyPage->setFieldProperties("type_destination", FIELDP_IS_REQUIRED, false);
  if ($type_ope == 2){
    $MyPage->setFieldProperties("type_destination", FIELDP_DEFAULT, $choices[3]);
  }else {
    $MyPage->setFieldProperties("type_destination", FIELDP_DEFAULT, $SESSION_VARS['type_destination']);
  }


  $MyPage->AddField("statut", _("Statut"), TYPC_TXT);
  $MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);

  if ($SESSION_VARS['fichier_lot'] == NULL) {
    $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier non reçu"));
  } else {
    $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier reçu"));
  }

  $MyPage->addHTMLExtraCode("htm2", "<br />");

  $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
  $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);

  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Dlf-3');
  $MyPage->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Dlf-1');
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-6');

  $MyPage->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Dlf-3 : Demande de confirmation du dépôt par lot via fichier */
else if ($global_nom_ecran == "Dlf-3") {
  if ($type_destination != NULL) {
    $SESSION_VARS['type_destination'] = $type_destination;
  }

  if ($SESSION_VARS["type_ref"] == 2 && $SESSION_VARS["type_ope"] == 2){
    $MyErr = parse_fichier_lot_quotite($SESSION_VARS['fichier_lot']);
  }
  else if ($SESSION_VARS["type_ref"] == 2 && $SESSION_VARS["type_ope"] == 1 ){
    $MyErr = parse_fichier_lot_client_par_matricule($SESSION_VARS['fichier_lot']);
  }
  else {
    $MyErr = parse_fichier_lot($SESSION_VARS['fichier_lot'], $SESSION_VARS['type_destination']);
  }
  if ($MyErr->errCode != NO_ERR) {
    $param = $MyErr->param;
    $html_err = new HTML_erreur(_("Echec de récupération du fichier de données"));
    $msg = _("Erreur : ").$error[$MyErr->errCode];
    if ($param != NULL) {
      $msg .= " ("._("ligne : ").$param["ligne"].")";
    }
    $html_err->setMessage($msg);
    $html_err->addButton("BUTTON_OK", 'Dlf-2');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
  else if ($MyErr->errCode == NO_ERR) {
    $param = $MyErr->param;

    $MyPage = new HTML_GEN2(_("Demande de confirmation du dépôt par lot via fichier"));
    $libel_ope = unserialize($SESSION_VARS["libel_ope"]);
		$htm1 = "<h2 align=\"center\">".$libel_ope->traduction()."</h2><br>\n";
  	$MyPage->addHTMLExtraCode("htm1", $htm1);

    if ($SESSION_VARS["type_ope"] != 2) {
      $cpteur = 0;
      foreach ($param['total'] as $code_devise => $total_mnt) {
        setMonnaieCourante($code_devise);
        $MyPage->addField("montant" . $cpteur, sprintf(_("Total montant (%s)"), $code_devise), TYPC_MNT);
        $MyPage->setFieldProperties("montant" . $cpteur, FIELDP_IS_LABEL, true);
        $MyPage->setFieldProperties("montant" . $cpteur, FIELDP_DEFAULT, $total_mnt);

        $MyPage->addField("confirmation" . $cpteur, sprintf(_("Confirmation montant (%s)"), $code_devise), TYPC_MNT);
        $MyPage->setFieldProperties("confirmation" . $cpteur, FIELDP_IS_REQUIRED, true);

        $JS1 .= "if (recupMontant(document.ADForm.montant" . $cpteur . ".value) != recupMontant(document.ADForm.confirmation" . $cpteur . ".value))";
        $JS1 .= "{";
        $JS1 .= "  msg += '-" . sprintf(_("Le montant en (%s), entré est incorrect"), $code_devise) . "\\n';";
        $JS1 .= "  ADFormValid = false;";
        $JS1 .= "}";

        $cpteur++;
      }
      $cpteur = 0;
      //champ frais virement
      foreach ($param['total_commission'] as $code_devise => $total_mnt_com) {
        if (isset($total_mnt_com) && $total_mnt_com > 0) {
          setMonnaieCourante($code_devise);
          $MyPage->addField("montant_com" . $cpteur, sprintf(_("Frais virement (%s)"), $code_devise), TYPC_MNT);
          $MyPage->setFieldProperties("montant_com" . $cpteur, FIELDP_IS_LABEL, true);
          $MyPage->setFieldProperties("montant_com" . $cpteur, FIELDP_DEFAULT, $total_mnt_com);

          $MyPage->addField("confirmation_com" . $cpteur, sprintf(_("Confirmation frais (%s)"), $code_devise), TYPC_MNT);
          $MyPage->setFieldProperties("confirmation_com" . $cpteur, FIELDP_IS_REQUIRED, true);

          $JS1 .= "if (recupMontant(document.ADForm.montant_com" . $cpteur . ".value) != recupMontant(document.ADForm.confirmation_com" . $cpteur . ".value))";
          $JS1 .= "{";
          $JS1 .= "  msg += '-" . sprintf(_("Le montant en (%s),des frais de virement entré est incorrect"), $code_devise) . "\\n';";
          $JS1 .= "  ADFormValid = false;";
          $JS1 .= "}";

          $cpteur++;
        }
      }

      $MyPage->addJS(JSP_BEGIN_CHECK, "JS1", $JS1);
    }

    $MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $MyPage->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
    $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);

    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Dlf-4');
    $MyPage->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Dlf-2');
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-6');

    $MyPage->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }

}
/*}}}*/

/*{{{ Dlf-4 : Confirmation du dépôt par lot via fichier */
else if ($global_nom_ecran == "Dlf-4") {

  if ($SESSION_VARS["type_ope"] != 2) {
    $MyErr = traite_fichier_lot($SESSION_VARS["fichier_lot"], $SESSION_VARS['type_destination'], $global_id_guichet, $SESSION_VARS['SOURCE'], unserialize($SESSION_VARS["libel_ope"]));
  }
  else{
    $MyErr = traite_fichier_matricule($SESSION_VARS["fichier_lot"],$global_id_guichet);
  }

  if ($MyErr->errCode == NO_ERR) {
    if ($SESSION_VARS["type_ope"] == 2){
      $html_mess = new HTML_message(_("Confirmation de mise à jour des quotité par lot via fichier"));
      $html_mess->setMessage(_("La mise à jour par lot via fichier s'est déroulé avec succès !"));
    }else {
      $html_mess = new HTML_message(_("Confirmation dépôt par lot via fichier"));
      $html_mess->setMessage(_("Le dépôt par lot via fichier s'est déroulé avec succès !"));
    }
    $html_mess->addButton(BUTTON_OK, "Gen-6");
    $html_mess->buildHTML();
    echo $html_mess->HTML_code;
  } else {
    $param = $MyErr->param;
    $html_err = new HTML_erreur(_("Echec du dépôt pat lot via fichier"));
    $msg = _("Erreur")." : ".$error[$MyErr->errCode];
    if ($param != NULL) {
    	if(is_array($param)){
    		foreach($param as $key => $val){
    			$msg .= "<br /> ".$key." : ".$param["$key"]."";
    		}
    	} else {
    		$msg .= " ("._("ligne")." : ".$param["ligne"].")";
    	}
    }
    $html_err->setMessage($msg);
    $html_err->addButton(BUTTON_OK, 'Gen-6');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__);
?>