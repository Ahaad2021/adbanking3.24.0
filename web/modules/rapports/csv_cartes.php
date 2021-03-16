<?php

/**
 * Création des fichiers CSV utilisés pour exporter des données d'épargne vers un tableur.
 *
 * @package Rapports
 **/

/**
 * Génère l'export CSV pour envoyer a Indigo.
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
function csvCartesIndigo() {
  // Le séparateur de champs est le point virgule
  $separator = ";";
  $csv = "";

    $infos = getCommandesCartes();

    if ($infos != NULL) {
        foreach ($infos as $key => $value) {
            $csv .= "\n". $value['request_ref_num'] . $separator .
            $value['branch_code'] . $separator .
            $value['first_name'] . $separator .
            $value['middle_name'] . $separator .
            $value['last_name'] . $separator .
            $value['id_client'] . $separator .
            $value['nom_carte'] . $separator .
            $value['titre'] . $separator .
            $value['num_identite_passeport'] . $separator .
            $value['resident'] . $separator .
            $value['type_client'] . $separator .
            $value['reason_for_issue'] . $separator .
            $value['id_cpte'] . $separator .
            $value['type_compte'] . $separator .
            $value['priorite'] . $separator .
            pg2phpDate($value['date_cmde']) . $separator .
            $value['guichet'] . $separator;
        }
    }

 
//  foreach ($a_chequiers_print as $id => $tab_comde_chequier) {
//  	$num_complet_cpte = $tab_comde_chequier ['num_complet_cpte'];
//  	$nom_cli= $tab_comde_chequier ['nom'];
//  	$nbre_carnet= $tab_comde_chequier ['nbre_carnets'];
//  	$csv .= "\n".$num_complet_cpte.$separator.$nom_cli.$separator.$nbre_carnet.$separator;
//
//  }
  //$csv = substr($csv, 1);
  return new ErrorObj(NO_ERR, $csv);
}


?>