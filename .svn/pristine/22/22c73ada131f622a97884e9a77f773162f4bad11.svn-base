<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Batch : fonctions spécifiques au traitement de l'agence
 * @package Systeme
 **/

require_once 'lib/dbProcedures/historique.php';

function total_clients() {
  global $dbHandler;
  global $global_id_agence;

  affiche(_("Démarre le calcul du nombre total de clients ..."));
  incLevel();

  $db = $dbHandler->openConnection();

  $sql = "update ad_agc set total_clients = (select count(*) from ad_cli) where id_ag = $global_id_agence";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    erreur("total_clients()", $result->getMessage());
  }


  $dbHandler->closeConnection(true);

  affiche(_("OK"), true);
  decLevel();
  affiche(_("Calcul du nombre total de clients terminé !"));
}

function clients_actifs() {
  global $dbHandler;
  global $global_id_agence;

  affiche(_("Démarre le calcul du nombre de clients actifs ..."));
  incLevel();

  $db = $dbHandler->openConnection();

# Les clients actifs sont ceux ayant fait au moins un mouvement depuis 1 an.
  $date = date("d/m/Y", mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1));

  $sql = "update ad_agc set clients_actifs = (select count(id_client) from ad_cli where (select count(c.id_cpte) from ad_cli a,  ad_mouvement m, ad_ecriture e, ad_cpt c where m.cpte_interne_cli = c.id_cpte and m.id_ecriture = e.id_ecriture and e.date_comptable > '$date' and c.id_titulaire = a.id_client) > 0 and etat = 2) where id_ag = $global_id_agence";

  $result = $db->query($sql);
  if (DB::isError($result)) {
    erreur("clients_actifs()", $result->getMessage());
  }

  $dbHandler->closeConnection(true);

  affiche("OK", true);
  decLevel();
  affiche(_("Calcul du nombre de clients actifs terminé !"));
}

function traite_agence() {

  affiche(_("Démarre le traitement des agences ..."));
  incLevel();

  total_clients();
  clients_actifs();

  decLevel();
  affiche(_("Traitement des agences terminé !"));
}

?>