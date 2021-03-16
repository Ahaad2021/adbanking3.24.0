<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Exécution du batch
 * @package Systeme
 **/

require_once 'lib/misc/VariablesGlobales.php';
require_once 'DB.php';
require_once 'lib/misc/Erreur.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/tarification.php';
require_once 'lib/dbProcedures/extraits.php';
require_once 'lib/dbProcedures/handleDB.php';

$appli = "batch"; //On est dans le batch (et pas dans l'application réelle)

$global_mouvements = array(); //Tous les mouvements réalisés durant le batch
$global_mouvements_attente = array();
//tableau des mouvements comptables du module crédit
$global_mouvements_credit=array();
//tableau contenant les mouvemnts des traitements de l'epargne
$global_mouvements_epargne=array();
$global_mouvements_attente_epargne=array();

// Au cas où le batch s'exécute à partir de cron, il faut initialiser certaines variables globales utilisées par le batch
$globalVars = initGlobalVars();
$global_id_agence = $globalVars["id_ag"];
$global_langue_rapport = $globalVars["langue_systeme_dft"];
$global_monnaie = $globalVars["monnaie"];
$global_monnaie_prec = $globalVars["monnaie_prec"];
$global_multidevise = $globalVars["multidevise"];
$global_monnaie_courante = $global_monnaie;
$global_monnaie_courante_prec = $global_monnaie_prec;

$arrete_comptes = array(); // Liste des comptes arrêtés : comptes dont on a calculé les intérêts
$dat_arretes = array(); //liste des DAT clôturés ou prolongés pour le compte rendu du batchw
$cat_arretes = array(); //liste des CAT clôturés ou prolongés pour le compte rendu du batch
$es_arretes = array(); //liste des épargnes à la source soldés et reconduits pour le compte rendu du batch
$rembourse_auto = array(); // Rembouresement automatique d'échéances  pour le compte rendu du batch
$frais_tenue_cpte = array(); // frais de tenue de compte pour le compte rendu du batch
$declasse_credit = array(); // pour le déclassement des crédits
$archivage_clients = array(); // liste des clients archivés
$frais_int_debiteurs = array(); // array contenant l'id, le solde et les intérêts des comptes dont on a prélevé des intérêts débiteurs
$transaction_ferlo = array(); // Liste des transactions Ferlo traitées
$ordres_traites = array(); // Liste des ordres permanents traités
$soldeComptaSoldeInterneCredit = array(); // liste des credits dont les solde de compta n'est pas egal au  compte interne associé
$soldeCreditSoldeInterneCredit = array(); // liste des credits dont le capital restant dû n'est pas egal au  compte interne associé

$level = 1;

$array_credit_mouvement = array(); // Liste des credits avec le solde_msq

require_once 'batch/batch_declarations.php';

//Sous-modules
require_once 'batch/backup_db.php';
require_once 'batch/traite_agence.php';
require_once 'batch/traite_clients.php';
require_once 'batch/traite_credit.php';
require_once 'batch/traite_epargne.php';
require_once 'batch/traite_compta.php';
require_once 'batch/traite_budget.php';
require_once 'batch/traite_net_bank.php';
require_once 'batch/rapport_batch.php';
require_once 'batch/divers.php';
require_once 'batch/traite_mobile_banking.php';

//test AT-68
global $mouvement_declassement;
$mouvement_declassement = array();

// Si la date n'est pas définie, c'est que le batch est exécuté automatiquement par cron
// Le batch s'exécutant après minuit, il faut que $date_total soit à la veille
if (!isset($date_jour)) {
  $date_jour = date("d") - 1;
  $date_mois = date("m");
  $date_annee = date("Y");
}
// Sinon, la date passée doit être la date pour laquelle on exécute le batch, généralement date_last_batch + 1 jour

$date_total = $date_jour."/".$date_mois."/".$date_annee;

echo "<div class='batch'>\n";
affiche(_("Démarrage traitements de nuit pour la date du ")."<b>$date_total</b> ...");
// On place le temps d'exécution maximal du batch à 10min par jour traité
set_time_limit(600);

