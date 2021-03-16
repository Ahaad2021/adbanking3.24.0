<?php

/**
 * Enregistrement des variables globales distante dans la session
 * Les session_register démarrent une session
 * Il faut donc _toujours_ faire le require_once "VariablesSessionRemote.php" après un session_name("ADbanking") !
 * @package Systeme
 */
//require 'ad_ma/app/controllers/misc/class.db.php';
require_once 'ad_ma/app/controllers/misc/class.db.oo.php';

session_register("global_remote_institution"); // Remote Nom de l'institution
session_register("global_remote_agence"); // Remote Nom de l'agence
session_register("global_remote_id_agence"); // Remote Identificateur de l'agence
session_register("global_remote_id_exo"); // Remote exercise comptable
session_register("global_remote_monnaie"); // Remote monnaie
session_register("global_remote_monnaie_courante"); // Remote monnaie courante

session_register("global_remote_agence_obj"); // Remote agence object

session_register("global_remote_db_name"); // Remote database name
session_register("global_remote_db_username"); // Remote database username
session_register("global_remote_db_password"); // Remote database password
session_register("global_remote_db_host"); // Remote database host
session_register("global_remote_db_port"); // Remote database port
session_register("global_remote_db_driver"); // Remote database driver

// Set database connection global variables
if (is_object($global_remote_agence_obj)) { // && (!isset($global_remote_db_driver) || !isset($global_remote_db_host) || !isset($global_remote_db_port) || !isset($global_remote_db_name) || !isset($global_remote_db_username) || !isset($global_remote_db_password))) {
    $global_remote_db_driver = "pgsql";
    $global_remote_agence = trim($global_remote_agence_obj->app_db_description);
    $global_remote_db_host = $global_remote_agence_obj->app_db_host;
    $global_remote_db_port = $global_remote_agence_obj->app_db_port;
    $global_remote_db_name = $global_remote_agence_obj->app_db_name;
    $global_remote_db_username = $global_remote_agence_obj->app_db_username;
    $global_remote_db_password = $global_remote_agence_obj->app_db_password;
}

// Clear connection instance
unset($pdo_conn);

if (isset($global_remote_db_driver) && isset($global_remote_db_host) && isset($global_remote_db_port) && isset($global_remote_db_name) && isset($global_remote_db_username) && isset($global_remote_db_password))
{
    // Initialize database connection
    $pdo_conn = new DBC($global_remote_db_name, $global_remote_db_username, $global_remote_db_password, $global_remote_db_host, $global_remote_db_port, $global_remote_db_driver);
}

session_register("global_remote_client"); // Remote Prénom & nom du client
session_register("global_remote_id_client"); // Remote Identificateur du client
session_register("global_remote_photo_client"); // Remote Nom du fichier contenant la photo du client
session_register("global_remote_signature_client"); // Remote Nom du fichier contenant la specimen de signature du client
session_register("global_remote_etat_client"); // Remote Contient l'état du client (2 = actif)

function resetVariablesGlobalesRemoteClient() {
    global $global_client, $global_id_client, $global_client_debiteur, $global_id_client_formate, $global_alerte_DAT, $global_credit_niveau_retard, $global_suspension_pen, $global_cli_epar_obli, $global_photo_client, $global_signature_client;
    global $global_remote_agence, $global_remote_client, $global_remote_id_client, $global_remote_id_client_formate, $global_remote_photo_client, $global_remote_signature_client, $global_remote_db_host, $global_remote_db_port, $global_remote_db_name, $global_remote_db_username, $global_remote_db_password, $global_remote_db_driver, $global_remote_agence_obj, $global_remote_id_exo;

    // Clear local info
    $global_client = "";
    $global_id_client = "";
    $global_client_debiteur = "";
    $global_id_client_formate = "";
    $global_alerte_DAT = "";
    $global_suspension_pen = false;
    $global_cli_epar_obli = "";
    $global_credit_niveau_retard = array();
    $global_signature_client = NULL;
    $global_photo_client = NULL;

    // Clear remote info
    $global_remote_agence = "";
    $global_remote_client = "";
    $global_remote_id_client = "";
    $global_remote_id_client_formate = "";
    $global_remote_photo_client = NULL;
    $global_remote_signature_client = NULL;
    $global_remote_agence_obj = NULL;
    $global_remote_db_driver = "";
    $global_remote_db_host = "";
    $global_remote_db_port = "";
    $global_remote_db_name = "";
    $global_remote_db_username = "";
    $global_remote_db_password = "";
    $global_remote_id_exo = "";
}

?>
