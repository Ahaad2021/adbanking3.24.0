<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [440] Gestion des exercices
 * Cette fonction appelle les écrans suivants :
 * - Tva-1 : Menu principal de déclaration de tva
 * - Tva-2 : Définition de la période de la déclaration de tva
 * - Tva-3 : Déclaration de tva et édition du rapport
 * - Tva-4 : Consultation de déclaration de tva
 * - Tva-5 : Edition du rapport de déclaration tva
 *
 * @package Compta
 **/

require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/misc/divers.php';
require_once 'modules/compta/xml_compta.php';
require_once 'modules/rapports/xslt.php';

/*{{{ Tva-1 : Menu principal déclaration de tva  */
if ($global_nom_ecran == "Tva-1") {
  global $global_id_agence;
  $MyPage = new HTML_GEN2(_("Déclaration de tva"));

 	$derniereDecTva = getLastDecTva();
	$derniereDecTva = $derniereDecTva->param;

	if ($derniereDecTva == NULL){
		$AG = getAgenceDatas($global_id_agence );
    $id_exo_encours = $AG["exercice"];
    $infos_exo_encours = getExercicesComptables($id_exo_encours);
		$date_deb_per = pg2phpDate(date($infos_exo_encours[0]["date_deb_exo"]));
	}else{
		$date_deb_per = demain(pg2phpDate($derniereDecTva["date_fin"]));
	}

  //Periode

 $MyPage->addField("date_debut", _("Date début de période"), TYPC_DTE);
 $MyPage->addField("date_fin", _("Date fin de période"), TYPC_DTE);
 $MyPage->setFieldProperties("date_debut", FIELDP_DEFAULT, date($date_deb_per));
 $SESSION_VARS["date_debut"] = $date_deb_per;
 $MyPage->setFieldProperties("date_debut", FIELDP_IS_LABEL, true);
 $MyPage->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, true);
 $MyPage->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);

  //Boutons
  $MyPage->addFormButton(1, 1, "declareTva", _("Déclarer"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("declareTva", BUTP_PROCHAIN_ECRAN, "Tva-2");
  $MyPage->setFormButtonProperties("declareTva", BUTP_CHECK_FORM, true);

  $MyPage->addFormButton(1, 2, "consultDecTva", _("Consulter déclaration tva "), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("consultDecTva", BUTP_PROCHAIN_ECRAN, "Tva-4");
  $MyPage->setFormButtonProperties("consultDecTva", BUTP_CHECK_FORM, false);

  //Bouton retour
  $MyPage->addFormButton(1, 3, "ret", _("Retour menu "), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ret", BUTP_PROCHAIN_ECRAN, "Gen-14");
  $MyPage->setFormButtonProperties("ret", BUTP_CHECK_FORM, false);
  
 // Code javascript pour la vérification de la date de fin
    $JSCheck = "\n\t if ('".localiser_date(date("d/m/Y"))."' == document.ADForm.HTML_GEN_date_date_fin.value)
             {
               msg += '- "._("La date de fin doit être différente de la date du jour")."\\n';
               ADFormValid = false;
             }";
    $MyPage->addJS(JSP_BEGIN_CHECK, "JSCheck", $JSCheck);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}
/*}}}*/

/*{{{ Tva-2 : Déclaration de tva */
else if ($global_nom_ecran == "Tva-2") {

    $MyPage = new HTML_GEN2(_("Déclaration de tva"));
	  //Periode
	  $MyPage->addField("date_debut", _("Date début de période"), TYPC_DTE);
	  $MyPage->addField("date_fin", _("Date fin de période"), TYPC_DTE);
	  $MyPage->setFieldProperties("date_debut", FIELDP_DEFAULT, date($SESSION_VARS["date_debut"]));
	  $MyPage->setFieldProperties("date_debut", FIELDP_IS_REQUIRED,true);
	  $MyPage->setFieldProperties("date_debut", FIELDP_IS_LABEL,true);
		$MyPage->setFieldProperties("date_fin", FIELDP_DEFAULT, date($date_fin));
		$SESSION_VARS["date_fin"] = $date_fin;
		$MyPage->setFieldProperties("date_fin", FIELDP_IS_REQUIRED,true);
		$MyPage->setFieldProperties("date_fin", FIELDP_IS_LABEL,true);

		// Code HTML pour message d'attention
    $xtHTML = "<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\">
              <tr><td align=\"center\"><font color=\"".$colt_error."\">
              <b>"._("Attention !")."</b></font></td></tr>
              <tr><td align=\"center\"><font color=\"".$colt_error."\">
              "._("La déclaration de tva sur la période ci-dessus va être effective.")."<br>"._("Êtes vous sûr de vouloir continuer ?")."</font></td></tr>
              <tr><td>&nbsp;</td></tr></table>";
    $MyPage->addHTMLExtraCode("attention", $xtHTML);
    $MyPage->addFormButton(1, 1, "oui", _("Oui"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("oui", BUTP_PROCHAIN_ECRAN, "Tva-3");
    $MyPage->addFormButton(1, 2, "non", _("Non"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("non", BUTP_PROCHAIN_ECRAN, "Tva-1");

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Tva-3 : Déclaration de tva et édition du rapport */
else if ($global_nom_ecran == "Tva-3") {

		$myErr = declareTva($SESSION_VARS["date_debut"], $SESSION_VARS["date_fin"]);
		if ($myErr->errCode != NO_ERR) {
		  $html_err = new HTML_erreur(_("Confirmation déclaration tva"));
	    $html_err->setMessage(_("Echec : ".$error[$myErr->errCode].$myErr->param));
	    $html_err->addButton("BUTTON_OK", 'Tva-1');
	    $html_err->buildHTML();
    	echo $html_err->HTML_code;
		}else{
			$data_tva = $myErr->param;
			if (sizeof($data_tva["detail_tva"]) > 0) {
				$xml = xml_declaration_tva($data_tva, array (
		                                   _("Déclaration de tva").": " => $libel,
		                                   _("du[[A partir de]]").": " => $SESSION_VARS["date_debut"],
		                                   _("au[[Jusqu au]]").": " => $SESSION_VARS["date_fin"]
		                                 ));

		  	$fichier_pdf = xml_2_xslfo_2_pdf($xml, 'declaration_tva.xslt');
		  	echo get_show_pdf_html("Tva-1", $fichier_pdf);
			}else {
		    //HTML
		    $MyPage = new HTML_message(_("Déclaration de tva"));
		    $MyPage->setMessage(_("Aucune donnée traitée!"));
		    $MyPage->addButton(BUTTON_OK, "Tva-1");
		    $MyPage->buildHTML();
		    echo $MyPage->HTML_code;
	   }
		}


}
/*}}}*/

/*{{{ Tva-4 : Consultation de déclaration de tva */
else if ($global_nom_ecran == "Tva-4") {
	global $global_id_exo;
  $MyPage = new HTML_GEN2(_("Consultation de déclaration de tva"));

	$decTva = getDecTva($global_id_exo);
	$decTva = $decTva->param;
	$SESSION_VARS["declaration_tva"] = $decTva;

	//liste des déclarations de tva
	$liste_dec = array();
	foreach($decTva as $key => $value){
	$liste_dec[$key] = "Du ".pg2phpDate($value['date_deb'])." au ".pg2phpDate($value['date_fin']);
	}
  $MyPage->addField("id_dec", _("Déclaration de tva"), TYPC_LSB);
  $MyPage->setFieldProperties("id_dec", FIELDP_ADD_CHOICES, $liste_dec);
  $MyPage->setFieldProperties("id_dec", FIELDP_IS_REQUIRED,true);
	$MyPage->setFieldProperties("id_dec", FIELDP_JS_EVENT, array("OnChange"=>"updateDate();"));
	$MyPage->addField("date_debut", _("Date début de période"), TYPC_DTE);
	$MyPage->addField("date_fin", _("Date fin de période"), TYPC_DTE);

	$MyPage->setFieldProperties("date_debut", FIELDP_IS_REQUIRED,true);
	$MyPage->setFieldProperties("date_debut", FIELDP_IS_LABEL,true);
	$MyPage->setFieldProperties("date_fin", FIELDP_IS_REQUIRED,true);
	$MyPage->setFieldProperties("date_fin", FIELDP_IS_LABEL,true);

	$js = "function updateDate()\n{\n ";
  foreach($decTva as $key=>$value) {
		$date_deb =pg2phpDate($value["date_deb"]);
		$date_fin =pg2phpDate($value["date_fin"]);
    $js .= "if ((document.ADForm.HTML_GEN_LSB_id_dec.value == ".$value["id"].")) {";
    $js .= "document.ADForm.HTML_GEN_date_date_debut.value = '".$date_deb."';";
    $js .= "document.ADForm.HTML_GEN_date_date_fin.value = '".$date_fin."';";
    $js .= "}";
   }
	 $js .= "};";
	 $js .= "updateDate();";
	 $MyPage->addJS(JSP_FORM, "jstest", $js);

  //Bouton Valider
  $MyPage->addFormButton(1, 1, "consulter", _("Consulter"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("consulter", BUTP_PROCHAIN_ECRAN, "Tva-5");
  $MyPage->setFormButtonProperties("consulter", BUTP_CHECK_FORM, true);

  //Bouton annuler
  $MyPage->addFormButton(1, 2, "annuler", _("Annuler "), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Tva-1");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}
/*}}}*/

/*{{{ Tva-5 : Edition de déclaration tva */
else if ($global_nom_ecran == "Tva-5") {

	$idDec = $id_dec;
	$decTva = $SESSION_VARS["declaration_tva"][$idDec];
	$date_debut = pg2phpDate($decTva["date_deb"]);
	$date_fin = pg2phpDate($decTva["date_fin"]);
	$data_tva = getMouvementsTva($date_debut, $date_fin);
	if (sizeof($data_tva["detail_tva"]) > 0) {
	$xml = xml_declaration_tva($data_tva, array (
                                   _("Déclaration de tva") => $libel,
                                   _("du[[A partir de]]") => $date_debut,
                                   _("au[[Jusqu au]]") => $date_fin
                                 ));

  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'declaration_tva.xslt');
  echo get_show_pdf_html("Tva-1", $fichier_pdf);
  } else {
    //HTML
    $MyPage = new HTML_message(_("Edition de déclaration tva"));
    $MyPage->setMessage(_("Aucune donnée renvoyée!"));
    $MyPage->addButton(BUTTON_OK, "Tva-1");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>