if (verif_conditions()) {
  //les conditions sont réunies pour l'exécution du batch
  $db = $dbHandler->openConnection();
  $global_id_agence = getNumAgence();
  //Met à jour le statut de l'agence
  $result = $db->query("UPDATE ad_agc SET statut = 3 WHERE id_ag=$global_id_agence");
  if (DB::isError($result))
    erreur("verif_conditions()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    
  // On inscrit tous les mouvements dans la table historique
  $myErr = ajout_historique(212, NULL, NULL, NULL, date("r"), NULL, NULL);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    erreur(_("appel à")." ajout_historique", $error[$myErr->errCode].$myErr->param);
  }

  traite_budget();

  $global_id_his=$myErr->param;
  if (!isSiege()) { //Si on n'est pas au siège

    traite_agence(); //Traite l'agence

    traite_epargne(); //Traite l'épargne

    $array_credit_mouvement = traite_credit(); //Traite les crédits

    traite_clients(); //Traite les clients
  }

  traite_compta(); // Traite la comptabilité

  update_conditions(); //Mise à jour date last_batch mais on n'ouvre pas l'agence avant prélèvement frais tenue

  affiche(_("Ecriture dans l'historique ..."));
  incLevel();
  // On supprime le temps d'exécution maximal pour la génération de l'historique (voir #1306)
  // TODO: trouver une meilleure solution, voir #1356
  set_time_limit(0);

  // Fix date comptable et date valeur
  overwrite_date_compta($global_mouvements);
  overwrite_date_compta($global_mouvements_attente);

  // On inscrit tous les mouvements dans la table historique
  $myErr = ajout_historique(212, NULL, NULL, NULL, date("r"), $global_mouvements, $global_mouvements_attente,$global_id_his);
  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    erreur(_("appel à")." ajout_historique", $error[$myErr->errCode].$myErr->param);
  }
  $id_his = $myErr->param;
  decLevel();
  affiche(_("Ecriture dans l'historique terminée !"));

  //Termine la transaction
  $dbHandler->closeConnection(true);

  if (!isSiege()) {
    if (!empty($global_mouvements_credit)){

      //Effectue le prélèvement frais transactionnel SMS || envoi mouvement msq
      traite_mobile($array_credit_mouvement);
    }
  }

  //AT-68 : Mise a jour des ecritures pour les declassements
  if (!isSiege()) {
    if (!empty($mouvement_declassement)){

      //Effectue le prélèvement frais transactionnel SMS
      traite_declassement($mouvement_declassement,270,212,$date_total);
    }
  }

}

//------------------------------------------------------- Traitement des frais de tenue de compte à part --------------


$db = $dbHandler->openConnection();

$ok_batch = verif_batch_exec($date_total, $global_id_agence);
$ok_frais = verif_frais_tenue($date_total, $global_id_agence); //est-ce qu'on a déjà fait les frais pour aujourd'hui ?

if ($ok_batch && !($ok_frais) ) {
  //Si le batch du jour a déjà eu lieu, on peut considérer le traitement des frais de tenue

  //détermination des dates de travail
  $dates_prelev = array();
  $dates_prelev = getDatesWork();

  //Effectue le prélèvement
  $myErr = prelevement_frais_tenue_cpt();
  if ($myErr->errCode != NO_ERR)
    erreur("batch.php",_("Erreur lors de la perception des frais de tenue")." : code ".$myErr->errCode);

  //Effectue le prélèvement frais forfaitaires mensuels abonnement
  $myErr = prelevement_frais_forfaitaires_mensuels_abonnement();
  if ($myErr->errCode != NO_ERR) {
    erreur("batch.php",_("Erreur lors de la perception des frais forfaitaires mensuels abonnement")." : code ".$myErr->errCode);
  }

  //NOTE : l'ajout historique des frais de tenue de compte est fait par la f° SQL

  update_conditions_frais_cpt();

}

// Mettre a jour les numeros de comptes comptable dans ad_cpt #357
// Commenté pour cause de lenteur: ticket#493
/**
 * @todo : decomment
 */
/* affiche (_("Démarre le mise à jour des numéros de comptes comptable pour les comptes internes...."));

$myErr = update_num_cpte_comptable();
if ($myErr->errCode != NO_ERR) {
	$dbHandler->closeConnection(false);
	erreur(_("appel à")." update_num_cpte_comptable", $error[$myErr->errCode].$myErr->param);
}

$counter = $myErr->param;
affiche ( sprintf ( _ ( "OK (%s comptes internes mises à jour avec les numéros de comptes comptable)" ), $counter[0], true, true ));
affiche (_("Mise à jour des numéros de comptes comptable pour les comptes internes terminés !")); 
*/

// Verification coherence donnees :
$myErr =verif_coherence_donnee_batch($date_total, $id_his);
if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    erreur(_("appel à")." verif_coherence_donnee_batch", $error[$myErr->errCode].$myErr->param);
  }
//NOTE : l'agence est déjà fermée et le reste

affiche("OK", true);

$dbHandler->closeConnection(true);

backup_db(); // Effectue une saubvegarde de la DB
//Il n'est pas opportun de faire une sauvegarde pour consolidation. Cette fonctionnalité sera revue.
//backup_db_consolidation(); // Effectue une saubvegarde de la DB pour consolidation

clean(); // Un peu de nettoyage

decLevel();
affiche(_("Traitements de nuit terminés !"));

// Compte rendu de traitement
traite_rapport();
echo "</div>";

// On considère qu'on vient de charger une nouvelle page et qu'on ne doit pas être déconnecté par le timeout
$global_last_axs = time();

?>