<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Dépôt sur un compte d'épargne
 * @author Hassan Diallo
 * @author Olivier Luickx
 * @since 04/02/2002
 * @package Epargne
 **/

require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'modules/epargne/recu.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/dbProcedures/tireur_benef.php';
require_once 'lib/misc/divers.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'modules/rapports/xml_devise.php';
require_once 'lib/dbProcedures/billetage.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/VariablesGlobales.php';

/*{{{ Dcp-1 : Choix du compte */
if ($global_nom_ecran == "Dcp-1") {

  $html = new HTML_GEN2(_("Dépôt sur un compte : choix du compte"));

  //Affichage de tous les comptes du client
  $TempListeComptes = get_comptes_epargne($global_id_client);
  //Retirer de la liste les comptes à dépôt unique
  $choix = array();
  if (isset($TempListeComptes)) {
    $ListeComptes = getComptesDepotPossible($TempListeComptes);
    if (isset($ListeComptes))
      foreach($ListeComptes as $key=>$value)
      $choix[$key] = $value["num_complet_cpte"]." ".$value["intitule_compte"];//index par id_cpte pour la listbox
  }

  $html->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);
  //Affichage des valeurs précédemment saisies
  $html->setFieldProperties("NumCpte", FIELDP_DEFAULT, $SESSION_VARS['NumCpte']);
  $html->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);
  $html->setFieldProperties("NumCpte", FIELDP_IS_REQUIRED, true);

  //Ajout des tables
  $html->addTable("ad_cpt", OPER_INCLUDE, array("etat_cpte"));
  $html->addTable("adsys_produit_epargne", OPER_INCLUDE, array("libel", "devise"));

  $html->setFieldProperties("libel", FIELDP_WIDTH, 40);

  //Code HTML pour la présentation à l'écran
  $xtra1 = "<b>"._("Choix du compte")."</b>";
  $html->addHTMLExtraCode ("htm1", $xtra1);
  $html->setHTMLExtraCodeProperties ("htm1",HTMP_IN_TABLE, true);
  $xtra2 = "<b>"._("Choix du type de dépôt")."</b>";
  $html->addHTMLExtraCode ("htm2", $xtra2);
  $html->setHTMLExtraCodeProperties ("htm2",HTMP_IN_TABLE, true);

  //Transformer les champs en labels non modifiables
  $fieldslabel = array("etat_cpte", "libel", "devise");
  foreach($fieldslabel as $value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
    $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
  }

  //En fonction du choix du compte, afficher les infos avec le onChange javascript
  $codejs = "\n\nfunction getInfoCompte() {";
  if (isset($ListeComptes)) {
    foreach($ListeComptes as $key=>$value) {
      $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_NumCpte.value == $key)\n\t";
      $codejs .= "{\n\t\tdocument.ADForm.HTML_GEN_LSB_etat_cpte.value = " . _($value["etat_cpte"]) . ";";
      $codejs .= "\n\t\tdocument.ADForm.libel.value = \"" . $value["libel"] . "\";";
      $codejs .= "\n\t\tdocument.ADForm.HTML_GEN_LSB_devise.value = '" . $value["devise"] . "';";
      $codejs .= "};\n";
    }
    $codejs .= "\n\tif (document.ADForm.HTML_GEN_LSB_NumCpte.value =='0') {";
    $codejs .= "\n\t\tdocument.ADForm.libel.value='';";
    $codejs .= "\n\t\tdocument.ADForm.HTML_GEN_LSB_etat_cpte.value='0';";
    $codejs .= "\n\t}\n";
  }
  $codejs .= "}\ngetInfoCompte();";

  $html->setFieldProperties("NumCpte", FIELDP_JS_EVENT, array("onChange"=>"getInfoCompte();"));
  $html->addJS(JSP_FORM, "JS3", $codejs);

  $html->addField("type_depot", _("Type de dépôt"), TYPC_LSB);
  $choix2 = array();
  $choix2[1]=_('Dépôt en espèce');
  $choix2[2]=_('Dépôt par chèque');
  $choix2[3]=_('Dépôt par ordre de payement');
  $choix2[5]=_('Dépôt par Travelers Cheque');

  foreach($choix2 as $key=>$value) {
    //Type de dépôt autorisé : 1:espèce, 2:chèque, 3:ordre de paiement, 5:Travelers cheque
    if ($key!=1 and $key!=2 and $key!=3 and $key!=5) {
      unset($choix2[$key]);
    }
  }
  $html->setFieldProperties("type_depot", FIELDP_ADD_CHOICES, $choix2);
  $html->setFieldProperties("type_depot", FIELDP_IS_REQUIRED, true);

  //Affichage des valeurs précédemment saisies
  $html->setFieldProperties("type_depot",FIELDP_DEFAULT,$SESSION_VARS['type_depot']);

  //Ordonner les champs pour l'affichage
  $html->setOrder(NULL, array("htm1","NumCpte","libel", "devise", "etat_cpte", "htm2", "type_depot"));

  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dcp-2');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');

  $html->buildHTML();
  echo $html->getHTML();
}
/*}}}*/

