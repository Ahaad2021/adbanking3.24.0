<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Gestion des tables de paramétrage
 * @package Parametrage
 */

require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_message.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/abonnement.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/dbProcedures/transfert.php';
require_once 'lib/dbProcedures/cheque_interne.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/net_bank.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/engrais_chimiques.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once '/usr/share/adbanking/web/services/misc_api.php';
require_once 'lib/dbProcedures/mobile_lending.php';
require_once '/usr/share/adbanking/web/lib/misc/password_encrypt_decrypt.php';

/* Les tables de paramétrage */
/*$tables = array("fen_pns" => _("FENACOBU - PNSEB"),
                "mod_aut" => _("Automatisme module spécifique")
  );*/
$tables = array();

if (isEngraisChimiques() && check_access(253)) {
  $tables["fen_pns"] = _("FENACOBU - PNSEB");
}
if (check_access(254)) {
  $tables["mod_aut"] = _("Automatisme module spécifique");
}
if (isMobileLending() && check_access(903)) {
    $tables["mod_mld"] = _("Automatisme Mobile Lending");
}


asort($tables);
$SESSION_VARS['tables'] = $tables;



if ($global_nom_ecran == "Gfp-1") {
  unset($SESSION_VARS["select_agence"]);
  unset($SESSION_VARS["table"]);
  resetGlobalIdAgence();
  $MyPage = new HTML_GEN2(_("Gestion des Modules Spécifiques"));
  //Liste des agence
  if (isSiege()) { //Si on est au siège
    $MyPage->addField("list_agence", "Liste des agences", TYPC_LSB);
    $MyPage->setFieldProperties("list_agence", FIELDP_ADD_CHOICES, $liste_agences);
    $MyPage->setFieldProperties("list_agence", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("list_agence", FIELDP_HAS_CHOICE_TOUS, false);
    $MyPage->setFieldProperties("list_agence", FIELDP_DEFAULT,getNumAgence());
  }

  $MyPage->addField("table", _("Liste des modules spécifiques"), TYPC_LSB);

  $MyPage->setFieldProperties("table", FIELDP_ADD_CHOICES, $tables);
  $MyPage->setFieldProperties("table", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("table", FIELDP_HAS_CHOICE_AUCUN, true);

  $MyPage->addButton("table", "param", _("Paramétrer"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("param", BUTP_JS_EVENT, array("onclick"=>"setProchainEcran();"));

  //Bouton formulaire
  $MyPage->addFormButton(1,1, "butret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gen-12");

  //Javascript
  $js  = "function setProchainEcran(){\n";
  $js .= "if (document.ADForm.HTML_GEN_LSB_table.value == 'fen_pns') {assign('Gfp-2');}\n";
  $js .= "if (document.ADForm.HTML_GEN_LSB_table.value == 'mod_aut') {assign('Gmd-1');}\n";
  $js .= "if (document.ADForm.HTML_GEN_LSB_table.value == 'mod_mld') {assign('Gmd-1');}\n";
  $js .= "}\n";
  $MyPage->addJS(JSP_FORM, "js1", $js);

  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();

}

else if ($global_nom_ecran == "Gfp-2") {
  if (!isset($SESSION_VARS['table']) || $SESSION_VARS['table'] == '') {
    $SESSION_VARS['table'] = $table;
  }

  $MyPage = new HTML_GEN2(_("Liste des tables du module :")." '".$SESSION_VARS['tables'][$SESSION_VARS['table']]."'");
  if($SESSION_VARS['table'] == 'fen_pns') {
    $array_menu_engrais = array(
      'ec_annee_agricole'=>_("Année Agricole"),
      'ec_saison_culturale'=>_("Saison"),
      'ec_produit'=>_("Produits"),
      'ec_localisation'=>_("Localisations")
    );
    $MyPage->addField("contenu", _("Liste Des Tables"), TYPC_LSB);
    $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
    $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $array_menu_engrais);
    $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);
  }

  //Bouton formulaire
  $MyPage->addButton("contenu", "butparam", _("Parametrer"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("butparam", BUTP_PROCHAIN_ECRAN, "Gfp-3");
  $MyPage->setButtonProperties("butparam", BUTP_AXS, 252);


  $MyPage->addFormButton(1,1, "butret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gfp-1");

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

else if ($global_nom_ecran == "Gfp-3") {
  $ary1 = array('ec_annee_agricole','ec_saison_culturale','ec_produit','ec_localisation');
  $ary2 = array('ec_annee_agricole'=>_("Année Agricole"),
    'ec_saison_culturale'=>_("Saison"),
    'ec_produit'=>_("Produits"),
    'ec_localisation'=>_("Localisations"));
  //Si table générique
  if ((isset($contenu) && in_array($contenu, $ary1)) || in_array($SESSION_VARS['ajout_table'], $ary1) || in_array($SESSION_VARS['consult_table'], $ary1) || in_array($SESSION_VARS['modif_table'], $ary1)) {
    if (isset($contenu) && $contenu != ''){
      $SESSION_VARS['ajout_table'] = $contenu;
      $SESSION_VARS['consult_table'] = $contenu;
      $SESSION_VARS['modif_table'] = $contenu;
    }

    $MyPage = new HTML_GEN2(_("Gestion de la table de paramétrage")." '".$ary2[$SESSION_VARS['ajout_table']]."'");

    if($contenu == 'ec_annee_agricole' || $SESSION_VARS['ajout_table'] == 'ec_annee_agricole') {
      $MyPage->addField("contenu", _("Contenu"), TYPC_LSB);
      $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);
      $liste_AnneeAgricolePNSEB=getListeAnneeAgricolePNSEB();
      //Trier par ordre alphabétique
      natcasesort($liste_AnneeAgricolePNSEB);
      if (sizeof($liste_AnneeAgricolePNSEB)>0)
        $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $liste_AnneeAgricolePNSEB);
    }

    if($contenu == 'ec_saison_culturale' || $SESSION_VARS['ajout_table'] == 'ec_saison_culturale') {
      $MyPage->addField("contenu", _("Contenu"), TYPC_LSB);
      $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);
      $liste_SaisonPNSEB=getListeSaisonPNSEB();
      //Trier par ordre alphabétique
      natcasesort($liste_SaisonPNSEB);
      if (sizeof($liste_SaisonPNSEB)>0)
        $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $liste_SaisonPNSEB);
    }

    if($contenu == 'ec_produit' || $SESSION_VARS['ajout_table'] == 'ec_produit') {
      $MyPage->addField("contenu", _("Contenu"), TYPC_LSB);
      $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);
      $liste_produitPNSEB=getListeProduitPNSEB();
      //Trier par ordre alphabétique
      natcasesort($liste_produitPNSEB);
      if (sizeof($liste_produitPNSEB)>0)
        $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $liste_produitPNSEB);
    }

    if($contenu == 'ec_localisation' || $SESSION_VARS['ajout_table'] == 'ec_localisation') {
      $MyPage->addField("contenu", _("Contenu"), TYPC_LSB);
      $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);
      $liste_localisationPNSEB=getListelocalisationPNSEB();
      //Trier par ordre alphabétique
      natcasesort($liste_localisationPNSEB);
      if (sizeof($liste_localisationPNSEB)>0)
        $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $liste_localisationPNSEB);
    }

    $MyPage->addButton("contenu", "butcons", _("Consulter"), TYPB_SUBMIT);
    $MyPage->setButtonProperties("butcons", BUTP_PROCHAIN_ECRAN, "Gcf-1");
    //$MyPage->setButtonProperties("butcons", BUTP_AXS, 252);
    $MyPage->addButton("contenu", "butmodif", _("Modifier"), TYPB_SUBMIT);
    $MyPage->setButtonProperties("butmodif", BUTP_PROCHAIN_ECRAN, "Gmf-1");

    //Bouton formulaire
    $MyPage->addFormButton(1,1, "butajou", _("Ajouter"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butajou", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butajou", BUTP_PROCHAIN_ECRAN, "Gaf-1");
    //$MyPage->setFormButtonProperties("butajou", BUTP_AXS, 252);

    $MyPage->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gfp-2");

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  } else signalErreur(__FILE__,__LINE__,__FUNCTION__);
}

