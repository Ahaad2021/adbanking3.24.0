<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * @package Systeme
 */

require_once 'lib/dbProcedures/ferie.php';
require_once 'lib/misc/cryptage.php';
require_once 'lib/misc/divers.php';
require_once 'batch/librairie.php';
require_once 'lib/dbProcedures/devise.php';


//FIXME : écrire une fonction getEtatAgence qui renvoie le statut, last_batch et last_date

function getMontantDroitsAdhesion($statJur) {
  // Fonction qui renvoie le montant des droits d'adhésion
  // IN : $statJur est la statut juridique du client concerné (cfr tableSys)
  // OUT: $mnt est le montant des droits d'adhésion
  global $dbHandler;
  global $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql = "SELECT pp_montant_droits_adhesion, pm_montant_droits_adhesion, gi_montant_droits_adhesion, gs_montant_droits_adhesion FROM ad_agc WHERE id_ag = $global_id_agence";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__, _("Il n'y a pas d'entrée pour les droits d'adhésion dans la table ad_agc"));
  $tmprow = $result->fetchrow();
  return $tmprow[$statJur-1];
}
/**
 * @description: Cette fonction permet de renvoyer la list des agence consolidées et la date du dernier mouvement
 * @return array un tableau associatif contenant les agences et la date du dernier mouvement effectué
 */
function getListAgenceConsolide() {
  global $dbHandler;
  global $global_id_agence;
  $db = $dbHandler->openConnection();
  $sql ="SELECT a.id_ag,b.libel_ag,max(a.date_valeur) as date_max ";
  $sql .="FROM ad_mouvement a,ad_agc b ";
  $sql .="WHERE a.id_ag=b.id_ag ";
  $sql .= "GROUP BY a.id_ag,b.libel_ag ORDER BY a.id_ag";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
  $list_ag = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $list_ag[$row['id_ag']]["id"]=$row['id_ag'];
    $list_ag[$row['id_ag']]["libel"]=$row['libel_ag'];
    $list_ag[$row['id_ag']]["date_dernier_mouv"]=pg2phpDate($row['date_max']);
  }
  $dbHandler->closeConnection(true);
  return $list_ag;
}
/**
 * Mise à jour de la variable $global_id_agence
 */
function setGlobalIdAgence($id_agence) {

  global $global_id_agence;

  if ($id_agence!="" && $id_agence!=NULL)
    $global_id_agence=$id_agence;

}
/**
 * Remise à la valeur du id_ag courant
 */
function resetGlobalIdAgence() {

  global $global_id_agence;

  $global_id_agence=getNumAgence();

}

/**
 * Renvoie l'identifiant de l'agence en utilisant la procédure stockée NumAgc()
 */
function getNumAgence() {
  // Fonction qui renvoie le numéro de l'agence, en fait le id_ag de la première entrée de l table ad_agc

  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT NumAgc()";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
  $tmprow = $result->fetchrow();
  if ($result->numRows() > 1) return 0;
  return $tmprow[0];

}
/**
 * Fonction qui renvoie les numéros d'agence dans tout le réseau
 * @return array Tableau contenant les id_ag de tout le réseau
 */
function getAllIdNomAgence() {

  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT id_ag,libel_ag FROM ad_agc";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
  $tmprow = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $tmprow[$row['id_ag']]=$row['libel_ag'];
  $dbHandler->closeConnection(true);
  return $tmprow;

}
/**
 * Fonction qui renvoie les id_operation et libellé operation dans tout le réseau
 * @return array Tableau contenant les id_operation de tout le réseau
 * @author Kheshan.A.G
 * 
 */

function getInfo_operation() {

	global $dbHandler;
	$db = $dbHandler->openConnection();
	$sql = "SELECT type_operation,traduction from ad_cpt_ope a, ad_traductions b where a.libel_ope = b.id_str and categorie_ope in (2,3) order by traduction ;";
	$result=$db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}
	if ($result->numRows() == 0)
		signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
	$tmprow = array();
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
		$tmprow[$row['type_operation']]=$row['traduction'];
	$dbHandler->closeConnection(true);
	return $tmprow;

}
/**
 * Fonction qui renvoie les logins dans tout le réseau
 * @return array Tableau contenant les logins de tout le réseau
 * @author Kheshan.A.G
 *
 */