/*{{{ Dcp-2 : Choix du compte et type de dépôt */
else if ($global_nom_ecran == "Dcp-2") {
  global $global_multidevise;
  if (isset($NumCpte)) $SESSION_VARS["NumCpte"] = $NumCpte;
  if (isset($type_depot)) $SESSION_VARS["type_depot"] = $type_depot;
  if (isset($SESSION_VARS['id_pers_ext'])) unset ($SESSION_VARS['id_pers_ext']);

  //Afficher la liste des comptes du client puis le montant à déposer et ne pas oublier les frais d'opérations sur compte éventuels
  $html = new HTML_GEN2(_("Dépôt sur un compte"));

  $infoCpte=getAccountDatas($SESSION_VARS['NumCpte']);
  setMonnaieCourante($infoCpte["devise"]);
  $MANDATS = getListeMandatairesActifs($SESSION_VARS['NumCpte'], true);
  if ($MANDATS != NULL) {
    foreach($MANDATS as $key=>$value) {
      $MANDATS_LSB[$key] = $value['libelle'];
      if ($key == 'CONJ') {
        $JS_open .=
          "if (document.ADForm.HTML_GEN_LSB_mandat.value == '$key')
        {
          OpenBrw('$SERVER_NAME/modules/externe/info_mandat.php?m_agc=".$_REQUEST['m_agc']."&id_cpte=".$SESSION_VARS['NumCpte']."');
          return false;
        }";
      } else {
        $JS_open .=
          "if (document.ADForm.HTML_GEN_LSB_mandat.value == $key)
        {
          OpenBrw('$SERVER_NAME/modules/externe/info_mandat.php?m_agc=".$_REQUEST['m_agc']."&id_mandat=$key');
          return false;
        }";
      }
    }
  }

  $JS_change =
    "if (document.ADForm.HTML_GEN_LSB_mandat.value != 'EXT')
  {
    document.ADForm.denomination.value = '';
    document.ADForm.id_pers_ext.value = '';
  }";

  $html->addField("mandat", _("Donneur d'ordre"), TYPC_LSB);
  $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("0" => _("Titulaire (".getClientName($global_id_client).")")));
  if ($MANDATS_LSB != NULL) {
    $MANDATS_LSB = array_flip($MANDATS_LSB); // array(valeur = >cle) au lieu de array(cle => valeur)
    unset($MANDATS_LSB[getClientName($global_id_client)]); //on supprime le nom du titulaire dans la liste déroulante
    $MANDATS_LSB = array_flip($MANDATS_LSB); // on remet le array(cle => valeur)
    $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, $MANDATS_LSB);
  }
  $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_AUCUN, false);
  $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_TOUS, false);
  $html->setFieldProperties("mandat", FIELDP_JS_EVENT, array("onchange" => $JS_change));
  $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("EXT" => _("Personne non cliente")));
  $html->setFieldProperties("mandat", FIELDP_DEFAULT, $SESSION_VARS['id_mandat']);
  $html->addLink("mandat", "afficher", _("Afficher"), "#");
  $html->setLinkProperties("afficher", LINKP_JS_EVENT, array("onclick" => $JS_open));
  $SESSION_VARS['mandat'] = $MANDATS_LSB;
  $JS_rech =
    "if (document.ADForm.HTML_GEN_LSB_mandat.value == 'EXT')
  {
    OpenBrw('$SERVER_NAME/modules/externe/gest_pers_ext.php?m_agc=".$_REQUEST['m_agc']."&denom=denomination&pers_ext=id_pers_ext');
    return false;
  }";

  $include = array("denomination");
  $html->addTable("ad_pers_ext", OPER_INCLUDE, $include);
  $html->setFieldProperties("denomination", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("denomination", FIELDP_IS_REQUIRED, false);
  $html->setFieldProperties("denomination", FIELDP_DEFAULT, $SESSION_VARS['denomination']);
  $html->addLink("denomination", "rech_pers_ext", _("Rechercher"), "#");
  $html->setLinkProperties("rech_pers_ext", LINKP_JS_EVENT, array("onclick" => $JS_rech));

  $html->addHiddenType("id_pers_ext", $SESSION_VARS['id_pers_ext']);

  $JS_check =
    "if (document.ADForm.HTML_GEN_LSB_mandat.value == 'EXT' && document.ADForm.id_pers_ext.value == '')
  {
    msg += ' - "._("Vous devez choisir une personne non cliente")."\\n';
    ADFormValid=false;
  }";
  $html->addJS(JSP_BEGIN_CHECK, "JS1", $JS_check);

  $html->addHTMLExtraCode("mandat_sep", "<br/>");

  $xtra1 = "<b>"._("Compte sélectionné")."</b>";
  $html->addHTMLExtraCode ("htm1", $xtra1);
  $html->setHTMLExtraCodeProperties ("htm1", HTMP_IN_TABLE, true);
  $xtra2 = "<b>"._("Frais / Montant du dépôt")."</b>";
  $html->addHTMLExtraCode ("htm2", $xtra2);
  $html->setHTMLExtraCodeProperties ("htm2", HTMP_IN_TABLE, true);

  $html->addField("NumCpte", _("Numéro de compte"), TYPC_TXT);
  $html->setFieldProperties("NumCpte", FIELDP_DEFAULT, $infoCpte["num_complet_cpte"]." ".$infoCpte["intitule_compte"]);
  $html->setFieldProperties("NumCpte", FIELDP_IS_LABEL, true);

  //Ajout des tables
  $access_solde = get_profil_acces_solde($global_id_profil, $infoCpte["id_prod"]);
  $access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $global_id_client);
  if(manage_display_solde_access($access_solde, $access_solde_vip)){
  	$champsCpte = array("etat_cpte", "solde");
  	$labelField = array("etat_cpte", "solde");
  }else{
  	$champsCpte = array("etat_cpte");
  	$labelField = array("etat_cpte");
  }

  $html->addTable("ad_cpt", OPER_INCLUDE, $champsCpte);
  $html->addTable("adsys_produit_epargne", OPER_INCLUDE, array("libel", "mnt_max", "frais_depot_cpt"));
  $html->setFieldProperties("frais_depot_cpt", FIELDP_CAN_MODIFY, true);

  $fill = new FILL_HTML_GEN2();
  $fill->addFillClause("compte", "ad_cpt");
  $fill->addCondition("compte", "id_cpte", $SESSION_VARS['NumCpte']);
  $fill->addManyFillFields("compte", OPER_INCLUDE, $champsCpte);
  $fill->addFillClause("produit_epargne", "adsys_produit_epargne");
  $fill->addCondition("produit_epargne", "id", $infoCpte["id_prod"]);
  $fill->addManyFillFields("produit_epargne", OPER_INCLUDE, array("libel", "mnt_max", "frais_depot_cpt"));
  $fill->fill($html);

  foreach($labelField as $key=>$value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
  }
  if(manage_display_solde_access($access_solde, $access_solde_vip))
  	$ordre = array("mandat", "denomination", "mandat_sep", "htm1" ,"NumCpte", "libel", "solde", "mnt_max", "etat_cpte");
  else
  	$ordre = array("mandat", "denomination", "mandat_sep", "htm1" ,"NumCpte", "libel", "mnt_max", "etat_cpte");
  $html->setFieldProperties("libel", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("libel", FIELDP_IS_REQUIRED, false);
  $html->setFieldProperties("mnt_max", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("mnt_max", FIELDP_IS_REQUIRED, false);
  $html->setFieldProperties("frais_depot_cpt", FIELDP_IS_LABEL, true);
  $html->setFieldProperties("frais_depot_cpt", FIELDP_IS_REQUIRED, false);

  //Champs pour le dépôt au guichet
  $html->addField("mnt",_("Montant déposé"),TYPC_MNT);
  $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS['mnt']);
  $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);

  if ($global_multidevise) {
    $html->addField("mnt_cv", _("Montant guichet/chèque"), TYPC_DVR);
    $html->setFieldProperties("mnt_cv", FIELDP_IS_REQUIRED, true);
    $html->linkFieldsChange("mnt_cv", "mnt", "achat", 1, true);
    $html->add_js_enable_disable("mnt_cv");
    if (is_array($SESSION_VARS['mnt_cv'])) {
      if ($SESSION_VARS['mnt_cv']['devise'] == $infoCpte['devise']) {
        $html->setFieldProperties("mnt_cv", FIELDP_DEFAULT, $SESSION_VARS['mnt']);
        $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $SESSION_VARS['mnt_cv']['devise']);
        $html->setFieldProperties("mnt_cv", FIELDP_IS_LABEL, true);
      } else {
        $html->setFieldProperties("mnt_cv", FIELDP_DEFAULT, $SESSION_VARS['mnt_cv']['cv']);
        $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $SESSION_VARS['mnt_cv']['devise']);
        $html->setFieldProperties("mnt_cv", FIELDP_IS_LABEL, false);
        $html->addJS(JSP_FORM, "js_mnt_cv_taux", "document.ADForm.HTML_GEN_dvr_mnt_cv_taux.value = '".$SESSION_VARS['mnt_cv']['taux']."';\n");
        $html->addJS(JSP_FORM, "js_mnt_cv_comm_nette", "document.ADForm.HTML_GEN_dvr_mnt_cv_comm_nette.value = '".$SESSION_VARS['mnt_cv']['comm_nette']."';\n");
        $html->addJS(JSP_FORM, "js_mnt_cv_dest_reste", "document.ADForm.HTML_GEN_dvr_mnt_cv_dest_reste.value = '".$SESSION_VARS['mnt_cv']['dest_reste']."';\n");
      }
    }
  }

   array_push($ordre,"htm2", "frais_depot_cpt", "mnt");


  //Champs pour le dépôt par chèque
  if ($SESSION_VARS['type_depot'] != 1) {
    $xtra3 = "<b>"._("Dépôt par chèque / ordre de payement")."</b>";
    $html->addHTMLExtraCode ("htm3", $xtra3);
    $html->setHTMLExtraCodeProperties ("htm3", HTMP_IN_TABLE, true);

    $html->addField("num_chq", _("Numéro"), TYPC_TXT);
    $html->setFieldProperties("num_chq", FIELDP_DEFAULT, $SESSION_VARS['num_chq']);
    $html->setFieldProperties("num_chq", FIELDP_IS_REQUIRED, true);

    array_push($ordre, "htm3", "num_chq", "date_chq");

    $html->addField("date_chq", _("Date"), TYPC_DTE);
    $html->setFieldProperties("date_chq",  FIELDP_HAS_CALEND, false);
    $html->addLink("date_chq", "calendrier1", _("Calendrier"), "#");
    $html->setFieldProperties("date_chq", FIELDP_DEFAULT, $SESSION_VARS['date_chq']);
    $html->setFieldProperties("date_chq", FIELDP_IS_REQUIRED, true);

    $JS_code =
      "if (! isDate(document.ADForm.HTML_GEN_date_date_chq.value))
    {
      document.ADForm.HTML_GEN_date_date_chq.value='';
    }
      open_calendrier(getMonth(document.ADForm.HTML_GEN_date_date_chq.value), getYear(document.ADForm.HTML_GEN_date_date_chq.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_date_chq');
      return false;
      ";
    $html->setLinkProperties("calendrier1", LINKP_JS_EVENT, array("onclick" => $JS_code));

    if ($SESSION_VARS['type_depot'] != 5) {
      //Informations bénéficiaire
      $html->addField("nom_ben", _("Nom du tireur"), TYPC_TXT);
      $html->setFieldProperties("nom_ben", FIELDP_DEFAULT, $SESSION_VARS['nom_ben']);
      $html->setFieldProperties("nom_ben", FIELDP_IS_REQUIRED, true);
      $html->setFieldProperties("nom_ben", FIELDP_IS_LABEL, true);
      $html->setFieldProperties("nom_ben", FIELDP_WIDTH, 24);
      $html->addLink("nom_ben", "rechercher", _("Rechercher"), "#");
      $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=t', '"._("Recherche")."'); return false;"));
      if ($global_nom_ecran_prec == 'Dcp-3') {
        $html->addHiddenType("id_ben",$SESSION_VARS['id_ben']);
        $info_ben = getTireurBenefDatas($SESSION_VARS['id_ben']);
        $html->setFieldProperties("nom_ben", FIELDP_DEFAULT, $info_ben['denomination']);
      } else {
        $html->addHiddenType("id_ben","");
      }

      //Correspondant bancaire
      $libel_correspondant=getLibelCorrespondant();
      $html->addField("correspondant", _("Correspondant bancaire"), TYPC_LSB);
      $html->setFieldProperties("correspondant", FIELDP_DEFAULT, $SESSION_VARS['id_correspondant']);
      $html->setFieldProperties("correspondant", FIELDP_IS_REQUIRED, true);
      $html->setFieldProperties("correspondant", FIELDP_ADD_CHOICES, $libel_correspondant);

      array_push($ordre, "nom_ben", "correspondant");

      $JS_check =
        "if (document.ADForm.id_ben.value == '')
      {
        msg += ' - "._("Le champ \"Tireur\" doit être renseigné")."\\n';
        ADFormValid=false;
      }";
      $html->addJS(JSP_BEGIN_CHECK, "JS2", $JS_check);
    }

    //validation chèque et ordre de paiement
    if($SESSION_VARS['type_depot'] == 2 or $SESSION_VARS['type_depot'] == 3){

      $chqValidite = getValidityChequeDate();
      $chqOrdVal = $chqValidite['validite_chq_ord'];
      $ordpayVal = $chqValidite['validite_ord_pay'];

      $typedepot = $SESSION_VARS['type_depot'];

      $JS_validity = "\n";
      $JS_validity = "
               function validChqOrdre()
                {
                    var num_chq = document.ADForm.num_chq;
                    var date_chq = document.ADForm.HTML_GEN_date_date_chq;

                    var isChq = $typedepot == 2;

                      if(date_chq.value != '' && isDate(date_chq.value))
                      {
                          var now = \"" . date("d/m/Y") . "\"
                          var isValid = checkDateRange(isChq?$chqOrdVal:$ordpayVal, date_chq.value, now);

                          if(!isValid)
                          {
                            if(isChq)
                            {
                              msg += ' - La validité du chèque dépasse le nombre de jours autorisé !\\n';
                            }
                            else
                            {
                              msg += ' - La validité de l\'ordre de paiement dépasse le nombre de jours autorisé !\\n';
                            }
                            ADFormValid=false;
                          }
                      }
                }

                validChqOrdre();
                \n";

      $html->addJS(JSP_BEGIN_CHECK, "JSchq",$JS_validity);
    }

  }

  $xtra4 = "<b>"._("Communication / remarque")."</b>";
  $html->addHTMLExtraCode ("htm4", $xtra4);
  $html->setHTMLExtraCodeProperties ("htm4", HTMP_IN_TABLE, true);

  //Communication
  $html->addField("communication", _("Communication"), TYPC_TXT);
  $html->setFieldProperties("communication",FIELDP_DEFAULT,$SESSION_VARS['communication']);

  //Remarque
  $html->addField("remarque", _("Remarque"), TYPC_ARE);
  $html->setFieldProperties("remarque",FIELDP_DEFAULT,$SESSION_VARS['remarque']);

  array_push($ordre, "htm4", "communication", "remarque");

  //Ordonner les champs pour l'affichage
  $html->setOrder(NULL, $ordre);

  //Code javascript de vérification au moment de la validation
  $JS_check =
    "if ((recupMontant(document.ADForm.mnt_max.value) > 0) && (recupMontant(document.ADForm.mnt.value) > recupMontant(document.ADForm.mnt_max.value)))
  {
    msg += ' - "._("Le montant est supérieur au montant maximum")."\\n';
    ADFormValid=false;
  }
    if ((recupMontant(document.ADForm.mnt_max.value) > 0 ) && ((recupMontant(document.ADForm.mnt.value) + ".$infoCpte["solde"].") > recupMontant(document.ADForm.mnt_max.value)))
  {
    msg += ' - "._("Le montant à déposer rendra le solde supérieur au montant maximum autorisé")."\\n';
    ADFormValid=false;
  }";

  $html->addJS(JSP_BEGIN_CHECK, "JS3", $JS_check);
  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
  $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dcp-3');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Dcp-1');

  $html->buildHTML();
  echo $html->getHTML();
}
/*}}}*/

