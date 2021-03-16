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
require_once 'ad_ma/batch/batch_declarations_ma.php';
require_once 'modules/rapports/xml_agence.php';
require_once 'modules/rapports/xslt.php';

function traite_rapport() {
  global $date_total, $date_annee, $date_mois, $date_jour;
  global $lib_path;
  global $archivage_clients, $arrete_comptes, $dat_arretes, $cat_arretes, $es_arretes, $rembourse_auto, $declasse_credit, $frais_tenue_cpte, $frais_int_debiteurs, $transaction_ferlo, $ordres_traites;
  global $soldeComptaSoldeInterneCredit ;
  global $soldeCreditSoldeInterneCredit;
  global $global_id_agence, $BatchObj, $batch_db_host;
  
  affiche(_("Démarre le traitement du rapport compte rendu ..."));
  incLevel();

  /* Construction du xml pour le rappport compte rendu batch */
  $xml = xml_batch($date_total, $archivage_clients, $arrete_comptes, $dat_arretes, $cat_arretes, $es_arretes, $rembourse_auto, $declasse_credit, $frais_tenue_cpte, $frais_int_debiteurs, $transaction_ferlo, $ordres_traites,$soldeComptaSoldeInterneCredit,$soldeCreditSoldeInterneCredit);

  $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'batch.xslt');
  
  $transfer_pdf_status = FALSE;
  
  // Initialiser les variables
  $local_host_ip      = $_SERVER["HTTP_HOST"];
  $remote_host_ip     = $batch_db_host;
  $local_path         = '';
  $remote_path        = '/var/lib/adbanking/backup/batch/rapports';
  $local_ssh_login    = 'batchma';
  $local_ssh_password = 'b@tchm@';
  $remote_ssh_login   = 'batchma';

  $dir = opendir("$lib_path/backup/batch/rapports");
  if ($dir == false) {
    affiche(_("Rapport non mémorisé: le chemin n'est pas défini"));
  } else {
    $agence = getAgence();
    $nomAgence = strtolower(cleanSpecialCharacters($agence[0]));
    $destination = "$lib_path/backup/batch/rapports/agc" . $global_id_agence . "_" . $date_annee."-".$date_mois."-".$date_jour . "." .$nomAgence. "_" . $agence[1] . ".pdf";
    get_pdf_html($fichier_pdf, $destination);
    
    // Set pdf path
    $local_path = $destination;

    if(file_exists($local_path) && $transfer_pdf_status===FALSE)
    {
        // Transfert le fichier crée en local sur le serveur distant 
        if($BatchObj->transferBatchFile($local_host_ip, $remote_host_ip, $local_path, $remote_path, $local_ssh_login, $local_ssh_password, $remote_ssh_login))
        {
            $transfer_pdf_status = TRUE;
        }
    }

    // Mettre à jour le chemin du pdf rapport batch
    $BatchObj->updateBatchRapportPdfPath($destination);
    
    affiche(_("Rapport mémorisé sous "). $destination);
    decLevel();
  }

  affiche(_("Traitement du rapport terminé !")); 
  
  if(file_exists($local_path) && $transfer_pdf_status===FALSE)
  {
    // Retry transfert le fichier crée en local sur le serveur distant 
    if($BatchObj->transferBatchFile($local_host_ip, $remote_host_ip, $local_path, $remote_path, $local_ssh_login, $local_ssh_password, $remote_ssh_login))
    {
        $transfer_pdf_status = TRUE;
    }
  }

}

?>