function getLogins() {

	global $dbHandler;
	$db = $dbHandler->openConnection();
	$sql = "Select login from ad_log order by login  ;";
	$result=$db->query($sql);
	if (DB::isError($result)) {
		$dbHandler->closeConnection(false);
		signalErreur(__FILE__,__LINE__,__FUNCTION__);
	}
	if ($result->numRows() == 0)
		signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
	$tmprow = array();
	
	while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
		$tmprow[$row['login']]=$row['login'];
	$dbHandler->closeConnection(true);
	return $tmprow;

}
/**
 * Fonction qui renvoie les numéros d'agences consolidées
 * @return array Tableau contenant les infos d'agences consolidées
 */
function getIdNomAgenceConso() {

  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT num_agence, nom_agence FROM ad_agence_conso";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
  $tmprow = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $tmprow[$row['num_agence']]=$row['nom_agence'];
  $dbHandler->closeConnection(true);
  return $tmprow;

}

/**
 * Fonction permettant de savoir si nous sommes au siège ou dans une agence
 * @author Djibril NIANG
 * @since 2.9
 * @return un boolean
 */
function isSiege() {
  $num_agence = getNumAgence();
  if ($num_agence == 0) return true;
  else return false;
}

function getAgenceDatas($id_ag) {
  /* Cette fonction renvoie toutes les informations relatives à l'agence dont lID est $id_agence
   IN : l'ID de l'agence
   OUT: un tableau associatif avec les infos sur l'agence si tout va bien
        NULL si l'agence n'existe pas
        Die si erreur de la DB
  */
  global $dbHandler, $global_id_agence;

  if ($id_ag == NULL)
    $id_ag = $global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "SELECT * FROM ad_agc";
  if ($id_ag != NULL)
    $sql .= " WHERE id_ag = $id_ag";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    return NULL;

  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);
  return $DATAS;
}


/**
 * Ouvre l'agence
 *
 * @param int $id_agc Identifiant de l'agence
 * @return : <ul>
 *   <li>     -1 si un login possédant un guichet est loggé; ['login'] = array(logins loggés)
 *   <li>     -2 si jour ferié
 *   <li>     -3 si batch non-exécuté
 *   <li>     -4 si agence déjà ouverte
 *   <li>     -5 si agence en cours de batch
 *   <li>     -6 si batch déjà exécuté pour aujourd'hui  (attention gestion prélèvment frais de tenue de compte)
 *   <li>     -7 batch exécuté mais frais de tenue de compte non prélevés
 *   <li>     -10 si état de l'agence non reconnu
 *   <li>     1 si tout OK
 *   </ul>
 */
function ouverture_agence($id_agc) {
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  //Agence déjà ouverte ?
  $sql = "SELECT statut, last_batch FROM ad_agc WHERE (id_ag=$id_agc)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Retour DB incohérent !"
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  if ($row['statut'] == 1) { //Agence déjà ouverte
    $dbHandler->closeConnection(false);
    return array("result"=>-4);
  } else if ($row['statut'] == 3) { //Agence en cours de batch
    $dbHandler->closeConnection(false);
    return array("result"=>-5);
  } else if ($row['statut'] != 2) { //Si statut inconnu
    $dbHandler->closeConnection(false);
    return array("result"=>-10);
  }
  $last_batch = $row['last_batch'];

  //Guichet loggé ?
  $sql = "SELECT login FROM ad_ses WHERE (login IN (SELECT login FROM ad_log WHERE (guichet IS NOT NULL)))";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() > 0) {
    $i = 1;
    $retour = array();
    while ($row = $result->fetchrow()) {
      $retour['login'][$i] = $row[0];
      ++$i;
    }
    $dbHandler->closeConnection(false);
    $retour['result'] = -1;
    return $retour;
  }

  //Jour ferié ?
  if (is_ferie(date("d"),date("m"),date("Y"))) {
    $dbHandler->closeConnection(false);
    return array("result"=>-2);
  }

  //Batch a déjà eu lieu pour le jour précédent ? Ou pour aujourd'hui et plus tard
  $date = pg2phpDatebis($last_batch);
  if (mktime(0,0,0,$date[0],$date[1],$date[2]) < mktime(0,0,0,date("m"), (date("d")-1), date("Y"))) {//batch n'a pas eu lieu jusqu'à hier
    $dbHandler->closeConnection(false);
    return array("result"=>-3);
  } else if (mktime(0,0,0,$date[0],$date[1],$date[2]) > mktime(0,0,0,date("m"), (date("d")-1), date("Y"))) {//batch a déjà eu lieu
    //FIXME : pourquoi renvoyer un close connection false ?
    $dbHandler->closeConnection(false);
    return array("result"=>-6);
  }

  //Vérifier si on a déjà pris les frais de tenue pour ce batch
  $date2 = pg2phpDate($last_batch);

  if (! verif_frais_tenue($date2, $id_agc)) {
    $dbHandler->closeConnection(false);
    return array("result"=>-7);
  }

  //Ouverture agence
  $sql = "UPDATE ad_agc SET statut=1, last_date='".date("r")."' WHERE id_ag=$id_agc";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  global $global_nom_login;
  ajout_historique(205, NULL, NULL, $global_nom_login, date("r"), NULL);

  $dbHandler->closeConnection(true);
  return array("result"=>1);

}