/*{{{ Dcp-3 : Confirmation du Montant à déposer */
else if ($global_nom_ecran == "Dcp-3") {
  global $global_id_client, $dbHandler, $global_id_agence, $global_mouvements;
  // /REM/ $mnt_cv est un Array qui n'est posté qu'en mode multidevise !!
  if ($global_multidevise) {
    $SESSION_VARS["mnt_cv"] = $mnt_cv;
  } else {
    // Fabrication de l'array $mnt_cv comme si on était en multidevise
    $mnt_cv = array("devise" => $global_monnaie);
    $SESSION_VARS["mnt_cv"] = $mnt_cv;
  }

  if (isset($mnt))			$SESSION_VARS["mnt"]		= recupMontant($mnt);
  if (isset($frais_depot_cpt))	$SESSION_VARS["frais_depot_cpt"]= recupMontant($frais_depot_cpt);
  if (isset($num_chq))		$SESSION_VARS["num_chq"]	= $num_chq;
  if (isset($date_chq))		$SESSION_VARS["date_chq"]	= $date_chq;
  if (isset($correspondant))		$SESSION_VARS["id_correspondant"]= $correspondant;
  if (isset($id_ben))		$SESSION_VARS["id_ben"]		= $id_ben;
  if (isset($remarque))		$SESSION_VARS["remarque"]	= $remarque;
  if (isset($communication))		$SESSION_VARS["communication"]	= $communication;

  if ( isset($SESSION_VARS['id_mandat'])) unset ($SESSION_VARS['id_mandat']);
  if ($mandat == 'EXT') {
    $SESSION_VARS['id_pers_ext'] = $id_pers_ext;
    $SESSION_VARS['denomination'] = $denomination;
    
  } elseif ($mandat != 0 && $mandat != 'CONJ') {
    $MANDAT = getInfosMandat($mandat);
    if($SESSION_VARS['mandat'][$mandat] == getClientName($global_id_client)){
    	$SESSION_VARS['id_pers_ext'] = NULL ;
    }else{
    	$SESSION_VARS['id_mandat']=$MANDAT['id_mandat'];
    	$SESSION_VARS['id_pers_ext'] = $MANDAT['id_pers_ext'];
    }
  } elseif($mandat == ''){
  	$SESSION_VARS['id_pers_ext'] = NULL ;
  } elseif ($mandat == 'CONJ') {
    $infos_pers_ext = getInfosPersExt($SESSION_VARS['mandat']['CONJ']);
    $SESSION_VARS['id_pers_ext'] = $infos_pers_ext['id_pers_ext'];
   
    
  }

  // Recherche des données des différents opérateurs (banque, tireur, correspondant, ...)
  if (isset($SESSION_VARS['id_correspondant'])) {
    $infosCorrespondant = getInfosCorrespondant($SESSION_VARS['id_correspondant']);
  }
  if (isset($SESSION_VARS['id_ben']) && $SESSION_VARS['id_ben']!='') {
    $majTireur = setTireur($SESSION_VARS['id_ben']);
    $infoTireur=getTireurBenefDatas($SESSION_VARS["id_ben"]);
    $infosbanque = getInfosBanque($infoTireur['id_banque']);
    $SESSION_VARS["banque"] = $infosbanque['nom_banque'];
  }
  $InfoCpte = getAccountDatas  ($SESSION_VARS["NumCpte"]);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

  if (!isset($frais_depot_cpt))
    $SESSION_VARS["frais_depot_cpt"] = $InfoProduit["frais_depot_cpt"];

  // Dans le cas d'un chèque, on vérifie que la devise est identique à celle des comptes du Correspondant et qu'elle est bien paramétrée dans la table correspondant.
  $message_erreur=NULL;
  if ($infosCorrespondant['devise']==NULL && $SESSION_VARS['type_depot']==2) {
    $message_erreur=_("Les comptes du correspondant ont des devises différentes.")."<br />";
    $message_erreur.=sprintf(_("Veuillez changer les paramètres de %s avant de continuer"), $infosCorrespondant['nom_banque']."-".$infosCorrespondant['numero_cpte'])."<br /><br />";
  }
  if ($infosCorrespondant['devise']!=$SESSION_VARS['mnt_cv']['devise'] && $SESSION_VARS['type_depot']==2 && $message_erreur==NULL) {
    $message_erreur.=sprintf(_("La devise du chèque (%s) est différente de la devise des comptes du Correspondant (%s)"),$SESSION_VARS['mnt_cv']['devise'],$infosCorrespondant['devise']);
  }

  // Construction du formulaire
  if ($message_erreur != NULL) {
    $html = new HTML_erreur(_("Erreur de paramétrage"));
    $html->setMessage($message_erreur);
    $html->addButton("BUTTON_OK", 'Dcp-2');
    $html->buildHTML();
    echo $html->HTML_code;
  } else {
    $html = new HTML_GEN2(_("Confirmation du montant à déposer"));

    if (($global_multidevise) && ( $InfoCpte["devise"] != $mnt_cv["devise"] )) {	// Dépôt au guichet, avec change.
      $champ_mnt = "mnt_cv";

      $html->addField("mnt",_("Montant déposé sur le compte"),TYPC_MNT);

      $html->addField("mnt_cv",_("Montant déposé au guichet"),TYPC_MNT);
      $html->setFieldProperties("mnt_cv", FIELDP_DEFAULT, $mnt_cv["cv"]);
      $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $mnt_cv["devise"]);
      $html->setFieldProperties("mnt_cv", FIELDP_IS_LABEL, true);

      $html->addField("mnt_reel",_("Confirmation montant"),TYPC_MNT);
      $html->setFieldProperties("mnt_reel", FIELDP_DEVISE, $mnt_cv["devise"]);

      $html->addField("taux",_("Taux"),TYPC_TXT);
      $html->setFieldProperties("taux", FIELDP_DEFAULT, $mnt_cv["taux"]);
      $html->setFieldProperties("taux", FIELDP_IS_LABEL, true);

      $html->addField("un_sur_taux",_("1 / Taux"),TYPC_TXT);
      $html->setFieldProperties("un_sur_taux", FIELDP_DEFAULT, 1/$mnt_cv["taux"]);
      $html->setFieldProperties("un_sur_taux", FIELDP_IS_LABEL, true);

      $html->addField("comm_nette",_("Commission nette"),TYPC_MNT);
      $html->setFieldProperties("comm_nette", FIELDP_DEFAULT, $mnt_cv["comm_nette"]);
      $html->setFieldProperties("comm_nette", FIELDP_IS_LABEL, true);
      if($SESSION_VARS['mnt_cv']['reste'] > 0){
 	      setMonnaieCourante($global_monnaie);
 	      $html->addField("reste",_("Reste à toucher"),TYPC_MNT);
 	      $html->setFieldProperties("reste", FIELDP_DEFAULT, $SESSION_VARS["mnt_cv"]['reste']);
 	      $html->setFieldProperties("reste", FIELDP_IS_LABEL, true);
 	      if ($SESSION_VARS["mnt_cv"]["dest_reste"] == 1) { // Le reste doit etre remis en cash
 	         $html->addField("conf_reste", "Confirmation du reste remis au guichet", TYPC_MNT);
 	         $html->setFieldProperties("conf_reste", FIELDP_HAS_BILLET, true);
 	         $html->setFieldProperties("conf_reste", FIELDP_IS_REQUIRED, true);
 	      }
 	    }
      $html->addTableRefField("dest_reste",_("Destination du reste"),"adsys_change_dest_reste");
      $html->setFieldProperties("dest_reste", FIELDP_DEFAULT, $mnt_cv["dest_reste"]);
      $html->setFieldProperties("dest_reste", FIELDP_IS_LABEL, true);
    } else {
      $champ_mnt = "mnt";

      $html->addField("mnt",_("Montant déposé au guichet"),TYPC_MNT);

      $html->addField("mnt_reel",_("Confirmation montant"),TYPC_MNT);
      $html->setFieldProperties("mnt_reel", FIELDP_DEVISE, $InfoCpte["devise"]);
    }

    $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS["mnt"]);
    $html->setFieldProperties("mnt", FIELDP_DEVISE, $InfoCpte["devise"]);
    $html->setFieldProperties("mnt", FIELDP_IS_LABEL, true);

    $html->setFieldProperties("mnt_reel", FIELDP_IS_REQUIRED, true);
    if ($SESSION_VARS['type_depot']==1) $html->setFieldProperties("mnt_reel", FIELDP_HAS_BILLET, true);

    setMonnaieCourante($InfoCpte['devise']);

    $set_monnaie_devise=$InfoCpte['devise'];
    $html->addField("frais_depot", _("Frais de dépot"), TYPC_MNT);
    $html->setFieldProperties("frais_depot", FIELDP_DEFAULT, $SESSION_VARS["frais_depot_cpt"]);
    $html->setFieldProperties("frais_depot", FIELDP_IS_LABEL, true);
    
    /* Printer Type
    $html->addField("type_imprimante", _("Type de imprimante"), TYPC_LSB);
    $html->setFieldProperties("type_imprimante", FIELDP_IS_REQUIRED, true);
    $html->setFieldProperties("type_imprimante", FIELDP_DEFAULT, $SESSION_VARS["type_imprimante"]);
    $choixImprimante=array();
    //les clés des choix correspondent à la table $adsys['adsys_type_piece_payement']
    $choixImprimante['A4']=_('A4 Format');
    $choixImprimante['A5']=_('A5 Format');
    $choixImprimante['A6']=_('A6 Format');
    $choixImprimante['CT']=_('COOPEC TWIZIGAMIRE');
    $choixImprimante['JALI']=_('Jali');
    $choixImprimante['TWG']=_('Twizigamire');
    $choixImprimante['DTB']=_('Duterimbere');
    $html->setFieldProperties("type_imprimante", FIELDP_ADD_CHOICES, $choixImprimante);*/



    if (($SESSION_VARS["type_depot"] >= 2) && ($SESSION_VARS["type_depot"] <= 5)) { //dépôt par chèque/virement/traveler
      $xtra1 = "<b>"._("Informations chèque")."</b>";
      $html->addHTMLExtraCode ("htm1", $xtra1);
      $html->setHTMLExtraCodeProperties ("htm1",HTMP_IN_TABLE, true);

      if ($SESSION_VARS["type_depot"] != 5) {
        $html->addField("banque", _("Banque"), TYPC_TXT);
        $html->setFieldProperties("banque", FIELDP_DEFAULT, $infosCorrespondant["nom_banque"]);
        $html->setFieldProperties("banque", FIELDP_IS_LABEL, true);
      }

      $html->addField("num_chq", _("Numéro de chèque"), TYPC_TXT);
      $html->setFieldProperties("num_chq", FIELDP_DEFAULT, $SESSION_VARS["num_chq"]);
      $html->setFieldProperties("num_chq", FIELDP_IS_LABEL, true);

      $html->addField("date_chq", _("Date du chèque"), TYPC_DTE);
      $html->setFieldProperties("date_chq", FIELDP_DEFAULT, $SESSION_VARS["date_chq"]);
      $html->setFieldProperties("date_chq", FIELDP_IS_LABEL, true);

      if ($SESSION_VARS["type_depot"] != 3) { //Pas de choix de traitement en cas de virement de l'extérieur
        $html->addField("trait", _("Traitement"), TYPC_LSB);
        $choix2 = array(1=>_("Mise en attente"), 2=>_("Crédit direct sauf bonne fin"));
        $html->setFieldProperties("trait", FIELDP_ADD_CHOICES, $choix2);
        $html->setFieldProperties("trait", FIELDP_IS_REQUIRED, true);
        $html->setFieldProperties("trait", FIELDP_JS_EVENT, array("onChange"=>"setCommCredit();"));

        $codejs = "\n document.ADForm.comm_credit.disabled=true;";
        $codejs .= "\nfunction setCommCredit() {";
        $codejs .= "\nif (document.ADForm.HTML_GEN_LSB_trait.value == 2){";
        $codejs .= "\n document.ADForm.comm_credit.disabled=false;";
        $codejs .= "\n} else {";
        $codejs .= "\n document.ADForm.comm_credit.disabled=true;\n}\n}";

        $html->addJS(JSP_FORM, "JS_trait", $codejs);
        $html->addField("comm_credit",_("Commission liée au crédit direct"),TYPC_MNT);
        $html->setFieldProperties("comm_credit", FIELDP_DEVISE, $InfoCpte['devise']);
        $html->setFieldProperties("comm_credit", FIELDP_IS_REQUIRED,false);
      }
      if ($SESSION_VARS["type_depot"] != 5) { //On n'affiche pas d'infos sur le tireur et le correspondant en cas de Travelers cheque
        $xtra2 = "<b>"._("Informations tireur")."</b>";
        $html->addHTMLExtraCode ("htm2", $xtra2);
        $html->setHTMLExtraCodeProperties ("htm2",HTMP_IN_TABLE, true);

        $html->addField("denomination", _("Dénomination"), TYPC_TXT);
        $html->setFieldProperties("denomination", FIELDP_DEFAULT, $infoTireur["denomination"]);
        $html->setFieldProperties("denomination", FIELDP_IS_LABEL, true);
        $xtra3 = "<b>"._("Informations Correspondant")."</b>";
        $html->addHTMLExtraCode ("htm3", $xtra3);
        $html->setHTMLExtraCodeProperties ("htm3",HTMP_IN_TABLE, true);

        $html->addField("num_cpte", _("n° de compte"), TYPC_TXT);
        $html->setFieldProperties("num_cpte", FIELDP_DEFAULT, $infosCorrespondant["numero_cpte"]);
        $html->setFieldProperties("num_cpte", FIELDP_IS_LABEL, true);
      }
    }

    //Crontôler si le montant à déposer ne dépasse pas le montant plafond de depot autorisé s'il y a lieu
	  global $global_nom_login, $global_id_agence, $colb_tableau;
	  $info_login = get_login_full_info($global_nom_login);
	  $info_agence = getAgenceDatas($global_id_agence);
	  $msg = "";
	  if ($info_agence['plafond_depot_guichet'] == 't'){
	    if($info_login['depasse_plafond_depot'] == 'f' && $SESSION_VARS["mnt"] > $info_agence['montant_plafond_depot']){
	   		$msg = "<center>"._("Le montant dépasse le montant plafond de dépôt autorisé. Ce login n'est pas habilité à le faire.");
	   		$msg .= " "._("Veuillez contacter votre administrateur")."</center>";
			}
	  }
		if ($msg != "") {
			 $html = new HTML_erreur(_("Dépôt impossible")." ");
			 $html->setMessage($msg);
			 $html->addButton(BUTTON_OK, "Dcp-2");
			 $html->buildHTML();
			 echo $html->HTML_code;
			 exit();
		}

    if (!(($SESSION_VARS['type_depot'] >= 1) && ($SESSION_VARS['type_depot'] <= 5))) signalErreur(__FILE__,__LINE__,__FUNCTION__); // _("Type dépôt non renseigné")


    $ChkJS = "
             if (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.$champ_mnt.value))
           {
             msg += '-"._("Le montant saisi ne correspond pas au montant à déposer")."\\n';
             ADFormValid=false;
           };";

    $html->addJS(JSP_BEGIN_CHECK, "JS3",$ChkJS);
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
    $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
    $SESSION_VARS['envoi'] = 0;
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dcp-4');
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Dcp-2');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-10');

    $html->buildHTML();
    echo $html->getHTML();

    $SESSION_VARS["set_monnaie_courante"]=$InfoCpte['devise'];
  }

}
/*}}}*/

