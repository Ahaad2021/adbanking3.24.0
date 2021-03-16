<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [150] Annulation de raccourcissement d'un dossier de crédit
 * Cette opération comprends les écrans :
 * - Ald-1 : sélection d'un dossier de crédit
 * - Ald-2 : affichage du dossier de crédit
 * - Ald-3 : confirmation de l'annulation
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/divers.php';

require_once 'lib/dbProcedures/historisation.php';

/*{{{ Ald-1 : Sélection d'un dossier de crédit */

if ($global_nom_ecran == "Ald-1") 
{	
	unset ( $SESSION_VARS ['infos_doss'] );
	// Récupération des infos du client
	$SESSION_VARS ['infos_client'] = getClientDatas ( $global_id_client );
	
	// en fonction du choix du numéro de dossier, afficher les infos avec le onChange javascript
	$codejs = "\n\nfunction getInfoDossier() {";
	
	$dossiers = array (); // tableau contenant les infos sur dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
	$liste = array (); // Liste des dossiers à afficher
	$i = 1;
	
	// Récupération des dossiers individuels dans ad_dcr en attente de décision ou en attente de Rééch/Moratoire
	$whereCl = " AND ((etat=15))";
	$dossiers_reels = getIdDossier ( $global_id_client, $whereCl );
	if (is_array ( $dossiers_reels ))
		foreach ( $dossiers_reels as $id_doss => $value )
			if ($value ['gs_cat'] != 2) { // les dossiers pris en groupe doivent être approuvés via le groupe
				$date = pg2phpDate ( $value ["date_dem"] ); // Fonction renvoie des dates au format jj/mm/aaaa
				$liste [$i] = "n° $id_doss du $date"; // Construit la liste en affichant N° dossier + date
				$dossiers [$i] = $value;
				
				$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
				$codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $value ["libelle"] . "\";";
				$codejs .= "}\n";
				$i ++;
			}
		
		// SI GS, récupérer les dossiers des membres dans le cas de dossiers multiples
	if ($SESSION_VARS ['infos_client'] ['statut_juridique'] == 4) {
		// Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2
		$whereCl = " WHERE id_membre=$global_id_client and gs_cat=2";
		$dossiers_fictifs = getCreditFictif ( $whereCl );
		
		// Pour chaque dossier fictif du GS, récupération des dossiers réels des membres du GS
		$dossiers_membre = getDossiersMultiplesGS ( $global_id_client );
		
		foreach ( $dossiers_fictifs as $id => $value ) {
			// Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
			$infos = '';
			foreach ( $dossiers_membre as $id_doss => $val )
				if (($val ['id_dcr_grp_sol'] == $id) and ($val ['etat'] == 15)) {
					$date_dem = $date = pg2phpDate ( $val ['date_dem'] );
					$infos .= "n° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
				}
			if ($infos != '') { // Si au moins on 1 dossier
				$infos .= "du $date_dem";
				$liste [$i] = $infos;
				$dossiers [$i] = $value; // on garde les infos du dossier fictif
				
				$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value ==$i)\n\t";
				$codejs .= "{\n\t\tdocument.ADForm.id_prod.value =\"" . $val ["libelle"] . "\";";
				$codejs .= "}\n";
				$i ++;
			}
		}
	}
	
	$SESSION_VARS ['dossiers'] = $dossiers;
	$codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_id_doss.value =='0') {";
	$codejs .= "\n\t\tdocument.ADForm.id_prod.value='';";
	$codejs .= "\n\t}\n";
	$codejs .= "}\ngetInfoDossier();";
	
	$Myform = new HTML_GEN2 ( _ ( "Sélection d'un dossier de crédit" ) );
	$Myform->addField ( "id_doss", _ ( "Dossier de crédit" ), TYPC_LSB );
	$Myform->addField ( "id_prod", _ ( "Type produit de crédit" ), TYPC_TXT );
	
	$Myform->setFieldProperties ( "id_prod", FIELDP_IS_LABEL, true );
	$Myform->setFieldProperties ( "id_prod", FIELDP_IS_REQUIRED, false );
	$Myform->setFieldProperties ( "id_prod", FIELDP_WIDTH, 30 );
	
	$Myform->setFieldProperties ( "id_doss", FIELDP_ADD_CHOICES, $liste );
	$Myform->setFieldProperties ( "id_doss", FIELDP_JS_EVENT, array (
			"onChange" => "getInfoDossier();" 
	) );
	$Myform->addJS ( JSP_FORM, "JS3", $codejs );
	
	// Javascript : vérifie qu'un dossier est sélectionné
	$JS_1 = "";
	$JS_1 .= "\t\tif(document.ADForm.HTML_GEN_LSB_id_doss.options[document.ADForm.HTML_GEN_LSB_id_doss.selectedIndex].value==0){ msg+=' - " . _ ( "Aucun dossier sélectionné" ) . " .\\n';ADFormValid=false;}\n";
	$Myform->addJS ( JSP_BEGIN_CHECK, "testdos", $JS_1 );
	
	// Ordre d'affichage des champs
	$order = array (
			"id_doss",
			"id_prod" 
	);
	
	// les boutons ajoutés
	$Myform->addFormButton ( 1, 1, "valider", _ ( "Valider" ), TYPB_SUBMIT );
	$Myform->addFormButton ( 1, 2, "annuler", _ ( "Retour Menu" ), TYPB_SUBMIT );
	
	// Propriétés des boutons
	$Myform->setFormButtonProperties ( "annuler", BUTP_PROCHAIN_ECRAN, "Gen-11" );
	$Myform->setFormButtonProperties ( "valider", BUTP_PROCHAIN_ECRAN, "Ald-2" );
	$Myform->setFormButtonProperties ( "annuler", BUTP_CHECK_FORM, false );
	
	$Myform->setOrder ( NULL, $order );
	$Myform->buildHTML ();
	echo $Myform->getHTML ();
}	

	/* }}} */

