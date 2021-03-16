<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Batch : traitements des clients
 * @package Systeme
 **/

require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/carte_atm.php';

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

function mise_a_jour_clientActif(){

  global $global_id_agence;

  // Vérification si Multi-Agence
  if(isset($global_id_agence) && isMultiAgence()) {

    require_once 'ad_ma/app/models/AgenceRemote.php';

    // Récupère la liste des agences
    $ListeAgences = AgenceRemote::getListAllAgence();

    $nb_clients_actifs_ma = 0;
    if (is_array($ListeAgences) && count($ListeAgences) > 0) {
      foreach ($ListeAgences as $key => $obj) {

        $remote_conn = AgenceRemote::getRemoteAgenceConnection($obj['id_agc']);

        $nb_clients_actifs = 0;

        if ($remote_conn != null && is_object($remote_conn) && $remote_conn instanceof DBC) {
          // Récupère le nombre de clients actif par agence
          $sql = "SELECT clients_actifs FROM ad_agc WHERE id_ag = ".$obj['id_agc'];
          $nb_clients_actifs = $remote_conn->prepareFetchColumn($sql);
          AgenceRemote::unsetRemoteAgenceConnection($remote_conn);

          global $dbHandler, $global_id_agence;

          // Ouvrir une connexion
          $db = $dbHandler->openConnection();
          $sql1 = "UPDATE adsys_multi_agence SET client_actifs = $nb_clients_actifs where id_agc = ".$obj['id_agc'];
          $result = $db->query($sql1);
          if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
          }
          $dbHandler->closeConnection(true);

        }
      }
    }
    return $nb_clients_actifs_ma;
  }
}

function traite_clients() {
    global $date_total;

  affiche(_("Démarre le traitement des clients ..."));
  incLevel();

  archive_clients_EAV();

  mise_a_jour_clientActif();

  //Expiration des cartes ATM arriver a echeance
  expirationCarteAtm($date_total);

  decLevel();
  affiche(_("Traitement des clients terminé !"));
}

?>