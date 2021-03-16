<?php
/**
 * Created by PhpStorm.
 * User: Roshan/Ahaad
 * Date: 10/26/2017
 * Time: 1:24 PM
 */
require_once 'lib/misc/xml_lib.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/dbProcedures/budget.php';

/**
 * Fonction pour generer le xml pour le rapport revision budgetaire historique
 *PARAM : type budget, exercice budget, date rapport, period et isCsv
 *RETURN : xml
 */
function xml_revision_historique_budget($type_budget, $exo_budget, $date_rapport, $period=null, $isCsv=false){
  global $adsys;

  $document = create_xml_doc("budget_revisionhistorique", "budget_revisionhistorique.dtd");

  $data = getDataRevisionHistorique($type_budget, $exo_budget, $date_rapport, $period);

  if ($data == null){
    return null;
  }

  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'BGT-RHB');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");

  //Affichage Critere de Recherche
  if ($type_budget <= 0){
    $crit_type_budget = _('Tous');
  }
  else{
    $crit_type_budget = $adsys["adsys_type_budget"][$type_budget];
  }
  if ($period != null){
    $crit_period = _('Trimestre '.$period);
  }
  else{
    $crit_period = _('Annuel');
  }
  $exercice = getExoDefini($exo_budget);//REL-104 : Ajout param id exo - correction Pour le rapport “historique de
  // révision budgétaire”, champ ‘Exercice' est de 2020 alors que le budget revisé est celui de 2019
  $annee_exercice = '';
  $year_exo_deb = explode("-",$exercice['date_deb_exo']);
  $year_exo_fin = explode("-",$exercice['date_fin_exo']);
  $annee_exercice = $year_exo_fin[0];
  if ($year_exo_deb[0] != $year_exo_fin[0]){
    $annee_exercice = $year_exo_deb[0]." - ".$year_exo_fin[0];
  }
  //$date_exo = explode("-",$exercice['date_fin_exo']);

  $criteres = array (
    _("Type de budget") => $crit_type_budget,
    _("Exercice Comptable") => $annee_exercice,
    _("Periode") => $crit_period,
    _("Date du Rapport") => date($date_rapport)
  );

  gen_criteres_recherche($header_contextuel, $criteres);

  $list_revision = $root->new_child("list_revision", "");
  foreach($data as $type => $typ_budget){
    $list_budget = $list_revision->new_child("list_budget", "");
    $type_budget_ = $list_budget->new_child("type_budget", $adsys["adsys_type_budget"][$type]);
    foreach($typ_budget as $val => $per){
      $list_period = $list_budget->new_child("list_period", "");
      $period_ = $list_period->new_child("period", "Trimestre ".$val);
      foreach($per as $ligne => $ligne_rev){
        $ligne_revision = $list_period->new_child("ligne_revision", "");
        $date_revision = $ligne_revision->new_child("date_revision", pg2phpDate($ligne_rev['date_revision']));
        $ligne_budget = $ligne_revision->new_child("ligne_budget", $ligne_rev['description']);
        $login_revise = $ligne_revision->new_child("login_revise", $ligne_rev['login_revise']);
        $login_valide = $ligne_revision->new_child("login_valide", $ligne_rev['login_valide']);
        $anc_montant = $ligne_revision->new_child("anc_montant", afficheMontant($ligne_rev['anc_montant']),false,$isCsv);
        $nouv_montant = $ligne_revision->new_child("nouv_montant", afficheMontant($ligne_rev['nouv_montant']),false,$isCsv);
        $variation = $ligne_revision->new_child("variation", afficheMontant($ligne_rev['variation']),false,$isCsv);
      }
    }
  }

  return $document->dump_mem(true);

}

/**
 * Fonction qui genere le xml pour le rapport sur les etats d'execution budgetaire
 *PARAM : type budget, date debut trimestre et date fin
 *RETURN : array of data $DATA
 */

