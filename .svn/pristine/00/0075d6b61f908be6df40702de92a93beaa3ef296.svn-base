<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 10/4/2017
 * Time: 1:25 PM
 */
require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/budget.php');
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/html/FILL_HTML_GEN2.php';

/*{{{ Vlb-1 : Choix du type budget -> Validation Budget */
if ($global_nom_ecran == "Vlb-1") {
  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Saisie du type de budget"));

  if (isset($SESSION_VARS['isRevision'])){
    unset($SESSION_VARS['isRevision']);
  }
  if (isset($SESSION_VARS['type_budget'])){
    unset($SESSION_VARS['type_budget']);
  }
  if (isset($SESSION_VARS['type_validation'])){
    unset($SESSION_VARS['type_validation']);
  }
  if (isset($SESSION_VARS['ligne_budgetaire'])){
    unset($SESSION_VARS['ligne_budgetaire']);
  }

  $exo_encours = getExoEnCoursAll();
  $exo_encours_all = array();
  if ($exo_encours != null){
    foreach($exo_encours as $key => $value){
      $exo_encours_all[$key] = $value["debut_annee"]." - ".$value["debut_annee"];
    }
  }
  //$liste_type_budget = getAllExoOuvertWithBudgetAvailable( " <> 2 and b.etat_budget <> 4 ");

  $myForm->addField("id_exo",_("Exercice(s) en cours"), TYPC_LSB);
  $myForm->setFieldProperties('id_exo', FIELDP_ADD_CHOICES, $exo_encours_all);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("id_exo", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("id_exo", FIELDP_JS_EVENT, array("onChange"=>"assign('Vlb-1'); this.form.submit();"));
  if (isset($id_exo)){
    $myForm->setFieldProperties("id_exo", FIELDP_DEFAULT, $id_exo);
  }

  if (isset($id_exo) && $id_exo != null ){
    $liste_type_budget = getDataBudget($id_exo," IN (2,4) ");
  }
  else{
    $liste_type_budget = null;
  }

  $myForm->addField("type_budget", _("Type de budget"), TYPC_LSB);
  $myForm->setFieldProperties('type_budget', FIELDP_ADD_CHOICES, $liste_type_budget);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("type_budget", FIELDP_IS_REQUIRED, true);

  $myForm->addField("type_validation", _("Type de validation"), TYPC_LSB);
  $myForm->setFieldProperties('type_validation', FIELDP_ADD_CHOICES, $adsys["adsys_type_validation_budget"]);
  $myForm->setFieldProperties("type_validation", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("type_validation", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("type_validation", FIELDP_IS_REQUIRED, true);

  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Vlb-2');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*{{{ Vlb-2 : Verification Données pour validation budget*/
else if ($global_nom_ecran == "Vlb-2") {

  //*********************************************Debut Ecran Validation Budget****************************************//
  $typeBudget = $type_budget;
  if (isset($SESSION_VARS['type_budget'])){
    $typeBudget = $SESSION_VARS['type_budget'];
  }
  if(isset($id_exo)){
    $SESSION_VARS['id_exo_choisi'] = $id_exo;
  }
  $typeValidation = $type_validation;
  if (isset($SESSION_VARS['type_validation'])){
    $typeValidation = $SESSION_VARS['type_validation'];
  }
  if (isset($type_validation) || isset($SESSION_VARS['type_validation'])){
    //Recupere les donnees du tableau budget qui est associé au type de budget et id exercice en parametre
    $titre_validation = "";
    $exo_encours= getExoEnCours();
    if ($typeValidation == 1){ //Raffinement Budget
      $SESSION_VARS['isRevision']='f';
      $titre_validation = "Raffinement";
      $tabBudget = getTabBudget($typeBudget,$id_exo,"= 2");
    }
    if ($typeValidation == 2){ //Revision Budget
      $SESSION_VARS['isRevision']='t';
      $titre_validation = "Revision";
      $tabBudget = getValidationRevisionBudget($typeBudget,$SESSION_VARS['id_exo_choisi'],">= 3");
    }
  }

  //Si != null, c'est que on a des donnees budget pour ce type de budget
  if ($tabBudget != null){
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Validation $titre_validation du Budget : ".$adsys["adsys_type_budget"][$typeBudget]));

    $myTableNumCol = 10; //le nombre de colonnes par defaut 10 pour raffinement budget
    $myTableNumRowSpan = 2; //Row span pour raffinement budget
    if ($typeValidation == 2){ //pour revision budget
      $myTableNumCol = 17;
      $myTableNumRowSpan = 3;
    }

    $myTable =& $myForm->addHTMLTable("plan_comptable", $myTableNumCol, TABLE_STYLE_ALTERN);
    if ($typeValidation != 2){
      $myTable->add_cell(new TABLE_cell(_("Correspondance"), 1, $myTableNumRowSpan));
    }
    $myTable->add_cell(new TABLE_cell(_("Poste"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Bloque Compte?"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Description"), 1, $myTableNumRowSpan));
    //$myTable->add_cell(new TABLE_cell(_("Compartiment"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Comptes Comptable"), 1, $myTableNumRowSpan));
    if ($typeValidation != 2){
      $myTable->add_cell(new TABLE_cell(_("Montant Budget"), 5, 1));
      $myTable->add_cell(new TABLE_cell(_("Trimestre 1"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Trimestre 2"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Trimestre 3"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Trimestre 4"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Annuel"), 1, 1));
    }
    else{
      $myTable->add_cell(new TABLE_cell(_("Budget Par Trimestre"), 12, 1));
      $myTable->add_cell(new TABLE_cell(_("Valider?"), 1, 3));
      $myTable->add_cell(new TABLE_cell(_("1"), 3, 1));
      $myTable->add_cell(new TABLE_cell(_("2"), 3, 1));
      $myTable->add_cell(new TABLE_cell(_("3"), 3, 1));
      $myTable->add_cell(new TABLE_cell(_("4"), 3, 1));
      $myTable->add_cell(new TABLE_cell(_("Budget"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("% Realisation"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Nouveau Budget"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Budget"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("% Realisation"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Nouveau Budget"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Budget"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("% Realisation"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Nouveau Budget"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Budget"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("% Realisation"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Nouveau Budget"), 1, 1));
    }

    //Recupere le nombre des lignes dans l'array budget
    $sizeBudget = sizeof($tabBudget);
    $poste = -1;

    if (isset($SESSION_VARS['ligne_budgetaire'])){
      unset($SESSION_VARS['ligne_budgetaire']);
    }
    if (isset($SESSION_VARS['isRevision'])){
      unset($SESSION_VARS['isRevision']);
    }

    //On loop dans l'array budget pour creer le tableau pour la validation du raffinement/revision budget
    for ($i = 0; $i < $sizeBudget; $i++) {
      if ($typeValidation != 2){
        $myTable->add_cell(new TABLE_cell($tabBudget[$i]['id_correspondance'], 1, 1));
      }
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['poste'], 1, 1));
      if ($typeValidation != 2){
        if ($tabBudget[$i]['etat_bloque']=='t'){
          $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  checked='true' />", 1, 1));
        }
        else{
          $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  unchecked='true' />", 1, 1));
        }
      }if ($typeValidation == 2){
        if ($tabBudget[$i]['etat_bloque']=='t'){
          $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  checked='true' readonly />", 1, 1));
        }
        else{
          $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  unchecked='true' readonly />", 1, 1));
        }
      }
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['description'], 1, 1));
      //$myTable->add_cell(new TABLE_cell($adsys["adsys_compartiment_comptable"][$tabBudget[$i]['compartiment']], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['cpte_correspondance'], 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim1'],0,'.',','), 1, 1));
      if ($typeValidation == 2){
        $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['prc_utilisation_trim1'],2,'.',' '), 1, 1));
        $myTable->add_cell(new TABLE_cell("<b>".number_format($tabBudget[$i]['nouv_mnt_trim1'],0,'.',',')."</b>", 1, 1));
      }
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim2'],0,'.',','), 1, 1));
      if ($typeValidation == 2){
        $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['prc_utilisation_trim2'],2,'.',' '), 1, 1));
        $myTable->add_cell(new TABLE_cell("<b>".number_format($tabBudget[$i]['nouv_mnt_trim2'],0,'.',',')."</b>", 1, 1));
      }
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim3'],0,'.',','), 1, 1));
      if ($typeValidation == 2){
        $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['prc_utilisation_trim3'],2,'.',' '), 1, 1));
        $myTable->add_cell(new TABLE_cell("<b>".number_format($tabBudget[$i]['nouv_mnt_trim3'],0,'.',',')."</b>", 1, 1));
      }
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim4'],0,'.',','), 1, 1));
      if ($typeValidation == 2){
        $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['prc_utilisation_trim4'],2,'.',' '), 1, 1));
        $myTable->add_cell(new TABLE_cell("<b>".number_format($tabBudget[$i]['nouv_mnt_trim4'],0,'.',',')."</b>", 1, 1));
      }
      if ($typeValidation != 2){
        $mnt_annuel = $tabBudget[$i]['mnt_trim1'] + $tabBudget[$i]['mnt_trim2'] + $tabBudget[$i]['mnt_trim3'] + $tabBudget[$i]['mnt_trim4'];
        $myTable->add_cell(new TABLE_cell(number_format($mnt_annuel,0,'.',','), 1, 1));
      }
      if ($typeValidation == 2){
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'valid_".($i+1)."'  unchecked='true' />", 1, 1));
      }

      //creation session pour garder certains informations des lignes budgetaires
      if ($typeValidation != 2){ //raffinement budget
        $SESSION_VARS['ligne_budgetaire'][$i+1]['ligne_budgetaire']=($i+1);
        $SESSION_VARS['ligne_budgetaire'][$i+1]['ref_budget']=$tabBudget[$i]['ref_budget'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['type_budget']=$typeBudget;
        $SESSION_VARS['ligne_budgetaire'][$i+1]['id_correspondance']=$tabBudget[$i]['id_correspondance'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['poste']=$tabBudget[$i]['poste'];
      }
      else{ //revision budget
        //Recupere le trimestre courant
        $trimestre = getTrimestre();

        $SESSION_VARS['ligne_budgetaire'][$i+1]['ligne_budgetaire']=($i+1);
        $SESSION_VARS['ligne_budgetaire'][$i+1]['ref_budget']=$tabBudget[$i]['ref_budget'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['type_budget']=$typeBudget;
        $SESSION_VARS['ligne_budgetaire'][$i+1]['id_correspondance']=$tabBudget[$i]['id_correspondance'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['id_ligne_budgetaire']=$tabBudget[$i]['id_ligne'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['poste']=$tabBudget[$i]['poste'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['description']=$tabBudget[$i]['description'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['compartiment']=$tabBudget[$i]['compartiment'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['cpte_correspondance']=$tabBudget[$i]['cpte_correspondance'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['etat_bloque']=$tabBudget[$i]['etat_bloque'];
        $isTrimestre1Open = 'f';
        if ($trimestre<=1){
          $isTrimestre1Open = 't';
        }
        $SESSION_VARS['ligne_budgetaire'][$i+1]['isTrimestre1Open'] = $isTrimestre1Open;
        $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget1']=$tabBudget[$i]['mnt_trim1'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['prc_utilisation_trim1']=$tabBudget[$i]['prc_utilisation_trim1'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['nouv_mnt_trim1']=$tabBudget[$i]['nouv_mnt_trim1'];
        $isTrimestre2Open = 'f';
        if ($trimestre<=2){
          $isTrimestre2Open = 't';
        }
        $SESSION_VARS['ligne_budgetaire'][$i+1]['isTrimestre2Open'] = $isTrimestre2Open;
        $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget2']=$tabBudget[$i]['mnt_trim2'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['prc_utilisation_trim2']=$tabBudget[$i]['prc_utilisation_trim2'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['nouv_mnt_trim2']=$tabBudget[$i]['nouv_mnt_trim2'];
        $isTrimestre3Open = 'f';
        if ($trimestre<=3){
          $isTrimestre3Open = 't';
        }
        $SESSION_VARS['ligne_budgetaire'][$i+1]['isTrimestre3Open'] = $isTrimestre3Open;
        $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget3']=$tabBudget[$i]['mnt_trim3'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['prc_utilisation_trim3']=$tabBudget[$i]['prc_utilisation_trim3'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['nouv_mnt_trim3']=$tabBudget[$i]['nouv_mnt_trim3'];
        $isTrimestre4Open = 'f';
        if ($trimestre<=4){
          $isTrimestre4Open = 't';
        }
        $SESSION_VARS['ligne_budgetaire'][$i+1]['isTrimestre4Open'] = $isTrimestre4Open;
        $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget4']=$tabBudget[$i]['mnt_trim4'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['prc_utilisation_trim4']=$tabBudget[$i]['prc_utilisation_trim4'];
        $SESSION_VARS['ligne_budgetaire'][$i+1]['nouv_mnt_trim4']=$tabBudget[$i]['nouv_mnt_trim4'];
        $SESSION_VARS['isRevision']='t';
      }
    }

    //sauvegarder le nom de l'écran et le nom du bouton  pour le prochain ecran confirmation 'Cmb-1'
    $myForm->addHiddenType("ecran_prec", 'Vlb-2');
    $myForm->addHiddenType("bouton_enreg", 'valider');
    $SESSION_VARS['type_budget'] = $typeBudget;
    if (isset($type_validation)){
      $SESSION_VARS['type_validation'] = $type_validation;
    }

    //Les boutons de navigation/operation
    $myForm->addFormButton(1, 1, "valider", _("Valider Budget"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "prec", _("Precedent"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 4, "annul", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Cmb-1');
    $myForm->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, 'Vlb-1');
    $myForm->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, 'Gen-15');

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
  else{
    $erreur = new HTML_erreur(_("Validation $titre_validation du Budget : ".$adsys["adsys_type_budget"][$typeBudget]));
    $erreur->setMessage(_("Aucun table de correspondance associe a ce type de budget ou aucune validation a faire!!"));
    $erreur->addButton(BUTTON_OK,"Vlb-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }

  //********************************************Fin Ecran Validation Budget*******************************************//
}
/*{{{ Vnl-1 : Choix du type budget -> Validation Nouvelle Ligne Budgetaire */
else if ($global_nom_ecran == "Vnl-1"){
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
  $myForm->setFieldProperties("id_exo", FIELDP_JS_EVENT, array("onChange"=>"assign('Vnl-1'); this.form.submit();"));
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
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Vnl-2');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*{{{ Vnl-2 : Verification Donnees et Choix ligne a valider  -> Validation Nouvelle Ligne Budgetaire */
else if ($global_nom_ecran == "Vnl-2"){

  $typeBudget = $type_budget;
  if (isset($SESSION_VARS['type_budget'])){
    $typeBudget = $SESSION_VARS['type_budget'];
  }

  //Recupere les donnees du tableau ligne budgetaire qui est associé au type de budget et id exercice en parametre
  $exo_encours= getExoEnCours();
  $tabBudget = getValidationLigneBudgetaire($typeBudget,$id_exo,">= 3");

  //Si != null, c'est que on a des donnees budget pour ce type de budget
  if ($tabBudget != null){
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Validation Nouvelle Ligne Budgetaire : ".$adsys["adsys_type_budget"][$typeBudget]));

    $myTableNumCol = 12; //le nombre de colonnes
    $myTableNumRowSpan = 2; //Row span

    $myTable =& $myForm->addHTMLTable("plan_comptable", $myTableNumCol, TABLE_STYLE_ALTERN);
    $myTable->add_cell(new TABLE_cell(_("Ligne Correspondance"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Poste"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Bloque Compte?"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Description"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Compartiment"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Comptes Comptable"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Montant Budget"), 5, 1));
    $myTable->add_cell(new TABLE_cell(_("Valider?"), 1, $myTableNumRowSpan));
    $myTable->add_cell(new TABLE_cell(_("Trimestre 1"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Trimestre 2"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Trimestre 3"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Trimestre 4"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Annuel"), 1, 1));

    //Recupere le nombre des lignes dans l'array budget
    $sizeBudget = sizeof($tabBudget);

    if (isset($SESSION_VARS['ligne_budgetaire'])){
      unset($SESSION_VARS['ligne_budgetaire']);
    }

    //On loop dans l'array budget pour creer le tableau pour la validation du raffinement/revision budget
    for ($i = 0; $i < $sizeBudget; $i++) {
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['id_correspondance'], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['poste_budget'], 1, 1));
      if ($tabBudget[$i]['etat_bloque']=='t'){
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  checked='true' />", 1, 1));
      }
      else{
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  unchecked='true' />", 1, 1));
      }
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['description'], 1, 1));
      $myTable->add_cell(new TABLE_cell($adsys["adsys_compartiment_comptable"][$tabBudget[$i]['compartiment']], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['cpte_correspondance'], 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim1'],0,'.',','), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim2'],0,'.',','), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim3'],0,'.',','), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim4'],0,'.',','), 1, 1));
      $mnt_annuel = $tabBudget[$i]['mnt_trim1'] + $tabBudget[$i]['mnt_trim2'] + $tabBudget[$i]['mnt_trim3'] + $tabBudget[$i]['mnt_trim4'];
      $myTable->add_cell(new TABLE_cell(number_format($mnt_annuel,0,'.',','), 1, 1));
      $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'valid_".($i+1)."'  unchecked='true' />", 1, 1));

      //creation session pour garder certains informations des lignes budgetaires
      $SESSION_VARS['ligne_budgetaire'][$i+1]['ligne_budgetaire']=($i+1);
      $SESSION_VARS['ligne_budgetaire'][$i+1]['ref_budget']=$tabBudget[$i]['ref_budget'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['type_budget']=$typeBudget;
      $SESSION_VARS['ligne_budgetaire'][$i+1]['id_correspondance']=$tabBudget[$i]['id_correspondance'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['id_ligne_budgetaire']=$tabBudget[$i]['id_ligne'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['poste']=$tabBudget[$i]['poste_budget'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['description']=$tabBudget[$i]['description'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['compartiment']=$tabBudget[$i]['compartiment'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['cpte_correspondance']=$tabBudget[$i]['cpte_correspondance'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['etat_bloque']=$tabBudget[$i]['etat_bloque'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget1']=$tabBudget[$i]['mnt_trim1'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget2']=$tabBudget[$i]['mnt_trim2'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget3']=$tabBudget[$i]['mnt_trim3'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget4']=$tabBudget[$i]['mnt_trim4'];
    }

    //sauvegarder le nom de l'écran et le nom du bouton  pour le prochain ecran confirmation 'Cmb-1'
    $myForm->addHiddenType("ecran_prec", 'Vnl-2');
    $myForm->addHiddenType("bouton_enreg", 'valider');
    $SESSION_VARS['type_budget'] = $typeBudget;

    //Les boutons de navigation/operation
    $myForm->addFormButton(1, 1, "valider", _("Valider Ligne(s)"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "prec", _("Precedent"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 4, "annul", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 'Cmb-1');
    $myForm->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, 'Vnl-1');
    $myForm->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, 'Gen-15');

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
  else{
    $erreur = new HTML_erreur(_("Validation Nouvelle Ligne Budgetaire : ".$adsys["adsys_type_budget"][$typeBudget]));
    $erreur->setMessage(_("Aucun table de correspondance associe a ce type de budget ou aucune validation a faire!!"));
    $erreur->addButton(BUTTON_OK,"Vnl-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }
}