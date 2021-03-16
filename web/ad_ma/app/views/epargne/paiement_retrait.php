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

if ($global_nom_ecran == "Prd-11") {
  // Clear data session variables
  unset($SESSION_VARS['NumCpte'], $SESSION_VARS['type_retrait'], $SESSION_VARS['mandat'], $SESSION_VARS['frais_retrait'], $SESSION_VARS['type_recherche'], $SESSION_VARS['field_name'], $SESSION_VARS['nom_ben'], $SESSION_VARS['field_id'], $SESSION_VARS['id_ben'], $SESSION_VARS['tib'], $SESSION_VARS['beneficiaire'], $SESSION_VARS['tireur'], $SESSION_VARS['denomination'], $SESSION_VARS['adresse'], $SESSION_VARS['code_postal'], $SESSION_VARS['ville'], $SESSION_VARS['num_tel'], $SESSION_VARS['num_piece'], $SESSION_VARS['lieu_delivrance'], $SESSION_VARS['id_mandat'], $SESSION_VARS['remarque'], $SESSION_VARS['communication'], $SESSION_VARS['mnt'], $SESSION_VARS['num_chq'], $SESSION_VARS['date_chq'], $SESSION_VARS['envoi'], $SESSION_VARS['gpe'], $SESSION_VARS['gpe']['denom'], $SESSION_VARS['gpe']['pers_ext'], $SESSION_VARS['gpe']['denomination'], $SESSION_VARS['gpe']['id_pers_ext'], $SESSION_VARS['id_pers_ext']);

  global $global_remote_id_client, $global_remote_id_agence;

  // Affichage de la liste des mouvements
  $table = new HTML_TABLE_table(5, TABLE_STYLE_ALTERN);
  $table->set_property("title", "Liste de demandes de retrait en déplacé autorisé");
  $table->add_cell(new TABLE_cell("N°"));
  $table->add_cell(new TABLE_cell("Type de retrait"));
  $table->add_cell(new TABLE_cell("Montant"));
  $table->add_cell(new TABLE_cell("Date demande"));
  $table->add_cell(new TABLE_cell(""));

  // Get liste autorisation de retrait
  $listeAutoriseRetraitDeplace = getListeRetraitDeplaceAttente($global_remote_id_client, 2,$global_remote_id_agence);

  foreach ($listeAutoriseRetraitDeplace as $id => $autoriseRetraitDeplace) {

    $id_demande = trim($autoriseRetraitDeplace["id"]);
    $type_retrait = getLabelChoixRetraitDeplace($autoriseRetraitDeplace["type_retrait"]);
    //$choix_retrait = getLabelChoixRetraitDeplace($autoriseRetraitDeplace["choix_retrait"]);
    $montant_retrait = afficheMontant($autoriseRetraitDeplace["montant_retrait"], true);
    $date_demande = pg2phpDate($autoriseRetraitDeplace["date_creation"]);

    if ($global_multidevise) {
      $prochain_ecran = 'Rtm-2';
    } else {
      $prochain_ecran = 'Rcp-21';
    }

    $table->add_cell(new TABLE_cell($id_demande));
    $table->add_cell(new TABLE_cell($type_retrait));
    $table->add_cell(new TABLE_cell($montant_retrait));
    $table->add_cell(new TABLE_cell($date_demande));
    $table->add_cell(new TABLE_cell("<a href=".$PHP_SELF."?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=".$prochain_ecran."&id_dem=".$id_demande.">Effectuer le retrait</a>"));
    $table->set_row_property("height","35px");
  }

  // Génération du tableau des demandes de retrait
  echo $table->gen_HTML();

}  else {
  signalErreur(__FILE__, __LINE__, __FUNCTION__);
  // _("L'écran $global_nom_ecran n'existe pas")
}