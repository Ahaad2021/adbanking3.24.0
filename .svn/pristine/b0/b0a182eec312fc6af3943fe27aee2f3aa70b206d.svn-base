<?php

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/compte.php';

/*
function buildBilletsVect() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id, valeur as libel, valeur FROM adsys_types_billets WHERE id_ag=$global_id_agence ";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $billet = array();
  $x = 0;
  
  while ($tmprow = $result->fetchrow()) {
    $billet[$x]['libel'] = $tmprow[1];
    $billet[$x]['valeur'] = $tmprow[2];
    $x++;
  }
  $dbHandler->closeConnection(TRUE);
  return $billet;
}
*/

function buildBilletsVect($dev) {
	global $dbHandler,$global_id_agence;
	$db = $dbHandler->openConnection();
	$sql = "SELECT id, valeur as libel, valeur FROM adsys_types_billets WHERE id_ag=$global_id_agence AND devise = '$dev' ORDER BY valeur DESC";
	$result = $db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}

	$billet = array();
	$x = 0;

	while ($tmprow = $result->fetchrow()) {
		$billet[$x]['libel'] = $tmprow[1];
		$billet[$x]['valeur'] = $tmprow[2];
		$x++;
	}
	$dbHandler->closeConnection(TRUE);
	return $billet;
}

?>