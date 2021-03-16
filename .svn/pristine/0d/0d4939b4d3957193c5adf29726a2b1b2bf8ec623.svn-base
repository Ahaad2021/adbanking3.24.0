<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [88] Modification des informations d'un compte client
 * Cette opération comprends les écrans :
 * - Mdc-1 : Sélection d'un compte épargne
 * - Mdc-2 : Modification des informations
 * - Mdc-3 : affichage des informations et Frais de dossier du découvert
 * - Mdc-4 : Confirmation modification du compte
 * @package Epargne
 */
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/epargne.php';

/*{{{ Mdc-1 : Sélection d'un compte épargne */
if ($global_nom_ecran == "Mdc-1") {
  // On construit la liste des comptes pour lesquels on peut donner un découvert
  $liste_comptes = get_comptes_epargne($global_id_client);
  $choix = array();
  if (isset($liste_comptes)) {
    foreach($liste_comptes as $id_cpte => $infos_cpte) {
      $infos_prod = getProdEpargne($infos_cpte['id_prod']);
      if (($infos_prod["classe_comptable"] == '1' OR $infos_prod["classe_comptable"] == '6') && $infos_cpte["etat_cpte"] != '3') {
        // C'est un compte à vue et il n'est pas bloqué
        $choix[$id_cpte] = $infos_cpte["num_complet_cpte"]." ".$infos_cpte["intitule_compte"];
      }
    }
  }

  // Création du formulaire
  $my_page = new HTML_GEN2(_("Choix du compte"));
  $my_page->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);
  $my_page->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);
  $my_page->setFieldProperties("NumCpte",FIELDP_IS_REQUIRED, true);

  // Boutons
  $my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Mdc-2");
  $my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-10");
  $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
  $my_page->show();
} /*}}}*/

