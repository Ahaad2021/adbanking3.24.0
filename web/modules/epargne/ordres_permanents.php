<?php
require_once 'lib/html/FILL_HTML_GEN2.php';
//--------- Ope-1 : Liste des ordres permanents ----------------------------------------
//--------- Ope-2 : Ajout d'un ordre permanent : selection compte source ---------------
//--------- Ope-3 : Ajout d'un ordre permanent : selection compte destination ----------
//--------- Ope-4 : Ajout d'un ordre permanent : confirmation --------------------------
//--------- Ope-5 : Modification d'un ordre permanent ----------------------------------

//--------------------------------------------------------------------------------------
//--------- Ope-1 : Liste des ordres permanents-----------------------------------------
//--------------------------------------------------------------------------------------
if ($global_nom_ecran == 'Ope-1') {
  $tempstr = "";

  // Génération du titre
  $myForm = new HTML_GEN2(_("Liste des ordres permanents"));
  $table =& $myForm->addHTMLTable("Ordres Permanents", 7, TABLE_STYLE_ALTERN);
  $table->add_cell(new TABLE_cell(_("Compte source"), 1, 1));
  $table->add_cell(new TABLE_cell(_("Bénéficiaire"), 1, 1));
  $table->add_cell(new TABLE_cell(_("Périodicité"), 1, 1));
  $table->add_cell(new TABLE_cell(_("Montant"), 1, 1));
  $table->add_cell(new TABLE_cell(_("Montant total prevu"), 1, 1));
  $table->add_cell(new TABLE_cell("", 1, 1));
  $table->add_cell(new TABLE_cell("", 1, 1));
  // Liste des comptes
  // Liste des comptes
  $LIST = getOrdresPermParClient($global_id_client);
  if ($LIST) {
    foreach ($LIST as $row) {
      // obtenir le nom de compte
      $cpte = getAccountDatas($row['cpt_from']);
      $table->add_cell(new TABLE_cell($cpte['num_complet_cpte'],1,1));
      if (($row['type_transfert'] == 1) or ($row['type_transfert'] == 2)) {
        // obtenir le nom de compte de destination
        $cpte = getAccountDatas($row['cpt_to']);
        $table->add_cell(new TABLE_cell($cpte['num_complet_cpte'],1,1));
      }
      if ($row['type_transfert'] == 3) {
        // benef est un compte externe
        $DATA=getTireurBenefDatas($row['id_benef']);
        $tempstr=$DATA['denomination'] . "(" . $DATA['num_cpte'] . ")";
        $table->add_cell(new TABLE_cell($tempstr,1,1));
      }
      $table->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_periodicite_ordre_perm"][$row['periodicite']]),1,1));
      $table->add_cell(new TABLE_cell($row['montant'],1,1));
      $table->add_cell(new TABLE_cell($row['mnt_total_prevu'],1,1));
      if ($row['etat_clos'] != 't') {
        $table->add_cell(new TABLE_cell_link(_("Modifier"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Ope-5&id_ord=" . $row['id_ord']));
        $table->add_cell(new TABLE_cell_link(_("Supprimer"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Ope-7&id_ord=" . $row['id_ord']));
      }else{
        $table->add_cell(new TABLE_cell("Modifier",1,1));
        $table->add_cell(new TABLE_cell("Supprimer",1,1));
      }
    }
  }
  // Boutons
  $myForm->addFormButton(1, 1, "ajouter", _("Ajouter"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ajouter", BUTP_PROCHAIN_ECRAN, "Ope-2");
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-10");
  $myForm->setFormButtonProperties("ajouter", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // Génération du code
  $myForm->buildHTML();
  echo $myForm->getHTML();
}

//--------------------------------------------------------------------------------------
//--------- Ope-2 : Ajout d'un ordre permanent -----------------------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Ope-2') {
  // Génération du titre
  $myForm = new HTML_GEN2(_("Ajout d'un ordre permanent : Choix du compte source"));
  //afficher la liste des comptes du client puis le montant à retirer et ne pas oublier les frais d'opérations éventuels

  //affichage de tous les comptes du client et s'il n'y a rien...ne rien faire
  $TempListeComptes = get_comptes_epargne($global_id_client);

  //retirer de la liste les comptes à retrait unique
  $choix = array();
  if (isset($TempListeComptes)) {
    //déterminer les comptes à partir desquels on peut retirer pour le transfert
    $ListeComptes = getComptesRetraitPossible($TempListeComptes);
    if (isset($ListeComptes)) {
      foreach($ListeComptes as $key=>$value) $choix[$key] = $value["num_complet_cpte"]." ".$value["intitule_compte"];//index par id_cpte pour la listbox
    };

    //déterminer si le client a des comptes sur lesquels on  peut faire un transfert, sinon ne pas lui permettre de poursuivre la procédure
    $ListeComptes2 = getComptesDepotPossible($TempListeComptes);

    //si il n'y a qu'un compte dans la liste des comptes de dépôts et de retraits, il s'agit forcément du compte de base, donc pas de transfert possible
    if (isset($ListeComptes2) && (count($ListeComptes2) == 1) && (count($ListeComptes) == 1)) unset($ListeComptes2);
  }

  $myForm->addField("cpt_from", _("Compte source"), TYPC_LSB);
  $myForm->setFieldProperties("cpt_from", FIELDP_ADD_CHOICES, $choix);
  $myForm->setFieldProperties("cpt_from", FIELDP_IS_REQUIRED, true);

  //$myForm->addField("nb_periode", _("Nombre de periodes"), TYPC_TXT);
  //$myForm->setFieldProperties("nb_periode", FIELDP_IS_REQUIRED, false);

  $myForm->addField("TypeTransfert", _("Type de transfert"), TYPC_LSB);
  //s'il n'y a pas de compte source pour le transfert, ne pas permettre le choix d'une destination
  if (count($choix)==0) $choix2 = array();
  else {
    $choix2 = $adsys["adsys_type_ordre_permanent"];
    if (!isset($ListeComptes2)) {
    	// Pas possible entre comptes d'un même client
      unset($choix2[1]);
    }
  }

  $myForm->setFieldProperties("TypeTransfert", FIELDP_ADD_CHOICES, $choix2);
  $myForm->setFieldProperties("TypeTransfert", FIELDP_IS_REQUIRED, true);
  $myForm->addTable("ad_cpt", OPER_INCLUDE, array("etat_cpte", "devise"));
  $myForm->addTable("adsys_produit_epargne", OPER_INCLUDE, array("libel",  "duree_min_retrait_jour"));
  //$myForm->addTable("ad_ord_perm", OPER_INCLUDE, array("nb_periode"));
  $myForm->setFieldProperties("libel", FIELDP_WIDTH, 30);

  //ordonner les champs
  $order_array = array("cpt_from","libel", "devise", "etat_cpte", "duree_min_retrait_jour","TypeTransfert");
  $myForm->setOrder(NULL, $order_array);
  //mettre les champs en label
  $fieldslabel = array_diff($order_array, array("cpt_from", "TypeTransfert"));
  foreach($fieldslabel as $value) {
    $myForm->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  };
  // en fonction du choix du compte de départ, afficher les infos avec le onChange javascript
  $codejs = "
            function getInfoCompte()
          {";
  if (isset($ListeComptes)) {
    foreach($ListeComptes as $key=>$value) {
      $codejs .= "
                 if (document.ADForm.HTML_GEN_LSB_cpt_from.value == " . $key.")
                 {
                 document.ADForm.HTML_GEN_LSB_etat_cpte.value = " . $value["etat_cpte"] . ";
                 document.ADForm.libel.value = \"".$value["libel"] . "\";
                 document.ADForm.HTML_GEN_LSB_devise.value = '".$value["devise"] . "';";
      if ($value["duree_min_retrait_jour"] > 0)
        $codejs .= "
                   document.ADForm.duree_min_retrait_jour.value = " . $value["duree_min_retrait_jour"].";";
      else
        $codejs .= "
                   document.ADForm.duree_min_retrait_jour.value = '0';
                 };";
    }
    $codejs .= "
               if (document.ADForm.HTML_GEN_LSB_cpt_from.value == '0')
             {
               document.ADForm.libel.value='';
               document.ADForm.mnt_min.value='';
               document.ADForm.HTML_GEN_LSB_etat_cpte.value='0';
               document.ADForm.duree_min_retrait_jour.value='';
             }";
  };
  $codejs .= "
           };getInfoCompte();";

  $myForm->setFieldProperties("cpt_from", FIELDP_JS_EVENT, array("onChange"=>"getInfoCompte();"));
  $myForm->addJS(JSP_FORM, "JS1", $codejs);


  // Boutons
  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ope-3");
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Ope-1");
  $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // Génération du code
  $myForm->buildHTML();
  echo $myForm->getHTML();

}

//--------------------------------------------------------------------------------------
//--------- Ope-3 : Ordre permanent : saisie du compte bénéficiaire---------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Ope-3') {
  if (isset($TypeTransfert))
    $SESSION_VARS["TypeTransfert"] = $TypeTransfert;
  if (isset($cpt_from))
    $SESSION_VARS["cpt_from"] = $cpt_from;

  //Affichage des informations compte source
  if ($SESSION_VARS['TypeTransfert'] >=1 && $SESSION_VARS['TypeTransfert'] <= 3) {
    switch ($SESSION_VARS['TypeTransfert']) {
    case 1:
      $myForm = new HTML_GEN2(_("Choix du compte destination (même client)"));
      break;
    case 2:
      $myForm = new HTML_GEN2(_("Choix du compte destination (autre client)"));
      break;
    case 3:
      // pas actif pour le moment, voir ticket:548
      $myForm = new HTML_GEN2(_("Choix du compte destination (compte externe)"));
      break;
    }

    $ValueCompte = getAccountDatas($SESSION_VARS["cpt_from"]);
    setMonnaieCourante($ValueCompte["devise"]);
    $soldeDispo=getSoldeDisponible($SESSION_VARS["cpt_from"]);
    $myForm->addTable("adsys_produit_epargne",OPER_INCLUDE, array("frais_transfert", "mnt_min"));
    $myForm->setFieldProperties("frais_transfert",FIELDP_DEFAULT,$ValueCompte["frais_transfert"]);
    $myForm->setFieldProperties("frais_transfert", FIELDP_CAN_MODIFY, true);
    $myForm->setFieldProperties("mnt_min", FIELDP_DEFAULT, $ValueCompte["mnt_min_cpte"]);
    $myForm->addField("soldeDispo", _("Solde disponible"), TYPC_MNT);
    $myForm->setFieldProperties("soldeDispo",FIELDP_DEFAULT,$soldeDispo);
    $champsCpte=array ("etat_cpte", "solde", "num_complet_cpte", "intitule_compte", "mnt_bloq", "mnt_bloq_cre");
    $myForm->addTable("ad_cpt",OPER_INCLUDE, $champsCpte);
    $fill=new FILL_HTML_GEN2();
    $fill->addFillClause("cpteSource", "ad_cpt");
    $fill->addCondition("cpteSource", "id_cpte", $SESSION_VARS["cpt_from"]);
    $fill->addManyFillFields("cpteSource", OPER_INCLUDE, $champsCpte);
    $fill->fill($myForm);
    $fieldslabel = array("etat_cpte", "solde", "num_complet_cpte", "intitule_compte", "mnt_bloq", "mnt_bloq_cre", "mnt_min", "soldeDispo", "frais_transfert");
    foreach($fieldslabel as $value) {
      $myForm->setFieldProperties($value, FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
    }
    $xtra1 = "<b>"._("Compte source")."</b>";
    $myForm->addHTMLExtraCode ("htm1", $xtra1);
    $myForm->setHTMLExtraCodeProperties ("htm1",HTMP_IN_TABLE, true);

    $xtra2 = "<b>"._("Autres informations")."</b>";
    $myForm->addHTMLExtraCode ("htm2", $xtra2);
    $myForm->setHTMLExtraCodeProperties ("htm2",HTMP_IN_TABLE, true);

  }
  //transfert sur le même client
  if ($SESSION_VARS["TypeTransfert"] == 1) {
    $choix3 = array();
    $TempListeComptes = get_comptes_epargne($global_id_client);
    if (isset($TempListeComptes)) {
      $ListeComptes2 = getComptesDepotPossible($TempListeComptes);
      if (isset($ListeComptes2)) {
        foreach($ListeComptes2 as $key=>$value) {
          //index par id_cpte pour la listbox; enlever le compte source s'il fait partie de la liste
          if ($value["id_cpte"] != $cpt_from) $choix3[$key] = $value["num_complet_cpte"]." ".$value["intitule_compte"];
        }
      };
    };
    $myForm->addField("cpt_to",_("Compte de destination"),TYPC_LSB);//enlever le compte source de la liste
    $myForm->setFieldProperties("cpt_to", FIELDP_ADD_CHOICES, $choix3);

    $myForm->addField("libel2",_("Libellé du produit d'épargne"),TYPC_TXT);
    $myForm->setFieldProperties("libel2", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("libel2", FIELDP_WIDTH, 40);

    //en fonction du choix du compte d'arrivée pour le même client, afficher les infos avec le onChange javascript
    $codejs = "\n\nfunction getInfoCompte2(){";
    if (isset($ListeComptes2)) {
      foreach($ListeComptes2 as $key=>$value) {
        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_cpt_to.value == " . $key;
        $codejs .= "){ \n\tdocument.ADForm.libel2.value = " . "'".$value["libel"] . "'".";";
        $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_cpt_to.value == document.ADForm.cpteSource.value ){alert('Les comptes souce et destination sont identiques');\n";
        $codejs .= "\n\t(document.ADForm.HTML_GEN_LSB_Cpt_to.value = 0);";
        $codejs .= "\n\t(document.ADForm.libel2.value = '')};\n";
        $codejs .= "}\n;";
      };
    };
    $codejs .= "}\n";
    $myForm->setFieldProperties("cpt_to", FIELDP_JS_EVENT, array("onChange"=>"getInfoCompte2();"));
    $myForm->addJS(JSP_FORM, "JS3", $codejs);

    $ChkJS = "\n\tif (document.ADForm.HTML_GEN_LSB_cpt_to.value == '0')";
    $ChkJS .= "{msg += '-"._("Vous devez saisir une valeur pour le compte client")."\\n'; ADFormValid=false;};\n";
    $myForm->addJS(JSP_BEGIN_CHECK, "JS4",$ChkJS);
    $myForm->addHTMLExtraCode("ligne_sep","<br />");
    $order=array("htm1", "num_complet_cpte", "intitule_compte", "solde", "etat_cpte", "mnt_bloq", "mnt_bloq_cre", "mnt_min", "frais_transfert","soldeDispo","ligne_sep","htm2", "cpt_to","libel2");
    $myForm->setOrder(NULL,$order);
    $myForm->setFieldProperties("frais_transfert", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("soldeDispo", FIELDP_IS_LABEL, true);

  }
  // transfert vers un autre client
  else if ($SESSION_VARS["TypeTransfert"] == 2) {
    $myForm->addField("cpt_dest",_("Compte destinataire"), TYPC_TXT);
    //$myForm->setFieldProperties("cpt_dest", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("cpt_dest", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("cpt_dest", FIELDP_IS_REQUIRED, true);
    
    $myForm->addLink("cpt_dest", "rechercher", _("Rechercher"), "#");
    $str = "OpenBrw('../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=cpt_dest&id_cpt_dest=cpt_to', 'Recherche');return false;";
    $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => $str));
    $myForm->addHiddenType("cpt_to", "");
    $myForm->addHTMLExtraCode("ligne_sep","<br />");
    $order=array("htm1", "num_complet_cpte", "intitule_compte", "etat_cpte", "solde", "mnt_bloq", "mnt_bloq_cre", "mnt_min", "soldeDispo", "frais_transfert", "ligne_sep","htm2", "cpt_dest");
    $myForm->setOrder(NULL,$order);
  }
  // transfert vers un compte externe (pas actif pour le moment, voir ticket:548)
  else if ($SESSION_VARS["TypeTransfert"] == 3 ) {

    $myForm->addField("nom_ben",_("Bénéficiaire"), TYPC_TXT);
    $myForm->setFieldProperties("nom_ben", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("nom_ben", FIELDP_WIDTH, 40);
    $myForm->addLink("nom_ben", "rechercher", _("Rechercher"), "#");
    $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('http://$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=b', '"._("Recherche")."');return false;"));
    $myForm->addHiddenType("id_ben", "");
    $checkJS = "
               if (document.ADForm.id_ben.value == '')
             {
               msg += '- "._("le Bénéficiaire n\'est pas renseigné")."\\n';
               ADFormValid=false;
             }
               ";

    $listeCorrespondant=getLibelCorrespondant();
    $myForm->addField("id_cor", _("Correspondant bancaire"), TYPC_LSB);
    $myForm->setFieldProperties("id_cor", FIELDP_ADD_CHOICES , $listeCorrespondant);
    $myForm->setFieldProperties("id_cor", FIELDP_IS_REQUIRED, true);


    $myForm->addHTMLExtraCode("ligne_sep","<br />");
    $order=array("htm1", "num_complet_cpte", "intitule_compte", "etat_cpte", "solde", "mnt_bloq", "mnt_bloq_cre", "mnt_min", "soldeDispo", "frais_transfert", "ligne_sep","htm2", "nom_ben", "id_cor");
    $myForm->setOrder(NULL,$order);
  }

  $myForm->addJS(JSP_BEGIN_CHECK, "JS1",$checkJS);

  $checkJS = "\t\tif (isBefore(document.ADForm.HTML_GEN_date_date_fin.value,document.ADForm.HTML_GEN_date_date_prem_exe)){\n";
  $checkJS .= "\t\t\tmsg += '- "._("la date de fin est inférieure à la date de proch. execution !")."\\n';ADFormValid=false;};\n";
  $myForm->addJS(JSP_BEGIN_CHECK, "jsdates",$checkJS);
  $checkJS = "\tif ((document.ADForm.HTML_GEN_date_date_fin.value != '') && (! isAfterToday(document.ADForm.HTML_GEN_date_date_fin.value))){\n";
  $checkJS .= "\t\t msg += '- "._("la date de fin est invalide")."\\n'; ADFormValid=false;}\n";
  $myForm->addJS(JSP_BEGIN_CHECK, "jsdatefin",$checkJS);
  $checkJS = "\tif (document.ADForm.num_complet_cpte.value == document.ADForm.cpt_dest.value){\n"; 
 	$checkJS .= "\t\t msg += '- Les comptes souce et destination sont identiques.\\n'; ADFormValid=false;}\n"; 
 	$myForm->addJS(JSP_BEGIN_CHECK, "jsdatecpte",$checkJS);
 	$checkJS = "\tif (document.ADForm.cpt_dest.value == ''){\n"; 
 	$checkJS .= "\t\t msg += '- Veuillez préciser le compte destinataire..\\n'; ADFormValid=false;}\n"; 
 	$myForm->addJS(JSP_BEGIN_CHECK, "jscptedest",$checkJS);


  $myForm->addField("date_prem_exe", _("Date de première exécution"), TYPC_DTG);
  $myForm->setFieldProperties("date_prem_exe", FIELDP_IS_REQUIRED, true);
  $myForm->addField("date_fin", _("Date de fin de validité"), TYPC_DTG);
  $myForm->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);
  $myForm->addField("nb_periode", _("Nombre de période"), TYPC_TXT);
  $myForm->setFieldProperties("nb_periode", FIELDP_IS_REQUIRED, false);
  $myForm->addField("montant", _("Montant"), TYPC_MNT);
  $myForm->setFieldProperties("montant", FIELDP_IS_REQUIRED, true);
  $myForm->addField("actif", _("Actif"), TYPC_BOL, 1);
  $myForm->setFieldProperties("actif", FIELDP_IS_REQUIRED, true);
  $myForm->addField("periodicite", _("Périodicité"), TYPC_LSB);
  $myForm->setFieldProperties("periodicite", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("periodicite",FIELDP_ADD_CHOICES,$adsys["adsys_periodicite_ordre_perm"]);
  $myForm->addField("interv", _("Intervalle de répetition"), TYPC_INT);
  $myForm->setFieldProperties("interv",FIELDP_DEFAULT,1);
  $myForm->addField("communication", _("Communication"), TYPC_TXT);
  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ope-4');
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Ope-2');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Ope-1');
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
} else if ($global_nom_ecran == 'Ope-4') {
  $SESSION_VARS["cpt_to"] = $cpt_to;
  $SESSION_VARS["id_benef"] = $id_ben;
  $SESSION_VARS["id_cor"] = $id_cor;
  $SESSION_VARS["date_prem_exe"] = $date_prem_exe;
  $SESSION_VARS["date_fin"] = $date_fin;
  $SESSION_VARS["periodicite"] = $periodicite;
  $SESSION_VARS["interv"] = $interv;
  if (isset($communication))            $SESSION_VARS['communication'] = $communication;
  $SESSION_VARS["frais_transfert"] = recupMontant($frais_transfert);
  $SESSION_VARS["montant"] = recupMontant($montant);
  $SESSION_VARS["actif"] = $actif;
  $SESSION_VARS["nb_periode"] = $nb_periode;
  $mnt_total_prevu = $nb_periode * recupMontant($montant);


  $DATA = array('cpt_from' => $SESSION_VARS["cpt_from"], 'cpt_to' => $SESSION_VARS["cpt_to"], 'date_prem_exe' => $SESSION_VARS["date_prem_exe"], 'date_fin' => $SESSION_VARS["date_fin"], 'montant' => $SESSION_VARS["montant"], 'actif' => $SESSION_VARS["actif"], 'periodicite' => $SESSION_VARS["periodicite"],'interv'=>$SESSION_VARS["interv"],'type_transfert'=> $SESSION_VARS["TypeTransfert"],'id_benef'=>$SESSION_VARS["id_benef"],'id_cor'=>$SESSION_VARS["id_cor"],'communication'=>$SESSION_VARS["communication"], 'nb_periode' => $SESSION_VARS["nb_periode"], 'mnt_total_prevu' => $mnt_total_prevu);
  if ( $frais_transfert )
    array_push_associative($DATA,array('frais_transfert'=>$SESSION_VARS["frais_transfert"]));

  // Verification si le montant est inferieur ou egal a la quotite disponible

  $data_agc = getAgenceDatas($global_id_agence);
  $data_cli = getClientDatas($global_id_client);
  if ($data_agc['quotite'] == 't'){
    //$quotite_dispo = get_quotite_client($global_id_client);
    $mnt_qutotite_dispo = $data_cli['mnt_quotite'];
    if ($mnt_qutotite_dispo <= $SESSION_VARS["montant"]){
      //$param = $MyErr->param;
      $html_err = new HTML_erreur(_("Echec de l'ajout de l'ordre permanent"));
      $msg = _("Erreur : Votre solde de quotite est insuffisant!");
      $html_err->setMessage($msg);
      $html_err->addButton(BUTTON_OK, 'Gen-10');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }
  }

  $result = ajouterOrdrepermanent($DATA);
  if ($data_agc['quotite'] == 't'){
    $quotite_dispo_apres = $mnt_qutotite_dispo - $SESSION_VARS["montant"];
    $DATA_QUOTITE = array();
    $DATA_QUOTITE["id_client"] = $global_id_client;
    $DATA_QUOTITE["quotite_avant"] = $mnt_qutotite_dispo;
    $DATA_QUOTITE["quotite_apres"] = $quotite_dispo_apres;
    $DATA_QUOTITE["mnt_quotite"] = $quotite_dispo_apres;
    $DATA_QUOTITE["date_modif"] = date('r');
    $DATA_QUOTITE["raison_modif"] = 'Ordres permanents';
    $ajout_quotite =ajouterQuotite($DATA_QUOTITE);


    $DATA_QUOTITE_UPDATE = array();
    $DATA_QUOTITE_WHERE = array();
    $DATA_QUOTITE_UPDATE["mnt_quotite"] = $quotite_dispo_apres;
    $DATA_QUOTITE_WHERE['id_client'] = $global_id_client;
    $update_client = update_quotite_client($DATA_QUOTITE_UPDATE,$DATA_QUOTITE_WHERE);
  }


  if ($result->errCode == NO_ERR) {
    $myForm = new HTML_message(_("Confirmation de l'ajout d'un ordre permanent"));
    $msg = _("L'ajout de l'ordre permanent s'est déroulé avec succès");
    $myForm->setMessage($msg);
    $myForm->addButton(BUTTON_OK, "Ope-1");
    $myForm->buildHTML();
    echo $myForm->HTML_code;
  }

}

//--------------------------------------------------------------------------------------
//--------- Ope-5 : Modification d'un ordre permanent ----------------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Ope-5') {
  global $dbHandler;

  $db = $dbHandler->openConnection();

  $SESSION_VARS['id_ord'] = $id_ord;
  // Génération du titre
  $myForm = new HTML_GEN2(_("Modification d'un ordre permanent"));

  if(($id_ord == null) or ($id_ord == '')){ 
 	   erreur("Odre permanent", sprintf(_("Le numéro de l'ordre permanent n'est pas renseigné."))); 
 	} else { 
 	   $sql = "select * from ad_ord_perm where id_ord = " . $id_ord ; 
 	}
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getCode() . ":" . $result->getMessage());
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getCode() . ":" . $result->getMessage());
  }
  $result->free();
  // obtenir le nom de compte
  $sql = "SELECT num_complet_cpte FROM ad_cpt WHERE id_cpte = ? ";
  $result=$db->prepare($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getCode() . ":" . $result->getMessage());
  }
  $result=$db->execute($sql,$row['cpt_from']);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getCode() . ":" . $result->getMessage());
  }
  $result->fetchinto($tempstr);
  $myForm->addField("cpt_from",_("Compte source"), TYPC_TXT);
  $myForm->setFieldProperties("cpt_from",FIELDP_IS_LABEL,true);
  $myForm->setFieldProperties("cpt_from",FIELDP_DEFAULT,$tempstr[0]);
  if (($row['type_transfert'] == 1) or ($row['type_transfert'] == 2)) {
    // obtenir le nom de compte de destination
    $sql = "SELECT num_complet_cpte FROM ad_cpt WHERE id_cpte = " . $row['cpt_to'];
  }
  if ($row['type_transfert'] == 3) {
    // benef est un compte externe
    $sql="SELECT denomination||' ('||num_cpte||')' FROM tireur_benef WHERE id = " . $row['id_benef'];
  }
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getCode() . ":" . $result->getMessage());
  }
  $result->fetchinto($tempstr);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getCode() . ":" . $result->getMessage());
  }
  $result->free();
  $myForm->addField("cpt_to",_("Compte Destination"), TYPC_TXT);
  $myForm->setFieldProperties("cpt_to",FIELDP_IS_LABEL,true);
  $myForm->setFieldProperties("cpt_to",FIELDP_DEFAULT,$tempstr[0]);
  if (dateCompare($row['date_prem_exe'],strtotime('now'))) {
    $myForm->addField("date_proch_exe",_("Date prochaine execution"),TYPC_DTG);
    $myForm->setFieldProperties("date_proch_exe",FIELDP_DEFAULT,$row['date_proch_exe']);
    $myForm->setFieldProperties("date_proch_exe",FIELDP_IS_REQUIRED,true);
  } else {
    $myForm->addField("date_prem_exe",_("Date première execution"),TYPC_DTG);
    $myForm->setFieldProperties("date_prem_exe",FIELDP_DEFAULT,$row['date_prem_exe']);
    $myForm->setFieldProperties("date_prem_exe",FIELDP_IS_REQUIRED,true);
  }

  $myForm->addField("date_fin",_("Date de fin"),TYPC_DTG) ;
  $myForm->setFieldProperties("date_fin",FIELDP_DEFAULT,$row['date_fin']);
  $myForm->addField("nb_periode",_("Nombre de période"),TYPC_TXT) ;
  $myForm->setFieldProperties("nb_periode",FIELDP_DEFAULT,$row['nb_periode']);
  $myForm->addField("montant",_("Montant"),TYPC_MNT) ;
  $myForm->setFieldProperties("montant",FIELDP_DEFAULT,$row['montant']);
  $myForm->addField("frais_transfert",_("Frais de transfert"),TYPC_MNT) ;
  $myForm->setFieldProperties("frais_transfert",FIELDP_DEFAULT,$row['frais_transfert']);
  $myForm->addField("periodicite",_("Périodicité"),TYPC_LSB) ;
  $myForm->setFieldProperties("periodicite",FIELDP_ADD_CHOICES,$adsys["adsys_periodicite_ordre_perm"]);
  $myForm->setFieldProperties("periodicite",FIELDP_DEFAULT,$row['periodicite']);
  $myForm->setFieldProperties("periodicite", FIELDP_IS_REQUIRED, true);
  $myForm->addField("interv",_("Intervalle de répétition"),TYPC_INT) ;
  $myForm->setFieldProperties("interv", FIELDP_DEFAULT, $row['interv']);
  $myForm->addField("actif", _("Actif"), TYPC_BOL, 1);
  $myForm->setFieldProperties("actif", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("actif", FIELDP_DEFAULT, $row['actif']);
  $myForm->addField("communication", _("Communication"), TYPC_TXT);
  $myForm->setFieldProperties("communication", FIELDP_DEFAULT, $row['communication']);

  $checkJS = "\t\tif (isBefore(document.ADForm.HTML_GEN_date_date_fin.value,document.ADForm.HTML_GEN_date_date_prem_exe)){\n";
  $checkJS .= "\t\t\tmsg += '- "._("la date de fin est inférieure à la date de proch. execution !")."\\n';ADFormValid=false;};\n";
  $myForm->addJS(JSP_BEGIN_CHECK, "jsdates",$checkJS);
  $checkJS = "\tif ((document.ADForm.HTML_GEN_date_date_fin.value != '') && (! isAfterToday(document.ADForm.HTML_GEN_date_date_fin.value))){\n";
  $checkJS .= "\t\t msg += '- "._("la date de fin est invalide")."\\n'; ADFormValid=false;}\n";
  $myForm->addJS(JSP_BEGIN_CHECK, "jsdatefin",$checkJS);

  // Boutons
  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Ope-6");
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Ope-1");
  $myForm->setFormButtonProperties("valider", BUTP_CHECK_FORM, true);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // Génération du code
  $myForm->buildHTML();
  echo $myForm->getHTML();

}

