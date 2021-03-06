<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/*
 * Dépôt par lot via fichier
 * @author Antoine Guyette
 * @since 01/03/2006
 * @package Guichet
 */

require_once 'lib/dbProcedures/guichet.php';
require_once "modules/rapports/csv_epargne.php";
require_once 'lib/misc/csv.php';
require_once 'modules/rapports/xslt.php';
require_once ('lib/dbProcedures/client.php');
require_once ('lib/misc/tableSys.php');

/*{{{ Dlf-1 : Choix de la source des fonds */
//if ($global_nom_ecran == "Ich-1") {
//}
/*}}}*/

/*{{{ Dlf-2 : Récupération du fichier de données */
//else
if ($global_nom_ecran == 'Ich-1') {
	if ($_POST['list_agence']=="" && $SESSION_VARS['select_agence']=="")
	$SESSION_VARS['select_agence']=$global_id_agence;
	elseif($SESSION_VARS['select_agence']=="")
	$SESSION_VARS['select_agence']=$_POST['list_agence'];
	setGlobalIdAgence($SESSION_VARS['select_agence']);


	if (file_exists($fichier_lot)) {
		$filename = $fichier_lot.".tmp";
		move_uploaded_file($fichier_lot, $filename);
		exec("chmod a+r ".escapeshellarg($filename));
		$SESSION_VARS['fichier_lot'] = $filename;
	} else {
		$SESSION_VARS['fichier_lot'] = NULL;
	}


	$titre=_("Récupération du fichier de données");
	// $titre.=" ".adb_gettext($adsys["adsys_rapport_BNR"][$SESSION_VARS['type_rapport']]);
	$MyPage = new HTML_GEN2($titre);

	$htm1 = "<P align=\"center\">"._("Fichier de données").": <INPUT name=\"fichier_lot\" type=\"file\" /></P>";
	$htm1 .= "<P align=\"center\"> <INPUT type=\"submit\" value=\"Envoyer\" onclick=\"document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';document.ADForm.prochain_ecran.value='Ich-1';\"/> </P>";
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
	$MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);

	$MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Ich-2');
	$MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-6');
	$MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

	$MyPage->buildHTML();
	echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Ich-2 : confirmation */
