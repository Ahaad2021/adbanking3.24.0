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

if ($global_nom_ecran == "Pdr-1") {

    global $global_id_client;

    // Affichage de la liste des mouvements
    $table = new HTML_TABLE_table(5, TABLE_STYLE_ALTERN);
    $table->set_property("title", "Liste de demandes de retrait autorisé");
    $table->add_cell(new TABLE_cell("N°"));
    $table->add_cell(new TABLE_cell("Type de retrait"));
    $table->add_cell(new TABLE_cell("Montant"));
    $table->add_cell(new TABLE_cell("Date demande"));
    $table->add_cell(new TABLE_cell(""));

    // Get liste autorisation de retrait
    $listeAutoriseRetrait = getListeRetraitAttente($global_id_client, 2);

    foreach ($listeAutoriseRetrait as $id => $autoriseRetrait) {

        $id_demande = trim($autoriseRetrait["id"]);
        $type_retrait = trim($autoriseRetrait["type_retrait"]);
        $choix_retrait = getLabelChoixRetrait($autoriseRetrait["choix_retrait"]);
        $montant_retrait = afficheMontant($autoriseRetrait["montant_retrait"], true);
        $date_demande = pg2phpDate($autoriseRetrait["date_crea"]);

        $prochain_ecran = "Rcp-2";
        if ($type_retrait == 2) {
            $prochain_ecran = "Rex-2";
        }

        $table->add_cell(new TABLE_cell($id_demande));
        $table->add_cell(new TABLE_cell($choix_retrait));
        $table->add_cell(new TABLE_cell($montant_retrait));
        $table->add_cell(new TABLE_cell($date_demande));
        $table->add_cell(new TABLE_cell("<a href=".$PHP_SELF."?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$prochain_ecran."&id_dem=".$id_demande.">Effectuer le retrait</a>"));
        $table->set_row_property("height","35px");
    }

    // Génération du tableau des demandes de retrait
    echo $table->gen_HTML();

} elseif ($global_nom_ecran == "Pdr-2") {



} else {
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
    // _("L'écran $global_nom_ecran n'existe pas")
}