function fermeture_agence($id_agc) {
  /* Ouvre l'agence; renvoie : ['result']
     -1 si un login possédant un guichet est loggé; ['login'] = array(logins loggés)
     -2 si il reste des guichets ouverts (utilisateurs déconnectés par timeout)
           ['guichets'] = ID des guichets non fermés
           ['login'] = logins associés
     -4 si agence déjà fermée
     -5 si agence en cours de batch
     1 si tout OK
  */
  global $dbHandler;
  $db = $dbHandler->openConnection();

  //Agence déjà fermée ?
  $sql = "SELECT statut, last_batch FROM ad_agc WHERE (id_ag=$id_agc)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() != 1) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Retour DB incohérent !"
  }
  $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
  if ($row['statut'] == 2) { //Agence déjà fermée
    $dbHandler->closeConnection(false);
    return array("result"=>-4);
  } else if ($row['statut'] == 3) { //Agence en cours de batch
    $dbHandler->closeConnection(false);
    return array("result"=>-5);
  } else if ($row['statut'] != 1) { //Si statut inconnu
    $dbHandler->closeConnection(false);
    return array("result"=>-10);
  }
  $last_batch = $row['last_batch'];

  //Guichet loggé ?
  $sql = "SELECT login FROM ad_ses WHERE (login IN (SELECT login FROM ad_log WHERE (guichet IS NOT NULL)))";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numrows() > 0) {
    $i = 1;
    $retour = array();
    while ($row = $result->fetchrow()) {
      $retour['login'][$i] = $row[0];
      ++$i;
    }
    $dbHandler->closeConnection(false);
    $retour['result'] = -1;
    return $retour;
  }

  // Existe un guicheteier déloggé avec caisse non-fermée (suite à timeout)
  $sql = "SELECT id_gui, login, libel_gui FROM ad_gui g, ad_log l WHERE g.id_ag = ".$id_agc." AND l.guichet = g.id_gui AND g.ouvert='t'";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  if ($result->numRows() > 0) {
    $i = 1;
    $retour = array();
    while ($row = $result->fetchrow()) {
      $retour['guichet'][$i] = $row[2];
      $retour['login'][$i] = $row[1];
      ++$i;
    }
    $dbHandler->closeConnection(false);
    $retour['result'] = -2;
    return $retour;
  }
  //Fermeture agence
  $sql = "UPDATE ad_agc SET statut=2 WHERE id_ag=$id_agc";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  global $global_nom_login;
  ajout_historique(206, NULL, NULL, $global_nom_login, date("r"), NULL);

  $dbHandler->closeConnection(true);
  return array("result"=>1);
}

function get_statut_agence($id_agence) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT statut FROM ad_agc WHERE (id_ag=$id_agence)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);

  return $row[0];
}

function get_last_batch($id_agence) {
  global $dbHandler;
  $db = $dbHandler->openConnection();

  $sql = "SELECT last_batch FROM ad_agc WHERE (id_ag=$id_agence)";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $row = $result->fetchrow();
  $dbHandler->closeConnection(true);

  return $row[0];
}

/**
 * Getter pour la licence de l'agence
 * @author Antoine Guyette
 */
function getLicence() {
  global $lib_path;

  $Licence = $lib_path."/backup/licence/licence.bin";

  if (!file_exists($Licence)) {
    return NULL;
  }

  return $Licence;
}

/**
 * Setter pour la licence d'une agence
 * @author Antoine Guyette
 */
function setLicence($Licence) {
  global $lib_path;

  $MyErr = isLicenceValide($Licence);
  if ($MyErr->errCode == NO_ERR) {
    rename($Licence, $lib_path."/backup/licence/licence.bin");
  }

  return $MyErr;
}

