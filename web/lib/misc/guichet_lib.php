<?php
require_once 'lib/misc/tableSys.php';

function isCaisseCentrale($possede_guichet, $liste_axs) {
  //Renvoie true si ce profile répond au critères pour être 'gestionnaire caisse centrale'
  global $adsys;

  $all_functions = true;
  reset($adsys["fonctions_cc"]);
  while (($all_functions) &&
         (list(,$value) = each($adsys["fonctions_cc"])))
    if (!in_array($value, $liste_axs)) $all_functions = false;

  return (($all_functions) && (!$possede_guichet));
}

?>