<?php

/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Batch : fonctions utilitaires pour la base de données
 * @package Systeme
 **/

require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/divers.php';
require_once 'ad_ma/app/controllers/misc/VariablesGlobales_ma.php';

/**
 * Fait un VACUUM de la BD
 * TODO: devrait être automatique lors de la sauvegarde dans make_gzip
 */
function vacuum_db() {
  global $DB_dsn;
  global $disable_vacuum;
  if ($disable_vacuum) {
    affiche(_("Le vacuum de la BD est désactivé dans l'ini file"));
    return;
  }

  affiche(_("Optimisation et nettoyage"));
  incLevel();

  $no_err = true;
  //Se connecte à la base de données (VACUUM ne peut être mit dans une transaction block)
  require_once 'DB.php';
  $db = DB :: connect($DB_dsn, true);
  if (DB :: isError($db))
    erreur("vacuum_db()", _("Impossible de se connecter à la base de données !"));

  //VACUUM
  $result = $db->query("VACUUM ANALYZE");
  if (DB :: isError($result))
    erreur("vacuum_db()", _("La requête ne s'est pas exécutée correctement")." : " . $result->getMessage());

  //Déconnecte
  $db->disconnect();

  affiche("OK", true);

  decLevel();
  affiche(_("Optimisation et nettoyage terminés"));
}

/**
 * Fait un dump gzipé de la BD.
 * @param $fichier Le fichier vers lequel on veut faire le dump
 * @return ErrorObj objet erreur contenant le code et la description de l'erreur le cas échéant
 */
function make_gzip($fichier) {
  global $DB_user, $DB_name, $DB_cluster, $batch_db_host, $DB_pass;

	$output = array ();
	$code_retour = 0;
	$fichier = escapeshellarg($fichier);
	// Commande simple qui permet de tester si on a accès à la BD
  
  if (!empty($DB_cluster)) {
    putenv("PGCLUSTER=$DB_cluster");
  }
  $retour = exec(escapeshellcmd("PGPASSWORD=$DB_pass psql -h $batch_db_host -U $DB_user -d $DB_name ")."-c 'SHOW port;' > /dev/null", $output, $code_retour);
	if ($code_retour == 0){
      $retour = exec(escapeshellcmd("PGPASSWORD=$DB_pass pg_dump -h $batch_db_host -U $DB_user $DB_name")." | gzip > $fichier", $output, $code_retour);
    }
	else{
      return new ErrorObj(ERR_PSQL_DUMP, sprintf(_('code erreur psql : %s'), $code_retour));
    }
	if ($code_retour == 0){
      return new ErrorObj(NO_ERR);
    }
	else{
      return new ErrorObj(ERR_GZIP, sprintf(_('code erreur gzip : %s'), $code_retour));
    }
}

/**
 * Fait un backup de la BD durant le batch
 */
function backup_db() {
  global $date_jour, $date_mois, $date_annee;
  global $lib_path, $disable_backup;
  global $global_id_agence, $BatchObj, $batch_db_host;

  $agence = getAgence();
  $nomAgence = strtolower(cleanSpecialCharacters($agence[0]));

  affiche(_("Démarre sauvegarde base de données ..."));
  incLevel();

  vacuum_db();
  $prefix = "agc" . $global_id_agence . "_";
  $suffix = "." .$nomAgence. "_" . $agence[1] . ".sql.gz";
  $fichier = "$lib_path/backup/batch/" . $prefix . $date_annee . "-" . $date_mois . "-" . $date_jour . $suffix;
  if ($disable_backup)
    affiche(_("Le backup de la BD est désactivé dans l'ini file"));
  else {
    affiche(_("Compression et archivage vers")." '$fichier'");
    incLevel();
    $result = make_gzip($fichier);
    if ($result->errCode == NO_ERR) {

        // Initialiser les variables
        $local_host_ip      = $_SERVER["HTTP_HOST"];
        $remote_host_ip     = $batch_db_host;
        $local_path         = $fichier;
        $remote_path        = '/var/lib/adbanking/backup/batch';
        $local_ssh_login    = 'batchma';
        $local_ssh_password = 'b@tchm@';
        $remote_ssh_login   = 'batchma';

        // Transfert le fichier crée en local sur le serveur distant 
        $BatchObj->transferBatchFile($local_host_ip, $remote_host_ip, $local_path, $remote_path, $local_ssh_login, $local_ssh_password, $remote_ssh_login);

        // Metter à jour le chemin du backup de la BDD
        $BatchObj->updateDbBackupPath($fichier);

      affiche("OK", true);
    } else {
      erreur("make_gzip()", _("Problème d'archivage de la BD ! ") . $result->param . $result->handler);
    }
    decLevel();
    affiche(_("Compression et archivage terminés"));
  }

  // Gestion des anciens backups, la politique suivante est adoptée :
  // - les backups de moins de 10 jours seront tous gardés,
  // - les backups entre 10 et 30 jours seront gardés si le numéro de leur jour (1 à 366) est pair,
  // - les backups de plus de 30 jours seront gardés si lu numéro de leur jour est un multiple de 4.
  affiche(_("Nettoyage des anciens backups ..."));
  incLevel();
  $dir = opendir("$lib_path/backup/batch/");
  while ($file = readdir($dir)) {
//    if (substr($file, -7) == '.sql.gz') {
    if (endsWith($file, $suffix) || (strlen($file) == (17 + strlen($prefix)) && substr($file, -7) == '.sql.gz') ) {
      $str_date_img = substr($file, strlen($prefix), 10);  //example: get only the part "2013-01-05" from "agc10_2016-01-19.sql.gz" or from "agc10_2016-01-19.meck_domoni_10.sql.gz"
      $timestamp_img = strtotime($str_date_img);
      $date_img = getdate($timestamp_img);
      if ($timestamp_img < strtotime($date_annee . "-" . $date_mois . "-" . $date_jour) - 3600 * 24 * 30) {
        // le fichier est âgé de plus de 30 jours
        if ($date_img['yday'] % 4 != 0) {
          affiche(sprintf(_("Suppression de %s"), $file));
          unlink("$lib_path/backup/batch/" . $file);
          affiche("OK", true);
        }
      } else
        if ($timestamp_img < strtotime($date_annee . "-" . $date_mois . "-" . $date_jour) - 3600 * 24 * 10) {
          // le fichier est âgé de plus de 10 jours
          if ($date_img['yday'] % 2 != 0) {
            affiche(sprintf(_("Suppression de %s"), $file));
            unlink("$lib_path/backup/batch/" . $file);
            affiche("OK", true);
          }
        }
    }
  }
  decLevel();

  decLevel();
  affiche(_("Sauvegarde base de données terminée !"));
}