/*{{{ Mdc-2 : Modification des informations */
else if ($global_nom_ecran == "Mdc-2") {
  // De quel écran vient-on et quelles variables doit-on sauvegarder ?
  if ($global_nom_ecran_prec == "Mdc-1")
    $SESSION_VARS["id_cpte"] = $NumCpte;

  // Informations sur le compte et le produit
  $infos_cpte = get_compte_epargne_info($SESSION_VARS["id_cpte"]);
  $infos_prod = getProdEpargne($infos_cpte['id_prod']);
  //recupération des infos sur l'agence
  $info_agence = getAgenceDatas($global_id_agence);

  // Met la monnaie courante dans le devise
  setMonnaieCourante($infos_cpte['devise']);

  // Création du formulaire
  $my_page = new HTML_GEN2(_("Modification du compte"));
  $my_page->addField("NumCpte", _("Numéro de compte"), TYPC_TXT);
  $my_page->setFieldProperties("NumCpte", FIELDP_IS_LABEL, true);
  $my_page->setFieldProperties("NumCpte", FIELDP_DEFAULT,$infos_cpte["num_complet_cpte"]);
	$my_page->addField("intituleCompte", _("Intitulé du compte"), TYPC_TXT);
	$my_page->setFieldProperties("intituleCompte", FIELDP_DEFAULT,$infos_cpte["intitule_compte"]);
  $my_page->addField("decouvert_cpt", _("Découvert maximum autorisé"), TYPC_MNT);
  $my_page->setFieldProperties("decouvert_cpt", FIELDP_DEFAULT,$infos_cpte["decouvert_max"]);
  $my_page->setFieldProperties("decouvert_cpt", FIELDP_IS_LABEL, true);
  $my_page->addField("mnt_min_cpte", _("Montant minimum du compte"), TYPC_MNT);

  $my_page->setFieldProperties("mnt_min_cpte", FIELDP_DEFAULT,$infos_cpte["mnt_min_cpte"]);
  $my_page->setFieldProperties("mnt_min_cpte", FIELDP_IS_LABEL, true);
  $my_page->addField("decouvert_new", _("Nouveau découvert maximum demandé"), TYPC_MNT);
  $my_page->addField("mnt_min_new", _("Nouveau montant minimum du compte"), TYPC_MNT);

    $export_netbank = false;
    $my_page->addField("export_netbank", _("Compte Netbank ?"), TYPC_BOL);
  //modification pour export Netbank
  if($info_agence['utilise_netbank'] == 't'){
  	if($infos_cpte['export_netbank'] == 't'){
		$export_netbank = true;	
  	}
  	//on les stocke dans des variables session	
	$SESSION_VARS["utilise_netbank"] = $info_agence['utilise_netbank'];
  }
  $my_page->setFieldProperties("export_netbank", FIELDP_DEFAULT, $export_netbank);
  $SESSION_VARS["export_netbank"] = $export_netbank;
  //Compte de versement des intêréts

  $CPTS = getAccounts($global_id_client);

  $CPT_AFF = array();
  $CPT_AFF[$infos_cpte["id_cpte"]]=_("Compte lui-meme");
  while (list($key, $CPT) = each($CPTS)) {
    /* On n ajoute que les comptes de service financiers = t, sans terme et de meme devise que le produit  */
    if (($CPT["service_financier"] == 't') && $CPT["devise"] == $infos_prod["devise"]&& $infos_cpte["id_cpte"]!=$CPT["id_cpte"])
      $CPT_AFF[$CPT["id_cpte"]]=$CPT["num_complet_cpte"]." ".$CPT["intitule_compte"];
  }

  /* Gestion du compte de versement des intérêts */
  $my_page->addField("cpt_vers_int", _("Compte de versement des Intérêts"), TYPC_LSB);
  if ($infos_prod["tx_interet"] > 0) {
    $my_page->setFieldProperties("cpt_vers_int", FIELDP_ADD_CHOICES, $CPT_AFF);
    $my_page->setFieldProperties("cpt_vers_int", FIELDP_IS_REQUIRED, true);
    $my_page->setFieldProperties("cpt_vers_int", FIELDP_DEFAULT,$infos_cpte["cpt_vers_int"]);
  } else {
    $my_page->setFieldProperties("cpt_vers_int", FIELDP_IS_LABEL, true);
  }
  /* Gestion du compte de virement à la clôture des intérêts */
  $my_page->addField("cpte_virement_clot", _("Compte de virement à la clôture"), TYPC_LSB);
  $my_page->setFieldProperties("cpte_virement_clot", FIELDP_ADD_CHOICES, $CPT_AFF);
  $my_page->setFieldProperties("cpte_virement_clot", FIELDP_EXCLUDE_CHOICES, array($infos_cpte["id_cpte"]));
  $my_page->setFieldProperties("cpte_virement_clot", FIELDP_DEFAULT,$infos_cpte["cpte_virement_clot"]);

  //Fin compte de versement des intérêts
  if ($global_nom_ecran_prec == "Mdc-3") {
    $my_page->setFieldProperties("decouvert_new", FIELDP_DEFAULT,$SESSION_VARS["decouvert_new"]);
    $my_page->setFieldProperties("mnt_min_new", FIELDP_DEFAULT,$SESSION_VARS["mnt_min_new"]);
    $my_page->setFieldProperties("export_netbank", FIELDP_DEFAULT,$SESSION_VARS["export_netbank"]);
  } else {
    if ($infos_cpte["decouvert_max"]==0 || $infos_cpte["decouvert_max"]=="")
      $my_page->setFieldProperties("decouvert_new", FIELDP_DEFAULT,$infos_prod["decouvert_max"]);
    else
      $my_page->setFieldProperties("decouvert_new", FIELDP_DEFAULT,$infos_cpte["decouvert_max"]);
    $my_page->setFieldProperties("mnt_min_new", FIELDP_DEFAULT,$infos_cpte["mnt_min_cpte"]);
  }
  $my_page->setFieldProperties("decouvert_new", FIELDP_IS_REQUIRED, true);
  $my_page->setFieldProperties("mnt_min_new", FIELDP_IS_REQUIRED, true);
  $my_page->addField("solde_dispo", _("Solde disponible (hors découvert)"), TYPC_MNT);
  $my_page->setFieldProperties("solde_dispo", FIELDP_DEFAULT,getSoldeDisponible($SESSION_VARS["id_cpte"]) - $infos_cpte["decouvert_max"]);
  $my_page->setFieldProperties("solde_dispo", FIELDP_IS_LABEL, true);
  $my_page->addField("decouvert_date_util", _("Date de début d'utilisation du découvert"), TYPC_DTE);
  $my_page->setFieldProperties("decouvert_date_util", FIELDP_DEFAULT,$infos_cpte["decouvert_date_util"]);
  $my_page->setFieldProperties("decouvert_date_util", FIELDP_IS_LABEL, true);

  //Boutons
  $my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Mdc-3");
  $my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-10");
  $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
  $my_page->show();
} /*}}}*/

