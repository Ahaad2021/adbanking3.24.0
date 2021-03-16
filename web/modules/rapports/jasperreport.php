<?php
/*
 * Created on 16 janv. 10
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once 'lib/jasper/CallJasper.php';
require_once 'lib/html/HTML_Jasper_param.php';
require_once 'lib/jasper/CallJasper.php';
require_once 'lib/misc/csv.php';
global $global_multidevise, $global_niveau_max;
/*{{{ Rae-1 : Sélection du rapport à imprimer */
if ($global_nom_ecran =='Rae-1'){

	$rapports=getJasperRapportsCodeByLibel();

	$MyPage = new HTML_GEN2(_("Sélection  rapport "));
	$MyPage->addField("code_rapport", _("Rapport "), TYPC_LSB);
	$MyPage->setFieldProperties("code_rapport", FIELDP_IS_REQUIRED, true);
	$MyPage->setFieldProperties("code_rapport", FIELDP_ADD_CHOICES, $rapports);

	//Boutons
	$MyPage->addFormButton(1, 1, "valider", _("Sélectionner"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rae-2");
	$MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-13");
	$MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

	//HTML
	$MyPage->buildHTML();
	echo $MyPage->getHTML();

}


/*{{{ Rae-1 : Sélection du rapport à imprimer */
elseif ($global_nom_ecran =='Rae-2'){
	global $adsys ;
	$SESSION_VARS['param']=array();
	$SESSION_VARS['rapport']=array();
	$fields_array=array('code_rapport','libel');
	$where['code_rapport']=$code_rapport;
	$SESSION_VARS['rapport']=$code_rapport;

	$MyPage = new HTML_GEN2("");
	//generer les champs
	$SESSION_VARS['param'] = html_jasper_param($code_rapport,$MyPage);

	$MyPage->addField("format", _("Format rapport"), TYPC_LSB);
	$MyPage->setFieldProperties("format", FIELDP_IS_REQUIRED, true);
	$MyPage->setFieldProperties("format", FIELDP_ADD_CHOICES, $adsys["adsys_jasper_format"]);

	//Boutons
	$MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Rae-3");
	$MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-13");
	$MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

	//HTML
	$MyPage->buildHTML();
	echo $MyPage->getHTML();

}
/*}}}*/

/*{{{ Rae-3 : Sélection du rapport à imprimer */
elseif ($global_nom_ecran =='Rae-3'){        

        $m_agc = 0;
        if((isset($_REQUEST['m_agc']) && $_REQUEST['m_agc'] > 0)) {
            $m_agc = $_REQUEST['m_agc'];
        }

	foreach($SESSION_VARS['param'] AS  $paramName => $paramChampName) {
		$param[$paramName]=array($_REQUEST[$paramChampName[0]],$paramChampName[1]);
	}

	$format = $_REQUEST['HTML_GEN_LSB_format'];
	$where['code_rapport']=$SESSION_VARS['rapport'];
	$report = getJasperRapports(null, $where);
	$reportFile = $report->param[$SESSION_VARS['rapport']]['nom_fichier'];
	
	debug( $reportFile);
	$jsp= new jasperReport ();
	$fichier_pdf=$jsp->buildReport( $reportFile,$param,$format,$m_agc);
	if (!$jsp->isError()) {
		debug($fichier_pdf,"fichier");
		debug($format);
		
		if($format == 'pdf') {
			
			echo get_show_pdf_html("Rae-1", $fichier_pdf);
		} elseif($format == 'csv' || $format = 'xls') {
			echo getShowCSVHTML("Rae-1", $fichier_pdf);
		}
	
	// ajout_historique(380, NULL, NULL, $global_nom_login, date("r"), NULL);
	} else {
		$MyPage = new HTML_erreur(sprintf(_("Impossible d'éditer le rapport '%s'"), $SESSION_VARS['rapport']));
		$msg = _("Veuillez contacter votre administrateur système.");
		$MyPage->setMessage($jsp->getError()."<br>".$msg);
		$MyPage->addButton(BUTTON_OK, "Rae-1");

		$MyPage->buildHTML();
		echo $MyPage->HTML_code;
	}

}
/*}}}*/
?>