/**
 * Extrait les informations d'un fichier de licence crypte
 * @author Antoine Guyette
 */
function getInfosLicence($Licence) {
  if ($Licence == NULL) {
    return NULL;
  }

  $Cle = "public";
  $LicenceCrypte = $Licence;
  $LicenceDecrypte = "/tmp/licence_decrypte";

  DecrypteFichier($LicenceCrypte, $LicenceDecrypte, $Cle);

  $InfosLicence = parse_ini_file($LicenceDecrypte);

  unlink($LicenceDecrypte);

  return($InfosLicence);
}

/**
 * Vérifie si un fichier de licence crypte valide
 * @author Antoine Guyette
 */
function isLicenceValide($Licence) {
  if ($Licence == NULL) {
    return new ErrorObj(ERR_LIC_FIC);
  }

  $id_agc = getNumAgence();
  $AGC = getAgenceDatas($id_agc);
  $LIC = getInfosLicence($Licence);

  if ($LIC['code_institution'] != $AGC['code_institution']) {
    return new ErrorObj(ERR_LIC_AGC);
  }

  if ($LIC['libel_ag'] != $AGC['libel_ag']) {
    return new ErrorObj(ERR_LIC_AGC);
  }

  if (isBefore($LIC['date_exp'], date("d/m/Y"))) {
    return new ErrorObj(ERR_LIC_EXP);
  }

  if ($LIC['max_clients_actifs'] < $AGC['clients_actifs']) {
    return new ErrorObj(ERR_LIC_CLI);
  }

  return new ErrorObj(NO_ERR);
}

/**
 * Extrait les informations nécessaires à un fichier de demande de licence
 * @author Antoine Guyette
 */
function getInfosDemandeLicence() {
  global $global_id_agence;

  $AGC = getAgenceDatas($global_id_agence);

  $DATA['code_institution'] = $AGC['code_institution'];
  $DATA['libel_ag'] = $AGC['libel_ag'];
  $DATA['clients_actifs'] = $AGC['clients_actifs'];
  $DATA['total_clients'] = $AGC['total_clients'];
  $DATA['date_demande'] = date("d/m/Y");

  return $DATA;
}

/**
 * Crée un fichier de demande de licence crypte
 * @author Antoine Guyette
 */
function getDemandeLicence() {
  global $lib_path;

  $DATA = getInfosDemandeLicence();

  $TexteDecrypte = "code_institution=".$DATA['code_institution']."\n";
  $TexteDecrypte .= "libel_ag=".$DATA['libel_ag']."\n";
  $TexteDecrypte .= "clients_actifs=".$DATA['clients_actifs']."\n";
  $TexteDecrypte .= "total_clients=".$DATA['total_clients']."\n";
  $TexteDecrypte .= "date_demande=".$DATA['date_demande'];

  $Cle = "public";
  $FichierDecrypte = $lib_path."/backup/licence/licence_request_decrypte.bin";
  $FichierCrypte = $lib_path."/backup/licence/licence_request.bin";
  $DemandeLicence = "licence/licence_request.bin";

  $FileHandler = fopen($FichierDecrypte, "w");
  fwrite($FileHandler, $TexteDecrypte);
  fclose($FileHandler);

  CrypteFichier($FichierDecrypte, $FichierCrypte, $Cle);

  unlink($FichierDecrypte);

  return $DemandeLicence;
}

/**
 * Fonction qui renvoie champ "num_seq_auto" de l'agence en paramètre
 *
 * @param $id_ag: code agence
 * @return bigint num_seq_auto: valeur de la prochaine séquence
 * @since mai 2007
 * @author Stefano A.
 * @version 2.10
 */
function getNumSeqAuto($id_ag) {
  global $dbHandler;
  $db = $dbHandler->openConnection();
  $sql = "SELECT num_seq_auto FROM ad_agc WHERE id_ag = $id_ag";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Il n'y a pas d'entrée dans la table agence"
  $tmprow = $result->fetchrow();
  return $tmprow[0];
}

/**
 * Récupère une liste d'agence dans l'IMF
 * @return array un tableau contenant la clé et le nom de l'agence/ tous les champs
 */