//--------------------------------------------------------------------------------------
//--------- Ope-6 : Confirmation de la modification d'un ordre ------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Ope-6') {

  $DATA['montant'] = recupMontant($montant);
  $DATA['frais_transfert'] = recupMontant($frais_transfert);
  $DATA['communication'] = $communication;
  $DATA['date_fin'] = $date_fin;
  $DATA['periodicite'] = $periodicite;
  $DATA['interv'] = $interv;
  $DATA['actif'] = $actif;
  $DATA['nb_periode'] = $nb_periode;
  $DATA['mnt_total_prevu'] = $nb_periode * recupMontant($montant);

  // modification quotite pour MA2E
  $data_agc = getAgenceDatas($global_id_agence);
  $data_cli = getClientDatas($global_id_client);
  if ($data_agc['quotite'] == 't') {
    $ord_perm = get_ordre_per($SESSION_VARS['id_ord']);
    if ($ord_perm['montant'] != $DATA['montant']){
      //$quotite_dispo = get_quotite_client($global_id_client);

      $quotite_dispo_apres = $data_cli["mnt_quotite"] + $ord_perm["montant"] - $DATA['montant'] ;
      $DATA_QUOTITE = array();
      $DATA_QUOTITE["id_client"] = $global_id_client;
      $DATA_QUOTITE["quotite_avant"] = $data_cli["mnt_quotite"];
      $DATA_QUOTITE["quotite_apres"] = $quotite_dispo_apres;
      $DATA_QUOTITE["mnt_quotite"] = $quotite_dispo_apres;
      $DATA_QUOTITE["date_modif"] = date('r');
      $DATA_QUOTITE["raison_modif"] = 'Ordres permanents';
      $ajout_quotite =ajouterQuotite($DATA_QUOTITE);


      $DATA_QUOTITE_UPDATE = array();
      $DATA_QUOTITE_WHERE = array();
      $DATA_QUOTITE_UPDATE["mnt_quotite"] = $quotite_dispo_apres;
      $DATA_QUOTITE_WHERE['id_client'] = $global_id_client;
      $update_client = update_quotite_client($DATA_QUOTITE_UPDATE,$DATA_QUOTITE_WHERE);
    }
  }


  $result=modifierOrdrepermanent($SESSION_VARS['id_ord'],$DATA);
  if ($result->errCode == NO_ERR) {
    $myForm = new HTML_message(_("Confirmation de la modification d'un ordre"));
    $msg = _("La modification de l'ordre s'est déroulée avec succès");
    $myForm->setMessage($msg);
    $myForm->addButton(BUTTON_OK, "Ope-1");
    $myForm->buildHTML();
    echo $myForm->HTML_code;
  }
}