/*{{{ Ald-2 : Affichage du dossier */
else if ($global_nom_ecran == "Ald-2") 
{
	global $adsys;
	// Si on vient de Ald-1, on récupère les infos de la BD
	if (strstr ( $global_nom_ecran_prec, "Ald-1" )) {
		
		// Récupération des dossiers à approuver
		if ($SESSION_VARS ['dossiers'] [$HTML_GEN_LSB_id_doss] ['gs_cat'] != 2) { // dossier individuel
		                                                                        // Les informations sur le dossier
			$id_doss = $SESSION_VARS ['dossiers'] [$HTML_GEN_LSB_id_doss] ['id_doss'];
			$id_prod = $SESSION_VARS ['dossiers'] [$HTML_GEN_LSB_id_doss] ['id_prod'];
			;
			$SESSION_VARS ['infos_doss'] [$id_doss] = getDossierCrdtInfo ( $id_doss ); // infos du dossier reel
			$SESSION_VARS ['infos_doss'] [$id_doss] ['cre_date_approb'] = date ( "d/m/Y" );
			$SESSION_VARS ['infos_doss'] [$id_doss] ['last_etat'] = $SESSION_VARS ['infos_doss'] [$id_doss] ['etat'];
			$SESSION_VARS ['infos_doss'] [$id_doss] ['cre_mnt_octr'] = $SESSION_VARS ['infos_doss'] [$id_doss] ['mnt_dem'];
			// Infos dossiers fictifs dans le cas de GS avec dossier unique
			if ($SESSION_VARS ['dossiers'] [$HTML_GEN_LSB_id_doss] ['gs_cat'] == 1) {
				$whereCond = " WHERE id_dcr_grp_sol = $id_doss";
				$SESSION_VARS ['infos_doss'] [$id_doss] ['doss_fic'] = getCreditFictif ( $whereCond );
			}
		} elseif ($SESSION_VARS ['dossiers'] [$HTML_GEN_LSB_id_doss] ['gs_cat'] == 2) { // GS avec dossiers multiples
		                                                                           // id du dossier fictif : id du dossier du groupe
			$id_doss_fic = $SESSION_VARS ['dossiers'] [$HTML_GEN_LSB_id_doss] ['id'];
			
			// dossiers réels des membre du GS
			$dossiers_membre = getDossiersMultiplesGS ( $global_id_client );
			
			foreach ( $dossiers_membre as $id_doss => $val ) {
				if ($val ['id_dcr_grp_sol'] == $SESSION_VARS ['dossiers'] [$HTML_GEN_LSB_id_doss] ['id'] and ($val ['etat'] == 1 or $val ['etat'] == 7 or $val ['etat'] == 15)) {
				
				$SESSION_VARS ['infos_doss'] [$id_doss] = $val; // infos d'un dossier reel d'un membre
					$SESSION_VARS ['infos_doss'] [$id_doss] ['cre_date_approb'] = date ( "d/m/Y" );
					$SESSION_VARS ['infos_doss'] [$id_doss] ['last_etat'] = $SESSION_VARS ['infos_doss'] [$id_doss] ['etat'];
					$SESSION_VARS ['infos_doss'] [$id_doss] ['cre_mnt_octr'] = $SESSION_VARS ['infos_doss'] [$id_doss] ['mnt_dem'];
					$id_prod = $SESSION_VARS ['infos_doss'] [$id_doss] ['id_prod']; // même produit pour tous les dossiers
				}
			}
		}
		
		/* Vérificateur de l'état des garanties */
		$gar_pretes = true;
		
		/* Récupération des garanties déjà mobilisées pour ce dossier */
		foreach ( $SESSION_VARS ['infos_doss'] as $id_doss => $infos_doss ) {
			$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] = array ();
			$liste_gar = getListeGaranties ( $id_doss );
			foreach ( $liste_gar as $key => $value ) {
				$num = count ( $SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] ) + 1; // indice du tableau
				$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['id_gar'] = $value ['id_gar'];
				$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['type'] = $value ['type_gar'];
				$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['valeur'] = recupMontant ( $value ['montant_vente'] );
				$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['devise_vente'] = $value ['devise_vente'];
				$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['etat'] = $value ['etat_gar'];
				
			/* Les garanties doivent être à l'état 'Prête' ou mobilisé au moment de l'approbation */

			  //Recuperation de l'etat du crédit
			  $etat_dossier=$SESSION_VARS['infos_doss'][$id_doss]['etat'];

			  //si l'etat du credit est en attente approbation raccourcissement durée ne pas prendre en compte cette partie. #531
			  if($etat_dossier != 15) {
				  if ($value ['etat_gar'] != 2 and $value ['etat_gar'] != 3)
					  $gar_pretes = false;
			  }
					/* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
				if ($value ['type_gar'] == 1) /* Garantie numéraire */
					$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['descr_ou_compte'] = $value ['gar_num_id_cpte_prelev'];
				elseif ($value ['type_gar'] == 2 and isset ( $value ['gar_mat_id_bien'] )) { /* garantie matérielle */
					$id_bien = $value ['gar_mat_id_bien'];
					$infos_bien = getInfosBien ( $id_bien );
					$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['descr_ou_compte'] = $infos_bien ['description'];
					$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['type_bien'] = $infos_bien ['type_bien'];
					$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['piece_just'] = $infos_bien ['piece_just'];
					$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['num_client'] = $infos_bien ['id_client'];
					$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['remarq'] = $infos_bien ['remarque'];
					$SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] [$num] ['gar_mat_id_bien'] = $id_bien;
				}
			} /* Fin foreach garantie */
		} /* Fin foreach infos dossiers */
	
		/* Toutes les garanties doivent être à l'état 'Prête' ou 'Mobilisé' au moment du déboursement  */
				if ($gar_pretes == false) {
			$erreur = new HTML_erreur ( _ ( "Approbation de dossier de crédit" ) );
			$msg = _ ( "Impossible d'approuver le dossier de crédit. Les garanties mobilisées ne sont pas toutes prêtes" );
			$erreur->setMessage ( $msg );
			$erreur->addButton ( BUTTON_OK, "Gen-11" );
			$erreur->buildHTML ();
			echo $erreur->HTML_code;
			exit ();
		}
		
		// Les informations sur le produit de crédit
		$Produit = getProdInfo ( " where id =" . $id_prod , $id_doss);
		$SESSION_VARS ['infos_prod'] = $Produit [0];
		
		// Récupérations des utilisateurs. C'est à dire les agents gestionnaires
		$SESSION_VARS ['utilisateurs'] = array ();
		$utilisateurs = getUtilisateurs ();
		foreach ( $utilisateurs as $id_uti => $val_uti )
			$SESSION_VARS ['utilisateurs'] [$id_uti] = $val_uti ['nom'] . " " . $val_uti ['prenom'];
			// Tri par ordre alphabétique des utilisateurs
		natcasesort ( $SESSION_VARS ['utilisateurs'] );
		// Objet demande de crédit
		$SESSION_VARS ['obj_dem'] = getObjetsCredit ();
	} // fin si on vient de Ard-1
	
	/* Récupération des garanties déjà mobilisées pour ce dossier */
	$SESSION_VARS ['DATA_GAR'] = array ();
	$liste_gar = getListeGaranties ( $id_doss );
	// recuperation de la precision de la devise du produit de credit
	$devise_prod = $SESSION_VARS ['infos_prod'] ['devise'];
	$DEV = getInfoDevise ( $devise_prod ); // recuperation d'info sur la devise'
	$precision_devise = pow ( 10, $DEV ["precision"] );
	
	foreach ( $liste_gar as $key => $value ) {
		/* Mémorisation des garanties */
		$num = count ( $SESSION_VARS ['DATA_GAR'] ) + 1;
		$SESSION_VARS ['DATA_GAR'] [$num] ['id_gar'] = $value ['id_gar'];
		$SESSION_VARS ['DATA_GAR'] [$num] ['type'] = $value ['type_gar'];
		$SESSION_VARS ['DATA_GAR'] [$num] ['valeur'] = recupMontant ( $value ['montant_vente'] );
		$SESSION_VARS ['DATA_GAR'] [$num] ['devise_vente'] = $value ['devise_vente'];
		$SESSION_VARS ['DATA_GAR'] [$num] ['etat'] = $value ['etat_gar'];
		
		/* Les garanties doivent être à l'état 'Prête' ou mobilisé sauf les garanties numéraires encours */
		if ($value ['etat_gar'] != 2 and $value ['etat_gar'] != 3 and $value ['type_gar'] != 1 and $value ['gar_num_id_cpte_prelev'] != NULL)
			$gar_pretes = false;
			
			/* Si c'est une garantie numéraire récupérer le numéro complet du compte de prélèvement */
		if ($value ['type_gar'] == 1) /* Garantie numéraire */
			$SESSION_VARS ['DATA_GAR'] [$num] ['descr_ou_compte'] = $value ['gar_num_id_cpte_prelev'];
		elseif ($value ['type_gar'] == 2 and isset ( $value ['gar_mat_id_bien'] )) { /* garantie matérielle */
			$id_bien = $value ['gar_mat_id_bien'];
			$infos_bien = getInfosBien ( $id_bien );
			$SESSION_VARS ['DATA_GAR'] [$num] ['descr_ou_compte'] = $infos_bien ['description'];
			$SESSION_VARS ['DATA_GAR'] [$num] ['type_bien'] = $infos_bien ['type_bien'];
			$SESSION_VARS ['DATA_GAR'] [$num] ['piece_just'] = $infos_bien ['piece_just'];
			$SESSION_VARS ['DATA_GAR'] [$num] ['num_client'] = $infos_bien ['id_client'];
			$SESSION_VARS ['DATA_GAR'] [$num] ['remarq'] = $infos_bien ['remarque'];
			$SESSION_VARS ['DATA_GAR'] [$num] ['gar_mat_id_bien'] = $id_bien;
		}
	} /* Fin foreach */
	
	// Gestion de la devise
	setMonnaieCourante ( $SESSION_VARS ['infos_prod'] ['devise'] );
	$id_prod = $SESSION_VARS ['infos_prod'] ['id'];
	
	// Création du formulaire
	$js_date_approb = '';
	$js_gar = '';
	$js_check = ""; // Javascript de validation de la saisie
	$can_mob_gar = false; // On ne peut mobiliser des garanties que si le dossier est en attente de décision
	$Myform = new HTML_GEN2 ( _ ( "Annulation raccourcissement du dossier de crédit" ) );

	foreach ( $SESSION_VARS ['infos_doss'] as $id_doss => $val_doss ) {
		$nom_cli = getClientName ( $val_doss ['id_client'] );
		
		if ($val_doss ['etat'] == 15) {
			$Myform->addHTMLExtraCode ( "espace" . $id_doss, "<br/><b><p align=\"center\"><b> " . sprintf ( _ ( "Annulation Raccourcissement du dossier N° %s de %s" ), $id_doss, $nom_cli ) . "</b></p>" );
		}

		$val_doss['detail_obj_dem'] = $val_doss['detail_obj_dem'];
		
		$Myform->addField ( "id_doss" . $id_doss, _ ( "Numéro de dossier" ), TYPC_TXT );
		$Myform->setFieldProperties ( "id_doss" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "id_doss" . $id_doss, FIELDP_DEFAULT, $val_doss ['id_doss'] );
		$Myform->addField ( "id_prod" . $id_doss, _ ( "Produit de crédit" ), TYPC_LSB );
		$Myform->setFieldProperties ( "id_prod" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "id_prod" . $id_doss, FIELDP_ADD_CHOICES, array (
				"$id_prod" => $SESSION_VARS ['infos_prod'] ['libel'] 
		) );
		$Myform->setFieldProperties ( "id_prod" . $id_doss, FIELDP_DEFAULT, $id_prod );
		// Ajout de liens
		$Myform->addLink ( "id_prod" . $id_doss, "produit" . $id_doss, _ ( "Détail produit" ), "#" );
		$Myform->setLinkProperties ( "produit" . $id_doss, LINKP_JS_EVENT, array (
				"onClick" => "open_produit(" . $id_prod . "," . $id_doss . ");"
		) );
		$Myform->addField ( "periodicite" . $id_doss, _ ( "Périodicité" ), TYPC_INT );
		$Myform->setFieldProperties ( "periodicite" . $id_doss, FIELDP_DEFAULT, adb_gettext ( $adsys ["adsys_type_periodicite"] [$SESSION_VARS ['infos_prod'] ['periodicite']] ) );
		$Myform->setFieldProperties ( "periodicite" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->addField ( "obj_dem" . $id_doss, _ ( "Objet de la demande" ), TYPC_LSB );
		$Myform->setFieldProperties ( "obj_dem" . $id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS ['obj_dem'] );
		$Myform->setFieldProperties ( "obj_dem" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "obj_dem" . $id_doss, FIELDP_DEFAULT, $val_doss ['obj_dem'] );
		$Myform->addField ( "detail_obj_dem" . $id_doss, _ ( "Détail objet demande" ), TYPC_TXT );
		$Myform->setFieldProperties ( "detail_obj_dem" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "detail_obj_dem" . $id_doss, FIELDP_DEFAULT, $val_doss ['detail_obj_dem'] );
		$Myform->addField ( "date_dem" . $id_doss, _ ( "Date demande" ), TYPC_DTE );
		$Myform->setFieldProperties ( "date_dem" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "date_dem" . $id_doss, FIELDP_DEFAULT, $val_doss ['date_dem'] );
		$Myform->addField ( "cre_date_debloc" . $id_doss, _ ( "Date de déblocage" ), TYPC_DTE );
		$Myform->setFieldProperties ( "cre_date_debloc" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "cre_date_debloc" . $id_doss, FIELDP_DEFAULT, $val_doss ['cre_date_debloc'] );
		$Myform->addField ( "mnt_dem" . $id_doss, _ ( "Montant demandé" ), TYPC_MNT );
		$Myform->setFieldProperties ( "mnt_dem" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "mnt_dem" . $id_doss, FIELDP_DEFAULT, $val_doss ['mnt_dem'], true );
		$Myform->addField ( "num_cre" . $id_doss, _ ( "Numéro de crédit" ), TYPC_INT );
		$Myform->setFieldProperties ( "num_cre" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "num_cre" . $id_doss, FIELDP_DEFAULT, $val_doss ['num_cre'] );
		$Myform->addField ( "cpt_liaison" . $id_doss, _ ( "Compte de liaison" ), TYPC_TXT );
		$Myform->setFieldProperties ( "cpt_liaison" . $id_doss, FIELDP_IS_LABEL, true );
		
		$cpt_lie = getAccountDatas ( $val_doss ['cpt_liaison'] );
		
		$Myform->setFieldProperties ( "cpt_liaison" . $id_doss, FIELDP_DEFAULT, $cpt_lie ["num_complet_cpte"] . " " . $cpt_lie ['intitule_compte'] );
		$Myform->addField ( "id_agent_gest" . $id_doss, _ ( "Agent gestionnaire" ), TYPC_LSB );
		$Myform->setFieldProperties ( "id_agent_gest" . $id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS ['utilisateurs'] );
		$Myform->setFieldProperties ( "id_agent_gest" . $id_doss, FIELDP_IS_LABEL, false );
		$Myform->setFieldProperties ( "id_agent_gest" . $id_doss, FIELDP_DEFAULT, $val_doss ['id_agent_gest'] );
		$Myform->addField ( "etat" . $id_doss, _ ( "Etat du dossier" ), TYPC_TXT );
		$Myform->setFieldProperties ( "etat" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "etat" . $id_doss, FIELDP_DEFAULT, adb_gettext ( $adsys ["adsys_etat_dossier_credit"] [$val_doss ['etat']] ) );
		$Myform->addField ( "date_etat" . $id_doss, _ ( "Date état du dossier" ), TYPC_DTE );
		$Myform->setFieldProperties ( "date_etat" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "date_etat" . $id_doss, FIELDP_DEFAULT, $val_doss ['date_etat'] );
		$Myform->addField ( "cre_mnt_octr" . $id_doss, _ ( "Montant octroyé" ), TYPC_MNT );
		$Myform->setFieldProperties ( "cre_mnt_octr" . $id_doss, FIELDP_IS_REQUIRED, true );
		$Myform->setFieldProperties ( "cre_mnt_octr" . $id_doss, FIELDP_IS_LABEL, false );
		$Myform->setFieldProperties ( "cre_mnt_octr" . $id_doss, FIELDP_DEFAULT, $val_doss ['cre_mnt_octr'] );
		$Myform->setFieldProperties ( "cre_mnt_octr" . $id_doss, FIELDP_JS_EVENT, array (
				"OnFocus" => "reset($id_doss);" 
		) );
		$Myform->setFieldProperties ( "cre_mnt_octr" . $id_doss, FIELDP_JS_EVENT, array (
				"OnChange" => "init($id_doss);" 
		) );
		$Myform->addHiddenType ( "mnt_octr" . $id_doss, $val_doss ['cre_mnt_octr'] );
		
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gs_cat"] == 1)
			$Myform->setFieldProperties ( "cre_mnt_octr" . $id_doss, FIELDP_IS_LABEL, true );
			
			// type de durée : en mois ou en semaine
		$type_duree = $SESSION_VARS ['infos_prod'] ['type_duree_credit'];
		$libelle_duree = mb_strtolower ( adb_gettext ( $adsys ['adsys_type_duree_credit'] [$type_duree] ) ); // libellé type durée en minuscules
		
		$Myform->addField ( "duree_mois" . $id_doss, sprintf ( _ ( "Durée en %s" ), $libelle_duree ), TYPC_INT );
		$Myform->setFieldProperties ( "duree_mois" . $id_doss, FIELDP_IS_REQUIRED, true );
		$Myform->setFieldProperties ( "duree_mois" . $id_doss, FIELDP_IS_LABEL, false );
		$Myform->setFieldProperties ( "duree_mois" . $id_doss, FIELDP_DEFAULT, $val_doss ['duree_mois'] );
		$Myform->addField ( "cre_date_approb" . $id_doss, _ ( "Date approbation" ), TYPC_DTE );
		$Myform->setFieldProperties ( "cre_date_approb" . $id_doss, FIELDP_IS_REQUIRED, true );
		$Myform->setFieldProperties ( "cre_date_approb" . $id_doss, FIELDP_IS_LABEL, false );
		$Myform->setFieldProperties ( "cre_date_approb" . $id_doss, FIELDP_DEFAULT, $val_doss ['cre_date_approb'] );
		
		// Test de la date d'approbation
		$js_date_approb .= "\t\tif(isBefore(document.ADForm.HTML_GEN_date_cre_date_approb" . $id_doss . ".value, 'document.ADForm.HTML_GEN_date_date_dem" . $id_doss . "value')){ msg+=' - " . sprintf ( _ ( "La date d\'approbation pour le dossier %s ne peut être antérieure à la date de demande." ), $id_doss ) . "\\n';ADFormValid=false;}\n";
		$Myform->addField ( "differe_jours" . $id_doss, _ ( "Différé en jours" ), TYPC_INN );
		$Myform->setFieldProperties ( "differe_jours" . $id_doss, FIELDP_IS_LABEL, false );
		$Myform->setFieldProperties ( "differe_jours" . $id_doss, FIELDP_DEFAULT, $val_doss ['differe_jours'] );
		$Myform->addField ( "differe_ech" . $id_doss, _ ( "Différé en échéance" ), TYPC_INT );
		$Myform->setFieldProperties ( "differe_ech" . $id_doss, FIELDP_DEFAULT, $val_doss ['differe_ech'] );
		$Myform->addField ( "delai_grac" . $id_doss, _ ( "Délai de grace" ), TYPC_INT );
		$Myform->setFieldProperties ( "delai_grac" . $id_doss, FIELDP_DEFAULT, $val_doss ['delai_grac'] );
		$Myform->addField ( "mnt_commission" . $id_doss, _ ( "Montant commission" ), TYPC_MNT );
		$Myform->setFieldProperties ( "mnt_commission" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "mnt_commission" . $id_doss, FIELDP_DEFAULT, $val_doss ['mnt_commission'] );
		$Myform->addField ( "mnt_assurance" . $id_doss, _ ( "Montant assurance" ), TYPC_MNT );
		$Myform->setFieldProperties ( "mnt_assurance" . $id_doss, FIELDP_IS_LABEL, true );
		$Myform->setFieldProperties ( "mnt_assurance" . $id_doss, FIELDP_DEFAULT, $val_doss ["mnt_dem"] * $SESSION_VARS ['infos_prod'] ['prc_assurance'] );
		
		if (! empty ( $val_doss ['cpt_prelev_frais'] )) {
			$cpt_frais = getAccountDatas ( $val_doss ['cpt_prelev_frais'] );
			$Myform->addField ( "prelev_auto" . $id_doss, _ ( "Prélèvement automatique" ), TYPC_BOL );
			$Myform->setFieldProperties ( "prelev_auto" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "prelev_auto" . $id_doss, FIELDP_DEFAULT, $val_doss ['prelev_auto'] );
			$Myform->addField ( "cpt_prelev_frais" . $id_doss, _ ( "Compte de prélévement des frais" ), TYPC_TXT );
			$Myform->setFieldProperties ( "cpt_prelev_frais" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "cpt_prelev_frais" . $id_doss, FIELDP_DEFAULT, $cpt_frais ["num_complet_cpte"] . " " . $cpt_frais ['intitule_compte'] );
			
			if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num"] > 0) {
				$Myform->addField ( "gar_num" . $id_doss, _ ( "Garantie numéraire attendue" ), TYPC_MNT );
				$Myform->setFieldProperties ( "gar_num" . $id_doss, FIELDP_IS_LABEL, true );
				$Myform->setFieldProperties ( "gar_num" . $id_doss, FIELDP_DEFAULT, $val_doss ['gar_num'] );
			}
		}
		
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num_encours"] > 0) {
			$Myform->addField ( "gar_num_encours" . $id_doss, _ ( "Garantie numéraire encours" ), TYPC_MNT );
			$Myform->setFieldProperties ( "gar_num_encours" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "gar_num_encours" . $id_doss, FIELDP_DEFAULT, $val_doss ['gar_num_encours'] );
		}
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_mat"] > 0) {
			$Myform->addField ( "gar_mat" . $id_doss, _ ( "Garantie matérielle attendue" ), TYPC_MNT );
			$Myform->setFieldProperties ( "gar_mat" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "gar_mat" . $id_doss, FIELDP_DEFAULT, $val_doss ['gar_mat'] );
		}
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_tot"] > 0) {
			$Myform->addField ( "gar_tot" . $id_doss, _ ( "Garantie totale attendue" ), TYPC_MNT );
			$Myform->setFieldProperties ( "gar_tot" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "gar_tot" . $id_doss, FIELDP_DEFAULT, $val_doss ['gar_tot'] );
		}
		
		/* Initialisation des garanties numéraires et matérielles au début et les garanties à numéraires à constituer */
		$mnt_credit = $val_doss ['cre_mnt_octr'];
		
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num"] > 0) {
			$js_gar .= "\n\tdocument.ADForm.gar_num" . $id_doss . ".value=Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_num'] . "*parseFloat(" . $mnt_credit . ")*" . $precision_devise . ")/" . $precision_devise . ";";
			$js_gar .= "\n\tdocument.ADForm.gar_num" . $id_doss . ".value =formateMontant(document.ADForm.gar_num" . $id_doss . ".value);\n";
		}
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_mat"] > 0) {
			$js_gar .= "\n\tdocument.ADForm.gar_mat" . $id_doss . ".value=Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_mat'] . "*parseFloat(" . $mnt_credit . ")*" . $precision_devise . ")/" . $precision_devise . ";";
			$js_gar .= "\n\tdocument.ADForm.gar_mat" . $id_doss . ".value =formateMontant(document.ADForm.gar_mat" . $id_doss . ".value);\n";
		}
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_tot"] > 0) {
			$js_gar .= "\n\tdocument.ADForm.gar_tot" . $id_doss . ".value=Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_tot'] . "*parseFloat(" . $mnt_credit . ")*" . $precision_devise . ")/" . $precision_devise . ";";
			$js_gar .= "\n\tdocument.ADForm.gar_tot" . $id_doss . ".value =formateMontant(document.ADForm.gar_tot" . $id_doss . ".value);\n";
		}
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num_encours"] > 0) {
			$js_gar .= "\n\tdocument.ADForm.gar_num_encours" . $id_doss . ".value=Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_encours'] . "*parseFloat(" . $mnt_credit . ")*" . $precision_devise . ")/" . $precision_devise . ";";
			$js_gar .= "\n\tdocument.ADForm.gar_num_encours" . $id_doss . ".value =formateMontant(document.ADForm.gar_num_encours" . $id_doss . ".value);\n";
		}
		$SESSION_VARS ['infos_doss'] [$id_doss] ['gar_num_mob'] = 0; // garanties numéraires totales mobilisées
		$SESSION_VARS ['infos_doss'] [$id_doss] ['gar_mat_mob'] = 0; // garanties matérilles totales mobilisées
		if (is_array ( $SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] ))
			foreach ( $SESSION_VARS ['infos_doss'] [$id_doss] ['DATA_GAR'] as $key => $value ) {
				if ($value ['type'] == 1)
					$SESSION_VARS ['infos_doss'] [$id_doss] ['gar_num_mob'] += recupMontant ( $value ['valeur'] );
				elseif ($value ['type'] == 2)
					$SESSION_VARS ['infos_doss'] [$id_doss] ['gar_mat_mob'] += recupMontant ( $value ['valeur'] );
			}
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num_mob"] > 0) {
			$Myform->addField ( "gar_num_mob" . $id_doss, _ ( "Garantie numéraire mobilisée" ), TYPC_MNT );
			$Myform->setFieldProperties ( "gar_num_mob" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "gar_num_mob" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS ['infos_doss'] [$id_doss] ['gar_num_mob'] );
		}
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_mat_mob"] > 0) {
			$Myform->addField ( "gar_mat_mob" . $id_doss, _ ( "Garantie matérielle mobilisée" ), TYPC_MNT );
			$Myform->setFieldProperties ( "gar_mat_mob" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "gar_mat_mob" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS ['infos_doss'] [$id_doss] ['gar_mat_mob'] );
		}
		
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ['etat'] == 15) { // Raccourcissement
		                                                           // type de durée : en mois ou en semaine
			$type_duree = $SESSION_VARS ['infos_prod'] ['type_duree_credit'];
			$libelle_duree = mb_strtolower ( adb_gettext ( $adsys ['adsys_type_duree_credit'] [$type_duree] ) ); // libellé type durée en minuscules
			                                                                                             
			// Recup la date souhaité de remboursement
			$his_obj = Historisation::getListDossierHis ( $id_doss, 2, 'f' );
						
			$tmp_reech_duree = ($his_obj [$id_doss] ['reech_duree']);
			// $tmp_date_dem = pg2phpDate($his_obj[$id_doss]['date_crea']);
			
			$new_duree_mois = count ( getEcheancier ( "WHERE id_doss = " . $id_doss ) );
			
			if (! isset ( $new_duree_mois ) || $new_duree_mois <= 0) {
				$new_duree_mois = $val_doss ['duree_mois'];
			}
			
			// $Myform->addField("nouv_duree_mois".$id_doss, _("Nouvelle durée en ".$libelle_duree), TYPC_INT);
			
			// Recupere les duree minimum et maximum permissible pour le dossier
			$duree = getDureeMinMaxForRaccourcissement ( $id_doss );
			$nbr_echeances_restant = $duree ['nbr_echeances_restant'];
			$nbr_echeances_max = $duree ['nbr_echeances_max'];
			
			// #430 : Le champ echeances_restant garde le nombre d’échéances restantes
			$Myform->addField ( "echeances_restant" . $id_doss, _ ( "Nombre d’échéances restantes" ), TYPC_INT );
			$Myform->setFieldProperties ( "echeances_restant" . $id_doss, FIELDP_IS_REQUIRED, false );
			$Myform->setFieldProperties ( "echeances_restant" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "echeances_restant" . $id_doss, FIELDP_DEFAULT, $nbr_echeances_restant );
				
			// #430 : Le champ nouv_duree_mois garde maintenant le nombre d’échéances souhaitees pour le racourcissement
			$Myform->addField ( "nouv_duree_mois" . $id_doss, _ ( "Nombre d’échéances demandées" ), TYPC_INT );
			$Myform->setFieldProperties ( "nouv_duree_mois" . $id_doss, FIELDP_IS_REQUIRED, false );
			$Myform->setFieldProperties ( "nouv_duree_mois" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "nouv_duree_mois" . $id_doss, FIELDP_DEFAULT, $tmp_reech_duree );
			
			if (! empty ( $val_doss ['cre_id_cpte'] )) {
				$cpt_cr = getAccountDatas ( $val_doss ['cre_id_cpte'] );
				$Myform->addField ( "cre_id_cpte" . $id_doss, _ ( "Compte de crédit" ), TYPC_TXT );
				$Myform->setFieldProperties ( "cre_id_cpte" . $id_doss, FIELDP_DEFAULT, $cpt_cr ["num_complet_cpte"] . " " . $cpt_cr ['intitule_compte'] );
				$Myform->setFieldProperties ( "cre_id_cpte" . $id_doss, FIELDP_IS_LABEL, true );
			}
			
			$Myform->addField ( "cre_etat" . $id_doss, _ ( "Etat crédit" ), TYPC_INT );
			$Myform->setFieldProperties ( "cre_etat" . $id_doss, FIELDP_DEFAULT, getLibel ( "adsys_etat_credits", $val_doss ['cre_etat'] ) );
			$Myform->setFieldProperties ( "cre_etat" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->addField ( "cre_nbre_reech" . $id_doss, _ ( "Nombre de rééchelonnement" ), TYPC_INT );
			$Myform->setFieldProperties ( "cre_nbre_reech" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "cre_nbre_reech" . $id_doss, FIELDP_DEFAULT, $val_doss ['cre_nbre_reech'] );
			$Myform->addField ( "nbre_reechelon_auth" . $id_doss, _ ( "Nombre maximum de rééchelonnements" ), TYPC_INT );
			$Myform->setFieldProperties ( "nbre_reechelon_auth" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS ['infos_prod'] ['nbre_reechelon_auth'] );
			$Myform->setFieldProperties ( "nbre_reechelon_auth" . $id_doss, FIELDP_IS_LABEL, true );
			
			$SESSION_VARS ['infos_doss'] [$id_doss] ['cap'] = 0;
			$SESSION_VARS ['infos_doss'] [$id_doss] ['int'] = 0; // Somme des intérêts
			$SESSION_VARS ['infos_doss'] [$id_doss] ['pen'] = 0; // Somme des pénalités
			$dateRechMor = date ( "d/m/Y" );
			$whereCond = "WHERE (remb='f') AND (id_doss='" . $id_doss . "')";
			$lastEch = getEcheancier ( $whereCond );
			if (is_array ( $lastEch ))
				while ( list ( $key, $value ) = each ( $lastEch ) ) {
					$SESSION_VARS ['infos_doss'] [$id_doss] ['cap'] += $value ["solde_cap"];
					$SESSION_VARS ['infos_doss'] [$id_doss] ['int'] += $value ["solde_int"];
					$SESSION_VARS ['infos_doss'] [$id_doss] ['pen'] += $value ["solde_pen"];
				}
				// FIXME : A-t-on besoin de cet appel à getLastRechMorHistorique si on appelle de nouveua getMontantExigible ?
			
			$reech_moratoire = getLastRechMorHistorique ( 143, $val_doss ['id_client'] );
			// $SESSION_VARS['infos_doss'][$id_doss]['mnt_reech'] = $reech_moratoire['infos']; //Le montant rééchelonné
			$SESSION_VARS ['infos_doss'] [$id_doss] ['date_reech'] = $reech_moratoire ["date"]; // La date de mise en attente de rééchélonnement
			
			$SESSION_VARS ['infos_doss'] [$id_doss] ['mnt_reech'] = 0;
			
			$MNT_EXIG = getMontantExigible ( $id_doss );
			// Nouveau capital = capital + montant rééchelonné
			$SESSION_VARS ['infos_doss'] [$id_doss] ['nouveau_cap'] = $SESSION_VARS ['infos_doss'] [$id_doss] ['cap'] + $SESSION_VARS ['infos_doss'] [$id_doss] ['mnt_reech'];
			$Myform->addField ( "mnt_cap" . $id_doss, _ ( "Montant dû en capital" ), TYPC_MNT );
			$Myform->addField ( "mnt_reech" . $id_doss, _ ( "Montant rééchelonné" ), TYPC_MNT );
			$Myform->addField ( "nouveau_cap" . $id_doss, _ ( "Nouveau capital" ), TYPC_MNT );
			$Myform->addField ( "date_reech" . $id_doss, "Date de demande de raccourcissement", TYPC_DTE );
			
			$Myform->setFieldProperties ( "mnt_cap" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS ['infos_doss'] [$id_doss] ['cap'] );
			$Myform->setFieldProperties ( "mnt_reech" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS ['infos_doss'] [$id_doss] ['mnt_reech'] );
			$Myform->setFieldProperties ( "nouveau_cap" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS ['infos_doss'] [$id_doss] ['nouveau_cap'] );
			$Myform->setFieldProperties ( "date_reech" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS ['infos_doss'] [$id_doss] ['date_etat'] );
			$Myform->setFieldProperties ( "mnt_cap" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "mnt_reech" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "nouveau_cap" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "date_reech" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "cre_nbre_reech" . $id_doss, FIELDP_DEFAULT, $SESSION_VARS ['infos_doss'] [$id_doss] ['cre_nbre_reech'] + 1 );
			$Myform->setFieldProperties ( "duree_mois" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "differe_jours" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "differe_ech" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "delai_grac" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "cre_mnt_octr" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "cre_date_approb" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "cre_date_debloc" . $id_doss, FIELDP_IS_LABEL, true );
			$Myform->setFieldProperties ( "id_agent_gest" . $id_doss, FIELDP_IS_LABEL, true );
		} // fin si rééchelonnement
		  
		// Afficahge des dossiers fictifs dans le cas d'un GS avec dossier réel unique
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ['gs_cat'] == 1) {
			$js_mnt_octr = "function calculeMontant() {"; // function de calcule du montant octroyé
			$js_mnt_octr .= "var tot_mnt_octr = 0;\n";
			
			foreach ( $SESSION_VARS ['infos_doss'] [$id_doss] ['doss_fic'] as $id_fic => $val_fic ) {
				$Myform->addHTMLExtraCode ( "espace_fic" . $id_fic, "<BR>" );
				$Myform->addField ( "membre" . $id_fic, _ ( "Membre" ), TYPC_TXT );
				$Myform->setFieldProperties ( "membre" . $id_fic, FIELDP_IS_REQUIRED, true );
				$Myform->setFieldProperties ( "membre" . $id_fic, FIELDP_IS_LABEL, true );
				$Myform->setFieldProperties ( "membre" . $id_fic, FIELDP_DEFAULT, $val_fic ['id_membre'] . " " . getClientName ( $val_fic ['id_membre'] ) );
				$Myform->addField ( "obj_dem_fic" . $id_fic, _ ( "Objet demande" ), TYPC_LSB );
				$Myform->setFieldProperties ( "obj_dem_fic" . $id_fic, FIELDP_ADD_CHOICES, $SESSION_VARS ['obj_dem'] );
				$Myform->setFieldProperties ( "obj_dem_fic" . $id_fic, FIELDP_IS_REQUIRED, true );
				$Myform->setFieldProperties ( "obj_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic ['obj_dem'] );
				$Myform->addField ( "detail_obj_dem_fic" . $id_fic, _ ( "Détail demande" ), TYPC_TXT );
				$Myform->setFieldProperties ( "detail_obj_dem_fic" . $id_fic, FIELDP_IS_REQUIRED, true );
				$Myform->setFieldProperties ( "detail_obj_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic ['detail_obj_dem'] );
				$Myform->addField ( "mnt_dem_fic" . $id_fic, _ ( "Montant demande" ), TYPC_MNT );
				$Myform->setFieldProperties ( "mnt_dem_fic" . $id_fic, FIELDP_IS_REQUIRED, true );
				$Myform->setFieldProperties ( "mnt_dem_fic" . $id_fic, FIELDP_DEFAULT, $val_fic ['mnt_dem'], true );
				$Myform->setFieldProperties ( "mnt_dem_fic" . $id_fic, FIELDP_JS_EVENT, array (
						"OnChange" => "calculeMontant();" 
				) );
				
				$js_mnt_octr .= "tot_mnt_octr = tot_mnt_octr + recupMontant(document.ADForm.mnt_dem_fic" . $id_fic . ".value);\n";
			}

			$js_mnt_octr .= "document.ADForm.cre_mnt_octr" . $id_doss . ".value = formateMontant(tot_mnt_octr);\n";
			$js_mnt_octr .= "document.ADForm.mnt_octr" . $id_doss . ".value = formateMontant(tot_mnt_octr);\n";
			if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num"] > 0) {
				$js_mnt_octr .= "\n\tdocument.ADForm.gar_num" . $id_doss . ".value =formateMontant(Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_num'] . "*parseFloat(tot_mnt_octr))*" . $precision_devise . ")/" . $precision_devise . ";";
			}
			if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_mat"] > 0) {
				$js_mnt_octr .= "\n\tdocument.ADForm.gar_mat" . $id_doss . ".value = formateMontant(Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_mat'] . "*parseFloat(tot_mnt_octr))*" . $precision_devise . ")/" . $precision_devise . ";";
			}
			if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_tot"] > 0) {
				$js_mnt_octr .= "\n\tdocument.ADForm.gar_tot" . $id_doss . ".value = formateMontant(Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_tot'] . "*parseFloat(tot_mnt_octr))*" . $precision_devise . ")/" . $precision_devise . ";";
			}
			if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num_encours"] > 0) {
				$js_mnt_octr .= "\n\tdocument.ADForm.gar_num_encours" . $id_doss . ".value = formateMontant(Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_encours'] . "*parseFloat(tot_mnt_octr))*" . $precision_devise . ")/" . $precision_devise . ";";
			}
			$js_mnt_octr .= "}\n";
		}
		
		// Contrôle Javascript
		// Vérifier que le montant totat mobilisé est supérieur ou égal au montant attendu
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ['gar_tot'] > 0) {
			$gar_num_mob = "document.ADForm.gar_num_mob" . $id_doss;
			$gar_mat_mob = "document.ADForm.gar_mat_mob" . $id_doss;
			$gar_tot = "document.ADForm.gar_tot" . $id_doss;
			if ($SESSION_VARS ['infos_doss'] [$id_doss] ['gar_num'] > 0) {
				$gar_num = "document.ADForm.gar_num" . $id_doss;
				// Vérifer que les garanties numéraires mobilisées sont supérieues aux garanties numéraires attendues
				$js_check .= "if (recupMontant($gar_num.value) > recupMontant($gar_num_mob.value)) {\n";
				$js_check .= "\tmsg += '- " . sprintf ( _ ( "Les garanties matérielles mobilisées par le dossier %s sont insuffisantes" ), $id_doss ) . "\\n';\n";
				$js_check .= "\tADFormValid = false;\n";
				$js_check .= "}\n";
			}
			if ($SESSION_VARS ['infos_doss'] [$id_doss] ['gar_mat'] > 0) {
				$gar_mat = "document.ADForm.gar_mat" . $id_doss;
				// Vérifer que les garanties matérielle mobilisées sont supérieues aux garanties matérielle attendues
				$js_check .= "if (recupMontant($gar_mat.value) > recupMontant($gar_mat_mob.value)) {\n";
				$js_check .= "\tmsg += '- " . sprintf ( _ ( "Les garanties matérielles mobilisées par le dossier %s sont insuffisantes" ), $id_doss ) . "\\n';\n";
				$js_check .= "\tADFormValid = false;\n";
				$js_check .= "}\n";
			}
			$js_check .= "gar_tot_mob = 0;\n";
			if ($SESSION_VARS ['infos_doss'] [$id_doss] ['gar_num_mob'] > 0) {
				$js_check .= "gar_tot_mob += recupMontant($gar_num_mob.value);\n";
			}
			if ($SESSION_VARS ['infos_doss'] [$id_doss] ['gar_mat_mob'] > 0) {
				$js_check .= "gar_tot_mob += recupMontant($gar_mat_mob.value);\n";
			}
			$js_check .= "if (recupMontant($gar_tot.value) > gar_tot_mob) {\n";
			$js_check .= "\tmsg += '- " . sprintf ( _ ( "Le montant total des garanties numéraires et matérielles mobilisées par le dossier %s est insuffisant" ), $id_doss ) . "\\n';\n";
			$js_check .= "\tADFormValid = false;\n";
			$js_check .= "}\n";
		}
		// Contrôle de la date de déblocage
		if ($SESSION_VARS ['infos_doss'] [$id_doss] ["cre_date_debloc"] != "" && $SESSION_VARS ['infos_doss'] [$id_doss] ["cre_date_debloc"] != NULL && $SESSION_VARS ['infos_doss'] [$id_doss] ['etat'] != 15) {
			$js_check .= "if(('" . $SESSION_VARS ['infos_doss'] [$id_doss] ["cre_date_debloc"] . "') <  ('" . php2pg ( date ( "d/m/Y" ) ) . "'))
               {
                 msg += '- " . sprintf ( _ ( "La date de déboursement du dossier %s est dépassée, Veuillez Contacter l\'agent de crédit" ), $id_doss ) . "\\n';
                 ADFormValid = false;
               }\t";
		}
		
		/* // Vérifier que le montant à octroyer est conforme aux paramètres du produit de crédit
		$js_check .= "\tif(parseFloat(" . $SESSION_VARS ['infos_prod'] ['mnt_max'] . ")>0){ \n";
		$js_check .= "\t\tif((parseFloat(recupMontant(document.ADForm.cre_mnt_octr" . $id_doss . ".value)) < parseFloat(" . $SESSION_VARS ['infos_prod'] ['mnt_min'] . ")) || (parseFloat(recupMontant(document.ADForm.cre_mnt_octr" . $id_doss . ".value)) > parseFloat(" . $SESSION_VARS ['infos_prod'] ['mnt_max'] . "))) {  msg+='- " . sprintf ( _ ( "Le montant demandé pour le dossier %s doit être compris entre %s et %s comme défini dans le produit." ), $id_doss, afficheMontant ( $SESSION_VARS ['infos_prod'] ['mnt_min'] ), afficheMontant ( $SESSION_VARS ['infos_prod'] ['mnt_max'] ) ) . "';ADFormValid=false;;}\n";
		$js_check .= "\t}\n";
		
		$js_check .= "\telse if( parseFloat(recupMontant(document.ADForm.cre_mnt_octr" . $id_doss . ".value)) < parseFloat(" . $SESSION_VARS ['infos_prod'] ['mnt_min'] . ")) { msg+='- " . sprintf ( _ ( "Le montant demandé doit être au moins égal à %s comme défini dans le produit" ), afficheMontant ( $SESSION_VARS ['infos_prod'] ['mnt_min'] ) ) . "'; ADFormValid=false;;}\n";
		
		// validation montant approuvé
		$js_check .= "\tif(parseFloat(recupMontant(document.ADForm.cre_mnt_octr" . $id_doss . ".value)) > parseInt(recupMontant(document.ADForm.mnt_dem" . $id_doss . ".value))) { msg+='- " . _ ( "Le montant approuvé doit être au plus égal au montant demandé" ) . "'; ADFormValid=false;}\n";
		
		// Les controls de duree :
		$msgControlDuree = _ ( " - Le nombre d’échéances demandé doit être entre 1 et $nbr_echeances_max" );
		
		$js_check .= "\t\t
		var selected_echeances = document.ADForm.nouv_duree_mois$id_doss.value;
		\n \t\t selected_echeances = parseInt(selected_echeances); \n
		\n \t\t var allowed_echeances = parseInt($nbr_echeances_max); \n
		 
		if(selected_echeances < 1 || selected_echeances > allowed_echeances) {
		msg +='" . $msgControlDuree . "\\n';
		//document.ADForm.nouv_duree_mois$id_doss.value = '';
		ADFormValid=false;
		}
		"; */
		
		/*
		 * // Vérification de la durée en mois $js_check .="\tif(parseInt(".$SESSION_VARS['infos_prod']['duree_max_mois'].")>0){\n"; $js_check .="\t\tif((parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$SESSION_VARS['infos_prod']['duree_min_mois'].")) || (parseInt(document.ADForm.duree_mois".$id_doss.".value) > parseInt(".$SESSION_VARS['infos_prod']['duree_max_mois']."))) { msg+=' - ".sprintf(_("La durée du crédit doit être comprise entre %s et %s"),$SESSION_VARS['infos_prod']['duree_min_mois'],$SESSION_VARS['infos_prod']['duree_max_mois'])." "._("Comme définie dans le produit.")."\\n';ADFormValid=false;}\n"; $js_check .="\t}else\n"; $JS_1.="\t\tif(parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$SESSION_VARS['infos_prod']['duree_min_mois'].")) { msg+=' - ".sprintf(_("La durée du crédit doit être au moins égale à %s"),$SESSION_VARS['infos_prod']['duree_min_mois'])." "._("Comme définie dans le produit.")."\\n';ADFormValid=false;}\n"; // Test de la durée demandée à faire uniquement si le nombre de mois de la période est > 1 if ($adsys["adsys_duree_periodicite"][$SESSION_VARS['infos_prod']['periodicite']] > 1) { $js_check .= "\t\tif (parseInt(document.ADForm.duree_mois".$id_doss.".value) % parseInt(".$adsys["adsys_duree_periodicite"][$SESSION_VARS['infos_prod']['periodicite']].") != 0) { msg += '- ".sprintf(_("La durée doit être multiple de %s"),$adsys["adsys_duree_periodicite"][$SESSION_VARS['infos_prod']['periodicite']])."'; ADFormValid = false; }\n"; } $js_date_approb .= "\t\tif(parseInt(document.ADForm.nouv_duree_mois$id_doss.value)<=0) { msg +='- "._("La nouvelle durée du crédit doit être superieur à 0")."\\n'; ADFormValid=false; }\n\n\t\tif(parseInt(document.ADForm.nouv_duree_mois$id_doss.value)>".($new_duree_mois-1).") { msg +='- "._("La nouvelle durée du crédit doit être inferieur à la durée en mois initial (".$new_duree_mois.")")."\\n'; ADFormValid=false; }\n";
		 */
	} // fin parcours dossiers
	
	$Myform->addJS ( JSP_BEGIN_CHECK, "testdateapprob", $js_date_approb );
	$Myform->addJS ( JSP_FORM, "testgar", $js_gar );
	$Myform->addJS ( JSP_BEGIN_CHECK, "js_check", $js_check );
	$Myform->addJS ( JSP_FORM, "js_mnt_octr", $js_mnt_octr );
	
	// les boutons ajoutés
	$Myform->addFormButton ( 1, 1, "valider", _ ( "Valider" ), TYPB_SUBMIT );
	$Myform->addFormButton ( 1, 2, "annuler", _ ( "Annuler" ), TYPB_SUBMIT );
	
	// Propriétés des boutons
	$Myform->setFormButtonProperties ( "annuler", BUTP_PROCHAIN_ECRAN, "Gen-11" );
	$Myform->setFormButtonProperties ( "valider", BUTP_PROCHAIN_ECRAN, "Ald-3" );
	$Myform->setFormButtonProperties ( "valider", BUTP_CHECK_FORM, true );
	$Myform->setFormButtonProperties ( "annuler", BUTP_CHECK_FORM, false );
	
	// JavaScript
	// Initialisation de champs dèsque le champ mnt_octr est activé
	$js_mnt_reset = "function reset(id_doss) { \n";
	$js_mnt_reset .= "var cre_mnt_octr = 'cre_mnt_octr'+id_doss;\n";
	$js_mnt_reset .= "var mnt_assurance = 'mnt_assurance'+id_doss;\n";
	$js_mnt_reset .= "var mnt_commission = 'mnt_commission'+id_doss;\n";
	$js_mnt_reset .= "var gar_num ='gar_num'+id_doss;\n";
	$js_mnt_reset .= "var gar_mat ='gar_mat'+id_doss;\n";
	$js_mnt_reset .= "var gar_tot ='gar_tot'+id_doss;\n";
	$js_mnt_reset .= "var gar_num_encours ='gar_num_encours'+id_doss;\n";
	$js_mnt_reset .= "document.ADForm.eval(cre_mnt_octr).value ='';\n";
	$js_mnt_reset .= "document.ADForm.eval(mnt_assurance).value ='';\n";
	$js_mnt_reset .= "document.ADForm.eval(mnt_commission).value ='';\n";
	if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num"] > 0) {
		$js_mnt_reset .= "document.ADForm.eval(gar_num).value ='';\n";
	}
	if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_mat"] > 0) {
		$js_mnt_reset .= "document.ADForm.eval(gar_mat).value ='';\n";
	}
	if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_tot"] > 0) {
		$js_mnt_reset .= "document.ADForm.eval(gar_tot).value ='';\n";
	}
	if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num_encours"] > 0) {
		$js_mnt_reset .= "document.ADForm.eval(gar_num_encours).value ='';\n";
	}
	$js_mnt_reset .= "}\n";
	$Myform->addJS ( JSP_FORM, "js_mnt_reset", $js_mnt_reset );
	
	// Calule du montant de l'assurance, de la commission et des garanties en fonction du montant à octoyer
	$js_mnt_init = "function init(id_doss) { \n";
	$js_mnt_init .= "var cre_mnt_octr = 'cre_mnt_octr'+id_doss;\n";
	$js_mnt_init .= "var mnt_octr = 'mnt_octr'+id_doss;\n";
	$js_mnt_init .= "var mnt_assurance = 'mnt_assurance'+id_doss;\n";
	$js_mnt_init .= "var mnt_commission = 'mnt_commission'+id_doss;\n";
	$js_mnt_init .= "var gar_num ='gar_num'+id_doss;\n";
	$js_mnt_init .= "var gar_mat ='gar_mat'+id_doss;\n";
	$js_mnt_init .= "var gar_tot ='gar_tot'+id_doss;\n";
	$js_mnt_init .= "var gar_num_encours ='gar_num_encours'+id_doss;\n";
	
	$js_mnt_init .= "\t\t eval('document.ADForm.'+mnt_assurance).value = Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_assurance'] . "*parseFloat(recupMontant(eval('document.ADForm.'+cre_mnt_octr).value))*" . $precision_devise . ")/" . $precision_devise . ";\n";
	$js_mnt_init .= "\t\t\teval('document.ADForm.'+mnt_assurance).value =formateMontant(eval('document.ADForm.'+mnt_assurance).value);\n";
	$js_mnt_init .= "\t\t\t eval('document.ADForm.'+mnt_commission).value = Math.round((" . $SESSION_VARS ['infos_prod'] ['prc_commission'] . "* parseFloat(recupMontant(eval('document.ADForm.'+cre_mnt_octr).value))+ " . $SESSION_VARS ['infos_prod'] ['mnt_commission'] . ")*" . $precision_devise . ")/" . $precision_devise . ";\n";
	$js_mnt_init .= "\t\t\t eval('document.ADForm.'+mnt_commission).value =formateMontant( eval ('document.ADForm.'+mnt_commission).value);\n";
	if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num"] > 0) {
		$js_mnt_init .= "\t\t\t eval('document.ADForm.'+gar_num).value =Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_num'] . "* parseFloat(recupMontant( eval('document.ADForm.'+cre_mnt_octr).value))*" . $precision_devise . ")/" . $precision_devise . ";\n";
		// 2116
		$js_mnt_init .= "\t\t\t eval('document.ADForm.'+gar_num).value =formateMontant( eval('document.ADForm.'+gar_num).value);\n";
	}
	if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_mat"] > 0) {
		$js_mnt_init .= "\t\t\t eval('document.ADForm.'+gar_mat).value =Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_mat'] . "* parseFloat(recupMontant( eval('document.ADForm.'+cre_mnt_octr).value)));\n";
		$js_mnt_init .= "\t\t\t eval('document.ADForm.'+gar_mat).value =formateMontant( eval('document.ADForm.'+gar_mat).value);\n";
	}
	if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_tot"] > 0) {
		// $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_tot).value = Math.round(".$SESSION_VARS['infos_prod']['prc_gar_tot']."* parseFloat(recupMontant( eval('document.ADForm.'+cre_mnt_octr).value))*".$precision_devise.")/".$precision_devise.";\n";
		// $js_mnt_init .="\t\t\t eval('document.ADForm.'+gar_tot).value = formateMontant( eval('document.ADForm.'+gar_tot).value);\n";
	}
	if ($SESSION_VARS ['infos_doss'] [$id_doss] ["gar_num_encours"] > 0) {
		$js_mnt_init .= "\t\t\t eval('document.ADForm.'+gar_num_encours).value =Math.round(" . $SESSION_VARS ['infos_prod'] ['prc_gar_encours'] . "* parseFloat(recupMontant(eval('document.ADForm.'+cre_mnt_octr).value))*" . $precision_devise . ")/" . $precision_devise . ";\n";
		$js_mnt_init .= "\t\t\t eval('document.ADForm.'+gar_num_encours).value = formateMontant( eval('document.ADForm.'+gar_num_encours).value);\n";
	}
	$js_mnt_init .= "\t\t\t eval('document.ADForm.'+mnt_octr).value = recupMontant(eval('document.ADForm.'+cre_mnt_octr).value);\n";
	$js_mnt_init .= "}";
	$Myform->addJS ( JSP_FORM, "js_mnt_init", $js_mnt_init );
	
	$Myform->buildHTML ();
	echo $Myform->getHTML ();
} 

