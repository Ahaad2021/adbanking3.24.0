<?php
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/misc/Erreur.php';
function coherenceComptaComptesInternesCredit($date) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql ="SELECT * FROM compare_compta_cpte_interne_credit (date('$date')); ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_DB_SQL, $result->getUserinfo());
  }

  $tab_Comptes = array();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
   $tab_Comptes [] = $tmprow;
  }
  $dbHandler->closeConnection(TRUE);
  return new ErrorObj(NO_ERR, $tab_Comptes);
}

function coherenceDossierCreditComptesInternesCredit($date) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql ="SELECT * FROM compare_credit_cpte_interne (date('$date')); ";
   $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    return new ErrorObj(ERR_DB_SQL, $result->getUserinfo());
  }

  $tab_Comptes = array();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
   $tab_Comptes [] = $tmprow;
  }
  $dbHandler->closeConnection(TRUE);
  return new ErrorObj(NO_ERR, $tab_Comptes);
}

/**
 * Permet la verification de l'equilibre inventaire/comptabilite.
 * Consulter le fonction postgres
 * @author b&d 
 * @return ErrorObj
 */
function coherenceInventaireCompta($id_his) 
{
	global $dbHandler, $global_nom_login;
	$db = $dbHandler->openConnection();
	
	$sql ="SELECT verification_equilibre_comptable_lot('$global_nom_login', $id_his); ";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		return new ErrorObj(ERR_DB_SQL, $result->getUserinfo());
	}
	
	$counter = $result->fetchrow();	
	$dbHandler->closeConnection(TRUE);
	return new ErrorObj(NO_ERR, $counter);
}
