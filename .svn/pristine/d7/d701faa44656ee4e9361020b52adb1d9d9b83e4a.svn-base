<?php

/**
 * [177] Effectuer approvisionnement delestage
 *
 * Cette opération comprends les écrans :
 * - Ead-1 : Liste des appro delestage autorises
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

if ($global_nom_ecran == "Ead-1") {

  global $global_id_client;

  // Affichage de la liste des mouvements
  $table = new HTML_TABLE_table(5, TABLE_STYLE_ALTERN);
  $table->set_property("title", "Liste des approvisionnements / délestages autorisés");
  $table->add_cell(new TABLE_cell("N°"));
  $table->add_cell(new TABLE_cell("Guichet"));
  $table->add_cell(new TABLE_cell("Montant"));
  $table->add_cell(new TABLE_cell("Type action"));
  $table->add_cell(new TABLE_cell(""));

  // Get liste autorisation de retrait
  $listeAutoriseApproDelestage = getListeApprovisionnementDelestage($global_id_guichet, 2);

  foreach ($listeAutoriseApproDelestage as $id => $autoriseApproDelestage) {

    $id_demande = trim($autoriseApproDelestage["id"]);
    $info_guichet= get_guichet_infos($autoriseApproDelestage["id_guichet"]);
    $guichet = trim($info_guichet["libel_gui"]);
    $montant = afficheMontant($autoriseApproDelestage["montant"])." ".$autoriseApproDelestage["devise"];
    $action_demande = adb_gettext($adsys["adsys_type_flux"][$autoriseApproDelestage["type_action"]]);
    $date_demande = pg2phpDate($autoriseApproDelestage["date_creation"]);

    if ($autoriseApproDelestage["type_action"] == 1){
      $prochain_ecran = "Agu-1";
    }else{
      $prochain_ecran = "Dgu-1";
    }

    $table->add_cell(new TABLE_cell($id_demande));
    $table->add_cell(new TABLE_cell($guichet));
    $table->add_cell(new TABLE_cell($montant));
    $table->add_cell(new TABLE_cell($action_demande));
    $table->add_cell(new TABLE_cell("<a href=".$PHP_SELF."?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$prochain_ecran."&id_dem=".$id_demande.">Effectuer action</a>"));
    $table->set_row_property("height","35px");
  }

  // Génération du tableau des demandes de retrait
  echo $table->gen_HTML();
}