<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Batch : traitements pour le rapport de batch
 * @package Systeme
 **/

require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/divers.php';
require_once 'batch/batch_declarations.php';
require_once 'modules/rapports/xml_agence.php';
require_once 'modules/rapports/xslt.php';

function traite_rapport() {
  global $date_total, $date_annee, $date_mois, $date_jour;
  global $lib_path;
  global $archivage_clients, $arrete_comptes, $dat_arretes, $cat_arretes, $es_arretes, $rembourse_auto, $declasse_credit, $frais_tenue_cpte, $frais_int_debiteurs, $transaction_ferlo, $ordres_traites;
  global $soldeComptaSoldeInterneCredit ;
  global $soldeCreditSoldeInterneCredit;
  
  affiche(_("Démarre le traitement du rapport compte rendu ..."));
  incLevel();

  /* Construction du xml pour le rappport compte rendu batch */
  $xml = xml_batch($date_total, $archivage_clients, $arrete_comptes, $dat_arretes, $cat_arretes, $es_arretes, $rembourse_auto, $declasse_credit, $frais_tenue_cpte, $frais_int_debiteurs, $transaction_ferlo, $ordres_traites,$soldeComptaSoldeInterneCredit,$soldeCreditSoldeInterneCredit);

  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'batch.xslt');

  $dir = opendir("$lib_path/backup/batch/rapports");
  if ($dir == false) {
    affiche(_("Rapport non mémorisé: le chemin n'est pas défini"));
  } else {
    $agence = getAgence();
    $nomAgence = strtolower(cleanSpecialCharacters($agence[0]));
    $destination = "$lib_path/backup/batch/rapports/".$date_annee."-".$date_mois."-".$date_jour . "." .$nomAgence. "_" . $agence[1] . ".pdf";
    get_pdf_html($fichier_pdf, $destination);
    affiche(_("Rapport mémorisé sous"). $destination);
    decLevel();
  }

  affiche(_("Traitement du rapport terminé !"));

}

?>
