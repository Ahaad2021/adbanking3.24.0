<?php

require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/budget.php');
require_once ('modules/rapports/xml_budget.php');
require_once 'lib/misc/csv.php';
require_once 'lib/misc/excel.php';

if ($global_nom_ecran == "Rpb-1") {
  if (isset($SESSION_VARS['nom_rapport'])){
    unset($SESSION_VARS['nom_rapport']);
  }

  $MyPage = new HTML_GEN2(_("Liste des rapports:"));
  $array_menu_rapport = array(
    'eta_exe'=>_("Etat d’exécution budgétaire "),
    'his_rev'=>_("Rapport historique de révision budgétaire "),
    'rap_bud'=>_("Rapport budget")
  );
  $MyPage->addField("contenu", _("Liste des rapports"), TYPC_LSB);
  $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
  $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $array_menu_rapport);
  $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);

  //Bouton formulaire
  $MyPage->addFormButton(1,1, "butparam", _("Parametrer"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butparam", BUTP_PROCHAIN_ECRAN, "Rpb-2");


  $MyPage->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gen-15");

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

else if ($global_nom_ecran == "Rpb-2") {
  if ($contenu == 'eta_exe' || $SESSION_VARS['nom_rapport'] == 'eta_exe'){
    $MyPage = new HTML_GEN2();
    $MyPage->setTitle(_("Etat d’exécution budgétaire"));
    if (isset($contenu)){
      $SESSION_VARS['nom_rapport']= $contenu;
    }

    if (isset($exo_budget) && $exo_budget > 0){
      $exo_encours = getExoDefini($exo_budget);
      $date_deb_exo = $exo_encours['date_deb_exo'];
      $date_deb_exo_explode = explode('-',$date_deb_exo);
      $annee_exo = $date_deb_exo_explode[0];

      $array_date_deb_trim = array(
        '01/01/'.$annee_exo =>_("Trimestre 1"),
        '01/04/'.$annee_exo=>_("Trimestre 2"),
        '01/07/'.$annee_exo =>_("Trimestre 3"),
        '01/10/'.$annee_exo=>_("Trimestre 4")
      );
    }



    $adsys_type_budget = $adsys["adsys_type_budget"];

    $date_fin_exo_explode = explode('-',$exo_encours["date_fin_exo"]);
    $annee_fin_exo = $date_fin_exo_explode[0];
    $mois_fin_exo = $date_fin_exo_explode[1];
    $jour_fin_exo = $date_fin_exo_explode[2];
    $date_fin_exo = $jour_fin_exo.'/'.$mois_fin_exo.'/'.$annee_fin_exo;

    $MyPage->addHiddenType ("date_fin_exo", $date_fin_exo);

    $MyPage->addField("type_budget", _("Type de budget"), TYPC_LSB);
    $MyPage->setFieldProperties('type_budget', FIELDP_ADD_CHOICES, $adsys_type_budget);
    $MyPage->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_TOUS, false);
    $MyPage->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_AUCUN, true);
    $MyPage->setFieldProperties("type_budget", FIELDP_IS_REQUIRED, true);
    if (isset($type_budget) && $type_budget > 0) {
      $MyPage->setFieldProperties("type_budget", FIELDP_DEFAULT, $type_budget);
    }

    //$exercices = getAllExerciceBudget();
    //$exo_encours = getExoEnCours();
    //REL-104 : correction - Après la cloture de l’exercice, il n’est pas possible d’acceder les rapports bubget de l’exercice précédent
    // car le filtre ‘Exercice Budget” affiche uniquement le budget de l’exercice en cours”
    $exo_encours = getExoEnCoursAll('etat_exo IN (1,3) AND id_exo_compta IN (SELECT DISTINCT exo_budget FROM ad_budget WHERE etat_budget >= 3)');
    $exo_encours_all = array();
    if ($exo_encours != null){
      foreach($exo_encours as $key => $value){
        $exo_encours_all[$key] = $value["debut_annee"]." - ".$value["debut_annee"];
      }
    }
    $MyPage->addField("exo_budget", _("Exercice Budget"), TYPC_LSB);
    $MyPage->setFieldProperties("exo_budget", FIELDP_HAS_CHOICE_AUCUN,true);
    $MyPage->setFieldProperties("exo_budget", FIELDP_HAS_CHOICE_TOUS,false);
    $MyPage->setFieldProperties("exo_budget", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("exo_budget", FIELDP_ADD_CHOICES, $exo_encours_all);
    $MyPage->setFieldProperties("exo_budget", FIELDP_JS_EVENT, array("onChange"=>"assign('Rpb-2'); this.form.submit();"));
    if (isset($exo_budget) && $exo_budget > 0) {
      $MyPage->setFieldProperties("exo_budget", FIELDP_DEFAULT, $exo_budget);
    }

    $MyPage->addField("trim_depart", _("Trimestre de depart"), TYPC_LSB);
    if (isset($exo_budget) && $exo_budget > 0){
      $MyPage->setFieldProperties('trim_depart', FIELDP_ADD_CHOICES, $array_date_deb_trim);
    }
    $MyPage->setFieldProperties("trim_depart", FIELDP_HAS_CHOICE_TOUS, false);
    $MyPage->setFieldProperties("trim_depart", FIELDP_HAS_CHOICE_AUCUN, true);
    $MyPage->setFieldProperties("trim_depart", FIELDP_IS_REQUIRED, true);

    $MyPage->addField("date_fin", _("Date fin "), TYPC_DTE);
    //REL-104 :  Pour les rapports budget (historique de revision budgetaire, rapport budget, et rapport execution budgétaire),
    // pour l’exercice qui precedent l’exercice en cours, la date fin devrait être la date de fin de l’année (soit le 31/12/2019)
    // avec flexibilité de modification pour une date inferieure de même année mais non pour une date superieure
    if (isset($exo_budget) && $exo_budget > 0){
      $MyPage->setFieldProperties("date_fin", FIELDP_DEFAULT, $date_fin_exo);
    }
    else{
      $MyPage->setFieldProperties("date_fin", FIELDP_DEFAULT, date("d/m/Y"));
    }
    $MyPage->setFieldProperties("date_fin", FIELDP_IS_REQUIRED, true);

    $js = "function CheckDateFin(){
      var date_debut = document.ADForm.HTML_GEN_LSB_trim_depart.value;
      var date_fin = document.ADForm.HTML_GEN_date_date_fin.value;
      var date_fin_exo = document.ADForm.date_fin_exo.value;
      if (isAfter(date_debut,date_fin)){
        ADFormValid = false;
        alert('"._("La date de fin doit etre superieure ou égal à la de debut du trimestre")."');exit;
      }
      if(isAfter(date_fin,date_fin_exo)){
        ADFormValid = false;
        alert('"._("La date de fin doit etre inferieure ou égal à la date de fin de lexercice saisie")."'); exit;
      }
    }";
    $MyPage->addJS(JSP_FORM,"checkDate",$js);


    $MyPage->addFormButton(1, 1, "pdf_eta_budget", _("Rapport PDF"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("pdf_eta_budget", BUTP_PROCHAIN_ECRAN, "Rpb-3");
    $MyPage->setFormButtonProperties("pdf_eta_budget", BUTP_JS_EVENT, array("onClick"=>"CheckDateFin();"));
    $MyPage->addFormButton(1, 2, "excel_eta_budget", _("Export EXCEL"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("excel_eta_budget", BUTP_PROCHAIN_ECRAN, "Rpb-3");
    $MyPage->setFormButtonProperties("excel_eta_budget", BUTP_JS_EVENT, array("onClick"=>"CheckDateFin();"));
    $MyPage->addFormButton(1, 3, "csv_eta_budget", _("Export CSV"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("csv_eta_budget", BUTP_PROCHAIN_ECRAN, "Rpb-3");
    $MyPage->setFormButtonProperties("csv_eta_budget", BUTP_JS_EVENT, array("onClick"=>"CheckDateFin();"));
    $MyPage->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-15");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }else if ($contenu == 'his_rev' || (isset($SESSION_VARS['nom_rapport']) && $SESSION_VARS['nom_rapport'] == 'his_rev')){
    if (isset($contenu)) {
      $SESSION_VARS['nom_rapport'] = $contenu;
    }
    //Titre
    $MyPage = new HTML_GEN2(_("Personalisation du Rapport Historique de Révision Budgétaire"));

    //Formulaire Critere de recherche
    $MyPage->addField("type_budget", _("Type Budget"), TYPC_LSB);
    $MyPage->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_AUCUN,false);
    $MyPage->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_TOUS,true);
    $MyPage->setFieldProperties("type_budget", FIELDP_IS_REQUIRED, false);
    $MyPage->setFieldProperties("type_budget", FIELDP_ADD_CHOICES, $adsys["adsys_type_budget"]);
    //Reload : prend en consideration valeur déjä saisie
    if (isset($type_budget) && $type_budget > 0) {
      $MyPage->setFieldProperties("type_budget", FIELDP_DEFAULT, $type_budget);
    }

    $exercices = getAllExerciceBudget();
    $exo_encours = getExoEnCours();

    ////Reload : prend en consideration valeur déjà saisie
    if (isset($exo_budget) && $exo_budget > 0){
      $exo_encours = getExoDefini($exo_budget); //REL-104 : correction Pour le rapport “historique de révision budgétaire”,
      // champ ‘Exercice' est de 2020 alors que le budget revisé est celui de 2019
      $date_deb_exo = $exo_encours['date_deb_exo'];
      $date_deb_exo_explode = explode('-',$date_deb_exo);
      $annee_exo = $date_deb_exo_explode[0];

      $array_date_deb_trim = array(
          '1-01/01/'.$annee_exo =>_("Trimestre 1"),
          '2-01/04/'.$annee_exo=>_("Trimestre 2"),
          '3-01/07/'.$annee_exo =>_("Trimestre 3"),
          '4-01/10/'.$annee_exo=>_("Trimestre 4")
      );
    }
    $date_fin_exo_explode = explode('-',$exo_encours["date_fin_exo"]);
    $annee_fin_exo = $date_fin_exo_explode[0];
    $mois_fin_exo = $date_fin_exo_explode[1];
    $jour_fin_exo = $date_fin_exo_explode[2];
    $date_fin_exo = $jour_fin_exo.'/'.$mois_fin_exo.'/'.$annee_fin_exo;

    $MyPage->addHiddenType ("date_fin_exo", $date_fin_exo);

    $MyPage->addField("exo_budget", _("Exercice Budget"), TYPC_LSB);
    $MyPage->setFieldProperties("exo_budget", FIELDP_HAS_CHOICE_AUCUN,true);
    $MyPage->setFieldProperties("exo_budget", FIELDP_HAS_CHOICE_TOUS,false);
    $MyPage->setFieldProperties("exo_budget", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("exo_budget", FIELDP_ADD_CHOICES, $exercices);
    ////Reload : prend en consideration valeur déjà saisie
    if (isset($exo_budget) && $exo_budget > 0) {
      $MyPage->setFieldProperties("exo_budget", FIELDP_DEFAULT, $exo_budget);
    }
    else{
      $MyPage->setFieldProperties("exo_budget", FIELDP_DEFAULT, 0);//$exo_encours['id_exo_compta']
    }
    $MyPage->setFieldProperties("exo_budget", FIELDP_JS_EVENT, array("onChange"=>"assign('Rpb-2'); this.form.submit();"));

    $MyPage->addField("date_rapport", _("Date du Rapport"), TYPC_DTG);
    ////Reload : prend en consideration valeur déjà saisie
    if (isset($exo_budget) && $exo_budget > 0){
      $MyPage->setFieldProperties("date_rapport", FIELDP_DEFAULT, $date_fin_exo);
    }
    else{
      $MyPage->setFieldProperties("date_rapport", FIELDP_DEFAULT, date("d/m/Y"));
    }
    $MyPage->setFieldProperties("date_rapport", FIELDP_IS_REQUIRED, true);
    //REL-104 : JS controle sur date fin
    $jsCheckDateRapport = "function CheckDateRapport(){
      var values_trimestre = document.ADForm.HTML_GEN_LSB_trimestre.value;
      var value_trimestre = values_trimestre.split('-');
      var date_trimestre = value_trimestre[1];
      var date_rapport = document.ADForm.HTML_GEN_date_date_rapport.value;
      var date_fin_exo = document.ADForm.date_fin_exo.value;
      if (values_trimestre != 0 && date_trimestre !== null && date_trimestre !== ''){
        if (isAfter(date_trimestre,date_rapport)){
          ADFormValid = false;
          alert('"._("La date du rapport doit etre superieure à la de début du trimestre")."');exit;
        }
      }
      if(isAfter(date_rapport,date_fin_exo)){
        ADFormValid = false;
        alert('"._("La date du rapport doit etre inferieure à la date fin de lexercice saisie")."');exit;
      }
    }";
    $MyPage->addJS(JSP_FORM,"CheckDateRapport",$jsCheckDateRapport);

    $MyPage->addField("periode_budget", _("Periode"), TYPC_LSB);
    $MyPage->setFieldProperties("periode_budget", FIELDP_HAS_CHOICE_AUCUN,true);
    $MyPage->setFieldProperties("periode_budget", FIELDP_HAS_CHOICE_TOUS,false);
    $MyPage->setFieldProperties("periode_budget", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("periode_budget", FIELDP_ADD_CHOICES, $adsys["adsys_type_periode_budget"]);
    $MyPage->setFieldProperties("periode_budget", FIELDP_JS_EVENT, array("onChange"=>"activerTrimestre(this.value);"));
    if (isset($periode_budget) && $periode_budget > 0) {
      $MyPage->setFieldProperties("periode_budget", FIELDP_DEFAULT, $periode_budget);
      //Page reloaded - Fonction JS pour activer/deactiver le champ trimestre dependant la valeur du champ periode
      $JScode_activerTrim = "if (document.ADForm.HTML_GEN_LSB_periode_budget.value >= 2){\n";
      $JScode_activerTrim .= "\t document.ADForm.HTML_GEN_LSB_trimestre.disabled = false; \n";
      $JScode_activerTrim .= "}\n";
      $JScode_activerTrim .= "else {\n";
      $JScode_activerTrim .= "\t document.ADForm.HTML_GEN_LSB_trimestre.disabled = true; \n";
      $JScode_activerTrim .= "}\n";
      $MyPage->addJS(JSP_FORM,"activerTrimPageReload",$JScode_activerTrim);
    }

    $trimestres[1] = _('Trimestre 1');
    $trimestres[2] = _('Trimestre 2');
    $trimestres[3] = _('Trimestre 3');
    $trimestres[4] = _('Trimestre 4');
    $MyPage->addField("trimestre", _("Trimestre"), TYPC_LSB);
    $MyPage->setFieldProperties("trimestre", FIELDP_HAS_CHOICE_AUCUN,false);
    $MyPage->setFieldProperties("trimestre", FIELDP_HAS_CHOICE_TOUS,true);
    $MyPage->setFieldProperties("trimestre", FIELDP_ADD_CHOICES, $array_date_deb_trim);
    ////Reload : prend en consideration valeur déjà saisie
    if (isset($trimestre) && $trimestre > 0) {
      $MyPage->setFieldProperties("trimestre", FIELDP_DEFAULT, $trimestre);
    }

    //Fonction JS pour activer/deactiver le champ trimestre dependant la valeur du champ periode
    $JScode_1 = "";
    $JScode_1 .= "\nfunction activerTrimestre(val){\n";
    $JScode_1 .= "\t\t if (val >= 2) {\n";
    $JScode_1 .= "\t\t\t document.ADForm.HTML_GEN_LSB_trimestre.disabled = false; \n";
    $JScode_1 .= "\t\t}\n";
    $JScode_1 .= "\t\t else {\n";
    $JScode_1 .= "\t\t\t document.ADForm.HTML_GEN_LSB_trimestre.disabled = true; \n";
    $JScode_1 .= "\t\t}\n";
    $JScode_1 .= "";
    $JScode_1 .= "}\n";
    $MyPage->addJS(JSP_FORM,"activerTrim",$JScode_1);

    //Boutons
    $MyPage->addFormButton(1, 1, "pdf_rev_budget", _("Rapport PDF"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("pdf_rev_budget", BUTP_PROCHAIN_ECRAN, "Rpb-3");
    $MyPage->setFormButtonProperties("pdf_rev_budget", BUTP_JS_EVENT, array("onClick"=>"CheckDateRapport();"));
    $MyPage->addFormButton(1, 2, "excel_rev_budget", _("Export EXCEL"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("excel_rev_budget", BUTP_PROCHAIN_ECRAN, "Rpb-3");
    $MyPage->setFormButtonProperties("excel_rev_budget", BUTP_JS_EVENT, array("onClick"=>"CheckDateRapport();"));
    $MyPage->addFormButton(1, 3, "csv_rev_budget", _("Export CSV"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("csv_rev_budget", BUTP_PROCHAIN_ECRAN, "Rpb-3");
    $MyPage->setFormButtonProperties("csv_rev_budget", BUTP_JS_EVENT, array("onClick"=>"CheckDateRapport();"));
    $MyPage->addFormButton(1, 4, "prec", _("Precedent"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, "Rpb-1");
    $MyPage->setFormButtonProperties("prec", BUTP_CHECK_FORM, false);
    $MyPage->addFormButton(1, 5, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-15");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();

  }
  else if   ($contenu == 'rap_bud' || (isset($SESSION_VARS['nom_rapport']) && $SESSION_VARS['nom_rapport'] == 'rap_bud')){

    $MyPage = new HTML_GEN2(_("Personalisation du Rapport Budget"));
    if (isset($contenu)){
      $SESSION_VARS['nom_rapport'] = $contenu;
    }
    $adsys_type_budget = $adsys["adsys_type_budget"];

    //Formulaire Critere de recherche
    $MyPage->addField("type_budget", _("Type Budget"), TYPC_LSB);
    $MyPage->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_AUCUN,true);
    $MyPage->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_TOUS,false);
    $MyPage->setFieldProperties("type_budget", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("type_budget", FIELDP_ADD_CHOICES, $adsys["adsys_type_budget"]);


    //$exercices = getAllExerciceBudget();
    //$exo_encours = getExoEnCours();
    //REL-104 : correction Après la cloture de l’exercice, il n’est pas possible d’acceder les rapports bubget de l’exercice
    // précédent car le filtre ‘Exercice Budget” affiche uniquement le budget de l’exercice en cours”
    $exo_encours = getExoEnCoursAll('etat_exo IN (1,3) AND id_exo_compta IN (SELECT DISTINCT exo_budget FROM ad_budget WHERE etat_budget >= 3)');
    $exo_encours_all = array();
    if ($exo_encours != null){
      foreach($exo_encours as $key => $value){
        $exo_encours_all[$key] = $value["debut_annee"]." - ".$value["debut_annee"];
      }
    }
    $MyPage->addField("exo_budget", _("Exercice Budget"), TYPC_LSB);
    $MyPage->setFieldProperties("exo_budget", FIELDP_HAS_CHOICE_AUCUN,true);
    $MyPage->setFieldProperties("exo_budget", FIELDP_HAS_CHOICE_TOUS,false);
    $MyPage->setFieldProperties("exo_budget", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("exo_budget", FIELDP_ADD_CHOICES, $exo_encours_all);


    //Boutons
    $MyPage->addFormButton(1, 1, "pdf_bud_budget", _("Rapport PDF"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("pdf_bud_budget", BUTP_PROCHAIN_ECRAN, "Rpb-3");
    //$MyPage->addFormButton(1, 2, "excel_bud_budget", _("Export EXCEL"), TYPB_SUBMIT);
    //$MyPage->setFormButtonProperties("excel_bud_budget", BUTP_PROCHAIN_ECRAN, "Rpb-3");
    //$MyPage->addFormButton(1, 3, "csv_bud_budget", _("Export CSV"), TYPB_SUBMIT);
    //$MyPage->setFormButtonProperties("csv_bud_budget", BUTP_PROCHAIN_ECRAN, "Rpb-3");
    $MyPage->addFormButton(1, 2, "prec", _("Precedent"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, "Rpb-1");
    $MyPage->setFormButtonProperties("prec", BUTP_CHECK_FORM, false);
    $MyPage->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-15");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }
}

else if ($global_nom_ecran == "Rpb-3") {

  if ($SESSION_VARS['nom_rapport']=='eta_exe'){

    CreateTempTableEtat();

    $date_explode = explode('/',$date_fin);
    $date_fin_now = $date_explode[2].'-'.$date_explode[1].'-'.$date_explode[0];
    $criteres = array(
      _("Type de budget") => adb_gettext($adsys['adsys_type_budget'][$type_budget]),
      _("Exercice") => $date_explode[2],
      _("Date debut") => $trim_depart,
      _("Date fin") =>$date_fin
    );

    if (isset($pdf_eta_budget) && $pdf_eta_budget == _('Rapport PDF')){ //Generation PDF
      $DATA_etat_exe = xml_etat_execution_budgetaire($criteres,$type_budget, $exo_budget,$trim_depart,$date_fin_now,$global_monnaie);

      if ($DATA_etat_exe != null){ //REL-104 - Correction Pour le rapport d’execution budgetaire et rapport budget, si aucun budget
        // n’a été mis en place, il faudrait donner l’arte disant qu’aucun budget n’est disponible au lieu d’afficher une page
        // blanche ou rapport avec valeurs zero
        $fichier = xml_2_xslfo_2_pdf($DATA_etat_exe, 'budget_etatbudgetaire.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Gen-15", $fichier);
      }
      else{
        $html_err = new HTML_erreur();

        $err_msg = "Aucune donnée correspond au criteres de recherche!!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Rpb-2');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

    }
    elseif ((isset($csv_eta_budget) && $csv_eta_budget == _('Export CSV'))||(isset($excel_eta_budget) && $excel_eta_budget == _('Export EXCEL'))){ //Generation CSV
      $DATA_etat_exe = xml_etat_execution_budgetaire($criteres,$type_budget, $exo_budget,$trim_depart,$date_fin_now,$global_monnaie,true);

      if ($DATA_etat_exe != null){
        $fichier = xml_2_csv($DATA_etat_exe, 'budget_etatbudgetaire.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        if (isset($excel_eta_budget) && $excel_eta_budget == _('Export EXCEL')){
          echo getShowEXCELHTML("Gen-15", $fichier);
        }
        else{
          echo getShowCSVHTML("Gen-15", $fichier);
        }
      }
      else{
        $html_err = new HTML_erreur();

        $err_msg = "Aucune donnée correspond au criteres de recherche!!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Rpb-2');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
    DropTempTableEtat();
  }
  elseif ($SESSION_VARS['nom_rapport']=='his_rev'){

    if (!isset($type_budget)){
      $type_budget = 0;
    }
    if (isset($trimestre)){
      $trimestre = explode('-',$trimestre);
      $trimestre = $trimestre[0];
    }

    if (isset($pdf_rev_budget) && $pdf_rev_budget == _('Rapport PDF')){ //Generation PDF
      $xml = xml_revision_historique_budget($type_budget, $exo_budget, $date_rapport, $trimestre);

      if ($xml != null){
        $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'budget_revisionhistorique.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Rpb-1", $fichier_pdf);
      }
      else{
        $html_err = new HTML_erreur();

        $err_msg = "Aucune donnée correspond au criteres de recherche!!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Rpb-2');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
    elseif ((isset($csv_rev_budget) && $csv_rev_budget == _('Export CSV'))||(isset($excel_rev_budget) && $excel_rev_budget == _('Export EXCEL'))){ //Generation CSV/EXCEL
      //Génération du CSV grâce à XALAN
      $xml = xml_revision_historique_budget($type_budget, $exo_budget, $date_rapport, $trimestre, true);

      if ($xml != null){
        $csv_file = xml_2_csv($xml, 'budget_revisionhistorique.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        if (isset($excel_rev_budget) && $excel_rev_budget == _('Export EXCEL')){
          echo getShowEXCELHTML("Rpb-1", $csv_file);
        }
        else{
          echo getShowCSVHTML("Rpb-1", $csv_file);
        }
      }
      else{
        $html_err = new HTML_erreur();

        $err_msg = "Aucune donnée correspond au criteres de recherche!!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Rpb-2');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }

  }
  elseif ($SESSION_VARS['nom_rapport']=='rap_bud'){

    $exerciceChoisi = getExoDefini($exo_budget);
    $annee_exo_choisi = explode("-",$exerciceChoisi['date_deb_exo']);
    $criteres = array(
      _("Type de budget") => adb_gettext($adsys['adsys_type_budget'][$type_budget]),
      _("Exercice") => $annee_exo_choisi[0]
    );


    if (isset($pdf_bud_budget) && $pdf_bud_budget == _('Rapport PDF')){ //Generation PDF
      $DATA_etat_exe = xml_rapport_budget($criteres,$type_budget, $exo_budget,$exerciceChoisi['date_deb_exo'],$exerciceChoisi['date_fin_exo'],$global_monnaie);

      if ($DATA_etat_exe != null){ //REL-104 - Correction Pour le rapport d’execution budgetaire et rapport budget, si aucun budget
        // n’a été mis en place, il faudrait donner l’arte disant qu’aucun budget n’est disponible au lieu d’afficher une page
        // blanche ou rapport avec valeurs zero
        $fichier = xml_2_xslfo_2_pdf($DATA_etat_exe, 'budget_rapportbudget.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Gen-15", $fichier);
      }
      else{
        $html_err = new HTML_erreur();

        $err_msg = "Aucune donnée correspond au criteres de recherche!!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Rpb-2');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

    }
    elseif ((isset($csv_bud_budget) && $csv_bud_budget == _('Export CSV'))||(isset($excel_bud_budget) && $excel_bud_budget == _('Export EXCEL'))){ //Generation CSV/EXCEL
      $DATA_etat_exe = xml_rapport_budget($criteres,$type_budget, $exo_budget,$exerciceChoisi['date_deb_exo'],$exerciceChoisi['date_fin_exo'],$global_monnaie,true);

      if ($DATA_etat_exe != null){
        $fichier = xml_2_csv($DATA_etat_exe, 'budget_rapportbudget.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        if (isset($excel_bud_budget) && $excel_bud_budget == _('Export EXCEL')){
          echo getShowEXCELHTML("Gen-15", $fichier);
        }
        else{
          echo getShowCSVHTML("Gen-15", $fichier);
        }
      }
      else{
        $html_err = new HTML_erreur();

        $err_msg = "Aucune donnée correspond au criteres de recherche!!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Rpb-2');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }


  }
}
?>