function xml_etat_execution_budgetaire($criteres,$type_budget, $exo_budget, $date_debut_trim, $date_fin, $devise, $isCsv=false){

  $document = create_xml_doc("budget_etatbudgetaire", "budget_etatbudgetaire.dtd");
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'BGT-EEB');
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $criteres);

  $info = $root->new_child("infos_synthetiques", $devise);

  $DATA = getDataRapportEtatExecutionBudgetaire($type_budget, $exo_budget, $date_debut_trim, $date_fin);
  $info_etat = $root->new_child("infos_etat", "");
  $return_null = true;
  foreach ($DATA as $type_budget) {
    $type_budget_rapport= $info_etat->new_child("type_budget", "");
    foreach ($type_budget as $poste) {
      //REL-104 - correction Pour le rapport d’execution budgetaire et rapport budget, si aucun budget n’a été mis en place,
      // il faudrait donner l’arte disant qu’aucun budget n’est disponible au lieu d’afficher une page blanche ou rapport avec valeurs zero
      // si $return_null = false -> le rapport a des données à afficher
      if ($poste['budget_annuel'] != 0 || $poste['budget_periode'] != 0 || $poste['realisation_period'] != 0 || $poste['performance_period'] != 0 || $poste['performance_annuelle'] != 0) {
        $return_null = false;
      }
      $poste_budget = $type_budget_rapport->new_child("details", "");
      $post = $poste_budget->new_child("poste", $poste['poste']);
      $niveau = $poste_budget->new_child("niveau", $poste['niveau']);
      $description = $poste_budget->new_child("description", $poste['description']);
      $budget_annuel = $poste_budget->new_child("budget_annuel", afficheMontant($poste['budget_annuel'],false,$isCsv));
      $budget_periode = $poste_budget->new_child("budget_periode", afficheMontant($poste['budget_periode'],false,$isCsv));
      $realisation_period = $poste_budget->new_child("realisation_period", afficheMontant($poste['realisation_period'],false,$isCsv));
      $performance_period = $poste_budget->new_child("performance_period", afficheMontant($poste['performance_period'],false,$isCsv));
      $performance_annuelle = $poste_budget->new_child("performance_annuelle", afficheMontant($poste['performance_annuelle'],false,$isCsv));
    }
  }
  if ($return_null === true){
    return null;
  }

  return $document->dump_mem(true);
}

function xml_rapport_budget($criteres,$type_budget, $exo_budget, $date_debut, $date_fin,$devise, $isCsv=false){

  $document = create_xml_doc("budget_rapportbudget", "budget_rapportbudget.dtd");
  $root = $document->root();

  /*$info_sup = array (
    "Devise" => _($devise)
  );*/
  //En-tête généraliste
  gen_header($root, 'BGT-RAB');
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $criteres);

  $info = $root->new_child("infos_synthetiques", $devise);


  $DATA = getDataRapportBudget($type_budget, $exo_budget, $date_debut, $date_fin);
  if ($DATA == null){ //REL-104 - Correction Pour le rapport d’execution budgetaire et rapport budget, si aucun budget
    // n’a été mis en place, il faudrait donner l’arte disant qu’aucun budget n’est disponible au lieu d’afficher une page
    // blanche ou rapport avec valeurs zero
    return null;
  }
  $info_etat = $root->new_child("infos_etat", "");
  foreach ($DATA as $type_budget) {
    $type_budget_rapport= $info_etat->new_child("type_budget", "");
    foreach ($type_budget as $poste) {
      $poste_budget = $type_budget_rapport->new_child("details", "");
      $post = $poste_budget->new_child("poste", $poste['poste']);
      $niveau = $poste_budget->new_child("niveau", $poste['niveau']);
      $description = $poste_budget->new_child("description", $poste['description']);
      $trim_1 = $poste_budget->new_child("trim_1", afficheMontant($poste['trim1'],false,$isCsv));
      $trim_2 = $poste_budget->new_child("trim_2", afficheMontant($poste['trim2'],false,$isCsv));
      $trim_3 = $poste_budget->new_child("trim_3", afficheMontant($poste['trim3'],false,$isCsv));
      $trim_4 = $poste_budget->new_child("trim_4", afficheMontant($poste['trim4'],false ,$isCsv));
      $budget_annuel = $poste_budget->new_child("budget_annuel", afficheMontant($poste['bud_annuel'],false,$isCsv));
    }
  }
  return $document->dump_mem(true);
}
?>