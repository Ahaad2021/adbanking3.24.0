<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Commande [41] et retrait [42] de chèquiers.
 * Ces fonctions appellent les écrans suivants :
 * - Chq-1 : Choix du compte client
 * - Chq-2 : Saisie des informations sur la commande du chèquiers et frais
 * - Chq-3 : Confirmation commande
 * - Rch-1 : Chèquiers disponibles au retrait
 * - Rch-2 : Remise du chèquier et décharge
 * - Rch-3 : Confirmation opération
 * TODO Il faut aussi une possibilité d'annuler une commande de chèquier ?
 *
 * @author Antoine Delvaux
 * @since 2.6
 * @package Clients
 **/

/*{{{ Chq-1 : Choix du compte client */
if ($global_nom_ecran == "Chq-1")  {
	// On construit la liste des comptes pour lesquels on peut commander un chèquier
	$liste_comptes = get_comptes_epargne($global_id_client);
	$choix = array();
	if (isset($liste_comptes)) {
		foreach($liste_comptes as $id_cpte => $infos_cpte) {
			$infos_prod = getProdEpargne($infos_cpte['id_prod']);
			$etatChqs_param=isNotDemandeChequier($id_cpte);
			$is_not_dmde=false;debug($etatChqs_param);
			if($etatChqs_param->errCode == NO_ERR ) $is_not_dmde=true ;

			if ($infos_prod["classe_comptable"] == '1' && $infos_cpte["etat_cpte"] != '3' && ($is_not_dmde)) {
				// C'est un compte à vue, il n'est pas bloqué et aucune demande de chèquier n'est en cours
				$choix[$id_cpte] = $infos_cpte["num_complet_cpte"]." ".$infos_cpte["intitule_compte"];
			}
		}
	}

	// Création du formulaire
	$my_page = new HTML_GEN2(_("Commande de chèquier : choix du compte d'épargne"));
	$my_page->addField("num_cpte", _("Numéro de compte"), TYPC_LSB);
	$my_page->setFieldProperties("num_cpte", FIELDP_ADD_CHOICES, $choix);
	$my_page->setFieldProperties("num_cpte",FIELDP_IS_REQUIRED, true);

	// Boutons
	$my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
	$my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Chq-2");
	$my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
	$my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-4");
	$my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
	$my_page->show();
}
/*}}}*/

/*{{{ Chq-2 : Saisie des informations chèquiers et frais */
else if ($global_nom_ecran == "Chq-2") {
	// De quel écran vient-on et quelles variables doit-on sauvegarder ?
	if ($global_nom_ecran_prec == "Chq-1")
	$SESSION_VARS["id_cpte"] = $num_cpte;

	// Informations sur le compte et le produit
	$infos_cpte = get_compte_epargne_info($SESSION_VARS["id_cpte"]);
	$infos_prod = getProdEpargne($infos_cpte['id_prod']);

	// Met la monnaie courante dans le devise
	setMonnaieCourante($infos_cpte['devise']);

	// Création du formulaire
	$my_page = new HTML_GEN2(_("Commande de chèquier"));
	$my_page->addField("NumCpte", _("Numéro de compte"), TYPC_TXT);
	$my_page->setFieldProperties("NumCpte", FIELDP_IS_LABEL, true);
	$my_page->setFieldProperties("NumCpte", FIELDP_DEFAULT,$infos_cpte["num_complet_cpte"]);

	$my_page->addField("nbre_carnets", _("Nombre de carnets"), TYPC_INT);
	$my_page->setFieldProperties("nbre_carnets", FIELDP_DEFAULT, 1);
	$my_page->setFieldProperties("nbre_carnets", FIELDP_IS_REQUIRED, TRUE);

	$my_page->addField("frais_chequier", _("Frais de commande de chèquier"), TYPC_MNT);
	$my_page->setFieldProperties("frais_chequier", FIELDP_IS_LABEL, TRUE);
	$my_page->setFieldProperties("frais_chequier", FIELDP_DEFAULT, $infos_prod["frais_chequier"]);
	$codejs = "function setFraisChequier() {
  		      document.ADForm.frais_chequier.value =  eval(document.ADForm.nbre_carnets.value )  * '". $infos_prod["frais_chequier"]."';	 
          }";
	$my_page->setFieldProperties("nbre_carnets", FIELDP_JS_EVENT, array("onChange"=>"setFraisChequier();"));
	$my_page->addJS(JSP_FORM, "JS1", $codejs);
	$my_page->addHiddenType('frais_chequier_unitaire',$infos_prod["frais_chequier"]);

	if (check_access(299)) {
		$my_page->setFieldProperties("frais_chequier", FIELDP_CAN_MODIFY, true);
	}

	// enable the field so that it is posted
	$js_mnt_frais = " if(ADFormValid == true) \n\t\t document.ADForm.frais_chequier.removeAttribute('disabled'); \n";
	$my_page->addJS(JSP_END_CHECK,"js_mnt_frais", $js_mnt_frais);

	//Boutons
	$my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
	$my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Chq-3");
	$my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
	$my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-4");
	$my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
	$my_page->show();
} /*}}}*/