else if ($global_nom_ecran == 'Ich-2') {
	global $adsys;
	$MyErr=parse_ajout_chequier_imprimer_fichier($SESSION_VARS['fichier_lot']);
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
		$html_err->addButton("BUTTON_OK", 'Ich-1');
		$html_err->buildHTML();
		echo $html_err->HTML_code;
		exit();
	}elseif ($MyErr->errCode == NO_ERR){
		debug($MyErr->param['data']);
		$err = traite_ajout_chequier_imprimer ($MyErr->param['data']);
		
		if($err->errCode != NO_ERR) {
			sendMsgErreur(_('erreur insertion chéquiers imprimés'),$err,'Ich-1');
		}
		
		global $global_id_agence, $global_nom_login, $global_id_client;
		// Enregistrement - Ajout chéquiers imprimés
		ajout_historique(161, $global_id_client, 'Ajout chéquiers imprimés', $global_nom_login, date("r"), NULL);
		
		$MyPage = new HTML_message(_("Confirmation de l'ajout des chéquiers imprimés"));
		$MyPage->setMessage(_("Les chéquiers ont été ajoutés avec succès"));
		$MyPage->addButton(BUTTON_OK, "Gen-6");
		$MyPage->buildHTML();
		echo $MyPage->HTML_code;
	}
}
/*}}}*/
/*{{{ Frc-4 : confirmation */
elseif ($global_nom_ecran == 'Ich-3' ) {
    global $global_nom_ecran_prec;
	$title = _("Liste chèquiers à imprimer");
	$checked = true;
	$SESSION_VARS['liste_chequiers'] = NULL;
	if($global_nom_ecran_prec == 'Ich-5'){
        // On construit la liste des comptes pour lesquels un chèquier est en attente d'impression (etat = 1)
        $result = getListChequiersPrintParNiveau($n_agence,$n_guichet);
        if ($result->errCode == NO_ERR) {
            $liste_comptes = $result->param;
            debug($liste_comptes);
            if (count($liste_comptes) > 0) {
                // Nous avons des chèquiers à imprimer
                $my_page = new HTML_GEN2($title);

                $liste_cptes = "";
                foreach ($liste_comptes as $id => $chequier) {
                    $id_cpte = $chequier["id_cpte"];
                    $id_comde_chequier = $chequier["id"];
                    $nom_cli = getClientName($chequier["id_titulaire"]);
                    $data_client = getClientDatas($chequier["id_titulaire"]);
                    $nbre_chequiers = $chequier["nbre_carnets"];
                    $num_complet_cpte = $chequier["num_complet_cpte"];
                    $libelle = sprintf(_(" %s - %s - %s chéquier(s)"), $num_complet_cpte, $nom_cli, $nbre_chequiers);
                    $liste_commande_chequiers [$id_comde_chequier]['id'] = $id_comde_chequier;
                    $liste_commande_chequiers [$id_comde_chequier]['nom'] = $nom_cli;
                    $liste_commande_chequiers [$id_comde_chequier]['nbre_carnets'] = $nbre_chequiers;
                    $liste_commande_chequiers [$id_comde_chequier]['num_complet_cpte'] = $num_complet_cpte;
                    $liste_commande_chequiers [$id_comde_chequier]['n_agence'] = empty($data_client['n_agence'])?"N/A":getLibelLocalisationFenacobu($data_client['n_agence']);
                    $liste_commande_chequiers [$id_comde_chequier]['n_guichet'] = empty($data_client['n_guichet'])?"N/A":getLibelLocalisationFenacobu($data_client['n_guichet']);

                    $my_page->addField("check_" . $id_comde_chequier, _("$libelle"), TYPC_BOL);
                    $my_page->setFieldProperties("check_" . $id_comde_chequier, FIELDP_DEFAULT, $checked);
                }
                //la liste des tous les comptes pour lesquels on pourrait imprimer un chèquier
                $SESSION_VARS['liste_chequiers'] = $liste_commande_chequiers;

                $my_page->addFormButton(1, 1, "valid", _("Valider"), TYPB_SUBMIT);
                $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, 'Ich-4');
                $my_page->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
                $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-6");
                $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
            } else {
                // Aucun chèquier ne doit être imprimé
                $my_page = new HTML_message(_("Aucun chèquier"));
                $my_page->setMessage(_("Aucun chèquier ne doit être imprimé ni confirmé."), true);
                $my_page->addButton("BUTTON_OK", 'Gen-6');
            }
        } else {
            // Erreur d'exécution
            $my_page = new HTML_erreur(_("Echec lors de la visualisation des chèquiers à imprimer ou confirmer."));
            $my_page->setMessage("Erreur : " . $error[$result->errCode] . "<br />Paramètre : " . $result->param);
            $my_page->addButton("BUTTON_OK", 'Ich-1');
        }
    }
	else {
        // On construit la liste des comptes pour lesquels un chèquier est en attente d'impression (etat = 1)
        $result = getListChequiersPrint();
        if ($result->errCode == NO_ERR) {
            $liste_comptes = $result->param;
            debug($liste_comptes);
            if (count($liste_comptes) > 0) {
                // Nous avons des chèquiers à imprimer
                $my_page = new HTML_GEN2($title);

                $liste_cptes = "";
                foreach ($liste_comptes as $id => $chequier) {
                    $id_cpte = $chequier["id_cpte"];
                    $id_comde_chequier = $chequier["id"];
                    $nom_cli = getClientName($chequier["id_titulaire"]);
                    $nbre_chequiers = $chequier["nbre_carnets"];
                    $num_complet_cpte = $chequier["num_complet_cpte"];
                    $libelle = sprintf(_(" %s - %s - %s chéquier(s)"), $num_complet_cpte, $nom_cli, $nbre_chequiers);
                    $liste_commande_chequiers [$id_comde_chequier]['id'] = $id_comde_chequier;
                    $liste_commande_chequiers [$id_comde_chequier]['nom'] = $nom_cli;
                    $liste_commande_chequiers [$id_comde_chequier]['nbre_carnets'] = $nbre_chequiers;
                    $liste_commande_chequiers [$id_comde_chequier]['num_complet_cpte'] = $num_complet_cpte;

                    $my_page->addField("check_" . $id_comde_chequier, _("$libelle"), TYPC_BOL);
                    $my_page->setFieldProperties("check_" . $id_comde_chequier, FIELDP_DEFAULT, $checked);
                }
                //la liste des tous les comptes pour lesquels on pourrait imprimer un chèquier
                $SESSION_VARS['liste_chequiers'] = $liste_commande_chequiers;

                $my_page->addFormButton(1, 1, "valid", _("Valider"), TYPB_SUBMIT);
                $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, 'Ich-4');
                $my_page->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
                $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-6");
                $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
            } else {
                // Aucun chèquier ne doit être imprimé
                $my_page = new HTML_message(_("Aucun chèquier"));
                $my_page->setMessage(_("Aucun chèquier ne doit être imprimé ni confirmé."), true);
                $my_page->addButton("BUTTON_OK", 'Gen-6');
            }
        } else {
            // Erreur d'exécution
            $my_page = new HTML_erreur(_("Echec lors de la visualisation des chèquiers à imprimer ou confirmer."));
            $my_page->setMessage("Erreur : " . $error[$result->errCode] . "<br />Paramètre : " . $result->param);
            $my_page->addButton("BUTTON_OK", 'Ich-1');
        }
    }
	$my_page->show();
}
/*}}}*/