/*{{{ Ald-3 : Confirmation de l'annulation */
else if ($global_nom_ecran == "Ald-3") 
{	
	$moratoire = 0;
	$modif_id_doss_arr = array ();	
	$les_dossiers = '';
	
	foreach ( $SESSION_VARS ['infos_doss'] as $id_doss => $val_doss ) 
	{
		if (in_array ( $val_doss ["etat"], array (	15) )){			
			$modif_id_doss_arr [] = $id_doss;
			$DATA [$id_doss] ["etat"] = 5; // Etat du dossier
			$DATA [$id_doss] ["id_client"] = $val_doss ['id_client']; // Etat du dossier			
			$les_dossiers .= $id_doss . " ";
			$moratoire ++;
		} else {
			$html_err = new HTML_erreur ( _ ( "Echec lors du raccourcissement." ) . " " );
			$html_err->setMessage ( _ ( "Le raccourcissement n'a pas été annulé" ) . " : " . $error [$myErr->errCode] . $myErr->param );
			$html_err->addButton ( "BUTTON_OK", 'Gen-11' );
			$html_err->buildHTML ();
			echo $html_err->HTML_code;
		}
	}
	
	if ($moratoire != 0) 
	{
		// Annulation du racourcissement
		$myErr = annulerMoratoire ( $DATA, 150 ); // 150 = Annulation raccourcissement de la durée du crédit
		if ($myErr->errCode == NO_ERR) {
			
			if (is_array ( $modif_id_doss_arr ) && count ( $modif_id_doss_arr ) > 0) {
				foreach ( $modif_id_doss_arr as $key => $id_doss ) {
					$HisObj = new Historisation ();					
					$HisObj->deleteDossierHis ( $id_doss, Historisation::MOD_TYPE_RACCOURCI, 'f' );					
					unset ( $HisObj );
				}
			}
			
			$msg = new HTML_message ( _ ( "Confirmation annulation de raccourcissement" ) );
			if ($moratoire > 1)
				$msg->setMessage ( _ ( "Les raccourcissements ont été annulés pour les dossiers de crédit N° : $les_dossiers !" ) );
			else
				$msg->setMessage ( _ ( "Le raccourcissement a été annulé pour le dossier de crédit N° $les_dossiers !" ) );
			
			$msg->addButton ( BUTTON_OK, "Gen-11" );
			$msg->buildHTML ();
			echo $msg->HTML_code;
		}
	}
} 
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>