/*{{{ Chq-3 : Confirmation commande */
else if ($global_nom_ecran == "Chq-3") {
	if($nbre_carnets > 0 ) {

		$frais_chequier = $_POST['frais_chequier'];

		if(empty($frais_chequier)) {
			$frais_chequier = recupMontant($frais_chequier_unitaire) * $nbre_carnets;
		}

		$result = doCommandeChequier($SESSION_VARS["id_cpte"], $nbre_carnets, recupMontant($frais_chequier));

		if ($result->errCode == NO_ERR) {
			$my_page =new HTML_message(_("Confirmation commande"));
			$my_page->setMessage(sprintf(_("La commande d'un nouveau chèquier de %s chèques a été introduite."),$nbr_cheques),true);
			$my_page->addButton("BUTTON_OK", 'Gen-4');
		} else {
			$my_page = new HTML_erreur(_("Echec lors de la commande d'un nouveau chèquier "));
			$my_page->setMessage(_("Erreur")." : ".$error[$result->errCode]."<br />"._("Paramètre")." : ".$result->param);
			$my_page->addButton("BUTTON_OK", 'Chq-1');
		}
		$my_page->show();
	} else {
		$my_page = new HTML_erreur(_("Echec lors de la commande d'un nouveau chèquier "));
		$my_page->setMessage(_("Le champs Nombre de carnets doit être supérieur à zero"));
		$my_page->addButton("BUTTON_OK", 'Chq-1');
		$my_page->show();
		exit();

	}
} /*}}}*/

/*{{{ Rch-1 : Chèquiers disponibles au retrait */
else if ($global_nom_ecran == "Rch-1") {
	// On construit la liste des chéquiers pour un client donnée qui sont à l'état =0
	$result = getListChequiers($global_id_client,0);
	$SESSION_VARS["id_cpte"]=NULL;
	if ($result->errCode == NO_ERR) {
		$liste_chiquiers = $result->param;
		if (count($liste_chiquiers) > 0) {
			// Nous avons des chèquiers disponibles
			$choix = array();
			foreach ($liste_chiquiers as $id => $chequier) {
				$infos_cpte = getAccountDatas($chequier['id_cpte']);
				$id_chequier=$chequier['id_chequier'];
				$choix[$id_chequier] = $infos_cpte["num_complet_cpte"]." ".$infos_cpte["intitule_compte"]." N°Chequier".$id_chequier ;
			}
			$my_page = new HTML_GEN2(_("Retrait de chèquier"));
			$my_page->addField("num_chequier", _("Numéro de compte"), TYPC_LSB);
			$my_page->setFieldProperties("num_chequier", FIELDP_ADD_CHOICES, $choix);
			$my_page->setFieldProperties("num_chequier",FIELDP_IS_REQUIRED, true);

			$my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
			$my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Rch-2");
			$my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
			$my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-4");
			$my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
			$my_page->addHiddenType("num_cpte",$chequier['id_cpte']);
			$my_page->addHiddenType("num_complet_cpte",$infos_cpte["num_complet_cpte"]);
		} else {
			// Aucun chèquier n'est diponible
			$my_page =new HTML_message(_("Aucun chèquier"));
			$my_page->setMessage(sprintf(_("Aucun chèquier n'est disponible.")),true);
			// TODO Il serait utile de montrer l'état des commandes en cours, le cas échéant.
			$my_page->addButton("BUTTON_OK", 'Gen-4');
		}
	} else {
		// Erreur d'exécution
		$my_page = new HTML_erreur(_("Echec lors de la visualisation des chèquiers au retrait."));
		$my_page->setMessage(_("Erreur")." : ".$error[$result->errCode]."<br />"._("Paramètre")." : ".$result->param);
		$my_page->addButton("BUTTON_OK", 'Gen-4');
	}
	$my_page->show();
} /*}}}*/