function getListeAgences($all_fields=false) {

  global $dbHandler;

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_multi_agence ORDER BY app_db_description ASC";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) return NULL;
  $DATAS=array();
  while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) {
    if($all_fields) {
      $plaintext = trim($row['app_db_password']);
      $password = trim($row['app_db_host']).'_'.trim($row['app_db_name']);

      $row['app_db_password'] = phpseclib_Decrypt($plaintext, $password);

      $DATAS[] = $row;
    } else {
      $DATAS[$row["id_mag"]] = sprintf("%s (%s)", trim($row['app_db_description']), trim($row['id_agc']));
    }
  }

  return $DATAS;

}

/**
 * Cette fonction renvoie toutes les informations relatives à l'agence dont lID est $id_agence
 * @param integer $id_agence
 * @return array un tableau associatif avec les infos sur l'agence
 */
function getAgenceInfo($id_agence) {
  global $dbHandler;
  
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_multi_agence WHERE id_agc = $id_agence";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() == 0)
    return NULL;

  $agc_info = $result->fetchrow(DB_FETCHMODE_OBJECT);
  
  $plaintext = trim($agc_info->app_db_password);
  $password = trim($agc_info->app_db_host).'_'.trim($agc_info->app_db_name);

  $agc_info->app_db_password = phpseclib_Decrypt($plaintext, $password);

  return $agc_info;
}

/**
 * Fonction permettant de savoir si on est en mode agence siège
 * @author BD
 * @since 1.0
 * @return un boolean
 */
function isMultiAgenceSiege() {

  $sql = "SELECT COUNT(*) FROM adsys_multi_agence WHERE is_agence_siege='t'";
  $result = executeDirectQuery($sql, true);
  if ($result->errCode != NO_ERR) {
    return false;
  } else {
    return ($result->param[0] > 0);
  }
}

/**
 * Fonction permettant de savoir si l'agence est siège dans la table adsys_multi_agence
 * @author BD
 * @since 1.0
 * @return un boolean
 */
function isCurrentAgenceSiege() {
    
  global $global_id_agence;

  $sql = "SELECT COUNT(*) FROM adsys_multi_agence WHERE id_agc=".$global_id_agence." AND is_agence_siege='t'";
  $result = executeDirectQuery($sql, true);
  if ($result->errCode != NO_ERR) {
    return false;
  } else {
    return ($result->param[0] == 1);
  }
}

/**
 * Cette fonction renvoie les informations d'un licence
 * @param integer $id_agence
 * @return array un tableau associatif avec les infos d'une licence
 */
function getCurrentLicenceInfo($id_agence) {
  global $dbHandler;
  
  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM adsys_licence WHERE statut_licence='t' AND id_agc = $id_agence";
  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);
  if ($result->numRows() <= 0) {
    return NULL;
  }

  $licence_info = $result->fetchrow(DB_FETCHMODE_ASSOC);

  return $licence_info;
}

/**
 * Met à jour la licence d'une agence
 * @author BD
 */