else if ($global_nom_ecran == "Gaf-1") {
  global $global_id_agence, $global_monnaie;
  $ary_exclude = array();

  $ary1 = array("ec_annee_agricole", "ec_saison_culturale", "ec_produit", "ec_localisation");
  $ary2 = array('ec_annee_agricole'=>_("Année Agricole"),
    'ec_saison_culturale'=>_("Saison"),
    'ec_produit'=>_("Produits"),
    'ec_localisation'=>_("Localisations"));
  //Si table générique
  if (in_array($SESSION_VARS['ajout_table'], $ary1)) {
    //Ajout
    $MyPage = new HTML_GEN2(_("Ajout d'une entrée "));
    $checkDateSaison = '';

    //Nom table
    $MyPage->addField("ntable", _("Table de paramétrage"), TYPC_TXT);
    $MyPage->setFieldProperties("ntable", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("ntable", FIELDP_DEFAULT, $ary2[$SESSION_VARS['ajout_table']]);

    // Récupération des infos sur l'entrée de la table
    $info = get_tablefield_info($SESSION_VARS['ajout_table'], NULL);
    if (isset($SESSION_VARS['info'])){
      unset($SESSION_VARS['info']);
    }
    $SESSION_VARS['info'] = $info;

    while (list($key, $value) = each($info)) { //Pour chaque champs de la table
      if (($key != "pkey") && //On n'insère pas les clés primaires
        (!in_array($key, $ary_exclude))
      ) { //On n'insère pas certains champs en fonction du contexte
        if (!$value['ref_field']) { //Si champs ordinaire
          $type = $value['type'];
          if ($value['traduit'])
            $type = TYPC_TTR;
          $fill = 0;
          if ((substr($type, 0, 2) == "in") && ($type != "int")) { //Si int avec fill zero
            $fill = substr($type, 2, 1);
            $type = "int";
          }

          $MyPage->addField($key, $value['nom_long'], $type);
          if ($fill != 0) $MyPage->setFieldProperties($key, FIELDP_FILL_ZERO, $fill);
        } else { //Si champs qui référence
          $MyPage->addField($key, $value['nom_long'], TYPC_LSB);
        }
        $MyPage->setFieldProperties($key, FIELDP_IS_REQUIRED, $value['requis']);
      }
    }

    if ($SESSION_VARS['ajout_table'] == 'ec_annee_agricole'){
      $MyPage->setFieldProperties("etat",FIELDP_TYPE,TYPC_LSB);
      //$MyPage->setFieldProperties("etat",FIELDP_ADD_CHOICES,($adsys["adsys_etat_annee_agricole"]));
      $annee_agri_en_cours = getAnneeAgricoleActif();
      //$date_fin_annee_encours = pg2phpDatebis($annee_agri_en_cours['date_fin']);
      $etat_annee_encours = $annee_agri_en_cours['etat'];
      if($etat_annee_encours == NULL){
        $etat_annee_encours= "''";
      }
      $etatAnneeArr["adsys_etat_annee_agricole"];
      if ($etat_annee_encours != '' && $etat_annee_encours == 1){
        $etatAnneeArr["adsys_etat_annee_agricole"][2]=_("Fermé");
        $MyPage->setFieldProperties("etat",FIELDP_ADD_CHOICES,($etatAnneeArr["adsys_etat_annee_agricole"]));
      }
      else{
        $etatAnneeArr["adsys_etat_annee_agricole"][1]=_("Ouvert");
        $MyPage->setFieldProperties("etat",FIELDP_ADD_CHOICES,($etatAnneeArr["adsys_etat_annee_agricole"]));
      }
      $MyPage->setFieldProperties("etat",FIELDP_HAS_CHOICE_AUCUN,false);
      // Validation sur les ajout d'annees agricoles
      //$checkDateSaison = "if(! isBefore('" . date("d/m/Y", mktime(0, 0, 0, (int)$date_fin_annee_encours[0], $date_fin_annee_encours[1], $date_fin_annee_encours[2])) . "', document.ADForm.HTML_GEN_date_date_debut.value)) { alert('- " . _("La date précisée dans le champ \'Date début\' doit être postérieure à la date de fin de l année agricole précédent (".$date_fin_annee_encours[1]."/".$date_fin_annee_encours[0]."/".$date_fin_annee_encours[2].")")."'); document.ADForm.HTML_GEN_date_date_debut.focus(); return false; }";
      $checkDateSaison = "if(! isBefore(document.ADForm.HTML_GEN_date_date_debut.value, document.ADForm.HTML_GEN_date_date_fin.value)) { alert('- " . _("La date précisée dans le champ \'Date fin\' doit être postérieure à la date de debut")."'); document.ADForm.HTML_GEN_date_date_fin.focus(); return false; }";
      $checkDateSaison .="
  if( ".$etat_annee_encours." != '' && ".$etat_annee_encours." !=2 && document.ADForm.HTML_GEN_LSB_etat.value == 1 ){ alert('- " . _("il y a deja une année ouverte")."');document.ADForm.HTML_GEN_LSB_etat.focus(); return false;}";
    }

    if ($SESSION_VARS['ajout_table'] == 'ec_annee_agricole'){
      $ordre1 = array("ntable","libel","date_debut","date_fin", "etat");
      $MyPage->setOrder(NULL, $ordre1);

    }

    if ($SESSION_VARS['ajout_table'] == 'ec_saison_culturale'){
      $annee_agri_en_cours = getListeAnneeAgricoleActif(' etat = 1 ');
      $MyPage->setFieldProperties("etat_saison",FIELDP_TYPE,TYPC_LSB);
      $MyPage->setFieldProperties("etat_saison",FIELDP_ADD_CHOICES,($adsys["adsys_etat_saison"]));
      $MyPage->setFieldProperties("id_annee",FIELDP_TYPE,TYPC_LSB);
      $MyPage->setFieldProperties("id_annee",FIELDP_ADD_CHOICES,$annee_agri_en_cours);
      $MyPage->setFieldProperties("id_annee", FIELDP_HAS_CHOICE_AUCUN, false);
      //$MyPage->setFieldProperties("id_annee", FIELDP_IS_LABEL, false);
      $MyPage->addHTMLExtraCode("general", "<b>"._("General")."</b>");
      $MyPage->setHTMLExtraCodeProperties("general", HTMP_IN_TABLE, true);
      $MyPage->addHTMLExtraCode("separation_period_avance", "<b>"._("Periode de paiement des avances")."</b>");
      $MyPage->setHTMLExtraCodeProperties("separation_period_avance", HTMP_IN_TABLE, true);
      $MyPage->addHTMLExtraCode("separation_period_solde", "<b>"._("Periode de paiement des soldes")."</b>");
      $MyPage->setHTMLExtraCodeProperties("separation_period_solde", HTMP_IN_TABLE, true);
      $MyPage->addHTMLExtraCode("separation_period_fin", "<b>"._("Periode de fin de saison")."</b>");
      $MyPage->setHTMLExtraCodeProperties("separation_period_fin", HTMP_IN_TABLE, true);

      $ordre = array("general","ntable", "id_annee","nom_saison", "plafond_engrais", "plafond_amendement", "etat_saison","separation_period_avance", "date_debut", "date_fin_avance","separation_period_solde", "date_debut_solde", "date_fin_solde", "separation_period_fin","date_fin");
      $MyPage->setOrder(NULL, $ordre);

      //Controle Javascript sur les dates
      $check_saison_exist = getListeSaisonPNSEBlatest($param);

      $date_fin_saison_exist_arr = pg2phpDatebis($check_saison_exist['date_fin']);
      $etat_saison_exist = $check_saison_exist['etat_saison'];
      if($etat_saison_exist == NULL){
        $etat_saison_exist= "''";
      }

      // date debut-fin de l'annee agricole
      $data_annee = getDateAnneeAgricoleActif();
      $date_debut_annee_arr =pg2phpDatebis($data_annee['date_debut']);
      $date_fin_annee_arr = pg2phpDatebis($data_annee['date_fin']);

      // check si la date de debut est superieur a la date debut annee agricole
      $checkDateSaison .= "if(! isBeforeOrEqualTo('" . date("d/m/Y", mktime(0, 0, 0, (int)$date_debut_annee_arr[0], $date_debut_annee_arr[1], $date_debut_annee_arr[2])) . "', document.ADForm.HTML_GEN_date_date_debut.value)) { alert('- " . _("La date précisée dans le champ \'Date début de la saison\' doit être postérieure à la date de debut de l année agricole (".$date_debut_annee_arr[1]."/".$date_debut_annee_arr[0]."/".$date_debut_annee_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_debut.focus(); return false; }";
      // check si la date est superieur a la date de fin de la derniere saison culturale
      $checkDateSaison .= "if(! isBefore('" . date("d/m/Y", mktime(0, 0, 0, (int)$date_fin_saison_exist_arr[0], $date_fin_saison_exist_arr[1], $date_fin_saison_exist_arr[2])) . "', document.ADForm.HTML_GEN_date_date_debut.value)) { alert('- " . _("La date précisée dans le champ \'Date début de la saison\' doit être postérieure à la date de fin de la dernière saison (".$date_fin_saison_exist_arr[1]."/".$date_fin_saison_exist_arr[0]."/".$date_fin_saison_exist_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_debut.focus(); return false; }";
      // check si la date fin des avances est superieur a la date debut saison culturale
      $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_debut.value, document.ADForm.HTML_GEN_date_date_fin_avance.value) && document.ADForm.HTML_GEN_date_date_debut.value != ''  && document.ADForm.HTML_GEN_date_date_fin_avance.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin des avances\' doit être postérieure à la Date début de la saison ")."'); document.ADForm.HTML_GEN_date_date_fin_avance.focus(); return false; }";
      // check si la date début des soldes est superieur a la date fin des avances
      $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_fin_avance.value, document.ADForm.HTML_GEN_date_date_debut_solde.value) && document.ADForm.HTML_GEN_date_date_fin_avance.value != ''  && document.ADForm.HTML_GEN_date_date_debut_solde.value != '') { alert('- " . _("La date précisée dans le champ \'Date début des soldes\' doit être postérieure à la Date fin des avances")."'); document.ADForm.HTML_GEN_date_date_debut_solde.focus(); return false; }";
      // check si la date Date fin des soldes est superieur a la Date début des soldes
      $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_debut_solde.value, document.ADForm.HTML_GEN_date_date_fin_solde.value) && document.ADForm.HTML_GEN_date_date_debut_solde.value != ''  && document.ADForm.HTML_GEN_date_date_fin_solde.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin des soldes\' doit être postérieure à la Date début des soldes")."'); document.ADForm.HTML_GEN_date_date_fin_solde.focus(); return false; }";
      // check si la date Date fin de la saison est superieur a la Date fin des soldes
      $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_fin_solde.value, document.ADForm.HTML_GEN_date_date_fin.value) && document.ADForm.HTML_GEN_date_date_fin_solde.value != '' && document.ADForm.HTML_GEN_date_date_fin.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin de la saison\' doit être postérieure à la Date fin des soldes")."'); document.ADForm.HTML_GEN_date_date_fin.focus(); return false; }";
      // check si la date de fin solde est anterieur a la date de fin de l'annee agricole
      $checkDateSaison .= "if(! isBeforeOrEqualTo(document.ADForm.HTML_GEN_date_date_fin_solde.value, '" . date("d/m/Y", mktime(0, 0, 0, (int)$date_fin_annee_arr[0], $date_fin_annee_arr[1], $date_fin_annee_arr[2])) . "') && document.ADForm.HTML_GEN_date_date_fin_solde.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin solde de la saison\' doit être antérieur à la date de fin de l année agricole(".$date_fin_annee_arr[1]."/".$date_fin_annee_arr[0]."/".$date_fin_annee_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_fin_solde.focus(); return false; }";
      // check si la date de fin est anterieur a la date de fin de l'annee agricole
      $checkDateSaison .= "if( isBefore('" . date("d/m/Y", mktime(0, 0, 0, (int)$date_fin_annee_arr[0], $date_fin_annee_arr[1], $date_fin_annee_arr[2])) . "', document.ADForm.HTML_GEN_date_date_fin.value) && document.ADForm.HTML_GEN_date_date_fin.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin de la saison\' doit être antérieur à la date de fin de l année agricole(".$date_fin_annee_arr[1]."/".$date_fin_annee_arr[0]."/".$date_fin_annee_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_fin.focus(); return false; }";
      // check si un etat en cours existe deja

      $checkDateSaison .="
  if( ".$etat_saison_exist." != '' && ".$etat_saison_exist." !=2 && document.ADForm.HTML_GEN_LSB_etat_saison.value == 1 ){ alert('- " . _("il y a deja une saison ouverte")."');document.ADForm.HTML_GEN_LSB_etat_saison.focus(); return false;}";
    }

    if ($SESSION_VARS['ajout_table'] == 'ec_produit'){
      $MyPage->setFieldProperties('type_produit', FIELDP_TYPE, TYPC_LSB);
      $MyPage->setFieldProperties("type_produit", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties('type_produit', FIELDP_ADD_CHOICES, $adsys["adsys_type_produit"]);
      $MyPage->setFieldProperties("prix_unitaire", FIELDP_TYPE, TYPC_MNT);
      $setPrixUnitaireModifiable = setPrixUnitaireModifiable();
      if ($setPrixUnitaireModifiable == FALSE) {
        $MyPage->setFieldProperties("prix_unitaire", FIELDP_DEFAULT, 0);
        $MyPage->setFieldProperties("prix_unitaire", FIELDP_IS_LABEL, true);
      }
      $MyPage->setFieldProperties('etat_produit', FIELDP_TYPE, TYPC_LSB);
      $MyPage->setFieldProperties("etat_produit", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties('etat_produit', FIELDP_ADD_CHOICES, $adsys["adsys_etat_produit"]);
      $MyPage->setFieldProperties("montant_minimum", FIELDP_TYPE, TYPC_MNT);
      $MyPage->setFieldProperties("montant_minimum", FIELDP_IS_REQUIRED, true);
      $MyPage->setFieldProperties('compte_produit', FIELDP_ADD_CHOICES, $SESSION_VARS['info']['compte_produit']['choices']);
      $MyPage->setFieldProperties('compte_produit', FIELDP_IS_REQUIRED, true);
    }

    if ($SESSION_VARS['ajout_table'] == 'ec_localisation'){
      $codejs = " function populateParent()
      {
        if (document.ADForm.HTML_GEN_LSB_type_localisation.value > 1) {
            var _cQueue = [];
            var valueToPush = {};
            if (document.ADForm.HTML_GEN_LSB_type_localisation.value == 2){
              ";
      $where = "type_localisation = 1";
      $valueToPush = getListelocalisationPNSEB($where);
      while (list($key, $value) = each($valueToPush)) {
        $codejs.=" valueToPush['".$key."'] = '".$value."';";
      }
      $codejs.="} if (document.ADForm.HTML_GEN_LSB_type_localisation.value == 3){
              ";
      $where = "type_localisation = 2";
      $valueToPush = getListelocalisationPNSEB($where);
      while (list($key, $value) = each($valueToPush)) {
        $codejs.=" valueToPush['".$key."'] = '".$value."';";
      }
      $codejs.="} if (document.ADForm.HTML_GEN_LSB_type_localisation.value == 4){
              ";
      $where = "type_localisation = 3";
      $valueToPush = getListelocalisationPNSEB($where);
      while (list($key, $value) = each($valueToPush)) {
        $codejs.=" valueToPush['".$key."'] = '".$value."';";
      }
      $codejs.="}";
      $codejs.="
            _cQueue.push(valueToPush);

            var slt = document.ADForm.HTML_GEN_LSB_parent;
            // Reset select
            slt.options.length = 0;
            // Set default value
            slt.options[0] = new Option(\"[Aucun]\", \"0\", true, true);
            for (var i=0; i<_cQueue.length; i++) { // iterate on the array
              var obj = _cQueue[i];
              for (var key in obj) { // iterate on object properties
                var value = obj[key];
                //console.log(value);
                 opt = document.createElement('option');
                 opt.value = key;
                 opt.text = value;
                 slt.appendChild(opt);
              }
            }
        } else {
            var slt = document.ADForm.HTML_GEN_LSB_parent;
            // Reset select
            slt.options.length = 0;
            // Set default value
            slt.options[0] = new Option(\"[Aucun]\", \"0\", true, true);
        }
      }";
      $MyPage->addJS(JSP_FORM, "JS1", $codejs);
      $MyPage->setFieldProperties("libel", FIELDP_IS_REQUIRED, true);
      $MyPage->setFieldProperties('type_localisation', FIELDP_TYPE, TYPC_LSB);
      $MyPage->setFieldProperties("type_localisation", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties('type_localisation', FIELDP_ADD_CHOICES, $adsys["adsys_type_localisation"]);
      $MyPage->setFieldProperties("type_localisation", FIELDP_JS_EVENT, array("onChange" => "populateParent();"));
      $MyPage->setFieldProperties("type_localisation", FIELDP_IS_REQUIRED, true);
      $MyPage->setFieldProperties('parent', FIELDP_TYPE, TYPC_LSB);
      $MyPage->setFieldProperties("parent", FIELDP_HAS_CHOICE_AUCUN,true);
    }

    //Bouton
    $MyPage->addFormButton(1, 1, "butval", _("Valider"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Gaf-2");
    $MyPage->setFormButtonProperties("butval", BUTP_JS_EVENT, array("onclick" => $checkDateSaison));
    $MyPage->addFormButton(1, 2, "butret", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gfp-3");

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  } else signalErreur(__FILE__,__LINE__,__FUNCTION__);
}

else if ($global_nom_ecran == "Gaf-2") {
  global $global_id_agence, $global_monnaie;
  $ary_exclude = array();

  if (isset($butval) && $butval == "Valider"){

    $MyPage = new HTML_GEN2(_("Ajout d'une entrée "));

    reset($SESSION_VARS['info']);
    while (list($key, $value) = each($SESSION_VARS['info'])) {
      if (($key != "pkey") && (! in_array($key, $ary_exclude))) { //On n'insére pas certains champs en fonction du contexte
        if ($value['type'] == TYPC_MNT) $DATA[$key] = recupMontant(${$key});
        else if ($value['type'] == TYPC_BOL) {
          if (isset(${$key}))
            $DATA[$key] = "t";
          else $DATA[$key] = "f";
        } else if ($value['type'] == TYPC_PRC)
          $DATA[$key] = "".((${$key}) / 100)."";
        //else if (($value['type'] == TYPC_TXT) && (${$key} == "0") && ($value['ref_field'] == 1400)) // il faut accepter les valeurs 0
        //$DATA[$key] = "NULL";//FIXME:je sais,ce n'est vraiment pas propre.Probléme d'intégrité référentielle sur les comptes comptables
        else if (($value['type'] == TYPC_TXT) && ($value['ref_field'] == 1400)) {
          // On considère que la valeur 0 pour les list box est le choix [Aucun]
          if ($ {"HTML_GEN_LSB_".$key}=="0")
            $DATA[$key] = "NULL";
          else
            $DATA[$key]= $ {"HTML_GEN_LSB_".$key
            };

        } else $DATA[$key] = ${
        $key
        };


        if ((($value['type'] == TYPC_MNT) || ($value['type'] == TYPC_INT) || ($value['type'] == TYPC_PRC)) && ($ {$key} == NULL || ${$key} == "")) {
          $DATA[$key] = '0'; //NULL correspond à la valeur zéro pour les chiffres.  Ah bon ?  Ca limite l'usage des valeurs par défaut de PSQL... dommage. :(
        }
        if ($key == "id_etat_prec") {
          $DATA[$key]  = array_pop(array_keys($value['choices']));
        }
      }
    }

    //appel DB
    $myErr=ajout_table($SESSION_VARS['ajout_table'], $DATA);

    //HTML
    if ($myErr->errCode==NO_ERR) {
      $MyPage = new HTML_message(_("Confirmation ajout"));
      $message = sprintf(_("L'entrée été ajoutée avec succès"));
      $MyPage->setMessage($message);
      $MyPage->addButton(BUTTON_OK, "Gfp-2");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    }
    else{
      $MyPage = new HTML_erreur(_("Echec de l'insertion"));
      $MyPage->setMessage($error[$myErr->errCode]);
      $MyPage->addButton(BUTTON_OK, "Gaf-1");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    }
  }
}

else if ($global_nom_ecran == "Gcf-1") {
  $ary_exclude = array();

  $ary1 = array("ec_annee_agricole", "ec_saison_culturale", "ec_produit", "ec_localisation");
  $ary2 = array('ec_annee_agricole'=>_("Année Agricole"),
    'ec_saison_culturale'=>_("Saison"),
    'ec_produit'=>_("Produits"),
    'ec_localisation'=>_("Localisations"));
  //Si table générique
  if (in_array($SESSION_VARS['consult_table'], $ary1)) {

    ajout_historique(293, NULL, $SESSION_VARS['consult_table'], $global_nom_login, date("r"), NULL);

    //Consultation
    $MyPage = new HTML_GEN2(_("Consultation d'une entrée "));

    //Nom table
    $MyPage->addField("ntable", _("Table de paramétrage"), TYPC_TXT);
    $MyPage->setFieldProperties("ntable", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("ntable", FIELDP_DEFAULT, $ary2[$SESSION_VARS['consult_table']]);

    // Récupération des infos sur l'entrée de la table
    $info = get_tablefield_info($SESSION_VARS['consult_table'], $contenu);

    foreach($info AS $key => $value) { //Pour chaque champs de la table
      if (($key != "pkey") && //On n'insére pas les clés primaires
        (!in_array($key, $ary_exclude))
      ) { //On n'insére pas certains champs en fonction du contexte

        if (!$value['ref_field']) { //Si champs ordinaire
          $type = $value['type'];
          if ($value['traduit'])
            $type = TYPC_TTR;
          if ($type == TYPC_PRC) $value['val'] *= 100;
          if ($type == TYPC_BOL) $value['val'] = ($value['val'] == 't');
          $fill = 0;
          if ((substr($type, 0, 2) == "in") && ($type != "int")) { //Si int avec fill zero
            $fill = substr($type, 2, 1);
            $type = "int";
          }

          $MyPage->addField($key, $value['nom_long'], $type);
          if ($fill != 0) $MyPage->setFieldProperties($key, FIELDP_FILL_ZERO, $fill);
        } else { //Si champs qui référence
          $MyPage->addField($key, $value['nom_long'], TYPC_LSB);
          $MyPage->setFieldProperties($key, FIELDP_ADD_CHOICES, $value['choices']);
        }
        $MyPage->setFieldProperties($key, FIELDP_IS_REQUIRED, $value['requis']);
        $MyPage->setFieldProperties($key, FIELDP_IS_LABEL, true);
        $MyPage->setFieldProperties($key, FIELDP_DEFAULT, $value['val']);
        if ($key == "etat") {
          $etat_annee_value = $value['val'];
        }
        if ($key == "etat_saison") {
          $etat_saison_value = $value['val'];
        }
        if ($key == "id_annee") {
          $id_annee_agri = $value['val'];
        }

        if ($SESSION_VARS['consult_table'] == 'ec_produit' ){
          if ($key=='type_produit' || $key=='etat_produit'){
            $MyPage->setFieldProperties($key, FIELDP_DEFAULT, $adsys["adsys_".$key][$value['val']]);
          }
        }

        if ($SESSION_VARS['consult_table'] == 'ec_localisation' ){
          if ($key=='type_localisation'){
            $MyPage->setFieldProperties($key, FIELDP_DEFAULT, $adsys["adsys_".$key][$value['val']]);
          }
          if ($key=='parent'){
            $where = "id = ".$value['val'];
            $valueParent = getListelocalisationPNSEB($where);
            $MyPage->setFieldProperties($key, FIELDP_DEFAULT, $valueParent[$value['val']]);
          }
        }
      }
    }

    if ($SESSION_VARS['consult_table'] == "ec_annee_agricole"){
      $MyPage->setFieldProperties("etat",FIELDP_DEFAULT, $adsys["adsys_etat_annee_agricole"][$etat_annee_value]);
    }
    if ($SESSION_VARS['consult_table'] == "ec_saison_culturale"){
      $get_annee_agri_actif_label =getAnneeAgricoleActif($id_annee_agri);

      $MyPage->setFieldProperties("id_annee",FIELDP_DEFAULT, $get_annee_agri_actif_label['libel']);

      $MyPage->setFieldProperties("etat_saison",FIELDP_DEFAULT, $adsys["adsys_etat_saison"][$etat_saison_value]);
      $ordre = array("ntable", "id_annee","nom_saison", "date_debut", "date_fin_avance", "date_debut_solde", "date_fin_solde", "date_fin", "plafond_engrais", "plafond_amendement", "etat_saison");
      $MyPage->setOrder(NULL, $ordre);
    }

    $MyPage->addFormButton(1,1, "butret", _("Retour"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gfp-3");

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  } else signalErreur(__FILE__,__LINE__,__FUNCTION__);
}

else if ($global_nom_ecran == "Gmf-1") {
  global $global_id_agence, $global_monnaie;
  $ary_exclude = array("id_annee");
  $isSuivante = false;
  $date = date("Y/m/d");

  $ary1 = array("ec_annee_agricole", "ec_saison_culturale", "ec_produit", "ec_localisation");
  $ary2 = array('ec_annee_agricole'=>_("Année Agricole"),
    'ec_saison_culturale'=>_("Saison"),
    'ec_produit'=>_("Produits"),
    'ec_localisation'=>_("Localisations"));
  //Si table générique
  if (in_array($SESSION_VARS['modif_table'], $ary1)) {
    //Modification
    $MyPage = new HTML_GEN2(_("Modification d'une entrée "));

    //Nom table
    $MyPage->addField("ntable", _("Table de paramétrage"), TYPC_TXT);
    $MyPage->setFieldProperties("ntable", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("ntable", FIELDP_DEFAULT, $ary2[$SESSION_VARS['modif_table']]);

    // Récupération des infos sur l'entrée de la table
    $info = get_tablefield_info($SESSION_VARS['modif_table'], $contenu);
    $SESSION_VARS['info'] = $info;
    $SESSION_VARS['info']['modif_pkeyid'] = $contenu;
    $logo=0;

    foreach ($info as $key => $value) {
      //Pour chaque champs de la table
      if (($key != "pkey") && (! in_array($key, $ary_exclude))) //On n'insère pas les clés primaires
      {	//On n'insère pas certains champs en fonction du contexte
        if (! $value['ref_field']) {	//Si champs ordinaire
          $type = $value['type'];
          if ($value['traduit'])
            $type = TYPC_TTR;
          if ($type == TYPC_PRC) $value['val'] *= 100;
          if ($type == TYPC_BOL) $value['val'] = ($value['val'] == 't');

          $fill = 0;
          if ((substr($type, 0, 2) == "in") && ($type != "int")) {	//Si int avec fill zero
            $fill = substr($type, 2, 1);
            $type = "int";
          }

          $MyPage->addField($key, $value['nom_long'], $type);

          if ($fill != 0)
            $MyPage->setFieldProperties($key, FIELDP_FILL_ZERO, $fill);
        } else {	//Si Ref field
          $MyPage->addField($key, $value['nom_long'], TYPC_LSB);

          $MyPage->setFieldProperties($key, FIELDP_ADD_CHOICES, $value['choices']);
        }

        $MyPage->setFieldProperties($key, FIELDP_IS_REQUIRED, $value['requis']);
        $MyPage->setFieldProperties($key, FIELDP_DEFAULT, $value['val']);
      }

      if ($SESSION_VARS['modif_table'] == 'ec_localisation'){
        if ($key=='parent'){
          $where = "id = ".$value['val'];
          $valueParent = getListelocalisationPNSEB($where);
          $MyPage->setFieldProperties($key, FIELDP_DEFAULT, $value['val']);
        }
      }

      if ($SESSION_VARS['modif_table'] == "ec_annee_agricole" && ($key == 'etat')) {
        $MyPage->setFieldProperties($key,FIELDP_TYPE,TYPC_LSB);
        $MyPage->setFieldProperties($key, FIELDP_ADD_CHOICES, $adsys["adsys_etat_annee_agricole"]);
        $etat_annee = $value['val'];
      }

      if ($SESSION_VARS['modif_table'] == "ec_saison_culturale" && ($key == 'etat_saison')) {
        $MyPage->setFieldProperties($key,FIELDP_TYPE,TYPC_LSB);
        $MyPage->setFieldProperties($key, FIELDP_ADD_CHOICES, $adsys["adsys_etat_saison"]);
      }

      if (($SESSION_VARS['modif_table'] == "ec_saison_culturale"  || $SESSION_VARS['modif_table'] == "ec_annee_agricole") && ($key == 'date_debut')) {
        $date = date("Y/m/d");
        $date_debut_base = pg2phpDatebis($value['val']);
        $date_debut_base_bis = date("Y/m/d",mktime(0, 0, 0, (int)$date_debut_base[0], $date_debut_base[1], $date_debut_base[2]));
        if ($date > $date_debut_base_bis && $value['val'] != ''){
          $MyPage->setFieldProperties($key, FIELDP_HAS_CALEND, false);
        }
      }
      if ($SESSION_VARS['modif_table'] == "ec_saison_culturale" && ($key == 'date_fin_avance')) {
        $date = date("Y/m/d");
        $date_fin_avance_base = pg2phpDatebis($value['val']);
        $date_fin_avance_base_bis = date("Y/m/d",mktime(0, 0, 0, (int)$date_fin_avance_base[0], $date_fin_avance_base[1], $date_fin_avance_base[2]));
        if ($date > $date_fin_avance_base_bis && $value['val'] != ''){
          $MyPage->setFieldProperties($key, FIELDP_HAS_CALEND, false);
        }
      }
      if ($SESSION_VARS['modif_table'] == "ec_saison_culturale" && ($key == 'date_debut_solde')) {
        $date = date("Y/m/d");
        $date_debut_solde_base = pg2phpDatebis($value['val']);
        $date_debut_solde_base_bis = date("Y/m/d",mktime(0, 0, 0, (int)$date_debut_solde_base[0], $date_debut_solde_base[1], $date_debut_solde_base[2]));
        if ($date > $date_debut_solde_base_bis && $value['val'] != ''){
          $MyPage->setFieldProperties($key, FIELDP_HAS_CALEND, false);
        }
      }
      if ($SESSION_VARS['modif_table'] == "ec_saison_culturale" && ($key == 'date_fin_solde')) {
        $date = date("Y/m/d");
        $date_fin_solde_base = pg2phpDatebis($value['val']);
        $date_fin_solde_base_bis = date("Y/m/d",mktime(0, 0, 0, (int)$date_fin_solde_base[0], $date_fin_solde_base[1], $date_fin_solde_base[2]));
        if ($date > $date_fin_solde_base_bis && $value['val'] != ''){
          $MyPage->setFieldProperties($key, FIELDP_HAS_CALEND, false);
        }
      }
      if (($SESSION_VARS['modif_table'] == "ec_saison_culturale" || $SESSION_VARS['modif_table'] == "ec_annee_agricole") && ($key == 'date_fin')) {
        $date = date("Y/m/d");
        $date_fin_base = pg2phpDatebis($value['val']);
        $date_fin_base_bis = date("Y/m/d",mktime(0, 0, 0, (int)$date_fin_base[0], $date_fin_base[1], $date_fin_base[2]));
        if ($date > $date_fin_base_bis && $value['val'] != ''){
          $MyPage->setFieldProperties($key, FIELDP_HAS_CALEND, false);
        }
      }
    }

    if($SESSION_VARS['modif_table'] == "ec_annee_agricole"){
      $saison_ouverte =getListeSaisonPNSEB("id_annee=".$contenu."and etat_saison = 1");

      $date = date("d/m/Y");
      $checkDateAvailable = "";
      $checkDateAvailable .= "if(! isBefore('" . $date . "', document.ADForm.HTML_GEN_date_date_debut.value)) { document.ADForm.HTML_GEN_date_date_debut.readOnly = true; }";
      $checkDateAvailable .= "if(! isBefore('" . $date . "', document.ADForm.HTML_GEN_date_date_fin.value)) { document.ADForm.HTML_GEN_date_date_fin.readOnly = true; }";
      $checkDateAvailable .= "if(document.ADForm.HTML_GEN_LSB_etat.value == 2) { document.ADForm.HTML_GEN_LSB_etat.disabled = true; document.ADForm.HTML_GEN_date_date_debut.readOnly = true; document.ADForm.HTML_GEN_date_date_fin.readOnly = true; document.ADForm.butval.hidden = true; document.ADForm.libel.readOnly = true; }";

      if ($etat_annee == 2){
        $MyPage->setFieldProperties('date_debut', FIELDP_HAS_CALEND, false);
        $MyPage->setFieldProperties('date_fin', FIELDP_HAS_CALEND, false);
      }

      $MyPage->addJS(JSP_FORM, "funct_check_date_annee", $checkDateAvailable);

      $annee_agri_en_cours =  getAnneeAgricole("id_annee !=".$contenu);
      //$date_fin_annee_encours = pg2phpDatebis($annee_agri_en_cours['date_fin']);
      $etat_annee_encours = $annee_agri_en_cours['etat'];
      if($etat_annee_encours == NULL){
        $etat_annee_encours= "''";
      }
      // Validation sur les ajout d'annees agricoles
      //$checkDateSaison = "if(! isBefore('" . date("d/m/Y", mktime(0, 0, 0, (int)$date_fin_annee_encours[0], $date_fin_annee_encours[1], $date_fin_annee_encours[2])) . "', document.ADForm.HTML_GEN_date_date_debut.value)) { alert('- " . _("La date précisée dans le champ \'Date début\' doit être postérieure à la date de fin de l année agricole précédent (".$date_fin_annee_encours[1]."/".$date_fin_annee_encours[0]."/".$date_fin_annee_encours[2].")")."'); document.ADForm.HTML_GEN_date_date_debut.focus(); return false; }";
      $checkDateSaison = "if(! isBefore(document.ADForm.HTML_GEN_date_date_debut.value, document.ADForm.HTML_GEN_date_date_fin.value)) { alert('- " . _("La date précisée dans le champ \'Date fin\' doit être postérieure à la date de debut")."'); document.ADForm.HTML_GEN_date_date_fin.focus(); return false; }";
      $checkDateSaison .="
  if( ".$etat_annee_encours." != '' && ".$etat_annee_encours." !=2 && document.ADForm.HTML_GEN_LSB_etat.value == 1 ){ alert('- " . _("il y a deja une année ouverte")."');document.ADForm.HTML_GEN_LSB_etat.focus(); return false;}";
    }

    if ($SESSION_VARS['modif_table'] == 'ec_saison_culturale'){
      //Controle Javascript
      $date = date("d/m/Y");
      $checkDateAvailable = "";

      // check si la date est superieur a la date du jour
      $checkDateAvailable .= "if(! isBefore('" . $date . "', document.ADForm.HTML_GEN_date_date_debut.value) && document.ADForm.HTML_GEN_date_date_debut.value != '') { document.ADForm.HTML_GEN_date_date_debut.readOnly = true; }";
      $checkDateAvailable .= "if(! isBefore('" . $date . "', document.ADForm.HTML_GEN_date_date_fin_avance.value) && document.ADForm.HTML_GEN_date_date_fin_avance.value != '') { document.ADForm.HTML_GEN_date_date_fin_avance.readOnly = true; }";
      $checkDateAvailable .= "if(! isBefore('" . $date . "', document.ADForm.HTML_GEN_date_date_debut_solde.value) && document.ADForm.HTML_GEN_date_date_debut_solde.value != '') { document.ADForm.HTML_GEN_date_date_debut_solde.readOnly = true; }";
      $checkDateAvailable .= "if(! isBefore('" . $date . "', document.ADForm.HTML_GEN_date_date_fin_solde.value) && document.ADForm.HTML_GEN_date_date_fin_solde.value != '') { document.ADForm.HTML_GEN_date_date_fin_solde.readOnly = true; }";
      $checkDateAvailable .= "if(! isBefore('" . $date . "', document.ADForm.HTML_GEN_date_date_fin.value) && document.ADForm.HTML_GEN_date_date_fin.value != '') { document.ADForm.HTML_GEN_date_date_fin.readOnly = true; }";

      //$js_hide = "document.ADForm.id_annee.readOnly = true;";

      $param = "id_saison = ".$contenu;
      $check_saison_exist = getListeSaisonPNSEBlatest($param);
      $date_debut_saison_exist_arr = pg2phpDatebis($check_saison_exist['date_debut']);
      $date_debut_saison_exist_bis = date("Y/m/d",mktime(0, 0, 0, (int)$date_debut_saison_exist_arr[0], $date_debut_saison_exist_arr[1], $date_debut_saison_exist_arr[2]));
      $date_debut_saison_exist = date("d/m/Y",mktime(0, 0, 0, (int)$date_debut_saison_exist_arr[0], $date_debut_saison_exist_arr[1], $date_debut_saison_exist_arr[2]));
      $date_fin_saison_exist_arr = pg2phpDatebis($check_saison_exist['date_fin']);

      $check_autre_saison_exist =CheckAutreSaisonExist(" id_saison <> ".$contenu);
      $date_debut_autre_saison_arr = pg2phpDatebis($check_autre_saison_exist['date_debut']);
      $date_fin_autre_saison_arr = pg2phpDatebis($check_autre_saison_exist['date_fin']);
      $date_debut_autre_saison_bis = date("Y/m/d",mktime(0, 0, 0, (int)$date_debut_autre_saison_arr[0], $date_debut_autre_saison_arr[1], $date_debut_autre_saison_arr[2]));
      $date_debut_autre_saison = date("d/m/Y",mktime(0, 0, 0, (int)$date_debut_autre_saison_arr[0], $date_debut_autre_saison_arr[1], $date_debut_autre_saison_arr[2]));
      $etat_saison_exist = $check_autre_saison_exist['etat_saison'];
      if($etat_saison_exist == NULL){
        $etat_saison_exist= "''";
      }

      // date debut-fin de l'annee agricole
      $data_annee = getDateAnneeAgricoleActif();
      $date_debut_annee_arr =pg2phpDatebis($data_annee['date_debut']);
      $date_fin_annee_arr = pg2phpDatebis($data_annee['date_fin']);

      $date_form = pg2phpDate($info['date_debut']['val']);
      if ($date_debut_saison_exist_bis > $date_debut_autre_saison_bis ){
        $isSuivante = true;
      }

      if ($isSuivante == true){
        // check si la date de debut est superieur a la date debut annee agricole
        $checkDateSaison = "if(! isBeforeOrEqualTo('" . date("d/m/Y", mktime(0, 0, 0, (int)$date_debut_annee_arr[0], $date_debut_annee_arr[1], $date_debut_annee_arr[2])) . "', document.ADForm.HTML_GEN_date_date_debut.value)) { alert('- " . _("La date précisée dans le champ \'Date début de la saison\' doit être postérieure à la date de debut de l année agricole (".$date_debut_annee_arr[1]."/".$date_debut_annee_arr[0]."/".$date_debut_annee_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_debut.focus(); return false; }";
        // check si la date est superieur a la date de fin de la derniere saison culturale
        $checkDateSaison .= "if(! isBefore('" . date("d/m/Y", mktime(0, 0, 0, (int)$date_fin_autre_saison_arr[0], $date_fin_autre_saison_arr[1], $date_fin_autre_saison_arr[2])) . "', document.ADForm.HTML_GEN_date_date_debut.value)) { alert('- " . _("La date précisée dans le champ \'Date début de la saison\' doit être postérieure à la date de fin de la dernière saison (".$date_fin_autre_saison_arr[1]."/".$date_fin_autre_saison_arr[0]."/".$date_fin_autre_saison_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_debut.focus(); return false; }";
        // check si la date fin des avances est superieur a la date debut saison culturale
        $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_debut.value, document.ADForm.HTML_GEN_date_date_fin_avance.value) && document.ADForm.HTML_GEN_date_date_debut.value != '' && document.ADForm.HTML_GEN_date_date_fin_avance.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin des avances\' doit être postérieure à la Date début de la saison ")."'); document.ADForm.HTML_GEN_date_date_fin_avance.focus(); return false; }";
        // check si la date début des soldes est superieur a la date fin des avances
        $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_fin_avance.value, document.ADForm.HTML_GEN_date_date_debut_solde.value) && document.ADForm.HTML_GEN_date_date_fin_avance.value != '' && document.ADForm.HTML_GEN_date_date_debut_solde.value != '') { alert('- " . _("La date précisée dans le champ \'Date début des soldes\' doit être postérieure à la Date fin des avances")."'); document.ADForm.HTML_GEN_date_date_debut_solde.focus(); return false; }";
        // check si la date Date fin des soldes est superieur a la Date début des soldes
        $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_debut_solde.value, document.ADForm.HTML_GEN_date_date_fin_solde.value) && document.ADForm.HTML_GEN_date_date_debut_solde.value != '' && document.ADForm.HTML_GEN_date_date_fin_solde.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin des soldes\' doit être postérieure à la Date début des soldes")."'); document.ADForm.HTML_GEN_date_date_fin_solde.focus(); return false; }";
        // check si la date Date fin de la saison est superieur a la Date fin des soldes
        $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_fin_solde.value, document.ADForm.HTML_GEN_date_date_fin.value) && document.ADForm.HTML_GEN_date_date_fin_solde.value != '' && document.ADForm.HTML_GEN_date_date_fin.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin de la saison\' doit être postérieure à la Date fin des soldes")."'); document.ADForm.HTML_GEN_date_date_fin.focus(); return false; }";
        // check si la date de fin solde est anterieur a la date de fin de l'annee agricole
        $checkDateSaison .= "if(! isBeforeOrEqualTo(document.ADForm.HTML_GEN_date_date_fin_solde.value, '" . date("d/m/Y", mktime(0, 0, 0, (int)$date_fin_annee_arr[0], $date_fin_annee_arr[1], $date_fin_annee_arr[2])) . "') && document.ADForm.HTML_GEN_date_date_fin_solde.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin solde de la saison\' doit être antérieur à la date de fin de l année agricole(".$date_fin_annee_arr[1]."/".$date_fin_annee_arr[0]."/".$date_fin_annee_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_fin_solde.focus(); return false; }";
        // check si la date de fin est anterieur a la date de fin de l'annee agricole
        $checkDateSaison .= "if(! isBeforeOrEqualTo(document.ADForm.HTML_GEN_date_date_fin.value, '" . date("d/m/Y", mktime(0, 0, 0, (int)$date_fin_annee_arr[0], $date_fin_annee_arr[1], $date_fin_annee_arr[2])) . "') && document.ADForm.HTML_GEN_date_date_fin.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin de la saison\' doit être antérieur à la date de fin de l année agricole(".$date_fin_annee_arr[1]."/".$date_fin_annee_arr[0]."/".$date_fin_annee_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_fin.focus(); return false; }";
        // check si un etat en cours existe deja

        $checkDateSaison .="
     if( ".$etat_saison_exist." != '' && ".$etat_saison_exist." !=2 && document.ADForm.HTML_GEN_LSB_etat_saison.value == 1 ){ alert('- " . _("il y a deja une saison ouverte")."');document.ADForm.HTML_GEN_LSB_etat_saison.focus(); return false;}";
      }
      else {
        // check si la date de debut est superieur a la date debut annee agricole
        $checkDateSaison .= "if(! isBeforeOrEqualTo('" . date("d/m/Y", mktime(0, 0, 0, (int)$date_debut_annee_arr[0], $date_debut_annee_arr[1], $date_debut_annee_arr[2])) . "', document.ADForm.HTML_GEN_date_date_debut.value)) { alert('- " . _("La date précisée dans le champ \'Date début de la saison\' doit être postérieure à la date de debut de l année agricole (".$date_debut_annee_arr[1]."/".$date_debut_annee_arr[0]."/".$date_debut_annee_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_debut.focus(); return false; }";
        // check si la date fin des avances est superieur a la date debut saison culturale
        $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_debut.value, document.ADForm.HTML_GEN_date_date_fin_avance.value) && document.ADForm.HTML_GEN_date_date_debut.value != '' && document.ADForm.HTML_GEN_date_date_fin_avance.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin des avances\' doit être postérieure à la Date début de la saison ")."'); document.ADForm.HTML_GEN_date_date_fin_avance.focus(); return false; }";
        // check si la date début des soldes est superieur a la date fin des avances
        $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_fin_avance.value, document.ADForm.HTML_GEN_date_date_debut_solde.value) && document.ADForm.HTML_GEN_date_date_fin_avance.value != '' && document.ADForm.HTML_GEN_date_date_debut_solde.value != '') { alert('- " . _("La date précisée dans le champ \'Date début des soldes\' doit être postérieure à la Date fin des avances")."'); document.ADForm.HTML_GEN_date_date_debut_solde.focus(); return false; }";
        // check si la date Date fin des soldes est superieur a la Date début des soldes
        $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_debut_solde.value, document.ADForm.HTML_GEN_date_date_fin_solde.value) && document.ADForm.HTML_GEN_date_date_debut_solde.value != '' && document.ADForm.HTML_GEN_date_date_fin_solde.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin des soldes\' doit être postérieure à la Date début des soldes")."'); document.ADForm.HTML_GEN_date_date_fin_solde.focus(); return false; }";
        // check si la date Date fin de la saison est superieur a la Date fin des soldes
        $checkDateSaison .= "if(! isBefore(document.ADForm.HTML_GEN_date_date_fin_solde.value, document.ADForm.HTML_GEN_date_date_fin.value) && document.ADForm.HTML_GEN_date_date_fin_solde.value != '' && document.ADForm.HTML_GEN_date_date_fin.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin de la saison\' doit être postérieure à la Date fin des soldes")."'); document.ADForm.HTML_GEN_date_date_fin.focus(); return false; }";
        // check si la date de fin solde est anterieur a la date de fin de l'annee agricole
        $checkDateSaison .= "if(! isBeforeOrEqualTo(document.ADForm.HTML_GEN_date_date_fin_solde.value, '" . date("d/m/Y", mktime(0, 0, 0, (int)$date_debut_autre_saison_arr[0], $date_debut_autre_saison_arr[1], $date_debut_autre_saison_arr[2])) . "') && document.ADForm.HTML_GEN_date_date_solde.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin solde de la saison\' doit être antérieur à la date de fin de la saison suivante(".$date_debut_autre_saison_arr[1]."/".$date_debut_autre_saison_arr[0]."/".$date_debut_autre_saison_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_fin_solde.focus(); return false; }";
        // check si la date de fin est anterieur a la date de fin de l'annee agricole
        $checkDateSaison .= "if(! isBeforeOrEqualTo(document.ADForm.HTML_GEN_date_date_fin.value, '" . date("d/m/Y", mktime(0, 0, 0, (int)$date_debut_autre_saison_arr[0], $date_debut_autre_saison_arr[1], $date_debut_autre_saison_arr[2])) . "') && document.ADForm.HTML_GEN_date_date_fin.value != '') { alert('- " . _("La date précisée dans le champ \'Date fin de la saison\' doit être antérieur à la date de fin de la saison suivante(".$date_debut_autre_saison_arr[1]."/".$date_debut_autre_saison_arr[0]."/".$date_debut_autre_saison_arr[2].")")."'); document.ADForm.HTML_GEN_date_date_fin.focus(); return false; }";
        // check si un etat en cours existe deja

        $checkDateSaison .="
   if( ".$etat_saison_exist." != '' && ".$etat_saison_exist." !=2 && document.ADForm.HTML_GEN_LSB_etat_saison.value == 1 ){ alert('- " . _("il y a deja une saison ouverte")."');document.ADForm.HTML_GEN_LSB_etat_saison.focus(); return false;}";
      }
      //$MyPage->addJS(JSP_FORM, "funct_hide", $js_hide);
      $MyPage->addJS(JSP_FORM, "funct_check_date", $checkDateAvailable);
    }

    if ($SESSION_VARS['modif_table'] == 'ec_produit'){
      $MyPage->setFieldProperties('type_produit', FIELDP_TYPE, TYPC_LSB);
      $MyPage->setFieldProperties("type_produit", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties('type_produit', FIELDP_ADD_CHOICES, $adsys["adsys_type_produit"]);
      $MyPage->setFieldProperties("prix_unitaire", FIELDP_TYPE, TYPC_MNT);
      $MyPage->setFieldProperties("prix_unitaire", FIELDP_JS_EVENT, array("onChange" => "check_mnt_unitaire();"));
      $setPrixUnitaireModifiable = setPrixUnitaireModifiable();
      if ($setPrixUnitaireModifiable == FALSE) {
        //$MyPage->setFieldProperties("prix_unitaire", FIELDP_DEFAULT, 0);
        $MyPage->setFieldProperties("prix_unitaire", FIELDP_IS_LABEL, true);
      }
      $MyPage->setFieldProperties('etat_produit', FIELDP_TYPE, TYPC_LSB);
      $MyPage->setFieldProperties("etat_produit", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties('etat_produit', FIELDP_ADD_CHOICES, $adsys["adsys_etat_produit"]);
      $MyPage->setFieldProperties("montant_minimum", FIELDP_TYPE, TYPC_MNT);
      $MyPage->setFieldProperties("montant_minimum", FIELDP_IS_REQUIRED, true);
      $MyPage->setFieldProperties('compte_produit', FIELDP_ADD_CHOICES, $SESSION_VARS['info']['compte_produit']['choices']);
      $MyPage->setFieldProperties('compte_produit', FIELDP_IS_REQUIRED, true);

      $checkMnt_unitaire = "
      function check_mnt_unitaire() {
      mnt_unitaire = recupMontant(document.ADForm.prix_unitaire.value);
      mnt_mini = recupMontant(document.ADForm.montant_minimum.value);
      
      if(parseInt(mnt_unitaire) < parseInt(mnt_mini)){
       alert('- " . _("le prix unitaire doit être supérieur au prix minimum!")."');document.ADForm.prix_unitaire.focus();
       document.getElementsByName('prix_unitaire').item(0).value = 0;
       return false;
      }
      }
      ";
      $MyPage->addJS(JSP_FORM, "JS1", $checkMnt_unitaire);

    }

    if ($SESSION_VARS['modif_table'] == 'ec_localisation'){
      $codejs = " function populateParent()
      {
        if (document.ADForm.HTML_GEN_LSB_type_localisation.value > 1) {
            var _cQueue = [];
            var valueToPush = {};
            if (document.ADForm.HTML_GEN_LSB_type_localisation.value == 2){
              ";
      $where = "type_localisation = 1";
      $valueToPush = getListelocalisationPNSEB($where);
      while (list($key, $value) = each($valueToPush)) {
        $codejs.=" valueToPush['".$key."'] = '".$value."';";
      }
      $codejs.="} if (document.ADForm.HTML_GEN_LSB_type_localisation.value == 3){
              ";
      $where = "type_localisation = 2";
      $valueToPush = getListelocalisationPNSEB($where);
      while (list($key, $value) = each($valueToPush)) {
        $codejs.=" valueToPush['".$key."'] = '".$value."';";
      }
      $codejs.="} if (document.ADForm.HTML_GEN_LSB_type_localisation.value == 4){
              ";
      $where = "type_localisation = 3";
      $valueToPush = getListelocalisationPNSEB($where);
      while (list($key, $value) = each($valueToPush)) {
        $codejs.=" valueToPush['".$key."'] = '".$value."';";
      }
      $codejs.="}";
      $codejs.="
            _cQueue.push(valueToPush);

            var slt = document.ADForm.HTML_GEN_LSB_parent;
            // Reset select
            slt.options.length = 0;
            // Set default value
            slt.options[0] = new Option(\"[Aucun]\", \"0\", true, true);
            for (var i=0; i<_cQueue.length; i++) { // iterate on the array
              var obj = _cQueue[i];
              for (var key in obj) { // iterate on object properties
                var value = obj[key];
                //console.log(value);
                 opt = document.createElement('option');
                 opt.value = key;
                 opt.text = value;
                 slt.appendChild(opt);
              }
            }
        } else {
            var slt = document.ADForm.HTML_GEN_LSB_parent;
            // Reset select
            slt.options.length = 0;
            // Set default value
            slt.options[0] = new Option(\"[Aucun]\", \"0\", true, true);
        }
      }";
      $MyPage->addJS(JSP_FORM, "JS1", $codejs);
      $MyPage->setFieldProperties('type_localisation', FIELDP_TYPE, TYPC_LSB);
      $MyPage->setFieldProperties("type_localisation", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties('type_localisation', FIELDP_ADD_CHOICES, $adsys["adsys_type_localisation"]);
      $MyPage->setFieldProperties("type_localisation", FIELDP_JS_EVENT, array("onChange" => "populateParent();"));

      $where = "type_localisation = ".($contenu-1);
      $valueParentList = getListelocalisationPNSEB($where);
      $MyPage->setFieldProperties('parent', FIELDP_TYPE, TYPC_LSB);
      $MyPage->setFieldProperties('parent', FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties('parent', FIELDP_ADD_CHOICES, $valueParentList);
    }

    //Bouton
    $MyPage->addFormButton(1, 1, "butval", _("Valider"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butval", BUTP_PROCHAIN_ECRAN, "Gmf-2");
    $MyPage->setFormButtonProperties("butval", BUTP_JS_EVENT, array("onclick" => $checkDateSaison));
    if($SESSION_VARS['modif_table'] == "ec_annee_agricole") {
      if($saison_ouverte !=null){
        $MyPage->setFormButtonProperties("butval", BUTP_JS_EVENT, array("onclick" =>
          " if (document.ADForm.HTML_GEN_LSB_etat.value == 2){
        if (!confirm('" . _("ATTENTION") . "\\n " . _("Cette operation permet la fermeture de l\' année agricole. \\nPar conséquent, tous les commandes enregistrées, en cours ou en attentes de derogation seront passées en état non-soldé. \\n Une saison culturale pour cette anneé agricole est ouverte. Elle sera mis en etat fermé si vous continuez.\\nEtes-vous sur de vouloir continuer ? ") . "')) return false;
        }"));
      }else{
        $MyPage->setFormButtonProperties("butval", BUTP_JS_EVENT, array("onclick" =>
          " if (document.ADForm.HTML_GEN_LSB_etat.value == 2){
        if (!confirm('" . _("ATTENTION") . "\\n " . _("Cette operation permet la fermeture de l\' année agricole. \\nPar conséquent, tous les commandes enregistrées, en cours ou en attentes de derogation seront passées en état non-soldé.\\nEtes-vous sur de vouloir continuer ? ") . "')) return false;
        }"));
      }

    }
    /*if($SESSION_VARS['modif_table'] == "ec_saison_culturale") {
      $MyPage->setFormButtonProperties("butval", BUTP_JS_EVENT, array("onclick" =>
        " if (document.ADForm.HTML_GEN_LSB_etat_saison.value == 2){
        if (!confirm('" . _("ATTENTION") . "\\n " . _("Cette operation permet la fermeture de la saison culturale. \\nPar conséquent, tous les prix unitaires des produits seront ré-initialiser.\\nEtes-vous sur de vouloir continuer ? ") . "')) return false;
        }"));
    }*/


    $MyPage->addFormButton(1, 2, "butret", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gfp-3");

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }else signalErreur(__FILE__,__LINE__,__FUNCTION__);
}

else if ($global_nom_ecran == "Gmf-2") {
  global $dbHandler,$global_id_agence;
  $ary_exclude = array("id_annee");

  //création DATA à mettre à jour
  reset($SESSION_VARS['info']);

  while (list($key, $value) = each($SESSION_VARS['info']))
  {
    if (($key != "pkey") && (!in_array($key, $ary_exclude))) { //On n'insére pas les clés primaires

      //On n'insére pas certains champs en fonction du contexte
      if ((($value['type'] == TYPC_MNT) || ($value['type'] == TYPC_INT) || ($value['type'] == TYPC_PRC)) && (${$key} == NULL))
      {
        ${$key} = "0"; //NULL correspond à la valeur zéro pour les chiffres
      }

      if ($value['type'] == TYPC_DTG && (${$key} == "")) {
        ${$key} = "NULL"; //reset les dates
      }

      //FIXME : je sais, ce n'est vraiment pas propre...
      //if (($value['type'] == TYPC_TXT) && (${$key} == 0) && ($value['ref_field'] == 1400))
      // ${$key} = "NULL";

      if (($value['type'] == TYPC_TXT) && ($value['ref_field'] == 1400)) {
        // On consodère que la valeur 0 pour les list box est le choix [Aucun]
        if (${"HTML_GEN_LSB_" . $key} == "0")
          ${$key} = "NULL";
        else
          $DATA[$key] = ${"HTML_GEN_LSB_" . $key
          };
      }

      if ($value['type'] == TYPC_MNT)
        $DATA[$key] = recupMontant(${$key});
      else if ($value['type'] == TYPC_BOL) {
        if (isset(${$key}))
          $DATA[$key] = "t";
        else
          $DATA[$key] = "f";
      }
      else if ($value['type'] == TYPC_PRC)
        $DATA[$key] = "" . ((${$key}) / 100) . "";
      else
        $DATA[$key] = ${$key};
    }
  }
  if ($SESSION_VARS['modif_table'] == 'ec_annee_agricole' && $etat == 2){
    $id_annee_param =$SESSION_VARS['info']['modif_pkeyid'];
    $db = $dbHandler->openConnection();
    $sql="select * from fermeture_annee_agricole($id_annee_param)";
    $result= $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }
    $saison_ouverte_valider=getListeSaisonPNSEB("id_annee=".$id_annee_param."and etat_saison = 1");
    if ($saison_ouverte_valider !=null){
      $sql_update = "update ec_saison_culturale set etat_saison = 2 where id_annee = $id_annee_param ";
      $result_update= $db->query($sql_update);
      if (DB::isError($result_update)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result_update->getMessage());
      }
    }
    $dbHandler->closeConnection(true);
  }

  if ($SESSION_VARS['modif_table'] == 'ec_saison_culturale' && $etat_saison == 2){
    $id_annee_param =$SESSION_VARS['info']['modif_pkeyid'];
    $countStock = CheckCountAgentStock();
    if ($countStock["count"] > 0){
      $MyPage = new HTML_erreur(_("Confirmation modification"));
      $MyPage->setMessage(sprintf(_("Veuillez verifier que les delestages des agents soit affectués avant de fermer la saison !")));
      $MyPage->addButton(BUTTON_OK, "Gfp-2");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
      exit();
    }
  }

  //Mise à jour de la table : appel dbProcedure
  $myErr =  modif_table($SESSION_VARS['modif_table'], $SESSION_VARS['info']['pkey'], $SESSION_VARS['info']['modif_pkeyid'], $DATA);

  //HTML
  if ($myErr->errCode==NO_ERR) {
    $MyPage = new HTML_message(_("Confirmation modification"));
    $MyPage->setMessage(sprintf(_("L'entrée a été modifiée avec succès !")));
    $MyPage->addButton(BUTTON_OK, "Gfp-2");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
  else{
    $MyPage = new HTML_erreur(_("Echec de la modification"));
    $MyPage->setMessage($error[$myErr->errCode]);
    $MyPage->addButton(BUTTON_OK, "Gfp-2");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}

else if ($global_nom_ecran == "Gmd-1") {
  if (!isset($SESSION_VARS['table']) || $SESSION_VARS['table'] == '') {
    $SESSION_VARS['table'] = $table;
  }

  $MyPage = new HTML_GEN2(_("Liste des automatismes "));
  if($SESSION_VARS['table'] == 'mod_aut' || $SESSION_VARS['table'] == 'mod_mld') {
    $array_menu_engrais = array();
    if (isEngraisChimiques() && check_access(253)) {
      $array_menu_engrais["mod_pnseb"] = _("PNSEB-FENACOBU");
}
  if (isMobileLending() && check_access(903)) {
      $array_menu_engrais["mod_mld"] = _("Mobile Lending");
  }
    /*$array_menu_engrais = array(
      'mod_pnseb'=>_("PNSEB-FENACOBU")
    );*/
    $MyPage->addField("contenu", _("Liste des modules"), TYPC_LSB);
    $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
    $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $array_menu_engrais);
    $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);
  }

  //Bouton formulaire
  if($SESSION_VARS['table'] == 'mod_mld'){
    $MyPage->addButton("contenu", "butparam", _("OK"), TYPB_SUBMIT);
  }else{
    $MyPage->addButton("contenu", "butparam", _("Parametrer"), TYPB_SUBMIT);
  }
  $MyPage->setButtonProperties("butparam", BUTP_PROCHAIN_ECRAN, "Gmd-2");
  $MyPage->setButtonProperties("butparam", BUTP_AXS, 252);


  $MyPage->addFormButton(1,1, "butret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gfp-1");

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

else if ($global_nom_ecran == "Gmd-2") {
  if (!isset($SESSION_VARS['table']) || $SESSION_VARS['table'] == '') {
    $SESSION_VARS['table'] = $table;
  }

  if($SESSION_VARS['table'] == 'mod_aut') {
  $MyPage = new HTML_GEN2(_("Automatisme PNSEB-FENACOBU"));

    $array_menu_automatisme = array(
      'mod_update_mnt'=>_("Mise à jour des montants commandes"),
      'raz_prix_produit'=>_("RAZ des prix unitaires des produits")
    );
    $MyPage->addField("contenu", _("Automatisme PNSEB-FENACOBU"), TYPC_LSB);
    $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
    $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $array_menu_automatisme);
    $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);
  }

    if($SESSION_VARS['table'] == 'mod_mld') {
        $MyPage = new HTML_GEN2(_("Automatisme Mobile Lending"));

        $array_menu_automatisme = array(
            'mod_launch_cron'=>_("Lancement manuel du cron de mise à jour"),
            'mod_launch_cron_salarie'=>_("Lancement manuel du cron de catégorisation des clients salariés")
        );
        $MyPage->addField("contenu", _("Automatisme Mobile Lending"), TYPC_LSB);
        $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
        $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $array_menu_automatisme);
        $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);
    }

  //Bouton formulaire
  if ($SESSION_VARS['table'] == 'mod_mld'){
    $MyPage->addButton("contenu", "butparam", _("OK"), TYPB_SUBMIT);
  }else{
    $MyPage->addButton("contenu", "butparam", _("Parametrer"), TYPB_SUBMIT);
  }
  $MyPage->setButtonProperties("butparam", BUTP_PROCHAIN_ECRAN, "Gmd-3");
  $MyPage->setButtonProperties("butparam", BUTP_AXS, 252);


  $MyPage->addFormButton(1,1, "butret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gfp-1");

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
else if ($global_nom_ecran == "Gmd-3") {
  if($contenu == 'mod_update_mnt') {
    $SESSION_VARS['auto_selected'] = $contenu;
    $MyPage = new HTML_GEN2(_("Mise à jour automatique des montants des commandes"));
    $alert_message = "";
    $alert_message = sprintf("<font color='red'>Cet automatisme permet de mettre à jour les montants pour toutes les commandes valides</font>");
    $msg_annulation = "<table align=\"center\" cellpadding=\"5\" width=\"65% \" border=0 cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
    <tr align=\"center\" ><th></th><th></th><th></th><th></th><th></th><th></th></tr><tr><td align=\"center\"  colspan='6'>".$alert_message."</td></tr></table></br>";
    $MyPage->addHTMLExtraCode("msg_annulation", $msg_annulation);


    $MyPage->addFormButton(1,1, "butmaj", _("Mise à jour automatique des montants des commandes"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butmaj", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butmaj", BUTP_PROCHAIN_ECRAN, "Gmd-4");

    $MyPage->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gfp-1");


    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }

  if($contenu == 'raz_prix_produit') {
    $SESSION_VARS['auto_selected'] = $contenu;
    $MyPage = new HTML_GEN2(_("Mise à jour automatique des prix unitaires des produits"));
    $alert_message = "";
    $alert_message = sprintf("<font color='red'>Cet automatisme permet de mettre à jour les prix unitaires des produits</font>");
    $msg_annulation = "<table align=\"center\" cellpadding=\"5\" width=\"65% \" border=0 cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
    <tr align=\"center\" ><th></th><th></th><th></th><th></th><th></th><th></th></tr><tr><td align=\"center\"  colspan='6'>".$alert_message."</td></tr></table></br>";
    $MyPage->addHTMLExtraCode("msg_annulation", $msg_annulation);


    $MyPage->addFormButton(1,1, "butmaj", _("Mise a jour des prix untaires des produits"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butmaj", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butmaj", BUTP_PROCHAIN_ECRAN, "Gmd-4");

    $MyPage->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gfp-1");


    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }

  if($contenu == 'mod_launch_cron'){
      $SESSION_VARS['auto_selected'] = $contenu;
    $MyPage = new HTML_message("Lancement du cron manuellement pour les mises à jour des données clients");

    // Javascript
    $js_valid  = "function processTraitement(){\n";
    $js_valid .= "document.ADForm.onsubmit = function(){ if (document.ADForm.prochain_ecran.value == 'Gmd-4') { document.ADForm.img_loading.src='$http_prefix/images/loading.gif'; document.ADForm.BOUI.disabled = true; } }";
    $js_valid .= "\n}\nprocessTraitement();";
    $MyPage->setMessage("<br />Lancer l'automatisme de mise à jour des données clientes ?<br /><img name=\"img_loading\"/><script>".$js_valid."</script>");

    $MyPage->addButton("BUTTON_OUI", 'Gmd-4');
    $MyPage->addButton("BUTTON_NON", 'Gfp-1');

      $MyPage->buildHTML();
      echo $MyPage->getHTML();
  }
  else if ($contenu = 'mod_launch_cron_salarie'){
      $SESSION_VARS['auto_selected'] = $contenu;
      $MyPage = new HTML_message("Lancement du cron manuellement pour la catégorisation des clients salariés");

      // Javascript
      $js_valid  = "function processTraitement(){\n";
      $js_valid .= "document.ADForm.onsubmit = function(){ if (document.ADForm.prochain_ecran.value == 'Gmd-4') { document.ADForm.img_loading.src='$http_prefix/images/loading.gif'; document.ADForm.BOUI.disabled = true; } }";
      $js_valid .= "\n}\nprocessTraitement();";
      $MyPage->setMessage("<br />Lancer l'automatisme la catégorisation des clients salariés?<br /><img name=\"img_loading\"/><script>".$js_valid."</script>");

      $MyPage->addButton("BUTTON_OUI", 'Gmd-4');
      $MyPage->addButton("BUTTON_NON", 'Gfp-1');

      $MyPage->buildHTML();
      echo $MyPage->getHTML();
  }


}
else if ($global_nom_ecran == "Gmd-4") {

  if($SESSION_VARS['auto_selected'] == 'mod_update_mnt') {
    global $dbHandler,$global_id_agence;

    $condition_annee_agri = "etat =1";
    $annee_agri_actuelle =getRangeDateAnneeAgri($condition_annee_agri);

    $condition_saison_cultu = "id_annee = ".$annee_agri_actuelle['id_annee']." and etat_saison = 1";
    $saison_cultu_acutelle = getDetailSaisonCultu($condition_saison_cultu);
    $id_annee= $annee_agri_actuelle['id_annee'];
    $id_saison = $saison_cultu_acutelle['id_saison'];


    $condi1="etat_produit = 1";
    $verif_prix_prod =getListeProduitPNSEB($condi1,true);

    while (list($key, $DET) = each($verif_prix_prod)) {
      if ($DET['prix_unitaire'] == 0){
        $html_err = new HTML_erreur(_("Mise a jour des montants des commandes"));
        $html_err->setMessage(_("Le prix unitaire du produit : ".$DET['libel']." n'a pas été renseigné"));
        $html_err->addButton("BUTTON_OK", 'Gfp-1');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit();
      }
    }


    $db = $dbHandler->openConnection();
    $sql="select * from update_montant_commande($id_annee,$id_saison)";
    $result= $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    }

    $condi_hist="etat_produit = 1";
    $produits_actif =getListeProduitPNSEB($condi_hist,true);
    while (list($key1, $DET1) = each($produits_actif)) {
      $DATA_PROD_HIST = array(
        'id_produit'=>$DET1['id_produit'],
        'id_saison' => $id_saison,
        'prix_unitaire' => $DET1['prix_unitaire'],
        'date_creation' =>date('r'),
        'id_ag' =>$global_id_agence
      );
      $result1 = executeQuery($db, buildInsertQuery("ec_produit_hist", $DATA_PROD_HIST));
      if (DB::isError($result1)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,$result1->getMessage());
      }
    }



    $dbHandler->closeConnection(true);

    $html_msg = new HTML_message("Confirmation de la mise à jour des montants des commandes");

    $demande_msg = "Votre automatisme de mise à jour est reussi!";


    $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

    $html_msg->addButton("BUTTON_OK", 'Gfp-1');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;

  }

  if($SESSION_VARS['auto_selected'] == 'raz_prix_produit') {
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();
    $sql="update ec_produit set prix_unitaire = 0";
    $result= $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
    } else{
      $dbHandler->closeConnection(true);
      $html_msg = new HTML_message("Confirmation de la mise à jour des prix des produits");

      $demande_msg = "Votre automatisme de mise à jour est reussi!";


      $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

      $html_msg->addButton("BUTTON_OK", 'Gfp-1');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    }


  }

    if($SESSION_VARS['auto_selected'] == 'mod_launch_cron') {

// Recuperation du pourcentage du montant max
        $file_path = '/usr/share/adbanking/web/services/mobile_lending/parametrage_mobile_lending.csv';
        $fichier_lot = $file_path;
        $handle = fopen($fichier_lot, "r");
        $columns_mnt_max = array(41);
        $count = 0;
        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count == 0) {
                $count++;
                continue;
            }
            foreach ($data as $index => $val) {
                if (in_array($index + 1, $columns_mnt_max) && $val != null) {
                    ${"prc_mnt_max" . $index} = $val;
                }
            }
        }



// recuperation du alpha de coefficient present
        $handle = fopen($fichier_lot, "r");
        $columns_coeff_present= array(43);
        $count = 0;
        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count == 0) {
                $count++;
                continue;
            }
            foreach ($data as $index => $val) {
                if (in_array($index + 1, $columns_coeff_present) && $val != null) {
                    ${"coeff_present" . $index} = $val;
                }
            }
        }

// montant max empruner
        $handle = fopen($fichier_lot, "r");
        $columns_mnt_max_emprunt = array(35);
        $count_coeff = 0;
        while (($data_mnt_emprunter = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_mnt_emprunter as $index_mnt_emprunter => $val_mnt_emprunter) {
                if (in_array($index_mnt_emprunter + 1, $columns_mnt_max_emprunt)) {
                    if ($val_mnt_emprunter != null){
                        $mnt_max_emprunter = $val_mnt_emprunter;
                    }
                }
            }
        }
        fclose($handle);

// montant max nouveau client
        $handle = fopen($fichier_lot, "r");
        $columns_mnt_max_new_client = array(36);
        $count_coeff = 0;
        while (($data_mnt_max_new_client = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_mnt_max_new_client as $index_mnt_max_new_client => $val_mnt_max_new_client) {
                if (in_array($index_mnt_max_new_client + 1, $columns_mnt_max_new_client)) {
                    if ($val_mnt_max_new_client != null){
                        $mnt_max_new_client = $val_mnt_max_new_client;
                    }
                }
            }
        }
        fclose($handle);

// Lecture des coefficients def irregularite
        $handle = fopen($fichier_lot, "r");
        $columns_coeff = array(45);
        $count_coeff = 0;
        while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_coeff as $index_coeff => $val_coeff) {
                if (in_array($index_coeff + 1, $columns_coeff)) {
                    if ($val_coeff != null){
                        $coeff_def_irregularite= $val_coeff;
                    }
                }
            }
        }

        fclose($handle);

// Appelle de la fonction de mise a jour des donnees clients
        $db = $dbHandler->openConnection();
        $sql = "SELECT extraction_donnees_mobile_lending_v2();";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            echo "Failed => Fonction mise a jour des donnees credits";echo "\n";
            $dbHandler->closeConnection(false);
        }else{
            $dbHandler->closeConnection(true);
        }



