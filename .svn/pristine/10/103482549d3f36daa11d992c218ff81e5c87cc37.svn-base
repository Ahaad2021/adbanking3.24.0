<?php

/**
 * Création des fichiers CSV utilisés pour exporter des données d'épargne vers un tableur.
 *
 * @package Rapports
 **/

/**
 * Génère l'export CSV pour l'impression des chèquiers.
 *
 * Pour chaque chèque, les données à exporter sont les suivantes :
 * - le numéro du chèque,
 * - le nom du client,
 * - le numéro de compte.
 *
 * @author Antoine Delvaux
 * @since 2.5
 * @param array $a_id_cptes Tableau avec les identifiants des comptes pour lesquels il faut exporter le chèquier.
 * @return string La chaîne pour l'export CSV
 */
function csvChequiers($a_chequiers_print) {
  // Le séparateur de champs est le point virgule
  $separator = ";";
  $csv = "";debug($a_chequiers_print,"gggg");
 
  foreach ($a_chequiers_print as $id => $tab_comde_chequier) {
  	$num_complet_cpte = $tab_comde_chequier ['num_complet_cpte'];
  	$nom_cli= $tab_comde_chequier ['nom'];
  	$nbre_carnet= $tab_comde_chequier ['nbre_carnets'];
  	$csv .= "\n".$num_complet_cpte.$separator.$nom_cli.$separator.$nbre_carnet.$separator;
   
  }
  //$csv = substr($csv, 1);
  return new ErrorObj(NO_ERR, $csv);
}


?>