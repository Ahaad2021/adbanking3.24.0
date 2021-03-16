<?php

/*
Simulation d'arré de compte.
  Permet d'obtenir la situation d'un compte d'épargne rémunéréà la date d'aujourd'hui.
Note : on peut faire l'arrêté du compte de base si celui-ci est rémunéré


*/
require_once('lib/dbProcedures/epargne.php');
global $global_monnaie_prec;
global $adsys;

if ($global_nom_ecran == "Scp-1") {

//choix du compte
  $html = new HTML_GEN2(_("Simulation d'arrêté de compte : choix du compte"));

  //affichage de tous les comptes du client sauf le compte de base; s'il n'y a que le compte de base ne pas aller plus loin. On ne fait d'arrêté de compte que pour les comptes rémunérés
  $ListeComptes = get_comptes_epargne($global_id_client);
  $choix = array();
  if (isset($ListeComptes)) {
    $id_cpte_base = getBaseAccountID ($global_id_client);
    foreach($ListeComptes as $key=>$value) {
      //enlever le compte de base de la liste des comptes
      if ($key !=  $id_cpte_base)
        $choix[$key] = $value["num_complet_cpte"]." ".$value["intitule_compte"];
      //enlever les comptes non rémunérés
      //      if ($value["tx_interet"] > 0) $choix[$key] = $value["num_complet_cpte"];
    }
  }

  $html->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);
  $html->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);
  $html->setFieldProperties("NumCpte", FIELDP_IS_REQUIRED, true);

  $html->addTable("ad_cpt", OPER_INCLUDE, array("date_ouvert", "etat_cpte"));
  $html->addTable("adsys_produit_epargne", OPER_INCLUDE, array("libel"));

  $html->setFieldProperties("libel", FIELDP_WIDTH, 40);

  //ordonner les champs
  $html->setOrder("NumCpte", array("libel", "etat_cpte", "date_ouvert"));
  //mettre les champs en label
  $fieldslabel = array("libel", "etat_cpte", "date_ouvert");
  foreach($fieldslabel as $value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  };

  //en fonction du choix du compte, afficher les infos avec le onChange javascript

  $codejs = "function getInfoCompte(){";

  if (isset($ListeComptes)) {
    foreach($ListeComptes as $key=>$value) {
      $codejs .= "\n\t\tif (document.ADForm.HTML_GEN_LSB_NumCpte.value == " . $key .
                 "){ \n\t\t\tdocument.ADForm.HTML_GEN_LSB_etat_cpte.value = " . $value["etat_cpte"] . ";";
      $tmp_date = pg2phpDatebis($value["date_ouvert"]); //array(mm,dd,yyyy)
      $codejs .= "\n\t\t\tdocument.ADForm.HTML_GEN_date_date_ouvert.value = convert_js_date('".$tmp_date[1]."/". $tmp_date[0]."/".$tmp_date[2]."');";
      $codejs .= "\n\t\t\tdocument.ADForm.libel.value = \"".$value["libel"]. "\";";
      $codejs .= "}\n;";
    };
    $codejs .= "\n\t\tif (document.ADForm.HTML_GEN_LSB_NumCpte.value == 0)".
               "{ \n\t\t\tdocument.ADForm.HTML_GEN_LSB_etat_cpte.value = ''; document.ADForm.HTML_GEN_date_date_ouvert.value='';";
    $codejs .= "document.ADForm.libel.value='';document.ADForm.HTML_GEN_LSB_etat_cpte.value='0';}";
  };
  $codejs .= "} getInfoCompte();\n";

  $html->setFieldProperties("NumCpte", FIELDP_JS_EVENT, array("onChange"=>"getInfoCompte();"));
  $html->addJS(JSP_FORM, "JS1", $codejs);


  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Scp-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();

} else if ($global_nom_ecran == "Scp-2") {
  $InfoCpte = getAccountDatas($NumCpte);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  $devise = $InfoCpte["devise" ];

  $fieldslabel = array();

  /* Récuper le solde à la clôture, les intérêst à verser, les frais de fermeture et les pénalités à retirer */
  $infos_simulation = simulationArrete($NumCpte);
  $solde_cloture = $infos_simulation["solde_cloture"];

  setMonnaieCourante($devise);
  $html = new HTML_GEN2(_("Traitement simulation d'arrêté de compte"));

  $nom_int_cpt=$InfoCpte["num_complet_cpte"]." ".$InfoCpte["intitule_compte"];
  $html->addField("Cpte", _("Compte à arrêter"), TYPC_TXT);
  $html->setFieldProperties("Cpte", FIELDP_DEFAULT, $nom_int_cpt);
  array_push($fieldslabel, "Cpte");
  $ordre[0] = "Cpte";

  $html->addField("TypeCpte", _("Type du compte"), TYPC_TXT);
  $html->setFieldProperties("TypeCpte", FIELDP_DEFAULT, $InfoProduit["libel"]);
  $html->setFieldProperties("TypeCpte", FIELDP_WIDTH, 40);
  array_push($fieldslabel,"TypeCpte");
  $ordre[1] = "TypeCpte";

  $html->addField("Solde", _("Solde courant"), TYPC_MNT);
  $html->setFieldProperties("Solde", FIELDP_DEFAULT, $InfoCpte["solde"] );
  array_push($fieldslabel,"Solde");
  $ordre[4] = "Solde";

  if ($InfoCpte["mnt_bloq"] > 0) {
    $dispo = $InfoCpte["solde"] - $InfoCpte["mnt_bloq"] - $InfoCpte["mnt_min_cpte"] - $InfoCpte["mnt_bloq_cre"];
    $html->addField("dispo", _("Solde disponible"), TYPC_MNT);
    $html->setFieldProperties("dispo", FIELDP_DEFAULT, $dispo );
    array_push($fieldslabel,"dispo");
    $ordre[6] = "dispo";

    $html->addField("mnt_bloq", _("Montant bloqué"), TYPC_MNT);
    $html->setFieldProperties("mnt_bloq", FIELDP_DEFAULT, ($InfoCpte["mnt_bloq"] + $InfoCpte["mnt_bloq_cre"]));
    array_push($fieldslabel,"mnt_bloq");
    $ordre[5] = "mnt_bloq";
  }

  $html->addField("interets", _("Intérêts à recevoir"), TYPC_MNT);
  $html->setFieldProperties("interets", FIELDP_DEFAULT, $infos_simulation["interets"]);
  array_push($fieldslabel,"interets");
  $ordre[7] = "interets";

  /* Pénalités de rupture anticipée */
  $html->addField("penalites", _("Pénalités pour rupture anticipée"), TYPC_MNT);
  $penalites = calculPenalites($NumCpte, $InfoCpte["solde"] + $interets);
  $html->setFieldProperties("penalites", FIELDP_DEFAULT, $infos_simulation["penalites"]);
  array_push($fieldslabel,"penalites");
  $ordre[10] = "penalites";

  $html->addField("frais_fermeture", _("Frais de fermeture du compte"), TYPC_MNT);
  $html->setFieldProperties("frais_fermeture", FIELDP_DEFAULT, $infos_simulation["frais_fermeture"]);
  array_push($fieldslabel,"frais_fermeture");
  $ordre[8] = "frais_fermeture";

  $html->addField("frais_tenue", _("Frais de tenue de compte"), TYPC_MNT);
  $html->setFieldProperties("frais_tenue", FIELDP_DEFAULT, $infos_simulation["frais_tenue"]);
  array_push($fieldslabel,"frais_tenue");
  $ordre[9] = "frais_tenue";

  /* Infos relatives aux comptes à terme (DAT ou CAT ) */
  if ( $InfoCpte["terme_cpte"] > 0 ) {
    $html->addField("Terme", _("Terme normal"),TYPC_DTE);
    $html->setFieldProperties("Terme", FIELDP_DEFAULT, $InfoCpte["dat_date_fin"]);
    array_push($fieldslabel,"Terme");
    $ordre[2] = "Terme";

    if ($InfoProduit["certif"] == 't') {
      $html->addField("num_certif", _("Numéro de certificat"), TYPC_TXT);
      $html->setFieldProperties("num_certif", FIELDP_DEFAULT,$InfoCpte["dat_num_certif"]);
      array_push($fieldslabel,"num_certif");
      $ordre[3] = "num_certif";
    }
  }


  $html->addField("solde_fin", _("Solde de clôture"), TYPC_MNT);
  $html->setFieldProperties("solde_fin", FIELDP_DEFAULT,$solde_cloture);
  array_push($fieldslabel,"solde_fin");
  $ordre[11] = "solde_fin";

  //mettre les champs en label
  foreach($fieldslabel as $value)
  $html->setFieldProperties($value, FIELDP_IS_LABEL, true);

  ksort($ordre);
  //trier $odre suivant les clés numériques

  $html->setOrder(NULL, $ordre);

  ajout_historique(55, $global_id_client,'', $global_nom_login, date("r"), NULL);

  $html->addFormButton(1, 1, "retour", _("Précédent"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Retour Menu"), TYPB_SUBMIT);
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Scp-1');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();

} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "L'écran $global_nom_ecran n'a pas été trouvé"
?>