// Appelle de la fonction de mise a jour des donnees clients
        $db = $dbHandler->openConnection();
        $sql = "SELECT mise_a_jour_donnee_abonnee(".$prc_mnt_max40.",".$coeff_present42.",".$mnt_max_emprunter.", ".$mnt_max_new_client.",".$coeff_def_irregularite.");";
        $result = $db->query($sql);
        if (DB::isError($result)) {
            echo "Failed => Fonction mise a jour des clients abonnees";echo "\n";
            $dbHandler->closeConnection(false);
        }else{
            $dbHandler->closeConnection(true);
        }


        /******************************************UPDATE TRACNHE DATA**********************************************************/

        $file_path = '/usr/share/adbanking/web/services/mobile_lending/parametrage_mobile_lending.csv';
        $fichier_lot = $file_path;

        if (!file_exists($fichier_lot)) {
            $dbHandler->closeConnection(false);
            return new ErrorObj(ERR_FICHIER_DONNEES);
        }


// SAlaire moyen
        $handle = fopen($fichier_lot, "r");

        $columns_sal_moy = array(21,22,23);
        $count_sal_moy = 0;

        while (($data_sal_moy = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_sal_moy == 0) {
                $count_sal_moy++;
                continue;
            }
            foreach ($data_sal_moy as $index_sal_moy => $val_sal_moy) {
                if (in_array($index_sal_moy + 1, $columns_sal_moy)) {
                    ${"sal_moy" . $index_sal_moy} = $val_sal_moy;
                }
            }

            if (!empty($sal_moy22)) {
                if ($sal_moy20 == NULL){
                    $sal_moy20 = 0;
                }
                $db = $dbHandler->openConnection();
                $sql_sal_moy = "UPDATE ml_donnees_client_abonnees SET tranche_sal_moyen = $sal_moy22 WHERE salaire_moyen>= $sal_moy20 and salaire_moyen <= $sal_moy21";

                $result_mnt_sal = $db->query($sql_sal_moy);
                if (DB::isError($result_mnt_sal)) {
                    echo "Failed";
                    echo "\n";
                    $dbHandler->closeConnection(false);
                } else {
                    $dbHandler->closeConnection(true);
                }
            }
        }
        fclose($handle);