/*{{{ Mdc-3 : affichage des informations et Frais de dossier du découvert */
else if ($global_nom_ecran == "Mdc-3") {
  // Sauvegarde du découvert encodé
  $SESSION_VARS["decouvert_new"] = recupMontant($decouvert_new);
  $SESSION_VARS["mnt_min_new"] = recupMontant($mnt_min_new);
  $SESSION_VARS["intitule_compte"] = $intituleCompte;
  $SESSION_VARS["cpte_vers_int"] = $HTML_GEN_LSB_cpt_vers_int;
  $SESSION_VARS["cpte_virement_clot"] = $HTML_GEN_LSB_cpte_virement_clot;
  //Assignation de valeur à export netbank
  if($export_netbank == ""){
  	$SESSION_VARS["export_netbank"] = 'f';
  } else {
  	$SESSION_VARS["export_netbank"] = 't';
  }
  // Informations sur le compte et le produit
  $infos_cpte = get_compte_epargne_info($SESSION_VARS["id_cpte"]);
  $infos_prod = getProdEpargne($infos_cpte['id_prod']);

  // Met la monnaie courante dans le devise
  setMonnaieCourante($infos_cpte['devise']);

  // Création du formulaire
  $my_page = new HTML_GEN2(_("Informations à modifier et frais de dossier découvert"));

  $my_page->addField("intitule_compte", _("Intitulé de compte actuel"), TYPC_TXT);
  $my_page->setFieldProperties("intitule_compte", FIELDP_DEFAULT, $infos_cpte["intitule_compte"]);
  $my_page->setFieldProperties("intitule_compte", FIELDP_IS_LABEL, true);
  $my_page->addField("intitule_compte_new", _("Nouveau intitulé de compte"), TYPC_TXT);
  $my_page->setFieldProperties("intitule_compte_new", FIELDP_DEFAULT, $SESSION_VARS["intitule_compte"]);
  $my_page->setFieldProperties("intitule_compte_new", FIELDP_IS_LABEL, true);

  $my_page->addField("decouvert", _("Découvert actuel"), TYPC_MNT);
  $my_page->setFieldProperties("decouvert", FIELDP_DEFAULT, $infos_cpte["decouvert_max"]);
  $my_page->setFieldProperties("decouvert", FIELDP_IS_LABEL, true);
  $my_page->addField("decouvert_new", _("Nouveau découvert maximum demandé"), TYPC_MNT);
  $my_page->setFieldProperties("decouvert_new", FIELDP_DEFAULT, $SESSION_VARS["decouvert_new"]);
  $my_page->setFieldProperties("decouvert_new", FIELDP_IS_LABEL, true);
  //pour l'export Netbank
  if($SESSION_VARS["utilise_netbank"] == 't'){
  	$my_page->addField("export_netbank", _("Export Netbank actuel"), TYPC_BOL);
  	if($infos_cpte['export_netbank'] == 't'){
  		$my_page->setFieldProperties("export_netbank", FIELDP_DEFAULT, true);
  	} else {
  		$my_page->setFieldProperties("export_netbank", FIELDP_DEFAULT, false);
  	}
  	$my_page->setFieldProperties("export_netbank", FIELDP_IS_LABEL, true);
  	$my_page->addField("export_netbank_new", _("Export Netbank demandé"), TYPC_BOL);
  	$my_page->setFieldProperties("export_netbank_new", FIELDP_DEFAULT, $export_netbank);
  	$my_page->setFieldProperties("export_netbank_new", FIELDP_IS_LABEL, true);
  }  
  $my_page->addField("mnt_min", _("Montant minimum actuel"), TYPC_MNT);
  $my_page->setFieldProperties("mnt_min", FIELDP_DEFAULT, $infos_cpte["mnt_min_cpte"]);
  $my_page->setFieldProperties("mnt_min", FIELDP_IS_LABEL, true);
  $my_page->addField("mnt_min_new", _("Nouveau montant minimum"), TYPC_MNT);
  $my_page->setFieldProperties("mnt_min_new", FIELDP_DEFAULT, $SESSION_VARS["mnt_min_new"]);
  $my_page->setFieldProperties("mnt_min_new", FIELDP_IS_LABEL, true);
  $my_page->addField("frais_decouvert_prc", _("Frais de dossier de découvert en pourcentage"), TYPC_PRC);
  $my_page->setFieldProperties("frais_decouvert_prc",FIELDP_JS_EVENT,array("OnChange"=>"calcul_frais();"));
  $my_page->addField("frais_decouvert", _("Frais de dossier de découvert forfaitaires"), TYPC_MNT);
  if (recupMontant($decouvert_new)==0 || $infos_cpte["decouvert_max"]==$SESSION_VARS["decouvert_new"]) {
    $my_page->setFieldProperties("frais_decouvert_prc",FIELDP_IS_LABEL,true);
    $my_page->setFieldProperties("frais_decouvert",FIELDP_IS_LABEL,true);
  }
  $my_page->setFieldProperties("frais_decouvert",FIELDP_JS_EVENT,array("OnChange"=>"calcul_frais();"));
  $my_page->addField("total_frais_decouvert", _("Total frais de dossier de découvert"), TYPC_MNT);
  $my_page->setFieldProperties("total_frais_decouvert", FIELDP_IS_LABEL, true);
  $my_page->addField("decouvert_new_total", _("Nouveau découvert maximum autorisé"), TYPC_MNT);
  $my_page->setFieldProperties("decouvert_new_total", FIELDP_IS_LABEL, true);

  if ($SESSION_VARS["decouvert_new"] > $infos_cpte["decouvert_max"]) {
    // On va prélever des frais de découvert
    $my_page->setFieldProperties("frais_decouvert_prc", FIELDP_DEFAULT, $infos_prod["decouvert_frais_dossier_prc"] * 100);
    $my_page->setFieldProperties("frais_decouvert", FIELDP_DEFAULT, $infos_prod["decouvert_frais_dossier"]);
    $my_page->setFieldProperties("total_frais_decouvert", FIELDP_DEFAULT, $infos_prod["decouvert_frais_dossier"] + $SESSION_VARS["decouvert_new"] * $infos_prod["decouvert_frais_dossier_prc"]);
  }
   $CPTS = getAccounts($global_id_client);
  $CPT_AFF = array();
  while (list($key, $CPT) = each($CPTS)) {
      $CPT_AFF[$CPT["id_cpte"]]=$CPT["num_complet_cpte"]." ".$CPT["intitule_compte"];
  }
  
  $my_page->addField("cpte_virement_clot", _("Compte de virement à la clôture"), TYPC_LSB);
  $my_page->setFieldProperties("cpte_virement_clot", FIELDP_ADD_CHOICES, $CPT_AFF);
  $my_page->setFieldProperties("cpte_virement_clot", FIELDP_IS_LABEL, true);
  $my_page->setFieldProperties("cpte_virement_clot", FIELDP_DEFAULT,$SESSION_VARS["cpte_virement_clot"]);

  // Code javascript pour mettre à jour les frais totaux de découvert
  $js = "\nfunction calcul_frais()\n";
  $js .= " { document.ADForm.total_frais_decouvert.value = formateMontant( parseFloat(document.ADForm.frais_decouvert_prc.value) * parseFloat(recupMontant(document.ADForm.decouvert_new.value)) / 100 + recupMontant(document.ADForm.frais_decouvert.value));
         document.ADForm.decouvert_new_total.value = formateMontant((parseFloat(document.ADForm.frais_decouvert_prc.value) * parseFloat(recupMontant(document.ADForm.decouvert_new.value)) / 100 + recupMontant(document.ADForm.frais_decouvert.value)) + parseFloat(recupMontant(document.ADForm.decouvert_new.value)));
       }";
  $my_page->addJS(JSP_FORM,"calcul",$js);

  //Boutons
  $my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Mdc-4");
  $my_page->addFormButton(1,2,"prec", _("Précédent"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, "Mdc-2");
  $my_page->setFormButtonProperties("prec", BUTP_CHECK_FORM, false);
  $my_page->addFormButton(1,3,"annul", _("Annuler"), TYPB_SUBMIT);
  $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-10");
  $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
  $my_page->show();
} /*}}}*/

