<?php

/* Ajustement du solde d'un compte épargne
   TF - 07/10/2002
   Implémente la fonction de correction du solde d'un client.
   !!  Cette fonction donne lieu à une correction pure et simple   !!
   !!  du compte du client ainsi que du compte comptable 24x       !!

*/

require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/HTML_message.php');
require_once('lib/dbProcedures/systeme.php');
require_once('lib/dbProcedures/compte.php');

if ($global_nom_ecran == 'Acp-1') {
  $myForm = new HTML_GEN2(_("Choix du client"));
  $myForm->addField("num_client", _("Numéro du client"), TYPC_INT);
  $myForm->addLink("num_client", "rechercher", _("Rechercher"), "#");
  $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client', '"._("Recherche")."');return false;"));
  $myForm->setFieldProperties("num_client", FIELDP_IS_REQUIRED, true);
  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Acp-2');
  $myForm->setFormButtonProperties("ok", BUTP_KEY, KEYB_ENTER);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-7');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
} else if ($global_nom_ecran == 'Acp-2') {
  // Vérifie si le client existe et possède des comptes
  if (!isset($num_client))
    $num_client = $SESSION_VARS["num_client"];

  $details = getClientDatas($num_client);
  if ($details == NULL) { //Si le client n'existe pas
    $erreur = new HTML_erreur(_("Client inexistant"));
    $erreur->setMessage(_("Le numéro de client entré ne correspond à aucun client valide"));
    $erreur->addButton(BUTTON_OK,"Acp-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }
  //Si le client existe, il faut vérifier son état, on ne prend que les clients actifs
  else if ($details["etat"] != 2) {
    $erreur = new HTML_erreur(_("Client non-actif"));
    $erreur->setMessage(_("Ce client n'est pas dans l'état actif! Vérifiez le numéro")." ");
    $erreur->addButton(BUTTON_OK,"Acp-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  } else { // Le client existe et est valide
    // Enregistre le numéro du client
    $SESSION_VARS["num_client"] = $num_client;
    // Récupère la liste des comptes de ce client
    $ACC = getAllAccounts($num_client);
    $choices = array();
    while (list(,$cpt) = each($ACC)) {
      $PROD = getProdEpargne($cpt["id_prod"]);
      if ($PROD["classe_comptable"] <= 2) // DAV ou DAT
        $choices[$cpt["id_cpte"]] = $cpt["num_complet_cpte"]." ".$cpt["intitule_compte"];
    }

    // Construit le formulaire
    $myForm = new HTML_GEN2(_("Choix du compte du client"));
    $myForm->addField("num_client", _("Numéro du client"), TYPC_TXT);
    $myForm->addField("nom_client", _("Nom du client"), TYPC_TXT);
    $myForm->setFieldProperties("num_client", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("num_client", FIELDP_DEFAULT, $num_client);
    $myForm->setFieldProperties("nom_client", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("nom_client", FIELDP_DEFAULT, getClientName($num_client));
    $myForm->addField("num_cpte", _("Numéro de compte"), TYPC_LSB);
    $myForm->setFieldProperties("num_cpte", FIELDP_ADD_CHOICES, $choices);
    $myForm->setFieldProperties("num_cpte", FIELDP_IS_REQUIRED, true);
    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Acp-3');
    $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-7');
    $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Acp-1');
    $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
} else if ($global_nom_ecran == 'Acp-3') {
  if (isset($num_cpte))
    $SESSION_VARS["id_cpte"] = $num_cpte;

  // Récupère les infos sur le compte
  $ACC = getAccountDatas($SESSION_VARS["id_cpte"]);
  $SESSION_VARS["solde_courant"] = $ACC["solde"];
  $devise = $ACC["devise"];
  setMonnaieCourante($devise);
  $PROD = getProdEpargne($ACC["id_prod"]);
  // Gén!re le formulaire
  $myForm = new HTML_GEN2(_("Saisie du nouveau solde"));
  $myForm->addField("num_client", _("Numéro du client"), TYPC_INT);
  $myForm->addField("nom_client", _("Nom du client"), TYPC_TXT);
  $identprod=$ACC["id_prod"];
  $infoprod=getProdEpargne($identprod);
  $libelprod=$infoprod["libel"];
  $myForm->addField("num_compte", _("Numéro de compte"), TYPC_TXT);
  $myForm->addField("prod_epargne", _("Produit d'épargne"), TYPC_TXT);
  $myForm->setFieldProperties("prod_epargne", FIELDP_DEFAULT, $libelprod);
  $myForm->setFieldProperties("prod_epargne", FIELDP_IS_LABEL, true);
  $myForm->addField("solde", _("Solde actuel"), TYPC_MNT);
  $myForm->addField("nouveau_solde", _("Nouveau solde"), TYPC_MNT);
  $myForm->addField("nouveau_solde_conf", _("Confirmation nouveau solde"), TYPC_MNT);
//  $myForm->addField("commentaire", _("Commentaire"), TYPC_ARE);
  $myForm->setFieldProperties("num_client", FIELDP_DEFAULT, $ACC["id_titulaire"]);
  $myForm->setFieldProperties("nom_client", FIELDP_DEFAULT, getClientName($ACC["id_titulaire"]));
  $myForm->setFieldProperties("num_compte", FIELDP_DEFAULT, $ACC["num_complet_cpte"]);
  $myForm->setFieldProperties("solde", FIELDP_DEFAULT, $ACC["solde"]);
  $myForm->setFieldProperties("nouveau_solde", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("nouveau_solde_conf", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("num_client", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("nom_client", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("num_compte", FIELDP_IS_LABEL, true);
  $myForm->setFieldProperties("solde", FIELDP_IS_LABEL, true);

	 // Pièce justificative
  $myForm->addTable("ad_his_ext", OPER_INCLUDE, array("type_piece", "num_piece", "date_piece", "communication", "remarque"));
  $myForm->setFieldProperties("type_piece", FIELDP_INCLUDE_CHOICES, array(9, 10, 11, 12, 13));
  // Ordre des champs de la pièce justificative
  $myForm->setOrder("nouveau_solde_conf", array("type_piece", "num_piece", "date_piece", "communication", "remarque"));

  $JS = "if (document.ADForm.nouveau_solde.value != document.ADForm.nouveau_solde_conf.value) {ADFormValid = false;msg += '- "._("Les deux soldes doivent être égaux")."\\n';}";
  $myForm->addJS(JSP_BEGIN_CHECK, "JS", $JS);

  // Ajout des boutons
  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "precedent", _("Précédent"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Acp-4');
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 'Gen-7');
  $myForm->setFormButtonProperties("precedent", BUTP_PROCHAIN_ECRAN, 'Acp-2');
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("precedent", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();
} else if ($global_nom_ecran == 'Acp-4') {
  $id_cpte = $SESSION_VARS["id_cpte"];
  $solde = recupMontant($nouveau_solde);
	$piece_just = array('type_piece' => $type_piece, 'num_piece' => $num_piece, 'date_piece' => $date_piece, 'remarque' => $remarque, "communication" => $communication);

  if ($SESSION_VARS["solde_courant"] == $solde) {
    $msg = _("Le nouveau solde doit être différent du solde actuel du compte");
    $html_err = new HTML_erreur(_("Echec lors de l'ajustement du solde du compte."));
    $html_err->setMessage($msg);
    $html_err->addButton("BUTTON_OK", 'Acp-3');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  $myErr = ajustementSoldeCptClient($id_cpte,$global_id_guichet, $solde, $piece_just);

  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec lors de l'ajustement du solde du compte "));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]."<br />"._("Paramètre")." : ".$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Acp-3');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $MyPage = new HTML_message(_("Ajustement de solde"));
    $MyPage->setMessage(_("Le solde du compte a été modifié avec succès"));
    $MyPage->addButton(BUTTON_OK, "Gen-7");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}

?>