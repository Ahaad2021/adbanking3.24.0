<?php
/**
 * Created by PhpStorm.
 * User: Roshan
 * Date: 06/29/2018
 */
require_once 'lib/misc/xml_lib.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/dbProcedures/traitements_compensation.php';

function xml_compensation_siege_auto_log($criteres,$DATA,$devise, $isCsv=false){

  global $adsys;

  $document = create_xml_doc("compensation_siege_log", "compensation_siege_log.dtd");
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'SYS-REC');
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $criteres);

  $compensation_etat_log = $root->new_child("compensation_etat_log", "");

  foreach ($DATA as $log_details) {
    $details_log = $compensation_etat_log->new_child("details_log", "");
    $id_agence = $details_log->new_child("id_agence", $log_details['id_agence']);
    $agence = $details_log->new_child("agence", $log_details['nom_agence']);
    //$date_rapport = $log_details['date_rapport'];
    //$date_rapport = $details_log->new_child("date_rapport", date('d/m/Y',strtotime("$date_rapport")));
    $etat = $details_log->new_child("etat", $log_details['etat_compensation']);
    $etat_compensation = $log_details['etat_compensation'];
    $etat_compensation = $details_log->new_child("etat_compensation", adb_gettext($adsys["adsys_etat_compensation_siege_auto"]["$etat_compensation"]));
    $date_derniere_compensation = $log_details['date_derniere_compensation'];
    $date_derniere_compensation = $details_log->new_child("date_derniere_compensation", date('d/m/Y H:i:s',strtotime("$date_derniere_compensation")));
    $date_derniere_compensation_reussi = $log_details['date_derniere_reussi'];
    $date_derniere_compensation_reussi = $details_log->new_child("date_derniere_compensation_reussi", date('d/m/Y H:i:s',strtotime("$date_derniere_compensation_reussi")));
  }
  return $document->dump_mem(true);
}
?>