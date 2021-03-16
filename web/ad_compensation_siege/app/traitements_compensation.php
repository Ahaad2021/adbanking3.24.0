<?php

/**
 * Fonction qui renvoie champ "statut" du job externe
 */

function getStatutJobExterne() {

  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $agence = 'numagc()';
  $data_agc =getDataAgence($agence);
  $id_ag = $data_agc['id_ag'];

  $sql = "SELECT statut FROM adsys_job_externe WHERE nom_job = 'COMPENSATION_MA' AND id_ag = $id_ag";//echo $sql;

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "\n Erreur getStatutJobExterne() ! \n";
    exit();
  }
  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
    echo "\n Erreur Pas de donnees ! \n";
    exit();
  }

  $tmprow = $result->fetchrow();

  return $tmprow[0];
}

/**
 * Fonction qui met à jour le champ "statut" du job externe
 */
function updateStatutJobExterne ($statut = 'TERMINE') {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();
  $agence = 'numagc()';
  $data_agc =getDataAgence($agence);
  $id_ag = $data_agc['id_ag'];

  $Fields = array();
  $Where = array();

  if ($statut == 'TERMINE') {
    $Fields["statut"] = 'TERMINE';
  } elseif ($statut == 'ENCOURS') {
    $Fields["statut"] = 'ENCOURS';
  } else {
    $Fields["statut"] = NULL;
  }

  $Fields["dernier_traitement"] = date("r");

  $Where["nom_job"] = 'COMPENSATION_MA';
  $Where["id_ag"] = $id_ag;

  $sql = buildUpdateQuery("adsys_job_externe", $Fields, $Where);

  // Exécution de la requête
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n".$result->getMessage());
    echo "\n Erreur requete updateStatutJobExterne()  ! \n";
    exit();
  }

  $dbHandler->closeConnection(true);

  return true;
}

/**
 * Récupère une liste des compensations au siège
 * @return array un tableau
 */
function getListeCompensations() {

  global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_multi_agence_compensation WHERE ajout_historique='f' AND msg_erreur IS NULL ORDER BY id ASC";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "\n Erreur requete getListeCompensations()  ! \n";
    exit();
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;

  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    $DATAS[] = $row;
  }

  return $DATAS;
}

/**
 * Fonction qui met à jour une compensation
 */
function updateCompensation ($id, $id_audit_agc, $id_ag_local, $id_ag_distant, $id_his_siege, $id_ecriture_siege, $ajout_historique, $msg_erreur) {

  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $Fields = array();
  $Where = array();

  $Fields["id_his_siege"] = $id_his_siege;
  $Fields["id_ecriture_siege"] = $id_ecriture_siege;
  $Fields["ajout_historique"] = $ajout_historique;
  $Fields["msg_erreur"] = $msg_erreur;

  $Where["id"] = $id;
  $Where["id_audit_agc"] = $id_audit_agc;
  $Where["id_ag_local"] = $id_ag_local;
  $Where["id_ag_distant"] = $id_ag_distant;

  $sql = buildUpdateQuery("ad_multi_agence_compensation", $Fields, $Where);

  // Exécution de la requête
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n".$result->getMessage());
    echo "\n Erreur requete updateCompensation()  ! \n";
    exit();
  }

  $dbHandler->closeConnection(true);

  return true;
}

/**
 * Fonction qui renvoie champ "id_ecriture"
 */
function getIDEcritureByIDHis($id_his) {

  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT id_ecriture FROM ad_ecriture WHERE id_ag=" . $global_id_agence . " AND id_his=" . $id_his . " ORDER BY id_ecriture DESC LIMIT 1";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "\n Erreur requete getIDEcritureByIDHis()  ! \n";
    exit();
  }
  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
    echo "\n Erreur pas de donnees()  ! \n";
    exit();
  }

  $tmprow = $result->fetchrow();

  return $tmprow[0];
}

/**
 * Fonction qui récupère une erreur de la table log_multiagence
 */
function getEtlLogError() {

  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT count(message) FROM log_multiagence_details WHERE id_log = (SELECT max(id) FROM log_multiagence) AND nom_composant='998'";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    //signalErreur(__FILE__,__LINE__,__FUNCTION__);
    echo "\n Erreur requete  ! \n";
    exit();
  }
  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
    return false;
  }

  $tmprow = $result->fetchrow();

  return $tmprow[0];//explode("|", $tmprow[0]);
}

/*
 * Fonction pour vider les tables log_multiagence et log_multiagence_details à une date calculée par rapport
 * au date du traitement compensation
 */
function truncatelogmultiagence(){
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT coalesce(truncatelogmultiagence(date(now())),0) as totaldelete;";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) {
    //signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
    return false;
  }

  $tmprow = $result->fetchrow();

  return $tmprow[0];
}