/*{{{ Rch-2 : Remise du chèquier et décharge */
else if ($global_nom_ecran == "Rch-2") {
	// De quel écran vient-on et quelles variables doit-on sauvegarder ?
	//$infos_cpte = getAccountDatas($num_cpte);
	$SESSION_VARS["id_cpte"]=$num_cpte;
	$infos_chequier_param=getChequiers($num_chequier);
	$infos_chequier=$infos_chequier_param->param[0];

	// Impression de la décharge TODO

	// Demande de confirmation
	$my_page = new HTML_GEN2(_("Remise du chèquier"));
	$my_page->addField("NumCpte", _("Numéro de compte"), TYPC_TXT);
	$my_page->setFieldProperties("NumCpte", FIELDP_IS_LABEL, true);
	$my_page->setFieldProperties("NumCpte", FIELDP_DEFAULT, $num_complet_cpte);
	$my_page->addField("num_chequier", _("Numéro du  chèquier"), TYPC_TXT);
	$my_page->setFieldProperties("num_chequier", FIELDP_IS_LABEL, true);
	$my_page->setFieldProperties("num_chequier", FIELDP_DEFAULT,$num_chequier);
	$my_page->addField("num_premier_cheque", _("Numéro du premier chèque"), TYPC_TXT);
	$my_page->setFieldProperties("num_premier_cheque", FIELDP_IS_LABEL, true);
	$my_page->setFieldProperties("num_premier_cheque", FIELDP_DEFAULT, $infos_chequier["num_first_cheque"]);
	$my_page->addField("num_dernier_cheque", _("Numéro du dernier chèque"), TYPC_TXT);
	$my_page->setFieldProperties("num_dernier_cheque", FIELDP_IS_LABEL, true);
	$my_page->setFieldProperties("num_dernier_cheque", FIELDP_DEFAULT, $infos_chequier["num_last_cheque"]);

	$my_page->addHiddenType("num_chequier_hidden",$num_chequier);

	//Boutons
	$my_page->addFormButton(1,1,"valid", _("Confirmer la remise du chèquier"), TYPB_SUBMIT);
	$my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Rch-3");
	$my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
	$my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-4");
	$my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
	$my_page->show();

} /*}}}*/

/*{{{ Rch-3 : Confirmation opération */
else if ($global_nom_ecran == "Rch-3") {
	debug($num_chequier_hidden);
	$result = setChequierRemis($num_chequier_hidden);

	if ($result->errCode == NO_ERR) {
		global $global_id_agence, $global_nom_login, $global_id_client;
		// Enregistrement - Retrait chéquier
		ajout_historique(42, $global_id_client, 'Retrait chéquier', $global_nom_login, date("r"), NULL);
		
		$infos_cpte = getAccountDatas($SESSION_VARS["id_cpte"]);
		$info_chequier=getChequiers($num_chequier_hidden);
		$nbre_cheque = $info_chequier->param[0]["num_last_cheque"] -$info_chequier->param[0]["num_first_cheque"] +1;
		debug($info_chequier);
		unset($SESSION_VARS["id_cpte"]);
		$my_page =new HTML_message(_("Confirmation de la remise"));
		$my_page->setMessage(sprintf(_("La remise d'un chèquier de %s chèques pour le compte<br>%s (%s)<br>est confirmée."), $nbre_cheque, $infos_cpte["intitule_compte"], $infos_cpte["num_complet_cpte"]),true);

		$result = getListChequiers($global_id_client,0);
		if ($result->errCode == NO_ERR) {
			$liste_comptes = $result->param;
			if (count($liste_comptes) > 0)
			// Il y a d'autres chèquiers à remettre
			$my_page->addButton("BUTTON_OK", 'Rch-1');
			else
			$my_page->addButton("BUTTON_OK", 'Gen-4');
		}
	} else {
		$my_page = new HTML_erreur(_("Echec lors de la remise d'un chèquier "));
		$my_page->setMessage(_("Erreur")." : ".$error[$result->errCode]."<br />"._("Paramètre")." : ".$result->param);
		$my_page->addButton("BUTTON_OK", 'Rch-1');
		unset($SESSION_VARS["id_cpte"]);
	}
	$my_page->show();
}
/*}}}*/

