<?php
/**
 * Permet de récupérer un fichier généré au format XML
 * @package Rapports
 **/

/* La session doit être nommée avant toute utilisation ! */
session_name("ADbanking");

require_once 'lib/misc/VariablesGlobales.php';

// Récupération du XML
if (!isset($filename)) {
  $filename = "$xml_output.".session_id();
}

$fd = fopen($filename, 'r');
$rapport = fread($fd,filesize($filename));
fclose($fd);

// Envoi
$len = strlen($rapport);
header("Content-type: application/xml");
header("Content-Length: $len");
header("Content-Disposition: attachment; filename=export.xml");
print $rapport;
?>