function setNewLicence($date_crea, $date_exp, $tmp_licence_path, $tmp_licence2_path) {
  global $dbHandler, $doc_prefix, $global_id_agence, $global_mode_agence;
  
  require_once('lib/misc/divers.php');

  /*
  error_reporting(E_ALL);
  ini_set("display_errors", "on");
  */

  $MyErr = checkLicenceValidity($date_exp);
  if ($MyErr->errCode == NO_ERR) {

    // Créer la licence ionCube
    if(file_exists($tmp_licence_path)) {
      
      // Supprimer la licence existante
      $curr_licence_path = $doc_prefix."/licence.txt";
      if(file_exists($curr_licence_path)) {
        @unlink($curr_licence_path);
      }

      // Créer la nouvelle licence
      rename($tmp_licence_path, $curr_licence_path);

      // Ouvrir une connexion
      $db = $dbHandler->openConnection();
      
      // Désactiver toutes les licences
      $sql_delete = "UPDATE adsys_licence SET statut_licence='f' WHERE id_agc=$global_id_agence";
      $result_delete = $db->query($sql_delete);
      if (DB::isError($result_delete)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }

      // Ajouter la nouvelle licence de la table adsys_licence
      $sql_insert = "INSERT INTO adsys_licence(id_licence, id_agc, date_creation, date_expiration, statut_licence) VALUES (nextval('adsys_licence_id_licence_seq'), ".$global_id_agence.", '".php2pg($date_crea)."', '".php2pg($date_exp)."', 't');";
      $result_insert = $db->query($sql_insert);
      if (DB::isError($result_insert)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
      $dbHandler->closeConnection(true);
      /*
      // Ouvrir une connexion
      $db = $dbHandler->openConnection();
      
      // Get all licences data
      $sql_select_licence = "SELECT * FROM adsys_licence ORDER BY id_licence ASC";
      $result_select_licence = $db->query($sql_select_licence);
      if (DB::isError($result_select_licence)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
      $dbHandler->closeConnection(true);

      $liste_licences = array();
      if ($result_select_licence->numRows() > 0) {
        while ( $row_licence = $result_select_licence->fetchRow(DB_FETCHMODE_ASSOC) ) {
          $liste_licences[] = $row_licence;
        }
      }

      $liste_agences = getListeAgences(true);
      
      require_once 'ad_ma/app/controllers/misc/class.db.oo.php';

      // Copier les données licence dans toutes les bases de données
      if(is_array($liste_agences) && count($liste_agences)>0) {

        foreach ($liste_agences as $bd_obj) {

          // Si pas l'agence locale
          if($bd_obj["id_agc"] !=  getNumAgence()) {
            // Initialize database connection
            $agc_db_name = trim($bd_obj["app_db_name"]);
            $agc_db_username = trim($bd_obj["app_db_username"]);
            $agc_db_password = trim($bd_obj["app_db_password"]);
            $agc_db_host = trim($bd_obj["app_db_host"]);
            $agc_db_port = trim($bd_obj["app_db_port"]);
            $agc_db_driver = "pgsql";
            
            if (DBC::pingConnection($bd_obj, 1) === TRUE) { // Vérifié si la BDD est active

                // Connect to remote agence
                $pdo_conn_agc = new DBC($agc_db_name, $agc_db_username, $agc_db_password, $agc_db_host, $agc_db_port, $agc_db_driver);

                // Begin remote transaction
                $pdo_conn_agc->beginTransaction();

                if(isset($pdo_conn_agc) && is_object($pdo_conn_agc) && $pdo_conn_agc instanceof DBC) {

                  // Truncate table adsys_licence
                  $sql_adsys_licence_truncate = "TRUNCATE adsys_licence;";
                  $result_truncate = $pdo_conn_agc->execute($sql_adsys_licence_truncate);

                  if($result_truncate) {
                    if(is_array($liste_licences) && count($liste_licences)>0) {

                      foreach ($liste_licences as $licence_obj) {

                        // Build query string
                        $sql_insert_licence = "INSERT INTO adsys_licence (id_licence, id_agc, date_creation, date_expiration, statut_licence) VALUES ('".$licence_obj['id_licence']."','".$bd_obj['id_agc']."','".$licence_obj['date_creation']."','".$licence_obj['date_expiration']."','".$licence_obj['statut_licence']."')";
  
                        $result_insert_licence = $pdo_conn_agc->execute($sql_insert_licence);
  
                        if($result_insert_licence===FALSE) {
                          $pdo_conn_agc->rollBack(); // Roll back
                          signalErreur(__FILE__, __LINE__, __FUNCTION__);
                        }
                      }
                      
                      reset($liste_licences);
                    }
                  }
                }

                // Commit remote transaction
                if($pdo_conn_agc->commit()) {
                    //$create_db_data_ini_msg = "<br /><br /><p style=\"color:#FF0000;\">Les données ont été copiées dans toutes les bases de données distantes.</p>";
                }
            }
          }
        }
      }
      */
    }

    // Créer la licence mode agence
    if(file_exists($tmp_licence2_path)) {

      // Supprimer la licence2 existante
      $curr_licence2_path = $doc_prefix."/licence2.txt";
      if(file_exists($curr_licence2_path)) {
        $global_mode_agence = '';
        @unlink($curr_licence2_path);
      }
      
      // Créer la nouvelle licence2
      if(rename($tmp_licence2_path, $curr_licence2_path)) {
          isMultiAgence();
      }
    }
  }

  return $MyErr;
}

/**
 * Modifier les donnés de la licence actuelle
 * @author BD
 */
/*
function updateLicenceInfo($date_crea, $date_exp) {
  global $dbHandler, $global_id_agence;

  $MyErr = checkLicenceValidity($date_exp);
  if ($MyErr->errCode == NO_ERR) {
      // Ouvrir une connexion
      $db = $dbHandler->openConnection();
      
      // Modifier les donnés de la licence actuelle
      $sql_delete = "UPDATE adsys_licence SET date_creation='".php2pg($date_crea)."', date_expiration='".php2pg($date_exp)."' WHERE id_agc=$global_id_agence";
      $result_delete = $db->query($sql_delete);
      if (DB::isError($result_delete)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
      }
      $dbHandler->closeConnection(true);
  }

  return $MyErr;
}
*/


/**
 * Vérifie si la date d'expiration est valide
 * @author BD
 */
function checkLicenceValidity($date_exp) {

  if (isBefore($date_exp, date("d/m/Y"))) {
    return new ErrorObj(ERR_LIC_AGC);
  }

  return new ErrorObj(NO_ERR);
}

/**
 * Affiche le nombre de jours restant avant l'expiration de la licence actuelle
 * @author BD
 */
function afficheAlerteLicence() {
    global $global_id_agence;

    $InfoLicence = getCurrentLicenceInfo($global_id_agence);
    $InfosAgence = getAgenceDatas($global_id_agence);
    
    $alert_message = "";
    if($InfoLicence != NULL) {

        $today = new DateTime(date("Y-m-d"));
        $date_expiration = new DateTime($InfoLicence["date_expiration"]);

        $interval = $today->diff($date_expiration);
        $nb_days_left = $interval->format('%R%a');

        if($nb_days_left <= $InfosAgence["licence_jours_alerte"]) {
            if($nb_days_left < 0) {
                $alert_message = "<strong>La licence a expiré !</strong>";
            }
            elseif($nb_days_left == 0) {
                $alert_message = "<strong>La licence expire aujourd'hui !</strong>";
            } else {
                $alert_message = sprintf("La licence expire dans <strong>%s</strong> jour%s !", abs($nb_days_left), (abs($nb_days_left)!=1?'s':''));
            }
        }
    }

    // Alert nb. client creation left
    // Récupéré le chemin physique du fichier
    preg_match("/(.*)\/lib\/dbProcedures\/agence\.php/",__FILE__,$doc_prefix);
    $doc_prefix = $doc_prefix[1];

    $licence2_path = "$doc_prefix/licence2.txt";

    // Vérification de l'existence du fichier licence2.txt
    if (file_exists($licence2_path)) {

      // Check agence mode in file
      require_once 'lib/misc/Erreur.php';
      require_once 'lib/dbProcedures/agence.php';
      require_once 'lib/multilingue/traductions.php';
      require_once 'lib/misc/cryptage.php';

      $crypte_key = "adbankingpublic";

      $crypte_text = file_get_contents($licence2_path);
      $decrypte_arr = unserialize(Decrypte($crypte_text, $crypte_key));

      // Check number clients creation left to display alert message
      if (isset($decrypte_arr[6]) && isset($decrypte_arr[7])) {

        $count_client_left = ((int)$decrypte_arr[6] - (int)$_SESSION['nb_clients_actifs']);
        $count_client_alert = (int)$decrypte_arr[7];

        if($count_client_left <= 0) {
          $alert_message .= "<br /><strong>Vous n'êtes plus autorisé à créer de nouveaux clients !</strong>";
        } elseif ($count_client_left > 0 && $count_client_left <= $count_client_alert) {
          $alert_message .= sprintf("<br />Attention, il vous reste <strong>%s</strong> nouveau%s client%s à créer !", abs($count_client_left), (abs($count_client_left)!=1?'x':''), (abs($count_client_left)!=1?'s':''));
        }
      }
    }

    return $alert_message;
}

/**
 * 
 * @return le base taux en jours
 */
function getBaseTauxCalculInteret()
{
	global $global_id_agence;	
	$InfosAgence = getAgenceDatas($global_id_agence);
	$base_taux = $InfosAgence['base_taux'];
	
	if($base_taux == 1)	return 360;	
	elseif($base_taux == 2) return 365;
}

/**
 * Recupere le parametre 'pct_comm_change_local' qui indique ou devrait etre percues les commissions pour les transactions en multidevises, multiagences.
 * Si pct_comm_change_local = true, les commissions sont prélevés dans l'agence 'locale', celle qui sert le client dans une transaction multi agence
 * Si pct_comm_change_local = false, les commisions sont prélevés dans l'agence distante, celle ou le compte du client se trouve.
 * 
 * @return boolean
 */
function getWherePerceptionCommissionsMultiAgence()
{
	global $global_id_agence;	
	$agenceDatas = getAgenceDatas($global_id_agence);	
	$wherePerceptionCommission = $agenceDatas['pct_comm_change_local'];	
	
	if($wherePerceptionCommission == 't') { 
		$wherePerceptionCommission = true;
	}
	else {
		$wherePerceptionCommission = false;
	}	
	return $wherePerceptionCommission;	
}

// Recuperation du nombre de jours pour la validation des chèques internes
function getValidityChequeDate(){

  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();

  $sql = "SELECT validite_chq_ord, validite_chq_cert,validite_ord_pay FROM ad_agc WHERE id_ag=$global_id_agence";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) {
      return NULL;
  }

  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

  return $DATAS;
}

