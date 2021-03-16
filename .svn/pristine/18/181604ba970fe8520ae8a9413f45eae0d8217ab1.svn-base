<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [473] opérations entre le siège et les agences
 * Cette fonction appelle les écrans suivants :
 * - Osa-1 : Sélection du type et du montant de l'opération siège/agence
 * - Osa-2 : Confirmation du montant de l'opération
 * - Osa-3 : passage de l'opération comptable
 * @package Compta
 * @since 2.9
 **/

global $global_multidevise;
global $adsys;
global $global_id_agence;

/*{{{ Osa-1 : choix de l'opération siège/agence */
if ($global_nom_ecran == "Osa-1") {
  // liste des opérations entre siège et agence
 // $choix = $adsys["adsys_op_siege_agence"];

  if (isSiege()) { // si on est au siège
    // si on est ua siège, éliminer les opérations qui ont dans les agences
    foreach($adsys["adsys_op_siege_agence"] as $num_op=>$libel){
    	if($num_op % 2!=0){ //num operation impaire au siege
    		$choix[$num_op]=$libel;
    	}
    }

  } else { // on est dans une agence
    // si on est dans une agence, éliminer les opérations qui ont lieu au siège
   foreach($adsys["adsys_op_siege_agence"] as $num_op=>$libel){
    	if($num_op % 2 ==0){ //num opération paire à l'agence
    		$choix[$num_op]=$libel;
    	}
    }
  }

  // choix type opération siège/agence
  $MyPage = new HTML_GEN2(_("Sélection type opération siège/agence"));
  $MyPage->addField("type_op", _("Type opération"), TYPC_LSB);
  $MyPage->setFieldProperties("type_op", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("type_op", FIELDP_ADD_CHOICES, $choix);

  //Selection de l'agence concernée
  if (isSiege()) {
     $MyPage->addField("agence", _("Agence concernée"), TYPC_LSB);
     $MyPage->setFieldProperties("agence", FIELDP_IS_REQUIRED, true);
     $liste_agences = getIdNomAgenceConso();
     $MyPage->setFieldProperties("agence", FIELDP_ADD_CHOICES, $liste_agences);
  }
  // date comptable de l'opération
  $MyPage->addField("date_op",_("Date opération"), TYPC_DTE);
  $MyPage->setFieldProperties("date_op", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("date_op", FIELDP_DEFAULT, date("d/m/Y"));

  // liste des devises
  $choix = array();
  $lise_dev = get_table_devises();
  foreach($lise_dev as $code=>$value)
  $choix[$code] = $code;

  $MyPage->addField("devise", _("Devise"), TYPC_LSB);
  $MyPage->setFieldProperties("devise", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("devise", FIELDP_ADD_CHOICES, $choix);

  // montant de l'opération
  $MyPage->addField("mnt_op", _("Montant opération"), TYPC_MNT);
  $MyPage->setFieldProperties("mnt_op", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("mnt_op", FIELDP_DEVISE, NULL);

  // Boutons
  $MyPage->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Osa-2");
  $MyPage->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-14");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Osa-2 : Confirmation du montant de l'opération */
elseif ($global_nom_ecran == "Osa-2") {
  $MyPage = new HTML_GEN2(_("Confirmation montant"));

  // type opération siège/agence
  if (isset($type_op)) // si on vient de l'écran Osa-1 TODO devrait être remplacé par un test sur global_ecran_prec
    $SESSION_VARS["op_siege_agence"] = $type_op;

  $MyPage->addField("type_op", _("Type opération"), TYPC_TXT);
  $MyPage->setFieldProperties("type_op", FIELDP_DEFAULT, adb_gettext($adsys["adsys_op_siege_agence"][$SESSION_VARS["op_siege_agence"]]));
  $MyPage->setFieldProperties("type_op", FIELDP_IS_LABEL, true);

  //Selection de l'agence concernée
  if (isset($agence)) { // si on vient de l'écran Osa-1
  	$SESSION_VARS["agence"] = $agence;
	  $MyPage->addField("agence",_("Agence N°"), TYPC_INT);
	  $MyPage->setFieldProperties("agence", FIELDP_DEFAULT, $SESSION_VARS["agence"]);
	  $MyPage->setFieldProperties("agence", FIELDP_IS_LABEL, true);
  }

  // date comptable de l'opération
  if (isset($date_op)) // si on vient de l'écran Osa-1
    $SESSION_VARS["date_op"] = $date_op;
  $MyPage->addField("date_op",_("Date opération"), TYPC_DTE);
  $MyPage->setFieldProperties("date_op", FIELDP_DEFAULT, $SESSION_VARS["date_op"]);
  $MyPage->setFieldProperties("date_op", FIELDP_IS_LABEL, true);

  // devise de l'opération
  if (isset($devise)) // si on vient de l'écran Osa-1
    $SESSION_VARS["devise"] = $devise;
  $MyPage->addField("devise", _("Devise"), TYPC_TXT);
  $MyPage->setFieldProperties("devise", FIELDP_DEFAULT, $SESSION_VARS["devise"]);
  $MyPage->setFieldProperties("devise", FIELDP_IS_LABEL, true);

  // montant de l'opération
  if (isset($mnt_op)) // si on vient de l'écran Osa-1
    $SESSION_VARS["mnt_op"] = recupMontant($mnt_op);
  $MyPage->addField("mnt_op", _("Montant opération"), TYPC_MNT);
  $MyPage->setFieldProperties("mnt_op", FIELDP_DEFAULT, recupMontant($SESSION_VARS["mnt_op"]));
  $MyPage->setFieldProperties("mnt_op", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("mnt_op", FIELDP_DEVISE, NULL);

  // confirmation montant
  $MyPage->addField("mnt", _("Confirmation montant"), TYPC_MNT);
  $MyPage->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("mnt", FIELDP_DEVISE, NULL);

  //Boutons
  $MyPage->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Osa-3");
  $MyPage->addFormButton(1,2,"annuler", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Osa-1");
  $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Osa-3 : Passage de l'écriture de l'opération */
elseif ($global_nom_ecran == "Osa-3") {
  $MyPage = new HTML_GEN2(_("Passage opération siège/agence"));

  // montant confirmation
  $mnt = recupMontant($mnt);

  // vérifier que les deux montants saisis sont égaux
  if ($SESSION_VARS["mnt_op"] != $mnt) {
    $html_err = new HTML_erreur(_("Echec passage opération siège/agence"));
    $html_err->setMessage(_("Veuillez saisir le même montant !"));
    $html_err->addButton("BUTTON_OK", 'Osa-2');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  // passage écriture siège/agence
  $result = passageOperationSiegeAgence($SESSION_VARS["op_siege_agence"], $SESSION_VARS["date_op"], $mnt, $SESSION_VARS["devise"], $SESSION_VARS["agence"]);
  if ($result->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec passage opération siège/agence"));
    $html_err->setMessage(_("L'opération n'a pas été passée ! ".$error[$result->errCode].$result->param));
    $html_err->addButton("BUTTON_OK", 'Osa-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $MyPage = new HTML_message(_("Confirmation opération siège/agence"));
    $MyPage->setMessage(_("L'opération a été passée avec succès !"));
    $MyPage->addButton(BUTTON_OK, "Osa-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }

}
/*}}}*/
else
  signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>