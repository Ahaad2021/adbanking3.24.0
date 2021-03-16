<?php

/**
 * [74] Paiement retrait
 *
 * Cette opération comprends les écrans :
 * - Pdr-1 : Liste paiement retrait autorisé
 * - Pdr-2 : Confirmation paiement retrait autorisé
 *
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';

if ($global_nom_ecran == "Pdt-1") {

  global $global_id_client;

  // Affichage de la liste des mouvements
  $table = new HTML_TABLE_table(5, TABLE_STYLE_ALTERN);
  $table->set_property("title", "Liste de demandes de transfert autorisé");
  $table->add_cell(new TABLE_cell("N°"));
  $table->add_cell(new TABLE_cell("Type de transfert"));
  $table->add_cell(new TABLE_cell("Montant"));
  $table->add_cell(new TABLE_cell("Date demande"));
  $table->add_cell(new TABLE_cell(""));

  // Get liste autorisation de retrait
  $listeAutoriseTransfert = getListeTransfertAttente($global_id_client, 2);

  foreach ($listeAutoriseTransfert as $id => $autoriseTransfert) {

    $id_demande = trim($autoriseTransfert["id"]);
    $type_transfert = trim($autoriseTransfert["type_transfert"]);
    $choix_transfert = getLabelChoixTransfert($autoriseTransfert["type_transfert"]);
    $montant_transfert = afficheMontant($autoriseTransfert["montant_transfert"], true);
    $date_demande = pg2phpDate($autoriseTransfert["date_crea"]);

    $prochain_ecran = "Tcp-3";
    /*if ($type_retrait == 2) {
      $prochain_ecran = "Rex-2";
    }*/

    $table->add_cell(new TABLE_cell($id_demande));
    $table->add_cell(new TABLE_cell($choix_transfert));
    $table->add_cell(new TABLE_cell($montant_transfert));
    $table->add_cell(new TABLE_cell($date_demande));
    $table->add_cell(new TABLE_cell("<a href=".$PHP_SELF."?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$prochain_ecran."&id_dem=".$id_demande.">Effectuer le transfert</a>"));
    $table->set_row_property("height","35px");
  }

  // Génération du tableau des demandes de retrait
  echo $table->gen_HTML();

} elseif ($global_nom_ecran == "Pdt-2") {



} else {
  signalErreur(__FILE__, __LINE__, __FUNCTION__);
  // _("L'écran $global_nom_ecran n'existe pas")
}