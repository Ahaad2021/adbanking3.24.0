<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

require_once 'lib/misc/cryptage.php';

if ($_SERVER['argc'] != 4) {
  echo _("Erreur : le nombre d'arguments est incorrect")."\n";
  die();
}

$FichierCrypte = $_SERVER['argv'][1];
$FichierDecrypte = $_SERVER['argv'][2];
$Cle = $_SERVER['argv'][3];

if (!file_exists($FichierCrypte)) {
  echo sprintf(_("Erreur : le fichier crypté %s n'existe pas"),$FichierCrypte)."\n";
  die();
}

if (DecrypteFichier($FichierCrypte, $FichierDecrypte, $Cle) == FALSE) {
  echo sprintf(_("Erreur : problème pendant le décryptage du fichier %s"),$FichierCrypte)."\n";
  die();
}

echo sprintf(_("Le fichier %s a été décrypté avec succès dans le fichier %s"),$FichierCrypte,$FichierDecrypte)."\n";
?>