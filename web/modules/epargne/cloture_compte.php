<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

require_once('lib/dbProcedures/compte.php');
require_once('lib/dbProcedures/epargne.php');
require_once 'lib/html/HTML_message.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'modules/epargne/recu.php';

/*{{{ Ccp-1 : Choix du compte */
if ($global_nom_ecran == "Ccp-1") {
  $html = new HTML_GEN2(_("Clôture d'un compte : choix du compte"));

  // Affichage de tous les comptes du client sauf le compte de base; s'il n'y a que le compte de base ne pas aller plus loin
  $ListeComptes = get_comptes_epargne($global_id_client);

  $ACCS = getComptesCloturePossible($global_id_client);

  $html->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);

  if (is_array($ACCS)) {
    foreach($ACCS as $id_cpte => $ACC) {
      $choix[$id_cpte] = $ACC["num_complet_cpte"]." ".$ACC["intitule_compte"];
    }
    $html->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);
  }

  $html->setFieldProperties("NumCpte", FIELDP_IS_REQUIRED, true);

  $html->addTable("ad_cpt", OPER_INCLUDE, array("date_ouvert", "etat_cpte", "devise"));

  $html->addTable("adsys_produit_epargne", OPER_INCLUDE, array("libel"));
  $html->setFieldProperties("libel", FIELDP_WIDTH, 40);

  // Ordonner les champs
  $html->setOrder("NumCpte", array("libel", "devise", "etat_cpte", "date_ouvert"));

  // Mettre les champs en label
  $fieldslabel = array("libel", "devise", "etat_cpte", "date_ouvert");
  foreach($fieldslabel as $value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  }

  // En fonction du choix du compte, afficher les infos avec le onChange javascript

  $codejs = "function getInfoCompte(){";

  if (isset($ListeComptes)) {
    foreach($ACCS as $key=>$value) {
      $codejs .= "\n\t\tif (document.ADForm.HTML_GEN_LSB_NumCpte.value == " . $key .
        "){ \n\t\t\tdocument.ADForm.HTML_GEN_LSB_etat_cpte.value = " . $value["etat_cpte"] . ";" .
        "\n\t\t\tdocument.ADForm.HTML_GEN_LSB_devise.value = '" . $value["devise"] . "';";
      $tmp_date = pg2phpDatebis($value["date_ouvert"]); //array(mm,dd,yyyy)
      $codejs .= "\n\t\t\tdocument.ADForm.HTML_GEN_date_date_ouvert.value = '".localiser_date($tmp_date[1]."/".
          $tmp_date[0]."/".$tmp_date[2])."';";
      $codejs .= "\n\t\t\tdocument.ADForm.libel.value = " . "\"".$value["libel"] . "\"".";";
      $codejs .= "}\n;";
    }
    $codejs .= "\n\t\tif (document.ADForm.HTML_GEN_LSB_NumCpte.value == 0)".
      "{ \n\t\t\tdocument.ADForm.HTML_GEN_LSB_etat_cpte.value = ''; document.ADForm.HTML_GEN_LSB_devise.value = ''; document.ADForm.HTML_GEN_date_date_ouvert.value='';";
    $codejs .= "document.ADForm.libel.value='';document.ADForm.HTML_GEN_LSB_etat_cpte.value='0';}";

  };
  $codejs .= "}\ngetInfoCompte();";

  $html->setFieldProperties("NumCpte", FIELDP_JS_EVENT, array("onChange"=>"getInfoCompte();"));

  $html->addJS(JSP_FORM, "JS1", $codejs);

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);

  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ccp-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();
}
/*}}}*/

