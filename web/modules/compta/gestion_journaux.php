<?Php
require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/misc/divers.php';

if ($global_nom_ecran == "Jou-1") {
	// Menu principal gestion des journaux
	
	unset ( $SESSION_VARS ['journal'] );
	$MyPage = new HTML_GEN2 ( _ ( "Gestion des journaux" ) );
	// Construction de la liste des journaux existant
	$MyPage->addTableRefField ( "journal", _ ( "Journaux" ), "ad_journaux" );
	$MyPage->setFieldProperties ( "journal", FIELDP_HAS_CHOICE_AUCUN, true );
	$MyPage->setFieldProperties ( "journal", FIELDP_IS_REQUIRED, true );
	$MyPage->setFieldProperties ( "journal", FIELDP_JS_EVENT, array (
			"onchange" => "activ_but();" 
	) );
	
	// Bouton consulter
	$MyPage->addButton ( "journal", "consult", _ ( "Consulter" ), TYPB_SUBMIT );
	$MyPage->setButtonProperties ( "consult", BUTP_AXS, 451 );
	$MyPage->setButtonProperties ( "consult", BUTP_PROCHAIN_ECRAN, "Jou-4" );
	
	// Bouton modifier
	$MyPage->addButton ( "journal", "modif", _ ( "Modifier" ), TYPB_SUBMIT );
	$MyPage->setButtonProperties ( "modif", BUTP_AXS, 452 );
	$MyPage->setButtonProperties ( "modif", BUTP_PROCHAIN_ECRAN, "Jou-3" );
	
	// Bouton supprimer
	$MyPage->addButton ( "journal", "supr", _ ( "Supprimer" ), TYPB_SUBMIT );
	$MyPage->setButtonProperties ( "supr", BUTP_AXS, 453 );
	$MyPage->setButtonProperties ( "supr", BUTP_PROCHAIN_ECRAN, "Jou-5" );
	
	// Bouton crÃ©er
	$MyPage->addFormButton ( 1, 1, "cree", _ ( "Créer un nouveau journal" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "cree", BUTP_AXS, 454 );
	$MyPage->setFormButtonProperties ( "cree", BUTP_PROCHAIN_ECRAN, "Jou-2" );
	$MyPage->setFormButtonProperties ( "cree", BUTP_CHECK_FORM, false );
	
	$MyPage->addFormButton ( 1, 2, "cree_ctre_ptie", _ ( "Contrepartie" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "cree_ctre_ptie", BUTP_PROCHAIN_ECRAN, "Jou-6" );
	$MyPage->setFormButtonProperties ( "cree_ctre_ptie", BUTP_CHECK_FORM, true );
	
	$MyPage->addFormButton ( 1, 3, "liaison", _ ( "Comptes de liaison" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "liaison", BUTP_PROCHAIN_ECRAN, "Jou-13" );
	$MyPage->setFormButtonProperties ( "liaison", BUTP_CHECK_FORM, true );
	
	// Bouton retour
	$MyPage->addFormButton ( 2, 1, "ret", _ ( "Retour" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "ret", BUTP_PROCHAIN_ECRAN, "Gen-14" );
	$MyPage->setFormButtonProperties ( "ret", BUTP_CHECK_FORM, false );
	
	$infos = getInfosJournal ();
	
	// Javascript pour dÃ©sactiver les boutons si le choix est "Aucun" si non les activÃ©s
	$js = "document.ADForm.consult.disabled = true; document.ADForm.modif.disabled = true; document.ADForm.supr.disabled = true;\n";
	$js .= "\nfunction activ_but()\n";
	$js .= "{\n";
	$js .= "jnl = new Array();\n";
	foreach ( $infos as $key => $value ) {
		$princ = $value ["num_cpte_princ"];
		$js .= "jnl[$key]='$princ';\n";
	}
	$js .= "if(jnl[document.ADForm.HTML_GEN_LSB_journal.value]!='')\n";
	$js .= "{\n";
	$js .= "\tactivate = document.ADForm.HTML_GEN_LSB_journal.value != 0;\n";
	$js .= "\tdocument.ADForm.consult.disabled=!activate;\n";
	$js .= "\tdocument.ADForm.modif.disabled=!activate;\n";
	$js .= "\tdocument.ADForm.supr.disabled=!activate;\n";
	$js .= "}";
	$js .= "else";
	$js .= "{document.ADForm.consult.disabled = true; document.ADForm.modif.disabled = true; document.ADForm.supr.disabled = true;}\n";
	$js .= "}\n";
	$MyPage->addJS ( JSP_FORM, "js", $js );
	
	$MyPage->buildHTML ();
	echo $MyPage->getHTML ();
} else if ($global_nom_ecran == "Jou-2") {
	
	/* ************* Gestion de l'écran d'ajout ********************** */
	
	$MyPage = new HTML_GEN2 ( _ ( "Ajout d'une entrée dans la table des journaux" ) );
	
	// Champs Ã  exclure
	$ary_exclude = array (
			"id_jou" 
	);
	
	// Affichage des champs
	$MyPage->addTable ( "ad_journaux", OPER_EXCLUDE, $ary_exclude );
	$MyPage->setFieldProperties ( "libel_jou", FIELDP_IS_REQUIRED, true );
	$MyPage->setFieldProperties ( "etat_jou", FIELDP_DEFAULT, "1" );
	$MyPage->setFieldProperties ( "etat_jou", FIELDP_IS_LABEL, true );
	$MyPage->setFieldProperties ( "code_jou", FIELDP_WIDTH, 3 );
	
	$ordre = array (
			"code_jou",
			"libel_jou",
			"num_cpte_princ",
			"etat_jou" 
	);
	$MyPage->setOrder ( NULL, $ordre );
	$MyPage->setFieldProperties ( "code_jou", FIELDP_JS_EVENT, array (
			"onChange" => "checkCodeJou();" 
	) );
	
	$codeJS = "function checkCodeJou(){ ";
	$codeJS .= "if(document.ADForm.code_jou.value.length>3){";
	$codeJS .= "alert('Le code journal doit avoir 3 caractères!');";
	$codeJS .= "document.ADForm.code_jou.value=\"\"";
	$codeJS .= "}";
	$codeJS .= "}";
	$MyPage->addJS ( JSP_FORM, "js", $codeJS );
	
	// Bouton
	$MyPage->addFormButton ( 1, 1, "butval", _ ( "Valider" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "butval", BUTP_PROCHAIN_ECRAN, "Jou-7" );
	$MyPage->addFormButton ( 1, 2, "butret", _ ( "Annuler" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "butret", BUTP_CHECK_FORM, false );
	$MyPage->setFormButtonProperties ( "butret", BUTP_PROCHAIN_ECRAN, "Jou-1" );
	
	// HTML
	$MyPage->buildHTML ();
	echo $MyPage->getHTML ();
} else if ($global_nom_ecran == "Jou-3") {
	/* ************** Gestion de l'écran de modification****************** */
	
	$MyPage = new HTML_GEN2 ( _ ( "Modification d'une entrée de la table des journaux" ) );
	
	$jou = getInfosjournal ( $journal );
	$SESSION_VARS ['info'] = $jou;
	
	$SESSION_VARS ['id_jou'] = $journal;
	
	// Affichage des champs modifiables
	$ary_exclude = array (
			"id_jou",
			"num_cpte_princ",
			"etat_jou" 
	);
	$MyPage->addTable ( "ad_journaux", OPER_EXCLUDE, $ary_exclude );
	$MyPage->setFieldProperties ( "libel_jou", FIELDP_IS_REQUIRED, true );
	$libel_jou = new Trad ( $jou [$journal] ["libel_jou"] );
	$MyPage->setFieldProperties ( "libel_jou", FIELDP_DEFAULT, $libel_jou );
	$MyPage->setFieldProperties ( "code_jou", FIELDP_IS_LABEL, true );
	$MyPage->setFieldProperties ( "code_jou", FIELDP_WIDTH, 3 );
	$MyPage->setFieldProperties ( "code_jou", FIELDP_DEFAULT, $jou [$journal] ["code_jou"] );
	
	$ordre = array (
			"code_jou",
			"libel_jou" 
	);
	$MyPage->setOrder ( NULL, $ordre );
	
	// Bouton
	$MyPage->addFormButton ( 1, 1, "butval", _ ( "Valider" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "butval", BUTP_PROCHAIN_ECRAN, "Jou-8" );
	$MyPage->addFormButton ( 1, 2, "butret", _ ( "Annuler" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "butret", BUTP_CHECK_FORM, false );
	$MyPage->setFormButtonProperties ( "butret", BUTP_PROCHAIN_ECRAN, 'Jou-1' );
	
	// HTML
	$MyPage->buildHTML ();
	echo $MyPage->getHTML ();
}
if ($global_nom_ecran == "Jou-4") {
	/* *************** Gestion de l'écran de consultation ****** */
	
	global $global_id_agence;
	ajout_historique ( 293, NULL, "ad_journaux", $global_nom_login, date ( "r" ), NULL );
	
	$MyPage = new HTML_GEN2 ( _ ( "Consultation d'une entrée de la table des journaux" ) );
	
	$cptes = getComptesComptables ();
	$info = getInfosJournal ();
	
	// Code journal
	$MyPage->addField ( "code", _ ( "Code" ), TYPC_TXT );
	$MyPage->setFieldProperties ( "code", FIELDP_DEFAULT, $info [$journal] ["code_jou"] );
	$MyPage->setFieldProperties ( "code", FIELDP_IS_LABEL, true );
	
	// libellé du Journal
	$MyPage->addField ( "libel", _ ( "Journal" ), TYPC_TTR );
	$libel_jou = new Trad ( $info [$journal] ["libel_jou"] );
	$MyPage->setFieldProperties ( "libel", FIELDP_DEFAULT, $libel_jou );
	$MyPage->setFieldProperties ( "libel", FIELDP_IS_LABEL, true );
	
	// Compte principal
	if ($info [$journal] ["num_cpte_princ"]) {
		$param = array ();
		$param ["num_cpte_comptable"] = $info [$journal] ["num_cpte_princ"];
		$cpte = getComptesComptables ( $param );
		$libel = $info [$journal] ["num_cpte_princ"] . "  " . $cpte [$info [$journal] ["num_cpte_princ"]] ["libel_cpte_comptable"];
		$MyPage->addField ( "princ", _ ( "Compte principal" ), TYPC_TXT );
		$MyPage->setFieldProperties ( "princ", FIELDP_DEFAULT, $libel );
		$MyPage->setFieldProperties ( "princ", FIELDP_IS_LABEL, true );
	}
	
	// Affichage des comptes de liaison
	$xtHTML1 = "<h4 align=\"center\"> " . _ ( "Comptes de liaison" ) . " </h4>";
	$xtHTML1 .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
	$xtHTML1 .= "\n<tr bgcolor=\"$colb_tableau\" align=\"center\">";
	$xtHTML1 .= "<td><b>" . _ ( "N°" ) . "</b></td>";
	$xtHTML1 .= "<td><b>" . _ ( "Journal" ) . "</b></td>";
	$xtHTML1 .= "<td><b>" . _ ( "Compte de liaison" ) . "</b></td>";
	$xtHTML1 .= "</tr>";
	
	$i = 1;
	// Rrecherche de tous les comptes de liaison
	$jouliaison = getJournauxLiaison ();
	
	// Récupération des comptes de liaison du journal
	if (isset ( $jouliaison ))
		foreach ( $jouliaison as $key => $liaison )
			if ($liaison ["id_jou1"] == $journal || $liaison ["id_jou2"] == $journal) {
				// Alterner la couleur du tableau
				if ($i % 2)
					$color = $colb_tableau;
				else
					$color = $colb_tableau_altern;
				
				$xtHTML1 .= "\n<tr bgcolor=\"$color\" align=\"center\">";
				$xtHTML1 .= "<td>" . $i . "</td>";
				if ($liaison ["id_jou1"] == $journal)
					$xtHTML1 .= "<td>" . $info [$liaison ["id_jou2"]] ["libel_jou"] . "</td>";
				else
					$xtHTML1 .= "<td>" . $info [$liaison ["id_jou1"]] ["libel_jou"] . "</td>";
				$xtHTML1 .= "<td>" . $liaison ["num_cpte_comptable"] . " " . $cptes [$liaison ["num_cpte_comptable"]] ["libel_cpte_comptable"] . "</td>";
				$xtHTML1 .= "</tr>";
				$i ++;
			}
	
	$xtHTML1 .= "</table>";
	
	// Affichage de la contrepartie
	$cptie = getInfosJournalCptie ( $journal );
	if (isset ( $cptie )) {
		$xtHTML1 .= "<h4 align=\"center\"> " . _ ( "Contrepartie" ) . " </h4>";
		$xtHTML1 .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
		$xtHTML1 .= "\n<tr bgcolor=\"$colb_tableau\" align=\"center\">";
		$xtHTML1 .= "<td><b>" . _ ( "N°" ) . "</b></td>";
		$xtHTML1 .= "<td><b>" . _ ( "Compte contrepartie" ) . "</b></td>";
		$xtHTML1 .= "<td><b>" . _ ( "Libelle compte comptable" ) . "</b></td>";
		$xtHTML1 .= "</tr>";
		
		$i = 1;
		foreach ( $cptie as $row ) {
			$num_compte = $row ["num_cpte_comptable"];
			if ($i % 2)
				$color = $colb_tableau;
			else
				$color = $colb_tableau_altern;
			
			$xtHTML1 .= "\n<tr bgcolor=\"$color\">";
			$xtHTML1 .= "<td>" . $i . "</td>";
			$xtHTML1 .= "<td>" . $num_compte . "</td>";
			$xtHTML1 .= "<td>" . $cptes [$num_compte] ["libel_cpte_comptable"] . "</td>";
			$xtHTML1 .= "</tr>";
			$SESSION_VARS ["contrepartie"] ["$num_compte"] = $cptes [$num_compte] ["libel_cpte_comptable"];
			$i ++;
		}
		
		$xtHTML1 .= "</table>";
	}
	
	$MyPage->addFormButton ( 1, 1, "butret", _ ( "Retour" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "butret", BUTP_PROCHAIN_ECRAN, "Jou-1" );
	
	$MyPage->addHTMLExtraCode ( "xtHTML1", $xtHTML1 );
	$MyPage->buildHTML ();
	echo $MyPage->getHTML ();
} else if ($global_nom_ecran == 'Jou-5') {
	// ********************* Gestion écran de suppression *****************************************
	global $adsys;
	
	$SESSION_VARS ["journal"] = $journal;
	
	// Afficher les infos
	$MyPage = new HTML_GEN2 ( _ ( "Confirmation Suppression" ) );
	
	$jou = getInfosJournal ( $journal );
	$etat = $jou [$journal] ["etat_jou"];
	
	// Champs Ã  exclure
	$ary_exclude = array (
			"id_jou" 
	);
	
	$MyPage->addTable ( "ad_journaux", OPER_EXCLUDE, $ary_exclude );
	$MyPage->setFieldProperties ( "libel_jou", FIELDP_IS_LABEL, true );
	$libel_jou = new Trad ( $jou [$journal] ["libel_jou"] );
	$MyPage->setFieldProperties ( "libel_jou", FIELDP_DEFAULT, $libel_jou );
	$MyPage->setFieldProperties ( "num_cpte_princ", FIELDP_IS_LABEL, true );
	$MyPage->setFieldProperties ( "num_cpte_princ", FIELDP_DEFAULT, $jou [$journal] ["num_cpte_princ"] );
	$MyPage->setFieldProperties ( "etat_jou", FIELDP_IS_LABEL, true );
	$MyPage->setFieldProperties ( "etat_jou", FIELDP_DEFAULT, $etat );
	$MyPage->setFieldProperties ( "code_jou", FIELDP_IS_LABEL, true );
	$MyPage->setFieldProperties ( "code_jou", FIELDP_DEFAULT, $jou [$journal] ["code_jou"] );
	
	$ordre = array (
			"code_jou",
			"libel_jou",
			"num_cpte_princ",
			"etat_jou" 
	);
	$MyPage->setOrder ( $NULL, $ordre );
	
	// Bouton
	$MyPage->addFormButton ( 1, 1, "sup", _ ( "Supprimer" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "sup", BUTP_PROCHAIN_ECRAN, "Jou-9" );
	
	$MyPage->addFormButton ( 1, 2, "annul", _ ( "Annuler" ), TYPB_SUBMIT );
	$MyPage->setFormButtonProperties ( "annul", BUTP_PROCHAIN_ECRAN, "Jou-1" );
	$MyPage->buildHTML ();
	echo $MyPage->getHTML ();
} else if ($global_nom_ecran == "Jou-6") {
	// Gestion de la contrepartie
	global $global_id_agence;
	
	if (! isset ( $SESSION_VARS ["journal"] )) // si on vient de l'ecran Jou-1
		$SESSION_VARS ["journal"] = $journal;
	
	$info = getInfosJournal ( $SESSION_VARS ["journal"] );
	
	// si c'est pas un journal auxiliaire
	if ($info [$SESSION_VARS ["journal"]] ['num_cpte_princ'] == NULL) {
		$html_err = new HTML_erreur ( _ ( "Echec gestion comptes de contrepartie." ) );
		$html_err->setMessage ( " " . _ ( "Veuillez choisir un journal auxiliaire." ) );
		$html_err->addButton ( "BUTTON_OK", "Jou-1" );
		$html_err->buildHTML ();
		echo $html_err->HTML_code;
	} else {
		$MyPage = new HTML_GEN2 ( _ ( "Gestion des comptes de contrepartie" ) );
		
		// Le code du journal
		$MyPage->addField ( "code_jou", _ ( "Code" ), TYPC_TXT );
		$MyPage->setFieldProperties ( "code_jou", FIELDP_IS_LABEL, true );
		$MyPage->setFieldProperties ( "code_jou", FIELDP_DEFAULT, $info [$SESSION_VARS ["journal"]] ["code_jou"] );
		
		// Le libellé du journal
		$MyPage->addField ( "libel_jou", _ ( "Libellé" ), TYPC_TTR );
		$MyPage->setFieldProperties ( "libel_jou", FIELDP_IS_LABEL, true );
		$libel_jou = new Trad ( $info [$SESSION_VARS ["journal"]] ["libel_jou"] );
		$MyPage->setFieldProperties ( "libel_jou", FIELDP_DEFAULT, $libel_jou );
		
		// initialise la contrepartie
		unset ( $SESSION_VARS ["contrepartie"], $SESSION_VARS ["nbcptie"] );
		
		// Recupération de tous les comptes comptables
		$cptes = getComptesComptables ();
		
		//Recupération de tous les comptes comptables associe au credits
		$credits_arr = getComptesAssocieAuxCredits();
	    // this array will contain the accounts and their main accounts to be blocked
		$credits_Centralisateur_arr = array();	
		 	 
		 // pour ramener et combine les comptes centralisateur avec leur compte associé au credits
		 foreach($credits_arr as $key => $value ){
		 	
		 	$credits_cent_arr = getComptesCentralisateurs($key) ;
		    $credits_cent_arr[] = $key ;
		   
		 	
		    for($x = 0; $x < count($credits_cent_arr) ; $x++) {
		    		$credits_Centralisateur_arr[] = $credits_cent_arr[$x];
		    }	
		 }

		 
		// recupérations des comptes de contrepartie du journal
		$cptectie = array ();
		
		$cptie = getInfosJournalCptie ( $SESSION_VARS ["journal"] );
		
		// Affichage de la contrepartie
		$xtHTML1 = "<h4 align=\"center\"> " . _ ( "Liste des comptes de contrepartie" ) . " </h4>";
		$xtHTML1 .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
		$xtHTML1 .= "\n<tr bgcolor=\"$colb_tableau\" align=\"center\">";
		$xtHTML1 .= "<td><b>&nbsp</b></td>";
		$xtHTML1 .= "<td><b>" . _ ( "N compte comptable" ) . "</b></td>";
		$xtHTML1 .= "<td><b>" . _ ( "Libelle compte comptable" ) . "</b></td>";
		$xtHTML1 .= "</tr>";
		
		$i = 1;
		foreach ( $cptie as $row ) {
			$num_compte = $row ["num_cpte_comptable"];
			if ($i % 2)
				$color = $colb_tableau;
			else
				$color = $colb_tableau_altern;
			
			array_push ( $cptectie, $num_compte );
			$xtHTML1 .= "\n<tr bgcolor=\"$color\" align=\"center\">";
			$xtHTML1 .= "<td><input type=\"checkbox\" name=\"sup" . $i . "\"></td>";
			$xtHTML1 .= "<td>" . $num_compte . "</td>";
			$xtHTML1 .= "<td>" . $cptes [$num_compte] ["libel_cpte_comptable"] . "</td>";
			$xtHTML1 .= "</tr>";
			$SESSION_VARS ["contrepartie"] ["$num_compte"] = $cptes [$num_compte] ["libel_cpte_comptable"];
			$i ++;
		}
		
		$SESSION_VARS ["nbcptie"] = $i;
		// Bouton sup
		$color = $colb_tableau;
		$xtHTML1 .= "\n<tr bgcolor=\"$color\" align=\"center\">";
		$xtHTML1 .= "<td><TABLE><TR>";
		$xtHTML1 .= "<TD><input type=\"submit\" value=\"Supprimer\" onclick=\"document.ADForm.m_agc.value='" . $_REQUEST ['m_agc'] . "';document.ADForm.prochain_ecran.value='Jou-11';\"></TD>";
		$xtHTML1 .= "</TR></TABLE></td>";
		$xtHTML1 .= "<td>&nbsp</td>";
		$xtHTML1 .= "<td>&nbsp</td>";
		$xtHTML1 .= "</tr>";
		$xtHTML1 .= "</table>";
		
		// Construction de la liste des comptes comptables pour ajout
		reset ( $cptie );
		$xtHTML1 .= "<h4 align=\"center\"> " . _ ( "Ajout comptes de contrepartie" ) . "</h4>";
		$xtHTML1 .= "<TABLE align=\"center\" bgcolor=$colb_tableau  ";
		$xtHTML1 .= "cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
		$xtHTML1 .= "<tr><td>";
		$xtHTML1 .= "<select NAME=\"HTML_GEN_LSB_compte\">\n";
		$xtHTML1 .= "<option value=\"0\">[" . _ ( "Aucun" ) . "]</option>\n";
		
		
		//foreach ( $cptes as $key => $value ) {
		foreach ( $cptes as $key => $value ) {
			/*
			 * exclure de la contrepartie: - les comptes principaux - les comptes qui font déjà partie de la contrepartie - les comptes centralisateurs
			 */
			if (($value ["cpte_princ_jou"] != 't') && (! in_array ( $key, $cptectie )) &&(! in_array ( $key, $credits_Centralisateur_arr ))){
				
	
				// && !isCentralisateur($key)
				$xtHTML1 .= "<option value=$key>" . $key . " " . $value ["libel_cpte_comptable"] . "</option>\n";
				}
				
		}
		
		$xtHTML1 .= "</select>\n";
		$xtHTML1 .= "</td>";
		$xtHTML1 .= "<td><INPUT TYPE=\"submit\" VALUE=\"Ajouter\" onclick=\"assign('Jou-10');checkform();\"></td></tr>";
		$xtHTML1 .= "</TABLE>";
		
		$js = "function checkform() {";
		$js .= "ADFormValid = true; msg='';";
		$js .= "if (document.ADForm.HTML_GEN_LSB_compte.value==0)\n";
		
		$js .= "{ msg+=' - " . _ ( "le compte de contrepartie doit être renseigné." ) . "\\n';ADFormValid=false;}\n";
		$js .= "if (msg != '') alert(msg);\n";
		$js .= "\n}\n";
		$MyPage->addJS ( JSP_FORM, "js", $js );
		
		$MyPage->addFormButton ( 1, 1, "retour", _ ( "Retour" ), TYPB_SUBMIT );
		$MyPage->setFormButtonProperties ( "retour", BUTP_CHECK_FORM, false );
		$MyPage->setFormButtonProperties ( "retour", BUTP_PROCHAIN_ECRAN, 'Jou-1' );
		
		$MyPage->addHTMLExtraCode ( "xtHTML1", $xtHTML1 );
		$MyPage->buildHTML ();
		echo $MyPage->getHTML ();
	}
} else if ($global_nom_ecran == "Jou-7") {
	// Confirmation ajout d'un journal
	
	if ($num_cpte_princ)
		$DATA ["num_cpte_princ"] = $num_cpte_princ;
	else
		$DATA ["num_cpte_princ"] = '';
	
	$DATA ["libel_jou"] = $libel_jou;
	$DATA ["code_jou"] = $code_jou;
	$DATA ["etat_jou"] = 1;

	
	$myErr = ajoutJournal ( $DATA );
	
	if ($myErr->errCode != NO_ERR) {
		$html_err = new HTML_erreur ( _ ( "Echec création journal. " ) );
		$html_err->setMessage ( _ ( "Erreur" ) . " : " . $error [$myErr->errCode] . $myErr->param );
		$html_err->addButton ( "BUTTON_OK", 'Jou-2' );
		$html_err->buildHTML ();
		echo $html_err->HTML_code;
	} else {
		$MyPage = new HTML_message ( _ ( "Confirmation ajout" ) );
		$MyPage->setMessage ( _ ( "Le journal a été ajouté avec succès." ) );
		$MyPage->addButton ( BUTTON_OK, "Jou-1" );
		$MyPage->buildHTML ();
		echo $MyPage->HTML_code;
	}
} else if ($global_nom_ecran == "Jou-8") {
	/* ************ Confirmation modification**************** */
	
	reset ( $SESSION_VARS ['info'] );
	
	$id_jou = $SESSION_VARS ['id_jou'];
	$DATA ["id_jou"] = $id_jou;
	$DATA ["libel_jou"] = $libel_jou;
	$DATA ["num_cpte_princ"] = $SESSION_VARS ['info'] [$id_jou] ["num_cpte_princ"];
	$DATA ["etat_jou"] = $SESSION_VARS ['info'] [$id_jou] ["etat_jou"];
	
	$result = modifJournal ( $DATA );
	
	// HTML
	$MyPage = new HTML_message ( _ ( "Confirmation modification" ) );
	if ($result)
		$MyPage->setMessage ( _ ( "Le journal a été modifié avec succès." ) );
	else
		$MyPage->setMessage ( _ ( "La modification n'a pas été effectuée avec succès !" ) );
	
	$MyPage->addButton ( BUTTON_OK, "Jou-1" );
	$MyPage->buildHTML ();
	echo $MyPage->HTML_code;
} else if ($global_nom_ecran == 'Jou-9') {
	/* ******************* Confirmation suppression ********* */
	
	$myErr = supJournal ( $SESSION_VARS ["journal"] );
	
	if ($myErr->errCode != NO_ERR) {
		$html_err = new HTML_erreur ( _ ( "Echec suppression journal. " ) );
		$html_err->setMessage ( _ ( "Erreur" ) . " : " . $error [$myErr->errCode] . $myErr->param );
		$html_err->addButton ( "BUTTON_OK", 'Jou-1' );
		$html_err->buildHTML ();
		echo $html_err->HTML_code;
	} else {
		$MyPage = new HTML_message ( _ ( "Confirmation suppression" ) );
		$MyPage->setMessage ( _ ( "Le journal a été supprimé avec succès." ) );
		$MyPage->addButton ( BUTTON_OK, 'Jou-1' );
		$MyPage->buildHTML ();
		echo $MyPage->HTML_code;
	}
} else if ($global_nom_ecran == "Jou-10") {
	if ($compte == 0) {
		$html_err = new HTML_erreur ( _ ( "Echec gestion comptes de contrepartie." ) );
		$html_err->setMessage ( " " . _ ( "Il faut choisir un compte !" ) . " " );
		$html_err->addButton ( "BUTTON_OK", "Jou-6" );
		$html_err->buildHTML ();
		echo $html_err->HTML_code;
	} else {
		
		// confirmation ajout compte contre partie
		
		$myErr = ajoutJournalCptie ( $SESSION_VARS ["journal"], $compte );
		
		if ($myErr->errCode != NO_ERR) {
			$html_err = new HTML_erreur ( _ ( "Echec création journal." ) . " " );
			$html_err->setMessage ( _ ( "Erreur" ) . " : " . $error [$myErr->errCode] . $myErr->param );
			$html_err->addButton ( "BUTTON_OK", 'Jou-6' );
			$html_err->buildHTML ();
			echo $html_err->HTML_code;
		} else {
			$MyPage = new HTML_message ( _ ( "Confirmation ajout contrepartie" ) );
			$MyPage->setMessage ( _ ( "L'entrée de la table a été ajoutée avec succès!" ) );
			$MyPage->addButton ( BUTTON_OK, "Jou-6" );
			
			$MyPage->buildHTML ();
			echo $MyPage->HTML_code;
		}
	}
} else if ($global_nom_ecran == "Jou-11") {
	// Demande de confirmation suppression contre partie
	
	$sup = false;
	for($i = 1; $i <= $SESSION_VARS ["nbcptie"]; $i ++) {
		$a = "sup" . $i;
		if (isset ( $$a ))
			$sup = true;
	}
	
	if (! $sup) {
		$html_msg = new HTML_message ( _ ( "Gestion des comptes de contrepartie" ) );
		$html_msg->setMessage ( _ ( "Aucun compte de contre partie selectionne" ) );
		$html_msg->addButton ( "BUTTON_OK", 'Jou-6' );
		$html_msg->buildHTML ();
		echo $html_msg->HTML_code;
	} else {
		unset ( $SESSION_VARS ["sup"] );
		$MyPage = new HTML_GEN2 ( _ ( "Demande de confirmation suppression" ) );
		
		$xtHTML1 = "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";
		// $xtHTML1 = "<TABLE width=\"95%\">";
		
		$xtHTML1 .= "\n<tr bgcolor=\"$colb_tableau\" align=\"center\">";
		$xtHTML1 .= "<td><b>" . _ ( "N compte comptable" ) . "</b></td>";
		$xtHTML1 .= "<td><b>" . _ ( "Libelle compte comptable" ) . "</b></td>";
		$xtHTML1 .= "</tr>";
		
		$i = 1;
		reset ( $SESSION_VARS ["contrepartie"] );
		foreach ( $SESSION_VARS ["contrepartie"] as $key => $value ) {
			if ($i % 2)
				$color = $colb_tableau;
			else
				$color = $colb_tableau_altern;
			$a = "sup" . $i;
			if (isset ( $$a )) {
				$xtHTML1 .= "\n<tr bgcolor=\"$color\" align=\"center\">";
				$xtHTML1 .= "<td>" . $key . "</td>";
				$xtHTML1 .= "<td>" . $value . "</td>";
				$xtHTML1 .= "</tr>";
				$SESSION_VARS ["sup"] ["$key"] = $key;
			}
			$i ++;
		}
		
		$xtHTML1 .= "</table>";
		
		$MyPage->addFormButton ( 1, 1, "supprimer", _ ( "Supprimer" ), TYPB_SUBMIT );
		$MyPage->setFormButtonProperties ( "supprimer", BUTP_PROCHAIN_ECRAN, "Jou-12" );
		
		$MyPage->addFormButton ( 1, 2, "retour", _ ( "Retour" ), TYPB_SUBMIT );
		$MyPage->setFormButtonProperties ( "retour", BUTP_CHECK_FORM, false );
		$MyPage->setFormButtonProperties ( "retour", BUTP_PROCHAIN_ECRAN, 'Jou-6' );
		
		$MyPage->addHTMLExtraCode ( "xtHTML1", $xtHTML1 );
		$MyPage->buildHTML ();
		echo $MyPage->getHTML ();
	}
} 

else if ($global_nom_ecran == "Jou-12") {
	// confirmation supprtession compte contre partie
	
	reset ( $SESSION_VARS ["sup"] );
	foreach ( $SESSION_VARS ["sup"] as $key => $value ) {
		supJournalCptie ( $SESSION_VARS ["journal"], $key );
	}
	
	$MyPage = new HTML_message ( _ ( "Confirmation suppression" ) );
	$MyPage->setMessage ( _ ( "L'entrée dans la table des contreparties a été supprimée avec succes !" ) );
	$MyPage->addButton ( BUTTON_OK, "Jou-6" );
	
	$MyPage->buildHTML ();
	echo $MyPage->HTML_code;
} else if ($global_nom_ecran == "Jou-13") {
	/* ************* l'écran de Gestion des comptes de liaison ********************** */
	
	global $global_id_agence;
	$SESSION_VARS ["jou1"] = $journal;
	$info = getInfosJournal ();
	
	// si c'est pas un journal auxiliaire
	if ($info [$journal] ['num_cpte_princ'] == NULL) {
		$html_err = new HTML_erreur ( _ ( "Echec gestion comptes de liaison." ) );
		$html_err->setMessage ( " " . _ ( "Veuillez choisir un journal auxiliaire." ) );
		$html_err->addButton ( "BUTTON_OK", "Jou-1" );
		$html_err->buildHTML ();
		echo $html_err->HTML_code;
	} else {
		$MyPage = new HTML_GEN2 ( _ ( "Gestion des comptes de liaison" ) );
		
		// le journal choisi
		$MyPage->addField ( "jou1", _ ( "Premier Journal" ), TYPC_TXT );
		$MyPage->setFieldProperties ( "jou1", FIELDP_IS_REQUIRED, true );
		$MyPage->setFieldProperties ( "jou1", FIELDP_DEFAULT, $info [$journal] ["code_jou"] . " " . $info [$journal] ["libel_jou"] );
		$MyPage->setFieldProperties ( "jou1", FIELDP_IS_LABEL, true );
		
		// Liste des autres journaux auxiliaires ( choix du deuxième journal)
		$choices = array ();
		if (isset ( $info ))
			foreach ( $info as $key => $value )
				if (($value ['id_jou'] != $journal) && ($value ['num_cpte_princ'] != NULL))
					$choices [$key] = $value ["code_jou"] . " " . $value ["libel_jou"];
		
		$MyPage->addField ( "jou2", _ ( "Deuxième Journal" ), TYPC_LSB );
		$MyPage->setFieldProperties ( "jou2", FIELDP_ADD_CHOICES, $choices );
		$MyPage->setFieldProperties ( "jou2", FIELDP_HAS_CHOICE_AUCUN, true );
		$MyPage->setFieldProperties ( "jou2", FIELDP_IS_REQUIRED, true );
		$MyPage->setFieldProperties ( "jou2", FIELDP_JS_EVENT, array (
				"onchange" => "activ_but();" 
		) );
		
		// Liste des comptes comptables non principaux et non centralisateurs ( compte de liaison)
		$param = array ();
		$param ["cpte_princ_jou"] = 'f';
		$cptes = getComptesComptables ( $param );
		$choices = array ();
		if (isset ( $cptes ))
			foreach ( $cptes as $key => $value )
				if (! isCentralisateur ( $key ))
					$choices [$key] = $key . "  " . $value ["libel_cpte_comptable"];
		
		$MyPage->addField ( "compte", _ ( "Compte de liaison" ), TYPC_LSB );
		$MyPage->setFieldProperties ( "compte", FIELDP_ADD_CHOICES, $choices );
		$MyPage->setFieldProperties ( "compte", FIELDP_HAS_CHOICE_AUCUN, true );
		$MyPage->setFieldProperties ( "compte", FIELDP_IS_REQUIRED, true );
		
		// Bouton
		$MyPage->addFormButton ( 1, 1, "ajout", _ ( "Ajouter" ), TYPB_SUBMIT );
		$MyPage->setFormButtonProperties ( "ajout", BUTP_CHECK_FORM, true );
		$MyPage->setFormButtonProperties ( "ajout", BUTP_PROCHAIN_ECRAN, "Jou-14" );
		
		$MyPage->addFormButton ( 1, 2, "modif", _ ( "Modifier" ), TYPB_SUBMIT );
		$MyPage->setFormButtonProperties ( "modif", BUTP_CHECK_FORM, true );
		$MyPage->setFormButtonProperties ( "modif", BUTP_PROCHAIN_ECRAN, "Jou-15" );
		
		$MyPage->addFormButton ( 1, 3, "sup", _ ( "Supprimer" ), TYPB_SUBMIT );
		$MyPage->setFormButtonProperties ( "sup", BUTP_CHECK_FORM, false );
		$MyPage->setFormButtonProperties ( "sup", BUTP_PROCHAIN_ECRAN, "Jou-16" );
		
		$MyPage->addFormButton ( 1, 4, "ret", _ ( "Retour" ), TYPB_SUBMIT );
		$MyPage->setFormButtonProperties ( "ret", BUTP_CHECK_FORM, false );
		$MyPage->setFormButtonProperties ( "ret", BUTP_PROCHAIN_ECRAN, "Jou-1" );
		
		$jou1 = $SESSION_VARS ["jou1"];
		
		// Les comptes de liaison et leurs journaux associés ( array(jou1,jou2,compte))
		$jouliaison = getJournauxLiaison ();
		
		// JavaScript
		$js = "document.ADForm.ajout.disabled = true; document.ADForm.modif.disabled = true; document.ADForm.sup.disabled = true;\n";
		$js .= "\nfunction activ_but()\n";
		$js .= "{\n";
		$js .= "\tactivate =0;var ind=1;\n";
		
		// Tableau javascript pour les comptes comptables
		$js .= "indices = new Array();\n";
		if (isset ( $cptes ))
			foreach ( $cptes as $key => $value )
				if (! isCentralisateur ( $key )) {
					$js .= "indices['$key']=ind;\n";
					$js .= "ind=ind+1;\n";
				}
		
		reset ( $cptes );
		if (isset ( $jouliaison ))
			foreach ( $jouliaison as $key => $value ) {
				$idjou1 = $value ["id_jou1"]; // le premier journal
				$idjou2 = $value ["id_jou2"]; // le second journal
				$cpte_liaison = $value ["num_cpte_comptable"]; // le compte de liaison
				$lib = $cptes [$cpte_liaison] ["libel_cpte_comptable"]; // le libellé du compte de liaison
				
				$js .= "if( ($jou1==$idjou1 || $jou1==$idjou2) && (document.ADForm.HTML_GEN_LSB_jou2.value==$idjou1 || document.ADForm.HTML_GEN_LSB_jou2.value==$idjou2))\n";
				$js .= "{\tactivate =1;\n";
				$js .= "ind=indices['$cpte_liaison'];";
				$js .= "document.ADForm.HTML_GEN_LSB_compte.selectedIndex=ind;";
				$js .= "\n}\n";
			}
		
		$js .= "if(!activate)\n";
		$js .= "document.ADForm.HTML_GEN_LSB_compte.selectedIndex=0;";
		
		$js .= "\tdocument.ADForm.ajout.disabled=activate;\n";
		$js .= "\tdocument.ADForm.modif.disabled=!activate;\n";
		$js .= "\tdocument.ADForm.sup.disabled=!activate;\n";
		$js .= "}\n";
		
		// HTML
		$MyPage->addJS ( JSP_FORM, "js", $js );
		$MyPage->buildHTML ();
		echo $MyPage->getHTML ();
	}
} 

else if ($global_nom_ecran == "Jou-14") {
	/* ************* l'écran d'ajout des comptes de liaison ********************** */
	
	// Le premier journal
	$DATA ["id_jou1"] = $SESSION_VARS ["jou1"];
	
	// Le second journal
	$DATA ["id_jou2"] = $jou2;
	
	// le compte de liaison
	$DATA ["num_cpte_comptable"] = $compte;
	
	// Ajout dans la base de données
	$myErr = ajoutJournauxLiaison ( $DATA );
	
	if ($myErr->errCode != NO_ERR) {
		$html_err = new HTML_erreur ( _ ( "Echec ajout d'un compte de liaison." ) . " " );
		$html_err->setMessage ( _ ( "Erreur" ) . " : " . $error [$myErr->errCode] . $myErr->param );
		$html_err->addButton ( "BUTTON_OK", 'Jou-1' );
		
		$html_err->buildHTML ();
		echo $html_err->HTML_code;
	} else {
		$MyPage = new HTML_message ( _ ( "Confirmation ajout compte de liaison" ) );
		$MyPage->setMessage ( _ ( "L'entrée dans la table a été bien ajoutée !" ) );
		$MyPage->addButton ( BUTTON_OK, "Jou-1" );
		
		$MyPage->buildHTML ();
		echo $MyPage->HTML_code;
	}
} else if ($global_nom_ecran == "Jou-15") {
	/**
	 * ************* l'écran de modification des comptes de liaison **********************
	 */
	
	// Le premier journal
	$DATA ["id_jou1"] = $SESSION_VARS ["jou1"];
	
	// Le second journal
	$DATA ["id_jou2"] = $jou2;
	
	// Le compte de liaison
	$DATA ["num_cpte_comptable"] = $compte;
	
	// la modification dans la base
	$result = modifJournauxLiaison ( $DATA );
	
	if ($result) {
		$MyPage = new HTML_message ( _ ( "Confirmation modification compte de liaison" ) );
		$MyPage->setMessage ( _ ( "L'entrée a bien été modifiée !" ) );
		$MyPage->addButton ( BUTTON_OK, 'Jou-1' );
		$MyPage->buildHTML ();
		echo $MyPage->HTML_code;
	} else {
		$html_err = new HTML_erreur ( _ ( "Echec modification compte de liaison" ) );
		$html_err->setMessage ( _ ( "L'opération n'a pas été effectuée !....." ) . " " );
		$html_err->addButton ( "BUTTON_OK", 'Jou-1' );
		$html_err->buildHTML ();
		echo $html_err->HTML_code;
	}
} else if ($global_nom_ecran == "Jou-16") {
	/* ************* l'écran de suppression des comptes de liaison ********************** */
	
	// Le premier journal
	$DATA ["id_jou1"] = $SESSION_VARS ["jou1"];
	
	// le second journal
	$DATA ["id_jou2"] = $jou2;
	
	// e compte de liaison
	$DATA ["num_cpte_comptable"] = $compte;
	
	// Suppression dans la base
	$result = supJournauxLiaison ( $DATA );
	
	if ($result) {
		$MyPage = new HTML_message ( _ ( "Confirmation suppression compte de liaison" ) );
		$MyPage->setMessage ( _ ( "L'entrée a bien été supprimée !" ) );
		$MyPage->addButton ( BUTTON_OK, 'Jou-1' );
		$MyPage->buildHTML ();
		echo $MyPage->HTML_code;
	} else {
		$html_err = new HTML_erreur ( _ ( "Echec suppression compte de liaison" ) );
		$html_err->setMessage ( _ ( "L'opération n'a pas été effectuée !....." ) . " " );
		$html_err->addButton ( "BUTTON_OK", 'Jou-1' );
		$html_err->buildHTML ();
		echo $html_err->HTML_code;
	}
}
?>