//irregularite
        $handle = fopen($fichier_lot, "r");

        $columns_irregularite= array(9,10,11);
        $count_irregularite = 0;

        while (($data_irregularite = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if($count_irregularite == 0){ $count_irregularite++; continue; }
            foreach ($data_irregularite as $index_irregularite => $val_irregularite) {
                if (in_array($index_irregularite + 1, $columns_irregularite)) {
                    ${"irregularite" . $index_irregularite} = $val_irregularite;
                }
            }

            if (!empty($irregularite10)) {
                $db = $dbHandler->openConnection();
                $sql_irregularite= "UPDATE ml_donnees_client_abonnees SET tranche_irregularite = $irregularite10 WHERE tx_irregularite>= $irregularite8 and tx_irregularite <= $irregularite9";
                $result_mnt_dem = $db->query($sql_irregularite);
                if (DB::isError($result_mnt_dem)) {
                    echo "Failed";echo "\n";
                    $dbHandler->closeConnection(false);
                }else{
                    $dbHandler->closeConnection(true);
                }
            }
        }
        fclose($handle);

// Lecture de la tranche nbre de credit
        $handle = fopen($fichier_lot, "r");

        $columns_nb_credit = array(17,18,19);
        $count_nb_credit = 0;

        while (($data_nb_credit = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if($count_nb_credit == 0){ $count_nb_credit++; continue; }
            foreach ($data_nb_credit as $index_nb_credit => $val_nb_credit) {
                if (in_array($index_nb_credit + 1, $columns_nb_credit)) {
                    ${"nb_credit" . $index_nb_credit} = $val_nb_credit;
                }
            }

            if (!empty($nb_credit18)) {
                $db = $dbHandler->openConnection();
                $sql_nbre_credit= "UPDATE ml_donnees_client_abonnees SET tranche_nbre_credit = $nb_credit18 WHERE nbre_credit>= $nb_credit16 and nbre_credit <= $nb_credit17";
                $result_nbre_credit= $db->query($sql_nbre_credit);
                if (DB::isError($result_nbre_credit)) {
                    echo "Failed";echo "\n";
                    $dbHandler->closeConnection(false);
                }else{
                    $dbHandler->closeConnection(true);
                }
            }
        }
        fclose($handle);

//Lecture mnt tot emprunter
        $handle = fopen($fichier_lot, "r");

        $columns_salaire = array(5,6,7);
        $count_salaire = 0;

        while (($data_salaire = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if($count_salaire == 0){ $count_salaire++; continue; }
            foreach ($data_salaire as $index_salaire => $val_salaire) {
                if (in_array($index_salaire + 1, $columns_salaire)) {
                    ${"salaire" . $index_salaire} = $val_salaire;
                }
            }
            if (!empty($salaire6)) {
                if ($salaire4 == NULL){
                    $salaire4 = 0;
                }
                $db = $dbHandler->openConnection();
                $sql_mnt_dem= "UPDATE ml_donnees_client_abonnees SET tranche_tot_emprunter= $salaire6 WHERE mnt_tot_emprunter>= $salaire4 and mnt_tot_emprunter <= $salaire5";
                $result_mnt_dem = $db->query($sql_mnt_dem);
                if (DB::isError($result_mnt_dem)) {
                    echo "Failed";echo "\n";
                    $dbHandler->closeConnection(false);
                }else{
                    $dbHandler->closeConnection(true);
                }
            }
        }
        fclose($handle);

//Lecture tranche age
        $handle = fopen($fichier_lot, "r");

        $columns_age = array(1,2,3);
        $count_age = 0;

        while (($data_age = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if($count_age == 0){ $count_age++; continue; }
            foreach ($data_age as $index_age => $val_age) {
                if (in_array($index_age + 1, $columns_age)) {
                    ${"age" . $index_age} = $val_age;
                }
            }
            if (!empty($age2)) {
                if ($age0 == NULL){
                    $age0 = 0;
                }
                $db = $dbHandler->openConnection();
                    $sql_age= "UPDATE ml_donnees_client_abonnees SET tranche_age= $age2 WHERE age>= $age0 and age <= $age1";
                $result_age = $db->query($sql_age);
                if (DB::isError($result_age)) {
                    echo "Failed age";echo "\n";
                    $dbHandler->closeConnection(false);
                }else{
                    $dbHandler->closeConnection(true);
                }
            }
        }
        fclose($handle);

//Lecture tranche lg_histo
$handle = fopen($fichier_lot, "r");

$columns_lg_histo= array(13,14,15);
$count_lg_histo = 0;

while (($data_lg_histo = fgetcsv($handle, 2000, ",")) !== FALSE) {
    if($count_lg_histo == 0){ $count_lg_histo++; continue; }
    foreach ($data_lg_histo as $index_lg_histo => $val_lg_histo) {
        if (in_array($index_lg_histo + 1, $columns_lg_histo)) {
            ${"lg_histo" . $index_lg_histo} = $val_lg_histo;
        }
    }
    if (!empty($lg_histo14)) {
        if ($lg_histo12 == NULL){
            $lg_histo12 = 0;
        }
        $db = $dbHandler->openConnection();
        $sql_lg_histo= "UPDATE ml_donnees_client_abonnees SET tranche_lg_histo= $lg_histo14 WHERE lg_histo>= $lg_histo12 and lg_histo <= $lg_histo13";
        $result_lg_histo = $db->query($sql_lg_histo);
        if (DB::isError($result_lg_histo)) {
            echo "Failed lg histo";echo "\n";
            $dbHandler->closeConnection(false);
        }else{
            $dbHandler->closeConnection(true);
        }
    }
}
fclose($handle);

// Lecture des coefficients passe
        $handle = fopen($fichier_lot, "r");

        $columns_coeff = array(47);
        $count_coeff = 0;

        while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_coeff as $index_coeff => $val_coeff) {
                if (in_array($index_coeff + 1, $columns_coeff)) {
                    if ($val_coeff != null){
                        $coeff_passe = $val_coeff;
                    }
                }
            }
        }

        fclose($handle);

// Lecture des coefficients present
        $handle = fopen($fichier_lot, "r");

        $columns_coeff = array(49);
        $count_coeff = 0;

        while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_coeff as $index_coeff => $val_coeff) {
                if (in_array($index_coeff + 1, $columns_coeff)) {
                    if ($val_coeff != null){
                        $coeff_present = $val_coeff;
                    }
                }
            }
        }

        fclose($handle);

