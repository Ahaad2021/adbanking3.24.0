<?php

/**
 * Permet de récupérer un fichier PDF généré
 * @package Rapports
 **/

/* La session doit être nommée avant toute utilisation ! */
session_name("ADbanking");

require_once 'lib/misc/VariablesGlobales.php';

// Récupération du PDF
if (!isset($filename)) {
  $filename = "$pdf_output.".session_id();
}

$fd = fopen($filename, 'r');
$rapport = fread($fd, filesize($filename));
fclose($fd);

// Envoi
$len = strlen($rapport);
header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=rapport.pdf");
print $rapport;

?>
