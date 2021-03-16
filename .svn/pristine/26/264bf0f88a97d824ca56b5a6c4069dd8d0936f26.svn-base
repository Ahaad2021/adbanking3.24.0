<?php

/**
 * Permet de récupérer un rapport généré au format CSV
 * @package Rapports
 **/

/* La session doit être nommée avant toute utilisation ! */
session_name("ADbanking");

require_once 'lib/misc/VariablesGlobales.php';

// Récupération du CSV
if (!isset($filename)) {
  $filename = "$csv_output.".session_id();
}

$fd = fopen($filename, 'r');
$rapport = fread($fd,filesize($filename));
fclose($fd);

// Envoi
$len = strlen($rapport);
header("Content-type: application/vnd.ms-excel");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=export.csv");
print $rapport;

?>
