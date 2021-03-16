<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 1/30/2018
 * Time: 2:35 PM
 */

/**
 * Permet de récupérer un rapport généré au format ZIP
 * @package Rapports
 **/

/* La session doit être nommée avant toute utilisation ! */
session_name("ADbanking");

require_once 'lib/misc/VariablesGlobales.php';

header("Content-type: application/zip");
header('Content-Length: ' . filesize($filename));
header("Content-Disposition: attachment; filename = $nomFichier");
header("Pragma: no-cache");
header("Expires: 0");
readfile($filename);

?>