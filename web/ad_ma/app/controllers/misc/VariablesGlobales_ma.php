<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Définition des variables globales (elles ne sont pas ici enregistrées dans la session)
 * @package Systeme
 */

require_once "lib/dbProcedures/handleDB.php";

/**
 * Connexion à la base de donnée
 * @var handleDB $dbHandler l'objet de connexion à la base de données
 */
$dbHandler = new handleDB();

$batch_db_host = trim($batch_agence_obj->app_db_host);
$batch_db_port = trim($batch_agence_obj->app_db_port);
$batch_db_name = trim($batch_agence_obj->app_db_name);
$batch_db_username = trim($batch_agence_obj->app_db_username);
$batch_db_password = trim($batch_agence_obj->app_db_password);

/*
echo "<pre>";
var_dump($DB_dsn);
echo "</pre>";
*/

$DB_name = $batch_db_name;
$DB_user = $batch_db_username;
$DB_cluster = $ini_array["DB_cluster"]; // To add in table adsys_multi_agence ??
if ($batch_db_host != '') {
    // Connexion par socket TCP
    $DB_dsn = sprintf("pgsql://%s:%s@%s:%s/%s", $batch_db_username, $batch_db_password, $batch_db_host, $batch_db_port, $batch_db_name);
}

/*
echo "<pre>";
var_dump($DB_dsn);
echo "</pre>";
exit;
*/