/*{{{ Era-23 : Export pour impression chèquiers */
else
if ($global_nom_ecran == 'Ich-4') {
	// Récupérer les identifiants des compte pours lesquels ont doit imprimer un chèquier
	$tous_commande_chequiers = $SESSION_VARS['liste_chequiers'];
	debug($tous_commande_chequiers);
	$chequiers_print = array ();
	foreach ($tous_commande_chequiers as $id_comde_chequier => $comde_chequier) {
		$var = "check_" . $id_comde_chequier;
		if (isset ($$var)) {
			$chequiers_print[$id_comde_chequier] = $comde_chequier;
		}
	}
	debug($chequiers_print);
	if (count($chequiers_print) > 0) {
		// Nous avons des chèquiers à imprimer
		$result = csvChequiers($chequiers_print);
		$datacsv = $result->param;
		$result = setAttenteImpressionChequier(array_keys($chequiers_print));
		if ($result->errCode == NO_ERR) {
			$result = doWriteCSV($datacsv);
			if ($result->errCode == NO_ERR) {
				echo getShowCSVHTML("Gen-6", $result->param);
				ajout_historique(192, NULL, NULL, $global_nom_login, date("r"), NULL);
			}
		}
		if ($result->errCode != NO_ERR) {
			$my_page = new HTML_erreur(_("Echec lors de l'export des chèquiers"));
			$my_page->setMessage("Erreur : " . $error[$result->errCode] . "<br />Paramètre : " . $result->param);
			$my_page->addButton("BUTTON_OK", 'Gen-6');
			$my_page->show();
		}
	} else {
		// Aucun chèquier ne doit être imprimé
		$my_page = new HTML_message(_("Aucun chèquier"));
		$my_page->setMessage(_("Aucun chèquier n'a été choisi pour l'impression."), true);
		$my_page->addButton("BUTTON_OK", 'Gen-6');
		$my_page->show();
	}
}