/*{{{ Dcp-4 : Confirmation du dépot */
else if ($global_nom_ecran == "Dcp-4") {
    global $report_backup_path;

    $isbilletage = getParamAffichageBilletage(); //recuperation du parametre d'affichage de billetage sur les recu

    // capturer des types de billets de la bd et nombre de billets saisie par l'utilisateur
	$valeurBilletArr = array();
	
	$hasBilletageRecu = true;
	$hasBilletageChange = false;
	
	// Multidevises
	if(!empty($SESSION_VARS['mnt_cv']['cv'])){
	    $dev = $SESSION_VARS['mnt_cv']['devise'];
	    $hasBilletageRecu = false;
	    $hasBilletageChange = true;
	}
	else {
	    $dev = $SESSION_VARS["set_monnaie_courante"];	    
	}
	
	$listTypesBilletArr = buildBilletsVect($dev);
	$total_billetArr = array();
	
	//insert nombre billet into array
	for($x = 0; $x < 20; $x++) {
		if(isset($_POST['mnt_reel_billet_'.$x]) && trim($_POST['mnt_reel_billet_'.$x])!='') {
			$valeurBilletArr[] = trim($_POST['mnt_reel_billet_'.$x]);
		}
		else{
			if(isset($listTypesBilletArr[$x]['libel']) && trim($listTypesBilletArr[$x]['libel'])!='') {
				$valeurBilletArr[] = 'XXXX';
			}
		}
	}
		// calcul total pour chaque billets
	for($x = 0; $x < 20; $x ++) {
		
		if ($valeurBilletArr [$x] == 'XXXX') {
			$total_billetArr [] = 'XXXX';
		} else {
			if (isset ( $listTypesBilletArr [$x] ['libel'] ) && trim ( $listTypesBilletArr [$x] ['libel'] ) != '' && isset ( $valeurBilletArr [$x] ['libel'] ) && trim ( $valeurBilletArr [$x] ['libel'] ) != '') {
				$total_billetArr [] = ( int ) ($valeurBilletArr [$x]) * ( int ) ($listTypesBilletArr [$x] ['libel']);
			}
		}
	}
	
	//controle d'envoie du formulaire
	$SESSION_VARS['envoi']++;
	if( $SESSION_VARS['envoi'] != 1 ) {
		$html_err = new HTML_erreur(_("Confirmation"));
	    $html_err->setMessage(_("Donnée dèjà envoyée"));
	    $html_err->addButton("BUTTON_OK", 'Gen-8');
	    $html_err->buildHTML();
	    echo $html_err->HTML_code;
	    exit();
	} 
	//fin contrôle
  setMonnaieCourante($SESSION_VARS["set_monnaie_courante"]);

  //mouvement des comptes avec gestion des frais d'opérations sur compte s'il y lieu
  //$NumCpte et $mnt ont été postés de l'écran précédent; $mnt est le montant net à verser non compris les frais d'opération
  //Vérification si le client n'est pas "débiteur"
  // recupére les information sur le compte
  $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  $montant_remboursement = $SESSION_VARS["mnt"];
  if ($SESSION_VARS['mnt_cv']['cv'] == '')
    $SESSION_VARS["mnt"] = recupMontant($mnt_reel);
  // remplacer les frais de dépot par la valeur saisie s'il y'a possibilité de modification de frais
  if (isset($SESSION_VARS['frais_depot_cpt'])) {
      $InfoProduit["frais_depot_cpt"] = $SESSION_VARS["frais_depot_cpt"];
      $montant_remboursement = $montant_remboursement - $InfoProduit["frais_depot_cpt"];
  }
  if ($SESSION_VARS['mnt_cv']['cv']!='')
    $CHANGE = $SESSION_VARS['mnt_cv'];
  else
    $CHANGE = NULL;

  $data['id_pers_ext'] = $SESSION_VARS['id_pers_ext'];


  if ($SESSION_VARS["type_depot"] == 1) { // dépôt au guichet
    $data['sens'] = 'in ';
    $data['communication'] = $SESSION_VARS['communication'];
    $data['remarque'] = $SESSION_VARS['remarque'];

    
    
    $type_depot=NULL;
    $erreur = depot_cpte($global_id_guichet, $SESSION_VARS["NumCpte"], $SESSION_VARS["mnt"],$InfoProduit, $InfoCpte, $data, $type_depot, $CHANGE); //mnt = montant net à déposer
    $montant_sms = getPreleveFraisSMS($erreur->datas["comptable"]);
    $montant_remboursement -= $montant_sms;

    if ($erreur->errCode == NO_ERR) {

      $id_his = $erreur->param['id'];

      $num_compte = $SESSION_VARS["NumCpte"]; debug($num_compte,"num cpte");

      $remboursement_cap_lcr = false;
      $total_mnt_cap_lcr = 0;
      //Kheshan ticket pp178p1 bon valeur du montant de depo
      // [Ligne de crédit] : Remboursement Capital
      $lcrErr = rembourse_cap_lcr(date("d/m/Y"), $num_compte, $SESSION_VARS['mnt'], $id_his);

      if ($lcrErr->errCode == NO_ERR) {
        $total_mnt_cap_lcr = $lcrErr->param[1];

        if ($total_mnt_cap_lcr > 0) {
          $remboursement_cap_lcr = true;
        }
      }

      //prélèvement des frais en attente si solde_disponible > montant_frais
      $prelevement_frais = false;
      $mnt_frais_attente = 0;
      //Y a t-il des frais en attente sur le compte ?
      if(hasFraisAttenteCompte($num_compte)){
      	$result = getFraisAttenteCompte($num_compte);
      	$liste_frais_attente = $result->param;
      	//Pour chaque frais en attente
      	foreach($liste_frais_attente as $key=>$frais_attente) {
      		//Recupération du solde disponible sur le compte
      		$solde_disponible = getSoldeDisponible($num_compte);
      		$montant_frais = $frais_attente['montant'];
      		$type_frais = $frais_attente['type_frais'];
      		$date_frais = $frais_attente['date_frais'];
      		$comptable = array();//pour passage ecritures
      		//vois si le solde disponible est suffisant pour prélever les frais
	      	if($solde_disponible >= $montant_frais){
	      		$erreurs = paieFraisAttente($num_compte, $type_frais, $montant_frais, $comptable);
		        if ($erreurs->errCode != NO_ERR){
		        	return $erreurs;
		        }
		        //Suppression dans la table des frais en attente
		        $sql = "DELETE FROM ad_frais_attente WHERE id_cpte = $num_compte AND date(date_frais) = date('$date_frais') AND type_frais = $type_frais;";
		        $result = executeDirectQuery($sql);
		        if ($result->errCode != NO_ERR){
		        	return new ErrorObj($result->errCode);
		        }
		        $prelevement_frais = true;
		        //memoriser montant des frais prélevés
	      		$mnt_frais_attente += $montant_frais;
	      		//Historiser le prelevement
		      	$myErr = ajout_historique(75, $InfoCpte["id_titulaire"],'', $global_nom_login, date("r"), $comptable, null, $id_his);
                if ($myErr->errCode != NO_ERR) {
                  $dbHandler->closeConnection(false);
                  return $myErr;
                }
	      	}
      	}
      }
/* Pending en attendant evol pour les salariés/ commercants
      if(isMobileLending()) {
          //EVOL -- MOBILE LENDING ML-76
          $montant_remboursement = $montant_remboursement - $mnt_frais_attente;
          $rembourse_erreur = rembourseMontantDepot($global_id_client, $montant_remboursement);

          if ($rembourse_erreur->errCode != NO_ERR) {
              $html_err = new HTML_erreur(_("Echec du remboursement."));
              $html_err->setMessage(_("Erreur") . " : " . $error[$rembourse_erreur->errCode] . "<br/>" . _("Paramètre") . " : " . $rembourse_erreur->param);
              $html_err->addButton("BUTTON_OK", 'Dcp-1');
              $html_err->buildHTML();
              echo $html_err->HTML_code;
              exit();
          }
      }*/

      $infos = get_compte_epargne_info($SESSION_VARS['NumCpte']);

      //affectation du parametre hasBilletageChange en cas de multidevise
      ($isbilletage == 'f') ? $hasBilletageChange = false : $hasBilletageChange = true;


      //$type_imprimante = $_POST['type_imprimante'];
      //print_recu_depot($global_id_client, $global_client, $SESSION_VARS['mnt'], $InfoProduit, $infos, $id_his, $data['id_pers_ext'],$SESSION_VARS["remarque"],$SESSION_VARS["communication"], $mnt_frais_attente, $SESSION_VARS['id_mandat'], $listTypesBilletArr, $valeurBilletArr, $global_langue_rapport, $total_billetArr, $hasBilletageRecu,$isbilletage,$type_imprimante);

        print_recu_depot($global_id_client, $global_client, $SESSION_VARS['mnt'], $InfoProduit, $infos, $id_his, $data['id_pers_ext'],$SESSION_VARS["remarque"],$SESSION_VARS["communication"], $mnt_frais_attente, $SESSION_VARS['id_mandat'], $listTypesBilletArr, $valeurBilletArr, $global_langue_rapport, $total_billetArr, $hasBilletageRecu,$isbilletage);

      create_report_tag("epargne",array($report_backup_path => array("num_client" => $global_id_client,"num_transactions" => $erreur->param["id"], "type_recu" => "depot", "id_ag" => $global_id_agence, "montant" => $SESSION_VARS["mnt"])));

      $html_msg =new HTML_message(_("Confirmation de dépôt sur un compte"));
      setMonnaieCourante($InfoCpte['devise']);
      $message =_("Montant déposé sur le compte : ").afficheMontant($SESSION_VARS['mnt'], true);
      if (isset($CHANGE)) {
        // Impression du bordereau de change
        $cpteSource=getAccountDatas($SESSION_VARS['NumCpte']);


        $cpteGuichet=getCompteCptaGui($global_id_guichet);
        $cpteDevise=$cpteGuichet.".".$SESSION_VARS['mnt_cv']['devise'];

        $SESSION_VARS["mnt_cv"]["source_achat"]=$cpteSource["num_complet_cpte"];//." ".$cpteSource["intitule_compte"];
        $SESSION_VARS["mnt_cv"]["dest_vente"]= $global_guichet;         
        
        printRecuChange($id_his, $SESSION_VARS["mnt_cv"]["cv"],$SESSION_VARS["mnt_cv"]["devise"],$SESSION_VARS["mnt_cv"]["source_achat"],$SESSION_VARS["mnt"],$global_monnaie_courante,$SESSION_VARS["mnt_cv"]["comm_nette"],$SESSION_VARS["mnt_cv"]["taux"],$SESSION_VARS["mnt_cv"]["reste"],$SESSION_VARS["mnt_cv"]["dest_vente"], NULL, NULL, $listTypesBilletArr, $valeurBilletArr, $global_langue_rapport, $total_billetArr, $hasBilletageChange);

        setMonnaieCourante($CHANGE['devise']);
        $message .="<br>"._("Montant déposé au guichet : ").afficheMontant($CHANGE['cv'], true);
      }
      if ($SESSION_VARS['frais_depot_cpt']>0) {
        setMonnaieCourante($InfoCpte['devise']);
        $message .="<br>"._("Frais de dépôt : ").afficheMontant($SESSION_VARS['frais_depot_cpt'], true);
      }

      if ($erreur->param["mnt"] > 0) {
        $message .= "<br>"._("Des frais impayés ont été débités de votre compte de base pour un montant de")." :<br>";
        $message .= afficheMontant($erreur->param["mnt"], true);
      }
      if ($prelevement_frais) {
        $message .= "<br>"._("Des frais en attente ont été débités de votre compte de base pour un montant de")." :<br>";
        $message .= afficheMontant($mnt_frais_attente, true);
      }
      if ($remboursement_cap_lcr) {
        $message .= "<br>"._("Ligne de crédit : Le capital restant dû a été débité de votre compte de base pour un montant de")." :<br>";
        $message .= afficheMontant($total_mnt_cap_lcr, true);
      }
      $message .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param['id'])."</code></B>";
      $html_msg->setMessage($message);
      $html_msg->addButton("BUTTON_OK", 'Gen-10');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec du dépôt sur un compte. "));
      $html_err->setMessage("Erreur : ".$error[$erreur->errCode]);
      $html_err->addButton("BUTTON_OK", 'Dcp-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    };
  }
  //Cheque ou travelers cheque
  else if ($SESSION_VARS["type_depot"] == 2 || $SESSION_VARS['type_depot']==5) { // Dépt par chèque ou TCH
      global $report_backup_path;

    $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
    if ($SESSION_VARS["type_depot"] == 2)
      $InfoTireur = getTireurBenefDatas($SESSION_VARS['id_ben']);
    $data['id_correspondant']	= $SESSION_VARS['id_correspondant'];
    $data['id_ext_benef']	= null;
    $data['id_cpt_benef']	= $SESSION_VARS['NumCpte'];
    $data['id_ext_ordre']	= $SESSION_VARS['id_ben'];
    $data['id_cpt_ordre']	= null;
    $data['sens']		= 'in ';
    $data['type_piece']		= $SESSION_VARS['type_depot'];
    $data['num_piece']		= $SESSION_VARS['num_chq'];
    $data['date_piece']		= $SESSION_VARS['date_chq'];
    if ($SESSION_VARS["type_depot"] == 5)
      $data['date_piece'] = date("d/m/Y");
    $data['date']		= date("d/m/Y");
    $data['etat']		= 1;                           //état = en attente
    if ($SESSION_VARS["type_depot"] == 2)
      $data['id_banque'] = $InfoTireur['id_banque'];
    $data['communication']	= $SESSION_VARS['communication'];
    $data['remarque']		= $SESSION_VARS['remarque'];

    if (isset($CHANGE)) {
      $data['montant']		= $CHANGE['cv']; // Montant du chèque / déposé au guichet
      $data['devise']           = $CHANGE['devise'];
    } else {
      $data['montant']		= $SESSION_VARS['mnt'];
      $data['devise']           = $InfoCpte["devise"];
    }

    if ($_POST['trait']==2) {
      $creditDirectSaufBonneFin = true;
      $commissionSurCreditDirect = recupMontant($_POST['comm_credit']);
    } else {
      $creditDirectSaufBonneFin = false;
      $commissionSurCreditDirect = null;
    }

    $montant_remboursement = $SESSION_VARS["mnt"];
    $erreur = receptionCheque($data, $InfoCpte, $InfoProduit, $SESSION_VARS["mnt"], $creditDirectSaufBonneFin, $commissionSurCreditDirect, $CHANGE, $montant_remboursement);

    $montant_sms = getPreleveFraisSMS($erreur->datas["comptable"]);
    $montant_remboursement -= $montant_sms;
    
    if ($erreur->errCode == NO_ERR) {
/*Pending en attendant evol pour les salariés/ commercants
      if(isMobileLending()) {
          //EVOL -- MOBILE LENDING
          $rembourse_erreur = rembourseMontantDepot($global_id_client, $montant_remboursement);

          if ($rembourse_erreur->errCode != NO_ERR) {
              $html_err = new HTML_erreur(_("Echec du remboursement."));
              $html_err->setMessage(_("Erreur") . " : " . $error[$rembourse_erreur->errCode] . "<br/>" . _("Paramètre") . " : " . $rembourse_erreur->param);
              $html_err->addButton("BUTTON_OK", 'Dcp-1');
              $html_err->buildHTML();
              echo $html_err->HTML_code;
              exit();
          }
      }*/

      $html_msg =new HTML_message(_("Confirmation de dépôt d'un chèque sur un compte"));
      $message = "";
      if (isset($SESSION_VARS['mnt_cv']['cv'])) {
        setMonnaieCourante($SESSION_VARS['mnt_cv']['devise']);
        $message .= _("Montant du chèque : ").afficheMontant($SESSION_VARS['mnt_cv']['cv'], true)."<br/>";
      }
      setMonnaieCourante($InfoCpte['devise']);
      $message .= _("Montant à déposer sur le compte : ").afficheMontant($SESSION_VARS["mnt"], true);
      if ($creditDirectSaufBonneFin) {
        $message .= "<BR/>"._("Frais de Crédit direct sauf bonne fin : ").afficheMontant($commissionSurCreditDirect, true)."</br>";
      }
      if ($SESSION_VARS['frais_depot_cpt']>0) {
        setMonnaieCourante($InfoCpte['devise']);
        $message .= "<BR/>"._("Frais de dépôt : ").afficheMontant($SESSION_VARS['frais_depot_cpt'], true)."</br>";
      }
      $message .= "<BR><BR>N° de transaction : <B><code>".sprintf("%09d", $erreur->param)."</code></B>";
      $html_msg->setMessage($message);
      $html_msg->addButton("BUTTON_OK", 'Gen-10');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;

      // Impression du bordereau de change
      if (isset($CHANGE) && $creditDirectSaufBonneFin) {
        printRecuChange($erreur->param, $SESSION_VARS["mnt_cv"]['cv'], $SESSION_VARS["mnt_cv"]['devise'], "Traveler's Cheque", $SESSION_VARS["mnt"], $InfoCpte["devise"], $CHANGE["comm_nette"],$CHANGE["taux"],$CHANGE["reste"],_("Compte ").$InfoCpte["num_complet_cpte"], $CHANGE["dest_reste"],true);
        create_report_tag("epargne",array($report_backup_path => array("num_client" => $global_id_client,"num_transactions" => $erreur->param["id"], "type_recu" => "depot", "id_ag" => $global_id_agence, "montant" => $SESSION_VARS["mnt"])));
      }

    } else if ($erreur->errCode == ERR_CPT_CENTRALISE) {
      $html_err = new HTML_erreur(_("Echec du dépôt d'un chèque sur un compte."));
      $html_err->setMessage(_("Erreur : Les comptes comptables des correspondants bancaires ne peuvent être des comptes centralisateurs. Merci de reconfigurer le correspondant bancaire utilisé lors de cette opération."));
      $html_err->addButton("BUTTON_OK", 'Dcp-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec du dépôt d'un chèque sur un compte."));
      $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]."\n".$erreur->param);
      $html_err->addButton("BUTTON_OK", 'Dcp-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }
  //virement
  else if ($SESSION_VARS["type_depot"] == 3) {
    $InfoCpte = getAccountDatas($SESSION_VARS["NumCpte"]);
    $InfoTireur = getTireurBenefDatas($SESSION_VARS['id_ben']);
    $data['id_correspondant']	= $SESSION_VARS['id_correspondant'];
    $data['id_ext_benef']	= null;
    $data['id_cpt_benef']	= $SESSION_VARS['NumCpte'];
    $data['id_ext_ordre']	= $SESSION_VARS['id_ben'];
    $data['id_cpt_ordre']	= null;
    $data['sens']		= 'in ';
    $data['type_piece']		= $SESSION_VARS['type_depot'];
    $data['num_piece']		= $SESSION_VARS['num_chq'];
    $data['date_piece']		= $SESSION_VARS['date_chq'];
    $data['date']		= date("d/m/Y");
    $data['montant']		= $SESSION_VARS['mnt'];
    $data['devise']		= $InfoCpte['devise'];
    $data['id_banque']		= $InfoTireur['id_banque'];
    $data['remarque']		= $SESSION_VARS['remarque'];
    $data['communication']	= $SESSION_VARS['communication'];

    $montant_remboursement = 0;
    $erreur = receptionVirement($data, $InfoCpte, $InfoProduit, $CHANGE, 124, 75, NULL, $montant_remboursement);
    $montant_sms = getPreleveFraisSMS($erreur->datas["comptable"]);
    $montant_remboursement -= $montant_sms;

    if ($erreur->errCode == NO_ERR) {
/*Pending en attendant evol pour les salariés/ commercants
      if(isMobileLending()) {
          //EVOL -- MOBILE LENDING
          $rembourse_erreur = rembourseMontantDepot($global_id_client, $montant_remboursement);

          if ($rembourse_erreur->errCode != NO_ERR) {
              $html_err = new HTML_erreur(_("Echec du remboursement."));
              $html_err->setMessage(_("Erreur") . " : " . $error[$rembourse_erreur->errCode] . "<br/>" . _("Paramètre") . " : " . $rembourse_erreur->param);
              $html_err->addButton("BUTTON_OK", 'Dcp-1');
              $html_err->buildHTML();
              echo $html_err->HTML_code;
              exit();
          }
      }*/

      // A vérifier mais je pense que dans ce cas pas besoin d'imprimer un reçu.
      // La pièce justificative est l'OP lui-meme
      //  print_recu_depot_cheque($global_id_client, $global_client, $SESSION_VARS['NumCpte'], $SESSION_VARS['mnt_chq'], $erreur->param['id'], $DATA["num"], $DATA["id_bqe"], $DATA["date"]);


      $html_msg =new HTML_message(_("Confirmation de dépôt d'un virement sur un compte"));
      setMonnaieCourante($InfoCpte['devise']);
      $message = "<br>"._("Montant à déposer sur le compte : ").afficheMontant($SESSION_VARS["mnt"], true);
      if (isset($SESSION_VARS['mnt_cv']['cv'])) {
        setMonnaieCourante($SESSION_VARS['mnt_cv']['devise']);
        $message .= "<br>"._("Montant du virement : ").afficheMontant($SESSION_VARS['mnt_cv']['cv'], true);
      }
      if ($SESSION_VARS['frais_depot_cpt']>0) {
        setMonnaieCourante($InfoCpte['devise']);
        $message .="<br />"._("Frais de dépôt")." : ".afficheMontant($SESSION_VARS['frais_depot_cpt'], true);
      }
      $message .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param["id"])."</code></B>";
      $html_msg->setMessage($message);
      $html_msg->addButton("BUTTON_OK", 'Gen-10');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    } else {
      $html_err = new HTML_erreur(_("Echec du dépôt d'un chèque sur un compte.")." ");
      $html_err->setMessage("Erreur : ".$error[$erreur->errCode]);
      $html_err->addButton("BUTTON_OK", 'Dcp-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }


  // On vérifie si le client n'est plus débiteur
  if (!isClientDebiteur($global_id_client))

    $global_client_debiteur = false;

}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>