/*{{{ Och-1 : Mis en opposition */
else if ($global_nom_ecran == "Och-1") {

	// On construit la liste des  chèquiers (statut = 1)
	$result = getListChequiers($global_id_client,NULL,1);
	if ($result->errCode == NO_ERR) {
		$liste_chiquiers = $result->param;
		if (count($liste_chiquiers) > 0) {
			// Nous avons des chèquiers disponibles
			$choix = array();
			foreach ($liste_chiquiers as $id => $chequier) {
				//$infos_cpte = getAccountDatas($chequier['id_cpte']);
				$id_chequier=$chequier['id_chequier'];
				$choix[$id_chequier] = " N°Chequier ".$id_chequier." [".$chequier['num_first_cheque']." - ".$chequier['num_last_cheque']."]" ;
			}
			$my_page = new HTML_GEN2(_("Mise en opposition chèquier/chèque"));
			$choix1[1]=_("chèque");
			$choix1[2]=_("chèquier");
			$my_page->addField("opposition", _("Mise en opposition"), TYPC_LSB);
			$my_page->setFieldProperties("opposition", FIELDP_ADD_CHOICES, $choix1);
			$my_page->setFieldProperties("opposition",FIELDP_IS_REQUIRED, true);
			$my_page->setFieldProperties("opposition",FIELDP_JS_EVENT, array("OnChange"=>"check_type_selection()"));

			$my_page->addField("num_chequier", _("Numéro chèquier"), TYPC_LSB);
			$my_page->setFieldProperties("num_chequier", FIELDP_ADD_CHOICES, $choix);
			//$my_page->setFieldProperties("num_chequier",FIELDP_IS_REQUIRED, true);
			$my_page->addField("num_cheque", _("Numéro du  chèque"), TYPC_INT);
			$my_page->setFieldProperties("num_cheque", FIELDP_IS_LABEL, false);
			//$my_page->setFieldProperties("num_cheque", FIELDP_DEFAULT, $num_last_cheque);
			global $adsys ;
			$etat_cheque=$adsys["adsys_etat_cheque"];
			unset($etat_cheque[1],$etat_cheque[4]);

			$my_page->addField("etat_cheque", _("Etat chèque"), TYPC_LSB);
			$my_page->setFieldProperties("etat_cheque", FIELDP_ADD_CHOICES, $etat_cheque);

			$my_page->addField("description", _("Description"), TYPC_ARE);
			$my_page->setFieldProperties("description", FIELDP_WIDTH, 50);

			$my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
			$my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Och-2");
			$my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
			$my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-4");
			$my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
			$my_page->addHiddenType("num_cpte",$chequier['id_cpte']);
			$my_page->addHiddenType("num_complet_cpte",$infos_cpte["num_complet_cpte"]);
			/* Contrôle des champs à renseigner selon le type d'opposition */
			$JS_valide ="";
			$JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_opposition.value == 2 && document.ADForm.HTML_GEN_LSB_num_chequier.value == 0)";
			$JS_valide .="\n\t{msg+='"._("Le Champ \"Numéro chèquier\" doit être renseigné")."'; ADFormValid = false;}";

			/* Contrôle des champs à renseigner selon le type d'opposition */

			$JS_valide .="\n\tif(document.ADForm.HTML_GEN_LSB_opposition.value == 1 && document.ADForm.num_cheque.value == '')";
			$JS_valide .="\n\t{msg+='"._("Le Champ \"Numéro chèque\" doit être renseigné")."'; ADFormValid = false;}";

			$my_page->addJS(JSP_BEGIN_CHECK, "valJS", $JS_valide);

			$JS_active =" ";
			$JS_active .="\nfunction check_type_selection()";
			$JS_active .="\n{";
			$JS_active .="\tif(document.ADForm.HTML_GEN_LSB_opposition.value == 1)";
			$JS_active .="\n\t{";
			$JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_num_chequier.disabled = true;";
			$JS_active .="\ndocument.ADForm.HTML_GEN_LSB_num_chequier.selectedIndex=0;";
			$JS_active .="\n\tdocument.ADForm.num_cheque.value = '';";
			$JS_active .="\n\tdocument.ADForm.num_cheque.disabled = false;";
			$JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_cheque.disabled = false;";
			$JS_active .="\n\t}";
			$JS_active .="\n\telse if(document.ADForm.HTML_GEN_LSB_opposition.value == 2)";
			$JS_active .="\n\t{";
			$JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_num_chequier.disabled = false;";
			$JS_active .="\n\tdocument.ADForm.num_cheque.value = '';";
			$JS_active .="\n\tdocument.ADForm.num_cheque.disabled = true;";
			$JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_cheque.selectedIndex=0;";
			$JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_cheque.disabled = true;";
			$JS_active .="\n\t}";
			$JS_active .="\n\telse ";
			$JS_active .="\n\t{";
			$JS_active .="\n\tdocument.ADForm.HTML_GEN_LSB_num_chequier.disabled = true;";
			$JS_active .="\ndocument.ADForm.HTML_GEN_LSB_num_chequier.selectedIndex=0;";
			$JS_active .="\ndocument.ADForm.HTML_GEN_LSB_etat_cheque.selectedIndex=0;";
			$JS_active .="\n\tdocument.ADForm.num_cheque.value = '';";
			$JS_active .="\n\tdocument.ADForm.num_cheque.disabled = true;";
			$JS_active .="\n\t}";
			$JS_active .="\n}";
			$my_page->addJS(JSP_FORM,"check", $JS_active);
			$my_page->show();
		} else {
			// Aucun chèquier n'est diponible
			$my_page_msg =new HTML_message(_("Aucun chèquier"));
			$my_page_msg->setMessage(sprintf(_("Aucun chèquier n'est disponible.")),true);
			// TODO Il serait utile de montrer l'état des commandes en cours, le cas échéant.
			$my_page_msg->addButton("BUTTON_OK", 'Gen-4');
			$my_page_msg->show();
			exit(0);
		}
	} else {
		// Erreur d'exécution
		$my_page_err = new HTML_erreur(_("Echec lors de la visualisation des chèquiers au retrait."));
		$my_page_err->setMessage(_("Erreur")." : ".$error[$result->errCode]."<br />"._("Paramètre")." : ".$result->param);
		$my_page_err->addButton("BUTTON_OK", 'Gen-4');
		$my_page_err->show();
		exit(0);
	}
	$JS_active = "";

}
/*}}}*/
/*{{{ Och-3 : Confirmation opération */
else if ($global_nom_ecran == "Och-2") {
	$data=array();debug($description);
	if($opposition == 1 ) {
		$data["id_cheque"]=$num_cheque;
		$data["etat_cheque"]=$etat_cheque;
		$data["description"]=$description;
		$msg=sprintf(_("Le chèque numéro  %s a été mise en opposition ."), $num_cheque);
	} elseif ($opposition == 2 ) {
		$msg=sprintf(_("Le chèquier numéro  %s a été mise en opposition ."), $num_chequier);
		$data["id_chequier"]=$num_chequier;
		$data["description"]=$description;
	}

	$result = opposeChequier($opposition,$data,$comptable);

	if ($result->errCode == NO_ERR) {
		global $global_id_agence, $global_nom_login, $global_id_client;
		// Enregistrement - Mise en opposition chèque / chèquier
		ajout_historique(45, $global_id_client, 'Mise en opposition chèque / chèquier', $global_nom_login, date("r"), $comptable);
		
		$my_page =new HTML_message(_("Confirmation de la mise en opposition"));
		$my_page->setMessage($msg,true);
		$my_page->addButton("BUTTON_OK", 'Gen-4');
	} else {
		$my_page = new HTML_erreur(_("Echec lors de la mise en oppostion d'un chèquier "));
		$my_page->setMessage(_("Erreur")." : ".$error[$result->errCode]."<br />"._("Paramètre")." : ".$result->param);
		$my_page->addButton("BUTTON_OK", 'Och-1');
		 
	}
	$my_page->show();
}
/*}}}*/
else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>