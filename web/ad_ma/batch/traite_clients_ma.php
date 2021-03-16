<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Batch : traitements des clients
 * @package Systeme
 **/

require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/agence.php';

function archive_clients_EAV() {
  global $dbHandler;
  global $date_total;
  global $global_id_agence;
  global $archivage_clients;

  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();

  // Récupération de delai maximale d'existance d'une personne en attente de validation
  $sql = "SELECT delai_max_eav FROM ad_agc WHERE id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB::isError($result)) erreur("archive_client_EAV()", $result->getMessage());
  $row = $result->fetchrow();
  $delai_max = $row[0];

  affiche(sprintf(_("Archivage des clients en attente de validation depuis plus de %s jours ..."),$delai_max));
  incLevel();

  // Recherche de tous les clients à l'état EAV dont la date de création est antérieure à delai_max
  $sql = "SELECT id_client, id_cpte_base FROM ad_cli WHERE etat = 1 AND date_trunc ('day', ('$date_total' - date_crea)) > '$delai_max days'";
  $result = $db->query($sql);
  if (DB::isError($result))
    erreur("archive_client_EAV():BOOH:$sql", $result->getMessage());
  $count = 0;
  while ($tmprow = $result->fetchrow()) {
    $id_client = $tmprow[0];
    $id_cpte_base = $tmprow[1];
    affiche(sprintf(_("Archivage du client n° %s"),$id_client));
    // Fermeture du compte de base du client
    $sql = "UPDATE ad_cpt SET etat_cpte = 2 WHERE id_ag=$global_id_agence AND id_cpte = $id_cpte_base";
    $result2 = $db->query($sql);
    if (DB::isError($result)) erreur("archive_client_EAV()", $result->getMessage());
    // Archivage du client
    $sql = "UPDATE ad_cli SET etat = 8 WHERE id_ag=$global_id_agence AND id_client = $id_client";
    $result2 = $db->query($sql);
    if (DB::isError($result)) erreur("archive_client_EAV()", $result->getMessage());
    $count++;

    /* Recupération du client pour le rapport compte rendu batch */
    array_push($archivage_clients, $id_client);

  }

  $dbHandler->closeConnection(true);

  affiche(sprintf(_("OK (%s clients ont été archivés)"),$count), true);
  decLevel();
  affiche(_("Archivage des clients terminé !"));
}

function traite_clients() {

  affiche(_("Démarre le traitement des clients ..."));
  incLevel();

  archive_clients_EAV();

  decLevel();
  affiche(_("Traitement des clients terminé !"));
}

?>