<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Batch : fonctions spécifiques au traitement du budget
 * @package Systeme
 **/

require_once 'lib/dbProcedures/historique.php';

function calculPourcentage(){
  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();

  $date = php2pg(date("d/m/Y"));

  $sql = "SELECT * from CalculUtilisationBudget('$date');";

  $result_calcul= $db->query($sql);
  if (DB::isError($result_calcul)) {
    $dbHandler->closeConnection(false);
    erreur("erreur query", $result_calcul->getUserInfo());
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}
function blockCompteBudget(){
  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();

  $date = php2pg(date("d/m/Y"));

  $sql = "SELECT * from BlockCompteBudget('$date');";

  $result_block= $db->query($sql);
  if (DB::isError($result_block)) {
    $dbHandler->closeConnection(false);
    erreur("erreur query", $result_block->getUserInfo());
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

//return new ErrorObj(NO_ERR);

function traite_budget(){

  affiche(_("Démarre le calcul du budget ..."));
  incLevel();

  calculPourcentage();

  blockCompteBudget();

  decLevel();
  affiche(_("Calcul du budget terniné"));
}

?>