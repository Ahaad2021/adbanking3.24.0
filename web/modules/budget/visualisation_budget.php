<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 10/3/2017
 * Time: 10:30 AM
 */
require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/budget.php');
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/html/FILL_HTML_GEN2.php';

/*{{{ Vdb-1 : Choix du type budget */
if ($global_nom_ecran == "Vdb-1") {
  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Saisie du Exercice/Type de budget"));

  $exo_encours = getExoEnCoursAll();
  $exo_encours_all = array();
  if ($exo_encours != null){
    foreach($exo_encours as $key => $value){
      $exo_encours_all[$key] = $value["debut_annee"]." - ".$value["debut_annee"];
    }
  }
  //$liste_type_budget = getAllExoOuvertWithBudgetAvailable(" > 2", true);
  $liste_type_budget = getDataBudget($id_exo," > 2");

  $myForm->addField("id_exo",_("Exercice(s) en cours"), TYPC_LSB);
  $myForm->setFieldProperties('id_exo', FIELDP_ADD_CHOICES, $exo_encours_all);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("id_exo", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("id_exo", FIELDP_JS_EVENT, array("onChange"=>"assign('Vdb-1'); this.form.submit();"));
  if (isset($id_exo)){
    $myForm->setFieldProperties("id_exo", FIELDP_DEFAULT, $id_exo);
  }

  if (isset($id_exo) && $id_exo != null ){
    $liste_type_budget = getDataBudget($id_exo," > 2");
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
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Vdb-2');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
/*{{{ Vdb-2 : Visualisation Données */
else if ($global_nom_ecran == "Vdb-2") {

  //Recupere les donnees du tableau budget qui est associé au type de budget en parametre
  $tabBudget = getTabBudget($type_budget,$id_exo);

  //Si != null, c'est que on a des donnees budget pour ce type de budget
  if ($tabBudget != null){
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Visualisation du Budget Annuel : ".$adsys["adsys_type_budget"][$type_budget]));

    $myTable =& $myForm->addHTMLTable("plan_comptable", 10, TABLE_STYLE_ALTERN);
    //$myTable->add_cell(new TABLE_cell(_("Correspondance"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Poste"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Description"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Compartiment"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Comptes Comptables"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Bloque Compte?"), 1, 2));
    $myTable->add_cell(new TABLE_cell(_("Montant Budget"), 5, 1));
    $myTable->add_cell(new TABLE_cell(_("Trimestre 1"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Trimestre 2"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Trimestre 3"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Trimestre 4"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Annuel"), 1, 1));

    //Recupere le nombre des lignes dans l'array budget
    $sizeBudget = sizeof($tabBudget);
    $poste = -1;

    //On loop dans l'array budget pour creer le tableau pour la visualisation du budget
    for ($i = 0; $i < $sizeBudget; $i++) {
      //$myTable->add_cell(new TABLE_cell($tabBudget[$i]['id_correspondance'], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['poste'], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['description'], 1, 1));
      $myTable->add_cell(new TABLE_cell($adsys["adsys_compartiment_comptable"][$tabBudget[$i]['compartiment']], 1, 1));
      $myTable->add_cell(new TABLE_cell($tabBudget[$i]['cpte_correspondance'], 1, 1));
      if ($tabBudget[$i]['etat_bloque']=='t'){
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  checked='true' disabled />", 1, 1));
      }
      else{
        $myTable->add_cell(new TABLE_cell("<input type = 'checkbox' name = 'etat_bloque_".($i+1)."'  unchecked='true' disabled />", 1, 1));
      }
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim1'],0,' ',','), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim2'],0,' ',','), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim3'],0,' ',','), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_trim4'],0,' ',','), 1, 1));
      $myTable->add_cell(new TABLE_cell(number_format($tabBudget[$i]['mnt_annuel'],0,' ',','), 1, 1));
    }

    //Les boutons de navigation
    $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "ret", _("Retour"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gen-15');
    $myForm->setFormButtonProperties("ret", BUTP_PROCHAIN_ECRAN, 'Gen-15');

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
  else{
    $erreur = new HTML_erreur(_("Visualisation du Budget Annuel : ".$adsys["adsys_type_budget"][$type_budget]));
    $erreur->setMessage(_("Aucun table de correspondance associe a ce type de budget!!"));
    $erreur->addButton(BUTTON_OK,"Vdb-1");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
  }

}