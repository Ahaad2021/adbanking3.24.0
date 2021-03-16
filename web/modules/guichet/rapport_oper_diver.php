<?php

/**
 * ticket 437
 * @author Kheshan.A.G
 * Rapports sur les operation diverses
 * [fonction 195]
 */
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/devise.php';
require_once 'modules/rapports/xml_guichet.php';
require_once 'lib/misc/csv.php';

global $adsys;
global $global_nom_ecran;

if ($global_nom_ecran == "Rod-1") {
  $MyPage = new HTML_GEN2(_("Saisie de critères pour le rapport "));
  
  //Remettre $global_id_agence à l'identifiant de l'agence courante
  resetGlobalIdAgence();
  //recuperation des infos
  $list_operations = getInfo_operation();
  $list_logins = getLogins();
  
  // filtre par login
  $MyPage->addField("login", _("Login"), TYPC_LSB);
  $MyPage->setFieldProperties("login", FIELDP_ADD_CHOICES, $list_logins);
  $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("login", FIELDP_HAS_CHOICE_TOUS, true);
  //operations
  $MyPage->addField("oper", _("Opération diverses"), TYPC_LSB);
  $MyPage->setFieldProperties("oper", FIELDP_ADD_CHOICES, $list_operations);
  $MyPage->setFieldProperties("oper", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("oper", FIELDP_HAS_CHOICE_TOUS, true);
   
  // Date Deboursement- Tri par date debut et date fin de deboursement
  $MyPage->addField("date_debut", _("Date min"), TYPC_DTE);
  $MyPage->setFieldProperties("date_debut", FIELDP_DEFAULT, date("01/01/2000"));
  $MyPage->setFieldProperties("date_debut", FIELDP_IS_REQUIRED, false);
   
  $MyPage->addField("date_fin", _("Date max"), TYPC_DTE);
  $MyPage->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));
  $MyPage->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, false);
  
  //Boutons
  $MyPage->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rod-2");
  $MyPage->addFormButton(1, 2, "csv", _("Export CSV"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Rod-3");
  $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  
  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
 
}
/*{{{ Rod-2 et Rod-3 : BAL - Impression ou export csv du rapports sur les opérations diverses */
elseif ($global_nom_ecran == "Rod-2" || $global_nom_ecran == "Rod-3") {
	setGlobalIdAgence($agence);
	if ($gest == "")
		$gest = 0;

	//get date debut
	if (!empty ($date_debut)) {
		$date_debut = $date_debut;
	}//get date fin
	if (!empty ($date_fin)) {
		$date_fin = $date_fin;
	}
	//get id operation
	if (!empty ($oper)) {
		$SESSION_VARS['operation'] = $oper;
	}
	//get login
	if (!empty ($login)) {
		$SESSION_VARS['login'] = $login;
	}
	
	
	if ($global_nom_ecran == 'Rod-3') {//CSV
		//Génération du CSV grâce à XALAN
		$xml = xml_rapport_oper_div($date_debut ,$date_fin ,$SESSION_VARS['operation'] ,$SESSION_VARS['login']  ,true);
		if($xml != NULL){
			$csv_file = xml_2_csv($xml, 'rapport_op_div.xslt');

			//Message de confirmation + affichage du rapport dans une nouvelle fenêtre
			echo getShowCSVHTML("Gen-6", $csv_file);
		}

	} elseif ($global_nom_ecran == 'Rod-2') {//PDF
		//Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
		$xml = xml_rapport_oper_div($date_debut ,$date_fin ,$SESSION_VARS['operation'], $SESSION_VARS['login']  );
		if($xml != NULL){
			$fichier_pdf = xml_2_xslfo_2_pdf($xml, 'rapport_op_div.xslt');

			//Message de confirmation + affichage du rapport dans une nouvelle fenêtre
			echo get_show_pdf_html("Gen-6", $fichier_pdf);
		}
	}
	if($xml == NULL){
		$html_msg = new HTML_message(_("Résultats de la requête"));
		$html_msg->setMessage(_("Aucun opérations"));
		$html_msg->addButton("BUTTON_OK", 'Gen-6');
		$html_msg->buildHTML();
		echo $html_msg->HTML_code;
	}



}else
  signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>