/*{{{ Mdc-4 : Confirmation modification du compte */
else if ($global_nom_ecran == "Mdc-4") {
  // Montant total Mdcouvert
  $SESSION_VARS["decouvert_new_total"] = ((($frais_decouvert_prc * $SESSION_VARS["decouvert_new"] / 100) + recupMontant($frais_decouvert)) + $SESSION_VARS["decouvert_new"]);

  if($SESSION_VARS["utilise_netbank"] == 't'){
  	$result = updateInfosCompte($SESSION_VARS["intitule_compte"], $SESSION_VARS["decouvert_new_total"], $SESSION_VARS["id_cpte"], $frais_decouvert_prc * $SESSION_VARS["decouvert_new"] / 100 + recupMontant($frais_decouvert),$SESSION_VARS["mnt_min_new"],$SESSION_VARS["cpte_vers_int"],$SESSION_VARS["cpte_virement_clot"], $SESSION_VARS["export_netbank"]);
  } else {
  	$result = updateInfosCompte($SESSION_VARS["intitule_compte"], $SESSION_VARS["decouvert_new_total"], $SESSION_VARS["id_cpte"], $frais_decouvert_prc * $SESSION_VARS["decouvert_new"] / 100 + recupMontant($frais_decouvert),$SESSION_VARS["mnt_min_new"],$SESSION_VARS["cpte_vers_int"],$SESSION_VARS["cpte_virement_clot"]);
  }

//  $result = updateInfosCompte($SESSION_VARS["intitule_compte"], $SESSION_VARS["decouvert_new_total"], $SESSION_VARS["id_cpte"], $frais_decouvert_prc * $SESSION_VARS["decouvert_new"] / 100 + recupMontant($frais_decouvert),$SESSION_VARS["mnt_min_new"],$SESSION_VARS["cpte_vers_int"]);

  if ($result->errCode == NO_ERR) {
    $my_page =new HTML_message(_("Confirmation modification du compte"));
    if($SESSION_VARS["export_netbank"] == 't'){
    	$etat_export_netbank = 'Activé';
    } else {
    	$etat_export_netbank = 'Désactivé';
    }
    if($SESSION_VARS['utilise_netbank'] == 't'){
    	$my_page->setMessage(_("Le nouvel intitulé est :")." ". $SESSION_VARS["intitule_compte"].".<br /> ".sprintf(_("Le nouveau découvert autorisé et le nouveau montant minimum sont de : %s et %s"),afficheMontant($SESSION_VARS["decouvert_new_total"],true),afficheMontant($SESSION_VARS["mnt_min_new"],true))."<br />"._("L'export Netbank de ce compte est :")." ".$etat_export_netbank);
    } else {
    	$my_page->setMessage(_("Le nouvel intitulé est :")." ". $SESSION_VARS["intitule_compte"].".<br /> ".sprintf(_("Le nouveau découvert autorisé et le nouveau montant minimum sont de : %s et %s"),afficheMontant($SESSION_VARS["decouvert_new_total"],true),afficheMontant($SESSION_VARS["mnt_min_new"],true)));
    }    
    $my_page->addButton("BUTTON_OK", 'Gen-10');
  } else {
    $my_page = new HTML_erreur(_("Echec de la modification du compte "));
    $my_page->setMessage("Erreur : ".$error[$result->errCode]);
    $my_page->addButton("BUTTON_OK", 'Mdc-1');
  }
  $my_page->show();
} /*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>