// Lecture des coefficients futur
        $handle = fopen($fichier_lot, "r");

        $columns_coeff = array(51);
        $count_coeff = 0;

        while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_coeff as $index_coeff => $val_coeff) {
                if (in_array($index_coeff + 1, $columns_coeff)) {
                    if ($val_coeff != null){
                        $coeff_futur = $val_coeff;
                    }
                }
            }
        }

        fclose($handle);

// Lecture des coefficients present sans credit
        $handle = fopen($fichier_lot, "r");

        $columns_coeff = array(53);
        $count_coeff = 0;

        while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_coeff as $index_coeff => $val_coeff) {
                if (in_array($index_coeff + 1, $columns_coeff)) {
                    if ($val_coeff != null){
                        $coeff_present_new_client = $val_coeff;
                    }
                }
            }
        }

        fclose($handle);

// Lecture des coefficients futur sans credit
        $handle = fopen($fichier_lot, "r");

        $columns_coeff = array(55);
        $count_coeff = 0;

        while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_coeff as $index_coeff => $val_coeff) {
                if (in_array($index_coeff + 1, $columns_coeff)) {
                    if ($val_coeff != null){
                        $coeff_futur_new_client = $val_coeff;
                    }
                }
            }
        }

        fclose($handle);

// Lecture des coefficients passe sans combinaison avec credit
        $handle = fopen($fichier_lot, "r");

        $columns_coeff = array(57);
        $count_coeff = 0;

        while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_coeff as $index_coeff => $val_coeff) {
                if (in_array($index_coeff + 1, $columns_coeff)) {
                    if ($val_coeff != null){
                        $coeff_passe_sans_combi_avec_credit= $val_coeff;
                    }
                }
            }
        }

        fclose($handle);

