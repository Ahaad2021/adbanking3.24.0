<?php

/**
 * Fonction qui renvoie champ "statut" du job externe
 */
function getStatutJobExterne() {

    global $dbHandler, $global_id_agence;

    $db = $dbHandler->openConnection();

    $sql = "SELECT statut FROM adsys_job_externe WHERE nom_job = 'COMPENSATION_MA' AND id_ag = $global_id_agence";

    $result=$db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
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
    $Where["id_ag"] = $global_id_agence;

    $sql = buildUpdateQuery("adsys_job_externe", $Fields, $Where);

    // Exécution de la requête
    $result = $db->query($sql);
    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n".$result->getMessage());
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
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
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
        signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql."\n".$result->getMessage());
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
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    if ($result->numRows() == 0) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
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
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
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

/**
 * Fonction pour recuperer l'etat compensation au siege
 * PARAMS: date_rapport, nom_agence et etat
 * RETURN array
 */
function getDataLogEtatCompensationSiege($date_rapport, $nom_agence = null, $etat = null){
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT DISTINCT m_agc.id_agc AS id_agence, d.nom_agence, (SELECT DISTINCT ld.status FROM log_multiagence_details ld WHERE ld.nom_agence = d.nom_agence AND ld.id = (SELECT MAX(id) FROM log_multiagence_details WHERE nom_agence = d.nom_agence AND date <= d.date";
  if ($etat != '' || $etat != null){
    $sql .= " AND status = COALESCE('$etat',status)";
  }
  $sql .= ")) AS etat_compensation, d.date, ";
  $sql .= "(SELECT MAX(date) FROM log_multiagence_details WHERE nom_agence = d.nom_agence AND date <= d.date) AS date_derniere_compensation,
(SELECT MAX(date) FROM log_multiagence_details WHERE nom_agence = d.nom_agence AND status = 't' AND date <= d.date) AS date_derniere_reussi
FROM log_multiagence_details d
INNER JOIN adsys_multi_agence m_agc ON m_agc.app_db_description = d.nom_agence
WHERE DATE(d.date) <= DATE('$date_rapport')";
  if ($nom_agence != '' || $nom_agence != null){
    $sql .= " AND d.nom_agence = '$nom_agence' ";
  }
  if ($etat != '' || $etat != null){
    $sql .= " AND d.status IN ('$etat')";
  }
  else{
    $sql .= " AND d.status IN ('t','f')";
  }
  //$sql .= " AND d.id IN (SELECT id FROM log_multiagence_details WHERE nom_agence = d.nom_agence AND DATE(date) <= DATE('$date_rapport'))";

  $sql .= " ORDER BY d.date DESC, m_agc.id_agc ASC";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
    $dbHandler->closeConnection(false);
  }

  $arr_ligne = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    array_push($arr_ligne, $row);
  }

  $dbHandler->closeConnection(true);
  return $arr_ligne;
}

/**
 * Fonction pour recuperer les agences interconnecter avec l'agence siege
 * PARAM: no
 * RETURN array
 */
function getListMultiAgences(){
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $sql = "SELECT DISTINCT id_agc, app_db_description FROM adsys_multi_agence WHERE is_agence_siege = 'f'";

    $result = $db->query($sql);

    if (DB::isError($result)) {
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
        $dbHandler->closeConnection(false);
    }

    $arr_ligne = array();
    while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        //array_push($arr_ligne, $row);
        $arr_ligne[$row['app_db_description']] = $row['app_db_description'];
    }

    $dbHandler->closeConnection(true);
    return $arr_ligne;
}
