<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */
/**
 * Gestion des jasper report
 *
 * Cette opération comprends les écrans :
 * - Gjr-1 : Menu principal de gestion de jasper repport
 * - Gjr-2 : Ajouter un rapport jasper report
 * - Gjr-3 : Consultez un rapport  jasper
 * - Gjr-4 : Modifiez un rapport jasper
 * - Gjr-5 : Confirmation ajout ou modification rapport
 * - Gjr-6 : suppression du rapport
 * - Gjr-7 : Confirmation suppression du rapport
 * - Gjr-8 : Gestion de parametre jasper report
 * - Gjr-9 : Ajout paramètre
 * - Gjr-10 : Consulté parametre
 * - Gjr-11 : Modifier parametre jaspere
 * - Gjr-12 :Confirmation ajout ou Modification parametre jaspere
 * - Gjr-13 :Suppression parametre jaspere
 * - Gjr-14 :Confirmation Suppression parametre jasper
 * - Gjr-15 :Associer  parametre au rapport  jaspere
 *
 * @package Rapports
 */

require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/jasper_report.php';

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_Jasper_param.php';



/*{{{ Gjr-1 : Menu principal gestion de jasper */
if ($global_nom_ecran == "Gjr-1") { //Menu principal gestion de jasper

	$MyPage = new HTML_GEN2(_("Gestion de jasper report"));
	//Javascript
	$js = "document.ADForm.consult.disabled = true; document.ADForm.modif.disabled = true; document.ADForm.supr.disabled = true;document.ADForm.param_rapport.disabled = true;\n";
	$js .= " document.ADForm.poste_compte.disabled = true;\n";

	$js .= "function activateButtons(){\n";
	$js .= "activate = (document.ADForm.HTML_GEN_LSB_code_rapport.value != 0);";
	$js .= "activate2 = (activate && (document.ADForm.HTML_GEN_LSB_code_rapport.value != 1));";
	$js .= "document.ADForm.consult.disabled = !activate; document.ADForm.modif.disabled = !activate2; document.ADForm.supr.disabled = !activate2; document.ADForm.param_rapport.disabled =!activate2;";
	$js .= " document.ADForm.poste_compte.disabled =  !activate;\n";
	$js .= "}\n";
	$MyPage->addJS(JSP_FORM, "js", $js);
	//initialisation variable de session
	$SESSION_VARS['code_rapport']=NULL;

	//liste libel rapport
	$list_rapport=getJasperRapportsCodeByLibel();

	$MyPage->addField("code_rapport", _("Libellé"), TYPC_LSB);
	$MyPage->setFieldProperties("code_rapport", FIELDP_ADD_CHOICES, $list_rapport);
	$MyPage->setFieldProperties("code_rapport", FIELDP_JS_EVENT, array("onchange"=>"activateButtons();"));

	//Bouton consulter
	$MyPage->addButton("code_rapport", "consult", _("Consulter"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("consult", BUTP_AXS, 271);
	$MyPage->setButtonProperties("consult", BUTP_PROCHAIN_ECRAN, "Gjr-3");

	//Bouton modifier
	$MyPage->addButton("code_rapport", "modif", _("Modifier"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("modif", BUTP_AXS, 272);
	$MyPage->setButtonProperties("modif", BUTP_PROCHAIN_ECRAN, "Gjr-4");

	//Bouton supprimer
	$MyPage->addButton("code_rapport", "supr", _("Supprimer"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("supr", BUTP_AXS, 273);
	$MyPage->setButtonProperties("supr", BUTP_PROCHAIN_ECRAN, "Gjr-6");


	//Bouton créer
	$MyPage->addFormButton(1, 1, "cree", _("Créer un nouvel rapport"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("cree", BUTP_AXS, 270);
	$MyPage->setFormButtonProperties("cree", BUTP_PROCHAIN_ECRAN, "Gjr-2");
	$MyPage->setFormButtonProperties("cree", BUTP_CHECK_FORM, false);

	//Bouton Gestion paramètre
	$MyPage->addFormButton(1, 2, "param", _("Gestion paramètre"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("param", BUTP_AXS, 270);
	$MyPage->setFormButtonProperties("param", BUTP_PROCHAIN_ECRAN, "Gjr-8");
	$MyPage->setFormButtonProperties("param", BUTP_CHECK_FORM, false);

	
	//Bouton Gestion paramètre
	$MyPage->addFormButton(1, 3, "poste_compte", _("Ajouter les comptes postes"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("poste_compte", BUTP_AXS, 270);
	$MyPage->setFormButtonProperties("poste_compte", BUTP_PROCHAIN_ECRAN, "Gjr-19");
	$MyPage->setFormButtonProperties("poste_compte", BUTP_CHECK_FORM, false);


	//Bouton Associer paramètre
	$MyPage->addFormButton(1, 4, "param_rapport", _("Associer paramètre au rapport"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("param_rapport", BUTP_AXS, 270);
	$MyPage->setFormButtonProperties("param_rapport", BUTP_PROCHAIN_ECRAN, "Gjr-15");
	$MyPage->setFormButtonProperties("param_rapport", BUTP_CHECK_FORM, false);


	//Bouton retour
	$MyPage->addFormButton(2, 2, "ret", _("Retour"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("ret", BUTP_PROCHAIN_ECRAN, "Gen-12");
	$MyPage->setFormButtonProperties("ret", BUTP_CHECK_FORM, false);
	$MyPage->buildHTML();




	echo $MyPage->getHTML();

	/*{{{ Gjr-2 : Ajouter un rapport jasper report */
} else if ($global_nom_ecran == "Gjr-2") { //Ajout  du rapport
	$array_fields= array("code_rapport","nom_fichier");
	$SESSION_VARS['action_rapport']='ajout';

	$MyPage = new HTML_GEN2(_("Ajout rapport"));
	$MyPage->addTable("ad_jasper_rapport", OPER_INCLUDE, $array_fields);

	$MyPage->addField("libel", _("libellé"), TYPC_TXT);
	$MyPage->setFieldProperties("libel", FIELDP_IS_REQUIRED, true);
	//Champs guichet container
	$MyPage->addField("subreport", _("Sous rapports"), TYPC_CNT);

	for ($i=1;$i<11;$i++ ) {
		$MyPage->addField("sous_rapport".$i,sprintf( _("Fichier %s"),$i), TYPC_FILE);
		$MyPage->makeNested("subreport", "sous_rapport".$i);
	}
	// ordre champs
	$MyPage->setOrder(NULL,array("code_rapport","libel","nom_fichier") );
	//Boutons
	$MyPage->addFormButton(1, 1, "butvalid", _("Valider"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butvalid", BUTP_PROCHAIN_ECRAN, "Gjr-5");
	$MyPage->addFormButton(1, 2, "butannul", _("Annuler"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-1");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-3 : Consultez un rapport  jasper */
} else if ($global_nom_ecran == "Gjr-3") { //consultez du rapport

	$MyPage = new HTML_GEN2(_("Consulté rapport"));

	$array_fields= array("code_rapport","libel","nom_fichier");
	ajout_historique(271,NULL, $code_rapport, $global_nom_login, date("r"), NULL); //Consultation
	$MyPage->addTable("ad_jasper_rapport", OPER_INCLUDE, $array_fields);
	$MyPage->setFieldProperties($array_fields, FIELDP_IS_LABEL, true);

	$MyData = new FILL_HTML_GEN2();
	$MyData->addFillClause("jr", "ad_jasper_rapport");
	$MyData->addCondition("jr", "code_rapport", $code_rapport);
	$MyData->addManyFillFields("jr", OPER_NONE, NULL);
	$MyData->fill($MyPage);

	// $MyPage->setFieldProperties($array_fields, FIELDP_IS_REQUIRED, true);
	//Champs guichet container
	/* $MyPage->addField("subreport", _("Sous rapports"), TYPC_CNT);

	for ($i=1;$i<5;$i++ ) {
	$MyPage->addField("sous_rapport".$i,sprintf( _("Fichier %s"),$i), TYPC_FILE);
	$MyPage->makeNested("subreport", "sous_rapport".$i);
	}*/
	//Boutons
	$MyPage->addFormButton(1, 1, "butannul", _("Retour"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-1");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-4 : Modifiez un rapport jasper */
}else if ($global_nom_ecran == "Gjr-4") { //Modifier informations du rapport
	$SESSION_VARS['action_rapport']='modif';
	$MyPage = new HTML_GEN2(_("Modifier rapport"));
	$array_fields= array("code_rapport","libel","nom_fichier");
	$MyPage->addTable("ad_jasper_rapport", OPER_INCLUDE,$array_fields);
	$MyData = new FILL_HTML_GEN2();
	$MyData->addFillClause("jr", "ad_jasper_rapport");
	$MyData->addCondition("jr", "code_rapport", $code_rapport);
	$MyData->addManyFillFields("jr", OPER_NONE, NULL);
	$MyData->fill($MyPage);
	$MyPage->setFieldProperties('nom_fichier', FIELDP_IS_REQUIRED, false);

	//$MyPage->setFieldProperties($array_fields, FIELDP_IS_LABEL, true);
	//Champs guichet container
	$MyPage->addField("subreport", _("Sous rapports"), TYPC_CNT);

	for ($i=1;$i<5;$i++ ) {
		$MyPage->addField("sous_rapport".$i,sprintf( _("Fichier %s"),$i), TYPC_FILE);
		$MyPage->makeNested("subreport", "sous_rapport".$i);
	}
	//Boutons
	$MyPage->addFormButton(1, 1, "butvalid", _("Valider"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butvalid", BUTP_PROCHAIN_ECRAN, "Gjr-5");
	$MyPage->addFormButton(1, 2, "butannul", _("Annuler"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-1");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-5 : Confirmation ajout ou modification rapport */
}  else if ($global_nom_ecran == "Gjr-5") { // confirmation ajout ou modification rapport
	global $global_id_agence;
	$dataReportFiles = array();

	if (isset($_FILES)) {
		foreach ($_FILES as $key => $value){
				
			if ($value['error'] == 0) {
				$dataReportFile = array();
				debug($value,$key);
				if(strcmp($key, "nom_fichier") == 0) {
					$dataReportFile['FILE']=$value['tmp_name'];
					$dataReportFile['NAME'] =$code_rapport.".jasper";
					$dataReportFile['TYPE'] ="MASTER";
					//verifier l'extension du fichier master
					$ext=renvoiExtFile ($value['name']);
					debug($ext);
					if( $ext != 'jasper' ){
						$titre_err=_("Echec de l'ajout du rapport.");
						$msg=sprintf(_("Format de fichier non supporté , seul les fichiers ( *.jasper).<br> Fichier '%s'"),$value['name']);
						$ecran="Gjr-2";
						sendMsgErreur($titre_err,$msg,$ecran);
					}
				} else {
					$dataReportFile['FILE']=$value['tmp_name'];
					$dataReportFile['NAME'] =$value['name'];
					$dataReportFile['TYPE'] ="RESOURCE";
				}
				$dataReportFiles[] = $dataReportFile;

			}
		}
	}
	$data["code_rapport"]=$code_rapport;
	$data[  "libel"] = $libel;
	$data[  "id_ag"]=$global_id_agence;
	$data['FILES'] = $dataReportFiles;
	//ajout rapport : Gjr-2
	if ( $SESSION_VARS['action_rapport']=='ajout') {
		$err=insertionJasperRapport($data);

		$titre_err=_("Echec de l'ajout du rapport.");
		$titre=sprintf(_("Confirmation ajout rapport  '%s'",$data[  "libel"]));
		$ecran="Gjr-2";
		$msg=sprintf(_("Le rapport '%s' a été ajouté avec succès !"),$data[  "libel"]);
	} elseif ( $SESSION_VARS['action_rapport']=='modif') {
		$where['code_rapport']=$code_rapport;
		$err=updateJasperRapport($data,$where);

		$titre_err=_("Echec de la Modification du rapport.");
		$titre=sprintf(_("Confirmation Modification rapport  '%s'",$data[  "libel"]));
		$ecran="Gjr-1";
		$msg=sprintf(_("Le rapport '%s' a été modifié avec succès !"),$data[  "libel"]);
	}
	$SESSION_VARS['action_rapport']=NULL;
	unset($SESSION_VARS['action_rapport']);
	if ($err->errCode != NO_ERR) {
		sendMsgErreur($titre_err,$err,$ecran);
	} else {
		sendMsgConfirmation($titre,$msg,$ecran);
	}

	/*{{{ Gjr-6 : suppression du rapport */
} else if ($global_nom_ecran == "Gjr-6") { //suppression du rapport

	$SESSION_VARS['code_rapport']=$code_rapport;
	$MyPage = new HTML_message(_('Demande confirmation'));
	$MyPage->setMessage(sprintf(_("Etes-vous sûr de vouloir supprimer le rapport de reference  %s ?"),$code_rapport));

	//Boutons
	$MyPage->addButton(BUTTON_OUI, "Gjr-7");
	$MyPage->addButton(BUTTON_NON, "Gjr-1");

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-7 : Confirmation suppression du rapport */
}  else if ($global_nom_ecran == "Gjr-7") { //suppression du rapport
	global  $doc_prefix;
	$err=deleteJasperRapport($SESSION_VARS['code_rapport']);
	$filename= "${doc_prefix}/rapports/jrxml/".$SESSION_VARS['code_rapport'].".jasper";
	unlink($filename);

	if ($err->errCode != NO_ERR) {
		$titre_err=sprintf(_("Echec de la Suppression du rapport %s."),$SESSION_VARS['code_rapport']);
		$ecran="Gjr-1";
		sendMsgErreur($titre_err,$err,$ecran);
	} else {
		$ecran="Gjr-1";
		$msg=sprintf(_("Le rapport '%s' a été supprimé avec succès !"),$SESSION_VARS['code_rapport']);
		sendMsgConfirmation($titre,$msg,$ecran);
	}

	/*{{{ Gjr-8 : Gestion de parametre jasper report */
}  elseif ($global_nom_ecran == "Gjr-8") {

	$MyPage = new HTML_GEN2(_("Gestion de parametre jasper report"));
	//Javascript
	$js = "document.ADForm.consult.disabled = true; document.ADForm.modif.disabled = true; document.ADForm.supr.disabled = true;\n";
	$js .= "function activateButtons(){\n";
	$js .= "activate = (document.ADForm.HTML_GEN_LSB_code_param.value != 0);";
	$js .= "activate2 = (activate && (document.ADForm.HTML_GEN_LSB_code_param.value != 1));";
	$js .= "document.ADForm.consult.disabled = !activate; document.ADForm.modif.disabled = !activate2; document.ADForm.supr.disabled = !activate2;";
	$js .= "}\n";
	//$MyPage->addJS(JSP_FORM, "js", $js);

	$SESSION_VARS['code_param']=NULL;

	//liste libel parametre
	$list_param=getJasperParamCodeByLibel();

	$MyPage->addField("code_param", _("Libellé"), TYPC_LSB);
	$MyPage->setFieldProperties("code_param", FIELDP_ADD_CHOICES, $list_param);
	$MyPage->setFieldProperties("code_param", FIELDP_JS_EVENT, array("onchange"=>"activateButtons();displayLsbButton();"));
        
        $list_param_by_type = getJasperParamCodeByType();

        $js .= "function displayLsbButton() {\nvar paramTypeArr = {};\n";

        foreach($list_param_by_type as $key=>$value) {
            $js .= "paramTypeArr['$key'] = '".trim($value)."';\n";
        }

        $js .= " if( paramTypeArr[document.ADForm.HTML_GEN_LSB_code_param.value] == 'lsb' ) {\n";
        $js .= "document.getElementsByName('lsb_screen')[0].parentNode.style.display = 'table-cell';\n}else{\n";
        $js .= "document.getElementsByName('lsb_screen')[0].parentNode.style.display = 'none';\n}\n\n";
        $js .= "return false;}\ndisplayLsbButton();";
        $MyPage->addJS(JSP_FORM, "js", $js);

        //Bouton Menu déroulant
	$MyPage->addButton("code_param", "lsb_screen", _("Menu déroulant"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("lsb_screen", BUTP_AXS, 271);
	$MyPage->setButtonProperties("lsb_screen", BUTP_PROCHAIN_ECRAN, "Gjr-22");
        
	//Bouton consulter
	$MyPage->addButton("code_param", "consult", _("Consulter"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("consult", BUTP_AXS, 271);
	$MyPage->setButtonProperties("consult", BUTP_PROCHAIN_ECRAN, "Gjr-10");

	//Bouton modifier
	$MyPage->addButton("code_param", "modif", _("Modifier"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("modif", BUTP_AXS, 272);
	$MyPage->setButtonProperties("modif", BUTP_PROCHAIN_ECRAN, "Gjr-11");

	//Bouton supprimer
	$MyPage->addButton("code_param", "supr", _("Supprimer"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("supr", BUTP_AXS, 273);
	$MyPage->setButtonProperties("supr", BUTP_PROCHAIN_ECRAN, "Gjr-13");


	//Bouton créer
	$MyPage->addFormButton(1, 1, "cree", _("Créer un nouveau parametre"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("cree", BUTP_AXS, 270);
	$MyPage->setFormButtonProperties("cree", BUTP_PROCHAIN_ECRAN, "Gjr-9");
	$MyPage->setFormButtonProperties("cree", BUTP_CHECK_FORM, false);

	//Bouton retour
	$MyPage->addFormButton(2, 1, "ret", _("Retour"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("ret", BUTP_PROCHAIN_ECRAN, "Gjr-1");
	$MyPage->setFormButtonProperties("ret", BUTP_CHECK_FORM, false);
	$MyPage->buildHTML();

	echo $MyPage->getHTML();

	/*{{{ Gjr-9 : Ajout paramètre */
} else if ($global_nom_ecran == "Gjr-9") {
	global $adsys;
	$SESSION_VARS['action_param']='ajout';
	$MyPage = new HTML_GEN2(_("Ajout paramètre"));
	$MyPage->addTable("ad_jasper_param" ,OPER_INCLUDE, array("libel","code_param"));

	$MyPage->addField("type_param", _("Type"), TYPC_LSB);
	$MyPage->setFieldProperties("type_param", FIELDP_ADD_CHOICES, $adsys["adsys_jasper_type_param"]);
	$MyPage->setFieldProperties("type_param", FIELDP_IS_REQUIRED,true);
        
        $MyPage->setFieldProperties("type_param", FIELDP_JS_EVENT, array("onchange"=>"refreshFields();"));

        $codejs = "
                  function refreshFields() {
                    var selection_type = document.ADForm.HTML_GEN_LSB_type_param.value;

                    if(selection_type == 'lsb') {
                      document.getElementsByName('HTML_GEN_BOL_type_lsb')[0].parentNode.parentNode.style.display = 'table-row';
                    } else {
                      document.getElementsByName('HTML_GEN_BOL_type_lsb')[0].parentNode.parentNode.style.display = 'none';
                      // Clear fields
                      document.ADForm.HTML_GEN_BOL_type_lsb.checked = false;
                      document.ADForm.table_name_param.value = '';
                      document.ADForm.key_param.value = '';
                      document.ADForm.value_param.value = '';
                    }

                    displayTableFields();

                    return false;
                  }
                  
                  function displayTableFields() {

                    if (document.ADForm.HTML_GEN_BOL_type_lsb.checked) {
                        document.getElementsByName('table_name_param')[0].parentNode.parentNode.style.display = 'table-row';
                        document.getElementsByName('key_param')[0].parentNode.parentNode.style.display = 'table-row';
                        document.getElementsByName('value_param')[0].parentNode.parentNode.style.display = 'table-row';
                        
                        // Clear fields
                        document.ADForm.table_name_param.value = '';
                        document.ADForm.key_param.value = '';
                        document.ADForm.value_param.value = '';
                    } else {
                        // Add blank
                        document.ADForm.table_name_param.value = ' ';
                        document.ADForm.key_param.value = ' ';
                        document.ADForm.value_param.value = ' ';

                        // Hide fields
                        document.getElementsByName('table_name_param')[0].parentNode.parentNode.style.display = 'none';
                        document.getElementsByName('key_param')[0].parentNode.parentNode.style.display = 'none';
                        document.getElementsByName('value_param')[0].parentNode.parentNode.style.display = 'none';
                    }

                    return false;
                  }
                  refreshFields();
    ";

        $MyPage->addJS(JSP_FORM, "JS_LSB", $codejs);

        $MyPage->addField("type_lsb", "Récupérer les données<br/>d'une table?", TYPC_BOL);
        $MyPage->setFieldProperties("type_lsb", FIELDP_JS_EVENT, array("onchange"=>"displayTableFields();"));

        $MyPage->addField("table_name_param", "Nom de la table", TYPC_TXT);
        $MyPage->setFieldProperties("table_name_param", FIELDP_IS_REQUIRED,true);
        
        $MyPage->addField("key_param", "Champ clé", TYPC_TXT);
        $MyPage->setFieldProperties("key_param", FIELDP_IS_REQUIRED,true);
        
        $MyPage->addField("value_param", "Champ valeur", TYPC_TXT);
        $MyPage->setFieldProperties("value_param", FIELDP_IS_REQUIRED,true);

	//Boutons
	$MyPage->addFormButton(1, 1, "butvalid", _("Valider"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butvalid", BUTP_PROCHAIN_ECRAN, "Gjr-12");
	$MyPage->addFormButton(1, 2, "butannul", _("Annuler"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-1");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-10 : Consulté parametre */
}else if ($global_nom_ecran == "Gjr-10") { //consultez parametre
	global $adsys;
	$MyPage = new HTML_GEN2(_("Consulté parametre"));
	$array_fields= array("code_param","libel","type_param");

	ajout_historique(271,NULL, $SESSION_VARS['utilisateur'], $global_nom_login, date("r"), NULL); //Consultation

	$MyPage->addTable("ad_jasper_param", OPER_INCLUDE,  array("code_param","libel"));

	$where["code_param"]=$code_param;
	$params=getJasperparams($array_fields,$where);
	$params=$params->param;
	$contenu=$params[$code_param];

	$MyPage->addField("type_param", _("Type"), TYPC_LSB);
	$MyPage->setFieldProperties("type_param", FIELDP_ADD_CHOICES, $adsys["adsys_jasper_type_param"]);
        
        if (trim($contenu["type_param"]) == 'lsb')
        {
            $paramsExtras = getJasperParamExtras($code_param);

            if ($paramsExtras != NULL && is_array($paramsExtras) && count($paramsExtras)>0)
            {
                $MyPage->addField("type_lsb", "Récupérer les données<br/>d'une table?", TYPC_BOL);
                
                
                if($paramsExtras['type_lsb'] == "dynamic") {
                    $MyPage->setFieldProperties("type_lsb", FIELDP_DEFAULT, 1);
                    
                    $MyPage->addField("table_name_param", "Nom de la table", TYPC_TXT);
                    $MyPage->setFieldProperties("table_name_param", FIELDP_DEFAULT, trim($paramsExtras["table_name_param"]));
                    $MyPage->setFieldProperties("table_name_param", FIELDP_IS_LABEL, true);
                    
                    $MyPage->addField("key_param", "Champ clé", TYPC_TXT);
                    $MyPage->setFieldProperties("key_param", FIELDP_DEFAULT, trim($paramsExtras["key_param"]));
                    $MyPage->setFieldProperties("key_param", FIELDP_IS_LABEL, true);

                    $MyPage->addField("value_param", "Champ valeur", TYPC_TXT);
                    $MyPage->setFieldProperties("value_param", FIELDP_DEFAULT, trim($paramsExtras["value_param"]));
                    $MyPage->setFieldProperties("value_param", FIELDP_IS_LABEL, true);
                }
                
                $MyPage->setFieldProperties("type_lsb", FIELDP_IS_LABEL, true);
            }
        }

	$MyPage->setFieldProperties($array_fields, FIELDP_IS_LABEL, true);
	$MyPage->setFieldProperties("code_param", FIELDP_DEFAULT, $contenu["code_param"]);
	$MyPage->setFieldProperties("type_param", FIELDP_DEFAULT, trim($contenu["type_param"]));
	$MyPage->setFieldProperties("libel", FIELDP_DEFAULT, $contenu["libel"]);

	//Boutons
	$MyPage->addFormButton(1, 1, "butannul", _("Retour"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-8");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();


	/*{{{ Gjr-11 : Modifier parametre jaspere */
}else if ($global_nom_ecran == "Gjr-11") { //Modifier parametre jasper
	global $adsys;
	$SESSION_VARS['action_param']='modif';
	$MyPage = new HTML_GEN2(_("Modifier paramètre"));
	$MyPage->addTable("ad_jasper_param" ,OPER_INCLUDE, array("libel","code_param"));
	$MyPage->addHiddenType("ancien_code_param",$code_param);

	$where["code_param"]=$code_param;
	$params=getJasperparams($array_fields,$where);
	$params=$params->param;
	$contenu=$params[$code_param];

	$MyPage->addField("type_param", _("Type"), TYPC_LSB);
	$MyPage->setFieldProperties("type_param", FIELDP_ADD_CHOICES, $adsys["adsys_jasper_type_param"]);
	$MyPage->setFieldProperties("type_param", FIELDP_IS_REQUIRED,true);
        $MyPage->addHiddenType("type_param_hidden", trim($contenu["type_param"]));
        
        $MyPage->setFieldProperties("type_param", FIELDP_JS_EVENT, array("onchange"=>"refreshFields();"));

        $codejs = "
                  if (!document.ADForm.HTML_GEN_BOL_type_lsb.checked) {
                        // Hide fields
                        document.getElementsByName('table_name_param')[0].parentNode.parentNode.style.display = 'none';
                        document.getElementsByName('key_param')[0].parentNode.parentNode.style.display = 'none';
                        document.getElementsByName('value_param')[0].parentNode.parentNode.style.display = 'none';
                      
                        // Add blank
                        document.ADForm.table_name_param.value = ' ';
                        document.ADForm.key_param.value = ' ';
                        document.ADForm.value_param.value = ' ';
                  }

                  function refreshFields() {
                    var selection_type = document.ADForm.HTML_GEN_LSB_type_param.value;

                    if(selection_type == 'lsb') {
                      document.getElementsByName('HTML_GEN_BOL_type_lsb')[0].parentNode.parentNode.style.display = 'table-row';
                    } else {
                      document.getElementsByName('HTML_GEN_BOL_type_lsb')[0].parentNode.parentNode.style.display = 'none';
                      // Clear fields
                      document.ADForm.HTML_GEN_BOL_type_lsb.checked = false;
                      document.ADForm.table_name_param.value = '';
                      document.ADForm.key_param.value = '';
                      document.ADForm.value_param.value = '';
                    }

                    return false;
                  }
                  
                  function displayTableFields() {

                    if (document.ADForm.HTML_GEN_BOL_type_lsb.checked) {
                        document.getElementsByName('table_name_param')[0].parentNode.parentNode.style.display = 'table-row';
                        document.getElementsByName('key_param')[0].parentNode.parentNode.style.display = 'table-row';
                        document.getElementsByName('value_param')[0].parentNode.parentNode.style.display = 'table-row';
                        
                        // Clear fields
                        document.ADForm.table_name_param.value = '';
                        document.ADForm.key_param.value = '';
                        document.ADForm.value_param.value = '';
                    } else {
                        // Add blank
                        document.ADForm.table_name_param.value = ' ';
                        document.ADForm.key_param.value = ' ';
                        document.ADForm.value_param.value = ' ';

                        // Hide fields
                        document.getElementsByName('table_name_param')[0].parentNode.parentNode.style.display = 'none';
                        document.getElementsByName('key_param')[0].parentNode.parentNode.style.display = 'none';
                        document.getElementsByName('value_param')[0].parentNode.parentNode.style.display = 'none';
                    }

                    return false;
                  }
                  refreshFields();
    ";

        $MyPage->addJS(JSP_FORM, "JS_LSB", $codejs);
        
        $MyPage->addField("type_lsb", "Récupérer les données<br/>d'une table?", TYPC_BOL);
        $MyPage->setFieldProperties("type_lsb", FIELDP_JS_EVENT, array("onchange"=>"displayTableFields();"));
        
        $MyPage->addField("table_name_param", "Nom de la table", TYPC_TXT);
        $MyPage->setFieldProperties("table_name_param", FIELDP_IS_REQUIRED,true);

        $MyPage->addField("key_param", "Champ clé", TYPC_TXT);
        $MyPage->setFieldProperties("key_param", FIELDP_IS_REQUIRED,true);

        $MyPage->addField("value_param", "Champ valeur", TYPC_TXT);
        $MyPage->setFieldProperties("value_param", FIELDP_IS_REQUIRED,true);

        $paramsExtras = getJasperParamExtras($code_param);

        if ($paramsExtras != NULL && is_array($paramsExtras) && count($paramsExtras)>0)
        {
            if($paramsExtras['type_lsb'] == "dynamic") {

                $MyPage->setFieldProperties("type_lsb", FIELDP_DEFAULT, 1);

                $MyPage->setFieldProperties("table_name_param", FIELDP_DEFAULT, trim($paramsExtras["table_name_param"]));

                $MyPage->setFieldProperties("key_param", FIELDP_DEFAULT, trim($paramsExtras["key_param"]));

                $MyPage->setFieldProperties("value_param", FIELDP_DEFAULT, trim($paramsExtras["value_param"]));
            }
        }

	debug($contenu["type_param"]);
	$MyPage->setFieldProperties("code_param", FIELDP_DEFAULT, $contenu["code_param"]);
	$MyPage->setFieldProperties("type_param", FIELDP_DEFAULT, trim($contenu["type_param"]));
	$MyPage->setFieldProperties("libel", FIELDP_DEFAULT, $contenu["libel"]);

	//Boutons
	$MyPage->addFormButton(1, 1, "butvalid", _("Valider"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butvalid", BUTP_PROCHAIN_ECRAN, "Gjr-12");
	$MyPage->addFormButton(1, 2, "butannul", _("Annuler"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-1");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-12 :Confirmation ajout ou Modification parametre jaspere */
} else if ($global_nom_ecran == "Gjr-12") {

	global  $doc_prefix;
	global $global_id_agence;
	$data["code_param"]=$code_param;
	$data["libel"] = $libel;
	$data["type_param"]=$type_param;
	$data["id_ag"]= $global_id_agence;


	if ( $SESSION_VARS['action_param']=='ajout') {
		$err=insertionJasperParam($data);
                
                if ($err->errCode == NO_ERR) {
                    if ($type_param == 'lsb') {

                        if (isset($type_lsb) && $type_lsb == true) {
                            $type_lsb_libel = 'dynamic';
                        } else {
                            $type_lsb_libel = 'static';                        
                        }

                        $err = insertJasperParamExtras($code_param, $type_lsb_libel, $table_name_param, $key_param, $value_param);
                    }
                }

		$titre_err=_("Echec de l'ajout du paramètre.");
		$titre=sprintf(_("Confirmation ajout paramètre  '%s'",$data[  "libel"]));
		$ecran="Gjr-9";
		$msg=sprintf(_("Le rapport '%s' a été ajouté avec succès !"),$data[  "libel"]);
	} elseif ( $SESSION_VARS['action_param']=='modif') {
		$where=array("code_param"=>trim($ancien_code_param));
		$err=updateJasperParam($data,$where);
                
                if ($err->errCode == NO_ERR) {
                    if ($type_param == 'lsb') {

                        if (isset($type_lsb) && $type_lsb == true) {
                            $type_lsb_libel = 'dynamic';
                        } else {
                            $type_lsb_libel = 'static';                        
                        }

                        $err = updateJasperParamExtras($ancien_code_param, $code_param, $type_lsb_libel, $table_name_param, $key_param, $value_param);
                    } elseif ($type_param_hidden == 'lsb') {
                        $myErr2=deleteJasperParamExtras($where);
                    }
                }

		$titre_err=_("Echec de la Modification du paramètre.");
		$titre=sprintf(_("Confirmation Modification paramètre  '%s'",$data[  "libel"]));
		$ecran="Gjr-8";
		$msg=sprintf(_("Le paramètre '%s' a été modifié avec succès !"),$data[  "libel"]);
	}
	$SESSION_VARS['action_param']=NULL;
	unset($SESSION_VARS['action_param']);
	if ($err->errCode != NO_ERR) {
		sendMsgErreur($titre_err,$err,$ecran);
	} else {
		sendMsgConfirmation($titre,$msg,$ecran);
	}

	/*{{{ Gjr-13 :Suppression parametre jaspere */
}  else if ($global_nom_ecran == "Gjr-13") { //suppression du paramètre

	$SESSION_VARS['code_param']=$code_param;
	$MyPage = new HTML_message(_('Demande confirmation'));
	$MyPage->setMessage(sprintf(_("Etes-vous sûr de vouloir supprimer le paramètre   %s ?"),$code_param));

	//Boutons
	$MyPage->addButton(BUTTON_OUI, "Gjr-14");
	$MyPage->addButton(BUTTON_NON, "Gjr-8");

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-14 :Confirmation Suppression parametre jaspere */
}  else if ($global_nom_ecran == "Gjr-14") {
	global $global_id_agence;
	//suppression du rapport
	$code_param=$SESSION_VARS['code_param'];
	if ( !isset($SESSION_VARS['suppr_param_rapport'])) {
		$SESSION_VARS['suppr_param_rapport']=false;
	}
	$where['code_param']=trim($code_param);
	$param_rap=getJasperParamRapport($where);
	$titre_err=sprintf(_("Impossible de supprimer le paramètre '%s'"),$code_param);
	if ($param_rap->errCode != NO_ERR) {
		sendMsgErreur($titre_err,$err,"Gjr-8");
	} else {

		if(count($param_rap->param) > 0 &&  $SESSION_VARS['suppr_param_rapport']!= true) {
			$MyPage = new HTML_message(_('Demande confirmation'));
			$MyPage->setMessage(sprintf(sprintf(_("Le paramètre '%s' est associé au(x) rapport(s) <br> Etes-vous sûr de vouloir supprimer le paramètre ?  !"),$code_param)));

			//Boutons
			$MyPage->addButton(BUTTON_OUI, "Gjr-14");
			$MyPage->addButton(BUTTON_NON, "Gjr-8");


			$MyPage->buildHTML();
			echo $MyPage->HTML_code;
			$SESSION_VARS['suppr_param_rapport']=true;
			exit(0);
		}

	}
	if ($SESSION_VARS['suppr_param_rapport'] == true) {
		$where["id_ag"]=$global_id_agence;
		$rep=deleteJasperParamRapport($where);

	}
	//
	unset ($SESSION_VARS['suppr_param_rapport']);
	$SESSION_VARS['code_param']=NULL;
	unset($SESSION_VARS['code_param']);
	//
        $myErr2=deleteJasperParamExtras($where);
        
        if ($myErr->errCode == NO_ERR) {
            $myErr=deleteJasperParam($where);
            if ($myErr->errCode != NO_ERR) {
                    $MyPage = new HTML_erreur(sprintf(_("Impossible de supprimer le paramètre '%s'"),$code_param));
                    $msg = _("Impossible de supprimer le paramètre")." '".$code_param."<br />".$myErr->param;
                    $MyPage->setMessage($msg);
                    $MyPage->addButton(BUTTON_OK, "Gjr-8");

                    $MyPage->buildHTML();
                    echo $MyPage->HTML_code;

            } else {
                    //HTML
                    $MyPage = new HTML_message(_("Confirmation suppression"));
                    $MyPage->setMessage(sprintf(_("Le paramètre '%s' a bien été supprimé !"),$code_param));
                    $MyPage->addButton(BUTTON_OK, "Gjr-8");
                    $MyPage->buildHTML();
                    echo $MyPage->HTML_code;
            }
        } else {
            $MyPage = new HTML_erreur(sprintf(_("Impossible de supprimer le paramètre '%s'"),$code_param));
            $msg = _("Impossible de supprimer le paramètre")." '".$code_param."<br />".$myErr->param;
            $MyPage->setMessage($msg);
            $MyPage->addButton(BUTTON_OK, "Gjr-8");

            $MyPage->buildHTML();
            echo $MyPage->HTML_code;
        }

	/*{{{ Gjr-15 :Associer/dissocier  parametre au rapport  jaspere */
}else if ($global_nom_ecran == "Gjr-15") {
	global  $doc_prefix;
	global $global_id_agence;
	global $adsys;


	// rapport
	$fields_array=array('code_rapport','libel');
	if ( $SESSION_VARS['code_rapport'] == NULL ) {
		$code_rapport=$_POST['code_rapport'];
		$SESSION_VARS['code_rapport']=$code_rapport;
	} else {
		$code_rapport=$SESSION_VARS['code_rapport'];
	}

	$where['code_rapport']=$code_rapport;
	$rapport=getJasperRapports($fields_array,$where);
	$rapport=$rapport->param;

	//confirmation associer paramètre au rapport
	if($action == 'ajout' ) {
		//associé paramètre
		$data['code_rapport']=$code_rapport;
		$data['code_param']=$code_param;
		$data['id_ag']=$global_id_agence;
		insertionJasperParamRapport($data);
	} elseif ($action='suppr') {
		$where=array();
		$where["code_rapport"]=$code_rapport;
		$where["code_param"]=$code_param_suppr;
		$where["id_ag"]=$global_id_agence;
		$rep=deleteJasperParamRapport($where);

	}


	$MyPage = new HTML_GEN2( _("Associer paramètre au rapport"));

	$MyPage->addField("code_rapport", _("Réference"), TYPC_TXT);
	$MyPage->setFieldProperties("code_rapport", FIELDP_IS_LABEL,true);
	$MyPage->setFieldProperties("code_rapport", FIELDP_DEFAULT,$rapport[$code_rapport]['code_rapport']);

	$MyPage->addField("libel_rapport", _("Rapport"), TYPC_TXT);
	$MyPage->setFieldProperties("libel_rapport", FIELDP_IS_LABEL,true);
	$MyPage->setFieldProperties("libel_rapport", FIELDP_DEFAULT,$rapport[$code_rapport]['libel']);

	//liste libel parametre
	$list_param=getJasperParamCodeByLibel();

	$MyPage->addField("code_param", _("Paramètre"), TYPC_LSB);
	$MyPage->setFieldProperties("code_param", FIELDP_ADD_CHOICES, $list_param);
	$MyPage->setFieldProperties("code_param", FIELDP_JS_EVENT, array("onchange"=>"activateButtons();"));

	//Boutons
	$MyPage->addFormButton(1, 1, "butvalid", _("Valider"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butvalid", BUTP_PROCHAIN_ECRAN, "Gjr-15");
	$MyPage->addFormButton(1, 2, "butannul", _("Annuler"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-1");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->addHiddenType("action",'ajout');

	//afficher la liste des paramètre associés au rapport selectionné.
	$table =& $MyPage->addHTMLTable('tableparam', 4 /*nbre colonnes*/, TABLE_STYLE_ALTERN);
	// Création de la ligne de titres
	$table->add_cell(new TABLE_cell(_("Code"),	/*colspan*/1,	/*rowspan*/2	));
	$table->add_cell(new TABLE_cell(_("Libellé"),	/*colspan*/1,	/*rowspan*/2	));
	$table->add_cell(new TABLE_cell(_("type"),	/*colspan*/1,	/*rowspan*/2	));
	$table->add_cell(new TABLE_cell(_("ACTION"),	/*colspan*/1,	/*rowspan*/2	));


	$params=getJasperParamsRapports($code_rapport)  ;
	$params=$params->param;
	foreach ($params as $key=>$value) {
		$table->add_cell(new TABLE_cell($value["code_param"]	));
		$table->add_cell(new TABLE_cell($value["libel"]));debug($value['type_param']);
		$type=$adsys["adsys_type_champs"][trim($value['type_param'])] ;
		$table->add_cell(new TABLE_cell( $adsys["adsys_type_champs"][ trim($value["type_param"]) ]  ) );
		$table->add_cell(new TABLE_cell_link(_("Supprimer"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gjr-15&action=suppr&code_param_suppr=".$value["code_param"]."&ac=ff"));

	}

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

}/*}}}*//*{{{ Gjr-19 : Gestion des états de crédit */
else if ($global_nom_ecran == "Gjr-19") {
	if ($_POST['list_agence']=="" && $SESSION_VARS['select_agence']=="")
	$SESSION_VARS['select_agence']=$global_id_agence;
	elseif($SESSION_VARS['select_agence']=="")
	$SESSION_VARS['select_agence']=$_POST['list_agence'];

	setGlobalIdAgence($SESSION_VARS['select_agence']);
	if( !isset($SESSION_VARS['code_rapport'])) {
		$SESSION_VARS['code_rapport']=$code_rapport;
	}

	if (file_exists($fichier_lot)) {
		$filename = $fichier_lot.".tmp";
		move_uploaded_file($fichier_lot, $filename);
		exec("chmod a+r ".escapeshellarg($filename));
		$SESSION_VARS['fichier_lot'] = $filename;
	} else {
		$SESSION_VARS['fichier_lot'] = NULL;
	}

	$titre=_("Récupération du fichier de données");
	$titre.=" ".adb_gettext($adsys["adsys_rapport_BNR"][$SESSION_VARS['type_rapport']]);
	$MyPage = new HTML_GEN2($titre);

	$htm1 = "<P align=\"center\">"._("Fichier de données").": <INPUT name=\"fichier_lot\" type=\"file\" /></P>";
	$htm1 .= "<P align=\"center\"> <INPUT type=\"submit\" value=\"Envoyer\" onclick=\"document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Gjr-19';\"/> </P>";
	$htm1 .= "<BR/>";

	$MyPage->addHTMLExtraCode("htm1", $htm1);

	$MyPage->AddField("statut", _("Statut"), TYPC_TXT);
	$MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);

	if ($SESSION_VARS['fichier_lot'] == NULL) {
		$MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier non reçu"));
	} else {
		$MyPage->setFieldProperties("statut", FIELDP_DEFAULT, _("Fichier reçu"));
	}

	$MyPage->addHTMLExtraCode("htm2", "<BR>");

	$MyPage->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
	$MyPage->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
	$MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);

	$MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Gjr-20');
	$MyPage->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Gjr-1');
	$MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gjr-1');

	$MyPage->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
	$MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();
}
/*}}}*/
/*{{{ Frc-3 :Demande de confirmation */
else if ($global_nom_ecran == 'Gjr-20') {
	global $adsys;


	$MyErr=parse_format_etat_compta_poste($SESSION_VARS['fichier_lot'], $SESSION_VARS['code_rapport']);
	if ($MyErr->errCode != NO_ERR) {
		$param = $MyErr->param;
		$html_err = new HTML_erreur(_("Echec de récupération du fichier de données"));
		$msg = _("Erreur : ").$error[$MyErr->errCode];
		if ($param != NULL) {
			if(is_array($param)){
				foreach($param as $key => $val){
					$msg .= "<BR> (".$key." : ".$param["$key"].")";
				}
			}

		}
		$html_err->setMessage($msg);
		$html_err->addButton("BUTTON_OK", 'Gjr-19');
		$html_err->buildHTML();
		echo $html_err->HTML_code;
	}elseif ($MyErr->errCode == NO_ERR){
		//verifier si le type de rapport existe
		$ok=existePosteCompteRapport($SESSION_VARS['code_rapport']);
		if(!$ok ){
			$titre=_("Demande confirmation de l'ajout des postes du rapport'");
			$msg=sprintf(_("Etes-vous sûr de vouloir ajouter les postes du rapport '%s' ?"),$SESSION_VARS['code_rapport']);
			$MyPage = new HTML_message($titre);
			$MyPage->setMessage($msg);
			$MyPage->addButton(BUTTON_OUI, "Gjr-21");
			$MyPage->addButton(BUTTON_NON, "Gjr-1");

			$MyPage->buildHTML();
			echo $MyPage->HTML_code;
		}else {
			$titre=_("Demande de suppression du rapport");
			$msg=sprintf(_("Le rapport '%s' existe dèjà,  êtes-vous sûr de vouloir le supprimer ?"),$SESSION_VARS['code_rapport']);
			$html_err = new HTML_erreur($titre);
			$html_err->setMessage($msg);
			$html_err->addCustomButton("BUTTON_OUI","OUI",  "Gjr-21");
			$html_err->addCustomButton("BUTTON_NON","NON", "Gjr-1");
			$html_err->buildHTML();
			echo $html_err->HTML_code;
		}



	}
}
/*}}}*/
/*{{{ Frc-4 : confirmation */
else if ($global_nom_ecran == "Gjr-21") {
	$MyErr=parse_format_etat_compta_poste($SESSION_VARS['fichier_lot'], $SESSION_VARS['code_rapport']);
	if ($MyErr->errCode != NO_ERR) {
		$param = $MyErr->param;
		$html_err = new HTML_erreur(_("Echec de récupération du fichier de données"));
		$msg = _("Erreur")." : ".$error[$MyErr->errCode];
		if ($param != NULL) {
			if(is_array($param)){
				foreach($param as $key => $val){
					$msg .= "<br /> (".$key." : ".$param["$key"].")";
				}
			}

		}
		$html_err->setMessage($msg);
		$html_err->addButton("BUTTON_OK", "Gjr-19");
		$html_err->buildHTML();
		echo $html_err->HTML_code;
	}elseif ($MyErr->errCode == NO_ERR){
		$param = $MyErr->param;
		debug($param);
		deletePostesCompteRapport($SESSION_VARS['code_rapport']);
		deletePostesRapport($SESSION_VARS['code_rapport']);
		 
		$MyErr=insertionPostesAndComptes($param['data']);
		 
		if ($MyErr->errCode != NO_ERR) {
			$param = $MyErr->param;
			$html_err = new HTML_erreur(_("Echec de récupération du fichier de données"));
			$msg = _("Erreur : ").$error[$MyErr->errCode];
			if ($param != NULL) {
				if(is_array($param)){
					foreach($param as $key => $val){
						$msg .= "<br /> (".$key." : ".$param["$key"].")";
					}
				}

			}
			$html_err->setMessage($msg);
			$html_err->addButton("BUTTON_OK", 'Gjr-1');
			$html_err->buildHTML();
			echo $html_err->HTML_code;
			exit(0);
		}
		$MyPage = new HTML_message(_("Confirmation de l'ajout des postes du rapport'"));
		$MyPage->setMessage(_("Les postes ont été ajoutés avec succès"));
		$MyPage->addButton(BUTTON_OK, "Gjr-1");


		$MyPage->buildHTML();
		echo $MyPage->HTML_code;
	}
    /*{{{ Gjr-22 : Gestion de parametre menu déroulant */
}  elseif ($global_nom_ecran == "Gjr-22") {

	$MyPage = new HTML_GEN2("Gestion paramètre menu déroulant '$code_param'");
	//Javascript
	$js = "document.ADForm.consult.disabled = true; document.ADForm.modif.disabled = true; document.ADForm.supr.disabled = true;\n";
	$js .= "function activateButtons(){\n";
	$js .= "activate = (document.ADForm.HTML_GEN_LSB_cle.value != 0);";
	$js .= "document.ADForm.consult.disabled = !activate; document.ADForm.modif.disabled = !activate; document.ADForm.supr.disabled = !activate;";
	$js .= "}\n";
	$MyPage->addJS(JSP_FORM, "js", $js);

	// Liste libel parametre
	$list_param = getListeJasperParamLsb($code_param);

	$MyPage->addField("cle", _("Libellé"), TYPC_LSB);
	$MyPage->setFieldProperties("cle", FIELDP_ADD_CHOICES, $list_param);
	$MyPage->setFieldProperties("cle", FIELDP_JS_EVENT, array("onchange"=>"activateButtons();displayLsbButton();"));
        
	//Bouton consulter
	$MyPage->addButton("cle", "consult", _("Consulter"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("consult", BUTP_AXS, 271);
	$MyPage->setButtonProperties("consult", BUTP_PROCHAIN_ECRAN, "Gjr-23");

	//Bouton modifier
	$MyPage->addButton("cle", "modif", _("Modifier"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("modif", BUTP_AXS, 272);
	$MyPage->setButtonProperties("modif", BUTP_PROCHAIN_ECRAN, "Gjr-25");

	//Bouton supprimer
	$MyPage->addButton("cle", "supr", _("Supprimer"), TYPB_SUBMIT);
	$MyPage->setButtonProperties("supr", BUTP_AXS, 273);
	$MyPage->setButtonProperties("supr", BUTP_PROCHAIN_ECRAN, "Gjr-27");

	//Bouton créer
	$MyPage->addFormButton(1, 1, "cree", _("Créer un nouveau parametre"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("cree", BUTP_AXS, 270);
	$MyPage->setFormButtonProperties("cree", BUTP_PROCHAIN_ECRAN, "Gjr-24");
	$MyPage->setFormButtonProperties("cree", BUTP_CHECK_FORM, false);
        $MyPage->addHiddenType("code_param", $code_param);

	//Bouton retour
	$MyPage->addFormButton(2, 1, "ret", _("Retour"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("ret", BUTP_PROCHAIN_ECRAN, "Gjr-8");
	$MyPage->setFormButtonProperties("ret", BUTP_CHECK_FORM, false);
	$MyPage->buildHTML();

	echo $MyPage->getHTML();

        /*{{{ Gjr-23 : Consulté parametre menu déroulant */
}else if ($global_nom_ecran == "Gjr-23") { //consultez parametre

	$MyPage = new HTML_GEN2(_("Consulté parametre menu déroulant '$code_param'"));
        
        $paramsLsb = getJasperParamLsbByCle($code_param, $cle);

        $MyPage->addField("cle", "Clé", TYPC_TXT);
        $MyPage->setFieldProperties("cle", FIELDP_DEFAULT, trim($paramsLsb["cle"]));
        $MyPage->setFieldProperties("cle", FIELDP_IS_LABEL, true);

        $MyPage->addField("valeur", "Valeur", TYPC_TXT);
        $MyPage->setFieldProperties("valeur", FIELDP_DEFAULT, trim($paramsLsb["valeur"]));
        $MyPage->setFieldProperties("valeur", FIELDP_IS_LABEL, true);

        $MyPage->addHiddenType("code_param", $code_param);

	//Boutons
	$MyPage->addFormButton(1, 1, "butannul", _("Retour"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-22");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-24 : Ajout paramètre menu déroulant */
} else if ($global_nom_ecran == "Gjr-24") {
        
	$SESSION_VARS['action_param'] = 'ajout';
        
	$MyPage = new HTML_GEN2(_("Ajout paramètre '$code_param'"));
        
        $MyPage->addField("cle", "Clé", TYPC_TXT);
        $MyPage->setFieldProperties("cle", FIELDP_IS_REQUIRED, true);

        $MyPage->addField("valeur", "Valeur", TYPC_TXT);
        $MyPage->setFieldProperties("valeur", FIELDP_IS_REQUIRED, true);

        $MyPage->addHiddenType("code_param", $code_param);

	//Boutons
	$MyPage->addFormButton(1, 1, "butvalid", _("Valider"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butvalid", BUTP_PROCHAIN_ECRAN, "Gjr-26");
	$MyPage->addFormButton(1, 2, "butannul", _("Annuler"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-22");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

        /*{{{ Gjr-25 : Modifier paramètre menu déroulant */
} else if ($global_nom_ecran == "Gjr-25") {

        $SESSION_VARS['action_param']='modif';
        $MyPage = new HTML_GEN2(_("Modifier paramètre '$code_param'"));

        $paramsLsb = getJasperParamLsbByCle($code_param, $cle);
        
        $MyPage->addField("cle", "Clé", TYPC_TXT);
        $MyPage->setFieldProperties("cle", FIELDP_DEFAULT, trim($paramsLsb["cle"]));
        $MyPage->setFieldProperties("cle", FIELDP_IS_REQUIRED, true);
        $MyPage->addHiddenType("ancien_cle", trim($paramsLsb["cle"]));

        $MyPage->addField("valeur", "Valeur", TYPC_TXT);
        $MyPage->setFieldProperties("valeur", FIELDP_DEFAULT, trim($paramsLsb["valeur"]));
        $MyPage->setFieldProperties("valeur", FIELDP_IS_REQUIRED, true);

        $MyPage->addHiddenType("code_param", $code_param);

	//Boutons
	$MyPage->addFormButton(1, 1, "butvalid", _("Valider"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butvalid", BUTP_PROCHAIN_ECRAN, "Gjr-26");
	$MyPage->addFormButton(1, 2, "butannul", _("Annuler"), TYPB_SUBMIT);
	$MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gjr-22");
	$MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-26 : Confirmation ajout ou modif parametre menu deroulant */
} else if ($global_nom_ecran == "Gjr-26") {

	global  $doc_prefix;
	global $global_id_agence;

	$data["code_param"] = $code_param;
	$data["cle"] = $cle;
	$data["valeur"] = $valeur;
	$data["id_ag"] = $global_id_agence;

	if ( $SESSION_VARS['action_param']=='ajout') {
		$err = insertJasperParamLsb($code_param, $cle, $valeur);

		$titre_err=_("Echec de l'ajout du paramètre.");
		$titre=sprintf(_("Confirmation ajout paramètre  '%s'", $valeur));
		$ecran="Gjr-22";
		$msg=sprintf(_("Le paramètre '%s' a été ajouté avec succès !"), $valeur);
	} elseif ( $SESSION_VARS['action_param']=='modif') {
		$err = updateJasperParamLsb($code_param, $ancien_cle, $cle, $valeur);

		$titre_err=_("Echec de la Modification du paramètre.");
		$titre=sprintf(_("Confirmation Modification paramètre  '%s'", $valeur));
		$ecran="Gjr-22";
		$msg=sprintf(_("Le paramètre '%s' a été modifié avec succès !"), $valeur);
	}
	$SESSION_VARS['action_param']=NULL;
	unset($SESSION_VARS['action_param']);
        
        $hdd_el = '<input type="hidden" name="code_param" value="'.$code_param.'"/>';
	if ($err->errCode != NO_ERR) {
            sendMsgErreur($titre_err,$titre_err.$hdd_el,$ecran);
	} else {
            sendMsgConfirmation($titre,$msg.$hdd_el,$ecran);
	}

	/*{{{ Gjr-27 :Suppression parametre cle */
}  else if ($global_nom_ecran == "Gjr-27") { //suppression du paramètre

	$SESSION_VARS['cle'] = $cle;
	$SESSION_VARS['code_param'] = $code_param;
        
        $hdd_el = '<input type="hidden" name="code_param" value="'.$code_param.'"/>';
        
	$MyPage = new HTML_message(_('Demande confirmation'));
	$MyPage->setMessage(sprintf(_("Etes-vous sûr de vouloir supprimer la clé %s du paramètre %s ?"), $cle, $code_param).$hdd_el);

	//Boutons
	$MyPage->addButton(BUTTON_OUI, "Gjr-28");
	$MyPage->addButton(BUTTON_NON, "Gjr-22");

	$MyPage->buildHTML();
	echo $MyPage->getHTML();

	/*{{{ Gjr-28 :Confirmation Suppression parametre cle */
}  else if ($global_nom_ecran == "Gjr-28") {
	global $global_id_agence;
        
	//suppression du rapport
	$cle = $SESSION_VARS['cle'];
	$code_param = $SESSION_VARS['code_param'];
        
	$where['cle'] = $cle;
	$where['code_param'] = $code_param;
        $where["id_ag"] = $global_id_agence;
        
	// Clear session
	$SESSION_VARS['cle'] = NULL;
	$SESSION_VARS['code_param'] = NULL;
	unset($SESSION_VARS['cle'], $SESSION_VARS['code_param']);
        
        $myErr = deleteJasperParamLsb($where);
        
        $hdd_el = '<input type="hidden" name="code_param" value="'.$code_param.'"/>';
        if ($myErr->errCode != NO_ERR) {
                $MyPage = new HTML_erreur(sprintf(_("Impossible de supprimer la clé %s du paramètre '%s'"),$cle,$code_param));
                $msg = _("Impossible de supprimer la clé")." '".$cle."<br />".$myErr->param;
                $MyPage->setMessage($msg.$hdd_el);
                $MyPage->addButton(BUTTON_OK, "Gjr-22");

                $MyPage->buildHTML();
                echo $MyPage->HTML_code;

        } else {
                //HTML
                $MyPage = new HTML_message(_("Confirmation suppression"));
                $MyPage->setMessage(sprintf(_("La clé %s du paramètre '%s' a bien été supprimé !"),$cle,$code_param).$hdd_el);
                $MyPage->addButton(BUTTON_OK, "Gjr-22");
                $MyPage->buildHTML();
                echo $MyPage->HTML_code;
        }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>