// Générer le numéro de compte avec Id agence
function hasCpteCmpltAgc($global_id_agence){

  global $dbHandler;

  $db = $dbHandler->openConnection();

  $sql = "SELECT has_cpte_cmplt_agc FROM ad_agc WHERE id_ag=$global_id_agence";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }

  $dbHandler->closeConnection(true);

  if ($result->numRows() == 0) {
    return NULL;
  }

  $DATAS = $result->fetchrow(DB_FETCHMODE_ASSOC);

  return ($DATAS["has_cpte_cmplt_agc"]=='t'?TRUE:FALSE);
}

// Formattage du numéro de compte
function formatCpteCmpltAgc($id_cli) {

  global $global_id_agence;

  $new_id_cli = $id_cli;

  if(hasCpteCmpltAgc($global_id_agence)) {
    $new_id_cli = sprintf("%02d%08d", $global_id_agence, $id_cli);
  }

  return $new_id_cli;
}

function getAgence() {
  global $global_id_agence;
  global $dbHandler, $global_id_agence;

  $db = $dbHandler->openConnection();
  $sql = "SELECT app_db_description, id_agc, id_mag, compte_liaison, is_agence_siege FROM adsys_multi_agence WHERE id_agc=$global_id_agence";
  $result = $db->query($sql);
  if (DB :: isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
  }
  $ligne = $result->fetchrow();

  $dbHandler->closeConnection(true);
  return $ligne;
}

