<?php

/*
 * Ecran de mise en place demande de parts sociales
 * TF - 07/01/2015
 */
require_once ('lib/dbProcedures/client.php');
require_once ('lib/dbProcedures/compte.php');
require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/historique.php');
require_once ('lib/dbProcedures/agence.php');
require_once ('lib/html/HTML_GEN2.php');
require_once ('lib/html/FILL_HTML_GEN2.php');
require_once ('modules/epargne/recu.php');
require_once ('lib/dbProcedures/epargne.php');


if ($global_nom_ecran == "Dps-1") {
	global $global_id_client;
	global $global_id_agence;
	global $global_nom_utilisateur;
	
	unset($SESSION_VARS ['transfert_ps']);
	
	$AGC = getAgenceDatas ( $global_id_agence );
	$id_cpt = getBaseAccountID ( $global_id_client );
	$CPT = getAccountDatas ( $id_cpt );
	// Etat du client
	$etat = $global_etat_client;
	if ($etat == 1) {
		$erreur = new HTML_erreur ( _ ( "Souscription de parts sociales non-autorisée" ) );
		$erreur->setMessage ( _ ( "Ce client doit d'abord acquitter les frais d'adhésion avant de pouvoir souscrire des parts sociales." ) );
		$erreur->addButton ( BUTTON_OK, "Gen-9" );
		$erreur->buildHTML ();
		echo $erreur->HTML_code;
	} else {
		$nbre_part = getNbrePartSoc ( $global_id_client );
		$nbrePS_reel = $nbre_part->param [0] ['nbre_parts'];
		$soldePartSoc = getSoldePartSoc ( $global_id_client );
		
		$soldePartSocRestant = $soldePartSoc->param [0] ['solde_part_soc_restant'];
		
		$solde = $CPT ['solde'];
		$allaccounts = getAllAccounts ( $global_id_client );
		$num_cpte_ps = getPSAccountID ( $global_id_client );
		$info_cpt_ps = getAccountDatas ( $num_cpte_ps );
		setMonnaieCourante ( $info_cpt_ps ["devise"] );
		
		$SESSION_VARS ["info_cpt_ps"] = $info_cpt_ps;
	}

	// get compte courant possible with classe comptable=1
	$cpte_courant_array = get_comptes_epargne_compte_courant ( $global_id_client );

	$Title = _ ( "Demande de transfert parts sociales" ) . " ";
	$myForm = new HTML_GEN2 ( $Title );
	// num compte source*
	$myForm->addField ( "num_cpte_source", _ ( "Numéro de compte source de parts sociales" ), TYPC_TXT );
	$myForm->setFieldProperties ( "num_cpte_source", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "num_cpte_source", FIELDP_DEFAULT, $info_cpt_ps ["num_complet_cpte"] );
	$myForm->setFieldProperties ( "num_cpte_source", FIELDP_IS_LABEL, true );

	$myForm->addHiddenType("hid_num_cpte_source",$info_cpt_ps ["num_complet_cpte"] );

	// libellé du compte
	$myForm->addField ( "libel_cpte", _ ( "Libellé du compte" ), TYPC_TXT );
	$myForm->setFieldProperties ( "libel_cpte", FIELDP_DEFAULT, $info_cpt_ps ["libel"] );
	$myForm->setFieldProperties ( "libel_cpte", FIELDP_IS_LABEL, true );
	
	// etat du compte*
	$myForm->addField ( "etat_cpt", _ ( "Etat du compte" ), TYPC_TXT );
	if (isset ( $info_cpt_ps ['etat_cpte'] )) {
		$myForm->setFieldProperties ( "etat_cpt", FIELDP_DEFAULT, $adsys ["adsys_etat_cpt_epargne"] [$info_cpt_ps ['etat_cpte']] );
	}
	$myForm->setFieldProperties ( "etat_cpt", FIELDP_IS_LABEL, true );
	
	// Type de transfert*
	$myForm->addField ( "type_transfert", _ ( "Type de transfert" ), TYPC_LSB );
	// s'il n'y a pas de compte source pour le transfert, ne pas permettre le choix d'une destination
	$include = array (
			'1' => 'Transfert vers un autre compte de PS',
			'2' => 'Transfert vers compte courant' 
	);
	$myForm->setFieldProperties ( "type_transfert", FIELDP_ADD_CHOICES, ($include) );
	$myForm->setFieldProperties ( "type_transfert", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "type_transfert", FIELDP_HAS_CHOICE_AUCUN, false );
	$myForm->setFieldProperties ( "type_transfert", FIELDP_JS_EVENT, array (
			"onChange" => "generateSearchUrl();" 
	) );
		
		/* Javascript genere l'url appele pour la recherche client */
	$codejs = "function generateSearchUrl()
	{  var type_trans = document.ADForm.HTML_GEN_LSB_type_transfert.value;
		if (type_trans ==1){
			document.ADForm.cpt_dest.value ='';
			document.ADForm.cpt_dest_hdd.value ='';
			}
		if(type_trans ==2){
			document.ADForm.cpt_dest.value ='';
			document.ADForm.cpt_dest_hdd.value ='';
           }	
	}";
		// Javascript pour adapter la recherche
	$codejs2 = "function openRechercheClient()
	 {		
     var urlcpt_ps = '../modules/clients/rech_client_ps.php?m_agc=" . $_REQUEST ['m_agc'] . "&choixCompte=2&cpt_dest=cpt_dest';
	 var urlcpt_courant = '../modules/clients/rech_client_cpt_courant.php?m_agc=" . $_REQUEST ['m_agc'] . "&choixCompte=1&cpt_dest=" . $global_id_client . " ';
	
               var type_trans = document.ADForm.HTML_GEN_LSB_type_transfert.value;
	            if(type_trans == 1)  { 
	                   OpenBrw(urlcpt_ps, '" . _ ( "Recherche" ) . "');
	              }
	            if(type_trans == 2)  { 
                       OpenBrw(urlcpt_courant, '" . _ ( "Recherche" ) . "');
                    }
}";
	
	$myForm->addJS ( JSP_FORM, "JS1", $codejs );
	$myForm->addJS ( JSP_FORM, "JS2", $codejs2 );
	
	$codejs4 = "function checkform() {";
	$codejs4 .= "ADFormValid = true; msg='';";
	$codejs4 .= "if (document.ADForm.cpt_dest_hdd.value==0)\n";
	
	$codejs4 .= "{ msg+=' - " . _ ( "le compte destinataire  doit être renseigné." ) . "\\n';ADFormValid=false;}\n";
	$codejs4 .= "if (msg != '') alert(msg);\n";
	$codejs4 .= "\n}\n";
	$myForm->addJS ( JSP_FORM, "JS4", $codejs4 );

	// compte destinataire*
	$myForm->addHiddenType ( "cpt_dest_hdd" );
	$myForm->addField ( "cpt_dest", _ ( "Compte destinataire" ), TYPC_TXT );
	$myForm->setFieldProperties ( "cpt_dest", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "cpt_dest", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "cpt_dest", FIELDP_DEFAULT, $SESSION_VARS ['transfert_ps'] ['cpt_dest'] );
	
	$myForm->addLink ( "cpt_dest", "rechercher", _ ( "Rechercher" ), "#" );
	$myForm->setLinkProperties ( "rechercher", LINKP_JS_EVENT, array (
			"onclick" => "openRechercheClient(); return false;" 
	) );
	$myForm->addHTMLExtraCode ( "ligne_sep", "<br />" );
	
	$libel_ope = new Trad ( $SESSION_VARS ['transfert_ps'] ['libel_operation'] );
	// Le libellé operation
	$myForm->addField ( "libel_operation", _ ( "Libellé opération" ), TYPC_TTR );
	$myForm->setFieldProperties ( "libel_operation", FIELDP_DEFAULT, $libel_ope );
	$myForm->setFieldProperties ( "libel_operation", FIELDP_IS_REQUIRED, true );
	
	// Boutons
	$myForm->addFormButton ( 1, 1, "ok", _ ( "Valider" ), TYPB_SUBMIT );
	$myForm->addFormButton ( 1, 2, "annuler", _ ( "Annuler" ), TYPB_SUBMIT );
	$myForm->setFormButtonProperties ( "ok", BUTP_PROCHAIN_ECRAN, 'Dps-2' );
	$myForm->setFormButtonProperties ( "ok", BUTP_JS_EVENT, array (
			"onclick" => "checkform();" 
	) );
	$myForm->setFormButtonProperties ( "annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9' );
	$myForm->setFormButtonProperties ( "annuler", BUTP_CHECK_FORM, false );
	$myForm->buildHTML ();
	echo $myForm->getHTML ();
} 

elseif ($global_nom_ecran == "Dps-2") { // ecran de confirmation
	$AGC = getAgenceDatas ( $global_id_agence );
	
	// recuperation de valeur de l'ecran precedent
	$SESSION_VARS ['transfert_ps'] = array ();
	if (! empty ( $hid_num_cpte_source ))
		$SESSION_VARS ['transfert_ps'] ['num_cpte_source'] = $hid_num_cpte_source;
	if (! empty ( $libel_cpte ))
		$SESSION_VARS ['transfert_ps'] ['libel_cpte'] = $libel_cpte;
	if (! empty ( $cpt_dest_hdd ))
		$SESSION_VARS ['transfert_ps'] ['cpt_dest'] = $cpt_dest_hdd;
	if (isset ( $libel_operation ))
		$SESSION_VARS ['transfert_ps'] ['libel_operation'] = $libel_operation;
	if (isset ( $etat_cpt ))
		$SESSION_VARS ['transfert_ps'] ['etat_cpte'] = $etat_cpt;
	if (isset ( $type_transfert ))
		$SESSION_VARS ['transfert_ps'] ['type_transfert'] = $type_transfert;
		
		// Les information du client source
	$nbre_part = getNbrePartSoc ( $global_id_client ); // returns an object
	                                                  // $nbrePS = $nbre_part->param [0] ['nbre_parts']; // object passed to variable
	//nbre ps souscrite_src
	$nbrePS_reel = $nbre_part->param [0] ['nbre_parts'];
	//nbre ps liberer_src
	$nbre_part_lib = getNbrePartSocLib($global_id_client);
	$nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib']; // nbre part transferable_src
	
	$soldePartSoc = getSoldePartSoc ( $global_id_client ); // returns an object
	$soldePS = $soldePartSoc->param [0] ['solde']; // object passed to variables
	$soldePartSocRestant = $soldePartSoc->param [0] ['solde_part_soc_restant']; // object passed to variables
	
	$num_cpte_ps = getPSAccountID ( $global_id_client );
	$info_compte_src = getIdtitulaire ( $SESSION_VARS ['transfert_ps'] ['num_cpte_source'] );
	$id_cpte_src = $info_compte_src [0];
	
	// info utiliser pour confirmer la demande
	$SESSION_VARS ['transfert_ps'] ['id_client_src'] = $global_id_client;
	$SESSION_VARS ['transfert_ps'] ['id_cpte_src'] = $id_cpte_src;
	$SESSION_VARS ['transfert_ps'] ['init_solde_part_src'] = $soldePS;
	
	// Les information du client destinataire
	$info_compte_dest = getIdtitulaire ( $SESSION_VARS ['transfert_ps'] ['cpt_dest'] );
	
	$id_client_dest = $info_compte_dest [1];
	$id_cpte_dest = $info_compte_dest [0];
	
	//nbre part souscrites_dest
	$NBRE_PART = getNbrePartSoc ( $id_client_dest ); // returns an object
	$nbrePS_dest = $NBRE_PART->param [0] ['nbre_parts']; // object passed to variable
	
	//nbre part liberer dest
	$nbre_part_lib_dest = getNbrePartSocLib($id_client_dest );
	$nbrePSlib_dest = $nbre_part_lib_dest->param[0]['nbre_parts_lib']; // nbre part transferable_src
	
	//soldePS_dest & soldePartSocRestant_dest
	$SOLDE_PART_SOC = getSoldePartSoc ( $id_client_dest ); // returns an object
	$soldePS_dest = $SOLDE_PART_SOC->param [0] ['solde']; // object passed to variables
	$soldePartSocRestant_dest = $SOLDE_PART_SOC->param [0] ['solde_part_soc_restant']; // object passed to variables
                                                                   
	// get nombre part max autorisé par client
	$nbre_part_max = $AGC ['nbre_part_social_max_cli'];
	
	//gestion compte courant
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 2) {
		$compte_courant_info = getAccountDatas ( $id_cpte_dest );
	} else {
		// info compte destinataire
		$num_cpte_ps_dest = getPSAccountID ( $id_client_dest );
		$info_cpt_ps_dest = getAccountDatas ( $num_cpte_ps_dest );
		setMonnaieCourante ( $info_cpt_ps_dest ["devise"] );
	}
	
	// info utiliser pour confirmer la demande
	$SESSION_VARS ['transfert_ps'] ['id_client_dest'] = $id_client_dest;
	$SESSION_VARS ['transfert_ps'] ['id_cpte_dest'] = $id_cpte_dest;
	$SESSION_VARS ['transfert_ps'] ['init_solde_part_dest'] = $soldePS_dest;
	$SESSION_VARS ['transfert_ps'] ['init_nbr_part_dest'] = $nbrePS_dest;
	
	$Title = _ ( "Validation demande transfert part sociales " );
	$myForm = new HTML_GEN2 ( $Title );
	// num compte source*
	$myForm->addField ( "num_cpte_source", _ ( "Numéro de compte source de parts sociales" ), TYPC_TXT );
	$myForm->setFieldProperties ( "num_cpte_source", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "num_cpte_source", FIELDP_DEFAULT, $SESSION_VARS ["info_cpt_ps"] ['num_complet_cpte'] );
	$myForm->setFieldProperties ( "num_cpte_source", FIELDP_IS_LABEL, true );
	
	$myForm->addHiddenType ( "hid_num_cpte_source", $SESSION_VARS ["info_cpt_ps"] ['num_complet_cpte'] );
	//nbre part max du destinataire
	$myForm->addHiddenType ( "hid_nbre_part_max", $nbre_part_max );
	
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		// libellé du compte
		$myForm->addField ( "libel_cpte", _ ( "Libellé du compte" ), TYPC_TXT );
		$myForm->setFieldProperties ( "libel_cpte", FIELDP_DEFAULT, $SESSION_VARS ["info_cpt_ps"] ['libel'] );
		$myForm->setFieldProperties ( "libel_cpte", FIELDP_IS_LABEL, true );
	} else {
		// libellé du compte courant
		$myForm->addField ( "libel_cpte", _ ( "Libellé du compte" ), TYPC_TXT );
		$myForm->setFieldProperties ( "libel_cpte", FIELDP_DEFAULT, $compte_courant_info ['libel'] );
		$myForm->setFieldProperties ( "libel_cpte", FIELDP_IS_LABEL, true );
	}
	
	// etat du compte*
	$myForm->addField ( "etat_cpt", _ ( "Etat du compte" ), TYPC_TXT );
	$myForm->setFieldProperties ( "etat_cpt", FIELDP_DEFAULT, $adsys ["adsys_etat_cpt_epargne"] [$SESSION_VARS ["info_cpt_ps"] ['etat_cpte']] );
	$myForm->setFieldProperties ( "etat_cpt", FIELDP_IS_LABEL, true );
	
	$myForm->addHiddenType ( "hid_etat_cpt", $SESSION_VARS ["info_cpt_ps"] ['etat_cpte'] );
	
	// Type de transfert*
	$myForm->addField ( "type_transfert", _ ( "Type de transfert" ), TYPC_TXT );
	$myForm->setFieldProperties ( "type_transfert", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "type_transfert", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "type_transfert", FIELDP_DEFAULT, $adsys ["adsys_type_transfert_ps"] [$SESSION_VARS ['transfert_ps'] ['type_transfert']] );
	
	// --// Solde initiale de parts sociale source T
	$myForm->addField ( "solde_init_source", _ ( "Solde initiale de part sociale" ), TYPC_MNT );
	$myForm->setFieldProperties ( "solde_init_source", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "solde_init_source", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "solde_init_source", FIELDP_DEFAULT, ($soldePS) );
	$myForm->addHiddenType ( "hid_solde_init_source", $soldePS );
	
	
	// nombre de parts sociale initiale source*
	$myForm->addField ( "nmbre_part_init", _ ( "Nombre de parts sociales souscrites " ), TYPC_TXT );
	$myForm->setFieldProperties ( "nmbre_part_init", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "nmbre_part_init", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "nmbre_part_init", FIELDP_DEFAULT, $nbrePS_reel );
	
	$myForm->addHiddenType ( "hid_nmbre_part_init_reel", $nbrePS_reel ); // pour inserez dans la base
	$myForm->addHiddenType ( "hid_nmbre_part_init", $nbrePS ); // pour controle JS(transferabilité)
	
	// nombre de parts sociale libérer source*
	$myForm->addField ( "nmbre_part_lib_init", _ ( "Nombre de parts sociales libérées " ), TYPC_TXT );
	$myForm->setFieldProperties ( "nmbre_part_lib_init", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "nmbre_part_lib_init", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "nmbre_part_lib_init", FIELDP_DEFAULT, $nbrePSlib );
	
	$myForm->addHiddenType ( "hid_nmbre_part_init_lib_reel", $nbrePSlib ); // pour inserez dans la base
	//$myForm->addHiddenType ( "hid_nmbre_part_init", $nbrePSlib );
	                                                          
	// Valeur nominale PS
	$myForm->addField ( "valeur_nominale_ps", _ ( "Valeur nominale part sociale" ), TYPC_MNT );
	$myForm->setFieldProperties ( "valeur_nominale_ps", FIELDP_DEFAULT, ($AGC ["val_nominale_part_sociale"]) );
	$myForm->setFieldProperties ( "valeur_nominale_ps", FIELDP_IS_LABEL, true );
	
	$myForm->addHiddenType ( "hid_valeur_nominale_ps", $AGC ["val_nominale_part_sociale"] );
	
	// nombre de parts sociale a transferer*
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) { // PS -> PS
		$myForm->addField ( "nmbre_part_a_transferer", _ ( "Nombre de parts sociale à transferer" ), TYPC_TXT );
		$myForm->setFieldProperties ( "nmbre_part_a_transferer", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nmbre_part_a_transferer", FIELDP_JS_EVENT, array (
				"onChange" => "setValues();" 
		) );
	} else { // PS -> Cpte courant
		$myForm->addField ( "nmbre_part_a_transferer_cr", _ ( "Nombre de parts sociale à transferer" ), TYPC_TXT );
		$myForm->setFieldProperties ( "nmbre_part_a_transferer_cr", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nmbre_part_a_transferer_cr", FIELDP_JS_EVENT, array (
				"onChange" => "setValues_cr();" 
		) );
	}
	
	// Fonctions java script permettant de generer les valeurs pour les autres text box basant sur la valeur saisie pour le champ 'nmbre_part_a_transferer'
	$codejs3 = "
				function setValues()
	{
	var valeur_nominale_ps = recupMontant(document.ADForm.valeur_nominale_ps.value);	
	var nbr_part_a_trans = parseInt(document.ADForm.nmbre_part_a_transferer.value);
			
	var nbre_ps_souscrit_src = parseInt(document.ADForm.hid_nmbre_part_init.value);
	var nbre_ps_liberer_src = parseInt(document.ADForm.hid_nmbre_part_init_lib_reel.value);
    var solde_init_source = recupMontant(document.ADForm.solde_init_source.value);
			
	var nbre_ps_souscrit_dest = parseInt(document.ADForm.hid_nmbre_part_init_dest.value);
	var nbre_ps_liberer_dest = parseInt(document.ADForm.hid_nmbre_part_init_dest_lib.value);
    var solde_init_dest = recupMontant(document.ADForm.solde_init_dest.value);		
					
		if  ((nbr_part_a_trans <= 0)|| (nbr_part_a_trans > nbre_ps_liberer_src )){	
			alert('Nombre max de part sociale transferable est '+ nbre_ps_liberer_src );
			document.ADForm.nmbre_part_a_transferer.value = nbre_ps_liberer_src;
		    document.ADForm.nouveau_solde_ps_src.value =  formateMontant(solde_init_source -(nbre_ps_liberer_src * valeur_nominale_ps));
			document.ADForm.nouveau_nmbre_part_dest.value = nbre_ps_souscrit_dest + nbre_ps_liberer_src;
			document.ADForm.nouveau_nmbre_part_lib_dest.value = nbre_ps_liberer_dest + nbre_ps_liberer_src;
		    document.ADForm.nouveau_solde_ps_dest.value = formateMontant(solde_init_dest + (nbre_ps_liberer_src * valeur_nominale_ps));	

		   document.ADForm.hid_nouveau_solde_ps_src.value = formateMontant(solde_init_source -(nbre_ps_liberer_src * valeur_nominale_ps));
           document.ADForm.hid_nouveau_nmbre_part_dest.value = nbre_ps_souscrit_dest + nbre_ps_liberer_src;
		   document.ADForm.hid_nouveau_nmbre_part_lib_dest.value = nbre_ps_liberer_dest + nbre_ps_liberer_src;
           document.ADForm.hid_nouveau_solde_ps_dest.value = formateMontant(solde_init_dest + (nbre_ps_liberer_src * valeur_nominale_ps));		
			}	
		
		else if (nbr_part_a_trans <= nbre_ps_liberer_src) {
		    var valeur_ps = nbr_part_a_trans * valeur_nominale_ps;
		    var solde_restant = solde_init_source - valeur_ps;
			
			  document.ADForm.nouveau_solde_ps_src.value =  formateMontant(solde_restant);
			  document.ADForm.nouveau_nmbre_part_dest.value = nbre_ps_souscrit_dest + nbr_part_a_trans;
			  document.ADForm.nouveau_nmbre_part_lib_dest.value = nbre_ps_liberer_dest + nbr_part_a_trans;
		      document.ADForm.nouveau_solde_ps_dest.value = formateMontant(solde_init_dest + valeur_ps);	
			
		   document.ADForm.hid_nouveau_solde_ps_src.value = formateMontant(solde_restant);
           document.ADForm.hid_nouveau_nmbre_part_dest.value = nbre_ps_souscrit_dest + nbr_part_a_trans;
		   document.ADForm.hid_nouveau_nmbre_part_lib_dest.value = nbre_ps_liberer_dest + nbr_part_a_trans;
           document.ADForm.hid_nouveau_solde_ps_dest.value = formateMontant(solde_init_dest + valeur_ps);	
		
		}		
	 else {
			  document.ADForm.nouveau_solde_ps_src.value =  formateMontant(solde_init_source);
			  document.ADForm.nouveau_nmbre_part_dest.value = nbre_ps_souscrit_dest;
			  document.ADForm.nouveau_nmbre_part_lib_dest.value = nbre_ps_liberer_dest;
		      document.ADForm.nouveau_solde_ps_dest.value = formateMontant(solde_init_dest);
			
		   document.ADForm.hid_nouveau_solde_ps_src.value = solde_init_source;
		   document.ADForm.hid_nouveau_nmbre_part_dest.value = nbre_ps_souscrit_dest;
		   document.ADForm.hid_nouveau_nmbre_part_lib_dest.value = nbre_ps_liberer_dest;
           document.ADForm.hid_nouveau_solde_ps_dest.value = formateMontant(solde_init_dest);
	 
				}
	}";
	
	
	
	$codejs5 = "
				function setValues_cr()
	{
	  
	  var valeur_nominale_ps = recupMontant(document.ADForm.valeur_nominale_ps.value);
	  var nbr_part_a_trans = parseInt(document.ADForm.nmbre_part_a_transferer_cr.value);
	  
			
      var nbre_ps_souscrit_src = parseInt(document.ADForm.hid_nmbre_part_init.value);
	  var nbre_ps_liberer_src = parseInt(document.ADForm.hid_nmbre_part_init_lib_reel.value);	
	  var solde_init_source = recupMontant(document.ADForm.solde_init_source.value);
		
	  var solde_init_dest = recupMontant(document.ADForm.solde_init_dest_cr.value);
		
		if  ((nbr_part_a_trans <= 0)|| (nbr_part_a_trans > nbre_ps_liberer_src )){
			alert('Nombre max de part sociale transferable est '+ nbre_ps_liberer_src );	
			document.ADForm.nmbre_part_a_transferer_cr.value = nbre_ps_liberer_src;
		    document.ADForm.nouveau_solde_ps_src.value =  formateMontant(solde_init_source -(nbre_ps_liberer_src * valeur_nominale_ps));
		    document.ADForm.nouveau_solde_ps_dest_cr.value = formateMontant(solde_init_dest + (nbre_ps_liberer_src * valeur_nominale_ps));
		    document.ADForm.nouveau_nmbre_part_src.value = nbr_part_source_init;
		   document.ADForm.hid_nouveau_solde_ps_src.value = formateMontant(solde_init_source -(nbre_ps_liberer_src * valeur_nominale_ps));
          
           document.ADForm.hid_nouveau_solde_ps_dest_cr.value = formateMontant(solde_init_dest + (nbre_ps_liberer_src * valeur_nominale_ps));
			}
		
		else if (nbr_part_a_trans <= nbre_ps_liberer_src ) {
		    var valeur_ps = nbr_part_a_trans * valeur_nominale_ps;
		    var solde_restant = solde_init_source - valeur_ps;
			  document.ADForm.nouveau_solde_ps_src.value =  formateMontant(solde_restant);
		      document.ADForm.nouveau_solde_ps_dest_cr.value = formateMontant(solde_init_dest + valeur_ps);
		   document.ADForm.hid_nouveau_solde_ps_src.value = formateMontant(solde_restant);
           document.ADForm.hid_nouveau_solde_ps_dest_cr.value = formateMontant(solde_init_dest + valeur_ps);
		
		}
	 else {
			  document.ADForm.nouveau_solde_ps_src.value =  formateMontant(solde_init_source);
		      document.ADForm.nouveau_solde_ps_dest_cr.value = formateMontant(solde_init_dest);
				
		   document.ADForm.hid_nouveau_solde_ps_src.value = solde_init_source;
           document.ADForm.hid_nouveau_solde_ps_dest_cr.value = formateMontant(solde_init_dest);
				}
	}";
	
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		$myForm->addJS ( JSP_FORM, "JS3", $codejs3 );
	} else {
		$myForm->addJS ( JSP_FORM, "JS5", $codejs5 );
	}
	
	// nouveau solde de parts sociales
	$myForm->addField ( "nouveau_solde_ps_src", _ ( "Nouveau solde de parts sociale" ), TYPC_MNT );
	$myForm->setFieldProperties ( "nouveau_solde_ps_src", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "nouveau_solde_ps_src", FIELDP_IS_LABEL, true );
	$myForm->addHiddenType ( "hid_nouveau_solde_ps_src", "" );
	
	$myForm->addHTMLExtraCode ( "ligne_sep", "<br />" );
	
	// compte destinataire*
	$myForm->addField ( "cpt_dest", _ ( "Compte destinataire" ), TYPC_TXT );
	$myForm->setFieldProperties ( "cpt_dest", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "cpt_dest", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "cpt_dest", FIELDP_DEFAULT, $SESSION_VARS ['transfert_ps'] ['cpt_dest'] );
	$myForm->addHiddenType ( "hid_cpt_dest", $SESSION_VARS ['transfert_ps'] ['cpt_dest'] );
	
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		// libelle compte destinataire*
		$myForm->addField ( "libel_cpt_dest", _ ( "Libelle compte destinataire" ), TYPC_TXT );
		$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_DEFAULT, $info_cpt_ps_dest ['libel'] ); // ****a afficher la valeur array dependent type transfert
	} else {
		// libelle compte destinataire*
		$myForm->addField ( "libel_cpt_dest", _ ( "Libelle compte destinataire" ), TYPC_TXT );
		$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_DEFAULT, $compte_courant_info ['libel'] ); // ****a afficher la valeur array dependent type transfert
	}
	
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		// nombre de parts sociale souscrite destinataire*
		$myForm->addField ( "nmbre_part_init_dest", _ ( "Nombre PS souscrites du destinataire" ), TYPC_TXT );
		$myForm->setFieldProperties ( "nmbre_part_init_dest", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nmbre_part_init_dest", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "nmbre_part_init_dest", FIELDP_DEFAULT, $nbrePS_dest );
		$myForm->addHiddenType ( "hid_nmbre_part_init_dest", $nbrePS_dest );
	}
	
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		// nombre de parts sociale liberer destinataire*
		$myForm->addField ( "nmbre_part_init_dest_lib", _ ( "Nombre PS libérées du destinataire" ), TYPC_TXT );
		$myForm->setFieldProperties ( "nmbre_part_init_dest_lib", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nmbre_part_init_dest_lib", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "nmbre_part_init_dest_lib", FIELDP_DEFAULT, $nbrePSlib_dest );
		$myForm->addHiddenType ( "hid_nmbre_part_init_dest_lib", $nbrePSlib_dest );
	}
	
	
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		// nouveau nombre de parts sociale souscrite destinataire*
		$myForm->addField ( "nouveau_nmbre_part_dest", _ ( "Nouveau nombre PS souscrites du destinataire" ), TYPC_TXT );
		$myForm->setFieldProperties ( "nouveau_nmbre_part_dest", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nouveau_nmbre_part_dest", FIELDP_IS_LABEL, true );
		$myForm->addHiddenType ( "hid_nouveau_nmbre_part_dest", "" );
	}
	
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		// nouveau nombre de parts sociale liberer  destinataire*
		$myForm->addField ( "nouveau_nmbre_part_lib_dest", _ ( "Nouveau nombre PS libérées du destinataire" ), TYPC_TXT );
		$myForm->setFieldProperties ( "nouveau_nmbre_part_lib_dest", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nouveau_nmbre_part_lib_dest", FIELDP_IS_LABEL, true );
		$myForm->addHiddenType ( "hid_nouveau_nmbre_part_lib_dest", "" );
	}
	
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		// Solde initiale de parts sociale destinataire
		$myForm->addField ( "solde_init_dest", _ ( "Solde initiale de PS du destinataire" ), TYPC_MNT );
		$myForm->setFieldProperties ( "solde_init_dest", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "solde_init_dest", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "solde_init_dest", FIELDP_DEFAULT, ($soldePS_dest) );
		
		$myForm->addHiddenType ( "hid_solde_init_dest", $soldePS_dest );
	} else {
		// Solde initiale courant destinataire
		$myForm->addField ( "solde_init_dest_cr", _ ( "Solde initiale du compte destinataire" ), TYPC_MNT );
		$myForm->setFieldProperties ( "solde_init_dest_cr", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "solde_init_dest_cr", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "solde_init_dest_cr", FIELDP_DEFAULT, ($compte_courant_info ['solde']) ); // la valeur a afficher
		
		$myForm->addHiddenType ( "hid_solde_init_dest_cr", $compte_courant_info ['solde'] );
	}
	
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		// nouveau solde de parts sociales destinataire
		$myForm->addField ( "nouveau_solde_ps_dest", _ ( "Nouveau solde de PS du destinataire" ), TYPC_MNT );
		$myForm->setFieldProperties ( "nouveau_solde_ps_dest", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nouveau_solde_ps_dest", FIELDP_IS_LABEL, true );
		
		$myForm->addHiddenType ( "hid_nouveau_solde_ps_dest", "" );
	} else {
		// nouveau solde compte courant destinataire
		$myForm->addField ( "nouveau_solde_ps_dest_cr", _ ( "Nouveau solde du compte destinataire" ), TYPC_MNT );
		$myForm->setFieldProperties ( "nouveau_solde_ps_dest_cr", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nouveau_solde_ps_dest_cr", FIELDP_IS_LABEL, true );
		
		$myForm->addHiddenType ( "hid_nouveau_solde_ps_dest_cr", "" );
	}
	
	$myForm->addHTMLExtraCode ( "ligne_sep1", "<br />" );
	// Le libellé operation
	$myForm->addField ( "libel_operation", _ ( "Libellé opération" ), TYPC_TTR );
	$myForm->setFieldProperties ( "libel_operation", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "libel_operation", FIELDP_DEFAULT, $SESSION_VARS ['transfert_ps'] ['libel_operation'] );

	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
	if( $nbre_part_max > 0) {
		$ExtraJS .= "\n\t  if( ".$nbre_part_max." < (parseInt(document.ADForm.hid_nouveau_nmbre_part_dest.value))) ";
		$ExtraJS .= "\n\t { ADFormValid = false; msg+='".sprintf(_(" %s !\\n nombre max de parts sociales : %s"),$error[ERR_NBRE_MAX_PS_DESTINATAIRE],$nbre_part_max)."';\n\t}";
	}
	$myForm->addJS(JSP_BEGIN_CHECK, "extrajs", $ExtraJS);
	}
	
	// Boutons
	$myForm->addFormButton ( 1, 1, "ok", _ ( "Valider" ), TYPB_SUBMIT );
	$myForm->addFormButton ( 1, 2, "retour", _ ( "Précédent" ), TYPB_SUBMIT );
	$myForm->addFormButton ( 1, 3, "annuler", _ ( "Annuler" ), TYPB_SUBMIT );
	
	$myForm->setFormButtonProperties ( "ok", BUTP_PROCHAIN_ECRAN, 'Dps-3' );
	
	$myForm->setFormButtonProperties ( "annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9' );
	$myForm->setFormButtonProperties ( "annuler", BUTP_CHECK_FORM, false );
	$myForm->setFormButtonProperties ( "retour", BUTP_PROCHAIN_ECRAN, 'Dps-1' );
	$myForm->setFormButtonProperties ( "retour", BUTP_CHECK_FORM, false );
	
	$myForm->buildHTML ();
	echo $myForm->getHTML ();
} elseif ($global_nom_ecran == "Dps-3") { // ecran de confirmation de demande transfert
	$AGC = getAgenceDatas ( $global_id_agence );

	$SESSION_VARS ['transfert_ps_c'] = array ();
	//type transfert =1 PS->PS
	if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 1) {
		// info transactionnelle et calcul
		if (! empty ( $hid_num_cpte_source ))
			$SESSION_VARS ['transfert_ps_c'] ['num_cpte_src'] = $hid_num_cpte_source;
		if (! empty ( $hid_libel_cpte ))
			$SESSION_VARS ['transfert_ps_c'] ['libel_cpte'] = $hid_libel_cpte;
		if (! empty ( $hid_cpt_dest ))
			$SESSION_VARS ['transfert_ps_c'] ['num_cpte_dest'] = $hid_cpt_dest;
		if (isset ( $libel_operation ))
			$SESSION_VARS ['transfert_ps_c'] ['libel_operation'] = $libel_operation;
		if (isset ( $hid_etat_cpt ))
			$SESSION_VARS ['transfert_ps_c'] ['etat_cpte'] = $hid_etat_cpt;
		if (isset ( $SESSION_VARS ['transfert_ps'] ['type_transfert'] ))
			$SESSION_VARS ['transfert_ps_c'] ['type_transfert'] = $SESSION_VARS ['transfert_ps'] ['type_transfert'];
		if (isset ( $hid_solde_init_source ))
			$SESSION_VARS ['transfert_ps_c'] ['init_solde_part_src'] = recupMontant ( $hid_solde_init_source );
		if (isset ( $hid_valeur_nominale_ps ))
			$SESSION_VARS ['transfert_ps_c'] ['valeur_nominale_ps'] = recupMontant ( $hid_valeur_nominale_ps );
		if (isset ( $nmbre_part_a_transferer ))
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] = $nmbre_part_a_transferer;
		
		//Souscrit source /dest
		if (isset ( $hid_nmbre_part_init_reel ))//souscrit_Src
			$SESSION_VARS ['transfert_ps_c'] ['init_nbre_part_src'] = $hid_nmbre_part_init_reel;
		if (isset ( $hid_nmbre_part_init_reel )) {//nouv nbre_part souscrit_src
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_src'] = ($hid_nmbre_part_init_reel - $nmbre_part_a_transferer);
			$SESSION_VARS ['transfert_ps_c'] ['solde_total_ps_sous'] = recupMontant (($hid_nmbre_part_init_reel - $nmbre_part_a_transferer_cr) * ($AGC ["val_nominale_part_sociale"]));
		}
		if (isset ( $SESSION_VARS ['transfert_ps'] ['init_nbr_part_dest'] )) //init souscrit_dest
				$SESSION_VARS ['transfert_ps_c'] ['init_nbr_part_dest'] = $SESSION_VARS ['transfert_ps'] ['init_nbr_part_dest']; // hid_nmbre_part_init_dest
		if (isset ( $hid_nouveau_nmbre_part_dest ))//nouveau souscrit dest
				$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_dest'] = recupMontant ( $hid_nouveau_nmbre_part_dest );

		//Liberation source/dest
		if (isset ( $hid_nmbre_part_init_lib_reel ))// init liberer_Src
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_init_src_lib'] = $hid_nmbre_part_init_lib_reel;
		if (isset ( $hid_nmbre_part_init_lib_reel )) {//nouv liberer_src
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_lib_src'] = ($hid_nmbre_part_init_lib_reel - $nmbre_part_a_transferer);
			$SESSION_VARS ['transfert_ps_c'] ['solde_total_ps_lib'] = recupMontant ( ($hid_nmbre_part_init_lib_reel - $nmbre_part_a_transferer_cr) * ($AGC ["val_nominale_part_sociale"]));
		}
		if (isset ( $hid_nmbre_part_init_dest_lib )) //init liberer_dest
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_init_dest_lib'] = $hid_nmbre_part_init_dest_lib; 
		if (isset ( $hid_nouveau_nmbre_part_lib_dest ))//nouveau liberer dest
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_lib_dest'] = $hid_nouveau_nmbre_part_lib_dest ;
	
		if (isset ( $hid_nouveau_solde_ps_src ))
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_src'] = recupMontant ( $hid_nouveau_solde_ps_src );
		if (isset ( $hid_nouveau_solde_ps_dest ))
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_dest'] = recupMontant ( $hid_nouveau_solde_ps_dest );
			// info client src
		if (isset ( $SESSION_VARS ['transfert_ps'] ['id_client_src'] ))
			$SESSION_VARS ['transfert_ps_c'] ['id_client_src'] = $SESSION_VARS ['transfert_ps'] ['id_client_src'];
		if (isset ( $SESSION_VARS ['transfert_ps'] ['id_cpte_src'] ))
			$SESSION_VARS ['transfert_ps_c'] ['id_cpte_src'] = $SESSION_VARS ['transfert_ps'] ['id_cpte_src'];
			// info client destinataire
		if (isset ( $SESSION_VARS ['transfert_ps'] ['id_client_dest'] ))
			$SESSION_VARS ['transfert_ps_c'] ['id_client_dest'] = $SESSION_VARS ['transfert_ps'] ['id_client_dest'];
		if (isset ( $SESSION_VARS ['transfert_ps'] ['id_cpte_dest'] ))
			$SESSION_VARS ['transfert_ps_c'] ['id_cpte_dest'] = $SESSION_VARS ['transfert_ps'] ['id_cpte_dest'];
		if (isset ( $SESSION_VARS ['transfert_ps'] ['init_solde_part_dest'] ))
			$SESSION_VARS ['transfert_ps_c'] ['init_solde_part_dest'] = recupMontant ( $SESSION_VARS ['transfert_ps'] ['init_solde_part_dest'] );
	//type transfert =1 PS->Courant
	} else if ($SESSION_VARS ['transfert_ps'] ['type_transfert'] == 2) {

		if (! empty ( $hid_num_cpte_source ))
			$SESSION_VARS ['transfert_ps_c'] ['num_cpte_src'] = $hid_num_cpte_source;
		if (! empty ( $hid_libel_cpte ))
			$SESSION_VARS ['transfert_ps_c'] ['libel_cpte'] = $hid_libel_cpte;
		if (! empty ( $hid_cpt_dest ))
			$SESSION_VARS ['transfert_ps_c'] ['num_cpte_dest'] = $hid_cpt_dest;
		if (isset ( $libel_operation ))
			$SESSION_VARS ['transfert_ps_c'] ['libel_operation'] = $libel_operation;
		if (isset ( $hid_etat_cpt ))
			$SESSION_VARS ['transfert_ps_c'] ['etat_cpte'] = $hid_etat_cpt;
		if (isset ( $SESSION_VARS ['transfert_ps'] ['type_transfert'] ))
			$SESSION_VARS ['transfert_ps_c'] ['type_transfert'] = $SESSION_VARS ['transfert_ps'] ['type_transfert'];
		if (isset ( $hid_solde_init_source ))
			$SESSION_VARS ['transfert_ps_c'] ['init_solde_part_src'] = recupMontant ( $hid_solde_init_source );
		if (isset ( $hid_valeur_nominale_ps ))
			$SESSION_VARS ['transfert_ps_c'] ['valeur_nominale_ps'] = recupMontant ( $hid_valeur_nominale_ps );
		if (isset ( $nmbre_part_a_transferer_cr ))
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] = $nmbre_part_a_transferer_cr;
		if (isset ( $hid_nouveau_solde_ps_src ))
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_src'] = recupMontant ( $hid_nouveau_solde_ps_src );
		if (isset ( $hid_nouveau_solde_ps_dest_cr ))
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_dest'] = recupMontant ( $hid_nouveau_solde_ps_dest_cr );

		//Souscrit source 
		if (isset ( $hid_nmbre_part_init_reel ))//souscrit_Src
			$SESSION_VARS ['transfert_ps_c'] ['init_nbre_part_src'] = $hid_nmbre_part_init_reel;
		
		if (isset ( $hid_nmbre_part_init_reel )) {//nouv nbre_part souscrit_src
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_src'] = ($hid_nmbre_part_init_reel - $nmbre_part_a_transferer_cr);
			$SESSION_VARS ['transfert_ps_c'] ['solde_total_ps_sous'] = recupMontant (($hid_nmbre_part_init_reel - $nmbre_part_a_transferer_cr) * ($AGC ["val_nominale_part_sociale"]));
		}
		//Liberation source
		if (isset ( $hid_nmbre_part_init_lib_reel ))// init liberer_Src
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_init_src_lib'] = $hid_nmbre_part_init_lib_reel;
		
		if (isset ( $hid_nmbre_part_init_lib_reel )){ //nouv liberer_src
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_lib_src'] = ($hid_nmbre_part_init_lib_reel - $nmbre_part_a_transferer_cr);
			$SESSION_VARS ['transfert_ps_c'] ['solde_total_ps_lib'] = recupMontant (($hid_nmbre_part_init_lib_reel - $nmbre_part_a_transferer_cr) * ($AGC ["val_nominale_part_sociale"]));
		}
			// info client src
		if (isset ( $SESSION_VARS ['transfert_ps'] ['id_client_src'] ))
			$SESSION_VARS ['transfert_ps_c'] ['id_client_src'] = $SESSION_VARS ['transfert_ps'] ['id_client_src'];
		if (isset ( $SESSION_VARS ['transfert_ps'] ['id_cpte_src'] ))
			$SESSION_VARS ['transfert_ps_c'] ['id_cpte_src'] = $SESSION_VARS ['transfert_ps'] ['id_cpte_src'];
			// info client destinataire
		if (isset ( $SESSION_VARS ['transfert_ps'] ['id_client_dest'] ))
			$SESSION_VARS ['transfert_ps_c'] ['id_client_dest'] = $SESSION_VARS ['transfert_ps'] ['id_client_dest'];
		if (isset ( $SESSION_VARS ['transfert_ps'] ['id_cpte_dest'] ))
			$SESSION_VARS ['transfert_ps_c'] ['id_cpte_dest'] = $SESSION_VARS ['transfert_ps'] ['id_cpte_dest'];
		if (isset ( $hid_solde_init_dest_cr ))
			$SESSION_VARS ['transfert_ps_c'] ['init_solde_part_dest'] = recupMontant ( $hid_solde_init_dest_cr );
	}

	$err = demande_transfert_ps ( $SESSION_VARS ['transfert_ps_c'] );
	
	if ($err->errCode == NO_ERR) {
		
		$html_msg = new HTML_message ( _ ( "La demande de transfert a été mise en place " ) );
		$message = _ ( "Client Source : " ) . " " . $SESSION_VARS ['transfert_ps_c'] ['num_cpte_src'] . "<br />";
		$message .= _ ( "Client destinataire : " ) . " " . $SESSION_VARS ['transfert_ps_c'] ['num_cpte_dest'] . "<br />";
		$message .= _ ( "Nombre de parts à transférer / remettre : " ) . " " . $SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] . "<br />";
		$message .= "<br/><br/>"._("N° de transaction")." : <b><code>".sprintf("%09d", $err->param)."</code></b>";
		$html_msg->setMessage ( $message );
		$html_msg->addButton ( "BUTTON_OK", 'Gen-9' );
		$html_msg->buildHTML ();
		echo $html_msg->HTML_code;

		print_demande_transfert( $global_id_client, $SESSION_VARS ['transfert_ps_c'], $err->param);

	} else {
		$param = $err->param;
		$html_err = new HTML_erreur ( _ ( "Mise en place demande transfert part sociale." ) );
		$msg = _ ( "Echec" ) . " : " . $error [$err->errCode] . "<br/>";
		if ($param != NULL) {
			if (is_array ( $param )) {
				foreach ( $param as $key => $val ) {
					$msg .= "<br /> " . $key . " : " . $param ["$key"] . "";
				}
			} else {
				$msg .= $param;
			}
		}
		
		$html_err->setMessage ( $msg );
		$html_err->addButton ( "BUTTON_OK", 'Gen-9' );
		$html_err->buildHTML ();
		echo $html_err->HTML_code;
	}
} else {
	signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // "Ecran non trouvé : " . $global_nom_ecran
}
?>