// Lecture des coefficients present sans combinaison avec credit
        $handle = fopen($fichier_lot, "r");

        $columns_coeff = array(59);
        $count_coeff = 0;

        while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_coeff as $index_coeff => $val_coeff) {
                if (in_array($index_coeff + 1, $columns_coeff)) {
                    if ($val_coeff != null){
                        $coeff_present_sans_combi_avec_credit= $val_coeff;
                    }
                }
            }
        }

        fclose($handle);

// Lecture des coefficients present sans combinaison sans credit
        $handle = fopen($fichier_lot, "r");

        $columns_coeff = array(61);
        $count_coeff = 0;

        while (($data_coeff = fgetcsv($handle, 2000, ",")) !== FALSE) {
            if ($count_coeff == 0) {
                $count_coeff++;
                continue;
            }
            foreach ($data_coeff as $index_coeff => $val_coeff) {
                if (in_array($index_coeff + 1, $columns_coeff)) {
                    if ($val_coeff != null){
                        $coeff_present_sans_combi_sans_credit= $val_coeff;
                    }
                }
            }
        }

        fclose($handle);

        /*****************************FIN UPDATE TRANCHE DATA CLIENT***********************************************/
// Recuperation score futur dans la table des combinaisons globales
        $data_client_abonnee = getDataClientAbonnee();