/**
 * Renvoie le nombre de clients actifs
 */
function getNumClientsActifs() {

  global $global_id_agence;

  $date_year_ago = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d"), date("Y")-1));

  // Vérification si Multi-Agence
  if(isset($global_id_agence) && isMultiAgence()) {
    $nb_clients_actifs_ma = 0;
    $nb_clients_actifs_ma =  getNbreClientActif();
    return $nb_clients_actifs_ma;
  }
  else
  {
    global $dbHandler;

    $db = $dbHandler->openConnection();

    $sql = "SELECT clients_actifs FROM ad_agc WHERE id_ag = ".$global_id_agence;

    $result = $db->query($sql);

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
}

/**
 * Met à jour du nombre de clients actifs
 * @author BD
 */
function updateNbClientActif($neg = null)
{
    global $dbHandler, $global_id_agence;

    // Ouvrir une connexion
    $db = $dbHandler->openConnection();

    $sens = '+';
    if ($neg != null) {
      $sens = '-';
    }

    $sql = "UPDATE ad_agc SET clients_actifs = clients_actifs $sens 1 WHERE id_ag = $global_id_agence";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur(__FILE__, __LINE__, __FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    return getNumClientsActifs();
}


/**
 * Method utile pour etablir si on est en Afrique de l'Ouest
 * @return bool
 */
function is_BCEAO ()
{
  global $global_id_agence;
  $agences_datas = getAgenceDatas($global_id_agence);
  $deviseRef = $agences_datas['code_devise_reference'];
  if($deviseRef == 'XOF') return true;
  else return false;
}

function getNbreClientActif(){
  global $dbHandler, $global_id_agence;

  // Ouvrir une connexion
  $db = $dbHandler->openConnection();
  $sql = "SELECT sum(client_actifs) from adsys_multi_agence ";
  $result = $db->query($sql);

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

function getZone($condition = NULL){
    global $dbHandler, $global_id_agence;

    // Ouvrir une connexion
    $db = $dbHandler->openConnection();
    $sql = "SELECT * FROM adsys_zone_client WHERE id_ag = $global_id_agence";

    if(!empty($condition)){
        $sql .= " AND $condition";
    }
    $result = $db->query($sql);

    if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__,__LINE__,__FUNCTION__);
    }
    $dbHandler->closeConnection(true);

    $dataset = array();
    while($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
        $dataset[$row['code_zone']] = $row;
    return $dataset;
}