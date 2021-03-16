<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 9/27/2017
 * Time: 2:27 PM
 */
require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/budget.php');
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/html/FILL_HTML_GEN2.php';

/*{{{ Meb-1 : Choix du type budget */
if ($global_nom_ecran == "Meb-1") {
  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Saisie du type de budget"));

  $exo_encours = getExoEnCoursAll();
  $exo_encours_all = array();
  if ($exo_encours != null){
    foreach($exo_encours as $key => $value){
      $exo_encours_all[$key] = $value["debut_annee"]." - ".$value["debut_annee"];
    }
  }
  if (isset($id_exo) && $id_exo != null ){
    $liste_type_budget = getDataBudget($id_exo," = 6");
  }
  else{
    $liste_type_budget = null;
  }

  $myForm->addField("id_exo",_("Exercice(s) en cours"), TYPC_LSB);
  $myForm->setFieldProperties('id_exo', FIELDP_ADD_CHOICES, $exo_encours_all);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("id_exo", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("id_exo", FIELDP_JS_EVENT, array("onChange"=>"assign('Meb-1'); this.form.submit();"));
  if (isset($id_exo)){
    $myForm->setFieldProperties("id_exo", FIELDP_DEFAULT, $id_exo);
  }

  /*$adsys_type_budget = $adsys["adsys_type_budget"];

  if (isset($id_exo) && $liste_type_budget != null){
    //filtrer type budget
    foreach($liste_type_budget as $exo => $value_exo){
      if ($exo == $id_exo){
        $adsys_type_budget = $value_exo;
      }
      else{
        $adsys_type_budget = $adsys["adsys_type_budget"];
      }
    }
  }
  else{
    $adsys_type_budget = null;
  }*/

  $myForm->addField("type_budget", _("Type de budget"), TYPC_LSB);
  $myForm->setFieldProperties('type_budget', FIELDP_ADD_CHOICES, $liste_type_budget);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("type_budget", FIELDP_IS_REQUIRED, true);

  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Meb-2');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*{{{ Meb-2 : Saisie Données */
else if ($global_nom_ecran == "Meb-2") {

    $get_ref_budget = getRefbudgetFromTypeExoBudget($id_exo,$type_budget);
  $ref_budget = $get_ref_budget['ref_budget'];
  $SESSION_VARS['exo_choisi'] = $id_exo;
  $SESSION_VARS['ref_budget_choisi'] = $ref_budget;

  //Recupere la table de correspondance qui est associé au type de budget en parametre
  $tabCorrespondance = getTabCorrespondance($ref_budget);

  //Si != null, c'est que on a une table de correspondance pour ce type de budget
  if ($tabCorrespondance != null){
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Mise en Place du Budget Annuel : ".$adsys["adsys_type_budget"][$type_budget]));

    $myTable =& $myForm->addHTMLTable("plan_comptable", 10, TABLE_STYLE_ALTERN);
    //$myTable->add_cell(new TABLE_cell(_("Ligne Correspondance"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Poste"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Description"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Compartiment"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Comptes Comptable"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Bloque Compte si dépassement Budgétaire?"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Montant Budget Trimestre"), 4, 1));
    $myTable->add_cell(new TABLE_cell(_("Montant Budget"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("1"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("2"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("3"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("4"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Annuel"), 1, 1));

    //Recupere le nombre des lignes dans l'array correspondance
    $sizeCorrespondance = sizeof($tabCorrespondance);
    $poste = -1;

    //On loop dans l'array correspondance pour creer le tableau pour la mise en place budget
    for ($i = 0; $i < $sizeCorrespondance; $i++) {
      //$myTable->add_cell(new TABLE_cell($tabCorrespondance[$i]['id'], 1, 1));
      if ($tabCorrespondance[$i]['poste_principal'] != null){
        $poste = $tabCorrespondance[$i]['poste_principal'];
      }
      if ($tabCorrespondance[$i]['poste_niveau_1'] != null){
        $poste .= ".".$tabCorrespondance[$i]['poste_niveau_1'];
      }
      if ($tabCorrespondance[$i]['poste_niveau_2'] != null){
        $poste .= ".".$tabCorrespondance[$i]['poste_niveau_2'];
      }
      if ($tabCorrespondance[$i]['poste_niveau_3'] != null){
        $poste .= ".".$tabCorrespondance[$i]['poste_niveau_3'];
      }
      $myTable->add_cell(new TABLE_cell($poste, 1, 1));
      $myTable->add_cell(new TABLE_cell($tabCorrespondance[$i]['description'], 1, 1));
      $myTable->add_cell(new TABLE_cell($adsys["adsys_compartiment_comptable"][$tabCorrespondance[$i]['compartiment']], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabCorrespondance[$i]['cpte_correspondance'], 1, 1));
      $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  unchecked='true' />", 1, 1));
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim1_".($i+1), "", "setMontantAnnuel(".($i+1)."); value = formateMontant(value);", "", false, "size='12'"));
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim2_".($i+1), "", "setMontantAnnuel(".($i+1)."); value = formateMontant(value);", "", false, "size='12'"));
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim3_".($i+1), "", "setMontantAnnuel(".($i+1)."); value = formateMontant(value);", "", false, "size='12'"));
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim4_".($i+1), "", "setMontantAnnuel(".($i+1)."); value = formateMontant(value);", "", false, "size='12'"));
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_annuel_".($i+1), "", "", "", false, "readonly='true'"));

      //creation session pour garder certains informations des lignes budgetaires
      $SESSION_VARS['ligne_budgetaire'][$i+1]['ligne_budgetaire']=($i+1);
      $SESSION_VARS['ligne_budgetaire'][$i+1]['type_budget']=$type_budget;
      $SESSION_VARS['ligne_budgetaire'][$i+1]['id_correspondance']=$tabCorrespondance[$i]['id'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['poste']=$poste;
      $SESSION_VARS['ligne_budgetaire'][$i+1]['description']=$tabCorrespondance[$i]['description'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['compartiment']=$tabCorrespondance[$i]['compartiment'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['cpte_correspondance']=$tabCorrespondance[$i]['cpte_correspondance'];
    }

    //Fonction javascript pour le calcule automatique du montant budget annuel pour chaque ligne dans le tableau
    $JScode_1 = "";
    $JScode_1 .= "\nfunction setMontantAnnuel(id)\n";
    $JScode_1 .= "{\n";
    $JScode_1 .= "\t var total = 0; var trim1 = 0; var trim2 = 0; var trim3 = 0; var trim4 = 0; var total_val = 0;\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_annuel_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t total = 0;\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_trim1_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t trim1 = recupMontant(document.getElementsByName('mnt_trim1_'+id).item(0).value);\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_trim2_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t trim2 = recupMontant(document.getElementsByName('mnt_trim2_'+id).item(0).value);\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_trim3_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t trim3 = recupMontant(document.getElementsByName('mnt_trim3_'+id).item(0).value);\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_trim4_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t trim4 = recupMontant(document.getElementsByName('mnt_trim4_'+id).item(0).value);\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t total_val = total + trim1 + trim2 + trim3 + trim4;\n";
    $JScode_1 .= "\t document.getElementsByName('mnt_annuel_'+id).item(0).value = formateMontant(total_val);\n";
    $JScode_1 .= "}\n";
    $myForm->addJS(JSP_FORM,"computMntAnnuel",$JScode_1);

    //Fonction javascript pour verfier si tout les budgets sont renseignés
    $JScode_2 = "";
    $JScode_2 .= "\nfunction checkMontantBudget(numLigne)\n";
    $JScode_2 .= "{\n";
    $JScode_2 .= "\t var valide='t';\n";
    $JScode_2 .= "\t for(var i = 1; i <= numLigne; i++) {\n";
    $JScode_2 .= "\t\t for(var j = 1; j <= 4; j++) {\n";
    $JScode_2 .= "\t\t\t if (document.getElementsByName('mnt_trim'+j+'_'+i).item(0).value == '') {\n";
    $JScode_2 .= "\t\t\t\t isSubmit=false;\n";
    $JScode_2 .= "\t\t\t\t ADFormValid=false;\n";
    $JScode_2 .= "\t\t\t\t valide='f';\n";
    $JScode_2 .= "\t\t\t }\n";
    $JScode_2 .= "\t\t }\n";
    $JScode_2 .= "\t }\n";
    $JScode_2 .= "\t if (valide=='f') {\n";
    $JScode_2 .= "\t\t alert('Veuillez renseigner tout les champs budgets avant de valider!!');\n";
    $JScode_2 .= "\t }\n";
    $JScode_2 .= "}\n";
    $myForm->addJS(JSP_FORM,"chkMntBudget",$JScode_2);

    //sauvegarder le nom de l'écran et le nom du bouton  pour le prochain ecran confirmation 'Cmb-1'
    $myForm->addHiddenType("ecran_prec", 'Meb-2');
    $myForm->addHiddenType("bouton_enreg", 'enreg');


    //Les boutons de navigation/operation
    $myForm->addFormButton(1, 1, "enreg", _("Enregistrer"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "prec", _("Precedent"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 4, "annul", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("enreg", BUTP_PROCHAIN_ECRAN, 'Cmb-1');
    $myForm->setFormButtonProperties("enreg", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("enreg", BUTP_JS_EVENT, array("onClick"=>"checkMontantBudget($sizeCorrespondance);")); //onClick"=>"assign('Pnr-2'); this.form.submit(); BUTP_CHECK_FORM
    $myForm->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, 'Meb-1');
    $myForm->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, 'Gen-15');

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
  else{
    $erreur = new HTML_erreur(_("Mise en Place du Budget Annuel : ".$adsys["adsys_type_budget"][$type_budget]));
    $erreur->setMessage(_("Aucun table de correspondance associe a ce type de budget!!"));
    $erreur->addButton(BUTTON_OK,"Meb-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }
}
/*{{{ Mnl-1 : Choix du type budget */
else if ($global_nom_ecran == "Mnl-1"){
  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Saisie du type de budget"));

  $exo_encours = getExoEnCoursAll();
  $exo_encours_all = array();
  if ($exo_encours != null){
    foreach($exo_encours as $key => $value){
      $exo_encours_all[$key] = $value["debut_annee"]." - ".$value["debut_annee"];
    }
  }
  //$liste_type_budget = getAllExoOuvertWithBudgetAvailable(" > 2", true);
  //$liste_type_budget = getDataBudget($id_exo," > 2");

  $myForm->addField("id_exo",_("Exercice(s) en cours"), TYPC_LSB);
  $myForm->setFieldProperties('id_exo', FIELDP_ADD_CHOICES, $exo_encours_all);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("id_exo", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("id_exo", FIELDP_JS_EVENT, array("onChange"=>"assign('Mnl-1'); this.form.submit();"));
  if (isset($id_exo)){
    $myForm->setFieldProperties("id_exo", FIELDP_DEFAULT, $id_exo);
  }

  if (isset($id_exo) && $id_exo != null ){
    $liste_type_budget = getDataBudget($id_exo," > 2 and etat_budget not in (6)");
  }
  else{
    $liste_type_budget = null;
  }
  $myForm->addField("type_budget", _("Type de budget"), TYPC_LSB);
  $myForm->setFieldProperties('type_budget', FIELDP_ADD_CHOICES, $liste_type_budget);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("type_budget", FIELDP_IS_REQUIRED, true);

  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Mnl-2');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*{{{ Mnl-2 : Choix du nouvelle ligne budgetaire */
else if ($global_nom_ecran == "Mnl-2"){

  //Recupere les donnees du tableau budget qui est associé au type de budget et id exercice en parametre
  $exo_encours= getExoEnCours();
  $typeBudget = $type_budget;
  $tabBudget = getRefbudgetFromTypeExoBudget($id_exo,$type_budget);
  if (isset($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['type_budget'])){
    $typeBudget = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['type_budget'];
  }

  $tabCorrespondance = getNouvelleLigneBudgetaire($typeBudget,$tabBudget['ref_budget']);

  //Si != null, c'est que on a des donnees budget pour ce type de budget
  if ($tabCorrespondance != null){
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Mise en Place Nouvelle Ligne Budgetaire : ".$adsys["adsys_type_budget"][$typeBudget]));

    $myTable =& $myForm->addHTMLTable("plan_comptable", 6, TABLE_STYLE_ALTERN);
    $myTable->add_cell(new TABLE_cell(_("Nouvelle Ligne"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Poste Centralisateur"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Description"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Compartiment"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Comptes Comptable"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Action"), 1, 1));

    //Recupere le nombre des lignes dans l'array budget
    $sizeBudget = sizeof($tabCorrespondance);
    $poste = -1;

    //On loop dans l'array budget pour creer le tableau pour la mise en place du ligne budget
    for ($i = 0; $i < $sizeBudget; $i++) {
      if ($tabCorrespondance[$i]['poste_principal'] != null){
        $poste = $tabCorrespondance[$i]['poste_principal'];
      }
      if ($tabCorrespondance[$i]['poste_niveau_1'] != null){
        $poste .= ".".$tabCorrespondance[$i]['poste_niveau_1'];
      }
      if ($tabCorrespondance[$i]['poste_niveau_2'] != null){
        $poste .= ".".$tabCorrespondance[$i]['poste_niveau_2'];
      }
      if ($tabCorrespondance[$i]['poste_niveau_3'] != null){
        $poste .= ".".$tabCorrespondance[$i]['poste_niveau_3'];
      }
      $myTable->add_cell(new TABLE_cell($poste, 1, 1));
      // Poste parent pour la nouvelle ligne budgetaire
      if ($tabCorrespondance[$i]['poste_niveau_3'] != null && $tabCorrespondance[$i]['poste_niveau_3'] != ''){
        $poste_parent = $tabCorrespondance[$i]['poste_principal'].".".$tabCorrespondance[$i]['poste_niveau_1'].".".$tabCorrespondance[$i]['poste_niveau_2'];
      }
      if ($tabCorrespondance[$i]['poste_niveau_3'] == null && $tabCorrespondance[$i]['poste_niveau_2'] != null && $tabCorrespondance[$i]['poste_niveau_2'] != ''){
        $poste_parent = $tabCorrespondance[$i]['poste_principal'].".".$tabCorrespondance[$i]['poste_niveau_1'];
      }
      if ($tabCorrespondance[$i]['poste_niveau_3'] == null && $tabCorrespondance[$i]['poste_niveau_2'] == null && $tabCorrespondance[$i]['poste_niveau_1'] != null && $tabCorrespondance[$i]['poste_niveau_1'] != ''){
        $poste_parent = $tabCorrespondance[$i]['poste_principal'];
      }
      $detailsPosteParent = getParentNouvelleLigneBudget($tabCorrespondance[$i]['poste_principal'],$tabCorrespondance[$i]['poste_niveau_1'],$tabCorrespondance[$i]['poste_niveau_2'],$tabCorrespondance[$i]['poste_niveau_3'],$tabBudget['ref_budget'],$id_exo); //Recuperation details poste parent si parent existait dans la table ad_ligne_budgetaire auparavant comme un poste de dernier niveau
      $myTable->add_cell(new TABLE_cell($poste_parent, 1, 1)); //$detailsPosteParent['poste_budget']
      $myTable->add_cell(new TABLE_cell($tabCorrespondance[$i]['description'], 1, 1));
      $myTable->add_cell(new TABLE_cell($adsys["adsys_compartiment_comptable"][$tabCorrespondance[$i]['compartiment']], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabCorrespondance[$i]['cpte_correspondance'], 1, 1));
      $myTable->add_cell(new TABLE_cell_link("<b>"._("Mettre en place")."</b>","$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Mnl-3&ligne_budgetaire=".($i+1)."&id_correspondance=".$tabCorrespondance[$i]['id']."&ecran_prec=Mnl-2"), 1, 1);

      //creation session pour garder certains informations des lignes budgetaires
      $SESSION_VARS['ligne_budgetaire'][$i+1]['ligne_budgetaire']=($i+1);
      $ref_budget = $tabCorrespondance[$i]['ref_budget'];
      if ($detailsPosteParent['ref_budget'] != null){
        $ref_budget = $detailsPosteParent['ref_budget'];
      }
      $SESSION_VARS['ligne_budgetaire'][$i+1]['ref_budget']=$ref_budget;
      $SESSION_VARS['ligne_budgetaire'][$i+1]['type_budget']=$typeBudget;
      $SESSION_VARS['ligne_budgetaire'][$i+1]['id_correspondance']=$tabCorrespondance[$i]['id'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['id_ligne_parent']=$detailsPosteParent['id_ligne'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['parent_mnt_trim1']=$detailsPosteParent['mnt_trim1'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['parent_mnt_trim2']=$detailsPosteParent['mnt_trim2'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['parent_mnt_trim3']=$detailsPosteParent['mnt_trim3'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['parent_mnt_trim4']=$detailsPosteParent['mnt_trim4'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['poste']=$poste;
      $SESSION_VARS['ligne_budgetaire'][$i+1]['description']=$tabCorrespondance[$i]['description'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['compartiment']=$tabCorrespondance[$i]['compartiment'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['cpte_correspondance']=$tabCorrespondance[$i]['cpte_correspondance'];
    }

    //sauvegarder le nom de l'écran et le nom du bouton  pour le prochain ecran confirmation 'Cmb-1'
    $myForm->addHiddenType("ecran_prec", 'Mnl-3');
    $myForm->addHiddenType("bouton_enreg", 'enreg');


    //Les boutons de navigation/operation
    $myForm->addFormButton(1, 1, "prec", _("Precedent"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, 'Mnl-1');
    $myForm->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, 'Gen-15');

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
  else{
    $erreur = new HTML_erreur(_("Mise en Place Nouvelle Ligne Budgetaire : ".$adsys["adsys_type_budget"][$type_budget]));
    $erreur->setMessage(_("Aucune nouvelle ligne correspondance associe a ce type de budget!!"));
    $erreur->addButton(BUTTON_OK,"Mnl-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }
}
/*{{{ Mnl-3 : Saisie Montant Budget nouvelle ligne budgetaire */
else if ($global_nom_ecran == "Mnl-3"){
  $mnt_budget_trim1 = "";
  $mnt_budget_trim2 = "";
  $mnt_budget_trim3 = "";
  $mnt_budget_trim4 = "";
  $mnt_annuel = 0;

  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Ajout Nouvelle Ligne Budgetaire : ".$adsys["adsys_type_budget"][$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['type_budget']]));

  //Tableau et formulaire pour la nouvelle ligne budgetaire
  $myForm->addField("id_correspondance",_("Numero Ligne Correspondance"), TYPC_TXT);
  $myForm->setFieldProperties("id_correspondance",FIELDP_DEFAULT,$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['id_correspondance']);
  $myForm->setFieldProperties("id_correspondance", FIELDP_IS_LABEL, true);

  $poste = explode('.',$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['poste']);
  $myForm->addField("poste_prinicipal",_("Poste Principal"), TYPC_TXT);
  $myForm->setFieldProperties("poste_prinicipal",FIELDP_DEFAULT,$poste[0]);
  $myForm->setFieldProperties("poste_prinicipal", FIELDP_IS_LABEL, true);
  $myForm->addField("extension_poste",_("Extension du Poste"), TYPC_TXT);
  $ext_poste = "";
  if ($poste[1]!=null){
    $ext_poste .= $poste[1];
  }
  if ($poste[2]!=null){
    $ext_poste .= ".".$poste[2];
  }
  if ($poste[3]!=null){
    $ext_poste .= ".".$poste[3];
  }
  $myForm->setFieldProperties("extension_poste",FIELDP_DEFAULT,$ext_poste);
  $myForm->setFieldProperties("extension_poste", FIELDP_IS_LABEL, true);

  $myForm->addField("poste_centralisateur",_("Poste Centralisateur"), TYPC_TXT);
  $poste_centra = "";
  if ($poste[3]!=null && $poste[3]!=''){
    $poste_centra = $poste[0].".".$poste[1].".".$poste[2];
  }
  if ($poste[3]==null && $poste[2]!=null && $poste[2]!=''){
    $poste_centra = $poste[0].".".$poste[1];
  }
  if ($poste[3]==null && $poste[2]==null && $poste[1]!=null && $poste[1]!=''){
    $poste_centra = $poste[0];
  }
  $myForm->setFieldProperties("poste_centralisateur",FIELDP_DEFAULT,$poste_centra);
  $myForm->setFieldProperties("poste_centralisateur", FIELDP_IS_LABEL, true);

  $myForm->addField("description",_("Description de la ligne budgetaire"), TYPC_TXT);
  $myForm->setFieldProperties("description",FIELDP_DEFAULT,$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['description']);
  $myForm->setFieldProperties("description", FIELDP_IS_LABEL, true);

  $myForm->addField("compartiment",_("Compartiment"), TYPC_TXT);
  $myForm->setFieldProperties("compartiment",FIELDP_DEFAULT,$adsys["adsys_compartiment_comptable"][$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['compartiment']]);
  $myForm->setFieldProperties("compartiment", FIELDP_IS_LABEL, true);

  $myForm->addField("cpte_correspondance",_("Compte Correspondance"), TYPC_TXT);
  $myForm->setFieldProperties("cpte_correspondance",FIELDP_DEFAULT,$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['cpte_correspondance']);
  $myForm->setFieldProperties("cpte_correspondance", FIELDP_IS_LABEL, true);

  /*$myForm->addField("dernier_niveau",_("Dernier Niveau?"), TYPC_BOL);
  $dernier_niv = false;
  $myForm->setFieldProperties("dernier_niveau",FIELDP_DEFAULT,$dernier_niv);*/

  $myForm->addField("etat_bloque",_("Bloque Compte si on a une depassement budgetaire?"), TYPC_BOL);
  $etat_bloc = false;
  $myForm->setFieldProperties("etat_bloque",FIELDP_DEFAULT,$etat_bloc);

  $myTable =& $myForm->addHTMLTable("plan_comptable", 9, TABLE_STYLE_ALTERN);
  $myTable->add_cell(new TABLE_cell(_("Trimestre 1"), 2, 1));
  $myTable->add_cell(new TABLE_cell(_("Trimestre 2"), 2, 1));
  $myTable->add_cell(new TABLE_cell(_("Trimestre 3"), 2, 1));
  $myTable->add_cell(new TABLE_cell(_("Trimestre 4"), 2, 1));
  $myTable->add_cell(new TABLE_cell(_("Annuel"), 1, 2));
  $myTable->add_cell(new TABLE_cell(_("Budget Disponible"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Nouveau Budget"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Budget Disponible"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Nouveau Budget"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Budget Disponible"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Nouveau Budget"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Budget Disponible"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Nouveau Budget"), 1, 1));

  //Trimestre 1
  $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "parent_mnt_trim1", number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['parent_mnt_trim1'],0,'.',' '), "value = formateMontant(value);", "", false, "size='12'; readonly='true'"));
  $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim1", number_format($mnt_budget_trim1,0,'.',' '), "chkMnt_setMntAnnuel(); value = formateMontant(value);", "", false, "size='12'"));
  $mnt_annuel += $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['parent_mnt_trim1'];

  //Trimestre 2
  $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "parent_mnt_trim2", number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['parent_mnt_trim2'],0,'.',' '), "value = formateMontant(value);", "", false, "size='12'; readonly='true'"));
  $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim2", number_format($mnt_budget_trim2,0,'.',' '), "chkMnt_setMntAnnuel(); value = formateMontant(value);", "", false, "size='12'"));
  $mnt_annuel += $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['parent_mnt_trim2'];

  //Trimestre 3
  $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "parent_mnt_trim3", number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['parent_mnt_trim3'],0,'.',' '), "value = formateMontant(value);", "", false, "size='12'; readonly='true'"));
  $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim3", number_format($mnt_budget_trim3,0,'.',' '), "chkMnt_setMntAnnuel(); value = formateMontant(value);", "", false, "size='12'"));
  $mnt_annuel += $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['parent_mnt_trim3'];

  //Trimestre 4
  $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "parent_mnt_trim4", number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['parent_mnt_trim4'],0,'.',' '), "value = formateMontant(value);", "", false, "size='12'; readonly='true'"));
  $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim4", number_format($mnt_budget_trim4,0,'.',' '), "chkMnt_setMntAnnuel(); value = formateMontant(value);", "", false, "size='12'"));
  $mnt_annuel += $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['parent_mnt_trim4'];

  // Annuel
  $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_annuel", number_format($mnt_annuel,0,'.',' '), "value = formateMontant(value);", "", false, "size='12' readonly='true'"));

  //Fonction javascript pour verification montant budget et le calcule automatique du montant budget annuel pour chaque ligne dans le tableau
  $JScode_1 = "";
  $JScode_1 .= "\nfunction chkMnt_setMntAnnuel()\n";
  $JScode_1 .= "{\n";
  $JScode_1 .= "\t var total = 0; var trim1 = 0; var trim2 = 0; var trim3 = 0; var trim4 = 0; var total_val = 0; var p_trim1;\n";
  $JScode_1 .= "\t var p_trim2 = 0; var p_trim3 = 0; var p_trim4 = 0;\n";
  $JScode_1 .= "\t if (document.getElementsByName('mnt_annuel').item(0).value != '') {\n";
  $JScode_1 .= "\t\t total = 0;\n";
  $JScode_1 .= "\t }\n";
  $JScode_1 .= "\t if (document.getElementsByName('mnt_trim1').item(0).value != '') {\n";
  $JScode_1 .= "\t\t trim1 = recupMontant(document.getElementsByName('mnt_trim1').item(0).value);\n";
  $JScode_1 .= "\t\t p_trim1 = recupMontant(document.getElementsByName('parent_mnt_trim1').item(0).value);\n";
  if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['id_ligne_parent'] != ''){
    $JScode_1 .= "\t\t if (trim1 > p_trim1) {\n";
    $JScode_1 .= "\t\t\t alert('Le montant budget doit etre inferieure à montant disponible pour Trimestre 1!!');\n";
    $JScode_1 .= "\t\t\t document.getElementsByName('mnt_trim1').item(0).value = '';\n";
    $JScode_1 .= "\t\t }\n";
  }
  $JScode_1 .= "\t }\n";
  $JScode_1 .= "\t if (document.getElementsByName('mnt_trim2').item(0).value != '') {\n";
  $JScode_1 .= "\t\t trim2 = recupMontant(document.getElementsByName('mnt_trim2').item(0).value);\n";
  $JScode_1 .= "\t\t p_trim2 = recupMontant(document.getElementsByName('parent_mnt_trim2').item(0).value);\n";
  if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['id_ligne_parent'] != ''){
    $JScode_1 .= "\t\t if (trim2 > p_trim2) {\n";
    $JScode_1 .= "\t\t\t alert('Le montant budget doit etre inferieure à montant disponible pour Trimestre 2!!');\n";
    $JScode_1 .= "\t\t\t document.getElementsByName('mnt_trim2').item(0).value = '';\n";
    $JScode_1 .= "\t\t }\n";
  }
  $JScode_1 .= "\t }\n";
  $JScode_1 .= "\t if (document.getElementsByName('mnt_trim3').item(0).value != '') {\n";
  $JScode_1 .= "\t\t trim3 = recupMontant(document.getElementsByName('mnt_trim3').item(0).value);\n";
  $JScode_1 .= "\t\t p_trim3 = recupMontant(document.getElementsByName('parent_mnt_trim3').item(0).value);\n";
  if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['id_ligne_parent'] != ''){
    $JScode_1 .= "\t\t if (trim3 > p_trim3) {\n";
    $JScode_1 .= "\t\t\t alert('Le montant budget doit etre inferieure à montant disponible pour Trimestre 3!!');\n";
    $JScode_1 .= "\t\t\t document.getElementsByName('mnt_trim3').item(0).value = '';\n";
    $JScode_1 .= "\t\t }\n";
  }
  $JScode_1 .= "\t }\n";
  $JScode_1 .= "\t if (document.getElementsByName('mnt_trim4').item(0).value != '') {\n";
  $JScode_1 .= "\t\t trim4 = recupMontant(document.getElementsByName('mnt_trim4').item(0).value);\n";
  $JScode_1 .= "\t\t p_trim4 = recupMontant(document.getElementsByName('parent_mnt_trim4').item(0).value);\n";
  if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['id_ligne_parent'] != ''){
    $JScode_1 .= "\t\t if (trim4 > p_trim4) {\n";
    $JScode_1 .= "\t\t\t alert('Le montant budget doit etre inferieure à montant disponible pour Trimestre 4!!');\n";
    $JScode_1 .= "\t\t\t document.getElementsByName('mnt_trim4').item(0).value = '';\n";
    $JScode_1 .= "\t\t }\n";
  }
  $JScode_1 .= "\t }\n";
  $JScode_1 .= "\t total_val = total + trim1 + trim2 + trim3 + trim4;\n";
  $JScode_1 .= "\t document.getElementsByName('mnt_annuel').item(0).value = formateMontant(total_val);\n";
  $JScode_1 .= "}\n";
  $myForm->addJS(JSP_FORM,"computMntAnnuel",$JScode_1);

  //Fonction javascript pour verfier si tout les budgets sont renseignés
  $JScode_2 = "";
  $JScode_2 .= "\nfunction checkMontantBudget(numLigne)\n";
  $JScode_2 .= "{\n";
  $JScode_2 .= "\t var valide='t';\n";
  $JScode_2 .= "\t for(var i = 1; i <= numLigne; i++) {\n";
  $JScode_2 .= "\t\t for(var j = 1; j <= 4; j++) {\n";
  $JScode_2 .= "\t\t\t if (document.getElementsByName('mnt_trim'+j).item(0).value == '') {\n";
  $JScode_2 .= "\t\t\t\t isSubmit=false;\n";
  $JScode_2 .= "\t\t\t\t ADFormValid=false;\n";
  $JScode_2 .= "\t\t\t\t valide='f';\n";
  $JScode_2 .= "\t\t\t }\n";
  $JScode_2 .= "\t\t }\n";
  $JScode_2 .= "\t }\n";
  $JScode_2 .= "\t if (valide=='f') {\n";
  $JScode_2 .= "\t\t alert('Veuillez renseigner tout les champs budgets avant de valider!!');\n";
  $JScode_2 .= "\t }\n";
  $JScode_2 .= "}\n";
  $myForm->addJS(JSP_FORM,"chkMntBudget",$JScode_2);

  //sauvegarder le nom de l'écran et le nom du bouton  pour le prochain ecran confirmation 'Cmb-1'
  $myForm->addHiddenType("ecran_prec", 'Mnl-3');
  $myForm->addHiddenType("bouton_enreg", 'enreg');
  $myForm->addHiddenType("ligne_budgetaire", $ligne_budgetaire);

  //unset previous sessions sauf pour la ligne budgetaire en jeu
  $sizeSession = sizeof($SESSION_VARS['ligne_budgetaire']);
  for ($i=1;$i<=$sizeSession;$i++){
    if ($ligne_budgetaire != $i){
      unset($SESSION_VARS['ligne_budgetaire'][$i]);
    }
  }

  //Les boutons de navigation/operation
  $myForm->addFormButton(1, 1, "enreg", _("Enregistrer"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "prec", _("Precedent"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 4, "annul", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("enreg", BUTP_PROCHAIN_ECRAN, 'Cmb-1');
  $myForm->setFormButtonProperties("enreg", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("enreg", BUTP_JS_EVENT, array("onClick"=>"checkMontantBudget(1);"));
  $myForm->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, 'Mnl-2');
  $myForm->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, 'Gen-15');

  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*{{{ Rlb-1 : Choix du type budget */
else if ($global_nom_ecran == "Rlb-1") {
  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Saisie du type de budget"));

  $exo_encours = getExoEnCoursAll();
  $exo_encours_all = array();
  if ($exo_encours != null){
    foreach($exo_encours as $key => $value){
      $exo_encours_all[$key] = $value["debut_annee"]." - ".$value["debut_annee"];
    }
  }
  //$liste_type_budget = getAllExoOuvertWithBudgetAvailable( " <> 1 ");

  $myForm->addField("id_exo",_("Exercice(s) en cours"), TYPC_LSB);
  $myForm->setFieldProperties('id_exo', FIELDP_ADD_CHOICES, $exo_encours_all);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("id_exo", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("id_exo", FIELDP_JS_EVENT, array("onChange"=>"assign('Rlb-1'); this.form.submit();"));
  if (isset($id_exo)){
    $myForm->setFieldProperties("id_exo", FIELDP_DEFAULT, $id_exo);
  }

  if (isset($id_exo) && $id_exo != null ){
    $liste_type_budget = getDataBudget($id_exo," = 1");
  }
  else{
    $liste_type_budget = null;
  }

  $myForm->addField("type_budget", _("Type de budget"), TYPC_LSB);
  $myForm->setFieldProperties('type_budget', FIELDP_ADD_CHOICES, $liste_type_budget);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("type_budget", FIELDP_IS_REQUIRED, true);

  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rlb-2');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*{{{ Rlb-2 : Saisie Données Modifiables*/
else if ($global_nom_ecran == "Rlb-2") {

  //Recupere les donnees du tableau budget qui est associé au type de budget et id exercice en parametre
  $exo_encours= getExoEnCours();
  $tabBudget = getTabBudget($type_budget,$id_exo,"<= 2");
  $SESSION_VARS['exo_budget_choisi'] = $id_exo;

  //Si != null, c'est que on a des donnees budget pour ce type de budget
  if ($tabBudget != null){
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Raffiner le Budget : ".$adsys["adsys_type_budget"][$type_budget]));

    $myTable =& $myForm->addHTMLTable("plan_comptable", 11, TABLE_STYLE_ALTERN);
    $myTable->add_cell(new TABLE_cell(_("Correspondance"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Poste"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Description"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Compartiment"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Comptes Comptable"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Bloque Compte?"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Montant Budget Trimestre"), 4, 1));
    $myTable->add_cell(new TABLE_cell(_("Montant Budget"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("1"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("2"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("3"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("4"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Annuel"), 1, 1));

    //Recupere le nombre des lignes dans l'array budget
    $sizeBudget = sizeof($tabBudget);
    $poste = -1;

    //initialisation variable contenant les JS pour renseigner par défaut la valeur des champs qui doivent avoir zéro comme
    //Valeur + le fonction JS
    $jsSetValueZero = "";
    $jsSetValueZero .= "function setValueZero(){\n";
    $jsSetValueZero .= "\tvar listChamps = '";

    //On loop dans l'array budget pour creer le tableau pour le raffinement du budget
    for ($i = 0; $i < $sizeBudget; $i++) {
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['id_correspondance'], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['poste'], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['description'], 1, 1));
      $myTable->add_cell(new TABLE_cell($adsys["adsys_compartiment_comptable"][$tabBudget[$i]['compartiment']], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['cpte_correspondance'], 1, 1));
      if ($tabBudget[$i]['etat_bloque']=='t'){
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  checked='true' />", 1, 1));
      }
      else{
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  unchecked='true' />", 1, 1));
      }
      //Evolution du ticket REL-104 : à prendre en considération la valeur zero pour les champs
      $mntTrim1 = number_format($tabBudget[$i]['mnt_trim1'],0,'.',' ');
      if ((int)$tabBudget[$i]['mnt_trim1'] == 0){
        $mntTrim1 = "0";
        $champ = "mnt_trim1_".($i+1)."-";
        $jsSetValueZero .= $champ;
      }
      $mntTrim2 = number_format($tabBudget[$i]['mnt_trim2'],0,'.',' ');
      if ((int)$tabBudget[$i]['mnt_trim2'] == 0){
        $mntTrim2 = "0";
        $champ = "mnt_trim2_".($i+1)."-";
        $jsSetValueZero .= $champ;
      }
      $mntTrim3 = number_format($tabBudget[$i]['mnt_trim3'],0,'.',' ');
      if ((int)$tabBudget[$i]['mnt_trim3'] == 0){
        $mntTrim3 = "0";
        $champ = "mnt_trim3_".($i+1)."-";
        $jsSetValueZero .= $champ;
      }
      $mntTrim4 = number_format($tabBudget[$i]['mnt_trim4'],0,'.',' ');
      if ((int)$tabBudget[$i]['mnt_trim4'] == 0){
        $mntTrim4 = "0";
        $champ = "mnt_trim4_".($i+1)."-";
        $jsSetValueZero .= $champ;
      }
      $mntAnnuel = number_format($tabBudget[$i]['mnt_annuel'],0,'.',' ');
      if ((int)$tabBudget[$i]['mnt_annuel'] == 0){
        $mntAnnuel = "0";
        $champ = "mnt_annuel_".($i+1)."-";
        $jsSetValueZero .= $champ;
      }
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim1_".($i+1), $mntTrim1, "setMontantAnnuel(".($i+1)."); value = formateMontant(value);", "", false, "size='12'"));
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim2_".($i+1), $mntTrim2, "setMontantAnnuel(".($i+1)."); value = formateMontant(value);", "", false, "size='12'"));
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim3_".($i+1), $mntTrim3, "setMontantAnnuel(".($i+1)."); value = formateMontant(value);", "", false, "size='12'"));
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim4_".($i+1), $mntTrim4, "setMontantAnnuel(".($i+1)."); value = formateMontant(value);", "", false, "size='12'"));
      $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_annuel_".($i+1), $mntAnnuel, "", "", false, "readonly='true'"));

      //creation session pour garder certains informations des lignes budgetaires
      $SESSION_VARS['ligne_budgetaire'][$i+1]['ligne_budgetaire']=($i+1);
      $SESSION_VARS['ligne_budgetaire'][$i+1]['ref_budget']=$tabBudget[$i]['ref_budget'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['type_budget']=$type_budget;
      $SESSION_VARS['ligne_budgetaire'][$i+1]['id_correspondance']=$tabBudget[$i]['id_correspondance'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['poste']=$tabBudget[$i]['poste'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['description']=$tabBudget[$i]['description'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['compartiment']=$tabBudget[$i]['compartiment'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['cpte_correspondance']=$tabBudget[$i]['cpte_correspondance'];
    }
    //REL-104 : les JS pour renseigner par defaut la valeur des champs qui devront avoir zero comme valeur
    $jsSetValueZero .= "';\n";
    $jsSetValueZero .= "\tvar champs = listChamps.split('-');\n";
    $jsSetValueZero .= "\tvar nbrechamps = champs.length;\n";
    $jsSetValueZero .= "\tfor (var i=0; i<(nbrechamps-1); i++){\n";
    $jsSetValueZero .= "\t\tdocument.getElementsByName(champs[i]).item(0).value=0;\n";
    $jsSetValueZero .= "\t}\n";
    $jsSetValueZero .= "}\n";
    $jsSetValueZero .= "setValueZero();";
    $myForm->addJS(JSP_FORM,"setValueZero",$jsSetValueZero);

    //Fonction javascript pour le calcule automatique du montant budget annuel pour chaque ligne dans le tableau
    $JScode_1 = "";
    $JScode_1 .= "\nfunction setMontantAnnuel(id)\n";
    $JScode_1 .= "{\n";
    $JScode_1 .= "\t var total = 0; var trim1 = 0; var trim2 = 0; var trim3 = 0; var trim4 = 0; var total_val = 0;\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_annuel_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t total = 0;\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_trim1_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t trim1 = recupMontant(document.getElementsByName('mnt_trim1_'+id).item(0).value);\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_trim2_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t trim2 = recupMontant(document.getElementsByName('mnt_trim2_'+id).item(0).value);\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_trim3_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t trim3 = recupMontant(document.getElementsByName('mnt_trim3_'+id).item(0).value);\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t if (document.getElementsByName('mnt_trim4_'+id).item(0).value != '') {\n";
    $JScode_1 .= "\t\t trim4 = recupMontant(document.getElementsByName('mnt_trim4_'+id).item(0).value);\n";
    $JScode_1 .= "\t }\n";
    $JScode_1 .= "\t total_val = total + trim1 + trim2 + trim3 + trim4;\n";
    $JScode_1 .= "\t document.getElementsByName('mnt_annuel_'+id).item(0).value = formateMontant(total_val);\n";
    $JScode_1 .= "}\n";
    $myForm->addJS(JSP_FORM,"computMntAnnuel",$JScode_1);

    //Fonction javascript pour verfier si tout les budgets sont renseignés
    $JScode_2 = "";
    $JScode_2 .= "\nfunction checkMontantBudget(numLigne)\n";
    $JScode_2 .= "{\n";
    $JScode_2 .= "\t var valide='t';\n";
    $JScode_2 .= "\t for(var i = 1; i <= numLigne; i++) {\n";
    $JScode_2 .= "\t\t for(var j = 1; j <= 4; j++) {\n";
    $JScode_2 .= "\t\t\t if (document.getElementsByName('mnt_trim'+j+'_'+i).item(0).value == '') {\n";
    $JScode_2 .= "\t\t\t\t isSubmit=false;\n";
    $JScode_2 .= "\t\t\t\t ADFormValid=false;\n";
    $JScode_2 .= "\t\t\t\t valide='f';\n";
    $JScode_2 .= "\t\t\t }\n";
    $JScode_2 .= "\t\t }\n";
    $JScode_2 .= "\t }\n";
    $JScode_2 .= "\t if (valide=='f') {\n";
    $JScode_2 .= "\t\t alert('Veuillez renseigner tout les champs budgets avant de valider!!');\n";
    $JScode_2 .= "\t }\n";
    $JScode_2 .= "}\n";
    $myForm->addJS(JSP_FORM,"chkMntBudget",$JScode_2);

    //sauvegarder le nom de l'écran et le nom du bouton  pour le prochain ecran confirmation 'Cmb-1'
    $myForm->addHiddenType("ecran_prec", 'Rlb-2');
    $myForm->addHiddenType("bouton_enreg", 'enreg');


    //Les boutons de navigation/operation
    $myForm->addFormButton(1, 1, "enreg", _("Enregistrer"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "prec", _("Precedent"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 4, "annul", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("enreg", BUTP_PROCHAIN_ECRAN, 'Cmb-1');
    $myForm->setFormButtonProperties("enreg", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("enreg", BUTP_JS_EVENT, array("onClick"=>"checkMontantBudget($sizeBudget);")); //onClick"=>"assign('Pnr-2'); this.form.submit(); BUTP_CHECK_FORM
    $myForm->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, 'Rlb-1');
    $myForm->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, 'Gen-15');

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
  else{
    $erreur = new HTML_erreur(_("Raffiner le Budget : ".$adsys["adsys_type_budget"][$type_budget]));
    $erreur->setMessage(_("Aucun table de correspondance associe a ce type de budget!!"));
    $erreur->addButton(BUTTON_OK,"Rlb-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }

}
/*{{{ Cmb-1 : Confirmation Budget */
else if ($global_nom_ecran == "Cmb-1") {
  global $global_id_agence;

  //****************************************Debut Mise en Place Budget Annuel*****************************************//
  //On verifie si on revient de l'ecran mise en place budget 'Meb-2' et si c'est bien une enregistrement
  if (isset($ecran_prec) && $ecran_prec == 'Meb-2' && $bouton_enreg == 'enreg'){
    //Recuperation donnees envoyer par session et post concernant les lignes budgetaires de l'écran precedent
    $DATA_LIGNE_BUDGETAIRE = array();
    $count_ligne = 0;
    foreach($SESSION_VARS['ligne_budgetaire'] as $key => $value){
      $etat_ligne_budgetaire = 'f';
      $DATA_LIGNE_BUDGETAIRE['type_budget']=$value['type_budget'];
      $DATA_LIGNE_BUDGETAIRE['id_correspondance']=$value['id_correspondance'];
      $DATA_LIGNE_BUDGETAIRE['poste']=$value['poste'];
      $DATA_LIGNE_BUDGETAIRE['mnt_trim1']=$ {'mnt_trim1_'.$key};
      $DATA_LIGNE_BUDGETAIRE['mnt_trim2']=$ {'mnt_trim2_'.$key};
      $DATA_LIGNE_BUDGETAIRE['mnt_trim3']=$ {'mnt_trim3_'.$key};
      $DATA_LIGNE_BUDGETAIRE['mnt_trim4']=$ {'mnt_trim4_'.$key};
      if (isset($ {'etat_bloque_'.$key}) && $ {'etat_bloque_'.$key}=='on'){
        $etat_ligne_budgetaire = 't';
      }
      $DATA_LIGNE_BUDGETAIRE['etat_bloque']=$etat_ligne_budgetaire;
      $DATA_LIGNE_BUDGETAIRE['id_ag']=$global_id_agence;
      $DATA_LIGNE_BUDGETAIRE['ref_budget']=$SESSION_VARS['ref_budget_choisi'];

      //La mise e place du budget
      $count_ligne++;
      $err = miseEnPlaceBudget($DATA_LIGNE_BUDGETAIRE,$count_ligne);
    }
    //Confirmation mise en place
    if ($err->errCode == NO_ERR){
      $type = $err->param["type_budget"];
      $myMsg = new HTML_message(_("Confirmation Mise en Place Budget Annuel"));
      $msg = _("La mise en place du Budget Annuel a ete faite avec succes");
      $msg .= "<BR><BR>" . _(sprintf("Le Type de Budget : %s", $adsys["adsys_type_budget"][$err->param["type_budget"]]));
      $msg .= "<BR>" . _(sprintf("La Reference du Budget : %s", $err->param["ref_budget"]));
      $msg .= "<BR>" . _(sprintf("Annee de l'exercice : %s", $err->param["annee"]));
      $myMsg->setMessage($msg);

      $myMsg->addButton(BUTTON_OK, 'Gen-15');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
    }
  }
  //On verifie si on revient de l'ecran mise en place ligne budgetaire 'Mnl-3' et si c'est bien une enregistrement
  if (isset($ecran_prec) && $ecran_prec == 'Mnl-3' && $bouton_enreg == 'enreg'){
    //Recuperation donnees envoyer par session et post concernant les lignes budgetaires de l'écran precedent
    $DATA_LIGNE_BUDGETAIRE = array();
    foreach($SESSION_VARS['ligne_budgetaire'] as $key => $value){
      // Nouvelle Ligne Budgetaire
      $etat_ligne_bloq = 'f';
      $dernier_niv = 'f';
      $DATA_NOUVELLE_LIGNE['ref_budget']=$value['ref_budget'];
      $DATA_NOUVELLE_LIGNE['type_budget']=$value['type_budget'];
      $DATA_NOUVELLE_LIGNE['id_correspondance']=$value['id_correspondance'];
      $DATA_NOUVELLE_LIGNE['poste']=$value['poste'];
      $DATA_NOUVELLE_LIGNE['mnt_trim1']=recupMontant($mnt_trim1);
      $DATA_NOUVELLE_LIGNE['mnt_trim2']=recupMontant($mnt_trim2);
      $DATA_NOUVELLE_LIGNE['mnt_trim3']=recupMontant($mnt_trim3);
      $DATA_NOUVELLE_LIGNE['mnt_trim4']=recupMontant($mnt_trim4);
      if (isset($etat_bloque) && $etat_bloque=='1'){
        $etat_ligne_bloq = 't';
      }
      $DATA_NOUVELLE_LIGNE['etat_bloque']=$etat_ligne_bloq;
      if (isset($dernier_niveau) && $dernier_niveau=='1'){
        $dernier_niv = 't';
      }
      $DATA_NOUVELLE_LIGNE['dernier_niveau']=$dernier_niv;
      $DATA_NOUVELLE_LIGNE['id_ag']=$global_id_agence;

      // Ligne Parent Budgetaire
      $DATA_LIGNE_PARENT['id'] = $value['id_ligne_parent'];
      $DATA_LIGNE_PARENT['ref_budget']=$value['ref_budget'];
      $DATA_LIGNE_PARENT['type_budget']=$value['type_budget'];
      $DATA_LIGNE_PARENT['mnt_trim1']=recupMontant($parent_mnt_trim1)-recupMontant($mnt_trim1);
      $DATA_LIGNE_PARENT['mnt_trim2']=recupMontant($parent_mnt_trim2)-recupMontant($mnt_trim2);
      $DATA_LIGNE_PARENT['mnt_trim3']=recupMontant($parent_mnt_trim3)-recupMontant($mnt_trim3);
      $DATA_LIGNE_PARENT['mnt_trim4']=recupMontant($parent_mnt_trim4)-recupMontant($mnt_trim4);
      $DATA_LIGNE_PARENT['id_ag']=$global_id_agence;

      //La mise en place
      $err = miseEnPlaceNouvelleLigne($DATA_NOUVELLE_LIGNE,$DATA_LIGNE_PARENT);
    }
    //Confirmation mise en place
    if ($err->errCode == NO_ERR){
      $type = $err->param["type_budget"];
      $myMsg = new HTML_message(_("Confirmation Mise en Place Nouvelle Ligne Budgetaire"));
      $msg = _("La mise en place de la Nouvelle Ligne Budgetaire a ete faite avec succes");
      $msg .= "<BR><BR>" . _(sprintf("Le Type de Budget : %s", $adsys["adsys_type_budget"][$err->param["type_budget"]]));
      $msg .= "<BR>" . _(sprintf("La Reference du Budget : %s", $err->param["ref_budget"]));
      $msg .= "<BR>" . _(sprintf("Annee de l'exercice : %s", $err->param["annee"]));
      $myMsg->setMessage($msg);

      $myMsg->addButton(BUTTON_OK, 'Gen-15');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
    }
  }
  //*****************************************Fin Mise en Place Budget Annuel******************************************//

  //*******************************************Debut Raffiner le Budget***********************************************//
  //On verifie si on revient de l'ecran raffiner le budget 'Rlb-2' et si c'est bien une enregistrement
  if (isset($ecran_prec) && $ecran_prec == 'Rlb-2' && $bouton_enreg == 'enreg'){
    //Recuperation donnees envoyer par session et post concernant les lignes budgetaires de l'écran precedent
    $DATA_LIGNE_BUDGETAIRE = array();
    $count_ligne = 0;
    foreach($SESSION_VARS['ligne_budgetaire'] as $key => $value){
      $etat_ligne_budgetaire = 'f';
      $DATA_LIGNE_BUDGETAIRE['type_budget']=$value['type_budget'];
      $DATA_LIGNE_BUDGETAIRE['ref_budget']=$value['ref_budget'];
      $DATA_LIGNE_BUDGETAIRE['id_correspondance']=$value['id_correspondance'];
      $DATA_LIGNE_BUDGETAIRE['poste']=$value['poste'];
      $DATA_LIGNE_BUDGETAIRE['mnt_trim1']=$ {'mnt_trim1_'.$key};
      $DATA_LIGNE_BUDGETAIRE['mnt_trim2']=$ {'mnt_trim2_'.$key};
      $DATA_LIGNE_BUDGETAIRE['mnt_trim3']=$ {'mnt_trim3_'.$key};
      $DATA_LIGNE_BUDGETAIRE['mnt_trim4']=$ {'mnt_trim4_'.$key};
      if (isset($ {'etat_bloque_'.$key}) && $ {'etat_bloque_'.$key}=='on'){
        $etat_ligne_budgetaire = 't';
      }
      $DATA_LIGNE_BUDGETAIRE['etat_bloque']=$etat_ligne_budgetaire;
      $DATA_LIGNE_BUDGETAIRE['id_ag']=$global_id_agence;

      //La mise a jour du budget
      $count_ligne++;
      $err = raffinerBudget($DATA_LIGNE_BUDGETAIRE,$count_ligne,$SESSION_VARS['exo_budget_choisi']);
    }
    //Confirmation mise a jour
    if ($err->errCode == NO_ERR){
      $type = $err->param["type_budget"];
      $myMsg = new HTML_message(_("Confirmation Raffiner le Budget"));
      $msg = _("Le Budget Annuel a ete modifie avec succes");
      $msg .= "<BR><BR>" . _(sprintf("Le Type de Budget : %s", $adsys["adsys_type_budget"][$err->param["type_budget"]]));
      $msg .= "<BR>" . _(sprintf("La Reference du Budget : %s", $err->param["ref_budget"]));
      $msg .= "<BR>" . _(sprintf("Annee de l'exercice : %s", $err->param["annee"]));
      $myMsg->setMessage($msg);

      $myMsg->addButton(BUTTON_OK, 'Gen-15');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
    }
  }
  //*********************************************Fin Raffiner le Budget***********************************************//

  //*******************************************Debut Reviser le Budget************************************************//
  //On verifie si on revient de l'ecran reviser le budget 'Rdb-3' et si c'est bien une revision
  if (isset($ecran_prec) && $ecran_prec == 'Rdb-3' && $bouton_enreg == 'enreg'){
    //Recuperation donnees envoyer par session et post concernant les lignes budgetaires de l'écran precedent
    $DATA_LIGNE_BUDGETAIRE = array();
    //---info general envoyer par session
    $DATA_LIGNE_BUDGETAIRE['ligne_budgetaire'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['id_ligne_budgetaire'];
    $DATA_LIGNE_BUDGETAIRE['hasRevisions'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['hasRevisions'];
    $DATA_LIGNE_BUDGETAIRE['type_budget'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['type_budget'];
    $DATA_LIGNE_BUDGETAIRE['ref_budget'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['ref_budget'];
    $DATA_LIGNE_BUDGETAIRE['type_budget'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['type_budget'];
    $DATA_LIGNE_BUDGETAIRE['id_correspondance'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['id_correspondance'];
    $DATA_LIGNE_BUDGETAIRE['poste'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['poste'];
    $DATA_LIGNE_BUDGETAIRE['cpte_correspondance'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['cpte_correspondance'];
    $DATA_LIGNE_BUDGETAIRE['etat_bloque'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['etat_bloque'];
    if (isset($etat_bloque) && $etat_bloque == 1){
      $DATA_LIGNE_BUDGETAIRE['etat_bloque'] = 't';
    }
    //---info trimestre nouveau budget envoyer par post et info si trimestre ouvert ou fermer envoyer par session
    $DATA_LIGNE_BUDGETAIRE['isTrimestre1Open'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre1Open'];
    $DATA_LIGNE_BUDGETAIRE['anc_mnt_trim1'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['mnt_budget1'];
    $DATA_LIGNE_BUDGETAIRE['mnt_trim1'] = $mnt_trim1;
    $DATA_LIGNE_BUDGETAIRE['isTrimestre2Open'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre2Open'];
    $DATA_LIGNE_BUDGETAIRE['anc_mnt_trim2'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['mnt_budget2'];
    $DATA_LIGNE_BUDGETAIRE['mnt_trim2'] = $mnt_trim2;
    $DATA_LIGNE_BUDGETAIRE['isTrimestre3Open'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre3Open'];
    $DATA_LIGNE_BUDGETAIRE['anc_mnt_trim3'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['mnt_budget3'];
    $DATA_LIGNE_BUDGETAIRE['mnt_trim3'] = $mnt_trim3;
    $DATA_LIGNE_BUDGETAIRE['isTrimestre4Open'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre4Open'];
    $DATA_LIGNE_BUDGETAIRE['anc_mnt_trim4'] = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['mnt_budget4'];
    $DATA_LIGNE_BUDGETAIRE['mnt_trim4'] = $mnt_trim4;
    $DATA_LIGNE_BUDGETAIRE['id_exo'] = $SESSION_VARS['id_exo'];

    $err=reviserBudget($DATA_LIGNE_BUDGETAIRE,1);
    //Confirmation revision
    if ($err->errCode == NO_ERR){
      $type = $err->param["type_budget"];
      $myMsg = new HTML_message(_("Confirmation Reviser le Budget"));
      $msg = _("Le Budget Annuel a ete revise avec succes");
      $msg .= "<BR><BR>" . _(sprintf("Le Type de Budget : %s", $adsys["adsys_type_budget"][$err->param["type_budget"]]));
      $msg .= "<BR>" . _(sprintf("La Reference du Budget : %s", $err->param["ref_budget"]));
      $msg .= "<BR>" . _(sprintf("Annee de l'exercice : %s", $err->param["annee"]));
      $myMsg->setMessage($msg);

      $myMsg->addButton(BUTTON_OK, 'Gen-15');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
    }
  }
  //*********************************************Fin Reviser le Budget************************************************//

  //********************************************Debut Valider le Budget***********************************************//
  //On verifie si on revient de l'ecran valider le budget 'Vlb-2' et si c'est bien une validation
  if (isset($ecran_prec) && $ecran_prec == 'Vlb-2' && $bouton_enreg == 'valider'){
    $type_budget = -1;
    $ref_budget = '';
    //Recuperation donnees envoyer par session et post concernant les lignes budgetaires de l'écran precedent
    $DATA_LIGNE_BUDGETAIRE = array();
    $count_ligne = 0;
    $countValidationRevision = 0;
    foreach($SESSION_VARS['ligne_budgetaire'] as $key => $value){
      $etat_ligne_budgetaire = 'f';
      $type_budget = $value['type_budget'];
      $DATA_LIGNE_BUDGETAIRE['type_budget']=$value['type_budget'];
      $ref_budget = $value['ref_budget'];
      $DATA_LIGNE_BUDGETAIRE['ref_budget']=$value['ref_budget'];
      $DATA_LIGNE_BUDGETAIRE['id_correspondance']=$value['id_correspondance'];
      $DATA_LIGNE_BUDGETAIRE['poste']=$value['poste'];
      if (isset($ {'etat_bloque_'.$key}) && $ {'etat_bloque_'.$key}=='on'){
        $etat_ligne_budgetaire = 't';
      }
      //Recuperation extra donnees pour la validation revision budgetaire
      $DATA_LIGNE_BUDGETAIRE['id_ligne_budgetaire'] = $value['id_ligne_budgetaire'];
      $DATA_LIGNE_BUDGETAIRE['isTrimestre1Open'] = $value['isTrimestre1Open'];
      $DATA_LIGNE_BUDGETAIRE['nouv_mnt_trim1'] = $value['nouv_mnt_trim1'];
      $DATA_LIGNE_BUDGETAIRE['isTrimestre2Open'] = $value['isTrimestre2Open'];
      $DATA_LIGNE_BUDGETAIRE['nouv_mnt_trim2'] = $value['nouv_mnt_trim2'];
      $DATA_LIGNE_BUDGETAIRE['isTrimestre3Open'] = $value['isTrimestre3Open'];
      $DATA_LIGNE_BUDGETAIRE['nouv_mnt_trim3'] = $value['nouv_mnt_trim3'];
      $DATA_LIGNE_BUDGETAIRE['isTrimestre4Open'] = $value['isTrimestre4Open'];
      $DATA_LIGNE_BUDGETAIRE['nouv_mnt_trim4'] = $value['nouv_mnt_trim4'];

      $DATA_LIGNE_BUDGETAIRE['etat_bloque']=$etat_ligne_budgetaire;
      $DATA_LIGNE_BUDGETAIRE['id_ag']=$global_id_agence;

      //La validation du budget
      $count_ligne++;
      $isRevision = false;
      $validerRevision = false;
      if (isset($SESSION_VARS['isRevision']) && $SESSION_VARS['isRevision']=='t'){
        $isRevision = true;
      }
      if (isset($ {'valid_'.$key}) && $ {'valid_'.$key}=='on'){
        $validerRevision = true;
        $countValidationRevision++;
      }
      if ($isRevision === false || ($isRevision === true && $validerRevision === true)){
        $errValider = validerBudget($DATA_LIGNE_BUDGETAIRE,$count_ligne,$isRevision,$SESSION_VARS['id_exo_choisi']);
      }
    }

    //Pour mise a jour table ad_budget
    //Verification si on doit changer l'etat du budget au cas tous ces lignes revisions ont ete validés
    if ($isRevision === true){
      $errChangeEtat = changeEtatBudgetRevision($type_budget, $ref_budget,$SESSION_VARS['id_exo_choisi']);
    }

    //Confirmation validation
    if (($isRevision === false && $errValider->errCode == NO_ERR) || ($isRevision === true && $errValider->errCode == NO_ERR && $errChangeEtat->errCode == NO_ERR && $countValidationRevision > 0)){
      $type = $err->param["type_budget"];
      $myMsg = new HTML_message(_("Confirmation Valider le Budget"));
      if ($isRevision === true){
        $msg = _("Le(s) Ligne(s) Budgetaire(s) et Revision(s) ont ete valide avec succes");
      }
      else{
        $msg = _("Le Budget Annuel a ete valide avec succes");
      }
      $msg .= "<BR><BR>" . _(sprintf("Le Type de Budget : %s", $adsys["adsys_type_budget"][$errValider->param["type_budget"]]));
      $msg .= "<BR>" . _(sprintf("La Reference du Budget : %s", $errValider->param["ref_budget"]));
      $msg .= "<BR>" . _(sprintf("Annee de l'exercice : %s", $errValider->param["annee"]));
      $myMsg->setMessage($msg);

      $myMsg->addButton(BUTTON_OK, 'Gen-15');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
    }
    else{
      $erreur = new HTML_erreur(_("Valider le Budget Budget Annuel : ".$adsys["adsys_type_budget"][$type_budget]));
      $erreur->setMessage(_("Aucune revision a été coché pour etre validé!!"));
      $erreur->addButton(BUTTON_OK,"Vlb-2");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
    }
    unset($SESSION_VARS);
  }
  //On verifie si on revient de l'ecran valider la nouvelle ligne budgetaire 'Vnl-2' et si c'est bien une validation
  if (isset($ecran_prec) && $ecran_prec == 'Vnl-2' && $bouton_enreg == 'valider'){
    $type_budget = -1;
    $ref_budget = '';
    //Recuperation donnees envoyer par session et post concernant les nouvelles lignes budgetaires de l'écran precedent
    $DATA_LIGNE_BUDGETAIRE = array();
    $countValidationLigne = 0;
    foreach($SESSION_VARS['ligne_budgetaire'] as $key => $value){
      $etat_bloq = 'f';
      $type_budget = $value['type_budget'];
      $DATA_LIGNE_BUDGETAIRE['type_budget']=$value['type_budget'];
      $ref_budget = $value['ref_budget'];
      $DATA_LIGNE_BUDGETAIRE['ref_budget']=$value['ref_budget'];
      $DATA_LIGNE_BUDGETAIRE['id_correspondance']=$value['id_correspondance'];
      $DATA_LIGNE_BUDGETAIRE['poste']=$value['poste'];
      if (isset($ {'etat_bloque_'.$key}) && $ {'etat_bloque_'.$key}=='on'){
        $etat_bloq = 't';
      }
      //Recuperation extra donnees pour la validation de la nouvelle ligne budgetaire
      $DATA_LIGNE_BUDGETAIRE['id_ligne_budgetaire'] = $value['id_ligne_budgetaire'];

      $DATA_LIGNE_BUDGETAIRE['etat_bloque']=$etat_bloq;
      $DATA_LIGNE_BUDGETAIRE['id_ag']=$global_id_agence;

      //La validation de la nouvelle ligne budgetaire
      $validerLigne = false;
      if (isset($ {'valid_'.$key}) && $ {'valid_'.$key}=='on'){
        $validerLigne = true;
        $countValidationLigne++;
      }
      if ($validerLigne === true){
        $errValider = validerLigneBudgetaire($DATA_LIGNE_BUDGETAIRE,$id_exo);
      }
    }

    //Confirmation validation
    if (($errValider->errCode == NO_ERR && $countValidationLigne > 0)){
      $type = $err->param["type_budget"];
      $myMsg = new HTML_message(_("Confirmation Valider le(s) Nouvelle(s) ligne(s) Budgetaire(s)"));
      $msg = _("Le(s) Nouvelle(s) Ligne(s) Budgetaire(s) ont ete valide avec succes");
      $msg .= "<BR><BR>" . _(sprintf("Le Type de Budget : %s", $adsys["adsys_type_budget"][$errValider->param["type_budget"]]));
      $msg .= "<BR>" . _(sprintf("La Reference du Budget : %s", $errValider->param["ref_budget"]));
      $msg .= "<BR>" . _(sprintf("Annee de l'exercice : %s", $errValider->param["annee"]));
      $myMsg->setMessage($msg);

      $myMsg->addButton(BUTTON_OK, 'Gen-15');
      $myMsg->buildHTML();
      echo $myMsg->HTML_code;
    }
    else{
      $erreur = new HTML_erreur(_("Valider le(s) ligne(s) Budgetaire(s) : ".$adsys["adsys_type_budget"][$type_budget]));
      $erreur->setMessage(_("Aucune ligne a été coché pour etre validé!!"));
      $erreur->addButton(BUTTON_OK,"Vnl-2");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
    }
  }
  //**********************************************Fin Valider le Budget***********************************************//
}