<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

require_once 'lib/misc/cryptage.php';

if ($_SERVER['argc'] != 4) {
  echo _("Erreur : le nombre d'arguments est incorrect")."\n";
  die();
}

$FichierDecrypte = $_SERVER['argv'][1];
$FichierCrypte = $_SERVER['argv'][2];
$Cle = $_SERVER['argv'][3];

if (!file_exists($FichierDecrypte)) {
  echo sprintf(_("Erreur : le fichier decrypté %s n'existe pas"), $FichierDecrypte)."\n";
  die();
}

if (CrypteFichier($FichierDecrypte, $FichierCrypte, $Cle) == FALSE) {
  echo sprintf(_("Erreur : problème pendant le cryptage du fichier %s"),$FichierDecrypte)." \n";
  die();
}

echo sprintf(_("Le fichier %s a été crypté avec succès dans le fichier %s"), $FichierDecrypte,$FichierCrypte)."\n";
?>