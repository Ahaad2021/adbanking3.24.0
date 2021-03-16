<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Fichier contenant la définition des fonctions utilisées par le batch
 * Séparé du reste car le code du batch peut être exécuté plusieurs fois par le même script
 * On veut donc éviter d'avoir une erreur de redéfinition de fonction déjà déclarées.
 * @package Systeme
 **/

function incLevel() {
  global $level;
  ++$level;
}

function decLevel() {
  global $level;
  --$level;
}

function getLevelStr() {
  global $level;
  for ($i = 1, $str=""; $i<$level; ++$i) $str .= "&nbsp&nbsp&nbsp ";
  return $str;
}

function affiche($msg, $confirm=false,$redcolor = FALSE) {
  $str = "<p>";
  $str .= getLevelStr();
  if ($confirm) {
  	$str .= ($redcolor)?"<font color=#E70739>":"<font color=#00FF00>";
  }
  $str .= "[".date("H:i:s  d/m/Y")."] ";
  $str .= $msg;
  if ($confirm) $str .= "</font>";
  $str .= "</p>\n";
  // On envoie les données HTTP en continu {@link PHP_MANUAL#flush}
  echo str_pad($str, 4096)."\n";
  flush();
}

function erreur($fonction, $msg) {
  global $colt_error, $db;
  echo "</div>";
  echo "\n<font color=$colt_error>";
  echo getLevelStr();
  echo "[".date("H:i:s  d/m/Y")."] "._("Erreur dans la fonction")." '$fonction' : "._("message")." $msg";
  echo "</font>";

  //Si possible on termine la db (mais si DB error précédemment alors ce n'est plus possible)
  if (method_exists($db, "query")) $result = $db->query("ROLLBACK");  //Termine la transaction
  if (method_exists($db, "disconnect")) $db->disconnect();  //Déconnecte de la base de données

  //Fin
  die("\n<hr />"._("Annulation des traitements déjà réalisés (ROLLBACK) et arrêt du batch !")."<br />\n</body></html>");
}

?>