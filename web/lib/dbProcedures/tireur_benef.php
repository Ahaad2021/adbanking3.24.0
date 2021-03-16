<?php

require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/handleDB.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/misc/divers.php';



/**
 * Cette fonction ajoute un nouveau tireur/bénéficiaire dans la table tireur_benef.
 * @author Bernard De Bois
 * @param array $data : toutes les champs de la table.
 * @return int $DATA['id']: Si l'insertion s'est bien passée, renvoie le numéro d'identifiant du tireur/bénéficiaire inséré.
 */
function insere_tireur_benef($DATA) {
  global $dbHandler;
  global $global_id_agence;

  $db = $dbHandler->openConnection();
  $DATA['id']= getNewTireurBenefID ();

  $DATA['id_ag']= $global_id_agence;
  $sql = buildInsertQuery ("tireur_benef", $DATA);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  return $DATA['id'];
}

/**
 * Cette fonction met à jour un tireur/bénéficiaire dans la table tireur_benef
 * @author Bernard De Bois
 * @param int $id : identifiant du tireur/bénéficiaire
 * @param array $Fields : les champs contenant les modifications.
 * @return l'objet erreur
 */
function updateTireurBenef($id, $Fields) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $Where["id"] = $id;
  $Where["id_ag"] = $global_id_agence;
  $sql = buildUpdateQuery("tireur_benef", $Fields, $Where);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Cette fonction met à true le champ "Bénéficiaire" d'un tireur/bénéficiaire dans la table tireur_beneficiaire
 * @author Bernard De Bois
 * @param int $id : identifiant du tireur/bénéficiaire
 * @return l'objet erreur
 */
function setBeneficiaire($id) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $Where["id"] = $id;
  $Where["id_ag"] = $global_id_agence;
  $Fields['beneficiaire']='t';
  $sql = buildUpdateQuery("tireur_benef", $Fields, $Where);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);

}

/**
 * Cette fonction met à true le champ "Tireur" d'un tireur/bénéficiaire dans la table tireur_beneficiaire
 * @author Bernard De Bois
 * @param int $id : identifiant du tireur/bénéficiaire
 * @return l'objet erreur
 */
function setTireur($id) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $Where["id"] = $id;
  $Where["id_ag"] = $global_id_agence;
  $Fields['tireur']='t';
  $sql = buildUpdateQuery("tireur_benef", $Fields, $Where);
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR);
}

/**
 * Cette fonction renvoie toutes les informations contenues dans la table tireur_benef pour un identifiant donné.
 * @author Bernard De Bois
 * @param int $id : identifiant du tireur/bénéficiaire
 * @return $DATAS : tableau contenant tous les champs du tireur/bénéficiaire choisi.
 */
function getTireurBenefDatas($id) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM tireur_benef WHERE id_ag = ".$global_id_agence." AND id = '".$id."';";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0)
    return NULL;
  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  $dbHandler->closeConnection(true);
  return $DATAS;
}

//PAS ENCORE MIS A JOUR
function getEtatTireurBenef($id_client) {
  /*
    Retourne l'état du client
  */
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT etat FROM ad_cli WHERE id_ag = ".$global_id_agence." AND id_client = '".$id_client."';";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $etat_client = $result->fetchrow();

  $dbHandler->closeConnection(true);

  return $etat_client[0];

}

function countTireurBenef ($Where, $type) {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $WhereClause = "";

  foreach ($Where as $key=>$value) {
    if ($value != "") {
      $WhereClause .= " upper($key) like '%' || upper('$value') || '%' AND";
    }
  }

  switch ($type) {
  case 'b':
    $WhereClause .= " beneficiaire = 't' AND ";
    break;
  case 't':
    $WhereClause .= " tireur = 't' AND ";
    break;
  }

  if ($WhereClause == "")
    $sql="SELECT count(*) FROM tireur_benef a, adsys_pays b WHERE a.id_ag = ".$global_id_agence."AND a.id_ag = b.id_ag AND a.pays = b.id_pays;";
  else {
    $sql = "SELECT count(*) FROM tireur_benef a, adsys_pays b WHERE".$WhereClause." a.pays=b.id_pays AND a.id_ag = ".$global_id_agence." AND a.id_ag = b.id_ag;";
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  return $row[0];
}

/**
 * Fonction qui recherche dans la tables tireurs_beneficiaires selon certains critères
 * @author Bernard De Bois
 * @param Array $Where Un tableau associatif avec les critères de recherche
 * @param char $type 'b' si recherche des bénéficaires, 't' sie recherche des tireurs, recherche générale si autre
 */
function getMatchedTireurBenef($Where, $type) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();
  $WhereClause = "";

  foreach ($Where as $key=>$value) {
    if ($value != "") {
      $WhereClause .= " upper($key) like '%' || upper('$value') || '%' AND";
    }
  }

  switch ($type) {
  case 'b':
    $WhereClause .= " beneficiaire = 't' AND ";
    break;
  case 't':
    $WhereClause .= " tireur = 't' AND ";
    break;
  }

  if ($WhereClause == "")
    $sql="SELECT a.*, b.libel_pays FROM tireur_benef a, adsys_pays b WHERE a.id_ag = ".$global_id_agence." AND a.id_ag = b.id_ag AND a.pays = b.id_pays;";
  else {
    $sql = "SELECT a.*, b.libel_pays FROM tireur_benef a, adsys_pays b WHERE".$WhereClause." a.pays=b.id_pays AND a.id_ag = ".$global_id_agence." AND a.id_ag = b.id_ag;";
  }

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  if ($result->numRows() == 0) {
    $dbHandler->closeConnection(true);
    return null;
  }

  $DATAS = array();
  while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC))
    array_push($DATAS, $tmprow);

  $dbHandler->closeConnection(true);
  return $DATAS;
}

function getNewTireurBenefID () {
  /* Renvoie le prochain ID de client libre dans la base
     Valeurs de retour :
     id_client si OK
     Die si refus de la base de données
  */
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT nextval('tireur_benef_seq');";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(true);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $id_tib = $result->fetchrow();
  $dbHandler->closeConnection(true);
  return $id_tib[0];
}

?>