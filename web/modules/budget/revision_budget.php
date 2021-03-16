<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 10/9/2017
 * Time: 2:45 PM
 */
require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/budget.php');
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/html/FILL_HTML_GEN2.php';

/*{{{ Rdb-1 : Choix du type budget */
if ($global_nom_ecran == "Rdb-1") {
  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Saisie du type de budget"));

  $exo_encours = getExoEnCoursAll();
  $exo_encours_all = array();
  if ($exo_encours != null){
    foreach($exo_encours as $key => $value){
      $exo_encours_all[$key] = $value["debut_annee"]." - ".$value["debut_annee"];
    }
  }

  $myForm->addField("id_exo",_("Exercice(s) en cours"), TYPC_LSB);
  $myForm->setFieldProperties('id_exo', FIELDP_ADD_CHOICES, $exo_encours_all);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("id_exo", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("id_exo", FIELDP_JS_EVENT, array("onChange"=>"assign('Rdb-1'); this.form.submit();"));
  if (isset($id_exo)){
    $myForm->setFieldProperties("id_exo", FIELDP_DEFAULT, $id_exo);
  }

  if (isset($id_exo) && $id_exo != null ){
    $liste_type_budget = getDataBudget($id_exo," >= 3");
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
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rdb-2');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*{{{ Rdb-2 : Selection revision*/
else if ($global_nom_ecran == "Rdb-2") {

  //Recupere les donnees du tableau budget qui est associé au type de budget et id exercice en parametre
  //$exo_encours= getExoEnCours();
  if(isset($id_exo)){
    $SESSION_VARS['id_exo'] = $id_exo;
  }
  $typeBudget = $type_budget;
  if (isset($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['type_budget'])){
    $typeBudget = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['type_budget'];
  }
  $tabBudget = getTabRevisionBudget($typeBudget,$SESSION_VARS['id_exo']," IN (3,4,5)");

  //Si != null, c'est que on a des donnees budget pour ce type de budget
  if ($tabBudget != null){
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Reviser le Budget : ".$adsys["adsys_type_budget"][$typeBudget]));

    $myTable =& $myForm->addHTMLTable("plan_comptable", 16, TABLE_STYLE_ALTERN);
    $myTable->add_cell(new TABLE_cell(_("Poste"), 1, 3));
    $myTable->add_cell(new TABLE_cell(_("Compartiment"), 1, 3));
    $myTable->add_cell(new TABLE_cell(_("Comptes Comptable"), 1, 3));
    //$myTable->add_cell(new TABLE_cell(_("Bloque Compte?"), 1, 3));
    $myTable->add_cell(new TABLE_cell(_("Budget Par Trimestre"), 12, 1));
    $myTable->add_cell(new TABLE_cell(_("Action"), 1, 3));
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

    //Recupere le nombre des lignes dans l'array budget
    $sizeBudget = sizeof($tabBudget);
    $poste = -1;

    //On loop dans l'array budget pour creer le tableau pour la revision du budget
    for ($i = 0; $i < $sizeBudget; $i++) {
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['poste'], 1, 1));
      $myTable->add_cell(new TABLE_cell($adsys["adsys_compartiment_comptable"][$tabBudget[$i]['compartiment']], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['cpte_comptable'], 1, 1));
      /*if ($tabBudget[$i]['etat_bloque']=='t'){
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  checked='true' disabled />", 1, 1));
      }
      else{
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  unchecked='true' disabled />", 1, 1));
      }*/
      $myTable->add_cell(new TABLE_cell("<b>".number_format($tabBudget[$i]['mnt_budget1'],0,'.',',')."</b>", 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['prc_utilisation_trim1'],2,'.',' '), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['nouv_mnt_trim1'],0,'.',','), 1, 1));
      $myTable->add_cell(new TABLE_cell("<b>".number_format($tabBudget[$i]['mnt_budget2'],0,'.',',')."</b>", 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['prc_utilisation_trim2'],2,'.',' '), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['nouv_mnt_trim2'],0,'.',','), 1, 1));
      $myTable->add_cell(new TABLE_cell("<b>".number_format($tabBudget[$i]['mnt_budget3'],0,'.',',')."</b>", 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['prc_utilisation_trim3'],2,'.',' '), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['nouv_mnt_trim3'],0,'.',','), 1, 1));
      $myTable->add_cell(new TABLE_cell("<b>".number_format($tabBudget[$i]['mnt_budget4'],0,'.',',')."</b>", 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['prc_utilisation_trim4'],2,'.',' '), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['nouv_mnt_trim4'],0,'.',','), 1, 1));
      $myTable->add_cell(new TABLE_cell_link("<b>"._("Reviser")."</b>","$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Rdb-3&ligne_budgetaire=".($i+1)."&id_correspondance=".$tabBudget[$i]['id_correspondance']."&ecran_prec=Rdb-2"), 1, 1);

      //creation session pour garder certains informations des lignes budgetaires
      $SESSION_VARS['ligne_budgetaire'][$i+1]['ligne_budgetaire']=($i+1);
      $SESSION_VARS['ligne_budgetaire'][$i+1]['ref_budget']=$tabBudget[$i]['ref_budget'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['type_budget']=$typeBudget;
      $SESSION_VARS['ligne_budgetaire'][$i+1]['id_correspondance']=$tabBudget[$i]['id_correspondance'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['id_ligne_budgetaire']=$tabBudget[$i]['id_ligne'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['poste']=$tabBudget[$i]['poste'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['description']=$tabBudget[$i]['description'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['compartiment']=$tabBudget[$i]['compartiment'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['cpte_correspondance']=$tabBudget[$i]['cpte_comptable'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['etat_bloque']=$tabBudget[$i]['etat_bloque'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget1']=$tabBudget[$i]['mnt_budget1'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['prc_utilisation_trim1']=$tabBudget[$i]['prc_utilisation_trim1'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['nouv_mnt_trim1']=$tabBudget[$i]['nouv_mnt_trim1'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget2']=$tabBudget[$i]['mnt_budget2'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['prc_utilisation_trim2']=$tabBudget[$i]['prc_utilisation_trim2'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['nouv_mnt_trim2']=$tabBudget[$i]['nouv_mnt_trim2'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget3']=$tabBudget[$i]['mnt_budget3'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['prc_utilisation_trim3']=$tabBudget[$i]['prc_utilisation_trim3'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['nouv_mnt_trim3']=$tabBudget[$i]['nouv_mnt_trim3'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['mnt_budget4']=$tabBudget[$i]['mnt_budget4'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['prc_utilisation_trim4']=$tabBudget[$i]['prc_utilisation_trim4'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['nouv_mnt_trim4']=$tabBudget[$i]['nouv_mnt_trim4'];
      $SESSION_VARS['ligne_budgetaire'][$i+1]['hasRevisions']=$tabBudget[$i]['hasrevision'];
    }

    //sauvegarder le nom de l'écran et le nom du bouton  pour le prochain ecran confirmation 'Cmb-1'
    $myForm->addHiddenType("ecran_prec", 'Rdb-3');
    $myForm->addHiddenType("bouton_enreg", 'enreg');


    //Les boutons de navigation/operation
    $myForm->addFormButton(1, 1, "prec", _("Precedent"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, 'Rdb-1');
    $myForm->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, 'Gen-15');

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
  else{
    $erreur = new HTML_erreur(_("Reviser le Budget : ".$adsys["adsys_type_budget"][$type_budget]));
    $erreur->setMessage(_("Aucun table de correspondance associe a ce type de budget ou aucun budget a ete raffiné!!"));
    $erreur->addButton(BUTTON_OK,"Rdb-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }

}
/*{{{ Rdb-3 : Saisie Données Révisés*/
else if ($global_nom_ecran == "Rdb-3") {
  $nouveau_mnt_budget_trim1 = "";
  $nouveau_mnt_budget_trim2 = "";
  $nouveau_mnt_budget_trim3 = "";
  $nouveau_mnt_budget_trim4 = "";

  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Reviser le Budget : Effectuer la revision du ".$adsys["adsys_type_budget"][$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['type_budget']]));

  //Tableau et formulaire pour la revision budgetaire
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

  $myForm->addField("description",_("Description de la ligne budgetaire"), TYPC_TXT);
  $myForm->setFieldProperties("description",FIELDP_DEFAULT,$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['description']);
  $myForm->setFieldProperties("description", FIELDP_IS_LABEL, true);

  $myForm->addField("compartiment",_("Compartiment"), TYPC_TXT);
  $myForm->setFieldProperties("compartiment",FIELDP_DEFAULT,$adsys["adsys_compartiment_comptable"][$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['compartiment']]);
  $myForm->setFieldProperties("compartiment", FIELDP_IS_LABEL, true);

  $myForm->addField("cpte_correspondance",_("Compte Correspondance"), TYPC_TXT);
  $myForm->setFieldProperties("cpte_correspondance",FIELDP_DEFAULT,$SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['cpte_correspondance']);
  $myForm->setFieldProperties("cpte_correspondance", FIELDP_IS_LABEL, true);

  $myForm->addField("etat_bloque",_("Bloque Compte si on a une depassement budgetaire?"), TYPC_BOL);
  $etat_bloc = false;
  if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['etat_bloque'] == 't'){
    $etat_bloc = true;
  }
  $myForm->setFieldProperties("etat_bloque",FIELDP_DEFAULT,$etat_bloc);

  $myTable =& $myForm->addHTMLTable("plan_comptable", 12, TABLE_STYLE_ALTERN);
  $myTable->add_cell(new TABLE_cell(_("Trimestre 1"), 3, 1));
  $myTable->add_cell(new TABLE_cell(_("Trimestre 2"), 3, 1));
  $myTable->add_cell(new TABLE_cell(_("Trimestre 3"), 3, 1));
  $myTable->add_cell(new TABLE_cell(_("Trimestre 4"), 3, 1));
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

  //Recupere le trimestre courant
  $trimestre = getTrimestre($SESSION_VARS['id_exo']);

  //Trimestre 1
  $myTable->add_cell(new TABLE_cell(number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['mnt_budget1'],0,' ',','), 1, 1));
  $myTable->add_cell(new TABLE_cell(number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['prc_utilisation_trim1'],2,'.',' '), 1, 1));
  //REL-104 : ajout condition trimestre > 0
  // Correction - Pour la révisions budgetaire, la logique de départ était de ne pas permettre la révision budgétaire
  // pour le trimestre ecoulé, cependant, pour la base de données ci-jointe, j’arrive à faire la révision budgétaire
  // pour tous les trimestres du budget 2019 alors que pour cette base on est le 04 Janvier 2020
  if ($trimestre > 0 && $trimestre <= 1){
    if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim1']!=0 && $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim1']!=null){
      $nouveau_mnt_budget_trim1 = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim1'];
    }
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim1", number_format($nouveau_mnt_budget_trim1,0,'.',' '), "value = formateMontant(value);", "", false, "size='12'"));
    $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre1Open'] = 't';
  }
  else{
    if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim1']!=0 && $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim1']!=null){
      $nouveau_mnt_budget_trim1 = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim1'];
    }
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim1", number_format($nouveau_mnt_budget_trim1,0,'.',','), "value = formateMontant(value);", "", false, "size='12'; readonly='true'"));
    $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre1Open'] = 'f';
  }
  //Trimestre 2
  $myTable->add_cell(new TABLE_cell(number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['mnt_budget2'],0,' ',','), 1, 1));
  $myTable->add_cell(new TABLE_cell(number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['prc_utilisation_trim2'],2,'.',' '), 1, 1));
  if ($trimestre > 0 && $trimestre <= 2){
    if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim2']!=0 && $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim2']!=null){
      $nouveau_mnt_budget_trim2 = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim2'];
    }
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim2", number_format($nouveau_mnt_budget_trim2,0,'.',' '), "value = formateMontant(value);", "", false, "size='12'"));
    $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre2Open'] = 't';
  }
  else{
    if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim2']!=0 && $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim2']!=null){
      $nouveau_mnt_budget_trim2 = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim2'];
    }
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim2", number_format($nouveau_mnt_budget_trim2,0,'.',','), "value = formateMontant(value);", "", false, "size='12'; readonly='true'"));
    $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre2Open'] = 'f';
  }
  //Trimestre 3
  $myTable->add_cell(new TABLE_cell(number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['mnt_budget3'],0,' ',','), 1, 1));
  $myTable->add_cell(new TABLE_cell(number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['prc_utilisation_trim3'],2,'.',' '), 1, 1));
  if ($trimestre > 0 && $trimestre <= 3){
    if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim3']!=0 && $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim3']!=null){
      $nouveau_mnt_budget_trim3 = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim3'];
    }
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim3", number_format($nouveau_mnt_budget_trim3,0,'.',' '), "value = formateMontant(value);", "", false, "size='12'"));
    $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre3Open'] = 't';
  }
  else{
    if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim3']!=0 && $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim3']!=null){
      $nouveau_mnt_budget_trim3 = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim3'];
    }
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim3", number_format($nouveau_mnt_budget_trim3,0,'.',','), "value = formateMontant(value);", "", false, "size='12'; readonly='true'"));
    $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre3Open'] = 'f';
  }
  //Trimestre 4
  $myTable->add_cell(new TABLE_cell(number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['mnt_budget4'],0,' ',','), 1, 1));
  $myTable->add_cell(new TABLE_cell(number_format($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['prc_utilisation_trim4'],2,'.',' '), 1, 1));
  if ($trimestre > 0 && $trimestre <= 4){
    if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim4']!=0 && $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim4']!=null){
      $nouveau_mnt_budget_trim4 = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim4'];
    }
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim4", number_format($nouveau_mnt_budget_trim4,0,'.',' '), "value = formateMontant(value);", "", false, "size='12'"));
    $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre4Open'] = 't';
  }
  else{
    if ($SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim4']!=0 && $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim4']!=null){
      $nouveau_mnt_budget_trim4 = $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['nouv_mnt_trim4'];
    }
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "mnt_trim4", number_format($nouveau_mnt_budget_trim4,0,'.',','), "value = formateMontant(value);", "", false, "size='12'; readonly='true'"));
    $SESSION_VARS['ligne_budgetaire'][$ligne_budgetaire]['isTrimestre4Open'] = 'f';
  }

  //sauvegarder le nom de l'écran et le nom du bouton  pour le prochain ecran confirmation 'Cmb-1'
  $myForm->addHiddenType("ecran_prec", 'Rdb-3');
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
  $myForm->setFormButtonProperties("prec", BUTP_PROCHAIN_ECRAN, 'Rdb-2');
  $myForm->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, 'Gen-15');

  $myForm->buildHTML();
  echo $myForm->getHTML();
}