else
if($global_nom_ecran == 'Ich-5'){
    $my_page = new HTML_GEN2( _("Choix de niveau agence"));
    $nivArrayFenacobu = getNivFenacobuArray("type_niveau > 1");
    // --> Sélection des champs à afficher dans id_loc
    reset($nivArrayFenacobu);
    $includeChoicesFenacobu = array();
    while (list (, $value_fenacobu) = each($nivArrayFenacobu)) {
        if ($value_fenacobu['type_niveau'] == 2)
            //array_push($includeChoicesFenacobu, $value_fenacobu['id'] =>$value_fenacobu['libelle_localisation']);
        $arrayDisplay[$value_fenacobu['id'] ] =$value_fenacobu['libelle_niveau'];

    }

    //$array_test = array(1=> 'test1', 2=> 'test2');print_rn($array_test);
    $my_page->addField("n_agence", _("Niveau agence"), TYPC_LSB);
    $my_page->addField("n_guichet", _("Niveau guichet"), TYPC_LSB);
    $my_page->setFieldProperties("n_agence", FIELDP_ADD_CHOICES, $arrayDisplay);
    $my_page->setFieldProperties("n_agence", FIELDP_HAS_CHOICE_TOUS, true);
    $my_page->setFieldProperties("n_agence", FIELDP_HAS_CHOICE_AUCUN, false);
    //$my_page->setFieldProperties("n_agence", FIELDP_IS_REQUIRED, true);

    $jsCodeNivFenacobu = "function displayNivFenacobu() {\n";
    $jsCodeNivFenacobu .= "for (i=0; i < document.ADForm.HTML_GEN_LSB_n_guichet.length; ++i) document.ADForm.HTML_GEN_LSB_n_guichet.options[i] = null;\n"; //Vide les choix
    $jsCodeNivFenacobu .= "document.ADForm.HTML_GEN_LSB_n_guichet.length = 0;";
    $jsCodeNivFenacobu .= "document.ADForm.HTML_GEN_LSB_n_guichet.options[document.ADForm.HTML_GEN_LSB_n_guichet.length] = new Option('[Tous]', 0, true, true);\n"; //[Aucun]
    $jsCodeNivFenacobu .= "document.ADForm.HTML_GEN_LSB_n_guichet.selectedIndex = 0; document.ADForm.HTML_GEN_LSB_n_guichet.length = 1; \n";
    reset($nivArrayFenacobu);
    while (list (, $value_fenacobu) = each($nivArrayFenacobu)) {
        if ($value_fenacobu['parent'] != '') {
            $jsCodeNivFenacobu .= "\tif (document.ADForm.HTML_GEN_LSB_n_agence.value == " . $value_fenacobu['parent'] . ")\n";
            $jsCodeNivFenacobu .= "\t\tdocument.ADForm.HTML_GEN_LSB_n_guichet.options[document.ADForm.HTML_GEN_LSB_n_guichet.length] = new Option('" . $value_fenacobu['libelle_niveau'] . "', '" . $value_fenacobu['id'] . "', false, false);\n";
        }
    }
    $jsCodeNivFenacobu .= "\n}";
    // --> Ajout de la fonction dans le formulaire
    $my_page->addJS(JSP_FORM, "jsCodeNivFenacobu", $jsCodeNivFenacobu);
    // --> ajout des champs

    //AT-150
    // --> Page reloaded Constrution des choix disponibles pour le district
    if (isset($n_agence) && isset($n_guichet)){
        $choices = array();
        reset($nivArrayFenacobu);
        while (list(, $value) = each($nivArrayFenacobu)) {
            if ($value['parent'] == $n_agence)
                $choices[$value['id']] = $value['libelle_niveau'];
        }
        $my_page->setFieldProperties("n_guichet", FIELDP_ADD_CHOICES, $choices);
        $my_page->setFieldProperties("n_guichet", FIELDP_DEFAULT, $n_guichet);
    }
    else{
//        $my_page->setFieldProperties("n_guichet", FIELDP_INCLUDE_CHOICES, array(
//            "0" => "[Tous]"
//        ));
        $my_page->setFieldProperties("n_guichet", FIELDP_HAS_CHOICE_TOUS, true);
        $my_page->setFieldProperties("n_guichet", FIELDP_HAS_CHOICE_AUCUN, false);
    }
    //$my_page->setFieldProperties("n_guichet", FIELDP_IS_REQUIRED, true);
    $my_page->setFieldProperties("n_agence", FIELDP_JS_EVENT, array("onchange" => "displayNivFenacobu()"));

    $my_page->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
    $my_page->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);

    $my_page->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Ich-3');
    $my_page->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-6');
    $my_page->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $my_page->show();
}



/*}}}*/
else signalErreur(__FILE__,__LINE__,__FUNCTION__);
?>