//--------------------------------------------------------------------------------------
//--------- Ope-7 : Confirmation de la suppression d'un ordre --------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Ope-7') {
  $SESSION_VARS['id_ord'] = $id_ord;
  $myForm = new HTML_message(_('Demande confirmation'));
  $myForm->setMessage(_("Etes-vous sûr de vouloir supprimer l'ordre permanent ?"));

  $myForm->addButton(BUTTON_OUI, "Ope-8");
  $myForm->addButton(BUTTON_NON, "Ope-1");

  $myForm->buildHTML();
  echo $myForm->HTML_code;

}

//--------------------------------------------------------------------------------------
//--------- Ope-8 : Suppression d'un ordre permanent -----------------------------------
//--------------------------------------------------------------------------------------
else if ($global_nom_ecran == 'Ope-8') {
  $data_agc = getAgenceDatas($global_id_agence);
  $data_cli = getClientDatas($global_id_client);

  //suprpression quotite pour MA2E
  if ($data_agc['quotite'] == 't'){
    $ord_perm =get_ordre_per($SESSION_VARS['id_ord']);
    //$quotite_dispo = get_quotite_client($global_id_client);

    $quotite_dispo_apres = $data_cli["mnt_quotite"] + $ord_perm["montant"];
    $DATA_QUOTITE = array();
    $DATA_QUOTITE["id_client"] = $global_id_client;
    $DATA_QUOTITE["quotite_avant"] = $data_cli["mnt_quotite"];
    $DATA_QUOTITE["quotite_apres"] = $quotite_dispo_apres;
    $DATA_QUOTITE["mnt_quotite"] = $quotite_dispo_apres;
    $DATA_QUOTITE["date_modif"] = date('r');
    $DATA_QUOTITE["raison_modif"] = 'Ordres permanents';
    $ajout_quotite =ajouterQuotite($DATA_QUOTITE);


    $DATA_QUOTITE_UPDATE = array();
    $DATA_QUOTITE_WHERE = array();
    $DATA_QUOTITE_UPDATE["mnt_quotite"] = $quotite_dispo_apres;
    $DATA_QUOTITE_WHERE['id_client'] = $global_id_client;
    $update_client = update_quotite_client($DATA_QUOTITE_UPDATE,$DATA_QUOTITE_WHERE);
  }

  $result = deleteOrdrepermanent($SESSION_VARS['id_ord']);
  if ($result->errCode == NO_ERR) {
    $myForm = new HTML_message(_("Confirmation de la suppression d'un ordre"));
    $msg = _("La suppression de l'ordre s'est déroulée avec succès");
    $myForm->setMessage($msg);
    $myForm->addButton(BUTTON_OK, "Ope-1");
    $myForm->buildHTML();
    echo $myForm->HTML_code;
  } else {
  	$html_err = new HTML_erreur(_("Echec à la suppression de l'ordre permanent."));
    $html_err->setMessage(_("Erreur")." : " . $error[$result->errCode] . "<br />".-("Paramètre")." : " . $result->param);
    $html_err->addButton("BUTTON_OK", "Ope-1");
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}

//--------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------
//--------- Erreur ---------------------------------------------------------------------
//--------------------------------------------------------------------------------------
else signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>