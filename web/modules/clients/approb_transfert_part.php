<?php

/*
 * Ecran d'approbation demande transfert part sociales
 * TF - 19/01/2015
 * T361
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
require_once ('lib/dbProcedures/multilingue.php');
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/handleDB.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/divers.php';

if ($global_nom_ecran == "Aps-1") {
	global $global_id_client;
	global $global_id_agence;
	global $global_nom_utilisateur;

	unset($SESSION_VARS['infos_doss']);
	// Récupération des infos du client
	$SESSION_VARS['infos_client'] = getClientDatas($global_id_client);
	
	//en fonction du choix du numéro de demande, afficher les infos de demande avec le onChange javascript
	$codejs = "\n\nfunction getInfoDemande() {";
	
	$demandes = array(); // tableau contenant les infos sur les demandes(dans ad_transfert_his)
	$liste = array(); // Liste des demande a afficher
	$traduction = array();
	$i = 1;

  //Recuperation de demandes qui sont en attente d'approbation ou rejet(etat_transfert = 1)
   $demande_transfert = array();
	$whereCl=" AND (etat_transfert=1)";
	$demande_transfert = getInfoDemandePS($global_id_client, $whereCl);
	
	if (is_array($demande_transfert))
		foreach($demande_transfert as $id=>$value){
			
			 if($value["etat_transfert"]== 1)
			$date = pg2phpDate($value["date_demande"]); //Fonction renvoie  des dates au format jj/mm/aaaa
			$liste[$i] ="n° $id du $date"; //Construit la liste en affichant N° demande + date_demande
			$demandes[$i] = $value;
			$libel_operationx = db_get_traductions($value["libel_operation"]);
			$traduction[$i] = $libel_operationx ;
			
			
			$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_demande.value == $i)\n\t";
			$codejs .= "{\n\t\tdocument.ADForm.HTML_GEN_ttr_libel_operation_fr_BE.value =\"" . $traduction[$i]['fr_BE']  . "\";"; //TO DO _a ramener la valeur
			$codejs .= "\n\t\tdocument.ADForm.HTML_GEN_ttr_libel_operation_en_GB.value =\"" . $traduction[$i]['en_GB'] . "\"; ";
			$codejs .= "}\n";
			$i++;
		}
	
		$SESSION_VARS['demandes'] = $demandes;
		$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_demande.value =='0') {";
		$codejs .= "\n\t\tdocument.ADForm.libel_operation.value='';";
		$codejs .= "\n\t}\n";
		$codejs .= "}\ngetInfoDemande();";
	
		$Myform = new HTML_GEN2(_("Sélection d'une demande de transfert PS "));
		$Myform->addField("id_demande",_("Demandes de transfert"), TYPC_LSB);
		$Myform->setFieldProperties("id_demande",FIELDP_ADD_CHOICES,$liste);
		$Myform->setFieldProperties("id_demande", FIELDP_JS_EVENT, array("onChange"=>"getInfoDemande();"));
		
		$Myform->addField("libel_operation",_("Libellé operation"), TYPC_TTR);
		$Myform->setFieldProperties("libel_operation", FIELDP_IS_LABEL, true);
		$Myform->setFieldProperties("libel_operation", FIELDP_IS_REQUIRED, true);
		//$Myform->setFieldProperties("libel_operation", FIELDP_DEFAULT, $libel_operationx );

		$Myform->addJS(JSP_FORM, "JS3", $codejs);
	
		 // Javascript : vérifie qu'un demande est sélectionné
		$JS_1 = "";
		$JS_1.="\t\tif(document.ADForm.HTML_GEN_LSB_id_demande.options[document.ADForm.HTML_GEN_LSB_id_demande.selectedIndex].value==0){ msg+=' - "._("Aucun dossier sélectionné")." .\\n';ADFormValid=false;}\n";
		$Myform->addJS(JSP_BEGIN_CHECK,"testdem",$JS_1);
	
		// les boutons ajoutés
		$Myform->addFormButton(1,1,"valider",_("Valider"),TYPB_SUBMIT);
		$Myform->addFormButton(1,2,"annuler",_("Retour Menu"),TYPB_SUBMIT);
	
		// Propriétés des boutons
		$Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Mps-1");
		$Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Aps-2");
		$Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
	
		$Myform->setOrder(NULL,$order);
		$Myform->buildHTML();
		echo $Myform->getHTML();
	
}
else if ($global_nom_ecran == "Aps-2") {
	
	$AGC = getAgenceDatas ( $global_id_agence );
       //la demande choisi pour approbation
		$id_demande = $SESSION_VARS['demandes'][$HTML_GEN_LSB_id_demande]['id'];
		$SESSION_VARS ['id_demande'] = array ();
	    $SESSION_VARS ['id_demande'] = $id_demande;
		
      //recuperation des infos sur la demande
		$DemandeAApprouver = array();
		$whereCl=" AND (id = '$id_demande' )";
		$DemandeAApprouver = getInfoDemandePS($global_id_client, $whereCl);
		
     //recuperation des infos sur le compte source
     // verification de l'Etat du client
       $etat = $global_etat_client;
       if ($etat == 1) {
       	$erreur = new HTML_erreur ( _ ( "Transfert de parts sociales non-autorisée" ) );
       	$erreur->setMessage ( _ ( "Ce client doit d'abord acquitter les frais d'adhésion avant de pouvoir transfert des parts sociales." ) );
       	$erreur->addButton ( BUTTON_OK, "Gen-9" );
       	$erreur->buildHTML ();
       	echo $erreur->HTML_code;
       } else {
       
       	//Les information Actuelle du client source
       	$nbre_part = getNbrePartSoc($global_id_client);//returns an object
       	$nbrePS_reel = $nbre_part->param [0] ['nbre_parts'];//object passed to variable
       
       	$nbre_part_lib = getNbrePartSocLib($global_id_client);
       	$nbrePSlib = $nbre_part_lib->param[0]['nbre_parts_lib']; // nbre part transferable_src

       	$soldePartSoc = getSoldePartSoc($global_id_client);//returns an object
       	$soldePS =$soldePartSoc->param[0]['solde'];//object passed to variables
       	$soldePartSocRestant = $soldePartSoc->param[0]['solde_part_soc_restant'];//object passed to variables
       
       	$solde = $CPT ['solde'];
       	$allaccounts = getAllAccounts ( $global_id_client );
       	$num_cpte_ps = getPSAccountID ( $global_id_client );
       	$info_cpt_ps = getAccountDatas ( $num_cpte_ps );
       	setMonnaieCourante ( $info_cpt_ps ["devise"] );
       
       	//Les information du client destinataire
       	$info_compte_dest = getIdtitulaire($DemandeAApprouver[$id_demande]["num_cpte_dest"]);
       	$id_client_dest= $info_compte_dest[1];
       	$id_cpte_dest =$info_compte_dest[0];
       	$NBRE_PART = getNbrePartSoc($id_client_dest);//returns an object
       	$nbrePS_dest = $NBRE_PART->param[0]['nbre_parts'];//object passed to variable
       	$SOLDE_PART_SOC = getSoldePartSoc(	$id_client_dest);//returns an object
       	$soldePS_dest =$SOLDE_PART_SOC->param[0]['solde'];//object passed to variables
       	$soldePartSocRestant_dest = $SOLDE_PART_SOC->param[0]['solde_part_soc_restant']; // object passed to variables

       	//nbre part liberer dest
       	$nbre_part_lib_dest = getNbrePartSocLib($id_client_dest );
       	$nbrePSlib_dest = $nbre_part_lib_dest->param[0]['nbre_parts_lib'];
       	
		// info compte destinataire
	    // gestion transfert compte courant
		if ($DemandeAApprouver [$id_demande] ["type_transfert"] == 2) {
			$compte_courant_info = getAccountDatas ( $id_cpte_dest );
		} else {
			// info compte destinataire
			$num_cpte_ps_dest = getPSAccountID ( $id_client_dest );
			$info_cpt_ps_dest = getAccountDatas ( $num_cpte_ps_dest );
			setMonnaieCourante ( $info_cpt_ps_dest ["devise"] );
		}
	}
       
       $num_cpte_ps = getPSAccountID ( $global_id_client );
       $info_compte_src = getIdtitulaire($SESSION_VARS ['transfert_ps'] ['num_cpte_source']);
       $id_cpte_src =$info_compte_src[0];
       
	$Title = _ ( "Approbation / Rejet demande transfert part sociales " );
	$myForm = new HTML_GEN2 ( $Title );
	// num compte source*
	$myForm->addField ( "num_cpte_source", _ ( "Numéro de compte source de parts sociales" ), TYPC_TXT );
	$myForm->setFieldProperties ( "num_cpte_source", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "num_cpte_source", FIELDP_DEFAULT, $DemandeAApprouver[$id_demande]["num_cpte_src"]);
	$myForm->setFieldProperties ( "num_cpte_source", FIELDP_IS_LABEL, true );
	$myForm->addHiddenType("hid_num_cpte_source", $DemandeAApprouver[$id_demande]["num_cpte_src"]);
	
	// libellé du compte
	$myForm->addField ( "libel_cpte", _ ( "Libellé du compte" ), TYPC_TXT );
	$myForm->setFieldProperties ( "libel_cpte", FIELDP_DEFAULT, $info_cpt_ps['libel'] );
	$myForm->setFieldProperties ( "libel_cpte", FIELDP_IS_LABEL, true );
	
	// etat du compte*
	$myForm->addField ( "etat_cpt", _ ( "Etat du compte" ), TYPC_TXT );
	if (isset ( $info_cpt_ps ['etat_cpte'] )) {
		$myForm->setFieldProperties ( "etat_cpt", FIELDP_DEFAULT, $adsys ["adsys_etat_cpt_epargne"] [$info_cpt_ps ['etat_cpte']] );
	}
	$myForm->setFieldProperties ( "etat_cpt", FIELDP_IS_LABEL, true );
	$myForm->addHiddenType ( "hid_etat_cpt", $info_cpt_ps ['etat_cpte'] );
	
	// Type de transfert*
	$myForm->addField ( "type_transfert", _ ( "Type de transfert" ), TYPC_TXT );
	$myForm->setFieldProperties ( "type_transfert", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "type_transfert", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "type_transfert", FIELDP_DEFAULT, $adsys ["adsys_type_transfert_ps"] [$DemandeAApprouver [$id_demande] ["type_transfert"]] );
	
	$myForm->addHiddenType ( "hid_type_transfert", $DemandeAApprouver [$id_demande] ["type_transfert"] );
	
	// Solde initiale de parts sociale source
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
	
	
	// nombre de parts sociale libérer source*
	$myForm->addField ( "nmbre_part_lib_init", _ ( "Nombre de parts sociales libérées " ), TYPC_TXT );
	$myForm->setFieldProperties ( "nmbre_part_lib_init", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "nmbre_part_lib_init", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "nmbre_part_lib_init", FIELDP_DEFAULT, $nbrePSlib );
	
	$myForm->addHiddenType ( "hid_nmbre_part_init_lib_reel", $nbrePSlib ); // pour inserez dans la base
	
	
	
	// Valeur nominale PS
	$myForm->addField ( "valeur_nominale_ps", _ ( "Valeur nominale part sociale" ), TYPC_MNT );
	$myForm->setFieldProperties ( "valeur_nominale_ps", FIELDP_DEFAULT, ($AGC ["val_nominale_part_sociale"]) );
	$myForm->setFieldProperties ( "valeur_nominale_ps", FIELDP_IS_LABEL, true );
	
	$myForm->addHiddenType ( "hid_valeur_nominale_ps", $AGC ["val_nominale_part_sociale"] );
	
	
	// nombre de parts sociale a transferer*
	if($DemandeAApprouver[$id_demande]["type_transfert"] == 1){
	$myForm->addField ( "nmbre_part_a_transferer", _ ( "Nombre de parts sociales à transferer" ), TYPC_TXT );
	$myForm->setFieldProperties ( "nmbre_part_a_transferer", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "nmbre_part_a_transferer", FIELDP_DEFAULT, $DemandeAApprouver [$id_demande] ["nbre_part_a_trans"] );
	$myForm->setFieldProperties ( "nmbre_part_a_transferer", FIELDP_JS_EVENT, array (
			"onChange" => "setValues();" 
	) );
	}
    else { // PS -> Cpte courant
		$myForm->addField ("nmbre_part_a_transferer_cr", _ ( "Nombre de parts sociales à transferer" ), TYPC_TXT );
		$myForm->setFieldProperties ( "nmbre_part_a_transferer_cr", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nmbre_part_a_transferer_cr", FIELDP_DEFAULT, $DemandeAApprouver [$id_demande] ["nbre_part_a_trans"] );
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
			
	var nbre_ps_souscrit_src = parseInt(document.ADForm.hid_nmbre_part_init_reel.value);
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
	  
			
      var nbre_ps_souscrit_src = parseInt(document.ADForm.hid_nmbre_part_init_reel.value);
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
	
	
	if($DemandeAApprouver[$id_demande]["type_transfert"] ==  1) {
		   $myForm->addJS ( JSP_FORM, "JS3", $codejs3 );
		}else {
		  $myForm->addJS ( JSP_FORM, "JS5", $codejs5 );
		}
	
	// nouveau solde de parts sociales
	$myForm->addField ( "nouveau_solde_ps_src", _ ( "Nouveau solde de parts sociales" ), TYPC_MNT );
	$myForm->setFieldProperties ( "nouveau_solde_ps_src", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "nouveau_solde_ps_src", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "nouveau_solde_ps_src", FIELDP_DEFAULT, ($DemandeAApprouver[$id_demande]["nouv_solde_part_src"] ));
	
	$myForm->addHiddenType("hid_nouveau_solde_ps_src", $DemandeAApprouver[$id_demande]["nouv_solde_part_src"]);
	
	$myForm->addHTMLExtraCode("ligne_sep","<br />");
	
	// compte destinataire*
	$myForm->addField ( "cpt_dest", _ ( "Compte destinataire" ), TYPC_TXT );
	$myForm->setFieldProperties ( "cpt_dest", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "cpt_dest", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "cpt_dest", FIELDP_DEFAULT, $DemandeAApprouver[$id_demande]["num_cpte_dest"] );
	$myForm->addHiddenType("hid_cpt_dest", $DemandeAApprouver[$id_demande]["num_cpte_dest"]);

	if($DemandeAApprouver[$id_demande]["type_transfert"] == 1){
    // libelle compte destinataire*
	$myForm->addField ( "libel_cpt_dest", _ ( "Libelle compte destinataire" ), TYPC_TXT );
	$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_DEFAULT, $info_cpt_ps_dest ['libel'] );
	}
	else{
		// libelle compte destinataire*
		$myForm->addField ( "libel_cpt_dest", _ ( "Libelle compte destinataire" ), TYPC_TXT );
		$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "libel_cpt_dest", FIELDP_DEFAULT, $compte_courant_info['libel'] ); //****a afficher la valeur array dependent type transfert
	}
	// nombre de parts sociale initiale destinataire*
	if($DemandeAApprouver[$id_demande]["type_transfert"] == 1){
	$myForm->addField ( "nmbre_part_init_dest", _ ( "Nombre PS souscrites du destinataire" ), TYPC_TXT );
	$myForm->setFieldProperties ( "nmbre_part_init_dest", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "nmbre_part_init_dest", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "nmbre_part_init_dest", FIELDP_DEFAULT, $nbrePS_dest );
	$myForm->addHiddenType ( "hid_nmbre_part_init_dest", $nbrePS_dest );
	}
	
	if($DemandeAApprouver[$id_demande]["type_transfert"] == 1){
		// nombre de parts sociale liberer destinataire*
		$myForm->addField ( "nmbre_part_init_dest_lib", _ ( "Nombre PS libérées du destinataire" ), TYPC_TXT );
		$myForm->setFieldProperties ( "nmbre_part_init_dest_lib", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nmbre_part_init_dest_lib", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "nmbre_part_init_dest_lib", FIELDP_DEFAULT, $nbrePSlib_dest );
		$myForm->addHiddenType ( "hid_nmbre_part_init_dest_lib", $nbrePSlib_dest );
	}
	
	
	// nombre de parts sociale initiale destinataire*
	if($DemandeAApprouver[$id_demande]["type_transfert"] == 1){
	$myForm->addField ( "nouveau_nmbre_part_dest", _ ( "Nouveau nombre PS souscrites du destinataire" ), TYPC_TXT );
	$myForm->setFieldProperties ( "nouveau_nmbre_part_dest", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "nouveau_nmbre_part_dest", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "nouveau_nmbre_part_dest", FIELDP_DEFAULT, $DemandeAApprouver [$id_demande] ["nouv_nbre_ps_sscrt_dest"] );
	
	$myForm->addHiddenType ( "hid_nouveau_nmbre_part_dest", $DemandeAApprouver [$id_demande] ["nouv_nbre_ps_sscrt_dest"] );
	}
	
	
	if($DemandeAApprouver[$id_demande]["type_transfert"] == 1){
		// nouveau nombre de parts sociale liberer  destinataire*
		$myForm->addField ( "nouveau_nmbre_part_lib_dest", _ ( "Nouveau nombre PS libérées du destinataire" ), TYPC_TXT );
		$myForm->setFieldProperties ( "nouveau_nmbre_part_lib_dest", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nouveau_nmbre_part_lib_dest", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "nouveau_nmbre_part_lib_dest", FIELDP_DEFAULT,$DemandeAApprouver [$id_demande] ["nouv_nbre_ps_lib_dest"]);
		$myForm->addHiddenType ( "hid_nouveau_nmbre_part_lib_dest", $DemandeAApprouver [$id_demande] ["nouv_nbre_ps_lib_dest"] );
	}
	
	// Solde initiale de parts sociale destinataire
	if($DemandeAApprouver[$id_demande]["type_transfert"] == 1){
	$myForm->addField ( "solde_init_dest", _ ( "Solde initiale de PS du destinataire" ), TYPC_MNT );
	$myForm->setFieldProperties ( "solde_init_dest", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "solde_init_dest", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "solde_init_dest", FIELDP_DEFAULT, ($soldePS_dest) );
	
	$myForm->addHiddenType ( "hid_solde_init_dest", $soldePS_dest );
	}else{
		// Solde initiale courant destinataire
			$myForm->addField ( "solde_init_dest_cr", _ ( "Solde initiale du compte destinataire" ), TYPC_MNT );
			$myForm->setFieldProperties ( "solde_init_dest_cr", FIELDP_IS_REQUIRED, true );
			$myForm->setFieldProperties ( "solde_init_dest_cr", FIELDP_IS_LABEL, true );
			$myForm->setFieldProperties ( "solde_init_dest_cr", FIELDP_DEFAULT,($compte_courant_info['solde'])  );// la valeur a afficher
			
			$myForm->addHiddenType("hid_solde_init_dest_cr", $compte_courant_info['solde']);
	}
	
	// nouveau solde de parts sociales destinataire
	if($DemandeAApprouver[$id_demande]["type_transfert"] == 1){
	$myForm->addField ( "nouveau_solde_ps_dest", _ ( "Nouveau solde de PS du destinataire" ), TYPC_MNT );
	$myForm->setFieldProperties ( "nouveau_solde_ps_dest", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "nouveau_solde_ps_dest", FIELDP_IS_LABEL, true );
	$myForm->setFieldProperties ( "nouveau_solde_ps_dest", FIELDP_DEFAULT, ($DemandeAApprouver [$id_demande] ["nouv_solde_compte_dest"]) );
	
	$myForm->addHiddenType ( "hid_nouveau_solde_ps_dest", AfficheMontant ( $DemandeAApprouver [$id_demande] ["nouv_solde_compte_dest"] ) );
	}
	else{
		// nouveau solde compte courant destinataire
		$myForm->addField ( "nouveau_solde_ps_dest_cr", _ ( "Nouveau solde du compte destinataire" ), TYPC_MNT );
		$myForm->setFieldProperties ( "nouveau_solde_ps_dest_cr", FIELDP_IS_REQUIRED, true );
		$myForm->setFieldProperties ( "nouveau_solde_ps_dest_cr", FIELDP_IS_LABEL, true );
		$myForm->setFieldProperties ( "nouveau_solde_ps_dest_cr", FIELDP_DEFAULT, ($DemandeAApprouver [$id_demande] ["nouv_solde_compte_dest"]) );
			
		$myForm->addHiddenType ( "hid_nouveau_solde_ps_dest_cr", AfficheMontant ( $DemandeAApprouver [$id_demande] ["nouv_solde_compte_dest"] ) );
	}
	$myForm->addHTMLExtraCode ( "ligne_sep1", "<br />" );
	
	$libel_operationx = new Trad ( $DemandeAApprouver [$id_demande] ["libel_operation"] );
	
	// Le libellé operation
	$myForm->addField ( "libel_operation", _ ( "Libellé opération" ), TYPC_TTR );
	$myForm->setFieldProperties ( "libel_operation", FIELDP_IS_REQUIRED, true );
	$myForm->setFieldProperties ( "libel_operation", FIELDP_DEFAULT, $libel_operationx );
	
	// Boutons
	$myForm->addFormButton ( 1, 1, "approuver", _ ( "Approuver" ), TYPB_SUBMIT );
	$myForm->addFormButton(1, 2, "rejetter", _("Rejetter"), TYPB_SUBMIT);
	$myForm->addFormButton ( 1, 3, "annuler", _ ( "Annuler" ), TYPB_SUBMIT );
	
	$myForm->setFormButtonProperties ( "approuver", BUTP_PROCHAIN_ECRAN, 'Aps-3' );
	$myForm->setFormButtonProperties ( "annuler", BUTP_PROCHAIN_ECRAN, 'Gen-9' );
	$myForm->setFormButtonProperties ( "annuler", BUTP_CHECK_FORM, false );
	$myForm->setFormButtonProperties("rejetter", BUTP_PROCHAIN_ECRAN, 'Aps-4');
	$myForm->setFormButtonProperties("rejetter", BUTP_CHECK_FORM, false);
	
	$myForm->buildHTML ();
	echo $myForm->getHTML ();
	
	
}else if ($global_nom_ecran == "Aps-3") {// processing approbation
	/**
	 * *************************************************
	 * get * info for processing
	 * *************************************************
	 */
	$id_demande = $SESSION_VARS ['id_demande']; //TODO unset session
	$infoDemande = array();
	$whereCl=" AND (id = '$id_demande' )";
	$infoDemande = getInfoDemandePS($global_id_client, $whereCl);
	
	if($infoDemande[$id_demande]["type_transfert"] == 1){
		if (! empty ( $id_demande))
		 $SESSION_VARS ['transfert_ps_c'] ['id_demande'] = $id_demande;
		if (! empty ( $hid_num_cpte_source))
			$SESSION_VARS ['transfert_ps_c'] ['num_cpte_src'] = $hid_num_cpte_source;
		if (! empty ( $hid_libel_cpte ) )
			$SESSION_VARS ['transfert_ps_c'] ['libel_cpte'] = $hid_libel_cpte;
		if (! empty ( $hid_cpt_dest))
			$SESSION_VARS ['transfert_ps_c'] ['num_cpte_dest'] = $hid_cpt_dest;
		if (isset ($libel_operation ))
			$SESSION_VARS ['transfert_ps_c'] ['libel_operation'] = $libel_operation;
		if (isset ( $hid_etat_cpt ) )
			$SESSION_VARS ['transfert_ps_c'] ['etat_cpte'] = $hid_etat_cpt;
		if (isset ( $hid_type_transfert) )
			$SESSION_VARS ['transfert_ps_c'] ['type_transfert'] = $hid_type_transfert;
		if (isset ( $hid_solde_init_source))
			$SESSION_VARS ['transfert_ps_c'] ['init_solde_part_src'] = recupMontant($hid_solde_init_source);
		/* if (isset ( $hid_nmbre_part_init_reel ))
			$SESSION_VARS ['transfert_ps_c'] ['init_nbre_part_src'] = $hid_nmbre_part_init_reel; */
		if (isset ( $hid_valeur_nominale_ps ))
			$SESSION_VARS ['transfert_ps_c'] ['valeur_nominale_ps'] = recupMontant($hid_valeur_nominale_ps);
		if (isset ( $nmbre_part_a_transferer )){
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] = $nmbre_part_a_transferer;
		}else{
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] = $infoDemande["nbre_part_a_trans"];
		} 
		if (isset ( $hid_nouveau_solde_ps_src) )
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_src'] = recupMontant($hid_nouveau_solde_ps_src);
		if (isset ( $hid_nouveau_solde_ps_dest ) )
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_dest'] = recupMontant($hid_nouveau_solde_ps_dest);
		
		//Souscrit source /dest
		if (isset ( $hid_nmbre_part_init_reel ))//souscrit_Src
			$SESSION_VARS ['transfert_ps_c'] ['init_nbre_part_src'] = $hid_nmbre_part_init_reel;
		
		if (isset ( $hid_nmbre_part_init_reel ))//nouv nbre_part souscrit_src
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_src'] = ($hid_nmbre_part_init_reel - $nmbre_part_a_transferer);
		 
		if (isset ( $hid_nmbre_part_init_dest ) )
			$SESSION_VARS ['transfert_ps_c'] ['init_nbr_part_dest']= $hid_nmbre_part_init_dest;//hid_nmbre_part_init_dest 
		
		if (isset ( $hid_nouveau_nmbre_part_dest ))//nouveau souscrit dest
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_dest'] =  $hid_nouveau_nmbre_part_dest ;
		
		//Liberation source/dest
		if (isset ( $hid_nmbre_part_init_lib_reel ))// init liberer_Src
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_init_src_lib'] = $hid_nmbre_part_init_lib_reel;
		
		if (isset ( $hid_nmbre_part_init_lib_reel ))//nouv liberer_src
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_lib_src'] = ($hid_nmbre_part_init_lib_reel - $nmbre_part_a_transferer);
			
		if (isset ( $hid_nmbre_part_init_dest_lib )) //init liberer_dest
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_init_dest_lib'] = $hid_nmbre_part_init_dest_lib;
		
		if (isset ( $hid_nouveau_nmbre_part_lib_dest ))//nouveau liberer dest
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_lib_dest'] = $hid_nouveau_nmbre_part_lib_dest ;
		
		//The values following are not being caught
		//info client src
		if (isset ( $infoDemande [$id_demande] ['id_client_src'] ) )
			$SESSION_VARS ['transfert_ps_c'] ['id_client_src']= $infoDemande [$id_demande] ['id_client_src']   ;
		if (!empty ( $infoDemande [$id_demande] ['id_cpt_src'] ) )
			$SESSION_VARS ['transfert_ps_c'] ['id_cpte_src']= $infoDemande [$id_demande] ['id_cpt_src'] ;
		//info client destinataire
		if (isset ( $infoDemande [$id_demande] ['id_client_dest'] ) )
			$SESSION_VARS ['transfert_ps_c'] ['id_client_dest']= $infoDemande [$id_demande] ['id_client_dest']  ;
		if (isset ( $infoDemande [$id_demande] ['id_cpt_dest'] ) )
			$SESSION_VARS ['transfert_ps_c'] ['id_cpte_dest']= $infoDemande [$id_demande] ['id_cpt_dest'] ;
		//this part is not caught
		if (isset ($SESSION_VARS ['transfert_ps'] ['init_solde_part_dest'] ) )
			$SESSION_VARS ['transfert_ps_c'] ['init_solde_compte_dest']= recupMontant($SESSION_VARS ['transfert_ps'] ['init_solde_compte_dest']);
		 
	
		
	}else{//get info_courant
		if (! empty ( $id_demande))
			$SESSION_VARS ['transfert_ps_c'] ['id_demande'] = $id_demande;
		
		if (! empty ( $hid_num_cpte_source))
			$SESSION_VARS ['transfert_ps_c'] ['num_cpte_src'] = $hid_num_cpte_source;
		if (! empty ( $hid_libel_cpte ) )
			$SESSION_VARS ['transfert_ps_c'] ['libel_cpte'] = $hid_libel_cpte;
		if (! empty ( $hid_cpt_dest))
			$SESSION_VARS ['transfert_ps_c'] ['num_cpte_dest'] = $hid_cpt_dest;
		if (isset ($libel_operation ))
			$SESSION_VARS ['transfert_ps_c'] ['libel_operation'] = $libel_operation;
		if (isset ( $hid_etat_cpt ) )
			$SESSION_VARS ['transfert_ps_c'] ['etat_cpte'] = $hid_etat_cpt;
		if (isset ( $hid_type_transfert) )
			$SESSION_VARS ['transfert_ps_c'] ['type_transfert'] = $hid_type_transfert;
		if (isset ( $hid_solde_init_source))
			$SESSION_VARS ['transfert_ps_c'] ['init_solde_part_src'] = recupMontant($hid_solde_init_source);
		if (isset ( $hid_valeur_nominale_ps ))
			$SESSION_VARS ['transfert_ps_c'] ['valeur_nominale_ps'] = recupMontant($hid_valeur_nominale_ps);
		if (isset ( $nmbre_part_a_transferer_cr ))
			$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] = $nmbre_part_a_transferer_cr;
		if (isset ( $hid_nouveau_solde_ps_src) )
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_src'] = recupMontant($hid_nouveau_solde_ps_src);
		if (isset ( $hid_nouveau_solde_ps_dest_cr ) )
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_dest'] = recupMontant($hid_nouveau_solde_ps_dest_cr);
		
		//Souscrit source 
		if (isset ( $hid_nmbre_part_init_reel ))//souscrit_Src
			$SESSION_VARS ['transfert_ps_c'] ['init_nbre_part_src'] = $hid_nmbre_part_init_reel;
		
		if (isset ( $nmbre_part_a_transferer_cr ))//nouv nbre_part souscrit_src
			$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_src'] = ($hid_nmbre_part_init_reel - $nmbre_part_a_transferer_cr);
			
	    //Liberation source
		if (isset ( $hid_nmbre_part_init_lib_reel ))// init liberer_Src
				$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_init_src_lib'] = $hid_nmbre_part_init_lib_reel;
		
		if (isset ( $hid_nmbre_part_init_lib_reel ))//nouv liberer_src
				$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_lib_src'] = ($hid_nmbre_part_init_lib_reel - $nmbre_part_a_transferer_cr);
				
		
		//The values following are not being caught
		//info client src
		if (isset ( $infoDemande [$id_demande] ['id_client_src'] ) )
			$SESSION_VARS ['transfert_ps_c'] ['id_client_src']= $infoDemande [$id_demande] ['id_client_src']   ;
		if (!empty ( $infoDemande [$id_demande] ['id_cpt_src'] ) )
			$SESSION_VARS ['transfert_ps_c'] ['id_cpte_src']= $infoDemande [$id_demande] ['id_cpt_src'] ;
		//info client destinataire
		if (isset ( $infoDemande [$id_demande] ['id_client_dest'] ) )
			$SESSION_VARS ['transfert_ps_c'] ['id_client_dest']= $infoDemande [$id_demande] ['id_client_dest']  ;
		if (isset ( $infoDemande [$id_demande] ['id_cpt_dest'] ) )
			$SESSION_VARS ['transfert_ps_c'] ['id_cpte_dest']= $infoDemande [$id_demande] ['id_cpt_dest'] ;
		//this part is not caught
		if (isset ($hid_solde_init_dest_cr) )
			$SESSION_VARS ['transfert_ps_c'] ['init_solde_compte_dest']= recupMontant($hid_solde_init_dest_cr);	
	}

	/**
	 * *************************************************
	 * Interface Processing transfert compte PS à compte PS
	 * type transfert = 1 
	 * *************************************************
	 */
	
	if($SESSION_VARS ['transfert_ps_c'] ['type_transfert'] == 1){
		
	$err = transfertPSPSInt( $SESSION_VARS ['transfert_ps_c'], $global_id_utilisateur);// $id_utilisateur a passé
	
	if ($err->errCode == NO_ERR) {
	
		$html_msg = new HTML_message(_("La demande de transfert a été approuvé "));
		$message =_("Client Source : ")." ".$SESSION_VARS ['transfert_ps_c'] ['num_cpte_src']."<br />";
		$message .=_("Client destinataire : ")." ".$SESSION_VARS ['transfert_ps_c'] ['num_cpte_dest']."<br />";
		$message .=_("Nombre de parts transférés : ")." ".$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] ."<br />";
		$message .= "<br/><br/>"._("N° de transaction")." : <b><code>".sprintf("%09d", $err->param)."</code></b>";
		$html_msg->setMessage($message);
		$html_msg->addButton("BUTTON_OK", 'Gen-9');
		$html_msg->buildHTML();
		echo $html_msg->HTML_code;
	
		//impression recu transfert ps to ps
		 //id_client, nbre ps_a tranferer, num cpt source, num cpt_dest,
		print_recu_transfert( $global_id_client, $SESSION_VARS ['transfert_ps_c'], $err->param);
		
	} else {
		$param=$err->param;
		$html_err = new HTML_erreur(_("Erreur d'approbation demande ."));
		$msg=_("Echec")." : ".$error[$err->errCode]."<br/>";
		if ($param != NULL) {
			if(is_array($param)){
				foreach($param as $key => $val){
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
	}

	/**
	 * *************************************************
	 * Interface Processing transfert compte PS à compte courant
	 * type transfert = 2
	 * *************************************************
	 */
	else if ($SESSION_VARS ['transfert_ps_c'] ['type_transfert'] == 2) {
		$err = transfertPSCourantInt ( $SESSION_VARS ['transfert_ps_c'], $global_id_utilisateur );
		
		if ($err->errCode == NO_ERR) {
			
			$html_msg = new HTML_message ( _ ( "La demande de transfert a été approuvé " ) );
			$message = _ ( "Client Source : " ) . " " . $SESSION_VARS ['transfert_ps_c'] ['num_cpte_src'] . "<br />";
			$message .= _ ( "Client destinataire : " ) . " " . $SESSION_VARS ['transfert_ps_c'] ['num_cpte_dest'] . "<br />";
			$message .= _ ( "Nombre de parts remises : " ) . " " . $SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] . "<br />";
			$message .= "<br/><br/>" . _ ( "N° de transaction" ) . " : <b><code>" . sprintf ( "%09d", $err->param ) . "</code></b>";
			$html_msg->setMessage ( $message );
			$html_msg->addButton ( "BUTTON_OK", 'Gen-9' );
			$html_msg->buildHTML ();
			echo $html_msg->HTML_code;
			//impression recu ps courant
			print_recu_transfert( $global_id_client, $SESSION_VARS ['transfert_ps_c'], $err->param);
		} else {
			$param = $err->param;
			$html_err = new HTML_erreur ( _ ( "Erreur d'approbation demande ." ) );
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
	}
}  

	/**
	 * *************************************************
	 * Processing Rejet transfert/remise part sociales
	 * type transfert = 3
	 * *************************************************
	 */

else if ($global_nom_ecran == "Aps-4") { // processing rejet
                                        
	$id_demande = $SESSION_VARS ['id_demande']; // TODO unset session
	$infoDemande = array ();
	$whereCl = " AND (id = '$id_demande' )";
	$infoDemande = getInfoDemandePS ( $global_id_client, $whereCl );
	
	if (! empty ( $id_demande ))
		$SESSION_VARS ['transfert_ps_c'] ['id_demande'] = $id_demande;
	
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
	if (isset ( $hid_type_transfert ))
		$SESSION_VARS ['transfert_ps_c'] ['type_transfert'] = $hid_type_transfert;
	if (isset ( $hid_solde_init_source ))
		$SESSION_VARS ['transfert_ps_c'] ['init_solde_part_src'] = recupMontant ( $hid_solde_init_source );
	if (isset ( $hid_nmbre_part_init ))
		$SESSION_VARS ['transfert_ps_c'] ['init_nbre_part_src'] = $hid_nmbre_part_init;
	if (isset ( $hid_valeur_nominale_ps ))
		$SESSION_VARS ['transfert_ps_c'] ['valeur_nominale_ps'] = recupMontant ( $hid_valeur_nominale_ps );
	if (isset ( $nmbre_part_a_transferer ))
		$SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] = $nmbre_part_a_transferer;
	if (isset ( $hid_nouveau_nmbre_part_dest ))
		$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_src'] = ($hid_nmbre_part_init - $nmbre_part_a_transferer);
	if (isset ( $hid_nouveau_solde_ps_src ))
		$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_src'] = recupMontant ( $hid_nouveau_solde_ps_src );
	if (isset ( $hid_nouveau_solde_ps_dest ))
		$SESSION_VARS ['transfert_ps_c'] ['nouveau_solde_ps_dest'] = recupMontant ( $hid_nouveau_solde_ps_dest );
	if (isset ( $hid_nouveau_nmbre_part_dest ))
		$SESSION_VARS ['transfert_ps_c'] ['nouveau_nmbre_part_dest'] = recupMontant ( $hid_nouveau_nmbre_part_dest );
		// The values following are not being caught
		// info client src
	if (isset ( $infoDemande [$id_demande] ['id_client_src'] ))
		$SESSION_VARS ['transfert_ps_c'] ['id_client_src'] = $infoDemande [$id_demande] ['id_client_src'];
	if (! empty ( $infoDemande [$id_demande] ['id_cpt_src'] ))
		$SESSION_VARS ['transfert_ps_c'] ['id_cpte_src'] = $infoDemande [$id_demande] ['id_cpt_src'];
		// info client destinataire
	if (isset ( $infoDemande [$id_demande] ['id_client_dest'] ))
		$SESSION_VARS ['transfert_ps_c'] ['id_client_dest'] = $infoDemande [$id_demande] ['id_client_dest'];
	if (isset ( $infoDemande [$id_demande] ['id_cpt_dest'] ))
		$SESSION_VARS ['transfert_ps_c'] ['id_cpte_dest'] = $infoDemande [$id_demande] ['id_cpt_dest'];
	if (isset ( $SESSION_VARS ['transfert_ps'] ['init_solde_part_dest'] ))
		$SESSION_VARS ['transfert_ps_c'] ['init_solde_part_dest'] = recupMontant ( $SESSION_VARS ['transfert_ps'] ['init_solde_part_dest'] );
	if (isset ( $SESSION_VARS ['transfert_ps'] ['init_nbr_part_dest'] ))
		$SESSION_VARS ['transfert_ps_c'] ['init_nbr_part_dest'] = $SESSION_VARS ['transfert_ps'] ['init_nbr_part_dest'];//hid_nmbre_part_init_dest
	
	/*
	 * Mise ajour base ad_part_sociale_his : Update etat du transfert =3 (Rejeté)
	 */

 	global $dbHandler;
	global $global_id_agence;
	global $db;
	// Ouverture de transaction
	$db = $dbHandler->openConnection ();
	
	$now = date ( "Y-m-d" );
	$sql = "UPDATE ad_transfert_ps_his SET etat_transfert = 3 ,date_rejet = '$now' WHERE id_ag = $global_id_agence AND id_client_src = '" . $SESSION_VARS ['transfert_ps_c'] ['id_client_src'] . "' AND id = '" . $SESSION_VARS ['transfert_ps_c'] ['id_demande'] . " ';";
	
	$result = $db->query ( $sql );
	if (DB::isError ( $result )) {
		$dbHandler->closeConnection ( false );
		
		$param = $err->param;
		$html_err = new HTML_erreur ( _ ( "Erreur rejet demande ." ) );
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
		
		signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // UPDATE echoué", $result->getMessage()
	} else {
		
		$html_msg = new HTML_message ( _ ( "La demande de transfert a été rejeté " ) );
		$message = _ ( "Client Source : " ) . " " . $SESSION_VARS ['transfert_ps_c'] ['num_cpte_src'] . "<br />";
		$message .= _ ( "Client destinataire : " ) . " " . $SESSION_VARS ['transfert_ps_c'] ['num_cpte_dest'] . "<br />";
		$message .= _ ( "Nombre de parts transférés : " ) . " " . $SESSION_VARS ['transfert_ps_c'] ['nmbre_part_a_transferer'] . "<br />";
		$message .= "<br/><br/>" . _ ( "Transfert rejeté!" ) . " ";
		$html_msg->setMessage ( $message );
		$html_msg->addButton ( "BUTTON_OK", 'Gen-9' );
		$html_msg->buildHTML ();
		echo $html_msg->HTML_code;
		
		$erreur = ajout_historique ( 24, NULL, _ ( "Rejet transfert parts sociales" ), $global_nom_login, date ( "r" ), NULL );
		if ($erreur->errCode != NO_ERR) {
			$dbHandler->closeConnection ( false );
			return $erreur;
		}
		
		$dbHandler->closeConnection ( true );
	}
} else
	signalErreur ( __FILE__, __LINE__, __FUNCTION__ ); // "Ecran non trouvé : " . $global_nom_ecran
?>