// fonction getLocalisationIMF
        $localisation_imf = getLocalisationIMF();
        $loc_imf = $localisation_imf['ml_localisation'];
        foreach ($data_client_abonnee as $index => $value) {
            $combinaison_pleine = 'f';
            $score = 0;
            $db = $dbHandler->openConnection();
            $sql = "SELECT score_retard, nbre_dossier FROM ml_combinaison_global WHERE tranche_sal_moyen = " . $value['tranche_sal_moyen'] . " AND  tranche_irregularite= " . $value['tranche_irregularite'] . " AND tranche_nbre_credit= " . $value['tranche_nbre_credit'] . " AND tranche_tot_emprunter= " . $value['tranche_tot_emprunter'] ." AND tranche_localisation = ".$loc_imf;

            $result = $db->query($sql);
            if (DB::isError($result)) {
                echo "Failed => " . $sql;
                echo "\n";
                $dbHandler->closeConnection(false);
            }
            $row = $result->fetchrow();
            $db = $dbHandler->closeConnection(true);
            $score = $row[0];
            $nbre_doss = $row[1];
            if ($nbre_doss >= 3){
              $combinaison_pleine = 't';
            }


            /******************Recuperation des Bonus garantit****************/
            $ratio_bonus_gar = 0;
            $bonus_gar_calculer = 0;
            $garantit_en_cours = getGarantiEnCours($value['client']);
            if ($garantit_en_cours > 0) {
                $ratio_bonus_gar = ($garantit_en_cours / $value['mnt_restant_du']) * 100;

                // Lecture des bonus de garanti
                $handle = fopen($fichier_lot, "r");
                echo "\n";
                echo "\n";
                $columns_bonus = array(31, 32, 33);
                $count_bonus = 0;

                while (($data_bonus = fgetcsv($handle, 2000, ",")) !== FALSE) {
                    if ($count_bonus == 0) {
                        $count_bonus++;
                        continue;
                    }
                    foreach ($data_bonus as $index_bonus => $val_bonus) {
                        if (in_array($index_bonus + 1, $columns_bonus)) {
                            ${"bonus_gar" . $index_bonus} = $val_bonus;
                        }
                    }

                    if (!empty($bonus_gar32)) {
                        if (($ratio_bonus_gar > $bonus_gar30) && ($ratio_bonus_gar <= $bonus_gar31)) {
                            $bonus_gar_calculer = $bonus_gar32;
                        }
                    }
                }
                fclose($handle);
            }
            /*****************************************************/

            // Lecture de la constante 1
            $handle = fopen($fichier_lot, "r");
            echo "\n";echo "\n";
            $columns_c1 = array(63);
            $count_c1 = 0;
            while (($data_c1 = fgetcsv($handle, 2000, ",")) !== FALSE) {
                if ($count_c1 == 0) {
                    $count_c1++;
                    continue;
                }
                foreach ($data_c1 as $index_c1 => $val_c1) {
                    if (in_array($index_c1 + 1, $columns_c1)) {
                        if ($val_c1 != null){
                            $coeff_c1= $val_c1;
                        }
                    }
                }
            }

            fclose($handle);

            // Lecture de la constante 2
            $handle = fopen($fichier_lot, "r");
            echo "\n";echo "\n";
            $columns_c2 = array(65);
            $count_c2 = 0;
            while (($data_c2 = fgetcsv($handle, 2000, ",")) !== FALSE) {
                if ($count_c2 == 0) {
                    $count_c2++;
                    continue;
                }
                foreach ($data_c2 as $index_c2 => $val_c2) {
                    if (in_array($index_c2 + 1, $columns_c2)) {
                        if ($val_c2 != null){
                            $coeff_c2= $val_c2;
                        }
                    }
                }
            }

            fclose($handle);

            // Lecture de la constante 3
            $handle = fopen($fichier_lot, "r");
            echo "\n";echo "\n";
            $columns_c3 = array(67);
            $count_c3 = 0;
            while (($data_c3 = fgetcsv($handle, 2000, ",")) !== FALSE) {
                if ($count_c3 == 0) {
                    $count_c3++;
                    continue;
                }
                foreach ($data_c3 as $index_c3 => $val_c3) {
                    if (in_array($index_c3 + 1, $columns_c3)) {
                        if ($val_c3 != null){
                            $coeff_c3= $val_c3;
                        }
                    }
                }
            }

            fclose($handle);

            // Lecture de la constante 4
            $handle = fopen($fichier_lot, "r");
            echo "\n";echo "\n";
            $columns_c4 = array(69);
            $count_c4 = 0;
            while (($data_c4 = fgetcsv($handle, 2000, ",")) !== FALSE) {
                if ($count_c4 == 0) {
                    $count_c4++;
                    continue;
                }
                foreach ($data_c4 as $index_c4 => $val_c4) {
                    if (in_array($index_c4 + 1, $columns_c4)) {
                        if ($val_c4 != null){
                            $coeff_c4= $val_c4;
                        }
                    }
                }
            }

            fclose($handle);


            //calcul du score finale
            if ($value['nbre_credit'] > 0){
                if ($score == 10000){
                    $score_final = $coeff_passe_sans_combi_avec_credit * $value['score_passe'] + $coeff_present_sans_combi_avec_credit * $value['score_present'] + $bonus_gar_calculer +$coeff_c3;
                    $score_final = round($score_final, 2);
                }else {
                    $score_final = $coeff_passe * $value['score_passe'] + $coeff_present * $value['score_present'] + $coeff_futur * $score + $bonus_gar_calculer+$coeff_c1;
                    $score_final = round($score_final, 2);
                }

            }
            else{
                if ($score == 10000){
                    $score_final =  $coeff_present_sans_combi_sans_credit * $value['score_present'] +$bonus_gar_calculer+ $coeff_c4;
                    $score_final = round($score_final, 2);
                }else {
                    $score_final = $coeff_present_new_client * $value['score_present'] + $coeff_futur_new_client * $score +$bonus_gar_calculer + $coeff_c2;
                    $score_final = round($score_final, 2);
                }
            }

            //Malus Actif moins de 3 mois
            if ($value['actif_3_mois'] == 'f'){
              $score_final = $score_final - 80;
            }
            if ($value['salaire_moyen_non_nul'] == 'f'){
              $score_final = $score_final - 80;
            }


            $db = $dbHandler->openConnection();
            $sql_update_score_futur = "UPDATE ml_donnees_client_abonnees SET score_futur = $score, score_final = $score_final, bonus_gar = $bonus_gar_calculer, combinaison_pleine = '$combinaison_pleine' WHERE client =" . $value['client'];

            $result_score_futur = $db->query($sql_update_score_futur);
            if (DB::isError($result_score_futur)) {
                echo "Failed => ".$sql_update_score_futur;
                echo "\n";
                $dbHandler->closeConnection(false);
            } else {
                $dbHandler->closeConnection(true);
            }

            $db = $dbHandler->openConnection();
            $sql_update_abo = "UPDATE ad_abonnement SET ml_score = $score_final WHERE id_client =" . $value['client']." AND deleted = 'f' AND id_service = 1";
            $result_abo = $db->query($sql_update_abo);
            if (DB::isError($result_abo)) {
                echo "Failed => ".$sql_update_abo;
                echo "\n";
                $dbHandler->closeConnection(false);
            } else {
                $dbHandler->closeConnection(true);
            }
        }
        $html_msg = new HTML_message("Confirmation de la mise à jour des données clients Mobile Lending");

        $demande_msg = "Votre automatisme de mise à jour est reussi!";


        $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

        $html_msg->addButton("BUTTON_OK", 'Gfp-1');
        $html_msg->buildHTML();
        echo $html_msg->HTML_code;


    }

  if($SESSION_VARS['auto_selected'] == 'mod_launch_cron_salarie') {
      global $dbHandler, $global_id_agence;
      $db = $dbHandler->openConnection();
      $sql="select recup_salarie();";
      $result= $db->query($sql);
      if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__,$result->getMessage());
      } else {
          $dbHandler->closeConnection(true);
          $html_msg = new HTML_message("Confirmation de la mise à jour de catégorisation des clients salariés");

          $demande_msg = "Votre automatisme de mise à jour est reussi!";


          $html_msg->setMessage(sprintf(" <br />%s  !<br /> ", $demande_msg));

          $html_msg->addButton("BUTTON_OK", 'Gfp-1');
          $html_msg->buildHTML();
          echo $html_msg->HTML_code;
      }
  }
}

?>