function backup_db_consolidation() {
  global $date_jour, $date_mois, $date_annee;
  global $lib_path,$dbHandler;
  global $global_id_agence, $batch_db_host, $DB_user, $DB_name, $DB_pass;

  $code_psql = 0;
  $code_gz = 0;

  affiche(_("Démarre sauvegarde base de données pour consolidation ..."));
  incLevel();

  vacuum_db();
  $fichier = "$lib_path/backup/batch/dump_conso_ag" . $global_id_agence . "_"  . $date_annee . "-" . $date_mois . "-" . $date_jour . ".sql";
  $dirname = "$lib_path/backup/batch/";
  // TODO cette vérification est aussi faite dans checkPaths(), elle est donc un peu inutile ici
  if (!is_dir($dirname))
    $rep = mkdir($dirname);

  // On optimise et on nettoye, ce qui permet aussi de tester si on a accès à la BD
  $retour = passthru("PGPASSWORD=$DB_pass psql -h $batch_db_host -U $DB_user -d $DB_name -qc 'VACUUM ANALYZE'", $code_psql);
  //on recupère la liste des tables à sauvegarder(contenant id_ag) dans le tableau nom_table
  $db = $dbHandler->openConnection();
  $sql ="SELECT c.relname from pg_class c,pg_attribute a where a.attrelid=c.oid AND c.relkind='r' AND c.relname !~ '^pg_' AND relname !~ '^sql' AND a.attname='id_ag';";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  }
  $nom_table = array ();
  while ($row = $result->fetchrow()) {
    array_push($nom_table, $row[0]);
  }
  $dbHandler->closeConnection(true);
  //suppression du fichier $fichier s'il existe 
  if (file_exists($fichier)) { 
	    $cmd_ef = "rm -rf $fichier"; 
	    shell_exec($cmd_ef); 
  }
  $resul = touch($fichier);
  $fp = fopen($fichier, "w");
  fwrite($fp, "BEGIN;");
  fclose($fp);
  //Dump des données des tables contenant id_ag c-à-d non sytème
  if ($code_psql == 0) {
    for ($i = 0; $i < count($nom_table); $i++) {
      passthru("PGPASSWORD=$DB_pass /usr/bin/pg_dump -U $DB_user --data-only --table=$nom_table[$i] $DB_name >> $fichier", $code_gz);
    }

  }
  $fp = fopen($fichier, "a+");
  fwrite($fp, "COMMIT;");
  fclose($fp);
  $retour = passthru("gzip $fichier", $code_gz);

  // Gestion des anciens backups, la politique est de supprimer les backups de plus de deux jours:
  affiche(_("Nettoyage des anciens backups ..."));
  //incLevel();
  $dir = opendir("$lib_path/backup/batch");
  while ($file = readdir($dir)) {
    if (substr($file, -7) == '.sql.gz' && ((substr($file, 0, 13) == 'dump_conso_ag'))) {
      $str_date_img = substr($file, 15, 10);
      $timestamp_img = strtotime($str_date_img);
      $date_img = getdate($timestamp_img);
      if ($timestamp_img < strtotime($date_annee . "-" . $date_mois . "-" . $date_jour) - 3600 * 24 * 1) {
        // le fichier est âgé de plus d'un jour
        affiche(sprintf(_("Suppression de %s"), $file));
        unlink("$lib_path/backup/batch/" . $file);
        affiche("OK", true);
      }
    }
  }
  decLevel();

  decLevel();
  affiche(_("Sauvegarde base de données pour consolidation terminée !"));
}

?>