/*{{{ Ccp-2 : Infos de clôture */
else if ($global_nom_ecran == "Ccp-2") {
  unset($SESSION_VARS['tax_int']);
  //FIXME : afficher une alerte pour les comptes à terme non échus
  if (isset($NumCpte)) {
    $SESSION_VARS["NumCpte"] = $NumCpte;
  }

  $infos_simulation = array();
  $myErr = autoriseCloture($SESSION_VARS["NumCpte"], $infos_simulation);
  if ($myErr->errCode != NO_ERR) {
    $html_err = new HTML_erreur(_("Echec de la cloture d'un compte."));
    $html_err->setMessage(_("Erreur")." : ".$error[$myErr->errCode]."<br />"._("Paramètre")." : ".$myErr->param);
    $html_err->addButton("BUTTON_OK", 'Gen-10');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  } else {
    $solde_cloture = $infos_simulation["solde_cloture"];
    $SESSION_VARS["solde_cloture"] = $solde_cloture;

    $InfoCpte = getAccountDatas($NumCpte);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
    $tmp_agc = getAgenceDatas($global_id_agence);

    $devise = $InfoCpte['devise'];

    setMonnaieCourante($devise);
    $html = new HTML_GEN2(_("Traitement clôture de compte"));

    $fieldslabel = array();
    $nom_int_cpt=$InfoCpte["num_complet_cpte"]." ".$InfoCpte["intitule_compte"];
    $SESSION_VARS["Numero_compte"]=$nom_int_cpt;
    $html->addField("Cpte", _("Compte à clôturer"), TYPC_TXT);
    $html->setFieldProperties("Cpte", FIELDP_DEFAULT, $nom_int_cpt);
    array_push($fieldslabel, "Cpte");

    $html->addField("TypeCpte", _("Type du compte"), TYPC_TXT);
    $html->setFieldProperties("TypeCpte", FIELDP_DEFAULT, $InfoProduit["libel"]);
    $html->setFieldProperties("TypeCpte", FIELDP_WIDTH, 40);
    array_push($fieldslabel,"TypeCpte");

    $solde = $InfoCpte["solde"];
    $html->addField("Solde", _("Solde actuel"), TYPC_MNT);
    $html->setFieldProperties("Solde", FIELDP_DEFAULT, $solde);
    array_push($fieldslabel,"Solde");

    // Intérêts à payer à la rupture
    $interets = $infos_simulation["interets"];
    $SESSION_VARS['interets_rup'] = $interets;
    $html->addField("interets", _("Intérêts à recevoir"), TYPC_MNT);
    $html->setFieldProperties("interets", FIELDP_DEFAULT, $interets);
    array_push($fieldslabel,"interets");

    // Pénalités de rupture
    $penalites = $infos_simulation["penalites"];
    $html->addField("penalites", _("Pénalités pour rupture anticipée"), TYPC_MNT);
    $html->setFieldProperties("penalites", FIELDP_DEFAULT, $penalites);
    $html->setFieldProperties("penalites", FIELDP_CAN_MODIFY,true);
    array_push($fieldslabel,"penalites");
    $SESSION_VARS["penalites"] = $penalites;


    $InfoCpte = getAccountDatas($SESSION_VARS['NumCpte']);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
    $isPrelevImpot = $InfoProduit['prelev_impot_imob'];

    if($isPrelevImpot=='t')
    {
      // impot
      $tax_int = calculImpotTax($interets);

      $html->addField("tax_int", _("Impôt mobilier déduit sur les intérêts"), TYPC_MNT);
      $html->setFieldProperties("tax_int", FIELDP_DEFAULT, $tax_int);

      $SESSION_VARS['tax_int'] = $tax_int;

      array_push($fieldslabel,"tax_int");
    }

    // Infos relatives aux comptes à terme ( DAT ou CAT )
    if ($InfoCpte["terme_cpte"] > 0 ) {
      // Date fin du compte à terme ( DAT ou CAT )
      $html->addField("Terme", _("Terme normal"),TYPC_TXT);
      $html->setFieldProperties("Terme", FIELDP_DEFAULT, pg2phpDate($InfoCpte["dat_date_fin"]));
      array_push($fieldslabel,"Terme");
      $SESSION_VARS['date_fin'] = $InfoCpte["dat_date_fin"];

      if ($InfoProduit["certif"] == 't') {
        $html->addField("num_certif", _("Numéro de certificat"), TYPC_TXT);
        $html->setFieldProperties("num_certif", FIELDP_DEFAULT,$InfoCpte["dat_num_certif"]);
        array_push($fieldslabel,"num_certif");
      }
    }

    // Frais de fermeture : ils sont modifiables
    if ($infos_simulation["frais_fermeture"] > 0) {
      $html->addField("frais_fermeture", _("Frais de fermeture du compte"), TYPC_MNT);
      $html->setFieldProperties("frais_fermeture", FIELDP_DEFAULT, $infos_simulation["frais_fermeture"]);
      $html->setFieldProperties("frais_fermeture", FIELDP_CAN_MODIFY,true);
      array_push($fieldslabel,"frais_fermeture");
      $SESSION_VARS['frais_fermeture'] = $infos_simulation["frais_fermeture"];
    }

    // Frais de tenue de compte : ils sont modifiables
    if ($infos_simulation["frais_tenue"] > 0) {
      $html->addField("frais_tenue", _("Frais de tenue de compte"), TYPC_MNT);
      $html->setFieldProperties("frais_tenue", FIELDP_DEFAULT, $infos_simulation["frais_tenue"]);
      $html->setFieldProperties("frais_tenue", FIELDP_CAN_MODIFY,true);
      array_push($fieldslabel,"frais_tenue");
      $SESSION_VARS['frais_tenue'] = $infos_simulation["frais_tenue"];
    }

    // Mettre les champs en label
    foreach($fieldslabel as $value)
      $html->setFieldProperties($value, FIELDP_IS_LABEL, true);

    // Choix de la destination des fonds
    $html->addField("destination", _("Destination des fonds"), TYPC_LSB);
    $choix = array(2 => _("Transfert sur compte d'épargne"));
    $html->setFieldProperties("destination", FIELDP_ADD_CHOICES, $choix);
    $html->setFieldProperties("destination", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("destination", FIELDP_IS_REQUIRED, false);

    $html->addField("id_cpte_dest", _("Numéro du compte destination"), TYPC_LSB);
    // Recherche de tous les comptes sur lesquels on peut déposer le montant $solde_cloture
    $CPTS = get_comptes_epargne($global_id_client);
    $choix = array();
    foreach ($CPTS as $key => $CPT) {
      $myErr = CheckDepot($CPT, $solde_cloture);
      if (($myErr->errCode == NO_ERR) && ($CPT["devise"] == $devise) && ($CPT["id_cpte"] != $NumCpte))
        $choix[$CPT["id_cpte"]] = $CPT["num_complet_cpte"]." ".$CPT["intitule_compte"];
    }

    // Ajout d'un petit JS pour que le champ "Compte destination" ne soit activé que si ona  choisi "Transfert sur compte d'épargne" comme type de destination
    $js = "if (document.ADForm.HTML_GEN_LSB_destination.value == 1)
        {
          document.ADForm.HTML_GEN_LSB_id_cpte_dest.value = 0;
          document.ADForm.HTML_GEN_LSB_id_cpte_dest.disabled = true;
        }
          else
        {
          cpte_virement = '".$InfoCpte["cpte_virement_clot"]."';
          document.ADForm.HTML_GEN_LSB_id_cpte_dest.disabled = false;
          if(cpte_virement == '')
          document.ADForm.HTML_GEN_LSB_id_cpte_dest.value = 0;
          else
          document.ADForm.HTML_GEN_LSB_id_cpte_dest.value = cpte_virement;
        }";

    $html->setFieldProperties("destination", FIELDP_JS_EVENT, array("onchange" => $js));

    $html->setFieldProperties("id_cpte_dest", FIELDP_ADD_CHOICES, $choix);
    $html->setFieldProperties("id_cpte_dest", FIELDP_IS_LABEL, false);
    $html->setFieldProperties("id_cpte_dest", FIELDP_IS_REQUIRED, true);

    $html->addField("communication", _("Communication"), TYPC_TXT);
    $html->setFieldProperties("communication", FIELDP_DEFAULT, $SESSION_VARS['communication']);
    $html->addField("remarque", _("Remarque"), TYPC_ARE);
    $html->setFieldProperties("remarque", FIELDP_DEFAULT, $SESSION_VARS['remarque']);

    // Vérification supplémentaire, on vérifie que si le champ destination = 2, alors le champ numéro de compte de destination est renseigné
    $check_js = "if (document.ADForm.HTML_GEN_LSB_destination.value == 2 && document.ADForm.HTML_GEN_LSB_id_cpte_dest.value == 0)
              {
                msg += '- "._("Le compte de destination doit etre précisé")."\\n';
                ADFormValid = false;
              }";
    $html->addJS(JSP_BEGIN_CHECK, "check", $check_js);

    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
    $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ccp-3');
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Ccp-1');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

    $html->buildHTML();
    echo $html->getHTML();
  }
}
/*}}}*/

/*{{{ Ccp-3 : Confirmation de clôture */
else if ($global_nom_ecran == "Ccp-3") {

  $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  $SESSION_VARS["devise"] = $InfoCpte["devise"];

  $SESSION_VARS["destination"] = $destination;
  $SESSION_VARS["id_cpte_dest"] = $id_cpte_dest;
  $SESSION_VARS['communication'] = $communication;
  $SESSION_VARS['remarque'] = $remarque;

  // Si les frais ont été modifiés
  if (isset($frais_fermeture)) {
    $SESSION_VARS["frais_fermeture"] = recupMontant($frais_fermeture);
  }

  // Si les frais de tenue de compte
  if (isset($frais_tenue)) {
    $SESSION_VARS["frais_tenue"] = recupMontant($frais_tenue);
  }

  // Si les pénalités ont été modifiées
  if (isset($penalites)) {
    $SESSION_VARS["penalites"] = recupMontant($penalites);
  }

  // Recalcul du solde de clôture avec les frais et pénalités éventuellement modifiés
  $infos_simul = simulationArrete($SESSION_VARS["NumCpte"], $SESSION_VARS["frais_fermeture"], $SESSION_VARS["penalites"], $SESSION_VARS["frais_tenue"]);
  $solde_cloture = $infos_simul["solde_cloture"];

  // Vérifie que l'encaisse du guichet est suffisante pour cloturer le compte
  if ($destination == 1) { // Destination guichet
    $devise = $InfoCpte["devise"];
    $encaisse = get_encaisse($global_id_guichet, $devise);
    if ($encaisse < $solde_cloture) {
      $html_err = new HTML_erreur(_("Clôture de comptes."));
      $html_err->setMessage(sprintf(_("Le montant de l'encaisse %s est insuffisant pour effectuer une cloture au guichet"), $devise ." (".afficheMontant($encaisse, true).") ")."<br />"._("Montant nécessaire")." : ".afficheMontant($solde_cloture, true));
      $html_err->addButton("BUTTON_OK", 'Gen-10');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      die();
    }
  }

  setMonnaieCourante($devise);

  // Affichage des infos
  $html = new HTML_GEN2(_("Confirmation clôture de compte"));

  $fieldslabel = array();

  $nom_int_cpt=$InfoCpte["num_complet_cpte"]." ".$InfoCpte["intitule_compte"];
  $html->addField("Cpte", _("Compte à clôturer"), TYPC_TXT);
  $html->setFieldProperties("Cpte", FIELDP_DEFAULT, $nom_int_cpt);
  array_push($fieldslabel, "Cpte");

  $html->addField("TypeCpte", _("Type du compte"), TYPC_TXT);
  $html->setFieldProperties("TypeCpte", FIELDP_DEFAULT, $InfoProduit["libel"]);
  $html->setFieldProperties("TypeCpte", FIELDP_WIDTH, 40);
  array_push($fieldslabel,"TypeCpte");

  $solde = $InfoCpte["solde"];
  $html->addField("Solde", _("Solde actuel"), TYPC_MNT);
  $html->setFieldProperties("Solde", FIELDP_DEFAULT, $solde );
  array_push($fieldslabel,"Solde");

  $html->addField("interets", _("Intérêts à recevoir"), TYPC_MNT);
  $html->setFieldProperties("interets", FIELDP_DEFAULT, $SESSION_VARS['interets_rup']);
  array_push($fieldslabel,"interets");

  $html->addField("penalites", _("Pénalités pour rupture anticipée"), TYPC_MNT);
  $html->setFieldProperties("penalites", FIELDP_DEFAULT, $SESSION_VARS["penalites"]);
  array_push($fieldslabel,"penalites");

  $tax_int = $SESSION_VARS['tax_int'];

  if($tax_int != null)
  {
    // impot

    $html->addField("tax_int", _("Impôt mobilier déduit sur les intérêts"), TYPC_MNT);
    $html->setFieldProperties("tax_int", FIELDP_DEFAULT, $tax_int);

    array_push($fieldslabel,"tax_int");
  }

  $html->addField("frais_fermeture", _("Frais de fermeture du compte"), TYPC_MNT);
  $html->setFieldProperties("frais_fermeture", FIELDP_DEFAULT,$SESSION_VARS["frais_fermeture"]);
  array_push($fieldslabel,"frais_fermeture");

  $html->addField("frais_tenue", _("Frais de tenue de compte"), TYPC_MNT);
  $html->setFieldProperties("frais_tenue", FIELDP_DEFAULT,$SESSION_VARS["frais_tenue"]);
  array_push($fieldslabel,"frais_tenue");


  // Affiche des infos relatives aux comptes à terme
  if ($InfoCpte["terme_cpte"]) {
    $html->addField("Terme", _("Terme normal"),TYPC_TXT);
    $html->setFieldProperties("Terme", FIELDP_DEFAULT, pg2phpDate($SESSION_VARS['date_fin']));
    array_push($fieldslabel,"Terme");

    if ($InfoProduit["certif"] == 't') {
      $html->addField("num_certif", _("Numéro de certificat"), TYPC_TXT);
      $html->setFieldProperties("num_certif", FIELDP_DEFAULT,$InfoCpte["dat_num_certif"]);
      array_push($fieldslabel,"num_certif");
    }
  }

  $html->addField("solde_cloture", _("Solde après arrêté"), TYPC_MNT);
  $html->setFieldProperties("solde_cloture", FIELDP_DEFAULT, $solde_cloture);
  array_push($fieldslabel,"solde_cloture");
  $SESSION_VARS["solde_compte"]= $solde_cloture;

  // Rappel de la destination des fonds
  $html->addField("destination", _("Destination des fonds"), TYPC_TXT);
  $html->setFieldProperties("destination", FIELDP_DEFAULT, $destination == 1? _("Versement au guichet") : _("Transfert sur compte d'épargne"));
  array_push($fieldslabel, "destination");

  if ($destination == 1) {
    // Le montant à décaisser doit etre arrondi au plus petit billet dispo
    $montant_decaisser = arrondiMonnaie($solde_cloture, -1, $devise);
    $html->addField("montant_decaisser", _("Montant à décaisser"), TYPC_MNT);
    $html->setFieldProperties("montant_decaisser", FIELDP_DEFAULT, $montant_decaisser);
    array_push($fieldslabel,"montant_decaisser");

    // Si y a différence d'arrondi supérieure à la précision en devise de référence,la diff doit etre remise en devise de référence
    if ($solde_cloture != $montant_decaisser && $devise != $global_monnaie) {
      $diff = $solde_cloture - $montant_decaisser;
      $diff_dev_ref = calculeCV($devise, $global_monnaie, $diff);
      if ($diff_dev_ref > 0) {
        // Il faut remettre au client la différence dans la devise de référence
        $html->addField("diff", _("Différence de change"), TYPC_MNT);
        $html->addField("diff_dev_ref", _("C/V en ").$global_monnaie, TYPC_MNT);
        $html->addField("montant_decaisse_dev_ref", _("Confirmation C/V différence de change")." ", TYPC_MNT);
        $html->setFieldProperties("diff", FIELDP_DEFAULT, $diff);
        $html->setFieldProperties("diff_dev_ref", FIELDP_DEFAULT, $diff_dev_ref);
        $html->setFieldProperties("diff_dev_ref", FIELDP_DEVISE, $global_monnaie);
        $html->setFieldProperties("montant_decaisse_dev_ref", FIELDP_IS_REQUIRED, true);
        $html->setFieldProperties("montant_decaisse_dev_ref", FIELDP_DEVISE, $global_monnaie);
        array_push($fieldslabel,"diff","diff_dev_ref");
      }
    }
    $html->addField("montant", _("Confirmation montant décaissé"), TYPC_MNT);
    $html->setFieldProperties("montant", FIELDP_HAS_BILLET, true);

    if ($diff_dev_ref > 0)
      $html->setOrder(ORDER_LAST, array("montant_decaisse_dev_ref"));

    $action = "décaissé"; // Petite astuce pour avoir une alert en bon français
    $verif_champ = "montant_decaisser"; // Champ à comparer avec montant
  } else if ($destination == 2) {
    $ACC = getAccountDatas($id_cpte_dest);
    $solde_cpte_dest = getSoldeDisponible($id_cpte_dest) + $solde_cloture;
    debug("Solde après : $solde_cpte_dest");
    $html->addField("id_cpte_dest", _("Numéro du compte destination"), TYPC_TXT);
    $html->setFieldProperties("id_cpte_dest", FIELDP_DEFAULT, $ACC["num_complet_cpte"]." ".$ACC["intitule_compte"]);
    $SESSION_VARS["Numero_compte_dest"] =$ACC["num_complet_cpte"]." ".$ACC["intitule_compte"];
    array_push($fieldslabel,"id_cpte_dest");
    $html->addField("solde_cpte_dest", _("Solde disponible sur le compte destination après transfert"), TYPC_MNT);
    $html->setFieldProperties("solde_cpte_dest", FIELDP_DEFAULT, $solde_cpte_dest);
    array_push($fieldslabel,"solde_cpte_dest");
    $html->addField("montant", _("Confirmation montant transféré"), TYPC_MNT);
    $action = _("transféré"); // Petite astuce pour avoir une alert en bon français
    $verif_champ = "solde_cloture"; // Champ à comparer avec montant
  }

  $html->setFieldProperties("montant", FIELDP_IS_REQUIRED, true);

  // Mettre les champs en label
  foreach($fieldslabel as $value)
  $html->setFieldProperties($value, FIELDP_IS_LABEL, true);

  $js = "if (recupMontant(document.ADForm.montant.value) != recupMontant(document.ADForm.$verif_champ.value))
      {
        ADFormValid = false;
        msg += '- ".sprintf(_("Le montant %s est incorrect"),$action)."\\n';
      }";
  if (isset($diff_dev_ref)) // Il faut aussi vérifier que le montant décaissé en dev_ref est correct
    $js .= "if (recupMontant(document.ADForm.montant_decaisse_dev_ref.value) != recupMontant(document.ADForm.diff_dev_ref.value))
           {
           ADFormValid = false;
           msg += '- ".sprintf(_("Le montant décaissé en %s doit etre égal à la C/V en %s"),$global_monnaie)."\\n';
         }";

  $html->addJS(JSP_BEGIN_CHECK, "js", $js);

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
  $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ccp-4');
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Ccp-1');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $html->buildHTML();

  echo $html->getHTML();

}
/*}}}*/

/*{{{ Ccp-4 : Traitement de la clôture */
else if ($global_nom_ecran == "Ccp-4") {

  // appel DB
  // FIXME : il faut revoir la raison de clôture
  if (isset($SESSION_VARS["frais_fermeture"])) {
    $frais["fermeture"] = $SESSION_VARS["frais_fermeture"];
  }

  if (isset($SESSION_VARS["penalites"])) {
    $frais["penalites"] = $SESSION_VARS["penalites"];
  }

  if (isset($SESSION_VARS["frais_tenue"])) {
    $frais["tenue"] = $SESSION_VARS["frais_tenue"];
  }

  $data_ext['communication'] = $SESSION_VARS['communication'];
  $data_ext['remarque'] = $SESSION_VARS['remarque'];
  $data_ext['sens'] = "---";
  $interets = $SESSION_VARS['interets_rup'];
  $erreur = cloture_cpte_interface($SESSION_VARS["NumCpte"], 2, $SESSION_VARS["destination"], $SESSION_VARS["id_cpte_dest"], $frais, $data_ext,$interets); //la raison de cloture est par défaut 'sur dde du client'

  if ($erreur->errCode == NO_ERR) {
    if ($SESSION_VARS["destination"] == 1) {
      $destination="Versement au guichet";
    }
    else {
      $destination=$SESSION_VARS["Numero_compte_dest"];
    }

    $id_his = $erreur->param["id_his"];
    setMonnaieCourante($SESSION_VARS["devise"]);
    //int_recu_cloture_compte("12-02-09-00012-24 mon teste1","10000","12-02-09-00012-24 mon teste2","001232");
    $tax_interet = $SESSION_VARS['tax_int'];
    print_recu_cloture_compte($SESSION_VARS["Numero_compte"],$SESSION_VARS["solde_compte"],$destination,$id_his,$frais,$tax_interet);

    $html_msg =new HTML_message(_("Confirmation de clôture de compte"));
    $message = _("Le compte a été clôturé avec succès");

    if ($erreur->param["mnt"] > 0) {
      $message .= "<br />"._("Des frais impayés ont été débités de votre compte de base pour un montant de")." :<br />";
      $message .= afficheMontant($erreur->param["mnt"], true);
    }
    $message .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $id_his)."</code></B>";
    $html_msg->setMessage($message);
    $html_msg->addButton("BUTTON_OK", 'Gen-10');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
    // On vérifie si le client n'est plus débiteur
    if (!isClientDebiteur($global_id_client)) {
      $global_client_debiteur = false;
    }
  } else {
    debug($erreur);
    $html_err = new HTML_erreur(_("Echec de clôture de comptes."));
    $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]."<br/>"._("Paramètre")." : ".$erreur->param);
    $html_err->addButton("BUTTON_OK", 'Gen-10');

    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'écran $global_nom_ecran n'a pas été trouvé"
?>